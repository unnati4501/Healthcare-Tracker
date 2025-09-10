<?php declare (strict_types = 1);

namespace App\Http\Controllers\API\V20;

use App\Events\SendEventCancelledEvent;
use App\Http\Collections\V20\EventListCollection;
use App\Http\Controllers\API\V14\EventController as v14EventController;
use App\Http\Resources\V20\EventDetailsResource;
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

class EventController extends v14EventController
{
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
            $user        = $this->user();
            $appTimezone = config('app.timezone');
            $timezone    = (!empty($user->timezone) ? $user->timezone : $appTimezone);
            $company     = $user->company()->first();

            $eventRecords = Event::select('events.id', 'event_booking_logs.id as booking_id', 'events.creator_id', 'event_booking_logs.presenter_user_id', 'events.subcategory_id', 'events.name', 'events.location_type', 'events.capacity', 'events.duration', DB::raw('concat("<p>",events.description,"</p>",IFNULL(event_booking_logs.notes, "")) as description'), 'event_booking_logs.booking_date', 'event_booking_logs.start_time', 'events.created_at', 'event_booking_logs.meta', \DB::raw("CONVERT_TZ(CONCAT(event_booking_logs.booking_date, ' ', event_booking_logs.start_time), '{$appTimezone}', '{$timezone}') AS event_date_time"))
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
            } elseif ($type == 'registered') {
                $checkAlreadyRegisterd = $user->registeredEvents()->get()->pluck('event_booking_log_id')->toArray();
                $eventRecords->whereNotIn('event_booking_logs.id', $checkAlreadyRegisterd);
            } elseif ($type == 'upcoming') {
                $eventRecords
                    ->leftJoin('event_registered_users_logs', function ($join) use ($user) {
                        $join
                            ->on('event_registered_users_logs.event_booking_log_id', '=', 'event_booking_logs.id')
                            ->where('event_registered_users_logs.user_id', '=', $user->id);
                    })
                    ->addSelect('event_registered_users_logs.is_cancelled')
                    ->where(function ($where) {
                        $where
                            ->whereNull('event_registered_users_logs.is_cancelled')
                            ->orWhere('event_registered_users_logs.is_cancelled', 0);
                    });
            }

            // where condition to get an events based on passed month in request
            if (!empty($request->month)) {
                $fromdate = Carbon::createFromFormat('m-Y', $request->month, $timezone)->startOfMonth()
                    ->setTimezone($appTimezone)->toDateTimeString();
                $todate = Carbon::createFromFormat('m-Y', $request->month, $timezone)->endOfMonth()
                    ->setTimezone($appTimezone)->toDateTimeString();
                $eventRecords
                    ->whereRaw("TIMESTAMP(CONCAT(event_booking_logs.booking_date, ' ', event_booking_logs.start_time)) BETWEEN '{$fromdate}' AND '{$todate}'");
            }

            $eventRecords = $eventRecords
                ->where('event_booking_logs.status', '4')
                ->where('events.status', '2')
                ->groupBy('event_booking_logs.id')
                ->orderBy('event_date_time')
                ->paginate(config('zevolifesettings.datatable.pagination.short'));

            if ($eventRecords->count() > 0) {
                return $this->successResponse(new EventListCollection($eventRecords), 'Events Retrieved Successfully');
            } else {
                return $this->successResponse(['data' => []], 'No results');
            }
        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse(trans('api_labels.common.something_wrong_try_again'));
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
            $appTimezone = config('app.timezone');
            $now         = now($appTimezone)->toDateTimeString();
            $user        = $this->user();
            $company     = $user->company()->select('companies.id')->first();

            // check booked event is registered for logged in user's company
            if ($company->id != $eventbookinglogs->company_id) {
                return $this->notFoundResponse('Event not found');
            }

            // check if event is cancelled or completed then prevent user to open details
            $skipValidation = !(isset($request->type) && $request->type == "csat");
            if ($eventbookinglogs->status != '4' && $skipValidation) {
                return $this->notFoundResponse('Event is no longer available');
            }

            if ($skipValidation) {
                $checkAlreadyRegisterd = $eventbookinglogs->users()
                    ->select('event_registered_users_logs.id', 'event_registered_users_logs.is_cancelled')
                    ->where('user_id', $user->id)
                    ->first();
                if (!is_null($checkAlreadyRegisterd) && $checkAlreadyRegisterd->is_cancelled) {
                    return $this->notFoundResponse('Event is no longer available');
                }
            }

            $eventRecords = Event::select('events.id', 'event_booking_logs.id as booking_id', 'events.creator_id', 'event_booking_logs.presenter_user_id', 'event_booking_logs.meta', 'event_booking_logs.is_csat', 'events.subcategory_id', 'events.name', 'events.location_type', 'events.capacity', DB::raw('concat("<p>", events.description, "</p>", IFNULL(event_booking_logs.notes, "")) as description'), 'event_booking_logs.booking_date', 'event_booking_logs.start_time', 'events.created_at', 'event_booking_logs.status', 'events.duration', \DB::raw("TIMESTAMPDIFF(SECOND, ADDTIME(TIMESTAMP(CONCAT(event_booking_logs . booking_date, ' ', event_booking_logs . start_time)), events.duration), '{$now}') AS endDiff"))
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
                ->select('id', 'creator_id', 'company_id', 'name', 'capacity', 'description', 'deep_link_uri')
                ->first();

            // check weather user has been already registered
            $checkAlreadyRegisterd = $eventbookinglogs->users()
                ->select('event_registered_users_logs.id', 'event_registered_users_logs.is_cancelled')
                ->where('user_id', $user->id)
                ->first();

            if (is_null($checkAlreadyRegisterd)) {
                // Check seats are available to register
                if (!is_null($event->capacity)) {
                    $totalRegisteredUsers = $eventbookinglogs->users()
                        ->where('event_registered_users_logs.is_cancelled', false)
                        ->count('event_registered_users_logs.id');
                    if (($totalRegisteredUsers + 1) > $event->capacity) {
                        return $this->invalidResponse([
                            'seats' => ['Seats are not available for the event'],
                        ], 'Seats are not available for the event');
                    }
                }

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

                // check user record exist for this booking in event_invite_sequence_user_logs
                $sequence    = 0;
                $sequenceLog = $eventbookinglogs->inviteSequence()->select('users.id')->where('user_id', $user->id)->first();
                if (is_null($sequenceLog)) {
                    // record not exist adding
                    $eventbookinglogs->inviteSequence()->attach($user);
                    $sequence = 0;
                } else {
                    // record exist updating sequence
                    $sequence = ($sequenceLog->pivot->sequence + 1);
                    $sequenceLog->pivot->update([
                        'sequence' => $sequence,
                    ]);
                }

                $userDate = Carbon::parse("{$eventbookinglogs->booking_date} {$eventbookinglogs->start_time}", $appTimezone)
                    ->setTimezone($timezone);
                dispatch(new SendEventBookedEamilJob($email, [
                    'eventName'   => $event->name,
                    'type'        => 'user',
                    'company'     => $company->id,
                    'companyName' => '',
                    'bookingDate' => $userDate->format('M d, Y h:i A'),
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
                        'sequence'    => $sequence,
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

            if ($eventbookinglogs->status == '3') {
                return $this->notFoundResponse('Event has been cancelled already');
            }

            if ($eventbookinglogs->status == '5') {
                return $this->notFoundResponse('Event has been completed already so you can\'t cancel evnet now.');
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

                    // check user record exist for this booking in event_invite_sequence_user_logs
                    $sequence    = 1;
                    $sequenceLog = $eventbookinglogs->inviteSequence()->select('users.id')->where('user_id', $user->id)->first();
                    if (is_null($sequenceLog)) {
                        // record not exist adding
                        $eventbookinglogs->inviteSequence()->attach($user);
                        $sequence = 1;
                    } else {
                        // record exist updating sequence
                        $sequence = ($sequenceLog->pivot->sequence + 1);
                        $sequenceLog->pivot->update([
                            'sequence' => $sequence,
                        ]);
                    }

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
                            'sequence'    => $sequence,
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
                return $this->successResponse(['data' => ""], 'Your attendance at ' . $event->name . ' event has been cancelled');
            }

            return $this->notFoundResponse('Event already cancelled!!!');
        } catch (\Exception $e) {
            \DB::rollback();
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }
}
