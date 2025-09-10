<?php

namespace App\Http\Controllers\Admin;

use App\Events\UserChangePasswordEvent;
use App\Events\UserForgotPasswordEvent;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CreateUserRequest;
use App\Http\Requests\Admin\EditUserRequest;
use App\Http\Requests\Admin\NpsReportExportRequest;
use App\Http\Requests\Admin\UpdateUserProfileRequest;
use App\Models\Calendly;
use App\Models\Company;
use App\Models\CompanyRoles;
use App\Models\CronofySchedule;
use App\Models\DigitalTherapyService;
use App\Models\DigitalTherapySpecific;
use App\Models\EventBookingLogs;
use App\Models\EventPresenters;
use App\Models\HealthCoachAvailability;
use App\Models\Role;
use App\Models\Service;
use App\Models\ServiceSubCategory;
use App\Models\SubCategory;
use App\Models\Team;
use App\Models\Timezone;
use App\Models\User;
use App\Models\UserDeviceHistory;
use App\Repositories\AuditLogRepository;
use Breadcrumbs;
use Carbon\Carbon;
use DB;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

/**
 * Class UserController
 *
 * @package App\Http\Controllers\Admin
 */
class UserController extends Controller
{
    /**
     * variable to store the model object
     * @var User
     */
    protected $model;

    /**
     * @var AuditLogRepository $auditLogRepository
     */
    private $auditLogRepository;

    /**
     * contructor to initialize model object
     * @param User $model ;
     */
    public function __construct(User $model, AuditLogRepository $auditLogRepository)
    {
        $this->model              = $model;
        $this->auditLogRepository = $auditLogRepository;
        $this->bindBreadcrumbs();
    }

    /**
     * bind breadcrumbs of user modules
     */
    private function bindBreadcrumbs()
    {
        // user crud
        Breadcrumbs::for ('user.index', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push('Users');
        });
        Breadcrumbs::for ('user.create', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push('Users', route('admin.users.index'));
            $trail->push('Add User');
        });
        Breadcrumbs::for ('user.edit', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push('Users', route('admin.users.index'));
            $trail->push('Edit User');
        });
        Breadcrumbs::for ('user.tracker-history', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push('Users', route('admin.users.index'));
            $trail->push("Tracker History");
        });
        // edit profile
        Breadcrumbs::for ('user.edit-profile', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push('Edit Profile');
        });
    }

    /**
     * @return View
     */
    public function index(Request $request)
    {
        if (!access()->allow('manage-user')) {
            abort(403);
        }
        try {
            // to check is valid referrer has passed or not
            $referrer = $request->get('referrer', '');
            if (!empty($referrer) && !in_array($referrer, ['teams'])) {
                $messageData = [
                    'data'   => 'Invalid referrer passed.',
                    'status' => 0,
                ];
                return \Redirect::route('admin.users.index')->with('message', $messageData);
            }

            $user    = auth()->user();
            $role    = getUserRole($user);
            $company = $user->company()->first();
            $selectedRole = '';
            if (!empty($request->role)) {
                $selectedRole = Role::find($request->role);
            }
            $data    = [
                'isSA'                 => ($role->group == 'zevo'),
                'isRSA'                => ($role->group == 'reseller' && isset($company) && is_null($company->parent_id)),
                'pagination'           => ($role->group == 'zevo' ? config('zevolifesettings.datatable.pagination.long') : config('zevolifesettings.datatable.pagination.short')),
                'companies'            => [],
                'userCompany'          => $company,
                'role'                 => $role->slug,
                'coaches'              => ['all' => 'All', 'yes' => 'Yes', 'no' => 'No'],
                'statuses'             => ['all' => 'All', 'active' => 'Active', 'blocked' => 'Blocked'],
                'ga_title'             => trans('page_title.users.users_list'),
                'teamSection'          => ($role->group == 'zevo' || $role->group == 'reseller' || ($role->group == 'company' && getCompanyPlanAccess($user, 'team-selection'))),
                'selectedRole'         => !empty($selectedRole) ? $selectedRole->slug : '',
                'responsibilitiesList' => config('zevolifesettings.responsibilitiesList'),
            ];

            if ($role->group != 'zevo') {
                if ($role->group == 'company') {
                    $data['companyRoles'] = CompanyRoles::select('roles.id', 'roles.name')
                        ->join('roles', function ($join) {
                            $join->on('roles.id', '=', 'company_roles.role_id');
                        })
                        ->where('company_roles.company_id', $company->id)
                        ->get()->pluck('name', 'id')->toArray();
                    $data['companyRoles'] = array_replace([2 => 'Zevo Company Admin', 3 => 'User'], $data['companyRoles']);
                    $data['companies']    = [$company->id => $company->name];
                    $data['teams']        = Team::with('company')
                        ->select('teams.name', 'teams.id')
                        ->whereHas('company', function ($query) use ($company) {
                            $query->where('company_id', $company->id);
                        })
                        ->pluck('name', 'id')
                        ->toArray();
                } elseif ($role->group == 'reseller') {
                    if (is_null($company->parent_id)) {
                        $companies = Company::where('id', $company->id)
                            ->orwhere('parent_id', $company->id)
                            ->where('status', 1)
                            ->pluck('id')
                            ->toArray();
                        $data['companyRoles'] = CompanyRoles::select('roles.id', 'roles.name')
                            ->join('roles', function ($join) {
                                $join->on('roles.id', '=', 'company_roles.role_id');
                            })
                            ->whereIn('company_roles.company_id', $companies)
                            ->get()
                            ->pluck('name', 'id')
                            ->toArray();
                        $rsaRole              = Role::where(['slug' => 'reseller_super_admin', 'default' => 1])->first();
                        $data['companyRoles'] = array_replace([$rsaRole->id => $rsaRole->name, 3 => 'User'], $data['companyRoles']);
                        if (count($companies) > 1) {
                            $rcaRole              = Role::where(['slug' => 'reseller_company_admin', 'default' => 1])->first();
                            $data['companyRoles'] = array_replace([$rcaRole->id => $rcaRole->name], $data['companyRoles']);
                        }
                        $data['companies'] = Company::select('id', 'name')
                            ->where(function ($query) use ($company) {
                                $query->where('parent_id', $company->id)
                                    ->orWhere('id', $company->id);
                            })
                            ->get()
                            ->pluck('name', 'id')
                            ->toArray();
                        if ($request->get('company')) {
                            $data['teams'] = Team::with('company')
                                ->whereHas('company', function ($query) use ($request) {
                                    $query->where('company_id', $request->get('company'));
                                })
                                ->get()
                                ->pluck('name', 'id')
                                ->toArray();
                        }
                    } elseif (!is_null($company->parent_id)) {
                        $data['companyRoles'] = CompanyRoles::select('roles.id', 'roles.name')
                            ->join('roles', function ($join) {
                                $join->on('roles.id', '=', 'company_roles.role_id');
                            })
                            ->where('company_roles.company_id', $company->id)
                            ->get()->pluck('name', 'id')->toArray();
                        $rcaRole              = Role::where(['slug' => 'reseller_company_admin', 'default' => 1])->first();
                        $data['companyRoles'] = array_replace([$rcaRole->id => $rcaRole->name, 3 => 'User'], $data['companyRoles']);
                        $data['companies']    = [$company->id => $company->name];
                        $data['teams']        = Team::with('company')
                            ->select('teams.name', 'teams.id')
                            ->whereHas('company', function ($query) use ($company) {
                                $query->where('company_id', $company->id);
                            })
                            ->pluck('name', 'id')
                            ->toArray();
                    }
                }

                // to check team is valid for the CA/RSA/RCA
                if (!empty($referrer) && $referrer == 'teams') {
                    $team = $request->get('team', 0);
                    if (!empty($team) && !array_key_exists($team, $data['teams'])) {
                        $messageData = [
                            'data'   => 'Team does not belong to logged-in admin or maybe not exist',
                            'status' => 0,
                        ];
                        return \Redirect::route('admin.teams.index')->with('message', $messageData);
                    }
                }
            } else {
                $data['companyRoles'] = Role::select('name', 'id')
                    ->get()
                    ->pluck('name', 'id')
                    ->toArray();
                $data['wbsstatus'] = ['all' => 'All', 'verified' => 'Verified', 'unverified' => 'Unverified', 'unassigned' => 'Verified & Unassigned'];
                $data['companies'] = Company::get()->pluck('name', 'id')->toArray();
                if ($request->get('company')) {
                    $data['teams'] = Team::with('company')
                        ->whereHas('company', function ($query) use ($request) {
                            $query->where('company_id', $request->get('company'));
                        })
                        ->get()
                        ->pluck('name', 'id')
                        ->toArray();
                }
            }

            if(!empty($selectedRole) && $selectedRole->slug == 'wellbeing_specialist'){
                $data['wbsstatus'] = ['all' => 'All', 'verified' => 'Verified', 'unverified' => 'Unverified', 'unassigned' => 'Verified & Unassigned'];
            }

            return \view('admin.user.index', $data);
        } catch (\Exception $exception) {
            report($exception);
            return response('Something wrong', 400)->header('Content-Type', 'text/plain');
        }
    }

    /**
     * @return View
     */
    public function create(Request $request)
    {
        if (!access()->allow('create-user')) {
            abort(403);
        }

        try {
            $user                = auth()->user();
            $company             = $user->company->first();
            $role                = getUserRole($user);
            $advanceNoticePeriod = [];
            for ($i = 2; $i <= 14; $i++) {
                $hours                       = 24 * $i;
                $advanceNoticePeriod[$hours] = $i . ' Days';
            }

            $data = [
                'company'                  => $company,
                'role'                     => $role,
                'roleGroupData'            => config('zevolifesettings.role_group'),
                'userTypes'                => config('zevolifesettings.userTypes'),
                'availability'             => config('zevolifesettings.hc_availability_status'),
                'gender'                   => config('zevolifesettings.gender'),
                'hc_availability_days'     => config('zevolifesettings.hc_availability_days'),
                'companies'                => [],
                'expertise'                => SubCategory::where(['category_id' => 6, 'status' => 1])->get()->pluck('name', 'id')->toArray(),
                'timezones'                => Timezone::get()->pluck('name', 'name')->toArray(),
                'ga_title'                 => trans('page_title.users.create'),
                'personalFieldsVisibility' => true,
                'editStartDate'            => true,
                'userSlots'                => [],
                'presenterSlots'           => [],
                'teamSection'              => ($role->group == 'zevo' || $role->group == 'reseller' || ($role->group == 'company' && getCompanyPlanAccess($user, 'team-selection'))),
                'loggedInUser'             => getUserRole($user),
                'userLanguage'             => config('zevolifesettings.userLanguage'),
                'video_conferencing_mode'  => config('zevolifesettings.video_conferencing_mode'),
                'shift'                    => config('zevolifesettings.shift'),
                'servicesArray'            => $this->getAllServicesGroupType(),
                'responsibilitiesList'     => config('zevolifesettings.responsibilitiesList'),
                'advanceNoticePeriod'      => $advanceNoticePeriod,
            ];

            if ($role->group == 'zevo') {
                $data['companyRoles'] = Role::select('name', 'id')
                    ->where('group', 'zevo')
                    ->whereNotIn('slug', ['user', 'health_coach', 'counsellor', 'wellbeing_team_lead'])
                    ->get()->pluck('name', 'id')->toArray();

                $data['skills'] = SubCategory::where(['category_id' => 8, 'status' => 1])->get()->pluck('name', 'id')->toArray();
            } elseif ($role->group == 'reseller') {
                $data['personalFieldsVisibility'] = false;
                if ($company->is_reseller) {
                    $data['companyRoles'] = [];
                    $data['companies']    = Company::select('id', 'name')
                        ->where(function ($where) use ($company) {
                            $where
                                ->where('id', $company->id)
                                ->orWhere('parent_id', $company->id);
                        })
                        ->get()->pluck('name', 'id')->toArray();
                    $data['company'] = null;
                } elseif (!is_null($company->parent_id)) {
                    $data['companyRoles'] = CompanyRoles::select('roles.id', 'roles.name')
                        ->join('roles', function ($join) {
                            $join->on('roles.id', '=', 'company_roles.role_id');
                        })
                        ->where('company_roles.company_id', $company->id)
                        ->get()->pluck('name', 'id')->toArray();
                    $rcaRole = Role::where(['slug' => 'reseller_company_admin', 'default' => 1])->first();

                    $data['companyRoles'] = array_replace([$rcaRole->id => $rcaRole->name], $data['companyRoles']);
                    $data['companies']    = [$company->id => $company->name];
                    $data['company']      = $user->teams()->first();
                }
            } elseif ($role->group == 'company') {
                $data['companyRoles'] = CompanyRoles::select('roles.id', 'roles.name')
                    ->join('roles', function ($join) {
                        $join->on('roles.id', '=', 'company_roles.role_id');
                    })
                    ->where('company_roles.company_id', $company->id)
                    ->get()->pluck('name', 'id')->toArray();
                $data['companyRoles'] = array_replace([2 => 'Zevo Company Admin'], $data['companyRoles']);
                $data['companies']    = [$company->id => $company->name];
                $data['company']      = $user->teams()->first();
            }

            // Set default value for event presenter avaibility
            $avaibilityDays = config('zevolifesettings.hc_event_presenter_availability_days');
            foreach ($avaibilityDays as $day => $dayFullName ){
               $presenterSlots[] = [
                'day'           => $day,
                'start_time'    => '10:00 AM',
                'end_time'      => '05:00 PM'
               ];
            }
            
            $daywisePresenterSlots = [];
            if (!empty($presenterSlots)) {
                foreach ($presenterSlots as $slots) {
                    $daywisePresenterSlots[$slots['day']][] = [
                        'id'         => null,
                        'start_time' => $slots['start_time'],
                        'end_time'   => $slots['end_time']
                    ];
                }
            }
            $data['presenterSlots'] = $daywisePresenterSlots;

            return \view('admin.user.create', $data);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('admin.users.index')->with('message', $messageData);
        }
    }

    /**
     * @param CreateUserRequest $request
     *
     * @return RedirectResponse
     */
    public function store(CreateUserRequest $request)
    {
        $user = auth()->user();
        if (!access()->allow('create-user')) {
            return response()->json([
                'message' => trans('labels.common_title.unauthorized_access'),
            ], 401);
        }

        $roles = json_decode(urldecode($request['companyRolesarr']), true);
        if ($request['user_type'] != 'health_coach' && $request['user_type'] != 'counsellor') {
            if ($request['role'] != null && !in_array($request['role'], array_keys($roles))) {
                return response()->json([
                    'message' => 'You are not Authorized to create user with this role.',
                    'status'  => false,
                ], 412);
            }
        }

        try {
            \DB::beginTransaction();
            $userLogData = [
                'user_id'   => $user->id,
                'user_name' => $user->full_name,
            ];
            $data = $this->model->storeEntity($request->all());

            $logData = array_merge($userLogData, $request->all());
            $this->auditLogRepository->created("User Added Successfully", $logData);

            if (isset($data['status']) && $data['status']) {
                \DB::commit();
                \Session::put('message', [
                    'data'   => 'User has been added successfully!',
                    'status' => 1,
                ]);
                return response()->json([
                    'message' => 'User has been added successfully!',
                    'status'  => true,
                ], 200);
            } else {
                \DB::rollback();
                return response()->json([
                    'message' => (!empty($data['message']) ? $data['message'] : trans('labels.common_title.something_wrong_try_again')),
                    'status'  => false,
                ], 412);
            }
        } catch (\Exception $exception) {
            \DB::rollback();
            report($exception);
            return response()->json([
                'message' => trans('labels.common_title.something_wrong_try_again'),
                'status'  => false,
            ], 500);
        }
    }

    /**
     * @return View
     */
    public function edit(User $user, Request $request)
    {
        if (!access()->allow('update-user')) {
            abort(403);
        }
        $loggedUser    = auth()->user();
        $role          = getUserRole($loggedUser);
        $loggedCompany = $loggedUser->company()->first();
        $userCompany   = $user->company()->first();
        $emaildisabled = (!empty($role->slug) && $role->slug != 'super_admin');

        if ($role->group != 'zevo') {
            if ($role->group == 'company') {
                if ($loggedCompany->id != $userCompany->id) {
                    return view('errors.401');
                }
            } elseif ($role->group == 'reseller') {
                if ($loggedCompany->is_reseller) {
                    $allcompanies = Company::select('id')->where('parent_id', $loggedCompany->id)->orWhere('id', $loggedCompany->id)->get()->pluck('id')->toArray();
                    if (is_null($userCompany) || (!in_array($userCompany->id, $allcompanies))) {
                        return view('errors.401');
                    }
                } elseif (!$loggedCompany->is_reseller && $userCompany->id != $loggedCompany->id) {
                    return view('errors.401');
                }
            }
        }

        $currTeam = $user->teams()->select('teams.id')->first();

        try {
            $wsDetails           = $user->wsuser()->first();
            $oldMexicoTimezone   = config('zevolifesettings.mexico_city_timezone.old_timezone');
            $newMexicoTimezone   = config('zevolifesettings.mexico_city_timezone.new_timezone');
            $advanceNoticePeriod = [];
            for ($i = 2; $i <= 14; $i++) {
                $hours                       = 24 * $i;
                $advanceNoticePeriod[$hours] = $i . ' Days';
            }
            $data = [
                'record'                  => $user,
                'wsDetails'               => $wsDetails,
                'currTeam'                => (!empty($currTeam) ? $currTeam->id : null),
                'role'                    => getUserRole($user),
                'roleGroupData'           => config('zevolifesettings.role_group'),
                'userTypes'               => config('zevolifesettings.userTypes'),
                'availability'            => config('zevolifesettings.hc_availability_status'),
                'gender'                  => config('zevolifesettings.gender'),
                'hc_availability_days'    => config('zevolifesettings.hc_availability_days'),
                'companies'               => [],
                'profileData'             => $user->profile,
                'weightData'              => $user->weights()->orderByDesc('user_weight.updated_at')->first(),
                'editStartDate'           => ($user->surveyUserLogs()->count() < 0),
                'expertise'               => SubCategory::where(['category_id' => 6, 'status' => 1])->get()->pluck('name', 'id')->toArray(),
                'userExpertise'           => [],
                'userSlots'               => [],
                'presenterSlots'          => [],
                'timezones'               => Timezone::get()->pluck('name', 'name')->toArray(),
                'selectedTimezone'        => ($user->timezone == $newMexicoTimezone && $user->is_timezone) ? $oldMexicoTimezone : $user->timezone,
                'userCompany'             => $userCompany,
                'ga_title'                => trans('page_title.users.edit'),
                'teamSection'             => ($role->group == 'zevo' || $role->group == 'reseller' || ($role->group == 'company' && getCompanyPlanAccess($user, 'team-selection'))),
                'loggedInUser'            => getUserRole($loggedUser),
                'userLanguage'            => config('zevolifesettings.userLanguage'),
                'video_conferencing_mode' => config('zevolifesettings.video_conferencing_mode'),
                'shift'                   => config('zevolifesettings.shift'),
                'servicesArray'           => $this->getAllServicesGroupType(),
                'service_users'           => $user->userservices->pluck('id')->toArray(),
                'language'                => (!empty($wsDetails)) ? explode(',', $wsDetails->language) : [],
                'emaildisabled'           => $emaildisabled,
                'responsibilitiesList'    => config('zevolifesettings.responsibilitiesList'),
                'advanceNoticePeriod'     => $advanceNoticePeriod,
            ];

            if ($user->availability_status == 2) {
                $customLeaveDates = $user->healthCocahAvailability()->where(['update_from' => 'profile', 'user_id' => $user->id])->get();
                if (!empty($customLeaveDates)) {
                    foreach ($customLeaveDates as $customLeaveDateIndex => $customLeaveDateValue) {
                        $data['customLeaveDates'][$customLeaveDateIndex] = [
                            'id'        => $customLeaveDateValue->id,
                            'from_date' => Carbon::parse($customLeaveDateValue->from_date)->setTimezone($user->timezone)->format('Y-m-d'),
                            'to_date'   => Carbon::parse($customLeaveDateValue->to_date)->setTimezone($user->timezone)->format('Y-m-d'),
                        ];
                    }
                }
            }
            $data['company'] = $user->teams()->first();
            $group           = $user->roles()->where('slug', '!=', 'user')->first();
            if (!empty($data['company'])) {
                if (empty($group)) {
                    $group = $user->roles()->where('slug', 'user')->first();
                }
            }
            $data['roleData'] = $group;
            if ($group->group == 'company' || $group->group == 'reseller') {
                $data['companies'] = [$userCompany->id => $userCompany->name];
                if (!empty($data['teamSection']) && !$data['teamSection']) {
                    $data['company'] = $userCompany;
                }
                $data['companyRoles'] = CompanyRoles::select('roles.id', 'roles.name')
                    ->join('roles', function ($join) {
                        $join->on('roles.id', '=', 'company_roles.role_id');
                    })
                    ->where('company_roles.company_id', $userCompany->id)
                    ->get()->pluck('name', 'id')->toArray();
                if (!$userCompany->is_reseller && is_null($userCompany->parent_id)) {
                    $data['companyRoles'] = array_replace([2 => 'Zevo Company Admin', 3 => 'User'], $data['companyRoles']);
                } elseif ($userCompany->is_reseller && is_null($userCompany->parent_id)) {
                    $rsaRole              = Role::where(['slug' => 'reseller_super_admin', 'default' => 1])->first();
                    $data['companyRoles'] = array_replace([$rsaRole->id => $rsaRole->name, 3 => 'User'], $data['companyRoles']);
                } elseif (!$userCompany->is_reseller && !is_null($userCompany->parent_id)) {
                    $rcaRole              = Role::where(['slug' => 'reseller_company_admin', 'default' => 1])->first();
                    $data['companyRoles'] = array_replace([$rcaRole->id => $rcaRole->name, 3 => 'User'], $data['companyRoles']);
                }
            } elseif ($group->group == 'zevo' || $group->slug == 'wellbeing_specialist') {
                $data['companyRoles'] = Role::select('name', 'id')
                    ->where('group', 'zevo')
                    ->whereNotIn('slug', ['user', 'health_coach', 'counsellor', 'wellbeing_specialist', 'wellbeing_team_lead'])
                    ->get()->pluck('name', 'id')->toArray();
                if ($user->is_coach || $group->slug == 'wellbeing_specialist') {
                    if ($user->is_coach) {
                        $data['expertise'] = SubCategory::where(['category_id' => 6, 'status' => 1])->get()->pluck('name', 'id')->toArray();
                    }
                    $data['userExpertise'] = $user->healthCocahExpertise()->select('sub_categories.name', 'sub_categories.id')->pluck('sub_categories.id')->toArray();
                    $userSlots             = $user->healthCocahSlots();
                    $presenterSlots        = $user->eventPresenterSlots();
                    $daywiseSlots          = [];
                    if ($userSlots->count() > 0) {
                        foreach ($userSlots->get() as $slots) {
                            $daywiseSlots[$slots->day][] = [
                                'id'         => $slots->id,
                                'start_time' => Carbon::createFromFormat('H:i:s', $slots->start_time, $user->timezone),
                                'end_time'   => Carbon::createFromFormat('H:i:s', $slots->end_time, $user->timezone),
                                // 'end_time'   => Carbon::createFromFormat('H:i:s', $slots->end_time, $user->timezone)->addSeconds(1),
                            ];
                        }
                    }
                    $data['userSlots'] = $daywiseSlots;

                    $daywisePresenterSlots          = [];
                    if ($presenterSlots->count() > 0) {
                        foreach ($presenterSlots->get() as $slots) {
                            $daywisePresenterSlots[$slots->day][] = [
                                'id'         => $slots->id,
                                'start_time' => Carbon::createFromFormat('H:i:s', $slots->start_time, $user->timezone),
                                'end_time'   => Carbon::createFromFormat('H:i:s', $slots->end_time, $user->timezone),
                            ];
                        }
                    }
                    $data['presenterSlots'] = $daywisePresenterSlots;
                }

                
                
                $data['skills'] = SubCategory::where(['category_id' => 8, 'status' => 1])->get()->pluck('name', 'id')->toArray();

                $data['userSkills'] = $user->counsellorSkills()->select('sub_categories.name', 'sub_categories.id')->pluck('sub_categories.id')->toArray();
            }
            $data['group'] = $group->slug;
            return \view('admin.user.edit', $data);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('admin.users.index')->with('message', $messageData);
        }
    }

    /**
     * @param EditUserRequest $request
     *
     * @return RedirectResponse
     */
    public function update(User $user, EditUserRequest $request)
    {
        if (!access()->allow('update-user')) {
            return response()->json([
                'message' => trans('labels.common_title.unauthorized_access'),
                'status'  => false,
            ], 401);
        }
        $loggedUser     = auth()->user();
        $loggedUserRole = getUserRole($loggedUser);
        $company        = $user->company()->first();
        $roles          = json_decode(urldecode($request['companyRolesarr']), true);
        if ($request['user_type'] != 'health_coach' && $request['user_type'] != 'counsellor') {
            if ($request['role'] != null && !in_array($request['role'], array_keys($roles))) {
                return response()->json([
                    'message' => 'You are not Authorized to update user with this role.',
                    'status'  => false,
                ], 412);
            }
        }
        try {
            \DB::beginTransaction();
            
            if (isset($company->has_domain) && $company->has_domain && $loggedUserRole->slug == 'super_admin') {
                $validator = Validator::make($request->toArray(), [
                    'email' => [function ($fail) use ($request, $company) {
                        $domain        = list(, $domain)        = \explode('@', strtolower($request['email']));
                        $companyDomain = $company->domains->pluck('domain')->toArray();
                        $list          = new Collection($companyDomain);
                        if (!$list->contains($domain[1])) {
                            $fail(':error');
                        }
                    }],
                ]);
                if ($validator->fails()) {
                    return response()->json([
                        'message' => trans('user.edit_profile.messages.allow_company_domain'),
                        'status'  => false,
                    ], 500);
                }
            }
            $userLogData = [
                'user_id'   => $loggedUser->id,
                'user_name' => $loggedUser->full_name,
            ];
            $oldUsersData       = array_merge($userLogData, $user->toArray());
            $data               = $user->updateEntity($request->all());
            $updatedUsersData   = array_merge($userLogData, $request->all());
            $finalLogs          = ['olddata' => $oldUsersData, 'newdata' => $updatedUsersData];
            $this->auditLogRepository->created("User Updated Successfully", $finalLogs);

            if ($data) {
                \DB::commit();

                \Session::put('message', [
                    'data'   => 'User has been updated successfully!',
                    'status' => 1,
                ]);
                return response()->json([
                    'message' => 'User has been updated successfully!',
                    'status'  => true,
                ], 200);
            } else {
                \DB::rollback();
                return response()->json([
                    'message' => (!empty($data['message']) ? $data['message'] : trans('labels.common_title.something_wrong_try_again')),
                    'status'  => false,
                ], 412);
            }
        } catch (\Exception $exception) {
            \DB::rollback();
            report($exception);
            return response()->json([
                'message' => trans('labels.common_title.something_wrong_try_again'),
                'status'  => false,
            ], 500);
        }
    }

    /**
     * @param Request $request
     *
     * @return View
     */

    public function getUsers(Request $request)
    {
        if (!access()->allow('manage-user')) {
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
     * @param  User $user
     *
     * @return View
     */

    public function delete(User $user)
    {
        $loggedInuser = auth()->user();
        if (!access()->allow('delete-user')) {
            abort(403);
        }
        try {
            if ($user->is_coach) {
                // get associated events of HC
                $associatedEvents = EventPresenters::select('event_id')
                    ->where('user_id', $user->id)
                    ->groupBy('event_id')
                    ->get()->pluck('event_id')->toArray();
                // get booked events count among from associated events
                $openEventsCount = EventBookingLogs::select('id')
                    ->where('status', '4')
                    ->whereIn('event_id', $associatedEvents)
                    ->count('id');
                if ($openEventsCount > 0) {
                    return array('deleted' => 'associatedPresenter');
                }
            }

            $company = $user->companyAccess()->first();
            if (!empty($company)) {
                $companyModeratorsCount = $company->moderators()->count();
                if ($companyModeratorsCount == 1) {
                    return array('deleted' => 'company_admin', 'company' => $company->name);
                }

                $groupRestriction = $user->company->first()->group_restriction;
                $adminOfGroups    = $user->groups()->get();

                if ($groupRestriction == 1 || $groupRestriction == 2) {
                    foreach ($adminOfGroups as $value) {
                        $randomGroupMember = $value->members()->whereNotIn('users.id', [$user->id])->first();
                        $user->groups()->where('id', $value->id)->first()->members()->detach();
                        if (!empty($randomGroupMember)) {
                            $user->groups()->where('id', $value->id)->update(['creator_id' => $randomGroupMember->id]);
                        }
                    }
                }
            }
            $userLogData = [
                'user_id'   => $loggedInuser->id,
                'user_name' => $loggedInuser->full_name,
            ];
            $logs  = array_merge($userLogData, ['deleted_user_name' => $user->full_name, 'deleted_user_email' => $user->email]);
            $this->auditLogRepository->created("Subcategory deleted successfully", $logs);

            return $user->deleteRecord();
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
     * Function used to change password of user landing page.
     *
     * @param Request $request
     * @return view
     */
    public function changePasswordForm(Request $request)
    {
        try {
            $data             = array();
            $data['ga_title'] = trans('page_title.changepassword');
            return \view('admin.user.changepass', $data);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('dashboard')->with('message', $messageData);
        }
    }

    /**
     * Function used to change password of user landing page.
     *
     * @param Request $request
     * @return view
     */
    public function changePassword(Request $request)
    {
        try {
            $user   = Auth::user();
            $result = $this->model->updatePassword($user, $request->all());
            if ($result) {
                $messageData = [
                    'data'   => trans('labels.user.pass_change_success'),
                    'status' => 1,
                ];

                // fire change password event
                event(new UserChangePasswordEvent($user, $request->get('password')));

                Auth::logout();
                \Session::flush();

                return \Redirect::route('login')->with('message', $messageData);
            } else {
                $messageData = [
                    'data'   => trans('labels.user.old_pass_error'),
                    'status' => 0,
                ];
                return \Redirect::route('admin.users.changepassword')->with('message', $messageData);
            }
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('dashboard')->with('message', $messageData);
        }
    }

    public function markStatus(User $user, Request $request)
    {
        $loggedInUser = auth()->user();
        if (!access()->allow('mark-user')) {
            abort(403);
        }
        try {
            $user->updateStatus();
            $userLogData = [
                'user_id'   => $loggedInUser->id,
                'user_name' => $loggedInUser->full_name,
            ];
            $isBlocked = ($user->is_blocked) ? true : false;
            $logs  = array_merge($userLogData, ['is_blocked' => $isBlocked]);
            $this->auditLogRepository->created("User status updated", $logs);
            $messageData = [
                'data'   => "User status has been updated successfully!",
                'status' => 1,
            ];
            return \Redirect::route('admin.users.index')->with('message', $messageData);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('admin.users.index')->with('message', $messageData);
        }
    }

    /**
     * Display the set new password view for the given token.
     *
     * If no token is present, display the link request form.
     *
     * @param string|null $token
     *
     * @return \Illuminate\Http\Response
     */
    public function getSetNewPassword(Request $request)
    {
        $token = $request->get('token');
        if (!empty($token) && !is_null($token)) {
            try {
                $userEmail = decrypt($token);

                $user = User::where('email', $userEmail)->first();

                if (isset($user) && !empty($user->email_verified_at)) {
                    return \Redirect::route('login');
                }

                if (!is_object($user)) {
                    $messageData = [
                        'data'   => trans('non-auth.set-password.messages.registration_with_link_error'),
                        'status' => 0,
                    ];
                    return \Redirect::route('login')->with('message', $messageData);
                }

                $branding = getBrandingData();

                $data = [
                    'token'    => $token,
                    'email'    => $userEmail,
                    'branding' => $branding,
                ];

                $data['ga_title'] = trans('page_title.set-password');
                return view('auth.set-password', $data);
            } catch (\Exception $e) {
                report($e);
                $messageData = [
                    'data'   => trans('non-auth.set-password.messages.registration_with_link_error'),
                    'status' => 0,
                ];
                return \Redirect::route('login')->with('message', $messageData);
            }
        }
        return \Redirect::route('login');
    }

    /**
     * Function used to verify the set password for user after first time registration.
     *
     * @param Request $request
     * @return view
     */
    public function postSetNewPassword(Request $request)
    {
        try {
            $payload   = $request->except('_token');
            $userEmail = decrypt($payload['token']);
            $user      = $this->model->findByEmail($userEmail);

            if (!empty($user->email_verified_at)) {
                $messageData = [
                    'data'   => trans('non-auth.set-password.messages.already_confirmed'),
                    'status' => 2,
                ];
                return redirect()->back()->withInput()->with('message', $messageData);
            }

            if ($user->is_blocked == 1) {
                $messageData = [
                    'data'   => trans('non-auth.set-password.messages.user_blocked'),
                    'status' => 2,
                ];
                return redirect()->back()->withInput()->with('message', $messageData);
            }

            if ($userEmail == $payload['email']) {
                $password = Hash::make($payload['password']);
                $user     = User::where('email', $payload['email'])
                    ->update([
                        'password'          => $password,
                        'email_verified_at' => \Carbon\Carbon::now(),
                    ]);
            }

            $messageData = [
                'data'   => trans('non-auth.set-password.messages.registration_with_link'),
                'status' => 1,
            ];
        } catch (\Exception $e) {
            report($e);
            $messageData = [
                'data'   => $e->getMessage(),
                'status' => 0,
            ];
            return \Redirect::route('login')->with('message', $messageData);
        }

        return \Redirect::route('login')->with('message', $messageData);
    }

    /**
     * Function used to change password of user landing page.
     *
     * @param Request $request
     * @return view
     */
    public function changeUserPasswordForm(User $user, Request $request)
    {
        try {
            $data['user'] = $user;

            return \view('admin.user.changeuserpass', $data);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('admin.users.index')->with('message', $messageData);
        }
    }

    /**
     * Function used to change password of user landing page.
     *
     * @param Request $request
     * @return view
     */
    public function changeUserPassword(User $user, Request $request)
    {
        try {
            $result = $this->model->updatePassword($user, $request->all());
            if ($result) {
                $messageData = [
                    'data'   => trans('labels.user.pass_change_success'),
                    'status' => 1,
                ];

                // fire change password event
                event(new UserChangePasswordEvent($user, $request->get('password')));

                return \Redirect::route('admin.users.index')->with('message', $messageData);
            } else {
                $messageData = [
                    'data'   => trans('labels.user.old_pass_error'),
                    'status' => 0,
                ];
                return \Redirect::route('admin.users.changeuserpassword')->with('message', $messageData);
            }
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('admin.users.index')->with('message', $messageData);
        }
    }

    /**
     * @return View
     */
    public function editProfile(Request $request)
    {
        try {
            $user = auth()->user();
            $role = getUserRole($user);
            if ($role->slug == 'wellbeing_specialist') {
                $wsDetails = $user->wsuser()->first();
            }

            $oldMexicoTimezone               = config('zevolifesettings.mexico_city_timezone.old_timezone');
            $newMexicoTimezone               = config('zevolifesettings.mexico_city_timezone.new_timezone');
            $user                            = auth()->user();
            $wsDetails                       = $user->wsuser()->first();
            $wcDetails                       = $user->healthCoachUser()->first();
            $data                            = [];
            $appTimezone                     = config('app.timezone');
            $totalSessions                   = 0;
            $data['genders']                 = config('zevolifesettings.gender');
            $data['availability']            = config('zevolifesettings.hc_availability_status');
            $data['roleGroupData']           = config('zevolifesettings.role_group');
            $data['profileData']             = $user->profile;
            $data['birthDate']               = Carbon::parse($user->profile->birth_date)->todateString();
            $data['companies']               = Company::select('name', 'id')->pluck('name', 'id')->toArray();
            $data['record']                  = $user;
            $data['weightData']              = $user->weights()->orderByDesc('user_weight.updated_at')->first();
            $data['company']                 = $user->teams()->first();
            $data['userLanguage']            = config('zevolifesettings.userLanguage');
            $data['video_conferencing_mode'] = config('zevolifesettings.video_conferencing_mode');
            $data['shift']                   = config('zevolifesettings.shift');
            $data['service_users']           = $user->userservices->pluck('id')->toArray();
            $data['language']                = (!empty($wsDetails)) ? explode(',', $wsDetails->language) : [];
            $data['servicesArray']           = $this->getAllServicesGroupType();
            $data['timezones']               = Timezone::get()->pluck('name', 'name')->toArray();
            $data['selectedTimezone']        = ($user->timezone == $newMexicoTimezone && $user->is_timezone) ? $oldMexicoTimezone : $user->timezone;
            $data['wsDetails']               = $wsDetails;
            $data['wcDetails']               = $wcDetails;
            $data['expertise']               = SubCategory::where(['category_id' => 6, 'status' => 1])->get()->pluck('name', 'id')->toArray();
            $data['responsibilitiesList']    = config('zevolifesettings.responsibilitiesList');

            if ($user->availability_status == 2) {
                $customLeaveDates = $user->healthCocahAvailability()->where(['update_from' => 'profile', 'user_id' => $user->id])->get();
                if (!empty($customLeaveDates)) {
                    foreach ($customLeaveDates as $customLeaveDateIndex => $customLeaveDateValue) {
                        $data['customLeaveDates'][$customLeaveDateIndex] = [
                            'id'        => $customLeaveDateValue->id,
                            'from_date' => Carbon::parse($customLeaveDateValue->from_date)->setTimezone($user->timezone)->format('Y-m-d'),
                            'to_date'   => Carbon::parse($customLeaveDateValue->to_date)->setTimezone($user->timezone)->format('Y-m-d'),
                        ];
                    }
                }
            }

            $group = $user->roles()->where('slug', '!=', 'user')->first();
            if (!empty($data['company']) && empty($group)) {
                $group = $user->roles()->where('slug', 'user')->first();
            }
            if ($group->slug == 'company_admin' || $group->slug == 'reseller_super_admin' || $group->slug == 'reseller_company_admin') {
                $plan = $user->company()->first()->companyplan()->first();
                if (!empty($plan)) {
                    $data['currentPlan'] = $plan->name;
                }
            }
            if ($group->slug == 'counsellor') {
                $currentTime   = now($appTimezone)->todatetimeString();
                $totalSessions = Calendly::where('therapist_id', $user->id)
                    ->where("end_time", "<=", $currentTime)
                    ->whereNull('cancelled_at')
                    ->count();
            }

            if ($user->is_coach || $group->slug == 'wellbeing_specialist') {
                $data['userExpertise'] = $user->healthCocahExpertise()->select('sub_categories.name', 'sub_categories.id')->pluck('sub_categories.id')->toArray();
            }
            $loggedUser            = auth()->user();
            $data['totalSessions'] = $totalSessions;
            $data['roleData']      = $group;
            $data['ga_title']      = trans('page_title.editProfile');
            $data['loggedInUser']  = getUserRole($loggedUser);

            $advanceNoticePeriod = [];
            for ($i = 2; $i <= 14; $i++) {
                $hours                       = 24 * $i;
                $advanceNoticePeriod[$hours] = $i . ' Days';
            }
            $data['advanceNoticePeriod'] = $advanceNoticePeriod;

            return \view('admin.user.editprofile', $data);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('dashboard')->with('message', $messageData);
        }
    }

    /**
     * @param UpdateUserProfileRequest $request
     *
     * @return RedirectResponse
     */
    public function updateProfile(UpdateUserProfileRequest $request)
    {
        try {
            \DB::beginTransaction();
            $user         = auth()->user();
            $loggedInUser = getUserRole($user);
            $data         = $user->updateEntityProfile($request->all());
            if ($data) {
                \DB::commit();
                $messageData = [
                    'data'   => "User Profile has been updated successfully!",
                    'status' => 1,
                ];
                if ($loggedInUser->slug == 'wellbeing_specialist' || $loggedInUser->slug == 'health_coach') {
                    return \Redirect::route('dashboard')->with('message', $messageData);
                } else {
                    return \Redirect::route('admin.users.editProfile')->with('message', $messageData);
                }
            } else {
                \DB::rollback();
                $messageData = [
                    'data'   => "Something went wrong please try again.",
                    'status' => 0,
                ];
                return \Redirect::route('admin.users.editProfile')->with('message', $messageData);
            }
        } catch (\Exception $exception) {
            \DB::rollback();
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('admin.users.index')->with('message', $messageData);
        }
    }

    /**
     * To disconnect user from devices.
     *
     * @param User $user, Request $request
     * @return json
     */
    public function disconnectUser(User $user)
    {
        if (!access()->allow('disconnect-user')) {
            abort(403);
        }
        try {
            $user->devices()->delete();
            return [
                'data'   => "User has been disconnected successfully!",
                'status' => 1,
            ];
        } catch (\Exception $exception) {
            report($exception);
            return [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
        }
    }

    /**
     * @param  Challenge $challenge
     *
     * @return View
     */

    public function getDetails(User $user)
    {
        if (!access()->allow('manage-user')) {
            abort(403);
        }

        try {
            $loggedUser    = auth()->user();
            $role          = getUserRole($loggedUser);
            $loggedCompany = $loggedUser->company()->first();
            $company       = $user->company->first();

            if ($role->group != 'zevo') {
                if ($role->group == 'company') {
                    if ($loggedCompany->id != $company->id) {
                        return view('errors.401');
                    }
                } elseif ($role->group == 'reseller') {
                    if ($loggedCompany->is_reseller) {
                        $allcompanies = Company::where('parent_id', $loggedCompany->id)->orWhere('id', $loggedCompany->id)->get()->pluck('id')->toArray();
                        if (is_null($company) || (!in_array($company->id, $allcompanies))) {
                            return view('errors.401');
                        }
                    } elseif (!$loggedCompany->is_reseller && $company->id != $loggedCompany->id) {
                        return view('errors.401');
                    }
                }
            }

            $group = $user->roles()->where('slug', '!=', 'user')->first();
            $data  = [
                'pagination'  => config('zevolifesettings.datatable.pagination.short'),
                'userData'    => $user,
                'userCompany' => "",
                'userTeam'    => "",
                'ga_title'    => trans('page_title.users.details'),
            ];

            if (!is_null($company)) {
                $data['userCompany'] = $company;
                $userTeam            = $user
                    ->teams()
                    ->wherePivot("user_id", $user->id)
                    ->wherePivot("company_id", $company->id)
                    ->first();

                if (!empty($userTeam)) {
                    $data['userTeam'] = $userTeam->name;
                    if (empty($group)) {
                        $group = $user->roles()->where('slug', 'user')->first();
                    }
                }
            }
            $data['roleData'] = $group;

            return \view('admin.user.details', $data);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong_try_again'),
                'status' => 0,
            ];
            return \Redirect::route('admin.users.index')->with('message', $messageData);
        }
    }

    public function getUserCourseData(Request $request, User $user)
    {
        if (!access()->allow('manage-user')) {
            abort(403);
        }
        try {
            return $user->getUserCourseData($request->all());
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong_try_again'),
                'status' => 0,
            ];
            return \Redirect::route('admin.users.index')->with('message', $messageData);
        }
    }

    public function getUserChallangeData(Request $request, User $user)
    {
        if (!access()->allow('manage-user')) {
            abort(403);
        }
        try {
            return $user->getUserChallangeData($request->all());
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong_try_again'),
                'status' => 0,
            ];
            return \Redirect::route('admin.users.index')->with('message', $messageData);
        }
    }

    public function getRoleWiseCompanies(Request $request)
    {
        if (!access()->allow('manage-user')) {
            abort(403);
        }
        try {
            return $this->model->getRoleWiseCompanies($request->all());
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong_try_again'),
                'status' => 0,
            ];
            return response($messageData, 500)->header('Content-Type', 'application/json');
        }
    }

    public function resetPassword(Request $request, User $user)
    {
        try {
            $token = $this->model->saveToken($user);

            // fire forgot password event
            event(new UserForgotPasswordEvent($user, $token));

            $messageData = [
                'data'   => "The reset password link has been sent successfully!",
                'status' => 1,
            ];
            $this->auditLogRepository->created("Reset password request sent", $user->toArray());

            return \Redirect::route('admin.users.index')->with('message', $messageData);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong_try_again'),
                'status' => 0,
            ];
            return response($messageData, 500)->header('Content-Type', 'application/json');
        }
    }

    public function trackerhistory(Request $request, User $user)
    {
        if (!access()->allow('view-tracker-history')) {
            abort(403);
        }
        try {
            $loggedUser       = auth()->user();
            $company          = $user->company->first();
            $getFirstSyncDate = UserDeviceHistory::select('log_date')->where('user_id', $user->id)->first();
            $loginemail       = ($loggedUser->email ?? "");
            if (empty($getFirstSyncDate)) {
                return \Redirect::route('admin.users.index');
            }

            $data = [
                'pagination'    => config('zevolifesettings.datatable.pagination.long'),
                'userData'      => $user,
                'company'       => $company,
                'ga_title'      => trans('page_title.users.tracker_history', [
                    'user' => $user->full_name,
                ]),
                'firstSyncDate' => (!empty($getFirstSyncDate)) ? $getFirstSyncDate->log_date : Carbon::now(),
                'loginemail'    => $loginemail,
                'timezone'      => (auth()->user()->timezone ?? config('app.timezone')),
                'date_format'   => config('zevolifesettings.date_format.moment_default_datetime'),

            ];

            return \view('admin.user.trackerhistory', $data);
        } catch (\Exception $exception) {
            report($exception);
            abort(500);
        }
    }

    public function gettrackerhistory(Request $request, User $user)
    {
        if (!access()->allow('view-tracker-history')) {
            abort(403);
        }
        try {
            return $this->model->getTrackerHistoryTableData($request->all(), $user);
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
     * delete custom leaves
     *
     * @param int $id
     * @param HealthCoachAvailability $healthCoachAvailability
     * @return view
     */
    public function deleteCustomLeave($id, HealthCoachAvailability $healthCoachAvailability)
    {
        try {
            return $healthCoachAvailability->deleteRecord($id);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('clientlist.message.something_wrong_try_again'),
                'status' => 0,
            ];
            return response()->json($messageData);
        }
    }

    /**
     * @param ChallengeExportRequest $request
     * @return RedirectResponse
     */
    public function exportTrackerHistoryReport(NpsReportExportRequest $request, User $user)
    {
        if (!access()->allow('view-tracker-history')) {
            abort(403);
        }

        try {
            \DB::beginTransaction();
            $data = $this->model->exportTrackerDataEntity($request->all(), $user);
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
            return \Redirect::route('admin.users.tracker-history', $user)->with('message', $messageData);
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

    /**
     * Get All services records
     *
     * @return array
     **/
    public function getAllServicesGroupType()
    {
        $services          = Service::select('id', 'name')->get();
        $servicesGroupType = [];
        foreach ($services as $service) {
            $subServiceGroupType = [];
            $subServices         = ServiceSubCategory::select('id', 'name')->where('service_id', $service->id)->get()->toArray();

            foreach ($subServices as $subservice) {
                $subServiceGroupType[] = [
                    'id'   => $subservice['id'],
                    'name' => $subservice['name'],
                ];
            }
            $servicesGroupType[] = [
                'serviceId'   => $service->id,
                'serviceName' => $service->name,
                'subService'  => $subServiceGroupType,
            ];
        }
        return $servicesGroupType;
    }

    /**
     * @param  User $user
     *
     * @return array
     */
    public function archive(User $user)
    {
        if (!access()->allow('delete-user')) {
            abort(403);
        }
        try {
            \DB::beginTransaction();
            $role = $user->roles()->first();

            if ($role->slug == 'wellbeing_specialist') {
                User::where('id', $user->id)
                    ->update([
                        'deleted_at' => Carbon::now()->todatetimeString(),
                    ]);

                DigitalTherapyService::where('ws_id', $user->id)->delete();
                DigitalTherapySpecific::where('ws_id', $user->id)->delete();

                DB::select(DB::raw("UPDATE digital_therapy_slots SET ws_id = TRIM(BOTH ',' FROM REPLACE( REPLACE(CONCAT(',',REPLACE(ws_id, ',', ',,'), ','),',{$user->id},', ''), ',,', ',') ) WHERE FIND_IN_SET({$user->id}, ws_id)")->getValue(DB::getQueryGrammar()));
                \DB::commit();
                return array('deleted' => 'true');
            } else {
                \DB::rollback();
                return array('deleted' => 'false');
            }
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
     * @param  User $user
     *
     * @return array
     */
    public function findSession(User $user)
    {
        if (!access()->allow('delete-user')) {
            abort(403);
        }
        try {
            $nowInUTC        = now(config('app.timezone'))->todatetimeString();
            $cronofySchedule = CronofySchedule::where('end_time', '>=', $nowInUTC)
                ->whereNotIn('status', ['canceled', 'open', 'completed', 'rescheduled'])
                ->where('ws_id', $user->id)
                ->select('ws_id')
                ->distinct()
                ->count();

            if ($cronofySchedule > 0) {
                return [
                    'status' => true,
                    'name'   => $user->first_name . ' ' . $user->last_name,
                ];
            } else {
                return [
                    'status' => false,
                ];
            }
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
