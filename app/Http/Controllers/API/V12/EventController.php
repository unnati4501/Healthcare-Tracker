<?php declare (strict_types = 1);

namespace App\Http\Controllers\API\V12;

use App\Http\Collections\V12\EventListCollection;
use App\Http\Controllers\API\V11\EventController as v11EventController;
use App\Http\Resources\V12\EventDetailsResource;
use App\Http\Traits\ProvidesAuthGuardTrait;
use App\Http\Traits\ServesApiTrait;
use App\Jobs\SendEventPushNotificationJob;
use App\Models\Event;
use App\Models\EventBookingLogs;
use App\Models\EventRegisteredUserLog;
use App\Models\Notification;
use App\Models\NotificationUser;
use App\Models\User;
use DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EventController extends v11EventController
{
    use ServesApiTrait, ProvidesAuthGuardTrait;

    /**
     * List all the event based on user company.
     *
     * @param string $type
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request, $type = 'registered')
    {
        try {
            $user    = $this->user();
            $company = $user->company()->first();

            $eventRecords = Event::select('events.id', 'event_booking_logs.id as booking_id', 'events.creator_id', 'event_booking_logs.presenter_user_id', 'events.subcategory_id', 'events.name', 'events.location_type', 'events.capacity', DB::raw('concat("<p>",events.description,"</p>",IFNULL(event_booking_logs.notes, "")) as description'), 'event_booking_logs.booking_date', 'event_booking_logs.start_time', 'events.created_at', 'event_booking_logs.meta')
                ->join('event_companies', function ($join) use ($company) {
                    $join
                        ->on('event_companies.event_id', '=', 'events.id')
                        ->where('event_companies.company_id', '=', $company->id);
                })
                ->join('event_booking_logs', function ($join) use ($company) {
                    $join
                        ->on('event_booking_logs.event_id', '=', 'events.id')
                        ->where('event_booking_logs.company_id', '=', $company->id);
                });

            if ($type == 'booked') {
                $eventRecords->join('event_registered_users_logs', function ($join) use ($user) {
                    $join
                        ->on('event_registered_users_logs.event_booking_log_id', '=', 'event_booking_logs.id')
                        ->where('event_registered_users_logs.user_id', '=', $user->id)
                        ->where('event_registered_users_logs.is_cancelled', '=', 0);
                });
            } else {
                $checkAlreadyRegisterd = EventRegisteredUserLog::where('user_id', $user->id)->pluck('event_booking_log_id')->toArray();
                $eventRecords->whereNotIn('event_booking_logs.id', $checkAlreadyRegisterd);
            }

            $eventRecords = $eventRecords
                ->where('event_booking_logs.status', '4')
                ->where('events.status', '2')
                ->groupBy('event_booking_logs.id')
                ->orderBy('event_booking_logs.booking_date', 'ASC')
                ->orderBy('events.created_at', 'DESC')
                ->paginate(config('zevolifesettings.datatable.pagination.short'));

            if ($eventRecords->count() > 0) {
                // collect required data and return response
                return $this->successResponse(new EventListCollection($eventRecords), 'Events Retrieved Successfully');
            } else {
                // return empty response
                return $this->successResponse(['data' => []], 'No results');
            }
        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * Get event details
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function detail(Request $request, EventBookingLogs $eventbookinglogs)
    {
        try {
            $user         = $this->user();
            $company      = $user->company()->first();
            $role         = getUserRole();
            $isRegistered = EventRegisteredUserLog::where('user_id', $user->id)->where('event_id', $eventbookinglogs->event_id)->first();
            // $isCheckCompany = $eventbookinglogs->companies()->where('company_id', $company->id)->first();

            if ($eventbookinglogs->status != '4') {
                return $this->notFoundResponse('Event not found');
            } elseif ($isRegistered == null) {
                return $this->notFoundResponse('Event not found');
            }

            $eventRecords = Event::select('events.id', 'event_booking_logs.id as booking_id', 'events.creator_id', 'event_booking_logs.presenter_user_id', 'events.subcategory_id', 'events.name', 'events.location_type', 'events.capacity', DB::raw('concat("<p>",events.description,"</p>",IFNULL(event_booking_logs.notes, "")) as description'), 'event_booking_logs.booking_date', 'event_booking_logs.start_time', 'events.created_at')
                ->join('event_companies', function ($join) use ($company) {
                    $join->on('event_companies.event_id', '=', 'events.id')
                        ->where('event_companies.company_id', '=', $company->id);
                })
                ->join('event_booking_logs', function ($join) use ($company) {
                    $join->on('event_booking_logs.event_id', '=', 'events.id')
                        ->where('event_booking_logs.company_id', '=', $company->id);
                })
                ->where('event_booking_logs.id', $eventbookinglogs->id)
                ->groupBy('events.id')
                ->first();

            return $this->successResponse(['data' => new EventDetailsResource($eventRecords)], 'Events details retrived successfully');
        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * Cancel Events
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function cancel(Request $request, EventBookingLogs $eventbookinglogs)
    {
        try {
            $user         = $this->user();
            $company      = $user->company()->first();
            $eventcompany = $eventbookinglogs->select('id')->where('company_id', $company->id)->first();

            if (!$eventcompany) {
                return $this->notFoundResponse('Event not found');
            }

            $event = Event::select('id', 'deep_link_uri', 'company_id', 'name', 'creator_id')
                ->where('id', $eventbookinglogs->event_id)
                ->first();

            // Get Records from event registered user logs
            $checkAlreadyRegisterd = EventRegisteredUserLog::select('id')
                ->where('event_id', $eventbookinglogs->event_id)
                ->where('event_booking_log_id', $eventbookinglogs->id)
                ->where('user_id', $user->id)->first();

            // Get Records from notification if notification available for registered and added
            $notificationIds = Notification::select('id')->where('notifications.tag', '=', 'event')->where('notifications.company_id', '=', $company->id)->where('notifications.deep_link_uri', '=', $event->deep_link_uri)->get()->pluck('id')->toArray();

            if (!empty($notificationIds)) {
                // Remove event notification registered and added
                NotificationUser::whereIn('notification_id', $notificationIds)->where('notification_user.user_id', '=', $user->id)->delete();
            }

            if ($checkAlreadyRegisterd) {
                $checkAlreadyRegisterd->delete();
                return $this->successResponse(['data' => ""], 'Your attendance at ' . $event->name . ' event has been cancelled');
            } else {
                $registeredLogs = [
                    'event_id'             => $eventbookinglogs->event_id,
                    'event_booking_log_id' => $eventbookinglogs->id,
                    'user_id'              => $user->id,
                    'is_cancelled'         => 1,
                ];

                EventRegisteredUserLog::create($registeredLogs);
                return $this->successResponse(['data' => ""], $event->name . ' is no longer available.');
            }

            return $this->notFoundResponse('Event already cancelled!!!');
        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * Register Events
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request, EventBookingLogs $eventbookinglogs)
    {
        try {
            $user         = $this->user();
            $company      = $user->company()->first();
            $eventcompany = $eventbookinglogs->select('id')->where('company_id', $company->id)->first();

            if (!$eventcompany) {
                return $this->notFoundResponse('Event not found');
            }

            $event = Event::select('id', 'deep_link_uri', 'company_id', 'name', 'creator_id')
                ->where('id', $eventbookinglogs->event_id)
                ->first();

            $checkAlreadyRegisterd = EventRegisteredUserLog::select('id')
                ->where('event_id', $eventbookinglogs->event_id)
                ->where('event_booking_log_id', $eventbookinglogs->id)
                ->where('user_id', $user->id)->first();

            if (!$checkAlreadyRegisterd) {
                $registeredLogs = [
                    'event_id'             => $eventbookinglogs->event_id,
                    'event_booking_log_id' => $eventbookinglogs->id,
                    'user_id'              => $user->id,
                ];
                $eventRegisteredUserLog = EventRegisteredUserLog::create($registeredLogs);

                if ($eventRegisteredUserLog) {
                    // Send push notification when user register from portal.
                    \dispatch(new SendEventPushNotificationJob($event, "registered", collect([$user]), [
                        'company_id' => $company->id,
                        'booking_id' => $eventbookinglogs->id,
                    ]));
                }

                return $this->successResponse(['data' => ""], "You have registered for {$event->name}");
            }

            return $this->notFoundResponse('Event already registered!');
        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }
}
