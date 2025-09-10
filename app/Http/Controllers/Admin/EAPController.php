<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CreateEAPRequest;
use App\Http\Requests\Admin\EditEAPIntroductionRequest;
use App\Http\Requests\Admin\EditEAPRequest;
use App\Models\Company;
use App\Models\CompanyLocation;
use App\Models\DepartmentLocation;
use App\Models\EAP;
use App\Models\EAPIntroduction;
use App\Repositories\AuditLogRepository;
use Breadcrumbs;
use DB;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Class TeamController
 *
 * @package App\Http\Controllers\Admin
 */
class EAPController extends Controller
{
    /**
     * variable to store the model object
     * @var Team
     */
    protected $eap_introduction;

    /**
     * variable to store the model object
     * @var Team
     */
    protected $eap_list;

    /**
     * @var AuditLogRepository $auditLogRepository
     */
    private $auditLogRepository;

    /**
     * contructor to initialize model object
     * @param Team $model ;
     */
    public function __construct(AuditLogRepository $auditLogRepository)
    {
        $this->eap_introduction     = new EAPIntroduction();
        $this->eap_list             = new EAP();
        $this->auditLogRepository   = $auditLogRepository;
        $this->bindBreadcrumbs();
    }

    /*
     * Bind breadcrumbs of role module
     */
    public function bindBreadcrumbs()
    {
        Breadcrumbs::for ('eap.index', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push('Support');
        });
        Breadcrumbs::for ('eap.create', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push('Support', route('admin.support.list'));
            $trail->push('Add Support');
        });
        Breadcrumbs::for ('eap.edit', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push('Support', route('admin.support.list'));
            $trail->push('Edit Support');
        });
        Breadcrumbs::for ('eap.introduction', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push('Support', route('admin.support.list'));
            $trail->push('Support Introduction');
        });
    }

    /**
     * @param Request $request
     * @return View
     */
    public function introductionIndex(Request $request)
    {
        if (!access()->allow('support-introduction')) {
            abort(403);
        }

        try {
            $user            = auth()->user();
            $role            = getUserRole($user);
            $isSupportAccess = getCompanyPlanAccess($user, 'supports');
            if ($role->group == 'company' && !$isSupportAccess) {
                abort(403);
            }

            if ($role->group == 'zevo') {
                $eapintroduction = EAPIntroduction::find(1);
            } else {
                $company         = $user->company()->first();
                $companyId       = $company->id;
                $eapintroduction = EAPIntroduction::where('company_id', $companyId)->first();
            }

            $data = [
                'introduction' => ($eapintroduction->introduction ?? ''),
                'ga_title'     => trans('page_title.eap.introduction'),
            ];
            return \view('admin.eap.introduction.index', $data);
        } catch (\Exception $exception) {
            report($exception);
            return response(trans('eap.message.something_wrong'), 400)
                ->header('Content-Type', 'text/plain');
        }
    }

    /**
     * @param EditEAPIntroductionRequest $request
     *
     * @return RedirectResponse
     */
    public function storeIntroduction(EditEAPIntroductionRequest $request)
    {
        if (!access()->allow('support-introduction')) {
            abort(403);
        }
        try {
            \DB::beginTransaction();
            $payload = $request->all();

            $data = $this->eap_introduction->storeEntity($payload);
            if ($data) {
                \DB::commit();
                $messageData = [
                    'data'   => trans('eap.message.introduction_success'),
                    'status' => 1,
                ];
            } else {
                \DB::rollback();
                $messageData = [
                    'data'   => trans('eap.message.something_wrong_try_again'),
                    'status' => 0,
                ];
            }
            return \Redirect::route('admin.support.introduction')->with('message', $messageData);
        } catch (\Exception $exception) {
            \DB::rollback();
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('admin.support.introduction')->with('message', $messageData);
        }
    }

    /**
     * @param Request $request
     * @return View
     */
    public function listIndex(Request $request)
    {
        $user                           = auth()->user();
        $role                           = getUserRole($user);
        $checkPlanAccess                = getCompanyPlanAccess($user, 'supports');
        $checkPlanAccessForReseller     = getDTAccessForParentsChildCompany($user, 'supports');
        if (!access()->allow('manage-support')  || ($role->group == 'company' &&  !$checkPlanAccess) || ($role->group == 'reseller' &&  !$checkPlanAccessForReseller)) {
            abort(403);
        }

        try {
            $company = $user->company()->first();
            $data    = [
                'pagination'                 => config('zevolifesettings.datatable.pagination.long'),
                'ga_title'                   => trans('page_title.eap.eap_list'),
                'reordering'                 => ($role->group == 'zevo' || ($role->group == 'reseller' && $company->parent_id == null)),
                'visabletocompanyVisibility' => ($role->group == 'zevo' || ($role->group == 'reseller' && $company->parent_id == null)),
                'timezone'                   => (auth()->user()->timezone ?? config('app.timezone')),
                'date_format'                => config('zevolifesettings.date_format.meditation_recepie_support_createdtime'),
            ];
            return \view('admin.eap.index', $data);
        } catch (\Exception $exception) {
            report($exception);
            return response(trans('eap.message.something_wrong'), 400)
                ->header('Content-Type', 'text/plain');
        }
    }

    /**
     * @param Request $request
     *
     * @return Json
     */

    public function getEaps(Request $request)
    {
        if (!access()->allow('manage-support')) {
            return response()->json([
                'message' => trans('eap.message.unauthorized_access'),
            ], 422);
        }
        try {
            return $this->eap_list->getTableData($request->all());
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('admin.support.list')->with('message', $messageData);
        }
    }

    /**
     * @param Request $request
     * @return View
     */
    public function create(Request $request)
    {
        if (!access()->allow('create-support')) {
            abort(403);
        }
        try {
            $user            = auth()->user();
            $role            = getUserRole($user);
            $isSupportAccess = getCompanyPlanAccess($user, 'supports');
            if ($role->group == 'company' && !$isSupportAccess) {
                abort(403);
            }
            $companyData  = $user->company()->first();
            $company_id   = (($role->group != 'zevo') ? $companyData->id : null);
            $count        = $this->eap_list->where('company_id', $company_id)->count();
            $data['isSA'] = ($role->group == 'zevo' || ($role->group == 'reseller' && $companyData->parent_id == null));

            $data['company']           = $this->getAllCompaniesGroupType($role->group, $companyData);
            $data['companyLocations']  = [];
            $data['companyDepartment'] = [];
            $data['loggedInUserRole']  = $role;
            $data['companyData']       = $companyData;

            if ($role->group == 'zevo' && $count >= (int) config('zevolifesettings.EAPLimits.SA')) {
                $messageData = [
                    'data'   => trans('eap.message.admin_validation_limit', [
                        'limit' => config('zevolifesettings.EAPLimits.SA'),
                    ]),
                    'status' => 0,
                ];
                return \Redirect::route('admin.support.list')->with('message', $messageData);
            } elseif ($role->group == 'company' && $count >= (int) config('zevolifesettings.EAPLimits.CA')) {
                $messageData = [
                    'data'   => trans('eap.message.company_admin_validation', [
                        'limit' => config('zevolifesettings.EAPLimits.CA'),
                    ]),
                    'status' => 0,
                ];
                return \Redirect::route('admin.support.list')->with('message', $messageData);
            } elseif ($role->group == 'reseller') {
                $companyDetails = $user->company()->first();
                if ($companyDetails->parent_id == null && $count >= (int) config('zevolifesettings.EAPLimits.RSA')) {
                    $messageData = [
                        'data'   => trans('eap.message.company_admin_validation', [
                            'limit' => config('zevolifesettings.EAPLimits.RSA'),
                        ]),
                        'status' => 0,
                    ];
                    return \Redirect::route('admin.support.list')->with('message', $messageData);
                } elseif ($companyDetails->parent_id != null && $count >= (int) config('zevolifesettings.EAPLimits.RCA')) {
                    $messageData = [
                        'data'   => trans('eap.message.company_admin_validation', [
                            'limit' => config('zevolifesettings.EAPLimits.RCA'),
                        ]),
                        'status' => 0,
                    ];
                    return \Redirect::route('admin.support.list')->with('message', $messageData);
                }
            }
            
            $companyLocation = \App\Models\CompanyLocation::select('id', 'name');
            if ($role->group == 'company') {
                $companyLocation = $companyLocation->where('company_id', $companyData->id);
            } elseif ($role->group == 'reseller') {
                if (is_null($companyData->parent_id)) {
                    $companies = Company::where('id', $companyData->id)
                        ->orwhere('parent_id', $companyData->id)
                        ->where('status', 1)
                        ->pluck('id')
                        ->toArray();
                    $companyLocation = $companyLocation->whereIn('company_id', $companies);
                } elseif (!is_null($companyData->parent_id)) {
                    $companyLocation = $companyLocation->where('company_id', $companyData->id);
                }
            }
            $companyLocation          = $companyLocation->get()->pluck('name', 'id')->toArray();
            $data['companyLocations'] = $companyLocation;
            $data['ga_title']         = trans('page_title.eap.create');

            return \view('admin.eap.create', $data);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('admin.support.list')->with('message', $messageData);
        }
    }

    /**
     * @param CreateEAPRequest $request
     *
     * @return RedirectResponse
     */
    public function store(CreateEAPRequest $request)
    {
        $user   = auth()->user();
        if (!access()->allow('create-support')) {
            abort(403);
        }

        try {
            \DB::beginTransaction();
            $payload = $request->all();

            $userLogData = [
                'user_id'   => $user->id,
                'user_name' => $user->full_name,
            ];
            $data    = $this->eap_list->storeEntity($payload);

            $logData = array_merge($userLogData, $payload);
            $this->auditLogRepository->created("Support added successfully", $logData);

            if ($data) {
                \DB::commit();
                $messageData = [
                    'data'   => trans('eap.message.data_store_success'),
                    'status' => 1,
                ];
                return \Redirect::route('admin.support.list')->with('message', $messageData);
            } else {
                \DB::rollback();
                $messageData = [
                    'data'   => trans('eap.message.something_wrong_try_again'),
                    'status' => 0,
                ];
                return \Redirect::route('admin.support.create')->with('message', $messageData);
            }
        } catch (\Exception $exception) {
            \DB::rollback();
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('admin.support.list')->with('message', $messageData);
        }
    }

    /**
     * @param Request $request
     * @return View
     */
    public function edit(Request $request, EAP $eap)
    {
        if (!access()->allow('update-support')) {
            abort(403);
        }
        $user            = auth()->user();
        $role            = getUserRole($user);
        $isSupportAccess = getCompanyPlanAccess($user, 'supports');
        if ($role->group == 'company' && !$isSupportAccess) {
            abort(403);
        }
        $companyData = $user->company()->first();

        if ($role->group == 'zevo' && !is_null($eap->company_id)) {
            abort(403);
        } elseif ($role->group == 'company' && $companyData->id != $eap->company_id) {
            abort(403);
        } elseif ($role->group == 'reseller' && $companyData->id != $eap->company_id) {
            abort(403);
        }

        try {
            $data            = array();
            $data['isSA']    = ($role->group == 'zevo' || ($role->group == 'reseller' && $companyData->parent_id == null));
            $data['company'] = $this->getAllCompaniesGroupType($role->group, $companyData);
            $data['eap']     = $eap;
            $eap_companys    = array();
            if (!empty($eap->eapcompany)) {
                if (($role->group == 'zevo' || ($role->group == 'reseller' && $companyData->parent_id == null))) {
                    // Display selected departments in tree for support for ZSA and RSA
                    $eap_companys = DB::table('eap_department')->where('eap_id', $eap->id)->pluck('department_id')->toArray();
                } else {
                    // Display selected departments for support for ZCA and RCA
                    $eap_companys = $eap->eapDepartment->pluck('id')->toArray();
                }

                $eap_locations = DB::table('eap_department')->where('eap_id', $eap->id)->pluck('location_id')->toArray();
            }
            $data['eap_companys']      = $eap_companys;
            $data['eap_locations']     = $eap_locations;
            $data['companyLocations']  = [];
            $data['companyDepartment'] = [];
            $data['loggedInUserRole']  = $role;
            $data['companyData']       = $companyData;
            $data['media']             = $eap->getFirstMedia('logo');
            $data['ga_title']          = trans('page_title.eap.edit');

            $locationArray = [];
            if (!empty($eap->locations)) {
                $locationArray = explode(',', $eap->locations);
            }
            $companyLocation   = \App\Models\CompanyLocation::select('id', 'name');
            $companyDepartment = \App\Models\DepartmentLocation::select('department_id');
            if (sizeof($locationArray)) {
                $companyDepartment = $companyDepartment->whereIn('company_location_id', $locationArray);
            }
            if ($role->group == 'company') {
                $companyLocation   = $companyLocation->where('company_id', $companyData->id);
                $companyDepartment = $companyDepartment->where('company_id', $companyData->id);
            } elseif ($role->group == 'reseller') {
                if (is_null($companyData->parent_id)) {
                    $companies = Company::where('id', $companyData->id)
                        ->orwhere('parent_id', $companyData->id)
                        ->where('status', 1)
                        ->pluck('id')
                        ->toArray();
                    $companyLocation   = $companyLocation->whereIn('company_id', $companies);
                    $companyDepartment = $companyDepartment->whereIn('company_id', $companies);
                } elseif (!is_null($companyData->parent_id)) {
                    $companyLocation   = $companyLocation->where('company_id', $companyData->id);
                    $companyDepartment = $companyDepartment->where('company_id', $companyData->id);
                }
            }
            $companyLocation           = $companyLocation->get()->pluck('name', 'id')->toArray();
            $data['companyLocations']  = $companyLocation;
            $companyDepartment         = $companyDepartment->get()->pluck('department_id')->toArray();
            $data['companyDepartment'] = \App\Models\Department::whereIn('id', $companyDepartment)->select('id', 'name')
                ->get()
                ->pluck('name', 'id')
                ->toArray();
            return \view('admin.eap.edit', $data);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('admin.support.list')->with('message', $messageData);
        }
    }

    /**
     * @param EditEAPRequest $request
     *
     * @return RedirectResponse
     */
    public function update(EditEAPRequest $request, EAP $eap)
    {
        $user   = auth()->user();
        if (!access()->allow('update-support')) {
            abort(403);
        }
        try {
            \DB::beginTransaction();

            $userLogData = [
                'user_id'   => $user->id,
                'user_name' => $user->full_name,
            ];
            $oldUsersData  = array_merge($userLogData, $eap->toArray());
            $data = $eap->updateEntity($request->all());

            $updatedUsersData   = array_merge($userLogData, $request->all());
            $finalLogs          = ['olddata' => $oldUsersData, 'newdata' => $updatedUsersData];
            $this->auditLogRepository->created("Support updated successfully", $finalLogs);

            if ($data) {
                \DB::commit();
                $messageData = [
                    'data'   => trans('eap.message.data_update_success'),
                    'status' => 1,
                ];

                return \Redirect::route('admin.support.list')->with('message', $messageData);
            } else {
                \DB::rollback();
                $messageData = [
                    'data'   => trans('eap.message.something_wrong_try_again'),
                    'status' => 0,
                ];
                return \Redirect::route('admin.eap.edit', $eap->id)->with('message', $messageData);
            }
        } catch (\Exception $exception) {
            \DB::rollback();
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('admin.support.list')->with('message', $messageData);
        }
    }

    /**
     * @param  $id
     *
     * @return View
     */
    public function delete(EAP $eap)
    {
        $user   = auth()->user();
        if (!access()->allow('delete-support')) {
            abort(403);
        }
        try {
            $userLogData = [
                'user_id'   => $user->id,
                'user_name' => $user->full_name,
            ];
            $logs  = array_merge($userLogData, ['deleted_support_id' => $eap->id,'deleted_support_name' => $eap->title]);
            $this->auditLogRepository->created("Support deleted successfully", $logs);

            return $eap->deleteRecord();
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('admin.support.list')->with('message', $messageData);
        }
    }

    /**
     * To reordering EAP records
     *
     * @param  $id
     *
     * @return View
     */
    public function reorderingEap(Request $request)
    {
        try {
            DB::beginTransaction();
            $data = [
                'status'  => false,
                'message' => '',
            ];
            $positions = $request->input('positions', []);

            if (!empty($positions)) {
                $updated = $this->eap_list->reorderingEap($positions);
                if ($updated) {
                    $data['status']  = true;
                    $data['message'] = trans('eap.message.order_update_success');
                } else {
                    $data['message'] = trans('eap.message.failed_updated_order');
                }
            } else {
                $data['message'] = trans('eap.message.nothing_change_order');
            }

            (($data['status']) ? DB::commit() : DB::rollback());
            return $data;
        } catch (\Exception $exception) {
            DB::rollback();
            report($exception);
            return [
                'status'  => false,
                'message' => trans('eap.message.something_wrong'),
            ];
        }
    }

    /**
     * Get All Companies Group Type
     *
     * @return array
     **/
    protected function getAllCompaniesGroupType($role = '', $companiesDetails = [])
    {
        $groupType = config('zevolifesettings.content_company_group_type');
        if ($role == 'reseller') {
            unset($groupType[1]);
        }
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
                        ->where('is_reseller', true);
                    if ($role == 'reseller') {
                        $companies->where('id', $companiesDetails->id);
                    }
                    $companies = $companies
                        ->get()
                        ->toArray();
                    break;
                case 'Child':
                    $companies = Company::select('name', 'id', 'plan_status', 'subscription_start_date', 'subscription_end_date')
                        ->whereNotNull('parent_id')
                        ->where('is_reseller', false);
                    if ($role == 'reseller') {
                        $companies->where('parent_id', $companiesDetails->id);
                    }
                    $companies = $companies
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
                            $departmentArray[] = [
                                'id'   => $departmentItem['id'],
                                'name' => $departmentItem['name'],
                            ];
                        }

                        $locationArray[] = [
                            'locationName' => $locationItem['name'],
                            'locationId'   => $locationItem['id'],
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
     * @param  Request $request
     * @return JsonResponse
     */
    public function getDepartments(Request $request)
    {
        $user       = auth()->user();
        $role       = getUserRole();
        $companyIds = [];
        try {
            $company = $user->company->first();
            if ($role->group == 'company') {
                $companyIds[] = $company->id;
            } elseif ($role->group == 'reseller') {
                if (is_null($company->parent_id)) {
                    $companies = Company::where('id', $company->id)
                        ->orwhere('parent_id', $company->id)
                        ->where('status', 1)
                        ->pluck('id')
                        ->toArray();
                    $companyIds = $companies;
                } elseif (!is_null($company->parent_id)) {
                    $companyIds[] = $company->id;
                }
            }
            return $this->eap_list->getDepartment($request->all(), $role, $companyIds);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('eap.message.something_wrong_try_again'),
                'status' => 0,
            ];
            return \Redirect::route('admin.support.list')->with('message', $messageData);
        }
    }

    /**
     * function for stick a EAP
     * @param EAP $eap
     */
    public function stickUnstick(EAP $eap, Request $request)
    {
        try {
            $action  = $request->input('action', "stick");
            $data    = [
                'status'  => 0,
                'message' => trans('eap.modal.failed_support_action', [
                    'action' => $action,
                ]),
            ];
            $stickCount      = config('zevolifesettings.eap.all');
            $stickWarningMsg = trans('eap.message.max_three_support');

            if ($action == 'stick') {
                if ($eap->is_stick == 1) {
                    $data['message'] = trans('eap.message.support_already_stick');
                } else {
                    $sticked_support_count = EAP::where('is_stick', 1)->count();

                    if ($sticked_support_count >= $stickCount) {
                        $data['message'] = $stickWarningMsg;
                    } else {
                        $sticked = $eap->stickUnstick($action);
                        if ($sticked) {
                            $data['status']  = 1;
                            $data['message'] = trans('eap.message.support_stick_successfully');
                        } else {
                            $data['message'] = trans('eap.message.failed_stick_support');
                        }
                    }
                }
            } else {
                if ($eap->is_stick == 0) {
                    $data['message'] = trans('eap.message.support_unstick');
                } else {
                    $sticked = $eap->stickUnstick($action);
                    if ($sticked) {
                        $data['status']  = 1;
                        $data['message'] = trans('eap.message.support_unstick_successfully');
                    } else {
                        $data['message'] = trans('eap.message.failed_unstick_support');
                    }
                }
            }

            return response()->json($data, 200);
        } catch (\Exception $exception) {
            report($exception);
            return response()->json([
                'status'  => 0,
                'message' => trans('labels.common_title.something_wrong'),
            ], 500);
        }
    }
}
