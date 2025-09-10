<?php declare (strict_types = 1);

namespace App\Http\Controllers\API\V5;

use App\Http\Collections\V5\PersonalChallengeListCollection;
use App\Http\Controllers\API\V3\PersonalChallengeController as v3PersonalChallengeController;
use App\Http\Requests\Api\V3\JoinPersonalChallengeRequest;
use App\Http\Resources\V5\PersonalChallengeDetailResource;
use App\Http\Traits\ProvidesAuthGuardTrait;
use App\Http\Traits\ServesApiTrait;
use App\Models\Notification;
use App\Models\PersonalChallenge;
use App\Models\PersonalChallengeUser;
use App\Models\PersonalChallengeUserTask;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PersonalChallengeController extends v3PersonalChallengeController
{
    use ServesApiTrait, ProvidesAuthGuardTrait;

    /**
     * Complete personal challenge
     *
     * @param  PersonalChallengeUser $personalChallengeUser
     * @return \Illuminate\Http\JsonResponse
     */
    public function completePersonalChallenge(PersonalChallengeUser $personalChallengeUser)
    {
        try {
            $user      = User::find($personalChallengeUser->user_id);
            $completed = $personalChallengeUser->update(['end_date' => Carbon::now()->toDateTimeString()]);

            if ($completed) {
                $deepLinkURI = "zevolife://zevo/personal-challenge/" . $personalChallengeUser->personal_challenge_id . '/' . $personalChallengeUser->id;
                $title       = trans('notifications.personal-challenge.challenge-end.title');

                Notification::leftJoin('notification_user', 'notification_user.notification_id', '=', 'notifications.id')
                    ->where('notifications.deep_link_uri', $deepLinkURI)
                    ->where('notifications.title', $title)
                    ->where('notification_user.sent', 0)
                    ->delete();

                $personalChallengeUser->sendFinishReminderNotification($personalChallengeUser);
                return $this->successResponse([], trans('api_messages.challenge.complete'));
            } else {
                return $this->notFoundResponse('Challenge not completed.');
            }
        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

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
                    $deepLinkURI = "zevolife://zevo/personal-challenge/" . $personalChallenge->getKey() . '/';
                    Notification::where('deep_link_uri', 'LIKE', $deepLinkURI . '%')
                        ->where('creator_id', $user->id)
                        ->delete();
                }
                \DB::commit();

                return $this->successResponse([], trans('api_messages.challenge.leave'));
            } else {
                return $this->notFoundResponse('Challenge not joined.');
            }
        } catch (\Exception $e) {
            \DB::rollback();
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }
}
