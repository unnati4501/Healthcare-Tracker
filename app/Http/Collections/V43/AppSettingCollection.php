<?php

namespace App\Http\Collections\V43;

use App\Http\Resources\V11\GethelpResource;
use App\Models\AppTheme;
use App\Models\AppSetting;
use App\Models\Challenge;
use App\Models\Company;
use App\Models\CompanyBranding;
use App\Models\CpFeatures;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use App;

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
        $user           = \Auth::guard('api')->user();
        $userData       = (!empty($user)) ? $user->getExtraFields() : [];
        $appThemeFile   = config('zevolifesettings.app_theme_path');
        $appEnvironment = app()->environment();
        $dataToReturn   = [];

        $defaultThemeJson         = readFileToSpaces($appThemeFile['dark']);
        $defaultThemeJson         = (!empty($defaultThemeJson)) ? json_decode($defaultThemeJson) : [];
        $dataToReturn['appTheme'] = $defaultThemeJson;

        if ($this->collection->count() > 0) {
            foreach ($this->collection as $setting) {
                if ($setting->type == 'file') {
                    if ($setting->key == 'splash_image_url') {
                        $dataToReturn[$setting->key] = $setting->getMediaData($setting->key, ['w' => 640, 'h' => 1280, 'zc' => 1]);
                    } elseif ($setting->key == 'logo_image_url') {
                        $dataToReturn[$setting->key] = $setting->getMediaData($setting->key, ['w' => 320, 'h' => 320, 'ct' => 1]);
                    } else {
                        $dataToReturn[$setting->key] = $setting->getMediaData($setting->key, ['w' => 320, 'h' => 320]);
                    }
                } elseif ($setting->type == 'radio') {
                    $dataToReturn[$setting->key] = (!empty($setting->value)) ;
                } elseif ($setting->type == 'list') {
                    if ($setting->key == 'app_theme') {
                        $defaultAppSetting     = AppSetting::all()->pluck('value', 'key')->toArray();
                        $defaultAppThemeName   = (!empty($defaultAppSetting) && isset($defaultAppSetting['app_theme'])) ? $defaultAppSetting['app_theme'] : 'dark';
                        $appThemeName          = $defaultAppThemeName;
                        
                        if (Schema::hasTable('app_themes') && !App::environment(['local'])) {
                            $appTheme         = AppTheme::where('slug', $appThemeName)->first();
                            $path             = $appTheme->getFirstMediaPath('theme');
                            $path             = (!empty($path) ? "{$path}" : (isset($appThemeFile[$defaultAppThemeName]) ? $appThemeFile[$defaultAppThemeName] : $appThemeFile['dark']));
                            $appThemeJson     = readFileToSpaces($path);
                        } else {
                            $appThemeName     = (isset($appThemeFile[$appThemeName]) ? $appThemeFile[$appThemeName] : $appThemeFile['dark']);
                            $appThemeJson     = readFileToSpaces($appThemeName);
                        }
                        $appThemeJson = (!empty($appThemeJson)) ? json_decode($appThemeJson) : [];
                        $dataToReturn['appTheme'] = $appThemeJson;
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
                    foreach ($companywiseAppSetting as $value) {
                        if ($value->type == 'file') {
                            if ($value->key == 'splash_image_url') {
                                $dataToReturn[$value->key] = $value->getMediaData($value->key, ['w' => 640, 'h' => 1280, 'zc' => 1]);
                            } elseif ($value->key == 'logo_image_url') {
                                $dataToReturn[$value->key] = $value->getMediaData($value->key, ['w' => 320, 'h' => 320, 'ct' => 1]);
                            } else {
                                $dataToReturn[$value->key] = $value->getMediaData($value->key, ['w' => 320, 'h' => 320]);
                            }
                        } elseif ($value->type == 'radio') {
                            $dataToReturn[$value->key] = (!empty($value->value)) ;
                        } elseif ($value->type == 'list') {
                            if ($setting->key == 'app_theme') {
                                if (Schema::hasTable('app_themes') && !App::environment(['local'])) {
                                    $appTheme                 = AppTheme::where('slug', $value->value)->first();
                                    $path                     = $appTheme->getFirstMediaPath('theme');
                                    $path                     = (!empty($path) ? "{$path}" : $appThemeFile['dark']);
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
                    ->whereRaw("CONVERT_TZ(challenges.end_date, ?, ?) >= ?",[
                        $appTimezone,$userTimeZone,$lastActivityAt
                    ])
                    ->whereRaw("CONVERT_TZ(challenges.end_date, ?, ?) <= ?",[
                        $appTimezone,$userTimeZone,$currentTime
                    ])
                    ->groupBy("challenge_wise_user_log.challenge_id")
                    ->get();

                $participatedChallenges = [];
                if ($challengeData->count() > 0) {
                    foreach ($challengeData as $value) {
                        $participatedChallenges[] = [
                            "id"       => $value->id,
                            "title"    => $value->title,
                            "iswin"    => !empty($value->is_winner) && $value->is_winner,
                            "type"     => $value->challenge_type,
                            "teamName" => "",
                        ];
                    }
                }

                $userData['completedChallenges'] = $participatedChallenges;

                $spotlightChallenge = Challenge::select("challenges.id", "challenges.title", "teams.name AS teamName", DB::raw('"true" AS iswin'), "challenges.challenge_type AS type", DB::raw('max(challenge_wise_team_ponits.points) AS max_points'))
                    ->join('challenge_wise_team_ponits', function ($join) {
                        $join->on('challenge_wise_team_ponits.challenge_id', '=', 'challenges.id')
                            ->whereRaw('round(challenge_wise_team_ponits.points, 1) > 0');
                    })
                    ->join("teams", "teams.id", "=", "challenge_wise_team_ponits.team_id")
                    ->join('challenge_participants', 'challenge_participants.challenge_id', '=', 'challenges.id')
                    ->where(function ($query) use ($team) {
                        $query->where('challenge_participants.team_id', $team->id);
                    })
                    ->whereIn('challenges.challenge_type', ['inter_company', 'company_goal', 'team'])
                    ->whereRaw("CONVERT_TZ(challenges.end_date, ?, ?) >= ?",[
                        $appTimezone,$userTimeZone,now($userTimeZone)->toDateTimeString()
                    ])
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
            $checkDTAccess              = false;
            if (!empty($user)) {
                $checkDTAccess = getCompanyPlanAccess($user, 'digital-therapy');
            }
            $dataToReturn['eapTab'] = $checkDTAccess;
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

        if (!empty($this->planFeatureList) && $request->header('X-Device-Os') != 'portal') {
            $userData['planFeatureList'] = $this->planFeatureList;
        }

        if ($request->header('X-Device-Os') == 'portal') {
            $origin                              = strtolower($request->header('origin', ""));
            $portalDomain                        = !empty($origin) ? parse_url($origin)['host'] : "";
            $branding                            = CompanyBranding::where('portal_domain', $portalDomain)->first();
            $brandingData['access']              = false;
            $brandingData['showConsentCheckbox'] = true;
            $faviconIcon['width']                = config('zevolifesettings.imageConversions.company.portal_favicon_icon.width');
            $faviconIcon['height']               = config('zevolifesettings.imageConversions.company.portal_favicon_icon.height');
            $portalFooterLogo['width']           = config('zevolifesettings.imageConversions.company.portal_footer_logo.width');
            $portalFooterLogo['height']          = config('zevolifesettings.imageConversions.company.portal_footer_logo.height');

            $brandingData['theme'] = 'purple';

            if (!empty($branding)) {
                $company                            = Company::find($branding->company_id);
                $parentCompany                      = !is_null($company->parent_id) ? Company::find($company->parent_id) : $company;
                $brandingData['access']             = true;
                $brandingData['title']              = $branding->portal_title;
                $brandingData['description']        = $branding->portal_description;
                $brandingData['subDescription']     = (!empty($branding->portal_sub_description) ? $branding->portal_sub_description : null);
                $brandingData['terms_url']          = $branding->terms_url;
                $brandingData['privacy_policy_url'] = $branding->privacy_policy_url;
                $brandingData['theme']              = $branding->portal_theme;
                if (!empty($company->getFirstMedia('portal_favicon_icon')->name)) {
                    $faviconIcon = $company->getMediaData('portal_favicon_icon', ['w' => 40, 'h' => 40]);
                } else {
                    $faviconIcon['url'] = config('zevolifesettings.fallback_image_url.company.zevo_favicon_icon');
                }
                $brandingData['portalLogoMain']        = $company->getMediaData('portal_logo_main', ['w' => 200, 'h' => 100]);
                $brandingData['portalLogoOptional']    = $company->getMediaData('portal_logo_optional', ['w' => 250, 'h' => 100]);
                $brandingData['portalBackgroundImage'] = $company->getMediaData('portal_background_image', ['w' => 1350, 'h' => 900]);
                $brandingData['portalFavicon']         = $faviconIcon;

                if (!empty($company->getFirstMedia('portal_homepage_logo_left')->name)) {
                    $portalHomepageLogoLeft = $company->getMediaData('portal_homepage_logo_left', ['w' => 200, 'h' => 100]);
                } else {
                    $portalHomepageLogoLeft = $company->getMediaData('portal_logo_main', ['w' => 200, 'h' => 100]);
                }

                if (!empty($company->getFirstMedia('portal_homepage_logo_right')->name)) {
                    $portalHomepageLogoRight = $company->getMediaData('portal_homepage_logo_right', ['w' => 250, 'h' => 100]);
                } else {
                    $portalHomepageLogoRight = $company->getMediaData('portal_logo_optional', ['w' => 250, 'h' => 100]);
                }

                $brandingData['portalHomePageLogoLeft']  = $portalHomepageLogoLeft;
                $brandingData['portalHomePageLogoRight'] = $portalHomepageLogoRight;

                if (!empty($user)) {
                    $childCompany = $user->company->first();
                    if (!empty($childCompany->branding->portal_footer_text)) {
                        $brandingData['portalFooterText'] = $childCompany->branding->portal_footer_text;
                    } else if (!empty($parentCompany->branding->portal_footer_text)) {
                        $brandingData['portalFooterText'] = $parentCompany->branding->portal_footer_text;
                    }

                    if (!empty($childCompany->branding->portal_footer_header_text)) {
                        $brandingData['portalFooterHeaderText'] = $childCompany->branding->portal_footer_header_text;
                    } else if (!empty($parentCompany->branding->portal_footer_header_text)) {
                        $brandingData['portalFooterHeaderText'] = $parentCompany->branding->portal_footer_header_text;
                    }

                    if (!empty($childCompany->branding->portal_footer_json)) {
                        $brandingData['portalFooterJson'] = json_decode($childCompany->branding->portal_footer_json, true);
                    } else if (!empty($parentCompany->branding->portal_footer_json)) {
                        $brandingData['portalFooterJson'] = json_decode($parentCompany->branding->portal_footer_json, true);
                    }

                    if (!empty($childCompany->getFirstMedia('portal_footer_logo'))) {
                        $portalFooterLogo = $childCompany->getMediaData('portal_footer_logo', ['w' => 180, 'h' => 60]);
                    } else if (!empty($parentCompany->getFirstMedia('portal_footer_logo'))) {
                        $portalFooterLogo = $parentCompany->getMediaData('portal_footer_logo', ['w' => 180, 'h' => 60]);
                    } else {
                        $portalFooterLogo['url'] = config('zevolifesettings.fallback_image_url.company.portal_footer_logo');
                    }
                    $brandingData['portalFooterLogo'] = $portalFooterLogo;
                }

                $brandingData['showConsentCheckbox'] = false;

                if (in_array($parentCompany->code, [316026, 210955, 279751])) {
                    if (!empty($company->getFirstMedia('portal_favicon_icon')->name)) {
                        $faviconIcon = $company->getMediaData('portal_favicon_icon', ['w' => 40, 'h' => 40]);
                    } else {
                        $faviconIcon['url'] = config('zevolifesettings.fallback_image_url.company.prod_favicon_icon');
                    }
                    $brandingData['portalFavicon'] = $faviconIcon;
                }
                if (!empty($user)) {
                    $checkDTAccess = getCompanyPlanAccess($user, 'digital-therapy');
                } else if (!empty($company)) {
                    $checkDTAccess = getCompanyPlanAccess([], 'digital-therapy', $company);
                }
                $brandingData['excludeGenderAndDob']   = ($branding->exclude_gender_and_dob == 1 );
                $brandingData['manageTheDesignChange'] = ($branding->manage_the_design_change == 1 );
                $companyPlan                           = $company->companyplan()->first();
                $companyPlanGroupType                  = ($request->header('X-Device-Os') == config('zevolifesettings.PORTAL') ? 2 : 1);
                $featuresList                          = [];
                $defaultPlan                           = config('zevolifesettings.default_plan');
                if (!empty($companyPlan)) {
                    $companyPlanFeature = $companyPlan->planFeatures()->select('feature_id')->get()->pluck('feature_id')->toArray();
                } else {
                    $companyPlanFeature = DB::table('cp_plan_features')->select('feature_id')->where('plan_id', $defaultPlan)->get()->pluck('feature_id')->toArray();
                }
                $parentFeatures = CpFeatures::select('id', 'parent_id', 'name', 'slug', 'manage')->where('parent_id', null)->where('group', $companyPlanGroupType)->get();
                foreach ($parentFeatures as $value) {
                    $result = CpFeatures::select('id', 'name', 'slug')->where('parent_id', $value->id)->get()->toArray();
                    if (!empty($result)) {
                        $tempArray = [];
                        foreach ($result as $childvalue) {
                            $slug             = str_replace('-', '_', $childvalue['slug']);
                            $tempArray[$slug] = (in_array($childvalue['id'], $companyPlanFeature));
                        }
                        $slug                = str_replace('-', '_', $value->slug);
                        $featuresList[$slug] = $tempArray;
                    } else {
                        $slug                = str_replace('-', '_', $value->slug);
                        $featuresList[$slug] = (in_array($value->id, $companyPlanFeature));
                    }
                }

                $userData['planFeatureList'] = $featuresList;
                $dataToReturn['eapTab']      = $checkDTAccess;

                //Display Digital therapy banner's title and description on 1:1 screen
                $brandingData['dtBannertitle']       = (!empty($parentCompany->branding->dt_title) ? $parentCompany->branding->dt_title : config('zevolifesettings.digital_therapy.title'));
                $brandingData['dtBannerdescription'] = (!empty($parentCompany->branding->dt_description) ? $parentCompany->branding->dt_description : config('zevolifesettings.digital_therapy.description'));

                $irishlifeCompanyCode = config('zevolifesettings.hide_sitemap_company_code.' . $appEnvironment);
                $siteMapFlag          = false;
                if (in_array($company->code, $irishlifeCompanyCode)) {
                    $siteMapFlag = true;
                }
                $brandingData['siteMapFlag'] = $siteMapFlag;

                $groupMenuCompanyCode = config('zevolifesettings.is_group_menu_for_portal.' . $appEnvironment);
                $groupMenuFlag        = false;
                $groupMenuCompanyLink = '';
                if (in_array($company->code, $groupMenuCompanyCode)) {
                    $groupMenuFlag        = true;
                    $groupMenuCompanyLink = config('zevolifesettings.is_group_menu_for_portal_link.' . $appEnvironment)[$company->code];
                }
                $brandingData['groupMenuFlag']        = $groupMenuFlag;
                $brandingData['groupMenuCompanyLink'] = $groupMenuCompanyLink;

                $isGACompanyCode = config('zevolifesettings.is_google_analytics_for_portal_tiktok.' . $appEnvironment);
                $isGATiktokFlag  = false;
                if (in_array($company->code, $isGACompanyCode)) {
                    $isGATiktokFlag = true;
                }
                $brandingData['isGATiktokFlag'] = $isGATiktokFlag;
            }
            $brandingData['theme']        = !is_null($brandingData['theme']) ? $brandingData['theme'] : 'purple';
            $dataToReturn['brandingData'] = $brandingData;

            $dataToReturn['cookieBot'] = false;
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
