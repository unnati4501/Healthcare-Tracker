<?php declare (strict_types = 1);

namespace App\Http\Controllers\API\V27;

use App\Http\Controllers\API\V26\PersonalChallengeController as v26PersonalChallengeController;
use App\Http\Requests\Api\V3\JoinPersonalChallengeRequest;
use App\Http\Requests\Api\V21\CreatePersonalChallengeRequest;
use App\Http\Requests\Api\V21\UpdatePersonalChallengeRequest;
use App\Http\Resources\V27\PersonalChallengeDetailResource;
use App\Http\Resources\V27\PersonalChallengeHistoryDetailResource;
use App\Http\Traits\ProvidesAuthGuardTrait;
use App\Http\Traits\ServesApiTrait;
use App\Models\PersonalChallenge;
use App\Models\PersonalChallengeUser;
use App\Models\PersonalChallengeUserTask;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PersonalChallengeController extends v26PersonalChallengeController
{
    use ServesApiTrait, ProvidesAuthGuardTrait;

    /**
     * Details of personal challenge
     *
     * @param  PersonalChallenge $personalChallenge, PersonalChallengeUser $personalChallengeUser
     * @return \Illuminate\Http\JsonResponse
     */
    public function details(PersonalChallenge $personalChallenge, PersonalChallengeUser $personalChallengeUser)
    {
        try {
            // logged-in user
            $user    = $this->user();
            $company = $user->company()->first();

            $challengeDetailData = PersonalChallenge::with(['personalChallengeUsers', 'personalChallengeTasks', 'personalChallengeUserTasks'])
                ->where('personal_challenges.id', $personalChallenge->id)
                ->select(
                    'personal_challenges.id',
                    'personal_challenges.title',
                    'personal_challenges.logo',
                    'personal_challenges.library_image_id',
                    'personal_challenges.challenge_type',
                    'personal_challenges.type',
                    'personal_challenges.target_value',
                    'personal_challenges.duration',
                    'personal_challenges.creator_id',
                    'personal_challenges.description',
                    'personal_challenges.recursive'
                )
                ->where(function ($query) use ($company) {
                    $query->where('personal_challenges.company_id', null)
                        ->orWhere('personal_challenges.company_id', $company->id);
                })
                ->first();

            if (!empty($challengeDetailData)) {
                // collect required data and return response
                return $this->successResponse(['data' => new PersonalChallengeDetailResource($challengeDetailData, $personalChallengeUser)], 'Challenge detail retrieved successfully.');
            } else {
                // return empty response
                return $this->successResponse(['data' => []], 'No results');
            }
        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * Join personal challenge
     *
     * @param JoinPersonalChallengeRequest $request, PersonalChallenge $personalChallenge
     * @return \Illuminate\Http\JsonResponse
     */
    public function joinPersonalChallenge(JoinPersonalChallengeRequest $request, PersonalChallenge $personalChallenge)
    {
        try {
            \DB::beginTransaction();

            // logged-in user
            $user    = $this->user();
            $payload = $request->all();

            $currentDateTime = now($user->timezone)->toDateTimeString();

            $currentUserData = $personalChallenge->personalChallengeUsers()
                ->where('user_id', $user->id)
                ->where('joined', 1)
                ->where('completed', 0)
                ->orderBy('id', 'DESC')
                ->first();

            if (!empty($currentUserData)) {
                return $this->notFoundResponse('Challenge already ongoing.!');
            }

            $currentChallenges = PersonalChallengeUser::where('user_id', $user->id)
                ->where('joined', 1)
                ->where('completed', 0)
                ->get();

            if (count($currentChallenges) >= 10) {
                return $this->invalidResponse([], "You have reached limit of joining maximum of 10 personal challenges at a time.");
            }

            $startDate = Carbon::parse($payload['startDate'], $user->timezone)->setTimezone(config('app.timezone'))->toDateTimeString();
            if ($personalChallenge->challenge_type == 'habit') {
                $endDate = $payload['startDate'];
                $endDate = Carbon::parse($endDate, $user->timezone)->setTimezone(config('app.timezone'))->addDays($personalChallenge->duration)->toDateString() . ' 23:59:59';
            } else {
                $endDate = Carbon::parse($payload['startDate'], $user->timezone)->setTimezone(config('app.timezone'))->addDays($personalChallenge->duration)->toDateTimeString();
            }

            $personalChallengeUserInput = [
                'personal_challenge_id' => $personalChallenge->id,
                'joined'                => 1,
                'start_date'            => $startDate,
                'end_date'              => $endDate,
                'frequency_type'        => $payload['frequencyType'],
                'reminder_at'           => ($payload['frequencyType'] == 'daily') ? $payload['reminderAt'] : null,
                'from_time'             => ($payload['frequencyType'] == 'hourly') ? $payload['fromTime'] : null,
                'to_time'               => ($payload['frequencyType'] == 'hourly') ? $payload['toTime'] : null,
                'in_every'              => ($payload['frequencyType'] == 'hourly') ? $payload['inEvery'] : null,
                'completed'             => 0,
            ];

            if (!empty($payload['isRecursive']) && $payload['isRecursive']) {
                $personalChallengeUserInput['recursive_count']     = $payload['recursiveCount'];
                $personalChallengeUserInput['recursive_completed'] = 0;
            }

            $personalChallenge->personalChallengeUsers()->attach($user->id, $personalChallengeUserInput);

            $userData = $personalChallenge->personalChallengeUsers()
                ->where('user_id', $user->id)
                ->where('joined', 1)
                ->where('completed', 0)
                ->orderBy('id', 'DESC')
                ->first();

            $personalChallengeUserTaskInput = [];

            if ($personalChallenge->challenge_type == 'habit') {
                $habitDuration = Carbon::parse($payload['startDate'], $user->timezone)->setTimezone(config('app.timezone'))->diffInDays(Carbon::parse($endDate, $user->timezone)->setTimezone(config('app.timezone')));
                $taskData      = $personalChallenge->personalChallengeTasks()->first();

                for ($i = 0; $i < $personalChallenge->duration; $i++) {
                    $date = Carbon::parse($startDate)->addDays($i)->toDateTimeString();
                    if ($date <= $endDate) {
                        if ($payload['frequencyType'] == 'hourly') {
                            $setTime = Carbon::parse($payload['fromTime'])->toTimeString();
                            $toTime  = Carbon::parse($payload['toTime'])->toTimeString();
                            do {
                                $inEvery                          = $payload['inEvery'];
                                $personalChallengeUserTaskInput[] = [
                                    'personal_challenge_id'       => $taskData->personal_challenge_id,
                                    'personal_challenge_user_id'  => $userData->pivot->id,
                                    'personal_challenge_tasks_id' => $taskData->id,
                                    'date'                        => $date,
                                    'set_time'                    => $setTime,
                                ];
                                $setTime = Carbon::parse($setTime)->addSeconds($inEvery)->toTimeString();
                            } while ($setTime <= $toTime);
                        } else {
                            $personalChallengeUserTaskInput[] = [
                                'personal_challenge_id'       => $taskData->personal_challenge_id,
                                'personal_challenge_user_id'  => $userData->pivot->id,
                                'personal_challenge_tasks_id' => $taskData->id,
                                'date'                        => $date,
                            ];
                        }
                    }
                }
            } elseif ($personalChallenge->type == 'streak') {
                $taskData = $personalChallenge->personalChallengeTasks()->first();

                for ($i = 0; $i < $personalChallenge->duration; $i++) {
                    $date                             = Carbon::parse($startDate)->addDays($i)->toDateTimeString();
                    $personalChallengeUserTaskInput[] = [
                        'personal_challenge_id'       => $taskData->personal_challenge_id,
                        'personal_challenge_user_id'  => $userData->pivot->id,
                        'personal_challenge_tasks_id' => $taskData->id,
                        'date'                        => $date,
                    ];
                }
            } else {
                $taskData = $personalChallenge->personalChallengeTasks()->get();

                $taskData->each(function ($item, $key) use (&$personalChallengeUserTaskInput, $userData) {
                    $personalChallengeUserTaskInput[] = [
                        'personal_challenge_id'       => $item->personal_challenge_id,
                        'personal_challenge_user_id'  => $userData->pivot->id,
                        'personal_challenge_tasks_id' => $item->id,
                    ];
                });
            }

            if (!empty($personalChallengeUserTaskInput)) {
                PersonalChallengeUserTask::insert($personalChallengeUserTaskInput);
            }

            if (!empty($userData)) {
                if (Carbon::now()->setTimezone($user->timezone)->toDateTimeString() < $startDate) {
                    $this->setStartReminder($personalChallenge, $startDate, $userData->pivot->id);
                }
                $endDateTz = Carbon::parse($endDate, config('app.timezone'))->setTimezone($user->timezone)->toDateTimeString();
                $this->setEndReminder($personalChallenge, $endDateTz, $userData->pivot->id);
            }

            $data = [
                'mappingId' => $userData->pivot->id,
            ];

            \DB::commit();

            return $this->successResponse(['data' => $data], trans('api_messages.challenge.start'));
        } catch (\Exception $e) {
            \DB::rollback();
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * Create personal challenge from app
     *
     * @param CreatePersonalChallengeRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(CreatePersonalChallengeRequest $request)
    {
        try {
            \DB::beginTransaction();
            // logged-in user
            $user    = $this->user();
            $payload = $request->all();

            $personalChallengeInput = [
                'creator_id'     => $user->id,
                'company_id'     => !is_null($user->company->first()) ? $user->company->first()->id : null,
                'logo'           => (array_key_exists('logo', $payload) && !empty($payload['logo'])) ? $payload['logo']->getClientOriginalName() : '',
                'title'          => $payload['name'],
                'duration'       => $payload['duration'],
                'challenge_type' => $payload['challengetype'],
                'type'           => $payload['type'],
                'description'    => $payload['description'],
                'recursive'      => ($payload['isRecursive'] == 'true') ? 1 : 0,
            ];

            if ($payload['challengetype'] == 'challenge') {
                $personalChallengeInput['target_value'] = $payload['target_value'];
            }

            $record = PersonalChallenge::create($personalChallengeInput);

            if ($record) {
                if ($payload['challengetype'] == 'routine' || $payload['challengetype'] == 'habit') {
                    $personalChallengeTaskInput = [];
                    $tasks                      = $payload['tasks'];

                    foreach ($tasks as $key => $value) {
                        $personalChallengeTaskInput[] = [
                            'personal_challenge_id' => $record->id,
                            'task_name'             => $value,
                        ];
                    }

                    $record->personalChallengeTasks()->insert($personalChallengeTaskInput);
                }

                if (isset($payload['logo']) && !empty($payload['logo'])) {
                    $name = $record->id . '_' . \time();
                    $record->clearMediaCollection('logo')
                        ->addMediaFromRequest('logo')
                        ->usingName($name)
                        ->usingFileName($name . '.' . $payload['logo']->extension())
                        ->toMediaCollection('logo', config('medialibrary.disk_name'));
                } else {
                    $record->library_image_id = $request->imageId;
                    $record->save();
                }
            }

            $currentChallenges = PersonalChallengeUser::where('user_id', $user->id)
                ->where('joined', 1)
                ->where('completed', 0)
                ->get();

            if (count($currentChallenges) >= 10) {
                return $this->invalidResponse([], "You have reached limit of joining maximum of 10 personal challenges at a time.");
            }

            $startDate = Carbon::parse($payload['startDate'], $user->timezone)->setTimezone(config('app.timezone'))->toDateTimeString();

            if ($payload['challengetype'] == 'habit') {
                $endDate = $payload['startDate'];
                $endDate = Carbon::parse($endDate, $user->timezone)->setTimezone(config('app.timezone'))->addDays($record->duration)->toDateString() . ' 23:59:59';
            } else {
                $endDate = Carbon::parse($payload['startDate'], $user->timezone)->setTimezone(config('app.timezone'))->addDays($record->duration)->toDateTimeString();
            }

            $personalChallengeUserInput = [
                'personal_challenge_id' => $record->id,
                'joined'                => 1,
                'start_date'            => $startDate,
                'end_date'              => $endDate,
                'frequency_type'        => $payload['frequencyType'],
                'reminder_at'           => ($payload['frequencyType'] == 'daily') ? $payload['reminderAt'] : null,
                'from_time'             => ($payload['frequencyType'] == 'hourly') ? $payload['fromTime'] : null,
                'to_time'               => ($payload['frequencyType'] == 'hourly') ? $payload['toTime'] : null,
                'in_every'              => ($payload['frequencyType'] == 'hourly') ? $payload['inEvery'] : null,
                'completed'             => 0,
            ];

            if (!empty($payload['isRecursive']) && $payload['isRecursive'] == "true") {
                $personalChallengeUserInput['recursive_count']     = $payload['recursiveCount'];
                $personalChallengeUserInput['recursive_completed'] = 0;
            }

            $record->personalChallengeUsers()->attach($user->id, $personalChallengeUserInput);

            $userData = $record->personalChallengeUsers()
                ->where('user_id', $user->id)
                ->where('joined', 1)
                ->where('completed', 0)
                ->orderBy('id', 'DESC')
                ->first();

            $personalChallengeUserTaskInput = [];
            if ($payload['challengetype'] == 'habit') {
                $taskData = $record->personalChallengeTasks()->first();
                for ($i = 0; $i < $record->duration; $i++) {
                    $date = Carbon::parse($startDate)->addDays($i)->toDateTimeString();
                    if ($date <= $endDate) {
                        if ($payload['frequencyType'] == 'hourly') {
                            $setTime = Carbon::parse($payload['fromTime'])->toTimeString();
                            $toTime  = Carbon::parse($payload['toTime'])->toTimeString();
                            do {
                                $inEvery                          = $payload['inEvery'];
                                $personalChallengeUserTaskInput[] = [
                                    'personal_challenge_id'       => $taskData->personal_challenge_id,
                                    'personal_challenge_user_id'  => $userData->pivot->id,
                                    'personal_challenge_tasks_id' => $taskData->id,
                                    'date'                        => $date,
                                    'set_time'                    => $setTime,
                                ];
                                $setTime = Carbon::parse($setTime)->addSeconds($inEvery)->toTimeString();
                            } while ($setTime <= $toTime);
                        } else {
                            $personalChallengeUserTaskInput[] = [
                                'personal_challenge_id'       => $taskData->personal_challenge_id,
                                'personal_challenge_user_id'  => $userData->pivot->id,
                                'personal_challenge_tasks_id' => $taskData->id,
                                'date'                        => $date,
                            ];
                        }
                    }
                }
            } elseif ($record->type == 'streak') {
                $taskData = $record->personalChallengeTasks()->first();
                for ($i = 0; $i < $record->duration; $i++) {
                    $date                             = Carbon::parse($startDate)->addDays($i)->toDateTimeString();
                    $personalChallengeUserTaskInput[] = [
                        'personal_challenge_id'       => $taskData->personal_challenge_id,
                        'personal_challenge_user_id'  => $userData->pivot->id,
                        'personal_challenge_tasks_id' => $taskData->id,
                        'date'                        => $date,
                    ];
                }
            } else {
                $taskData = $record->personalChallengeTasks()->get();

                $taskData->each(function ($item, $key) use (&$personalChallengeUserTaskInput, $userData) {
                    $personalChallengeUserTaskInput[] = [
                        'personal_challenge_id'       => $item->personal_challenge_id,
                        'personal_challenge_user_id'  => $userData->pivot->id,
                        'personal_challenge_tasks_id' => $item->id,
                    ];
                });
            }

            if (!empty($personalChallengeUserTaskInput)) {
                PersonalChallengeUserTask::insert($personalChallengeUserTaskInput);
            }

            if (!empty($userData)) {
                if (Carbon::now()->setTimezone($user->timezone)->toDateTimeString() < $startDate) {
                    $this->setStartReminder($record, $startDate, $userData->pivot->id);
                }
                $endDateTz = Carbon::parse($endDate, config('app.timezone'))->setTimezone($user->timezone)->toDateTimeString();
                $this->setEndReminder($record, $endDateTz, $userData->pivot->id);
            }

            $data = [
                'id'        => $record->id,
                'mappingId' => $userData->pivot->id,
            ];

            \DB::commit();

            return $this->successResponse(['data' => $data], trans('api_messages.personalChallenge.create'));
        } catch (\Exception $e) {
            \DB::rollback();
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * Update personal challenge from app
     *
     * @param UpdatePersonalChallengeRequest $request, PersonalChallenge $personalChallenge
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdatePersonalChallengeRequest $request, PersonalChallenge $personalChallenge)
    {
        try {
            \DB::beginTransaction();
            // logged-in user
            $user    = $this->user();
            $payload = $request->all();

            $personalChallengeInput = [
                'logo'        => (in_array('logo', $payload)) ? $payload['logo']->getClientOriginalName() : '',
                'title'       => $payload['name'],
                'duration'    => $payload['duration'],
                'description' => $payload['description'],
            ];

            if ($personalChallenge->challenge_type == 'challenge') {
                $personalChallengeInput['target_value'] = $payload['target_value'];
            }

            $record = $personalChallenge->update($personalChallengeInput);

            $userData = $personalChallenge->personalChallengeUsers()
                ->where('user_id', $user->id)
                ->where('joined', 1)
                ->where('completed', 0)
                ->orderBy('id', 'DESC')
                ->first();

            if ($record) {
                if ($personalChallenge->challenge_type == 'routine' || $payload['challengetype'] == 'habit') {
                    $oldResult = $personalChallenge->personalChallengeTasks()->where('personal_challenge_id', $personalChallenge->id)->count();
                    if ($oldResult > 0) {
                        $personalChallenge->personalChallengeTasks()->where('personal_challenge_id', $personalChallenge->id)->delete();
                    }
                    $personalChallengeTaskInput = [];
                    $tasks                      = $payload['tasks'];

                    foreach ($tasks as $key => $value) {
                        $personalChallengeTaskInput[] = [
                            'personal_challenge_id' => $personalChallenge->id,
                            'task_name'             => $value,
                        ];
                    }

                    $personalChallenge->personalChallengeTasks()->insert($personalChallengeTaskInput);
                }

                $personalChallengeUserTaskInput = [];

                if ($payload['challengetype'] == 'habit') {
                    $taskData = $personalChallenge->personalChallengeTasks()->first();
                    $endDate  = $payload['endDate'];
                    for ($i = 0; $i < $personalChallenge->duration; $i++) {
                        $date = Carbon::parse($payload['startDate'])->addDays($i)->toDateTimeString();
                        if ($date <= $endDate) {
                            if ($payload['frequencyType'] == 'hourly') {
                                $setTime = Carbon::parse($payload['fromTime'])->toTimeString();
                                $toTime  = Carbon::parse($payload['toTime'])->toTimeString();
                                do {
                                    $inEvery                          = $payload['inEvery'];
                                    $personalChallengeUserTaskInput[] = [
                                        'personal_challenge_id'       => $taskData->personal_challenge_id,
                                        'personal_challenge_user_id'  => $userData->pivot->id,
                                        'personal_challenge_tasks_id' => $taskData->id,
                                        'date'                        => $date,
                                        'set_time'                    => $setTime,
                                    ];
                                    $setTime = Carbon::parse($setTime)->addSeconds($inEvery)->toTimeString();
                                } while ($setTime <= $toTime);
                            } else {
                                $personalChallengeUserTaskInput[] = [
                                    'personal_challenge_id'       => $taskData->personal_challenge_id,
                                    'personal_challenge_user_id'  => $userData->pivot->id,
                                    'personal_challenge_tasks_id' => $taskData->id,
                                    'date'                        => $date,
                                ];
                            }
                        }
                    }
                } elseif ($payload['type'] == 'streak') {
                    $taskData = $personalChallenge->personalChallengeTasks()->first();

                    for ($i = 0; $i < $personalChallenge->duration; $i++) {
                        $startDate                        = Carbon::parse($payload['startDate'], $user->timezone)->setTimezone(config('app.timezone'))->toDateTimeString();
                        $date                             = Carbon::parse($startDate)->addDays($i)->toDateTimeString();
                        $personalChallengeUserTaskInput[] = [
                            'personal_challenge_id'       => $taskData->personal_challenge_id,
                            'personal_challenge_user_id'  => $userData->pivot->id,
                            'personal_challenge_tasks_id' => $taskData->id,
                            'date'                        => $date,
                        ];
                    }
                } else {
                    $taskData = $personalChallenge->personalChallengeTasks()->get();

                    $taskData->each(function ($item, $key) use (&$personalChallengeUserTaskInput, $userData) {
                        $personalChallengeUserTaskInput[] = [
                            'personal_challenge_id'       => $item->personal_challenge_id,
                            'personal_challenge_user_id'  => $userData->pivot->id,
                            'personal_challenge_tasks_id' => $item->id,
                        ];
                    });
                }

                if (!empty($personalChallengeUserTaskInput)) {
                    PersonalChallengeUserTask::insert($personalChallengeUserTaskInput);
                }

                if (isset($payload['logo']) && !empty($payload['logo'])) {
                    $name = $personalChallenge->id . '_' . \time();
                    $personalChallenge->clearMediaCollection('logo')
                        ->addMediaFromRequest('logo')
                        ->usingName($name)
                        ->usingFileName($name . '.' . $payload['logo']->extension())
                        ->toMediaCollection('logo', config('medialibrary.disk_name'));
                    $personalChallenge->library_image_id = null;
                    $personalChallenge->save();
                } elseif (!empty($request->imageId)) {
                    if ($personalChallenge->library_image_id != $request->imageId) {
                        $personalChallenge->clearMediaCollection('logo');
                        $personalChallenge->library_image_id = $request->imageId;
                        $personalChallenge->save();
                    }
                }
            }

            \DB::commit();

            return $this->successResponse(['data' => []], trans('api_messages.personalChallenge.update'));
        } catch (\Exception $e) {
            \DB::rollback();
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * Details of personal challenge
     *
     * @param  PersonalChallenge $personalChallenge, PersonalChallengeUser $personalChallengeUser
     * @return \Illuminate\Http\JsonResponse
     */
    public function historyDetails(PersonalChallenge $personalChallenge, PersonalChallengeUser $personalChallengeUser)
    {
        try {
            // logged-in user
            $user    = $this->user();
            $company = $user->company()->first();

            $challengeDetailData = PersonalChallenge::with(['personalChallengeUsers', 'personalChallengeTasks', 'personalChallengeUserTasks'])
                ->where('personal_challenges.id', $personalChallenge->id)
                ->select(
                    'personal_challenges.id',
                    'personal_challenges.title',
                    'personal_challenges.logo',
                    'personal_challenges.library_image_id',
                    'personal_challenges.challenge_type',
                    'personal_challenges.type',
                    'personal_challenges.target_value',
                    'personal_challenges.duration',
                    'personal_challenges.creator_id',
                    'personal_challenges.description',
                    'personal_challenges.recursive'
                )
                ->where(function ($query) use ($company) {
                    $query->where('personal_challenges.company_id', null)
                        ->orWhere('personal_challenges.company_id', $company->id);
                })
                ->first();

            if (!empty($challengeDetailData)) {
                // collect required data and return response
                return $this->successResponse(['data' => new PersonalChallengeHistoryDetailResource($challengeDetailData, $personalChallengeUser)], 'Challenge detail retrieved successfully.');
            } else {
                // return empty response
                return $this->successResponse(['data' => []], 'No results');
            }
        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * Complete tasks of personal challenge
     *
     * @param  PersonalChallenge $personalChallenge, PersonalChallengeTask $personalChallengeTask
     * @return \Illuminate\Http\JsonResponse
     */
    public function completeTasksNew(PersonalChallenge $personalChallenge, PersonalChallengeUserTask $personalChallengeUserTask)
    {
        try {
            // logged-in user
            $user                   = $this->user();
            $progressPercent        = (float) 0;
            $progressPercentDisplay = false;
            $completedTask          = 0;
            $totalTask              = 0;

            $userData = $personalChallenge->personalChallengeUsers()
                ->where('user_id', $user->id)
                ->where('joined', 1)
                ->where('completed', 0)
                ->orderBy('id', 'DESC')
                ->first();

            if (!empty($userData)) {
                if (!empty($personalChallengeUserTask)) {
                    \DB::beginTransaction();
                    $personalChallengeUserTask->update(['completed' => $personalChallengeUserTask->completed ? 0 : 1]);
                    \DB::commit();

                    if ($personalChallenge->challenge_type == 'habit') {
                        $progressPercentDisplay = true;
                        $userChallengeTask      = $personalChallenge->personalChallengeUserTasks()
                            ->wherePivot('personal_challenge_user_id', $personalChallengeUserTask->personal_challenge_user_id);
                        $totalTask     = $userChallengeTask->count();
                        $completedTask = $userChallengeTask->wherePivot('completed', 1)->count();
                        if ($completedTask > 0) {
                            $progressPercent = ($completedTask * 100) / $totalTask;
                        }
                        $progressPercent = (float) number_format($progressPercent, 1, '.', '');
                    }

                    if ($personalChallengeUserTask->completed) {
                        $markMessage = trans('api_messages.challenge.mark');
                    } else {
                        $markMessage = trans('api_messages.challenge.unmark');
                    }

                    $data['progressPercent'] = $progressPercent;

                    return $this->successResponse(['data' => $data], $markMessage);
                } else {
                    return $this->notFoundResponse('Task not found.');
                }
            } else {
                return $this->notFoundResponse('User not joined.');
            }
        } catch (\Exception $e) {
            \DB::rollback();
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }
}
