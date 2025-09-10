<?php declare (strict_types = 1);

namespace App\Http\Controllers\API\V43;

use App\Http\Controllers\API\V34\ChallengeController as v34ChallengeController;
use App\Http\Resources\V43\FinishedChallengeDetailResource;
use App\Http\Traits\ProvidesAuthGuardTrait;
use App\Http\Traits\ServesApiTrait;
use App\Models\Challenge;
use App\Models\Company;
use App\Models\User;
use DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChallengeController extends v34ChallengeController
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
            $checkAccess     = getCompanyPlanAccess($user, 'my-challenges');

            if (!$checkAccess) {
                return $this->notFoundResponse('Challenge is disabled for this company.');
            }

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
                    DB::raw("COUNT(challenge_participants.user_id) as members")
                )
                ->selectRaw("(CASE
                        WHEN (CONVERT_TZ(challenges.start_date, 'UTC', ?) < CONVERT_TZ(now(), 'UTC', ?)) THEN 'ongoing'
                        WHEN ((CONVERT_TZ(challenges.start_date, 'UTC', ?) > CONVERT_TZ(now(), 'UTC', ?)) AND challenges.id NOT IN (?)) THEN 'open'
                        WHEN (CONVERT_TZ(challenges.start_date, 'UTC', ?) > CONVERT_TZ(now(), 'UTC', ?)) THEN 'upcoming'
                    END) AS chStatus",[
                        $timezone,$timezone,$timezone,$timezone,$implodedChallengeIds,$timezone,$timezone
                    ])
                ->selectRaw("IF((CONVERT_TZ(now(), 'UTC', ?) > CONVERT_TZ(challenges.end_date, 'UTC', ?)), 1, 0) AS challenge_enddate_order",[
                    $timezone,$timezone
                ])
                ->where(function ($query) use ($user, $company, $challengeIds, $timezone) {
                    $query->where(function ($subQuery) use ($user, $challengeIds, $timezone) {
                        $subQuery->where('challenges.challenge_type', 'individual')
                            ->where(function ($subQuery1) use ($user, $challengeIds, $timezone) {
                                $subQuery1->where(function ($subQuery2) use ($challengeIds) {
                                    $subQuery2->whereIn("challenges.id", $challengeIds);
                                })->orWhere(function ($subQuery2) use ($timezone) {
                                    $subQuery2->where("challenges.close", false)
                                        ->whereRaw("CONVERT_TZ(challenges.start_date, 'UTC', ?) > ?",[
                                            $timezone,now($timezone)->toDateTimeString()
                                        ]);
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
                ->whereRaw("CONVERT_TZ(DATE_ADD(challenges.end_date, INTERVAL 1 DAY), 'UTC', ?) > ?",[
                    $timezone,now($timezone)->toDateTimeString()
                ]);

            if (!empty($request->slug) && strtolower($request->slug) != 'all') {
                $exploreChallengeData = $exploreChallengeData->where("challenge_categories.short_name", $request->slug);
            }

            $exploreChallengeData = $exploreChallengeData
                ->orderBy('challenge_enddate_order', 'ASC')
                ->orderByRaw("FIELD(chStatus, 'ongoing', 'upcoming', 'open')")
                ->orderByRaw("CASE
                        WHEN chStatus = 'ongoing' THEN TIMESTAMPDIFF(SECOND, ? ,challenges.end_date)
                        WHEN chStatus = 'upcoming' THEN TIMESTAMPDIFF(SECOND, ? ,challenges.start_date)
                        WHEN chStatus = 'open' THEN TIMESTAMPDIFF(SECOND,?,challenges.start_date)
                        END",[
                            $currentDateTime,$currentDateTime,$currentDateTime
                        ])
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
                ->whereRaw("CONVERT_TZ(DATE_ADD(challenges.end_date, INTERVAL 1 DAY), 'UTC', ?) < ?",[
                    $timezone,now($timezone)->toDateTimeString()
                ])
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
                ->whereRaw("CONVERT_TZ(challenges.start_date, 'UTC', ?) > ?",[
                    $timezone,now($timezone)->toDateTimeString()
                ])
                ->where("challenges.cancelled", false)
                ->where("challenges.challenge_type", 'individual')
                ->orderByRaw(DB::raw("TIMESTAMPDIFF(SECOND,?,challenges.start_date)"),[
                    $currentDateTime
                ])
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
                ->select("challenges.*", "challengeMember.members")
                ->selectRaw(" ( TIMESTAMPDIFF(HOUR , CONVERT_TZ(challenges.start_date, 'UTC', ?) , ?) * 100) / TIMESTAMPDIFF(HOUR , CONVERT_TZ(challenges.start_date, 'UTC', ?) , CONVERT_TZ(challenges.end_date, 'UTC', ?)) as completedPer ",[
                    $timezone,$now,$timezone,$timezone
                ])
                ->whereRaw("CONVERT_TZ(challenges.start_date, 'UTC', ?) <= ?",[
                    $timezone,now($timezone)->toDateTimeString()
                ])
                ->whereRaw("CONVERT_TZ(challenges.end_date, 'UTC', ?) >= ?",[
                    $timezone,now($timezone)->toDateTimeString()
                ])
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
                ->whereRaw("CONVERT_TZ(challenges.start_date, 'UTC', ?) > ?",[
                    $timezone,now($timezone)->toDateTimeString()
                ])
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
                ->join("challenge_categories", "challenge_categories.id", "=", "challenges.challenge_category_id")

                ->select("challenges.*", "challengeMember.members", "challenge_categories.name as challengeCatName", "challenge_categories.short_name as challengeCatShortName")
                ->whereRaw("CONVERT_TZ(challenges.start_date, 'UTC', ?) <= ?",[
                    $timezone,now($timezone)->toDateTimeString()
                ])
                ->whereRaw("CONVERT_TZ(challenges.end_date, 'UTC', ?) >= ?",[
                    $timezone,now($timezone)->toDateTimeString()
                ])
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
}