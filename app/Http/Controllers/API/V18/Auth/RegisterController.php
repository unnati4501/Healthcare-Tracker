<?php
declare (strict_types = 1);

namespace App\Http\Controllers\Api\V18\Auth;

use App\Http\Controllers\API\V17\Auth\RegisterController as v17RegisterController;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\RegisterRequest;
use App\Http\Traits\ProvidesAuthGuardTrait;
use App\Http\Traits\ServesApiTrait;
use App\Models\Category;
use App\Models\Company;
use App\Models\CompanyBranding;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Route;
use JWTAuth;

/**
 * Class RegisterController
 *
 * @package App\Http\Controllers\Api\Auth
 */
class RegisterController extends v17RegisterController
{
    use RegistersUsers, ServesApiTrait, ProvidesAuthGuardTrait {
        ProvidesAuthGuardTrait::guard insteadof RegistersUsers;
    }

    /**
     * Handle a registration request for the application.
     *
     * @param RegisterRequest $request
     *
     * @return JsonResponse
     */
    public function register(RegisterRequest $request)
    {
        try {
            \DB::beginTransaction();

            if (empty($request->input('companyCode'))) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'companyCode' => \trans('api_labels.auth.allowed_company'),
                ])->status(422);
            }

            $company   = Company::where('code', $request->input('companyCode'))->first();
            $xDeviceOs = strtolower($request->header('X-Device-Os', ""));

            if (empty($company)) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'companyCode' => 'The selected company code is invalid.',
                ])->status(422);
            } else {
                // If register with portal and try with different portal url
                if ($xDeviceOs == config()->get('zevolifesettings.PORTAL')) {
                    $companyId       = ($company->parent_id != null) ? $company->parent_id : $company->id;
                    $companyBranding = CompanyBranding::where('company_id', $companyId)->first();

                    if ($companyBranding && ($company->is_reseller || $company->parent_id != null)) {
                        $origin       = strtolower($request->header('origin', ""));
                        $hostURL      = ($origin) ? parse_url($origin)['host'] : "";
                        $portalDomain = $companyBranding->portal_domain;

                        if ($hostURL !== $portalDomain) {
                            $userData = [];
                            return $this->invalidResponse($userData, \trans('api_labels.auth.not_same_domain'), 401);
                        }
                    }
                }
                // Check condition for access portal and app when company login
                if ($xDeviceOs == config('zevolifesettings.PORTAL') && !$company->allow_portal) {
                    // if company don't have portal access
                    return $this->notaccessFailedResponse(\trans('api_labels.auth.not_access_portal'));
                } elseif (($xDeviceOs == config('zevolifesettings.IOS') || $xDeviceOs == config('zevolifesettings.ANDROID')) && !$company->allow_app) {
                    return $this->notaccessFailedResponse(\trans('api_labels.auth.not_access_app'));
                }
            }

            if ($xDeviceOs == config('zevolifesettings.PORTAL')) {
                $team = $company->getDefaultTeam();
            } else {
                $team = Team::where('company_id', $company->id)
                    ->withCount('users')
                    ->where('department_id', $request->department_id)
                    ->where('id', $request->team_id)
                    ->first();
                if (empty($team)) {
                    return $this->invalidResponse([
                        'team_id' => ['Team is not belongs to the selected company!'],
                    ], 'The given data is invalid.');
                } else {
                    if (!$team->default && $company->auto_team_creation && $team->users_count >= $company->team_limit) {
                        return $this->invalidResponse([
                            'team_id' => ['Team members limit is exceeded, Please select another team.'],
                        ], 'The given data is invalid.');
                    }
                }
            }

            $user        = User::findByEmail($request->email);
            $appTimezone = config('app.timezone');

            if (!empty($user)) {
                if ($xDeviceOs != config('zevolifesettings.PORTAL') && $user->can_access_app) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'email' => 'Email already exists.',
                    ])->status(422);
                } elseif ($xDeviceOs == config('zevolifesettings.PORTAL') && $user->can_access_portal) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'email' => 'Email already exists.',
                    ])->status(422);
                } else {
                    $timezone                  = $user->timezone ?? $appTimezone;
                    $weightCurrentDateTimeZone = Carbon::parse(now()->toDateTimeString(), $user->timezone);

                    if (empty($request->get('socialId'))) {
                        $token = JWTAuth::attempt($request->only('email', 'password'));
                        if (!$token) {
                            throw \Illuminate\Validation\ValidationException::withMessages([
                                'password' => 'Please enter your current password.',
                            ])->status(422);
                        }
                    }

                    $userCompany = $user->company->first();

                    if (!empty($userCompany) && $userCompany->code != $request->input('companyCode')) {
                        throw \Illuminate\Validation\ValidationException::withMessages(['companyCode' => 'You are not authorized to register in another company'])->status(422);
                    }

                    $userData = [
                        'first_name'        => $request->input('firstName'),
                        'last_name'         => $request->input('lastName'),
                        'last_activity_at'  => \now(config('app.timezone')),
                        'start_date'        => $company->subscription_start_date,
                        'can_access_app'    => ($userCompany->allow_app) ,
                        'can_access_portal' => ($userCompany->allow_portal) ,
                    ];

                    $user->update($userData);

                    $birth_date = Carbon::parse($request->dob, \config('app.timezone'))->setTime(0, 0, 0);
                    $now        = \now()->setTime(0, 0, 0);
                    $age        = $now->diffInYears($birth_date);

                    $user->profile()->updateOrCreate([], [
                        'birth_date' => $request->dob,
                        'age'        => $age,
                    ]);

                    //Delete all weight entry for current day before insert
                    $user->weights()
                        ->where(\DB::raw("DATE(CONVERT_TZ(user_weight.log_date, '{$appTimezone}', '{$user->timezone}'))"), $weightCurrentDateTimeZone->toDateString())
                        ->get()->each->delete();

                    // create weight entry for user
                    $user->weights()->create([
                        'weight'   => $request->input('weight'),
                        'log_date' => now()->toDateTimeString(),
                    ]);

                    //Delete all user bmi entry for current day before insert
                    $user->bmis()
                        ->where(\DB::raw("DATE(CONVERT_TZ(user_bmi.log_date, '{$appTimezone}', '{$user->timezone}'))"), $weightCurrentDateTimeZone->toDateString())
                        ->get()->each->delete();

                    $bmi = $request->input('weight') / pow(($request->input('height') / 100), 2);

                    $user->bmis()->create([
                        'bmi'      => $bmi,
                        'weight'   => $request->input('weight'), // kg
                        'height'   => $request->input('height'), // cm
                        'age'      => $age,
                        'log_date' => now()->toDateTimeString(),
                    ]);

                    if ($user->teams()->count() == 0) {
                        // attach team to user
                        $user->teams()->attach($team, ['company_id' => $team->company_id, 'department_id' => $team->department_id]);

                        // sync user with survey users if any surveys for the company is active
                        $user->syncWithSurveyUsers(true);
                    }

                    $user->userGoalTags()->detach();
                    if (!empty($request->get('goals'))) {
                        $user->userGoalTags()->sync($request->get('goals'));
                    }
                }
            } else {
                // Verify company domain - if email domain is not from allowed company domain then rerutn error
                if ($company->has_domain) {
                    list(, $domain) = \explode('@', $request->input('email'));
                    if (!$company->domains->contains('domain', $domain)) {
                        throw \Illuminate\Validation\ValidationException::withMessages([
                            'email' => \trans('api_labels.auth.allowed_company'),
                        ])->status(422);
                    }
                }

                $userData = [
                    'email'             => $request->input('email'),
                    'first_name'        => $request->input('firstName'),
                    'last_name'         => $request->input('lastName'),
                    'is_premium'        => true,
                    'can_access_app'    => ($company->allow_app) ,
                    'can_access_portal' => ($company->allow_portal) ,
                    'last_activity_at'  => \now(config('app.timezone')),
                    'start_date'        => $company->subscription_start_date,
                ];

                if (empty($request->get('socialId'))) {
                    $userData['password'] = \bcrypt($request->input('password'));
                } else {
                    $userData['social_id']   = $request->get('socialId');
                    $userData['social_type'] = $request->get('socialType') ?? 1;
                }

                // create user
                $user = User::create($userData);

                $timezone                  = $user->timezone ?? $appTimezone;
                $weightCurrentDateTimeZone = Carbon::parse(now()->toDateTimeString(), $user->timezone);

                // attach app user role to new user
                $role = \App\Models\Role::where('slug', 'user')->first();
                $user->roles()->attach($role);

                // create profile and goals
                $user->profile()->create($request->only(['gender', 'height']));

                $birth_date = Carbon::parse($request->dob, \config('app.timezone'))->setTime(0, 0, 0);
                $now        = \now()->setTime(0, 0, 0);
                $age        = $now->diffInYears($birth_date);

                $user->profile()->update([
                    'birth_date' => $request->dob,
                    'age'        => $age,
                ]);

                //Delete all weight entry for current day before insert
                $user->weights()
                    ->where(\DB::raw("DATE(CONVERT_TZ(user_weight.log_date, '{$appTimezone}', '{$user->timezone}'))"), $weightCurrentDateTimeZone->toDateString())
                    ->get()->each->delete();

                // create weight entry for user
                $user->weights()->create([
                    'weight'   => $request->input('weight'),
                    'log_date' => now()->toDateTimeString(),
                ]);

                //Delete all user bmi entry for current day before insert
                $user->bmis()
                    ->where(\DB::raw("DATE(CONVERT_TZ(user_bmi.log_date, '{$appTimezone}', '{$user->timezone}'))"), $weightCurrentDateTimeZone->toDateString())
                    ->get()->each->delete();

                // calculate bmi and store
                $bmi = $request->input('weight') / pow(($request->input('height') / 100), 2);

                $user->bmis()->create([
                    'bmi'      => $bmi,
                    'weight'   => $request->input('weight'), // kg
                    'height'   => $request->input('height'), // cm
                    'age'      => $age,
                    'log_date' => now()->toDateTimeString(),
                ]);

                $categoriesData = Category::where('in_activity_level', 1)->pluck('id')->toArray();

                if (!empty($categoriesData)) {
                    $user->expertiseLevels()->attach($categoriesData, ['expertise_level' => "beginner"]);
                }

                // attach team to user
                $user->teams()->attach($team, ['company_id' => $team->company_id, 'department_id' => $team->department_id]);

                // sync user with survey users if any surveys for the company is active
                $user->syncWithSurveyUsers();

                // create or update user steps and calories goal
                $userGoalData = [
                    'steps'    => 6000,
                    'calories' => (($request->input('gender') == "male") ? 2500 : 2000),
                ];
                $user->goal()->updateOrCreate(['user_id' => $user->getKey()], $userGoalData);

                // set true flag in all notification modules
                $notificationModules = config('zevolifesettings.notificationModules');
                if (!empty($notificationModules)) {
                    foreach ($notificationModules as $key => $value) {
                        $user->notificationSettings()->create([
                            'module' => $key,
                            'flag'   => $value,
                        ]);
                    }
                }

                // setting up selected goals for content
                $user->userGoalTags()->detach();
                if (!empty($request->get('goals'))) {
                    $user->userGoalTags()->sync($request->get('goals'));
                }
            }

            \DB::commit();

            // call login api to make user logged in
            $headers = $request->headers->all();
            $payload = $request->all();

            $version = config('zevolifesettings.version.api_version');
            // To call internal login API - creates request object
            $loginRequest = Request::create('api/' . $version . '/auth/login', 'POST', $headers, $payload);
            // dispatch created request to requested route  to get response
            $loginResponse = Route::dispatch($loginRequest);

            return $loginResponse;
        } catch (\Exception $e) {
            \DB::rollback();
            if ($e instanceof \Illuminate\Validation\ValidationException) {
                throw $e;
            }
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }
}
