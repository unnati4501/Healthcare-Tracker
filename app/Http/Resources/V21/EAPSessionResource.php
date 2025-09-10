<?php declare (strict_types = 1);

namespace App\Http\Resources\V21;

use App\Http\Traits\ProvidesAuthGuardTrait;
use App\Models\user;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class EAPSessionResource extends JsonResource
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
        $w                     = 1280;
        $h                     = 640;
        $therapistDetailsArray = [];
        $user                  = $this->user();
        $appTimezone           = config('app.timezone');
        $userTimeZone          = (!empty($user->timezone) ? $user->timezone : $appTimezone);
        $therapistDetails      = user::where('id', $this->therapist_id)->select('id', 'first_name', 'last_name', 'timezone')->first();
        if (!empty($therapistDetails)) {
            $therapistLogo         = $therapistDetails->getMediaData('logo', ['w' => $w, 'h' => $h, 'zc' => 3]);
            $therapistDetailsArray = [
                'id'   => $therapistDetails->id,
                'name' => $therapistDetails->first_name . ' ' . $therapistDetails->last_name,
                'logo' => $therapistLogo,
            ];
        }

        $startDate           = Carbon::parse($this->start_time)->setTimezone($userTimeZone);
        $endDate             = Carbon::parse($this->end_time)->setTimezone($userTimeZone);
        $diff                = $startDate->diffInMinutes($endDate);
        $startTime           = $startDate->format('h:ia');
        $endTime             = $endDate->format('h:ia');
        $day                 = $startDate->format('D, M d, Y');
        $sessionDurationTime = $startTime . ' - ' . $endTime . ' ' . $day;

        $currentTime = now($appTimezone)->todatetimeString();
        if ($this->start_time >= $currentTime && $this->status != 'rescheduled' && $this->status != 'canceled') {
            $status = 'upcoming';
        } elseif ($this->start_time <= $currentTime && $this->end_time >= $currentTime && $this->status != 'rescheduled' && $this->status != 'canceled') {
            $status = 'ongoing';
        } elseif ($this->end_time <= $currentTime && $this->status != 'rescheduled' && $this->status != 'canceled') {
            $status = 'completed';
        } else {
            $status = $this->status;
        }

        return [
            'id'                  => $this->id,
            'name'                => $this->name,
            'therapistDetails'    => $therapistDetailsArray,
            'notes'               => $this->notes,
            'duration'            => $diff . ' min',
            'sessionDurationTime' => $sessionDurationTime,
            'timezone'            => $therapistDetails->timezone,
            'startTime'           => $this->start_time->setTimezone($userTimeZone)->toAtomString(),
            'endTime'             => $this->end_time->setTimezone($userTimeZone)->toAtomString(),
            'eventIdentifier'     => $this->event_identifier,
            'location'            => $this->location,
            'cancelUrl'           => $this->cancel_url,
            'rescheduleUrl'       => $this->reschedule_url,
            'status'              => $status,
        ];
    }
}
