<?php declare (strict_types = 1);

namespace App\Http\Resources\V31;

use App\Http\Traits\ProvidesAuthGuardTrait;
use App\Models\user;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class AppoitmentSessionListResource extends JsonResource
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
        $user           = $this->user();
        $appTimezone    = config('app.timezone');
        $company        = $user->company()->select('companies.id')->first();
        $digitalTherapy = $company->digitalTherapy()->first();
        $userTimeZone   = (!empty($user->timezone) ? $user->timezone : $appTimezone);
        $currentTime    = now($appTimezone)->todatetimeString();
        $startDate      = Carbon::parse($this->start_time, $appTimezone)->setTimezone($userTimeZone);
        $wsUser         = user::where('id', $this->ws_id)->first();
        $sessionUpdate  = config('cronofy.sessionUpdate');

        if ($this->start_time >= $currentTime && $this->status != 'rescheduled' && $this->status != 'canceled' && $this->status != 'short_canceled') {
            $status = 'upcoming';
        } elseif ($this->start_time <= $currentTime && $this->end_time >= $currentTime && $this->status != 'rescheduled' && $this->status != 'open' && $this->status != 'canceled' && $this->status != 'short_canceled' && $this->status != 'completed') {
            $status = 'ongoing';
        } elseif (($this->end_time <= $currentTime && $this->status != 'open' && $this->status != 'rescheduled' && $this->status != 'canceled' && $this->status != 'short_canceled') || $this->status == 'completed') {
            $status = 'completed';
        } elseif ($this->status == 'canceled' || $this->status == 'short_canceled') {
            $status = 'cancelled';
        }

        if (!empty($digitalTherapy)) {
            $sessionUpdate    = $digitalTherapy->dt_session_update;
        }
        $sessionUpdate = $sessionUpdate * 3600;

        if ($this->is_group) {
            if (!empty($this->is_cancelled) && $this->is_cancelled) {
                $status = 'cancelled';
            }
        }

        $isBookedCount = $user->bookedCronofySessions()
            ->where('end_time', '>=', $currentTime)
            ->whereNull('cancelled_at')
            ->where('status', '!=', 'rescheduled')
            ->count();

        return [
            'id'                         => $this->id,
            'name'                       => $this->name,
            'wellbeingSpecialistDetails' => $this->getWellbeingSpecialistData(),
            'startTime'                  => $startDate->toAtomString(),
            'coverImage'                 => $wsUser->getMediaData('counsellor_cover', ['w' => 1280, 'h' => 640, 'zc' => 3]),
            'allowBook'                  => (($this->status != 'canceled' || $this->status != 'short_canceled') && $isBookedCount <= 0) ? true : false,
            'isGroup'                    => ($this->is_group) ? true : false,
            'sessionUpdate'              => $sessionUpdate,
            'participants'               => $this->participants,
            'status'                     => $status,
        ];
    }
}
