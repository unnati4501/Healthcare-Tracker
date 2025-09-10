<?php declare (strict_types = 1);

namespace App\Http\Controllers\API\V22;

use App\Http\Collections\V21\PersonalChallengeListCollection;
use App\Http\Controllers\API\V21\PersonalChallengeController as v21PersonalChallengeController;
use App\Http\Resources\V22\PersonalChallengeDetailResource;
use App\Http\Traits\ProvidesAuthGuardTrait;
use App\Http\Traits\ServesApiTrait;
use App\Models\PersonalChallenge;
use App\Models\PersonalChallengeUser;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PersonalChallengeController extends v21PersonalChallengeController
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

            $moderatorUsers = $company->moderators()->pluck('user_id')->toArray();
            array_push($moderatorUsers, $user->id);

            $exploreChallengeData = PersonalChallenge::with(['personalChallengeUsers'])
                ->leftjoin("personal_challenge_users", function ($join) use ($user) {
                    $join->on("personal_challenge_users.personal_challenge_id", "=", "personal_challenges.id")
                        ->where('personal_challenge_users.user_id', $user->id);
                })
                ->select(
                    'personal_challenges.id',
                    'personal_challenges.title',
                    'personal_challenges.logo',
                    'personal_challenges.library_image_id',
                    'personal_challenges.challenge_type',
                    'personal_challenges.type',
                    'personal_challenges.duration',
                    'personal_challenges.creator_id',
                    'personal_challenges.updated_at',
                    'personal_challenge_users.updated_at',
                    'personal_challenge_users.completed'
                )
                ->distinct('personal_challenges.id')
                ->where(function ($query) use ($company, $moderatorUsers) {
                    $query->where('personal_challenges.company_id', null)
                    ->orWhere(function ($q) use ($company, $moderatorUsers) {
                        $q->where('personal_challenges.company_id', $company->id)
                        ->whereIn('personal_challenges.creator_id', $moderatorUsers);
                    });
                })
                ->where(function ($query) {
                    $query->where('personal_challenge_users.completed', 0)
                    ->orWhere('personal_challenge_users.completed', null);
                })
                ->groupBy('personal_challenges.id')
                ->orderBy('personal_challenge_users.end_date', 'DESC')
                ->orderBy('personal_challenge_users.start_date', 'DESC')
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
}
