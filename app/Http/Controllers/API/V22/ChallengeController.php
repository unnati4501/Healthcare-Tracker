<?php declare (strict_types = 1);

namespace App\Http\Controllers\API\V22;

use App\Http\Controllers\API\V20\ChallengeController as v20ChallengeController;
use App\Http\Requests\Api\V1\ChallengeCreateRequest;
use App\Http\Resources\V22\ChallengeDetailsResource;
use App\Http\Resources\V22\FinishedChallengeDetailResource;
use App\Http\Traits\ProvidesAuthGuardTrait;
use App\Http\Traits\ServesApiTrait;
use App\Jobs\SendChallengePushNotification;
use App\Models\Badge;
use App\Models\Challenge;
use App\Models\ChallengeTarget;
use App\Models\Company;
use App\Models\Exercise;
use App\Models\Group;
use App\Models\User;
use Carbon\Carbon;
use DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChallengeController extends v20ChallengeController
{
    use ServesApiTrait, ProvidesAuthGuardTrait;

    /**
     * Create challenge from app
     *
     * @param ChallengeCreateRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(ChallengeCreateRequest $request)
    {
        try {
            DB::beginTransaction();

            $user         = $this->user();
            $userTimeZone = $user->timezone;
            $appTimeZone  = config('app.timezone');
            $company_id   = !is_null($user->company->first()) ? $user->company->first()->id : null;

            $rules   = (!empty($request->rules)) ? json_decode($request->rules, true) : [];
            $members = (!empty($request->users)) ? json_decode($request->users, true) : [];
            $badges  = (!empty($request->badges)) ? json_decode($request->badges, true) : [];

            if (count($members) > 50) {
                DB::rollback();
                return $this->invalidResponse([], "Maximum 50 participants are allowed including you.");
            } else {
                $selectedMembers = User::find($members);
                if (count($selectedMembers) != count($members)) {
                    DB::rollback();
                    return $this->invalidResponse([], "Some of selected users not found.");
                }
            }

            if (count($badges) > 10) {
                DB::rollback();
                return $this->invalidResponse([], "Maximum 10 badges are allowed.");
            } else {
                $selectedBadges = Badge::find($badges);
                if (count($selectedBadges) != count($badges)) {
                    DB::rollback();
                    return $this->invalidResponse([], "Some of selected badges not found.");
                }
            }

            $challenges_rule = array();
            if (!empty($rules)) {
                if (count($rules) > 2) {
                    DB::rollback();
                    return $this->invalidResponse([], "Maximum 2 targets are allowed.");
                }

                foreach ($rules as $key => $value) {
                    if (!empty($value['targetId'])) {
                        $target = ChallengeTarget::find($value['targetId']);
                        if (empty($target)) {
                            DB::rollback();
                            return $this->notFoundResponse("Target not found.");
                        }

                        if (!empty($value['exerciseId'])) {
                            $exercise = Exercise::find($value['exerciseId']);
                            if (empty($exercise)) {
                                DB::rollback();
                                return $this->notFoundResponse("Exercise not found.");
                            }
                        }
                    } else {
                        DB::rollback();
                        return $this->invalidResponse([], "Target field is required.");
                    }

                    $challenges_rule[$key]['challenge_category_id'] = $request->categoryId;
                    $challenges_rule[$key]['challenge_target_id']   = $value['targetId'];
                    $challenges_rule[$key]['target']                = $value['value'];
                    $challenges_rule[$key]['uom']                   = $value['uom'];

                    if ($target->short_name == 'exercises') {
                        $challenges_rule[$key]['model_id']   = $value['exerciseId'];
                        $challenges_rule[$key]['model_name'] = 'Exercise';
                    }
                }
            }

            $startDate = Carbon::parse($request->startDate, $userTimeZone)->setTime(0, 0, 0)->setTimezone($appTimeZone);

            $endDate = Carbon::parse($request->endDate, $userTimeZone)->setTime(23, 59, 59)->setTimezone($appTimeZone);

            $insertData                          = array();
            $insertData['creator_id']            = $user->id;
            $insertData['company_id']            = $company_id;
            $insertData['timezone']              = $userTimeZone;
            $insertData['start_date']            = $startDate;
            $insertData['end_date']              = $endDate;
            $insertData['challenge_end_at']      = $endDate;
            $insertData['challenge_category_id'] = $request->categoryId;
            $insertData['title']                 = $request->title;
            $insertData['description']           = $request->description;
            $insertData['close']                 = true;
            $insertData['challenge_type']        = 'individual';

            $record = Challenge::create($insertData);

            // add challenge logo image if not empty
            if ($request->hasFile('image')) {
                $name = $record->getKey() . '_' . \time();
                $record
                    ->clearMediaCollection('logo')
                    ->addMediaFromRequest('image')
                    ->usingName($request->file('image')->getClientOriginalName())
                    ->usingFileName($name . '.' . $request->file('image')->extension())
                    ->toMediaCollection('logo', config('medialibrary.disk_name'));
            } else {
                $record->library_image_id = $request->imageId;
                $record->save();
            }

            foreach ($challenges_rule as $key => $value) {
                $record->challengeRules()->create($value);
            }

            // array_unshift($members, $user->id);
            $record->members()->attach($user->id);
            foreach ($members as $key => $value) {
                if ($value != $user->id) {
                    $record->members()->attach($value, ["status" => "Pending"]);
                }
            }

            if (!empty($badges)) {
                $record->challengeBadges()->attach($badges);
            }

            DB::commit();

            $record->autoGenerateGroups();
            // dispatch job to SendChallengePushNotification
            $this->dispatch(new SendChallengePushNotification($record, 'challenge-invitation', '', $members));

            return $this->successResponse([], trans('api_messages.challenge.create'));
        } catch (\Exception $e) {
            DB::rollback();
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
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
     * Accept/Reject Challenge created by app user.
     *
     * @param Request $request, Challenge $challenge
     * @return \Illuminate\Http\JsonResponse
     */
    public function changeInvitationStatus(Request $request, Challenge $challenge)
    {
        try {
            \DB::beginTransaction();

            if ($challenge->cancelled) {
                return $this->notFoundResponse("The challenge has been cancelled.");
            }

            // logged-in user
            $user     = $this->user();
            $userName = $user->full_name;

            $currentDateTime = now($user->timezone)->toDateTimeString();
            $startDate       = Carbon::parse($challenge->start_date, config('app.timezone'))->setTimezone($user->timezone)->toDateTimeString();

            if ($currentDateTime >= $startDate) {
                return $this->notFoundResponse("You can not perform any operation, Because challenge already started.");
            }

            $isChallengeMember = $challenge->members()->wherePivot('challenge_id', $challenge->id)->wherePivot('user_id', $user->id)->first();

            if (!empty($isChallengeMember)) {
                if ($request->status == 'accepted') {
                    $isChallengeMember->pivot->status = 'Accepted';
                    $isChallengeMember->pivot->save();
                } else {
                    $challenge->members()->detach([$user->id]);
                }

                \DB::commit();

                if ($request->status == 'accepted') {
                    $challenge->autoGenerateGroups();
                    // dispatch job to SendChallengePushNotification
                    $this->dispatch(new SendChallengePushNotification($challenge, 'challenge-invitation-accepted', $userName));

                    return $this->successResponse([], trans('api_messages.challenge.accept'));
                } else {
                    // dispatch job to SendChallengePushNotification
                    // $this->dispatch(new SendChallengePushNotification($challenge, 'challenge-invitation-rejected', $userName));

                    return $this->successResponse([], trans('api_messages.challenge.reject'));
                }
            } else {
                return $this->notFoundResponse("You are not authorized to perform this operation.");
            }
        } catch (\Exception $e) {
            \DB::rollback();
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * Join challenge API
     *
     * @param Request $request, Challenge $challenge
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
                        $modelId     = !empty($challenge->parent_id) ? $challenge->parent_id : $challenge->id;
                        $groupExists = Group::where('model_id', $modelId)
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
                            $challenge->autoGenerateGroups();
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
     * @param Request $request, Challenge $challenge
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

            $modelId = !empty($challenge->parent_id) ? $challenge->parent_id : $challenge->id;
            $group   = Group::where('model_name', 'challenge')
                ->where('model_id', $modelId)
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
     * @param Request $request, Challenge $challenge
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
