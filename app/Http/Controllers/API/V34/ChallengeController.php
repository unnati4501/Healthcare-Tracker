<?php declare (strict_types = 1);

namespace App\Http\Controllers\API\V34;

use App\Http\Controllers\API\V33\ChallengeController as v33ChallengeController;
use App\Http\Resources\V34\FinishedChallengeDetailResource;
use App\Http\Traits\ProvidesAuthGuardTrait;
use App\Http\Traits\ServesApiTrait;
use App\Models\Challenge;
use App\Models\Company;
use App\Models\User;
use DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChallengeController extends v33ChallengeController
{
    use ServesApiTrait, ProvidesAuthGuardTrait;

    /**
     * get details of ongoing challenge woth user points and rank
     *
     * @param Request $request, Challenge $challenge
     * @return \Illuminate\Http\JsonResponse
     */
    public function ongoingDetailsNew(Request $request, Challenge $challenge)
    {
        try {
            if ($challenge->cancelled) {
                return $this->notFoundResponse("The challenge has been cancelled.");
            }

            // logged-in user
            $user        = $this->user();
            $userCompany = $user->company()->first();
            $timezone    = $user->timezone ?? config('app.timezone');
            $checkAccess = getCompanyPlanAccess($user, 'my-challenges');

            if (!$checkAccess) {
                return $this->notFoundResponse('Challenge is disabled for this company.');
            }
            if ($challenge->close) {
                if (!$challenge->finished) {
                    if ($challenge->challenge_type == 'individual') {
                        $mappingId = $challenge->members()->where('user_id', $user->id)->first();
                    } else {
                        $mappingId = $challenge->memberTeams()->where('team_id', $userCompany->pivot->team_id)->first();
                    }
                } else {
                    if ($challenge->challenge_type == 'individual') {
                        $mappingId = $challenge->membersHistory()->where('user_id', $user->id)->first();
                    } else {
                        $mappingId = $challenge->memberTeamsHistory()->where('team_id', $userCompany->pivot->team_id)->first();
                    }
                }

                if (empty($mappingId)) {
                    return $this->notFoundResponse("Sorry! Requested data not found");
                }
            }

            $challengeHistory = $challenge->challengeHistory;

            // if (!empty($challengeHistory)) {
            // completed challenge detail resource
            $challengeDetails = $challenge
                ->leftJoin("freezed_challenge_participents", function ($join) {
                    $join->on("challenges.id", "=", "freezed_challenge_participents.challenge_id");
                })
                ->join("challenge_categories", "challenge_categories.id", "=", "challenges.challenge_category_id")
                ->select("challenges.*", DB::raw("COUNT(freezed_challenge_participents.user_id) as members"), "challenge_categories.name as challengeCatName", "challenge_categories.short_name as challengeCatShortName")
                ->where('challenges.id', $challenge->getKey())
                ->where("challenges.cancelled", false)
                ->groupBy('challenges.id')
                ->first();

            if (!empty($challengeDetails)) {
                $data = array("data" => new FinishedChallengeDetailResource($challengeDetails));
                return $this->successResponse($data, 'Detail retrieved successfully.');
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
