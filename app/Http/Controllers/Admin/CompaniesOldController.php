<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CreateCompanyModeratorRequest;
use App\Http\Requests\Admin\CreateCompanyRequest;
use App\Http\Requests\Admin\EditCompanyRequest;
use App\Http\Requests\Admin\EditLimitRequest;
use App\Http\Requests\Admin\ExportSurveyReportRequest;
use App\Http\Requests\Admin\StoreFooterDetailsRequest;
use App\Http\Requests\Admin\UpdateSurveyUsersRequest;
use App\Models\AppSetting;
use App\Models\AppTheme;
use App\Models\Calendly;
use App\Models\ChallengeTarget;
use App\Models\Company;
use App\Models\CompanyWiseAppSetting;
use App\Models\Country;
use App\Models\Course;
use App\Models\CourseSurveyQuestionAnswers;
use App\Models\CpPlan;
use App\Models\Feed;
use App\Models\Industry;
use App\Models\MeditationTrack;
use App\Models\Recipe;
use App\Models\Role;
use App\Models\SubCategory;
use App\Models\Webinar;
use App\Models\ZcSurvey;
use App\Models\ZcSurveyReportExportLogs;
use App\Models\ZcSurveyResponse;
use Breadcrumbs;
use Carbon\Carbon;
use DB;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Class CompaniesController
 *
 * @package App\Http\Controllers\Admin
 */
class CompaniesOldController extends Controller
{
    /**
     * Company model object
     *
     * @var model
     */
    protected $model;

    /**
     * CompanyWiseAppSetting model object
     *
     * @var companyWiseAppSetting
     **/
    protected $companyWiseAppSetting;

    /**
     * ZcSurveyReportExportLogs model object
     *
     * @var zcSurveyReportExportLogs
     **/
    protected $zcSurveyReportExportLogs;

    /**
     * ZcSurveyResponse model object
     *
     * @var zcSurveyResponse
     **/
    protected $zcSurveyResponse;

    /**
     * CourseSurveyQuestionAnswers model object
     *
     * @var courseSurveyQuestionAnswers
     **/
    protected $courseSurveyQuestionAnswers;

    /**
     * contructor to initialize model object
     *
     * @param Company $model
     * @param CompanyWiseAppSetting $companyWiseAppSetting
     * @param ZcSurveyReportExportLogs $zcSurveyReportExportLogs
     * @param ZcSurveyResponse $zcSurveyResponse
     * @param CourseSurveyQuestionAnswers $courseSurveyQuestionAnswers
     */
    public function __construct(Company $model, CompanyWiseAppSetting $companyWiseAppSetting, ZcSurveyReportExportLogs $zcSurveyReportExportLogs, ZcSurveyResponse $zcSurveyResponse, CourseSurveyQuestionAnswers $courseSurveyQuestionAnswers)
    {
        $this->model                       = $model;
        $this->companyWiseAppSetting       = $companyWiseAppSetting;
        $this->zcSurveyReportExportLogs    = $zcSurveyReportExportLogs;
        $this->zcSurveyResponse            = $zcSurveyResponse;
        $this->courseSurveyQuestionAnswers = $courseSurveyQuestionAnswers;
        $this->bindBreadcrumbs();
    }

    /**
     * bind breadcrumbs of company modules
     */
    private function bindBreadcrumbs()
    {
        // companies crud
        Breadcrumbs::for('companiesold.index', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push('Companies');
        });
        // list moderator
        Breadcrumbs::for('companiesold.moderator.index', function ($trail, $companyType) {
            $trail->push('Home', route('dashboard'));
            $trail->push('companiesold', route('admin.companiesold.index', $companyType));
            $trail->push("Moderators");
        });
        // create moderator
        Breadcrumbs::for('companiesold.moderator.create', function ($trail, $companyType, $companyId) {
            $trail->push('Home', route('dashboard'));
            $trail->push('companiesold', route('admin.companiesold.index', $companyType));
            $trail->push('Moderators', route('admin.companiesold.moderators', [$companyType, $companyId]));
            $trail->push('Create Moderator');
        });
        // view/set limits
        Breadcrumbs::for('companies.limits.index', function ($trail, $companyType) {
            $trail->push('Home', route('dashboard'));
            $trail->push('Companies', route('admin.companiesold.index', $companyType));
            $trail->push("Limits");
        });
        // Breadcrumbs::for('companies.limits.edit', function ($trail, $companyType, $companyId) {
        //     $trail->push('Home', route('dashboard'));
        //     $trail->push('Companies', route('admin.companiesold.index', $companyType));
        //     $trail->push('Limits', route('admin.companiesold.getLimits', [$companyType, $companyId]));
        //     $trail->push('Edit Limits');
        // });
        // survey-configuration
        Breadcrumbs::for('companies.survey-configuration', function ($trail, $type) {
            $trail->push('Home', route('dashboard'));
            $trail->push('Companies', route('admin.companiesold.index', $type));
            $trail->push('Survey Configuration');
        });
        // portal footer
        Breadcrumbs::for('companies.portalFooter', function ($trail, $companyType, $companyId) {
            $trail->push('Home', route('dashboard'));
            $trail->push('Companies', route('admin.companiesold.index', $companyType));
            $trail->push('Portal Footer');
        });
    }

    /**
     * @return View
     */
    public function index(Request $request, $companyType)
    {
        try {
            $user    = auth()->user();
            $role    = getUserRole($user);
            $company = $user->company->first();

            if (!access()->allow('manage-company') || ($role->group == 'reseller' && $company->parent_id != null)) {
                return \view('errors.401');
            }

            $data = [
                'companySize'  => config('zevolifesettings.company_size'),
                'pagination'   => config('zevolifesettings.datatable.pagination.short'),
                'role'         => $role,
                'company'      => $company,
                'ga_title'     => trans('page_title.companies.companies_list'),
                'companyplans' => CpPlan::where('status', 1)->pluck('name', 'id')->toArray(),
                'companyType'  => $companyType,
            ];
            return \view('admin.companies_old.index', $data);
        } catch (\Exception $exception) {
            report($exception);
            abort(500);
        }
    }

    /**
     * @return View
     */
    public function create(Request $request, $companyType)
    {
        Breadcrumbs::for('companies_old.create', function ($trail) use ($companyType) {
            $trail->push('Home', route('dashboard'));
            $trail->push('Companies', route('admin.companiesold.index', $companyType));
            $trail->push('Add Company');
        });
        $user      = auth()->user();
        $user_role = getUserRole($user);
        $company   = $user->company->first();
        if (!access()->allow('create-company') || ($user_role->group == 'reseller' && $company->parent_id != null)) {
            abort(403);
        }

        try {
            $parent_comapnies  = $roles  = $parentCompanyMedia  = $parentCompanyEmailHeader  = [];
            $brandingCo        = new Company();
            $timezone          = (!empty($user->timezone) ? $user->timezone : config('app.timezone'));
            $maxEndDate        = now()->addYears(100)->toDateString();
            $has_branding      = $enable_survey      = $disable_survey      = $disable_branding      = $branding      = $disable_sso      = $isZendesk      = $disableCompanyLogo      = $disableEmailHeader      = false;
            $mobile_app_value  = true;
            $disable_portal    = true;
            $isShowContentType = false;
            if ($user_role->group == 'zevo') {
                $parent_comapnies = Company::where(['is_reseller' => true])->get()->pluck('name', 'id')->toArray();
                if ($companyType == 'zevo') {
                    $parent_comapnies = array_replace(['zevo' => 'Zevo'], $parent_comapnies);
                }
                $roles             = Role::where(['group' => 'company', 'default' => 0])->get()->pluck('name', 'id')->toArray();
                $isShowContentType = true;
            } elseif ($user_role->group == 'reseller') {
                $isShowContentType = $company->is_reseller;
                $parent_comapnies  = $company->id;
                $roles             = $company->resellerRoles()->get()->pluck('name', 'id')->toArray();
                $brandingCo        = $company;
                $maxEndDate        = Carbon::parse($company->subscription_end_date)->toDateString();
                $branding          = $company->branding;
                $has_branding      = (bool) $company->is_branding;
                $enable_survey     = (bool) $company->enable_survey;
                $disable_sso       = (bool) $company->disable_sso;
                $mobile_app_value  = false;
                $disable_branding  = $disable_survey  = $disableCompanyLogo  = $disableEmailHeader  = true;
            }
            $parentCompanyEmailHeaderUrl = $parentCompanyEmailHeader = [];
            if ($companyType != 'zevo' && $companyType != 'reseller') {
                //Get parent company data
                $isZendesk                   = (bool) $company->is_intercom;
                $parentCompanyData           = Company::where('id', $company->id)->first();
                $parentCompanyMedia          = $parentCompanyData->getFirstMedia('logo');
                $parentCompanyEmailHeader    = $parentCompanyData->getFirstMedia('email_header');
                $parentCompanyEmailHeaderUrl = $parentCompanyData->getMediaData('email_header', ['w' => 600, 'h' => 157, 'zc' => 1]);
            }

            $data                                = [];
            $data['portalDomain']                = getPortalDomain();
            $data['user_role']                   = $user_role;
            $data['parent_comapnies']            = $parent_comapnies;
            $data['resellerRoles']               = $roles;
            $data['companySize']                 = config('zevolifesettings.company_size');
            $data['groupRestrictionRules']       = config('zevolifesettings.group_restriction_rules');
            $data['industries']                  = Industry::pluck('name', 'id')->toArray();
            $data['countries']                   = Country::pluck('name', 'id')->toArray();
            $data['branding']                    = $branding;
            $data['brandingCo']                  = $brandingCo;
            $data['has_branding']                = $has_branding;
            $data['disable_branding']            = $disable_branding;
            $data['enable_survey']               = $enable_survey;
            $data['disable_sso']                 = $disable_sso;
            $data['disable_survey']              = $disable_survey;
            $data['disable_mobile_app']          = false;
            $data['disable_portal']              = $disable_portal;
            $data['disable_portal_domain']       = $disable_portal;
            $data['mobile_app_value']            = $mobile_app_value;
            $data['maxEndDate']                  = $maxEndDate;
            $data['surveys']                     = ZcSurvey::where('status', '!=', 'Draft')->where('is_premium', 0)->pluck('title', 'id')->toArray();
            $data['survey_frequency']            = config('zevolifesettings.survey_frequency');
            $data['survey_days']                 = config('zevolifesettings.survey_days');
            $data['ga_title']                    = trans('page_title.companies.create');
            $data['masterContentType']           = $this->getAllMasterContent($company);
            $data['isShowContentType']           = $isShowContentType;
            $data['registration_restriction']    = config('zevolifesettings.domain_verification_types');
            $data['disableEvent']                = false;
            $data['totalSessions']               = 0;
            $data['companyplans']                = CpPlan::where('status', 1)->pluck('name', 'id')->toArray();
            $data['selectedPlan']                = CpPlan::where('slug', 'challenge')->orderBy('id', 'ASC')->pluck('id')->first();
            $data['isShowPlan']                  = true;
            $data['companyType']                 = $companyType;
            $data['isZendesk']                   = $isZendesk;
            $data['companyData']                 = $company;
            $data['disableCompanyLogo']          = $disableCompanyLogo;
            $data['disableEmailHeader']          = $disableEmailHeader;
            $data['parentCompanyMedia']          = $parentCompanyMedia;
            $data['parentCompanyEmailHeader']    = $parentCompanyEmailHeader;
            $data['parentCompanyEmailHeaderUrl'] = $parentCompanyEmailHeaderUrl;

            $data['portalTheme'] = [
                'blue'     => 'Blue',
                'darkblue' => 'Dark Blue',
                'darkgrey' => 'Dark Grey',
                'green'    => 'Green',
                'pink'     => 'Pink',
                'purple'   => 'Purple',
                'yellow'   => 'Yellow',
            ];

            return \view('admin.companies_old.create', $data);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('admin.companiesold.index', $companyType)->with('message', $messageData);
        }
    }

    /**
     * @param CreateCompanyRequest $request
     *
     * @return RedirectResponse
     */
    public function store(CreateCompanyRequest $request, $companyType)
    {
        $user      = auth()->user();
        $user_role = getUserRole($user);
        $company   = $user->company->first();
        if (!access()->allow('create-company') || ($user_role->group == 'reseller' && $company->parent_id != null)) {
            abort(403);
        }
        try {
            \DB::beginTransaction();
            $data = $this->model->storeEntity($request->all());
            if ($data) {
                \DB::commit();
                $messageData = [
                    'data'   => "Company has been added successfully!",
                    'status' => 1,
                ];
                return \Redirect::route('admin.companiesold.index', $companyType)->with('message', $messageData);
            } else {
                \DB::rollback();
                $messageData = [
                    'data'   => "Something went wrong please try again.",
                    'status' => 0,
                ];
                return redirect()->back()->withInput()->with('message', $messageData);
            }
        } catch (\Exception $exception) {
            \DB::rollback();
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('admin.companiesold.index', $companyType)->with('message', $messageData);
        }
    }

    /**
     * @return View
     */
    public function edit($companyType, Company $company, Request $request)
    {
        $user         = auth()->user();
        $user_role    = getUserRole($user);
        $user_company = $user->company->first();
        if (!access()->allow('update-company') || ($user_role->group == 'reseller' && $user_company->parent_id != null)) {
            abort(403);
        }

        try {
            $timezone    = (!empty($user->timezone) ? $user->timezone : config('app.timezone'));
            $nowInUTC    = now(config('app.timezone'))->toDateTimeString();
            $surveys     = ZcSurvey::where('status', '!=', 'Draft');
            $appTimezone = config('app.timezone');
            $survey      = $company->survey;
            if (!is_null($survey) && !$survey->is_premium) {
                $surveys->where('is_premium', false);
            } elseif (is_null($survey)) {
                $surveys->where('is_premium', false);
            }
            $surveys     = $surveys->get();
            $surveysList = [];
            $surveys->each(function ($survey, $key) use (&$surveysList) {
                $surveysList[$survey->id] = (($survey->is_premium) ? "⭐" : "") . " " . $survey->title;
            });

            $parent_comapnies        = "";
            $roles                   = [];
            $selectedRoles           = $company->resellerRoles()->get()->pluck('id')->toArray();
            $branding                = null;
            $disable_branding        = $disable_survey        = $disable_mobile_app        = false;
            $mobile_app_value        = $company->allow_app;
            $has_branding            = (bool) $company->is_branding;
            $brandingCo              = $company;
            $current_date            = now()->setTime(0, 0, 0, 0)->toDateString();
            $max_end_date            = now()->addYears(100)->toDateString();
            $subscription_start_date = Carbon::parse($company->subscription_start_date);
            $subscription_end_date   = Carbon::parse($company->subscription_end_date);
            $start_start_date        = $current_date;
            $end_start_date          = $current_date;
            $disable_portal          = ($company->is_reseller) ? false : true;
            $disable_portal_domain   = true;
            $usersAttachedRole       = [];
            $isShowContentType       = false;
            $isZendesk               = (bool) $company->is_intercom;
            if (!$company->is_reseller) {
                if (!is_null($company->parent_id)) {
                    $parentComapny             = Company::find($company->parent_id);
                    $parent_comapnies          = (!empty($parentComapny) ? $parentComapny->name : "");
                    $roles                     = $parentComapny->resellerRoles();
                    $brandingCo                = $parentComapny;
                    $branding                  = $parentComapny->branding;
                    $has_branding              = (bool) $parentComapny->is_branding;
                    $disable_branding          = true;
                    $disable_survey            = true;
                    $roleIds                   = $roles->get()->implode('id', ',');
                    $usersAttachedRole         = DB::select("select `id`, (select count(users.id) from `users` inner join `role_user` on `users`.`id` = `role_user`.`user_id` inner join `user_team` on `user_team`.`user_id` = `users`.`id` where `roles`.`id` = `role_user`.`role_id` AND `user_team`.`company_id` = ?) as `users_count` from `roles` where (`group` = ? and `default` = 0 and id in (?)) group by `roles`.`id` having `users_count` > 0", [$company->id, 'reseller',$roleIds]);
                    $parent_company_start_date = Carbon::parse($parentComapny->subscription_start_date)->toDateString();
                    $max_end_date              = Carbon::parse($parentComapny->subscription_end_date)->toDateString();
                    $start_start_date          = $parent_company_start_date;
                    $end_start_date            = $max_end_date;
                    if ($current_date > $start_start_date) {
                        $start_start_date = $current_date;
                    }
                } else {
                    $parent_comapnies   = config('zevolifesettings.company_types.zevo');
                    $roles              = Role::select('name', 'id')->where(['group' => 'company', 'default' => 0]);
                    $branding           = $company->branding;
                    $disable_mobile_app = true;

                    $usersAttachedRole = DB::select("select `id`, (select count(users.id) from `users` inner join `role_user` on `users`.`id` = `role_user`.`user_id` inner join `user_team` on `user_team`.`user_id` = `users`.`id` where `roles`.`id` = `role_user`.`role_id` AND `user_team`.`company_id` = ?) as `users_count` from `roles` where (`group` = ? and `default` = 0) group by `roles`.`id` having `users_count` > 0", [$company->id, 'company']);
                }
            } else {
                $roles             = Role::select('name', 'id')->where(['group' => 'reseller', 'default' => 0]);
                $branding          = $company->branding;
                $disable_survey    = true;
                $allcompanies      = Company::where('parent_id', $company->id)->orWhere('id', $company->id)->get()->implode('id', ',');
                $usersAttachedRole = DB::select("select `id`, (select count(users.id) from `users` inner join `role_user` on `users`.`id` = `role_user`.`user_id` inner join `user_team` on `user_team`.`user_id` = `users`.`id` where `roles`.`id` = `role_user`.`role_id` AND `user_team`.`company_id` in (?)) as `users_count` from `roles` where (`group` = ? and `default` = 0) group by `roles`.`id` having `users_count` > 0", [$allcompanies,'reseller']);
            }

            $parentCompanyMedia = $parentCompanyEmailHeader = [];
            if ($companyType != 'zevo' && $companyType != 'reseller') {
                //Get parent company data
                //$isZendesk          = (bool) $company->is_intercom;
                $parentCompanyData        = Company::where('id', $parentComapny->id)->first();
                $parentCompanyMedia       = $parentCompanyData->getFirstMedia('logo');
                $parentCompanyEmailHeader = $parentCompanyData->getFirstMedia('email_header');
            }

            $isShowContentType = true;
            $disableEvent      = false;
            if ($company->enable_event) {
                $upComingEvents = $company->evnetBookings()
                    ->where('event_booking_logs.status', '4')
                    ->whereRaw("TIMESTAMP(CONCAT(event_booking_logs.booking_date, ' ', event_booking_logs.end_time)) > ?",[
                        $nowInUTC
                    ])
                    ->count('event_booking_logs.id');
                $disableEvent = (!empty($upComingEvents));
            }

            $data                             = [];
            $data['portalDomain']             = getPortalDomain();
            $data['user_role']                = $user_role;
            $data['parent_comapnies']         = $parent_comapnies;
            $data['resellerRoles']            = $roles->get()->pluck('name', 'id')->toArray();
            $data['usersAttachedRole']        = Collect($usersAttachedRole)->pluck('id')->toJson();
            $data['subscription_start_date']  = $subscription_start_date->toDateString();
            $data['subscription_end_date']    = $subscription_end_date->toDateString();
            $data['max_end_date']             = $max_end_date;
            $data['start_start_date']         = $start_start_date;
            $data['end_start_date']           = $end_start_date;
            $data['selectedRoles']            = $selectedRoles;
            $data['recordData']               = $company;
            $data['companySize']              = config('zevolifesettings.company_size');
            $data['groupRestrictionRules']    = config('zevolifesettings.group_restriction_rules');
            $data['industries']               = Industry::pluck('name', 'id')->toArray();
            $data['countries']                = Country::pluck('name', 'id')->toArray();
            $data['companyLocData']           = $company->getDefaultLocation();
            $data['branding']                 = $branding;
            $data['brandingCo']               = $brandingCo;
            $data['has_branding']             = $has_branding;
            $data['disable_branding']         = $disable_branding;
            $data['survey']                   = $company->survey;
            $data['enable_survey']            = (bool) $company->enable_survey;
            $data['disable_sso']              = (bool) $company->disable_sso;
            $data['disable_survey']           = $disable_survey;
            $data['mobile_app_value']         = $mobile_app_value;
            $data['disable_mobile_app']       = $disable_mobile_app;
            $data['disable_portal']           = $disable_portal;
            $data['disable_portal_domain']    = $disable_portal_domain;
            $data['surveys']                  = $surveysList;
            $data['survey_frequency']         = config('zevolifesettings.survey_frequency');
            $data['survey_days']              = config('zevolifesettings.survey_days');
            $data['ga_title']                 = trans('page_title.companies.edit');
            $data['isShowContentType']        = $isShowContentType;
            $data['masterContentType']        = $this->getAllMasterContent($user_company);
            $data['selectedContent']          = [];//$this->getAllSeletedParentResellerData($company);
            
            $data['registration_restriction'] = config('zevolifesettings.domain_verification_types');
            $data['disableEvent']             = $disableEvent;
            $data['companyplans']             = CpPlan::where('status', 1)->pluck('name', 'id')->toArray();
            $data['selectedPlan']             = $company->companyplan()->pluck('plan_id')->first();
            $data['isShowPlan']               = ($company->parent_id == null && $company->is_reseller == 0);
            $checkAccess                      = getCompanyPlanAccess($user, 'eap');
            $data['companyType']              = $companyType;
            $data['isZendesk']                = $isZendesk;
            $data['parentCompanyMedia']       = $parentCompanyMedia;
            $data['parentCompanyEmailHeader'] = $parentCompanyEmailHeader;

            if ($companyType == 'normal') {
                $data['disableCompanyLogo'] = true;
                $data['disableEmailHeader'] = true;
            } else {
                $data['disableCompanyLogo'] = false;
                $data['disableEmailHeader'] = false;
            }
            $data['portalTheme'] = [
                'blue'     => 'Blue',
                'darkblue' => 'Dark Blue',
                'darkgrey' => 'Dark Grey',
                'green'    => 'Green',
                'pink'     => 'Pink',
                'purple'   => 'Purple',
                'yellow'   => 'Yellow',
            ];
            if ($company->eap_tab || $checkAccess) {
                $data['totalSessions'] = Calendly::Join('user_team', 'user_team.user_id', '=', 'eap_calendly.user_id')
                    ->where('user_team.company_id', $company->id)
                    ->where('eap_calendly.end_time', ">=", $nowInUTC)
                    ->whereNull('cancelled_at')
                    ->count();
            } else {
                $data['totalSessions'] = 0;
            }
            
            return \view('admin.companies_old.edit', $data);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('admin.companiesold.index', $companyType)->with('message', $messageData);
        }
    }

    /**
     * @param EditCompanyRequest $request
     *
     * @return RedirectResponse
     */
    public function update($companyType, Company $company, EditCompanyRequest $request)
    {
        $user         = auth()->user();
        $user_role    = getUserRole($user);
        $user_company = $user->company->first();
        if (!access()->allow('update-company') || ($user_role->group == 'reseller' && $user_company->parent_id != null)) {
            abort(403);
        }
        try {
            \DB::beginTransaction();
            $data = $company->updateEntity($request->all());
            if ($data) {
                \DB::commit();
                $messageData = [
                    'data'   => "Company has been updated successfully!",
                    'status' => 1,
                ];
                return \Redirect::route('admin.companiesold.index', $companyType)->with('message', $messageData);
            } else {
                \DB::rollback();
                $messageData = [
                    'data'   => "Something went wrong please try again.",
                    'status' => 0,
                ];
                return \Redirect::route('admin.companiesold.edit', [$companyType, $company->id])->with('message', $messageData);
            }
        } catch (\Exception $exception) {
            \DB::rollback();
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('admin.companiesold.index', $companyType)->with('message', $messageData);
        }
    }

    /**
     * @param Request $request
     *
     * @return View
     */

    public function getCompanies(Request $request)
    {
        $user    = auth()->user();
        $role    = getUserRole();
        $company = $user->company->first();

        if (!access()->allow('manage-company') || ($role->group == 'reseller' && $company->parent_id != null)) {
            return response()->json([
                'message' => trans('labels.common_title.unauthorized_access'),
            ], 422);
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
     * @param  Company $company
     *
     * @return View
     */

    public function delete(Company $company)
    {
        $user         = auth()->user();
        $user_role    = getUserRole($user);
        $user_company = $user->company->first();

        if (!access()->allow('delete-company') || ($user_role->group == 'reseller' && $user_company->parent_id != null)) {
            abort(403);
        }
        try {
            return $company->deleteRecord();
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('admin.companiesold.index')->with('message', $messageData);
        }
    }

    /**
     * @return View
     */
    public function teams($companyType, Company $company, Request $request)
    {
        // get teams
        Breadcrumbs::for('companiesold.teams', function ($trail) use ($companyType) {
            $trail->push('Home', route('dashboard'));
            $trail->push('Companies', route('admin.companiesold.index', $companyType));
            $trail->push('Teams');
        });
        $user         = auth()->user();
        $user_role    = getUserRole($user);
        $user_company = $user->company->first();
        if (!access()->allow('get-teams') || ($user_role->group == 'reseller' && $user_company->parent_id != null)) {
            abort(403);
        }
        try {
            $data                = array();
            $data['pagination']  = config('zevolifesettings.datatable.pagination.short');
            $data['company']     = $company;
            $data['ga_title']    = trans('page_title.companies.teams') . $company->name;
            $data['companyType'] = $companyType;
            return \view('admin.companies_old.teams', $data);
        } catch (\Exception $exception) {
            report($exception);
            return response('Something wrong', 400)->header('Content-Type', 'text/plain');
        }
    }

    /**
     * @param Request $request
     *
     * @return View
     */

    public function getCompanyTeams(Company $company, Request $request)
    {
        $user         = auth()->user();
        $user_role    = getUserRole($user);
        $user_company = $user->company->first();
        if (!access()->allow('get-teams') || ($user_role->group == 'reseller' && $user_company->parent_id != null)) {
            return response()->json([
                'message' => trans('labels.common_title.unauthorized_access'),
            ], 422);
        }
        try {
            return $company->getTeamsTableData($request->all());
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('admin.companiesold.index')->with('message', $messageData);
        }
    }

    /**
     * @return View
     */
    public function createModerator($companyType, Company $company, Request $request)
    {

        $user         = auth()->user();
        $user_role    = getUserRole($user);
        $user_company = $user->company->first();
        if (!access()->allow('add-moderator') || ($user_role->group == 'reseller' && $user_company->parent_id != null)) {
            abort(403);
        }
        try {
            $referrer = (!empty($request->referrer) ? $request->referrer : 'listing');
            $data     = [
                'company'     => $company,
                'ga_title'    => trans('page_title.companies.createModerator'),
                'referrer'    => $referrer,
                'companyType' => $companyType,
                'cancel_url'  => (($referrer == 'index') ? route('admin.companiesold.index', $companyType) : route('admin.companiesold.moderators', [$companyType, $company->id])),
            ];

            return \view('admin.companies_old.createmoderator', $data);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('admin.companiesold.index', $companyType)->with('message', $messageData);
        }
    }

    /**
     * @param CreateCompanyModeratorRequest $request
     *
     * @return RedirectResponse
     */
    public function storeModerator($companyType, Company $company, CreateCompanyModeratorRequest $request)
    {
        $user         = auth()->user();
        $user_role    = getUserRole($user);
        $user_company = $user->company->first();
        if (!access()->allow('add-moderator') || ($user_role->group == 'reseller' && $user_company->parent_id != null)) {
            abort(403);
        }
        try {
            \DB::beginTransaction();
            $referrer = (!empty($request->referrer) ? $request->referrer : 'listing');
            $data     = $company->createModerator($request->all());
            if ($data) {
                \DB::commit();
                $messageData = [
                    'data'   => "Moderator has been created successfully for the requested company!",
                    'status' => 1,
                ];
                $url = (($referrer == 'index') ? route('admin.companiesold.index', $companyType) : route('admin.companiesold.moderators', [$companyType, $company->id]));
                return redirect($url)->with('message', $messageData);
            } else {
                \DB::rollback();
                $messageData = [
                    'data'   => "Something went wrong please try again.",
                    'status' => 0,
                ];
                return \Redirect::route('admin.companiesold.createmoderator', $companyType)->with('message', $messageData);
            }
        } catch (\Exception $exception) {
            \DB::rollback();
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            if ($request->session()->has('createModeratorRedirect')) {
                $url = $request->session()->get('createModeratorRedirect')[0];
                return redirect($url)->with('message', $messageData);
            } else {
                return \Redirect::route('admin.companiesold.index', $companyType)->with('message', $messageData);
            }
        }
    }

    /**
     * @return View
     */
    public function moderators($companyType, Company $company, Request $request)
    {
        $user         = auth()->user();
        $user_role    = getUserRole($user);
        $user_company = $user->company->first();
        if (!access()->allow('view-moderator') || ($user_role->group == 'reseller' && $user_company->parent_id != null)) {
            abort(403);
        }
        try {
            $data                = array();
            $data['pagination']  = config('zevolifesettings.datatable.pagination.short');
            $data['company']     = $company;
            $data['companyType'] = $companyType;
            $data['ga_title']    = trans('page_title.companies.moderators') . $company->name;
            return \view('admin.companies_old.moderators', $data);
        } catch (\Exception $exception) {
            report($exception);
            return response('Something wrong', 400)->header('Content-Type', 'text/plain');
        }
    }

    /**
     * @param Request $request
     *
     * @return View
     */

    public function getCompanyModerators(Company $company, Request $request)
    {
        $user         = auth()->user();
        $user_role    = getUserRole($user);
        $user_company = $user->company->first();
        if (!access()->allow('view-moderator') || ($user_role->group == 'reseller' && $user_company->parent_id != null)) {
            return response()->json([
                'message' => trans('labels.common_title.unauthorized_access'),
            ], 422);
        }
        try {
            return $company->getModeratorsTableData($request->all());
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('admin.companiesold.index')->with('message', $messageData);
        }
    }

    /**
     * @return View
     */
    public function getLimits($companyType, Company $company, Request $request)
    {
        $role = getUserRole();
        if (!access()->allow('view-limits') || $role->group != 'zevo') {
            return response()->json([
                'message' => trans('labels.common_title.unauthorized_access'),
            ], 422);
        }
        try {
            $data = [
                'company'     => $company,
                'pagination'  => config('zevolifesettings.datatable.pagination.short'),
                'ga_title'    => trans('page_title.companies.limits') . $company->name,
                'companyType' => $companyType,
            ];

            return \view('admin.companies_old.limits', $data);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('admin.companiesold.index', $companyType)->with('message', $messageData);
        }
    }

    /**
     * To get company wise limit
     *
     * @param Company $company
     * @param Request $request
     * @return JSON
     */
    public function getLimitsList(Company $company, Request $request)
    {
        $role = getUserRole();
        if (!access()->allow('view-limits') || $role->group != 'zevo') {
            return response()->json([
                'message' => trans('labels.common_title.unauthorized_access'),
            ], 422);
        }

        try {
            return $company->getLimitsTableData($request->all());
        } catch (\Exception $exception) {
            report($exception);
            return response()->json([
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ], 422);
        }
    }

    /**
     * Edit company wise limit
     *
     * @param Company $company
     * @param Request $request
     * @return View
     */

    public function editLimits($companyType, Company $company, Request $request)
    {
        $role = getUserRole();
        if (!access()->allow('view-limits') || $role->group != 'zevo') {
            abort(401);
        }

        try {
            $type      = ($request->type ?? null);
            $hrefArray = [
                'challenge'          => '#challengepoints',
                'reward'             => '#rewardspoints',
                'reward-daily-limit' => '#rewardspointslimit',
            ];
            $data = [
                'company'     => $company,
                'type'        => $type,
                'companyType' => $companyType,
                'ga_title'    => trans('page_title.companies.editLimits'),
                'cancel_url'  => route('admin.companiesold.getLimits', [$companyType, $company->id, $hrefArray[$type]]),
            ];

            if ($type == "challenge") {
                if (!$company->allow_app) {
                    return view('errors.401');
                }

                $data['limitsData']        = $company->limits()->pluck('value', 'type')->toArray();
                $data['challenge_targets'] = ChallengeTarget::where("is_excluded", 0)->pluck('name', 'short_name')->toArray();
                $data['uom']               = config('zevolifesettings.target_uom');
                $data['default_limits']    = config('zevolifesettings.default_limits');
            } elseif ($type == "reward" || $type == "reward-daily-limit") {
                if ($company->is_reseller  || !is_null($company->parent_id)) {
                    $data['company_portal_limits']         = $company->companyWisePointsSetting()->pluck('value', 'type')->toArray();
                    $data['portal_limits']                 = config('zevolifesettings.portal_limits');
                    $data['default_portal_limits']         = config('zevolifesettings.default_portal_limits');
                    $data['default_portal_limits_message'] = config('zevolifesettings.default_portal_limits_message');

                    $data['company_reward_point_limits']      = $company->companyWisePointsDailyLimit()->pluck('value', 'type')->toArray();
                    $data['reward_point_labels']              = config('zevolifesettings.reward_point_labels');
                    $data['reward_point_daily_limit']         = config('zevolifesettings.reward_point_daily_limit');
                    $data['reward_point_daily_limit_message'] = config('zevolifesettings.reward_point_daily_limit_message');
                } else {
                    return view('errors.401');
                }
            } else {
                return view('errors.401');
            }

            return \view('admin.companies_old.editlimits', $data);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('admin.companiesold.index', $companyType)->with('message', $messageData);
        }
    }

    /**
     * Update company wise limit
     *
     * @param Company $company
     * @param Request $request
     * @return Redirect response
     */
    public function updateLimits($companyType, Company $company, EditLimitRequest $request)
    {
        $role = getUserRole();
        if (!access()->allow('view-limits') || $role->group != 'zevo') {
            abort(401);
        }

        try {
            \DB::beginTransaction();
            $hrefArray = [
                'challenge'          => '#challengepoints',
                'reward'             => '#rewardspoints',
                'reward-daily-limit' => '#rewardspointslimit',
            ];
            $data = $company->updateLimits($request->all());
            if ($data) {
                \DB::commit();
                $messageData = [
                    'data'   => "Limits has been updated successfully for the requested company!",
                    'status' => 1,
                ];
                return \Redirect::route('admin.companiesold.getLimits', [$companyType, $company->id, $hrefArray[$request->type]])->with('message', $messageData);
            } else {
                \DB::rollback();
                $messageData = [
                    'data'   => "Something went wrong please try again.",
                    'status' => 0,
                ];
                return \Redirect::route('admin.companiesold.editLimits', [$companyType, $company->id, $hrefArray[$request->type]])->with('message', $messageData);
            }
        } catch (\Exception $exception) {
            \DB::rollback();
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('admin.companiesold.index', $companyType)->with('message', $messageData);
        }
    }

    /**
     * Set companies limit to default
     *
     * @param Company $company
     * @param Request $request
     * @return JSON response
     */
    public function setDefaultLimits(Company $company, Request $request)
    {
        $role = getUserRole();
        if (!access()->allow('manage-company-app-settings') || $role->group != 'zevo') {
            return response()->json([
                'data'   => trans('labels.common_title.unauthorized_access'),
                'status' => 0,
            ], 422);
        }

        try {
            return $company->setDefaultLimits($request->all());
        } catch (\Exception $exception) {
            report($exception);
            return response()->json([
                'data'   => trans('labels.common_title.something_wrong_try_again'),
                'status' => 0,
            ], 500);
        }
    }

    /**
     * @param Company $company
     * @param Request $request
     * @return View
     */
    public function changeAppSettingIndex($companyType, Company $company, Request $request)
    {
        // app settings
        Breadcrumbs::for('companies.app-settings.index', function ($trail) use ($companyType) {
            $trail->push('Home', route('dashboard'));
            $trail->push('Companies', route('admin.companiesold.index', $companyType));
            $trail->push('App Settings');
        });
        $role = getUserRole();
        if (!access()->allow('manage-company-app-settings') || $role->group != 'zevo') {
            abort(403);
        }
        try {
            if (!$company->allow_app) {
                return view('errors.401');
            }

            $data               = array();
            $data['pagination'] = config('zevolifesettings.datatable.pagination.short');
            $data['company']    = $company;
            if (isset($company)) {
                $companyWiseAppSetting = $company->companyWiseAppSetting()->get();
            }

            $data['dsiplayDefaultSettings'] = false;
            if ($companyWiseAppSetting->count() > 0) {
                $data['dsiplayDefaultSettings'] = true;
            }

            $data['ga_title']    = trans('page_title.companies.changeAppSettingIndex') . ' ' . $company->name;
            $data['companyType'] = $companyType;
            return \view('admin.companies_old.changeAppSettingIndex', $data);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('admin.companiesold.index', $companyType)->with('message', $messageData);
        }
    }

    /**
     * @param Request $request
     * @return Datatable
     */
    public function getCompanyAppSettings($companyType, Request $request)
    {
        $role = getUserRole();
        if (!access()->allow('manage-company-app-settings') || $role->group != 'zevo') {
            abort(403);
        }
        try {
            return $this->companyWiseAppSetting->getTableData($request->all());
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('admin.appsettings.index', $companyType)->with('message', $messageData);
        }
    }

    /**
     * @param Request $request
     * @return View
     */
    public function changeAppSettingCreateEdit($companyType, Request $request)
    {
        Breadcrumbs::for('companies.app-settings.update', function ($trail, $companyType, $companyId) {
            $trail->push('Home', route('dashboard'));
            $trail->push('Companies', route('admin.companiesold.index', $companyType));
            $trail->push('App Settings', route('admin.companiesold.changeAppSettingIndex', [$companyType, $companyId]));
            $trail->push('Change App Settings');
        });
        $role = getUserRole();
        if (!access()->allow('change-company-app-settings') || $role->group != 'zevo') {
            abort(403);
        }
        try {
            $data = [];
            if (isset($request->company)) {
                $company               = $this->model->find($request->company);
                $companyWiseAppSetting = $company->companyWiseAppSetting()->get();
                if ($companyWiseAppSetting->count() > 0) {
                    foreach ($companyWiseAppSetting as $key => $value) {
                        $data[$value->key] = [
                            'value'     => $value->value,
                            'type'      => $value->type,
                            'image_url' => $value->image_url,
                        ];
                    }
                }
            }

            $data['app_theme_default'] = AppTheme::all()->pluck('name', 'slug')->toArray();

            $defaultAppSetting                     = AppSetting::get();
            $data['logo_image_url']['placeholder'] = asset('app_assets/zevo_logo_splash_web.png');
            foreach ($defaultAppSetting as $key => $value) {
                if ($value->key == 'splash_message') {
                    $data[$value->key]['placeholder'] = $value->value;
                    if (!array_key_exists('value', $data[$value->key])) {
                        $data[$value->key]['value'] = $value->value;
                    }
                } elseif ($value->key == 'splash_image_url') {
                    $data[$value->key]['placeholder'] = $value->logo;
                } elseif ($value->key == 'app_theme') {
                    $data[$value->key]['placeholder'] = $value->value;
                    if (empty($data[$value->key]['value'])) {
                        $data[$value->key]['value'] = $value->value;
                    }
                }
            }

            $data['ga_title']    = trans('page_title.companies.changeAppSettingCreateEdit');
            $data['companyType'] = $companyType;
            return \view('admin.companies_old.changeAppSettingCreateEdit', $data);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('admin.companiesold.changeAppSettingIndex', $companyType, $request->company)->with('message', $messageData);
        }
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function changeAppSettingStoreUpdate(Request $request)
    {
        $role = getUserRole();
        if (!access()->allow('change-company-app-settings') || $role->group != 'zevo') {
            abort(403);
        }
        try {
            $payload     = $request->all();
            $company     = $payload['company_id'];
            $companyType = $payload['companyType'];
            if (isset($company)) {
                $companyWiseAppSetting = Company::find($company)->companyWiseAppSetting()->get();
            }

            if (!isset($payload['splash_message']) && !isset($payload['splash_image_url']) && !isset($payload['logo_image_url']) && !isset($payload['app_theme']) && $companyWiseAppSetting->count() == 0) {
                $messageData = [
                    'data'   => 'Please enter atleast one field to set custom app settings.',
                    'status' => 0,
                ];
                return \Redirect::route('admin.companiesold.changeAppSettingCreateEdit', [$companyType, $company])->with('message', $messageData);
            }
            DB::beginTransaction();
            unset($payload['_token']);
            unset($payload['company_id']);
            unset($payload['companyType']);

            $data = $this->companyWiseAppSetting->storeUpdateEntity($payload, $company);

            if ($data) {
                DB::commit();
                $messageData = [
                    'data'   => trans('labels.app_settings.data_store_success'),
                    'status' => 1,
                ];
                return \Redirect::route('admin.companiesold.changeAppSettingIndex', [$companyType, $company])->with('message', $messageData);
            } else {
                DB::rollBack();
                $messageData = [
                    'data'   => trans('labels.common_title.something_wrong_try_again'),
                    'status' => 0,
                ];
                return \Redirect::route('admin.companiesold.changeAppSettingCreateEdit', [$companyType, $company])->with('message', $messageData);
            }
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('admin.companiesold.changeAppSettingIndex', [$companyType, $request->company_id])->with('message', $messageData);
        }
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function changeToDefaultSettings(Request $request)
    {
        $role = getUserRole();
        if (!access()->allow('change-company-app-settings') || $role->group != 'zevo') {
            abort(403);
        }
        try {
            if (isset($request->company)) {
                $company = $this->model->find($request->company);
            }

            if (isset($company)) {
                $companyWiseAppSetting = $company->companyWiseAppSetting()->delete();
            }

            $messageData = [
                'data'   => 'Default app settings will be used.',
                'status' => 1,
            ];
            return \Redirect::route('admin.companiesold.changeAppSettingIndex', $request->company)->with('message', $messageData);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('admin.companiesold.changeAppSettingIndex', $request->company)->with('message', $messageData);
        }
    }

    public function resellerDetails(Request $request)
    {
        try {
            return $this->model->resellerDetails($request->all());
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
     * To get survey details of company
     *
     * @param Company $company
     * @param String $type
     * @return JSON
     **/
    public function getSurveyDetails(Company $company, String $type)
    {
        if ($type == "zcsurvey" && !access()->allow('export-survey-report')) {
            return response()->json([
                'status'  => false,
                'message' => trans('labels.common_title.unauthorized_access'),
            ], 401);
        }

        if ($type == "masterclass" && !access()->allow('masterclass-survey-report')) {
            return response()->json([
                'status'  => false,
                'message' => trans('labels.common_title.unauthorized_access'),
            ], 401);
        }

        try {
            $lastLog = null;
            if ($type == "zcsurvey") {
                $lastLog = $company->surveyExportLogs()->select('id', 'process_completed_at')->orderbyDesc('id')->first();
            } elseif ($type == "masterclass") {
                $lastLog = $company->mcSurveyReportExportLogs()->select('id', 'process_completed_at')->orderbyDesc('id')->first();
            }

            if (!is_null($lastLog) && is_null($lastLog->process_completed_at)) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Report generation is running in the background, Please try again after some time.',
                ], 422);
            }

            return $company->getSurveyDetails($type);
        } catch (\Exception $exception) {
            report($exception);
            return response()->json([
                'status'  => false,
                'message' => trans('labels.common_title.something_wrong_try_again'),
            ], 500);
        }
    }

    /**
     * Export survey report as per selected dates and sent to entered email
     *
     * @param Company $company
     * @param String $type
     * @param Request $request
     * @return void
     **/
    public function exportSurveyReport(Company $company, String $type, ExportSurveyReportRequest $request)
    {
        if ($type == "zcsurvey" && !access()->allow('export-survey-report')) {
            return response()->json([
                'status'  => false,
                'message' => trans('labels.common_title.unauthorized_access'),
            ], 401);
        }

        if ($type == "masterclass" && !access()->allow('masterclass-survey-report')) {
            return response()->json([
                'status'  => false,
                'message' => trans('labels.common_title.unauthorized_access'),
            ], 401);
        }

        try {
            \DB::beginTransaction();

            $lastLog     = null;
            $user        = auth()->user();
            $appTimeZone = config('app.timezone');
            $timezone    = (!empty($user->timezone) ? $user->timezone : $appTimeZone);

            // check if any previous process is running or not
            if ($type == "zcsurvey") {
                $lastLog = $company->surveyExportLogs()->select('id', 'process_completed_at')->orderbyDesc('id')->first();
            } elseif ($type == "masterclass") {
                $lastLog = $company->mcSurveyReportExportLogs()->select('id', 'process_completed_at')->orderbyDesc('id')->first();
            }

            if (!is_null($lastLog) && is_null($lastLog->process_completed_at)) {
                return response()->json([
                    'message' => 'Report generation is running in the background, Please try again after some time.',
                    'status'  => 0,
                ], 422);
            }

            // check responses are exist for company and selected dates
            if ($type == "masterclass") {
                $responseCount = $this->courseSurveyQuestionAnswers->select('id')->where('company_id', $company->id)->first();
                if (!is_null($responseCount)) {
                    $start_date = Carbon::parse($request->start_date, $timezone)
                        ->setTime(0, 0, 0)->setTimezone($appTimeZone)->toDateTimeString();
                    $end_date = Carbon::parse($request->end_date, $timezone)
                        ->setTime(23, 59, 59)->setTimezone($appTimeZone)->toDateTimeString();
                    $count = $this->courseSurveyQuestionAnswers
                        ->where('course_survey_question_answers.company_id', $company->id)
                        ->whereBetween('course_survey_question_answers.created_at', [$start_date, $end_date])
                        ->count('id');
                    if ($count == 0) {
                        return response()->json([
                            'message' => 'No responses are exist between selected duration.',
                            'status'  => 0,
                        ], 422);
                    }
                } else {
                    return response()->json([
                        'status'  => false,
                        'message' => 'It seems company don\'t have received any responses yet.',
                    ], 422);
                }
            } elseif ($type == "zcsurvey") {
                $responseCount = $this->zcSurveyResponse->select('id')->where('company_id', $company->id)->first();
                if (!is_null($responseCount)) {
                    $start_date = Carbon::parse($request->start_date, $timezone)
                        ->setTime(0, 0, 0)->setTimezone($appTimeZone)->toDateTimeString();
                    $end_date = Carbon::parse($request->end_date, $timezone)
                        ->setTime(23, 59, 59)->setTimezone($appTimeZone)->toDateTimeString();
                    $count = $this->zcSurveyResponse
                        ->where('zc_survey_responses.company_id', $company->id)
                        ->whereBetween('zc_survey_responses.created_at', [$start_date, $end_date])
                        ->count('id');
                    if ($count == 0) {
                        return response()->json([
                            'message' => 'No responses are exist between selected duration.',
                            'status'  => 0,
                        ], 422);
                    }
                } else {
                    return response()->json([
                        'status'  => false,
                        'message' => 'It seems company don\'t have received any responses yet.',
                    ], 422);
                }
            }

            // run process for export survey
            $data = $company->exportSurveyReport($type, $request->all());
            if ($data) {
                \DB::commit();
                return response()->json([
                    'message' => 'Report generation is running in the background once it will be generated, the report will send to email.',
                    'status'  => 1,
                ], 200);
            } else {
                \DB::rollback();
                return response()->json([
                    'message' => trans('labels.common_title.something_wrong_try_again'),
                    'status'  => 0,
                ], 500);
            }
        } catch (\Exception $exception) {
            \DB::rollback();
            report($exception);
            return response()->json([
                'status'  => false,
                'message' => trans('labels.common_title.something_wrong_try_again'),
            ], 500);
        }
    }

    /**
     * Get All Master Content Type
     * @param $company Company
     * @return array
     **/
    protected function getAllMasterContent($company = null)
    {
        $type = config('zevolifesettings.company_content_master_type');
        foreach ($type as $key => $value) {
            $subcategory = SubCategory::select('id', 'name')
                ->where('status', 1)->where("category_id", $key)
                ->pluck('name', 'id')->toArray();
            $subcategoryArray = [];
            foreach ($subcategory as $subKey => $subValue) {
                $result = null;
                switch ($value) {
                    case 'Masterclass':
                        $result = Course::select('courses.id', 'courses.title', 'category_tags.name as categoryTag')
                            ->where('sub_category_id', $subKey)
                            ->where(function ($query) use ($company) {
                                if (!empty($company)) {
                                    $assignedContent = DB::select("SELECT masterclass_id FROM `masterclass_company` WHERE `company_id` = ?", [$company->id]);
                                    $assignedContent = Collect($assignedContent)->pluck('masterclass_id')->toArray();
                                    $query->whereIn('courses.id', $assignedContent);
                                }
                            })
                            ->leftjoin('category_tags', 'category_tags.id', '=', 'courses.tag_id')
                            ->get()
                            ->toArray();
                        break;
                    case 'Meditation':
                        $result = MeditationTrack::select('meditation_tracks.id', 'meditation_tracks.title', 'category_tags.name as categoryTag')
                            ->where('sub_category_id', $subKey)
                            ->where(function ($query) use ($company) {
                                if (!empty($company)) {
                                    $assignedContent = DB::select("SELECT meditation_track_id FROM `meditation_tracks_company` WHERE `company_id` = ?", [$company->id]);
                                    $assignedContent = Collect($assignedContent)->pluck('meditation_track_id')->toArray();
                                    $query->whereIn('meditation_tracks.id', $assignedContent);
                                }
                            })
                            ->leftjoin('category_tags', 'category_tags.id', '=', 'meditation_tracks.tag_id')
                            ->get()
                            ->toArray();
                        break;
                    case 'Webinar':
                        $result = Webinar::select('webinar.id', 'webinar.title', 'category_tags.name as categoryTag')
                            ->where('sub_category_id', $subKey)
                            ->where(function ($query) use ($company) {
                                if (!empty($company)) {
                                    $assignedContent = DB::select("SELECT webinar_id FROM `webinar_company` WHERE `company_id` = ?", [$company->id]);
                                    $assignedContent = Collect($assignedContent)->pluck('webinar_id')->toArray();
                                    $query->whereIn('webinar.id', $assignedContent);
                                }
                            })
                            ->leftjoin('category_tags', 'category_tags.id', '=', 'webinar.tag_id')
                            ->get()
                            ->toArray();
                        break;
                    case 'Feed':
                        $result = Feed::select('feeds.id', 'feeds.title', 'category_tags.name as categoryTag')
                            ->where('company_id', null)
                            ->where('sub_category_id', $subKey)
                            ->where(function ($query) use ($company) {
                                if (!empty($company)) {
                                    $assignedContent = DB::select("SELECT feed_id FROM `feed_company` WHERE `company_id` = ?", [$company->id]);
                                    $assignedContent = Collect($assignedContent)->pluck('feed_id')->toArray();
                                    $query->whereIn('feeds.id', $assignedContent);
                                }
                            })
                            ->leftjoin('category_tags', 'category_tags.id', '=', 'feeds.tag_id')
                            ->get()
                            ->toArray();
                        break;
                    default:
                        $result = Recipe::select('recipe.id', 'recipe.title', 'category_tags.name as categoryTag')
                            ->where('company_id', null)
                            ->join('recipe_category', 'recipe_category.recipe_id', '=', 'recipe.id')
                            ->where('recipe_category.sub_category_id', $subKey)
                            ->where(function ($query) use ($company) {
                                if (!empty($company)) {
                                    $assignedContent = DB::select("SELECT recipe_id FROM `recipe_company` WHERE `company_id` = ?", [$company->id]);
                                    $assignedContent = Collect($assignedContent)->pluck('recipe_id')->toArray();
                                    $query->whereIn('recipe.id', $assignedContent);
                                }
                            })
                            ->leftjoin('category_tags', 'category_tags.id', '=', 'recipe.tag_id')
                            ->get()
                            ->toArray();
                        break;
                }

                if (!empty($result)) {
                    foreach ($result as $k => $item) {
                        $categoryTag = 'N/A';
                        if (!empty($item['categoryTag']) && $item['categoryTag'] != '') {
                            $categoryTag = $item['categoryTag'];
                        }
                        $plucked[$subValue][$value][$item['id']] = $item['title'] . ' - ' . $categoryTag;
                    }
                    $subcategoryArray[] = [
                        'id'              => $subKey,
                        'subcategoryName' => $subValue,
                        $value            => $plucked[$subValue][$value],
                    ];
                }
            }
            $masterContentType[] = [
                'id'           => $key,
                'categoryName' => $value,
                'subcategory'  => $subcategoryArray,
            ];
        }
        return $masterContentType;
    }

    /**
     * Survey configuration page
     *
     * @param Company $company
     * @param Request $request
     * @return View
     */
    public function surveyConfiguration($companyType, Company $company, Request $request)
    {
        if (!access()->allow('survey-configuration')) {
            abort(401);
        }

        try {
            if (!$company->enable_survey) {
                return view('errors.401');
            }

            // get survey to all falg of company
            $survey = $company->survey()
                ->select('zc_survey_settings.id', 'zc_survey_settings.survey_to_all')
                ->first();

            // get existing users
            $existingUsers = $company->surveyUsers()->select('users.id')->get()->pluck('id')->toArray();

            $users = "";
            $company->locations()->select('company_locations.id', 'company_locations.name')->get()
                ->each(function ($location) use (&$users, $existingUsers) {
                    $location->departments()->select('departments.id', 'departments.name')->get()
                        ->each(function ($department) use (&$users, $location, $existingUsers) {
                            $department->teams()->select('teams.id', 'teams.name')
                                ->whereHas('teamlocation', function ($query) use ($location) {
                                    $query->where('company_locations.id', $location->id);
                                })->get()
                                ->each(function ($team) use (&$users, $location, $department, $existingUsers) {
                                    $team->users()->select('users.id', 'users.first_name', 'users.last_name', 'users.email')->get()
                                        ->each(function ($user) use (&$users, $location, $department, $team, $existingUsers) {
                                            $selected = (in_array($user->id, $existingUsers) ? 'selected="selected"' : '');
                                            $users .= "<option data-section='{$location->name}/{$department->name}/{$team->name}' value='{$user->id}' {$selected}>{$user->full_name}($user->email)</option>";
                                        });
                                });
                        });
                });

            $data = [
                'company'               => $company,
                'survey_to_all'         => $survey->survey_to_all,
                'surveyUsersVisibility' => ($survey->survey_to_all ? 'hide' : 'show'),
                'users'                 => $users,
                'companyType'           => $companyType,
                'ga_title'              => trans('page_title.companies.survey_configuration', [
                    'company' => $company->name,
                ]),
            ];

            return \view('admin.companies_old.survey-config.index', $data);
        } catch (\Exception $exception) {
            report($exception);
            abort(500);
        }
    }

    /**
     * Update survey users
     *
     * @param Company $company
     * @param Request $request
     * @return JSON
     */
    public function setSurveyConfiguration(Company $company, UpdateSurveyUsersRequest $request)
    {
        if (!access()->allow('survey-configuration')) {
            abort(401);
        }

        try {
            \DB::beginTransaction();
            $data = $company->setSurveyConfiguration($request->all());
            if ($data) {
                \DB::commit();
                \Session::put('message', [
                    'data'   => trans('company.survey_configuration.messages.survey_config_success'),
                    'status' => 1,
                ]);
                return response()->json([
                    'status'  => 1,
                    'message' => trans('company.survey_configuration.messages.survey_config_success'),
                ], 200);
            } else {
                \DB::rollback();
                return response()->json([
                    'message' => trans('labels.common_title.something_wrong_try_again'),
                ], 500);
            }
        } catch (\Exception $exception) {
            \DB::rollback();
            report($exception);
            return response()->json([
                'message' => trans('labels.common_title.something_wrong_try_again'),
            ], 500);
        }
    }

    /**
     * Get All Selected Parent Reseller Data
     * @param $company Company
     * @return array
     **/
    protected function getAllSeletedParentResellerData($company = [])
    {
        
        $type = config('zevolifesettings.company_content_master_type');
        
        foreach ($type as $key => $value) {
            $subcategory = SubCategory::select('id', 'name')
                ->where('status', 1)->where("category_id", $key)
                ->pluck('name', 'id')->toArray();
               
            foreach ($subcategory as $subKey => $subValue) {
                $result = null;
                switch ($value) {
                    case 'Masterclass':
                        $result = DB::table('masterclass_company')->where('company_id', $company->id)->pluck('masterclass_id')->toArray();
                        break;
                    case 'Meditation':
                        $result = DB::table('meditation_tracks_company')->where('company_id', $company->id)->pluck('meditation_track_id')->toArray();
                        break;
                    case 'Webinar':
                        $result = DB::table('webinar_company')->where('company_id', $company->id)->pluck('webinar_id')->toArray();
                        break;
                    case 'Feed':
                        $result = DB::table('feed_company')->where('company_id', $company->id)->pluck('feed_id')->toArray();
                        break;
                    default:
                        $result = DB::table('recipe_company')->where('company_id', $company->id)->pluck('recipe_id')->toArray();
                        break;
                }

                if (!empty($result)) {
                    foreach ($result as $resultKey => $resultValue) {
                        $contentId          = $key . '-' . $subKey . '-' . $resultValue;
                        $subcategoryArray[] = $contentId;
                    }
                }
            }
        }

        return $subcategoryArray;
    }

    /**
     * @param Company $company, Request $request
     * @return View
     */
    public function portalFooter($companyType, Company $company, Request $request)
    {
        $user = auth()->user();
        if (!access()->allow('portal-footer') || !$company->is_reseller || !is_null($company->parent_id)) {
            abort(403);
        }
        try {
            $portal_footer_text = $company->branding->portal_footer_text ?? config('zevolifesettings.portalFooter.footerText');
            $portal_footer_data = json_decode($company->branding->portal_footer_json, true);
            if (!empty($portal_footer_data)) {
                $header1   = $portal_footer_data['header1'];
                $header2   = $portal_footer_data['header2'];
                $header3   = $portal_footer_data['header3'];
                $col1key   = array_keys($portal_footer_data['col1data']);
                $col2key   = array_keys($portal_footer_data['col2data']);
                $col3key   = array_keys($portal_footer_data['col3data']);
                $col1value = array_values($portal_footer_data['col1data']);
                $col2value = array_values($portal_footer_data['col2data']);
                $col3value = array_values($portal_footer_data['col3data']);
            } else {
                $header1   = config('zevolifesettings.portalFooter.header1');
                $header2   = config('zevolifesettings.portalFooter.header2');
                $header3   = config('zevolifesettings.portalFooter.header3');
                $col1key   = config('zevolifesettings.portalFooter.col1key');
                $col2key   = config('zevolifesettings.portalFooter.col2key');
                $col3key   = config('zevolifesettings.portalFooter.col3key');
                $col1value = config('zevolifesettings.portalFooter.col1value');
                $col2value = config('zevolifesettings.portalFooter.col2value');
                $col3value = config('zevolifesettings.portalFooter.col3value');
            }
            $data = [
                'company'            => $company,
                'ga_title'           => trans('page_title.companies.portalFooter'),
                'companyType'        => $companyType,
                'portal_footer_text' => $portal_footer_text,
                'header1'            => $header1,
                'header2'            => $header2,
                'header3'            => $header3,
                'col1key'            => $col1key,
                'col2key'            => $col2key,
                'col3key'            => $col3key,
                'col1value'          => $col1value,
                'col2value'          => $col2value,
                'col3value'          => $col3value,
            ];
            return \view('admin.companies_old.portalFooter', $data);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('admin.companiesold.index', $companyType)->with('message', $messageData);
        }
    }

    /**
     * @param $companyType, Company $company, StoreFooterDetailsRequest $request
     * @return RedirectResponse
     */
    public function storePortalFooterDetails($companyType, Company $company, StoreFooterDetailsRequest $request)
    {
        $user = auth()->user();
        if (!access()->allow('portal-footer') || !$company->is_reseller || !is_null($company->parent_id)) {
            abort(403);
        }
        try {
            $payload            = $request->all();
            $col1data           = array_combine(array_filter($payload['col1key']), array_filter($payload['col1value']));
            $col2data           = array_combine(array_filter($payload['col2key']), array_filter($payload['col2value']));
            $col3data           = array_combine(array_filter($payload['col3key']), array_filter($payload['col3value']));
            $portal_footer_data = [
                'header1'  => $payload['header1'],
                'col1data' => $col1data,
                'header2'  => $payload['header2'],
                'col2data' => $col2data,
                'header3'  => $payload['header3'],
                'col3data' => $col3data,
            ];
            $portal_footer_json = json_encode($portal_footer_data);
            $record             = $company->branding()->updateOrCreate(['company_id' => $company->id], [
                'portal_footer_text' => $payload['footer_text'],
                'portal_footer_json' => $portal_footer_json,
            ]);
            if ($record) {
                if (!empty($payload['portal_footer_logo'])) {
                    $name = $company->id . '_' . \time();
                    $company
                        ->clearMediaCollection('portal_footer_logo')
                        ->addMediaFromRequest('portal_footer_logo')
                        ->usingName($payload['portal_footer_logo']->getClientOriginalName())
                        ->usingFileName($name . '.' . $payload['portal_footer_logo']->getClientOriginalExtension())
                        ->toMediaCollection('portal_footer_logo', config('medialibrary.disk_name'));
                }
                $messageData = [
                    'data'   => "Portal footer data has been updated successfully!",
                    'status' => 1,
                ];
            } else {
                $messageData = [
                    'data'   => "Something went wrong please try again.",
                    'status' => 0,
                ];
            }
            return \Redirect::route('admin.companiesold.index', $companyType)->with('message', $messageData);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('admin.companiesold.index', $companyType)->with('message', $messageData);
        }
    }
}
