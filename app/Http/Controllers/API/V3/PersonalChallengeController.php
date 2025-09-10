<?php declare (strict_types = 1);

namespace App\Http\Controllers\API\V3;

use App\Http\Collections\V3\PersonalChallengeHistoryCollection;
use App\Http\Collections\V3\PersonalChallengeListCollection;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V3\JoinPersonalChallengeRequest;
use App\Http\Resources\V3\PersonalChallengeDetailResource;
use App\Http\Traits\ProvidesAuthGuardTrait;
use App\Http\Traits\ServesApiTrait;
use App\Models\Notification;
use App\Models\NotificationSetting;
use App\Models\PersonalChallenge;
use App\Models\PersonalChallengeTask;
use App\Models\PersonalChallengeUser;
use App\Models\PersonalChallengeUserTask;
use Auth;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PersonalChallengeController extends Controller
{
    use ServesApiTrait, ProvidesAuthGuardTrait;

    /**
     * Get personal challenge listing
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function explorePersonalChallenges(Request $request)
    {
        try {
            // logged-in user
            $user    = $this->user();
            $company = $user->company()->first();

            $exploreChallengeData = PersonalChallenge::with(['personalChallengeUsers'])
            // ->leftJoin('personal_challenge_users', 'personal_challenge_users.personal_challenge_id', '=', 'personal_challenges.id')
                ->leftJoin("personal_challenge_users", function ($join) use ($user) {
                    $join->on("personal_challenge_users.personal_challenge_id", "=", "personal_challenges.id")
                        ->where('personal_challenge_users.completed', 0)
                        ->where('personal_challenge_users.user_id', $user->id);
                })
                ->select(
                    'personal_challenges.id',
                    'personal_challenges.title',
                    'personal_challenges.logo',
                    'personal_challenges.type',
                    'personal_challenges.duration',
                    'personal_challenges.creator_id',
                    'personal_challenges.updated_at',
                    'personal_challenge_users.updated_at'
                )
                ->distinct('personal_challenges.id')
                ->where(function ($query) use ($company) {
                    $query->where('personal_challenges.company_id', null)
                        ->orWhere('personal_challenges.company_id', $company->id);
                })
            // ->orWhereHas('personalChallengeUsers', function ($query) use ($user) {
            //     $query->where('user_id', $user->id)
            //         ->orderBy('personal_challenge_users.updated_at', 'DESC');
            // })
                ->orderBy('personal_challenge_users.updated_at', 'DESC')
                ->orderBy('personal_challenges.updated_at', 'DESC')
                ->paginate(config('zevolifesettings.datatable.pagination.short'));

            if ($exploreChallengeData->count() > 0) {
                // collect required data and return response
                return $this->successResponse(new PersonalChallengeListCollection($exploreChallengeData), 'Personal Challenge Listed successfully.');
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

            if (count($currentChallenges) >= 5) {
                return $this->invalidResponse([], "You have reached limit of joining maximum of 5 personal challenges at a time.");
            }

            $startDate = Carbon::parse($payload['startDate'])->toDateTimeString();
            $endDate   = Carbon::parse($payload['startDate'])->addDays($personalChallenge->duration)->toDateTimeString();

            $personalChallengeUserInput = [
                'personal_challenge_id' => $personalChallenge->id,
                'joined'                => 1,
                'start_date'            => $startDate,
                'end_date'              => $endDate,
                'reminder_at'           => $payload['reminderAt'],
                'completed'             => 0,
            ];

            $personalChallenge->personalChallengeUsers()->attach($user->id, $personalChallengeUserInput);

            $userData = $personalChallenge->personalChallengeUsers()
                ->where('user_id', $user->id)
                ->where('joined', 1)
                ->where('completed', 0)
                ->orderBy('id', 'DESC')
                ->first();

            $personalChallengeUserTaskInput = [];
            if ($personalChallenge->type == 'streak') {
                $taskData = $personalChallenge->personalChallengeTasks()->first();

                for ($i = 0; $i < $personalChallenge->duration; $i++) {
                    $date                             = Carbon::parse($payload['startDate'])->addDays($i)->toDateTimeString();
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
                $this->setEndReminder($personalChallenge, $endDate, $userData->pivot->id);
            }

            $data = [
                'mappingId' => $userData->pivot->id,
            ];

            \DB::commit();

            return $this->successResponse(['data' => $data], 'Challenge has been joined and reminder setup successfully.');
        } catch (\Exception $e) {
            \DB::rollback();
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * Leave personal challenge
     *
     * @param  PersonalChallenge $personalChallenge
     * @return \Illuminate\Http\JsonResponse
     */
    public function leavePersonalChallenge(PersonalChallenge $personalChallenge)
    {
        try {
            // logged-in user
            $user = $this->user();

            $userData = PersonalChallengeUser::where('personal_challenge_id', $personalChallenge->id)
                ->where('user_id', $user->id)
                ->where('joined', 1)
                ->where('completed', 0)
                ->orderBy('id', 'DESC')
                ->first();

            if (!empty($userData)) {
                \DB::beginTransaction();
                if ($userData->delete()) {
                    $startTitle   = 'Personal challenge start reminder';
                    $startMessage = "Don't forget " . $personalChallenge->title . " starts tomorrow.";
                    $startDate    = Carbon::parse($userData->start_date)->subHours(24)->toDateTimeString();
                    $startTime    = \Carbon\Carbon::parse($startDate, $user->timezone)
                        ->setTimezone(config('app.timezone'))
                        ->todatetimeString();

                    $endTitle   = 'Personal challenge end reminder';
                    $endMessage = $personalChallenge->title . " ends at " . $userData->end_date . " .";
                    $endDate    = Carbon::parse($userData->end_date)->subHours(12)->toDateTimeString();
                    $endTime    = \Carbon\Carbon::parse($endDate, $user->timezone)
                        ->setTimezone(config('app.timezone'))
                        ->todatetimeString();

                    $notification = Notification::where('creator_id', $user->id)
                        ->where(function ($query) use ($startTitle, $endTitle) {
                            $query->where('title', $startTitle)
                                ->orWhere('title', $endTitle);
                        })
                        ->where(function ($query) use ($startMessage, $endMessage) {
                            $query->where('message', $startMessage)
                                ->orWhere('message', $endMessage);
                        })
                        ->where(function ($query) use ($startTime, $endTime) {
                            $query->where('scheduled_at', $startTime)
                                ->orWhere('scheduled_at', $endTime);
                        })
                        ->delete();
                }
                \DB::commit();

                return $this->successResponse([], 'Challenge left successfully.');
            } else {
                return $this->notFoundResponse('Challenge not joined.');
            }
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
                    'personal_challenges.type',
                    'personal_challenges.duration',
                    'personal_challenges.creator_id',
                    'personal_challenges.description'
                )
                ->where(function ($query) use ($company) {
                    $query->where('personal_challenges.company_id', null)
                        ->orWhere('personal_challenges.company_id', $company->id);
                })
                ->first();

            // $history = isset($request->history) ? filter_var($request->history, FILTER_VALIDATE_BOOLEAN) : false;

            if (!empty($challengeDetailData)) {
                // collect required data and return response
                return $this->successResponse(['data' => new PersonalChallengeDetailResource($challengeDetailData, $personalChallengeUser)], 'Challenge detail retrieved successfully.');
            } else {
                // return empty response
                return $this->successResponse(['data' => []], 'No results');
            }
        } catch (\Exception $e) {
            \DB::rollback();
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * History of personal challenge
     *
     * @param  none
     * @return \Illuminate\Http\JsonResponse
     */
    public function history()
    {
        try {
            // logged-in user
            $user    = $this->user();
            $company = $user->company()->first();

            $challengeHistoryData = PersonalChallenge::leftJoin('personal_challenge_users', 'personal_challenge_users.personal_challenge_id', '=', 'personal_challenges.id')
            // with(['personalChallengeUsers'])
                ->select(
                    'personal_challenges.id',
                    'personal_challenges.title',
                    'personal_challenges.logo',
                    'personal_challenges.type',
                    'personal_challenges.duration',
                    'personal_challenges.creator_id',
                    'personal_challenges.description',
                    'personal_challenge_users.id as personal_challenge_mapping_id',
                    'personal_challenge_users.user_id',
                    'personal_challenge_users.start_date',
                    'personal_challenge_users.end_date',
                    'personal_challenge_users.completed',
                    'personal_challenge_users.is_winner',
                    'personal_challenge_users.updated_at'
                )
                ->where(function ($query) use ($company) {
                    $query->where('personal_challenges.company_id', null)
                        ->orWhere('personal_challenges.company_id', $company->id);
                })
                ->where('personal_challenge_users.user_id', $user->id)
                ->where('personal_challenge_users.completed', 1)
            // ->whereHas('personalChallengeUsers', function ($query) use ($user) {
            //     $query->where('user_id', $user->id)
            //         ->where('completed', 1);
            // })
                ->orderBy('personal_challenge_users.updated_at', 'DESC')
                ->paginate(config('zevolifesettings.datatable.pagination.short'));

            if ($challengeHistoryData->count() > 0) {
                // collect required data and return response
                return $this->successResponse(new PersonalChallengeHistoryCollection($challengeHistoryData), 'Challenge history listed successfully.');
            } else {
                // return empty response
                return $this->successResponse(['data' => []], 'No results');
            }
        } catch (\Exception $e) {
            \DB::rollback();
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
    public function completeTasks(PersonalChallenge $personalChallenge, PersonalChallengeTask $personalChallengeTask)
    {
        try {
            // logged-in user
            $user = $this->user();

            $userData = $personalChallenge->personalChallengeUsers()
                ->where('user_id', $user->id)
                ->where('joined', 1)
                ->where('completed', 0)
                ->orderBy('id', 'DESC')
                ->first();

            if (!empty($userData)) {
                if ($personalChallenge->type == 'streak') {
                    $userTaskData = PersonalChallengeUserTask::where('personal_challenge_id', $personalChallenge->id)
                        ->where('personal_challenge_user_id', $userData->pivot->id)
                        ->where('personal_challenge_tasks_id', $personalChallengeTask->id)
                        ->whereDate('date', Carbon::now()->setTimezone($user->timezone)->toDateString())
                        ->first();
                } else {
                    $userTaskData = PersonalChallengeUserTask::where('personal_challenge_id', $personalChallenge->id)
                        ->where('personal_challenge_user_id', $userData->pivot->id)
                        ->where('personal_challenge_tasks_id', $personalChallengeTask->id)
                        ->first();
                }

                if (!empty($userTaskData)) {
                    \DB::beginTransaction();
                    $userTaskData->update(['completed' => $userTaskData->completed ? 0 : 1]);
                    \DB::commit();

                    if ($userTaskData->completed) {
                        return $this->successResponse([], 'Task marked as completed successfully.');
                    } else {
                        return $this->successResponse([], 'Task marked as pending successfully.');
                    }
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

    /**
     * @param PersonalChallenge $personalChallenge, $startDate
     * @return void
     */
    protected function setStartReminder(PersonalChallenge $personalChallenge, $startDate, $personalChallengeMappingId): void
    {
        $user = Auth::user();

        $title   = trans('notifications.personal-challenge.challenge-start.title');
        $message = trans('notifications.personal-challenge.challenge-start.message');
        $message = str_replace(["#challenge_name#"], [$personalChallenge->title], $message);

        $date = Carbon::parse($startDate)->subHours(24)->toDateTimeString();

        $time = \Carbon\Carbon::parse($date, $user->timezone)
            ->setTimezone(config('app.timezone'))
            ->todatetimeString();

        $userNotification = NotificationSetting::select('flag')
            ->where(['flag' => 1, 'user_id' => $user->getKey()])
            ->whereRaw('(`user_notification_settings`.`module` = ? OR `user_notification_settings`.`module` = ?)', ['challenges', 'all'])
            ->first();

        $notification = Notification::create([
            'type'             => 'Manual',
            'creator_id'       => $user->id,
            'creator_timezone' => $user->timezone,
            'title'            => $title,
            'message'          => $message,
            'push'             => ($userNotification->flag ?? false),
            'scheduled_at'     => $time,
            'deep_link_uri'    => $personalChallenge->deep_link_uri . '/' . $personalChallengeMappingId,
            'tag'              => 'challenge',
        ]);

        if (isset($notification)) {
            $user->notifications()->attach($notification, ['sent' => false]);
        }
    }

    /**
     * @param PersonalChallenge $personalChallenge, $endDate
     * @return void
     */
    protected function setEndReminder(PersonalChallenge $personalChallenge, $endDate, $personalChallengeMappingId): void
    {
        $user = Auth::user();

        $title   = trans('notifications.personal-challenge.challenge-end.title');
        $message = trans('notifications.personal-challenge.challenge-end.message');
        $message = str_replace(["#challenge_name#", "#end_time#"], [$personalChallenge->title, $endDate], $message);

        $date = Carbon::parse($endDate)->subHours(12)->toDateTimeString();

        $time = \Carbon\Carbon::parse($date, $user->timezone)
            ->setTimezone(config('app.timezone'))
            ->todatetimeString();

        $userNotification = NotificationSetting::select('flag')
            ->where(['flag' => 1, 'user_id' => $user->getKey()])
            ->whereRaw('(`user_notification_settings`.`module` = ? OR `user_notification_settings`.`module` = ?)', ['challenges', 'all'])
            ->first();

        $notification = Notification::create([
            'type'             => 'Manual',
            'creator_id'       => $user->id,
            'creator_timezone' => $user->timezone,
            'title'            => $title,
            'message'          => $message,
            'push'             => ($userNotification->flag ?? false),
            'scheduled_at'     => $time,
            'deep_link_uri'    => $personalChallenge->deep_link_uri . '/' . $personalChallengeMappingId,
            'tag'              => 'challenge',
        ]);

        if (isset($notification)) {
            $user->notifications()->attach($notification, ['sent' => false]);
        }
    }
}
