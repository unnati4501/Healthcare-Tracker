<?php declare (strict_types = 1);

namespace App\Http\Controllers\API\V13;

use App\Http\Collections\V4\SubCategoryCollection as v4subcategorycollection;
use App\Http\Collections\V6\HomeCourseCollection;
use App\Http\Collections\V8\RecommendationCollection;
use App\Http\Collections\V11\FeedListCollection;
use App\Http\Controllers\API\V12\CommonController as v12CommonController;
use App\Http\Requests\Api\V8\NpsFeedBackRequest;
use App\Http\Resources\V1\SurveyListResource;
use App\Http\Traits\ProvidesAuthGuardTrait;
use App\Http\Traits\ServesApiTrait;
use App\Models\Category;
use App\Models\Course;
use App\Models\Feed;
use App\Models\Goal;
use App\Models\Group;
use App\Models\HsSurvey;
use App\Models\MeditationTrack;
use App\Models\Recipe;
use App\Models\SubCategory;
use App\Models\User;
use App\Models\Webinar;
use Carbon\Carbon;
use DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class CommonController extends v12CommonController
{
    use ServesApiTrait, ProvidesAuthGuardTrait;

    /**
     *store nps feed back given by the user
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeNPSAppTabFeedback(NpsFeedBackRequest $request)
    {
        try {
            \DB::beginTransaction();
            $xDeviceOs = strtolower($request->header('X-Device-Os', ""));
            // logged-in user
            $user = $this->user();

            $userNpsData = $user->npsSurveyLinkLogs()->whereNull("survey_received_on")
                ->where("user_id", $user->id);
            if ($xDeviceOs == config('zevolifesettings.PORTAL')) {
                $userNpsData->where('is_portal', '1');
            } else {
                $userNpsData->where('is_portal', '0');
            }
            $userNpsData = $userNpsData->orderBy("id", "DESC")
                ->first();

            $isPortal = ($xDeviceOs == config('zevolifesettings.PORTAL')) ? '1' : '0';

            if (!empty($userNpsData)) {
                $npsData = [
                    'feedback_type'      => $request->feedbackType,
                    'feedback'           => $request->feedback,
                    'survey_received_on' => now()->toDateTimeString(),
                    'is_portal'          => $isPortal,
                ];
                $userNpsData->update($npsData);
                \DB::commit();
                return $this->successResponse(['data' => ["isFeedBackSubmitted" => false]], "Thanks, we really appreciate your feedback.");
            } else {
                \DB::rollback();
                return $this->successResponse(['data' => ["isFeedBackSubmitted" => true]], "Thanks, we really appreciate your feedback. Why not leave a review on the App store?");
            }
        } catch (\Exception $e) {
            \DB::rollback();
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * Get list of master categories
     *
     * @param Request $request, Category $category
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSubCategories(Request $request, Category $category)
    {
        try {
            $xDeviceOs = strtolower($request->header('X-Device-Os', ""));
            $records   = SubCategory::where('category_id', $category->id)
                ->orderBy('is_excluded', 'DESC')
                ->get();

            if ($category->id == 4) {
                $user            = $this->user();
                $company         = $user->company()->first();
                $favouritedCount = $user->userTrackrLogs()->wherePivot('favourited', true)->count();

                if ($xDeviceOs == config('zevolifesettings.PORTAL')) {
                    $subcategoryRecords = $records->filter(function ($item, $key) use ($category, $company) {

                        return $item->meditations()->join('meditation_tracks_company', function ($join) use ($company) {
                            $join->on('meditation_tracks_company.meditation_track_id', '=', 'meditation_tracks.id')
                                ->where('meditation_tracks_company.company_id', $company->id);
                        })->count() > 0;
                    });
                    $subcategoryRecords = $subcategoryRecords->pluck("name", "id")->toArray();
                } else {
                    $subcategoryRecords = $records->pluck("name", "id")->toArray();
                    $subcategoryRecords = array(-1 => "All") + $subcategoryRecords;
                }

                if ($favouritedCount > 0) {
                    $records = array(0 => "My ❤️") + $subcategoryRecords;
                } else {
                    $records = $subcategoryRecords + array(0 => "My ❤️");
                }

                $new_array = array_map(function ($id, $name) {
                    return array(
                        'id'         => $id,
                        'name'       => $name,
                        'short_name' => str_replace(' ', '_', strtolower($name)),
                    );
                }, array_keys($records), $records);

                $records = SubCategory::hydrate($new_array);
            }

            if ($xDeviceOs == config('zevolifesettings.PORTAL')) {
                $records = $records->filter(function ($item, $key) use ($category) {
                    switch ($category->short_name) {
                        case 'group':
                            if ($item->short_name == 'public') {
                                return true;
                            }
                            return $item->groups()
                                ->where('is_archived', 0)
                                ->where('is_visible', 1)
                                ->count() > 0;
                            break;
                        case 'course':
                            $users     = $this->user();
                            $companyId = $users->company()->first()->id;

                            $categoryCount = Course::where("sub_category_id", $item->id)
                                ->join('masterclass_company', function ($join) use ($companyId) {
                                    $join->on('masterclass_company.masterclass_id', '=', 'courses.id')
                                        ->where('masterclass_company.company_id', $companyId);
                                })
                                ->where("courses.status", true)
                                ->count();
                            return ($categoryCount > 0) ? $item : [];
                            break;
                        case 'feed':
                            $users     = $this->user();
                            $companyId = $users->company()->first()->id;
                            $timezone  = $user->timezone ?? config('app.timezone');
                            $feedCount = Feed::where("sub_category_id", $item->id)
                                ->join('feed_company', function ($join) use ($companyId) {
                                    $join->on('feeds.id', '=', 'feed_company.feed_id')
                                        ->where('feed_company.company_id', '=', $companyId);
                                })
                                ->join('sub_categories', function ($join) {
                                    $join->on('sub_categories.id', '=', 'feeds.sub_category_id');
                                })
                                ->where(function (Builder $query) use ($timezone) {
                                    return $query->where(\DB::raw("CONVERT_TZ(feeds.start_date, 'UTC', feeds.timezone)"), '<=', \DB::raw("CONVERT_TZ(now(), @@session.time_zone , feeds.timezone)"))->orWhere('feeds.start_date', null);
                                })
                                ->where(function (Builder $query) use ($timezone) {
                                    return $query->where(\DB::raw("CONVERT_TZ(feeds.end_date, 'UTC', feeds.timezone)"), '>=', \DB::raw("CONVERT_TZ(now(), @@session.time_zone , feeds.timezone)"))->orWhere('feeds.end_date', null);
                                })
                                ->where("feeds.sub_category_id", $item->id)
                                ->count();

                            return ($feedCount > 0) ? $item : [];
                            break;
                        case 'recipe':
                            return $item->recipes()->count() > 0;
                            break;
                        case 'webinar':
                            $user          = $this->user();
                            $company       = $user->company()->first();
                            $categoryCount = Webinar::where("sub_category_id", $item->id)
                                ->join('webinar_company', function ($join) use ($company) {
                                    $join->on('webinar_company.webinar_id', '=', 'webinar.id')
                                        ->where('webinar_company.company_id', $company->id);
                                })
                                ->count();
                            return ($categoryCount > 0) ? $item : [];
                            break;
                        default:
                            return true;
                            break;
                    }
                });
            } else {
                $records = $records->filter(function ($item, $key) use ($category) {
                    if ($category->short_name == 'group') {
                        if ($item->short_name == 'public') {
                            return true;
                        }
                        return $item->groups()
                            ->where('is_archived', 0)
                            ->where('is_visible', 1)
                            ->count() > 0;
                    } else {
                        return true;
                    }
                });

                if ($category->short_name == 'course' || $category->short_name == 'recipe' || $category->short_name == 'webinar') {
                    $records = array(-1 => "All") + $records->pluck("name", "id")->toArray();

                    $new_array = array_map(function ($id, $name) {
                        return array(
                            'id'         => $id,
                            'name'       => $name,
                            'short_name' => str_replace(' ', '_', strtolower($name)),
                        );
                    }, array_keys($records), $records);

                    $records = SubCategory::hydrate($new_array);
                }
            }

            return $this->successResponse(($records->count() > 0) ? new v4subcategorycollection($records) : ['data' => []], 'Sub Categories Received Successfully.');
        } catch (\Exception $e) {
            \DB::rollback();
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * Home statistics for move nourish inspire sliders
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getHomeStatistics(Request $request)
    {
        try {
            $user             = $this->user();
            $company          = $user->company()->first();
            $appTimezone      = config('app.timezone');
            $timezone         = $user->timezone ?? config('app.timezone');
            $data             = array();
            $userSelectedGoal = $user->userGoalTags()->pluck("goals.id")->toArray();
            $role             = getUserRole();

            // Health score survey
            $usersurveyData = HsSurvey::where('user_id', $user->id)
                ->whereNotNull('survey_complete_time')
                ->orderBy('id', 'DESC')
                ->first();

            $headers = $request->headers->all();
            $payload = $request->all();

            if (!empty($usersurveyData)) {
                $version               = config('zevolifesettings.version.api_version');
                $surveyHistoryRequest  = Request::create("api/" . $version . "/healthscore/report/" . $usersurveyData->id, 'GET', $headers, $payload);
                $surveyHistoryResponse = \Route::dispatch($surveyHistoryRequest);
                $surveyHistoryBody     = json_decode($surveyHistoryResponse->getContent());
            }

            if (!empty($surveyHistoryBody)) {
                $surveyHistoryBody->result->data->surveyId = $usersurveyData->id;
                $data['surveyinfo']                        = new SurveyListResource($surveyHistoryBody);
            }

            // User statistics data for current day
            $userCalorieHistory = $user->steps()->select(\DB::raw("SUM(user_step.calories) as calories"), \DB::raw("SUM(user_step.steps) as steps"))
                ->where(\DB::raw("DATE(CONVERT_TZ(user_step.log_date, '{$appTimezone}', '{$timezone}'))"), '=', now($timezone)->toDateString())
                ->first();

            $companyUsersList = $company->members()->pluck('users.id')->toArray();

            $end   = Carbon::today()->subDay()->toDateTimeString();
            $start = Carbon::parse($end)->subDays(7)->toDateTimeString();

            $companyLeader = User::leftJoin('user_step', 'user_step.user_id', '=', 'users.id')
                ->whereIn('user_step.user_id', $companyUsersList)
                ->whereBetween('user_step.log_date', array($start, $end))
                ->select('users.id', \DB::raw("SUM(user_step.steps) as steps"))
                ->groupBy('user_step.user_id')
                ->orderBy('steps', 'DESC')
                ->orderBy('user_step.created_at', 'ASC')
                ->first();

            $data['userstatistics']['move'] = [
                'dailySteps' => (!empty($userCalorieHistory) && !empty($userCalorieHistory['steps'])) ? (int) $userCalorieHistory['steps'] : 0,
            ];

            if (!empty($companyLeader) && $companyLeader->steps > 0) {
                $companyLeaderArray = [
                    'id'    => $companyLeader->id,
                    'steps' => (int) $companyLeader->steps,
                    'image' => $companyLeader->getMediaData('logo', ['w' => 320, 'h' => 320, 'zc' => 0]),
                ];
                $data['userstatistics']['move']['companyLeader'] = $companyLeaderArray;
            }

            $latestRecipe = Recipe::select('recipe.id', 'recipe.title')
                ->join('recipe_company', function ($join) use ($company) {
                    $join->on('recipe_company.recipe_id', '=', 'recipe.id')
                        ->where('recipe_company.company_id', $company->id);
                })
                ->where('recipe.status', 1)
                ->orderBy('recipe.id', 'DESC')
                ->first();

            $data['userstatistics']['nourish'] = [
                'dailyCalories' => (!empty($userCalorieHistory) && !empty($userCalorieHistory['calories'])) ? (double) $userCalorieHistory['calories'] : 0.0,
            ];

            if (!empty($latestRecipe)) {
                $latestRecipeArray = [
                    'id'    => $latestRecipe->id,
                    'title' => $latestRecipe->title,
                    'image' => $latestRecipe->getMediaData('logo', ['w' => 320, 'h' => 320, 'zc' => 0]),
                ];
                $data['userstatistics']['nourish']['recipe'] = $latestRecipeArray;
            }

            $meditationCount = $user->completedMeditationTracks()
                ->join('meditation_tracks_company', function ($join) use ($company) {
                    $join->on('meditation_tracks_company.meditation_track_id', '=', 'meditation_tracks.id')
                        ->where('meditation_tracks_company.company_id', $company->id);
                })
                ->where(\DB::raw("DATE(CONVERT_TZ(user_listened_tracks.created_at, '{$appTimezone}', '{$timezone}'))"), '=', now($timezone)->toDateString())
                ->count();

            $latestMeditation = MeditationTrack::join('meditation_tracks_company', function ($join) use ($company) {
                $join->on('meditation_tracks_company.meditation_track_id', '=', 'meditation_tracks.id')
                    ->where('meditation_tracks_company.company_id', $company->id);
            })
                ->orderBy('meditation_tracks.id', 'DESC')
                ->first();

            $data['userstatistics']['inspire'] = [
                'dailyMeditations' => $meditationCount,
            ];

            if (!empty($latestMeditation)) {
                $latestMeditationArray = [
                    'id'         => $latestMeditation->id,
                    'title'      => $latestMeditation->title,
                    'categoryId' => $latestMeditation->sub_category_id,
                    'image'      => $latestMeditation->getMediaData('cover', ['w' => 320, 'h' => 320, 'zc' => 0]),
                ];
                $data['userstatistics']['inspire']['meditation'] = $latestMeditationArray;
            }

            // get user's running lessions with course data
            $runningCourseRecords = $user->courseLogs()
                ->wherePivot('joined', 1)
                ->wherePivot('started_course', 1)
                ->wherePivot('completed_on', '=', null)
                ->orderByDesc('user_course.joined_on');

            // use count based on receieved data from course API total data count must be 10 max.
            $runningCourseRecords = $runningCourseRecords->paginate(10);

            // collect required course data
            $data['masterclasses'] = new HomeCourseCollection($runningCourseRecords);

            // Feed List get max 10 feed for home statistics
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
                } else {
                    $feedRecords->addSelect(DB::raw("CASE
                            WHEN feeds.company_id = " . $company->id . " AND feeds.is_stick != 0 then 0
                            WHEN feeds.company_id IS NULL AND feeds.is_stick != '' then 1
                            ELSE 2
                            END AS is_stick_count"));
                }
            }

            $feedRecords->where(function (Builder $query) use ($timezone) {
                return $query->where(\DB::raw("CONVERT_TZ(feeds.start_date, 'UTC', feeds.timezone)"), '<=', \DB::raw("CONVERT_TZ(now(), @@session.time_zone , feeds.timezone)"))->orWhere('feeds.start_date', null);
            })
                ->where(function (Builder $query) use ($timezone) {
                    return $query->where(\DB::raw("CONVERT_TZ(feeds.end_date, 'UTC', feeds.timezone)"), '>=', \DB::raw("CONVERT_TZ(now(), @@session.time_zone , feeds.timezone)"))->orWhere('feeds.end_date', null);
                });

            $userGoalFeed = $feedRecords;
            $feedRecords  = $feedRecords
                ->groupBy('feeds.id')
                ->orderBy('is_stick_count', 'ASC')
                ->orderBy('feeds.id', 'DESC')
                ->limit(10)
                ->get();

            $data['feeds'] = new FeedListCollection($feedRecords, true);

            // $data['showRecommendation'] = true;
            $recomendedSection          = [];
            $goalObj                    = new Goal();
            $goalRecords                = $goalObj->getAssociatedGoalTags();
            $data['showRecommendation'] = ($goalRecords->count() > 0);

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

                $recomendedCollection = new Collection();
                $recomendedCollection = $recomendedCollection->merge($userGoalCourse);
                $recomendedCollection = $recomendedCollection->merge($userGoalFeed);
                $recomendedCollection = $recomendedCollection->merge($userGoalMeditation);
                $recomendedCollection = $recomendedCollection->merge($userGoalRecipe);

                if ($recomendedCollection->isNotEmpty()) {
                    $recomendedSection = new RecommendationCollection($recomendedCollection);
                }
            }
            $data['recommendation'] = $recomendedSection;

            // for custom labels
            $companyLabelString       = $company->companyWiseLabelString()->pluck('label_name', 'field_name')->toArray();
            $defaultLabelString       = config('zevolifesettings.company_label_string', []);
            $finalCompanyLabelStrings = [];

            // iterate default labels loop and check is label's custom value is set then user custom value else default value
            foreach ($defaultLabelString as $groupKey => $groups) {
                foreach ($groups as $labelKey => $labelValue) {
                    $finalCompanyLabelStrings[$labelKey] = ($companyLabelString[$labelKey] ?? $labelValue['default_value']);
                }
            }
            $data['companyLabelString'] = $finalCompanyLabelStrings;

            return $this->successResponse(['data' => $data], 'Data retrieved successfully.');
        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }
}
