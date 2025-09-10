<?php declare (strict_types = 1);

namespace App\Http\Controllers\API\V18;

use App\Http\Controllers\API\V9\ChallengeController as v9ChallengeController;
use App\Http\Resources\V18\FinishedChallengeDetailResource;
use App\Http\Traits\ProvidesAuthGuardTrait;
use App\Http\Traits\ServesApiTrait;
use App\Models\Challenge;
use App\Models\Company;
use App\Models\User;
use DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChallengeController extends v9ChallengeController
{
    use ServesApiTrait, ProvidesAuthGuardTrait;

    /**
     * get details of ongoing challenge woth user points and rank
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function ongoingDetails(Request $request, Challenge $challenge)
    {
        try {
            if ($challenge->cancelled) {
                return $this->notFoundResponse("The challenge has been cancelled.");
            }

            // logged-in user
            $user        = $this->user();
            $userCompany = $user->company()->first();
            $timezone    = $user->timezone ?? config('app.timezone');

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
            //  && empty($challenge->freezed_data_at)
            if (!$challenge->finished && !$challenge->cancelled) {
                $challengeExecuteStatus = $challenge->checkExecutionRequired();
                if ($challengeExecuteStatus) {
                    $company        = Company::find($challenge->company_id);
                    $pointCalcRules = (!empty($company) && $company->companyWiseChallengeSett()->count() > 0) ? $company->companyWiseChallengeSett()->pluck('value', 'type')->toArray() : config('zevolifesettings.default_limits');
                    $procedureData  = [
                        config('app.timezone'),
                        $challenge->id,
                        $pointCalcRules['steps'],
                        $pointCalcRules['distance'],
                        $pointCalcRules['exercises_distance'],
                        $pointCalcRules['exercises_duration'],
                        $pointCalcRules['meditations'],
                    ];
                    if ($challenge->challenge_type == 'individual') {
                        DB::select('CALL sp_individual_challenge_pointcalculation(?, ?, ?, ?, ?, ?, ?)', $procedureData);
                    } elseif ($challenge->challenge_type == 'team') {
                        DB::select('CALL sp_team_challenge_pointcalculation(?, ?, ?, ?, ?, ?, ?)', $procedureData);
                    } elseif ($challenge->challenge_type == 'company_goal') {
                        DB::select('CALL sp_company_challenge_pointcalculation(?, ?, ?, ?, ?, ?, ?)', $procedureData);
                    } elseif ($challenge->challenge_type == 'inter_company') {
                        DB::select('CALL sp_inter_comp_challenge_pointcalculation(?, ?, ?, ?, ?, ?, ?)', $procedureData);
                    }
                }
            }

            $challengeHistory = $challenge->challengeHistory;

            if (!empty($challengeHistory)) {
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
