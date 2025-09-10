<?php

namespace App\Http\Resources\V27;

use App\Http\Traits\ProvidesAuthGuardTrait;
use App\Models\PersonalChallengeUser;
use Carbon\Carbon;
use DB;
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
        $user            = $this->user();
        $currentDateTime = now($user->timezone)->toDateTimeString();
        $timeInUTC       = now(config('app.timezone'))->toDateTimeString();

        $userPivot = PersonalChallengeUser::where('user_id', $user->id)
            ->where('personal_challenge_id', $this->id);
        if (!empty($this->personalChallengeUser)) {
            $userPivot->where('id', $this->personalChallengeUser->id);
        }
        $userPivot = $userPivot->where('completed', 0)
            ->where('end_date', '>', $timeInUTC)
            ->orderBy('id', 'DESC')
            ->first();

        $timerData              = array();
        $tasks                  = [];
        $taskList               = [];
        $challengeData          = [];
        $challengeStartDate     = Carbon::now();
        $challengeEndDate       = Carbon::now();
        $durationInHumanFormat  = null;
        $isStarted              = false;
        $isJoined               = false;
        $isCompleted            = false;
        $challengeAchieved      = false;
        $progressPercent        = 0;
        $progressPercentDisplay = false;
        $completedTask          = 0;
        $totalTask              = 0;

        if (!empty($userPivot)) {
            if ($this->challenge_type == 'habit' && $userPivot->frequency_type == 'hourly') {
                $taskTime = Carbon::now()->setTimezone(config('app.timezone'))->toTimeString();

                $userTaskData = $this->personalChallengeUserTasks()
                    ->wherePivot('personal_challenge_user_id', $userPivot->id)
                    ->groupBy(DB::raw('format(date, "yyyy-MM-dd")'))
                    ->get();
            } else {
                $userTaskData = $this->personalChallengeUserTasks()
                    ->wherePivot('personal_challenge_user_id', $userPivot->id)
                    ->get();
            }

            $isJoined  = $userPivot->joined ? true : false;
            $startDate = Carbon::parse($userPivot->start_date, config('app.timezone'))->setTimezone($user->timezone)->toDateTimeString();
            $endDate   = Carbon::parse($userPivot->end_date, config('app.timezone'))->setTimezone($user->timezone)->toDateTimeString();

            if ($currentDateTime >= $startDate) {
                $isStarted = true;
            }

            if ($currentDateTime >= $endDate) {
                $isCompleted = true;
            }

            $challengeAchieved = $userPivot->is_winner ? true : false;

            if ($currentDateTime < $startDate) {
                $timerData = calculatDayHrMin($currentDateTime, $startDate);
            } else {
                $timerData = calculatDayHrMin($currentDateTime, $endDate);
            }

            $challengeStartDate = Carbon::parse($userPivot->start_date, config('app.timezone'))->setTimezone($user->timezone);
            $challengeEndDate   = Carbon::parse($userPivot->end_date, config('app.timezone'))->setTimezone($user->timezone);
            // unset($timerData['hour']);
            // unset($timerData['minute']);
            // if ($isStarted ) {
            //     // When challenge is not started change updated based on ticket ZL-4800
            //     $timerData['day'] = $timerData['day'] + 1;
            // }

            $durationInHumanFormat = "1 minute left";
            $nowInUTC              = now(config('app.timezone'));
            $diff                  = Carbon::parse("{$userPivot->start_date}", config('app.timezone'))->diffInSeconds($nowInUTC);
            if ($diff > 60) {
                $durationInHumanFormat = Carbon::parse("{$userPivot->start_date}", config('app.timezone'))->diffForHumans();
                $durationInHumanFormat = str_replace("from now", "left", $durationInHumanFormat);
            }
        }

        if ($this->challenge_type == 'routine' || $this->challenge_type == 'habit') {
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

                if ($this->challenge_type == 'habit' && $userPivot->frequency_type == 'hourly') {
                    $currentDateTime = Carbon::now()->setTimezone($user->timezone)->toDateTimeString();
                    $userTaskData->each(function ($item, $key) use (&$tasks, $user, $userPivot, &$taskList, $currentDateTime) {

                        $subItemDate          = null;
                        $tasks                = array();
                        $compareDate          = Carbon::parse($item->pivot->date)->toDateString();
                        $userTimeZoneTaskDate = Carbon::parse($item->pivot->date)->setTimezone($user->timezone)->toDateString();
                        $userTaskDataWithDate = $this->personalChallengeUserTasks()
                            ->wherePivot('personal_challenge_user_id', $userPivot->id)
                            ->whereRaw('DATE_FORMAT(DATE, "%Y-%m-%d") = ?', [$compareDate])
                            ->get();

                        $userTaskDataWithDate->each(function ($itemSub, $keySub) use (&$tasks, $user, $currentDateTime, $userTimeZoneTaskDate) {
                            $userSubItemDate = $userTimeZoneTaskDate . ' ' . $itemSub->pivot->set_time;
                            $tasks[]         = [
                                'taskId'      => $itemSub->pivot->id,
                                'taskTitle'   => $itemSub->task_name,
                                "markAllowed" => (Carbon::now()->setTimezone($user->timezone)->toDateString() == Carbon::parse($itemSub->pivot->date)->setTimezone($user->timezone)->toDateString() && $currentDateTime >= $userSubItemDate) || (Carbon::now()->subDay()->toDateString() == Carbon::parse($itemSub->pivot->date)->setTimezone($user->timezone)->toDateString()),
                                "completed"   => $itemSub->pivot->completed ? true : false,
                            ];
                        });

                        $taskList[] = [
                            'date'             => Carbon::parse($item->pivot->date)->setTimezone($user->timezone)->toDateTimeString(),
                            'allTaskCompleted' => false,
                            'isDateLocked'     => Carbon::now()->toDateTimeString() >= $item->pivot->date ? false : true,
                            'tasks'            => $tasks,
                        ];
                    });
                } elseif ($this->type == 'to-do' && $this->challenge_type != 'habit') {
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
                            "markAllowed" => (Carbon::now()->setTimezone($user->timezone)->toDateString() == Carbon::parse($item->pivot->date)->setTimezone($user->timezone)->toDateString()) || (Carbon::now()->subDay()->toDateString() == Carbon::parse($item->pivot->date)->setTimezone($user->timezone)->toDateString()),
                            "completed"   => $item->pivot->completed ? true : false,
                        ];

                        $taskList[] = [
                            'date'             => Carbon::parse($item->pivot->date)->setTimezone($user->timezone)->toDateTimeString(),
                            'allTaskCompleted' => false,
                            'isDateLocked'     => Carbon::now()->toDateTimeString() >= $item->pivot->date ? false : true,
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

                    $value['allTaskCompleted'] = $allTaskCompleted;
                    $taskList[$key]            = $value;
                }

                if ($this->challenge_type == 'habit') {
                    $progressPercentDisplay = true;
                    $userChallengeTask      = $this->personalChallengeUserTasks()
                        ->wherePivot('personal_challenge_user_id', $userPivot->id);
                    $totalTask     = $userChallengeTask->count();
                    $completedTask = $userChallengeTask->wherePivot('completed', 1)->count();
                    if ($completedTask > 0) {
                        $progressPercent = ($completedTask * 100) / $totalTask;
                    }
                    $progressPercent = (float) number_format($progressPercent, 1, '.', '');
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
        $fromTime                 = null;
        $toTime                   = null;
        $inEvery                  = null;
        $frequencyType            = 'daily';
        if ($isJoined) {
            $reminderAt               = Carbon::parse($userPivot->reminder_at, config('app.timezone'))->format('H:i');
            $challengeStartReminderAt = $challengeStartDate->toDateString() . ' ' . $reminderAt;
            $recursiveCount           = $userPivot->recursive_count;
            $fromTime                 = Carbon::parse($userPivot->from_time, config('app.timezone'))->format('H:i');
            $toTime                   = Carbon::parse($userPivot->to_time, config('app.timezone'))->format('H:i');
            $inEvery                  = $userPivot->in_every;
            $frequencyType            = $userPivot->frequency_type;
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
            'frequencyType'         => $this->when($isJoined , $frequencyType),
            'fromTime'              => $this->when($isJoined  && $this->challenge_type == 'habit' && $frequencyType == 'hourly', $fromTime),
            'toTime'                => $this->when($isJoined  && $this->challenge_type == 'habit' && $frequencyType == 'hourly', $toTime),
            'inEvery'               => $this->when($isJoined  && $this->challenge_type == 'habit' && $frequencyType == 'hourly', $inEvery),
            'taskList'              => $this->when(!empty($taskList), $taskList),
            'challengeData'         => $this->when(!empty($challengeData), $challengeData),
            'progressPercent'       => $this->when($progressPercentDisplay, $progressPercent),
        ];

        return $returnArray;
    }
}
