<?php

namespace App\Models;

use App\Models\ZcQuestion;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Yajra\DataTables\Facades\DataTables;

class ZcSurvey extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'zc_survey';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title',
        'description',
        'status',
        'is_premium',
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
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function surveyCompany(): HasMany
    {
        return $this->hasMany('App\Models\ZcSurveySettings', 'survey_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function surveyQuestions(): HasMany
    {
        return $this->hasMany('App\Models\ZcSurveyQuestion', 'survey_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function surveyReponses(): HasMany
    {
        return $this->hasMany('App\Models\ZcSurveyResponse', 'survey_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function surveyLogs(): HasMany
    {
        return $this->hasMany('App\Models\ZcSurveyLog', 'survey_id', 'id');
    }

    /**
     * Set datatable for record list.
     *
     * @param payload
     * @return dataTable
     */

    public function getTableData($payload)
    {
        $list = $this->getRecordList($payload);
        return DataTables::of($list['record'])
            ->skipPaging()
            ->with([
                "recordsTotal"    => $list['total'],
                "recordsFiltered" => $list['total'],
            ])
            ->addColumn('title', function ($record) {
                $title = trim(html_entity_decode(strip_tags($record->title)), " \t\n\r\0\x0B\xC2\xA0");
                $title = mb_strimwidth($title, 0, 50, '...');

                if (access()->allow('view-survey')) {
                    return "<a href='" . route('admin.zcsurvey.view', $record->id) . "' title='View survey'>" . $title . "</a>";
                } else {
                    return $title;
                }
            })
            ->addColumn('description', function ($record) {
                if (empty($record->description)) {
                    return 'N/A';
                } else {
                    $direction = trim(html_entity_decode(strip_tags($record->description)), " \t\n\r\0\x0B\xC2\xA0");
                    return mb_strimwidth($direction, 0, 50, '...');
                }
            })
            ->addColumn('status', function ($record) {
                $class = (($record->status == 'Assigned') ? 'text-success' : (($record->status == 'Published') ? 'text-primary' : 'text-muted'));
                return "<span class='{$class}'>{$record->status}</span>";
            })
            ->addColumn('actions', function ($record) {
                return view('admin.zcsurvey.listaction', compact('record'))->render();
            })
            ->rawColumns(['title', 'status', 'actions'])
            ->make(true);
    }

    public function getRecordList($payload)
    {
        $query = $this
            ->leftJoin('zc_survey_log', function ($join) {
                $join->on('zc_survey_log.survey_id', '=', 'zc_survey.id');
            })
            ->leftJoin('zc_survey_user_log', function ($join) {
                $join->on('zc_survey_user_log.survey_log_id', '=', 'zc_survey_log.id');
            })
            ->select('zc_survey.*')
            ->selectRaw('COUNT(CASE WHEN zc_survey_user_log.survey_submitted_at IS NOT NULL THEN 1 END) AS surveyreponses_count')
            ->withCount(['surveyquestions', 'surveylogs'])
            ->groupBy('zc_survey.id');

        if (!empty($payload['survey_title'])) {
            $query->whereRaw('LOWER(zc_survey.title) like ?', ['%' . strtolower($payload['survey_title']) . '%']);
        }

        if (in_array('survey_status', array_keys($payload)) && !empty($payload['survey_status'])) {
            $query->where('zc_survey.status', $payload['survey_status']);
        }

        if (isset($payload['order'])) {
            $column = $payload['columns'][$payload['order'][0]['column']]['data'];
            $order  = $payload['order'][0]['dir'];
            $query->orderBy($column, $order);
        } else {
            $query->orderByDesc('zc_survey.id');
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

    public function getQuestionsTableData($payload, $survey = null)
    {
        $list = $this->getQuestions($payload, $survey);
        return DataTables::of($list['record'])
            ->skipPaging()
            ->addColumn('title', function ($record) {
                return $record->title ?? $record->question->title;
            })
            ->addColumn('checkbox', function ($record) {
                return "<div class='check-row-box' data-id='{$record->id}'>
                    <i class='fal fa-check'>
                    </i>
                </div>";
            })
            ->addColumn('is_premium', function ($record) {
                $return = "";
                if ($record->subcat_is_primum) {
                    $return = "<i class='fas fa-star text-warning'></i>";
                }
                return $return;
            })
            ->with([
                "recordsTotal"    => $list['total'],
                "recordsFiltered" => $list['total'],
            ])
            ->rawColumns(['checkbox', 'is_premium'])
            ->make(true);
    }

    public function getQuestions($payload, $survey)
    {
        $questions = ZcQuestion::select('zc_questions.*', 'zc_categories.display_name AS category_name', 'zc_sub_categories.display_name AS subcategory_name', 'zc_sub_categories.is_primum AS subcat_is_primum', 'zc_question_types.display_name AS questiontype_name')
            ->join('zc_categories', function ($join) {
                $join->on('zc_categories.id', '=', 'zc_questions.category_id');
            })
            ->join('zc_sub_categories', function ($join) {
                $join->on('zc_sub_categories.id', '=', 'zc_questions.sub_category_id');
            })
            ->join('zc_question_types', function ($join) {
                $join->on('zc_question_types.id', '=', 'zc_questions.question_type_id');
            })
            ->where('zc_questions.status', '1')
            ->groupBy('zc_questions.id');

        if (!is_null($survey) && $survey instanceof ZcSurvey) {
            $questions
                ->join('zc_survey_questions', function ($join) use ($survey) {
                    $join
                        ->on('zc_survey_questions.question_id', '=', 'zc_questions.id')
                        ->where('zc_survey_questions.survey_id', $survey->id);
                })
                ->addSelect('zc_survey_questions.order_priority', 'zc_survey_questions.created_at AS question_created_at')
                ->orderBy('zc_survey_questions.order_priority');
            return [
                'total'  => $questions->get()->count(),
                'record' => $questions->get(),
            ];
        } else {
            if (!empty($payload['question_category'])) {
                $questions->where('zc_questions.category_id', $payload['question_category']);
            }
            if (!empty($payload['question_subcategory'])) {
                $questions->where('zc_questions.sub_category_id', $payload['question_subcategory']);
            }
            if (!empty($payload['question_search'])) {
                $questions->whereRaw('LOWER(zc_questions.title) like ?', ['%' . strtolower($payload['question_search']) . '%']);
            }

            if (isset($payload['order'])) {
                $column = $payload['columns'][$payload['order'][0]['column']]['data'];
                $order  = $payload['order'][0]['dir'];
                $questions->orderBy($column, $order);
            } else {
                $questions->orderByDesc('zc_questions.id');
            }

            return [
                'total'  => $questions->get()->count(),
                'record' => $questions->offset($payload['start'])->limit($payload['length'])->get(),
            ];
        }
    }

    /**
     * store record data.
     *
     * @param payload
     * @return boolean
     */

    public function storeEntity($payload)
    {
        $finalQuestionsArray = [];
        $order_priority      = 1;
        $survey              = $this->create([
            'title'       => $payload['title'],
            'description' => $payload['description'],
            'is_premium'  => (int) $payload['is_premium'],
        ]);

        foreach ($payload['questions'] as $key => $question) {
            $finalQuestionsArray[] = [
                "survey_id"        => $survey->id,
                "category_id"      => $payload['category'][$key],
                "sub_category_id"  => $payload['subcategory'][$key],
                "question_id"      => $question,
                "question_type_id" => $payload['questions_type'][$key],
                "order_priority"   => $order_priority,
            ];
            $order_priority++;
        }

        if ($survey) {
            $survey->surveyQuestions()->createMany($finalQuestionsArray);
            return true;
        }

        return false;
    }

    /**
     * update record data.
     *
     * @param $payload
     * @return boolean
     */
    public function updateEntity($payload)
    {
        $newQuestionsArray = [];
        $oldQs             = $this->surveyquestions()->pluck('question_id')->toArray();
        $sequence          = array_values($payload['questions']);
        $removeIds         = array_diff($oldQs, $payload['questions']);
        $addIds            = array_diff($payload['questions'], $oldQs);

        $updated = $this->update([
            'title'       => $payload['title'],
            'description' => $payload['description'],
            'is_premium'  => (int) $payload['is_premium'],
        ]);

        if (count($removeIds) > 0) {
            $this->surveyQuestions()->whereIn('question_id', $removeIds)->delete();
        }

        if (count($addIds) > 0) {
            foreach ($addIds as $questionId) {
                $newQuestionsArray[] = [
                    "survey_id"        => $this->id,
                    "category_id"      => $payload['category'][$questionId],
                    "sub_category_id"  => $payload['subcategory'][$questionId],
                    "question_id"      => $payload['questions'][$questionId],
                    "question_type_id" => $payload['questions_type'][$questionId],
                ];
            }
            $this->surveyQuestions()->createMany($newQuestionsArray);
        }

        foreach ($sequence as $seq => $question) {
            $this
                ->surveyQuestions()
                ->where(['question_id' => $question])
                ->update(['order_priority' => ($seq + 1)]);
        }

        if ($updated) {
            return true;
        }

        return false;
    }

    /**
     * delete record by record id.
     *
     * @return array
     */
    public function deleteRecord()
    {
        if ($this->delete()) {
            return array('deleted' => true, 'message' => trans('labels.zcsurvey.deleted_success'));
        }
        return array('deleted' => false, 'message' => trans('labels.zcsurvey.deleted_error'));
    }

    /**
     * generate copy of survey from existing
     *
     * @return array
     */
    public function copy()
    {
        $copiedSurvey         = $this->replicate();
        $copiedSurvey->title  = 'Copied:' . time() . ' ' . mb_strimwidth($copiedSurvey->title, 0, 82, '');
        $copiedSurvey->status = "Draft";
        $copiedSurvey->save();

        if ($copiedSurvey->id) {
            $finalQuestionsArray = [];
            $this->surveyquestions->each(function ($question) use (&$finalQuestionsArray, $copiedSurvey) {
                $finalQuestionsArray[] = [
                    "survey_id"        => $copiedSurvey->id,
                    "category_id"      => $question->category_id,
                    "sub_category_id"  => $question->sub_category_id,
                    "question_id"      => $question->question_id,
                    "question_type_id" => $question->question_type_id,
                    "order_priority"   => $question->order_priority,
                ];
            });
            $copiedSurvey->surveyQuestions()->createMany($finalQuestionsArray);
            return true;
        }
        return false;
    }
}
