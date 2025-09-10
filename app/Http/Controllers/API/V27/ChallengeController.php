<?php declare (strict_types = 1);

namespace App\Http\Controllers\API\V27;

use App\Http\Collections\V20\ChallengeTeamMembersDetailsCollection;
use App\Http\Collections\V27\ChallengeListCollection;
use App\Http\Collections\V27\InvitationsListCollection;
use App\Http\Controllers\API\V26\ChallengeController as v26ChallengeController;
use App\Http\Requests\Api\V27\CancelChallengeRequest;
use App\Http\Resources\V27\ChallengeDetailsResource;
use App\Http\Resources\V27\FinishedChallengeDetailResource;
use App\Http\Traits\ProvidesAuthGuardTrait;
use App\Http\Traits\ServesApiTrait;
use App\Jobs\SendChallengePushNotification;
use App\Models\Challenge;
use App\Models\ChallengeParticipant;
use App\Models\Company;
use App\Models\Group;
use App\Models\User;
use Carbon\Carbon;
use DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ChallengeController extends v26ChallengeController
{
    use ServesApiTrait, ProvidesAuthGuardTrait;

    /**
     * get details of ongoing challenge woth user points and rank
     *
     * @param Request $request, Challenge $challenge
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
        } catch (\Illuminate\Database\QueryException $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong_try_again'),
                'status' => 0,
            ];
            return response()->json($messageData);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong_try_again'),
                'status' => 0,
            ];
            return response()->json($messageData);
        }
    }

    /**
     * Get challenge details.
     *
     * @param Request $request, Challenge $challenge
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDetails(Request $request, Challenge $challenge)
    {
        try {
            // logged-in user
            $user        = $this->user();
            $company     = $user->company()->first();
            $timezone    = $user->timezone ?? config('app.timezone');
            $checkAccess = getCompanyPlanAccess($user, 'my-challenges');

            if (!$checkAccess) {
                return $this->notFoundResponse('Challenge is disabled for this company.');
            }

            if ($challenge->close) {
                if ($challenge->challenge_type == 'individual') {
                    $mappingId = $challenge->members()->where('user_id', $user->id)->first();
                } else {
                    $mappingId = $challenge->memberTeams()->where('team_id', $company->pivot->team_id)->first();
                }

                if (empty($mappingId)) {
                    return $this->notFoundResponse("Sorry! Requested data not found");
                }
            }

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
                ->orderBy(DB::raw("TIMESTAMPDIFF(SECOND,'{$currentDateTime}',challenges.start_date)"))
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

    /**
     * Cancel challenge
     *
     * @param Request $request, Challenge $challenge
     * @return \Illuminate\Http\JsonResponse
     */
    public function cancelNewChallenge(CancelChallengeRequest $request, Challenge $challenge)
    {
        try {
            $loggedInUser = $this->user();

            // check user is creater of Challenge or not if user creater of Challenge then not allow to perform operation
            if ($challenge->creator_id != $loggedInUser->getKey()) {
                return $this->notFoundResponse("You are not authorized to perform this operation");
            }

            $currentDateTime = now($loggedInUser->timezone)->toDateTimeString();
            $endDate         = Carbon::parse($challenge->end_date, config('app.timezone'))->setTimezone($loggedInUser->timezone)->toDateTimeString();

            if ($currentDateTime > $endDate) {
                return $this->notFoundResponse("You can not cancel this challenge, Because challenge already completed.");
            }

            \DB::beginTransaction();

            $challenge->update(['cancelled' => true, 'deleted_by' => $loggedInUser->id, 'deleted_reason' => $request->reason]);

            $challenge->expireBadges();

            if ($challenge->recurring != 1 && empty($challenge->parent_id)) {
                Group::where('model_name', 'challenge')
                    ->where('model_id', $challenge->id)
                    ->delete();
            }

            \DB::commit();

            // dispatch job to SendChallengePushNotification
            $this->dispatch(new SendChallengePushNotification($challenge, 'challenge-cancelled'));

            return $this->successResponse([], trans('api_messages.challenge.cancel'));
        } catch (\Exception $e) {
            \DB::rollback();
        }
    }

    /**
     * Leaderboard
     *
     * @param Request $request, Challenge $challenge
     * @return \Illuminate\Http\JsonResponse
     */
    public function leaderboard(Request $request, Challenge $challenge)
    {

        try {
            // logged-in user
            $user     = $this->user();
            $company  = $user->company()->first();
            $userTeam = $user->teams()->first();
            $timezone = $user->timezone ?? config('app.timezone');

            $challengeHistory = $challenge->challengeHistory;

            if (!empty($challengeHistory)) {
                if ($challenge->challenge_type == 'individual') {
                    $challengeParticipantCount       = $challenge->membersHistory()->count(); //$challenge->membersHistory()->count();
                    $challengeParticipantsWithPoints = $challenge->challengeWiseUserPoints()
                        ->join('freezed_challenge_participents', 'freezed_challenge_participents.user_id', '=', 'challenge_wise_user_ponits.user_id')
                        ->select('freezed_challenge_participents.user_id', 'freezed_challenge_participents.participant_name', 'challenge_wise_user_ponits.challenge_id', 'challenge_wise_user_ponits.points', 'challenge_wise_user_ponits.rank')->where('freezed_challenge_participents.challenge_id', $challenge->id)
                        ->where('challenge_wise_user_ponits.challenge_id', $challenge->id)
                        ->where(DB::raw("round(challenge_wise_user_ponits.points,1)"), '>', 0)
                        ->orderBy('challenge_wise_user_ponits.rank', 'ASC')
                        ->orderBy('challenge_wise_user_ponits.user_id', 'ASC')
                        ->groupBy('challenge_wise_user_ponits.user_id');
                } elseif ($challenge->challenge_type == 'team' || $challenge->challenge_type == 'company_goal') {
                    $challengeParticipantCount       = $challenge->memberTeamsHistory()->distinct('team_id')->count();
                    $challengeParticipantsWithPoints = $challenge->challengeWiseTeamPoints()
                        ->join('freezed_challenge_participents', 'freezed_challenge_participents.team_id', '=', 'challenge_wise_team_ponits.team_id')
                        ->select(
                            'freezed_challenge_participents.team_id',
                            'freezed_challenge_participents.participant_name',
                            'challenge_wise_team_ponits.challenge_id',
                            'challenge_wise_team_ponits.points',
                            'challenge_wise_team_ponits.rank'
                        )
                        ->where('freezed_challenge_participents.challenge_id', $challenge->id)
                        ->where('challenge_wise_team_ponits.challenge_id', $challenge->id)
                        ->where(DB::raw("round(challenge_wise_team_ponits.points,1)"), '>', 0)
                        ->orderBy('challenge_wise_team_ponits.rank', 'ASC')
                        ->orderBy('challenge_wise_team_ponits.team_id', 'ASC')
                        ->groupBy('challenge_wise_team_ponits.team_id');
                } elseif ($challenge->challenge_type == 'inter_company') {
                    $challengeParticipantCount       = $challenge->memberCompaniesHistory()->distinct('company_id')->pluck('company_id')->count();
                    $challengeParticipantsWithPoints = $challenge->challengeWiseCompanyPoints()
                        ->join('freezed_challenge_participents', 'freezed_challenge_participents.company_id', '=', 'challenge_wise_company_points.company_id')
                        ->select(
                            'freezed_challenge_participents.company_id',
                            'freezed_challenge_participents.participant_name',
                            'challenge_wise_company_points.challenge_id',
                            'challenge_wise_company_points.points',
                            'challenge_wise_company_points.rank'
                        )
                        ->where('freezed_challenge_participents.challenge_id', $challenge->id)
                        ->where('challenge_wise_company_points.challenge_id', $challenge->id)
                        ->where(DB::raw("round(challenge_wise_company_points.points,1)"), '>', 0)
                        ->orderBy('challenge_wise_company_points.rank', 'ASC')
                        ->orderBy('challenge_wise_company_points.company_id', 'ASC')
                        ->groupBy('challenge_wise_company_points.company_id');
                }

                // if ($challenge->challenge_type == 'individual') {
                //     if ($challengeParticipantCount > 25) {
                //         $challengeParticipantsWithPoints = $challengeParticipantsWithPoints->limit(10)->get();
                //     } elseif ($challengeParticipantCount >= 10) {
                //         $challengeParticipantsWithPoints = $challengeParticipantsWithPoints->limit(5)->get();
                //     } elseif ($challengeParticipantCount < 10 && $challengeParticipantCount >= 2) {
                //         $challengeParticipantsWithPoints = $challengeParticipantsWithPoints->limit(3)->get();
                //     }
                // } else {
                //     $challengeParticipantsWithPoints = $challengeParticipantsWithPoints->get();
                // }

                $challengeParticipantsWithPoints = $challengeParticipantsWithPoints->get();

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
                            $img['url']    = getDefaultFallbackImageURL("user", "user-none1");
                            $img['width']  = 0;
                            $img['height'] = 0;

                            if ($challenge->challenge_type == 'individual') {
                                $deletedText = 'Deleted User';
                            } elseif ($challenge->challenge_type == 'team' || $challenge->challenge_type == 'company_goal') {
                                $deletedText = 'Deleted Team';
                            } elseif ($challenge->challenge_type == 'inter_company') {
                                $deletedText = 'Deleted Company';
                            }

                            $participant['name']    = $deletedText;
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
                    ->groupBy('challenge_wise_user_ponits.user_id')
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
