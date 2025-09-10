<?php declare (strict_types = 1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\DashbaordChartDataRequest;
use App\Models\Company;
use App\Models\HsCategories;
use App\Models\HsQuestions;
use App\Models\HsQuestionType;
use App\Models\HsSubCategories;
use Illuminate\Http\Request;

/**
 * Class HealthScoreController
 *
 * @package App\Http\Controllers\Admin
 */
class HealthScoreController extends Controller
{
    /**
     * variable to store the model object
     * @var HsQuestions
     */
    protected $model;

    /**
     * contructor to initialize model object
     * @param HsQuestions $model;
     */
    public function __construct(HsQuestions $model)
    {
        $this->model = $model;
    }

    /**
     * @return View
     */
    public function index(Request $request)
    {
        abort(403);
        $role = getUserRole();
        if (!access()->allow('survey-questioners') || $role->group != 'zevo') {
            abort(403);
        }
        try {
            $data                   = array();
            $data['pagination']     = config('zevolifesettings.datatable.pagination.short');
            $data['categories']     = HsCategories::where('status', 1)->get()->pluck('display_name', 'id')->toArray();
            $data['question_types'] = HsQuestionType::where('status', 1)->get()->pluck('display_name', 'id')->toArray();

            if ($request->get('category')) {
                $data['sub_categories'] = HsSubCategories::where('category_id', $request->get('category'))
                    ->where('status', 1)
                    ->get()
                    ->pluck('display_name', 'id')
                    ->toArray();
            }
            $data['ga_title'] = trans('page_title.questions');
            return \view('admin.hsQuestions.index', $data);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('admin.hsQuestions.index')->with('message', $messageData);
        }
    }

    /**
     * @param Request $request
     *
     * @return datatables
     */
    public function getQuestions(Request $request)
    {
        return response()->json([
            'message' => trans('labels.common_title.unauthorized_access'),
        ], 422);
    }

    /**
     * @param $id
     *
     * @return view
     */
    public function show($id)
    {
        abort(403);

        $role = getUserRole();
        if (!access()->allow('view-options') || $role->group != 'zevo') {
            abort(403);
        }
        try {
            $hsQuestions = HsQuestions::find($id);

            $data = [
                'hsQuestions' => $hsQuestions,
            ];

            return view('admin.hsQuestions.show', $data);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('admin.hsQuestions.index')->with('message', $messageData);
        }
    }

    /**
     *
     * Wellbeing survey board page
     *
     * @param Request $request
     * @return View
     *
     */
    public function wellbeingSurveyBoardIndex(Request $request)
    {
        abort(403);

        $user    = Auth()->user();
        $role    = getUserRole();
        $company = $user->company()->first();
        if (!access()->allow('view-wellbeing-survey-board') || ($role->group == 'reseller' && $company->parent_id == null)) {
            abort(403);
        }

        if ($role->group == 'reseller' && $company->parent_id != null && !$company->allow_app) {
            abort(403);
        }
        try {
            $data = [
                'visibility' => (!empty($role && $role->group == 'zevo') ? '' : 'd-none'),
                'company'    => [],
                'department' => [],
                'company_id' => 0,
                'age'        => config('zevolifesettings.age'),
            ];
            if ($role->group == 'zevo') {
                $data['company'] = Company::pluck('name', 'id')->toArray();
            } elseif ($role->group == 'company') {
                $companyData        = auth()->user()->company->first();
                $data['company_id'] = ($companyData->id ?? 0);
                if (!empty($companyData)) {
                    $data['company'] = [$companyData->id => $companyData->name];
                }
            } elseif ($role->group == 'reseller') {
                $companyData        = auth()->user()->company->first();
                $data['company_id'] = ($companyData->id ?? 0);
                if (!empty($companyData)) {
                    $data['company'] = [$companyData->id => $companyData->name];
                }
            }
            $data['ga_title'] = trans('page_title.health-score');
            return view('admin.hsDashboard.index', $data);
        } catch (\Exception $exception) {
            report($exception);
            abort(500);
        }
    }

    /**
     * Get all charts data of wellbeing survey board
     *
     * @param Request $request
     *
     * @return JSON Response
     */
    public function wellbeingSurveyChartData(DashbaordChartDataRequest $request)
    {
        return response()->json([
            'message' => trans('labels.common_title.unauthorized_access'),
        ], 422);
    }
}
