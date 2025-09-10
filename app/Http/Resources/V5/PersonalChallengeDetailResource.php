<?php

namespace App\Http\Resources\V5;

use App\Http\Traits\ProvidesAuthGuardTrait;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class PersonalChallengeDetailResource extends JsonResource
{
    use ProvidesAuthGuardTrait;

    protected $personalChallengeUser;

    /**
     * Create a new resource instance.
     *
     * @param  mixed  $resource
     * @return void
     */
    public function __construct($resource, $personalChallengeUser)
    {
        $this->personalChallengeUser = $personalChallengeUser;

        parent::__construct($resource);
    }

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
            ->where('personal_challenge_users.id', $this->personalChallengeUser->id)
            ->where('user_id', $user->id)
            ->where('joined', 1)
            ->orderBy('id', 'DESC')
            ->first();

        $taskData = $this->personalChallengeTasks()->get();

        $timerData         = array();
        $taskList          = [];
        $isStarted         = false;
        $isJoined          = false;
        $isCompleted       = false;
        $challengeAchieved = false;

        $taskData->each(function ($item, $key) use (&$taskList) {
            $taskList[] = [
                'taskId'      => $item->id,
                'taskTitle'   => $item->task_name,
                "markAllowed" => false,
                "completed"   => false,
            ];
        });

        if (!empty($userData)) {
            $userTaskData = $this->personalChallengeUserTasks()
                ->wherePivot('personal_challenge_user_id', $userData->pivot->id)
                ->get();

            $isJoined = $userData->pivot->joined ? true : false;

            $currentDateTime = now($user->timezone)->toDateTimeString();
            $startDate       = Carbon::parse($userData->pivot->start_date, config('app.timezone'))->setTimezone($user->timezone)->toDateTimeString();
            $endDate         = Carbon::parse($userData->pivot->end_date, config('app.timezone'))->setTimezone($user->timezone)->toDateTimeString();

            if ($currentDateTime >= $startDate) {
                $isStarted = true;
            }

            if ($currentDateTime >= $endDate) {
                $isCompleted = true;
            }

            $challengeAchieved = $userData->pivot->is_winner ? true : false;

            if ($currentDateTime < $startDate) {
                $timerData = calculatDayHrMin($currentDateTime, $startDate);
            } else {
                $timerData = calculatDayHrMin($currentDateTime, $endDate);
            }

            unset($taskList);
            $taskList = [];
            $userTaskData->each(function ($item, $key) use (&$taskList, $user) {
                $taskList[] = [
                    'taskId'      => $item->id,
                    'taskTitle'   => $item->task_name,
                    "markAllowed" => Carbon::now()->setTimezone($user->timezone)->toDateString() == Carbon::parse($item->pivot->date)->toDateString(),
                    "date"        => $item->pivot->date,
                    "completed"   => $item->pivot->completed ? true : false,
                ];
            });
        }

        if ($this->type == 'to-do') {
            foreach ($taskList as $key => $subArr) {
                unset($subArr['date']);
                $subArr['markAllowed'] = true;
                $taskList[$key]        = $subArr;
            }
        }

        $returnArray = [
            'challengeId'       => $this->id,
            'title'             => $this->title,
            'description'       => $this->description,
            'taskList'          => $taskList,
            'image'             => $this->getMediaData('logo', ['w' => 1280, 'h' => 640]),
            'type'              => ucfirst($this->type),
            'isJoined'          => $isJoined,
            'isStarted'         => $isStarted,
            'isStarted'         => $isStarted,
            'isCompleted'       => $isCompleted,
            'challengeAchieved' => $challengeAchieved,
            'duration'          => (int) $this->duration,
            'creator'           => $this->getCreatorData(),
        ];

        if ($isJoined) {
            $returnArray['challengeStartDate'] = Carbon::parse($userData->pivot->start_date, config('app.timezone'))->setTimezone($user->timezone)->toAtomString();
            $returnArray['challengeEndDate']   = Carbon::parse($userData->pivot->end_date, config('app.timezone'))->setTimezone($user->timezone)->toAtomString();
            unset($timerData['hour']);
            unset($timerData['minute']);
            $timerData['day']         = $timerData['day'] + 1;
            $returnArray['timerData'] = $timerData;
        }

        return $returnArray;
    }
}
