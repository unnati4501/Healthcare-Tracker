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

class Dashboard extends Model
{
    /**
     * Get App Usage Tab Tier 1 Data - Users, Meditation Hours Blocks
     *
     * @param Request $request
     * @return Array
     */
    public function getAppUsageTabTier1Data($payload)
    {
        try {
            $userId     = Auth::user()->id;
            $timezone   = Auth::user()->timezone ?? null;
            $timezone   = !empty($timezone) ? $timezone : config('app.timezone');
            $last30Days = Carbon::parse(now()->toDateTimeString())->setTimeZone($timezone)->subDays(30)->format('Y-m-d 00:00:00');
            $last7Days  = Carbon::parse(now()->toDateTimeString())->setTimeZone($timezone)->subDays(7)->format('Y-m-d 00:00:00');
            $data       = [];
            $regex      = "/^\d+(?:,\d+)*$/";

            $procedureData = [
                $timezone,
                $last30Days,
                $last7Days,
                $userId,
                (!is_null($payload['companyId']) && preg_match($regex, $payload['companyId'])) ? $payload['companyId'] : null,
                is_numeric($payload['departmentId']) ? $payload['departmentId'] : null,
                is_numeric($payload['locationId']) ? $payload['locationId'] : null,
                is_numeric($payload['age1']) ? $payload['age1'] : null,
                is_numeric($payload['age2']) ? $payload['age2'] : null,
            ];

            $spUsersData       = DB::select('CALL sp_dashboard_app_usage_users(?, ?, ?, ?, ?, ?, ?, ?, ?)', $procedureData);
            $data['usersData'] = !empty($spUsersData) ? Arr::collapse(json_decode(json_encode($spUsersData), true)) : [];

            $totaAppUsers        = $avgMeditationTime        = 0;
            $meditationHoursData = [
                'data'              => [],
                'labels'            => [],
                'totalUsers'        => $totaAppUsers,
                'avgMeditationTime' => $avgMeditationTime,
            ];
            $durationThreshold = !empty($payload['options']) && isset($payload['options']['fromDateMeditationHours']) && is_numeric($payload['options']['fromDateMeditationHours']) ? $payload['options']['fromDateMeditationHours'] : 7;
            $type              = (($durationThreshold <= 7) ? "day" : (($durationThreshold <= 30) ? "month" : "year"));
            $fromDate          = Carbon::parse(now()->toDateTimeString())->setTimeZone($this->timezone)->subDays($durationThreshold - 1)->format('Y-m-d 00:00:00');
            $emptyData         = $this->getDurationEmptyData($type, ['today' => $fromDate, 'timezone' => $timezone]);
            $groupByCol        = (($type == 'day') ? 'log_date_only' : (($type == 'month') ? 'log_date_week' : 'log_month'));

            $procedureData = [
                'app',
                null,
                null,
                (!is_null($payload['companyId']) && preg_match($regex, $payload['companyId'])) ? $payload['companyId'] : null,
                is_numeric($payload['departmentId']) ? $payload['departmentId'] : null,
                is_numeric($payload['age1']) ? $payload['age1'] : null,
                is_numeric($payload['age2']) ? $payload['age2'] : null,
                null,
            ];

            $totaAppUsers = spGetUser($procedureData);

            $procedureData = [
                $timezone,
                $type,
                strtotime($fromDate) !== false ? $fromDate : null,
                (!is_null($payload['companyId']) && preg_match($regex, $payload['companyId'])) ? $payload['companyId'] : null,
                is_numeric($payload['departmentId']) ? $payload['departmentId'] : null,
                is_numeric($payload['locationId']) ? $payload['locationId'] : null,
                is_numeric($payload['age1']) ? $payload['age1'] : null,
                is_numeric($payload['age2']) ? $payload['age2'] : null,
            ];
            $chartData = DB::select('CALL sp_inspire_meditation_hours(?, ?, ?, ?, ?, ?, ?, ?)', $procedureData);

            if (!empty($chartData)) {
                $totalListenedHours = array_sum(array_column($chartData, 'listened_hours'));
                $avgMeditationTime  = (($totalListenedHours > 0 && $totaAppUsers > 0) ? ($totalListenedHours / ($totaAppUsers)) : 0);
                $chartData          = Collect($chartData)->pluck('listened_hours', $groupByCol);
            }

            foreach ($emptyData as $key => $value) {
                array_push($meditationHoursData['labels'], $value);
                array_push($meditationHoursData['data'], (isset($chartData[$key]) ? $chartData[$key] : 0));
            }

            $meditationHoursData['totalUsers']        = numberFormatShort($totaAppUsers);
            $meditationHoursData['avgMeditationTime'] = number_format($avgMeditationTime, 2) . ' Min' . (($avgMeditationTime > 1) ? 's' : '');

            $data['meditationHoursData'] = $meditationHoursData;

            if (isset($payload['options']) && isset($payload['options']['change'])) {
                $data['change'] = $payload['options']['change'];
            }

            return $data;
        } catch (\Illuminate\Database\QueryException $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong_try_again'),
                'status' => 0,
            ];
            return response()->json($messageData);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong_try_again'),
                'status' => 0,
            ];
            return response()->json($messageData);
        }
    }

    /**
     * Get App Usage Tab Tier 2 Data - Meditation - Popular Categories/Top 10
     *
     * @param Request $request
     * @return Array
     */
    public function getAppUsageTabTier2Data($payload)
    {
        try {
            $timezone = Auth::user()->timezone ?? null;
            $timezone = !empty($timezone) ? $timezone : config('app.timezone');
            $data     = [];
            $regex    = "/^\d+(?:,\d+)*$/";
            $fromDate = !empty($payload['options']) && isset($payload['options']['fromDatePopularMeditation']) ? Carbon::parse(now()->toDateTimeString())->setTimeZone($timezone)->subDays($payload['options']['fromDatePopularMeditation'])->format('Y-m-d 00:00:00') : Carbon::parse(now()->toDateTimeString())->setTimeZone($timezone)->subDays(7)->format('Y-m-d 00:00:00');

            $procedureData = [
                $timezone,
                strtotime($fromDate) !== false ? $fromDate : date("Y-m-d 00:00:00"),
                (!is_null($payload['companyId']) && preg_match($regex, $payload['companyId'])) ? $payload['companyId'] : null,
                is_numeric($payload['departmentId']) ? $payload['departmentId'] : null,
                is_numeric($payload['locationId']) ? $payload['locationId'] : null,
                is_numeric($payload['age1']) ? $payload['age1'] : null,
                is_numeric($payload['age2']) ? $payload['age2'] : null,
            ];

            $spPopularMeditationCategoriesData = DB::select('CALL sp_dashboard_psychological_popular_meditations(?, ?, ?, ?, ?, ?, ?)', $procedureData);

            if (!empty($spPopularMeditationCategoriesData)) {
                $data['popularMeditationCategoriesData'] = [
                    'meditationCategory' => array_column($spPopularMeditationCategoriesData, 'meditationCategory'),
                    'totalViews'         => array_column($spPopularMeditationCategoriesData, 'totalViews'),
                ];
            }
            $fromDate = !empty($payload['options']) && isset($payload['options']['fromDateTopMeditationTracks']) ? $payload['options']['fromDateTopMeditationTracks'] : Carbon::parse(now()->toDateTimeString())->setTimeZone($timezone)->subDays(7)->format('Y-m-d 00:00:00');
            $toDate   = !empty($payload['options']) && isset($payload['options']['endDateTopMeditationTracks']) ? $payload['options']['endDateTopMeditationTracks'] : Carbon::parse(now()->toDateTimeString())->setTimeZone($timezone)->format('Y-m-d 00:00:00');

            $procedureData = [
                $timezone,
                strtotime($fromDate) !== false ? $fromDate : date("Y-m-d 00:00:00"),
                strtotime($toDate) !== false ? $toDate : date("Y-m-d 59:59:59"),
                (!is_null($payload['companyId']) && preg_match($regex, $payload['companyId'])) ? $payload['companyId'] : null,
                is_numeric($payload['departmentId']) ? $payload['departmentId'] : null,
                is_numeric($payload['locationId']) ? $payload['locationId'] : null,
                is_numeric($payload['age1']) ? $payload['age1'] : null,
                is_numeric($payload['age2']) ? $payload['age2'] : null,
            ];

            $spTopMeditationTracksData = DB::select('CALL sp_dashboard_psychological_top_meditations(?, ?, ?, ?, ?, ?, ?, ?)', $procedureData);

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
        } catch (\Illuminate\Database\QueryException $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong_try_again'),
                'status' => 0,
            ];
            return response()->json($messageData);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong_try_again'),
                'status' => 0,
            ];
            return response()->json($messageData);
        }
    }

    /**
     * Get App Usage Tab Tier 3 Data - Recipe - Top 5 / Webinar - Popular Categories/Top 10
     *
     * @param Request $request
     * @return Array
     */
    public function getAppUsageTabTier3Data($payload)
    {
        try {
            $timezone      = Auth::user()->timezone ?? null;
            $timezone      = !empty($timezone) ? $timezone : config('app.timezone');
            $data          = [];
            $regex         = "/^\d+(?:,\d+)*$/";
            $procedureData = [
                $timezone,
                (!is_null($payload['companyId']) && preg_match($regex, $payload['companyId'])) ? $payload['companyId'] : null,
                is_numeric($payload['departmentId']) ? $payload['departmentId'] : null,
                is_numeric($payload['locationId']) ? $payload['locationId'] : null,
                is_numeric($payload['age1']) ? $payload['age1'] : null,
                is_numeric($payload['age2']) ? $payload['age2'] : null,
            ];

            $spPopularRecipesData = DB::select('CALL sp_dashboard_physical_popular_recipes(?, ?, ?, ?, ?, ?)', $procedureData);

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

            $fromDate = !empty($payload['options']) && isset($payload['options']['fromDatePopularWebinar']) ? Carbon::parse(now()->toDateTimeString())->setTimeZone($timezone)->subDays($payload['options']['fromDatePopularWebinar'])->format('Y-m-d 00:00:00') : Carbon::parse(now()->toDateTimeString())->setTimeZone($timezone)->subDays(7)->format('Y-m-d 00:00:00');

            $procedureData = [
                $timezone,
                strtotime($fromDate) !== false ? $fromDate : date("Y-m-d 00:00:00"),
                (!is_null($payload['companyId']) && preg_match($regex, $payload['companyId'])) ? $payload['companyId'] : null,
                is_numeric($payload['departmentId']) ? $payload['departmentId'] : null,
                is_numeric($payload['locationId']) ? $payload['locationId'] : null,
                is_numeric($payload['age1']) ? $payload['age1'] : null,
                is_numeric($payload['age2']) ? $payload['age2'] : null,
            ];

            $spPopularWebinarCategoriesData = DB::select('CALL sp_dashboard_app_usage_popular_webinar(?, ?, ?, ?, ?, ?, ?)', $procedureData);

            if (!empty($spPopularWebinarCategoriesData)) {
                $data['popularWebinarCategoriesData'] = [
                    'webinarCategory' => array_column($spPopularWebinarCategoriesData, 'webinarCategory'),
                    'totalViews'      => array_column($spPopularWebinarCategoriesData, 'totalViews'),
                ];
            }

            $fromDate = !empty($payload['options']) && isset($payload['options']['fromDateTopWebinar']) ? $payload['options']['fromDateTopWebinar'] : Carbon::parse(now()->toDateTimeString())->setTimeZone($timezone)->subDays(7)->format('Y-m-d 00:00:00');
            $toDate   = !empty($payload['options']) && isset($payload['options']['endDateTopWebinars']) ? $payload['options']['endDateTopWebinars'] : Carbon::parse(now()->toDateTimeString())->setTimeZone($timezone)->format('Y-m-d 00:00:00');

            $procedureData = [
                $timezone,
                strtotime($fromDate) !== false ? $fromDate : date("Y-m-d 00:00:00"),
                strtotime($toDate) !== false ? $toDate : date("Y-m-d 59:59:59"),
                (!is_null($payload['companyId']) && preg_match($regex, $payload['companyId'])) ? $payload['companyId'] : null,
                is_numeric($payload['departmentId']) ? $payload['departmentId'] : null,
                is_numeric($payload['locationId']) ? $payload['locationId'] : null,
                is_numeric($payload['age1']) ? $payload['age1'] : null,
                is_numeric($payload['age2']) ? $payload['age2'] : null,
            ];

            $spTopWebinarData = DB::select('CALL sp_dashboard_usage_top_webinar(?, ?, ?, ?, ?, ?, ?, ?)', $procedureData);

            if (!empty($spTopWebinarData)) {
                $data['topWebinarsData'] = [
                    'webinarTitle' => array_column($spTopWebinarData, 'title'),
                    'totalViews'   => array_column($spTopWebinarData, 'totalViews'),
                ];
            }

            if (isset($payload['options']) && isset($payload['options']['change'])) {
                $data['change'] = $payload['options']['change'];
            }

            return $data;
        } catch (\Illuminate\Database\QueryException $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong_try_again'),
                'status' => 0,
            ];
            return response()->json($messageData);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong_try_again'),
                'status' => 0,
            ];
            return response()->json($messageData);
        }
    }

    /**
     * Get App Usage Tab Tier 4 Data - Masterclass - Popular Categories/Top 10 / Feed - Popular Categories/Top 10
     *
     * @param Request $request
     * @return Array
     */
    public function getAppUsageTabTier4Data($payload)
    {
        try {
            $timezone = Auth::user()->timezone ?? null;
            $timezone = !empty($timezone) ? $timezone : config('app.timezone');
            $data     = [];
            $regex    = "/^\d+(?:,\d+)*$/";
            $fromDate = !empty($payload['options']) && isset($payload['options']['fromDatePopularMasterclass']) ? Carbon::parse(now()->toDateTimeString())->setTimeZone($timezone)->subDays($payload['options']['fromDatePopularMasterclass'])->format('Y-m-d 00:00:00') : Carbon::parse(now()->toDateTimeString())->setTimeZone($timezone)->subDays(7)->format('Y-m-d 00:00:00');

            $procedureData = [
                $timezone,
                strtotime($fromDate) !== false ? $fromDate : date("Y-m-d 00:00:00"),
                (!is_null($payload['companyId']) && preg_match($regex, $payload['companyId'])) ? $payload['companyId'] : null,
                is_numeric($payload['departmentId']) ? $payload['departmentId'] : null,
                is_numeric($payload['locationId']) ? $payload['locationId'] : null,
                is_numeric($payload['age1']) ? $payload['age1'] : null,
                is_numeric($payload['age2']) ? $payload['age2'] : null,
            ];

            $spPopularMasterclassCategoriesData = DB::select('CALL sp_dashboard_app_usage_popular_masterclass(?, ?, ?, ?, ?, ?, ?)', $procedureData);

            if (!empty($spPopularMasterclassCategoriesData)) {
                $data['popularMasterclassCategoriesData'] = [
                    'masterclassCategory' => array_column($spPopularMasterclassCategoriesData, 'masterclassCategory'),
                    'totalEnrollments'    => array_column($spPopularMasterclassCategoriesData, 'totalEnrollment'),
                ];
            }

            $fromDate = !empty($payload['options']) && isset($payload['options']['fromDateTopMasterclass']) ? $payload['options']['fromDateTopMasterclass'] : Carbon::parse(now()->toDateTimeString())->setTimeZone($timezone)->subDays(30)->format('Y-m-d 00:00:00');
            $toDate   = !empty($payload['options']) && isset($payload['options']['endDateTopMasterclass']) ? $payload['options']['endDateTopMasterclass'] : Carbon::parse(now()->toDateTimeString())->setTimeZone($timezone)->format('Y-m-d 00:00:00');

            $procedureData = [
                $timezone,
                strtotime($fromDate) !== false ? $fromDate : date("Y-m-d 00:00:00"),
                strtotime($toDate) !== false ? $toDate : date("Y-m-d 59:59:59"),
                (!is_null($payload['companyId']) && preg_match($regex, $payload['companyId'])) ? $payload['companyId'] : null,
                is_numeric($payload['departmentId']) ? $payload['departmentId'] : null,
                is_numeric($payload['locationId']) ? $payload['locationId'] : null,
                is_numeric($payload['age1']) ? $payload['age1'] : null,
                is_numeric($payload['age2']) ? $payload['age2'] : null,
            ];

            $spTopMasterclassData = DB::select('CALL sp_dashboard_app_usage_top_masterclass(?, ?, ?, ?, ?, ?, ?, ?)', $procedureData);

            if (!empty($spTopMasterclassData)) {
                $data['topMasterclassData'] = [
                    'masterclassTitle' => array_column($spTopMasterclassData, 'title'),
                    'totalEnrollment'  => array_column($spTopMasterclassData, 'totalEnrollment'),
                ];
            }

            $fromDate = !empty($payload['options']) && isset($payload['options']['fromDatePopularFeeds']) ? Carbon::parse(now()->toDateTimeString())->setTimeZone($timezone)->subDays($payload['options']['fromDatePopularFeeds'])->format('Y-m-d 00:00:00') : Carbon::parse(now()->toDateTimeString())->setTimeZone($timezone)->subDays(7)->format('Y-m-d 00:00:00');

            $procedureData = [
                $timezone,
                strtotime($fromDate) !== false ? $fromDate : date("Y-m-d 00:00:00"),
                (!is_null($payload['companyId']) && preg_match($regex, $payload['companyId'])) ? $payload['companyId'] : null,
                is_numeric($payload['departmentId']) ? $payload['departmentId'] : null,
                is_numeric($payload['locationId']) ? $payload['locationId'] : null,
                is_numeric($payload['age1']) ? $payload['age1'] : null,
                is_numeric($payload['age2']) ? $payload['age2'] : null,
            ];

            $spPopularFeedCategoriesData = DB::select('CALL sp_dashboard_app_usage_popular_feeds(?, ?, ?, ?, ?, ?, ?)', $procedureData);

            if (!empty($spPopularFeedCategoriesData)) {
                $data['popularFeedCategoriesData'] = [
                    'feedCategory' => array_column($spPopularFeedCategoriesData, 'feedCategory'),
                    'totalViews'   => array_column($spPopularFeedCategoriesData, 'totalViews'),
                ];
            }

            $fromDate = !empty($payload['options']) && isset($payload['options']['fromDateTopFeeds']) ? $payload['options']['fromDateTopFeeds'] : Carbon::parse(now()->toDateTimeString())->setTimeZone($timezone)->subDays(7)->format('Y-m-d 00:00:00');
            $toDate   = !empty($payload['options']) && isset($payload['options']['endDateTopFeeds']) ? $payload['options']['endDateTopFeeds'] : Carbon::parse(now()->toDateTimeString())->setTimeZone($timezone)->format('Y-m-d 00:00:00');

            $procedureData = [
                $timezone,
                strtotime($fromDate) !== false ? $fromDate : date("Y-m-d 00:00:00"),
                strtotime($toDate) !== false ? $toDate : date("Y-m-d 23:59:59"),
                (!is_null($payload['companyId']) && preg_match($regex, $payload['companyId'])) ? $payload['companyId'] : null,
                is_numeric($payload['departmentId']) ? $payload['departmentId'] : null,
                is_numeric($payload['locationId']) ? $payload['locationId'] : null,
                is_numeric($payload['age1']) ? $payload['age1'] : null,
                is_numeric($payload['age2']) ? $payload['age2'] : null,
            ];

            $spTopFeedsData = DB::select('CALL sp_dashboard_app_usage_top_feeds(?, ?, ?, ?, ?, ?, ?, ?)', $procedureData);

            if (!empty($spTopFeedsData)) {
                $data['topFeedsData'] = [
                    'FeedsTitle' => array_column($spTopFeedsData, 'title'),
                    'totalViews' => array_column($spTopFeedsData, 'totalViews'),
                ];
            }

            if (isset($payload['options']) && isset($payload['options']['change'])) {
                $data['change'] = $payload['options']['change'];
            }

            return $data;
        } catch (\Illuminate\Database\QueryException $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong_try_again'),
                'status' => 0,
            ];
            return response()->json($messageData);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong_try_again'),
                'status' => 0,
            ];
            return response()->json($messageData);
        }
    }

    /**
     * Get Physical Tab Tier 1 Data - Physical category and sub-categories data of health score
     *
     * @param Request $request
     * @return Array
     */
    public function getPhysicalTabTier1Data($payload)
    {
        try {
            $payload['category'] = 1;

            return $this->getHealthScoreCategoryCommonData($payload);
        } catch (\Illuminate\Database\QueryException $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong_try_again'),
                'status' => 0,
            ];
            return response()->json($messageData);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong_try_again'),
                'status' => 0,
            ];
            return response()->json($messageData);
        }
    }

    /**
     * Get Physical Tab Tier 2 Data - Steps Range, Exercise Range
     *
     * @param Request $request
     * @return Array
     */
    public function getPhysicalTabTier2Data($payload)
    {
        try {
            $timezone = Auth::user()->timezone ?? null;
            $timezone = !empty($timezone) ? $timezone : config('app.timezone');
            $fromDate = !empty($payload['options']) && isset($payload['options']['fromDateExerciseRanges']) ? $payload['options']['fromDateExerciseRanges'] : Carbon::parse(now()->toDateTimeString())->setTimeZone($timezone)->subDays(7)->format('Y-m-d 00:00:00');
            $toDate   = !empty($payload['options']) && isset($payload['options']['endDateExerciseRanges']) ? $payload['options']['endDateExerciseRanges'] : Carbon::parse(now()->toDateTimeString())->setTimeZone($timezone)->format('Y-m-d H:i:s');

            $fromDate = strtotime($fromDate) !== false ? $fromDate : date("Y-m-d 00:00:00");
            $toDate   = strtotime($toDate) !== false ? $toDate : date("Y-m-d 23:59:59");
            $days     = Carbon::parse($toDate)->diffInDays(Carbon::parse($fromDate)) + 1;
            $data     = [];
            $regex    = "/^\d+(?:,\d+)*$/";
            
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
                is_numeric($days) ? $days : 7,
                (!is_null($payload['companyId']) && preg_match($regex, $payload['companyId'])) ? $payload['companyId'] : null,
                is_numeric($payload['departmentId']) ? $payload['departmentId'] : null,
                is_numeric($payload['locationId']) ? $payload['locationId'] : null,
                is_numeric($payload['age1']) ? $payload['age1'] : null,
                is_numeric($payload['age2']) ? $payload['age2'] : null,
            ];

            $spExercisesData = DB::select('CALL sp_dashboard_physical_exercises_range(?, ?, ?, ?, ?, ?, ?, ?, ?)', $procedureData);

            if (!empty($spExercisesData)) {
                $exercisesData         = collect($spExercisesData)->pluck('percent', 'exerciseRange')->toArray();
                $data['exercisesData'] = Arr::flatten(array_map(function ($value) use ($exercisesData) {
                    return array_key_exists($value, $exercisesData) ? $exercisesData[$value] : 0;
                }, $exerciseLabels));
            }

            $fromDate = !empty($payload['options']) && isset($payload['options']['fromDateStepRanges']) ? $payload['options']['fromDateStepRanges'] : Carbon::parse(now()->toDateTimeString())->setTimeZone($timezone)->subDays(7)->format('Y-m-d 00:00:00');
            $toDate   = !empty($payload['options']) && isset($payload['options']['endDateStepRanges']) ? $payload['options']['endDateStepRanges'] : Carbon::parse(now()->toDateTimeString())->setTimeZone($timezone)->format('Y-m-d H:i:s');

            $fromDate   = strtotime($fromDate) !== false ? $fromDate : date("Y-m-d 00:00:00");
            $toDate     = strtotime($toDate) !== false ? $toDate : date("Y-m-d 23:59:59");
            $days       = Carbon::parse($toDate)->diffInDays(Carbon::parse($fromDate)) + 1;

            $procedureData[1] = $fromDate;
            $procedureData[2] = $toDate;
            $procedureData[3] = is_numeric($days) ? $days : 7;

            $spStepsData = DB::select('CALL sp_dashboard_physical_steps_range(?, ?, ? , ?, ?, ?, ?, ?, ?)', $procedureData);

            if (!empty($spStepsData)) {
                $stepsData         = collect($spStepsData)->pluck('percent', 'stepRange')->toArray();
                $data['stepsData'] = Arr::flatten(array_map(function ($value) use ($stepsData) {
                    return array_key_exists($value, $stepsData) ? $stepsData[$value] : 0;
                }, $stepLabels));
            }
            // most popular exercise by tracker
            $fromDate = !empty($payload['options']) && isset($payload['options']['fromDatePopularExerciseTrackerRanges']) ? $payload['options']['fromDatePopularExerciseTrackerRanges'] : Carbon::parse(now()->toDateTimeString())->setTimeZone($timezone)->subDays(7)->format('Y-m-d 00:00:00');
            $toDate   = !empty($payload['options']) && isset($payload['options']['endDatePopularExerciseTrackerRanges']) ? $payload['options']['endDatePopularExerciseTrackerRanges'] : Carbon::parse(now()->toDateTimeString())->setTimeZone($timezone)->format('Y-m-d H:i:s');

            $fromDate   = strtotime($fromDate) !== false ? $fromDate : date("Y-m-d 00:00:00");
            $toDate     = strtotime($toDate) !== false ? $toDate : date("Y-m-d 23:59:59");
            $days       = Carbon::parse($toDate)->diffInDays(Carbon::parse($fromDate)) + 1;

            $procedureData[1] = $fromDate;
            $procedureData[2] = $toDate;
            $procedureData[3] = is_numeric($days) ? $days : 7;
            $procedureData[9] = 0;

            $spPopulerExerciseTrackerData = DB::select('CALL sp_dashboard_physical_most_popular_exercises(?, ?, ?, ? , ?, ?, ?, ?, ?, ?)', $procedureData);

            if (!empty($spPopulerExerciseTrackerData)) {
                $data['popularTrackerExercise'] = [
                    'title'   => array_column($spPopulerExerciseTrackerData, 'title'),
                    'percent' => array_column($spPopulerExerciseTrackerData, 'percent'),
                ];
            }

            // most popular exercise by manual
            $fromDate = !empty($payload['options']) && isset($payload['options']['fromDatePopularExerciseManualRanges']) ? $payload['options']['fromDatePopularExerciseManualRanges'] : Carbon::parse(now()->toDateTimeString())->setTimeZone($timezone)->subDays(7)->format('Y-m-d 00:00:00');
            $toDate   = !empty($payload['options']) && isset($payload['options']['endDatePopularExerciseManualRanges']) ? $payload['options']['endDatePopularExerciseManualRanges'] : Carbon::parse(now()->toDateTimeString())->setTimeZone($timezone)->format('Y-m-d H:i:s');

            $fromDate   = strtotime($fromDate) !== false ? $fromDate : date("Y-m-d 00:00:00");
            $toDate     = strtotime($toDate) !== false ? $toDate : date("Y-m-d 23:59:59");
            $days       = Carbon::parse($toDate)->diffInDays(Carbon::parse($fromDate)) + 1;

            $procedureData[1] = $fromDate;
            $procedureData[2] = $toDate;
            $procedureData[3] = is_numeric($days) ? $days : 7;
            $procedureData[9] = 1;
            
            $spPopulerExerciseManualData = DB::select('CALL sp_dashboard_physical_most_popular_exercises(?, ?, ?, ? , ?, ?, ?, ?, ?, ?)', $procedureData);

            if (!empty($spPopulerExerciseManualData)) {
                $data['popularManualExercise'] = [
                    'title'   => array_column($spPopulerExerciseManualData, 'title'),
                    'percent' => array_column($spPopulerExerciseManualData, 'percent'),
                ];
            }

            if (isset($payload['options']) && isset($payload['options']['change'])) {
                $data['change'] = $payload['options']['change'];
            }
            $payload['category'] = 2;
            $categoryScore       = $this->getHealthScoreCategoryCommonData($payload);
            return array_merge($data, $categoryScore);
        } catch (\Illuminate\Database\QueryException $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong_try_again'),
                'status' => 0,
            ];
            return response()->json($messageData);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong_try_again'),
                'status' => 0,
            ];
            return response()->json($messageData);
        }
    }

    /**
     * Get Physical Tab Tier 3 Data - Popular exercises
     *
     * @param Request $request
     * @return Array
     */
    public function getPhysicalTabTier3Data($payload)
    {
        try {
            $timezone = Auth::user()->timezone ?? null;
            $timezone = !empty($timezone) ? $timezone : config('app.timezone');
            $fromDate = !empty($payload['options']) && isset($payload['options']['fromDatePopularExercises']) ? Carbon::parse(now()->toDateTimeString())->setTimeZone($timezone)->subDays($payload['options']['fromDatePopularExercises'])->format('Y-m-d 00:00:00') : Carbon::parse(now()->toDateTimeString())->setTimeZone($timezone)->subDays(7)->format('Y-m-d 00:00:00');
            $toDate   = !empty($payload['options']) && isset($payload['options']['toDatePopularExercises']) ? $payload['options']['toDatePopularExercises'] : Carbon::parse(now()->toDateTimeString())->setTimeZone($timezone)->subDay()->format('Y-m-d 23:59:59');

            $fromDate = strtotime($fromDate) !== false ? $fromDate : date("Y-m-d 00:00:00");
            $toDate   = strtotime($toDate) !== false ? $toDate : date("Y-m-d 23:59:59");
            $days     = Carbon::parse($toDate)->diffInDays(Carbon::parse($fromDate)) + 1;
            $regex    = "/^\d+(?:,\d+)*$/";

            $data          = [];
            $procedureData = [
                $timezone,
                $fromDate,
                (!is_null($payload['companyId']) && preg_match($regex, $payload['companyId'])) ? $payload['companyId'] : null,
                is_numeric($payload['departmentId']) ? $payload['departmentId'] : null,
                is_numeric($payload['locationId']) ? $payload['locationId'] : null,
                is_numeric($payload['age1']) ? $payload['age1'] : null,
                is_numeric($payload['age2']) ? $payload['age2'] : null,
            ];

            $spPopularExercisesData = DB::select('CALL sp_dashboard_physical_popular_exercises(?, ?, ?, ?, ?, ?, ?)', $procedureData);

            if (!empty($spPopularExercisesData)) {
                $data = [
                    'exercise' => array_column($spPopularExercisesData, 'title'),
                    'percent'  => array_column($spPopularExercisesData, 'percent'),
                ];
            }

            if (isset($payload['options']) && isset($payload['options']['change']) && $payload['options']['change'] == 'daterangeStepsPeriod') {
                $fromDate = !empty($payload['options']) && isset($payload['options']['fromDateStepsPeriod']) ? $payload['options']['fromDateStepsPeriod'] : Carbon::parse(now()->toDateTimeString())->setTimeZone($timezone)->subDays(7)->format('Y-m-d 00:00:00');
                $toDate   = !empty($payload['options']) && isset($payload['options']['endDateStepsPeriod']) ? $payload['options']['endDateStepsPeriod'] : Carbon::parse(now()->toDateTimeString())->setTimeZone($timezone)->format('Y-m-d H:i:s');
            }

            $procedureData = [
                $timezone,
                strtotime($fromDate) !== false ? $fromDate : date("Y-m-d 00:00:00"),
                strtotime($toDate) !== false ? $toDate : date("Y-m-d 23:59:59"),
                is_numeric($days) ? $days : 7,
                (!is_null($payload['companyId']) && preg_match($regex, $payload['companyId'])) ? $payload['companyId'] : null,
                is_numeric($payload['departmentId']) ? $payload['departmentId'] : null,
                is_numeric($payload['locationId']) ? $payload['locationId'] : null,
                is_numeric($payload['age1']) ? $payload['age1'] : null,
                is_numeric($payload['age2']) ? $payload['age2'] : null,
            ];

            $spStepsData = DB::select('CALL sp_dashboard_app_usage_steps_period(?, ?, ?, ?, ?, ?, ?, ?, ?)', $procedureData);

            $fromDate = !empty($payload['options']) && isset($payload['options']['fromDateCaloriesPeriod']) ? $payload['options']['fromDateCaloriesPeriod'] : Carbon::parse(now()->toDateTimeString())->setTimeZone($timezone)->subDays(30)->format('Y-m-d 00:00:00');
            $toDate   = !empty($payload['options']) && isset($payload['options']['endDateCaloriesPeriod']) ? $payload['options']['endDateCaloriesPeriod'] : Carbon::parse(now()->toDateTimeString())->setTimeZone($timezone)->subDay()->format('Y-m-d 23:59:59');
            $fromDate = strtotime($fromDate) !== false ? $fromDate : date("Y-m-d 00:00:00");
            $toDate   = strtotime($toDate) !== false ? $toDate : date("Y-m-d 23:59:59");
            $days     = Carbon::parse($toDate)->diffInDays(Carbon::parse($fromDate)) + 1;

            $procedureData[1] = $fromDate;
            $procedureData[2] = $toDate;
            $procedureData[3] = is_numeric($days) ? $days : 7;

            $spCaloriesData = DB::select('CALL sp_dashboard_app_usage_calories_period(?, ?, ?, ?, ?, ?, ?, ?, ?)', $procedureData);

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
                (!is_null($payload['companyId']) && preg_match($regex, $payload['companyId'])) ? $payload['companyId'] : null,
                is_numeric($payload['departmentId']) ? $payload['departmentId'] : null,
                is_numeric($payload['locationId']) ? $payload['locationId'] : null,
                is_numeric($payload['age1']) ? $payload['age1'] : null,
                is_numeric($payload['age2']) ? $payload['age2'] : null,
            ];

            $spSyncDetailsData = DB::select('CALL sp_dashboard_app_usage_sync_details(?, ?, ?, ?, ?, ?)', $procedureData);

            if (!empty($spSyncDetailsData)) {
                $data['syncDetails']                   = Arr::collapse(json_decode(json_encode($spSyncDetailsData), true));
                $data['syncDetails']['notSyncPercent'] = (float) number_format(100 - ($data['syncDetails']['syncPercent'] ?? 0), 1);
            }

            if (isset($payload['options']) && isset($payload['options']['change'])) {
                $data['change'] = $payload['options']['change'];
            }

            return $data;
        } catch (\Illuminate\Database\QueryException $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong_try_again'),
                'status' => 0,
            ];
            return response()->json($messageData);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong_try_again'),
                'status' => 0,
            ];
            return response()->json($messageData);
        }
    }

    /**
     * Get Physical Tab Tier 4 Data - Recipe views, BMI
     *
     * @param Request $request
     * @return Array
     */
    public function getPhysicalTabTier4Data($payload)
    {
        try {
            $role             = getUserRole();
            $user             = auth()->user();
            $company          = $user->company->first();
            $timezone         = $user->timezone ?? null;
            $timezone         = !empty($timezone) ? $timezone : config('app.timezone');
            $fromDate         = !empty($payload['options']) && isset($payload['options']['fromDateMoodsAnalysis']) && is_numeric($payload['options']['fromDateMoodsAnalysis']) ? Carbon::parse(now()->toDateTimeString())->setTimeZone($timezone)->subDays($payload['options']['fromDateMoodsAnalysis'])->format('Y-m-d 00:00:00') : Carbon::parse(now()->toDateTimeString())->setTimeZone($timezone)->subDays(7)->format('Y-m-d 00:00:00');
            $toDate           = !empty($payload['options']) && isset($payload['options']['endDateCaloriesPeriod']) && is_numeric($payload['options']['endDateCaloriesPeriod']) ? $payload['options']['endDateCaloriesPeriod'] : Carbon::parse(now()->toDateTimeString())->setTimeZone($timezone)->subDay()->format('Y-m-d 23:59:59');
            $regex            = "/^\d+(?:,\d+)*$/";
            $checkStringRegex = "/^[a-zA-Z\d]+$/";

            $gender                    = !empty($payload['options']) && isset($payload['options']['gender']) ? $payload['options']['gender'] : null;
            $data                      = [];
            $data['role']              = $role->group;
            $data['company_parent_id'] = isset($company) ? $company->parent_id : null;
            $procedureData             = [
                $timezone,
                (!is_null($payload['companyId']) && preg_match($regex, $payload['companyId'])) ? $payload['companyId'] : null,
                is_numeric($payload['departmentId']) ? $payload['departmentId'] : null,
                is_numeric($payload['locationId']) ? $payload['locationId'] : null,
                is_numeric($payload['age1']) ? $payload['age1'] : null,
                is_numeric($payload['age2']) ? $payload['age2'] : null,
            ];

            $spPopularRecipesData = DB::select('CALL sp_dashboard_physical_popular_recipes(?, ?, ?, ?, ?, ?)', $procedureData);

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
                (!is_null($payload['companyId']) && preg_match($regex, $payload['companyId'])) ? $payload['companyId'] : null,
                is_numeric($payload['departmentId']) ? $payload['departmentId'] : null,
                is_numeric($payload['locationId']) ? $payload['locationId'] : null,
                is_numeric($payload['age1']) ? $payload['age1'] : null,
                is_numeric($payload['age2']) ? $payload['age2'] : null,
                (!is_null($gender) && preg_match($checkStringRegex, $gender)) ? $gender : null,
            ];

            $spBmiData = DB::select('CALL sp_dashboard_physical_bmi(?, ?, ?, ?, ?, ?)', $procedureData);

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
                strtotime($fromDate) !== false ? $fromDate : date("Y-m-d 00:00:00"),
                (!is_null($payload['companyId']) && preg_match($regex, $payload['companyId'])) ? $payload['companyId'] : null,
                is_numeric($payload['departmentId']) ? $payload['departmentId'] : null,
                is_numeric($payload['locationId']) ? $payload['locationId'] : null,
                is_numeric($payload['age1']) ? $payload['age1'] : null,
                is_numeric($payload['age2']) ? $payload['age2'] : null,
            ];

            $spMoodsAnalysisData = DB::select('CALL sp_dashboard_psychological_moods_analysis(?, ?, ?, ?, ?, ?, ?)', $procedureData);

            if (!empty($spMoodsAnalysisData)) {
                $data['moodAnalysis'] = [
                    'title'   => array_column($spMoodsAnalysisData, 'title'),
                    'percent' => array_column($spMoodsAnalysisData, 'percent'),
                ];
            }

            $fromDate = !empty($payload['options']) && isset($payload['options']['fromDateSuperstars']) ? $payload['options']['fromDateSuperstars'] : Carbon::parse(now()->toDateTimeString())->setTimeZone($timezone)->subDays(7)->format('Y-m-d 00:00:00');
            $toDate   = !empty($payload['options']) && isset($payload['options']['endDateSuperstars']) ? $payload['options']['endDateSuperstars'] : Carbon::parse(now()->toDateTimeString())->setTimeZone($timezone)->format('Y-m-d H:i:s');

            $procedureData = [
                $timezone,
                strtotime($fromDate) !== false ? $fromDate : date("Y-m-d 00:00:00"),
                strtotime($toDate) !== false ? $toDate : date("Y-m-d 23:59:59"),
                (!is_null($payload['companyId']) && preg_match($regex, $payload['companyId'])) ? $payload['companyId'] : null,
                is_numeric($payload['departmentId']) ? $payload['departmentId'] : null,
                is_numeric($payload['locationId']) ? $payload['locationId'] : null,
                is_numeric($payload['age1']) ? $payload['age1'] : null,
                is_numeric($payload['age2']) ? $payload['age2'] : null,
            ];

            $spActiveTeamData       = DB::select('CALL sp_dashboard_app_usage_active_team(?, ?, ?, ?, ?, ?, ?, ?)', $procedureData);
            $spActiveIndividualData = DB::select('CALL sp_dashboard_app_usage_active_individual(?, ?, ?, ?, ?, ?, ?, ?)', $procedureData);
            $spBadgesEarnedData     = DB::select('CALL sp_dashboard_app_usage_badges_earned(?, ?, ?, ?, ?, ?, ?, ?)', $procedureData);

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
        } catch (\Illuminate\Database\QueryException $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong_try_again'),
                'status' => 0,
            ];
            return response()->json($messageData);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong_try_again'),
                'status' => 0,
            ];
            return response()->json($messageData);
        }
    }

    /**
     * Get Psychological Tab Tier 1 Data - Psychological category and sub-categories data of health score
     *
     * @param Request $request
     * @return Array
     */
    public function getPsychologicalTabTier1Data($payload)
    {
        try {
            $payload['category'] = 2;

            return $this->getHealthScoreCategoryCommonData($payload);
        } catch (\Illuminate\Database\QueryException $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong_try_again'),
                'status' => 0,
            ];
            return response()->json($messageData);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong_try_again'),
                'status' => 0,
            ];
            return response()->json($messageData);
        }
    }

    /**
     * Get Psychological Tab Tier 2 Data - Meditation hours chart
     *
     * @param Request $request
     * @return Array
     */
    public function getPsychologicalTabTier2Data($payload)
    {
        try {
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
            $regex             = "/^\d+(?:,\d+)*$/";
            $procedureData     = [
                'app',
                null,
                null,
                (!is_null($payload['companyId']) && preg_match($regex, $payload['companyId'])) ? $payload['companyId'] : null,
                is_numeric($payload['departmentId']) ? $payload['departmentId'] : null,
                is_numeric($payload['age1']) ? $payload['age1'] : null,
                is_numeric($payload['age2']) ? $payload['age2'] : null,
                null,
            ];

            $totaAppUsers = spGetUser($procedureData);

            $procedureData = [
                $timezone,
                $type,
                strtotime($fromDate) !== false ? $fromDate : date("Y-m-d 00:00:00"),
                (!is_null($payload['companyId']) && preg_match($regex, $payload['companyId'])) ? $payload['companyId'] : null,
                is_numeric($payload['departmentId']) ? $payload['departmentId'] : null,
                is_numeric($payload['age1']) ? $payload['age1'] : null,
                is_numeric($payload['age2']) ? $payload['age2'] : null,
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
        } catch (\Illuminate\Database\QueryException $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong_try_again'),
                'status' => 0,
            ];
            return response()->json($messageData);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong_try_again'),
                'status' => 0,
            ];
            return response()->json($messageData);
        }
    }

    /**
     * Get Psychological Tab Tier 3 Data - Popular meditation categories and Top 10 meditations chart
     *
     * @param Request $request
     * @return Array
     */
    public function getPsychologicalTabTier3Data($payload)
    {
        try {
            $timezone      = Auth::user()->timezone ?? null;
            $timezone      = !empty($timezone) ? $timezone : config('app.timezone');
            $fromDate      = !empty($payload['options']) && isset($payload['options']['fromDateTopMeditationTracks']) ? $payload['options']['fromDateTopMeditationTracks'] : Carbon::parse(now()->toDateTimeString())->setTimeZone($timezone)->subDays(30)->format('Y-m-d 00:00:00');
            $toDate        = !empty($payload['options']) && isset($payload['options']['endDateTopMeditationTracks']) ? $payload['options']['endDateTopMeditationTracks'] : Carbon::parse(now()->toDateTimeString())->setTimeZone($timezone)->format('Y-m-d H:i:s');
            $data          = [];
            $regex         = "/^\d+(?:,\d+)*$/";
            $procedureData = [
                (!is_null($payload['companyId']) && preg_match($regex, $payload['companyId'])) ? $payload['companyId'] : null,
                is_numeric($payload['departmentId']) ? $payload['departmentId'] : null,
                is_numeric($payload['locationId']) ? $payload['locationId'] : null,
                is_numeric($payload['age1']) ? $payload['age1'] : null,
                is_numeric($payload['age2']) ? $payload['age2'] : null,
            ];

            $spPopularMeditationCategoriesData = DB::select('CALL sp_dashboard_psychological_popular_meditations(?, ?, ?, ?, ?)', $procedureData);

            if (!empty($spPopularMeditationCategoriesData)) {
                $data['popularMeditationCategoriesData'] = [
                    'meditationCategory' => array_column($spPopularMeditationCategoriesData, 'meditationCategory'),
                    'totalViews'         => array_column($spPopularMeditationCategoriesData, 'totalViews'),
                ];
            }

            $procedureData = [
                $timezone,
                strtotime($fromDate) !== false ? $fromDate : date("Y-m-d 00:00:00"),
                strtotime($toDate) !== false ? $toDate : date("Y-m-d 23:59:59"),
                (!is_null($payload['companyId']) && preg_match($regex, $payload['companyId'])) ? $payload['companyId'] : null,
                is_numeric($payload['departmentId']) ? $payload['departmentId'] : null,
                is_numeric($payload['locationId']) ? $payload['locationId'] : null,
                is_numeric($payload['age1']) ? $payload['age1'] : null,
                is_numeric($payload['age2']) ? $payload['age2'] : null,
            ];

            $spTopMeditationTracksData = DB::select('CALL sp_dashboard_psychological_top_meditations(?, ?, ?, ?, ?, ?, ?, ?)', $procedureData);

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
        } catch (\Illuminate\Database\QueryException $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong_try_again'),
                'status' => 0,
            ];
            return response()->json($messageData);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong_try_again'),
                'status' => 0,
            ];
            return response()->json($messageData);
        }
    }

    /**
     * Get Psychological Tab Tier 4 Data - Moods analysis
     *
     * @param Request $request
     * @return Array
     */
    public function getPsychologicalTabTier4Data($payload)
    {
        try {
            $timezone      = Auth::user()->timezone ?? null;
            $timezone      = !empty($timezone) ? $timezone : config('app.timezone');
            $fromDate      = !empty($payload['options']) && isset($payload['options']['fromDateMoodsAnalysis']) ? Carbon::parse(now()->toDateTimeString())->setTimeZone($timezone)->subDays($payload['options']['fromDateMoodsAnalysis'])->format('Y-m-d 00:00:00') : Carbon::parse(now()->toDateTimeString())->setTimeZone($timezone)->subDays(7)->format('Y-m-d 00:00:00');
            $data          = [];
            $regex         = "/^\d+(?:,\d+)*$/";
            $procedureData = [
                $timezone,
                strtotime($fromDate) !== false ? $fromDate : date("Y-m-d 00:00:00"),
                (!is_null($payload['companyId']) && preg_match($regex, $payload['companyId'])) ? $payload['companyId'] : null,
                is_numeric($payload['departmentId']) ? $payload['departmentId'] : null,
                is_numeric($payload['locationId']) ? $payload['locationId'] : null,
                is_numeric($payload['age1']) ? $payload['age1'] : null,
                is_numeric($payload['age2']) ? $payload['age2'] : null,
            ];

            $spMoodsAnalysisData = DB::select('CALL sp_dashboard_psychological_moods_analysis(?, ?, ?, ?, ?, ?, ?)', $procedureData);

            if (!empty($spMoodsAnalysisData)) {
                $data['moodAnalysis'] = [
                    'title'   => array_column($spMoodsAnalysisData, 'title'),
                    'percent' => array_column($spMoodsAnalysisData, 'percent'),
                ];
            }

            return $data;
        } catch (\Illuminate\Database\QueryException $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong_try_again'),
                'status' => 0,
            ];
            return response()->json($messageData);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong_try_again'),
                'status' => 0,
            ];
            return response()->json($messageData);
        }
    }

    /**
     * Get Health Score Categories common data for physical and psychological tab
     *
     * @param $payload
     * @return Array
     */
    public function getHealthScoreCategoryCommonData($payload)
    {
        try {
            $role       = getUserRole();
            $timezone   = Auth::user()->timezone ?? null;
            $timezone   = !empty($timezone) ? $timezone : config('app.timezone');
            $last30Days = Carbon::parse(now()->toDateTimeString())->setTimeZone($timezone)->subDays(30)->format('Y-m-d 00:00:00');
            $regex      = "/^\d+(?:,\d+)*$/";
            $data       = [];

            if ($payload['category'] == 1) {
                $fromDate = !empty($payload['options']) && isset($payload['options']['fromDateHsPhysical']) ? $payload['options']['fromDateHsPhysical'] : Carbon::parse(now()->toDateTimeString())->setTimeZone($timezone)->subDays(30)->format('Y-m-d 00:00:00');
                $toDate   = !empty($payload['options']) && isset($payload['options']['endDateHsPhysical']) ? $payload['options']['endDateHsPhysical'] : Carbon::parse(now()->toDateTimeString())->setTimeZone($timezone)->format('Y-m-d H:i:s');
            } else {
                $fromDate = !empty($payload['options']) && isset($payload['options']['fromDateHsPsychological']) ? $payload['options']['fromDateHsPsychological'] : Carbon::parse(now()->toDateTimeString())->setTimeZone($timezone)->subDays(30)->format('Y-m-d 00:00:00');
                $toDate   = !empty($payload['options']) && isset($payload['options']['endDateHsPsychological']) ? $payload['options']['endDateHsPsychological'] : Carbon::parse(now()->toDateTimeString())->setTimeZone($timezone)->format('Y-m-d H:i:s');
            }
            $data = [
                'hsCategoryData'    => [],
                'hsSubCategoryData' => [],
                'attemptedBy'       => [
                    'attemptedPercent' => 0,
                ],
            ];

            $procedureData = [
                $timezone,
                strtotime($last30Days) !== false ? $last30Days : null,
                (!is_null($payload['companyId']) && preg_match($regex, $payload['companyId'])) ? $payload['companyId'] : null,
                is_numeric($payload['locationId']) ? $payload['locationId'] : null,
                is_numeric($payload['departmentId']) ? $payload['departmentId'] : null,
            ];

            $spTeamsData       = DB::select('CALL sp_dashboard_app_usage_teams(?, ?, ?, ?, ?)', $procedureData);
            $data['teamsData'] = !empty($spTeamsData) ? Arr::collapse(json_decode(json_encode($spTeamsData), true)) : [];

            $procedureData = [
                $timezone,
                (!is_null($payload['companyId']) && preg_match($regex, $payload['companyId'])) ? $payload['companyId'] : null,
                $role->group,
            ];

            $spChallengesData       = DB::select('CALL sp_dashboard_app_usage_challenges(?, ?, ?)', $procedureData);
            $data['challengesData'] = !empty($spChallengesData) ? Arr::collapse(json_decode(json_encode($spChallengesData), true)) : [];

            $procedureData = [
                $timezone,
                strtotime($fromDate) !== false ? $fromDate : date("Y-m-d 00:00:00"),
                strtotime($toDate) !== false ? $toDate : date("Y-m-d 23:59:59"),
                (!is_null($payload['companyId']) && preg_match($regex, $payload['companyId'])) ? $payload['companyId'] : null,
                is_numeric($payload['departmentId']) ? $payload['departmentId'] : null,
                is_numeric($payload['locationId']) ? $payload['locationId'] : null,
                is_numeric($payload['age1']) ? $payload['age1'] : null,
                is_numeric($payload['age2']) ? $payload['age2'] : null,
                is_numeric($payload['category']) ? $payload['category'] : null,
            ];

            $spHsCategoryData    = DB::select('CALL sp_dashboard_hs_category_wise(?, ?, ?, ?, ?, ?, ?, ?, ?)', $procedureData);
            $spHsSubCategoryData = DB::select('CALL sp_dashboard_hs_sub_category_wise(?, ?, ?, ?, ?, ?, ?, ?, ?)', $procedureData);
            $spHsAttemptedByData = DB::select('CALL sp_dashboard_hs_attempted_by(?, ?, ?, ?, ?, ?, ?, ?, ?)', $procedureData);

            $mapLabels = [
                'Low',
                'Moderate',
                'High',
            ];

            $hsCategoryData = [];
            if (!empty($spHsCategoryData)) {
                $hsCategoryData = collect($spHsCategoryData)->mapWithKeys(function ($item) {
                    return [$item->physicalScore => $item->percent];
                })->toArray();
            }

            $data['hsCategoryData'] = Arr::flatten(array_map(function ($value) use ($hsCategoryData) {
                return array_key_exists($value, $hsCategoryData) ? (float) $hsCategoryData[$value] : 0;
            }, $mapLabels));

            if (!empty($spHsSubCategoryData)) {
                $data['hsSubCategoryData'] = collect($spHsSubCategoryData)->map(function ($item) {
                    $item->percent = $item->percent < 0 ? 0 : $item->percent;
                    return [
                        'sub_category' => $item->sub_category,
                        'percent'      => $item->percent,
                        'color'        => getScoreColor($item->percent),
                    ];
                })->toArray();
            } else {
                $physicalSubCategories = HsSubCategories::where('category_id', $payload['category'])->where('status', 1)->get();

                $data['hsSubCategoryData'] = collect($physicalSubCategories)->map(function ($item) {
                    return [
                        'sub_category' => $item->display_name,
                        'percent'      => 0,
                        'color'        => getScoreColor($item->percent),
                    ];
                })->toArray();
            }

            if (!empty($spHsAttemptedByData)) {
                $data['attemptedBy'] = !empty($spHsAttemptedByData) ? Arr::collapse(json_decode(json_encode($spHsAttemptedByData), true)) : [];
            }
            return $data;
        } catch (\Illuminate\Database\QueryException $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong_try_again'),
                'status' => 0,
            ];
            return response()->json($messageData);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong_try_again'),
                'status' => 0,
            ];
            return response()->json($messageData);
        }
    }

    /**
     * Get Duration empty data for meditiation hours chart
     *
     * @param $payload
     * @return Array
     */
    public function getDurationEmptyData($type, $data)
    {
        try {
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
        } catch (\Illuminate\Database\QueryException $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong_try_again'),
                'status' => 0,
            ];
            return response()->json($messageData);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong_try_again'),
                'status' => 0,
            ];
            return response()->json($messageData);
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
        try {
            $user                      = auth()->user();
            $timezone                  = !empty($user->timezone) ? $user->timezone : config('app.timezone');
            $fromDate                  = !empty($payload['options']) && isset($payload['options']['fromDateCompanyScore']) ? $payload['options']['fromDateCompanyScore'] : Carbon::parse(now()->toDateTimeString())->setTimeZone($timezone)->subMonths(5)->format('Y-m-d 00:00:00');
            $toDate                    = !empty($payload['options']) && isset($payload['options']['endDateCompanyScore']) ? $payload['options']['endDateCompanyScore'] : Carbon::parse(now()->toDateTimeString())->setTimeZone($timezone)->format('Y-m-d H:i:s');
            $companyScoreLineChartData = ['data' => [], 'labels' => [], 'colors' => []];

            $fromDate   = strtotime($fromDate) !== false ? $fromDate : date("Y-m-d 00:00:00");
            $toDate     = strtotime($toDate) !== false ? $toDate : date("Y-m-d 23:59:59");
            $emptyData  = getMonthsBetweenDatebyKeys($timezone, $fromDate, $toDate);
            $regex      = "/^\d+(?:,\d+)*$/";
            
            // Company score gauge chart calculation
            $procedureData = [
                $timezone,
                $fromDate,
                $toDate,
                config('zevolifesettings.zc_survey_max_score_value', 7),
                (!is_null($payload['companyId']) && preg_match($regex, $payload['companyId'])) ? $payload['companyId'] : null,
                is_numeric($payload['departmentId']) ? $payload['departmentId'] : null,
                is_numeric($payload['locationId']) ? $payload['locationId'] : null,
                is_numeric($payload['age1']) ? $payload['age1'] : null,
                is_numeric($payload['age2']) ? $payload['age2'] : null,
            ];
            $spCompanyScoreGaugeChartData = DB::select('CALL sp_dashboard_audit_company_score(?, ?, ?, ?, ?, ?, ?, ?, ?)', $procedureData);
            $spCompanyScoreGaugeChartData = (!empty($spCompanyScoreGaugeChartData) ? $spCompanyScoreGaugeChartData[0] : (object) ['percentage' => 0]);

            // Company score line chart calculation
            $procedureData = [
                $timezone,
                $fromDate,
                $toDate,
                config('zevolifesettings.zc_survey_max_score_value', 7),
                (!is_null($payload['companyId']) && preg_match($regex, $payload['companyId'])) ? $payload['companyId'] : null,
                is_numeric($payload['departmentId']) ? $payload['departmentId'] : null,
                is_numeric($payload['locationId']) ? $payload['locationId'] : null,
                null,
                null,
                is_numeric($payload['age1']) ? $payload['age1'] : null,
                is_numeric($payload['age2']) ? $payload['age2'] : null,
            ];
            $spCompanyScoreLineChartData = DB::select('CALL sp_dashboard_audit_company_score_line_graph(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', $procedureData);
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
                (!is_null($payload['companyId']) && preg_match($regex, $payload['companyId'])) ? $payload['companyId'] : null,
                is_numeric($payload['departmentId']) ? $payload['departmentId'] : null,
                is_numeric($payload['locationId']) ? $payload['locationId'] : null,
                null,
                null,
                is_numeric($payload['age1']) ? $payload['age1'] : null,
                is_numeric($payload['age2']) ? $payload['age2'] : null,
            ];
            $chartData = DB::select('CALL sp_dashboard_audit_company_score_baseline(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?);', $procedureData);
            if (!empty($chartData) && isset($chartData[0])) {
                $companyScoreLineChartData['baseline'] = (($chartData[0]->baseLine > 0) ? $chartData[0]->baseLine : 0);
            }

            return [
                'companyScoreGaugeChart' => [
                    'score'  => $spCompanyScoreGaugeChartData->percentage,
                    'data'   => [$spCompanyScoreGaugeChartData->percentage, (100 - $spCompanyScoreGaugeChartData->percentage)],
                    'colors' => [getScoreColor($spCompanyScoreGaugeChartData->percentage), (($spCompanyScoreGaugeChartData->percentage > 0) ? "#EBECF0" : getScoreColor(0))],
                    'labels' => [trans('dashboard.audit.headings.company_score'), ""],
                ],
                'companyScoreLineChart'  => $companyScoreLineChartData,
            ];
        } catch (\Illuminate\Database\QueryException $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong_try_again'),
                'status' => 0,
            ];
            return response()->json($messageData);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong_try_again'),
                'status' => 0,
            ];
            return response()->json($messageData);
        }
    }

    /**
     * Get Audit Tab Tier 2 Data - category wise tabs
     *
     * @param Request $request
     * @return Array
     */
    public function getAuditTabTier2Data($payload)
    {
        try {
            $user                 = auth()->user();
            $timezone             = !empty($user->timezone) ? $user->timezone : config('app.timezone');
            $categoryWiseTabsData = [
                'tabs' => [],
            ];
            $regex = "/^\d+(?:,\d+)*$/";

            // category wise tabs
            $procedureData = [
                config('zevolifesettings.zc_survey_max_score_value', 7),
                (!is_null($payload['companyId']) && preg_match($regex, $payload['companyId'])) ? $payload['companyId'] : null,
                is_numeric($payload['departmentId']) ? $payload['departmentId'] : null,
                is_numeric($payload['locationId']) ? $payload['locationId'] : null,
                null,
                $timezone,
                null,
                null,
                is_numeric($payload['age1']) ? $payload['age1'] : null,
                is_numeric($payload['age2']) ? $payload['age2'] : null,
            ];
            $spCategoryWiseCompanyScoreChartData = DB::select('call sp_dashboard_audit_category_wise_tabs(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', $procedureData);
            if (!empty($spCategoryWiseCompanyScoreChartData)) {
                foreach ($spCategoryWiseCompanyScoreChartData as $value) {
                    $subCategory                    = SurveyCategory::find($value->category_id, ['id']);
                    $value->image                   = $subCategory->logo;
                    $categoryWiseTabsData['tabs'][] = $value;
                }
            }
            return $categoryWiseTabsData;
        } catch (\Illuminate\Database\QueryException $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong_try_again'),
                'status' => 0,
            ];
            return response()->json($messageData);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong_try_again'),
                'status' => 0,
            ];
            return response()->json($messageData);
        }
    }

    /**
     * Get Audit Tab Tier 3 Data - category wise company score gauge and line charts
     *
     * @param Request $request
     * @return Array
     */
    public function getAuditTabTier3Data($payload)
    {
        try {
            $user        = auth()->user();
            $company     = $user->company->first();
            $role        = getUserRole($user);
            $timezone    = !empty($user->timezone) ? $user->timezone : config('app.timezone');
            $fromDate    = !empty($payload['options']) && isset($payload['options']['fromDateCategoryCompanyScore']) ? $payload['options']['fromDateCategoryCompanyScore'] : Carbon::parse(now()->toDateTimeString())->setTimeZone($timezone)->subMonths(5)->format('Y-m-d 00:00:00');
            $toDate      = !empty($payload['options']) && isset($payload['options']['endDateCategoryCompanyScore']) ? $payload['options']['endDateCategoryCompanyScore'] : Carbon::parse(now()->toDateTimeString())->setTimeZone($timezone)->format('Y-m-d H:i:s');
            $category_id = (int) (!empty($payload['options']['category_id']) ? $payload['options']['category_id'] : 0);
            $change      = (!empty($payload['options']['change']) ? $payload['options']['change'] : 'true');
            $fromDate    = strtotime($fromDate) !== false ? $fromDate : date("Y-m-d 00:00:00");
            $toDate      = strtotime($toDate) !== false ? $toDate : date("Y-m-d 23:59:59");
            $emptyData   = getMonthsBetweenDatebyKeys($timezone, $fromDate, $toDate);
            $regex       = "/^\d+(?:,\d+)*$/";

            $categoryWiseChartData = [
                'change'                         => $change,
                'companyCategoryScoreGaugeChart' => [
                    'colors' => ["#EBECF0"],
                    'data'   => [0],
                    'labels' => [trans('dashboard.audit.tooltips.category_score')],
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

            //Hide question report button if no categories match
            $companyIds                                        = [];
            $categoryWiseChartData['showQuestionReportButton'] = true;
            if ($role->group == 'reseller' && $company->parent_id == null) {
                $companyIds = Company::select('id')->where('id', $company->id)->orWhere('parent_id', $company->id)->get()->pluck('id')->toArray();
            }

            $compnayIdNew = (isset($company) && $company->id != null ? $company->id : null);

            $categories = ZcSurveyResponse::select('zc_survey_responses.category_id AS id', 'zc_categories.display_name AS category_name')
                ->join('users', function ($join) {
                    $join->on('users.id', '=', 'zc_survey_responses.user_id');
                })
                ->join('zc_categories', function ($join) {
                    $join->on('zc_categories.id', '=', 'zc_survey_responses.category_id');
                })
                ->where(function ($query) use ($timezone, $fromDate, $toDate, $compnayIdNew, $companyIds) {
                    $query
                        ->where('users.is_blocked', 0)
                        ->where('zc_categories.status', 1)
                        ->whereRaw("(CONVERT_TZ(zc_survey_responses.created_at, ? , ?) BETWEEN ? AND ?)", ['UTC', $timezone, $fromDate, $toDate]);

                    if (!empty($companyIds)) {
                        $query->whereIn('zc_survey_responses.company_id', $companyIds);
                    } elseif (!empty($compnayIdNew)) {
                        $query->where('zc_survey_responses.company_id', $compnayIdNew);
                    }
                })
                ->groupBy('zc_survey_responses.category_id')
                ->get()
                ->pluck('category_name', 'id')
                ->toArray();

            if (!empty($categories) && !array_key_exists($category_id, $categories)) {
                $categoryWiseChartData['showQuestionReportButton'] = false;
            }
            // specified category wise company score gauge charts
            $procedureData = [
                config('zevolifesettings.zc_survey_max_score_value', 7),
                (!is_null($payload['companyId']) && preg_match($regex, $payload['companyId'])) ? $payload['companyId'] : null,
                is_numeric($payload['departmentId']) ? $payload['departmentId'] : null,
                is_numeric($payload['locationId']) ? $payload['locationId'] : null,
                (!empty($category_id) ? $category_id : null),
                $timezone,
                strtotime($fromDate) !== false ? $fromDate : date("Y-m-d 00:00:00"),
                strtotime($toDate) !== false ? $toDate : date("Y-m-d 23:59:59"),
                is_numeric($payload['age1']) ? $payload['age1'] : null,
                is_numeric($payload['age2']) ? $payload['age2'] : null,
            ];
            $spCategoryWiseCompanyScoreChartData = DB::select('call sp_dashboard_audit_category_wise_graph(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', $procedureData);
            if (!empty($spCategoryWiseCompanyScoreChartData)) {
                $score                                                   = $spCategoryWiseCompanyScoreChartData[0];
                $categoryWiseChartData['score']                          = $score;
                $categoryWiseChartData['companyCategoryScoreGaugeChart'] = [
                    'data'   => [$score->category_percentage, (100 - $score->category_percentage)],
                    'colors' => [getScoreColor($score->category_percentage), "#EBECF0"],
                    'labels' => [trans('dashboard.audit.tooltips.category_score'), ""],
                ];
            }

            // specified category wise company score line charts
            $procedureData = [
                $timezone,
                strtotime($fromDate) !== false ? $fromDate : date("Y-m-d 00:00:00"),
                strtotime($toDate) !== false ? $toDate : date("Y-m-d 23:59:59"),
                config('zevolifesettings.zc_survey_max_score_value', 7),
                (!is_null($payload['companyId']) && preg_match($regex, $payload['companyId'])) ? $payload['companyId'] : null,
                is_numeric($payload['departmentId']) ? $payload['departmentId'] : null,
                is_numeric($payload['locationId']) ? $payload['locationId'] : null,
                (!empty($category_id) ? $category_id : null),
                null,
                is_numeric($payload['age1']) ? $payload['age1'] : null,
                is_numeric($payload['age2']) ? $payload['age2'] : null,
            ];
            $spCategoryWiseCompanyScoreLineChartData = DB::select('CALL sp_dashboard_audit_company_score_line_graph(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', $procedureData);

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
                strtotime($fromDate) !== false ? $fromDate : date("Y-m-d 00:00:00"),
                strtotime($toDate) !== false ? $toDate : date("Y-m-d 23:59:59"),
                config('zevolifesettings.zc_survey_max_score_value', 7),
                (!is_null($payload['companyId']) && preg_match($regex, $payload['companyId'])) ? $payload['companyId'] : null,
                is_numeric($payload['departmentId']) ? $payload['departmentId'] : null,
                is_numeric($payload['locationId']) ? $payload['locationId'] : null,
                (!empty($category_id) ? $category_id : null),
                null,
                is_numeric($payload['age1']) ? $payload['age1'] : null,
                is_numeric($payload['age2']) ? $payload['age2'] : null,
            ];
            $chartData = DB::select('CALL sp_dashboard_audit_company_score_baseline(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?);', $procedureData);
            if (!empty($chartData) && isset($chartData[0])) {
                $categoryWiseChartData['performance']['baseline'] = (($chartData[0]->baseLine > 0) ? $chartData[0]->baseLine : 0);
            }

            $procedureData = [
                config('zevolifesettings.zc_survey_max_score_value', 7),
                (!is_null($payload['companyId']) && preg_match($regex, $payload['companyId'])) ? $payload['companyId'] : null,
                is_numeric($payload['departmentId']) ? $payload['departmentId'] : null,
                is_numeric($payload['locationId']) ? $payload['locationId'] : null,
                (!empty($category_id) ? $category_id : null),
                null,
                $timezone,
                strtotime($fromDate) !== false ? $fromDate : date("Y-m-d 00:00:00"),
                strtotime($toDate) !== false ? $toDate : date("Y-m-d 23:59:59"),
                is_numeric($payload['age1']) ? $payload['age1'] : null,
                is_numeric($payload['age2']) ? $payload['age2'] : null,
            ];
            $spSubCategoryWiseCompanyScoreChartData = DB::select('call sp_dashboard_audit_sub_category_wise_graph(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', $procedureData);
            $categoryWiseChartData['subcategories'] = "";
            if (!empty($spSubCategoryWiseCompanyScoreChartData)) {
                foreach ($spSubCategoryWiseCompanyScoreChartData as $key => $category) {
                    $categoryWiseChartData['subcategories'] .= "<option value='{$category->sub_category_id}'>{$category->subcategory_name}</option>";
                }
            }

            return $categoryWiseChartData;
        } catch (\Illuminate\Database\QueryException $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong_try_again'),
                'status' => 0,
            ];
            return response()->json($messageData);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong_try_again'),
                'status' => 0,
            ];
            return response()->json($messageData);
        }
    }

    /**
     * Get Audit Tab Tier 4 Data - subcategory wise company score gauge charts
     *
     * @param Request $request
     * @return Array
     */
    public function getAuditTabTier4Data($payload)
    {
        try {
            $user                       = auth()->user();
            $timezone                   = !empty($user->timezone) ? $user->timezone : config('app.timezone');
            $fromDate                   = !empty($payload['options']) && isset($payload['options']['fromDateCategoryCompanyScore']) ? $payload['options']['fromDateCategoryCompanyScore'] : Carbon::parse(now()->toDateTimeString())->setTimeZone($timezone)->subMonths(5)->format('Y-m-d 00:00:00');
            $toDate                     = !empty($payload['options']) && isset($payload['options']['endDateCategoryCompanyScore']) ? $payload['options']['endDateCategoryCompanyScore'] : Carbon::parse(now()->toDateTimeString())->setTimeZone($timezone)->format('Y-m-d H:i:s');
            $category_id                = (int) (!empty($payload['options']['category_id']) ? $payload['options']['category_id'] : null);
            $subcategory_id             = (int) (!empty($payload['options']['sub_category_id']) ? $payload['options']['sub_category_id'] : null);
            $fromDate                   = strtotime($fromDate) !== false ? $fromDate : date("Y-m-d 00:00:00");
            $toDate                     = strtotime($toDate) !== false ? $toDate : date("Y-m-d 23:59:59");
            $emptyData                  = getMonthsBetweenDatebyKeys($timezone, $fromDate, $toDate);
            $regex                      = "/^\d+(?:,\d+)*$/";
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
                (!is_null($payload['companyId']) && preg_match($regex, $payload['companyId'])) ? $payload['companyId'] : null,
                is_numeric($payload['departmentId']) ? $payload['departmentId'] : null,
                is_numeric($payload['locationId']) ? $payload['locationId'] : null,
                is_numeric($category_id) ? $category_id : null,
                null,
                $timezone,
                strtotime($fromDate) !== false ? $fromDate : date("Y-m-d 00:00:00"),
                strtotime($toDate) !== false ? $toDate : date("Y-m-d 23:59:59"),
                is_numeric($payload['age1']) ? $payload['age1'] : null,
                is_numeric($payload['age2']) ? $payload['age2'] : null,
            ];
            $spSubCategoryWiseCompanyScoreChartData = DB::select('call sp_dashboard_audit_sub_category_wise_graph(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', $procedureData);
            if (!empty($spSubCategoryWiseCompanyScoreChartData)) {
                $subCategoriesWiseChartData['subcategories'] = $spSubCategoryWiseCompanyScoreChartData;
            }

            // specified subcategory wise company score line charts
            $procedureData = [
                $timezone,
                strtotime($fromDate) !== false ? $fromDate : date("Y-m-d 00:00:00"),
                strtotime($toDate) !== false ? $toDate : date("Y-m-d 23:59:59"),
                config('zevolifesettings.zc_survey_max_score_value', 7),
                (!is_null($payload['companyId']) && preg_match($regex, $payload['companyId'])) ? $payload['companyId'] : null,
                is_numeric($payload['departmentId']) ? $payload['departmentId'] : null,
                is_numeric($payload['locationId']) ? $payload['locationId'] : null,
                is_numeric($category_id) ? $category_id : null,
                is_numeric($subcategory_id) ? $subcategory_id : null,
                is_numeric($payload['age1']) ? $payload['age1'] : null,
                is_numeric($payload['age2']) ? $payload['age2'] : null,
            ];
            $spSubCategoryWiseCompanyScoreLineChartData = DB::select('CALL sp_dashboard_audit_company_score_line_graph(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', $procedureData);
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
                strtotime($fromDate) !== false ? $fromDate : date("Y-m-d 00:00:00"),
                strtotime($toDate) !== false ? $toDate : date("Y-m-d 23:59:59"),
                config('zevolifesettings.zc_survey_max_score_value', 7),
                (!is_null($payload['companyId']) && preg_match($regex, $payload['companyId'])) ? $payload['companyId'] : null,
                is_numeric($payload['departmentId']) ? $payload['departmentId'] : null,
                is_numeric($payload['locationId']) ? $payload['locationId'] : null,
                is_numeric($category_id) ? $category_id : null,
                is_numeric($subcategory_id) ? $subcategory_id : null,
                is_numeric($payload['age1']) ? $payload['age1'] : null,
                is_numeric($payload['age2']) ? $payload['age2'] : null,
            ];
            $chartData = DB::select('CALL sp_dashboard_audit_company_score_baseline(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?);', $procedureData);
            if (!empty($chartData) && isset($chartData[0])) {
                $subCategoriesWiseChartData['performance']['baseline'] = (($chartData[0]->baseLine > 0) ? $chartData[0]->baseLine : 0);
            }
            return $subCategoriesWiseChartData;
        } catch (\Illuminate\Database\QueryException $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong_try_again'),
                'status' => 0,
            ];
            return response()->json($messageData);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong_try_again'),
                'status' => 0,
            ];
            return response()->json($messageData);
        }
    }

    /**
     * category tabs
     *
     * @param Request $request
     * @return Array
     */
    public function getQuestionReportTier1Data($payload)
    {
        try {
            $user      = auth()->user();
            $timezone  = !empty($user->timezone) ? $user->timezone : config('app.timezone');
            $tier1Data = [
                'tabs' => [],
            ];
            $regex = "/^\d+(?:,\d+)*$/";

            $procedureData = [
                config('zevolifesettings.zc_survey_max_score_value', 7),
                (!is_null($payload['companyId']) && preg_match($regex, $payload['companyId'])) ? $payload['companyId'] : null,
                is_numeric($payload['departmentId']) ? $payload['departmentId'] : null,
                is_numeric($payload['locationId']) ? $payload['locationId'] : null,
                null,
                $timezone,
                strtotime($payload['fromDate']) !== false ? $payload['fromDate'] : null,
                strtotime($payload['endDate']) !== false ? $payload['endDate'] : null,
                null,
                null,
            ];
            $subCategories = DB::select('call sp_dashboard_audit_category_wise_tabs(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', $procedureData);
            foreach ($subCategories as $value) {
                $subCategory         = SurveyCategory::find($value->category_id, ['id']);
                $value->image        = $subCategory->logo;
                $tier1Data['tabs'][] = $value;
            }
            return $tier1Data;
        } catch (\Illuminate\Database\QueryException $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong_try_again'),
                'status' => 0,
            ];
            return response()->json($messageData);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong_try_again'),
                'status' => 0,
            ];
            return response()->json($messageData);
        }
    }

    /**
     * category score gauge charts and subcategory progrss bars
     *
     * @param Request $request
     * @return Array
     */
    public function getQuestionReportTier2Data($payload)
    {
        try {
            $user      = auth()->user();
            $timezone  = !empty($user->timezone) ? $user->timezone : config('app.timezone');
            $tier2Data = [
                'score'         => [],
                'subcategories' => [],
            ];
            $regex = "/^\d+(?:,\d+)*$/";

            $procedureData = [
                config('zevolifesettings.zc_survey_max_score_value', 7),
                (!is_null($payload['companyId']) && preg_match($regex, $payload['companyId'])) ? $payload['companyId'] : null,
                is_numeric($payload['departmentId']) ? $payload['departmentId'] : null,
                is_numeric($payload['locationId']) ? $payload['locationId'] : null,
                is_numeric($payload['categoryId']) ? $payload['categoryId'] : null,
                $timezone,
                strtotime($payload['fromDate']) !== false ? $payload['fromDate'] : null,
                strtotime($payload['endDate']) !== false ? $payload['endDate'] : null,
                null,
                null,
            ];

            $categoryScoreGaugeChartData = DB::select('call sp_dashboard_audit_category_wise_tabs(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', $procedureData);
            if (!empty($categoryScoreGaugeChartData)) {
                $categoryScoreGaugeChartData             = $categoryScoreGaugeChartData[0];
                $categoryScoreGaugeChartData->color_code = getScoreColor($categoryScoreGaugeChartData->category_percentage);
                $tier2Data['score']                      = $categoryScoreGaugeChartData;
                $tier2Data['categoryScoreGaugeChart']    = [
                    'data'   => [$categoryScoreGaugeChartData->category_percentage, (100 - $categoryScoreGaugeChartData->category_percentage)],
                    'colors' => [$categoryScoreGaugeChartData->color_code, "#EBECF0"],
                    'labels' => [trans('dashboard.audit.tooltips.category_score'), ""],
                ];
            }

            $procedureData = [
                config('zevolifesettings.zc_survey_max_score_value', 7),
                (!is_null($payload['companyId']) && preg_match($regex, $payload['companyId'])) ? $payload['companyId'] : null,
                is_numeric($payload['departmentId']) ? $payload['departmentId'] : null,
                is_numeric($payload['locationId']) ? $payload['locationId'] : null,
                is_numeric($payload['categoryId']) ? $payload['categoryId'] : null,
                null,
                $timezone,
                strtotime($payload['fromDate']) !== false ? $payload['fromDate'] : null,
                strtotime($payload['endDate']) !== false ? $payload['endDate'] : null,
                null,
                null,
            ];
            $subCategoryData = DB::select('call sp_dashboard_audit_sub_category_wise_graph(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', $procedureData);
            if (!empty($subCategoryData)) {
                $tier2Data['subcategories'] = $subCategoryData;
            }

            return $tier2Data;
        } catch (\Illuminate\Database\QueryException $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong_try_again'),
                'status' => 0,
            ];
            return response()->json($messageData);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong_try_again'),
                'status' => 0,
            ];
            return response()->json($messageData);
        }
    }

    /**
     * subcategory progrss bars
     *
     * @param Request $request
     * @return Array
     */
    public function getQuestionReportTier3Data($payload)
    {
        try {
            $user           = auth()->user();
            $role           = getUserRole();
            $company        = $user->company->first();
            $timezone       = !empty($user->timezone) ? $user->timezone : config('app.timezone');
            $fromDate       = (isset($payload['fromDate']) && (strtotime($payload['fromDate']) !== false) ? $payload['fromDate'] : Carbon::parse(now()->toDateTimeString())->setTimeZone($timezone)->subMonths(5)->format('Y-m-d 00:00:00'));
            $toDate         = isset($payload['endDate']) && (strtotime($payload['endDate']) !== false) ? $payload['endDate'] : Carbon::parse(now()->toDateTimeString())->setTimeZone($timezone)->format('Y-m-d H:i:s');
            $subcategory_id = (!empty($payload['options']['subcategory_id']) && is_numeric($payload['options']['subcategory_id']) ? $payload['options']['subcategory_id'] : 0);

            $param    = ['from' => $fromDate, 'to' => $toDate];

            $param['departmentId'] = is_numeric($payload['departmentId']) ? $payload['departmentId'] : null;
            $param['locationId']   = is_numeric($payload['locationId']) ? $payload['locationId'] : null;
            $categoryId            = is_numeric($payload['categoryId']) ? $payload['categoryId'] : null;

            $questions = ZcSurveyResponse::whereRaw("CONVERT_TZ(zc_survey_responses.created_at, ? , ?) BETWEEN ? AND ?", ['UTC', $timezone, $fromDate, $toDate])
                ->join('zc_questions', function ($join) {
                    $join->on('zc_questions.id', '=', 'zc_survey_responses.question_id');
                })
                ->join('zc_question_types', function ($join) {
                    $join->on('zc_question_types.id', '=', 'zc_questions.question_type_id');
                });
            if (!empty($payload['locationId'])) {
                $questions = $questions->join('company_locations', function ($join) {
                    $join->on('company_locations.company_id', '=', 'zc_survey_responses.company_id');
                });
            }
            $questions = $questions->where('zc_questions.category_id', $categoryId)
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

            if (!empty($payload['departmentId'])) {
                $questions->where('zc_survey_responses.department_id', $payload['departmentId']);
            }

            if (!empty($payload['locationId'])) {
                $questions->where('company_locations.id', $payload['locationId']);
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
        } catch (\Illuminate\Database\QueryException $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong_try_again'),
                'status' => 0,
            ];
            return response()->json($messageData);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong_try_again'),
                'status' => 0,
            ];
            return response()->json($messageData);
        }
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
        try {
            $role     = getUserRole();
            $user     = auth()->user();
            $company  = $user->company->first();
            $timezone = !empty($user->timezone) ? $user->timezone : config('app.timezone');
            $fromDate = (isset($payload['fromDate']) ? $payload['fromDate'] : Carbon::parse(now()->toDateTimeString())->setTimeZone($timezone)->subMonths(5)->format('Y-m-d 00:00:00'));
            $toDate   = isset($payload['endDate']) ? $payload['endDate'] : Carbon::parse(now()->toDateTimeString())->setTimeZone($timezone)->format('Y-m-d H:i:s');
            $fromDate = strtotime($fromDate) !== false ? $fromDate : date("Y-m-d 00:00:00");
            $toDate   = strtotime($toDate) !== false ? $toDate : date("Y-m-d 23:59:59");

            $questions = ZcSurveyResponse::whereRaw("CONVERT_TZ(zc_survey_responses.created_at, ? , ?) BETWEEN ? AND ?", ['UTC', $timezone, $fromDate, $toDate])
                ->join('companies', function ($join) {
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
        } catch (\Illuminate\Database\QueryException $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong_try_again'),
                'status' => 0,
            ];
            return response()->json($messageData);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong_try_again'),
                'status' => 0,
            ];
            return response()->json($messageData);
        }
    }

    /**
     * Get Booking Tab
     *
     * @param Array $payload
     * @return Array
     */
    public function getBookingTabTier1Data($payload)
    {
        try {
            $user       = Auth::user();
            $role       = getUserRole($user);
            $timezone   = $user->timezone ?? null;
            $timezone   = !empty($timezone) ? $timezone : config('app.timezone');
            $fromDate   = Carbon::parse(now()->toDateTimeString())->setTimeZone($timezone)->setTime(0, 0, 0)->toDateTimeString();
            $toDate     = Carbon::parse(now()->toDateTimeString())->setTimeZone($timezone)->setTime(23, 59, 59)->toDateTimeString();
            $wellbeingSpecialistId = ($role->slug == 'wellbeing_specialist') ? $user->id : null;
            $companyId  = null;
            $roleName   = null;
            $regex      = "/^\d+(?:,\d+)*$/";

            if ($role->slug == 'company_admin') {
                $company   = $user->company()->first();
                $companyId = (string) $company->id;
            } else {
                $companyId = $payload['companyId'];
            }

            if ($role->group == 'zevo') {
                $roleName = 'zevo';
            } elseif ($role->group == 'reseller' && isset($company) && $company->parent_id == null) {
                $roleName = 'reseller';
            } elseif ($role->group == 'company' && isset($company) && $company->enable_event) {
                $roleName = 'reseller';
            }

            $procedureData = [
                $timezone,
                (!is_null($companyId) && preg_match($regex, $companyId)) ? $companyId : null,
                $roleName,
                $fromDate,
                $toDate,
                $wellbeingSpecialistId,
            ];
            
            $spBookingData = DB::select('CALL sp_dashboard_booking_tab_event_count(?, ?, ?, ?, ?, ?)', $procedureData);
            $data['bookingData'] = !empty($spBookingData) ? Arr::collapse(json_decode(json_encode($spBookingData), true)) : [];

            return $data;
        } catch (\Illuminate\Database\QueryException $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong_try_again'),
                'status' => 0,
            ];
            return response()->json($messageData);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong_try_again'),
                'status' => 0,
            ];
            return response()->json($messageData);
        }
    }

    /**
     * Get Booking Tab - Events Revenue
     *
     * @param Array $payload
     * @return Array
     */
    public function getBookingTabTier2Data($payload)
    {
        try {
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
            $regex       = "/^\d+(?:,\d+)*$/";

            if ($role->group == 'zevo') {
                $roleName = 'zevo';
            } elseif ($role->group == 'reseller' && isset($company) && $company->parent_id == null) {
                $roleName = 'reseller';
            } elseif ($role->group == 'company' && isset($company) && $company->enable_event) {
                $roleName = 'reseller';
            }

            if (isset($payload['options']['days']) && is_numeric($payload['options']['days'])) {
                $days = now($timezone)->subDays($payload['options']['days'])->setTime(0, 0, 0)->setTimeZone($appTimezone)->toDateTimeString();
            } else {
                $days = now($timezone)->subDays(7)->setTime(0, 0, 0)->setTimeZone($appTimezone)->toDateTimeString();
            }

            $procedureData = [
                $timezone,
                (!is_null($payload['companyId']) && preg_match($regex, $payload['companyId'])) ? $payload['companyId'] : null,
                strtotime($days) !== false ? $days : null,
                strtotime($toDates) !== false ? $toDates : null,
                $roleName,
                is_numeric($companyId) ? $companyId : null,
            ];

            $spBookingData = DB::select('CALL sp_dashboard_booking_tab_events_revenue(?, ?, ?, ?, ?, ?)', $procedureData);

            $data['bookingDataEventRevenue'] = !empty($spBookingData) ? Arr::collapse(json_decode(json_encode($spBookingData), true)) : [];

            return $data;
        } catch (\Illuminate\Database\QueryException $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong_try_again'),
                'status' => 0,
            ];
            return response()->json($messageData);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong_try_again'),
                'status' => 0,
            ];
            return response()->json($messageData);
        }
    }

    /**
     * Get Booking Tab - Today's Event calendar
     *
     * @param Array $payload
     * @return Array
     */
    public function getBookingTabTier3Data($payload)
    {
        try {
            $user      = Auth::user();
            $timezone  = $user->timezone ?? null;
            $timezone  = !empty($timezone) ? $timezone : config('app.timezone');
            $fromDate  = Carbon::parse(now()->toDateTimeString())->setTimeZone($timezone)->setTime(0, 0, 0)->toDateTimeString();
            $toDate    = Carbon::parse(now()->toDateTimeString())->setTimeZone($timezone)->setTime(23, 59, 59)->toDateTimeString();
            $role      = getUserRole($user);
            $data      = [];
            $company   = $user->company->first();
            $companyId = ($company != null) ? $company->id : null;
            $regex     = "/^\d+(?:,\d+)*$/";

            $roleName = null;
            if ($role->group == 'zevo') {
                $roleName = 'zevo';
            } elseif ($role->group == 'reseller' && isset($company) && $company->parent_id == null) {
                $roleName = 'reseller';
            } elseif ($role->group == 'company' && isset($company) && $company->enable_event) {
                $roleName = 'reseller';
            }

            $procedureData = [
                $timezone,
                (!is_null($payload['companyId']) && preg_match($regex, $payload['companyId'])) ? $payload['companyId'] : null,
                $roleName,
                is_numeric($companyId) ? $companyId : null,
                strtotime($fromDate) !== false ? $fromDate : null,
                strtotime($toDate) !== false ? $toDate : null,
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
        } catch (\Illuminate\Database\QueryException $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong_try_again'),
                'status' => 0,
            ];
            return response()->json($messageData);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong_try_again'),
                'status' => 0,
            ];
            return response()->json($messageData);
        }
    }

    /**
     * Get Booking Tab - Top 10 categories
     *
     * @param Array $payload
     * @return Array
     */
    public function getBookingTabTier4Data($payload = '')
    {
        try {
            $user                  = Auth::user();
            $role                  = getUserRole($user);
            $timezone              = $user->timezone ?? null;
            $timezone              = !empty($timezone) ? $timezone : config('app.timezone');
            $fromDate              = Carbon::parse(now()->toDateTimeString())->setTimeZone($timezone)->setTime(0, 0, 0)->toDateTimeString();
            $toDate                = Carbon::parse(now()->toDateTimeString())->setTimeZone($timezone)->setTime(23, 59, 59)->toDateTimeString();
            $wellbeingSpecialistId = ($role->slug == 'wellbeing_specialist') ? $user->id : null;
            $regex                 = "/^\d+(?:,\d+)*$/";
            $roleName              = null;
            $companyId             = null;

            if ($role->slug == 'company_admin') {
                $company   = $user->company()->first();
                $companyId = (string) $company->id;
            } else {
                $companyId = $payload['companyId'];
            }
            
            if ($role->group == 'zevo') {
                $roleName = 'zevo';
            } elseif ($role->group == 'reseller' && isset($company) && $company->parent_id == null) {
                $roleName = 'reseller';
            } elseif ($role->group == 'company' && isset($company) && $company->enable_event) {
                $roleName = 'reseller';
            }

            $procedureData = [
                $timezone,
                (!is_null($companyId) && preg_match($regex, $companyId)) ? $companyId : null,
                $roleName,
                $fromDate,
                $toDate,
                $wellbeingSpecialistId,
            ];
            $spSkillTrend = DB::select('CALL sp_dashboard_booking_tab_skill_trend(?, ?, ?, ?, ?, ?)', $procedureData);
            $data = [];
            if (!empty($spSkillTrend)) {
                foreach ($spSkillTrend as $value) {
                    if ($value->totalAssignUser > 0) {
                        $customArray[] = [
                            'categoriesSkill' => $value->categoriesSkill,
                            'totalAssignUser' => $value->totalAssignUser,
                        ];
                    }
                }
                $data['skillTrend'] = [
                    'categoriesSkill' => array_column($customArray, 'categoriesSkill'),
                    'totalAssignUser' => array_column($customArray, 'totalAssignUser'),
                ];
            }

            return $data;
        } catch (\Illuminate\Database\QueryException $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong_try_again'),
                'status' => 0,
            ];
            return response()->json($messageData);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong_try_again'),
                'status' => 0,
            ];
            return response()->json($messageData);
        }
    }

    /**
     * Get EAP Activity Tab - First Block
     *
     * @param Array $payload
     * @return Array
     */
    public function getEapActivityTabTier1Data($payload)
    {
        try {
            $user         = Auth::user();
            $role         = getUserRole($user);
            $timezone     = $user->timezone ?? null;
            $timezone     = !empty($timezone) ? $timezone : config('app.timezone');
            $fromDate     = Carbon::parse(now()->toDateTimeString())->setTimeZone($timezone)->setTime(0, 0, 0)->toDateTimeString();
            $toDate       = Carbon::parse(now()->toDateTimeString())->setTimeZone($timezone)->setTime(23, 59, 59)->toDateTimeString();
            $counsellorId = ($role->slug == 'counsellor') ? $user->id : null;
            $regex        = "/^\d+(?:,\d+)*$/";

            $procedureData = [
                $timezone,
                (!is_null($payload['companyId']) && preg_match($regex, $payload['companyId'])) ? $payload['companyId'] : null,
                strtotime($fromDate) !== false ? $fromDate : null,
                strtotime($toDate) !== false ? $toDate : null,
                is_numeric($payload['age1']) ? $payload['age1'] : null,
                is_numeric($payload['age2']) ? $payload['age2'] : null,
                $counsellorId,
            ];

            $spSessionData = DB::select('CALL sp_dashboard_eap_activity_tab_session_count(?, ?, ?, ?, ?, ?, ?)', $procedureData);

            $data['todaySession']     = $spSessionData[0]->todaySession;
            $data['upcomingSession']  = $spSessionData[0]->upcomingSession;
            $data['completedSession'] = $spSessionData[0]->completedSession;
            $data['cancelledSession'] = $spSessionData[0]->cancelledSession;
            return $data;
        } catch (\Illuminate\Database\QueryException $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong_try_again'),
                'status' => 0,
            ];
            return response()->json($messageData);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong_try_again'),
                'status' => 0,
            ];
            return response()->json($messageData);
        }
    }

    /**
     * EAP Activity - Appointment Trend
     *
     * @param Array $payload
     * @return Array
     */
    public function getEapActivityTabTier2Data($payload)
    {
        try {
            $user         = Auth::user();
            $role         = getUserRole($user);
            $timezone     = $user->timezone ?? null;
            $timezone     = !empty($timezone) ? $timezone : config('app.timezone');
            $fromDate     = Carbon::parse(now()->subDays(1)->toDateTimeString())->setTimeZone($timezone)->setTime(0, 0, 0)->toDateTimeString();
            $toDate       = Carbon::parse(now()->subDays(7)->toDateTimeString())->setTimeZone($timezone)->setTime(23, 59, 59)->toDateTimeString();
            $counsellorId = ($role->slug == 'counsellor') ? $user->id : null;
            $regex        = "/^\d+(?:,\d+)*$/";

            $procedureData = [
                $timezone,
                (!is_null($payload['companyId']) && preg_match($regex, $payload['companyId'])) ? $payload['companyId'] : null,
                strtotime($fromDate) !== false ? $fromDate : null,
                strtotime($toDate) !== false ? $toDate : null,
                is_numeric($payload['age1']) ? $payload['age1'] : null,
                is_numeric($payload['age2']) ? $payload['age2'] : null,
                $counsellorId,
            ];

            $spAppointmentTrend = DB::select('CALL sp_dashboard_eap_activity_tab_appointment(?, ?, ?, ?, ?, ?, ?)', $procedureData);

            $dayArray = [
                [
                    'day'   => 'Mon',
                    'count' => 0,
                ],
                [
                    'day'   => 'Tue',
                    'count' => 0,
                ],
                [
                    'day'   => 'Wed',
                    'count' => 0,
                ],
                [
                    'day'   => 'Thu',
                    'count' => 0,
                ],
                [
                    'day'   => 'Fri',
                    'count' => 0,
                ],
                [
                    'day'   => 'Sat',
                    'count' => 0,
                ],
                [
                    'day'   => 'Sun',
                    'count' => 0,
                ],
            ];
            if (!empty($spAppointmentTrend)) {
                foreach ($spAppointmentTrend as $key => $value) {
                    $day                     = Carbon::parse($value->daydate)->format('D');
                    $key                     = array_search($day, array_column($dayArray, 'day'));
                    $dayArray[$key]['count'] = $value->sessionCount;
                }
            }
            $data['appointmentTrend'] = [
                'day'   => array_column($dayArray, 'day'),
                'count' => array_column($dayArray, 'count'),
            ];
            return $data;
        } catch (\Illuminate\Database\QueryException $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong_try_again'),
                'status' => 0,
            ];
            return response()->json($messageData);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong_try_again'),
                'status' => 0,
            ];
            return response()->json($messageData);
        }
    }

    /**
     * EAP Activity - Skill Trend
     *
     * @param Array $payload
     * @return Array
     */
    public function getEapActivityTabTier3Data($payload = '')
    {
        try {
            $user         = Auth::user();
            $role         = getUserRole($user);
            $timezone     = $user->timezone ?? null;
            $timezone     = !empty($timezone) ? $timezone : config('app.timezone');
            $fromDate     = Carbon::parse(now()->toDateTimeString())->setTimeZone($timezone)->setTime(0, 0, 0)->toDateTimeString();
            $toDate       = Carbon::parse(now()->toDateTimeString())->setTimeZone($timezone)->setTime(23, 59, 59)->toDateTimeString();
            $counsellorId = ($role->slug == 'counsellor') ? $user->id : null;
            $regex        = "/^\d+(?:,\d+)*$/";

            $procedureData = [
                $timezone,
                (!is_null($payload['companyId']) && preg_match($regex, $payload['companyId'])) ? $payload['companyId'] : null,
                $fromDate,
                $toDate,
                is_numeric($payload['age1']) ? $payload['age1'] : null,
                is_numeric($payload['age2']) ? $payload['age2'] : null,
                $counsellorId,
            ];

            $spSkillTrend = DB::select('CALL sp_dashboard_eap_activity_tab_skill_trend(?, ?, ?, ?, ?, ?, ?)', $procedureData);

            $data = [];
            if (!empty($spSkillTrend)) {
                foreach ($spSkillTrend as $value) {
                    if ($value->totalAssignUser > 0) {
                        $customArray[] = [
                            'categoriesSkill' => $value->categoriesSkill,
                            'totalAssignUser' => $value->totalAssignUser,
                        ];
                    }
                }
                $data['skillTrend'] = [
                    'categoriesSkill' => array_column($customArray, 'categoriesSkill'),
                    'totalAssignUser' => array_column($customArray, 'totalAssignUser'),
                ];
            }

            return $data;
        } catch (\Illuminate\Database\QueryException $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong_try_again'),
                'status' => 0,
            ];
            return response()->json($messageData);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong_try_again'),
                'status' => 0,
            ];
            return response()->json($messageData);
        }
    }

    /**
     * EAP Activity - Therapist
     *
     * @param Array $payload
     * @return Array
     */
    public function getEapActivityTabTier4Data($payload)
    {
        try {
            $user     = Auth::user();
            $timezone = $user->timezone ?? null;
            $timezone = !empty($timezone) ? $timezone : config('app.timezone');
            $fromDate = Carbon::parse(now()->toDateTimeString())->setTimeZone($timezone)->setTime(0, 0, 0)->toDateTimeString();
            $toDate   = Carbon::parse(now()->subDays(30)->toDateTimeString())->setTimeZone($timezone)->setTime(23, 59, 59)->toDateTimeString();
            $regex    = "/^\d+(?:,\d+)*$/";

            $procedureData = [
                $timezone,
                (!is_null($payload['companyId']) && preg_match($regex, $payload['companyId'])) ? $payload['companyId'] : null,
                $fromDate,
                $toDate,
                is_numeric($payload['age1']) ? $payload['age1'] : null,
                is_numeric($payload['age2']) ? $payload['age2'] : null,
            ];

            $spCounsellorsData = DB::select('CALL sp_dashboard_eap_activity_tab_counsellors_count(?, ?, ?, ?, ?, ?)', $procedureData);
            $spUtilizationData = DB::select('CALL sp_dashboard_eap_activity_tab_utilization(?, ?, ?, ?, ?, ?)', $procedureData);

            $data['totalCounsellors']  = $spCounsellorsData[0]->totalCounsellors;
            $data['activeCounsellors'] = $spCounsellorsData[0]->activeCounsellors;

            $userUseService      = $spUtilizationData[0]->userUseService;
            $numberOfUsers       = $spUtilizationData[0]->numberOfUsers;
            $assignToCounsellors = $spUtilizationData[0]->assignToCounsellors;
            $utilization         = 0;
            $totalUtilization    = 0;
            $referrerRate        = 0;
            // Utilization Calculation
            if ($userUseService != 0 && $numberOfUsers != 0) {
                $utilization = $userUseService / $numberOfUsers;
            }
            $totalUtilization    = 100 - number_format($utilization, 2);
            $data['utilization'] = [
                number_format($utilization, 2),
                number_format($totalUtilization, 2),
            ];

            // Referral rate
            if ($assignToCounsellors != 0 && $numberOfUsers != 0) {
                $referrerRate = (100 * $assignToCounsellors) / $numberOfUsers;
            }
            $totalReferrerRate    = 100 - number_format($referrerRate, 2);
            $data['referrerRate'] = [
                number_format($referrerRate, 2),
                number_format($totalReferrerRate, 2),
            ];

            return $data;
        } catch (\Illuminate\Database\QueryException $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong_try_again'),
                'status' => 0,
            ];
            return response()->json($messageData);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong_try_again'),
                'status' => 0,
            ];
            return response()->json($messageData);
        }
    }

    /**
     * Get Physical Tab Tier 5 Data - Most popular exercise by manual and tracker
     *
     * @param Request $request
     * @return Array
     */
    public function getPhysicalTabTier5Data($payload)
    {
        try {
            $timezone = Auth::user()->timezone ?? null;
            $timezone = !empty($timezone) ? $timezone : config('app.timezone');
            $fromDate = !empty($payload['options']) && isset($payload['options']['fromDateExerciseRanges']) ? $payload['options']['fromDateExerciseRanges'] : Carbon::parse(now()->toDateTimeString())->setTimeZone($timezone)->subDays(7)->format('Y-m-d 00:00:00');
            $toDate   = !empty($payload['options']) && isset($payload['options']['endDateExerciseRanges']) ? $payload['options']['endDateExerciseRanges'] : Carbon::parse(now()->toDateTimeString())->setTimeZone($timezone)->format('Y-m-d H:i:s');
            $fromDate = strtotime($fromDate) !== false ? $fromDate : date("Y-m-d 00:00:00");
            $toDate   = strtotime($toDate) !== false ? $toDate : date("Y-m-d 23:59:59");
            $days     = Carbon::parse($toDate)->diffInDays(Carbon::parse($fromDate)) + 1;
            $regex    = "/^\d+(?:,\d+)*$/";
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
                is_numeric($days) ? $days : null,
                (!is_null($payload['companyId']) && preg_match($regex, $payload['companyId'])) ? $payload['companyId'] : null,
                is_numeric($payload['departmentId']) ? $payload['departmentId'] : null,
                is_numeric($payload['locationId']) ? $payload['locationId'] : null,
                is_numeric($payload['age1']) ? $payload['age1'] : null,
                is_numeric($payload['age2']) ? $payload['age2'] : null,
            ];

            $spExercisesData = DB::select('CALL sp_dashboard_physical_exercises_range(?, ?, ?, ?, ?, ?, ?, ?, ?)', $procedureData);

            if (!empty($spExercisesData)) {
                $exercisesData         = collect($spExercisesData)->pluck('percent', 'exerciseRange')->toArray();
                $data['exercisesData'] = Arr::flatten(array_map(function ($value) use ($exercisesData) {
                    return array_key_exists($value, $exercisesData) ? $exercisesData[$value] : 0;
                }, $exerciseLabels));
            }

            $fromDate = !empty($payload['options']) && isset($payload['options']['fromDateStepRanges']) ? $payload['options']['fromDateStepRanges'] : Carbon::parse(now()->toDateTimeString())->setTimeZone($timezone)->subDays(7)->format('Y-m-d 00:00:00');
            $toDate   = !empty($payload['options']) && isset($payload['options']['endDateStepRanges']) ? $payload['options']['endDateStepRanges'] : Carbon::parse(now()->toDateTimeString())->setTimeZone($timezone)->format('Y-m-d H:i:s');
            $fromDate = strtotime($fromDate) !== false ? $fromDate : date("Y-m-d 00:00:00");
            $toDate   = strtotime($toDate) !== false ? $toDate : date("Y-m-d 23:59:59");
            $days     = Carbon::parse($toDate)->diffInDays(Carbon::parse($fromDate)) + 1;

            $procedureData[1] = $fromDate;
            $procedureData[2] = $toDate;
            $procedureData[3] = is_numeric($days) ? $days : null;

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
        } catch (\Illuminate\Database\QueryException $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong_try_again'),
                'status' => 0,
            ];
            return response()->json($messageData);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong_try_again'),
                'status' => 0,
            ];
            return response()->json($messageData);
        }
    }

    /**
     * Get EAP Activity Tab - First Block
     *
     * @param Array $payload
     * @return Array
     */
    public function getDigitalTherapyTabTier1Data($payload)
    {
        try {
            $user                  = Auth::user();
            $role                  = getUserRole($user);
            $timezone              = $user->timezone ?? null;
            $timezone              = !empty($timezone) ? $timezone : config('app.timezone');
            $fromDate              = Carbon::parse(now()->toDateTimeString())->setTimeZone($timezone)->setTime(0, 0, 0)->toDateTimeString();
            $toDate                = Carbon::parse(now()->toDateTimeString())->setTimeZone($timezone)->setTime(23, 59, 59)->toDateTimeString();
            $wellbeingSpecialistId = ($role->slug == 'wellbeing_specialist') ? $user->id : null;
            $companyId             = null;
            $regex                 = "/^\d+(?:,\d+)*$/";

            if ($role->slug == 'company_admin') {
                $company   = $user->company()->first();
                $companyId = (string) $company->id;
            } else {
                $companyId = $payload['companyId'];
            }
            $procedureData = [
                $timezone,
                (!is_null($companyId) && preg_match($regex, $companyId)) ? $companyId : null,
                is_numeric($payload['departmentId']) ? $payload['departmentId'] : null,
                is_numeric($payload['locationId']) ? $payload['locationId'] : null,
                $fromDate,
                $toDate,
                is_numeric($payload['age1']) ? $payload['age1'] : null,
                is_numeric($payload['age2']) ? $payload['age2'] : null,
                $wellbeingSpecialistId,
                (isset($payload['serviceId']) && preg_match($regex, $payload['serviceId'])) ? $payload['serviceId'] : null,
            ];
            $spSessionData = DB::select('CALL sp_dashboard_digital_therapy_tab_session_count(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', $procedureData);

            $data['todaySession']     = $spSessionData[0]->todaySession;
            $data['upcomingSession']  = $spSessionData[0]->upcomingSession;
            $data['completedSession'] = $spSessionData[0]->completedSession;
            $data['cancelledSession'] = $spSessionData[0]->cancelledSession;
            return $data;
        } catch (\Illuminate\Database\QueryException $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong_try_again'),
                'status' => 0,
            ];
            return response()->json($messageData);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong_try_again'),
                'status' => 0,
            ];
            return response()->json($messageData);
        }
    }

    /**
     * EAP Activity - Appointment Trend
     *
     * @param Array $payload
     * @return Array
     */
    public function getDigitalTherapyTabTier2Data($payload)
    {
        try {
            $user                  = Auth::user();
            $role                  = getUserRole($user);
            $timezone              = $user->timezone ?? null;
            $timezone              = !empty($timezone) ? $timezone : config('app.timezone');
            $fromDate              = Carbon::parse(now()->subDays(1)->toDateTimeString())->setTimeZone($timezone)->setTime(0, 0, 0)->toDateTimeString();
            $toDate                = Carbon::parse(now()->subDays(7)->toDateTimeString())->setTimeZone($timezone)->setTime(23, 59, 59)->toDateTimeString();
            $wellbeingSpecialistId = ($role->slug == 'wellbeing_specialist') ? $user->id : null;
            $companyId             = null;
            $regex                 = "/^\d+(?:,\d+)*$/";

            if ($role->slug == 'company_admin') {
                $company   = $user->company()->first();
                $companyId = (string) $company->id;
            } else {
                $companyId = $payload['companyId'];
            }
            $procedureData = [
                $timezone,
                (!is_null($companyId) && preg_match($regex, $companyId)) ? $companyId : null,
                is_numeric($payload['departmentId']) ? $payload['departmentId'] : null,
                is_numeric($payload['locationId']) ? $payload['locationId'] : null,
                $fromDate,
                $toDate,
                is_numeric($payload['age1']) ? $payload['age1'] : null,
                is_numeric($payload['age2']) ? $payload['age2'] : null,
                $wellbeingSpecialistId,
                (isset($payload['serviceId']) && preg_match($regex, $payload['serviceId'])) ? $payload['serviceId'] : null,
            ];

            $spAppointmentTrend = DB::select('CALL sp_dashboard_digital_therapy_tab_appointment(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', $procedureData);

            $dayArray = [
                [
                    'day'   => 'Mon',
                    'count' => 0,
                ],
                [
                    'day'   => 'Tue',
                    'count' => 0,
                ],
                [
                    'day'   => 'Wed',
                    'count' => 0,
                ],
                [
                    'day'   => 'Thu',
                    'count' => 0,
                ],
                [
                    'day'   => 'Fri',
                    'count' => 0,
                ],
                [
                    'day'   => 'Sat',
                    'count' => 0,
                ],
                [
                    'day'   => 'Sun',
                    'count' => 0,
                ],
            ];
            if (!empty($spAppointmentTrend)) {
                foreach ($spAppointmentTrend as $key => $value) {
                    $newDay                  = isset($value->daydate) ? date('d', strtotime($value->daydate)) : '';
                    $day                     = isset($value->day) ? $value->day : $newDay;
                    $key                     = array_search($day, array_column($dayArray, 'day'));
                    $dayArray[$key]['count'] = $value->sessionCount;
                }
            }
            $data['appointmentTrend'] = [
                'day'   => array_column($dayArray, 'day'),
                'count' => array_column($dayArray, 'count'),
            ];
            return $data;
        } catch (\Illuminate\Database\QueryException $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong_try_again'),
                'status' => 0,
            ];
            return response()->json($messageData);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong_try_again'),
                'status' => 0,
            ];
            return response()->json($messageData);
        }
    }

    /**
     * EAP Activity - Skill Trend
     *
     * @param Array $payload
     * @return Array
     */
    public function getDigitalTherapyTabTier3Data($payload = '')
    {
        try {
            $user                  = Auth::user();
            $role                  = getUserRole($user);
            $timezone              = $user->timezone ?? null;
            $timezone              = !empty($timezone) ? $timezone : config('app.timezone');
            $fromDate              = Carbon::parse(now()->toDateTimeString())->setTimeZone($timezone)->setTime(0, 0, 0)->toDateTimeString();
            $toDate                = Carbon::parse(now()->toDateTimeString())->setTimeZone($timezone)->setTime(23, 59, 59)->toDateTimeString();
            $wellbeingSpecialistId = ($role->slug == 'wellbeing_specialist') ? $user->id : null;
            $regex                 = "/^\d+(?:,\d+)*$/";

            $procedureData = [
                $timezone,
                (!is_null($payload['companyId']) && preg_match($regex, $payload['companyId'])) ? $payload['companyId'] : null,
                is_numeric($payload['departmentId']) ? $payload['departmentId'] : null,
                is_numeric($payload['locationId']) ? $payload['locationId'] : null,
                $fromDate,
                $toDate,
                is_numeric($payload['age1']) ? $payload['age1'] : null,
                is_numeric($payload['age2']) ? $payload['age2'] : null,
                $wellbeingSpecialistId,
                (isset($payload['serviceId']) && preg_match($regex, $payload['serviceId'])) ? $payload['serviceId'] : null,
            ];
            $spSkillTrend = DB::select('CALL sp_dashboard_digital_therapy_tab_skill_trend(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', $procedureData);

            $data = [];
            if (!empty($spSkillTrend)) {
                foreach ($spSkillTrend as $value) {
                    if ($value->totalAssignUser > 0) {
                        $customArray[] = [
                            'categoriesSkill' => $value->categoriesSkill,
                            'totalAssignUser' => $value->totalAssignUser,
                        ];
                    }
                }
                $data['skillTrend'] = [
                    'categoriesSkill' => array_column($customArray, 'categoriesSkill'),
                    'totalAssignUser' => array_column($customArray, 'totalAssignUser'),
                ];
            }

            return $data;
        } catch (\Illuminate\Database\QueryException $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong_try_again'),
                'status' => 0,
            ];
            return response()->json($messageData);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong_try_again'),
                'status' => 0,
            ];
            return response()->json($messageData);
        }
    }

    /**
     * EAP Activity - Therapist
     *
     * @param Array $payload
     * @return Array
     */
    public function getDigitalTherapyTabTier4Data($payload)
    {
        try {
            $user                  = Auth::user();
            $timezone              = $user->timezone ?? null;
            $timezone              = !empty($timezone) ? $timezone : config('app.timezone');
            $fromDate              = Carbon::parse(now()->toDateTimeString())->setTimeZone($timezone)->setTime(0, 0, 0)->toDateTimeString();
            $toDate                = Carbon::parse(now()->subDays(30)->toDateTimeString())->setTimeZone($timezone)->setTime(23, 59, 59)->toDateTimeString();
            $wellbeingSpecialistId = $user->id;
            $regex                 = "/^\d+(?:,\d+)*$/";

            $procedureData = [
                $timezone,
                (!is_null($payload['companyId']) && preg_match($regex, $payload['companyId'])) ? $payload['companyId'] : null,
                $fromDate,
                $toDate,
                is_numeric($payload['age1']) ? $payload['age1'] : null,
                is_numeric($payload['age2']) ? $payload['age2'] : null,
                $wellbeingSpecialistId,
                (isset($payload['serviceId']) && preg_match($regex, $payload['serviceId'])) ? $payload['serviceId'] : null,
            ];

            $spCounsellorsData = DB::select('CALL sp_dashboard_digital_therapy_tab_wellbeing_specialist_count(?, ?, ?, ?, ?, ?, ?, ?)', $procedureData);

            $data['totalCounsellors']  = $spCounsellorsData[0]->totalWellbeingSpecialists;
            $data['activeCounsellors'] = $spCounsellorsData[0]->activeWellbeingSpecialists;

            return $data;
        } catch (\Illuminate\Database\QueryException $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong_try_again'),
                'status' => 0,
            ];
            return response()->json($messageData);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong_try_again'),
                'status' => 0,
            ];
            return response()->json($messageData);
        }
    }
}
