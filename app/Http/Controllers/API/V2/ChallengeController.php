<?php declare (strict_types = 1);

namespace App\Http\Controllers\API\V2;

use App\Http\Collections\V1\RunningChallengeListCollection;
use App\Http\Collections\V2\ChallengeCompanyTeamDetailsCollection;
use App\Http\Collections\V2\ChallengeHistoryListCollection;
use App\Http\Collections\V2\ChallengeListCollection;
use App\Http\Collections\V2\ChallengeTeamMembersDetailsCollection;
use App\Http\Collections\V2\ChallengeUserListCollection;
use App\Http\Collections\V2\UpcomingChallengeListCollection;
use App\Http\Controllers\API\V1\ChallengeController as v1ChallengeController;
use App\Http\Resources\V2\ChallengeDetailsResource;
use App\Http\Resources\V2\ChallengeInfoResource;
use App\Http\Resources\V2\FinishedChallengeDetailResource;
use App\Models\Challenge;
use App\Models\ChallengeCategory;
use App\Models\ChallengeParticipant;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;

class ChallengeController extends v1ChallengeController
{
    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function runningChallenges(Request $request)
    {
        try {
            // logged-in user
            $user     = $this->user();
            $timezone = $user->timezone ?? config('app.timezone');
            $now      = now($timezone)->toDateTimeString();
            $company  = $user->company()->first();

            $exploreChallengeData = Challenge::
                leftJoin("challenge_participants", "challenges.id", "=", "challenge_participants.challenge_id")
                ->leftJoin(DB::raw("(SELECT challenge_participants.challenge_id,  COUNT(DISTINCT challenge_participants.user_id) AS members FROM challenge_participants WHERE challenge_participants.status = 'Accepted' GROUP BY challenge_participants.challenge_id) as challengeMember"), "challenges.id", "=", "challengeMember.challenge_id")
                ->select("challenges.*", "challengeMember.members", DB::raw(" ( TIMESTAMPDIFF(HOUR , CONVERT_TZ(challenges.start_date, 'UTC', '{$timezone}') , '{$now}') * 100) / TIMESTAMPDIFF(HOUR , CONVERT_TZ(challenges.start_date, 'UTC', '{$timezone}') , CONVERT_TZ(challenges.end_date, 'UTC', '{$timezone}')) as completedPer "))
                ->where(DB::raw("CONVERT_TZ(challenges.start_date, 'UTC', '{$timezone}')"), "<=", now($timezone)->toDateTimeString())
                ->where(DB::raw("CONVERT_TZ(challenges.end_date, 'UTC', '{$timezone}')"), ">=", now($timezone)->toDateTimeString())
                ->where("challenges.cancelled", false)
                ->where("challenge_participants.status", "Accepted")
            // ->where("challenge_participants.user_id", $user->id)
                ->where(function ($query) use ($user, $company) {
                    $query->where(function ($subQuery) use ($user) {
                        $subQuery->where('challenges.challenge_type', 'individual')
                            ->where('challenge_participants.user_id', $user->id);
                    })->orWhere(function ($subQuery) use ($user) {
                        $subQuery->whereIn('challenges.challenge_type', ['team', 'company_goal'])
                            ->where('challenge_participants.team_id', $user->teams()->first()->id);
                    })->orWhere(function ($subQuery) use ($user, $company) {
                        $subQuery->where('challenges.challenge_type', 'inter_company')
                            ->where('challenge_participants.team_id', $user->teams()->first()->id)
                            ->where('challenge_participants.company_id', $company->getKey());
                    });
                    // $query->where("challenge_participants.user_id", $user->id)
                    //     ->orWhere('challenge_participants.team_id', $user->teams()->first()->id)
                    //     ->orWhere('challenge_participants.company_id', $company->getKey());
                })
                ->where(function ($query) use ($company) {
                    $query->where(function ($subQuery) use ($company) {
                        $subQuery->where('challenges.challenge_type', '!=', 'inter_company')
                            ->where('challenges.company_id', $company->getKey());
                    })->orWhere(function ($subQuery) use ($company) {
                        $subQuery->where('challenges.challenge_type', 'inter_company')
                            ->where('challenges.company_id', null);
                    });
                })
                ->orderBy('completedPer', 'DESC')
                ->orderBy('challenges.id', 'DESC')
                ->groupBy('challenges.id')
                ->limit(10)
                ->get();

            if ($exploreChallengeData->count() > 0) {
                // collect required data and return response
                return $this->successResponse(new RunningChallengeListCollection($exploreChallengeData), 'Challenges retrieved successfully.');
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

            $exploreChallengeData = Challenge::leftJoin("challenge_participants", function ($join) {
                $join->on("challenges.id", "=", "challenge_participants.challenge_id")
                    ->where("challenge_participants.status", "Accepted");
            })->select("challenges.*", DB::raw("COUNT(challenge_participants.user_id) as members"))
            // ->where(function ($query) use ($challengeIds, $timezone) {
            //     $query->where(function ($subQuery) use ($challengeIds) {
            //         $subQuery->whereIn("challenges.id", $challengeIds);
            //     })->orWhere(function ($subQuery) use ($timezone) {
            //         $subQuery->where("challenges.close", false)
            //             ->where(DB::raw("CONVERT_TZ(challenges.start_date, 'UTC', '{$timezone}')"), ">", now($timezone)->toDateTimeString());
            //     });
            // })
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
                $catId = ChallengeCategory::where("short_name", $request->slug)->first();

                if (!empty($catId)) {
                    $exploreChallengeData = $exploreChallengeData->where("challenges.challenge_category_id", $catId->id);
                } else {
                    return $this->notFoundResponse("Sorry! Challenge category not found");
                }
            }

            $exploreChallengeData = $exploreChallengeData
                ->orderByRaw("FIELD(challenge_type, 'company_goal', 'team', 'individual')")
                ->orderByRaw(DB::raw("TIMESTAMPDIFF(SECOND,'{$currentDateTime}',challenges.end_date)"))
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
                ->select("challenges.*")
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
    public function upcoming(Request $request)
    {
        try {
            // logged-in user
            $user     = $this->user();
            $timezone = $user->timezone ?? config('app.timezone');
            $company  = $user->company()->first();

            $exploreChallengeData = Challenge::select("challenges.*")
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
                ->leftJoin("challenge_participants", function ($join) {
                    $join->on("challenges.id", "=", "challenge_participants.challenge_id")
                        ->where("challenge_participants.status", "Accepted");
                })
                ->select("challenges.*", DB::raw("COUNT(challenge_participants.user_id) as members"))
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
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function runningViewAll(Request $request)
    {
        try {
            // logged-in user
            $user     = $this->user();
            $timezone = $user->timezone ?? config('app.timezone');
            $company  = $user->company()->first();

            $exploreChallengeData = Challenge::
                leftJoin("challenge_participants", "challenges.id", "=", "challenge_participants.challenge_id")
                ->leftJoin(DB::raw("(SELECT challenge_participants.challenge_id,  COUNT(DISTINCT challenge_participants.user_id) AS members FROM challenge_participants WHERE challenge_participants.status = 'Accepted' GROUP BY challenge_participants.challenge_id) as challengeMember"), "challenges.id", "=", "challengeMember.challenge_id")

                ->select("challenges.*", "challengeMember.members")
                ->where(DB::raw("CONVERT_TZ(challenges.start_date, 'UTC', '{$timezone}')"), "<=", now($timezone)->toDateTimeString())
                ->where(DB::raw("CONVERT_TZ(challenges.end_date, 'UTC', '{$timezone}')"), ">=", now($timezone)->toDateTimeString())
                ->where("challenges.cancelled", false)
                ->where("challenge_participants.status", "Accepted")
                ->where(function ($query) use ($user, $company) {
                    $query->where(function ($subQuery) use ($user) {
                        $subQuery->where('challenges.challenge_type', 'individual')
                            ->where('challenge_participants.user_id', $user->id);
                    })->orWhere(function ($subQuery) use ($user) {
                        $subQuery->whereIn('challenges.challenge_type', ['team', 'company_goal'])
                            ->where('challenge_participants.team_id', $user->teams()->first()->id);
                    })->orWhere(function ($subQuery) use ($user, $company) {
                        $subQuery->where('challenges.challenge_type', 'inter_company')
                            ->where('challenge_participants.team_id', $user->teams()->first()->id)
                            ->where('challenge_participants.company_id', $company->getKey());
                    });
                    // $query->where("challenge_participants.user_id", $user->id)
                    //     ->orWhere("challenge_participants.team_id", $user->teams()->first()->id)
                    //     ->orWhere("challenge_participants.company_id", $company->id);
                })
            // ->where("challenge_participants.user_id", $user->id)
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
    public function getInfo(Request $request, Challenge $challenge)
    {
        try {
            // logged-in user
            $user     = $this->user();
            $timezone = $user->timezone ?? config('app.timezone');

            $ChallengeDetails = $challenge->where("challenges.id", $challenge->id)
                ->leftJoin("challenge_participants", function ($join) {
                    $join->on("challenges.id", "=", "challenge_participants.challenge_id")
                        ->where("challenge_participants.status", "Accepted");
                })
                ->join("challenge_categories", "challenge_categories.id", "=", "challenges.challenge_category_id")
                ->select("challenges.*", DB::raw("COUNT(challenge_participants.user_id) as members"), "challenge_categories.name as challengeCatName", "challenge_categories.short_name as challengeCatShortName")
                ->groupBy('challenges.id')
                ->first();

            // get group details data with json response
            $data = array("data" => new ChallengeInfoResource($ChallengeDetails));

            return $this->successResponse($data, 'Detail retrieved successfully.');
        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * Get users active in the challenge.
     *
     * @param Challenge $challenge
     * @return \Illuminate\Http\JsonResponse
     */
    public function getChallengeUsers(Challenge $challenge)
    {
        try {
            $start_date = Carbon::parse($challenge->start_date)->timezone($challenge->timezone);
            $end_date   = Carbon::parse($challenge->end_date)->timezone($challenge->timezone);

            $challengeType = 'individual';
            if ($challenge->challenge_type == 'individual') {
                if ($end_date > Carbon::now()->timezone($challenge->timezone)) {
                    $users = $challenge->members()
                        ->where('challenge_participants.status', 'Accepted')
                        ->orderBy('users.first_name', 'ASC')
                        ->paginate(config('zevolifesettings.datatable.pagination.short'));
                } else {
                    $users = $challenge->challengeHistoryParticipants()
                        ->orderBy('participant_name', 'ASC')
                        ->paginate(config('zevolifesettings.datatable.pagination.short'));
                }
            } elseif ($challenge->challenge_type == 'team' || $challenge->challenge_type == 'company_goal') {
                $challengeType = 'team';
                if ($end_date > Carbon::now()->timezone($challenge->timezone)) {
                    $users = $challenge->memberTeams()
                        ->where('challenge_participants.status', 'Accepted')
                        ->orderBy('teams.name', 'ASC')
                        ->paginate(config('zevolifesettings.datatable.pagination.short'));
                } else {
                    $users = $challenge->challengeHistoryParticipants()
                        ->orderBy('participant_name', 'ASC')
                        ->paginate(config('zevolifesettings.datatable.pagination.short'));
                }
            } elseif ($challenge->challenge_type == 'inter_company') {
                $challengeType = 'company';
                if ($end_date > Carbon::now()->timezone($challenge->timezone)) {
                    $users = $challenge->memberCompanies()
                        ->where('challenge_participants.status', 'Accepted')
                        ->groupBy('company_id')
                        ->orderBy('companies.name', 'ASC')
                        ->paginate(config('zevolifesettings.datatable.pagination.short'));
                } else {
                    $users = $challenge->challengeHistoryParticipants()
                        ->groupBy('company_id')
                        ->orderBy('participant_name', 'ASC')
                        ->paginate(config('zevolifesettings.datatable.pagination.short'));
                }
            }

            if ($users->count() > 0) {
                // collect required data and return response
                return $this->successResponse(new ChallengeUserListCollection($users, $challengeType), 'Challenge participants listed successfully.');
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
    public function leaderboard(Request $request, Challenge $challenge)
    {
        try {
            // logged-in user
            $user     = $this->user();
            $timezone = $user->timezone ?? config('app.timezone');

            $challengeHistory = $challenge->challengeHistory;

            if (!empty($challengeHistory)) {
                $challengeParticipantCount = $challenge->challengeHistoryParticipants()->count();

                if ($challenge->challenge_type == 'individual') {
                    $challengeParticipantsWithPoints = $challenge->challengeWiseUserPoints()
                        ->join('freezed_challenge_participents', 'freezed_challenge_participents.user_id', '=', 'challenge_wise_user_ponits.user_id')
                        ->select('freezed_challenge_participents.user_id', 'freezed_challenge_participents.participant_name', 'challenge_wise_user_ponits.challenge_id', 'challenge_wise_user_ponits.points', 'challenge_wise_user_ponits.rank')->where('freezed_challenge_participents.challenge_id', $challenge->id)
                        ->where('challenge_wise_user_ponits.challenge_id', $challenge->id)
                        ->where(DB::raw("round(challenge_wise_user_ponits.points,1)"), '>', 0)
                        ->orderBy('challenge_wise_user_ponits.rank', 'ASC')
                        ->orderBy('challenge_wise_user_ponits.user_id', 'ASC')
                        ->groupBy('challenge_wise_user_ponits.user_id');
                } elseif ($challenge->challenge_type == 'team' || $challenge->challenge_type == 'company_goal') {
                    $challengeParticipantsWithPoints = $challenge->challengeWiseTeamPoints()
                        ->join('freezed_challenge_participents', 'freezed_challenge_participents.team_id', '=', 'challenge_wise_team_ponits.team_id')
                        ->select('freezed_challenge_participents.team_id', 'freezed_challenge_participents.participant_name', 'challenge_wise_team_ponits.challenge_id', 'challenge_wise_team_ponits.points', 'challenge_wise_team_ponits.rank')->where('freezed_challenge_participents.challenge_id', $challenge->id)
                        ->where('challenge_wise_team_ponits.challenge_id', $challenge->id)
                        ->where(DB::raw("round(challenge_wise_team_ponits.points,1)"), '>', 0)
                        ->orderBy('challenge_wise_team_ponits.rank', 'ASC')
                        ->orderBy('challenge_wise_team_ponits.team_id', 'ASC')
                        ->groupBy('challenge_wise_team_ponits.team_id');
                } elseif ($challenge->challenge_type == 'inter_company') {
                    $challengeParticipantCount = $challenge->challengeHistoryParticipants()->distinct('company_id')->pluck('company_id')->count();

                    $challengeParticipantsWithPoints = $challenge->challengeWiseCompanyPoints()
                        ->join('freezed_challenge_participents', 'freezed_challenge_participents.company_id', '=', 'challenge_wise_company_points.company_id')
                        ->select('freezed_challenge_participents.company_id', 'freezed_challenge_participents.participant_name', 'challenge_wise_company_points.challenge_id', 'challenge_wise_company_points.points', 'challenge_wise_company_points.rank')
                        ->where('freezed_challenge_participents.challenge_id', $challenge->id)
                        ->where('challenge_wise_company_points.challenge_id', $challenge->id)
                        ->where(DB::raw("round(challenge_wise_company_points.points,1)"), '>', 0)
                        ->orderBy('challenge_wise_company_points.rank', 'ASC')
                        ->orderBy('challenge_wise_company_points.company_id', 'ASC')
                        ->groupBy('challenge_wise_company_points.company_id');
                }

                if ($challenge->challenge_type == 'individual') {
                    if ($challengeParticipantCount > 25) {
                        $challengeParticipantsWithPoints = $challengeParticipantsWithPoints->limit(10)->get();
                    } elseif ($challengeParticipantCount >= 10) {
                        $challengeParticipantsWithPoints = $challengeParticipantsWithPoints->limit(5)->get();
                    } elseif ($challengeParticipantCount < 10 && $challengeParticipantCount >= 2) {
                        $challengeParticipantsWithPoints = $challengeParticipantsWithPoints->limit(3)->get();
                    }
                } else {
                    $challengeParticipantsWithPoints = $challengeParticipantsWithPoints->get();
                }

                $challengeInfo = [
                    'id'      => $challenge->id,
                    'title'   => $challenge->title,
                    'members' => $challengeParticipantCount,
                    'type'    => $challenge->challenge_type,
                ];

                if ($challengeParticipantsWithPoints->count() > 0) {
                    $participantsData = [];
                    foreach ($challengeParticipantsWithPoints as $key => $record) {
                        $participant = [];

                        if ($challenge->challenge_type == 'individual') {
                            $participantId = $record->user_id;
                            $userRecord    = \App\Models\User::find($record->user_id);
                        } elseif ($challenge->challenge_type == 'team' || $challenge->challenge_type == 'company_goal') {
                            $participantId = $record->team_id;
                            $userRecord    = \App\Models\Team::find($record->team_id);
                        } elseif ($challenge->challenge_type == 'inter_company') {
                            $participantId = $record->company_id;
                            $userRecord    = \App\Models\Company::find($record->company_id);
                        }

                        $participant['id']   = $participantId;
                        $participant['name'] = $record->participant_name;

                        $participant['deleted'] = false;
                        if (!empty($userRecord)) {
                            if ($challenge->challenge_type == 'inter_company') {
                                $participant['name'] = $userRecord->name;
                            }

                            $participant['image'] = $userRecord->getMediaData('logo', ['w' => 320, 'h' => 320]);
                        } else {
                            $img           = [];
                            $img['url']    = "";
                            $img['width']  = 0;
                            $img['height'] = 0;

                            $participant['name']    = 'Deleted';
                            $participant['image']   = (object) $img;
                            $participant['deleted'] = true;
                        }

                        array_push($participantsData, [
                            'user'   => $participant,
                            'points' => (float) number_format((float) $record->points, 1, '.', ''),
                            'rank'   => $record->rank,
                        ]);
                    }

                    // collect required data and return response
                    return $this->successResponse(['data' => $participantsData, 'challenge' => $challengeInfo], 'Leaderboard data retrieved successfully.');
                } else {
                    // return empty response
                    return $this->successResponse(['data' => [], 'challenge' => $challengeInfo], 'No results');
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
    public function myTeamsLeaderboard(Request $request, Challenge $challenge)
    {
        if ($challenge->challenge_type != 'inter_company') {
            abort(403);
        }

        try {
            // logged-in user
            $user     = $this->user();
            $timezone = $user->timezone ?? config('app.timezone');

            $challengeHistory = $challenge->challengeHistory;

            if (!empty($challengeHistory)) {
                $challengeParticipantCount = $challenge->challengeHistoryParticipants()->distinct('company_id')->pluck('company_id')->count();

                $challengeParticipantsWithPoints = $challenge->challengeWiseTeamPoints()
                    ->join('freezed_challenge_participents', 'freezed_challenge_participents.team_id', '=', 'challenge_wise_team_ponits.team_id')
                    ->select('freezed_challenge_participents.team_id', 'freezed_challenge_participents.participant_name', 'challenge_wise_team_ponits.challenge_id', 'challenge_wise_team_ponits.points', 'challenge_wise_team_ponits.rank')
                    ->where('freezed_challenge_participents.challenge_id', $challenge->id)
                    ->where('challenge_wise_team_ponits.challenge_id', $challenge->id)
                    ->where('freezed_challenge_participents.company_id', $user->company()->first()->id)
                    ->where(DB::raw("round(challenge_wise_team_ponits.points,1)"), '>', 0)
                    ->orderBy('challenge_wise_team_ponits.rank', 'ASC')
                    ->orderBy('challenge_wise_team_ponits.team_id', 'ASC')
                    ->groupBy('challenge_wise_team_ponits.team_id')
                    ->get();

                $challengeInfo = [
                    'id'      => $challenge->id,
                    'title'   => $challenge->title,
                    'members' => $challengeParticipantCount,
                    'type'    => $challenge->challenge_type,
                ];

                if ($challengeParticipantsWithPoints->count() > 0) {
                    $participantsData = [];
                    foreach ($challengeParticipantsWithPoints as $key => $record) {
                        $participant = [];

                        $participantId = $record->team_id;
                        $companyRecord = \App\Models\Team::find($record->team_id);

                        $participant['id'] = $participantId;

                        $participant['deleted'] = false;
                        if (!empty($companyRecord)) {
                            $participant['name']  = $companyRecord->name;
                            $participant['image'] = $companyRecord->getMediaData('logo', ['w' => 320, 'h' => 320]);
                        } else {
                            $img           = [];
                            $img['url']    = "";
                            $img['width']  = 0;
                            $img['height'] = 0;

                            $participant['name']    = 'Deleted';
                            $participant['image']   = (object) $img;
                            $participant['deleted'] = true;
                        }

                        array_push($participantsData, [
                            'user'   => $participant,
                            'points' => (float) number_format((float) $record->points, 1, '.', ''),
                            'rank'   => $record->rank,
                        ]);
                    }

                    // collect required data and return response
                    return $this->successResponse(['data' => $participantsData, 'challenge' => $challengeInfo], 'Leaderboard data retrieved successfully.');
                } else {
                    // return empty response
                    return $this->successResponse(['data' => [], 'challenge' => $challengeInfo], 'No results');
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
     * Get Company teams
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCompanyTeams(Request $request, Challenge $challenge)
    {
        if ($challenge->challenge_type != 'inter_company') {
            abort(403);
        }

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
                // completed challenge detail resource
                $challengeTeamIds = $challenge
                    ->leftJoin("freezed_challenge_participents", function ($join) use ($company) {
                        $join->on("challenges.id", "=", "freezed_challenge_participents.challenge_id")
                            ->where('freezed_challenge_participents.company_id', $company->id);
                    })
                    ->select('freezed_challenge_participents.team_id')
                    ->where('challenges.id', $challenge->getKey())
                    ->where("challenges.cancelled", false)
                    ->paginate(config('zevolifesettings.datatable.pagination.short'));

                if (!empty($challengeTeamIds)) {
                    return $this->successResponse(
                        ($challengeTeamIds->count() > 0) ? new ChallengeCompanyTeamDetailsCollection($challengeTeamIds) : ['data' => []],
                        ($challengeTeamIds->count() > 0) ? 'Company teams list retrieved successfully.' : 'No company teams found'
                    );
                } else {
                    // return empty response
                    return $this->successResponse(['data' => []], 'No results');
                }
            } else {
                // completed challenge detail resource
                $challengeTeamIds = $challenge
                    ->leftJoin("challenge_participants", function ($join) use ($company) {
                        $join->on("challenges.id", "=", "challenge_participants.challenge_id")
                            ->where('challenge_participants.company_id', $company->id);
                    })
                    ->select('challenge_participants.team_id')
                    ->where('challenges.id', $challenge->getKey())
                    ->where("challenges.cancelled", false)
                    ->paginate(config('zevolifesettings.datatable.pagination.short'));

                if (!empty($challengeTeamIds)) {
                    return $this->successResponse(
                        ($challengeTeamIds->count() > 0) ? new ChallengeCompanyTeamDetailsCollection($challengeTeamIds) : ['data' => []],
                        ($challengeTeamIds->count() > 0) ? 'Company teams list retrieved successfully.' : 'No company teams found'
                    );
                } else {
                    // return empty response
                    return $this->successResponse(['data' => []], 'No results');
                }
            }
        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * Get winners list for the challenge.
     *
     * @param Challenge $challenge
     * @return \Illuminate\Http\JsonResponse
     */
    public function getWinnersList(Challenge $challenge)
    {
        try {
            // logged-in user
            $user     = $this->user();
            $timezone = $user->timezone ?? config('app.timezone');

            if ($challenge->challenge_type == 'individual') {
                $winners = \App\Models\ChallengeWiseUserLogData::with('user')
                    ->where('challenge_wise_user_log.challenge_id', $challenge->id)
                    ->where('challenge_wise_user_log.is_winner', 1)
                    ->get();
            } elseif ($challenge->challenge_type == 'team' || $challenge->challenge_type == 'company_goal') {
                $winners = \App\Models\ChallengeWiseUserLogData::with('team')
                    ->where('challenge_wise_user_log.challenge_id', $challenge->id)
                    ->where('challenge_wise_user_log.is_winner', 1)
                    ->groupBy('challenge_wise_user_log.team_id')
                    ->distinct('challenge_wise_user_log.team_id')
                    ->get();
            } elseif ($challenge->challenge_type == 'inter_company') {
                $winners = \App\Models\ChallengeWiseUserLogData::with('company')
                    ->where('challenge_wise_user_log.challenge_id', $challenge->id)
                    ->where('challenge_wise_user_log.is_winner', 1)
                    ->groupBy('challenge_wise_user_log.company_id')
                    ->distinct('challenge_wise_user_log.company_id')
                    ->get();
            }

            if ($winners->count() > 0) {
                $winnerData = $winners->map(function ($query) use ($challenge) {

                    if ($challenge->challenge_type == 'individual') {
                        $winnerData['id'] = $query->user_id;
                        $userRecord       = \App\Models\User::find($query->user_id);
                        if (!empty($userRecord)) {
                            $winnerData['name'] = $userRecord->first_name . ' ' . $userRecord->last_name;
                        }
                    } elseif ($challenge->challenge_type == 'team' || $challenge->challenge_type == 'company_goal') {
                        $winnerData['id'] = $query->team_id;
                        $userRecord       = \App\Models\Team::find($query->team_id);
                        if (!empty($userRecord)) {
                            $winnerData['name'] = $userRecord->name;
                        }
                    } elseif ($challenge->challenge_type == 'inter_company') {
                        $winnerData['id'] = $query->company_id;
                        $userRecord       = \App\Models\Company::find($query->company_id);
                        if (!empty($userRecord)) {
                            $winnerData['name'] = $userRecord->name;
                        }
                    }

                    $winnerData['deleted'] = false;
                    if (!empty($userRecord)) {
                        $winnerData['image'] = $userRecord->getMediaData('logo', ['w' => 320, 'h' => 320]);
                    } else {
                        $image           = [];
                        $image['url']    = "";
                        $image['width']  = 0;
                        $image['height'] = 0;

                        $winnerData['name']    = 'Deleted';
                        $winnerData['image']   = (object) $image;
                        $winnerData['deleted'] = true;
                    }

                    return $winnerData;
                });

                // collect required data and return response
                return $this->successResponse(['data' => $winnerData], 'Winners list retrieved successfully.');
            } else {
                // return empty response
                return $this->successResponse(['data' => []], 'There was no winner of this challenge');
            }
        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * Get Team members
     *
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
                $challengeUserIds = $challenge
                    ->leftJoin("freezed_team_challenge_participents", function ($join) use ($user, $company) {
                        $join->on("challenges.id", "=", "freezed_team_challenge_participents.challenge_id")
                            ->where('freezed_team_challenge_participents.team_id', $user->teams()->first()->id);
                    })
                    ->select('freezed_team_challenge_participents.user_id')
                    ->where('challenges.id', $challenge->getKey())
                    ->where("challenges.cancelled", false)
                    ->paginate(config('zevolifesettings.datatable.pagination.short'));

                if (!empty($challengeUserIds)) {
                    return $this->successResponse(
                        ($challengeUserIds->count() > 0) ? new ChallengeTeamMembersDetailsCollection($challengeUserIds) : ['data' => []],
                        ($challengeUserIds->count() > 0) ? 'Team members list retrieved successfully.' : 'No team members found'
                    );
                } else {
                    // return empty response
                    return $this->successResponse(['data' => []], 'No results');
                }
            } else {
                $challengeUserIds = \DB::table('user_team')
                    ->where('user_team.team_id', $user->teams()->first()->id)
                    ->select('user_team.user_id')
                    ->paginate(config('zevolifesettings.datatable.pagination.short'));

                if (!empty($challengeUserIds)) {
                    return $this->successResponse(
                        ($challengeUserIds->count() > 0) ? new ChallengeTeamMembersDetailsCollection($challengeUserIds) : ['data' => []],
                        ($challengeUserIds->count() > 0) ? 'Team members list retrieved successfully.' : 'No team members found'
                    );
                } else {
                    // return empty response
                    return $this->successResponse(['data' => []], 'No results');
                }
            }
        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }
}
