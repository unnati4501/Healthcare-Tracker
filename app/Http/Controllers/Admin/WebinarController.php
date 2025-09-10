<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CreateWebinarRequest;
use App\Http\Requests\Admin\EditWebinarRequest;
use App\Models\CategoryTags;
use App\Models\Company;
use App\Models\CompanyLocation;
use App\Models\DepartmentLocation;
use App\Models\Goal;
use App\Models\SubCategory;
use App\Models\TeamLocation;
use App\Models\User;
use App\Models\Webinar;
use App\Repositories\AuditLogRepository;
use Breadcrumbs;
use DB;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Class WebinarController
 *
 * @package App\Http\Controllers\Admin
 */
class WebinarController extends Controller
{
    /**
     * variable to store the model object
     * @var model
     */
    protected $model;

    /**
     * @var AuditLogRepository $auditLogRepository
     */
    private $auditLogRepository;

    /**
     * contructor to initialize model object
     * @param Webinar $model;
     */
    public function __construct(Webinar $model, AuditLogRepository $auditLogRepository)
    {
        $this->model                = $model;
        $this->auditLogRepository   = $auditLogRepository;
        $this->bindBreadcrumbs();
    }

    /*
     * Bind breadcrumbs of role module
     */
    public function bindBreadcrumbs()
    {
        Breadcrumbs::for('webinar.index', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push('Webinar');
        });
        Breadcrumbs::for('webinar.create', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push('Webinar', route('admin.webinar.index'));
            $trail->push('Add Webinar');
        });
        Breadcrumbs::for('webinar.edit', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push('Webinar', route('admin.webinar.index'));
            $trail->push('Edit Webinar');
        });
    }

    /**
     * @param Request $request
     * @return View
     */
    public function index(Request $request)
    {
        $role = getUserRole();
        if (!access()->allow('webinar-management') || $role->group != 'zevo') {
            abort(403);
        }

        try {
            $user                = auth()->user();
            $role                = getUserRole($user);
            $data                = array();
            $data['timezone']    = ($user->timezone ?? config('app.timezone'));
            $data['date_format'] = config('zevolifesettings.date_format.meditation_recepie_support_createdtime');
            $data['pagination']  = config('zevolifesettings.datatable.pagination.short');
            $data['ga_title']    = trans('page_title.webinar.webinar_list');
            $data['author']      = User::select(DB::raw("CONCAT(first_name,' ',last_name) AS name"), 'id')->where("is_coach", 1)->pluck('name', 'id')->toArray();
            if ($role->group == 'zevo') {
                $data['author'] = array_replace(config('zevolifesettings.defaultAuthor'), $data['author']);
            }
            $data['webinarSubCategories'] = SubCategory::where(['category_id' => 7, 'status' => 1])->pluck('name', 'id')->toArray();
            $data['webinarTrackType']     = config('zevolifesettings.webinarTrackType');

            $data['roleGroup'] = $role->group;
            if ($role->group == 'zevo') {
                $tags         = CategoryTags::where("category_id", 7)->pluck('name', 'id')->toArray();
                $data['tags'] = array_replace(['NA' => 'NA'], $tags);
            }

            return \view('admin.webinar.index', $data);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('webinar.message.something_wrong_try_again'),
                'status' => 0,
            ];
            return \Redirect::route('admin.webinar.index')->with('message', $messageData);
        }
    }

    /**
     * @param Request $request
     * @return View
     */
    public function create(Request $request)
    {
        $role = getUserRole();
        if (!access()->allow('add-webinar') || $role->group != 'zevo') {
            abort(403);
        }
        try {
            $data                = array();
            $data['subcategory'] = SubCategory::where(['category_id' => 7, 'status' => 1])->pluck('name', 'id')->toArray();
            $data['author']      = User::select(DB::raw("CONCAT(first_name,' ',last_name) AS name"), 'id')->where("is_coach", 1)->pluck('name', 'id')->toArray();
            $data['companies']   = $this->getAllCompaniesGroupType();
            $data['goalTags']    = Goal::pluck('title', 'id')->toArray();
            $data['roleGroup']   = $role->group;
            if ($role->group == 'zevo') {
                $data['author'] = array_replace(config('zevolifesettings.defaultAuthor'), $data['author']);
                $data['tags']   = CategoryTags::where("category_id", 7)->pluck('name', 'id')->toArray();
            }

            $data['ga_title'] = trans('page_title.webinar.create');
            return \view('admin.webinar.create', $data);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('admin.webinar.index')->with('message', $messageData);
        }
    }

    /**
     * @param CreateWebinarRequest $request
     *
     * @return RedirectResponse
     */
    public function store(CreateWebinarRequest $request)
    {
        $user = auth()->user();
        $role = getUserRole();
        if (!access()->allow('add-webinar') || $role->group != 'zevo') {
            abort(403);
        }
        try {
            \DB::beginTransaction();
            $userLogData = [
                'user_id'   => $user->id,
                'user_name' => $user->full_name,
            ];
            $data = $this->model->storeEntity($request->all());
            $logData = array_merge($userLogData, $request->all());
            $this->auditLogRepository->created("Webinar added successfully", $logData);

            if ($data) {
                \DB::commit();
                \Session::put('message', [
                    'data'   => trans('webinar.message.data_store_success'),
                    'status' => 1,
                ]);
                return response()->json([
                    'status' => 1,
                ], 200);
            } else {
                \DB::rollback();
                return response()->json([
                    'status'  => 0,
                    'message' => trans('webinar.message.something_wrong_try_again'),
                ], 422);
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

    /**
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function getWebinar(Request $request)
    {
        $role = getUserRole();
        if (!access()->allow('webinar-management') || $role->group != 'zevo') {
            return response()->json([
                'message' => trans('webinar.message.unauthorized_access'),
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
     * @param Request $request, Webinar $Webinar
     * @return View
     */
    public function edit(Request $request, Webinar $webinar)
    {
        $role = getUserRole();
        if (!access()->allow('edit-webinar') || $role->group != 'zevo') {
            abort(403);
        }

        try {
            $data              = $webinar->webinarEditData();
            $data['companies'] = $this->getAllCompaniesGroupType();
            $data['ga_title']  = trans('page_title.webinar.edit');
            return \view('admin.webinar.edit', $data);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('admin.webinar.index')->with('message', $messageData);
        }
    }

    /**
     * @param EditWebinarRequest $request, Webinar $webinar
     *
     * @return RedirectResponse
     */
    public function update(EditWebinarRequest $request, Webinar $webinar)
    {
        $user = auth()->user();
        $role = getUserRole();
        if (!access()->allow('edit-webinar') || $role->group != 'zevo') {
            abort(403);
        }
        try {
            \DB::beginTransaction();
            $userLogData = [
                'user_id'   => $user->id,
                'user_name' => $user->full_name,
            ];
            $oldUsersData       = array_merge($userLogData, $webinar->toArray());
            $data = $webinar->updateEntity($request->all());
            $updatedUsersData   = array_merge($userLogData, $request->all());
            $finalLogs          = ['olddata' => $oldUsersData, 'newdata' => $updatedUsersData];
            $this->auditLogRepository->created("Webinar updated successfully", $finalLogs);

            if ($data) {
                \DB::commit();
                \Session::put('message', [
                    'data'   => trans('webinar.message.data_update_success'),
                    'status' => 1,
                ]);
                return response()->json([
                    'status' => 1,
                ], 200);
            } else {
                \DB::rollback();
                return response()->json([
                    'status'  => 0,
                    'message' => trans('webinar.message.something_wrong_try_again'),
                ], 422);
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

    /**
     * @param  Webinar $webinar
     *
     * @return RedirectResponse
     */
    public function delete(Webinar $webinar)
    {
        $user = auth()->user();
        $role = getUserRole();
        if (!access()->allow('delete-webinar') || $role->group != 'zevo') {
            abort(403);
        }

        try {
            $userLogData = [
                'user_id'   => $user->id,
                'user_name' => $user->full_name,
            ];
            $logs  = array_merge($userLogData, ['deleted_webinar_id' => $webinar->id, 'deleted_webinar_name' => $webinar->title]);
            $this->auditLogRepository->created("Webinar deleted successfully", $logs);

            return $webinar->deleteRecord();
        } catch (\Exception $exception) {
            report($exception);
            return response()->json(['deleted' => false], 500);
        }
    }

    /**
     * Get All Companies Group Type
     *
     * @return array
     **/
    public function getAllCompaniesGroupType()
    {
        $groupType        = config('zevolifesettings.content_company_group_type');
        $companyGroupType = [];
        $user             = auth()->user();
        $appTimeZone      = config('app.timezone');
        $timezone         = (!empty($user->timezone) ? $user->timezone : $appTimeZone);
        $now              = now($timezone);
        foreach ($groupType as $value) {
            switch ($value) {
                case 'Zevo':
                    $companies = Company::select('name', 'id', 'plan_status', 'subscription_start_date', 'subscription_end_date')
                        ->whereNull('parent_id')
                        ->where('is_reseller', false)
                        ->get()
                        ->toArray();
                    break;
                case 'Parent':
                    $companies = Company::select('name', 'id', 'plan_status', 'subscription_start_date', 'subscription_end_date')
                        ->whereNull('parent_id')
                        ->where('is_reseller', true)
                        ->get()
                        ->toArray();
                    break;
                case 'Child':
                    $companies      = Company::select('name', 'id', 'plan_status', 'subscription_start_date', 'subscription_end_date')
                        ->whereNotNull('parent_id')
                        ->where('is_reseller', false)
                        ->get()
                        ->toArray();
                    break;
            }

            if (count($companies) > 0) {
                foreach ($companies as $item) {
                    $diff         = $now->diffInHours($item['subscription_end_date'], false);
                    $startDayDiff = $now->diffInHours($item['subscription_start_date'], false);
                    $days         = (int) ceil($diff / 24);

                    if ($startDayDiff > 0) {
                        $planStatus = 'Inactive';
                    } elseif ($days <= 0) {
                        $planStatus = 'Expired';
                    } else {
                        $planStatus = 'Active';
                    }

                    $companyLocation = CompanyLocation::where('company_id', $item['id'])->select('id', 'name')->get()->toArray();

                    $locationArray = [];
                    foreach ($companyLocation as $locationItem) {
                        $departmentArray   = [];
                        $departmentRecords = DepartmentLocation::join('departments', 'departments.id', '=', 'department_location.department_id')->where('department_location.company_location_id', $locationItem['id'])->where('department_location.company_id', $item['id'])->select('departments.id', 'departments.name')->get()->toArray();

                        foreach ($departmentRecords as $departmentItem) {
                            $teamArray   = [];
                            $teamRecords = TeamLocation::join('teams', 'teams.id', '=', 'team_location.team_id')->where('team_location.department_id', $departmentItem['id'])->where('team_location.company_id', $item['id'])->where('team_location.company_location_id', $locationItem['id'])->select('teams.id', 'teams.name')->get()->toArray();

                            foreach ($teamRecords as $teamItem) {
                                $teamArray[] = [
                                    'id'   => $teamItem['id'],
                                    'name' => $teamItem['name'],
                                ];
                            }

                            if (!empty($teamArray)) {
                                $departmentArray[] = [
                                    'departmentName' => $departmentItem['name'],
                                    'team'           => $teamArray,
                                ];
                            }
                        }

                        $locationArray[] = [
                            'locationName' => $locationItem['name'],
                            'department'   => $departmentArray,
                        ];
                    }

                    $plucked[$value][$item['id']] = [
                        'companyName' => $item['name'] . ' - ' . $planStatus,
                        'location'    => $locationArray,
                    ];
                }

                $companyGroupType[] = [
                    'roleType'  => $value,
                    'companies' => $plucked[$value],
                ];
            }
        }

        return $companyGroupType;
    }
}
