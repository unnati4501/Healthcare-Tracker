<?php declare (strict_types = 1);

namespace App\Http\Resources\V21;

use App\Http\Traits\ProvidesAuthGuardTrait;
use App\Models\user;
use Illuminate\Http\Resources\Json\JsonResource;

class CounsellorResource extends JsonResource
{
    use ProvidesAuthGuardTrait;

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $user = auth()->user();

        $w          = 800;
        $h          = 800;
        $eapTickets = $this;
        if (!empty($eapTickets)) {
            $userDetailsArray      = [];
            $therapistDetailsArray = [];
            $userDetails           = user::where('id', $eapTickets->user_id)->select('id', 'first_name', 'last_name')->first();
            if (!empty($userDetails)) {
                $userLogo         = $userDetails->getMediaData('logo', ['w' => $w, 'h' => $h, 'zc' => 3]);
                $userDetailsArray = [
                    'id'   => $userDetails->id,
                    'name' => $userDetails->first_name . ' ' . $userDetails->last_name,
                    'logo' => $userLogo,
                ];
            }

            $therapistDetails = user::where('id', $eapTickets->therapist_id)->select('id', 'first_name', 'last_name')->first();
            if (!empty($therapistDetails)) {
                $therapistLogo         = $therapistDetails->getMediaData('logo', ['w' => $w, 'h' => $h, 'zc' => 3]);
                $therapistDetailsArray = [
                    'id'   => $therapistDetails->id,
                    'name' => $therapistDetails->first_name . ' ' . $therapistDetails->last_name,
                    'logo' => $therapistLogo,
                ];
            }

            $appTimezone   = config('app.timezone');
            $userTimeZone  = (!empty($user->timezone) ? $user->timezone : $appTimezone);
            $currentTime   = now($appTimezone)->todatetimeString();
            $isBookedCount = $user->bookedSessions()
                ->where('end_time', '>=', $currentTime)
                ->where('status', '!=', 'completed')
                ->whereNull('cancelled_at')
                ->count();

            $fullName            = $user->first_name . ' ' . $user->last_name;
            $fullName            = preg_replace('/[^A-Za-z0-9\-]/', '%20', $fullName);
            $therapistBookingUrl = $eapTickets->custom_fields->TherapistCalendlyHandle . '?name=' . $fullName . '&email=' . $user->email;

            return [
                'id'                      => $eapTickets->id,
                'ticketId'                => $eapTickets->ticket_id,
                'userDetails'             => $userDetailsArray,
                'therapistDetails'        => $therapistDetailsArray,
                "summary"                 => (array_key_exists('Summary', $eapTickets->custom_fields)) ? $eapTickets->custom_fields->Summary : '',
                "reasonOfChat"            => (array_key_exists('ReasonOfChat', $eapTickets->custom_fields)) ? $eapTickets->custom_fields->ReasonOfChat : '',
                "prefForTherapist"        => (array_key_exists('PrefForTherapist', $eapTickets->custom_fields)) ? $eapTickets->custom_fields->PrefForTherapist : '',
                "therapistCalendlyHandle" => $therapistBookingUrl,
                'status'                  => $eapTickets->status,
                'isSessionBooked'         => (($isBookedCount > 0) ? true : false),
            ];
        }
    }
}
