<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CreateDepartmentRequest;
use App\Http\Requests\Admin\EditDepartmentRequest;
use App\Http\Requests\Admin\NpsReportExportRequest;
use App\Models\Company;
use App\Models\Department;
use App\Models\Team;
use App\Models\TeamLocation;
use App\Repositories\AuditLogRepository;
use Breadcrumbs;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Class DepartmentController
 *
 * @package App\Http\Controllers\Admin
 */
class DepartmentController extends Controller
{
    /**
     * variable to store the model object
     * @var Department
     */
    protected $model;

    /**
     * @var AuditLogRepository $auditLogRepository
     */
    private $auditLogRepository;

    /**
     * contructor to initialize model object
     * @param Department $model ;
     */
    public function __construct(Department $model, AuditLogRepository $auditLogRepository)
    {
        $this->model              = $model;
        $this->auditLogRepository = $auditLogRepository;
        $this->bindBreadcrumbs();
    }

    /*
     * Bind breadcrumbs of role module
     */
    public function bindBreadcrumbs()
    {
        Breadcrumbs::for('department.index', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push('Departments');
        });
        Breadcrumbs::for('department.create', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push('Departments', route('admin.departments.index'));
            $trail->push('Add Department');
        });
        Breadcrumbs::for('department.edit', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push('Departments', route('admin.departments.index'));
            $trail->push('Edit Department');
        });
        Breadcrumbs::for('location.index', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push('Departments', route('admin.departments.index'));
            $trail->push('Locations');
        });
    }

    /**
     * @param Request $request
     * @return View
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $role = getUserRole($user);

        if (!access()->allow('manage-department') || ($role->group == 'company' && !getCompanyPlanAccess($user, 'team-selection'))) {
            abort(403);
        }
        try {
            $data      = array();
            $company   = $user->company()->first();
            $companies = [];

            if (is_null($company)) {
                $companies = Company::pluck('name', 'id')->toArray();
            } elseif ($company && $company->is_reseller) {
                $companies = Company::where('parent_id', $company->id)
                    ->whereNotNull('parent_id')
                    ->get()
                    ->pluck('name', 'id')
                    ->toArray();
                $companies = array_replace([$company->id => $company->name], $companies);
            }

            $data['pagination']             = config('zevolifesettings.datatable.pagination.long');
            $data['ga_title']               = trans('page_title.departments.departments_list');
            $data['company_col_visibility'] = (is_null($company) || ($company && $company->is_reseller));
            $data['companies']              = $companies;
            $data['loginemail']             = ($user->email ?? "");
            $data['role']                   = $role;
            $data['company']                = $company;
            $data['timezone']               = (auth()->user()->timezone ?? config('app.timezone'));
            $data['date_format']            = config('zevolifesettings.date_format.moment_default_date');
            return \view('admin.department.index', $data);
        } catch (\Exception $exception) {
            report($exception);
            return response(trans('department.message.something_wrong'), 500)
                ->header('Content-Type', 'text/plain');
        }
    }

    /**
     * @param Request $request
     * @return View
     */
    public function create(Request $request)
    {
        $user  = auth()->user();
        $role  = getUserRole($user);
        if (!access()->allow('create-department') || ($role->group == 'company' && !getCompanyPlanAccess($user, 'team-selection'))) {
            abort(403);
        }
        try {
            $company                = $user->company()->first();
            $department_location    = $companies    = [];
            $askForAutoTeamCreation = false;
            $companieswithTeamLimit = "{}";
            $teamBlockVisibility    = "none";

            if ($role->group == 'zevo') {
                $companies = Company::select('id', 'name')->get()
                    ->pluck('name', 'id')->toArray();
            } elseif ($role->group == 'reseller' && $company->is_reseller) {
                $companies = Company::select('id', 'name')
                    ->where('parent_id', $company->id)
                    ->orWhere('id', $company->id)
                    ->get()
                    ->pluck('name', 'id')
                    ->toArray();
            } else {
                $department_location = $company->locations->pluck('name', 'id')->toArray();
            }

            // check auto_team_creation is enabled or not
            $askForAutoTeamCreation = ($role->group == 'company');
            if ($askForAutoTeamCreation) {
                $companieswithTeamLimit = json_encode([$company->id => $company->team_limit]);
            }

            $data = [
                'role'                   => $role,
                'company'                => $company,
                'companies'              => $companies,
                'department_location'    => $department_location,
                'askForAutoTeamCreation' => $askForAutoTeamCreation,
                'companieswithTeamLimit' => $companieswithTeamLimit,
                "teamBlockVisibility"    => $teamBlockVisibility,
                'namingConvention'       => [],
                'ga_title'               => trans('page_title.departments.create'),
            ];

            return \view('admin.department.create', $data);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('admin.departments.index')->with('message', $messageData);
        }
    }

    /**
     * @param CreateDepartmentRequest $request
     *
     * @return RedirectResponse
     */
    public function store(CreateDepartmentRequest $request)
    {
        $user = auth()->user();
        $role = getUserRole($user);
        if (!access()->allow('create-department') || ($role->group == 'company' && !getCompanyPlanAccess($user, 'team-selection'))) {
            return response()->json([
                'message' => trans('department.message.unauthorized_access'),
            ], 422);
        }

        try {
            // validate team name if exist prevent
            $naming_convention = ($request->naming_convention ?? null);
            if (!is_null($naming_convention)) {
                foreach ($naming_convention as $name) {
                    $exist = Team::where('name', 'like', "%{$name}%")->where('company_id', $request->company_id)->count('id');
                    if ($exist > 0) {
                        return response()->json([
                            'message' => trans('department.validation.already_teamname_taken', [
                                'name' => $name,
                            ]),
                        ], 422);
                    }
                }
            }

            \DB::beginTransaction();
            $userLogData = [
                'user_id'   => $user->id,
                'user_name' => $user->full_name,
            ];
            $data = $this->model->storeEntity($request->all());

            $logData = array_merge($userLogData, $request->all());
            $this->auditLogRepository->created("Department added successfully", $logData);

            if ($data) {
                \DB::commit();
                $message = [
                    'data'   => trans('department.message.data_store_success'),
                    'status' => 1,
                ];
                \Session::put('message', $message);
                return response()->json($message, 200);
            } else {
                \DB::rollback();
                return response()->json([
                    'message' => trans('department.message.something_wrong_try_again'),
                ], 500);
            }
        } catch (\Exception $exception) {
            \DB::rollback();
            report($exception);
            return response()->json([
                'message' => trans('department.message.something_wrong_try_again'),
            ], 500);
        }
    }

    /**
     * @param Request $request
     * @return View
     */
    public function edit(Department $department, Request $request)
    {
        $user       = auth()->user();
        $role       = getUserRole($user);

        if (!access()->allow('update-department') || ($role->group == 'company' && !getCompanyPlanAccess($user, 'team-selection'))) {
            abort(403);
        }
        try {
            $company                = $user->company()->first();
            $askForAutoTeamCreation = false;
            $companieswithTeamLimit = "{}";
            $teamBlockVisibility    = "none";
            $autoTeamCreationMeta   = [];
            $departmentCompany      = $department->company()->select('id', 'name', 'auto_team_creation', 'team_limit')->first();

            if ($role->group != 'zevo') {
                if ($role->group == 'company') {
                    if ($company->id != $department->company_id) {
                        return view('errors.401');
                    }
                } elseif ($role->group == 'reseller') {
                    if ($company->is_reseller) {
                        $allcompanies = Company::where('parent_id', $company->id)->orWhere('id', $company->id)->get()->pluck('id')->toArray();
                        if (!in_array($department->company->id, $allcompanies)) {
                            return view('errors.401');
                        }
                    } elseif (!$company->is_reseller && $department->company_id != $company->id) {
                        return view('errors.401');
                    }
                }
            }

            $companies                    = [$departmentCompany->id => $departmentCompany->name];
            $department_location          = $department->company->locations->pluck('name', 'id')->toArray();
            $selected_department_location = $department->departmentlocations->pluck('id')->toArray();
            $locationsBeingUsed           = TeamLocation::select('id', 'company_location_id')
                ->where('department_id', $department->id)
                ->whereIn('company_location_id', $selected_department_location)
                ->groupBy('company_location_id')
                ->get()->pluck('company_location_id')->toJson();

            // check auto_team_creation is enabled or not
            $askForAutoTeamCreation = ($role->group == 'company' && $departmentCompany->auto_team_creation);
            if ($departmentCompany->auto_team_creation) {
                $autoTeamCreationMeta = $department->locations()
                    ->select('id', 'company_location_id', 'auto_team_creation_meta')
                    ->whereNotNull('auto_team_creation_meta')
                    ->get()->pluck('auto_team_creation_meta', 'company_location_id')->toArray();
                $teamBlockVisibility    = (!empty($autoTeamCreationMeta) ? "block" : "none");
                $companieswithTeamLimit = json_encode([$departmentCompany->id => $departmentCompany->team_limit]);
            }

            $data = [
                'role'                         => $role,
                'company'                      => $company,
                'companies'                    => $companies,
                'department_location'          => $department_location,
                'department'                   => $department,
                'selected_department_location' => $selected_department_location,
                'askForAutoTeamCreation'       => $askForAutoTeamCreation,
                'companieswithTeamLimit'       => $companieswithTeamLimit,
                'teamBlockVisibility'          => $teamBlockVisibility,
                'autoTeamCreationMeta'         => $autoTeamCreationMeta,
                'locationsBeingUsed'           => $locationsBeingUsed,
                'ga_title'                     => trans('page_title.departments.edit'),
            ];

            return \view('admin.department.edit', $data);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('admin.departments.index')->with('message', $messageData);
        }
    }

    /**
     * Update department data
     *
     * @param Department $department
     * @param EditDepartmentRequest $request
     * @return json
     */
    public function update(Department $department, EditDepartmentRequest $request)
    {
        $user   = auth()->user();
        $role   = getUserRole($user);
        if (!access()->allow('update-department') || ($role->group == 'company' && !getCompanyPlanAccess($user, 'team-selection'))) {
            abort(403);
        }
        try {
            // validate team name if exist prevent
            $naming_convention = ($request->naming_convention ?? null);
            if (!is_null($naming_convention)) {
                foreach ($naming_convention as $name) {
                    $exist = Team::where('name', 'like', "%{$name}%")->where('company_id', $request->company_id)->count('id');
                    if ($exist > 0) {
                        return response()->json([
                            'message' => trans('department.validation.already_teamname_taken', [
                                'name' => $name,
                            ]),
                        ], 422);
                    }
                }
            }

            \DB::beginTransaction();
            $userLogData = [
                'user_id'   => $user->id,
                'user_name' => $user->full_name,
            ];
            $oldUsersData       = array_merge($userLogData, $department->toArray());
            $data = $department->updateEntity($request->all());

            $updatedUsersData   = array_merge($userLogData, $request->all());
            $finalLogs          = ['olddata' => $oldUsersData, 'newdata' => $updatedUsersData];
            $this->auditLogRepository->created("Department updated successfully", $finalLogs);

            if ($data) {
                \DB::commit();
                $message = [
                    'data'   => trans('department.message.data_store_success'),
                    'status' => 1,
                ];
                \Session::put('message', $message);
                return response()->json($message, 200);
            } else {
                \DB::rollback();
                return response()->json([
                    'message' => trans('department.message.something_wrong_try_again'),
                ], 500);
            }
        } catch (\Exception $exception) {
            \DB::rollback();
            report($exception);
            return response()->json([
                'message' => trans('department.message.something_wrong_try_again'),
            ], 500);
        }
    }

    /**
     * @param Request $request
     *
     * @return View
     */

    public function getDepartments(Request $request)
    {
        $user = auth()->user();
        $role = getUserRole($user);
        if (!access()->allow('manage-department') || ($role->group == 'company' && !getCompanyPlanAccess($user, 'team-selection'))) {
            abort(403);
        }
        try {
            return $this->model->getTableData($request->all());
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return response($messageData, 500)->header('Content-Type', 'application/json');
        }
    }

    /**
     * @param  $id
     *
     * @return View
     */

    public function delete(Department $department)
    {
        $user = auth()->user();
        $role = getUserRole($user);
        if (!access()->allow('delete-department') || ($role->group == 'company' && !getCompanyPlanAccess($user, 'team-selection'))) {
            abort(403);
        }
        try {
            $userLogData = [
                'user_id'   => $user->id,
                'user_name' => $user->full_name,
            ];
            $logs  = array_merge($userLogData, ['deleted_department_id' => $department->id,'deleted_department_name' => $department->name]);
            $this->auditLogRepository->created("Department deleted successfully", $logs);

            return $department->deleteDepartment();
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('admin.departments.index')->with('message', $messageData);
        }
    }

    /**
     * @param Request $request , $id
     * @return View
     */
    public function locationList(Request $request, Department $department)
    {
        $user = auth()->user();
        $role = getUserRole($user);
        if (!access()->allow('view-location') || ($role->group == 'company' && !getCompanyPlanAccess($user, 'team-selection'))) {
            abort(403);
        }

        try {
            $company = $user->company()->first();
            if ($role->group != 'zevo') {
                if ($role->group == 'company') {
                    if ($company->id != $department->company_id) {
                        return view('errors.401');
                    }
                } elseif ($role->group == 'reseller') {
                    if ($company->is_reseller) {
                        $allcompanies = Company::where('parent_id', $company->id)->orWhere('id', $company->id)->get()->pluck('id')->toArray();
                        if (!in_array($department->company->id, $allcompanies)) {
                            return view('errors.401');
                        }
                    } elseif (!$company->is_reseller && $department->company_id != $company->id) {
                        return view('errors.401');
                    }
                }
            }

            $data = [
                'pagination' => config('zevolifesettings.datatable.pagination.long'),
                'department' => $department,
                'ga_title'   => trans('page_title.departments.locationList') . ' of ' . $department->name,
            ];
            return \view('admin.department.locationlist', $data);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('admin.departments.index')->with('message', $messageData);
        }
    }

    /**
     * @param Request $request
     *
     * @return View
     */
    public function getLocationList(Request $request, Department $department)
    {
        $user = auth()->user();
        $role = getUserRole($user);
        if (!access()->allow('view-location') || ($role->group == 'company' && !getCompanyPlanAccess($user, 'team-selection'))) {
            return response()->json([
                'message' => trans('labels.common_title.unauthorized_access'),
            ], 422);
        }
        try {
            return $this->model->getLocationTableData($department->id);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('admin.departments.index')->with('message', $messageData);
        }
    }

    /**
     * @param ChallengeExportRequest $request
     * @return RedirectResponse
     */
    public function exportDepartments(NpsReportExportRequest $request)
    {
        $user = auth()->user();
        $role = getUserRole($user);
        if (!access()->allow('manage-department') || ($role->group == 'company' && !getCompanyPlanAccess($user, 'team-selection'))) {
            abort(403);
        }

        try {
            \DB::beginTransaction();
            $data = $this->model->exportDepartmentDataEntity($request->all());
            if ($data) {
                \DB::commit();
                $messageData = [
                    'data'   => trans('challenges.messages.report_success'),
                    'status' => 1,
                ];
            } else {
                \DB::rollback();
                $messageData = [
                    'data'   => trans('challenges.messages.no_records_found'),
                    'status' => 0,
                ];
            }
            return \Redirect::route('admin.departments.index')->with('message', $messageData);
        } catch (\Exception $exception) {
            \DB::rollback();
            report($exception);
            $messageData = [
                'data'   => trans('challenges.messages.something_wrong_try_again'),
                'status' => 0,
            ];
            return response()->json($messageData);
        }
    }
}
