<?php declare (strict_types = 1);

namespace App\Http\Controllers\API\V39;

use App\Http\Collections\V31\EventListCollection;
use App\Http\Controllers\API\V35\EventController as v35EventController;
use App\Models\Company;
use App\Models\Event;
use App\Models\User;
use App\Models\EventBookingLogs;
use App\Models\EventRegisteredUserLog;
use App\Models\Notification;
use App\Models\NotificationUser;
use App\Events\SendEventCancelledEvent;
use App\Jobs\SendEventPushNotificationJob;
use App\Jobs\SendEventBookedEamilJob;
use Carbon\Carbon;
use DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EventController extends v35EventController
{
    /**
     * Register for an event
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request, EventBookingLogs $eventbookinglogs)
    {
        try {
            $appTimezone    = config('app.timezone');
            $utcNow         = now($appTimezone);
            $user           = $this->user();
            $company        = $user->company()->select('companies.id')->first();
            $userTimezone   = $user->timezone;
            $uid            = date('Ymd') . 'T' . date('His') . '-' . rand() . '@zevo.app';

            // check booked event is registered for logged in user's company
            if ($company->id != $eventbookinglogs->company_id) {
                return $this->notFoundResponse('Event not found');
            }

            // get an event details
            $event = $eventbookinglogs->event()
                ->select('id', 'creator_id', 'company_id', 'name', 'capacity', 'description', 'deep_link_uri', 'duration')
                ->first();

            $presenterDetails = $eventbookinglogs->presenter()->select('id', 'first_name', 'last_name', 'email')->first();

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
                $meta             = [
                    "presenter"  => $presenterDetails->full_name,
                    "timezone"   => $userTimezone,
                    "uid"        => $uid,
                ];

                \DB::beginTransaction();
                // register user for an event
                $eventbookinglogs->users()->attach($user, [
                    'event_id'     => $eventbookinglogs->event_id,
                    'is_cancelled' => false,
                ]);

                $eventbookinglogs->users()->update(['meta' => $meta]);

                $startTime        = Carbon::parse("{$eventbookinglogs->booking_date} {$eventbookinglogs->start_time}", $appTimezone);
                $endTime          = Carbon::parse("{$eventbookinglogs->booking_date} {$eventbookinglogs->end_time}", $appTimezone);
                
                // Dispatch job to send email booked event email
                $email = collect([collect([$user->email])]);

                $userDate = Carbon::parse("{$eventbookinglogs->booking_date} {$eventbookinglogs->start_time}", $appTimezone)
                    ->setTimezone($userTimezone);
                $companyBrandingId = !empty($company->parent_id) ? $company->parent_id : $company->id;
                $brandingCompany   = Company::where('id', $companyBrandingId)->first();

                dispatch(new SendEventBookedEamilJob($email, [
                    'eventName'     => $event->name,
                    'type'          => 'user',
                    'company'       => $company->id,
                    'companyName'   => $brandingCompany->name,
                    'bookingDate'   => $userDate->format('M d, Y h:i A'),
                    'duration'      => $event->duration,
                    'presenterName' => (!empty($presenterDetails) ? $presenterDetails->full_name : (!empty($meta->presenter) ? $meta->presenter : 'Zevo')),
                    'emailNotes'    => (!empty($eventbookinglogs->email_notes) ? $eventbookinglogs->email_notes : null),
                    'iCalData'      => [
                        'uid'         => $uid,
                        'appName'     => config('app.name'),
                        'inviteTitle' => $event->name,
                        'description' => $event->description,
                        'timezone'    => $userTimezone,
                        'today'       => Carbon::parse($utcNow)->format('Ymd\THis\Z'),
                        'startTime'   => Carbon::parse($startTime)->format('Ymd\THis\Z'),
                        'endTime'     => Carbon::parse($endTime)->format('Ymd\THis\Z'),
                        'orgName'     => (!empty($presenterDetails) ? $presenterDetails->full_name : (!empty($meta->presenter) ? $meta->presenter : 'Zevo')),
                        'orgEamil'    => (!empty($presenterDetails) ? $presenterDetails->email : 'admin@zevo.app'),
                        'userEmail'   => $user->email,
                        'sequence'    => 0,
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
     * @param Request $request, EventBookingLogs $eventbookinglogs
     * @return \Illuminate\Http\JsonResponse
     */
    public function cancel(Request $request, EventBookingLogs $eventbookinglogs)
    {
        try {
            \DB::beginTransaction();
            $appTimezone    = config('app.timezone');
            $utcNow         = now($appTimezone);
            $user           = $this->user();
            $company        = $user->company()->select('companies.id')->first();
            $userTimezone   = $user->timezone;

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
                ->select('id', 'creator_id', 'company_id', 'name', 'deep_link_uri', 'duration')
                ->first();

            // check weather user has been already registered
            $checkAlreadyRegisterd = EventRegisteredUserLog::select('id', 'is_cancelled', 'meta')
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
                $meta = $checkAlreadyRegisterd->meta;
                $uid = (!empty($meta) ? $meta->uid : date('Ymd') . 'T' . date('His') . '-' . rand() . '@zevo.app');               

                // check if event status is already cancelled then no need to send email
                if (!$cancelledStatus) {
                    // prepare data for iCal generation
                    $presenterDetails = $eventbookinglogs->presenter()->select('id', 'first_name', 'last_name', 'email')->first();
                    $startTime        = Carbon::parse("{$eventbookinglogs->booking_date} {$eventbookinglogs->start_time}" . $appTimezone);
                    $endTime          = Carbon::parse("{$eventbookinglogs->booking_date} {$eventbookinglogs->end_time}" . $appTimezone);

                    $displayStartTime = Carbon::parse("{$eventbookinglogs->booking_date} {$eventbookinglogs->start_time}", $appTimezone)->setTimezone($userTimezone)->format('M d, Y h:i A');
                    // send event cancel email to user
                    event(new SendEventCancelledEvent($user, [
                        "subject" => "{$event->name} - Event Registration Cancelled",
                        "message" => "Hi {$user->first_name},<br/><br/> This is to notify you that your registration for {$event->name} on {$displayStartTime} has been cancelled.",
                        'iCal'    => generateiCal([
                            'uid'           => $uid,
                            'appName'       => config('app.name'),
                            'inviteTitle'   => $event->name,
                            'description'   => "Your attendance at {$event->name} event has been cancelled",
                            'duration'      => $event->duration,
                            'presenterName' => (!empty($presenterDetails) ? $presenterDetails->full_name : (!empty($meta->presenter) ? $meta->presenter : 'Zevo')),
                            'timezone'      => $userTimezone,
                            'today'         => Carbon::parse($utcNow)->format('Ymd\THis\Z'),
                            'startTime'     => Carbon::parse($startTime)->format('Ymd\THis\Z'),
                            'endTime'       => Carbon::parse($endTime)->format('Ymd\THis\Z'),
                            'userEmail'     => $user->email,
                            'orgName'       => (!empty($presenterDetails) ? $presenterDetails->full_name : (!empty($meta->presenter) ? $meta->presenter : 'Zevo')),
                            'orgEamil'      => (!empty($presenterDetails) ? $presenterDetails->email : 'admin@zevo.app'),
                            'sequence'      => 1,
                        ], 'cancelled'),
                    ]));
                }

                $checkAlreadyRegisterd->delete();

                \DB::commit();
                return $this->successResponse(['data' => ""], $event->name . ' has been removed');
            } else {
                $eventbookinglogs->users()->attach($user, [
                    'event_id'     => $eventbookinglogs->event_id,
                    'is_cancelled' => true,
                ]);
                \DB::commit();
                return $this->successResponse(['data' => ""], $event->name . ' has been removed');
            }

            return $this->notFoundResponse('Event already cancelled!!!');
        } catch (\Exception $e) {
            \DB::rollback();
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }
}
