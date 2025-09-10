<?php declare (strict_types = 1);

namespace App\Http\Controllers\API\V13;

use App\Http\Collections\V8\RecommendationCollection;
use App\Http\Collections\V12\EventListCollection;
use App\Http\Collections\V12\PortalHomeSectionWebinarCollection;
use App\Http\Controllers\API\V12\PortalHomeController as v12PortalHomeController;
use App\Http\Traits\ProvidesAuthGuardTrait;
use App\Http\Traits\ServesApiTrait;
use App\Models\Course;
use App\Models\Event;
use App\Models\EventRegisteredUserLog;
use App\Models\Feed;
use App\Models\Goal;
use App\Models\MeditationTrack;
use App\Models\Recipe;
use App\Models\User;
use App\Models\Webinar;
use App\Models\ZcSurveyResponse;
use DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class PortalHomeController extends v12PortalHomeController
{
    use ServesApiTrait, ProvidesAuthGuardTrait;

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
            $company           = $user->company()->first();
            $userSelectedGoal  = $user->userGoalTags()->pluck("goals.id")->toArray();
            $goalObj           = new Goal();
            $goalRecords       = $goalObj->getAssociatedGoalTags();
            $role              = getUserRole();
            $appTimezone       = config('app.timezone');
            $timezone          = $user->timezone ?? config('app.timezone');

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
            $feedRecords->selectRaw("(CASE feeds.type WHEN 1 THEN 'feed_audio' WHEN 2 THEN 'feed_video' WHEN 3 THEN 'feed_youtube' WHEN 4 THEN 'feed' ELSE 'feed' END) as 'goalContentType'");

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
            $data['showRecommendation'] = ($goalRecords->count() > 0 && count($recomendedSection) > 0);
            $data['recommendation']     = $recomendedSection;
            // Portal home display feed name
            $feedHomePageRecords = clone $feedRecords;
            $feedHomePageRecords = $feedHomePageRecords->groupBy('feeds.id')
                ->orderBy('is_stick_count', 'ASC')
                ->orderBy('feeds.id', 'DESC')
                ->first();
            if ($feedHomePageRecords) {
                $data['latestFeed'] = [
                    'id'    => $feedHomePageRecords->id,
                    'title' => $feedHomePageRecords->title,
                ];
            } else {
                $data['latestFeed'] = [
                    'id'    => '',
                    'title' => '',
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
                'events.location_type',
                'events.capacity',
                'event_booking_logs.presenter_user_id',
                DB::raw('concat("<p>",events.description,"</p>",IFNULL(event_booking_logs.notes, "")) as description'),
                'event_booking_logs.booking_date',
                'event_booking_logs.start_time',
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
                ->groupBy('event_booking_logs.id')
                ->orderBy('event_booking_logs.booking_date', 'ASC')
                ->orderBy('events.created_at', 'DESC')
                ->limit(10)
                ->get();

            $data['upcomingWorkshop'] = new EventListCollection($eventRecords, true);

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

            return $this->successResponse(['data' => $data], 'Data retrieved successfully.');
        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }
    /**
     * Wellbeing recommendation section for portal
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function wellbeingRecommendation(Request $request)
    {
        try {
            $user              = $this->user();
            $company           = $user->company()->first();
            $role              = getUserRole();
            $appTimezone       = config('app.timezone');
            $timezone          = $user->timezone ?? config('app.timezone');
            $data              = array();
            $recomendedSection = [];

            $surveyCategoryGoalTags = ZcSurveyResponse::select('zc_categories_tag.goal_id')
                ->join('zc_categories_tag', 'zc_categories_tag.categories_id', '=', 'zc_survey_responses.category_id')
                ->where('zc_survey_responses.user_id', $user->id)
                ->groupBy('zc_categories_tag.goal_id')
                ->get()
                ->pluck('goal_id')
                ->toArray();

            $data['showRecommendation'] = (count($surveyCategoryGoalTags) > 0 && count($surveyCategoryGoalTags) > 0);
            if (!empty($surveyCategoryGoalTags)) {
                // Get Feeds Records
                $feedRecords = Feed::join('feed_company', function ($join) use ($company) {
                    $join->on('feeds.id', '=', 'feed_company.feed_id')
                        ->where('feed_company.company_id', '=', $company->getKey());
                })
                    ->join('sub_categories', function ($join) {
                        $join->on('sub_categories.id', '=', 'feeds.sub_category_id');
                    })
                    ->leftJoin('companies', 'companies.id', '=', 'feeds.company_id')
                    ->select('feeds.id', "feeds.title", 'sub_categories.name AS sub_category_name', "feeds.deep_link_uri")
                    ->selectRaw("(CASE feeds.type WHEN 1 THEN 'feed_audio' WHEN 2 THEN 'feed_video' WHEN 3 THEN 'feed_youtube' WHEN 4 THEN 'feed' ELSE 'feed' END) as 'goalContentType'");

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

                $userGoalFeed = $feedRecords
                    ->join('feed_tag', function ($join) {
                        $join->on('feed_tag.feed_id', '=', 'feeds.id');
                    })
                    ->whereIn("feed_tag.goal_id", $surveyCategoryGoalTags)
                    ->groupBy('feeds.id')
                    ->get();
                if ($userGoalFeed->isNotEmpty() && $userGoalFeed->count() > 3) {
                    $userGoalFeed = $userGoalFeed->random(3);
                }

                // Get Recipe Records
                $userGoalRecipe = Recipe::select("recipe.id", "recipe.title", "recipe.deep_link_uri", DB::raw("'recipe' goalContentType"))
                    ->join('recipe_tag', function ($join) {
                        $join->on('recipe_tag.recipe_id', '=', 'recipe.id');
                    })
                    ->join('recipe_company', function ($join) use ($company) {
                        $join->on('recipe_company.recipe_id', '=', 'recipe.id')
                            ->where('recipe_company.company_id', $company->id);
                    })
                    ->where("recipe.status", true)
                    ->whereIn("recipe_tag.goal_id", $surveyCategoryGoalTags)
                    ->groupBy('recipe.id')
                    ->get();
                if ($userGoalRecipe->isNotEmpty() && $userGoalRecipe->count() > 3) {
                    $userGoalRecipe = $userGoalRecipe->random(3);
                }

                // Get meditation records
                $userGoalMeditation = MeditationTrack::select("meditation_tracks.id", "meditation_tracks.title", "meditation_tracks.sub_category_id", "meditation_tracks.deep_link_uri", DB::raw("'meditation' goalContentType"))
                    ->join('meditation_tracks_tag', function ($join) {
                        $join->on('meditation_tracks_tag.meditation_track_id', '=', 'meditation_tracks.id');
                    })
                    ->join('meditation_tracks_company', function ($join) use ($company) {
                        $join->on('meditation_tracks_company.meditation_track_id', '=', 'meditation_tracks.id')
                            ->where('meditation_tracks_company.company_id', $company->id);
                    })
                    ->whereIn("meditation_tracks_tag.goal_id", $surveyCategoryGoalTags)
                    ->groupBy('meditation_tracks.id')
                    ->get();
                if ($userGoalMeditation->isNotEmpty() && $userGoalMeditation->count() > 3) {
                    $userGoalMeditation = $userGoalMeditation->random(3);
                }

                // Get masterclass records
                $userGoalCourse = Course::select("courses.id", "courses.title", "courses.sub_category_id", "courses.deep_link_uri", DB::raw("'masterclass' goalContentType"))
                    ->join('course_tag', function ($join) {
                        $join->on('course_tag.course_id', '=', 'courses.id');
                    })
                    ->join('masterclass_company', function ($join) use ($company) {
                        $join->on('masterclass_company.masterclass_id', '=', 'courses.id')
                            ->where('masterclass_company.company_id', $company->id);
                    })
                    ->where("courses.status", true)
                    ->whereIn("course_tag.goal_id", $surveyCategoryGoalTags)
                    ->groupBy('courses.id')
                    ->get();
                if ($userGoalCourse->isNotEmpty() && $userGoalCourse->count() > 3) {
                    $userGoalCourse = $userGoalCourse->random(3);
                }

                // Get Webinar Records
                $userGoalWebinar = Webinar::select("webinar.id", "webinar.title", "webinar.sub_category_id", "webinar.deep_link_uri", DB::raw("'webinar' goalContentType"))
                    ->join('webinar_tag', function ($join) {
                        $join->on('webinar_tag.webinar_id', '=', 'webinar.id');
                    })
                    ->join('webinar_company', function ($join) use ($company) {
                        $join->on('webinar_company.webinar_id', '=', 'webinar.id')
                            ->where('webinar_company.company_id', $company->id);
                    })
                    ->whereIn("webinar_tag.goal_id", $surveyCategoryGoalTags)
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

                $data['recommendation'] = $recomendedSection;
            }

            return $this->successResponse(['data' => $data], 'Data retrieved successfully.');
        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }
}
