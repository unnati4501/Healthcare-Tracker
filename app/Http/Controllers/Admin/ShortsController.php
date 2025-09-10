<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CreateShortsRequest;
use App\Http\Requests\Admin\EditShortsRequest;
use App\Models\CategoryTags;
use App\Models\Company;
use App\Models\CompanyLocation;
use App\Models\DepartmentLocation;
use App\Models\Goal;
use App\Models\SubCategory;
use App\Models\TeamLocation;
use App\Models\User;
use App\Models\Shorts;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Repositories\AuditLogRepository;
use Breadcrumbs;
use DB;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Class ShortsController
 *
 * @package App\Http\Controllers\Admin
 */
class ShortsController extends Controller
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
     * @param Shorts $model;
     */
    public function __construct(Shorts $model, AuditLogRepository $auditLogRepository)
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
        Breadcrumbs::for('shorts.index', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push('Shorts');
        });
        Breadcrumbs::for('shorts.create', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push('Shorts', route('admin.shorts.index'));
            $trail->push('Add Shorts');
        });
        Breadcrumbs::for('shorts.edit', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push('Shorts', route('admin.shorts.index'));
            $trail->push('Edit Shorts');
        });
    }

    /**
     * @param Request $request
     * @return View
     */
    public function index(Request $request)
    {
        $role = getUserRole();
        if (!access()->allow('manage-shorts')) {
            abort(403);
        }

        try {
            $user                = auth()->user();
            $role                = getUserRole($user);
            $data                = array();
            $data['timezone']    = ($user->timezone ?? config('app.timezone'));
            $data['date_format'] = config('zevolifesettings.date_format.moment_default_datetime');
            $data['pagination']  = config('zevolifesettings.datatable.pagination.short');
            $data['ga_title']    = trans('page_title.shorts.shorts_list');
            $data['author']      = User::select(DB::raw("CONCAT(first_name,' ',last_name) AS name"), 'id')->where("is_coach", 1)->pluck('name', 'id')->toArray();
            if ($role->group == 'zevo') {
                $data['author'] = array_replace(config('zevolifesettings.defaultAuthor'), $data['author']);
            }
            $data['shortsSubCategories'] = SubCategory::where(['category_id' => 10, 'status' => 1])->pluck('name', 'id')->toArray();
            $data['shortsTrackType']     = config('zevolifesettings.shortsTrackType');

            $data['roleGroup'] = $role->group;
            if ($role->group == 'zevo') {
                $tags         = CategoryTags::where("category_id", 10)->pluck('name', 'id')->toArray();
                $data['tags'] = array_replace(['NA' => 'NA'], $tags);
            }

            return \view('admin.shorts.index', $data);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('shorts.message.something_wrong_try_again'),
                'status' => 0,
            ];
            return \Redirect::route('admin.shorts.index')->with('message', $messageData);
        }
    }

    /**
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function getShorts(Request $request)
    {
        if (!access()->allow('manage-shorts') ) {
            return response()->json([
                'message' => trans('shorts.message.unauthorized_access'),
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
        if (!access()->allow('manage-shorts')) {
            abort(403);
        }
        try {
            $data                = array();
            $data['subcategory'] = SubCategory::where(['category_id' => 10, 'status' => 1])->pluck('name', 'id')->toArray();
            $data['author']      = User::select(DB::raw("CONCAT(first_name,' ',last_name) AS name"), 'id')->where("is_coach", 1)->pluck('name', 'id')->toArray();
            $data['companies']   = $this->getAllCompaniesGroupType();
            $data['goalTags']    = Goal::pluck('title', 'id')->toArray();
            $data['roleGroup']   = $role->group;
            if ($role->group == 'zevo') {
                $data['author'] = array_replace(config('zevolifesettings.defaultAuthor'), $data['author']);
                $data['tags']   = CategoryTags::where("category_id", 10)->pluck('name', 'id')->toArray();
            }

            $data['ga_title'] = trans('page_title.shorts.create');
            return \view('admin.shorts.create', $data);
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
     * @param CreateShortsRequest $request
     *
     * @return RedirectResponse
     */
    public function store(CreateShortsRequest $request)
    {
        $user = auth()->user();
        if (!access()->allow('create-shorts')) {
            abort(403);
        }

        try {
            if ($request->shorts_type == 3) {
                $validator = Validator::make($request->toArray(), [
                    'vimeo' => [function ($attribute, $value, $fail) use ($request) {
                        $url = config('zevolifesettings.vimeoshorsurl');
                        if (!Str::contains($request->vimeo, $url)) {
                            $fail(':error');
                        }
                    }],
                ]);
        
                if ($validator->fails()) {
                    return response()->json([
                        'message' => trans('shorts.validation.valid_vimeo_url'),
                        'status'  => false,
                    ], 500);
                }
            }

            \DB::beginTransaction();

            $userLogData = [
                'user_id'   => $user->id,
                'user_name' => $user->full_name,
            ];
            $data = $this->model->storeEntity($request->all());
            $logData = array_merge($userLogData, $request->all());
            $this->auditLogRepository->created("Short added successfully", $logData);

            if ($data) {
                \DB::commit();
                \Session::put('message', [
                    'data'   => trans('shorts.message.data_store_success'),
                    'status' => 1,
                ]);
                return response()->json([
                    'status' => 1,
                ], 200);
            } else {
                \DB::rollback();
                return response()->json([
                    'status'  => 0,
                    'message' => trans('shorts.message.something_wrong_try_again'),
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
     * @param Request $request, Shorts $shorts
     * @return View
     */
    public function edit(Request $request, Shorts $shorts)
    {
        $role = getUserRole();
        if (!access()->allow('update-shorts') || $role->group != 'zevo') {
            abort(403);
        }

        try {
            $data              = $shorts->shortsEditData();
            $data['companies'] = $this->getAllCompaniesGroupType();
            $data['ga_title']  = trans('page_title.shorts.edit');
            return \view('admin.shorts.edit', $data);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('admin.shorts.index')->with('message', $messageData);
        }
    }

    /**
     * @param EditShortsRequest $request, Shorts $shorts
     *
     * @return RedirectResponse
     */
    public function update(EditShortsRequest $request, Shorts $shorts)
    {
        $user = auth()->user();
        $role = getUserRole();
        if (!access()->allow('update-shorts') || $role->group != 'zevo') {
            abort(403);
        }
        try {
            $validator = Validator::make($request->toArray(), [
                'vimeo' => [function ($attribute, $value, $fail) use ($request) {
                    $url = config('zevolifesettings.vimeoshorsurl');
                    if (!Str::contains($request->vimeo, $url)) {
                        $fail(':error');
                    }
                }],
            ]);
    
            if ($validator->fails()) {
                return response()->json([
                    'message' => trans('shorts.validation.valid_vimeo_url'),
                    'status'  => false,
                ], 500);
            }
            \DB::beginTransaction();


            $userLogData = [
                'user_id'   => $user->id,
                'user_name' => $user->full_name,
            ];
            $oldUsersData       = array_merge($userLogData, $shorts->toArray());
            $data               = $shorts->updateEntity($request->all());
            $updatedUsersData   = array_merge($userLogData, $request->all());
            $finalLogs          = ['olddata' => $oldUsersData, 'newdata' => $updatedUsersData];
            $this->auditLogRepository->created("Short updated successfully", $finalLogs);

            if ($data) {
                \DB::commit();
                \Session::put('message', [
                    'data'   => trans('shorts.message.data_update_success'),
                    'status' => 1,
                ]);
                return response()->json([
                    'status' => 1,
                ], 200);
            } else {
                \DB::rollback();
                return response()->json([
                    'status'  => 0,
                    'message' => trans('shorts.message.something_wrong_try_again'),
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
     * Get All Companies Group Type
     *
     * @return array
     **/
    public function getAllCompaniesGroupType()
    {
        $groupType        = config('zevolifesettings.shorts_company_group_type');
        $companyGroupType = [];
        $user             = auth()->user();
        $appTimeZone      = config('app.timezone');
        $timezone         = (!empty($user->timezone) ? $user->timezone : $appTimeZone);
        $now              = now($timezone);
        foreach ($groupType as $value) {
            if ($value == 'Zevo') {
                    $companies = Company::select('name', 'id', 'plan_status', 'subscription_start_date', 'subscription_end_date')
                        ->whereNull('parent_id')
                        ->where('is_reseller', false)
                        ->get()
                        ->toArray();
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

    /**
     * @param  Shorts $shorts
     *
     * @return RedirectResponse
     */
    public function delete(Shorts $shorts)
    {
        $user = auth()->user();
        $role = getUserRole();
        if (!access()->allow('delete-shorts') || $role->group != 'zevo') {
            abort(403);
        }

        try {
            $userLogData = [
                'user_id'   => $user->id,
                'user_name' => $user->full_name,
            ];
            $logs  = array_merge($userLogData, ['deleted_shorts_id' => $shorts->id, 'deleted_shorts_name' => $shorts->title]);
            $this->auditLogRepository->created("Short deleted successfully", $logs);

            return $shorts->deleteRecord();
        } catch (\Exception $exception) {
            report($exception);
            return response()->json(['deleted' => false], 500);
        }
    }
}
