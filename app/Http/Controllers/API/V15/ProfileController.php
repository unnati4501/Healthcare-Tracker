<?php
declare (strict_types = 1);

namespace App\Http\Controllers\API\V15;

use App\Http\Controllers\API\V11\ProfileController as v11ProfileController;
use App\Http\Requests\Api\V1\EditProfileRequest;
use App\Http\Resources\V1\SurveyListResource;
use App\Http\Resources\V15\UserProfileResource;
use App\Models\HsSurvey;
use App\Models\Team;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends v11ProfileController
{
    /**
     * Get user profile details
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function detail(Request $request)
    {
        try {
            $data = new \stdClass();

            // get logged in user and retrieve profile
            $data->user = $user = $this->user();

            // get latest hs survey
            $usersurveyData = HsSurvey::where('user_id', $user->id)
                ->whereNotNull('survey_complete_time')
                ->orderBy('id', 'DESC')
                ->first();

            if (!empty($usersurveyData)) {
                $headers                                   = $request->headers->all();
                $payload                                   = $request->all();
                $version                                   = config('zevolifesettings.version.api_version');
                $surveyHistoryRequest                      = Request::create("api/" . $version . "/healthscore/report/" . $usersurveyData->id, 'GET', $headers, $payload);
                $surveyHistoryResponse                     = \Route::dispatch($surveyHistoryRequest);
                $surveyHistoryBody                         = json_decode($surveyHistoryResponse->getContent());
                $surveyHistoryBody->result->data->surveyId = $usersurveyData->id;

                if (!empty($surveyHistoryBody)) {
                    $data->lastSubmittedSurvey = new SurveyListResource($surveyHistoryBody);
                }
            }

            return $this->successResponse(['data' => new UserProfileResource($data)], 'Profile details retrieved successfully.');
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
     * Update user profile
     *
     * @param EditProfileRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function edit(EditProfileRequest $request)
    {
        try {
            \DB::beginTransaction();

            $appTimezone = config('app.timezone');
            $user        = $this->user();
            $company     = $this->user()->company()
                ->select('companies.id', 'companies.auto_team_creation', 'companies.team_limit')->first();
            $timezone                  = $user->timezone ?? $appTimezone;
            $weightCurrentDateTimeZone = Carbon::parse(now()->toDateTimeString(), $user->timezone);
            $existingProfile           = $user->profile;
            $xDeviceOs                 = strtolower($request->header('X-Device-Os', ""));

            // check if current team_id and team_id from request are same or not
            if ($xDeviceOs != config('zevolifesettings.PORTAL')) {
                $currentTeam = $user->teams()->select('teams.id')->first();
                if ($currentTeam->id != $request->team_id) {
                    $newTeam = Team::select('teams.id', 'teams.company_id', 'teams.default', 'teams.department_id')
                        ->withCount('users')
                        ->where('department_id', $request->department_id)
                        ->where('id', $request->team_id)
                        ->first();
                    if (empty($newTeam)) {
                        \DB::rollback();
                        return $this->invalidResponse([
                            'team_id' => ['Team is not belongs to the selected company!'],
                        ], 'The given data is invalid.');
                    } else {
                        if (!$newTeam->default && $company->auto_team_creation && $newTeam->users_count >= $company->team_limit) {
                            \DB::rollback();
                            return $this->invalidResponse([
                                'team_id' => ['Team members limit is exceeded, Please select another team.'],
                            ], 'The given data is invalid.');
                        }

                        // detach old team entry and attach user to new team
                        $user->teams()->detach();
                        $user->teams()->attach($newTeam, ['company_id' => $newTeam->company_id, 'department_id' => $newTeam->department_id]);

                        // check if user is switched to default team then remove this user from all the challenge type group
                        if ($newTeam->default) {
                            removeUserFromChallengeTypeGroups($user, $company->id);
                        }
                    }
                }
            }

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
            $data       = new \stdClass();
            $data->user = $user;
            return $this->successResponse(['data' => new UserProfileResource($data)], trans('api_messages.profile.edit'));
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
