<?php

namespace App\Models;

use App\Models\ZcSurveyReviewSuggestionLog;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Yajra\DataTables\Facades\DataTables;

class ZcSurveyReviewSuggestion extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'zc_survey_review_suggestion';

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
        'comment',
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
    protected $casts = [
        'user_id'       => 'integer',
        'company_id'    => 'integer',
        'department_id' => 'integer',
        'survey_log_id' => 'integer',
        'comment'       => 'string',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['created_at', 'updated_at'];

    /**
     * Set datatable for record list.
     *
     * @param payload
     * @return dataTable
     */

    public function getTableData($payload)
    {
        $role = getUserRole();
        $list = $this->getRecordList($payload);
        return DataTables::of($list['record'])
            ->skipPaging()
            ->with([
                "recordsTotal"    => $list['total'],
                "recordsFiltered" => $list['total'],
            ])
            ->addColumn('is_favorite', function ($record) {
                return (int) ($record->is_favorite ?? 0);
            })
            ->addColumn('status', function ($record) {
                if ($record->status == 1) {
                    return "<span class='text-success'>" . trans('survey.feedback.labels.progress') . "</span>";
                } else {
                    return "<span class='text-danger'>" . trans('survey.feedback.labels.expired') . "</span>";
                }
            })
            ->addColumn('actions', function ($record) use ($role) {
                if ($role->group != 'zevo') {
                    return view('admin.zcsurvey.review-suggestion.listaction', compact('record'))->render();
                }
                return false;
            })
            ->rawColumns(['status', 'actions'])
            ->make(true);
    }

    public function getRecordList($payload)
    {
        $role     = getUserRole();
        $user     = auth()->user();
        $timezone = (!empty($user->timezone) ? $user->timezone : config('app.timezone'));
        $now      = \now(config('app.timezone'))->setTimeZone($timezone)->toDateTimeString();
        $query    = $this
            ->leftJoin('zc_survey_log', function ($join) {
                $join->on('zc_survey_log.id', '=', 'zc_survey_review_suggestion.survey_log_id');
            })
            ->leftJoin('zc_survey', function ($join) {
                $join->on('zc_survey.id', '=', 'zc_survey_log.survey_id');
            })
            ->select(
                'zc_survey_review_suggestion.id',
                'zc_survey_review_suggestion.comment AS suggestion',
                'zc_survey.title AS survey_title',
                'zc_survey_log.roll_out_date AS published_date',
                'zc_survey_log.expire_date',
            )->selectRaw("IF((CONVERT_TZ(zc_survey_log.expire_date, ?, ?) > ?), 1, 0) AS `status`",[
                'UTC',$timezone,$now
            ]);

        if ($role->group == "zevo") {
            $query
                ->addSelect('companies.name AS company_name')
                ->leftJoin('companies', function ($join) {
                    $join->on('companies.id', '=', 'zc_survey_review_suggestion.company_id');
                });
        } else {
            $company_id = $user->company->first()->id;
            $query
                ->selectRaw("(SELECT is_favorite FROM zc_survey_review_suggestion_log WHERE company_id = ? AND suggestion_id = zc_survey_review_suggestion.id) AS is_favorite",[
                    $company_id
                ]);
            $query->where('zc_survey_review_suggestion.company_id', $company_id);
        }

        if (isset($payload['favoriteOnly']) && $payload['favoriteOnly'] == "true") {
            $query
                ->join('zc_survey_review_suggestion_log', function ($join) {
                    $join
                        ->on('zc_survey_review_suggestion_log.suggestion_id', '=', 'zc_survey_review_suggestion.id')
                        ->where('is_favorite', 1);
                });
        }

        if (!empty($payload['company_id'])) {
            $query->where('zc_survey_review_suggestion.company_id', $payload['company_id']);
        }

        if (in_array('date_range', array_keys($payload)) && !empty($payload['date_range'])) {
            $timezone = ($user->timezone ?? null);
            $timezone = (!empty($timezone) ? $timezone : config('app.timezone'));
            $dateRange    = explode('-', $payload['date_range']);

            $fromDate = (!empty($dateRange[1]) && strtotime($dateRange[1]) !== false) ? $dateRange[1] : null;
            $toDate = (!empty($dateRange[0]) && strtotime($dateRange[0]) !== false) ? $dateRange[0] : null;

            if(!empty($fromDate) && !empty($toDate)){
                $to       = Carbon::parse($toDate)->setTimezone($timezone)->format('Y-m-d');
                $from     = Carbon::parse($fromDate)->setTimezone($timezone)->format('Y-m-d');
                
                $query->whereRaw("DATE(CONVERT_TZ(zc_survey_log.roll_out_date, ?, ?)) BETWEEN ? AND ?", ['UTC', $timezone, $to, $from]);
            }
        }

        if (isset($payload['order'])) {
            $column = $payload['columns'][$payload['order'][0]['column']]['data'];
            $order  = $payload['order'][0]['dir'];
            $query->orderBy($column, $order);
        } else {
            $query->orderByDesc('zc_survey_review_suggestion.id');
        }

        return [
            'total'  => $query->get()->count(),
            'record' => $query->offset($payload['start'])->limit($payload['length'])->get(),
        ];
    }

    public function suggestionAction($payload)
    {
        $return = [
            'data'   => trans('labels.common_title.something_wrong_try_again'),
            'status' => 0,
        ];
        $user      = auth()->user();
        $company   = $user->company()->first();
        $logRecord = ZcSurveyReviewSuggestionLog::where(['company_id' => $company->id, 'suggestion_id' => $this->id])->first();
        if (!is_null($logRecord)) {
            if ($logRecord->is_favorite == 1) {
                $logRecord->is_favorite = 0;
                $return['status']       = 1;
                $return['data']         = "Suggestion has been removed from favorite successfully.";
            } elseif ($logRecord->is_favorite == 0) {
                $logRecord->is_favorite = 1;
                $return['status']       = 1;
                $return['data']         = "Suggestion has been marked as favorite successfully.";
            }
            $logRecord->save();
        } else {
            $inserted = ZcSurveyReviewSuggestionLog::create(['company_id' => $company->id, 'suggestion_id' => $this->id, 'is_favorite' => 1]);
            if (!is_null($inserted)) {
                $return['status'] = 1;
                $return['data']   = "Suggestion has been marked as favorite successfully.";
            } else {
                $return['status'] = 0;
                $return['data']   = "Failed to complete action on suggestion! Please try again.";
            }
        }
        return $return;
    }
}
