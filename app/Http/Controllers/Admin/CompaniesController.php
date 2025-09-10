<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CreateCompanyModeratorRequest;
use App\Http\Requests\Admin\CreateCompanyRequest;
use App\Http\Requests\Admin\EditCompanyRequest;
use App\Http\Requests\Admin\EditLimitRequest;
use App\Http\Requests\Admin\ExportSurveyReportRequest;
use App\Http\Requests\Admin\StoreCreditRequest;
use App\Http\Requests\Admin\StoreFooterDetailsRequest;
use App\Http\Requests\Admin\UpdateSurveyUsersRequest;
use App\Http\Requests\Admin\CreateDTBannerRequest;
use App\Http\Requests\Admin\EditDTBannerRequest;
use App\Models\AppSetting;
use App\Models\AppTheme;
use App\Models\Calendly;
use App\Models\ChallengeTarget;
use App\Models\Company;
use App\Models\CompanyWiseAppSetting;
use App\Models\CompanyWiseCredit;
use App\Models\CompanyDigitalTherapyBanner;
use App\Models\Country;
use App\Models\Course;
use App\Models\CourseSurveyQuestionAnswers;
use App\Models\CpPlan;
use App\Models\DigitalTherapyService;
use App\Models\DigitalTherapySpecific;
use App\Models\Feed;
use App\Models\Industry;
use App\Models\MeditationTrack;
use App\Models\Recipe;
use App\Models\Role;
use App\Models\Service;
use App\Models\SubCategory;
use App\Models\TempDigitalTherapySlots;
use App\Models\User;
use App\Models\Webinar;
use App\Models\ZcSurvey;
use App\Models\ZcSurveyLog;
use App\Models\ZcSurveyReportExportLogs;
use App\Models\ZcSurveyResponse;
use App\Models\CompanyBranding;
use App\Models\Podcast;
use App\Repositories\AuditLogRepository;
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
class CompaniesController extends Controller
{
    /**
     * Company model object
     *
     * @var model
     */
    protected $model;

    /**
     * @var AuditLogRepository $auditLogRepository
     */
    private $auditLogRepository;

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
     * CompanyWiseCredit model object
     *
     * @var companyWiseCredit
     **/
    protected $companyWiseCredit;

    /**
     * CompanyDigitalTherapyBanner model object
     *
     * @var companyDigitalTherapyBanner
     **/
    protected $companyDigitalTherapyBanner;
    
    /**
     * contructor to initialize model object
     *
     * @param Company $model
     * @param CompanyWiseAppSetting $companyWiseAppSetting
     * @param ZcSurveyReportExportLogs $zcSurveyReportExportLogs
     * @param ZcSurveyResponse $zcSurveyResponse
     * @param CourseSurveyQuestionAnswers $courseSurveyQuestionAnswers
     * @param CompanyWiseManageCredit $companyWiseCredit
     * @param CompanyDigitalTherapyBanner $companyDigitalTherapyBanner
     */
    public function __construct(Company $model, CompanyWiseAppSetting $companyWiseAppSetting, ZcSurveyReportExportLogs $zcSurveyReportExportLogs, ZcSurveyResponse $zcSurveyResponse, CourseSurveyQuestionAnswers $courseSurveyQuestionAnswers, CompanyWiseCredit $companyWiseCredit, CompanyDigitalTherapyBanner $companyDigitalTherapyBanner, AuditLogRepository $auditLogRepository)
    {
        $this->model                       = $model;
        $this->companyWiseAppSetting       = $companyWiseAppSetting;
        $this->zcSurveyReportExportLogs    = $zcSurveyReportExportLogs;
        $this->zcSurveyResponse            = $zcSurveyResponse;
        $this->courseSurveyQuestionAnswers = $courseSurveyQuestionAnswers;
        $this->companyWiseCredit           = $companyWiseCredit;
        $this->companyDigitalTherapyBanner = $companyDigitalTherapyBanner;
        $this->auditLogRepository          = $auditLogRepository;
        $this->bindBreadcrumbs();
    }

    /**
     * bind breadcrumbs of company modules
     */
    private function bindBreadcrumbs()
    {
        // companies crud
        Breadcrumbs::for ('companies.index', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push('Companies');
        });
        // list moderator
        Breadcrumbs::for ('companies.moderator.index', function ($trail, $companyType) {
            $trail->push('Home', route('dashboard'));
            $trail->push('Companies', route('admin.companies.index', $companyType));
            $trail->push("Moderators");
        });
        // create moderator
        Breadcrumbs::for ('companies.moderator.create', function ($trail, $companyType, $companyId) {
            $trail->push('Home', route('dashboard'));
            $trail->push('Companies', route('admin.companies.index', $companyType));
            $trail->push('Moderators', route('admin.companies.moderators', [$companyType, $companyId]));
            $trail->push('Create Moderator');
        });
        // view/set limits
        Breadcrumbs::for ('companies.limits.index', function ($trail, $companyType) {
            $trail->push('Home', route('dashboard'));
            $trail->push('Companies', route('admin.companies.index', $companyType));
            $trail->push("Limits");
        });
        Breadcrumbs::for ('companies.limits.edit', function ($trail, $companyType, $companyId) {
            $trail->push('Home', route('dashboard'));
            $trail->push('Companies', route('admin.companies.index', $companyType));
            $trail->push('Limits', route('admin.companies.getLimits', [$companyType, $companyId]));
            $trail->push('Edit Limits');
        });
        // survey-configuration
        Breadcrumbs::for ('companies.survey-configuration', function ($trail, $type) {
            $trail->push('Home', route('dashboard'));
            $trail->push('Companies', route('admin.companies.index', $type));
            $trail->push('Survey Configuration');
        });
        // portal footer
        Breadcrumbs::for ('companies.portalFooter', function ($trail, $companyType) {
            $trail->push('Home', route('dashboard'));
            $trail->push('Companies', route('admin.companies.index', $companyType));
            $trail->push('Portal Footer');
        });
        // manage credits
        Breadcrumbs::for ('companies.manageCredits', function ($trail, $companyType) {
            $trail->push('Home', route('dashboard'));
            $trail->push('Companies', route('admin.companies.index', $companyType));
            $trail->push('Manage Credits');
        });

        // DT Baners
        Breadcrumbs::for ('companies.dt-banners.index', function ($trail, $companyType) {
            $trail->push('Home', route('dashboard'));
            $trail->push('Companies', route('admin.companies.index', $companyType));
            $trail->push('DT Banners');
        });
    }

    /**
     * @return View
     */
    public function index(Request $request, $companyType)
    {
        try {
            $user                 = auth()->user();
            $role                 = getUserRole($user);
            $company              = $user->company->first();
            $companyPlanGroupType = ($companyType == 'zevo' ? 1 : 2);
            $cpPlans              = [];
            $cpPlans              = CpPlan::where('status', 1)->where('group', $companyPlanGroupType)->pluck('name', 'id')->toArray();

            if (!access()->allow('manage-company') || ($role->group == 'reseller' && $company->parent_id != null)) {
                return \view('errors.401');
            }

            $data = [
                'companySize'  => config('zevolifesettings.company_size'),
                'pagination'   => config('zevolifesettings.datatable.pagination.short'),
                'role'         => $role,
                'company'      => $company,
                'ga_title'     => trans('page_title.companies.companies_list'),
                'companyplans' => $cpPlans,
                'companyType'  => $companyType,
            ];

            return \view('admin.companies.index', $data);
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
        Breadcrumbs::for ('companies.create', function ($trail) use ($companyType) {
            $trail->push('Home', route('dashboard'));
            $trail->push('Companies', route('admin.companies.index', $companyType));
            $trail->push('Add Company');
        });
        $user                 = auth()->user();
        $user_role            = getUserRole($user);
        $company              = $user->company->first();
        $companyPlanGroupType = ($companyType == 'zevo' ? 1 : 2);
        $cpPlans              = [];
        if (!access()->allow('create-company') || ($user_role->group == 'reseller' && $company->parent_id != null)) {
            abort(403);
        }

        try {
            $parent_comapnies  = $roles  = $parentCompanyMedia  = $parentCompanyEmailHeader  = [];
            $brandingCo        = new Company();
            $maxEndDate        = now()->addYears(100)->toDateString();
            $has_branding      = $enable_survey      = $disable_survey      = $disable_branding      = $branding      = $disable_sso      = $isZendesk      = $disableCompanyLogo      = $disableEmailHeader      = $hide_content      = $disableUserConsent      = $excludeGenderAndDob      = $manageTheDesignChange      = $brandingContactData  = false;
            $mobile_app_value  = true;
            $disable_portal    = true;
            $isShowContentType = false;
            $dt_servicemode    = false;

            if ($user_role->group == 'zevo') {
                $parent_comapnies = Company::where(['is_reseller' => true])->get()->pluck('name', 'id')->toArray();
                if ($companyType == 'zevo') {
                    $parent_comapnies = array_replace(['zevo' => 'Zevo'], $parent_comapnies);
                }
                $roles             = Role::where(['group' => 'company', 'default' => 0])->get()->pluck('name', 'id')->toArray();
                $roles             = array_replace([2 => 'Zevo Company Admin'], $roles);
                $isShowContentType = true;
            } elseif ($user_role->group == 'reseller') {
                $isShowContentType     = $company->is_reseller;
                $parent_comapnies      = $company->id;
                $roles                 = $company->resellerRoles()->get()->pluck('name', 'id')->toArray();
                $brandingCo            = $company;
                $maxEndDate            = Carbon::parse($company->subscription_end_date)->toDateString();
                $branding              = $company->branding;
                $brandingContactData   = $company->brandingContactDetails;
                $has_branding          = (bool) $company->is_branding;
                $enable_survey         = (bool) $company->enable_survey;
                $disable_sso           = (bool) $company->disable_sso;
                $hide_content          = (bool) $company->hide_content;
                $mobile_app_value      = false;
                $disable_branding      = $disable_survey      = $disableCompanyLogo      = $disableEmailHeader      = true;
                $dt_servicemode        = true;
                $disableUserConsent    = true;
                $excludeGenderAndDob   = true;
                $manageTheDesignChange = true;
            }
            $parentCompanyEmailHeaderUrl = $parentCompanyEmailHeader = [];
            if ($companyType != 'zevo' && $companyType != 'reseller') {
                //Get parent company data
                $isZendesk                   = (bool) $company->is_intercom;
                $parentCompanyData           = Company::where('id', $company->id)->first();
                $parentCompanyMedia          = $parentCompanyData->getFirstMedia('logo');
                $parentCompanyEmailHeader    = $parentCompanyData->getFirstMedia('email_header');
                $parentCompanyEmailHeaderUrl = $parentCompanyData->getMediaData('email_header', ['w' => 600, 'h' => 157, 'zc' => 1]);

                $appointmentTitle          = !empty($parentCompanyData->branding->appointment_title) ? $parentCompanyData->branding->appointment_title : 'Appointments';
                $appointmentDescription    = !empty($parentCompanyData->branding->appointment_description) ? $parentCompanyData->branding->appointment_description : null;
                $appointmentImageName      = !empty($parentCompanyData->appointment_image_name) ?  $parentCompanyData->appointment_image_name : null ;
                $appointmentImage          = $parentCompanyData->getFirstMedia('appointment_image');
            }

            $cpPlans = CpPlan::where('status', 1)->where('group', $companyPlanGroupType)->pluck('name', 'id')->toArray();

            if ($user_role->group == 'reseller' && $company->parent_id == null) {
                $parentComapny = Company::find($company->id);
                $selectedPlan  = ($companyType == 'normal' ? $parentComapny->companyplan()->pluck('plan_id')->first() : CpPlan::where('slug', 'challenge')->orderBy('id', 'ASC')->pluck('id')->first());
            } else {
                $selectedPlan = CpPlan::where('slug', 'challenge')->orderBy('id', 'ASC')->pluck('id')->first();
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
            $data['brandingContactData']         = $brandingContactData;
            $data['has_branding']                = $has_branding;
            $data['disable_branding']            = $disable_branding;
            $data['enable_survey']               = $enable_survey;
            $data['disable_sso']                 = $disable_sso;
            $data['hide_content']                = $hide_content;
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
            $data['masterContentType']           = $this->getAllMasterContent($company, $companyType);
            $data['isShowContentType']           = $isShowContentType;
            $data['registration_restriction']    = config('zevolifesettings.domain_verification_types');
            $data['disableEvent']                = false;
            $data['totalSessions']               = 0;
            $data['companyplans']                = $cpPlans;
            $data['selectedPlan']                = $selectedPlan;
            $data['isShowPlan']                  = true;
            $data['companyType']                 = $companyType;
            $data['isZendesk']                   = $isZendesk;
            $data['companyData']                 = $company;
            $data['disableCompanyLogo']          = $disableCompanyLogo;
            $data['disableEmailHeader']          = $disableEmailHeader;
            $data['parentCompanyMedia']          = $parentCompanyMedia;
            $data['parentCompanyEmailHeader']    = $parentCompanyEmailHeader;
            $data['parentCompanyEmailHeaderUrl'] = $parentCompanyEmailHeaderUrl;
            $data['dt_availability_days']        = config('zevolifesettings.hc_availability_days');
            $data['dtSlots']                     = [];
            $data['dtWsIds']                     = [];
            $data['wellbeingSp']                 = [];
            $data['companyLocation']             = [];
            $data['dtWsNames']                   = [];
            $data['dt_servicemode']              = $dt_servicemode;
            $data['disableUserConsent']          = $disableUserConsent;
            //$data['disabledPortalCompanyPlan']   = $disabledPortalCompanyPlan;
            $data['excludeGenderAndDob']   = $excludeGenderAndDob;
            $data['manageTheDesignChange'] = $manageTheDesignChange;
            $setHoursBy                    = config('zevolifesettings.setHoursBy');
            unset($setHoursBy[2]);
            $data['setAvailabilityBy'] = config('zevolifesettings.setAvailabilityBy');
            $data['setHoursBy']        = $setHoursBy;
            for ($i = 0; $i < 50;) {
                $dtSessionRulesHrs[$i] = $i . ($i == 0 ? " Hour" : " Hours");
                $i                     = $i + 2;
            }
            $data['dtSessionRulesHrs'] = $dtSessionRulesHrs;
            for ($i = 0; $i <= 14;) {
                $dtSessionRulesDays[$i] = $i . ($i == 0 ? " Day" : " Days");
                $i++;
            }
            $data['dtSessionRulesDays'] = $dtSessionRulesDays;

            for ($i = 1; $i <= 14;) {
                $dtFutureBookingRules[$i] = $i . ($i == 1 ? " Day" : " Days");
                $i++;
            }
            $data['dtFutureBookingRules'] = $dtFutureBookingRules;
            $request['companyType']       = $companyType;
            $data['dtSessionRulesMins']   = config('cronofy.dtSessionRulesMins');
            $data['portalTheme']          = config('zevolifesettings.portal_theme');
            $data['wellbeingSp']          = User::select(DB::raw("CONCAT(users.first_name,' ',users.last_name) AS name"), 'users.id')->
                leftJoin('role_user', function ($join) {
                $join->on('role_user.user_id', '=', 'users.id');
            })
                ->leftJoin('roles', function ($join) {
                    $join->on('roles.id', '=', 'role_user.role_id');
                })
                ->leftJoin('ws_user', function ($join) {
                    $join->on('ws_user.user_id', '=', 'users.id');
                })
                ->whereNull('users.deleted_at')
                ->where('roles.slug', 'wellbeing_specialist')
                ->where('ws_user.responsibilities', '!=', 2)
                ->where('ws_user.is_cronofy', true)
                ->pluck('name', 'users.id')->toArray();

            $data['disableContactDetails'] = ($user_role->slug != 'super_admin');
            $data['contactUsRequest'] = config('zevolifesettings.branding_contact_details.contact_us_request');
            $data['dtLocationGenralSlots'] = [];
            $data['dtSpecificArray']       = [];

            //Set appointment details for RSA 
            $data['appointmentTitle']           = $appointmentTitle ?? 'Appointments';
            $data['appointmentDescription']     = $appointmentDescription ?? null;
            $data['appointmentImage']           = $appointmentImage ?? null;
            $data['appointmentImageName']       = $appointmentImageName ?? null;

            return \view('admin.companies.create', $data);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
           return \Redirect::route('admin.companies.index', $companyType)->with('message', $messageData);
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

            $userLogData = [
                'user_id'   => $user->id,
                'user_name' => $user->full_name,
            ];
            $data = $this->model->storeEntity($request->all());

            $logData = array_merge($userLogData, $request->all());
            $this->auditLogRepository->created("Company added successfully", $logData);

            if ($data) {
                \DB::commit();
                $messageData = [
                    'data'   => "Company has been added successfully!",
                    'status' => 1,
                ];
                return \Redirect::route('admin.companies.index', $companyType)->with('message', $messageData);
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
            return \Redirect::route('admin.companies.index', $companyType)->with('message', $messageData);
        }
    }

    /**
     * @return View
     */
    public function edit($companyType, Company $company, Request $request)
    {
        Breadcrumbs::for ('companies.edit', function ($trail) use ($companyType) {
            $trail->push('Home', route('dashboard'));
            $trail->push('Companies', route('admin.companies.index', $companyType));
            $trail->push('Edit Company');
        });
        $user                 = auth()->user();
        $user_role            = getUserRole($user);
        $user_company         = $user->company->first();
        $companyPlanGroupType = ($companyType == 'zevo' ? 1 : 2);
        $cpPlans              = [];
        if (!access()->allow('update-company') || ($user_role->group == 'reseller' && $user_company->parent_id != null)) {
            abort(403);
        }

        try {
            $nowInUTC    = now(config('app.timezone'))->toDateTimeString();
            $surveys     = ZcSurvey::where('status', '!=', 'Draft');
            $appTimezone = config('app.timezone');
            $survey      = $company->survey;
            $companyDT   = $company->digitalTherapy()->first();
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
            $branding                = $brandingContactData = null;
            $disable_branding        = $disable_survey        = $disable_mobile_app        = $disableUserConsent        = $excludeGenderAndDob        = $manageTheDesignChange         =  false;
            $mobile_app_value        = $company->allow_app;
            $has_branding            = (bool) $company->is_branding;
            $brandingCo              = $company;
            $current_date            = now()->setTime(0, 0, 0, 0)->toDateString();
            $max_end_date            = now()->addYears(100)->toDateString();
            $subscription_start_date = Carbon::parse($company->subscription_start_date);
            $subscription_end_date   = Carbon::parse($company->subscription_end_date);
            $start_start_date        = $current_date;
            $end_start_date          = $current_date;
            $disable_portal          = (!$company->is_reseller);
            $disable_portal_domain   = true;
            $usersAttachedRole       = [];
            $isShowContentType       = false;
            $isZendesk               = (bool) $company->is_intercom;
            $dt_servicemode          = false;
            if (!$company->is_reseller) {
                if (!is_null($company->parent_id)) {
                    $parentComapny             = Company::find($company->parent_id);
                    $parent_comapnies          = (!empty($parentComapny) ? $parentComapny->name : "");
                    $roles                     = $parentComapny->resellerRoles();
                    $brandingCo                = $parentComapny;
                    $branding                  = $parentComapny->branding;
                    $appointmentTitle          = !empty($company->branding->appointment_title) ? $company->branding->appointment_title : $parentComapny->branding->appointment_title;
                    $appointmentDescription    = !empty($company->branding->appointment_description) ? $company->branding->appointment_description : $parentComapny->branding->appointment_description;
                    $appointmentImageName      = !empty($company->appointment_image_name) ?  $company->appointment_image_name : $parentComapny->appointment_image_name ?? null ;
                    $brandingContactData       = !empty($company->brandingContactDetails) ? $company->brandingContactDetails : $parentComapny->brandingContactDetails;
                    $has_branding              = (bool) $parentComapny->is_branding;
                    $disable_branding          = true;
                    $disable_survey            = true;
                    $roleIds                   = $roles->get()->implode('id', ',');
                    $usersAttachedRole         = DB::select("select `id`, (select count(users.id) from `users` inner join `role_user` on `users`.`id` = `role_user`.`user_id` inner join `user_team` on `user_team`.`user_id` = `users`.`id` where `roles`.`id` = `role_user`.`role_id` AND `user_team`.`company_id` = ?) as `users_count` from `roles` where (`group` = ? and `default` = 0 and id in ({$roleIds})) group by `roles`.`id` having `users_count` > 0", [$company->id, 'reseller']);
                    $parent_company_start_date = Carbon::parse($parentComapny->subscription_start_date)->toDateString();
                    $max_end_date              = Carbon::parse($parentComapny->subscription_end_date)->toDateString();
                    $start_start_date          = $parent_company_start_date;
                    $end_start_date            = $max_end_date;
                    if ($current_date > $start_start_date) {
                        $start_start_date = $current_date;
                    }

                    if (!empty($company->getFirstMedia('appointment_image'))) {
                        $appointmentImage = $company->appointment_image;
                    } elseif (!is_null($company->parent_id) && !empty($parentComapny->getFirstMedia('appointment_image'))) {
                        $appointmentImage = $parentComapny->appointment_image;
                    } else {
                        $appointmentImage = null;
                    }
                } else {
                    $parent_comapnies       = config('zevolifesettings.company_types.zevo');
                    $roles                  = Role::select('name', 'id')->where(['group' => 'company', 'default' => 0]);
                    $branding               = $company->branding;
                    $brandingContactData    = $company->brandingContactDetails;
                    $disable_mobile_app     = true;
                    $usersAttachedRole      = DB::select("select `id`, (select count(users.id) from `users` inner join `role_user` on `users`.`id` = `role_user`.`user_id` inner join `user_team` on `user_team`.`user_id` = `users`.`id` where `roles`.`id` = `role_user`.`role_id` AND `user_team`.`company_id` = ?) as `users_count` from `roles` where (`group` = ? and `default` = 0) group by `roles`.`id` having `users_count` > 0", [$company->id, 'company']);
                }
            } else {
                $roles                  = Role::select('name', 'id')->where(['group' => 'reseller', 'default' => 0]);
                $branding               = $company->branding;
                $brandingContactData    = $company->brandingContactDetails;
                $disable_survey         = true;
                $allcompanies           = Company::where('parent_id', $company->id)->orWhere('id', $company->id)->get()->implode('id', ',');
                $usersAttachedRole      = DB::select("select `id`, (select count(users.id) from `users` inner join `role_user` on `users`.`id` = `role_user`.`user_id` inner join `user_team` on `user_team`.`user_id` = `users`.`id` where `roles`.`id` = `role_user`.`role_id` AND `user_team`.`company_id` in ({$allcompanies})) as `users_count` from `roles` where (`group` = ? and `default` = 0) group by `roles`.`id` having `users_count` > 0", ['reseller']);
                $appointmentTitle          = !empty($company->branding->appointment_title) ? $company->branding->appointment_title : 'Appointments';
                $appointmentDescription    = !empty($company->branding->appointment_description) ? $company->branding->appointment_description : null;
                $appointmentImageName      = !empty($company->appointment_image_name) ?  $company->appointment_image_name : null ;

                if (!empty($company->getFirstMedia('appointment_image'))) {
                    $appointmentImage = $company->appointment_image;
                } else {
                    $appointmentImage = null;
                }
            }

            $parentCompanyMedia = $parentCompanyEmailHeader = [];
            $parentHideContent  = false;
            if ($companyType != 'zevo' && $companyType != 'reseller') {
                // Get parent company data
                $parentCompanyData        = Company::where('id', $parentComapny->id)->first();
                $parentCompanyMedia       = $parentCompanyData->getFirstMedia('logo');
                $parentCompanyEmailHeader = $parentCompanyData->getFirstMedia('email_header');
            }

            if (!is_null($company->parent_id)) {
                $parentCompanyData = Company::where('id', $company->parent_id)->first();
                $parentHideContent = $parentCompanyData->hide_content;
            }

            $isShowContentType = true;
            $disableEvent      = false;
            if ($company->enable_event) {
                $upComingEvents = $company->evnetBookings()
                    ->where('event_booking_logs.status', '4')
                    ->whereRaw("TIMESTAMP(CONCAT(event_booking_logs.booking_date, ' ', event_booking_logs.end_time)) > ?", [$nowInUTC])
                    ->count('event_booking_logs.id');
                $disableEvent = (!empty($upComingEvents));
            }
            if ($user_role->group == 'reseller') {
                $dt_servicemode = true;
                if (!is_null($company->parent_id)) {
                    $disableUserConsent = true;
                }
                $excludeGenderAndDob   = true;
                $manageTheDesignChange = true;
            }
            if ($companyType == 'zevo' || $company->is_reseller = true) {
                $selectedPlan = $company->companyplan()->pluck('plan_id')->first();
            }

            $cpPlans                   = CpPlan::where('status', 1)->where('group', $companyPlanGroupType)->pluck('name', 'id')->toArray();
            $companyLocation           = $company->locations(); //->get();
            $locationWiseSlots         = [];
            $locationWiseSpecificSlots = [];

            $companyLocation = $companyLocation
                ->select(
                    'company_locations.id',
                    'company_locations.name'
                )->selectRaw(
                    '(select count(digital_therapy_specific.id) FROM digital_therapy_specific WHERE company_id = ? AND digital_therapy_specific.location_id = company_locations.id) as slot_count_specific'
                ,[$company->id])->selectRaw(
                    '(select count(digital_therapy_slots.id) FROM digital_therapy_slots WHERE company_id = ? AND digital_therapy_slots.location_id = company_locations.id) as slot_count_general'
                ,[$company->id])
                ->groupBy('company_locations.id')
                ->get();

            // Get Company Specific Records
            $dtSpecific = DigitalTherapySpecific::select('ws_id', 'date')
                ->where('company_id', $company->id)
                ->whereNull('location_id')
                ->get();
            $dtSpecificArray = [];
            if (!empty($dtSpecific)) {
                foreach ($dtSpecific as $key => $value) {
                    $dtSpecificArray[$value->ws_id][] = Carbon::parse($value['date'])->toDateString();
                }
            }

            // Get Company Location Specific Records
            $dtLocationSpecific = DigitalTherapySpecific::select('location_id', 'ws_id', 'date')
                ->where('company_id', $company->id)
                ->whereNotNull('location_id')
                ->get();
            $dtLocationSpecificArray = [];
            if (!empty($dtLocationSpecific)) {
                foreach ($dtLocationSpecific as $key => $value) {
                    $dtLocationSpecificArray[$value->location_id][$value->ws_id][] = Carbon::parse($value['date'])->toDateString();
                }
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
            $data['brandingContactData']      = $brandingContactData;
            $data['has_branding']             = $has_branding;
            $data['disable_branding']         = $disable_branding;
            $data['survey']                   = $company->survey;
            $data['enable_survey']            = (bool) $company->enable_survey;
            $data['disable_sso']              = (bool) $company->disable_sso;
            $data['hide_content']             = (!is_null($company->parent_id)) ? $parentHideContent : (bool) $company->hide_content;
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
            $data['masterContentType']        = $this->getAllMasterContent($user_company, $companyType);
            $data['selectedContent']          = $this->getAllSeletedParentResellerData($company, $companyType);
            $data['registration_restriction'] = config('zevolifesettings.domain_verification_types');
            $data['disableEvent']             = $disableEvent;
            $data['companyplans']             = $cpPlans;
            $data['selectedPlan']             = $selectedPlan;
            $data['isShowPlan']               = true;
            $checkAccess                      = getCompanyPlanAccess($user, 'eap');
            $data['companyType']              = $companyType;
            $data['isZendesk']                = $isZendesk;
            $data['parentCompanyMedia']       = $parentCompanyMedia;
            $data['parentCompanyEmailHeader'] = $parentCompanyEmailHeader;
            $data['pagination']               = config('zevolifesettings.datatable.pagination.short');
            $data['dt_availability_days']     = config('zevolifesettings.hc_availability_days');
            $data['dtSlots']                  = [];
            $data['dtData']                   = [];
            $data['dt_servicemode']           = $dt_servicemode;
            $data['appTimezone']              = $appTimezone;
            $data['disableUserConsent']       = $disableUserConsent;
            $data['excludeGenderAndDob']      = $excludeGenderAndDob;
            $data['manageTheDesignChange']    = $manageTheDesignChange;
            $data['dtSpecificArray']          = $dtSpecificArray;
            $data['dtLocationSpecificArray']  = $dtLocationSpecificArray;
            $data['disableContactDetails'] = ($user_role->slug != 'super_admin');
            for ($i = 0; $i < 50;) {
                $dtSessionRulesHrs[$i] = $i . ($i == 0 ? " Hour" : " Hours");
                $i                     = $i + 2;
            }
            $data['dtSessionRulesHrs'] = $dtSessionRulesHrs;
            for ($i = 0; $i <= 14;) {
                $dtSessionRulesDays[$i] = $i . ($i == 0 ? " Day" : " Days");
                $i++;
            }
            $data['dtSessionRulesDays'] = $dtSessionRulesDays;
            $data['dtSessionRulesMins'] = config('cronofy.dtSessionRulesMins');

            for ($i = 1; $i <= 14;) {
                $dtFutureBookingRules[$i] = $i . ($i == 1 ? " Day" : " Days");
                $i++;
            }
            $data['dtFutureBookingRules'] = $dtFutureBookingRules;

            if ($companyType == 'normal') {
                $data['disableCompanyLogo'] = true;
                $data['disableEmailHeader'] = true;
                $checkAccess                = true;
            } else {
                $data['disableCompanyLogo'] = false;
                $data['disableEmailHeader'] = false;
            }
            $data['portalTheme'] = config('zevolifesettings.portal_theme');

            TempDigitalTherapySlots::where('company_id', $company->id)->delete();

            if ($company->eap_tab || $checkAccess) {
                $data['totalSessions'] = Calendly::Join('user_team', 'user_team.user_id', '=', 'eap_calendly.user_id')
                    ->where('user_team.company_id', $company->id)
                    ->where('eap_calendly.end_time', ">=", $nowInUTC)
                    ->whereNull('cancelled_at')
                    ->count();
                $dtWsIds   = $company->digitalTherapyService()->distinct('ws_id')->groupBy('ws_id')->get()->implode('ws_id', ',');
                $dtWsNames = $company->digitalTherapyService()
                    ->leftjoin('users', 'users.id', '=', 'digital_therapy_services.ws_id')
                    ->select(
                        'users.id',
                        DB::raw('concat(users.first_name," ",users.last_name) as user_name')
                    )->selectRaw(
                        '(select count(id) FROM digital_therapy_specific WHERE ws_id = users.id AND company_id = ? AND date >= CURDATE() AND location_id IS NULL) as slot_count'
                    ,[$company->id])
                    ->whereNull('users.deleted_at')
                    ->distinct()
                    ->get()
                    ->toArray();

                $data['dtWsIds']                   = $dtWsIds;
                $data['dtWsNames']                 = $dtWsNames;
                $request['fromPage']               = 'edit';
                $request['wsIds']                  = $dtWsIds;
                $request['companyType']            = $companyType;
                $data['setAvailabilityBy']         = config('zevolifesettings.setAvailabilityBy');
                $data['setHoursBy']                = config('zevolifesettings.setHoursBy');
                $data['companyDT']                 = $companyDT;
                $data['companyLocation']           = $companyLocation;
                $data['locationWiseSlots']         = $locationWiseSlots;
                $data['locationWiseSpecificSlots'] = $locationWiseSpecificSlots;
                $data['staffServicesData']         = $this->getStaffServices($request, $company);

            } else {
                $data['totalSessions'] = 0;
            }

            $data['wellbeingSp'] = User::select(DB::raw("CONCAT(users.first_name,' ',users.last_name) AS name"), 'users.id')->
                leftJoin('role_user', function ($join) {
                $join->on('role_user.user_id', '=', 'users.id');
            })
                ->leftJoin('roles', function ($join) {
                    $join->on('roles.id', '=', 'role_user.role_id');
                })
                ->leftJoin('ws_user', function ($join) {
                    $join->on('ws_user.user_id', '=', 'users.id');
                })
                ->whereNull('users.deleted_at')
                ->where('roles.slug', 'wellbeing_specialist')
                ->where('ws_user.is_cronofy', true)
                ->where('ws_user.responsibilities', '!=', 2)
                ->pluck('name', 'users.id')
                ->toArray();

            // Company General Slots
            $dtSlots      = $company->digitalTherapySlots()->whereNull('location_id');
            $daywiseSlots = [];
            if ($dtSlots->count() > 0) {
                foreach ($dtSlots->get() as $slots) {
                    $wsTemplate       = "";
                    $wsHiddenTemplate = "";
                    if (!empty($slots->ws_id)) {
                        $wsId  = explode(',', $slots->ws_id);
                        $count = 1;
                        foreach ($wsId as $id) {
                            $blankString = "";
                            if ($count < count($wsId)) {
                                $blankString = ", ";
                            }
                            if (array_key_exists($id, $data['wellbeingSp'])) {
                                $ws_name = $data['wellbeingSp'][$id];
                                $value   = $id;
                                $key     = $slots->day;
                                $id      = $slots->id;
                                $wsTemplate .= view('admin.companies.slot-ws-preview', compact('ws_name', 'value', 'key', 'id'))->render() . $blankString;
                                $wsHiddenTemplate .= view('admin.companies.slot-ws-hidden', compact('ws_name', 'value', 'key', 'id'))->render();
                            }
                            $count++;
                        }
                    }
                    $daywiseSlots[$slots->day][] = [
                        'id'               => $slots->id,
                        'start_time'       => Carbon::createFromFormat('H:i:s', $slots->start_time, $user->timezone),
                        'end_time'         => Carbon::createFromFormat('H:i:s', $slots->end_time, $user->timezone),
                        'ws_id'            => $wsTemplate,
                        'wsHiddenTemplate' => $wsHiddenTemplate,
                    ];
                }
            }

            // Company Specific Slots
            $dtSpecificSlot = $company->digitalTherapySpecificSlots()->get();
            $data['dtSpecificSlot'] = $dtSpecificSlot;
            $data['dtSlots']        = $daywiseSlots;
            $data['dtData']         = $company->digitalTherapy;

            $dtLocationGenralSlots = $company->digitalTherapySlots()->whereNotNull('location_id')->get()->toArray();
            $data['dtLocationGenralSlots'] = $dtLocationGenralSlots;

            if ($company->enable_survey) {
                $data['surveyRollOutData'] = $this->getUpcomingSurveyDetails($request, $company);
            }
            $data['company'] = $company;
            $data['contactUsRequest'] = config('zevolifesettings.branding_contact_details.contact_us_request');

            $data['appointmentTitle']       = $appointmentTitle ?? null;
            $data['appointmentDescription'] = $appointmentDescription ?? null;
            $data['appointmentImage']       = $appointmentImage ?? null;
            $data['appointmentImageName']   = $appointmentImageName ?? null;

            return \view('admin.companies.edit', $data);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
           return \Redirect::route('admin.companies.index', $companyType)->with('message', $messageData);
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

            $userLogData = [
                'user_id'   => $user->id,
                'user_name' => $user->full_name,
            ];
            $oldUsersData       = array_merge($userLogData, $company->toArray());
            $data = $company->updateEntity($request->all());

            $updatedUsersData   = array_merge($userLogData, $request->all());
            $finalLogs          = ['olddata' => $oldUsersData, 'newdata' => $updatedUsersData];
            $this->auditLogRepository->created("Company updated successfully", $finalLogs);


            if ($data) {
                \DB::commit();
                $messageData = [
                    'data'   => "Company has been updated successfully!",
                    'status' => 1,
                ];
                return \Redirect::route('admin.companies.index', $companyType)->with('message', $messageData);
            } else {
                \DB::rollback();
                $messageData = [
                    'data'   => "Something went wrong please try again.",
                    'status' => 0,
                ];
                return \Redirect::route('admin.companies.edit', [$companyType, $company->id])->with('message', $messageData);
            }
        } catch (\Exception $exception) {
            \DB::rollback();
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('admin.companies.index', $companyType)->with('message', $messageData);
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
            $userLogData = [
                'user_id'   => $user->id,
                'user_name' => $user->full_name,
            ];
            $logs  = array_merge($userLogData, ['deleted_company_id' => $company->id,'deleted_company_name' => $company->name]);
            $this->auditLogRepository->created("Company deleted successfully", $logs);

            return $company->deleteRecord();
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('admin.companies.index','reseller')->with('message', $messageData);
        }
    }

    /**
     * @return View
     */
    public function teams($companyType, Company $company, Request $request)
    {
        // get teams
        Breadcrumbs::for ('companies.teams', function ($trail) use ($companyType) {
            $trail->push('Home', route('dashboard'));
            $trail->push('Companies', route('admin.companies.index', $companyType));
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
            return \view('admin.companies.teams', $data);
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
            return \Redirect::route('admin.companies.index')->with('message', $messageData);
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
                'cancel_url'  => (($referrer == 'index') ? route('admin.companies.index', $companyType) : route('admin.companies.moderators', [$companyType, $company->id])),
            ];

            return \view('admin.companies.createmoderator', $data);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('admin.companies.index', $companyType)->with('message', $messageData);
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
                $url = (($referrer == 'index') ? route('admin.companies.index', $companyType) : route('admin.companies.moderators', [$companyType, $company->id]));
                return redirect($url)->with('message', $messageData);
            } else {
                \DB::rollback();
                $messageData = [
                    'data'   => "Something went wrong please try again.",
                    'status' => 0,
                ];
                return \Redirect::route('admin.companies.createmoderator', $companyType)->with('message', $messageData);
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
                return \Redirect::route('admin.companies.index', $companyType)->with('message', $messageData);
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
            return \view('admin.companies.moderators', $data);
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
            return \Redirect::route('admin.companies.index')->with('message', $messageData);
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

            return \view('admin.companies.limits', $data);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('admin.companies.index', $companyType)->with('message', $messageData);
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
            $type = array_key_exists($type, $hrefArray) ? $type : 'challenge';
            $data = [
                'company'     => $company,
                'type'        => $type,
                'companyType' => $companyType,
                'ga_title'    => trans('page_title.companies.editLimits'),
                'cancel_url'  => route('admin.companies.getLimits', [$companyType, $company->id, $hrefArray[$type]]),
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
                if ($company->is_reseller || !is_null($company->parent_id)) {
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

            return \view('admin.companies.editlimits', $data);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('admin.companies.index', $companyType)->with('message', $messageData);
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
        $user   = auth()->user();
        $role   = getUserRole();
        if (!access()->allow('view-limits') || $role->group != 'zevo') {
            abort(401);
        }

        try {
            \DB::beginTransaction();
            $userLogData = [
                'user_id'       => $user->id,
                'user_name'     => $user->full_name,
                'company_id'    => $company->id,
                'company_name'  => $company->name,
            ];
            $hrefArray = [
                'challenge'          => '#challengepoints',
                'reward'             => '#rewardspoints',
                'reward-daily-limit' => '#rewardspointslimit',
            ];
            $data = $company->updateLimits($request->all());
            $logData = array_merge($userLogData, $request->all());
            $this->auditLogRepository->created("Limits updated successfully", $logData);

            if ($data) {
                \DB::commit();
                $messageData = [
                    'data'   => "Limits has been updated successfully for the requested company!",
                    'status' => 1,
                ];
                return \Redirect::route('admin.companies.getLimits', [$companyType, $company->id, $hrefArray[$request->type]])->with('message', $messageData);
            } else {
                \DB::rollback();
                $messageData = [
                    'data'   => "Something went wrong please try again.",
                    'status' => 0,
                ];
                return \Redirect::route('admin.companies.editLimits', [$companyType, $company->id, $hrefArray[$request->type]])->with('message', $messageData);
            }
        } catch (\Exception $exception) {
            \DB::rollback();
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('admin.companies.index', $companyType)->with('message', $messageData);
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
        Breadcrumbs::for ('companies.app-settings.index', function ($trail) use ($companyType) {
            $trail->push('Home', route('dashboard'));
            $trail->push('Companies', route('admin.companies.index', $companyType));
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
            return \view('admin.companies.changeAppSettingIndex', $data);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('admin.companies.index', $companyType)->with('message', $messageData);
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
        Breadcrumbs::for ('companies.app-settings.update', function ($trail, $companyType, $companyId) {
            $trail->push('Home', route('dashboard'));
            $trail->push('Companies', route('admin.companies.index', $companyType));
            $trail->push('App Settings', route('admin.companies.changeAppSettingIndex', [$companyType, $companyId]));
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
                    foreach ($companyWiseAppSetting as $value) {
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
            foreach ($defaultAppSetting as $value) {
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
            return \view('admin.companies.changeAppSettingCreateEdit', $data);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('admin.companies.changeAppSettingIndex', $companyType, $request->company)->with('message', $messageData);
        }
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function changeAppSettingStoreUpdate(Request $request)
    {
        $user   = auth()->user();
        $role   = getUserRole();
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
                return \Redirect::route('admin.companies.changeAppSettingCreateEdit', [$companyType, $company])->with('message', $messageData);
            }
            DB::beginTransaction();
            unset($payload['_token']);
            unset($payload['company_id']);
            unset($payload['companyType']);
            $userLogData = [
                'user_id'       => $user->id,
                'user_name'     => $user->full_name,
                'company_id'    => $company->id,
                'company_name'  => $company->name
            ];
            $data    = $this->companyWiseAppSetting->storeUpdateEntity($payload, $company);
            $logData = array_merge($userLogData, $payload);
            $this->auditLogRepository->created("App settings updated successfully", $logData);

            if ($data) {
                DB::commit();
                $messageData = [
                    'data'   => trans('labels.app_settings.data_store_success'),
                    'status' => 1,
                ];
                return \Redirect::route('admin.companies.changeAppSettingIndex', [$companyType, $company])->with('message', $messageData);
            } else {
                DB::rollBack();
                $messageData = [
                    'data'   => trans('labels.common_title.something_wrong_try_again'),
                    'status' => 0,
                ];
                return \Redirect::route('admin.companies.changeAppSettingCreateEdit', [$companyType, $company])->with('message', $messageData);
            }
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('admin.companies.changeAppSettingIndex', [$companyType, $request->company_id])->with('message', $messageData);
        }
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function changeToDefaultSettings($companyType, Request $request)
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
                $company->companyWiseAppSetting()->delete();
            }

            $messageData = [
                'data'   => 'Default app settings will be used.',
                'status' => 1,
            ];
            return \Redirect::route('admin.companies.changeAppSettingIndex', [$companyType, $request->company])->with('message', $messageData);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('admin.companies.changeAppSettingIndex', $request->company)->with('message', $messageData);
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
    protected function getAllMasterContent($company = null, $companyType)
    {
        $type = config('zevolifesettings.company_content_master_type');
        if ($companyType == 'zevo') {
            $type = config('zevolifesettings.company_content_master_type_zevo');
        }
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
                    case 'Podcast':
                        $result = Podcast::select('podcasts.id', 'podcasts.title', 'category_tags.name as categoryTag')
                            ->where('sub_category_id', $subKey)
                            ->where(function ($query) use ($company) {
                                if (!empty($company)) {
                                    $assignedContent = DB::select("SELECT podcast_id FROM `podcast_company` WHERE `company_id` = ?", [$company->id]);
                                    $assignedContent = Collect($assignedContent)->pluck('podcast_id')->toArray();
                                    $query->whereIn('podcasts.id', $assignedContent);
                                }
                            })
                            ->leftjoin('category_tags', 'category_tags.id', '=', 'podcasts.tag_id')
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
                    foreach ($result as $item) {
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
                        ->each(function ($department) use ($users, $location, $existingUsers) {
                            $department->teams()->select('teams.id', 'teams.name')
                                ->whereHas('teamlocation', function ($query) use ($location) {
                                    $query->where('company_locations.id', $location->id);
                                })->get()
                                ->each(function ($team) use ($users, $location, $department, $existingUsers) {
                                    $team->users()->select('users.id', 'users.first_name', 'users.last_name', 'users.email')->get()
                                        ->each(function ($user) use ($users, $location, $department, $team, $existingUsers) {
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

            return \view('admin.companies.survey-config.index', $data);
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
    protected function getAllSeletedParentResellerData($company = [], $companyType)
    {
        $type = config('zevolifesettings.company_content_master_type');
        if ($companyType == 'zevo') {
            $type = config('zevolifesettings.company_content_master_type_zevo');
        }
        $subcategoryArray = [];
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
                    case 'Podcast':
                        $result = DB::table('podcast_company')->where('company_id', $company->id)->pluck('podcast_id')->toArray();
                        break;
                    default:
                        $result = DB::table('recipe_company')->where('company_id', $company->id)->pluck('recipe_id')->toArray();
                        break;
                }

                if (!empty($result)) {
                    foreach ($result as $resultValue) {
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
        if (!access()->allow('portal-footer') || (!$company->is_reseller && is_null($company->parent_id))) {
            abort(403);
        }
        try {
            if (!is_null($company->parent_id)) {
                $parentCompany                  = Company::find($company->parent_id);
                $parentPortalFooterText         = $parentCompany->branding->portal_footer_text ?? null;
                $parentPortalFooterHeaderText   = $parentCompany->branding->portal_footer_header_text ?? null;
                $parentPortalFooterData         = json_decode($parentCompany->branding->portal_footer_json, true);
                $parentFooterLogoName           = $parentCompany->portal_footer_logo_name ?? null;
                $portalDomain                   = !empty($parentCompany->branding->portal_domain) ? "https://".$parentCompany->branding->portal_domain."/" : null;
            }

            $portal_footer_text        = !empty($company->branding->portal_footer_text) ? $company->branding->portal_footer_text : $parentPortalFooterText ?? config('zevolifesettings.portalFooter.footerText');
            $portal_footer_header_text = !empty($company->branding->portal_footer_header_text) ? $company->branding->portal_footer_header_text :  $parentPortalFooterHeaderText ?? config('zevolifesettings.portalFooter.footerHeader');
            $portal_footer_data        = !empty($company->branding->portal_footer_json) ?  json_decode($company->branding->portal_footer_json, true) : $parentPortalFooterData ?? null ;
            $portal_footer_logo_name   = !empty($company->portal_footer_logo_name) ?  $company->portal_footer_logo_name : $parentFooterLogoName ?? null ;
            $portalDomain              = !empty($company->branding->portal_domain) ? "https://".$company->branding->portal_domain."/" : null;

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
                $col1value = preg_filter('/^/', $portalDomain, $col1value);
                $col2value = config('zevolifesettings.portalFooter.col2value');
                $col2value = preg_filter('/^/', $portalDomain, $col2value);
                $col3value = config('zevolifesettings.portalFooter.col3value');
                $col3value = preg_filter('/^/', $portalDomain, $col3value);
            }

            if (!empty($company->getFirstMedia('portal_footer_logo'))) {
                $portal_footer_logo = $company->portal_footer_logo;
            } elseif (!is_null($company->parent_id) && !empty($parentCompany->getFirstMedia('portal_footer_logo'))) {
                $portal_footer_logo = $parentCompany->portal_footer_logo;
            } else {
                $portal_footer_logo = null;
            }
            $data = [
                'company'                   => $company,
                'portal_footer_logo'        => $portal_footer_logo,
                'portal_footer_logo_name'   => $portal_footer_logo_name,
                'ga_title'                  => trans('page_title.companies.portalFooter'),
                'companyType'               => $companyType,
                'portal_footer_text'        => $portal_footer_text,
                'portal_footer_header_text' => $portal_footer_header_text,
                'header1'                   => $header1,
                'header2'                   => $header2,
                'header3'                   => $header3,
                'col1key'                   => $col1key,
                'col2key'                   => $col2key,
                'col3key'                   => $col3key,
                'col1value'                 => $col1value,
                'col2value'                 => $col2value,
                'col3value'                 => $col3value,
            ];
            
            return \view('admin.companies.portalFooter', $data);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('admin.companies.index', $companyType)->with('message', $messageData);
        }
    }

    /**
     * @param $companyType, Company $company, StoreFooterDetailsRequest $request
     * @return RedirectResponse
     */
    public function storePortalFooterDetails($companyType, Company $company, StoreFooterDetailsRequest $request)
    {
        if (!access()->allow('portal-footer') || (!$company->is_reseller && is_null($company->parent_id))){
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
           
            $portal_footer_json = htmlspecialchars_decode(json_encode($portal_footer_data, JSON_FORCE_OBJECT));
            $record             = $company->branding()->updateOrCreate(['company_id' => $company->id], [
                'portal_footer_text'        => $payload['footer_text'],
                'portal_footer_json'        => $portal_footer_json,
                'portal_footer_header_text' => !empty($payload['portal_footer_header_text']) ? $payload['portal_footer_header_text'] : null,
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
                    'data'   => trans('company.messages.portal_footer_updated'),
                    'status' => 1,
                ];
            } else {
                $messageData = [
                    'data'   => trans('labels.common_title.something_wrong_try_again'),
                    'status' => 0,
                ];
            }
            return \Redirect::route('admin.companies.index', $companyType)->with('message', $messageData);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('admin.companies.index', $companyType)->with('message', $messageData);
        }
    }

    /**
     * @param Request $request, Company $company
     * @return Array
     */
    public function getUpcomingSurveyDetails(Request $request, Company $company)
    {
        if (!access()->allow('create-company') && !access()->allow('update-company')) {
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong_try_again'),
                'status' => 0,
            ];
            return response($messageData, 500)->header('Content-Type', 'application/json');
        }
        try {
            $appTimezone             = config('app.timezone');
            $payload                 = $request->all();
            $user                    = auth()->user();
            $company                 = isset($company->id) ? $company : (!empty($payload['company']) ? Company::find($payload['company']) : '');
            $nextSurveyAfter         = Carbon::now()->setTimezone($user->timezone);
            $subscription_start_date = isset($payload['subscription_start_date']) ? $payload['subscription_start_date'] : Carbon::parse($company->subscription_start_date, $appTimezone)->setTimezone($user->timezone); //$company->subscription_start_date;

            if (!empty($subscription_start_date) && ($subscription_start_date > Carbon::now()->setTimezone($user->timezone))) {
                $nextSurveyAfter = $subscription_start_date;
            }

            if (isset($company->id) && $company != null && !empty($company->survey->survey_id)) {
                $lastSurvey = ZcSurveyLog::select('id', 'roll_out_date', 'expire_date')
                    ->where('survey_id', $company->survey->survey_id)
                    ->where('company_id', $company->id)
                    ->orderBy('id', 'DESC')
                    ->first();

                if (isset($lastSurvey) && $lastSurvey != null) {
                    $lastSurveyRollOutDay = Carbon::parse($lastSurvey->roll_out_date, $appTimezone)->setTimezone($user->timezone)->format('M d,Y, H:i');
                    $lastSurveyExpireDay  = Carbon::parse($lastSurvey->expire_date, $appTimezone)->setTimezone($user->timezone)->format('M d,Y, H:i');
                    $nextSurveyAfter      = Carbon::parse($lastSurvey->expire_date)->setTimezone($user->timezone);
                }
            }
            $surveyRollOutDay = isset($payload['survey_roll_out_day']) ? $payload['survey_roll_out_day'] : (isset($company->id) ? $company->survey->survey_roll_out_day : '');
            $rollOutTime      = isset($payload['roll_out_time']) ? $payload['roll_out_time'] : (isset($company->id) ? $company->survey->survey_roll_out_time : '');
            $surveyFrequency  = isset($payload['survey_frequency']) ? $payload['survey_frequency'] : (isset($company->id) ? $company->survey->survey_frequency : '');

            $surveyDay  = Carbon::parse($nextSurveyAfter)->modify($surveyRollOutDay)->format('M d,Y');
            $surveyTime = Carbon::parse($rollOutTime)->format('H:i');

            $upcomingRollOutDay  = $surveyDay . ", " . $surveyTime;
            $upcomingRolloutTime = date('Y-m-d H:i:s', strtotime($upcomingRollOutDay));

            if (isset($lastSurveyExpireDay) && $lastSurveyExpireDay != null) {
                $expireTime = date('Y-m-d H:i:s', strtotime($lastSurveyExpireDay));
                if (strtotime($expireTime) > strtotime($upcomingRolloutTime)) {
                    $surveyDay = Carbon::parse($nextSurveyAfter)->modify("next " . $surveyRollOutDay)->format('M d,Y');
                }
                $upcomingRollOutDay = $surveyDay . ", " . $surveyTime;
            }

            $upcomingExpireDay = Carbon::parse($nextSurveyAfter)
                ->modify($surveyRollOutDay)
                ->addDays(config('zevolifesettings.survey_frequency_day.' . $surveyFrequency))
                ->format('M d,Y');
            $upcomingExpireDay = $upcomingExpireDay . ", " . $surveyTime;

            if (isset($company->id) && $company->subscription_end_date < Carbon::now()) {
                $upcomingRollOutDay = $upcomingExpireDay = null;
            }

            return [
                'lastSurveyRollOutDay' => isset($lastSurveyRollOutDay) ? $lastSurveyRollOutDay : null,
                'lastSurveyExpiredDay' => isset($lastSurveyExpireDay) ? $lastSurveyExpireDay : null,
                'upcomingRollOutDay'   => isset($upcomingRollOutDay) ? $upcomingRollOutDay : null,
                'upcomingExpiredDay'   => isset($upcomingExpireDay) ? $upcomingExpireDay : null,
            ];
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
     * Fetch the services for wellbeing specialist
     * @param Request $request, Company $company
     * @return Array
     */
    public function getStaffServices(Request $request, Company $company)
    {
        if (!access()->allow('create-company') && !access()->allow('update-company')) {
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong_try_again'),
                'status' => 0,
            ];
            return response($messageData, 500)->header('Content-Type', 'application/json');
        }
        try {
            if ($request['fromPage'] == 'edit') {
                $wellBeingSpIds   = isset($request->wsIds) ? $request->wsIds : '';
                $servicesDataTest = DigitalTherapyService::select(
                    'digital_therapy_services.id as id',
                    'digital_therapy_services.ws_id as user_id',
                    \DB::raw("CONCAT(users.first_name, ' ', users.last_name) AS staffName"),
                    'services.id as service_id',
                    'services.name as services'
                )
                    ->leftJoin('services', 'services.id', '=', 'digital_therapy_services.service_id')
                    ->leftJoin('users', 'users.id', '=', 'digital_therapy_services.ws_id')
                    ->whereIn('digital_therapy_services.ws_id', explode(',', $wellBeingSpIds))
                    ->where('digital_therapy_services.company_id', $company->id)
                    ->get()->toArray();

                $staffArr = [];
                foreach ($servicesDataTest as $value) {
                    $ws_id                                             = $value['user_id'];
                    $service_id                                        = $value['service_id'];
                    $staffArr[$ws_id]['staffName']                     = $value['staffName'];
                    $staffArr[$ws_id]['services'][$service_id]['id']   = $value['id'];
                    $staffArr[$ws_id]['services'][$service_id]['name'] = $value['services'];
                }
            } else {
                $wellBeingSpIds = isset($company->digitalTherapy) ? $company->digitalTherapy->dt_wellbeing_sp_ids : (!empty($request->value) ? $request->value : '');
                if (!empty($wellBeingSpIds)) {
                    $servicesDataTest = Service::select(
                        'users.id as user_id',
                        \DB::raw("CONCAT(users.first_name, ' ', users.last_name) AS staffName"),
                        'services.id as service_id',
                        'services.name as services'
                    )
                        ->leftJoin('service_sub_categories', 'service_sub_categories.service_id', '=', 'services.id')
                        ->leftJoin('users_services', 'users_services.service_id', '=', 'service_sub_categories.id')
                        ->leftJoin('users', 'users.id', '=', 'users_services.user_id')
                        ->whereIn('users_services.user_id', explode(',', $wellBeingSpIds))
                        ->distinct('user_id')->distinct()->get()->toArray();
                }
                $staffArr = [];
                foreach ($servicesDataTest as $value) {
                    $ws_id                                     = $value['user_id'];
                    $service_id                                = $value['service_id'];
                    $staffArr[$ws_id]['staffName']             = $value['staffName'];
                    $staffArr[$ws_id]['services'][$service_id] = $value['services'];
                }
            }
            return [
                'staffServices' => isset($staffArr) ? $staffArr : null,
            ];
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
     * Save the location general slots to temperory table
     * @param Request $request
     * @return JsonResponse
     */
    public function saveLocationWiseSlotsTemp(Request $request)
    {
        if (!access()->allow('update-company')) {
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong_try_again'),
                'status' => 0,
            ];
            return response($messageData, 500)->header('Content-Type', 'application/json');
        }
        try {
            $getData = $request->all();
            $data    = $this->model->saveLocationSlotsTemp($getData);
            return response()->json(array('success' => true, 'last_insert_id' => $data), 200);
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
     * Delete the temperory slots
     * @param Request $request
     * @param integer $tempId
     * @return JsonResponse
     */
    public function deletetempSlots(Request $request, $tempId)
    {
        if (!access()->allow('update-company')) {
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong_try_again'),
                'status' => 0,
            ];
            return response($messageData, 500)->header('Content-Type', 'application/json');
        }
        try {
            $deleted = TempDigitalTherapySlots::where('id', $tempId)->delete();
            if ($deleted) {
                $messageData = ['data' => 'Record deleted', 'status' => 1];
            } else {
                $messageData = ['data' => 'Error in delete record', 'status' => 0];
            }
            return response($messageData, 200)->header('Content-Type', 'application/json');
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
     * Get Specific Slots
     * @param company $company_id
     * @param user $user_id
     *
     * @return string
     */
    public function getSpecificSlots(Request $request)
    {
        if (!access()->allow('create-company')) {
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong_try_again'),
                'status' => 0,
            ];
            return response($messageData, 500)->header('Content-Type', 'application/json');
        }
        try {
            $dtSpecificResult = DigitalTherapySpecific::select(
                DB::raw("CONCAT(users.first_name,' ',users.last_name) AS wellbeing_specialist"),
                'digital_therapy_specific.date',
                'digital_therapy_specific.start_time',
                'digital_therapy_specific.end_time'
            )->leftJoin('users', function ($join) {
                $join->on('users.id', '=', 'digital_therapy_specific.ws_id');
            })
                ->where('digital_therapy_specific.company_id', $request->company_id);
            if (!is_null($request->location_id)) {
                $dtSpecificResult->where('location_id', $request->location_id);
            } else {
                $dtSpecificResult->whereNull('digital_therapy_specific.location_id')
                    ->where('digital_therapy_specific.ws_id', $request->ws_id);
            }
            $dtSpecificResult = $dtSpecificResult
                ->whereNull('users.deleted_at')
                ->orderBy('digital_therapy_specific.date', 'DESC')
                ->get()
                ->toArray();

            if (!empty($dtSpecificResult)) {
                $htmlText = '';
                foreach ($dtSpecificResult as $value) {
                    $htmlText .= '<tr><td>';
                    $htmlText .= date('M d, Y', strtotime($value['date']));
                    $htmlText .= '</td><td>';
                    $htmlText .= $value['wellbeing_specialist'];
                    $htmlText .= '</td><td>';
                    $htmlText .= date('h:i A', strtotime($value['start_time'])) . ' - ' . date('h:i A', strtotime($value['end_time']));
                    $htmlText .= '</td></tr>';
                }
                $messageData = [
                    'data'   => $htmlText,
                    'status' => 1,
                ];
            } else {
                $messageData = [
                    'data'   => 'No any record found!!!',
                    'status' => 0,
                ];
            }
            return response($messageData, 200)->header('Content-Type', 'application/json');
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
     * Display html view for credits
     * @param Company $company, Request $request
     * @return View
     */
    public function manageCredits($companyType, Company $company, Request $request)
    {
        $user       = auth()->user();
        $timezone   = (!empty($user->timezone) ? $user->timezone : config('app.timezone'));
        if (!access()->allow('manage-credits')) {
            abort(403);
        }
        try {
            $data               = [
                'company'     => $company,
                'ga_title'    => trans('page_title.companies.manageCredits'),
                'companyType' => $companyType,
                'loginemail'  => ($user->email ?? ""),
                'timezone'    => $timezone,
                'date_format' => config('zevolifesettings.date_format.moment_default_datetime'),
                'pagination'  => config('zevolifesettings.datatable.pagination.long'),
            ];
            return \view('admin.companies.managecredits', $data);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('admin.companies.index', $companyType)->with('message', $messageData);
        }
    }

    /**
     * Store the credits in database
     * @param $companyType, Company $company, StoreCreditRequest $request
     * @return RedirectResponse
     */
    public function storeCredits($companyType, Company $company, StoreCreditRequest $request)
    {
        $user   = auth()->user();
        if (!access()->allow('manage-credits')) {
            abort(403);
        }
        try {
            \DB::beginTransaction();
            $userLogData = [
                'user_id'   => $user->id,
                'user_name' => $user->full_name,
            ];
            $data = $this->companyWiseCredit->storeEntity($request->all());

            $logData = array_merge($userLogData, $request->all());
            $this->auditLogRepository->created("Credits added successfully", $logData);

            if ($data) {
                \DB::commit();
                $messageData = [
                    'data'   => trans('company.manage_credits.messages.credit_update_success'),
                    'status' => 1,
                ];
            } else {
                \DB::rollback();
                $messageData = [
                    'data'   => trans('labels.common_title.something_wrong_try_again'),
                    'status' => 0,
                ];
            }
            return \Redirect::route('admin.companies.manageCredits', [$companyType, $company->id])->with('message', $messageData);
        } catch (\Exception $exception) {
            \DB::rollback();
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('admin.companies.index', $companyType)->with('message', $messageData);
        }
    }

     /**
     * Digital therapy view page
     * 
     * @param $companyType, Company $company, Request $request
     * 
     * @return View
     */
    public function digitalTherapyBanners($companyType, Company $company, Request $request)
    {
        if (!access()->allow('manage-dt-banners')) {
            return \view('errors.401');
        }
        try{
            $user                 = auth()->user();
            $role                 = getUserRole($user);
            $data = [
                'pagination'   => config('zevolifesettings.datatable.pagination.short'),
                'role'         => $role,
                'company'      => $company,
                'ga_title'     => trans('page_title.companies.banners').' of '.$company->name,
                'companyType'  => $companyType,
            ];
            return \view('admin.companies.dt-banners.index', $data);
        } catch (\Exception $exception) {
            report($exception);
            abort(500);
        }
    }

    /**
     * Get the table data for digital therapy company banners
     * 
     * @param Request $request
     *
     * @return jsonResponse
     */
     public function getDigitalTherapyBanners(Request $request)
     {
        if (!access()->allow('manage-dt-banners')) {
            return response()->json([
                'message' => trans('labels.common_title.unauthorized_access'),
            ], 422);
        }
        try {
            return $this->companyDigitalTherapyBanner->getTableData($request->all());
        } catch (\Exception $exception) {
            report($exception);
            return response()->json([
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ], 422);
        }
    }

    /**
     * Create banner view page
     *
     * @param $companyType, Company $company
     *
     * @return view
     */
    public function createBanner($companyType, Company $company){
        if (!access()->allow('add-dt-banners')) {
            abort(403);
        }
        Breadcrumbs::for ('companies.dt-banners.create', function ($trail) use ( $companyType, $company ){
            $trail->push('Home', route('dashboard'));
            $trail->push('Companies', route('admin.companies.index', $companyType));
            $trail->push('DT Banners', route('admin.companies.digitalTherapyBanners', [$companyType, $company]));
            $trail->push('Add Banner');
        });
        try {
            $count              = $this->companyDigitalTherapyBanner->where('company_id', $company->id)->count();
            $maxBannnersLimit   = config('zevolifesettings.company_dt_banners_max_limit');
            if ($count >= (int) $maxBannnersLimit) {
                $messageData = [
                    'data'   => trans('company.dt_banners.validation.banner_validation_max_limit', [
                        'limit' => $maxBannnersLimit,
                    ]),
                    'status' => 0,
                ];
                return \Redirect::route('admin.companies.digitalTherapyBanners', [$companyType, $company->id])->with('message', $messageData);
            }
            $data['company']     = $company;
            $data['companyType'] = $companyType;
            $data['ga_title']    = trans('page_title.companies.createBanner');

            return \view('admin.companies.dt-banners.create', $data);
        } catch (\Exception $exception) {
            \DB::rollback();
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('admin.companies.digitalTherapyBanners', [$companyType, $company->id])->with('message', $messageData);
        }
    }

    /**
     * Store banner
     *
     * @param $companyType, Company $company, CreateDTBannerRequest $request
     *
     * @return redirectResponse
     */
    public function storeBanner($companyType, Company $company, CreateDTBannerRequest $request){
        $user   = auth()->user();
        if (!access()->allow('add-dt-banners')) {
            abort(403);
        }
        try {
            \DB::beginTransaction();
            $userLogData = [
                'user_id'       => $user->id,
                'user_name'     => $user->full_name,
                'company_id'    => $company->id,
                'company_name'  => $company->name,
            ];
            $data = $this->companyDigitalTherapyBanner->storeEntity($request->all(), $company);

            $logData = array_merge($userLogData, $request->all());
            $this->auditLogRepository->created("Banner added successfully", $logData);

            if ($data) {
                \DB::commit();
                $messageData = [
                    'data'   => trans('company.dt_banners.messages.banner_added'),
                    'status' => 1,
                ];
            } else {
                \DB::rollback();
                $messageData = [
                    'data'   => trans('labels.common_title.something_wrong_try_again'),
                    'status' => 0,
                ];
            }
            return \Redirect::route('admin.companies.digitalTherapyBanners', [$companyType, $company->id])->with('message', $messageData);
        } catch (\Exception $exception) {
            \DB::rollback();
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('admin.companies.digitalTherapyBanners', [$companyType, $company->id])->with('message', $messageData);
        }
    }

    /**
     * Edit banner
     *
     * @param $companyType, Company $company, CompanyDigitalTherapyBanner $companyDigitalTherapyBanner
     *
     * @return View
     */
    public function editBanner($companyType, Company $company, CompanyDigitalTherapyBanner $companyDigitalTherapyBanner){
        if (!access()->allow('edit-dt-banners')) {
            abort(403);
        }
        Breadcrumbs::for ('companies.dt-banners.edit', function ($trail) use ($companyType, $company) {
            $trail->push('Home', route('dashboard'));
            $trail->push('Companies', route('admin.companies.index', $companyType));
            $trail->push('DT Banners', route('admin.companies.digitalTherapyBanners', [$companyType, $company->id]));
            $trail->push('Edit Banner');
        });
        try{
            $data['company']        = $company;
            $data['record']         = $companyDigitalTherapyBanner;
            $data['companyType']    = $companyType;
            $data['ga_title']       = trans('page_title.companies.editBanner');

            return \view('admin.companies.dt-banners.edit', $data);
        } catch (\Exception $exception) {
            \DB::rollback();
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('admin.companies.digitalTherapyBanners', [$companyType, $company->id])->with('message', $messageData);
        }
    }

    /**
     * Update banner
     *
     * @param $companyType, Company $company, CompanyDigitalTherapyBanner $companyDigitalTherapyBanner, EditDTBannerRequest $request
     *
     * @return RedirectResponse
     */
    public function updateBanner($companyType, CompanyDigitalTherapyBanner $companyDigitalTherapyBanner, EditDTBannerRequest $request)
    {
        $user   = auth()->user();
        if (!access()->allow('edit-dt-banners')) {
            abort(403);
        }
        try {
            \DB::beginTransaction();

            $userLogData = [
                'user_id'   => $user->id,
                'user_name' => $user->full_name,
            ];
            $oldUsersData       = array_merge($userLogData, $companyDigitalTherapyBanner->toArray());
            $data = $companyDigitalTherapyBanner->updateEntity($request->all());
            
            $updatedUsersData   = array_merge($userLogData, $request->all());
            $finalLogs          = ['olddata' => $oldUsersData, 'newdata' => $updatedUsersData];
            $this->auditLogRepository->created("Banner updated successfully", $finalLogs);

            if ($data) {
                \DB::commit();
                $messageData = [
                    'data'   =>  trans('company.dt_banners.messages.banner_updated'),
                    'status' => 1,
                ];
            } else {
                \DB::rollback();
                $messageData = [
                    'data'   => trans('labels.common_title.something_wrong_try_again'),
                    'status' => 0,
                ];
            }
            return \Redirect::route('admin.companies.digitalTherapyBanners', [$companyType, $companyDigitalTherapyBanner->company_id])->with('message', $messageData);
        } catch (\Exception $exception) {
            \DB::rollback();
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('admin.companies.digitalTherapyBanners', [$companyType, $companyDigitalTherapyBanner->company_id])->with('message', $messageData);
        }
    }

    /**
     * @param  CompanyDigitalTherapyBanner $companyDigitalTherapyBanner
     *
     * @return RedirectResponse
     */
    public function deleteBanner(CompanyDigitalTherapyBanner $companyDigitalTherapyBanner)
    {
        $user   = auth()->user();
        if (!access()->allow('delete-dt-banners')) {
            abort(403);
        }
        try {
            $userLogData = [
                'user_id'       => $user->id,
                'user_name'     => $user->full_name,
                'company_id'    => $companyDigitalTherapyBanner->company->name,
                'company_name'  => $companyDigitalTherapyBanner->id
            ];
            $logs  = array_merge($userLogData, ['deleted_banner_id' => $companyDigitalTherapyBanner->id,'deleted_Banner_description' => $companyDigitalTherapyBanner->description]);
            $this->auditLogRepository->created("Banner deleted successfully", $logs);

            return $companyDigitalTherapyBanner->deleteRecord();
        } catch (\Exception $exception) {
             report($exception);
             return [
                 'data'   => trans('labels.common_title.something_wrong'),
                 'status' => 0,
             ];
        }
    }

     /**
      * Function to update the order of banner by drag
     * @param  Request $request
     * @param  Company $company
     * @return RedirectResponse
     */
    public function reorderingScreen(Request $request, Company $company)
    {
        try {
            \DB::beginTransaction();
            $data = [
                'status'  => false,
                'message' => '',
            ];
            $positions = $request->input('positions', []);
            if (!empty($positions)) {
                $updated = $this->companyDigitalTherapyBanner->reorderingBanner($positions, $company);

                if ($updated) {
                    $data['status']  = true;
                    $data['message'] = trans('company.dt_banners.messages.order_update_success');
                } else {
                    $data['message'] = trans('company.dt_banners.messages.failed_update_order');
                }
            } else {
                $data['message'] = trans('company.dt_banners.messages.nothing_change_order');
            }

            (($data['status']) ? \DB::commit() : \DB::rollback());
            return $data;
        } catch (\Exception $exception) {
            \DB::rollback();
            report($exception);
            return [
                'status'  => false,
                'message' => trans('labels.common_title.something_wrong_try_again'),
            ];
        }
    }

    /**
     * @param Request $request
     *
     * @return View
     */

     public function getCreditHistory(Request $request)
     {
         if (!access()->allow('manage-credits')) {
             abort(403);
         }
         try {
             return $this->companyWiseCredit->getTableData($request->all());
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
     * @param Request $request
     * @return Array
     * @throws Exception
     */
    public function exportCreditHistory(Request $request)
    {
        if (!access()->allow('manage-credits')) {
            $messageData = [
                'data'   => trans('labels.common_title.unauthorized_access'),
                'status' => 0,
            ];
            return response()->json($messageData, 401);
        }
        try {
            \DB::beginTransaction();
            $data = $this->companyWiseCredit->exportCreditHistory($request->all());
            if ($data) {
                \DB::commit();
                $messageData = [
                    'data'   => trans('contentreport.message.report_generate_in_background'),
                    'status' => 1,
                ];
            } else {
                \DB::rollback();
                $messageData = [
                    'data'   => trans('contentreport.message.no_records_found'),
                    'status' => 0,
                ];
            }
            return $messageData;
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('contentreport.message.something_wrong_try_again'),
                'status' => 0,
            ];
            return response()->json($messageData, 500);
        }
    }
}
