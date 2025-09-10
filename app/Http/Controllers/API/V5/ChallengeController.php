<?php declare (strict_types = 1);

namespace App\Http\Controllers\API\V5;

use App\Http\Collections\V5\ChallengeHistoryListCollection;
use App\Http\Collections\V5\ChallengeListCollection;
use App\Http\Collections\V5\InvitationsListCollection;
use App\Http\Collections\V5\UpcomingChallengeListCollection;
use App\Http\Controllers\API\V3\ChallengeController as v3ChallengeController;
use App\Http\Resources\V5\ChallengeDetailsResource;
use App\Http\Resources\V5\FinishedChallengeDetailResource;
use App\Http\Traits\ProvidesAuthGuardTrait;
use App\Http\Traits\ServesApiTrait;
use App\Models\Challenge;
use App\Models\ChallengeParticipant;
use DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChallengeController extends v3ChallengeController
{
    use ServesApiTrait, ProvidesAuthGuardTrait;

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getInvitationsList(Request $request)
    {
        try {
            // logged-in user
            $user            = $this->user();
            $timezone        = $user->timezone ?? config('app.timezone');
            $currentDateTime = now()->toDateTimeString();

            $exploreChallengeData = Challenge::select("challenges.*", 'challenge_categories.name as challengeCatName')
                ->join("challenge_categories", "challenge_categories.id", "=", "challenges.challenge_category_id")
                ->join("challenge_participants", "challenge_participants.challenge_id", "=", "challenges.id")
                ->where("challenge_participants.status", "Pending")
                ->where("challenge_participants.user_id", $user->id)
                ->where(DB::raw("CONVERT_TZ(challenges.start_date, 'UTC', '{$timezone}')"), ">", now($timezone)->toDateTimeString())
                ->where("challenges.cancelled", false)
                ->where("challenges.challenge_type", 'individual')
                ->orderByRaw(DB::raw("TIMESTAMPDIFF(SECOND,'{$currentDateTime}',challenges.start_date)"))
                ->orderBy('challenges.updated_at', 'DESC')
                ->orderBy('challenges.id', 'DESC')
                ->groupBy('challenges.id')
                ->paginate(config('zevolifesettings.datatable.pagination.short'));

            if ($exploreChallengeData->count() > 0) {
                // collect required data and return response
                return $this->successResponse(new InvitationsListCollection($exploreChallengeData), 'Invitations recieved successfully.');
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
                    END) AS chStatus")
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
            // ->where('challenges.company_id', $company->id)
                ->where(DB::raw("CONVERT_TZ(challenges.end_date, 'UTC', '{$timezone}')"), ">", now($timezone)->toDateTimeString());

            if (!empty($request->slug) && strtolower($request->slug) != 'all') {
                $exploreChallengeData = $exploreChallengeData->where("challenge_categories.short_name", $request->slug);
            }

            $exploreChallengeData = $exploreChallengeData
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
            $user     = $this->user();
            $timezone = $user->timezone ?? config('app.timezone');
            $now      = now($timezone)->toDateTimeString();
            $company  = $user->company()->first();

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
                ->where(DB::raw("CONVERT_TZ(challenges.end_date, 'UTC', '{$timezone}')"), "<", now($timezone)->toDateTimeString())
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
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDetails(Request $request, Challenge $challenge)
    {
        try {
            // logged-in user
            $user     = $this->user();
            $timezone = $user->timezone ?? config('app.timezone');

            $ChallengeDetails = $challenge->where("challenges.id", $challenge->id)
                ->join("challenge_categories", "challenge_categories.id", "=", "challenges.challenge_category_id")
                ->leftJoin("challenge_participants", function ($join) {
                    $join->on("challenges.id", "=", "challenge_participants.challenge_id")
                        ->where("challenge_participants.status", "Accepted");
                })
                ->select("challenges.*", DB::raw("COUNT(challenge_participants.user_id) as members"), "challenge_categories.name as challengeCatName", "challenge_categories.short_name as challengeCatShortName")
                ->groupBy('challenges.id')
                ->first();

            // get group details data with json response
            $data = array("data" => new ChallengeDetailsResource($ChallengeDetails));

            return $this->successResponse($data, 'Detail retrieved successfully.');
        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

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
            $user     = $this->user();
            $timezone = $user->timezone ?? config('app.timezone');

            if (!$challenge->finished && !$challenge->cancelled) {
                $company        = \App\Models\Company::find($challenge->company_id);
                $pointCalcRules = (!empty($company) && $company->companyWiseChallengeSett()->count() > 0) ? $company->companyWiseChallengeSett()->pluck('value', 'type')->toArray() : config('zevolifesettings.default_limits');
                $procedureData  = array();
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

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function upcoming(Request $request)
    {
        try {
            // logged-in user
            $user     = $this->user();
            $timezone = $user->timezone ?? config('app.timezone');
            $company  = $user->company()->first();

            $exploreChallengeData = Challenge::select("challenges.*", "challenge_categories.name as challengeCatName", "challenge_categories.short_name as challengeCatShortName")
                ->join("challenge_categories", "challenge_categories.id", "=", "challenges.challenge_category_id")
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
                ->where("challenges.cancelled", false);

            if ($request->createdBy == 'me') {
                if ($user->teams()->first()->default) {
                    $exploreChallengeData = $exploreChallengeData->where("creator_id", $user->id)->where('challenges.challenge_type', 'individual');
                } else {
                    $exploreChallengeData = $exploreChallengeData->where("creator_id", $user->id);
                }
            } elseif ($request->createdBy == 'others') {
                $exploreChallengeData = $exploreChallengeData
                    ->join("challenge_participants", "challenge_participants.challenge_id", "=", "challenges.id")
                    ->where("challenge_participants.status", "Accepted")
                    ->where(function ($query) use ($user, $company) {

                        $query->where(function ($subQuery) use ($user) {
                            $subQuery->where('challenges.challenge_type', 'individual')
                                ->where('challenge_participants.user_id', $user->id);
                        });

                        if (!$user->teams()->first()->default) {
                            $query->orWhere(function ($subQuery) use ($user) {
                                $subQuery->whereIn('challenges.challenge_type', ['team', 'company_goal'])
                                    ->where('challenge_participants.team_id', $user->teams()->first()->id);
                            });
                        }

                        $query->orWhere(function ($subQuery) use ($user, $company) {
                            $subQuery->where('challenges.challenge_type', 'inter_company')
                                ->where('challenge_participants.team_id', $user->teams()->first()->id)
                                ->where('challenge_participants.company_id', $company->getKey());
                        });
                    })
                    ->where("creator_id", "!=", $user->id);
            } else {
                return $this->notFoundResponse("Sorry! Requested data not found");
            }

            $exploreChallengeData = $exploreChallengeData
                ->where(DB::raw("CONVERT_TZ(challenges.start_date, 'UTC', '{$timezone}')"), ">", now($timezone)->toDateTimeString())
                ->orderBy('challenges.updated_at', 'DESC')
                ->orderBy('challenges.id', 'DESC')
                ->groupBy('challenges.id')
                ->paginate(config('zevolifesettings.datatable.pagination.short'));

            if ($exploreChallengeData->count() > 0) {
                // collect required data and return response
                return $this->successResponse(new UpcomingChallengeListCollection($exploreChallengeData), 'Upcoming challenges retrieved successfully');
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
