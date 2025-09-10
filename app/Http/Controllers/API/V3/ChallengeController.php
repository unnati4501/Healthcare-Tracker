<?php declare (strict_types = 1);

namespace App\Http\Controllers\API\V3;

use App\Http\Controllers\API\V2\ChallengeController as v2ChallengeController;
use App\Models\Challenge;
use App\Models\ChallengeCategory;
use App\Models\ChallengeParticipant;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use App\Http\Collections\V3\ChallengeListCollection;
use App\Http\Collections\V3\UpcomingChallengeListCollection;
use App\Http\Resources\V3\FinishedChallengeDetailResource;
use App\Http\Collections\V3\ChallengeHistoryListCollection;
use App\Http\Resources\V3\ChallengeDetailsResource;

class ChallengeController extends v2ChallengeController
{
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
            })
            ->join("challenge_categories", "challenge_categories.id", "=", "challenges.challenge_category_id")
            ->select("challenges.*", DB::raw("COUNT(challenge_participants.user_id) as members"), "challenge_categories.name as challengeCatName", "challenge_categories.short_name as challengeCatShortName")
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
                $exploreChallengeData = $exploreChallengeData->where("challenge_categories.short_name", $request->slug);
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
}
