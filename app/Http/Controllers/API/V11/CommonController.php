<?php declare (strict_types = 1);

namespace App\Http\Controllers\API\V11;

use App\Http\Collections\V4\SubCategoryCollection as v4subcategorycollection;
use App\Http\Collections\V6\HomeCourseCollection;
use App\Http\Collections\V11\FeedListCollection;
use App\Http\Collections\V8\RecommendationCollection;
use App\Http\Collections\V11\HomeLeaderboardCollection;
use App\Http\Controllers\API\V10\CommonController as v10CommonController;
use App\Http\Resources\V1\SurveyListResource;
use App\Http\Traits\ProvidesAuthGuardTrait;
use App\Http\Traits\ServesApiTrait;
use App\Models\Category;
use App\Models\CompanyWiseLabelString;
use App\Models\Course;
use App\Models\EAP;
use App\Models\Feed;
use App\Models\Goal;
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

class CommonController extends v10CommonController
{
    use ServesApiTrait, ProvidesAuthGuardTrait;

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

            $latestRecipe = Recipe::where('status', 1)
                ->where(function ($query) use ($company) {
                    return $query->where('company_id', null)
                        ->orWhere('company_id', $company->id);
                })
                ->orderBy('id', 'DESC')
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
                ->where(\DB::raw("DATE(CONVERT_TZ(user_listened_tracks.created_at, '{$appTimezone}', '{$timezone}'))"), '=', now($timezone)->toDateString())
                ->count();

            $latestMeditation = MeditationTrack::orderBy('id', 'DESC')
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

    /**
     * Home leaderboard screen
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function homeLeaderboard(Request $request)
    {
        try {
            $user    = $this->user();
            $company = $user->company()->first();

            $days  = isset($request->days) ? (int) $request->days : 7;
            $end   = Carbon::today()->subDay()->toDateTimeString();
            $start = Carbon::parse($end)->subDays($days)->toDateTimeString();

            $companyUsersList = $company->members()
                ->where('users.is_blocked', 0)
                ->where('users.can_access_app', 1)
                ->where('users.step_last_sync_date_time', '>', $start)
                ->pluck('users.id')
                ->toArray();

            $companyUsersList = (count($companyUsersList) > 0) ? $companyUsersList : [0];

            $companyUserList = implode(',', $companyUsersList);

            $results         = \DB::select("SELECT temp.*, @rownum:=@rownum+1 AS rank_no FROM (SELECT @rownum := 0) AS dummy CROSS JOIN (SELECT users.id, CONCAT(users.first_name,' ',users.last_name) AS name, SUM(user_step.steps) AS steps, user_step.created_at FROM users LEFT JOIN user_step ON user_step.user_id = users.id WHERE user_step.user_id IN (" . $companyUserList . ") AND user_step.log_date BETWEEN '" . $start . "' AND '" . $end . "' AND steps > 0 GROUP BY user_step.user_id ORDER BY steps DESC, user_step.created_at ASC LIMIT 5) AS temp");

            $records = user::hydrate($results);

            // Check current user in result or not.
            $recordsArray  = $records->toArray();

            $loginUserName = $user->first_name . ' ' . $user->last_name;
            $isUsers       = in_array($loginUserName, array_column($recordsArray, 'name'));
            if (!$isUsers) {
                // Get rank no from list of user step
                $getCurrentUserNumber = \DB::select("SELECT zcs.user_id, @rownum:=@rownum+1 AS rank_no
                                            FROM (SELECT @rownum := 0) AS dummy
                                            CROSS JOIN (
                                            SELECT `user_step`.`user_id`, sum(`user_step`.`steps`) as steps from user_step INNER JOIN user_team ON `user_team`.`user_id` = `user_step`.`user_id` WHERE `user_team`.`company_id` = '".$company->id."' AND `user_step`.`log_date` BETWEEN '" . $start . "' AND '" . $end . "' AND `user_step`.`steps` > 0 GROUP BY `user_step`.`user_id` ORDER BY steps DESC, user_step.created_at ASC
                                            ) AS zcs");

                $getResults = user::hydrate($getCurrentUserNumber)->pluck('rank_no', 'user_id')->toArray();

                $userRank = (array_key_exists($user->id, $getResults)) ? $getResults[$user->id] : null;

                // Get current user records with user step.
                $recordsUser = User::leftJoin('user_step', 'user_step.user_id', '=', 'users.id')
                    ->where('user_step.user_id', $user->id)
                    ->select('users.id', \DB::raw("CONCAT(first_name,' ',last_name) AS name"), \DB::raw("SUM(user_step.steps) as steps"))
                    ->whereBetween('user_step.log_date', [$start, $end])
                    ->first();

                if ($recordsUser && $userRank) {
                    // Bind with original records.
                    $loginUserArray = array([
                        'id'      => $recordsUser->id,
                        'name'    => $recordsUser->name,
                        'rank_no' => $userRank,
                        'steps'   => $recordsUser->steps,
                    ]);

                    $finalUsersArray = user::hydrate($loginUserArray);

                    $records->push($finalUsersArray[0]);
                }
            }

            $data = new HomeLeaderboardCollection($records);

            return $this->successResponse(['data' => $data], 'Home Leaderboard data retrieved successfully.');
        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * Set view count
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function setViewCount(Request $request, $id, $modelType)
    {
        try {
            // logged-in user
            $user      = $this->user();
            $tableName = "";

            switch ($modelType) {
                case 'feed':
                    $modelData = Feed::find($id);
                    $tableName = "feeds";
                    break;
                case 'meditation':
                    $modelData = MeditationTrack::find($id);
                    $tableName = "meditation_tracks";
                    break;
                case 'recipe':
                    $modelData = Recipe::find($id);
                    $tableName = "recipe";
                    break;
                case 'eap':
                    $modelData = EAP::find($id);
                    $tableName = "eap_logs";
                    break;
                case 'webinar':
                    $modelData = Webinar::find($id);
                    $tableName = "webinar";
                    break;
                default:
                    return $this->notFoundResponse("Requested data not found");
                    break;
            }

            if (!empty($modelData)) {
                if ($modelType == 'feed') {
                    $pivotExsisting = $modelData->feedUserLogs()->wherePivot('user_id', $user->getKey())->wherePivot('feed_id', $modelData->getKey())->first();
                } elseif ($modelType == 'meditation') {
                    $pivotExsisting = $modelData->trackUserLogs()->wherePivot('user_id', $user->getKey())->wherePivot('meditation_track_id', $modelData->getKey())->first();
                } elseif ($modelType == 'recipe') {
                    $pivotExsisting = $modelData->recipeUserLogs()->wherePivot('user_id', $user->getKey())->wherePivot('recipe_id', $modelData->getKey())->first();
                } elseif ($modelType == 'eap') {
                    $pivotExsisting = $modelData->eapUserLogs()->wherePivot('user_id', $user->getKey())->wherePivot('eap_id', $modelData->getKey())->first();
                } elseif ($modelType == 'webinar') {
                    $pivotExsisting = $modelData->webinarUserLogs()->wherePivot('user_id', $user->getKey())->wherePivot('webinar_id', $modelData->getKey())->first();
                }

                $updateCount = false;
                if (!empty($pivotExsisting)) {
                    if ($pivotExsisting->pivot->view_count < 2) {
                        $pivotExsisting->pivot->view_count = $pivotExsisting->pivot->view_count + 1;
                        $pivotExsisting->pivot->save();
                        $updateCount = true;
                    }
                } else {
                    if ($modelType == 'feed') {
                        $modelData->feedUserLogs()->attach($user, ['view_count' => 1]);
                    } elseif ($modelType == 'meditation') {
                        $modelData->trackUserLogs()->attach($user, ['view_count' => 1]);
                    } elseif ($modelType == 'recipe') {
                        $modelData->recipeUserLogs()->attach($user, ['view_count' => 1]);
                    } elseif ($modelType == 'eap') {
                        $modelData->eapUserLogs()->attach($user, ['view_count' => 1]);
                    } elseif ($modelType == 'webinar') {
                        $modelData->webinarUserLogs()->attach($user, ['view_count' => 1]);
                    }
                    $updateCount = true;
                }

                $view_count = $modelData->view_count;
                if ($updateCount) {
                    $view_count = $modelData->view_count + 1;

                    DB::table($tableName)
                        ->where("id", $modelData->id)
                        ->increment('view_count');
                }

                return $this->successResponse(['data' => ['viewCount' => $view_count]], 'View Count updated successfully.');
            } else {
                return $this->notFoundResponse("Requested data not found");
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
                $favouritedCount = $user->userTrackrLogs()->wherePivot('favourited', true)->count();

                if ($xDeviceOs == config('zevolifesettings.PORTAL')) {
                    $subcategoryRecords = $records->filter(function ($item, $key) use ($category) {
                        return $item->meditations()->count() > 0;
                    });
                    $subcategoryRecords = $subcategoryRecords->pluck("name", "id")->toArray();
                } else {
                    $subcategoryRecords = $records->pluck("name", "id")->toArray();
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
                            return $item->webinar()->count() > 0;
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
            }

            return $this->successResponse(($records->count() > 0) ? new v4subcategorycollection($records) : ['data' => []], 'Sub Categories Received Successfully.');
        } catch (\Exception $e) {
            \DB::rollback();
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }
}
