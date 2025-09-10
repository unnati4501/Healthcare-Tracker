<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CreateDomainRequest;
use App\Http\Requests\Admin\EditDomainRequest;
use App\Models\Company;
use App\Models\Domain;
use App\Models\User;
use Breadcrumbs;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

/**
 * Class DomainController
 *
 * @package App\Http\Controllers\Admin
 */
class DomainController extends Controller
{
    /**
     * variable to store the model object
     * @var Domain
     */
    protected $model;

    /**
     * contructor to initialize model object
     * @param Domain $model ;
     */
    public function __construct(Domain $model)
    {
        $this->model = $model;
        $this->bindBreadcrumbs();
    }
    /*
     * Bind breadcrumbs of role module
     */
    public function bindBreadcrumbs()
    {
        Breadcrumbs::for('domains.index', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push('Domains');
        });
        Breadcrumbs::for('domains.create', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push('Domains', route('admin.domains.index'));
            $trail->push('Add Domain');
        });
        Breadcrumbs::for('domains.edit', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push('Domains', route('admin.domains.index'));
            $trail->push('Edit Domain');
        });
    }
    /**
     * @param Request $request
     * @return View
     */
    public function index(Request $request)
    {
        if (empty(\Auth::user()->company->first())) {
            abort(403);
        }

        try {
            $data               = array();
            $data['pagination'] = config('zevolifesettings.datatable.pagination.short');
            $data['ga_title']   = trans('page_title.domains.index');

            return \view('admin.domain.index', $data);
        } catch (\Exception $exception) {
            report($exception);
            return response(trans('labels.common_title.something_wrong'), 400)
                ->header('Content-Type', 'text/plain');
        }
    }

    /**
     * @param Request $request
     * @return View
     */
    public function create(Request $request)
    {
        if (empty(\Auth::user()->company->first())) {
            abort(403);
        }

        try {
            $data               = array();
            $data['company_id'] = \Auth::user()->company->first()->id;
            $data['ga_title']   = trans('page_title.domains.add_form_title');
            return \view('admin.domain.create', $data);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('admin.domains.index')->with('message', $messageData);
        }
    }

    /**
     * @param CreateDomainRequest $request
     *
     * @return RedirectResponse
     */
    public function store(CreateDomainRequest $request)
    {
        try {
            \DB::beginTransaction();
            $payload = $request->all();

            $validator = Validator::make($payload, [
                'domain' => [function ($attribute, $value, $fail) use ($payload) {
                    $duplicationCheck = $this->model->duplicationCheck($payload);
                    if (!empty($duplicationCheck)) {
                        $fail(':error');
                    }
                }],
            ]);

            if ($validator->fails()) {
                return redirect()->back()->withInput()->withErrors(trans('domain.message.domain_already_taken'));
            }

            $data = $this->model->storeEntity($payload);
            if ($data) {
                \DB::commit();
                $messageData = [
                    'data'   => trans('domain.message.data_store_success'),
                    'status' => 1,
                ];
                return \Redirect::route('admin.domains.index')->with('message', $messageData);
            } else {
                \DB::rollback();
                $messageData = [
                    'data'   => trans('domain.message.something_wrong_try_again'),
                    'status' => 0,
                ];
                return \Redirect::route('admin.domains.create')->with('message', $messageData);
            }
        } catch (\Exception $exception) {
            \DB::rollback();
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('admin.domains.index')->with('message', $messageData);
        }
    }

    /**
     * @param Request $request
     * @return View
     */
    public function edit(Request $request, Domain $domain)
    {
        if (empty(\Auth::user()->company->first())) {
            abort(403);
        }
        $companyId    = \Auth::user()->company->first()->id;
        $userAssigned = User::join("user_team", "user_team.user_id", "=", "users.id")
            ->where("user_team.company_id", $companyId)
            ->where("users.email", "LIKE", "%@" . $domain->domain . "%")
            ->get();

        if ($userAssigned->count() > 0) {
            $messageData = [
                'data'   => trans('domain.message.domain_assign_error'),
                'status' => 0,
            ];
            return \Redirect::route('admin.domains.index')->with('message', $messageData);
        }

        try {
            $data               = array();
            $data['company_id'] = \Auth::user()->company->first()->id;
            $data['domainData'] = $domain;
            $data['ga_title']   = trans('page_title.domains.edit_form_title');
            return \view('admin.domain.edit', $data);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('admin.domains.index')->with('message', $messageData);
        }
    }

    /**
     * @param EditDomainRequest $request
     *
     * @return RedirectResponse
     */
    public function update(EditDomainRequest $request, Domain $domain)
    {
        try {
            \DB::beginTransaction();
            $payload   = $request->all();
            $id        = $domain->id;
            $validator = Validator::make($payload, [
                'domain' => [function ($attribute, $value, $fail) use ($payload, $id) {
                    $duplicationCheck = $this->model->duplicationCheck($payload, $id);
                    if (!empty($duplicationCheck)) {
                        $fail(':error');
                    }
                }],
            ]);

            if ($validator->fails()) {
                return redirect()->back()->withInput()->withErrors(trans('domain.message.domain_already_taken'));
            }

            $userAssigned = User::join("user_team", "user_team.user_id", "=", "users.id")
                ->where("user_team.company_id", $domain->company_id)
                ->where("users.email", "LIKE", "%@" . $domain->domain . "%")
                ->get();

            if ($userAssigned->count() > 0) {
                $messageData = [
                    'data'   => trans('domain.message.domain_assign_error'),
                    'status' => 0,
                ];
                return \Redirect::route('admin.domains.index')->with('message', $messageData);
            }

            $data = $domain->updateEntity($request->all());
            if ($data) {
                \DB::commit();
                $messageData = [
                    'data'   => trans('domain.message.data_update_success'),
                    'status' => 1,
                ];
                return \Redirect::route('admin.domains.index')->with('message', $messageData);
            } else {
                \DB::rollback();
                $messageData = [
                    'data'   => trans('domain.message.something_wrong_try_again'),
                    'status' => 0,
                ];
                return \Redirect::route('admin.domains.edit', $domain->id)->with('message', $messageData);
            }
        } catch (\Exception $exception) {
            \DB::rollback();
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('admin.domains.index')->with('message', $messageData);
        }
    }

    /**
     * @param Request $request
     *
     * @return View
     */

    public function getDomains(Request $request)
    {
        try {
            return $this->model->getTableData($request->all());
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('admin.domains.index')->with('message', $messageData);
        }
    }

    /**
     * @param  $id
     *
     * @return View
     */

    public function delete(Domain $domain)
    {
        if (empty(\Auth::user()->company->first())) {
            abort(403);
        }

        try {
            return $domain->deleteDepartment();
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('admin.departments.index')->with('message', $messageData);
        }
    }
}
