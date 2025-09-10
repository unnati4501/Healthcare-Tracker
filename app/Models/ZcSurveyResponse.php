<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Yajra\DataTables\Facades\DataTables;

class ZcSurveyResponse extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'zc_survey_responses';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'company_id',
        'department_id',
        'survey_log_id',
        'category_id',
        'sub_category_id',
        'question_id',
        'option_id',
        'answer_value',
        'score',
        'max_score',
        'created_at',
        'updated_at',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['created_at', 'updated_at'];

    /**
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo('App\Models\User', 'user_id');
    }

    /**
     * @return BelongsTo
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo('App\Models\Company', 'company_id');
    }

    /**
     * @return BelongsTo
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo('App\Models\Department', 'department_id');
    }

    /**
     * @return BelongsTo
     */
    public function surveyLog(): BelongsTo
    {
        return $this->belongsTo('App\Models\ZcSurveyLog', 'survey_log_id');
    }

    /**
     * @return BelongsTo
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo('App\Models\SurveyCategory', 'category_id');
    }

    /**
     * @return BelongsTo
     */
    public function subcategory(): BelongsTo
    {
        return $this->belongsTo('App\Models\SurveySubCategory', 'sub_category_id');
    }

    /**
     * @return BelongsTo
     */
    public function question(): BelongsTo
    {
        return $this->belongsTo('App\Models\ZcQuestion', 'question_id');
    }

    /**
     * Set datatable for record list.
     *
     * @param payload
     * @return dataTable
     */
    public function getHrReportTableData($payload)
    {
        $role = getUserRole();
        $list = $this->getHrReportRecordList($payload);
        return DataTables::of($list)
            ->addColumn('role', function () use ($role) {
                return $role->group;
            })
            ->make(true);
    }

    public function getHrReportRecordList($payload)
    {
        $role      = getUserRole();
        $user      = auth()->user();
        $timezone  = !empty($user->timezone) ? $user->timezone : config('app.timezone');
        $statement = "";
        $company_id            = is_numeric($payload['company']) ? $payload['company'] : 0;
        $startDate             = (isset($payload['from']) && !empty($payload['from'] && strtotime($payload['from']) !== false) ? $payload['from'] : 0);
        $endDate               = (isset($payload['to']) && !empty($payload['to'] && strtotime($payload['to']) !== false) ? $payload['to'] : 0);
        $whereCond             = "1 = 1";

        if ($role->group != 'zevo') {
            $company    = $user->company->first();
            $company_id = $company->id;
        }

        $categories = $this
            ->select(
                'zc_survey_responses.category_id AS id',
                'zc_categories.name',
                'zc_categories.display_name',
                \DB::raw("FORMAT(IFNULL(((IFNULL(SUM(zc_survey_responses.score), 0) * 100) / IFNULL(SUM(zc_survey_responses.max_score), 0)), 0), 2) AS category_percent")
            )
            ->join('zc_categories', function ($join) {
                $join->on('zc_categories.id', '=', 'zc_survey_responses.category_id');
            })
            ->groupBy('zc_survey_responses.category_id');

        $getCategories = $categories->get()->toArray();
        
        if (!empty($getCategories)) {
            foreach ($getCategories as $category) {
                $statement .= ", FORMAT(IFNULL((SUM(IF((res.category_id = " . $category['id'] . "), res.category_score, 0)) / SUM(IF((res.category_id = " . $category['id'] . "), res.category_qs, 0))), 0), 2) AS " . $category['name'];
            }
        }

        if (!empty($company_id)) {
            $whereCond .= " AND zc_survey_responses.company_id = {$company_id}";
        }

        if (!empty($startDate) && !empty($endDate)) {
            $whereCond .= " AND CONVERT_TZ(zc_survey_responses.created_at, 'UTC', '{$timezone}') BETWEEN '{$startDate}' AND '{$endDate}'";
        }

        // IFNULL(((SUM(zc_survey_responses.score) * 100) / (SUM(zc_survey_responses.score <> 0) * {$zc_survey_max_score_value})), 0) AS category_percent,
        $statement = "
            SELECT
                res.company_id,
                res.department_id,
                companies.name AS company_name,
                departments.name AS department_name,
                FORMAT(IFNULL((SUM(res.category_score) / SUM(res.category_qs)), 0), 2) AS score
                {$statement}
            FROM(
                SELECT
                    zc_survey_responses.company_id,
                    zc_survey_responses.department_id,
                    zc_survey_responses.category_id,
                    IFNULL(((SUM(zc_survey_responses.score) * 100) / IFNULL(SUM(zc_survey_responses.max_score), 0)), 0) AS category_percent,
                    (SUM(zc_survey_responses.score) * 100) AS category_score,
                    IFNULL(SUM(zc_survey_responses.max_score), 0) AS category_qs
                FROM zc_survey_responses
                WHERE {$whereCond}
                GROUP BY zc_survey_responses.category_id, zc_survey_responses.department_id
            ) AS res
            INNER JOIN companies ON (companies.id = res.company_id)
            INNER JOIN departments ON (departments.id = res.department_id)
            GROUP BY res.department_id
        ";

        return \DB::select(trim($statement));
    }

    /**
     * Get hr report details for specifed departmentId and categoryId
     *
     * @param departmentId
     * @param categoryId
     * @param payload
     * @return array
     */
    public function getHrReportDetails($company, $department, $category, $payload)
    {
        try {
            $user                   = auth()->user();
            $timezone               = !empty($user->timezone) ? $user->timezone : config('app.timezone');
            $fromDate               = (isset($payload['from']) && !empty($payload['from']) && strtotime($payload['from']) !== false) ? $payload['from'] : Carbon::parse(now()->toDateTimeString())->setTimeZone($timezone)->subMonths(5)->format('Y-m-d 00:00:00');
            $toDate                 = (isset($payload['to']) && !empty($payload['to']) && strtotime($payload['to']) !== false) ? $payload['to'] : Carbon::parse(now()->toDateTimeString())->setTimeZone($timezone)->format('Y-m-d H:i:s');

            $emptyData              = getMonthsBetweenDatebyKeys($timezone, $fromDate, $toDate);
            $departmentCategoryData = [
                'performance'   => ['data' => [], 'labels' => []],
                'subcategories' => [],
            ];

            $procedureData = [
                $timezone,
                $fromDate,
                $toDate,
                config('zevolifesettings.zc_survey_max_score_value', 7),
                $company->id,
                $department->id,
                null,
                $category->id,
                null,
                null,
                null,
            ];
            $spdepartmentCategoryData = \DB::select('CALL sp_dashboard_audit_company_score_line_graph(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', $procedureData);
            if (!empty($spdepartmentCategoryData)) {
                $chartData = Collect($spdepartmentCategoryData)->pluck('month_percentage', 'log_month');
                foreach ($emptyData as $key => $value) {
                    if (isset($chartData[$key]) || is_numeric(array_key_last($departmentCategoryData['performance']['data']))) {
                        array_push($departmentCategoryData['performance']['labels'], $value);
                        array_push($departmentCategoryData['performance']['data'], (isset($chartData[$key]) ? $chartData[$key] : end($departmentCategoryData['performance']['data'])));
                    }
                }
            }

            $procedureData = [
                config('zevolifesettings.zc_survey_max_score_value', 7),
                $company->id,
                $department->id,
                null,
                $category->id,
                null,
                $timezone,
                $fromDate,
                $toDate,
                null,
                null,
            ];
            $spdepartmentSubCategoryData             = \DB::select('CALL sp_dashboard_audit_sub_category_wise_graph(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', $procedureData);
            $departmentCategoryData['subcategories'] = $spdepartmentSubCategoryData;

            return $departmentCategoryData;
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
