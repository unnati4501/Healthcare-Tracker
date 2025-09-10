<?php
declare (strict_types = 1);

namespace App\Http\Controllers\API\V1;

use App\Http\Collections\V1\BadgeListCollection;
use App\Http\Collections\V1\ChallengeHistoryListCollection;
use App\Http\Collections\V1\ChallengeListCollection;
use App\Http\Collections\V1\ChallengeUserListCollection;
use App\Http\Collections\V1\InvitationsListCollection;
use App\Http\Collections\V1\RunningChallengeListCollection;
use App\Http\Collections\V1\UpcomingChallengeListCollection;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ChallengeCreateRequest;
use App\Http\Requests\Api\V1\ChallengeEditRequest;
use App\Http\Requests\Api\V1\GetBadgeRequest;
use App\Http\Requests\Api\V1\InviteUserToChallengeRequest;
use App\Http\Resources\V1\ChallengeDetailsResource;
use App\Http\Resources\V1\ChallengeInfoResource;
use App\Http\Resources\V1\FinishedChallengeDetailResource;
use App\Http\Resources\V1\FinishedChallengeDetailResourceNew;
use App\Http\Traits\ProvidesAuthGuardTrait;
use App\Http\Traits\ServesApiTrait;
use App\Jobs\SendChallengePushNotification;
use App\Models\Badge;
use App\Models\Challenge;
use App\Models\ChallengeCategory;
use App\Models\ChallengeHistoryParticipants;
use App\Models\ChallengeParticipant;
use App\Models\ChallengeTarget;
use Carbon\Carbon;
use DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChallengeController extends Controller
{
    use ServesApiTrait, ProvidesAuthGuardTrait;

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
                ->where(function ($query) use ($user) {
                    $query->where("challenge_participants.user_id", $user->id)
                        ->orWhere('challenge_participants.team_id', $user->teams()->first()->id);
                })
                ->where('challenges.company_id', $company->getKey())
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
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function cancelChallenge(Request $request, Challenge $challenge)
    {
        try {
            \DB::beginTransaction();
            // logged-in user
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

            $challenge->update(['cancelled' => true]);

            $challenge->expireBadges();

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
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function inviteUsers(InviteUserToChallengeRequest $request, Challenge $challenge)
    {
        try {
            \DB::beginTransaction();

            // $currentMembersCount = $challenge->members()->wherePivot('status', 'Accepted')->count();
            $currentMembersCount = $challenge->members()->count();

            $requestedMembersCount = count($request->members);

            if (($currentMembersCount + $requestedMembersCount) > 50) {
                \DB::rollback();
                return $this->invalidResponse([], "Maximum 50 participants are allowed including you.");
            }

            $challenge->members()->attach($request->members, ["status" => "Pending"]);

            \DB::commit();

            // dispatch job to SendChallengePushNotification
            $this->dispatch(new SendChallengePushNotification($challenge, 'challenge-invitation', '', $request->members));

            return $this->successResponse([], trans('api_messages.challenge.invite'));
        } catch (\Exception $e) {
            \DB::rollback();
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * Log the user out (Invalidate the token).
     *
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
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getChallengeCategories(Request $request)
    {
        try {
            // logged-in user
            $user     = $this->user();
            $data     = array();
            $uom_data = config('zevolifesettings.uom');

            $challenge_categories = ChallengeCategory::where("is_excluded", 0)->get();
            foreach ($challenge_categories as $key => $value) {
                $data['categories'][] = array("id" => $value->id, "name" => $value->name, "slug" => $value->short_name);
            }
            $challenge_targets = ChallengeTarget::where("is_excluded", 0)->get();

            foreach ($challenge_targets as $key => $value) {
                $targetData         = array();
                $targetData['id']   = $value->id;
                $targetData['name'] = $value->name;
                $targetData['slug'] = $value->short_name;

                if (array_key_exists($value->name, $uom_data)) {
                    $targetData['uom'] = array_keys($uom_data[$value->name]);
                }
                $data['targets'][] = $targetData;
            }

            return $this->successResponse(['data' => $data], 'Challenge Categories Retrieved successfully.');
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
            $user     = $this->user();
            $timezone = $user->timezone ?? config('app.timezone');

            $exploreChallengeData = Challenge::select("challenges.*")
                ->join("challenge_participants", "challenge_participants.challenge_id", "=", "challenges.id")
                ->where("challenge_participants.status", "Pending")
                ->where("challenge_participants.user_id", $user->id)
                ->where(DB::raw("CONVERT_TZ(challenges.start_date, 'UTC', '{$timezone}')"), ">", now($timezone)->toDateTimeString())
                ->where("challenges.cancelled", false)
                ->where("challenges.challenge_type", 'individual')
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
    public function store(ChallengeCreateRequest $request)
    {
        try {
            \DB::beginTransaction();
            // logged-in user
            $user         = $this->user();
            $userTimeZone = $user->timezone;
            $appTimeZone  = config('app.timezone');
            $company_id   = !is_null($user->company->first()) ? $user->company->first()->id : null;

            $rules   = (!empty($request->rules)) ? json_decode($request->rules, true) : [];
            $members = (!empty($request->users)) ? json_decode($request->users, true) : [];
            $badges  = (!empty($request->badges)) ? json_decode($request->badges, true) : [];

            if (count($members) > 19) {
                \DB::rollback();
                return $this->invalidResponse([], "Maximum 20 participants are allowed including you.");
            } else {
                $selectedMembers = \App\Models\User::find($members);

                if (count($selectedMembers) != count($members)) {
                    \DB::rollback();
                    return $this->invalidResponse([], "Some of selected users not found.");
                }
            }

            if (count($badges) > 10) {
                \DB::rollback();
                return $this->invalidResponse([], "Maximum 10 badges are allowed.");
            } else {
                $selectedBadges = \App\Models\Badge::find($badges);

                if (count($selectedBadges) != count($badges)) {
                    \DB::rollback();
                    return $this->invalidResponse([], "Some of selected badges not found.");
                }
            }

            $challenges_rule = array();
            if (!empty($rules)) {
                if (count($rules) > 2) {
                    \DB::rollback();
                    return $this->invalidResponse([], "Maximum 2 targets are allowed.");
                }

                foreach ($rules as $key => $value) {
                    if (!empty($value['targetId'])) {
                        $target = \App\Models\ChallengeTarget::find($value['targetId']);
                        if (empty($target)) {
                            \DB::rollback();
                            return $this->notFoundResponse("Target not found.");
                        }

                        if (!empty($value['exerciseId'])) {
                            $exercise = \App\Models\Exercise::find($value['exerciseId']);
                            if (empty($exercise)) {
                                \DB::rollback();
                                return $this->notFoundResponse("Exercise not found.");
                            }
                        }
                    } else {
                        \DB::rollback();
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

            $record = Challenge::create($insertData);

            // aad challenge logo image if not empty
            if ($request->hasFile('image')) {
                $name = $record->getKey() . '_' . \time();
                $record->clearMediaCollection('logo')
                    ->addMediaFromRequest('image')
                    ->usingName($request->file('image')->getClientOriginalName())
                    ->usingFileName($name . '.' . $request->file('image')->extension())
                    ->toMediaCollection('logo', config('medialibrary.disk_name'));
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

            \DB::commit();

            // dispatch job to SendChallengePushNotification
            $this->dispatch(new SendChallengePushNotification($record, 'challenge-invitation', '', $members));

            return $this->successResponse([], trans('api_messages.challenge.create'));
        } catch (\Exception $e) {
            \DB::rollback();
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(ChallengeEditRequest $request, Challenge $challenge)
    {
        try {
            \DB::beginTransaction();
            // logged-in user
            $user         = $this->user();
            $userTimeZone = $user->timezone;
            $appTimeZone  = config('app.timezone');

            $badges = (!empty($request->badges)) ? json_decode($request->badges, true) : [];

            if (count($badges) > 10) {
                \DB::rollback();
                return $this->invalidResponse([], "Maximum 10 badges are allowed.");
            } else {
                $selectedBadges = \App\Models\Badge::find($badges);

                if (count($selectedBadges) != count($badges)) {
                    \DB::rollback();
                    return $this->invalidResponse([], "Some of selected badges not found.");
                }
            }

            $startDate = Carbon::parse($request->startDate, $userTimeZone)->setTime(0, 0, 0)->setTimezone($appTimeZone);

            $endDate = Carbon::parse($request->endDate, $userTimeZone)->setTime(23, 59, 59)->setTimezone($appTimeZone);

            $insertData                     = array();
            $insertData['start_date']       = $startDate;
            $insertData['end_date']         = $endDate;
            $insertData['challenge_end_at'] = $endDate;
            $insertData['title']            = $request->title;
            $insertData['description']      = $request->description;

            $challenge->update($insertData);

            // update challenge logo image if not empty
            if ($request->hasFile('image')) {
                $name = $challenge->getKey() . '_' . \time();
                $challenge->clearMediaCollection('logo')
                    ->addMediaFromRequest('image')
                    ->usingName($request->file('image')->getClientOriginalName())
                    ->usingFileName($name . '.' . $request->file('image')->extension())
                    ->toMediaCollection('logo', config('medialibrary.disk_name'));
            }

            $challenge->challengeBadges()->detach();
            if (!empty($badges)) {
                $challenge->challengeBadges()->attach($badges);
            }

            \DB::commit();

            $membersData = $challenge->members()->where('user_id', '!=', $challenge->creator_id);

            if (now()->toDateTimeString() < $challenge->start_date) {
                $membersData = $membersData->wherePivotIn('status', ["Accepted", "Pending"]);
            } else {
                $membersData = $membersData->wherePivotIn('status', ["Accepted"]);
            }
            $membersData = $membersData->get()->pluck('id')->toArray();

            // dispatch job to SendChallengePushNotification
            // Challenge Update notification has been disabled as an update.
            // $this->dispatch(new SendChallengePushNotification($challenge, 'challenge-updated', "", $membersData));

            return $this->successResponse([], trans('api_messages.challenge.update'));
        } catch (\Exception $e) {
            \DB::rollback();
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getBadges(GetBadgeRequest $request)
    {
        try {
            // logged-in user
            $user      = $this->user();
            $badgeData = array();
            $CompanyId = !is_null($user->company->first()) ? $user->company->first()->id : null;

            if ($request->categoryId == '2') {
                if (!empty($request->targetIds[0])) {
                    $badgeData = Badge::where("challenge_target_id", $request->targetIds[0]['id'])
                        ->where("type", "challenge")
                        ->where(function ($query) use ($CompanyId) {
                            $query->whereNull("company_id");
                            if (!empty($CompanyId)) {
                                $query->orWhere("company_id", $CompanyId);
                            }
                        })
                        ->orderBy('badges.updated_at', 'DESC')
                        ->paginate(config('zevolifesettings.datatable.pagination.short'));
                }
            } else {
                $target_units  = (!empty($request->targetIds[0]) && !empty($request->targetIds[0]['value'])) ? $request->targetIds[0]['value'] : 0;
                $target_units1 = (!empty($request->targetIds[1]) && !empty($request->targetIds[1]['value'])) ? $request->targetIds[1]['value'] : 0;

                $target_type  = (!empty($request->targetIds[0]) && !empty($request->targetIds[0]['id'])) ? $request->targetIds[0]['id'] : 0;
                $target_type1 = (!empty($request->targetIds[1]) && !empty($request->targetIds[1]['id'])) ? $request->targetIds[1]['id'] : 0;

                $badgeData = Badge::where("type", "challenge")
                    ->where(function ($query) use ($CompanyId) {
                        $query->whereNull("company_id");
                        if (!empty($CompanyId)) {
                            $query->orWhere("company_id", $CompanyId);
                        }
                    });
                $badgeData = $badgeData->where(function ($query) use ($target_type, $target_type1, $target_units, $target_units1) {
                    $query->where(function ($subQuery) use ($target_type, $target_units) {
                        $subQuery->where("challenge_target_id", $target_type)
                            ->where("target", "<=", $target_units);
                    })->orWhere(function ($subQuery1) use ($target_type1, $target_units1) {
                        $subQuery1->where("challenge_target_id", $target_type1)
                            ->where("target", "<=", $target_units1);
                    });
                })
                    ->orderBy('badges.updated_at', 'DESC')
                    ->paginate(config('zevolifesettings.datatable.pagination.short'));
            }

            if (!empty($badgeData) && $badgeData->count() > 0) {
                // collect required data and return response
                return $this->successResponse(new BadgeListCollection($badgeData, true), 'Badge list retrieved successfully.');
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
    public function linkBadge(Request $request)
    {
        $jsonString = '{"code":200,"message":"Badge linked successfully."}';

        return json_decode($jsonString, true);
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

            $challengeIds = ChallengeParticipant::where(function ($query) use ($user) {
                $query->where("user_id", $user->id)
                    ->orWhere("team_id", $user->teams()->first()->id);
            })
                ->where("status", "Accepted")
                ->groupBy("challenge_id")
                ->get()
                ->pluck('challenge_id')
                ->toArray();

            $exploreChallengeData = Challenge::
                leftJoin("challenge_participants", function ($join) {
                    $join->on("challenges.id", "=", "challenge_participants.challenge_id")
                    ->where("challenge_participants.status", "Accepted");
                })
                ->select("challenges.*", DB::raw("COUNT(challenge_participants.user_id) as members"))
                ->where(function ($query) use ($challengeIds, $timezone) {
                    $query->where(function ($subQuery) use ($challengeIds) {
                        $subQuery->whereIn("challenges.id", $challengeIds);
                    })->orWhere(function ($subQuery) use ($timezone) {
                        $subQuery->where("challenges.close", false)
                            ->where(DB::raw("CONVERT_TZ(challenges.start_date, 'UTC', '{$timezone}')"), ">", now($timezone)->toDateTimeString());
                    });
                })
                ->where("challenges.cancelled", false)
                ->where('challenges.company_id', $company->id)
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
                        ->orderBy('challenge_wise_user_ponits.rank', 'ASC')
                        ->orderBy('challenge_wise_user_ponits.user_id', 'ASC')
                        ->groupBy('challenge_wise_user_ponits.user_id');
                } else {
                    $challengeParticipantsWithPoints = $challenge->challengeWiseTeamPoints()
                        ->join('freezed_challenge_participents', 'freezed_challenge_participents.team_id', '=', 'challenge_wise_team_ponits.team_id')
                        ->select('freezed_challenge_participents.team_id', 'freezed_challenge_participents.participant_name', 'challenge_wise_team_ponits.challenge_id', 'challenge_wise_team_ponits.points', 'challenge_wise_team_ponits.rank')->where('freezed_challenge_participents.challenge_id', $challenge->id)
                        ->where('challenge_wise_team_ponits.challenge_id', $challenge->id)
                        ->orderBy('challenge_wise_team_ponits.rank', 'ASC')
                        ->orderBy('challenge_wise_team_ponits.team_id', 'ASC')
                        ->groupBy('challenge_wise_team_ponits.team_id');
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

                if ($challengeParticipantsWithPoints->count() > 0) {
                    $participantsData = [];
                    foreach ($challengeParticipantsWithPoints as $key => $record) {
                        $participant         = [];
                        $participant['id']   = $challenge->challenge_type == 'individual' ? $record->user_id : $record->team_id;
                        $participant['name'] = $record->participant_name;

                        $userRecord = $challenge->challenge_type == 'individual' ? \App\Models\User::find($record->user_id) : \App\Models\Team::find($record->team_id);

                        $participant['deleted'] = false;
                        if (!empty($userRecord)) {
                            $participant['image'] = $userRecord->getMediaData();
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

                    $challengeInfo = [
                        'id'      => $challenge->id,
                        'title'   => $challenge->title,
                        'members' => $challengeParticipantCount,
                        'type'    => $challenge->challenge_type,
                    ];

                    // collect required data and return response
                    return $this->successResponse(['data' => $participantsData, 'challenge' => $challengeInfo], 'Leaderboard data retrieved successfully.');
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

            $exploreChallengeData = Challenge::select("challenges.*")->where('challenges.company_id', $company->id)->where("challenges.cancelled", false);

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
                    ->where(function ($query) use ($user) {
                        $query->where('challenge_participants.user_id', $user->id);
                        if (!$user->teams()->first()->default) {
                            $query->orWhere('challenge_participants.team_id', $user->teams()->first()->id);
                        }
                    })
                    // ->where("challenge_participants.user_id", $user->id)
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
                ->join("freezed_challenge_participents", function ($join) use ($user) {
                    $join->on("challenges.id", "=", "freezed_challenge_participents.challenge_id")
                        ->where(function ($query) use ($user) {
                            $query->where("freezed_challenge_participents.user_id", $user->id)
                                ->orWhere("freezed_challenge_participents.team_id", $user->teams()->first()->id);
                        });
                })
                ->select("challenges.*")
                ->where("challenges.cancelled", false)
                ->where('challenges.company_id', $company->id)
                ->where(DB::raw("CONVERT_TZ(challenges.end_date, 'UTC', '{$timezone}')"), "<", now($timezone)->toDateTimeString())
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
                ->where(function ($query) use ($user) {
                    $query->where("challenge_participants.user_id", $user->id)
                        ->orWhere("challenge_participants.team_id", $user->teams()->first()->id);
                })
            // ->where("challenge_participants.user_id", $user->id)
                ->where('challenges.company_id', $company->id)
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

            $isTeamData = false;
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
            } else {
                $isTeamData = true;
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
            }

            if ($users->count() > 0) {
                // collect required data and return response
                return $this->successResponse(new ChallengeUserListCollection($users, $isTeamData), 'Challenge participants listed successfully.');
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
            } else {
                $winners = \App\Models\ChallengeWiseUserLogData::with('team')
                    ->where('challenge_wise_user_log.challenge_id', $challenge->id)
                    ->where('challenge_wise_user_log.is_winner', 1)
                    ->groupBy('challenge_wise_user_log.team_id')
                    ->distinct('challenge_wise_user_log.team_id')
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
                    } else {
                        $winnerData['id'] = $query->team_id;
                        $userRecord       = \App\Models\Team::find($query->team_id);
                        if (!empty($userRecord)) {
                            $winnerData['name'] = $userRecord->name;
                        }
                    }

                    $winnerData['deleted'] = false;
                    if (!empty($userRecord)) {
                        $winnerData['image'] = $userRecord->getMediaData();
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
     * get details of ongoing challenge woth user points and rank
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function ongoingDetailsNew(Request $request, Challenge $challenge)
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
                    $data = array("data" => new FinishedChallengeDetailResourceNew($challengeDetails));
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
