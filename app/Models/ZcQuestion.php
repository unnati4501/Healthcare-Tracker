<?php

namespace App\Models;

use App\Models\ZcQuestionType;
use Auth;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Yajra\DataTables\Facades\DataTables;

class ZcQuestion extends Model implements HasMedia
{
    use InteractsWithMedia;
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'zc_questions';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = [
        'category_id',
        'sub_category_id',
        'question_type_id',
        'title',
        'status',
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
    protected $dates = [];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['question_logo'];

    /**
     * @return BelongsTo
     */
    public function category()
    {
        return $this->belongsTo('App\Models\SurveyCategory', 'category_id');
    }

    /**
     * @return BelongsTo
     */
    public function subcategory()
    {
        return $this->belongsTo('App\Models\SurveySubCategory', 'sub_category_id');
    }

    /**
     * @return BelongsTo
     */
    public function questiontype()
    {
        return $this->belongsTo('App\Models\ZcQuestionType', 'question_type_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function questionoptions()
    {
        return $this->hasMany('App\Models\ZcQuestionOption', 'question_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function surveyquestions()
    {
        return $this->hasMany('App\Models\ZcSurveyQuestion', 'question_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function questionResponses()
    {
        return $this->hasMany('App\Models\ZcSurveyResponse', 'question_id', 'id');
    }

    /**
     * @return string
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversion
     */
    public function getQuestionLogoAttribute()
    {
        return $this->getLogo(['w' => 900, 'h' => 450]);
    }

    /**
     * @param string $params
     *
     * @return string
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversion
     */
    public function getLogo(array $params): string
    {
        $media = $this->getFirstMedia('logo');
        if (!is_null($media) && $media->count() > 0) {
            $params['src'] = $this->getFirstMediaUrl('logo');
        }
        return getThumbURL($params, 'question', 'logo');
    }

    /**
     * Get data table data fro question listing.
     * @param $payload
     * @return mixed
     * @throws \Exception
     */
    public function getTableData($payload)
    {
        $list = $this->getQuestionBankList($payload);

        return DataTables::of($list['record'])
            ->addIndexColumn()
            ->skipPaging()
            ->with([
                "recordsTotal"    => $list['total'],
                "recordsFiltered" => $list['total'],
            ])
            ->addColumn('images', function ($record) {
                $hasMedia = $record->getFirstMediaUrl('logo');
                return !empty($hasMedia) ? '<i class="fal fa-check-circle text-success fa-lg"></i>' : '<i class="fal fa-times-circle text-danger fa-lg"></i>';
            })
            ->addColumn('question_status', function ($record) {
                if (access()->allow('update-question-bank')) {
                    if ($record->status == '0') {
                        return '<a class="btn btn-primary badge-btn" data-action="review" data-id="' . $record->id . '" href="javascript:void(0);" id="reviewQuestion" statusId="' . $record->status . '">Review</a>';
                    } elseif ($record->status == '1') {
                        if ($record->surveyquestions()->count() > 0) {
                            return "<span class='text-success'>Published</span>";
                        } else {
                            return '<a class="btn btn-outline-secondary badge-btn" data-action="unpublish" data-id="' . $record->id . '" href="javascript:void(0);" statusId="' . $record->status . '" id="unpublishQuestion">Unpublish</a>';
                        }
                    } elseif ($record->status == '2') {
                        return '<a class="btn btn-primary badge-btn" data-action="publish" data-id="' . $record->id . '" href="javascript:void(0);" id="publishQuestion" statusId="' . $record->status . '">Publish</a>';
                    } else {
                        return '<a class="btn btn-primary badge-btn" data-action="review" data-id="' . $record->id . '" href="javascript:void(0);" id="reviewQuestion" statusId="' . $record->status . '">Review</a>';
                    }
                } else {
                    if ($record->status == 1) {
                        return "<span class='text-success'>Published</span>";
                    } else {
                        return "<span class='text-secondary'>Draft</span>";
                    }
                }
            })
            ->addColumn('actions', function ($record) {
                return view('admin.zcquestionbank.listaction', compact('record'))->render();
            })
            ->rawColumns(['images', 'question_status', 'actions'])
            ->make(true);
    }

    /**
     * Get question list for question bank
     *
     * @method GET
     * @param array $payload
     * @return mixed
     */
    public function getQuestionBankList($payload = [])
    {

        $query = $this
            ->select(
                'zc_questions.id',
                'zc_questions.title AS question',
                'zc_questions.status',
                'zc_questions.updated_at',
                'zc_categories.display_name AS category',
                'zc_sub_categories.display_name AS subcategory',
                'zc_question_types.display_name AS question_type',
                \DB::raw("IFNULL(media.id, 0) AS has_media")
            )
            ->join('zc_categories', 'zc_categories.id', '=', 'zc_questions.category_id')
            ->join('zc_sub_categories', 'zc_sub_categories.id', '=', 'zc_questions.sub_category_id')
            ->join('zc_question_types', 'zc_question_types.id', '=', 'zc_questions.question_type_id')
            ->leftJoin('media', function ($join) {
                $join
                    ->on('media.model_id', '=', 'zc_questions.id')
                    ->where('media.model_type', 'like', '%ZcQuestion')
                    ->where('media.collection_name', '=', 'logo');
            })
            ->when(($payload['category'] ?? null), function ($query, $category) {
                $query->where('zc_questions.category_id', $category);
            })
            ->when(($payload['subcategories'] ?? null), function ($query, $subcategories) {
                $query->where('zc_questions.sub_category_id', $subcategories);
            })
            ->when(($payload['question'] ?? null), function ($query, $question) {
                $query->where(function ($subquery) use ($question) {
                    $subquery->orWhere('title', 'like', '%' . $question . '%');
                });
            })
            ->when(($payload['question_type'] ?? null), function ($query, $questionType) {
                $query->where('zc_questions.question_type_id', $questionType);
            })
            ->when(($payload['with_image'] ?? null), function ($query, $withImage) {
                if ($withImage == "yes") {
                    $query->having('has_media', '>', 0);
                } elseif ($withImage == "no") {
                    $query->having('has_media', 0);
                }
            })
            ->groupBy('zc_questions.id');

        if (in_array('question_status', array_keys($payload)) && $payload['question_status'] != null) {
            $questionStatus = ($payload['question_status'] == 1 || $payload['question_status'] == 0 || $payload['question_status'] == 2) ? $payload['question_status'] : null;
            if (null != $questionStatus) {
                $query->where('zc_questions.status', '=', $questionStatus);
            }
        }

        if (isset($payload['order'])) {
            $column = $payload['columns'][$payload['order'][0]['column']]['data'];
            $order  = $payload['order'][0]['dir'];
            $query->orderBy($column, $order);
        } else {
            $query->orderByDesc('zc_questions.updated_at');
        }

        return [
            'total'  => $query->get()->count(),
            'record' => $query->offset($payload['start'])->limit($payload['length'])->get(),
        ];
    }

    /**
     * @param $payLoad
     * @param null $id
     * @param array $options
     * @return array
     * @throws \Exception
     */
    public function freeTextQuestionStoreInsertUpdate($payLoad, $id = null, array $options = [])
    {
        $isEdit = false;
        if (isset($id) && !empty($id)) {
            $isEdit = true;
        }
        $now           = Carbon::now();
        $questionTypes = ZcQuestionType::all()->pluck('id', 'name')->toArray();

        $createBulkData = [];
        if ($isEdit) {
            $questionMainObject = $this->find($id);
            $questionType       = strtolower($payLoad['question_type']);
            $questionTypeId     = $questionTypes[$questionType];
            $payloadUpdate      = [
                'category_id'      => (isset($payLoad['category']) && !empty($payLoad['category'])) ? $payLoad['category'] : 0,
                'sub_category_id'  => (isset($payLoad['subcategories']) && !empty($payLoad['subcategories'])) ? $payLoad['subcategories'] : 0,
                'question_type_id' => (isset($questionTypeId) && !empty($questionTypeId)) ? $questionTypeId : 0,
                'title'            => $payLoad['question'][1],
                'updated_at'       => $now,
            ];

            $questionMainUpdatedObject = $questionMainObject->update($payloadUpdate);

            $imageObj = !empty($payLoad['question_image-free-text']) ? $payLoad['question_image-free-text'][1] : [];

            if (isset($imageObj) && !empty($imageObj)) {
                $name = $questionMainObject->id . '_' . \time();
                $questionMainObject->clearMediaCollection('logo')
                    ->addMedia($imageObj)
                    ->usingName($name)
                    ->usingFileName($name . '.' . $imageObj->extension())
                    ->toMediaCollection('logo', config('medialibrary.disk_name'));
            }

            if (null !== $questionMainObject && is_object($questionMainObject) && $questionMainObject->id) {
                // Insert meta item.
                $questionMeta[] = [
                    'question_id' => $questionMainObject->id,
                    'score'       => 0,
                    'choice'      => 'meta',
                    'updated_at'  => $now,
                    'created_at'  => $now,
                ];

                $questionOptions = [];

                $updateBulkData = array_merge($questionOptions, $questionMeta);
                $questionMainObject->questionoptions()->delete();
                $questionMetaObject                    = $this->questionoptions()->insert($updateBulkData);
                $updateBulkData[1]['question']         = $questionMainUpdatedObject;
                $updateBulkData[1]['question_options'] = $questionMetaObject;
            } else {
                $updateBulkData[1]['question']         = null;
                $updateBulkData[1]['question_options'] = null;
            }

            return $updateBulkData;
        } else {
            // For insert
            foreach ($payLoad['question'] as $questionIndex => $question) {
                $questionType   = strtolower($payLoad['question_type']);
                $questionTypeId = $questionTypes[$questionType];
                $payloadCreate  = [
                    'category_id'      => (isset($payLoad['category']) && !empty($payLoad['category'])) ? $payLoad['category'] : 0,
                    'sub_category_id'  => (isset($payLoad['subcategories']) && !empty($payLoad['subcategories'])) ? $payLoad['subcategories'] : 0,
                    'question_type_id' => (isset($questionTypeId) && !empty($questionTypeId)) ? $questionTypeId : 0,
                    'title'            => $question,
                    'status'           => '0',
                    'created_at'       => $now,
                    'updated_at'       => $now,
                ];

                $questionObject = $this->create($payloadCreate);

                $imageObj = !empty($payLoad['question_image-free-text']) ? $payLoad['question_image-free-text'][$questionIndex] : [];

                if (isset($imageObj) && !empty($imageObj)) {
                    $name = $questionObject->id . '_' . \time();
                    $questionObject->clearMediaCollection('logo')
                        ->addMedia($imageObj)
                        ->usingName($name)
                        ->usingFileName($name . '.' . $imageObj->extension())
                        ->toMediaCollection('logo', config('medialibrary.disk_name'));
                }

                // Associate question option(s)
                if (null !== $questionObject && is_object($questionObject) && $questionObject->id) {
                    // Insert meta item.
                    $questionOptions = [];
                    $questionMeta    = [
                        'question_id' => $questionObject->id,
                        'score'       => 0,
                        'choice'      => 'meta',
                        'created_at'  => $now,
                        'updated_at'  => $now,
                    ];

                    $questionMetaObject                                 = $this->questionoptions()->insert($questionMeta);
                    $createBulkData[$questionIndex]['question']         = $questionObject;
                    $createBulkData[$questionIndex]['question_options'] = $questionMetaObject;
                } else {
                    $createBulkData[$questionIndex]['question']         = null;
                    $createBulkData[$questionIndex]['question_options'] = null;
                }
            }

            return $createBulkData;
        }
    }

    /**
     * @param $payLoad
     * @param null $id
     * @param array $options
     * @return array
     * @throws \Exception
     */
    public function choiceQuestionStoreInsertUpdate($payLoad, $id = null, array $options = [])
    {
        $isEdit = false;
        if (isset($id) && !empty($id)) {
            $isEdit = true;
        }
        $now           = Carbon::now();
        $questionTypes = ZcQuestionType::all()->pluck('id', 'name')->toArray();

        if ($isEdit) {
            $questionMainObject = $this->find($id);
            foreach ($payLoad['question'] as $questionIndex => $question) {
                $questionType   = strtolower($payLoad['question_type']);
                $questionTypeId = $questionTypes[$questionType];

                $payloadUpdate = [
                    'category_id'      => (isset($payLoad['category']) && !empty($payLoad['category'])) ? $payLoad['category'] : 0,
                    'sub_category_id'  => (isset($payLoad['subcategories']) && !empty($payLoad['subcategories'])) ? $payLoad['subcategories'] : 0,
                    'question_type_id' => (isset($questionTypeId) && !empty($questionTypeId)) ? $questionTypeId : 0,
                    'title'            => $question,
                    'updated_at'       => $now,
                ];

                $questionObject = $questionMainObject->update($payloadUpdate);

                $imageObj = !empty($payLoad['question_image-choice']) ? $payLoad['question_image-choice'][1] : [];

                if (isset($imageObj) && !empty($imageObj)) {
                    $name = $questionMainObject->id . '_' . \time();
                    $questionMainObject->clearMediaCollection('logo')
                        ->addMedia($imageObj)
                        ->usingName($name)
                        ->usingFileName($name . '.' . $imageObj->extension())
                        ->toMediaCollection('logo', config('medialibrary.disk_name'));
                }

                // Associate question option(s)
                if (null !== $questionMainObject && is_object($questionMainObject) && $questionMainObject->id) {
                    $oldQuestionOptions = $questionMainObject->questionoptions()
                        ->get()
                        ->where('choice', '!=', 'meta')
                        ->pluck('choice', 'id')
                        ->toArray();

                    foreach ($payLoad['image'][$questionIndex] as $key => $value) {
                        if (!is_null($value['optionId'])) {
                            if (isset($oldQuestionOptions[$value['optionId']])) {
                                unset($oldQuestionOptions[$value['optionId']]);
                            }

                            $questionOptionObject = $questionMainObject->questionoptions()->find($value['optionId']);

                            $questionOption = [
                                'question_id' => $questionMainObject->id,
                                'score'       => (isset($payLoad['score'][$questionIndex][$key]) && !empty($payLoad['score'][$questionIndex][$key])) ? $payLoad['score'][$questionIndex][$key] : 0,
                                'choice'      => (isset($payLoad['choice'][$questionIndex][$key]) && !empty($payLoad['choice'][$questionIndex][$key])) ? $payLoad['choice'][$questionIndex][$key] : 0,
                                'created_at'  => $now,
                                'updated_at'  => $now,
                            ];

                            $questionOptionObject->update($questionOption);

                            if (isset($value['imageId']) && !empty($value['imageId'])) {
                                $name = $questionOptionObject->id . '_' . \time();
                                $questionOptionObject->clearMediaCollection('logo')
                                    ->addMedia($value['imageId'])
                                    ->usingName($name)
                                    ->usingFileName($name . '.' . $value['imageId']->extension())
                                    ->toMediaCollection('logo', config('medialibrary.disk_name'));
                            }
                        } else {
                            $questionOption = [
                                'question_id' => $questionMainObject->id,
                                'score'       => (isset($payLoad['score'][$questionIndex][$key]) && !empty($payLoad['score'][$questionIndex][$key])) ? $payLoad['score'][$questionIndex][$key] : 0,
                                'choice'      => (isset($payLoad['choice'][$questionIndex][$key]) && !empty($payLoad['choice'][$questionIndex][$key])) ? $payLoad['choice'][$questionIndex][$key] : 0,
                                'created_at'  => $now,
                                'updated_at'  => $now,
                            ];

                            $questionOptionObject = $questionMainObject->questionoptions()->create($questionOption);

                            if (isset($value['imageId']) && !empty($value['imageId'])) {
                                $name = $questionOptionObject->id . '_' . \time();
                                $questionOptionObject->clearMediaCollection('logo')
                                    ->addMedia($value['imageId'])
                                    ->usingName($name)
                                    ->usingFileName($name . '.' . $value['imageId']->extension())
                                    ->toMediaCollection('logo', config('medialibrary.disk_name'));
                            }
                        }
                    }

                    $oldQuestionOptions = array_values(array_flip($oldQuestionOptions));

                    $questionMainObject->questionoptions()
                        ->whereIn('zc_questions_options.id', $oldQuestionOptions)
                        ->delete();

                    $updateBulkData[$questionIndex]['question']         = $questionObject;
                    $updateBulkData[$questionIndex]['question_options'] = true;
                } else {
                    $updateBulkData[$questionIndex]['question']         = null;
                    $updateBulkData[$questionIndex]['question_options'] = null;
                }
            }

            return $updateBulkData;
        } else {
            // Create..
            foreach ($payLoad['question'] as $questionIndex => $question) {
                $questionType   = strtolower($payLoad['question_type']);
                $questionTypeId = $questionTypes[$questionType];

                $payloadCreate = [
                    'category_id'      => (isset($payLoad['category']) && !empty($payLoad['category'])) ? $payLoad['category'] : 0,
                    'sub_category_id'  => (isset($payLoad['subcategories']) && !empty($payLoad['subcategories'])) ? $payLoad['subcategories'] : 0,
                    'question_type_id' => (isset($questionTypeId) && !empty($questionTypeId)) ? $questionTypeId : 0,
                    'title'            => $question,
                    'status'           => '0',
                    'created_at'       => $now,
                    'updated_at'       => $now,
                ];

                $questionObject = $this->create($payloadCreate);

                $imageObj = !empty($payLoad['question_image-choice']) ? $payLoad['question_image-choice'][$questionIndex] : [];

                if (isset($imageObj) && !empty($imageObj)) {
                    $name = $questionObject->id . '_' . \time();
                    $questionObject->clearMediaCollection('logo')
                        ->addMedia($imageObj)
                        ->usingName($name)
                        ->usingFileName($name . '.' . $imageObj->extension())
                        ->toMediaCollection('logo', config('medialibrary.disk_name'));
                }

                // Associate question option(s)
                if (null !== $questionObject && is_object($questionObject) && $questionObject->id) {
                    // Insert meta item.
                    $questionMeta = [
                        'question_id' => $questionObject->id,
                        'score'       => 0,
                        'choice'      => 'meta',
                        'created_at'  => $now,
                        'updated_at'  => $now,
                    ];

                    $score           = 0;
                    $questionOptions = [];

                    $questionObject->questionoptions()->create($questionMeta);

                    foreach ($payLoad['choice'][$questionIndex] as $increment => $choice) {
                        ++$score;
                        $questionOptions = [
                            'question_id' => $questionObject->id,
                            'score'       => (isset($payLoad['score'][$questionIndex][$increment]) && !empty($payLoad['score'][$questionIndex][$increment])) ? $payLoad['score'][$questionIndex][$increment] : 0,
                            'choice'      => $choice,
                            'created_at'  => $now,
                            'updated_at'  => $now,
                        ];

                        $questionOptionObject = $questionObject->questionoptions()->create($questionOptions);

                        $imageChoiceObj = $payLoad['image'][$questionIndex][$increment];

                        if (isset($imageChoiceObj) && !empty($imageChoiceObj['imageId'])) {
                            $name = $questionOptionObject->id . '_' . \time();
                            $questionOptionObject->clearMediaCollection('logo')
                                ->addMedia($imageChoiceObj['imageId'])
                                ->usingName($name)
                                ->usingFileName($name . '.' . $imageChoiceObj['imageId']->extension())
                                ->toMediaCollection('logo', config('medialibrary.disk_name'));
                        }
                    }

                    $createBulkData[$questionIndex]['question']         = $questionObject;
                    $createBulkData[$questionIndex]['question_options'] = true;
                } else {
                    $createBulkData[$questionIndex]['question']         = null;
                    $createBulkData[$questionIndex]['question_options'] = null;
                }
            }
            return $createBulkData;
        }
    }

    /**
     * delete record by record id.
     *
     * @param $id
     * @return array
     */
    public function deleteRecord()
    {
        $this->clearMediaCollection('logo');

        foreach ($this->questionoptions()->get() as $value) {
            $value->clearMediaCollection('logo');
        }

        if ($this->delete()) {
            return array('deleted' => 'true');
        }
        return array('deleted' => 'error');
    }

    /**
     * @param none
     * @return void|null
     */
    public function getQuestionOptions()
    {
        $questionTypeName = $this->questiontype->name;
        $questionOptions  = $this->questionoptions;
        switch ($questionTypeName) {
            case 'free-text':
                return $this->freeTextOptions($this);
                break;
            case 'choice':
                return $this->choiceOptions($this, $questionOptions);
                break;
            default:
                return null;
                break;
        }
        return false;
    }

    /**
     * @param $question
     * @param $questionOptions
     * @return array
     */
    public function freeTextOptions($question)
    {
        $defaultImage = asset('assets/dist/img/73a90acaae2b1ccc0e969709665bc62f.png');

        if (!empty($question->getFirstMediaUrl('logo'))) {
            $imageUrl = $question->question_logo;
            $imageId  = 0;
        } else {
            $imageUrl = $defaultImage;
            $imageId  = 0;
        }

        return [
            'imageId'  => $imageId,
            'imageUrl' => $imageUrl,
        ];
    }

    /**
     * @param $question
     * @param $questionOptions
     * @return array
     */
    public function choiceOptions($question, $questionOptions)
    {
        $images       = [];
        $defaultImage = asset('assets/dist/img/73a90acaae2b1ccc0e969709665bc62f.png');

        foreach ($questionOptions as $index => $questionOption) {
            if (isset($questionOption->option_logo)) {
                $imageUrl = $questionOption->option_logo;
                $imageId  = 0;
                $optionId = $questionOption->id;
            } else {
                $imageUrl = $defaultImage;
                $imageId  = 0;
                $optionId = $questionOption->id;
            }

            if (Str::contains($imageUrl, '73a90acaae2b1ccc0e969709665bc62f')) {
                $imageUrl = null;
            }

            $choice = $questionOption->choice;
            $score  = $questionOption->score;
            if ($choice == 'meta' && $score == 0) {
                if (!empty($question->getFirstMediaUrl('logo'))) {
                    $images['meta'] = [
                        'imageId'  => isset($imageId) ? $imageId : 0,
                        'imageUrl' => $question->question_logo,
                    ];
                } else {
                    $images['meta'] = [
                        'imageId'  => isset($imageId) ? $imageId : 0,
                        'imageUrl' => $defaultImage,
                    ];
                }
            } else {
                $xDeviceOs      = strtolower(request()->header('X-Device-Os', ""));
                $staticImageURL = ($xDeviceOs != config('zevolifesettings.PORTAL')) ? asset('assets/dist/img/choice-' . $index . '.png') : null;

                $images['score'][$index] = [
                    'imageId'  => $imageId,
                    'optionId' => $optionId,
                    'imageUrl' => isset($imageUrl) ? $imageUrl : $staticImageURL,
                    'choice'   => $choice,
                    'score'    => $score,
                ];
            }
        }

        return $images;
    }

    /**
     * Get datatable data for free text answers
     * @param $payload
     * @return mixed
     * @throws \Exception
     */
    public function getFreeTextAnswersTableData($payload)
    {
        $list = $this->getFreeTextAnswersList($payload);
        return DataTables::of($list['record'])
            ->addIndexColumn()
            ->skipPaging()
            ->with([
                "recordsTotal"    => $list['total'],
                "recordsFiltered" => $list['total'],
            ])
            ->make(true);
    }

    /**
     * Get free text answers list for free text review
     *
     * @method GET
     * @param array $payload
     * @return mixed
     */
    public function getFreeTextAnswersList($payload = [])
    {
        $user     = auth()->user();
        $timezone = !empty($user->timezone) ? $user->timezone : config('app.timezone');
        $query    = $this
            ->questionResponses()
            ->select('zc_survey_responses.id', 'answer_value AS answers', 'companies.name AS company_name')
            ->join('companies', function ($join) {
                $join->on('companies.id', '=', 'zc_survey_responses.company_id');
            });

        if (!empty($payload['company'])) {
            $query->where('zc_survey_responses.company_id', $payload['company']);
        }

        if (!empty($payload['from']) && !empty($payload['to'])) {
            $startDate = Carbon::parse($payload['from'], $timezone)->timezone($timezone)->toDateTimeString();
            $endDate   = Carbon::parse($payload['to'], $timezone)->timezone($timezone)->format('Y-m-d 23:59:59');
            $query->whereRaw("CONVERT_TZ(zc_survey_responses.created_at, ?, ?) BETWEEN ? AND ?", ['UTC', $timezone, $startDate, $endDate]);
        }

        if (isset($payload['order'])) {
            $column = $payload['columns'][$payload['order'][0]['column']]['data'];
            $order  = $payload['order'][0]['dir'];

            $query->orderBy($column, $order);
        } else {
            $query->orderByDesc('zc_survey_responses.created_at');
        }

        return [
            'total'  => $query->get()->count(),
            'record' => $query->offset($payload['start'])->limit($payload['length'])->get(),
        ];
    }

    /**
     * @param string $collection
     * @param array $param
     *
     * @return array
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversion
     */
    public function getMediaData(string $collection, array $param): array
    {
        $return = [
            'width'  => $param['w'],
            'height' => $param['h'],
        ];
        $media = $this->getFirstMedia($collection);

        if (!is_null($media) && $media->count() > 1) {
            $param['src'] = $this->getFirstMediaUrl($collection, ($param['conversion'] ?? ''));
        }
        $return['url'] = getThumbURL($param, 'app_setting', $collection);
        return $return;
    }
}
