<?php
declare (strict_types = 1);

namespace App\Http\Controllers\API\V1;

use App\Http\Collections\V1\UserNotificationSettingCollection;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\EditProfileRequest;
use App\Http\Resources\V1\UserProfileResource;
use App\Http\Traits\ProvidesAuthGuardTrait;
use App\Http\Traits\ServesApiTrait;
use Carbon\Carbon;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    use ServesApiTrait, ProvidesAuthGuardTrait;

    /**
     * Update user profile
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function edit(EditProfileRequest $request)
    {
        try {
            \DB::beginTransaction();

            $appTimezone               = config('app.timezone');
            $user                      = $this->user();
            $timezone                  = $user->timezone ?? $appTimezone;
            $weightCurrentDateTimeZone = Carbon::parse(now()->toDateTimeString(), $user->timezone);
            $existingProfile           = $user->profile;

            $data = [
                'first_name' => $request->input('firstName'),
                'last_name'  => $request->input('lastName'),
            ];

            if (!empty($request->input('password'))) {
                $data['password'] = \bcrypt($request->input('password'));
            }

            $updated = $user->update($data);

            // update user profile image if not empty
            if ($request->hasFile('profileImage')) {
                $name = $user->getKey() . '_' . \time();
                $user->clearMediaCollection('logo')
                    ->addMediaFromRequest('profileImage')
                    ->usingName($request->file('profileImage')->getClientOriginalName())
                    ->usingFileName($name . '.' . $request->file('profileImage')->extension())
                    ->toMediaCollection('logo', config('medialibrary.disk_name'));
            }

            // update user cover image if not empty
            if ($request->hasFile('coverImage')) {
                $name = $user->getKey() . '_' . \time();
                $user->clearMediaCollection('coverImage')
                    ->addMediaFromRequest('coverImage')
                    ->usingName($request->file('coverImage')->getClientOriginalName())
                    ->usingFileName($name . '.' . $request->file('coverImage')->extension())
                    ->toMediaCollection('coverImage', config('medialibrary.disk_name'));
            }

            // calculate user age
            $birth_date = Carbon::parse($request->dob, \config('app.timezone'))->setTime(0, 0, 0);
            $now        = \now()->setTime(0, 0, 0);
            $age        = $now->diffInYears($birth_date);

            // update profile
            $user->profile()->update([
                'about'      => $request->about,
                'birth_date' => $request->dob,
                'age'        => $age,
                'gender'     => $request->gender,
                'location'   => $request->location,
            ]);

            // update BMI if age is changed
            if ($existingProfile->age != $age) {
                // fetch latest weight entry
                $lastWeight = $user->weights()->orderByDesc('user_weight.updated_at')->first();

                if (!empty($lastWeight) && $existingProfile->height) {
                    //Delete all user bmi entry for current day before insert
                    $user->bmis()
                        ->where(\DB::raw("DATE(CONVERT_TZ(user_bmi.log_date, '{$appTimezone}', '{$user->timezone}'))"), $weightCurrentDateTimeZone->toDateString())
                        ->get()->each->delete();

                    // calculate bmi and store
                    $bmi = $lastWeight->weight / pow(($existingProfile->height / 100), 2);

                    // log updated BMI
                    $user->bmis()->create([
                        'bmi'      => $bmi,
                        'weight'   => $lastWeight->weight, // kg
                        'height'   => $existingProfile->height, // cm
                        'age'      => $age,
                        'log_date' => now()->toDateTimeString(),
                    ]);
                }
            }

            \DB::commit();
            return $this->successResponse(['data' => new UserProfileResource($user)], trans('api_messages.profile.edit'));
        } catch (\Exception $e) {
            \DB::rollback();
            if ($e instanceof AuthenticationException) {
                report($e);
                return $this->unauthorizedResponse(trans('labels.common_title.something_wrong'));
            }
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * Get user profile details
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function detail(Request $request)
    {
        try {
            // get logged in user and retrieve profile
            $user = $this->user();
            return $this->successResponse(['data' => new UserProfileResource($user)], 'Profile details retrieved successfully.');
        } catch (\Exception $e) {
            if ($e instanceof AuthenticationException) {
                report($e);
                return $this->unauthorizedResponse(trans('labels.common_title.something_wrong'));
            }
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * Get user wise notification settings
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function notificationSettings(Request $request)
    {
        try {
            // get logged in user and retrieve module wise notification settings
            $user = $this->user();

            return $this->successResponse(new UserNotificationSettingCollection($user->notificationSettings), 'Notification settings retrieved successfully.');
        } catch (\Exception $e) {
            if ($e instanceof AuthenticationException) {
                report($e);
                return $this->unauthorizedResponse(trans('labels.common_title.something_wrong'));
            }
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * Update user wise notification settings
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function editNotificationSettings(Request $request)
    {
        try {
            \DB::beginTransaction();
            // get logged in user and update module wise notification flag
            $user = $this->user();

            if (!empty($request->all())) {
                foreach ($request->all() as $module => $flag) {
                    $setting = $user->notificationSettings()->updateOrCreate([
                        'user_id' => $user->getKey(),
                        'module'  => strtolower($module),
                    ], [
                        'flag' => (bool) $flag,
                    ]);
                }
            }

            \DB::commit();
            return $this->successResponse(new UserNotificationSettingCollection($user->notificationSettings), trans('api_messages.profile.notification-setting'));
        } catch (\Exception $e) {
            \DB::rollback();
            if ($e instanceof AuthenticationException) {
                report($e);
                return $this->unauthorizedResponse(trans('labels.common_title.something_wrong'));
            }
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function userSettings(Request $request)
    {
        try {
            // get logged in user and update module wise notification flag
            $user = $this->user();

            // fetch latest weight entry
            $lastWeight = $user->weights()->orderByDesc('user_weight.updated_at')->first();
            $goal       = $user->goal;
            $profile    = $user->profile;

            $userSettings                = [];
            $userSettings['weight']      = 0;
            $userSettings['stepGoal']    = 0;
            $userSettings['calorieGoal'] = 0;
            $userSettings['height']      = 0;

            if (!empty($lastWeight)) {
                $userSettings['weight'] = $lastWeight->weight;
            }

            if (!empty($goal)) {
                $userSettings['stepGoal']    = $goal->steps ?? 0;
                $userSettings['calorieGoal'] = $goal->calories ?? 0;
            }

            if (!empty($profile)) {
                $userSettings['height'] = $profile->height ?? 0;
            }

            return $this->successResponse(['data' => $userSettings], 'Settings data retrived successfully.');
        } catch (\Exception $e) {
            if ($e instanceof AuthenticationException) {
                report($e);
                return $this->unauthorizedResponse(trans('labels.common_title.something_wrong'));
            }
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function changeUserSettings(Request $request)
    {
        try {
            \DB::beginTransaction();
            // get logged in user and update module wise notification flag
            $user        = $this->user();
            $appTimezone = config('app.timezone');
            $timezone    = $user->timezone ?? $appTimezone;

            $existingProfile           = $user->profile;
            $weightCurrentDateTimeZone = Carbon::parse(now()->toDateTimeString(), $user->timezone);

            if (!empty($request->weight)) {
                // create weight entry for user

                $user->weights()
                    ->where(\DB::raw("DATE(CONVERT_TZ(user_weight.log_date, '{$appTimezone}', '{$user->timezone}'))"), $weightCurrentDateTimeZone->toDateString())
                    ->get()->each->delete();

                $user->weights()->create([
                    'weight'   => $request->input('weight'),
                    'log_date' => now()->toDateTimeString(),
                ]);
            }

            if (!empty($request->height)) {
                // create height entry for user
                $user->profile->update([
                    'height' => $request->input('height'),
                ]);
            }

            if (!empty($request->calorieGoal) || !empty($request->stepGoal)) {
                $data = [];
                if (!empty($request->calorieGoal)) {
                    $data['calories'] = $request->calorieGoal;
                }
                if (!empty($request->stepGoal)) {
                    $data['steps'] = $request->stepGoal;
                }
                // create or update user goal
                $user->goal()->updateOrCreate(['user_id' => $user->getKey()], $data);
            }

            // update BMI if age is changed
            if ((!empty($request->height) && $existingProfile->height != $request->height) || !empty($request->weight)) {
                // calculate bmi and store
                if (!empty($request->height)) {
                    $bmi = $request->weight / pow(($request->height / 100), 2);

                    $user->bmis()
                        ->where(\DB::raw("DATE(CONVERT_TZ(user_bmi.log_date, '{$appTimezone}', '{$user->timezone}'))"), $weightCurrentDateTimeZone->toDateString())
                        ->get()->each->delete();

                    // log updated BMI
                    $user->bmis()->create([
                        'bmi'      => $bmi,
                        'weight'   => $request->weight, // kg
                        'height'   => $request->height, // cm
                        'age'      => $existingProfile->age,
                        'log_date' => now()->toDateTimeString(),
                    ]);
                }
            }

            \DB::commit();
            return $this->successResponse(['data' => []], 'Settings updated successfully.');
        } catch (\Exception $e) {
            \DB::rollback();
            if ($e instanceof AuthenticationException) {
                report($e);
                return $this->unauthorizedResponse(trans('labels.common_title.something_wrong'));
            }
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function changeActivityLevel(Request $request)
    {
        try {
            // get logged in user and update module wise notification flag
            $user = $this->user();

            // fetch respective expertise level
            if (!empty($request)) {
                foreach ($request->all() as $key => $levelArr) {
                    if (!empty($levelArr['id']) && !empty($levelArr['level'])) {
                        $pivotExisting = $user->expertiseLevels()->wherePivot('category_id', $levelArr['id'])->first();

                        if (!empty($pivotExisting)) {
                            $pivotExisting->pivot->expertise_level = $levelArr['level'];
                            $pivotExisting->pivot->save();
                        } else {
                            $user->expertiseLevels()->attach($levelArr['id'], ['expertise_level' => strtolower($levelArr['level'])]);
                        }
                    }
                }
            }

            return $this->successResponse(['data' => []], 'Level of Expertise updated Successfully.');
        } catch (\Exception $e) {
            if ($e instanceof AuthenticationException) {
                report($e);
                return $this->unauthorizedResponse(trans('labels.common_title.something_wrong'));
            }
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }
}
