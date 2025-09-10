<?php declare (strict_types = 1);

namespace App\Http\Controllers\API\V26;

use App\Events\SendEventCancelledEvent;
use App\Http\Controllers\API\V25\EventController as v25EventController;
use App\Models\Company;
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

class EventController extends v25EventController
{
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
                ->select('id', 'creator_id', 'company_id', 'name', 'deep_link_uri', 'duration')
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

                    $displayStartTime = Carbon::parse($startTime)->format('M d, Y h:i A');

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
                            'timezone'      => $appTimezone,
                            'today'         => $utcNow->format('Ymd\THis\Z'),
                            'startTime'     => $startTime->format('Ymd\THis\Z'),
                            'endTime'       => $endTime->format('Ymd\THis\Z'),
                            'orgName'       => (!empty($presenterDetails) ? $presenterDetails->full_name : (!empty($meta->presenter) ? $meta->presenter : 'Zevo')),
                            'orgEamil'      => (!empty($presenterDetails) ? $presenterDetails->email : 'admin@zevo.app'),
                            'sequence'      => $sequence,
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
