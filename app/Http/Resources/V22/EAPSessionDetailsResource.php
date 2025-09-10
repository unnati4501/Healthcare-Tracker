<?php declare (strict_types = 1);

namespace App\Http\Resources\V22;

use App\Http\Traits\ProvidesAuthGuardTrait;
use App\Models\user;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class EAPSessionDetailsResource extends JsonResource
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

        $user                = $this->user();
        $appTimezone         = config('app.timezone');
        $userTimeZone        = (!empty($user->timezone) ? $user->timezone : $appTimezone);
        $therapist           = $this->therapist()->select('id', 'first_name', 'last_name', 'timezone')->first();
        $startDate           = Carbon::parse($this->start_time)->setTimezone($userTimeZone);
        $endDate             = Carbon::parse($this->end_time)->setTimezone($userTimeZone);
        $diff                = $startDate->diffInMinutes($endDate);
        $startTime           = $startDate->format('h:ia');
        $endTime             = $endDate->format('h:ia');
        $day                 = $startDate->format('D, M d, Y');
        $sessionDurationTime = $startTime . ' - ' . $endTime . ' ' . $day;
        $currentTime         = now($appTimezone)->todatetimeString();
        $status              = $this->status;
        $bookAgainUrl        = "";

        if ($this->start_time >= $currentTime && $this->status != 'rescheduled' && $this->status != 'canceled') {
            $status = 'upcoming';
        } elseif ($this->start_time <= $currentTime && $this->end_time >= $currentTime && $this->status != 'rescheduled' && $this->status != 'canceled') {
            $status = 'ongoing';
        } elseif ($this->end_time <= $currentTime && $this->status != 'rescheduled' && $this->status != 'canceled') {
            $status = 'completed';
        } elseif ($this->status == 'canceled') {
            $status = 'cancelled';
        }

        $cancellationDetails = [];
        if ($this->status == 'canceled') {
            $cancellationDetails = [
                'reason' => (!empty($this->cancelled_reason) ? $this->cancelled_reason : ""),
                'at'     => $this->cancelled_at->setTimezone($userTimeZone)->format('g:ia, l, F j, Y'),
            ];
        }

        $allowBook = ($this->status != 'canceled' ? !$this->hasUpComingSession : false);
        if ($allowBook ) {
            // find the last booked session of the current session's therapist and logged in user and grab TherapistCalendlyHandle to book this session again
            $lastBookedSession = $user->myZdTickets()
                ->select('id', 'custom_fields')
                ->where('therapist_id', $therapist->id)
                ->orderByDesc('id')
                ->first();
            if (!empty($lastBookedSession)) {
                $bookAgainUrl = ((!empty($lastBookedSession->custom_fields) && isset($lastBookedSession->custom_fields->TherapistCalendlyHandle)) ? $lastBookedSession->custom_fields->TherapistCalendlyHandle : "");
            }
        }

        return [
            'id'                  => $this->id,
            'name'                => $this->name,
            'therapistDetails'    => [
                'id'   => $therapist->id,
                'name' => $therapist->full_name,
                'logo' => $therapist->getMediaData('logo', ['w' => 512, 'h' => 512, 'zc' => 3]),
            ],
            'notes'               => $this->notes,
            'duration'            => $diff . ' min',
            'sessionDurationTime' => $sessionDurationTime,
            'timezone'            => $therapist->timezone,
            'startTime'           => $this->start_time->setTimezone($userTimeZone)->toAtomString(),
            'endTime'             => $this->end_time->setTimezone($userTimeZone)->toAtomString(),
            'eventIdentifier'     => $this->event_identifier,
            'location'            => $this->location,
            'cancelUrl'           => $this->cancel_url,
            'rescheduleUrl'       => $this->reschedule_url,
            'status'              => $status,
            'allowBook'           => $allowBook,
            'bookAgainUrl'        => $this->when(($allowBook ), $bookAgainUrl),
            'cancellationDetails' => $this->when(($this->status == 'canceled'), $cancellationDetails),
        ];
    }
}
