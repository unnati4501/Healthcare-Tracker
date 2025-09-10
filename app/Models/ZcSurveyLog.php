<?php

namespace App\Models;

use App\Traits\HasRewardPointsTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\hasMany;
use Yajra\DataTables\Facades\DataTables;

class ZcSurveyLog extends Model
{
    use HasRewardPointsTrait;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'zc_survey_log';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = [
        'company_id',
        'survey_id',
        'roll_out_date',
        'roll_out_time',
        'expire_date',
        'survey_to_all',
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
    protected $casts = ['survey_to_all' => 'boolean'];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['roll_out_date', 'expire_date', 'created_at', 'updated_at'];

    /**
     * @return BelongsTo
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo('App\Models\Company');
    }

    /**
     * @return BelongsTo
     */
    public function survey(): BelongsTo
    {
        return $this->belongsTo('App\Models\ZcSurvey');
    }

    /**
     * @return HasMany
     */
    public function surveyUserLogs(): HasMany
    {
        return $this->HasMany('App\Models\ZcSurveyUserLog', 'survey_log_id', 'id');
    }

    /**
     * Set datatable for record list.
     *
     * @param payload
     * @return dataTable
     */

    public function getSurveyInsightsTableData($payload)
    {
        $list = $this->getSurveyInsightsRecordList($payload);
        return DataTables::of($list['record'])
            ->skipPaging()
            ->addColumn('response_rate', function ($record) {
                $responseRate = ($record->surveyreponses_count > 0 || $record->surveysent_count > 0) ? ($record->surveyreponses_count * 100) / $record->surveysent_count : 0;
                $responseRate = ($responseRate > 0) ? number_format($responseRate, 2, '.', '') : 0;
                return $responseRate . '%';
            })
            ->addColumn('status', function ($record) {
                if ($record->status == 1) {
                    return "<span class='text-success'>" . trans('survey.insights.labels.progress') . "</span>";
                } else {
                    return "<span class='text-danger'>" . trans('survey.insights.labels.expired') . "</span>";
                }
            })
            ->addColumn('view', function ($record) {
                return view('admin.zcsurvey.survey-insights.listaction', compact('record'))->render();
            })
            ->with([
                "recordsTotal"    => $list['total'],
                "recordsFiltered" => $list['total'],
            ])
            ->rawColumns(['view', 'status'])
            ->make(true);
    }

    public function getSurveyInsightsRecordList($payload)
    {
        $role     = getUserRole();
        $user     = auth()->user();
        $timezone = (!empty($user->timezone) ? $user->timezone : config('app.timezone'));
        $now      = \now(config('app.timezone'))->setTimeZone($timezone)->toDateTimeString();

        $query = $this
            ->leftJoin('zc_survey', function ($join) {
                $join->on('zc_survey.id', '=', 'zc_survey_log.survey_id');
            })
            ->leftJoin('zc_survey_user_log', function ($join) {
                $join->on('zc_survey_user_log.survey_log_id', '=', 'zc_survey_log.id');
            })
            ->leftJoin('companies', function ($join) {
                $join->on('companies.id', '=', 'zc_survey_log.company_id');
            })
            ->select(
                'zc_survey_log.*',
                'zc_survey.title AS survey_title',
                'companies.name AS company_name',
                \DB::raw('COUNT(zc_survey_user_log.id) AS surveysent_count'),
                \DB::raw('SUM(zc_survey_user_log.survey_submitted_at IS NOT NULL) AS surveyreponses_count'),
                \DB::raw('IFNULL(SUM(zc_survey_user_log.retake),0) AS retake_response'),
                \DB::raw("(SELECT FORMAT(IFNULL(((IFNULL(SUM(zc_survey_responses.score), 0) * 100) / IFNULL(SUM(zc_survey_responses.max_score), 0)), 0), 2) FROM zc_survey_responses WHERE zc_survey_responses.survey_log_id = zc_survey_log.id) AS percentage"),
            )
            ->selectRaw("IF((CONVERT_TZ(zc_survey_log.expire_date, ?, ?) > ?), 1, 0) AS `status`",[
                'UTC',$timezone,$now
            ])
            ->groupBy('zc_survey_log.id');

        if ($role->group != "zevo") {
            $company_id = $user->company->first()->id;
            $query->where('zc_survey_log.company_id', $company_id);
        }

        if (!empty($payload['company_id'])) {
            $query->where('zc_survey_log.company_id', $payload['company_id']);
        }

        if (!empty($payload['publish_date'])) {
            $query->whereRaw("DATE(CONVERT_TZ(zc_survey_log.roll_out_date, ?, ?)) = ?", ['UTC', $timezone, $payload['publish_date']]);
        }

        if (!empty($payload['expiry_date'])) {
            $query->whereRaw("DATE(CONVERT_TZ(zc_survey_log.expire_date, ?, ?)) = ?", ['UTC', $timezone, $payload['expiry_date']]);
        }

        if (isset($payload['order'])) {
            $column = $payload['columns'][$payload['order'][0]['column']]['data'];
            if ($column == 'response_rate') {
                $column = 'surveyreponses_count';
            }
            $order = $payload['order'][0]['dir'];
            $query->orderBy($column, $order);
        } else {
            $query->orderByDesc('zc_survey_log.id');
        }

        return [
            'total'  => $query->get()->count(),
            'record' => $query->offset($payload['start'])->limit($payload['length'])->get(),
        ];
    }

    /**
     * Set datatable for record list.
     *
     * @param payload
     * @return dataTable
     */
    public function getSurveyInsightQuestionsTableData($categoryId, $payload)
    {
        $list = $this->getSurveyInsightQuestionsRecordList($categoryId, $payload);
        return DataTables::of($list['record'])
            ->skipPaging()
            ->with([
                "recordsTotal"    => $list['total'],
                "recordsFiltered" => $list['total'],
            ])
            ->addColumn('options', function ($record) {
                if ($record->question_type == 'Choice') {
                    return $record->options;
                } else {
                    return '-';
                }
            })
            ->addColumn('actions', function ($record) {
                return view('admin.zcsurvey.survey-insights.questionview', compact('record'))->render();
            })
            ->rawColumns(['actions'])
            ->make(true);
    }

    public function getSurveyInsightQuestionsRecordList($categoryId, $payload)
    {
        $query = $this
            ->leftJoin('zc_survey_responses', function ($join) {
                $join->on('zc_survey_responses.survey_log_id', '=', 'zc_survey_log.id');
            })
            ->leftJoin('zc_survey_questions', function ($join) {
                $join->on('zc_survey_questions.survey_id', '=', 'zc_survey_log.survey_id');
            })
            ->leftJoin('zc_categories', function ($join) {
                $join->on('zc_categories.id', '=', 'zc_survey_questions.category_id');
            })
            ->leftJoin('zc_sub_categories', function ($join) {
                $join->on('zc_sub_categories.id', '=', 'zc_survey_questions.sub_category_id');
            })
            ->leftJoin('zc_questions', function ($join) {
                $join->on('zc_questions.id', '=', 'zc_survey_questions.question_id');
            })
            ->leftJoin('zc_question_types', function ($join) {
                $join->on('zc_question_types.id', '=', 'zc_questions.question_type_id');
            })
            ->select(
                "zc_survey_responses.id",
                "zc_survey_questions.question_id",
                "zc_categories.display_name AS category_name",
                "zc_sub_categories.display_name AS sub_category_name",
                "zc_questions.title AS question",
                "zc_question_types.display_name AS question_type",
                \DB::raw("(SELECT COUNT(id) FROM zc_survey_responses WHERE survey_log_id = zc_survey_log.id AND question_id = zc_survey_questions.question_id) AS responses"),
                \DB::raw("(SELECT FORMAT(IFNULL(((IFNULL(SUM(zc_survey_responses.score), 0) * 100) / IFNULL(SUM(zc_survey_responses.max_score), 0)), 0), 2) FROM zc_survey_responses WHERE zc_survey_responses.survey_log_id = zc_survey_log.id AND zc_survey_responses.question_id = zc_survey_questions.question_id) AS percentage"),
                \DB::raw("(SELECT count(id) FROM zc_questions_options WHERE question_id = zc_survey_questions.question_id AND choice != 'meta') AS options")
            )
            ->where(['zc_survey_log.id' => $this->id, 'zc_survey_questions.category_id' => $categoryId->id])
            ->groupBy('zc_survey_questions.question_id');

        if (isset($payload['order'])) {
            $column = $payload['columns'][$payload['order'][0]['column']]['data'];
            $order  = $payload['order'][0]['dir'];
            $query->orderBy($column, $order);
        } else {
            $query->orderBy('zc_survey_questions.id');
        }

        return [
            'total'  => $query->get()->count(),
            'record' => $query->offset($payload['start'])->limit($payload['length'])->get(),
        ];
    }
}
