<?php declare (strict_types = 1);

namespace App\Models;

use App\Models\Company;
use App\Models\Event;
use App\Models\HsSubCategories;
use App\Models\Recipe;
use App\Models\Team;
use App\Models\User;
use App\Models\ZcSurveyResponse;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class NewDashboard extends Model
{
    /**
     * Get App Usage Tab Tier 1 Data - Users, Teams, Challenges Blocks
     *
     * @param Request $request
     * @return Array
     */
    public function getAppUsageTabTier1Data($payload)
    {
        $timezone   = Auth::user()->timezone ?? null;
        $timezone   = !empty($timezone) ? $timezone : config('app.timezone');
        $last30Days = Carbon::parse(now()->toDateTimeString())->setTimeZone($timezone)->subDays(30)->format('Y-m-d 00:00:00');
        $role       = getUserRole();
        $data       = [];

        $procedureData = [
            $timezone,
            $last30Days,
            $payload['companyId'],
            $payload['departmentId'],
            $payload['age1'],
            $payload['age2'],
        ];

        $spUsersData = DB::select('CALL sp_dashboard_app_usage_users(?, ?, ?, ?, ?, ?)', $procedureData);

        $data['usersData'] = !empty($spUsersData) ? Arr::collapse(json_decode(json_encode($spUsersData), true)) : [];

        $procedureData = [
            $timezone,
            $last30Days,
            $payload['companyId'],
            $payload['departmentId'],
        ];

        $spTeamsData       = DB::select('CALL sp_dashboard_app_usage_teams(?, ?, ?, ?)', $procedureData);
        $data['teamsData'] = !empty($spTeamsData) ? Arr::collapse(json_decode(json_encode($spTeamsData), true)) : [];

        $procedureData = [
            $timezone,
            $payload['companyId'],
            $role->group,
        ];

        $spChallengesData       = DB::select('CALL sp_dashboard_app_usage_challenges(?, ?, ?)', $procedureData);
        $data['challengesData'] = !empty($spChallengesData) ? Arr::collapse(json_decode(json_encode($spChallengesData), true)) : [];

        return $data;
    }

    /**
     * Get App Usage Tab Tier 2 Data - Steps Period, Calories Period
     *
     * @param Request $request
     * @return Array
     */
    public function getAppUsageTabTier2Data($payload)
    {
        $timezone = Auth::user()->timezone ?? null;
        $timezone = !empty($timezone) ? $timezone : config('app.timezone');
        $fromDate = !empty($payload['options']) && isset($payload['options']['fromDateStepsPeriod']) ? $payload['options']['fromDateStepsPeriod'] : Carbon::parse(now()->toDateTimeString())->setTimeZone($timezone)->subDays(30)->format('Y-m-d 00:00:00');
        $toDate   = !empty($payload['options']) && isset($payload['options']['endDateStepsPeriod']) ? $payload['options']['endDateStepsPeriod'] : Carbon::parse(now()->toDateTimeString())->setTimeZone($timezone)->subDay()->format('Y-m-d 23:59:59');
        $days     = Carbon::parse($toDate)->diffInDays(Carbon::parse($fromDate)) + 1;
        $data     = [];

        $procedureData = [
            $timezone,
            $fromDate,
            $toDate,
            $days,
            $payload['companyId'],
            $payload['departmentId'],
            $payload['age1'],
            $payload['age2'],
        ];

        $spStepsData = DB::select('CALL sp_dashboard_app_usage_steps_period(?, ?, ?, ?, ?, ?, ?, ?)', $procedureData);

        $fromDate = !empty($payload['options']) && isset($payload['options']['fromDateCaloriesPeriod']) ? $payload['options']['fromDateCaloriesPeriod'] : Carbon::parse(now()->toDateTimeString())->setTimeZone($timezone)->subDays(30)->format('Y-m-d 00:00:00');
        $toDate   = !empty($payload['options']) && isset($payload['options']['endDateCaloriesPeriod']) ? $payload['options']['endDateCaloriesPeriod'] : Carbon::parse(now()->toDateTimeString())->setTimeZone($timezone)->subDay()->format('Y-m-d 23:59:59');
        $days     = Carbon::parse($toDate)->diffInDays(Carbon::parse($fromDate)) + 1;

        $procedureData[1] = $fromDate;
        $procedureData[2] = $toDate;
        $procedureData[3] = $days;

        $spCaloriesData = DB::select('CALL sp_dashboard_app_usage_calories_period(?, ?, ?, ?, ?, ?, ?, ?)', $procedureData);

        if (!empty($spStepsData)) {
            $data['stepsData'] = [
                'averageSteps' => array_column($spStepsData, 'averageSteps'),
                'userPercent'  => array_column($spStepsData, 'userPercent'),
            ];
        }

        if (!empty($spCaloriesData)) {
            $data['caloriesData'] = [
                'averageCalories' => array_column($spCaloriesData, 'averageCalories'),
                'userPercent'     => array_column($spCaloriesData, 'userPercent'),
            ];
        }

        if (isset($payload['options']) && isset($payload['options']['change'])) {
            $data['change'] = $payload['options']['change'];
        }

        $totaAppUsers = $avgMeditationTime = 0;
        $data         = [
            'data'              => [],
            'labels'            => [],
            'totalUsers'        => $totaAppUsers,
            'avgMeditationTime' => $avgMeditationTime,
        ];

        $timezone          = Auth::user()->timezone ?? null;
        $timezone          = !empty($timezone) ? $timezone : config('app.timezone');
        $durationThreshold = !empty($payload['options']) && isset($payload['options']['fromDateMeditationHours']) ? $payload['options']['fromDateMeditationHours'] : 7;
        $type              = (($durationThreshold <= 7) ? "day" : (($durationThreshold <= 30) ? "month" : "year"));
        $fromDate          = Carbon::parse(now()->toDateTimeString())->setTimeZone($this->timezone)->subDays($durationThreshold - 1)->format('Y-m-d 00:00:00');
        $emptyData         = $this->getDurationEmptyData($type, ['today' => $fromDate, 'timezone' => $timezone]);
        $groupByCol        = (($type == 'day') ? 'log_date_only' : (($type == 'month') ? 'log_date_week' : 'log_month'));

        $procedureData = [
            'app',
            null,
            null,
            $payload['companyId'],
            $payload['departmentId'],
            $payload['age1'],
            $payload['age2'],
            null,
        ];

        $totaAppUsers = spGetUser($procedureData);

        $procedureData = [
            $timezone,
            $type,
            $fromDate,
            $payload['companyId'],
            $payload['departmentId'],
            $payload['age1'],
            $payload['age2'],
        ];
        $chartData = DB::select('CALL sp_inspire_meditation_hours(?, ?, ?, ?, ?, ?, ?)', $procedureData);
        if (!empty($chartData)) {
            $totalListenedHours = array_sum(array_column($chartData, 'listened_hours'));
            $avgMeditationTime  = (($totalListenedHours > 0 && $totaAppUsers > 0) ? ($totalListenedHours / ($totaAppUsers * $durationThreshold)) : 0);
            $chartData          = Collect($chartData)->pluck('listened_hours', $groupByCol);
        }

        foreach ($emptyData as $key => $value) {
            array_push($data['labels'], $value);
            array_push($data['data'], (isset($chartData[$key]) ? $chartData[$key] : 0));
        }

        $data['totalUsers']        = numberFormatShort($totaAppUsers);
        $data['avgMeditationTime'] = number_format($avgMeditationTime, 2) . ' Hrs' . (($avgMeditationTime > 1) ? 's' : '');

        return $data;
    }

    /**
     * Get App Usage Tab Tier 3 Data - Popular feeds, Sync details
     *
     * @param Request $request
     * @return Array
     */
    public function getAppUsageTabTier3Data($payload)
    {
        $timezone = Auth::user()->timezone ?? null;
        $timezone = !empty($timezone) ? $timezone : config('app.timezone');
        $fromDate = !empty($payload['options']) && isset($payload['options']['fromDatePopularFeeds']) ? Carbon::parse(now()->toDateTimeString())->setTimeZone($timezone)->subDays($payload['options']['fromDatePopularFeeds'])->format('Y-m-d 00:00:00') : Carbon::parse(now()->toDateTimeString())->setTimeZone($timezone)->subDays(7)->format('Y-m-d 00:00:00');
        $data     = [];

        $procedureData = [
            $timezone,
            $fromDate,
            $payload['companyId'],
            $payload['departmentId'],
            $payload['age1'],
            $payload['age2'],
        ];

        $spPopularFeedCategoriesData = DB::select('CALL sp_dashboard_app_usage_popular_feeds(?, ?, ?, ?, ?, ?)', $procedureData);

        if (!empty($spPopularFeedCategoriesData)) {
            $data['popularFeedCategoriesData'] = [
                'feedCategory' => array_column($spPopularFeedCategoriesData, 'feedCategory'),
                'totalViews'   => array_column($spPopularFeedCategoriesData, 'totalViews'),
            ];
        }

        $procedureData = [
            $timezone,
            $payload['companyId'],
            $payload['departmentId'],
            $payload['age1'],
            $payload['age2'],
        ];

        $spSyncDetailsData = DB::select('CALL sp_dashboard_app_usage_sync_details(?, ?, ?, ?, ?)', $procedureData);

        if (!empty($spSyncDetailsData)) {
            $data['syncDetails']                   = Arr::collapse(json_decode(json_encode($spSyncDetailsData), true));
            $data['syncDetails']['notSyncPercent'] = (float) number_format(100 - ($data['syncDetails']['syncPercent'] ?? 0), 1);
        }

        $procedureData = [
            $timezone,
            $payload['companyId'],
            $payload['departmentId'],
            $payload['age1'],
            $payload['age2'],
        ];

        $spPopularRecipesData = DB::select('CALL sp_dashboard_physical_popular_recipes(?, ?, ?, ?, ?)', $procedureData);

        if (!empty($spPopularRecipesData)) {
            $data['popularRecipesData'] = collect($spPopularRecipesData)->map(function ($item) {
                $recipe = Recipe::find($item->recipe_id);
                return [
                    'name'       => $recipe->title,
                    'logo'       => $recipe->logo,
                    'chef'       => $recipe->chef()->first()->full_name,
                    'totalViews' => $item->totalViews,
                ];
            })->toArray();
        }

        $procedureData = [
            $payload['companyId'],
            $payload['departmentId'],
            $payload['age1'],
            $payload['age2'],
        ];

        $spPopularMeditationCategoriesData = DB::select('CALL sp_dashboard_psychological_popular_meditations(?, ?, ?, ?)', $procedureData);

        if (!empty($spPopularMeditationCategoriesData)) {
            $data['popularMeditationCategoriesData'] = [
                'meditationCategory' => array_column($spPopularMeditationCategoriesData, 'meditationCategory'),
                'totalViews'         => array_column($spPopularMeditationCategoriesData, 'totalViews'),
            ];
        }

        return $data;
    }

    /**
     * Get App Usage Tab Tier 4 Data - Superstars blocks
     *
     * @param Request $request
     * @return Array
     */
    public function getAppUsageTabTier4Data($payload)
    {
        $timezone = Auth::user()->timezone ?? null;
        $timezone = !empty($timezone) ? $timezone : config('app.timezone');
        $fromDate = !empty($payload['options']) && isset($payload['options']['fromDateTopMeditationTracks']) ? $payload['options']['fromDateTopMeditationTracks'] : Carbon::parse(now()->toDateTimeString())->setTimeZone($timezone)->subDays(7)->format('Y-m-d 00:00:00');
        $toDate   = !empty($payload['options']) && isset($payload['options']['endDateTopMeditationTracks']) ? $payload['options']['endDateTopMeditationTracks'] : Carbon::parse(now()->toDateTimeString())->setTimeZone($timezone)->format('Y-m-d 00:00:00');
        $data     = [];

        $role         = getUserRole();
        $data['role'] = $role->group;

        $procedureData = [
            $timezone,
            $fromDate,
            $toDate,
            $payload['companyId'],
            $payload['departmentId'],
            $payload['age1'],
            $payload['age2'],
        ];

        $spActiveTeamData       = DB::select('CALL sp_dashboard_app_usage_active_team(?, ?, ?, ?, ?, ?, ?)', $procedureData);
        $spActiveIndividualData = DB::select('CALL sp_dashboard_app_usage_active_individual(?, ?, ?, ?, ?, ?, ?)', $procedureData);
        $spBadgesEarnedData     = DB::select('CALL sp_dashboard_app_usage_badges_earned(?, ?, ?, ?, ?, ?, ?)', $procedureData);

        if (!empty($spActiveTeamData)) {
            $data['activeTeamData'] = collect($spActiveTeamData)->map(function ($item) {
                $team = Team::find($item->team_id);
                return [
                    'name'         => $team->name,
                    'company'      => $team->company()->first()->name,
                    'logo'         => $team->logo,
                    'averageHours' => $item->averageHours,
                ];
            })->toArray();
        }

        if (!empty($spActiveIndividualData)) {
            $data['activeIndividualData'] = collect($spActiveIndividualData)->map(function ($item) {
                $user = User::find($item->user_id);
                return [
                    'name'       => $user->first_name . ' ' . $user->last_name,
                    'company'    => $user->company()->first()->name,
                    'logo'       => $user->logo,
                    'totalHours' => $item->totalHours,
                ];
            })->toArray();
        }

        if (!empty($spBadgesEarnedData)) {
            $data['badgesEarnedData'] = collect($spBadgesEarnedData)->map(function ($item) {
                $user = User::find($item->user_id);
                return [
                    'name'       => $user->first_name . ' ' . $user->last_name,
                    'company'    => $user->company()->first()->name,
                    'logo'       => $user->logo,
                    'mostBadges' => $item->mostBadges,
                ];
            })->toArray();
        }

        $procedureData = [
            $timezone,
            $fromDate,
            $toDate,
            $payload['companyId'],
            $payload['departmentId'],
            $payload['age1'],
            $payload['age2'],
        ];

        $spTopMeditationTracksData = DB::select('CALL sp_dashboard_psychological_top_meditations(?, ?, ?, ?, ?, ?, ?)', $procedureData);

        if (!empty($spTopMeditationTracksData)) {
            $data['topMeditationTracksData'] = [
                'meditationTitle' => array_column($spTopMeditationTracksData, 'title'),
                'totalViews'      => array_column($spTopMeditationTracksData, 'totalViews'),
            ];
        }

        $fromDate = !empty($payload['options']) && isset($payload['options']['fromDatePopularWebinar']) ? Carbon::parse(now()->toDateTimeString())->setTimeZone($timezone)->subDays($payload['options']['fromDatePopularWebinar'])->format('Y-m-d 00:00:00') : Carbon::parse(now()->toDateTimeString())->setTimeZone($timezone)->subDays(7)->format('Y-m-d 00:00:00');

        $procedureData = [
            $timezone,
            $fromDate,
            $payload['companyId'],
            $payload['departmentId'],
            $payload['age1'],
            $payload['age2'],
        ];

        $spPopularWebinarCategoriesData = DB::select('CALL sp_dashboard_app_usage_popular_webinar(?, ?, ?, ?, ?, ?)', $procedureData);

        if (!empty($spPopularWebinarCategoriesData)) {
            $data['popularWebinarCategoriesData'] = [
                'webinarCategory' => array_column($spPopularWebinarCategoriesData, 'webinarCategory'),
                'totalViews'      => array_column($spPopularWebinarCategoriesData, 'totalViews'),
            ];
        }

        $fromDate = !empty($payload['options']) && isset($payload['options']['fromDatePopularMasterclass']) ? Carbon::parse(now()->toDateTimeString())->setTimeZone($timezone)->subDays($payload['options']['fromDatePopularMasterclass'])->format('Y-m-d 00:00:00') : Carbon::parse(now()->toDateTimeString())->setTimeZone($timezone)->subDays(7)->format('Y-m-d 00:00:00');

        $procedureData = [
            $timezone,
            $fromDate,
            $payload['companyId'],
            $payload['departmentId'],
            $payload['age1'],
            $payload['age2'],
        ];

        $spPopularMasterclassCategoriesData = DB::select('CALL sp_dashboard_app_usage_popular_masterclass(?, ?, ?, ?, ?, ?)', $procedureData);

        if (!empty($spPopularMasterclassCategoriesData)) {
            $data['popularMasterclassCategoriesData'] = [
                'masterclassCategory' => array_column($spPopularMasterclassCategoriesData, 'masterclassCategory'),
                'totalEnrollments'    => array_column($spPopularMasterclassCategoriesData, 'totalEnrollment'),
            ];
        }

        $fromDate = !empty($payload['options']) && isset($payload['options']['fromDateTopWebinar']) ? $payload['options']['fromDateTopWebinar'] : Carbon::parse(now()->toDateTimeString())->setTimeZone($timezone)->subDays(7)->format('Y-m-d 00:00:00');
        $toDate   = !empty($payload['options']) && isset($payload['options']['endDateTopWebinars']) ? $payload['options']['endDateTopWebinars'] : Carbon::parse(now()->toDateTimeString())->setTimeZone($timezone)->format('Y-m-d 00:00:00');

        $procedureData = [
            $timezone,
            $fromDate,
            $toDate,
            $payload['companyId'],
            $payload['departmentId'],
            $payload['age1'],
            $payload['age2'],
        ];

        $spTopWebinarData = DB::select('CALL sp_dashboard_usage_top_webinar(?, ?, ?, ?, ?, ?, ?)', $procedureData);

        if (!empty($spTopWebinarData)) {
            $data['topWebinarsData'] = [
                'webinarTitle' => array_column($spTopWebinarData, 'title'),
                'totalViews'   => array_column($spTopWebinarData, 'totalViews'),
            ];
        }

        $fromDate = !empty($payload['options']) && isset($payload['options']['fromDateTopMasterclass']) ? Carbon::parse(now()->toDateTimeString())->setTimeZone($timezone)->subDays($payload['options']['fromDateTopMasterclass'])->format('Y-m-d 00:00:00') : null;

        $procedureData = [
            $timezone,
            $fromDate,
            $payload['companyId'],
            $payload['departmentId'],
            $payload['age1'],
            $payload['age2'],
        ];

        $spTopMasterclassData = DB::select('CALL sp_dashboard_app_usage_top_masterclass(?, ?, ?, ?, ?, ?)', $procedureData);

        if (!empty($spTopMasterclassData)) {
            $data['topMasterclassData'] = [
                'masterclassTitle' => array_column($spTopMasterclassData, 'title'),
                'totalEnrollment'  => array_column($spTopMasterclassData, 'totalEnrollment'),
            ];
        }

        return $data;
    }

    /**
     * Get Physical Tab Tier 1 Data - Physical category and sub-categories data of health score
     *
     * @param Request $request
     * @return Array
     */
    public function getPhysicalTabTier1Data($payload)
    {
        $payload['category'] = 1;

        return $this->getHealthScoreCategoryCommonData($payload);
    }

    /**
     * Get Physical Tab Tier 2 Data - Steps Range, Exercise Range
     *
     * @param Request $request
     * @return Array
     */
    public function getPhysicalTabTier2Data($payload)
    {
        $timezone = Auth::user()->timezone ?? null;
        $timezone = !empty($timezone) ? $timezone : config('app.timezone');
        $fromDate = !empty($payload['options']) && isset($payload['options']['fromDateExerciseRanges']) ? $payload['options']['fromDateExerciseRanges'] : Carbon::parse(now()->toDateTimeString())->setTimeZone($timezone)->subDays(7)->format('Y-m-d 00:00:00');
        $toDate   = !empty($payload['options']) && isset($payload['options']['endDateExerciseRanges']) ? $payload['options']['endDateExerciseRanges'] : Carbon::parse(now()->toDateTimeString())->setTimeZone($timezone)->format('Y-m-d H:i:s');
        $days     = Carbon::parse($toDate)->diffInDays(Carbon::parse($fromDate)) + 1;
        $data     = [];

        $exerciseLabels = [
            'Low',
            'Moderate',
            'High',
            'Very High',
        ];

        $stepLabels = [
            'Low',
            'Moderate',
            'High',
        ];

        $procedureData = [
            $timezone,
            $fromDate,
            $toDate,
            $days,
            $payload['companyId'],
            $payload['departmentId'],
            $payload['age1'],
            $payload['age2'],
        ];

        $spExercisesData = DB::select('CALL sp_dashboard_physical_exercises_range(?, ?, ?, ?, ?, ?, ?, ?)', $procedureData);

        if (!empty($spExercisesData)) {
            $exercisesData         = collect($spExercisesData)->pluck('percent', 'exerciseRange')->toArray();
            $data['exercisesData'] = Arr::flatten(array_map(function ($value) use ($exercisesData) {
                return array_key_exists($value, $exercisesData) ? $exercisesData[$value] : 0;
            }, $exerciseLabels));
        }

        $fromDate = !empty($payload['options']) && isset($payload['options']['fromDateStepRanges']) ? $payload['options']['fromDateStepRanges'] : Carbon::parse(now()->toDateTimeString())->setTimeZone($timezone)->subDays(7)->format('Y-m-d 00:00:00');
        $toDate   = !empty($payload['options']) && isset($payload['options']['endDateStepRanges']) ? $payload['options']['endDateStepRanges'] : Carbon::parse(now()->toDateTimeString())->setTimeZone($timezone)->format('Y-m-d H:i:s');
        $days     = Carbon::parse($toDate)->diffInDays(Carbon::parse($fromDate)) + 1;

        $procedureData[1] = $fromDate;
        $procedureData[2] = $toDate;
        $procedureData[3] = $days;

        $spStepsData = DB::select('CALL sp_dashboard_physical_steps_range(?, ?, ? , ?, ?, ?, ?, ?)', $procedureData);

        if (!empty($spStepsData)) {
            $stepsData         = collect($spStepsData)->pluck('percent', 'stepRange')->toArray();
            $data['stepsData'] = Arr::flatten(array_map(function ($value) use ($stepsData) {
                return array_key_exists($value, $stepsData) ? $stepsData[$value] : 0;
            }, $stepLabels));
        }

        if (isset($payload['options']) && isset($payload['options']['change'])) {
            $data['change'] = $payload['options']['change'];
        }
        $payload['category'] = 2;
        $categoryScore       = $this->getHealthScoreCategoryCommonData($payload);
        return array_merge($data, $categoryScore);
    }

    /**
     * Get Physical Tab Tier 3 Data - Popular exercises
     *
     * @param Request $request
     * @return Array
     */
    public function getPhysicalTabTier3Data($payload)
    {

        $timezone = Auth::user()->timezone ?? null;
        $timezone = !empty($timezone) ? $timezone : config('app.timezone');
        $fromDate = !empty($payload['options']) && isset($payload['options']['fromDatePopularExercises']) ? Carbon::parse(now()->toDateTimeString())->setTimeZone($timezone)->subDays($payload['options']['fromDatePopularExercises'])->format('Y-m-d 00:00:00') : Carbon::parse(now()->toDateTimeString())->setTimeZone($timezone)->subDays(7)->format('Y-m-d 00:00:00');
        $toDate   = !empty($payload['options']) && isset($payload['options']['toDatePopularExercises']) ? $payload['options']['toDatePopularExercises'] : Carbon::parse(now()->toDateTimeString())->setTimeZone($timezone)->subDay()->format('Y-m-d 23:59:59');
        $days     = Carbon::parse($toDate)->diffInDays(Carbon::parse($fromDate)) + 1;

        $data          = [];
        $procedureData = [
            $timezone,
            $fromDate,
            $payload['companyId'],
            $payload['departmentId'],
            $payload['age1'],
            $payload['age2'],
        ];

        $spPopularExercisesData = DB::select('CALL sp_dashboard_physical_popular_exercises(?, ?, ?, ?, ?, ?)', $procedureData);

        if (!empty($spPopularExercisesData)) {
            $data = [
                'exercise' => array_column($spPopularExercisesData, 'title'),
                'percent'  => array_column($spPopularExercisesData, 'percent'),
            ];
        }

        if (isset($payload['options']) && isset($payload['options']['change'])) {
            if ($payload['options']['change'] == 'daterangeStepsPeriod') {
                $fromDate = !empty($payload['options']) && isset($payload['options']['fromDateStepsPeriod']) ? $payload['options']['fromDateStepsPeriod'] : Carbon::parse(now()->toDateTimeString())->setTimeZone($timezone)->subDays(7)->format('Y-m-d 00:00:00');
                $toDate   = !empty($payload['options']) && isset($payload['options']['endDateStepsPeriod']) ? $payload['options']['endDateStepsPeriod'] : Carbon::parse(now()->toDateTimeString())->setTimeZone($timezone)->format('Y-m-d H:i:s');
            }
        }

        $procedureData = [
            $timezone,
            $fromDate,
            $toDate,
            $days,
            $payload['companyId'],
            $payload['departmentId'],
            $payload['age1'],
            $payload['age2'],
        ];

        $spStepsData = DB::select('CALL sp_dashboard_app_usage_steps_period(?, ?, ?, ?, ?, ?, ?, ?)', $procedureData);

        $fromDate = !empty($payload['options']) && isset($payload['options']['fromDateCaloriesPeriod']) ? $payload['options']['fromDateCaloriesPeriod'] : Carbon::parse(now()->toDateTimeString())->setTimeZone($timezone)->subDays(30)->format('Y-m-d 00:00:00');
        $toDate   = !empty($payload['options']) && isset($payload['options']['endDateCaloriesPeriod']) ? $payload['options']['endDateCaloriesPeriod'] : Carbon::parse(now()->toDateTimeString())->setTimeZone($timezone)->subDay()->format('Y-m-d 23:59:59');
        $days     = Carbon::parse($toDate)->diffInDays(Carbon::parse($fromDate)) + 1;

        $procedureData[1] = $fromDate;
        $procedureData[2] = $toDate;
        $procedureData[3] = $days;

        $spCaloriesData = DB::select('CALL sp_dashboard_app_usage_calories_period(?, ?, ?, ?, ?, ?, ?, ?)', $procedureData);

        if (!empty($spStepsData)) {
            $data['stepsData'] = [
                'averageSteps' => array_column($spStepsData, 'averageSteps'),
                'userPercent'  => array_column($spStepsData, 'userPercent'),
            ];
        }

        if (!empty($spCaloriesData)) {
            $data['caloriesData'] = [
                'averageCalories' => array_column($spCaloriesData, 'averageCalories'),
                'userPercent'     => array_column($spCaloriesData, 'userPercent'),
            ];
        }

        $procedureData = [
            $timezone,
            $payload['companyId'],
            $payload['departmentId'],
            $payload['age1'],
            $payload['age2'],
        ];

        $spSyncDetailsData = DB::select('CALL sp_dashboard_app_usage_sync_details(?, ?, ?, ?, ?)', $procedureData);

        if (!empty($spSyncDetailsData)) {
            $data['syncDetails']                   = Arr::collapse(json_decode(json_encode($spSyncDetailsData), true));
            $data['syncDetails']['notSyncPercent'] = (float) number_format(100 - ($data['syncDetails']['syncPercent'] ?? 0), 1);
        }

        return $data;
    }

    /**
     * Get Physical Tab Tier 4 Data - Recipe views, BMI
     *
     * @param Request $request
     * @return Array
     */
    public function getPhysicalTabTier4Data($payload)
    {
        $role     = getUserRole();
        $user     = auth()->user();
        $company  = $user->company->first();
        $timezone = $user->timezone ?? null;
        $timezone = !empty($timezone) ? $timezone : config('app.timezone');
        $fromDate = !empty($payload['options']) && isset($payload['options']['fromDateMoodsAnalysis']) ? Carbon::parse(now()->toDateTimeString())->setTimeZone($timezone)->subDays($payload['options']['fromDateMoodsAnalysis'])->format('Y-m-d 00:00:00') : Carbon::parse(now()->toDateTimeString())->setTimeZone($timezone)->subDays(7)->format('Y-m-d 00:00:00');
        $toDate   = !empty($payload['options']) && isset($payload['options']['endDateCaloriesPeriod']) ? $payload['options']['endDateCaloriesPeriod'] : Carbon::parse(now()->toDateTimeString())->setTimeZone($timezone)->subDay()->format('Y-m-d 23:59:59');

        $gender                    = !empty($payload['options']) && isset($payload['options']['gender']) ? $payload['options']['gender'] : null;
        $data                      = [];
        $data['role']              = $role->group;
        $data['company_parent_id'] = isset($company) ? $company->parent_id : null;
        $procedureData             = [
            $timezone,
            $payload['companyId'],
            $payload['departmentId'],
            $payload['age1'],
            $payload['age2'],
        ];

        $spPopularRecipesData = DB::select('CALL sp_dashboard_physical_popular_recipes(?, ?, ?, ?, ?)', $procedureData);

        if (!empty($spPopularRecipesData)) {
            $data['popularRecipesData'] = collect($spPopularRecipesData)->map(function ($item) {
                $recipe = Recipe::find($item->recipe_id);
                return [
                    'name'       => $recipe->title,
                    'logo'       => $recipe->logo,
                    'chef'       => $recipe->chef()->first()->full_name,
                    'totalViews' => $item->totalViews,
                ];
            })->toArray();
        }

        $procedureData = [
            $payload['companyId'],
            $payload['departmentId'],
            $payload['age1'],
            $payload['age2'],
            $gender,
        ];

        $spBmiData = DB::select('CALL sp_dashboard_physical_bmi(?, ?, ?, ?, ?)', $procedureData);

        if (!empty($spBmiData)) {
            $label     = array_column($spBmiData, 'label');
            $userCount = array_column($spBmiData, 'count');
            $weight    = array_column($spBmiData, 'weight');

            $totalUsers  = array_sum($userCount);
            $totalWeight = array_sum($weight);

            $data['totalUsers'] = $totalUsers;
            $data['avgWeight']  = $totalUsers > 0 ? (float) number_format($totalWeight / $data['totalUsers'], 1) : 0;

            $bmiData = array_combine($label, $userCount);

            $mapLabels = [
                'UnderWeight',
                'Normal',
                'OverWeight',
                'Obese',
            ];

            $data['bmiData'] = Arr::flatten(array_map(function ($value) use ($bmiData, $totalUsers) {
                return array_key_exists($value, $bmiData) ? (float) number_format($bmiData[$value] * 100 / ($totalUsers), 1) : 0;
            }, $mapLabels));
        }

        if (isset($payload['options']) && isset($payload['options']['change'])) {
            $data['change'] = $payload['options']['change'];
        }

        $procedureData = [
            $timezone,
            $fromDate,
            $payload['companyId'],
            $payload['departmentId'],
            $payload['age1'],
            $payload['age2'],
        ];

        $spMoodsAnalysisData = DB::select('CALL sp_dashboard_psychological_moods_analysis(?, ?, ?, ?, ?, ?)', $procedureData);

        if (!empty($spMoodsAnalysisData)) {
            $data['moodAnalysis'] = [
                'title'   => array_column($spMoodsAnalysisData, 'title'),
                'percent' => array_column($spMoodsAnalysisData, 'percent'),
            ];
        }

        if (isset($payload['options']) && isset($payload['options']['change'])) {
            if ($payload['options']['change'] == 'daterangeSuperstars') {
                $fromDate = !empty($payload['options']) && isset($payload['options']['fromDateSuperstars']) ? $payload['options']['fromDateSuperstars'] : Carbon::parse(now()->toDateTimeString())->setTimeZone($timezone)->subDays(7)->format('Y-m-d 00:00:00');
                $toDate   = !empty($payload['options']) && isset($payload['options']['endDateSuperstars']) ? $payload['options']['endDateSuperstars'] : Carbon::parse(now()->toDateTimeString())->setTimeZone($timezone)->format('Y-m-d H:i:s');
            }
        }

        $procedureData = [
            $timezone,
            $fromDate,
            $toDate,
            $payload['companyId'],
            $payload['departmentId'],
            $payload['age1'],
            $payload['age2'],
        ];

        $spActiveTeamData       = DB::select('CALL sp_dashboard_app_usage_active_team(?, ?, ?, ?, ?, ?, ?)', $procedureData);
        $spActiveIndividualData = DB::select('CALL sp_dashboard_app_usage_active_individual(?, ?, ?, ?, ?, ?, ?)', $procedureData);
        $spBadgesEarnedData     = DB::select('CALL sp_dashboard_app_usage_badges_earned(?, ?, ?, ?, ?, ?, ?)', $procedureData);

        if (!empty($spActiveTeamData)) {
            $data['activeTeamData'] = collect($spActiveTeamData)->map(function ($item) {
                $team = Team::find($item->team_id);
                return [
                    'name'         => $team->name,
                    'company'      => $team->company()->first()->name,
                    'logo'         => $team->logo,
                    'averageHours' => $item->averageHours,
                ];
            })->toArray();
        }

        if (!empty($spActiveIndividualData)) {
            $data['activeIndividualData'] = collect($spActiveIndividualData)->map(function ($item) {
                $user = User::find($item->user_id);
                return [
                    'name'       => $user->first_name . ' ' . $user->last_name,
                    'company'    => $user->company()->first()->name,
                    'logo'       => $user->logo,
                    'totalHours' => $item->totalHours,
                ];
            })->toArray();
        }

        if (!empty($spBadgesEarnedData)) {
            $data['badgesEarnedData'] = collect($spBadgesEarnedData)->map(function ($item) {
                $user = User::find($item->user_id);
                return [
                    'name'       => $user->first_name . ' ' . $user->last_name,
                    'company'    => $user->company()->first()->name,
                    'logo'       => $user->logo,
                    'mostBadges' => $item->mostBadges,
                ];
            })->toArray();
        }

        return $data;
    }

    /**
     * Get Psychological Tab Tier 1 Data - Psychological category and sub-categories data of health score
     *
     * @param Request $request
     * @return Array
     */
    public function getPsychologicalTabTier1Data($payload)
    {
        $payload['category'] = 2;

        return $this->getHealthScoreCategoryCommonData($payload);
    }

    /**
     * Get Psychological Tab Tier 2 Data - Meditation hours chart
     *
     * @param Request $request
     * @return Array
     */
    public function getPsychologicalTabTier2Data($payload)
    {
        $totaAppUsers = $avgMeditationTime = 0;
        $data         = [
            'data'              => [],
            'labels'            => [],
            'totalUsers'        => $totaAppUsers,
            'avgMeditationTime' => $avgMeditationTime,
        ];

        $timezone          = Auth::user()->timezone ?? null;
        $timezone          = !empty($timezone) ? $timezone : config('app.timezone');
        $durationThreshold = !empty($payload['options']) && isset($payload['options']['fromDateMeditationHours']) ? $payload['options']['fromDateMeditationHours'] : 7;
        $type              = (($durationThreshold <= 7) ? "day" : (($durationThreshold <= 30) ? "month" : "year"));
        $fromDate          = Carbon::parse(now()->toDateTimeString())->setTimeZone($this->timezone)->subDays($durationThreshold - 1)->format('Y-m-d 00:00:00');
        $emptyData         = $this->getDurationEmptyData($type, ['today' => $fromDate, 'timezone' => $timezone]);
        $groupByCol        = (($type == 'day') ? 'log_date_only' : (($type == 'month') ? 'log_date_week' : 'log_month'));

        $procedureData = [
            'app',
            null,
            null,
            $payload['companyId'],
            $payload['departmentId'],
            $payload['age1'],
            $payload['age2'],
            null,
        ];

        $totaAppUsers = spGetUser($procedureData);

        $procedureData = [
            $timezone,
            $type,
            $fromDate,
            $payload['companyId'],
            $payload['departmentId'],
            $payload['age1'],
            $payload['age2'],
        ];
        $chartData = DB::select('CALL sp_inspire_meditation_hours(?, ?, ?, ?, ?, ?, ?)', $procedureData);
        if (!empty($chartData)) {
            $totalListenedHours = array_sum(array_column($chartData, 'listened_hours'));
            $avgMeditationTime  = (($totalListenedHours > 0 && $totaAppUsers > 0) ? ($totalListenedHours / ($totaAppUsers * $durationThreshold)) : 0);
            $chartData          = Collect($chartData)->pluck('listened_hours', $groupByCol);
        }

        foreach ($emptyData as $key => $value) {
            array_push($data['labels'], $value);
            array_push($data['data'], (isset($chartData[$key]) ? $chartData[$key] : 0));
        }

        $data['totalUsers']        = numberFormatShort($totaAppUsers);
        $data['avgMeditationTime'] = number_format($avgMeditationTime, 2) . ' Hrs' . (($avgMeditationTime > 1) ? 's' : '');

        return $data;
    }

    /**
     * Get Psychological Tab Tier 3 Data - Popular meditation categories and Top 10 meditations chart
     *
     * @param Request $request
     * @return Array
     */
    public function getPsychologicalTabTier3Data($payload)
    {
        $timezone = Auth::user()->timezone ?? null;
        $timezone = !empty($timezone) ? $timezone : config('app.timezone');
        $fromDate = !empty($payload['options']) && isset($payload['options']['fromDateTopMeditationTracks']) ? $payload['options']['fromDateTopMeditationTracks'] : Carbon::parse(now()->toDateTimeString())->setTimeZone($timezone)->subDays(30)->format('Y-m-d 00:00:00');
        $toDate   = !empty($payload['options']) && isset($payload['options']['endDateTopMeditationTracks']) ? $payload['options']['endDateTopMeditationTracks'] : Carbon::parse(now()->toDateTimeString())->setTimeZone($timezone)->format('Y-m-d H:i:s');
        $data     = [];

        $procedureData = [
            $payload['companyId'],
            $payload['departmentId'],
            $payload['age1'],
            $payload['age2'],
        ];

        $spPopularMeditationCategoriesData = DB::select('CALL sp_dashboard_psychological_popular_meditations(?, ?, ?, ?)', $procedureData);

        if (!empty($spPopularMeditationCategoriesData)) {
            $data['popularMeditationCategoriesData'] = [
                'meditationCategory' => array_column($spPopularMeditationCategoriesData, 'meditationCategory'),
                'totalViews'         => array_column($spPopularMeditationCategoriesData, 'totalViews'),
            ];
        }

        $procedureData = [
            $timezone,
            $fromDate,
            $toDate,
            $payload['companyId'],
            $payload['departmentId'],
            $payload['age1'],
            $payload['age2'],
        ];

        $spTopMeditationTracksData = DB::select('CALL sp_dashboard_psychological_top_meditations(?, ?, ?, ?, ?, ?, ?)', $procedureData);

        if (!empty($spTopMeditationTracksData)) {
            $data['topMeditationTracksData'] = [
                'meditationTitle' => array_column($spTopMeditationTracksData, 'title'),
                'totalViews'      => array_column($spTopMeditationTracksData, 'totalViews'),
            ];
        }

        if (isset($payload['options']) && isset($payload['options']['change'])) {
            $data['change'] = $payload['options']['change'];
        }

        return $data;
    }

    /**
     * Get Psychological Tab Tier 4 Data - Moods analysis
     *
     * @param Request $request
     * @return Array
     */
    public function getPsychologicalTabTier4Data($payload)
    {
        $timezone = Auth::user()->timezone ?? null;
        $timezone = !empty($timezone) ? $timezone : config('app.timezone');
        $fromDate = !empty($payload['options']) && isset($payload['options']['fromDateMoodsAnalysis']) ? Carbon::parse(now()->toDateTimeString())->setTimeZone($timezone)->subDays($payload['options']['fromDateMoodsAnalysis'])->format('Y-m-d 00:00:00') : Carbon::parse(now()->toDateTimeString())->setTimeZone($timezone)->subDays(7)->format('Y-m-d 00:00:00');
        $data     = [];

        $procedureData = [
            $timezone,
            $fromDate,
            $payload['companyId'],
            $payload['departmentId'],
            $payload['age1'],
            $payload['age2'],
        ];

        $spMoodsAnalysisData = DB::select('CALL sp_dashboard_psychological_moods_analysis(?, ?, ?, ?, ?, ?)', $procedureData);

        if (!empty($spMoodsAnalysisData)) {
            $data['moodAnalysis'] = [
                'title'   => array_column($spMoodsAnalysisData, 'title'),
                'percent' => array_column($spMoodsAnalysisData, 'percent'),
            ];
        }

        return $data;
    }

    /**
     * Get Health Score Categories common data for physical and psychological tab
     *
     * @param $payload
     * @return Array
     */
    public function getHealthScoreCategoryCommonData($payload)
    {
        $role       = getUserRole();
        $timezone   = Auth::user()->timezone ?? null;
        $timezone   = !empty($timezone) ? $timezone : config('app.timezone');
        $last30Days = Carbon::parse(now()->toDateTimeString())->setTimeZone($timezone)->subDays(30)->format('Y-m-d 00:00:00');
        $data = [
            'hsCategoryData'    => [],
            'hsSubCategoryData' => [],
            'attemptedBy'       => [
                'attemptedPercent' => 0,
            ],
        ];

        $procedureData = [
            $timezone,
            $last30Days,
            $payload['companyId'],
            $payload['departmentId'],
        ];

        $spTeamsData       = DB::select('CALL sp_dashboard_app_usage_teams(?, ?, ?, ?)', $procedureData);
        $data['teamsData'] = !empty($spTeamsData) ? Arr::collapse(json_decode(json_encode($spTeamsData), true)) : [];

        $procedureData = [
            $timezone,
            $payload['companyId'],
            $role->group,
        ];

        $spChallengesData       = DB::select('CALL sp_dashboard_app_usage_challenges(?, ?, ?)', $procedureData);
        $data['challengesData'] = !empty($spChallengesData) ? Arr::collapse(json_decode(json_encode($spChallengesData), true)) : [];

        return $data;
    }

    /**
     * Get Duration empty data for meditiation hours chart
     *
     * @param $payload
     * @return Array
     */
    public function getDurationEmptyData($type, $data)
    {
        switch ($type) {
            case 'day':
                return getDaysbyKeys($data['today'], $data['timezone']);
                break;
            case 'month':
                return getWeeksbyKeys($data['today'], $data['timezone']);
                break;
            case 'year':
                return getMonthsbyKeys($data['today'], $data['timezone'], (isset($data['key_with_year']) ? $data['key_with_year'] : null));
                break;
            default:
                return [];
                break;
        }
    }

    /**
     * Get Audit Tab Tier 1 Data - Company score gauge and line chart
     *
     * @param Request $request
     * @return Array
     */
    public function getAuditTabTier1Data($payload)
    {
        $user                      = auth()->user();
        $timezone                  = !empty($user->timezone) ? $user->timezone : config('app.timezone');
        $fromDate                  = !empty($payload['options']) && isset($payload['options']['fromDateCompanyScore']) ? $payload['options']['fromDateCompanyScore'] : Carbon::parse(now()->toDateTimeString())->setTimeZone($timezone)->subMonths(5)->format('Y-m-d 00:00:00');
        $toDate                    = !empty($payload['options']) && isset($payload['options']['endDateCompanyScore']) ? $payload['options']['endDateCompanyScore'] : Carbon::parse(now()->toDateTimeString())->setTimeZone($timezone)->format('Y-m-d H:i:s');
        $companyScoreLineChartData = ['data' => [], 'labels' => [], 'colors' => []];
        $emptyData                 = getMonthsBetweenDatebyKeys($timezone, $fromDate, $toDate);

        // Company score gauge chart calculation
        $procedureData = [
            $timezone,
            $fromDate,
            $toDate,
            config('zevolifesettings.zc_survey_max_score_value', 7),
            $payload['companyId'],
            $payload['departmentId'],
            $payload['age1'],
            $payload['age2'],
        ];
        $spCompanyScoreGaugeChartData = DB::select('CALL sp_dashboard_audit_company_score(?, ?, ?, ?, ?, ?, ?, ?)', $procedureData);
        $spCompanyScoreGaugeChartData = (!empty($spCompanyScoreGaugeChartData) ? $spCompanyScoreGaugeChartData[0] : (object) ['percentage' => 0]);

        // Company score line chart calculation
        $procedureData = [
            $timezone,
            $fromDate,
            $toDate,
            config('zevolifesettings.zc_survey_max_score_value', 7),
            $payload['companyId'],
            $payload['departmentId'],
            null,
            null,
            $payload['age1'],
            $payload['age2'],
        ];
        $spCompanyScoreLineChartData = DB::select('CALL sp_dashboard_audit_company_score_line_graph(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', $procedureData);
        if (!empty($spCompanyScoreLineChartData)) {
            $chartData = Collect($spCompanyScoreLineChartData)->pluck('month_percentage', 'log_month')->toArray();
            foreach ($emptyData as $key => $value) {
                if (isset($chartData[$key]) || is_numeric(array_key_last($companyScoreLineChartData['data']))) {
                    $data  = (isset($chartData[$key]) ? $chartData[$key] : end($companyScoreLineChartData['data']));
                    $color = getScoreColor($data);
                    array_push($companyScoreLineChartData['labels'], $value);
                    array_push($companyScoreLineChartData['data'], $data);
                    array_push($companyScoreLineChartData['colors'], $color);
                }
            }
        }

        $procedureData = [
            $timezone,
            $fromDate,
            $toDate,
            config('zevolifesettings.zc_survey_max_score_value', 7),
            ($payload['companyId'] ?? null),
            ($payload['departmentId'] ?? null),
            null,
            null,
            $payload['age1'],
            $payload['age2'],
        ];
        $chartData = DB::select('CALL sp_dashboard_audit_company_score_baseline(?, ?, ?, ?, ?, ?, ?, ?, ?, ?);', $procedureData);
        if (!empty($chartData) && isset($chartData[0])) {
            $companyScoreLineChartData['baseline'] = (($chartData[0]->baseLine > 0) ? $chartData[0]->baseLine : 0);
        }

        return [
            'companyScoreGaugeChart' => [
                'score'  => $spCompanyScoreGaugeChartData->percentage,
                'data'   => [$spCompanyScoreGaugeChartData->percentage, (100 - $spCompanyScoreGaugeChartData->percentage)],
                'colors' => [getScoreColor($spCompanyScoreGaugeChartData->percentage), "#ffffff"],
                'label'  => [trans('dashboard.audit.headings.company_score'), ""],
            ],
            'companyScoreLineChart'  => $companyScoreLineChartData,
        ];
    }

    /**
     * Get Audit Tab Tier 2 Data - category wise tabs
     *
     * @param Request $request
     * @return Array
     */
    public function getAuditTabTier2Data($payload)
    {
        $user                 = auth()->user();
        $timezone             = !empty($user->timezone) ? $user->timezone : config('app.timezone');
        $categoryWiseTabsData = [
            'tabs' => [],
        ];

        // category wise tabs
        $procedureData = [
            config('zevolifesettings.zc_survey_max_score_value', 7),
            $payload['companyId'],
            $payload['departmentId'],
            null,
            $timezone,
            null,
            null,
            $payload['age1'],
            $payload['age2'],
        ];
        $spCategoryWiseCompanyScoreChartData = DB::select('call sp_dashboard_audit_category_wise_tabs(?, ?, ?, ?, ?, ?, ?, ?, ?)', $procedureData);
        if (!empty($spCategoryWiseCompanyScoreChartData)) {
            foreach ($spCategoryWiseCompanyScoreChartData as $value) {
                $subCategory                    = SurveyCategory::find($value->category_id, ['id']);
                $value->image                   = $subCategory->logo;
                $categoryWiseTabsData['tabs'][] = $value;
            }
        }
        return $categoryWiseTabsData;
    }

    /**
     * Get Audit Tab Tier 3 Data - category wise company score gauge and line charts
     *
     * @param Request $request
     * @return Array
     */
    public function getAuditTabTier3Data($payload)
    {
        $user                  = auth()->user();
        $company               = $user->company->first();
        $role                  = getUserRole($user);
        $timezone              = !empty($user->timezone) ? $user->timezone : config('app.timezone');
        $fromDate              = !empty($payload['options']) && isset($payload['options']['fromDateCategoryCompanyScore']) ? $payload['options']['fromDateCategoryCompanyScore'] : Carbon::parse(now()->toDateTimeString())->setTimeZone($timezone)->subMonths(5)->format('Y-m-d 00:00:00');
        $toDate                = !empty($payload['options']) && isset($payload['options']['endDateCategoryCompanyScore']) ? $payload['options']['endDateCategoryCompanyScore'] : Carbon::parse(now()->toDateTimeString())->setTimeZone($timezone)->format('Y-m-d H:i:s');
        $category_id           = (int) (!empty($payload['options']['category_id']) ? $payload['options']['category_id'] : 0);
        $change                = (!empty($payload['options']['change']) ? $payload['options']['change'] : 'true');
        $emptyData             = getMonthsBetweenDatebyKeys($timezone, $fromDate, $toDate);
        $categoryWiseChartData = [
            'change'                         => $change,
            'companyCategoryScoreGaugeChart' => [
                'colors' => ["#ffffff"],
                'data'   => [0],
                'labels'  => [trans('dashboard.audit.tooltips.category_score')],
            ],
            'score'                          => [],
            'performance'                    => [
                'labels' => [],
                'data'   => [],
                'colors' => [],
            ],
            'questionReportURL'              => '#',
        ];
        $urlParam = [$category_id, 'from' => $fromDate, 'to' => $toDate];
        if (!empty($payload['companyId']) && ($role->group == 'company' || ($role->group == 'reseller' && $company->parent_id != null))) {
            $urlParam['company'] = $payload['companyId'];
        }

        $categoryWiseChartData['questionReportURL'] = route('dashboard.questionReport', $urlParam);

        // specified category wise company score gauge charts
        $procedureData = [
            config('zevolifesettings.zc_survey_max_score_value', 7),
            $payload['companyId'],
            $payload['departmentId'],
            (!empty($category_id) ? $category_id : null),
            $timezone,
            $fromDate,
            $toDate,
            $payload['age1'],
            $payload['age2'],
        ];
        $spCategoryWiseCompanyScoreChartData = DB::select('call sp_dashboard_audit_category_wise_graph(?, ?, ?, ?, ?, ?, ?, ?, ?)', $procedureData);
        if (!empty($spCategoryWiseCompanyScoreChartData)) {
            $score                                                   = $spCategoryWiseCompanyScoreChartData[0];
            $categoryWiseChartData['score']                          = $score;
            $categoryWiseChartData['companyCategoryScoreGaugeChart'] = [
                'data'   => [$score->category_percentage, (100 - $score->category_percentage)],
                'colors' => [getScoreColor($score->category_percentage), "#ffffff"],
                'labels'  => [trans('dashboard.audit.tooltips.category_score'), ""],
            ];
        }

        // specified category wise company score line charts
        $procedureData = [
            $timezone,
            $fromDate,
            $toDate,
            config('zevolifesettings.zc_survey_max_score_value', 7),
            $payload['companyId'],
            $payload['departmentId'],
            (!empty($category_id) ? $category_id : null),
            null,
            $payload['age1'],
            $payload['age2'],
        ];
        $spCategoryWiseCompanyScoreLineChartData = DB::select('CALL sp_dashboard_audit_company_score_line_graph(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', $procedureData);

        if (!empty($spCategoryWiseCompanyScoreLineChartData)) {
            $chartData = Collect($spCategoryWiseCompanyScoreLineChartData)->pluck('month_percentage', 'log_month');
            foreach ($emptyData as $key => $value) {
                if (isset($chartData[$key]) || is_numeric(array_key_last($categoryWiseChartData['performance']['data']))) {
                    $data  = (isset($chartData[$key]) ? $chartData[$key] : end($categoryWiseChartData['performance']['data']));
                    $color = getScoreColor($data);
                    array_push($categoryWiseChartData['performance']['labels'], $value);
                    array_push($categoryWiseChartData['performance']['data'], $data);
                    array_push($categoryWiseChartData['performance']['colors'], $color);
                }
            }
        }

        $procedureData = [
            $timezone,
            $fromDate,
            $toDate,
            config('zevolifesettings.zc_survey_max_score_value', 7),
            ($payload['companyId'] ?? null),
            ($payload['departmentId'] ?? null),
            (!empty($category_id) ? $category_id : null),
            null,
            $payload['age1'],
            $payload['age2'],
        ];
        $chartData = DB::select('CALL sp_dashboard_audit_company_score_baseline(?, ?, ?, ?, ?, ?, ?, ?, ?, ?);', $procedureData);
        if (!empty($chartData) && isset($chartData[0])) {
            $categoryWiseChartData['performance']['baseline'] = (($chartData[0]->baseLine > 0) ? $chartData[0]->baseLine : 0);
        }

        $procedureData = [
            config('zevolifesettings.zc_survey_max_score_value', 7),
            $payload['companyId'],
            $payload['departmentId'],
            (!empty($category_id) ? $category_id : null),
            null,
            $timezone,
            $fromDate,
            $toDate,
            $payload['age1'],
            $payload['age2'],
        ];
        $spSubCategoryWiseCompanyScoreChartData = DB::select('call sp_dashboard_audit_sub_category_wise_graph(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', $procedureData);
        $categoryWiseChartData['subcategories'] = "";
        if (!empty($spSubCategoryWiseCompanyScoreChartData)) {
            foreach ($spSubCategoryWiseCompanyScoreChartData as $key => $category) {
                $categoryWiseChartData['subcategories'] .= "<option value='{$category->sub_category_id}'>{$category->subcategory_name}</option>";
            }
        }

        return $categoryWiseChartData;
    }

    /**
     * Get Audit Tab Tier 4 Data - subcategory wise company score gauge charts
     *
     * @param Request $request
     * @return Array
     */
    public function getAuditTabTier4Data($payload)
    {
        $user           = auth()->user();
        $timezone       = !empty($user->timezone) ? $user->timezone : config('app.timezone');
        $fromDate       = !empty($payload['options']) && isset($payload['options']['fromDateCategoryCompanyScore']) ? $payload['options']['fromDateCategoryCompanyScore'] : Carbon::parse(now()->toDateTimeString())->setTimeZone($timezone)->subMonths(5)->format('Y-m-d 00:00:00');
        $toDate         = !empty($payload['options']) && isset($payload['options']['endDateCategoryCompanyScore']) ? $payload['options']['endDateCategoryCompanyScore'] : Carbon::parse(now()->toDateTimeString())->setTimeZone($timezone)->format('Y-m-d H:i:s');
        $category_id    = (int) (!empty($payload['options']['category_id']) ? $payload['options']['category_id'] : null);
        $subcategory_id = (int) (!empty($payload['options']['sub_category_id']) ? $payload['options']['sub_category_id'] : null);
        $emptyData      = getMonthsBetweenDatebyKeys($timezone, $fromDate, $toDate);

        $subCategoriesWiseChartData = [
            'subcategories' => [],
            'performance'   => [
                'labels' => [],
                'data'   => [],
                'colors' => [],
            ],
        ];

        $procedureData = [
            config('zevolifesettings.zc_survey_max_score_value', 7),
            $payload['companyId'],
            $payload['departmentId'],
            $category_id,
            null,
            $timezone,
            $fromDate,
            $toDate,
            $payload['age1'],
            $payload['age2'],
        ];
        $spSubCategoryWiseCompanyScoreChartData = DB::select('call sp_dashboard_audit_sub_category_wise_graph(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', $procedureData);
        if (!empty($spSubCategoryWiseCompanyScoreChartData)) {
            $subCategoriesWiseChartData['subcategories'] = $spSubCategoryWiseCompanyScoreChartData;
        }

        // specified subcategory wise company score line charts
        $procedureData = [
            $timezone,
            $fromDate,
            $toDate,
            config('zevolifesettings.zc_survey_max_score_value', 7),
            $payload['companyId'],
            $payload['departmentId'],
            $category_id,
            $subcategory_id,
            $payload['age1'],
            $payload['age2'],
        ];
        $spSubCategoryWiseCompanyScoreLineChartData = DB::select('CALL sp_dashboard_audit_company_score_line_graph(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', $procedureData);
        if (!empty($spSubCategoryWiseCompanyScoreLineChartData)) {
            $chartData = Collect($spSubCategoryWiseCompanyScoreLineChartData)->pluck('month_percentage', 'log_month');
            foreach ($emptyData as $key => $value) {
                if (isset($chartData[$key]) || is_numeric(array_key_last($subCategoriesWiseChartData['performance']['data']))) {
                    $data  = (isset($chartData[$key]) ? $chartData[$key] : end($subCategoriesWiseChartData['performance']['data']));
                    $color = getScoreColor($data);
                    array_push($subCategoriesWiseChartData['performance']['labels'], $value);
                    array_push($subCategoriesWiseChartData['performance']['data'], $data);
                    array_push($subCategoriesWiseChartData['performance']['colors'], $color);
                }
            }
        }

        $procedureData = [
            $timezone,
            $fromDate,
            $toDate,
            config('zevolifesettings.zc_survey_max_score_value', 7),
            ($payload['companyId'] ?? null),
            ($payload['departmentId'] ?? null),
            $category_id,
            $subcategory_id,
            $payload['age1'],
            $payload['age2'],
        ];
        $chartData = DB::select('CALL sp_dashboard_audit_company_score_baseline(?, ?, ?, ?, ?, ?, ?, ?, ?, ?);', $procedureData);
        if (!empty($chartData) && isset($chartData[0])) {
            $subCategoriesWiseChartData['performance']['baseline'] = (($chartData[0]->baseLine > 0) ? $chartData[0]->baseLine : 0);
        }
        return $subCategoriesWiseChartData;
    }

    /**
     * category tabs
     *
     * @param Request $request
     * @return Array
     */
    public function getQuestionReportTier1Data($payload)
    {
        $user      = auth()->user();
        $timezone  = !empty($user->timezone) ? $user->timezone : config('app.timezone');
        $tier1Data = [
            'tabs' => [],
        ];
        $procedureData = [
            config('zevolifesettings.zc_survey_max_score_value', 7),
            ($payload['companyId'] ?? null),
            null,
            null,
            $timezone,
            $payload['fromDate'],
            $payload['endDate'],
            null,
            null,
        ];
        $tabs              = DB::select('call sp_dashboard_audit_category_wise_tabs(?, ?, ?, ?, ?, ?, ?, ?, ?)', $procedureData);
        $tier1Data['tabs'] = (!empty($tabs) ? $tabs : []);
        return $tier1Data;
    }

    /**
     * category score gauge charts and subcategory progrss bars
     *
     * @param Request $request
     * @return Array
     */
    public function getQuestionReportTier2Data($payload)
    {
        $user      = auth()->user();
        $timezone  = !empty($user->timezone) ? $user->timezone : config('app.timezone');
        $tier2Data = [
            'score'         => [],
            'subcategories' => [],
        ];
        $procedureData = [
            config('zevolifesettings.zc_survey_max_score_value', 7),
            ($payload['companyId'] ?? null),
            null,
            $payload['categoryId'],
            $timezone,
            $payload['fromDate'],
            $payload['endDate'],
            null,
            null,
        ];
        $categoryScoreGaugeChartData = DB::select('call sp_dashboard_audit_category_wise_tabs(?, ?, ?, ?, ?, ?, ?, ?, ?)', $procedureData);
        if (!empty($categoryScoreGaugeChartData)) {
            $categoryScoreGaugeChartData             = $categoryScoreGaugeChartData[0];
            $categoryScoreGaugeChartData->color_code = getScoreColor($categoryScoreGaugeChartData->category_percentage);
            $tier2Data['score']                      = $categoryScoreGaugeChartData;
        }

        $procedureData = [
            config('zevolifesettings.zc_survey_max_score_value', 7),
            $payload['companyId'],
            null,
            $payload['categoryId'],
            null,
            $timezone,
            $payload['fromDate'],
            $payload['endDate'],
            null,
            null,
        ];
        $subCategoryData = DB::select('call sp_dashboard_audit_sub_category_wise_graph(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', $procedureData);
        if (!empty($subCategoryData)) {
            $tier2Data['subcategories'] = $subCategoryData;
        }

        return $tier2Data;
    }

    /**
     * subcategory progrss bars
     *
     * @param Request $request
     * @return Array
     */
    public function getQuestionReportTier3Data($payload)
    {
        $user           = auth()->user();
        $role           = getUserRole();
        $company        = $user->company->first();
        $timezone       = !empty($user->timezone) ? $user->timezone : config('app.timezone');
        $fromDate       = (isset($payload['fromDate']) ? $payload['fromDate'] : Carbon::parse(now()->toDateTimeString())->setTimeZone($timezone)->subMonths(5)->format('Y-m-d 00:00:00'));
        $toDate         = isset($payload['endDate']) ? $payload['endDate'] : Carbon::parse(now()->toDateTimeString())->setTimeZone($timezone)->format('Y-m-d H:i:s');
        $subcategory_id = (!empty($payload['options']['subcategory_id']) ? $payload['options']['subcategory_id'] : 0);
        $param = ['from' => $payload['fromDate'], 'to' => $payload['endDate']];

        $questions = ZcSurveyResponse::whereRaw("(CONVERT_TZ(zc_survey_responses.created_at, ?, ?) BETWEEN ? AND ?)",[
                'UTC',$timezone,$fromDate,$toDate
            ])->join('zc_questions', function ($join) {
                $join->on('zc_questions.id', '=', 'zc_survey_responses.question_id');
            })
            ->join('zc_question_types', function ($join) {
                $join->on('zc_question_types.id', '=', 'zc_questions.question_type_id');
            })
            ->where('zc_questions.category_id', $payload['categoryId'])
            ->where('zc_questions.sub_category_id', $subcategory_id)
            ->select(
                "zc_questions.id",
                "zc_questions.title AS question",
                "zc_question_types.id AS question_type_id",
                "zc_question_types.display_name AS question_type",
                DB::raw('COUNT(`zc_survey_responses`.`question_id`) as `responses`'),
                DB::raw("IFNULL(FORMAT(((SUM(zc_survey_responses.score) * 100) / (IFNULL(SUM(zc_survey_responses.max_score), 0))), 2), 0) AS percentage")
            )
            ->groupBy('zc_survey_responses.question_id');

        if (!empty($payload['companyId'])) {
            $questions->where('zc_survey_responses.company_id', $payload['companyId']);
            $param['company'] = $payload['companyId'];
        } elseif ($role->group == 'reseller' && $company->parent_id == null) {
            $companyIds = company::select('id')->where('id', $company->id)->orWhere('parent_id', $company->id)->get()->pluck('id')->toArray();
            $questions->whereIn('zc_survey_responses.company_id', $companyIds);
        }

        if (isset($payload['order'])) {
            $column = $payload['columns'][$payload['order'][0]['column']]['data'];
            $order  = $payload['order'][0]['dir'];
            $questions->orderBy($column, $order);
        } else {
            $questions->orderBy('zc_questions.title');
        }

        $total  = $questions->get()->count();
        $record = $questions->offset($payload['start'])->limit($payload['length'])->get();

        return DataTables::of($record)
            ->skipPaging()
            ->addIndexColumn()
            ->addColumn('percentage', function ($record) use ($param) {
                if ($record->question_type_id == 1) {
                    return view('newDashboard.view_freetext_answers_action', ['record' => $record, 'param' => ([$record->id] + $param)])->render();
                } else {
                    return $record->percentage . '%';
                }
            })
            ->rawColumns(['percentage'])
            ->with([
                "recordsTotal"    => $total,
                "recordsFiltered" => $total,
            ])
            ->make(true);
    }

    /**
     * Get Question responses
     *
     * @param ZcQuestion $question
     * @param Array $payload
     * @return Array
     */
    public function getQuestionReportTier4Data($question, $payload)
    {
        $role      = getUserRole();
        $user      = auth()->user();
        $company   = $user->company->first();
        $timezone  = !empty($user->timezone) ? $user->timezone : config('app.timezone');
        $fromDate  = (isset($payload['fromDate']) ? $payload['fromDate'] : Carbon::parse(now()->toDateTimeString())->setTimeZone($timezone)->subMonths(5)->format('Y-m-d 00:00:00'));
        $toDate    = isset($payload['endDate']) ? $payload['endDate'] : Carbon::parse(now()->toDateTimeString())->setTimeZone($timezone)->format('Y-m-d H:i:s');
        $questions = ZcSurveyResponse::whereRaw("(CONVERT_TZ(zc_survey_responses.created_at, ?, ?) BETWEEN ? AND ?)",[
                'UTC',$timezone,$fromDate,$toDate
            ])->join('companies', function ($join) {
                $join->on('companies.id', '=', 'zc_survey_responses.company_id');
            })
            ->where('zc_survey_responses.question_id', $question->id)
            ->select(
                "zc_survey_responses.answer_value",
                "companies.name AS company_name"
            );

        if (!empty($payload['companyId'])) {
            $questions->where('zc_survey_responses.company_id', $payload['companyId']);
            $param['company'] = $payload['companyId'];
        } elseif ($role->group == 'reseller' && $company->parent_id == null) {
            $questions->where(function ($where) use ($company) {
                $where->where('companies.id', $company->id)->orWhere('companies.parent_id', $company->id);
            });
        }

        if (isset($payload['order'])) {
            $column = $payload['columns'][$payload['order'][0]['column']]['data'];
            $order  = $payload['order'][0]['dir'];
            $questions->orderBy($column, $order);
        } else {
            $questions->orderByDesc('zc_survey_responses.id');
        }

        $total  = $questions->get()->count();
        $record = $questions->offset($payload['start'])->limit($payload['length'])->get();

        return DataTables::of($record)
            ->skipPaging()
            ->addIndexColumn()
            ->rawColumns([])
            ->with([
                "recordsTotal"    => $total,
                "recordsFiltered" => $total,
            ])
            ->make(true);
    }

    /**
     * Get Booking Tab
     *
     * @param Array $payload
     * @return Array
     */
    public function getBookingTabTier1Data($payload)
    {
        $user       = Auth::user();
        $timezone   = $user->timezone ?? null;
        $timezone   = !empty($timezone) ? $timezone : config('app.timezone');
        $toDates    = Carbon::parse(now()->toDateTimeString())->setTimeZone($timezone)->setTime(0, 0, 0)->toDateTimeString();
        $next30Days = Carbon::parse(now()->toDateTimeString())->setTimeZone($timezone)->addDays(30)->setTime(23, 59, 59)->toDateTimeString();
        $role       = getUserRole($user);
        $data       = [];
        $company    = $user->company->first();
        $companyId  = ($company != null) ? $company->id : null;

        $roleName = null;
        if ($role->group == 'zevo') {
            $roleName = 'zevo';
        } elseif ($role->group == 'reseller' && isset($company) && $company->parent_id == null) {
            $roleName = 'reseller';
        }

        $procedureData = [
            $timezone,
            $next30Days,
            $toDates,
            $payload['companyId'],
            '30days',
            $roleName,
            $companyId,
        ];

        $spBookingData = DB::select('CALL sp_dashboard_booking_tab(?, ?, ?, ?, ?, ?, ?)', $procedureData);

        $data['last30daysbookingData'] = !empty($spBookingData) ? Arr::collapse(json_decode(json_encode($spBookingData), true)) : [];

        $next7Days     = Carbon::parse(now()->toDateTimeString())->setTimeZone($timezone)->addDays(7)->setTime(23, 59, 59)->toDateTimeString();
        $procedureData = [
            $timezone,
            $next7Days,
            $toDates,
            $payload['companyId'],
            '7days',
            $roleName,
            $companyId,
        ];

        $spBookingData = DB::select('CALL sp_dashboard_booking_tab(?, ?, ?, ?, ?, ?, ?)', $procedureData);

        $data['last7daysbookingData'] = !empty($spBookingData) ? Arr::collapse(json_decode(json_encode($spBookingData), true)) : [];

        $todays        = Carbon::parse(now()->toDateTimeString())->setTimeZone($timezone)->setTime(23, 59, 59)->toDateTimeString();
        $procedureData = [
            $timezone,
            $todays,
            $toDates,
            $payload['companyId'],
            'today',
            $roleName,
            $companyId,
        ];

        $spBookingData = DB::select('CALL sp_dashboard_booking_tab(?, ?, ?, ?, ?, ?, ?)', $procedureData);

        $data['todaysbookingData'] = !empty($spBookingData) ? Arr::collapse(json_decode(json_encode($spBookingData), true)) : [];

        $procedureData = [
            $timezone,
            null,
            $toDates,
            $payload['companyId'],
            'total',
            $roleName,
            $companyId,
        ];

        $spBookingData = DB::select('CALL sp_dashboard_booking_tab(?, ?, ?, ?, ?, ?, ?)', $procedureData);

        $data['bookingData'] = !empty($spBookingData) ? Arr::collapse(json_decode(json_encode($spBookingData), true)) : [];

        return $data;
    }

    /**
     * Get Booking Tab - Events Revenue
     *
     * @param Array $payload
     * @return Array
     */
    public function getBookingTabTier2Data($payload)
    {
        $user        = Auth::user();
        $timezone    = $user->timezone ?? null;
        $timezone    = !empty($timezone) ? $timezone : config('app.timezone');
        $appTimezone = config('app.timezone');
        $role        = getUserRole($user);
        $data        = [];
        $company     = $user->company->first();
        $companyId   = ($company != null) ? $company->id : null;
        $toDates     = now($timezone)->setTime(23, 59, 59)->setTimeZone($appTimezone)->toDateTimeString();
        $roleName    = null;
        if ($role->group == 'zevo') {
            $roleName = 'zevo';
        } elseif ($role->group == 'reseller' && isset($company) && $company->parent_id == null) {
            $roleName = 'reseller';
        }

        if (isset($payload['options']['days'])) {
            $days = now($timezone)->subDays($payload['options']['days'])->setTime(0, 0, 0)->setTimeZone($appTimezone)->toDateTimeString();
        } else {
            $days = now($timezone)->subDays(7)->setTime(0, 0, 0)->setTimeZone($appTimezone)->toDateTimeString();
        }

        $procedureData = [
            $timezone,
            $payload['companyId'],
            $days,
            $toDates,
            $roleName,
            $companyId,
        ];

        $spBookingData = DB::select('CALL sp_dashboard_booking_tab_events_revenue(?, ?, ?, ?, ?, ?)', $procedureData);

        $data['bookingDataEventRevenue'] = !empty($spBookingData) ? Arr::collapse(json_decode(json_encode($spBookingData), true)) : [];

        return $data;
    }

    /**
     * Get Booking Tab - Today's Event calendar
     *
     * @param Array $payload
     * @return Array
     */
    public function getBookingTabTier3Data($payload)
    {
        $user      = Auth::user();
        $timezone  = $user->timezone ?? null;
        $timezone  = !empty($timezone) ? $timezone : config('app.timezone');
        $fromDate  = Carbon::parse(now()->toDateTimeString())->setTimeZone($timezone)->setTime(0, 0, 0)->toDateTimeString();
        $toDate    = Carbon::parse(now()->toDateTimeString())->setTimeZone($timezone)->setTime(23, 59, 59)->toDateTimeString();
        $role      = getUserRole($user);
        $data      = [];
        $company   = $user->company->first();
        $companyId = ($company != null) ? $company->id : null;

        $roleName = null;
        if ($role->group == 'zevo') {
            $roleName = 'zevo';
        } elseif ($role->group == 'reseller' && isset($company) && $company->parent_id == null) {
            $roleName = 'reseller';
        }

        $procedureData = [
            $timezone,
            $payload['companyId'],
            $roleName,
            $companyId,
            $fromDate,
            $toDate,
        ];

        $spBookingData = DB::select('CALL sp_dashboard_booking_tab_today_event_calendar(?, ?, ?, ?, ?, ?)', $procedureData);
        $appTimezone   = config('app.timezone');
        foreach ($spBookingData as $key => $value) {
            $bookingDate = Carbon::parse("{$value->booking_date} {$value->start_time}", $appTimezone)
                ->setTimezone($timezone)->format(config('zevolifesettings.date_format.default_date'));
            $startTime = Carbon::parse("{$value->booking_date} {$value->start_time}", $appTimezone)
                ->setTimezone($timezone)->format('h:i A');
            $day = Carbon::parse("{$value->booking_date} {$value->start_time}", $appTimezone)
                ->setTimezone($timezone)->format('l');

            $data[$key]['day']                = $day;
            $data[$key]['displayDate']        = $bookingDate;
            $eventModel                       = Event::find($value->id);
            $data[$key]['mediaImage']         = $eventModel->getMediaData('logo', ['w' => 600, 'h' => 600]);
            $data[$key]['companyName']        = ($roleName != null) ? $value->company_name : '';
            $data[$key]['name']               = $value->name;
            $data[$key]['startTime']          = $startTime;
            $data[$key]['participants_users'] = $value->participants_users;
        }

        return $data;
    }
}
