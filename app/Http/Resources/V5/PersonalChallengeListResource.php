<?php

namespace App\Http\Resources\V5;

use App\Http\Traits\ProvidesAuthGuardTrait;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class PersonalChallengeListResource extends JsonResource
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

        $userData = $this->personalChallengeUsers()
            ->where('user_id', $user->id)
            ->where('joined', 1)
            ->where('completed', 0)
            ->orderBy('id', 'DESC')
            ->first();

        $timerData = array();
        $isStarted = false;
        $isJoined  = false;

        if (!empty($userData)) {
            $isJoined = $userData->pivot->joined && !$userData->pivot->completed ? true : false;

            $currentDateTime = now($user->timezone)->toDateTimeString();
            $startDate       = Carbon::parse($userData->pivot->start_date, config('app.timezone'))->setTimezone($user->timezone)->toDateTimeString();
            $endDate         = Carbon::parse($userData->pivot->end_date, config('app.timezone'))->setTimezone($user->timezone)->toDateTimeString();

            if ($currentDateTime >= $startDate) {
                $isStarted = true;
            }

            if ($currentDateTime < $startDate) {
                $timerData = calculatDayHrMin($currentDateTime, $startDate);
            } else {
                $timerData = calculatDayHrMin($currentDateTime, $endDate);
            }
        }

        $returnArray = [
            'challengeId' => $this->id,
            'title'       => $this->title,
            'image'       => $this->getMediaData('logo', ['w' => 1280, 'h' => 640, 'zc' => 3]),
            'type'        => ucfirst($this->type),
            'isJoined'    => $isJoined,
            'isStarted'   => $isStarted,
            'duration'    => (int) $this->duration,
            'creator'     => $this->getCreatorData(),
        ];

        if ($isJoined) {
            unset($timerData['hour']);
            unset($timerData['minute']);
            $timerData['day']         = $timerData['day'] + 1;
            $returnArray['timerData'] = $timerData;
            $returnArray['mappingId'] = $userData->pivot->id;
        }

        return $returnArray;
    }
}
