<?php declare (strict_types = 1);

namespace App\Http\Controllers\API\V28;

use App\Http\Controllers\API\V26\EventController as v26EventController;
use App\Jobs\SendEventBookedEamilJob;
use App\Jobs\SendEventPushNotificationJob;
use App\Models\Company;
use App\Models\Event;
use App\Models\EventBookingLogs;
use App\Models\User;
use Carbon\Carbon;
use DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EventController extends v26EventController
{
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
                ->select('id', 'creator_id', 'company_id', 'name', 'capacity', 'description', 'deep_link_uri', 'duration')
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
}
