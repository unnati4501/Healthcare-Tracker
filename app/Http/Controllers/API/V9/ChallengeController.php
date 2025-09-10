<?php declare (strict_types = 1);

namespace App\Http\Controllers\API\V9;

use App\Http\Controllers\API\V6\ChallengeController as v6ChallengeController;
use App\Http\Requests\Api\V1\ChallengeCreateRequest;
use App\Http\Requests\Api\V1\ChallengeEditRequest;
use App\Jobs\SendChallengePushNotification;
use App\Models\Badge;
use App\Models\Challenge;
use App\Models\ChallengeTarget;
use App\Models\Exercise;
use App\Models\User;
use Carbon\Carbon;
use DB;
use Illuminate\Http\JsonResponse;

class ChallengeController extends v6ChallengeController
{

    /**
     * Create challenge
     *
     * Log the user out (Invalidate the token).
     *
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

            if (count($members) > 19) {
                DB::rollback();
                return $this->invalidResponse([], "Maximum 20 participants are allowed including you.");
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
     * Update challenge
     *
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(ChallengeEditRequest $request, Challenge $challenge)
    {
        try {
            DB::beginTransaction();
            // logged-in user
            $user         = $this->user();
            $userTimeZone = $user->timezone;
            $appTimeZone  = config('app.timezone');

            $badges = (!empty($request->badges)) ? json_decode($request->badges, true) : [];

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
                $challenge
                    ->clearMediaCollection('logo')
                    ->addMediaFromRequest('image')
                    ->usingName($request->file('image')->getClientOriginalName())
                    ->usingFileName($name . '.' . $request->file('image')->extension())
                    ->toMediaCollection('logo', config('medialibrary.disk_name'));
                $challenge->library_image_id = null;
                $challenge->save();
            } elseif (!empty($request->imageId)) {
                if ($challenge->library_image_id != $request->imageId) {
                    $challenge->clearMediaCollection('logo');
                    $challenge->library_image_id = $request->imageId;
                    $challenge->save();
                }
            }

            $challenge->challengeBadges()->detach();
            if (!empty($badges)) {
                $challenge->challengeBadges()->attach($badges);
            }

            DB::commit();

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
            DB::rollback();
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }
}
