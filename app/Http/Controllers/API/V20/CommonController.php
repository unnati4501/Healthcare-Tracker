<?php declare (strict_types = 1);

namespace App\Http\Controllers\API\V20;

use App\Http\Collections\V6\HomeCourseCollection;
use App\Http\Collections\V8\RecommendationCollection;
use App\Http\Collections\V20\FeedListCollection;
use App\Http\Controllers\API\V18\CommonController as v18CommonController;
use App\Http\Traits\ProvidesAuthGuardTrait;
use App\Http\Traits\ServesApiTrait;
use App\Models\Course;
use App\Models\EAP;
use App\Models\Feed;
use App\Models\Group;
use App\Models\MeditationTrack;
use App\Models\MoodUser;
use App\Models\Recipe;
use App\Models\User;
use App\Models\UserGoal;
use App\Models\Webinar;
use App\Models\ZcSurveyLog;
use App\Models\ZcSurveyResponse;
use App\Models\ZcSurveyUserLog;
use Carbon\Carbon;
use DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class CommonController extends v18CommonController
{
    use ServesApiTrait, ProvidesAuthGuardTrait;

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
                $view_count  = "";
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
                    $updateCount = false;
                    $view_count  = $modelData->view_count;
                }

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
            $timezone         = !empty($user->timezone) ? $user->timezone : $appTimezone;
            $todayDateInUTC   = now($user->timezone)->setTimezone($appTimezone)->toDateTimeString();
            $data             = [];
            $userSelectedGoal = $user->userGoalTags()->pluck("goals.id")->toArray();
            $role             = getUserRole();

            // User daily steps
            $userDailySteps = UserGoal::select('steps')->where('user_id', $user->id)->first();

            // first check does user has submitted any survey till date
            $hasSubmittedSurvey = ZcSurveyResponse::select('id')
                ->where('zc_survey_responses.user_id', $user->id)->limit(1)->first();
            $checkForPrevoiusSurveyScoreIfAny = false;

            // if submitted survey previously then show score and isSubmitted to true
            if (!empty($hasSubmittedSurvey)) {
                // check does he has any active survey
                $zcsurveylog = ZcSurveyLog::select('id', 'survey_to_all')
                    ->where('company_id', $company->id)
                    ->where('roll_out_date', '<=', $todayDateInUTC)
                    ->where('expire_date', '>=', $todayDateInUTC)
                    ->first();

                if (!empty($zcsurveylog) && $user->start_date <= $todayDateInUTC) {
                    $zcSurveyUserLog = ZcSurveyUserLog::select('id', 'survey_submitted_at')
                        ->where('user_id', $user->id)
                        ->where('survey_log_id', $zcsurveylog->id)
                        ->first();

                    // if user log is present then avail survey or if survey_to_all is set to false and user isn't present in survey user logs then prevent user to avail the survey as user isn't selected in survey config
                    if (!empty($zcSurveyUserLog) || (is_null($zcSurveyUserLog) && $zcsurveylog->survey_to_all)) {
                        if (!empty($zcSurveyUserLog) && !is_null($zcSurveyUserLog->survey_submitted_at)) {
                            $checkForPrevoiusSurveyScoreIfAny = true;
                        } else {
                            $data['surveyinfo'] = [
                                'surveyId'         => $zcsurveylog->id,
                                'alreadySubmitted' => false,
                                'score'            => 0.0,
                            ];
                        }
                    } else {
                        $checkForPrevoiusSurveyScoreIfAny = true;
                    }
                } else {
                    $checkForPrevoiusSurveyScoreIfAny = true;
                }

                if ($checkForPrevoiusSurveyScoreIfAny) {
                    // get previously submitted survey and calculate score accordingly
                    $zcSurveyUserLog = ZcSurveyUserLog::select('id', 'survey_log_id')
                        ->where('user_id', $user->id)
                        ->whereNotNull('survey_submitted_at')
                        ->orderByDesc('id')
                        ->first();

                    if (!empty($zcSurveyUserLog)) {
                        $allOverSurveyResponse = ZcSurveyResponse::select(\DB::raw("FORMAT(IFNULL(((IFNULL(SUM(zc_survey_responses.score), 0) * 100) / IFNULL(SUM(zc_survey_responses.max_score), 0)), 0), 1) AS percentage"))
                            ->where('zc_survey_responses.user_id', $user->id)
                            ->where('zc_survey_responses.survey_log_id', $zcSurveyUserLog->survey_log_id)
                            ->groupBy('zc_survey_responses.user_id')
                            ->first();

                        $data['surveyinfo'] = [
                            'surveyId'         => 0,
                            'alreadySubmitted' => true,
                            'score'            => ((!empty($allOverSurveyResponse) && !empty($allOverSurveyResponse->percentage)) ? (float) $allOverSurveyResponse->percentage : 0.0),
                        ];
                    }
                }
            } else {
                // if not submitted any survey then find present if exist
                $zcsurveylog = ZcSurveyLog::select('id', 'survey_to_all')
                    ->where('company_id', $company->id)
                    ->where('roll_out_date', '<=', $todayDateInUTC)
                    ->where('expire_date', '>=', $todayDateInUTC)
                    ->first();

                if (!empty($zcsurveylog) && $user->start_date <= $todayDateInUTC) {
                    $zcSurveyUserLog = ZcSurveyUserLog::select('id', 'survey_submitted_at')
                        ->where('user_id', $user->id)
                        ->where('survey_log_id', $zcsurveylog->id)
                        ->first();

                    // if user log is present then avail survey or if survey_to_all is set to false and user isn't present in survey user logs then prevent user to avail the survey as user isn't selected in survey config
                    if (!empty($zcSurveyUserLog) || (is_null($zcSurveyUserLog) && $zcsurveylog->survey_to_all)) {
                        $data['surveyinfo'] = [
                            'surveyId'         => $zcsurveylog->id,
                            'alreadySubmitted' => false,
                            'score'            => 0.0,
                        ];
                    }
                }
            }

            // User statistics data for current day
            $userCalorieHistory = $user->steps()->select(\DB::raw("SUM(user_step.calories) as calories"), \DB::raw("SUM(user_step.steps) as steps"), \DB::raw("SUM(user_step.distance) as distances"))
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

            $latestRecipe = Recipe::select('recipe.id', 'recipe.title')
                ->join('recipe_company', function ($join) use ($company) {
                    $join->on('recipe_company.recipe_id', '=', 'recipe.id')
                        ->where('recipe_company.company_id', $company->id);
                })
                ->where('recipe.status', 1)
                ->orderBy('recipe.id', 'DESC')
                ->first();

            $data['userstatistics'] = [
                'dailySteps'    => (!empty($userCalorieHistory) && !empty($userCalorieHistory['steps'])) ? (int) $userCalorieHistory['steps'] : 0,
                'dailyDistance' => (!empty($userCalorieHistory) && !empty($userCalorieHistory['distances'])) ? (int) $userCalorieHistory['distances'] : 0,
                'dailyCalories' => (!empty($userCalorieHistory) && !empty($userCalorieHistory['calories'])) ? (double) $userCalorieHistory['calories'] : 0.0,
                'goalSteps'     => (!empty($userDailySteps) || $userDailySteps != null) ? $userDailySteps->steps : 0,
            ];

            if (!empty($companyLeader) && $companyLeader->steps > 0) {
                $companyLeaderArray = [
                    'id'    => $companyLeader->id,
                    'steps' => (int) $companyLeader->steps,
                    'image' => $companyLeader->getMediaData('logo', ['w' => 320, 'h' => 320, 'zc' => 0]),
                ];
                $data['userstatistics']['companyLeader'] = $companyLeaderArray;
            }

            // get user's running lessions with course data
            $runningCourseRecords = $user->courseLogs()
                ->where("courses.status", true)
                ->wherePivot("completed", false)
                ->orderByDesc('user_course.joined_on');

            // ->wherePivot('joined', 1)
            // ->wherePivot('started_course', 1)
            // ->wherePivot('completed_on', '=', null)
            // ->orderByDesc('user_course.joined_on');

            // use count based on receieved data from course API total data count must be 5 max.
            $runningCourseRecordsData = $runningCourseRecords->limit(5)->get();

            // collect required course data
            $data['masterclasses'] = [
                'totalCount' => $runningCourseRecords->count(),
                'data'       => new HomeCourseCollection($runningCourseRecordsData),
            ];

            // Feed List get max 5 feed for home statistics
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
                ->limit(5)
                ->get();
            // merge upcomming events with story
            $storycollection = new Collection();

            if ($company->enable_event) {
                // get upcomming non-registered 3 events
                $from   = now(config('app.timezone'))->setTime(0, 0, 0);
                $to     = $from->copy()->addDays(6)->setTime(23, 59, 59, 999999);
                $events = $company
                    ->evnetBookings()
                    ->select(
                        'events.id',
                        'event_booking_logs.id AS booking_log_id',
                        'event_booking_logs.event_id',
                        'events.name',
                        'events.description',
                        'events.capacity',
                        \DB::raw("TIMESTAMP(CONCAT(event_booking_logs.booking_date, ' ', event_booking_logs.start_time)) AS eventStartTime"),
                        \DB::raw("event_registered_users_logs.is_cancelled AS isRegistered"),
                        \DB::raw("0 AS type"),
                        \DB::raw("(SELECT COUNT(id) FROM event_registered_users_logs WHERE event_registered_users_logs.event_booking_log_id = event_booking_logs.id AND is_cancelled = 0) AS users_count")
                    )
                    ->leftJoin('event_registered_users_logs', function ($join) use ($user) {
                        $join
                            ->on('event_registered_users_logs.event_booking_log_id', '=', 'event_booking_logs.id')
                            ->where('event_registered_users_logs.user_id', $user->id);
                    })
                    ->whereRaw("TIMESTAMP(CONCAT(event_booking_logs.booking_date, ' ', event_booking_logs.start_time)) BETWEEN '{$from}' AND '{$to}'")
                    ->where('event_booking_logs.add_to_story', true)
                    ->where('event_booking_logs.status', '4')
                    ->where('events.status', '2')
                    ->havingRaw("isRegistered IS NULL")
                    ->havingRaw("(events.capacity IS NULL OR events.capacity > users_count)")
                    ->groupBy('event_booking_logs.id')
                    ->orderBy('eventStartTime')
                    ->limit(3)
                    ->get();
                $storycollection = $storycollection->merge($events);
            }

            $storycollection = $storycollection->merge($feedRecords);
            $data['feeds']   = new FeedListCollection($storycollection, true);

            $recomendedSection = [];

            // Get Mood Survey Fill or not
            $moodUserSubmitted = MoodUser::select('id')->where('user_id', $user->id)->where(\DB::raw("DATE(CONVERT_TZ(date, '{$appTimezone}', '{$timezone}'))"), '=', now($timezone)->toDateString())->first();

            $data['moodSubmitted'] = !empty($moodUserSubmitted);

            if (!empty($userSelectedGoal)) {
                $userGoalFeed = $userGoalFeed
                    ->join('feed_tag', function ($join) {
                        $join->on('feed_tag.feed_id', '=', 'feeds.id');
                    })
                    ->whereIn("feed_tag.goal_id", $userSelectedGoal)
                    ->groupBy('feeds.id')
                    ->get();
                if ($userGoalFeed->isNotEmpty() && $userGoalFeed->count() > 1) {
                    $userGoalFeed = $userGoalFeed->random(1);
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
                if ($userGoalRecipe->isNotEmpty() && $userGoalRecipe->count() > 1) {
                    $userGoalRecipe = $userGoalRecipe->random(1);
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
                if ($userGoalMeditation->isNotEmpty() && $userGoalMeditation->count() > 1) {
                    $userGoalMeditation = $userGoalMeditation->random(1);
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
                if ($userGoalCourse->isNotEmpty() && $userGoalCourse->count() > 1) {
                    $userGoalCourse = $userGoalCourse->random(1);
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
                if ($userGoalWebinar->isNotEmpty() && $userGoalWebinar->count() > 1) {
                    $userGoalWebinar = $userGoalWebinar->random(1);
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

            $data['recommendation'] = $recomendedSection;

            // for custom labels
            $companyLabelString       = $company->companyWiseLabelString()->pluck('label_name', 'field_name')->toArray();
            $defaultLabelString       = config('zevolifesettings.company_label_string', []);
            $finalCompanyLabelStrings = [];

            // iterate default labels loop and check is label's custom value is set then user custom value else default value
            foreach ($defaultLabelString as $groupKey => $groups) {
                foreach ($groups as $labelKey => $labelValue) {
                    $label = ($companyLabelString[$labelKey] ?? $labelValue['default_value']);
                    if (in_array($labelKey, ['location_logo', 'department_logo'])) {
                        $label = $company->getMediaData($labelKey, ['w' => 60, 'h' => 60, 'zc' => 3, 'ct' => 1]);
                    }
                    $finalCompanyLabelStrings[$labelKey] = $label;
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
