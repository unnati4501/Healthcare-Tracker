<?php

namespace App\Http\Collections\V12;

use App\Http\Resources\V11\GethelpResource;
use App\Models\AppTheme;
use App\Models\Challenge;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class AppSettingCollection extends ResourceCollection
{
    protected $forceUpdateCount;
    protected $deviceOs;
    protected $lastActivityAt;
    /**
     * Create a new resource instance.
     *
     * @param  mixed  $resource
     * @return void
     */
    public function __construct($resource, $forceUpdateCount = false, $deviceOs = "", $lastActivityAt = "", $appSettingCollection = [])
    {
        $this->forceUpdateCount     = $forceUpdateCount;
        $this->deviceOs             = $deviceOs;
        $this->lastActivityAt       = $lastActivityAt;
        $this->appSettingCollection = $appSettingCollection;

        parent::__construct($resource);
    }

    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $user         = \Auth::guard('api')->user();
        $userData     = (!empty($user)) ? $user->getExtraFields() : [];
        $appThemeFile = config('zevolifesettings.app_theme_path');
        $dataToReturn = [];

        $defaultThemeJson         = readFileToSpaces($appThemeFile['dark']);
        $defaultThemeJson         = (!empty($defaultThemeJson)) ? json_decode($defaultThemeJson) : [];
        $dataToReturn['appTheme'] = $defaultThemeJson;
        if ($this->collection->count() > 0) {
            foreach ($this->collection as $setting) {
                if ($setting->type == 'file') {
                    if (($setting->key == 'splash_image_url')) {
                        $dataToReturn[$setting->key] = $setting->getMediaData($setting->key, ['w' => 640, 'h' => 1280, 'zc' => 1]);
                    } elseif (($setting->key == 'logo_image_url')) {
                        $dataToReturn[$setting->key] = $setting->getMediaData($setting->key, ['w' => 320, 'h' => 320, 'ct' => 1]);
                    } else {
                        $dataToReturn[$setting->key] = $setting->getMediaData($setting->key, ['w' => 320, 'h' => 320]);
                    }
                } elseif ($setting->type == 'radio') {
                    $dataToReturn[$setting->key] = (!empty($setting->value));
                } elseif ($setting->type == 'list') {
                    if ($setting->key == 'app_theme') {
                        if (Schema::hasTable('app_themes')) {
                            $appTheme                 = AppTheme::where('slug', $setting->value)->first();
                            $path                     = $appTheme->getFirstMediaPath('theme');
                            $folder                   = config('filesystems.disks.spaces.root');
                            $path                     = (!empty($path) ? "{$folder}/{$path}" : $appThemeFile['dark']);
                            $themeJson                = readFileToSpaces($path);
                            $themeJson                = (!empty($themeJson)) ? json_decode($themeJson) : [];
                            $dataToReturn['appTheme'] = $themeJson;
                        } else {
                            $themeName                = (isset($appThemeFile[$setting->value]) ? $appThemeFile[$setting->value] : $appThemeFile['dark']);
                            $themeJson                = readFileToSpaces($themeName);
                            $themeJson                = (!empty($themeJson)) ? json_decode($themeJson) : [];
                            $dataToReturn['appTheme'] = $themeJson;
                        }
                    }
                } else {
                    $dataToReturn[$setting->key] = (!empty($setting->value) ? $setting->value : "");
                }
            }
        }

        if (!empty($user)) {
            $company = $user->company->first();
            $team    = $user->teams()->first();
            $dept    = $user->department()->first();

            if (!empty($company)) {
                $dataToReturn['userRestriction'] = $company->group_restriction;
                $companywiseAppSetting           = $company->companywiseAppSetting()->get();
                if ($companywiseAppSetting->count() > 0) {
                    foreach ($companywiseAppSetting as $value) {
                        if ($value->type == 'file') {
                            if (($value->key == 'splash_image_url')) {
                                $dataToReturn[$value->key] = $value->getMediaData($value->key, ['w' => 640, 'h' => 1280, 'zc' => 1]);
                            } elseif (($value->key == 'logo_image_url')) {
                                $dataToReturn[$value->key] = $value->getMediaData($value->key, ['w' => 320, 'h' => 320, 'ct' => 1]);
                            } else {
                                $dataToReturn[$value->key] = $value->getMediaData($value->key, ['w' => 320, 'h' => 320]);
                            }
                        } elseif ($value->type == 'radio') {
                            $dataToReturn[$value->key] = (!empty($value->value));
                        } elseif ($value->type == 'list') {
                            if ($setting->key == 'app_theme') {
                                if (Schema::hasTable('app_themes')) {
                                    $appTheme                 = AppTheme::where('slug', $value->value)->first();
                                    $path                     = $appTheme->getFirstMediaPath('theme');
                                    $folder                   = config('filesystems.disks.spaces.root');
                                    $path                     = (!empty($path) ? "{$folder}/{$path}" : $appThemeFile['dark']);
                                    $themeJson                = readFileToSpaces($path);
                                    $themeJson                = (!empty($themeJson)) ? json_decode($themeJson) : [];
                                    $dataToReturn['appTheme'] = $themeJson;
                                } else {
                                    $themeName                = (isset($appThemeFile[$value->value]) ? $appThemeFile[$value->value] : $appThemeFile['dark']);
                                    $themeJson                = readFileToSpaces($themeName);
                                    $themeJson                = (!empty($themeJson)) ? json_decode($themeJson) : [];
                                    $dataToReturn['appTheme'] = $themeJson;
                                }
                            }
                        } else {
                            $dataToReturn[$value->key] = (!empty($value->value) ? $value->value : "");
                        }
                    }
                }
            }

            if (!empty($team) && !empty($userData)) {
                $userData['company']['id']   = $company->getKey();
                $userData['company']['name'] = $company->name;

                $userData['department']['id']   = $dept->getKey();
                $userData['department']['name'] = $dept->name;

                $userData['team']['id']   = $team->getKey();
                $userData['team']['name'] = $team->name;
            }

            if (!empty($this->lastActivityAt)) {
                $appTimezone    = config('app.timezone');
                $userTimeZone   = $user->timezone;
                $lastActivityAt = Carbon::parse($this->lastActivityAt, $appTimezone)->setTimezone($user->timezone)->todatetimeString();
                $currentTime    = Carbon::now()->timezone($userTimeZone)->todatetimeString();
                $challengeData  = Challenge::select("challenges.id", "challenges.title", "challenges.challenge_type", "challenge_wise_user_log.is_winner")
                    ->join("challenge_wise_user_log", "challenge_wise_user_log.challenge_id", "=", "challenges.id")
                    ->where("challenges.challenge_type", "!=", "individual")
                    ->where("challenge_wise_user_log.user_id", $user->id)
                    ->where("finished", true)
                    ->whereNotNull("challenge_wise_user_log.finished_at")
                    ->where(\DB::raw("CONVERT_TZ(challenges.end_date, '{$appTimezone}', '{$userTimeZone}')"), ">=", $lastActivityAt)
                    ->where(\DB::raw("CONVERT_TZ(challenges.end_date, '{$appTimezone}', '{$userTimeZone}')"), "<=", $currentTime)
                    ->groupBy("challenge_wise_user_log.challenge_id")
                    ->get();

                $participatedChallenges = [];
                if ($challengeData->count() > 0) {
                    foreach ($challengeData as $value) {
                        $participatedChallenges[] = [
                            "id"    => $value->id,
                            "title" => $value->title,
                            "iswin" => (!empty($value->is_winner) && $value->is_winner),
                            "type"  => $value->challenge_type,
                        ];
                    }
                }

                $userData['completedChallenges'] = $participatedChallenges;
            }
            $dataToReturn['eapSetting'] = new GethelpResource($user);
        } else {
            $dataToReturn['eapSetting'] = config('zevolifesettings.eapSetting');
        }

        if (!isset($dataToReturn['logo_image_url'])) {
            $dataToReturn['logo_image_url'] = $setting->getMediaData('logo_image_url', ['w' => 320, 'h' => 320, 'ct' => 1]);
        }

        $userData['ios_force_update'] = $userData['android_force_update'] = false;
        if ($this->deviceOs == 'android' && $this->forceUpdateCount >= 1) {
            $userData['android_force_update'] = true;
        } elseif ($this->deviceOs == 'ios' && $this->forceUpdateCount >= 1) {
            $userData['ios_force_update'] = true;
        }

        return [
            'data'               => (!empty($userData)) ? array_merge($dataToReturn, $userData) : $dataToReturn,
            'companyLabelString' => $this->appSettingCollection,
        ];
    }

    public function withResponse($request, $response)
    {
        $jsonResponse = json_decode($response->getContent(), true);
        unset($jsonResponse['links'], $jsonResponse['meta']);
        $response->setContent(json_encode($jsonResponse));
    }
}
