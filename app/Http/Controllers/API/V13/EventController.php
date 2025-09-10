<?php declare (strict_types = 1);

namespace App\Http\Controllers\API\V13;

use App\Events\SendEventCancelledEvent;
use App\Http\Controllers\API\V12\EventController as v12EventController;
use App\Http\Requests\Api\V13\CreateEventCsatRequest;
use App\Http\Resources\V12\EventDetailsResource;
use App\Http\Traits\ProvidesAuthGuardTrait;
use App\Http\Traits\ServesApiTrait;
use App\Jobs\SendEventBookedEamilJob;
use App\Jobs\SendEventPushNotificationJob;
use App\Models\Event;
use App\Models\EventBookingLogs;
use App\Models\EventRegisteredUserLog;
use App\Models\Notification;
use App\Models\NotificationUser;
use App\Models\User;
use Carbon\Carbon;
use DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EventController extends v12EventController
{
    use ServesApiTrait, ProvidesAuthGuardTrait;

    /**
     * To store Event CSAT(feedback) response of user
     * @param CreateEventCsatRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function submitCsat(CreateEventCsatRequest $request)
    {
        try {
            $user            = $this->user();
            $eventBookingLog = EventBookingLogs::select('id', 'is_csat')->find($request->bookingId);

            // check is_csat is enable for the booking
            if (empty($eventBookingLog) || (!empty($eventBookingLog) && !$eventBookingLog->is_csat)) {
                return $this->preConditionsFailedResponse('It seems feedback has been turned off for an event.');
            }

            // check user is a participated member of an event
            $isMember = $eventBookingLog->users()->select('users.id')->where('user_id', $user->id)->first();
            if (is_null($isMember)) {
                return $this->notFoundResponse('It seems an event is not longer available for you.');
            }

            // check user has already submitted CSAT
            $alreadySubmitted = $eventBookingLog->csat()->select('event_csat_user_logs.id')
                ->where('user_id', $user->id)->first();
            if (!is_null($alreadySubmitted)) {
                return $this->preConditionsFailedResponse('It seems feedback has been already submitted for an event.');
            }

            // store CSAT feedback into database
            $eventBookingLog->csat()->attach($user, [
                'feedback'      => (!empty($request->feedback) ? $request->feedback : null),
                'feedback_type' => $request->feedbackType,
            ]);

            // send success response
            return $this->successResponse(['data' => []], "Thanks, we really appreciate your feedback.");
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
            $appTimezone    = config('app.timezone');
            $now            = now($appTimezone)->toDateTimeString();
            $user           = $this->user();
            $company        = $user->company()->first();
            $role           = getUserRole();
            $skipValidation = !(isset($request->type) && $request->type == "csat");
            $isRegistered   = EventRegisteredUserLog::select('id')
                ->where('user_id', $user->id)
                ->where('event_booking_log_id', $eventbookinglogs->id)
                ->where('is_cancelled', 0)
                ->first();

            if ($eventbookinglogs->status != '4' && $skipValidation) {
                return $this->notFoundResponse('Event not found');
            }

            if (is_null($isRegistered)) {
                return $this->notFoundResponse('Event not found');
            }

            $eventRecords = Event::select('events.id', 'event_booking_logs.id as booking_id', 'events.creator_id', 'event_booking_logs.presenter_user_id', 'event_booking_logs.meta', 'event_booking_logs.is_csat', 'events.subcategory_id', 'events.name', 'events.location_type', 'events.capacity', DB::raw('concat("<p>", events.description, "</p>", IFNULL(event_booking_logs.notes, "")) as description'), 'event_booking_logs.booking_date', 'event_booking_logs.start_time', 'events.created_at', 'event_booking_logs.status', \DB::raw("TIMESTAMPDIFF(SECOND, ADDTIME(TIMESTAMP(CONCAT(event_booking_logs . booking_date, ' ', event_booking_logs . start_time)), events.duration), '{$now}') AS endDiff"))
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
     * Register for an event
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request, EventBookingLogs $eventbookinglogs)
    {
        try {
            $appTimezone = config('app.timezone');
            $utcNow      = now($appTimezone);
            $user        = $this->user();
            $company     = $user->company()->select('companies.id')->first();
            $location    = $company->locations()->where('default', 1)->select('timezone')->first();
            $timezone    = (!empty($location->timezone) ? $location->timezone : $appTimezone);

            // check booked event is registered for logged in user's company
            if ($company->id != $eventbookinglogs->company_id) {
                return $this->notFoundResponse('Event not found');
            }

            // get an event details
            $event = $eventbookinglogs->event()
                ->select('id', 'creator_id', 'company_id', 'name', 'description', 'deep_link_uri')
                ->first();

            // check weather user has been already registered
            $checkAlreadyRegisterd = $eventbookinglogs->users()
                ->select('event_registered_users_logs.id', 'event_registered_users_logs.is_cancelled')
                ->where('user_id', $user->id)
                ->first();

            if (is_null($checkAlreadyRegisterd)) {
                \DB::beginTransaction();
                // register user for an event
                $eventbookinglogs->users()->attach($user, [
                    'event_id'     => $eventbookinglogs->event_id,
                    'is_cancelled' => false,
                ]);

                // data for iCal generation
                $meta             = $eventbookinglogs->meta;
                $presenterDetails = $eventbookinglogs->presenter()->select('id', 'first_name', 'last_name', 'email')->first();
                $startTime        = Carbon::parse("{$eventbookinglogs->booking_date} {$eventbookinglogs->start_time}", $appTimezone);
                $endTime          = Carbon::parse("{$eventbookinglogs->booking_date} {$eventbookinglogs->end_time}", $appTimezone);

                // Dispatch job to send email booked event email
                $email = collect([collect([$user->email])]);
                dispatch(new SendEventBookedEamilJob($email, [
                    'eventName'   => $event->name,
                    'type'        => 'user',
                    'company'     => $company->id,
                    'companyName' => '',
                    'bookingDate' => '',
                    'iCalData'    => [
                        'uid'         => (!empty($meta->uid) ? $meta->uid : date('Ymd') . 'T' . date('His') . '-' . rand() . '@zevo.app'),
                        'appName'     => config('app.name'),
                        'inviteTitle' => $event->name,
                        'description' => $event->description,
                        'timezone'    => $appTimezone,
                        'today'       => $utcNow->format('Ymd\THis\Z'),
                        'startTime'   => $startTime->format('Ymd\THis\Z'),
                        'endTime'     => $endTime->format('Ymd\THis\Z'),
                        'orgName'     => (!empty($presenterDetails) ? $presenterDetails->full_name : (!empty($meta->presenter) ? $meta->presenter : 'Zevo')),
                        'orgEamil'    => (!empty($presenterDetails) ? $presenterDetails->email : 'admin@zevo.app'),
                    ],
                ]));

                // Send push notification when user register from portal.
                \dispatch(new SendEventPushNotificationJob($event, "registered", collect([$user]), [
                    'company_id' => $company->id,
                    'booking_id' => $eventbookinglogs->id,
                ]));
                \DB::commit();
                return $this->successResponse(['data' => ""], "You have registered for {$event->name}");
            } elseif (!empty($checkAlreadyRegisterd) && $checkAlreadyRegisterd->is_cancelled) {
                return $this->notFoundResponse('Event is no longer available.');
            }
            return $this->notFoundResponse('Event already registered!');
        } catch (\Exception $e) {
            \DB::rollback();
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * Cancel a registered event
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function cancel(Request $request, EventBookingLogs $eventbookinglogs)
    {
        try {
            \DB::beginTransaction();
            $appTimezone = config('app.timezone');
            $utcNow      = now($appTimezone);
            $user        = $this->user();
            $company     = $user->company()->select('companies.id')->first();
            $location    = $company->locations()->where('default', 1)->select('timezone')->first();
            $timezone    = (!empty($location->timezone) ? $location->timezone : $appTimezone);

            if ($eventbookinglogs->company_id != $company->id) {
                return $this->notFoundResponse('Event not found');
            }

            // get an event details
            $event = $eventbookinglogs->event()
                ->select('id', 'creator_id', 'company_id', 'name', 'deep_link_uri')
                ->first();

            // check weather user has been already registered
            $checkAlreadyRegisterd = EventRegisteredUserLog::select('id', 'is_cancelled')
                ->where('event_booking_log_id', $eventbookinglogs->id)
                ->where('user_id', $user->id)->first();

            // Get Records from notification if notification available for registered and added
            $notificationIds = Notification::select('id')
                ->where('notifications.tag', 'event')
                ->where('notifications.company_id', $company->id)
                ->where('notifications.deep_link_uri', $event->deep_link_uri)
                ->get()->pluck('id')->toArray();
            if (!empty($notificationIds)) {
                // Remove event notification registered and added
                NotificationUser::whereIn('notification_id', $notificationIds)
                    ->where('notification_user.user_id', $user->id)
                    ->delete();
            }

            if ($checkAlreadyRegisterd) {
                // remove user entry from booking logs
                $cancelledStatus = $checkAlreadyRegisterd->is_cancelled;
                $checkAlreadyRegisterd->delete();

                // check if event status is already cancelled then no need to send email
                if (!$cancelledStatus) {
                    // prepare data for iCal generation
                    $meta             = $eventbookinglogs->meta;
                    $presenterDetails = $eventbookinglogs->presenter()->select('id', 'first_name', 'last_name', 'email')->first();
                    $startTime        = Carbon::parse("{$eventbookinglogs->booking_date} {$eventbookinglogs->start_time}" . $appTimezone);
                    $endTime          = Carbon::parse("{$eventbookinglogs->booking_date} {$eventbookinglogs->end_time}" . $appTimezone);
                    $uid              = (!empty($meta->uid) ? $meta->uid : date('Ymd') . 'T' . date('His') . '-' . rand() . '@zevo.app');

                    // send event cancel email to user
                    event(new SendEventCancelledEvent($user, [
                        "subject" => "{$event->name} Event Cancelled",
                        "message" => "Hi {$user->full_name}, this is to notify you that your attendance at {$event->name} event, has been cancelled.",
                        'iCal'    => generateiCal([
                            'uid'         => $uid,
                            'appName'     => config('app.name'),
                            'inviteTitle' => $event->name,
                            'description' => "Your attendance at {$event->name} event has been cancelled",
                            'timezone'    => $appTimezone,
                            'today'       => $utcNow->format('Ymd\THis\Z'),
                            'startTime'   => $startTime->format('Ymd\THis\Z'),
                            'endTime'     => $endTime->format('Ymd\THis\Z'),
                            'orgName'     => (!empty($presenterDetails) ? $presenterDetails->full_name : (!empty($meta->presenter) ? $meta->presenter : 'Zevo')),
                            'orgEamil'    => (!empty($presenterDetails) ? $presenterDetails->email : 'admin@zevo.app'),
                        ], 'cancelled'),
                    ]));
                }

                \DB::commit();
                return $this->successResponse(['data' => ""], 'Your attendance at ' . $event->name . ' event has been cancelled');
            } else {
                $eventbookinglogs->users()->attach($user, [
                    'event_id'     => $eventbookinglogs->event_id,
                    'is_cancelled' => true,
                ]);
                \DB::commit();
                return $this->successResponse(['data' => ""], $event->name . ' is no longer available.');
            }

            return $this->notFoundResponse('Event already cancelled!!!');
        } catch (\Exception $e) {
            \DB::rollback();
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }
}
