<?php declare (strict_types = 1);

namespace App\Http\Controllers\API\V6;

use App\Http\Controllers\API\V5\ChallengeController as v5ChallengeController;
use App\Http\Resources\V5\ChallengeDetailsResource;
use App\Http\Resources\V6\FinishedChallengeDetailResource;
use App\Http\Traits\ProvidesAuthGuardTrait;
use App\Http\Traits\ServesApiTrait;
use App\Jobs\SendChallengePushNotification;
use App\Models\Challenge;
use App\Models\ChallengeParticipant;
use App\Models\Company;
use App\Models\Group;
use App\Models\UserExercise;
use App\Models\UserListenedTrack;
use App\Models\UserStep;
use Carbon\Carbon;
use DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChallengeController extends v5ChallengeController
{
    use ServesApiTrait, ProvidesAuthGuardTrait;

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
            $company  = $user->company()->first();
            $timezone = $user->timezone ?? config('app.timezone');

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
                $challengeExecuteStatus = $this->checkExecutionRequired($challenge);
                if ($challengeExecuteStatus ) {
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

    private function checkExecutionRequired($challenge)
    {
        $challengeExecuteStatus = false;

        if (!empty($challenge->freezed_data_at)) {
            $challengeRulesData = $challenge->challengeRules()->join('challenge_targets', 'challenge_targets.id', '=', 'challenge_rules.challenge_target_id')->select('challenge_rules.*', 'challenge_targets.short_name', 'challenge_targets.name')->get();

            $userIds = array();

            if ($challenge->challenge_type == "individual") {
                $userIds = $challenge->members->pluck('id')->toArray();
            } else {
                $userIds = ChallengeParticipant::join('user_team', 'user_team.team_id', '=', 'challenge_participants.team_id')
                    ->where("challenge_participants.challenge_id", $challenge->id)
                    ->pluck('user_team.user_id')
                    ->toArray();
            }

            if (!empty($userIds)) {
                $insertedStepsData = false;
                foreach ($challengeRulesData as $outer => $rule) {
                    $checkDataSync = array();
                    if (($rule->short_name == 'distance' || $rule->short_name == 'steps') && !$insertedStepsData) {
                        $insertedStepsData = true;
                        $checkDataSync     = UserStep::whereIn("user_id", $userIds)
                            ->where("created_at", ">=", $challenge->freezed_data_at)
                            ->first();
                    } elseif ($rule->short_name == 'meditations') {
                        $checkDataSync = UserListenedTrack::whereIn("user_id", $userIds)
                            ->where("created_at", ">=", $challenge->freezed_data_at)
                            ->first();
                    } elseif ($rule->short_name == 'exercises' && $rule->model_name == 'Exercise') {
                        $checkDataSync = UserExercise::whereIn("user_id", $userIds)
                            ->where("created_at", ">=", $challenge->freezed_data_at)
                            ->where('user_exercise.exercise_id', $rule->model_id)
                            ->first();
                    }

                    if (!empty($checkDataSync)) {
                        $challengeExecuteStatus = true;
                    }
                }
            }
        } else {
            $challengeExecuteStatus = true;
        }

        return $challengeExecuteStatus;
    }

    /**
     * Join challenge API
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function joinToChallenge(Request $request, Challenge $challenge)
    {
        try {
            \DB::beginTransaction();
            // logged-in user
            $user = $this->user();

            $currentDateTime = now($user->timezone)->toDateTimeString();
            $startDate       = Carbon::parse($challenge->start_date, config('app.timezone'))->setTimezone($user->timezone)->toDateTimeString();

            if ($currentDateTime >= $startDate) {
                return $this->notFoundResponse("You can not join this challenge, Because challenge already started.");
            }

            if ($challenge->cancelled) {
                return $this->notFoundResponse("You can not join this challenge, Because challenge already cancelled.");
            }

            if (!$challenge->close) {
                $isChallengeMember = $challenge->members()->wherePivot('challenge_id', $challenge->id)->wherePivot('user_id', $user->id)->first();

                if (empty($isChallengeMember)) {
                    $challenge->members()->attach($user->id);
                    \DB::commit();

                    if ($challenge->members()->where('status', 'Accepted')->count() >= 5) {
                        $groupExists = Group::where('model_id', $challenge->id)
                            ->where('model_name', 'challenge')
                            ->first();

                        $members = $challenge->members()->where('status', 'Accepted')->get()->pluck('id')->toArray();
                        if (!empty($groupExists)) {
                            $membersInput   = [];
                            $membersInput[] = [
                                'user_id'     => 1,
                                'group_id'    => $groupExists->id,
                                'status'      => "Accepted",
                                'joined_date' => now()->toDateTimeString(),
                            ];
                            foreach ($members as $key => $value) {
                                $membersInput[$value] = [
                                    'user_id'     => $value,
                                    'group_id'    => $groupExists->id,
                                    'status'      => "Accepted",
                                    'joined_date' => now()->toDateTimeString(),
                                ];
                            }
                            $groupExists->members()->sync($membersInput);
                        } else {
                            /**
                             * BUG#ZL-3115
                             *
                             * Commenting below group creation and assigned notification block as this will generate group and send
                             * notification to users before challenge get started
                             *
                             * Now group will be formed automatically through cron(system:groups)
                             */
                            // $subCategory = SubCategory::where('short_name', 'challenge')->first();

                            // $groupPayload = [
                            //     'name'             => $challenge->title,
                            //     'category'         => $subCategory->id,
                            //     'introduction'     => $challenge->description,
                            //     'members_selected' => $members,
                            //     'model_id'         => $challenge->id,
                            //     'model_name'       => 'challenge',
                            //     'is_visible'       => 0,
                            // ];

                            // $groupModel = new Group();
                            // $group      = $groupModel->storeEntity($groupPayload);

                            // if (!empty($group)) {
                            //     if (!empty($challenge->getFirstMediaUrl('logo'))) {
                            //         $media     = $challenge->getFirstMedia('logo');
                            //         $imageData = explode(".", $media->file_name);
                            //         $name      = $group->id . '_' . \time();
                            //         $group->clearMediaCollection('logo')
                            //             ->addMediaFromUrl(
                            //                 $challenge->getFirstMediaUrl('logo'),
                            //                 $challenge->getAllowedMediaMimeTypes('image')
                            //             )
                            //             ->usingName($media->name)
                            //             ->usingFileName($name . '.' . $imageData[1])
                            //             ->toMediaCollection('logo', config('medialibrary.disk_name'));
                            //     }

                            //     \dispatch(new SendGroupPushNotification($group, "user-assigned-group"));
                            // }
                        }
                    }

                    return $this->successResponse([], trans('api_messages.challenge.join'));
                } else {
                    return $this->notFoundResponse("You are already member of this challenge");
                }
            } else {
                return $this->notFoundResponse("Requested challenge is not open challenge");
            }
        } catch (\Exception $e) {
            \DB::rollback();
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * Leave challenge API.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function leaveChallenge(Request $request, Challenge $challenge)
    {
        try {
            \DB::beginTransaction();
            // logged-in user
            $loggedInUser = $this->user();
            $userName     = $loggedInUser->full_name;

            // check user is creater of Challenge or not if user creater of Challenge then not allow to perform operation
            if ($challenge->creator_id == $loggedInUser->getKey()) {
                return $this->notFoundResponse("You are not authorized to perform this operation");
            }

            $currentDateTime = now($loggedInUser->timezone)->toDateTimeString();
            $endDate         = Carbon::parse($challenge->end_date, config('app.timezone'))->setTimezone($loggedInUser->timezone)->toDateTimeString();

            if ($currentDateTime > $endDate) {
                return $this->notFoundResponse("You can not leave this challenge, Because challenge already completed.");
            }

            $challenge->members()->detach([$loggedInUser->id]);

            $group = Group::where('model_name', 'challenge')
                ->where('model_id', $challenge->id)
                ->first();

            if (!empty($group)) {
                $group->members()->detach([$loggedInUser->id]);
                if ($group->members()->count() < 2) {
                    $group->delete();
                }
            }

            \DB::commit();

            // dispatch job to SendChallengePushNotification
            // $this->dispatch(new SendChallengePushNotification($challenge, 'challenge-left', $userName));

            $membersCount = $challenge->members()->wherePivot('status', 'Accepted')->count();

            return $this->successResponse(['data' => ['members' => $membersCount]], trans('api_messages.challenge.leave'));
        } catch (\Exception $e) {
            \DB::rollback();
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * Cancel challenge
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function cancelChallenge(Request $request, Challenge $challenge)
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

            $challenge->update(['cancelled' => true]);

            $challenge->expireBadges();

            Group::where('model_name', 'challenge')
                ->where('model_id', $challenge->getKey())
                ->delete();

            \DB::commit();

            // dispatch job to SendChallengePushNotification
            $this->dispatch(new SendChallengePushNotification($challenge, 'challenge-cancelled'));

            return $this->successResponse([], trans('api_messages.challenge.cancel'));
        } catch (\Exception $e) {
            \DB::rollback();
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }
}
