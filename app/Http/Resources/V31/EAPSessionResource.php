<?php declare (strict_types = 1);

namespace App\Http\Resources\V31;

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
        $user         = $this->user();
        $appTimezone  = config('app.timezone');
        $userTimeZone = (!empty($user->timezone) ? $user->timezone : $appTimezone);
        $startDate    = Carbon::parse($this->start_time, $appTimezone)->setTimezone($userTimeZone);
        $status       = $this->status;
        $currentTime  = now($appTimezone)->todatetimeString();
        $wsUser       = user::where('id', $this->ws_id)->first();

        if ($this->start_time >= $currentTime && $this->status != 'rescheduled' && $this->status != 'canceled') {
            $status = 'upcoming';
        } elseif ($this->start_time <= $currentTime && $this->end_time >= $currentTime && $this->status != 'rescheduled' && $this->status != 'canceled' && $this->status != 'completed') {
            $status = 'ongoing';
        } elseif (($this->end_time <= $currentTime && $this->status != 'rescheduled' && $this->status != 'canceled') || $this->status == 'completed') {
            $status = 'completed';
        } elseif ($this->status == 'canceled') {
            $status = 'cancelled';
        }

        /*$isBookedCount = $user->bookedSessions()
        ->where('end_time', '>=', $currentTime)
        ->where('status', '!=', 'rescheduled')
        ->whereNull('cancelled_at')
        ->count();*/
        $isBookedCount = $user->bookedCronofySessions()
            ->where('end_time', '>=', $currentTime)
            ->whereNull('cancelled_at')
            ->where('status', '!=', 'rescheduled')
            ->count();

        $allowBook = ($this->status != 'canceled' && $isBookedCount <= 0) ? true : false;

        return [
            'id'                         => $this->id,
            'name'                       => $this->name,
            'wellbeingSpecialistDetails' => $this->getWellbeingSpecialistData(),
            'coverImage'                 => $wsUser->getMediaData('counsellor_cover', ['w' => 1280, 'h' => 640, 'zc' => 3]),
            'startTime'                  => $startDate->toAtomString(),
            'status'                     => $status,
            'isGroup'                    => ($this->is_group) ? true : false,
            'allowBook'                  => ($this->status != 'canceled' && $isBookedCount <= 0) ? true : false,
        ];
    }
}
