<?php

namespace App\Http\Collections\V27;

use App\Http\Resources\V11\GethelpResource;
use App\Models\AppTheme;
use App\Models\Challenge;
use App\Models\Company;
use App\Models\CompanyBranding;
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
    public function __construct($resource, $forceUpdateCount = false, $deviceOs = "", $lastActivityAt = "", $appSettingCollection = [], $planFeatureList = [])
    {
        $this->forceUpdateCount     = $forceUpdateCount;
        $this->deviceOs             = $deviceOs;
        $this->lastActivityAt       = $lastActivityAt;
        $this->appSettingCollection = $appSettingCollection;
        $this->planFeatureList      = $planFeatureList;

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
        $appThemes    = [];

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
                    $dataToReturn[$setting->key] = (!empty($setting->value)) ? true : false;
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

        // eventEnable flag
        $dataToReturn['eventEnable']         = false;
        $dataToReturn['challengeCacheTimer'] = config('zevolifesettings.SPCronScheduleTime');

        if (!empty($user)) {
            $company = $user->company->first();
            $team    = $user->teams()->first();
            $dept    = $user->department()->first();

            if (!empty($company)) {
                $dataToReturn['eventEnable']     = $company->enable_event;
                $dataToReturn['userRestriction'] = $company->group_restriction;
                $companywiseAppSetting           = $company->companywiseAppSetting()->get();
                if ($companywiseAppSetting->count() > 0) {
                    foreach ($companywiseAppSetting as $key => $value) {
                        if ($value->type == 'file') {
                            if (($value->key == 'splash_image_url')) {
                                $dataToReturn[$value->key] = $value->getMediaData($value->key, ['w' => 640, 'h' => 1280, 'zc' => 1]);
                            } elseif (($value->key == 'logo_image_url')) {
                                $dataToReturn[$value->key] = $value->getMediaData($value->key, ['w' => 320, 'h' => 320, 'ct' => 1]);
                            } else {
                                $dataToReturn[$value->key] = $value->getMediaData($value->key, ['w' => 320, 'h' => 320]);
                            }
                        } elseif ($value->type == 'radio') {
                            $dataToReturn[$value->key] = (!empty($value->value)) ? true : false;
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

                $challengeData = Challenge::select("challenges.id", "challenges.title", "challenges.challenge_type", "challenge_wise_user_log.is_winner")
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
                            "id"       => $value->id,
                            "title"    => $value->title,
                            "iswin"    => (!empty($value->is_winner) && $value->is_winner == true) ? true : false,
                            "type"     => $value->challenge_type,
                            "teamName" => "",
                        ];
                    }
                }

                $userData['completedChallenges'] = $participatedChallenges;

                $spotlightChallenge = Challenge::select("challenges.id", "challenges.title", "teams.name AS teamName", DB::raw('"true" AS iswin'), "challenges.challenge_type AS type", DB::raw('max(challenge_wise_team_ponits.points) AS max_points'))
                    ->join('challenge_wise_team_ponits', function ($join) use ($appTimezone, $userTimeZone) {
                        $join->on('challenge_wise_team_ponits.challenge_id', '=', 'challenges.id')
                            ->whereRaw('round(challenge_wise_team_ponits.points, 1) > 0');
                    })
                    ->join("teams", "teams.id", "=", "challenge_wise_team_ponits.team_id")
                    ->join('challenge_participants', 'challenge_participants.challenge_id', '=', 'challenges.id')
                    ->where(function ($query) use ($team) {
                        $query->where('challenge_participants.team_id', $team->id);
                    })
                    ->whereIn('challenges.challenge_type', ['inter_company', 'company_goal', 'team'])
                    ->where(DB::raw("CONVERT_TZ(challenges.end_date, '{$appTimezone}', '{$userTimeZone}')"), ">=", now($userTimeZone)->toDateTimeString())
                    ->orderByRaw('max(challenge_wise_team_ponits.points) DESC')
                    ->orderBy('challenge_wise_team_ponits.team_id', 'ASC')
                    ->groupBy("challenge_wise_team_ponits.challenge_id")
                    ->get();

                $spotlightChallengeArray = [];
                if ($spotlightChallenge->count() > 0) {
                    foreach ($spotlightChallenge as $value) {
                        $spotlightChallengeArray[] = [
                            "id"       => $value->id,
                            "title"    => $value->title,
                            "iswin"    => true,
                            "type"     => $value->type,
                            "teamName" => $value->teamName,
                        ];
                    }
                }
                $userData['spotlightChallenge'] = $spotlightChallengeArray;
            }
            $dataToReturn['eapSetting'] = new GethelpResource($user);
            $dataToReturn['eapTab']     = ($company->eap_tab) ? true : false;
        } else {
            $dataToReturn['eapSetting'] = config('zevolifesettings.eapSetting');
            $dataToReturn['eapTab']     = false;
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

        if (!empty($user->social_id)) {
            $userData['socialId']   = $user->social_id;
            $userData['socialType'] = (int) $user->social_type;
        }

        if (!empty($this->planFeatureList)) {
            $userData['planFeatureList'] = $this->planFeatureList;
        }

        if ($request->header('X-Device-Os') == 'portal') {
            $origin                 = strtolower($request->header('origin', ""));
            $portalDomain           = !empty($origin) ? parse_url($origin)['host'] : "";
            $branding               = CompanyBranding::where('portal_domain', $portalDomain)->first();
            $brandingData['access'] = false;
            $brandingData['theme']  = 'purple';
            if (!empty($branding)) {
                $company                     = Company::find($branding->company_id);
                $parentCompany               = !is_null($company->parent_id) ? Company::find($company->parent_id) : $company;
                $brandingData['access']      = true;
                $brandingData['title']       = $branding->portal_title;
                $brandingData['description'] = $branding->portal_description;
                $brandingData['theme']       = $branding->portal_theme;
                if (!in_array($parentCompany->code, [316026, 210955, 279751])) {
                    $brandingData['portalLogoMain']        = $company->getMediaData('portal_logo_main', ['w' => 200, 'h' => 100]);
                    $brandingData['portalLogoOptional']    = $company->getMediaData('portal_logo_optional', ['w' => 250, 'h' => 100]);
                    $brandingData['portalBackgroundImage'] = $company->getMediaData('portal_background_image', ['w' => 1350, 'h' => 900]);
                }
            }
            $brandingData['theme']        = !is_null($brandingData['theme']) ? $brandingData['theme'] : 'purple';
            $dataToReturn['brandingData'] = $brandingData;
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
