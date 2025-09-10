<?php declare (strict_types = 1);

namespace App\Http\Controllers\API\V37;

use App\Http\Collections\V8\RecommendationCollection;
use App\Http\Collections\V12\PortalHomeSectionWebinarCollection;
use App\Http\Collections\V25\PortalHomeEventListCollection;
use App\Http\Controllers\API\V25\PortalHomeController as v25PortalHomeController;
use App\Models\Course;
use App\Models\Event;
use App\Models\EventRegisteredUserLog;
use App\Models\Feed;
use App\Models\Goal;
use App\Models\MeditationTrack;
use App\Models\Recipe;
use App\Models\User;
use App\Models\Webinar;
use App\Models\ZcSurveyLog;
use App\Models\Company;
use App\Models\CompanyBrandingContactDetails;
use DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class PortalHomeController extends v25PortalHomeController
{
    /**
     * Home page section for portal
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function portalHome(Request $request)
    {
        try {
            $data              = array();
            $recomendedSection = [];
            $user              = $this->user();
            $userProfile       = $user->profile()->select('id', 'points')->first();
            $company           = $user->company()
                ->select('companies.id', 'companies.parent_id', 'companies.is_reseller')->first();
            $userSelectedGoal = $user->userGoalTags()->pluck("goals.id")->toArray();
            $goalObj          = new Goal();
            $goalRecords      = $goalObj->getAssociatedGoalTags();
            $role             = getUserRole();
            $appTimezone      = config('app.timezone');
            $timezone         = (!empty($user->timezone) ? $user->timezone : $appTimezone);
            $now              = now($timezone)->toDateTimeString();
            $appEnvironment   = app()->environment();

            // Portal points
            $data['points'] = ($userProfile->points ?? 0);

            // Feed List get max 10 feed for home portal
            $feedRecords = Feed::join('feed_company', function ($join) use ($company) {
                $join->on('feeds.id', '=', 'feed_company.feed_id')
                    ->where('feed_company.company_id', '=', $company->getKey());
            });

            $feedRecords->join('sub_categories', function ($join) {
                $join->on('sub_categories.id', '=', 'feeds.sub_category_id');
            })
                ->leftJoin('companies', 'companies.id', '=', 'feeds.company_id')
                ->select('feeds.*', 'sub_categories.name AS sub_category_name');
            $feedRecords->selectRaw("(CASE feeds.type WHEN 1 THEN 'feed_audio' WHEN 2 THEN 'feed_video' WHEN 3 THEN 'feed_youtube' WHEN 4 THEN 'feed' WHEN 5 THEN 'feed_vimeo' ELSE 'feed' END) as 'goalContentType'");

            if ($role->group == 'company' && is_null($company->parent_id) && !$company->is_reseller) {
                $feedRecords->addSelect(DB::raw("CASE
                            WHEN feeds.company_id = " . $company->id . " AND feeds.is_stick != 0 then 0
                            WHEN feeds.company_id IS NULL AND feeds.is_stick != '' then 1
                            ELSE 2
                            END AS is_stick_count"));
            } else {
                if ($company->parent_id == null && $company->is_reseller) {
                    $feedRecords->addSelect(DB::raw("CASE
                            WHEN feeds.company_id = " . $company->id . " AND feeds.is_stick != 0 then 0
                            WHEN feeds.company_id IS NULL AND feeds.is_stick != '' then 1
                            ELSE 2
                            END AS is_stick_count"));
                } elseif (!is_null($company->parent_id)) {
                    $feedRecords->addSelect(DB::raw("CASE
                            WHEN feeds.company_id = " . $company->id . " AND feeds.is_stick != 0 then 0
                            WHEN companies.parent_id IS NULL AND feeds.company_id IS NOT NULL AND feeds.is_stick != 0 then 1
                            WHEN feeds.company_id IS NULL AND feeds.is_stick != 0 then 2
                            ELSE 3
                            END AS is_stick_count"));
                }
            }

            $feedRecords->where(function (Builder $query) use ($timezone) {
                return $query->where(\DB::raw("CONVERT_TZ(feeds.start_date, 'UTC', feeds.timezone)"), '<=', \DB::raw("CONVERT_TZ(now(), @@session.time_zone , feeds.timezone)"))->orWhere('feeds.start_date', null);
            })->where(function (Builder $query) use ($timezone) {
                return $query->where(\DB::raw("CONVERT_TZ(feeds.end_date, 'UTC', feeds.timezone)"), '>=', \DB::raw("CONVERT_TZ(now(), @@session.time_zone , feeds.timezone)"))->orWhere('feeds.end_date', null);
            });

            $userGoalFeed = clone $feedRecords;

            if (!empty($userSelectedGoal)) {
                $userGoalFeed = $userGoalFeed
                    ->join('feed_tag', function ($join) {
                        $join->on('feed_tag.feed_id', '=', 'feeds.id');
                    })
                    ->whereIn("feed_tag.goal_id", $userSelectedGoal)
                    ->groupBy('feeds.id')
                    ->get();
                if ($userGoalFeed->isNotEmpty() && $userGoalFeed->count() > 3) {
                    $userGoalFeed = $userGoalFeed->random(3);
                }

                $userGoalRecipe = Recipe::select("recipe.*", DB::raw("'recipe' goalContentType"))
                    ->join('recipe_tag', function ($join) {
                        $join->on('recipe_tag.recipe_id', '=', 'recipe.id');
                    })
                    ->join('recipe_company', function ($join) use ($company) {
                        $join->on('recipe_company.recipe_id', '=', 'recipe.id')
                            ->where('recipe_company.company_id', $company->id);
                    })
                    ->where("recipe.status", true)
                    ->whereIn("recipe_tag.goal_id", $userSelectedGoal)
                    ->groupBy('recipe.id')
                    ->get();
                if ($userGoalRecipe->isNotEmpty() && $userGoalRecipe->count() > 3) {
                    $userGoalRecipe = $userGoalRecipe->random(3);
                }

                $userGoalMeditation = MeditationTrack::select("meditation_tracks.*", DB::raw("'meditation' goalContentType"))
                    ->join('meditation_tracks_tag', function ($join) {
                        $join->on('meditation_tracks_tag.meditation_track_id', '=', 'meditation_tracks.id');
                    })
                    ->join('meditation_tracks_company', function ($join) use ($company) {
                        $join->on('meditation_tracks_company.meditation_track_id', '=', 'meditation_tracks.id')
                            ->where('meditation_tracks_company.company_id', $company->id);
                    })
                    ->whereIn("meditation_tracks_tag.goal_id", $userSelectedGoal)
                    ->groupBy('meditation_tracks.id')
                    ->get();
                if ($userGoalMeditation->isNotEmpty() && $userGoalMeditation->count() > 3) {
                    $userGoalMeditation = $userGoalMeditation->random(3);
                }

                $userGoalCourse = Course::select("courses.*", DB::raw("'masterclass' goalContentType"))
                    ->join('course_tag', function ($join) {
                        $join->on('course_tag.course_id', '=', 'courses.id');
                    })
                    ->join('masterclass_company', function ($join) use ($company) {
                        $join->on('masterclass_company.masterclass_id', '=', 'courses.id')
                            ->where('masterclass_company.company_id', $company->id);
                    })
                    ->where("courses.status", true)
                    ->whereIn("course_tag.goal_id", $userSelectedGoal)
                    ->groupBy('courses.id')
                    ->get();
                if ($userGoalCourse->isNotEmpty() && $userGoalCourse->count() > 3) {
                    $userGoalCourse = $userGoalCourse->random(3);
                }

                // Get Webinar Records
                $userGoalWebinar = Webinar::select("webinar.*", DB::raw("'webinar' goalContentType"))
                    ->join('webinar_tag', function ($join) {
                        $join->on('webinar_tag.webinar_id', '=', 'webinar.id');
                    })
                    ->join('webinar_company', function ($join) use ($company) {
                        $join->on('webinar_company.webinar_id', '=', 'webinar.id')
                            ->where('webinar_company.company_id', $company->id);
                    })
                    ->whereIn("webinar_tag.goal_id", $userSelectedGoal)
                    ->groupBy('webinar.id')
                    ->get();
                if ($userGoalWebinar->isNotEmpty() && $userGoalWebinar->count() > 3) {
                    $userGoalWebinar = $userGoalWebinar->random(3);
                }

                $recomendedCollection = new Collection();
                $recomendedCollection = $recomendedCollection->merge($userGoalCourse);
                $recomendedCollection = $recomendedCollection->merge($userGoalFeed);
                $recomendedCollection = $recomendedCollection->merge($userGoalMeditation);
                $recomendedCollection = $recomendedCollection->merge($userGoalRecipe);
                $recomendedCollection = $recomendedCollection->merge($userGoalWebinar);

                if ($recomendedCollection->isNotEmpty()) {
                    $recomendedSection = new RecommendationCollection($recomendedCollection);
                }
            }
            $data['showRecommendation'] = (($goalRecords->count() > 0 && count($recomendedSection) > 0) );
            $data['goalsSelected']      = (!empty($userSelectedGoal)) ;
            $data['recommendation']     = $recomendedSection;

            // Portal home display feed name
            $feedHomePageRecords = clone $feedRecords;
            $feedHomePageRecords = $feedHomePageRecords->groupBy('feeds.id')
                ->orderBy('is_stick_count', 'ASC')
                ->orderBy('feeds.id', 'DESC')
                ->first();
            if ($feedHomePageRecords) {
                $data['latestFeed'] = [
                    'id'       => $feedHomePageRecords->id,
                    'title'    => $feedHomePageRecords->title,
                    'subtitle' => (!empty($feedHomePageRecords->subtitle) ? $feedHomePageRecords->subtitle : ''),
                    'banner'   => $feedHomePageRecords->getMediaData('featured_image', ['w' => 1280, 'h' => 640, 'zc' => 3]),
                ];
            } else {
                $data['latestFeed'] = [
                    'id'       => '',
                    'title'    => '',
                    'subtitle' => '',
                    'banner'   => '',
                ];
            }

            // Top 10 Upcomming Events
            $checkAlreadyRegisterd = EventRegisteredUserLog::where('user_id', $user->id)->pluck('event_booking_log_id')->toArray();

            $eventRecords = Event::select(
                'events.id',
                'event_booking_logs.id as booking_id',
                'events.creator_id',
                'events.subcategory_id',
                'events.name',
                'events.duration',
                'events.location_type',
                'events.capacity',
                'event_booking_logs.presenter_user_id',
                DB::raw('concat("<p>",events.description,"</p>",IFNULL(event_booking_logs.notes, "")) as description'),
                'event_booking_logs.booking_date',
                'event_booking_logs.start_time',
                'event_booking_logs.meta',
                'events.created_at',
                DB::raw('(SELECT count(event_registered_users_logs.id) FROM event_registered_users_logs INNER JOIN user_team ON user_team.user_id = event_registered_users_logs.user_id WHERE event_registered_users_logs.event_booking_log_id = event_booking_logs.id AND user_team.company_id = ' . $company->id . ' ) as registered_users')
            )
                ->join('event_companies', function ($join) use ($company) {
                    $join->on('event_companies.event_id', '=', 'events.id')
                        ->where('event_companies.company_id', '=', $company->id);
                })
                ->join('event_booking_logs', function ($join) use ($company) {
                    $join->on('event_booking_logs.event_id', '=', 'events.id')
                        ->where('event_booking_logs.company_id', '=', $company->id);
                })
                ->whereNotIn('event_booking_logs.id', $checkAlreadyRegisterd)
                ->where('event_booking_logs.status', '4')
                ->where('events.status', '2')
                // condition to check if the event is ongoaing and user is not registered then event record should be removed from listing AND in case of seat full events, records should not appear for other users who are not registered
                ->whereRaw("('{$now}' BETWEEN CONVERT_TZ(CONCAT(event_booking_logs.booking_date, ' ', event_booking_logs.start_time), '{$appTimezone}', '{$timezone}') AND ADDTIME(CONVERT_TZ(CONCAT(event_booking_logs.booking_date, ' ', event_booking_logs.start_time), '{$appTimezone}', '{$timezone}'), events.duration)) != 1 AND (capacity IS NOT NULL AND capacity > 0 AND ((SELECT IFNULL(COUNT(event_registered_users_logs.user_id), 0) FROM event_registered_users_logs WHERE event_registered_users_logs.is_cancelled = 0 AND event_registered_users_logs.event_booking_log_id = event_booking_logs.id) >= capacity)) != 1")
                ->groupBy('event_booking_logs.id')
                ->orderBy('event_booking_logs.booking_date', 'ASC')
                ->orderBy('events.created_at', 'DESC')
                ->limit(10)
                ->get();

            $data['upcomingWorkshop'] = new PortalHomeEventListCollection($eventRecords, true);

            // Get Workshop Top 10
            $webinar = Webinar::select('webinar.*')
                ->join('webinar_category', function ($join) {
                    $join->on('webinar_category.webinar_id', '=', 'webinar.id');
                })
                ->join('webinar_company', function ($join) use ($company) {
                    $join->on('webinar_company.webinar_id', '=', 'webinar.id')
                        ->where('webinar_company.company_id', $company->id);
                })
                ->orderByRaw("`webinar`.`updated_at` DESC")
                ->limit(10)
                ->get();

            $data['webinar'] = new PortalHomeSectionWebinarCollection($webinar);

            // check is there any active survey for logged in user's company
            $zcsurveylog = ZcSurveyLog::select('id')
                ->where('company_id', $company->id)
                ->where(\DB::raw("CONVERT_TZ(roll_out_date, '{$appTimezone}', '{$timezone}')"), '<=', $now)
                ->where(\DB::raw("CONVERT_TZ(expire_date, '{$appTimezone}', '{$timezone}')"), '>=', $now)
                ->first();
            $data['availableSurveyLogId'] = (!empty($zcsurveylog) ? $zcsurveylog->id : 0);
            
            // Contact Us branding data
            if(!empty($user)){
                $userCompany                           = $user->company->first();
                $companyDetails                        = Company::where('id', $userCompany->id)->first();
                $brandingContactDetails                = CompanyBrandingContactDetails::where('company_id', $companyDetails->id)->first();
                $brandingContactDetailsParent          = CompanyBrandingContactDetails::where('company_id', $companyDetails->parent_id)->first();
                $brandingData['contactUsHeader']       = (!empty($brandingContactDetails->contact_us_header) ? $brandingContactDetails->contact_us_header : $brandingContactDetailsParent->contact_us_header);
                $brandingData['contactUsRequest']      = (!empty($brandingContactDetails->contact_us_request) ? $brandingContactDetails->contact_us_request : $brandingContactDetailsParent->contact_us_request);
                $brandingData['contactUsDescription']  = (!empty($brandingContactDetails->contact_us_description) ? $brandingContactDetails->contact_us_description : $brandingContactDetailsParent->contact_us_description);
                if (!empty($brandingContactDetails) && !empty($brandingContactDetails->getFirstMedia('contact_us_image')->name)) {
                    $contactUsImage = $brandingContactDetails->getMediaData('contact_us_image', ['w' => 800, 'h' => 800]);
                } else if (!empty($brandingContactDetailsParent->getFirstMedia('contact_us_image')->name)) {
                    $contactUsImage = $brandingContactDetailsParent->getMediaData('contact_us_image', ['w' => 800, 'h' => 800]);
                }
                $brandingData['contactUsImage'] = $contactUsImage;

                // Display portal Footer for parent and child companis
                $userCompany    = $user->company->first();
                $parentCompany  = !is_null($userCompany->parent_id) ? Company::find($userCompany->parent_id) : $userCompany;
                if (!empty($userCompany->branding->portal_footer_text)) {
                    $brandingData['portalFooterText'] = $userCompany->branding->portal_footer_text;
                } else if (!empty($parentCompany->branding->portal_footer_text)) {
                    $brandingData['portalFooterText'] = $parentCompany->branding->portal_footer_text;
                }

                if (!empty($userCompany->branding->portal_footer_header_text)) {
                    $brandingData['portalFooterHeaderText'] = $userCompany->branding->portal_footer_header_text;
                } else if (!empty($parentCompany->branding->portal_footer_header_text)) {
                    $brandingData['portalFooterHeaderText'] = $parentCompany->branding->portal_footer_header_text;
                }

                if (!empty($userCompany->branding->portal_footer_json)) {
                    $brandingData['portalFooterJson'] = json_decode($userCompany->branding->portal_footer_json, true);
                } else if (!empty($parentCompany->branding->portal_footer_json)) {
                    $brandingData['portalFooterJson'] = json_decode($parentCompany->branding->portal_footer_json, true);
                }

                if (!empty($userCompany->getFirstMedia('portal_footer_logo'))) {
                    $portalFooterLogo = $userCompany->getMediaData('portal_footer_logo', ['w' => 180, 'h' => 60]);
                } else if (!empty($parentCompany->getFirstMedia('portal_footer_logo'))) {
                    $portalFooterLogo = $parentCompany->getMediaData('portal_footer_logo', ['w' => 180, 'h' => 60]);
                } else {
                    $portalFooterLogo['width']   = config('zevolifesettings.imageConversions.company.portal_footer_logo.width');
                    $portalFooterLogo['height']  = config('zevolifesettings.imageConversions.company.portal_footer_logo.height');
                    $portalFooterLogo['url']    = config('zevolifesettings.fallback_image_url.company.portal_footer_logo');
                }
                $brandingData['portalFooterLogo'] = $portalFooterLogo;
                
                // Set group link
                $groupMenuCompanyCode = config('zevolifesettings.is_group_menu_for_portal.' . $appEnvironment);
                $groupMenuFlag        = false;
                $groupMenuCompanyLink = '';
                if (in_array($userCompany->code, $groupMenuCompanyCode)) {
                   
                    $groupMenuFlag        = true;
                    $groupMenuCompanyLink = config('zevolifesettings.is_group_menu_for_portal_link.' . $appEnvironment)[$userCompany->code];
                }
                $brandingData['groupMenuFlag']        = $groupMenuFlag;
                $brandingData['groupMenuCompanyLink'] = $groupMenuCompanyLink;

                $isGACompanyCode = config('zevolifesettings.is_google_analytics_for_portal_tiktok.' . $appEnvironment);
                $isGATiktokFlag  = false;
                if (in_array($userCompany->code, $isGACompanyCode)) {
                    $isGATiktokFlag = true;
                }
                $brandingData['isGATiktokFlag'] = $isGATiktokFlag;

                $data['brandingData']          = $brandingData ?? [];
            }
            return $this->successResponse(['data' => $data], 'Data retrieved successfully.');
        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }
}
