<?php
declare (strict_types = 1);

namespace App\Http\Controllers\API\V11;

use App\Events\UserChangePasswordEvent;
use App\Http\Controllers\API\V8\ProfileController as v8ProfileController;
use App\Http\Requests\Api\V1\EditProfileRequest;
use App\Http\Requests\Api\V11\ChangePasswordRequest;
use App\Http\Resources\V1\UserProfileResource;
use App\Http\Traits\ProvidesAuthGuardTrait;
use App\Http\Traits\ServesApiTrait;
use App\Models\User;
use Carbon\Carbon;

class ProfileController extends v8ProfileController
{
    use ServesApiTrait, ProvidesAuthGuardTrait;

    /**
     * change user password
     *
     * @param ChangePasswordRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function changePassword(ChangePasswordRequest $request)
    {
        try {
            // logged-in user
            $user      = $this->user();
            $xDeviceOs = strtolower($request->header('X-Device-Os', ""));
            if ($user) {
                \DB::beginTransaction();

                $user->password = bcrypt($request->get('password'));
                $user->save();

                \DB::commit();

                // fire change password event
                event(new UserChangePasswordEvent($user, $request->get('password'), $xDeviceOs));
            }

            return $this->successResponse([], "Password changed successfully.");
        } catch (\Exception $e) {
            \DB::rollback();
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * Update user profile
     *
     * @param EditProfileRequest $request
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
            $xDeviceOs      = strtolower($request->header('X-Device-Os', ""));

            $data = [
                'first_name' => $request->input('firstName'),
                'last_name'  => $request->input('lastName'),
            ];

            if (!empty($request->input('password'))) {
                $data['password'] = \bcrypt($request->input('password'));
            }

            $updated = $user->update($data);

            if ($xDeviceOs == config('zevolifesettings.PORTAL') && $request->profileImage) {
                $name = $user->getKey() . '_' . \time();
                $user->clearMediaCollection('logo')
                    ->addMediaFromBase64($request->profileImage)
                    ->usingName($name)
                    ->toMediaCollection('logo', config('medialibrary.disk_name'));
            }

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
}
