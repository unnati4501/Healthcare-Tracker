<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CreateCompanyplanRequest;
use App\Http\Requests\Admin\EditCompanyPlanRequest;
use App\Models\Company;
use App\Models\CpFeatures;
use App\Models\CpPlan;
use App\Models\User;
use Breadcrumbs;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Class CompanyplanController
 *
 * @package App\Http\Controllers\Admin
 */
class CompanyplanController extends Controller
{
    /**
     * variable to store the model object
     * @var model
     */
    protected $model;

    /**
     * contructor to initialize model object
     * @param CpPlan $model;
     */
    public function __construct(CpPlan $model, CpFeatures $cpFeatures)
    {
        $this->model      = $model;
        $this->cpFeatures = $cpFeatures;
        $this->bindBreadcrumbs();
    }

    /*
     * Bind breadcrumbs of role module
     */
    public function bindBreadcrumbs()
    {
        Breadcrumbs::for('company-plan.index', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push('Company Plan');
        });
        Breadcrumbs::for('company-plan.create', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push('Company Plan', route('admin.company-plan.index'));
            $trail->push('Add Company Plan');
        });
        Breadcrumbs::for('company-plan.edit', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push('Company Plan', route('admin.company-plan.index'));
            $trail->push('Edit Company Plan');
        });
    }

    /**
     * @param Request $request
     * @return View
     */
    public function index(Request $request)
    {
        $role = getUserRole();
        if (!access()->allow('manage-company-plan') || $role->group != 'zevo') {
            abort(403);
        }

        try {
            $user               = auth()->user();
            $role               = getUserRole($user);
            $data               = array();
            $data['pagination'] = config('zevolifesettings.datatable.pagination.short');
            $data['ga_title']   = trans('page_title.companyplans.index');
            $data['groupType']  = config('zevolifesettings.company_plan_group_type');
            $data['type']       = 1; // Currently fixed selected company in group type

            return \view('admin.companyplan.index', $data);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('companyplans.message.something_wrong_try_again'),
                'status' => 0,
            ];
            return \Redirect::route('admin.company-plan.index')->with('message', $messageData);
        }
    }

    /**
     * @param Request $request
     * @return View
     */
    public function getCompanyplan(Request $request)
    {
        $role = getUserRole();
        if (!access()->allow('manage-company-plan') || $role->group != 'zevo') {
            return response()->json([
                'message' => trans('companyplans.message.unauthorized_access'),
            ], 422);
        }
        try {
            return $this->model->getTableData($request->all());
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return response()->json($messageData, 500);
        }
    }

    /**
     * @param Request $request
     * @return View
     */
    public function create(Request $request)
    {
        $role = getUserRole();
        if (!access()->allow('create-company-plan') || $role->group != 'zevo') {
            abort(403);
        }
        try {
            $data                 = array();
            $data['groupType']    = config('zevolifesettings.company_plan_group_type');
            $data['type']         = 1; // Currently fixed selected company in group type
            $data['roleGroup']    = $role->group;
            $data['ga_title']     = trans('page_title.companyplans.create');
            $data['featuresData'] = $this->cpFeatures->getCpPlanFeatures();
            return \view('admin.companyplan.create', $data);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('admin.company-plan.index')->with('message', $messageData);
        }
    }

    /**
     * @param CreateCompanyplanRequest $request
     * @return View
     */
    public function store(CreateCompanyplanRequest $request)
    {
        $role = getUserRole();
        if (!access()->allow('create-company-plan') || $role->group != 'zevo') {
            abort(403);
        }
        try {
            \DB::beginTransaction();
            $data = $this->model->storeEntity($request->all());
            if ($data) {
                \DB::commit();
                $messageData = [
                    'data'   => trans('companyplans.message.data_store_success'),
                    'status' => 1,
                ];
                return \Redirect::route('admin.company-plan.index')->with('message', $messageData);
            } else {
                \DB::rollback();
                $messageData = [
                    'data'   => trans('companyplans.message.something_wrong_try_again'),
                    'status' => 0,
                ];
                return \Redirect::route('admin.company-plan.index')->with('message', $messageData);
            }
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('admin.company-plan.index')->with('message', $messageData);
        }
    }

    /**
     * @param Request $request, Webinar $Webinar
     * @return View
     */
    public function edit(CpPlan $cpPlan, Request $request)
    {
        $role = getUserRole();
        if (!access()->allow('update-company-plan') || $role->group != 'zevo') {
            abort(403);
        }

        try {
            $data                 = array();
            $data                 = $cpPlan->getEditCompanyPlan();
            $data['featuresData'] = $this->cpFeatures->getCpPlanFeatures($data['type']);

            return \view('admin.companyplan.edit', $data);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('admin.company-plan.index')->with('message', $messageData);
        }
    }

    /**
     * @param EditCompanyPlanRequest $request
     * @return View
     */
    public function update(EditCompanyPlanRequest $request, CpPlan $cpPlan)
    {
        $role = getUserRole();
        if (!access()->allow('update-company-plan') || $role->group != 'zevo') {
            abort(403);
        }
        try {
            \DB::beginTransaction();
            $data = $cpPlan->updateEntity($request->all());
            if ($data) {
                \DB::commit();
                $messageData = [
                    'data'   => trans('companyplans.message.data_update_success'),
                    'status' => 1,
                ];
                return \Redirect::route('admin.company-plan.index')->with('message', $messageData);
            } else {
                \DB::rollback();
                $messageData = [
                    'data'   => trans('companyplans.message.something_wrong_try_again'),
                    'status' => 0,
                ];
                return \Redirect::route('admin.company-plan.index')->with('message', $messageData);
            }
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('admin.company-plan.index')->with('message', $messageData);
        }
    }

    /**
     * @param  $id
     *
     * @return View
     */

    public function delete(CpPlan $cpPlan)
    {
        if (!access()->allow('delete-company-plan')) {
            abort(403);
        }
        try {
            $attechCount = $cpPlan->plancompany()->count();
            if ($attechCount > 0) {
                return array('deleted' => 'error', 'alreadyUse' => 'true');
            }
            return $cpPlan->deleteRecord();
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('admin.company-plan.index')->with('message', $messageData);
        }
    }
}
