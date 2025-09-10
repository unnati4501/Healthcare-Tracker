<?php declare (strict_types = 1);

namespace App\Http\Controllers\API\V18;

use App\Http\Collections\V6\HomeCourseCollection;
use App\Http\Collections\V8\RecommendationCollection;
use App\Http\Collections\V17\FeedListCollection;
use App\Http\Controllers\API\V17\CommonController as v17CommonController;
use App\Http\Requests\Api\V18\CategoriesWellbeingStatisticsRequest;
use App\Http\Requests\Api\V18\WellbeingStatisticsRequest;
use App\Models\Course;
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

class CommonController extends v17CommonController
{
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

            $data['feeds'] = new FeedListCollection($feedRecords, true);

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
     * Wellbeing score vs month graph
     * @param WellbeingStatisticsRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getWellbeingStatistics(WellbeingStatisticsRequest $request)
    {
        try {
            $data            = [];
            $user            = $this->user();
            $company         = $user->company()->select('companies.id')->first();
            $appTimezone     = config('app.timezone');
            $timezone        = (!empty($user->timezone) ? $user->timezone : $appTimezone);
            $monthArr        = getMonths($request->year);
            $userCreatedAt   = Carbon::parse($user->created_at, $appTimezone)->setTimezone($timezone);
            $userCreatedYear = $userCreatedAt->format("m");
            $userCreatedAt   = $userCreatedAt->format("Y-m");
            $minDate         = ZcSurveyResponse::select('id', 'updated_at')
                ->where('user_id', $user->id)
                ->whereNotNull('max_score')
                ->orderBy('id')->limit(1)->first();
            $maxDate = ZcSurveyResponse::select('id', 'updated_at')
                ->where('user_id', $user->id)
                ->whereNotNull('max_score')
                ->orderByDesc('id')->limit(1)->first();

            if ($userCreatedYear <= $request->year && !empty($minDate)) {
                $minDateYm = Carbon::parse($minDate->updated_at)->setTimezone($timezone)->format("Y-m");
                $maxDateYm = Carbon::parse($maxDate->updated_at)->setTimezone($timezone)->format("Y-m");
                foreach ($monthArr as $monthKey => $month) {
                    $fetchMonth = date("Y-m", strtotime($request->year . "-" . $month));
                    if (($userCreatedAt <= $fetchMonth) && ($minDateYm <= $fetchMonth) && ($maxDateYm >= $fetchMonth)) {
                        $usersurveyData = ZcSurveyResponse::where('user_id', $user->id)
                            ->select(
                                \DB::raw("IFNULL(FORMAT(((SUM(zc_survey_responses.score) * 100) / IFNULL(SUM(zc_survey_responses.max_score), 0)), 1), 0) AS percentage")
                            )
                            ->where(\DB::raw("MONTH(CONVERT_TZ(zc_survey_responses.updated_at, '{$appTimezone}', '{$timezone}'))"), '=', $monthKey)
                            ->where(\DB::raw("YEAR(CONVERT_TZ(zc_survey_responses.updated_at, '{$appTimezone}', '{$timezone}'))"), '=', $request->year)
                            ->first();

                        $data[] = [
                            'key'   => ucfirst($month),
                            'value' => (!empty($usersurveyData) ? (float) $usersurveyData->percentage : 0.0),
                        ];
                    }
                }
            }

            return $this->successResponse([
                'data'        => $data,
                'minimumDate' => ((!empty($minDate)) ? $minDate->updated_at->setTimezone($timezone)->toDateString() : null),
            ], 'Wellbeing statistics retrived successfully.');
        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong_try_again'));
        }
    }

    /**
     * Categories score vs month graph
     * @param CategoriesWellbeingStatisticsRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCategoriesWellbeingStatistics(CategoriesWellbeingStatisticsRequest $request)
    {
        try {
            $data            = [];
            $user            = $this->user();
            $company         = $user->company()->select('companies.id')->first();
            $appTimezone     = config('app.timezone');
            $timezone        = (!empty($user->timezone) ? $user->timezone : $appTimezone);
            $userCreatedAt   = Carbon::parse($user->created_at, $appTimezone)->setTimezone($timezone);
            $userCreatedYear = $userCreatedAt->format("m");
            $userCreatedAt   = $userCreatedAt->format("Y-m");
            $minDate         = ZcSurveyResponse::select('id', 'updated_at')
                ->where('user_id', $user->id)
                ->whereNotNull('max_score')
                ->orderBy('id')->limit(1)->first();
            $maxDate = ZcSurveyResponse::select('id', 'updated_at')
                ->where('user_id', $user->id)
                ->whereNotNull('max_score')
                ->orderByDesc('id')->limit(1)->first();

            if ($userCreatedYear <= $request->year && !empty($minDate)) {
                $minDateYm  = Carbon::parse($minDate->updated_at)->setTimezone($timezone)->format("Y-m");
                $maxDateYm  = Carbon::parse($maxDate->updated_at)->setTimezone($timezone)->format("Y-m");
                $fetchMonth = date("Y-m", strtotime("{$request->year}-{$request->month}"));

                if (($userCreatedAt <= $fetchMonth) && ($minDateYm <= $fetchMonth) && ($maxDateYm >= $fetchMonth)) {
                    $usersurveyData = ZcSurveyResponse::where('user_id', $user->id)
                        ->select(
                            'zc_survey_responses.category_id',
                            'zc_categories.display_name AS category_name',
                            \DB::raw("IFNULL(FORMAT(((SUM(zc_survey_responses.score) * 100) / IFNULL(SUM(zc_survey_responses.max_score), 0)), 1), 0) AS percentage")
                        )
                        ->join('zc_categories', 'zc_categories.id', '=', 'zc_survey_responses.category_id')
                        ->where(\DB::raw("MONTH(CONVERT_TZ(zc_survey_responses.updated_at, '{$appTimezone}', '{$timezone}'))"), '=', $request->month)
                        ->where(\DB::raw("YEAR(CONVERT_TZ(zc_survey_responses.updated_at, '{$appTimezone}', '{$timezone}'))"), '=', $request->year)
                        ->groupBy('zc_survey_responses.category_id')
                        ->get()
                        ->each(function ($category) use (&$data) {
                            $data[] = [
                                'key'   => $category->category_name,
                                'value' => (!empty($category->percentage) ? (float) $category->percentage : 0.0),
                            ];
                        });
                }
            }

            return $this->successResponse([
                'data'        => $data,
                'minimumDate' => ((!empty($minDate)) ? $minDate->updated_at->setTimezone($timezone)->toDateString() : null),
            ], 'Wellbeing category wise statistics retrived successfully.');
        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong_try_again'));
        }
    }
}
