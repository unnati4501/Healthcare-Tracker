<?php declare (strict_types = 1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SurveyCategory;
use App\Models\SurveySubCategory;
use App\Models\ZcQuestion;
use App\Models\ZcQuestionType;
use Breadcrumbs;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Proengsoft\JsValidation\Facades\JsValidatorFacade;
use Validator;

/**
 * Class ZcQuestionBankController
 *
 * @package App\Http\Controllers\Admin
 */
class ZcQuestionBankController extends Controller
{
    /**
     * variable to store the model object
     * @var surveyCategory
     * @var surveySubCategory
     * @var zcQuestion
     * @var zcQuestionType
     */
    protected $surveyCategory;
    protected $surveySubCategory;
    protected $zcQuestion;
    protected $zcQuestionType;

    /**
     * contructor to initialize model object
     * @param SurveyCategory $surveyCategory, SurveySubCategory $surveySubCategory, ZcQuestion $zcQuestion, ZcQuestionType $zcQuestionType
     */
    public function __construct(SurveyCategory $surveyCategory, SurveySubCategory $surveySubCategory, ZcQuestion $zcQuestion, ZcQuestionType $zcQuestionType)
    {
        $this->surveyCategory    = $surveyCategory;
        $this->surveySubCategory = $surveySubCategory;
        $this->zcQuestion        = $zcQuestion;
        $this->zcQuestionType    = $zcQuestionType;
        $this->bindBreadcrumbs();
    }

    /**
     * bind breadcrumbs of zcquestionbank module
     */
    private function bindBreadcrumbs()
    {
        Breadcrumbs::for('zcquestionbank.index', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push('Question Library');
        });
        Breadcrumbs::for('zcquestionbank.create', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push('Question Library', route('admin.zcquestionbank.index'));
            $trail->push('Add Question');
        });
        Breadcrumbs::for('zcquestionbank.edit', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push('Question Library', route('admin.zcquestionbank.index'));
            $trail->push('Edit Question');
        });
    }

    /**
     * @return View
     */
    public function index(Request $request)
    {
        $role = getUserRole();
        if (!access()->allow('manage-question-bank') || $role->group != 'zevo') {
            abort(403);
        }

        try {
            $data                  = array();
            $data['pagination']    = config('zevolifesettings.datatable.pagination.long');
            $data['categories']    = $this->surveyCategory->get()->pluck('display_name', 'id')->toArray();
            $data['subcategories'] = $this->surveySubCategory->get()->pluck('display_name', 'id')->toArray();
            $data['questionTypes'] = $this->zcQuestionType->get()->pluck('display_name', 'id')->toArray();
            $data['ga_title']      = trans('page_title.zcquestionbank.zcquestionbank_list');
            return \view('admin.zcquestionbank.index', $data);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong_try_again'),
                'status' => 0,
            ];
            return \Redirect::route('admin.zcquestionbank.index')->with('message', $messageData);
        }
    }

    /**
     *
     * @param Request $request
     * @return Datatable
     */
    public function getQuestions(Request $request)
    {
        $role = getUserRole();
        if (!access()->allow('manage-question-bank') || $role->group != 'zevo') {
            return response()->json([
                'message' => trans('labels.common_title.unauthorized_access'),
            ], 422);
        }
        try {
            return $this->zcQuestion->getTableData($request->all());
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
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function create()
    {
        $role = getUserRole();
        if (!access()->allow('create-question-bank') || $role->group != 'zevo') {
            abort(403);
        }

        try {
            $data                  = array();
            $data['categories']    = $this->surveyCategory->get()->pluck('display_name', 'id')->toArray();
            $data['freeTextRules'] = JsValidatorFacade::make(self::questionBankShortAnswerRules());
            $data['choiceRules']   = JsValidatorFacade::make(self::questionBankChoiceRules());
            $data['ga_title']      = trans('page_title.zcquestionbank.create');
            return \view('admin.zcquestionbank.create', $data);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong_try_again'),
                'status' => 0,
            ];
            return \Redirect::route('admin.zcquestionbank.index')->with('message', $messageData);
        }
    }

    /**
     * Validation rules for the questionBankShortAnswerRules
     * @param bool $isEdit
     * @param array $options
     * @return array
     */
    public static function questionBankShortAnswerRules($isEdit = false, $options = [])
    {
        return [
            'question'      => 'required|max:175',
            'category'      => 'required|integer',
            'subcategories' => 'required|integer',
            'question_type' => [
                'required',
                Rule::in('free-text', 'choice'),
            ],
        ];
    }

    /**
     * Validation rules for the questionBankChoiceRules
     * @param bool $isEdit
     * @param array $options
     * @return array
     */
    public static function questionBankChoiceRules($isEdit = false, $options = [])
    {
        return [
            'question'      => 'required|max:175',
            'choice'        => 'required|max:100',
            'score'         => 'required|array',
            'category'      => 'required|integer',
            'subcategories' => 'required|integer',
            'question_type' => [
                'required',
                Rule::in('free-text', 'choice'),
            ],
        ];
    }

    /**
     * @param InsertUpdateQuestionbankRequest $request
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function freeTextQuestionStoreUpdate(Request $request)
    {
        if (is_null($request->id)) {
            if (!access()->allow('create-question-bank')) {
                $messageData = [
                    'data'   => 'This action is unauthorized.',
                    'status' => 0,
                ];
                return response()->json($messageData);
            }
        } else {
            if (!access()->allow('update-question-bank')) {
                $messageData = [
                    'data'   => 'This action is unauthorized.',
                    'status' => 0,
                ];
                return response()->json($messageData);
            }
        }

        $payload      = $request->all();
        $isSaveAndNew = false;

        if (isset($payload['isSaveAndNew']) && $payload['isSaveAndNew'] == 1) {
            $isSaveAndNew = true;
        }

        // Check is valid form-data and valid json data.
        if (isset($payload['form-data']) && !empty($payload['form-data']) && !isValidJson($payload['form-data'])) {
            $messageData = [
                'data'   => 'Form data is invalid.',
                'status' => 0,
            ];
            return response()->json($messageData);
        }

        $rules     = self::questionBankShortAnswerRules();
        $validator = Validator::make($payload, $rules);

        if ($validator->fails()) {
            $messageData = [
                'data'   => implode(',', $validator->getMessageBag()->all()),
                'status' => 0,
            ];
            return response()->json($messageData);
        }

        try {
            DB::beginTransaction();
            if (is_null($request->id)) {
                $this->zcQuestion->freeTextQuestionStoreInsertUpdate($payload);
                $messageData = [
                    'data'   => 'The question was successfully created.',
                    'status' => 1,
                ];
            } else {
                $this->zcQuestion->freeTextQuestionStoreInsertUpdate($payload, $request->id);
                $messageData = [
                    'data'   => 'The question was successfully updated.',
                    'status' => 1,
                ];
            }

            DB::commit();
            if ($isSaveAndNew) {
                return response()->json($messageData);
            }

            $messageData['route'] = route('admin.zcquestionbank.index');
            return response()->json($messageData);
        } catch (\Exception $exception) {
            DB::rollBack();
            report($exception);
            $messageData = [
                'data'   => 'Something went wrong. Please try again.',
                'status' => 0,
            ];
            return response()->json($messageData);
        }
    }

    /**
     * @param InsertUpdateQuestionbankRequest $request
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function choiceQuestionStoreUpdate(Request $request)
    {
        if (is_null($request->id)) {
            if (!access()->allow('create-question-bank')) {
                $messageData = [
                    'data'   => 'This action is unauthorized.',
                    'status' => 0,
                ];
                return response()->json($messageData);
            }
        } else {
            if (!access()->allow('update-question-bank')) {
                $messageData = [
                    'data'   => 'This action is unauthorized.',
                    'status' => 0,
                ];
                return response()->json($messageData);
            }
        }

        $payload      = $request->all();
        $isSaveAndNew = false;

        if (isset($payload['isSaveAndNew']) && $payload['isSaveAndNew'] == 1) {
            $isSaveAndNew = true;
        }

        // Check is valid form-data and valid json data.
        if (isset($payload['form-data']) && !empty($payload['form-data']) && !isValidJson($payload['form-data'])) {
            $messageData = [
                'data'   => 'Form data is invalid.',
                'status' => 0,
            ];
            return response()->json($messageData);
        }

        $rules     = self::questionBankChoiceRules();
        $validator = Validator::make($payload, $rules);

        if ($validator->fails()) {
            $messageData = [
                'data'   => implode(',', $validator->getMessageBag()->all()),
                'status' => 0,
            ];
            return response()->json($messageData);
        }

        try {
            DB::beginTransaction();
            if (is_null($request->id)) {
                $this->zcQuestion->choiceQuestionStoreInsertUpdate($payload);
                $messageData = [
                    'data'   => 'The question was successfully created.',
                    'status' => 1,
                ];
            } else {
                $this->zcQuestion->choiceQuestionStoreInsertUpdate($payload, $request->id);
                $messageData = [
                    'data'   => 'The question was successfully updated.',
                    'status' => 1,
                ];
            }
            DB::commit();
            if ($isSaveAndNew) {
                return response()->json($messageData);
            }

            $messageData['route'] = route('admin.zcquestionbank.index');
            return response()->json($messageData);
        } catch (\Exception $exception) {
            DB::rollBack();
            report($exception);
            $messageData = [
                'data'   => 'Something went wrong. Please try again.',
                'status' => 0,
            ];
            return response()->json($messageData);
        }
    }

    /**
     * @param ZcQuestion $zcQuestion
     * @return View
     */
    public function edit(ZcQuestion $zcQuestion)
    {
        $role = getUserRole();
        if (!access()->allow('update-question-bank') || $role->group != 'zevo') {
            abort(403);
        }

        try {
            $data                  = array();
            $data['categories']    = $this->surveyCategory->get()->pluck('display_name', 'id')->toArray();
            $data['subcategories'] = $this->surveySubCategory->where('category_id', $zcQuestion->category_id)->get()->pluck('display_name', 'id')->toArray();
            $data['freeTextRules'] = JsValidatorFacade::make(self::questionBankShortAnswerRules());
            $data['choiceRules']   = JsValidatorFacade::make(self::questionBankChoiceRules());
            $data['type']          = $zcQuestion->questiontype->name;
            $data['question']      = $zcQuestion;
            $questionOptions       = $zcQuestion->getQuestionOptions();

            if (isset($questionOptions['score'])) {
                foreach ($questionOptions['score'] as $key => $value) {
                    if (Str::contains($value['imageUrl'], 'assets/dist/img/choice-' . $key . '.png')) {
                        $questionOptions['score'][$key]['imageUrl'] = '';
                    }
                }
            }

            $data['questionOptions'] = $questionOptions;
            $data['ga_title']        = trans('page_title.zcquestionbank.edit');
            return \view('admin.zcquestionbank.edit', $data);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong_try_again'),
                'status' => 0,
            ];
            return \Redirect::route('admin.zcquestionbank.index')->with('message', $messageData);
        }
    }

    /**
     * @param ZcQuestion $id
     * @return json
     */
    public function publish(ZcQuestion $zcQuestion, Request $request)
    {
        $role = getUserRole();
        if (!access()->allow('update-question-bank') || $zcQuestion->surveyquestions()->count() > 0 || $role->group != 'zevo') {
            abort(403);
        }

        try {
            \DB::beginTransaction();

            if (empty($zcQuestion->getFirstMediaUrl('logo'))) {
                $data['published'] = 0;
                $data['message']   = 'Please add question image before publish.';
                return response()->json($data);
            }

            if ($request->status == 'publish' && $request->action == 'review') {
                $status  = '2';
                $message = "Question has been reviewed";
            } elseif ($request->status == 'publish' && $request->action == 'publish') {
                $status  = '1';
                $message = "Question has been published";
            } elseif ($request->status == 'Draft' && ($request->action == 'publish' || $request->action == 'unpublish')) {
                $status  = '0';
                $message = "Question has been move to draft status";
            } elseif ($request->status == 'unpublish' && $request->action == 'unpublish') {
                $status  = '2';
                $message = "Question has been unpublished";
            }

            $zcQuestion->status = $status;
            $zcQuestion->save();

            $data['published'] = 1;
            $data['message']   = $message;

            \DB::commit();
            return response()->json($data);
        } catch (\Exception $exception) {
            \DB::rollback();
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong_try_again'),
                'status' => 0,
            ];
            return response()->json($messageData);
        }
    }

    /**
     * @param  ZcQuestion $zcQuestion
     * @return json
     */
    public function delete(ZcQuestion $zcQuestion)
    {
        $role = getUserRole();
        if (!access()->allow('delete-question-bank') || $zcQuestion->status == 1 || $zcQuestion->surveyquestions()->count() > 0 || $role->group != 'zevo') {
            abort(403);
        }

        try {
            return $zcQuestion->deleteRecord();
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
     * @param  ZcQuestion $zcQuestion
     * @return json
     */
    public function show(ZcQuestion $zcQuestion)
    {
        $role = getUserRole();
        if (!access()->allow('view-question-bank') || $role->group != 'zevo') {
            abort(403);
        }

        try {
            $question_type       = $zcQuestion->questiontype->name;
            $questionOptions     = $zcQuestion->getQuestionOptions();
            $image               = [];
            $text                = [];
            $choice              = [];
            $question            = [];
            $questionImagesrc    = [];
            $questionImageChoice = [];
            if (isset($questionOptions['score'])) {
                foreach ($questionOptions['score'] as $value) {
                    $image[1][] = [
                        'imageId'  => $value['imageId'],
                        'imageSrc' => $value['imageUrl'],
                    ];
                    $choice[1][] = $value['choice'];
                    $text[1][]   = isset($value['text']) ? $value['text'] : '';
                }
            }
            $question[1] = $zcQuestion->title;
            if ($zcQuestion->question_type_id == 1) {
                $questionImagesrc[1]    = $questionOptions['imageUrl'];
                $questionImageChoice[1] = $questionOptions['imageId'];
            } else {
                $questionImagesrc[1]    = $questionOptions['meta']['imageUrl'];
                $questionImageChoice[1] = $questionOptions['meta']['imageId'];
            }

            $data = [
                'status'             => 1,
                'question_type'      => $question_type,
                'question'           => $question,
                'image'              => $image,
                'choice'             => $choice,
                'question_image_src' => $questionImagesrc,
                'question_image_id'  => $questionImageChoice,
                'text'               => $text,
            ];
            return response()->json($data);
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
     * @param  ZcQuestion $zcQuestion
     * @return json
     */
    public function getViewQuestionRecords(ZcQuestion $zcQuestion)
    {
        if (!access()->allow('view-question-bank')) {
            return response()->json([
                'message' => trans('labels.common_title.unauthorized_access'),
            ], 422);
        }

        try {
            $question_type       = $zcQuestion->questiontype->name;
            $questionOptions     = $zcQuestion->getQuestionOptions();
            $image               = [];
            $text                = [];
            $choice              = [];
            $question            = [];
            $questionImagesrc    = [];
            $questionImageChoice = [];
            if (isset($questionOptions['score'])) {
                foreach ($questionOptions['score'] as $value) {
                    $image[1][] = [
                        'imageId'  => $value['imageId'],
                        'imageSrc' => $value['imageUrl'],
                    ];
                    $choice[1][] = $value['choice'];
                    $text[1][]   = isset($value['text']) ? $value['text'] : '';
                }
            }
            $question[1] = $zcQuestion->title;
            if ($zcQuestion->question_type_id == 1) {
                $questionImagesrc[1]    = $questionOptions['imageUrl'];
                $questionImageChoice[1] = $questionOptions['imageId'];
            } else {
                $questionImagesrc[1]    = $questionOptions['meta']['imageUrl'];
                $questionImageChoice[1] = $questionOptions['meta']['imageId'];
            }

            $data = [
                'status'             => 1,
                'question_type'      => $question_type,
                'question'           => $question,
                'image'              => $image,
                'choice'             => $choice,
                'question_image_src' => $questionImagesrc,
                'question_image_id'  => $questionImageChoice,
                'text'               => $text,
            ];
            return response()->json($data);
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
