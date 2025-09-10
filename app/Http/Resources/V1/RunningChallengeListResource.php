<?php

namespace App\Http\Resources\V1;

use App\Http\Traits\ProvidesAuthGuardTrait;
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class RunningChallengeListResource extends JsonResource
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
        /** @var User $user */
        $user = $this->user();

        $loginUserData = $this->members()->wherePivot("status", "Accepted")->wherePivot("challenge_id", $this->id)->wherePivot("user_id", $user->getKey())->first();

        $challengeBadge = $this->challengeBadges()->wherePivot("challenge_id", $this->id)->first();
        $isStarted = false;
        $timerData = array();
        $currentDateTime = now($user->timezone)->toDateTimeString();
        $startDate = Carbon::parse($this->start_date, config('app.timezone'))->setTimezone($user->timezone)->toDateTimeString();
        $endDate = Carbon::parse($this->end_date, config('app.timezone'))->setTimezone($user->timezone)->toDateTimeString();

        if ($currentDateTime >= $startDate) {
            $isStarted = true;
        }

        if ($currentDateTime < $startDate) {
            $timerData = calculatDayHrMin($currentDateTime, $startDate);
        } else {
            $timerData = calculatDayHrMin($currentDateTime, $endDate);
        }

        return [
            'id'  => $this->id,
            'title'  => $this->title,
            'image' => $this->getMediaData('logo', ['w' => 1280, 'h' => 640]),
            'description' => (!empty($this->description))? $this->description : "",
            'completedPercentage' => (!empty($this->completedPer) && $this->completedPer > 0)? round($this->completedPer) : 0,
            'type' => $this->challenge_type
        ];
    }
}
