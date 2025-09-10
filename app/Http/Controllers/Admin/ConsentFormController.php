<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ConsentForm;
use App\Models\ConsentFormQuestions;
use Breadcrumbs;
use Illuminate\Http\Request;
use App\Http\Requests\Admin\UpdateConsentformRequest;

/**
 * Class ConsentFormController
 *
 * @package App\Http\Controllers\Admin
 */
class ConsentFormController extends Controller
{
    /**
     * variable to store the model object
     * @var ConsentForm
     */
    protected $model;

    /**
     * contructor to initialize model object
     */
    public function __construct(ConsentForm $model)
    {
        $this->model = $model;
        $this->bindBreadcrumbs();
    }

    /**
     * bind breadcrumbs of course module
     */
    private function bindBreadcrumbs()
    {
        Breadcrumbs::for ('cronofy.consentform.index', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push('Consent Form');
        });
        Breadcrumbs::for ('cronofy.consentform.edit', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push('Consent Form', route('admin.cronofy.consent-form.index'));
            $trail->push('Edit Consent Form');
        });
    }

    /**
     * @param Request $request
     * @return View
     */
    public function index(Request $request)
    {
        if (!access()->allow('manage-consent-form')) {
            abort(403);
        }

        try {
            $user       = auth()->user();
            $role       = getUserRole($user);            
            $data = [
                'role'       => $role->slug,
                'pagination' => config('zevolifesettings.datatable.pagination.short'),
                'ga_title'   => trans('page_title.consentform.index'),
            ];

            return \view('admin.cronofy.consentform.index', $data);
        } catch (\Exception $exception) {
            abort(500);
        }
    }

    /**
     * @param Request $request
     *
     * @return View
     */

     public function getConsents(Request $request)
     {
        if (!access()->allow('manage-consent-form')) {
            abort(403);
         }
         try {
             return $this->model->getTableData($request->all());
         } catch (\Exception $exception) {
             report($exception);
             $messageData = [
                 'data'   => trans('labels.common_title.something_wrong_try_again'),
                 'status' => 0,
             ];
             return response($messageData, 500)->header('Content-Type', 'application/json');
         }
     }

    /**
     * Display the edit form with questions in html
     * @param ConsentForm $consentForm
     * @param Request $request
     * @return View
     */
    public function editConsentForm(ConsentForm $consentForm, Request $request)
    {
        if (!access()->allow('manage-consent-form')) {
            abort(403);
        }

        try {
            $consetFormQuestions = $consentForm->questions()->get();
            $getLastInsertedQues = ConsentFormQuestions::select('id')->orderBy('id', 'DESC')->first();
            if (empty($consetFormQuestions)) {
                $consetFormQuestions = [
                    [
                        'id'          => 1,
                        'title'       => trans('Cronofy.consent_form.static_data.question'),
                        'description' => trans('Cronofy.consent_form.static_data.question_description'),
                    ],
                ];
            }
            $user = auth()->user();
            $role = getUserRole($user);
            $data = [
                'role'                => $role->slug,
                'record'              => $consentForm,
                'consetFormQuestions' => $consetFormQuestions,
                'getLastInsertedQues' => $getLastInsertedQues,
                'pagination'          => config('zevolifesettings.datatable.pagination.short'),
                'ga_title'            => trans('page_title.consentform.edit_consent_form'),
            ];
            return \view('admin.cronofy.consentform.edit', $data);
        } catch (\Exception $exception) {
            abort(500);
        }
    }

    /**
     * Update the form with questions
     * @param ConsentForm $consentForm
     * @param UpdateConsentformRequest $request
     * @return View
     */
    public function updateconsentform(ConsentForm $consentForm, UpdateConsentformRequest $request)
    {
        if (!access()->allow('manage-consent-form')) {
            abort(403);
        }
        try {
            \DB::beginTransaction();
            $data = $consentForm->updateEntity($request->all());
            if ($data) {
                \DB::commit();
                $messageData = [
                    'data'   => trans('Cronofy.consent_form.message.data_update_success'),
                    'status' => 1,
                ];
                return \Redirect::route('admin.cronofy.consent-form.index')->with('message', $messageData);

            } else {
                \DB::rollback();
                $messageData = [
                    'data'   => trans('Cronofy.consent_form.message.something_wrong_try_again'),
                    'status' => 0,
                ];
                return \Redirect::route('admin.cronofy.consent-form.index')->with('message', $messageData);
            }
        } catch (\Exception $exception) {
            \DB::rollback();
            report($exception);
            return response()->json([
                'status'  => 0,
                'message' => trans('labels.common_title.something_wrong'),
            ], 500);
        }
    }
}
