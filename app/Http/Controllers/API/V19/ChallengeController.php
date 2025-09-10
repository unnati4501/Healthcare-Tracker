<?php declare (strict_types = 1);

namespace App\Http\Controllers\API\V19;

use App\Http\Collections\V5\ChallengeHistoryListCollection;
use App\Http\Collections\V19\ChallengeListCollection;
use App\Http\Collections\V19\ChallengeTeamMembersDetailsCollection;
use App\Http\Controllers\API\V18\ChallengeController as v18ChallengeController;
use App\Http\Traits\ProvidesAuthGuardTrait;
use App\Http\Traits\ServesApiTrait;
use App\Models\Challenge;
use App\Models\ChallengeParticipant;
use App\Models\Company;
use App\Models\User;
use DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChallengeController extends v18ChallengeController
{
    use ServesApiTrait, ProvidesAuthGuardTrait;

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function exploreChallenges(Request $request)
    {
        try {
            // logged-in user
            $user            = $this->user();
            $timezone        = $user->timezone ?? config('app.timezone');
            $company         = $user->company()->first();
            $currentDateTime = now()->toDateTimeString();

            $challengeIds = ChallengeParticipant::where(function ($query) use ($user, $company) {
                $query->where("user_id", $user->id)
                    ->orWhere("team_id", $user->teams()->first()->id)
                    ->orWhere("company_id", $company->getKey());
            })
                ->where("status", "Accepted")
                ->groupBy("challenge_id")
                ->get()
                ->pluck('challenge_id')
                ->toArray();

            $implodedChallengeIds = implode(',', $challengeIds);

            if (empty($implodedChallengeIds)) {
                $implodedChallengeIds = '0';
            }

            $exploreChallengeData = Challenge::leftJoin("challenge_participants", function ($join) {
                $join->on("challenges.id", "=", "challenge_participants.challenge_id")
                    ->where("challenge_participants.status", "Accepted");
            })
                ->join("challenge_categories", "challenge_categories.id", "=", "challenges.challenge_category_id")
                ->select(
                    "challenges.*",
                    "challenge_categories.name as challengeCatName",
                    "challenge_categories.short_name as challengeCatShortName",
                    DB::raw("COUNT(challenge_participants.user_id) as members"),
                    DB::raw("(CASE
                        WHEN (CONVERT_TZ(challenges.start_date, 'UTC', '{$timezone}') < CONVERT_TZ(now(), 'UTC', '{$timezone}')) THEN 'ongoing'
                        WHEN ((CONVERT_TZ(challenges.start_date, 'UTC', '{$timezone}') > CONVERT_TZ(now(), 'UTC', '{$timezone}')) AND challenges.id NOT IN ({$implodedChallengeIds})) THEN 'open'
                        WHEN (CONVERT_TZ(challenges.start_date, 'UTC', '{$timezone}') > CONVERT_TZ(now(), 'UTC', '{$timezone}')) THEN 'upcoming'
                    END) AS chStatus"),
                    DB::raw("IF((CONVERT_TZ(now(), 'UTC', '{$timezone}') > CONVERT_TZ(challenges.end_date, 'UTC', '{$timezone}')), 1, 0) AS challenge_enddate_order")
                    // DB::raw(" IF( CONVERT_TZ(challenges.start_date, 'UTC', '{$timezone}') > CONVERT_TZ(now(), 'UTC', '{$timezone}') , 'upcommig' , 'ongoing' )  as chStatus")
                )
                ->where(function ($query) use ($user, $company, $challengeIds, $timezone) {
                    $query->where(function ($subQuery) use ($user, $challengeIds, $timezone) {
                        $subQuery->where('challenges.challenge_type', 'individual')
                            ->where(function ($subQuery1) use ($user, $challengeIds, $timezone) {
                                $subQuery1->where(function ($subQuery2) use ($challengeIds) {
                                    $subQuery2->whereIn("challenges.id", $challengeIds);
                                })->orWhere(function ($subQuery2) use ($timezone) {
                                    $subQuery2->where("challenges.close", false)
                                        ->where(DB::raw("CONVERT_TZ(challenges.start_date, 'UTC', '{$timezone}')"), ">", now($timezone)->toDateTimeString());
                                });
                            });
                    })->orWhere(function ($subQuery) use ($user) {
                        $subQuery->whereIn('challenges.challenge_type', ['team', 'company_goal'])
                            ->where('challenge_participants.team_id', $user->teams()->first()->id);
                    })->orWhere(function ($subQuery) use ($user, $company) {
                        $subQuery->where('challenges.challenge_type', 'inter_company')
                            ->where('challenge_participants.team_id', $user->teams()->first()->id)
                            ->where('challenge_participants.company_id', $company->getKey());
                    });
                })
                ->where("challenges.cancelled", false)
                ->where(function ($query) use ($company) {
                    $query->where(function ($subQuery) use ($company) {
                        $subQuery->where('challenges.challenge_type', '!=', 'inter_company')
                            ->where('challenges.company_id', $company->getKey());
                    })->orWhere(function ($subQuery) use ($company) {
                        $subQuery->where('challenges.challenge_type', 'inter_company')
                            ->where('challenges.company_id', null);
                    });
                })
            // Use challenge End Date for each and every challenge type
                ->where(DB::raw("CONVERT_TZ(DATE_ADD(challenges.end_date, INTERVAL 1 DAY), 'UTC', '{$timezone}')"), ">", now($timezone)->toDateTimeString());

            if (!empty($request->slug) && strtolower($request->slug) != 'all') {
                $exploreChallengeData = $exploreChallengeData->where("challenge_categories.short_name", $request->slug);
            }

            $exploreChallengeData = $exploreChallengeData
                ->orderBy('challenge_enddate_order', 'ASC')
                ->orderByRaw("FIELD(chStatus, 'ongoing', 'upcoming', 'open')")
                ->orderByRaw("CASE
                        WHEN chStatus = 'ongoing' THEN TIMESTAMPDIFF(SECOND,'{$currentDateTime}',challenges.end_date)
                        WHEN chStatus = 'upcoming' THEN TIMESTAMPDIFF(SECOND,'{$currentDateTime}',challenges.start_date)
                        WHEN chStatus = 'open' THEN TIMESTAMPDIFF(SECOND,'{$currentDateTime}',challenges.start_date)
                        END")
                ->orderBy('challenges.updated_at', 'DESC')
                ->groupBy('challenges.id')
                ->paginate(config('zevolifesettings.datatable.pagination.short'));

            if ($exploreChallengeData->count() > 0) {
                // collect required data and return response
                return $this->successResponse(new ChallengeListCollection($exploreChallengeData), 'Challenge Listed successfully.');
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
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function history(Request $request)
    {
        try {
            // logged-in user
            $user                = $this->user();
            $timezone            = $user->timezone ?? config('app.timezone');
            $now                 = now($timezone)->toDateTimeString();
            $company             = $user->company()->first();
            $completedChallenges = Challenge::
                join("challenge_history", "challenges.id", "=", "challenge_history.challenge_id")
                ->join("challenge_categories", "challenge_categories.id", "=", "challenges.challenge_category_id")
                ->join("freezed_challenge_participents", function ($join) use ($user, $company) {
                    $join->on("challenges.id", "=", "freezed_challenge_participents.challenge_id")
                        ->where(function ($query) use ($user, $company) {
                            $query->where(function ($subQuery) use ($user) {
                                $subQuery->where('challenges.challenge_type', 'individual')
                                    ->where('freezed_challenge_participents.user_id', $user->id);
                            })->orWhere(function ($subQuery) use ($user) {
                                $subQuery->whereIn('challenges.challenge_type', ['team', 'company_goal'])
                                    ->where('freezed_challenge_participents.team_id', $user->teams()->first()->id);
                            })->orWhere(function ($subQuery) use ($user, $company) {
                                $subQuery->where('challenges.challenge_type', 'inter_company')
                                    ->where('freezed_challenge_participents.team_id', $user->teams()->first()->id)
                                    ->where('freezed_challenge_participents.company_id', $company->getKey());
                            });
                        });
                })
                ->select("challenges.*", "challenge_categories.name as challengeCatName", "challenge_categories.short_name as challengeCatShortName")
                ->where("challenges.cancelled", false)
            // ->where('challenges.company_id', $company->id)
                ->where(function ($query) use ($company) {
                    $query->where(function ($subQuery) use ($company) {
                        $subQuery->where('challenges.challenge_type', '!=', 'inter_company')
                            ->where('challenges.company_id', $company->getKey());
                    })->orWhere(function ($subQuery) use ($company) {
                        $subQuery->where('challenges.challenge_type', 'inter_company')
                            ->where('challenges.company_id', null);
                    });
                })
            // Use challenge End Date for each and every challenge type
                ->where(DB::raw("CONVERT_TZ(DATE_ADD(challenges.end_date, INTERVAL 1 DAY), 'UTC', '{$timezone}')"), "<", now($timezone)->toDateTimeString())
                ->groupBy('freezed_challenge_participents.challenge_id')
                ->orderBy('challenge_history.id', 'DESC')
                ->paginate(config('zevolifesettings.datatable.pagination.short'));

            if ($completedChallenges->count() > 0) {
                // collect required data and return response
                return $this->successResponse(new ChallengeHistoryListCollection($completedChallenges), 'Challenge history retrieved successfully.');
            } else {
                // return empty response
                return $this->successResponse(['data' => []], 'No results');
            }
        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }

        return json_decode($jsonString, true);
    }

    /**
     * Get Team members of a challenge
     *
     * @param Request $request, Challenge $challenge
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTeamMembers(Request $request, Challenge $challenge)
    {
        try {
            // logged-in user
            $user     = $this->user();
            $company  = $user->company()->first();
            $timezone = $user->timezone ?? config('app.timezone');

            if (empty($company)) {
                abort(403);
            }

            $challengeHistory = $challenge->challengeHistory;

            if (!empty($challengeHistory)) {
                $challengeUsers = $challenge
                    ->leftJoin('challenge_wise_user_ponits', 'challenge_wise_user_ponits.challenge_id', '=', 'challenges.id')
                    ->select('challenge_wise_user_ponits.user_id', 'challenge_wise_user_ponits.points')
                    ->where("challenges.id", $challenge->getKey())
                    ->where("challenges.cancelled", false)
                    ->where('challenge_wise_user_ponits.team_id', $user->teams()->first()->id)
                    ->orderBy('challenge_wise_user_ponits.points', 'DESC')
                    ->paginate(config('zevolifesettings.datatable.pagination.short'));
            } else {
                $challengeUsers = \DB::table('user_team')
                    ->where('user_team.team_id', $user->teams()->first()->id)
                    ->select('user_team.user_id')
                    ->paginate(config('zevolifesettings.datatable.pagination.short'));
            }

            if (!empty($challengeUsers)) {
                return $this->successResponse(
                    ($challengeUsers->count() > 0) ? new ChallengeTeamMembersDetailsCollection($challengeUsers) : ['data' => []],
                    ($challengeUsers->count() > 0) ? 'Team members list retrieved successfully.' : 'No team members found'
                );
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
