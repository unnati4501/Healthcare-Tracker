<?php

namespace App\Models;

use App\Models\HsSubCategories;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

/**
 * @property integer $id
 * @property integer $category_id
 * @property integer $sub_category_id
 * @property integer $question_type_id
 * @property string $title
 * @property int $image
 * @property float $max_score
 * @property boolean $status
 * @property string $created_at
 * @property string $updated_at
 * @property HsCategory $hsCategory
 * @property HsQuestionType $hsQuestionType
 * @property HsSubCategory $hsSubCategory
 * @property HsQuestionsOption[] $hsQuestionsOptions
 */
class HsQuestions extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'hs_questions';

    /**
     * @var array
     */
    protected $fillable = [
        'category_id',
        'sub_category_id',
        'question_type_id',
        'title',
        'image',
        'max_score',
        'status',
        'created_at',
        'updated_at',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function hsCategory()
    {
        return $this->belongsTo('App\Models\HsCategories', 'category_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function hsQuestionType()
    {
        return $this->belongsTo('App\Models\HsQuestionType', 'question_type_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function hsSubCategory()
    {
        return $this->belongsTo('App\Models\HsSubCategories', 'sub_category_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function hsQuestionsOptions()
    {
        return $this->hasMany('App\Models\HsQuestionsOption', 'question_id');
    }

    /**
     * Set datatable for question list.
     *
     * @param payload
     * @return dataTable
     */
    public function getTableData($payload)
    {
        $list = $this->getHsQuestionList($payload);

        return DataTables::of($list)
            ->addColumn('updated_at', function ($question) {
                return $question->updated_at;
            })
            ->addColumn('category', function ($question) {
                return $question->hsCategory->display_name;
            })
            ->addColumn('sub_category', function ($question) {
                return $question->hsSubCategory->display_name;
            })
            ->addColumn('question', function ($question) {
                return $question->title;
            })
            ->addColumn('image', function ($question) {
                if (!empty($question->image)) {
                    return '<img class="tbl-user-img img-circle elevation-2" src="' . asset($question->image) . '" width="70" />';
                } else {
                    return '<img class="tbl-user-img img-circle elevation-2" src="' . asset('assets/dist/img/logo.png') . '" width="70" />';
                }
            })
            ->addColumn('question_type', function ($question) {
                return $question->hsQuestionType->display_name;
            })
            ->addColumn('actions', function ($question) {
                if (access()->allow('view-options')) {
                    return '<a class="btn btn-sm btn-outline-primary animated bounceIn slow" href="javaScript:void(0)" title="View question options"
                            data-toggle="modal" data-target="#questionShow" data-id="' . $question->id . '" id="getQuestions">
                            <i aria-hidden="true" class="fa fa-eye">
                            </i>
                        </a>';
                }
            })
            ->rawColumns(['image', 'actions'])
            ->make(true);
    }

    /**
     * get question list for data table list.
     *
     * @param payload
     * @return categoryList
     */
    public function getHsQuestionList($payload)
    {
        $query = self::with(['hsCategory', 'hsSubCategory', 'hsQuestionType'])
            ->orderBy('updated_at', 'DESC');

        if (in_array('question', array_keys($payload)) && !empty($payload['question'])) {
            $query->where('hs_questions.title', 'like', '%' . $payload['question'] . '%');
        }

        if (in_array('category', array_keys($payload)) && !empty($payload['category'])) {
            $query->where('hs_questions.category_id', $payload['category']);
        }

        if (in_array('sub_category', array_keys($payload)) && !empty($payload['sub_category'])) {
            $query->where('hs_questions.sub_category_id', $payload['sub_category']);
        }

        if (in_array('question_type', array_keys($payload)) && !empty($payload['question_type'])) {
            $query->where('hs_questions.question_type_id', $payload['question_type']);
        }

        return $query->get();
    }

    /**
     * @param string $size
     *
     * @return array
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversion
     */
    public function getMediaData(): array
    {
        $return = [];

        $cover = "";
        if (!is_null($this->image)) {
            $cover = $this->image;
        } else {
            $cover = \asset('assets/dist/img/logo.png');
        }

        $return['url']    = $cover;
        $return['width']  = 0;
        $return['height'] = 0;

        return $return;
    }

    public function wellbeingSurveyChartData($payLoad)
    {
        $chartData = [];
        switch ($payLoad['chartType']) {
            case 'healthScoreSurvey':
                $chartData = $this->hsSurveyChartData($payLoad);
                break;
            case 'healthScorePhysicalCatWise':
                $chartData = $this->hsTypewiseChartData($payLoad);
                break;
            case 'healthScorePsychologicalCatWise':
                $chartData = $this->hsTypewiseChartData($payLoad);
                break;
            default:
                $chartData = [];
                break;
        }
        return $chartData;
    }

    public function hsSurveyChartData($payLoad)
    {
        try {
            $hsSurveyChartData = ['labels' => [], 'data' => [], 'baseline' => 0];
            $this->timezone    = (auth()->user()->timezone ?? null);
            $this->timezone    = (!empty($this->timezone) ? $this->timezone : config('app.timezone'));
            $today             = Carbon::parse(now()->toDateTimeString())->setTimeZone($this->timezone);
            $currMonthString   = $today->format('Y_n');
            $today             = $today->subYear()->format('Y-m-d H:i:s');
            $emptyData         = getMonthsbyKeys($today, $this->timezone, true);

            $procedureData = [
                $payLoad['comapnyId'],
                null,
                null,
                null,
            ];
            $userCounts                  = DB::select('CALL sp_healthscore_survey(?, ?, ?, ?);', $procedureData);
            $hsSurveyChartData['counts'] = $userCounts[0];

            $procedureData = [
                'category',
                $this->timezone,
                $today,
                ($payLoad['comapnyId'] ?? null),
                null,
                null,
                ($payLoad['category_id'] ?? null),
                null,
                null,
                null,
            ];
            $chartData = DB::select('CALL sp_healthscore_survey_backend(?, ?, ?, ?, ?, ?, ?, ?, ?, ?);', $procedureData);
            if (!empty($chartData)) {
                $chartData = Collect($chartData)->pluck('scorePercentage', 'log_month_year')->toArray();
                if (!isset($chartData[$currMonthString])) {
                    unset($emptyData[$currMonthString]);
                }
                foreach ($emptyData as $key => $value) {
                    if (isset($chartData[$key]) || is_numeric(array_key_last($hsSurveyChartData['data']))) {
                        array_push($hsSurveyChartData['labels'], $emptyData[$key]);
                        array_push($hsSurveyChartData['data'], (isset($chartData[$key]) ? (($chartData[$key] > 0) ? $chartData[$key] : 0) : end($hsSurveyChartData['data'])));
                    }
                }
            }

            $procedureData = [
                'category',
                $this->timezone,
                ($payLoad['comapnyId'] ?? null),
                null,
                ($payLoad['category_id'] ?? null),
                null,
                null,
                null,
            ];
            $chartData = DB::select('CALL sp_healthscore_baseline(?, ?, ?, ?, ?, ?, ?, ?);', $procedureData);
            if (!empty($chartData) && isset($chartData[0])) {
                $hsSurveyChartData['baseline'] = (($chartData[0]->baselinePercentage > 0) ? $chartData[0]->baselinePercentage : 0);
            }

            return $hsSurveyChartData;

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

    public function hsTypewiseChartData($payLoad)
    {
        try {
            $hsTypewiseChartData = ['labels' => [], 'data' => [], 'baseline' => 0];
            $this->timezone      = (auth()->user()->timezone ?? null);
            $this->timezone      = (!empty($this->timezone) ? $this->timezone : config('app.timezone'));
            $today               = Carbon::parse(now()->toDateTimeString())->setTimeZone($this->timezone);
            $currMonthString     = $today->format('Y_n');
            $today               = $today->subYear()->format('Y-m-d H:i:s');
            $emptyData           = getMonthsbyKeys($today, $this->timezone, true);

            $procedureData = [
                'subcategory',
                $this->timezone,
                $today,
                ($payLoad['comapnyId'] ?? null),
                null,
                null,
                ($payLoad['sub_category_id'] ?? null),
                null,
                null,
                null,
            ];
            $chartData = DB::select('CALL sp_healthscore_survey_backend(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', $procedureData);
            if (!empty($chartData)) {
                $chartData = Collect($chartData)->pluck('scorePercentage', 'log_month_year')->toArray();
                if (!isset($chartData[$currMonthString])) {
                    unset($emptyData[$currMonthString]);
                }
                foreach ($emptyData as $key => $value) {
                    if (isset($chartData[$key]) || is_numeric(array_key_last($hsTypewiseChartData['data']))) {
                        array_push($hsTypewiseChartData['labels'], $emptyData[$key]);
                        array_push($hsTypewiseChartData['data'], (isset($chartData[$key]) ? (($chartData[$key] > 0) ? $chartData[$key] : 0) : end($hsTypewiseChartData['data'])));
                    }
                }
            }

            $procedureData = [
                'subcategory',
                $this->timezone,
                ($payLoad['comapnyId'] ?? null),
                null,
                ($payLoad['sub_category_id'] ?? null),
                null,
                null,
                null,
            ];
            $chartData = DB::select('CALL sp_healthscore_baseline(?, ?, ?, ?, ?, ?, ?, ?);', $procedureData);
            if (!empty($chartData) && isset($chartData[0])) {
                $hsTypewiseChartData['baseline'] = (($chartData[0]->baselinePercentage > 0) ? $chartData[0]->baselinePercentage : 0);
            }

            return $hsTypewiseChartData;

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
