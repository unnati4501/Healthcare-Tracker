<?php declare (strict_types = 1);

namespace App\Http\Resources\V22;

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
        $therapist    = $this->therapist()->select('id', 'first_name', 'last_name', 'timezone')->first();
        $startDate    = Carbon::parse($this->start_time, $appTimezone)->setTimezone($userTimeZone);
        $status       = $this->status;
        $currentTime  = now($appTimezone)->todatetimeString();

        if ($this->start_time >= $currentTime && $this->status != 'rescheduled' && $this->status != 'canceled') {
            $status = 'upcoming';
        } elseif ($this->start_time <= $currentTime && $this->end_time >= $currentTime && $this->status != 'rescheduled' && $this->status != 'canceled') {
            $status = 'ongoing';
        } elseif ($this->end_time <= $currentTime && $this->status != 'rescheduled' && $this->status != 'canceled') {
            $status = 'completed';
        } elseif ($this->status == 'canceled') {
            $status = 'cancelled';
        }

        $isBookedCount = $user->bookedSessions()
                ->where('end_time', '>=', $currentTime)
                ->whereNull('cancelled_at')
                ->count();

        // 'allowBook'        => ($this->status != 'canceled' ? !$this->hasUpComingSession : false),

        return [
            'id'               => $this->id,
            'name'             => $this->name,
            'therapistDetails' => [
                'id'   => $therapist->id,
                'name' => $therapist->full_name,
                'logo' => $therapist->getMediaData('logo', ['w' => 1280, 'h' => 640, 'zc' => 3]),
            ],
            'startTime'        => $this->start_time->setTimezone($userTimeZone)->toAtomString(),
            'status'           => $status,
            'allowBook'        => ($this->status != 'canceled' && $isBookedCount <= 0) ? true : false,
        ];
    }
}
