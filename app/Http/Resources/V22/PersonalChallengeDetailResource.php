<?php

namespace App\Http\Resources\V22;

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

        $timerData             = array();
        $tasks                 = [];
        $taskList              = [];
        $challengeData         = [];
        $challengeStartDate    = Carbon::now();
        $challengeEndDate      = Carbon::now();
        $durationInHumanFormat = null;
        $isStarted             = false;
        $isJoined              = false;
        $isCompleted           = false;
        $challengeAchieved     = false;

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

            $challengeStartDate = Carbon::parse($userData->pivot->start_date, config('app.timezone'))->setTimezone($user->timezone);
            $challengeEndDate   = Carbon::parse($userData->pivot->end_date, config('app.timezone'))->setTimezone($user->timezone);
            unset($timerData['hour']);
            unset($timerData['minute']);
            $timerData['day'] = $timerData['day'] + 1;

            $durationInHumanFormat = "1 minute left";
            $nowInUTC              = now(config('app.timezone'));
            $diff                  = Carbon::parse("{$userData->pivot->start_date}", config('app.timezone'))->diffInSeconds($nowInUTC);
            if ($diff > 60) {
                $durationInHumanFormat = Carbon::parse("{$userData->pivot->start_date}", config('app.timezone'))->diffForHumans();
                $durationInHumanFormat = str_replace("from now", "left", $durationInHumanFormat);
            }
        }

        if ($this->challenge_type == 'routine') {
            $taskData = $this->personalChallengeTasks()->get();

            $taskData->each(function ($item, $key) use (&$tasks) {
                $tasks[] = [
                    'taskId'      => $item->id,
                    'taskTitle'   => $item->task_name,
                    "markAllowed" => false,
                    "completed"   => false,
                ];
            });

            $taskList[] = [
                'date'             => Carbon::today()->toDateTimeString(),
                'allTaskCompleted' => false,
                'isDateLocked'     => false,
                'tasks'            => $tasks,
            ];

            if ($isJoined) {
                unset($tasks);
                unset($taskList);
                $tasks    = [];
                $taskList = [];

                if ($this->type == 'to-do') {
                    $userTaskData->each(function ($item, $key) use (&$tasks, $user) {
                        $tasks[] = [
                            'taskId'      => $item->pivot->id,
                            'taskTitle'   => $item->task_name,
                            "markAllowed" => true,
                            "completed"   => $item->pivot->completed ? true : false,
                        ];
                    });

                    $taskList[] = [
                        'date'             => Carbon::today()->toDateTimeString(),
                        'allTaskCompleted' => false,
                        'isDateLocked'     => false,
                        'tasks'            => $tasks,
                    ];
                } else {
                    $userTaskData->each(function ($item, $key) use (&$taskList, $user) {
                        unset($tasks);
                        $tasks   = [];
                        $tasks[] = [
                            'taskId'      => $item->pivot->id,
                            'taskTitle'   => $item->task_name,
                            "markAllowed" => (Carbon::now()->setTimezone($user->timezone)->toDateString() == Carbon::parse($item->pivot->date)->toDateString()) || (Carbon::now()->setTimezone($user->timezone)->subDay()->toDateString() == Carbon::parse($item->pivot->date)->toDateString()),
                            "completed"   => $item->pivot->completed ? true : false,
                        ];

                        $taskList[] = [
                            'date'             => $item->pivot->date,
                            'allTaskCompleted' => false,
                            'isDateLocked'     => Carbon::today()->toDateTimeString() >= $item->pivot->date ? false : true,
                            'tasks'            => $tasks,
                        ];
                    });
                }

                foreach ($taskList as $key => $value) {
                    $allTaskCompleted = false;
                    foreach ($value['tasks'] as $k => $val) {
                        if (!$val['completed']) {
                            $allTaskCompleted = false;
                            break;
                        }
                        $allTaskCompleted = true;
                    }
                }

                foreach ($taskList as $key => $value) {
                    $value['allTaskCompleted'] = $allTaskCompleted;
                    $taskList[$key]            = $value;
                }
            }
        }

        if ($this->challenge_type == 'challenge') {
            $target          = $this->target_value;
            $completedTarget = 0;
            $dataPoints      = [];

            if ($isJoined) {
                $daysRange = \createDateRange($challengeStartDate, $challengeEndDate->subSecond());
                $challengeEndDate->addSecond();
                foreach ($daysRange as $inner => $day) {
                    $dataTarget['key'] = $day->format('d M');
                    if ($this->type == 'steps') {
                        $dataTarget['value'] = (int) $user->getSteps($day->toDateString(), config('app.timezone'), $user->timezone);
                    } elseif ($this->type == 'distance') {
                        $dataTarget['value'] = (int) $user->getDistance($day->toDateString(), config('app.timezone'), $user->timezone);
                    } else {
                        $dataTarget['value'] = (int) $user->getMeditation($day->toDateString(), config('app.timezone'), $user->timezone);
                    }
                    $completedTarget += $dataTarget['value'];
                    $dataPoints[] = $dataTarget;
                }
            }

            $challengeData = [
                'target'          => $target,
                'completedTarget' => $completedTarget,
                'dataPoints'      => $dataPoints,
            ];
        }

        $challengeStartReminderAt = Carbon::parse(now());
        $recursiveCount           = 0;
        if ($isJoined) {
            $reminderAt               = Carbon::parse($userData->pivot->reminder_at, config('app.timezone'))->format('h:i');
            $challengeStartReminderAt = $challengeStartDate->toDateString() . ' ' . $reminderAt;
            $recursiveCount           = $userData->pivot->recursive_count;
        }

        $returnArray = [
            'challengeId'           => $this->id,
            'title'                 => $this->title,
            'description'           => $this->description,
            'image'                 => $this->getMediaData('logo', ['w' => 1280, 'h' => 640, 'zc' => 3]),
            'type'                  => ucfirst($this->challenge_type),
            'subType'               => ucfirst($this->type),
            'isJoined'              => $isJoined,
            'isStarted'             => $isStarted,
            'isCompleted'           => $isCompleted,
            'isRecursive'           => $this->recursive ? true : false,
            'challengeAchieved'     => $challengeAchieved,
            'duration'              => (int) $this->duration,
            'creator'               => $this->getCreatorData(),
            'recursiveCount'        => $this->when($isJoined , $recursiveCount),
            'timerData'             => $this->when($isJoined , $timerData),
            'challengeStartDate'    => $this->when($isJoined , $challengeStartDate->toAtomString()),
            'challengeEndDate'      => $this->when($isJoined , $challengeEndDate->toAtomString()),
            'challengeReminderAt'   => $this->when($isJoined , $challengeStartReminderAt),
            'durationInHumanFormat' => $this->when($isJoined , $durationInHumanFormat),
            'taskList'              => $this->when(!empty($taskList), $taskList),
            'challengeData'         => $this->when(!empty($challengeData), $challengeData),
        ];

        return $returnArray;
    }
}
