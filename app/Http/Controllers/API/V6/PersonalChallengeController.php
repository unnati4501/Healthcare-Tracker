<?php declare (strict_types = 1);

namespace App\Http\Controllers\API\V6;

use App\Http\Controllers\API\V5\PersonalChallengeController as v5PersonalChallengeController;
use App\Http\Requests\Api\V3\JoinPersonalChallengeRequest;
use App\Http\Resources\V6\PersonalChallengeDetailResource;
use App\Http\Traits\ProvidesAuthGuardTrait;
use App\Http\Traits\ServesApiTrait;
use App\Jobs\SendPersonalChallengePushNotification;
use App\Models\NotificationSetting;
use App\Models\PersonalChallenge;
use App\Models\PersonalChallengeUser;
use App\Models\PersonalChallengeUserTask;
use App\Models\User;
use Auth;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PersonalChallengeController extends v5PersonalChallengeController
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

            $startDate = Carbon::parse($payload['startDate'], $user->timezone)->setTimezone(config('app.timezone'))->toDateTimeString();
            $endDate   = Carbon::parse($payload['startDate'], $user->timezone)->setTimezone(config('app.timezone'))->addDays($personalChallenge->duration)->toDateTimeString();

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
     * @param PersonalChallenge $personalChallenge, $startDate
     * @return void
     */
    protected function setStartReminder(PersonalChallenge $personalChallenge, $startDate, $personalChallengeMappingId): void
    {
        $user = Auth::user();
        $date = Carbon::parse($startDate)->subHours(24)->toDateTimeString();
        $time = Carbon::parse($date, $user->timezone)
            ->setTimezone(config('app.timezone'))
            ->todatetimeString();
        $userNotification = NotificationSetting::select('flag')
            ->where(['flag' => 1, 'user_id' => $user->getKey()])
            ->whereRaw('(`user_notification_settings`.`module` = ? OR `user_notification_settings`.`module` = ?)', ['challenges', 'all'])
            ->first();

        // dispatch job to send push notification to app user when challenge start reminder
        \dispatch(new SendPersonalChallengePushNotification($personalChallenge, "challenge-start", $user, [
            'type'         => 'Manual',
            'scheduled_at' => $time,
            'push'         => ($userNotification->flag ?? false),
            'mapping_id'   => $personalChallengeMappingId,
        ]));
    }

    /**
     * @param PersonalChallenge $personalChallenge, $endDate
     * @return void
     */
    protected function setEndReminder(PersonalChallenge $personalChallenge, $endDate, $personalChallengeMappingId): void
    {
        $user = Auth::user();
        $date = Carbon::parse($endDate)->subHours(12)->toDateTimeString();
        $time = Carbon::parse($date, $user->timezone)
            ->setTimezone(config('app.timezone'))
            ->todatetimeString();
        $userNotification = NotificationSetting::select('flag')
            ->where(['flag' => 1, 'user_id' => $user->getKey()])
            ->whereRaw('(`user_notification_settings`.`module` = ? OR `user_notification_settings`.`module` = ?)', ['challenges', 'all'])
            ->first();
        $notification_end_date = Carbon::parse($endDate, $user->timezone)
            ->format(config('zevolifesettings.date_format.default_datetime'));

        // dispatch job to send push notification to app user for challenge end reminder
        \dispatch(new SendPersonalChallengePushNotification($personalChallenge, "challenge-end", $user, [
            'type'         => 'Manual',
            'scheduled_at' => $time,
            'push'         => ($userNotification->flag ?? false),
            'mapping_id'   => $personalChallengeMappingId,
            'end_date'     => $notification_end_date,
        ]));
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
            $user = $this->user();

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

                    if ($personalChallengeUserTask->completed) {
                        return $this->successResponse([], trans('api_messages.challenge.mark'));
                    } else {
                        return $this->successResponse([], trans('api_messages.challenge.unmark'));
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
}
