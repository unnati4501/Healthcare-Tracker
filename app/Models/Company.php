<?php

namespace App\Models;

use App\Events\SendZCUserSurveyEvent;
use App\Events\UserRegisterEvent;
use App\Jobs\SendGeneralPushNotification;
use App\Jobs\SendTeamChangePushNotificationJob;
use App\Jobs\ZcSurveyReportExportJob;
use App\Jobs\CompanyArchivedJob;
use App\Models\Calendly;
use App\Models\Category;
use App\Models\Challenge;
use App\Models\ChallengeParticipant;
use App\Models\CompanyRoles;
use App\Models\CompanyWiseAppSetting;
use App\Models\CompanyWisePointsLimit;
use App\Models\CompanyWisePointsSettings;
use App\Models\DigitalTherapyService;
use App\Models\EAP;
use App\Models\EventBookingLogs;
use App\Models\FileImport;
use App\Models\McSurveyReportExportLogs;
use App\Models\Notification;
use App\Models\Role;
use App\Models\TempDigitalTherapySlots;
use App\Models\User;
use App\Models\UserTeam;
use App\Models\ZcSurvey;
use App\Models\ZcSurveyConfig;
use App\Models\ZcSurveyLog;
use App\Models\ZcSurveyResponse;
use App\Models\CompanyLocation;
use App\Events\CompanyArchivedEvent;
use App\Models\CronofySchedule;
use App\Models\WsUser;
use App\Models\ScheduleUsers;
use App\Models\CompanyDigitalTherapy;
use App\Observers\CompanyObserver;
use Carbon\Carbon;
use DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Schema;
use Laravel\Telescope\Telescope;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Yajra\DataTables\Facades\DataTables;
use \App\Models\ChallengeTarget;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use App\Jobs\SpContentAssignFromCompanyJob;
use App\Jobs\SpContentAssignToCompanyJob;
use App\Jobs\SpContentAssignFromTeamJob;
use App\Jobs\SpContentAssignToTeamJob;
use Illuminate\Bus\Batch;
use Illuminate\Support\Facades\Bus;

class Company extends Model implements HasMedia
{

    use InteractsWithMedia;
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'companies';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = [
        'industry_id',
        'code',
        'name',
        'description',
        'size',
        'subscription_start_date',
        'subscription_end_date',
        'has_domain',
        'status',
        'is_intercom',
        'is_faqs',
        'is_eap',
        'eap_tab',
        'hide_content',
        'is_branding',
        'enable_survey',
        'group_restriction',
        'parent_id',
        'is_reseller',
        'portal',
        'allow_app',
        'allow_portal',
        'auto_team_creation',
        'team_limit',
        'zcsurvey_on_email',
        'enable_event',
        'plan_status',
        'disable_sso',
        'credits',
        'on_hold_credits'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = ['status' => 'boolean', 'has_domain' => 'boolean', 'is_intercom' => 'boolean', 'is_faqs' => 'boolean', 'is_eap' => 'boolean', 'is_branding' => 'boolean', 'enable_survey' => 'boolean', 'is_reseller' => 'boolean', 'allow_app' => 'boolean', 'allow_portal' => 'boolean', 'auto_team_creation' => 'boolean', 'team_limit' => 'integer', 'zcsurvey_on_email' => 'boolean', 'enable_event' => 'boolean', 'exclude_gender_and_dob' => 'boolean', 'manage_the_design_change' => 'boolean'];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['subscription_start_date', 'subscription_end_date'];

    /**
     * Boot model
     */
    protected static function boot()
    {
        parent::boot();
        static::observe(CompanyObserver::class);
    }

    /**
     * @return string
     * @throws \RuntimeException
     */
    public function createUniqueCode(): string
    {
        do {
            $code = substr(number_format(time() * rand(), 0, '', ''), 0, 6);
        } while ((new static )->where('code', '=', $code)->count());

        return $code;
    }

    /**
     * @return BelongsTo
     */
    public function industry(): BelongsTo
    {
        return $this->belongsTo('App\Models\Industry');
    }

    /**
     * @return HasMany
     */
    public function domains(): HasMany
    {
        return $this->hasMany('App\Models\Domain');
    }

    /**
     * @return HasMany
     */
    public function departments(): HasMany
    {
        return $this->hasMany('App\Models\Department');
    }

    /**
     * @return HasMany
     */
    public function teams(): HasMany
    {
        return $this->hasMany('App\Models\Team');
    }

    /**
     * @return HasMany
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * @return BelongsToMany
     */
    public function members(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\User', 'user_team', 'company_id', 'user_id')
            ->withPivot('team_id', 'department_id')
            ->withTimestamps();
    }

    /**
     * With Company plan attech records
     * @return BelongsToMany
     */
    public function companyplan(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\CpPlan', 'cp_company_plans', 'company_id', 'plan_id');
    }

    /**
     * @return HasMany
     */
    public function locations(): HasMany
    {
        return $this->hasMany('App\Models\CompanyLocation');
    }

    /**
     * @return HasMany
     */
    public function limits(): HasMany
    {
        return $this->hasMany('App\Models\ChallengeSetting');
    }

    /**
     * @return BelongsToMany
     */
    public function moderators(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'company_moderator',
            'company_id',
            'user_id'
        )->withTimestamps();
    }

    /**
     * @return HasMany
     */
    public function companyWiseChallengeSett(): HasMany
    {
        return $this->hasMany('App\Models\ChallengeSetting');
    }

    /**
     * @return HasMany
     */
    public function branding(): HasOne
    {
        return $this->hasOne('App\Models\CompanyBranding', 'company_id', 'id');
    }

    /**
     * @return HasMany
     */
    public function brandingContactDetails(): HasOne
    {
        return $this->hasOne('App\Models\CompanyBrandingContactDetails', 'company_id', 'id');
    }

    /**
     * @return HasMany
     */
    public function digitalTherapy(): HasOne
    {
        return $this->hasOne('App\Models\CompanyDigitalTherapy', 'company_id', 'id');
    }

    /**
     * @return HasMany
     */
    public function survey(): HasOne
    {
        return $this->hasOne('App\Models\ZcSurveySettings', 'company_id', 'id');
    }

    /**
     * "Belongs To Many" relation to `zc_survey_configs` table
     * via `company_id` field.
     *
     * @return BelongsToMany
     */
    public function surveyUsers(): belongsToMany
    {
        return $this->belongsToMany(User::class, ZcSurveyConfig::class, 'company_id', 'user_id')
            ->withTimestamps();
    }

    /**
     * "Belongs To Many" relation to `company_roles` table
     * via `company_id` field.
     *
     * @return BelongsToMany
     */
    public function resellerRoles(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\Role', 'company_roles', 'company_id', 'role_id')
            ->withPivot('company_id', 'role_id')
            ->withTimestamps();
    }

    /**
     * "belongsTo" relation to `companies` table
     * via `parent_id` field.
     *
     * @return BelongsTo
     */
    public function parentCompany(): BelongsTo
    {
        return $this->belongsTo('App\Models\Company', 'parent_id');
    }

    /**
     * "has many" relation to `companies` table
     * via `parent_id` field.
     *
     * @return HasMany
     */
    public function childCompanies(): HasMany
    {
        return $this->hasMany('App\Models\Company', 'parent_id');
    }

    /**
     * "has many" relation to `company_wise_label_string` table
     * via `company_id` field.
     *
     * @return HasMany
     */
    public function companyWiseLabelString(): HasMany
    {
        return $this->hasMany('App\Models\CompanyWiseLabelString', 'company_id');
    }

    /**
     * @return HasMany
     */
    public function companyWiseCredit(): HasMany
    {
        return $this->hasMany('App\Models\CompanyWiseCredit');
    }

    /**
     * "Belongs To Many" relation to `event_companies` table
     * via `company_id` field.
     *
     * @return BelongsToMany
     */
    public function events(): BelongsToMany
    {
        return $this
            ->belongsToMany('App\Models\Event', 'event_companies', 'company_id', 'event_id')
            ->withPivot('status')
            ->withTimestamps();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function eap()
    {
        return $this->belongsTo('App\Models\EAP', 'eap_id');
    }

    /**
     * "HasMany" relation to `file_imports` table
     * via `company_id` field.
     *
     * @return HasMany
     */
    public function fileImport(): HasMany
    {
        return $this->hasMany(FileImport::class);
    }

    /**
     * "HasMany" relation to `zc_survey_log` table
     * via `company_id` field.
     *
     * @return HasMany
     */
    public function companySurveyLog(): HasMany
    {
        return $this->hasMany(ZcSurveyLog::class);
    }

    /**
     * "HasMany" relation to `zc_survey_log` table
     * via `company_id` field.
     *
     * @return HasMany
     */
    public function companyMcSurveyLog(): HasMany
    {
        return $this->hasMany(CourseSurveyQuestionAnswers::class);
    }

    /**
     * "HasMany" relation to `company_wise_app_settings` table
     * via `company_id` field.
     *
     * @return HasMany
     */
    public function companyWiseAppSetting(): HasMany
    {
        return $this->hasMany(CompanyWiseAppSetting::class);
    }

    /**
     * "HasMany" relation to `zc_survey_report_export_logs` table
     * via `company_id` field.
     *
     * @return HasMany
     */
    public function surveyExportLogs(): HasMany
    {
        return $this->hasMany(ZcSurveyReportExportLogs::class);
    }

    /**
     * "HasMany" relation to `mc_survey_report_export_logs` table
     * via `company_id` field.
     *
     * @return HasMany
     */
    public function mcSurveyReportExportLogs(): HasMany
    {
        return $this->hasMany(McSurveyReportExportLogs::class);
    }

    /**
     * "HasMany" relation to `company_wise_points_settings` table
     * via `company_id` field.
     *
     * @return HasMany
     */
    public function companyWisePointsSetting(): HasMany
    {
        return $this->hasMany(CompanyWisePointsSettings::class);
    }

    /**
     * "HasMany" relation to `company_wise_points_limits` table
     * via `company_id` field.
     *
     * @return HasMany
     */
    public function companyWisePointsDailyLimit(): HasMany
    {
        return $this->hasMany(CompanyWisePointsLimit::class);
    }

    /**
     * "HasMany" relation to `challenges` table
     * via `company_id` field.
     *
     * @return HasMany
     */
    public function challenge(): HasMany
    {
        return $this->hasMany(Challenge::class, 'company_id');
    }

    /**
     * "BelongsToMany" relation to `challenges` table
     * via `company_id` field.
     *
     * @return HasMany
     */
    public function icChallenge(): BelongsToMany
    {
        return $this
            ->belongsToMany(Challenge::class, ChallengeParticipant::class, 'company_id', 'challenge_id')
            ->where('challenges.challenge_type', 'inter_company');
    }

    /**
     * @return string
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversion
     */
    public function getBrandingLogoAttribute()
    {
        return $this->getBrandingLogo(['w' => 250, 'h' => 50], false);
    }

    /**
     * @return string
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversion
     */
    public function getBrandingLoginBackgroundAttribute()
    {
        return $this->getBrandingLoginBackgroundLogo(['w' => 1920, 'h' => 1280], false);
    }

    /**
     * @return string
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversion
     */
    public function getBrandingLogoNameAttribute()
    {
        $media = $this->getFirstMedia('branding_logo');
        return ($media->name ?? null);
    }

    /**
     * @return string
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversion
     */
    public function getBrandingLoginBackgroundNameAttribute()
    {
        $media = $this->getFirstMedia('branding_login_background');
        return ($media->name ?? null);
    }

    /**
     * @return string
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversion
     */
    public function getLogoAttribute()
    {
        return $this->getLogo(['w' => 320, 'h' => 320]);
    }

    /**
     * @return string
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversion
     */
    public function getLogoNameAttribute()
    {
        return $this->getFirstMedia('logo')->name ?? '';
    }

    /**
     * @return string
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversion
     */
    public function getPortalLogoMainAttribute()
    {
        return $this->getPortalLogoMain(['w' => 320, 'h' => 320]);
    }

    /**
     * @return string
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversion
     */
    public function getPortalLogoMainNameAttribute()
    {
        $portalLogoMain = $this->getFirstMedia('portal_logo_main');
        return !empty($portalLogoMain) ? $portalLogoMain->name : '';
    }

    /**
     * @return string
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversion
     */
    public function getPortalLogoOptionalAttribute()
    {
        return $this->getPortalLogoOptional(['w' => 320, 'h' => 320]);
    }

    /**
     * @return string
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversion
     */
    public function getPortalLogoOptionalNameAttribute()
    {
        $portalLogoOptional = $this->getFirstMedia('portal_logo_optional');
        return !empty($portalLogoOptional) ? $portalLogoOptional->name : '';
    }

    /**
     * @return string
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversion
     */
    public function getPortalHomepageLogoLeftAttribute()
    {
        return $this->getPortalHomepageLogoLeft(['w' => 320, 'h' => 320]);
    }

    /**
     * @return string
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversion
     */
    public function getPortalHomePageLogoLeftNameAttribute()
    {
        $portalHomePageLogoLeft = $this->getFirstMedia('portal_homepage_logo_left');
        return !empty($portalHomePageLogoLeft) ? $portalHomePageLogoLeft->name : '';
    }

    /**
     * @return string
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversion
     */
    public function getPortalHomepageLogoRightAttribute()
    {
        return $this->getPortalHomepageLogoRight(['w' => 320, 'h' => 320]);
    }

    /**
     * @return string
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversion
     */
    public function getPortalHomePageLogoRightNameAttribute()
    {
        $portalHomepageLogoRight = $this->getFirstMedia('portal_homepage_logo_right');
        return !empty($portalHomepageLogoRight) ? $portalHomepageLogoRight->name : '';
    }

    /**
     * @return string
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversion
     */
    public function getPortalBackgroundImageAttribute()
    {
        return $this->getPortalBackgroundImage(['w' => 320, 'h' => 320]);
    }

    /**
     * @return string
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversion
     */
    public function getPortalBackgroundImageNameAttribute()
    {
        $portalBackgroundImage = $this->getFirstMedia('portal_background_image');
        return !empty($portalBackgroundImage) ? $portalBackgroundImage->name : '';
    }

    /**
     * @return string
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversion
     */
    public function getPortalFooterLogoAttribute()
    {
        return $this->getPortalFooterLogo(['w' => 180, 'h' => 60]);
    }

    /**
     * @return string
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversion
     */
    public function getPortalFooterLogoNameAttribute()
    {
        $portalFooterLogo = $this->getFirstMedia('portal_footer_logo');
        return !empty($portalFooterLogo) ? $portalFooterLogo->name : '';
    }

    /**
     * @return string
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversion
     */
    public function getEmailHeaderAttribute()
    {
        return $this->getEmailHeaderLogo(['w' => 600, 'h' => 157]);
    }

    /**
     * @return string
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversion
     */
    public function getEmailHeaderNameAttribute()
    {
        return $this->getFirstMedia('email_header')->name;
    }

    /**
     * One-to-Many relations with Meditation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function meditationcompany(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\Company', 'meditation_tracks_company', 'meditation_track_id', 'company_id');
    }

    /**
     * One-to-Many relations with Webinar.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function webinarcompany(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\Company', 'webinar_company', 'webinar_id', 'company_id');
    }

    /**
     * One-to-Many relations with Recipe.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function recipecompany(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\Company', 'recipe_company', 'recipe_id', 'company_id');
    }

    /**
     * One-to-Many relations with Feed.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function feedcompany(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\Company', 'feed_company', 'feed_id', 'company_id');
    }

    /**
     * One-to-Many relations with Masterclass.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function masterclassCompany(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\Company', 'masterclass_company', 'masterclass_id', 'company_id');
    }

    /**
     * One-to-Many relations with Podcast.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function podcastcompany(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\Company', 'podcast_company', 'podcast_id', 'company_id');
    }

    /**
     * @return hasMany
     */
    public function digitalTherapySlots(): hasMany
    {
        return $this->hasMany('App\Models\DigitalTherapySlots', 'company_id', 'id');
    }

    /**
     * @return hasMany
     */
    public function tempDigitalTherapySlots(): hasMany
    {
        return $this->hasMany('App\Models\TempDigitalTherapySlots', 'company_id', 'id');
    }

    /**
     * @return hasMany
     */
    public function digitalTherapySpecificSlots(): hasMany
    {
        return $this->hasMany('App\Models\DigitalTherapySpecific', 'company_id', 'id');
    }

    /**
     * @return hasMany
     */
    public function digitalTherapyService(): hasMany
    {
        return $this->hasMany('App\Models\DigitalTherapyService', 'company_id', 'id');
    }

    /**
     * "BelongsToMany" relation to `event_booking_logs` table
     * via `company_id` field.
     *
     * @return BelongsToMany
     */
    public function evnetBookings(): BelongsToMany
    {
        $table_fields = Schema::getColumnListing('event_booking_logs');
        return $this->belongsToMany(Event::class, EventBookingLogs::class, 'company_id', 'event_id')
            ->withPivot($table_fields)
            ->withTimestamps();
    }

    /**
     * @param string $params
     *
     * @return string
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversion
     */
    public function getLogo(array $params): string
    {
        $media = $this->getFirstMedia('logo');
        if (!is_null($media) && $media->count() > 0) {
            $params['src'] = $this->getFirstMediaUrl('logo');
        }
        return getThumbURL($params, 'company', 'logo');
    }

    /**
     * @param string $params
     * @param bool $fallback
     *
     * @return string
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversion
     */
    public function getBrandingLogo(array $params, bool $fallback = true): string
    {
        $media = $this->getFirstMedia('branding_logo');
        if (!is_null($media) && $media->count() > 0) {
            $params['src'] = $this->getFirstMediaUrl('branding_logo');
        }
        if (empty($params['src']) && !$fallback) {
            return '';
        }
        return getThumbURL($params, 'company', 'branding_logo');
    }

    /**
     * @param string $params
     * @param bool $fallback
     *
     * @return string
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversion
     */
    public function getBrandingLoginBackgroundLogo(array $params, bool $fallback = true): string
    {
        $media = $this->getFirstMedia('branding_login_background');
        if (!is_null($media) && $media->count() > 0) {
            $params['src'] = $this->getFirstMediaUrl('branding_login_background');
        }
        if (empty($params['src']) && !$fallback) {
            return '';
        }
        return getThumbURL($params, 'company', 'branding_login_background');
    }

    /**
     * @param string $params
     *
     * @return string
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversion
     */
    public function getEmailHeaderLogo(array $params): string
    {
        $media = $this->getFirstMedia('email_header');
        if (!is_null($media) && $media->count() > 0) {
            $params['src'] = $this->getFirstMediaUrl('email_header');
        }
        if (empty($params['src'])) {
            return '';
        }
        return getThumbURL($params, 'company', 'email_header');
    }

    /**
     * @param string $params
     *
     * @return string
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversion
     */
    public function getPortalLogoMain(array $params): string
    {
        $media = $this->getFirstMedia('portal_logo_main');
        if (!is_null($media) && $media->count() > 0) {
            $params['src'] = $this->getFirstMediaUrl('portal_logo_main');
        }
        return getThumbURL($params, 'company', 'portal_logo_main');
    }

    /**
     * @param string $params
     *
     * @return string
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversion
     */
    public function getPortalLogoOptional(array $params): string
    {
        $media = $this->getFirstMedia('portal_logo_optional');
        if (!is_null($media) && $media->count() > 0) {
            $params['src'] = $this->getFirstMediaUrl('portal_logo_optional');
        }
        return getThumbURL($params, 'company', 'portal_logo_optional');
    }

    /**
     * @param string $params
     *
     * @return string
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversion
     */
    public function getPortalHomepageLogoLeft(array $params): string
    {
        $media = $this->getFirstMedia('portal_homepage_logo_left');
        if (!is_null($media) && $media->count() > 0) {
            $params['src'] = $this->getFirstMediaUrl('portal_homepage_logo_left');
        }
        return getThumbURL($params, 'company', 'portal_homepage_logo_left');
    }

    /**
     * @param string $params
     *
     * @return string
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversion
     */
    public function getPortalHomepageLogoRight(array $params): string
    {
        $media = $this->getFirstMedia('portal_homepage_logo_right');
        if (!is_null($media) && $media->count() > 0) {
            $params['src'] = $this->getFirstMediaUrl('portal_homepage_logo_right');
        }
        return getThumbURL($params, 'company', 'portal_homepage_logo_right');
    }

    /**
     * @param string $params
     *
     * @return string
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversion
     */
    public function getPortalBackgroundImage(array $params): string
    {
        $media = $this->getFirstMedia('portal_background_image');
        if (!is_null($media) && $media->count() > 0) {
            $params['src'] = $this->getFirstMediaUrl('portal_background_image');
        }
        return getThumbURL($params, 'company', 'portal_background_image');
    }

    /**
     * @param string $params
     *
     * @return string
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversion
     */
    public function getPortalFooterLogo(array $params): string
    {
        $media = $this->getFirstMedia('portal_footer_logo');
        if (!is_null($media) && $media->count() > 0) {
            $params['src'] = $this->getFirstMediaUrl('portal_footer_logo');
        }
        return getThumbURL($params, 'company', 'portal_footer_logo');
    }

    /**
     * Set datatable for record list.
     *
     * @param payload
     * @return dataTable
     */
    public function getTableData($payload)
    {
        $user             = auth()->user();
        $appTimeZone      = config('app.timezone');
        $timezone         = (!empty($user->timezone) ? $user->timezone : $appTimeZone);
        $now              = now($timezone);
        $role             = getUserRole($user);
        $company          = $user->company()->select('companies.id', 'companies.parent_id')->first();
        $list             = $this->getRecordList($payload);
        $roleGroupCompany = $payload['companyType'];

        return DataTables::of($list)
            ->addColumn('logo', function ($record) {
                return $record->logo;
            })
            ->addColumn('is_reseller', function ($record) {
                if (!$record->is_reseller) {
                    return !is_null($record->parent_id) ? config('zevolifesettings.company_types.child') : config('zevolifesettings.company_types.zevo');
                } elseif ($record->is_reseller && is_null($record->parent_id)) {
                    return config('zevolifesettings.company_types.parent');
                } else {
                    return config('zevolifesettings.company_types.zevo');
                }
            })
            ->addColumn('has_domain', function ($record) {
                return (!empty($record->has_domain) && $record->has_domain == 1) ? 'Yes' : 'No';
            })
            ->addColumn('company_plan', function ($record) {
                $companyPlanName = $record->companyplan()->pluck('name')->first();
                if ($companyPlanName) {
                    return $companyPlanName;
                } else {
                    return 'N/A';
                }
            })
            ->addColumn('enable_survey', function ($record) {
                return (!empty($record->enable_survey) && $record->enable_survey == 1) ? 'Yes' : 'No';
            })
            ->addColumn('actions', function ($record) use ($user, $role, $company, $roleGroupCompany) {
                $companyType            = (($record->is_reseller) ? '(Reseller Parent)' : (!is_null($record->parent_id) ? '(Reseller Child)' : '(Zevo)'));
                $responseCount          = ZcSurveyResponse::select('id')->where('company_id', $record->id)->first();
                $zcsReportBtnVisibility = !is_null($responseCount);
                $mcResponseCount        = CourseSurveyQuestionAnswers::select('id')->where('company_id', $record->id)->first();
                $mcsReportBtnVisibility = !is_null($mcResponseCount);
                $companySlug            = Company::select('cp_features.slug')->leftJoin('cp_company_plans', 'companies.id', '=', 'cp_company_plans.company_id')
                ->join('cp_plan', 'cp_plan.id', '=', 'cp_company_plans.plan_id')
                ->join('cp_plan_features', 'cp_plan_features.plan_id', '=', 'cp_plan.id')
                ->join('cp_features', 'cp_features.id', '=', 'cp_plan_features.feature_id')
                ->where('companies.id', $record->id);
                $companySlug    = $companySlug->where(function ($q) {
                    $q->where('cp_features.slug', 'digital-therapy')
                        ->orWhere('cp_features.slug', 'eap');
                })->get()->first();

                return view('admin.companies.listaction', compact('record', 'companyType', 'zcsReportBtnVisibility', 'user', 'role', 'company', 'mcsReportBtnVisibility', 'roleGroupCompany', 'companySlug'))->render();
            })
            ->addColumn('start_date_diff', function ($record) use ($now) {
                return $now->diffInHours($record->subscription_start_date, false);
            })
            ->addColumn('diff', function ($record) use ($now) {
                $endDiff = $now->diffInHours($record->subscription_end_date, false);
                return $endDiff ?? 0;
            })
            ->rawColumns(['actions'])
            ->make(true);
    }

    /**
     * get record list for data table list.
     *
     * @param payload
     * @return recordList
     */

    public function getRecordList($payload)
    {
        $user = auth()->user();
        $role = getUserRole($user);
        \DB::connection()->enableQueryLog();

        $query = $this
            ->leftJoin('industries', 'companies.industry_id', '=', 'industries.id')
            ->leftJoin('cp_company_plans', 'companies.id', '=', 'cp_company_plans.company_id')
            ->select(
                'companies.id',
                'companies.name',
                'companies.code',
                'companies.updated_at',
                'companies.has_domain',
                'companies.is_reseller',
                'companies.parent_id',
                'companies.allow_app',
                'companies.enable_survey',
                'companies.subscription_start_date',
                'companies.subscription_end_date',
                DB::raw("industries.name as industry")
            )
            ->orderBy('updated_at', 'DESC');

        if ($payload['companyType'] == 'zevo') {
            $query
                ->whereNull('companies.parent_id')
                ->where('companies.is_reseller', false);
        } elseif ($payload['companyType'] == 'reseller') {
            $query->where(function ($q) {
                $q->whereNull('companies.parent_id')
                    ->where('companies.is_reseller', true)
                    ->orWhere(function ($q1) {
                        $q1->where('companies.is_reseller', false)
                            ->whereNotNull('companies.parent_id');
                    });
            });
        }

        if ($role->group == "reseller") {
            $companyId = $user->company()->first()->id;
            $query
                ->whereNotNull('companies.parent_id')
                ->where('companies.parent_id', $companyId);
        }

        if (in_array('recordName', array_keys($payload)) && !empty($payload['recordName'])) {
            $query->where('companies.name', 'like', '%' . $payload['recordName'] . '%');
        }

        if (in_array('recordCode', array_keys($payload)) && !empty($payload['recordCode'])) {
            $query->where('companies.code', 'like', '%' . $payload['recordCode'] . '%');
        }

        if (in_array('domain_verification', array_keys($payload)) && !empty($payload['domain_verification'])) {
            $query->where('companies.has_domain', (($payload['domain_verification'] == "true") ? true : false));
        }

        if (in_array('domain_verification', array_keys($payload)) && !empty($payload['domain_verification'])) {
            $query->where('companies.has_domain', (($payload['domain_verification'] == "true") ? true : false));
        }

        if (in_array('companyplans', array_keys($payload)) && !empty($payload['companyplans'])) {
            $query->where(function ($q) use ($payload) {
                $q->where('cp_company_plans.plan_id', $payload['companyplans']);
            });
        }

        if (isset($payload['survey']) && $payload['survey'] != "") {
            $query->where('companies.enable_survey', $payload['survey']);
        }

        if (in_array('reseller', array_keys($payload)) && !empty($payload['reseller'])) {
            if ($payload['reseller'] == 1) {
                // Parent comapanies
                $query
                    ->where('companies.is_reseller', true)
                    ->whereNull('companies.parent_id');
            } elseif ($payload['reseller'] == 2) {
                // Child comapanies
                $query
                    ->where('companies.is_reseller', false)
                    ->whereNotNull('companies.parent_id');
            } elseif ($payload['reseller'] == 3) {
                // Zevo comapanies
                $query
                    ->where('companies.is_reseller', false)
                    ->whereNull('companies.parent_id');
            }
        }

        return $query->get();
    }

    /**
     * store record data.
     *
     * @param payload
     * @return boolean
     */
    public function storeEntity($payload)
    {
        $user                 = auth()->user();
        $role                 = getUserRole($user);
        $is_reseller          = ((!empty($payload['is_reseller']) && $payload['is_reseller'] == 'yes') ? true : false);
        $parent_id            = ((!$is_reseller && $payload['parent_company'] != 'zevo') ? $payload['parent_company'] : null);
        $enable_survey        = ((!empty($payload['enable_survey']) && $payload['enable_survey'] == 'on') ? true : false);
        $disable_sso          = ((!empty($payload['disable_sso']) && $payload['disable_sso'] == 'on') ? true : false);
        $parentCompanyDetails = [];

        if ($is_reseller || $payload['parent_company'] != 'zevo') {
            $enableEvent = true;
        } else {
            $enableEvent = ((!empty($payload['enable_event']) && $payload['enable_event'] == 'on') ? true : false);
        }

        if (isset($payload['parent_company']) && !empty($payload['parent_company'])) {
            $parentCompanyDetails = self::find($payload['parent_company']);
        }

        $record = self::create([
            'name'                    => $payload['name'],
            'description'             => $payload['description'],
            'industry_id'             => $payload['industry'],
            'size'                    => $payload['size'],
            'subscription_start_date' => Carbon::parse($payload['subscription_start_date'])->format('Y-m-d 00:00:00'),
            'subscription_end_date'   => Carbon::parse($payload['subscription_end_date'])->format('Y-m-d 00:00:00'),
            'has_domain'              => (((!empty($payload['registration_restriction']) && $payload['registration_restriction'] == '1')) ? true : false),
            'status'                  => (Carbon::parse($payload['subscription_start_date'])->toDateTimeString() <= Carbon::today()->toDateTimeString()),
            'is_reseller'             => $is_reseller,
            'parent_id'               => $parent_id,
            'is_intercom'             => ((!empty($payload['is_intercom']) && $payload['is_intercom'] == 'on') ? true : false),
            'is_faqs'                 => ((!empty($payload['is_faqs']) && $payload['is_faqs'] == 'on') ? true : false),
            'is_eap'                  => ((!empty($payload['is_support']) && $payload['is_support'] == 'on') ? true : false),
            'eap_tab'                 => ((!empty($payload['eap_tab']) && $payload['eap_tab'] == 'on') ? true : false),
            'hide_content'            => ((!empty($payload['hidecontent']) && $payload['hidecontent'] == '1') ? true : ((!empty($payload['parent_company']) && !empty($parentCompanyDetails)) ? $parentCompanyDetails->hide_content : false)),
            'group_restriction'       => ((!empty($payload['group_restriction']) && $payload['group_restriction'] == 'on') ? $payload['group_restriction_rule'] : 0),
            'is_branding'             => ((!empty($payload['is_branding']) && $payload['is_branding'] == 'on') ? true : false),
            'enable_survey'           => $enable_survey,
            'disable_sso'             => $disable_sso,
            'allow_app'               => ((!$is_reseller && is_null($parent_id)) ? 1 : (!empty($payload['allow_app']) ? 1 : 0)),
            'allow_portal'            => ($is_reseller || (!$is_reseller && $payload['parent_company'] != 'zevo')),
            'zcsurvey_on_email'       => ((!empty($payload['zcsurvey_on_email']) && $payload['zcsurvey_on_email'] == 'on') ? true : false),
            'enable_event'            => $enableEvent,
        ]);

        // Add Default banners for zevo and parent/child companies
        if(!empty($payload['companyplanSlug']) && $payload['dtExistsHidden']){
            $this->addDefaultBanners($record->id, $payload['companyType']);
        }
        
        $companyPlan = ((!empty($payload['companyplan'])) ? $payload['companyplan'] : null);
        
        if (!empty($companyPlan)) {
            $record->companyplan()->sync($companyPlan);
        }
        // Store contact details for portal branding
        if ($payload['parent_company'] != 'zevo' && $role->slug == 'super_admin') {
            $brandingContactData = $record->brandingContactDetails()->create([
                'contact_us_header'        => ($payload['contact_us_header'] ?? ""),
                'contact_us_request'       => ($payload['contact_us_request'] ?? ""),
                'contact_us_description'   => ($payload['contact_us_description'] ? trim(str_replace(["\r\n", "&nbsp;", "&nbsp; "], "", htmlspecialchars_decode($payload['contact_us_description']))) : ""),
            ]);

            if (!$record->is_branding) {
                $record->branding()->create([
                    'appointment_title'         => $payload['appointment_title'],
                    'appointment_description'   => !empty($payload['appointment_description']) ? $payload['appointment_description'] : null,
                ]);

                if (!empty($payload['appointment_image'])) {
                    $name = $record->id . '_' . \time();
                    $record
                        ->clearMediaCollection('appointment_image')
                        ->addMediaFromRequest('appointment_image')
                        ->usingName($payload['appointment_image']->getClientOriginalName())
                        ->usingFileName($name . '.' . $payload['appointment_image']->getClientOriginalExtension())
                        ->toMediaCollection('appointment_image', config('medialibrary.disk_name'));
                }
            }

            if (!empty($payload['contact_us_image'])) {
                $name = $brandingContactData->id . '_' . \time();
                $brandingContactData
                    ->clearMediaCollection('contact_us_image')
                    ->addMediaFromRequest('contact_us_image')
                    ->usingName($payload['contact_us_image']->getClientOriginalName())
                    ->usingFileName($name . '.' . $payload['contact_us_image']->getClientOriginalExtension())
                    ->toMediaCollection('contact_us_image', config('medialibrary.disk_name'));
            }

            

            if (empty($payload['contact_us_image']) && $role->slug == 'super_admin' && (!$record->is_reseller && !is_null($record->parent_id))) {
                $companyBrandingContact = CompanyBrandingContactDetails::where('company_id', $record->parent_id)->first();
                if (!empty($companyBrandingContact->getFirstMediaUrl('contact_us_image'))) {
                    $media     = $companyBrandingContact->getFirstMedia('contact_us_image');
                    $brandingContactData->clearMediaCollection('contact_us_image')
                        ->addMediaFromUrl(
                            $companyBrandingContact->getFirstMediaUrl('contact_us_image'),
                        )
                        ->usingName($media->name)
                        ->usingFileName($media->file_name)
                        ->toMediaCollection('contact_us_image', config('medialibrary.disk_name'));
                }
            }

            if (empty($payload['appointment_image']) && $role->slug == 'super_admin' && (!$record->is_reseller && !is_null($record->parent_id))) {
                $parentCompanyData = Company::find($parent_id);
                if (!empty($parentCompanyData->getFirstMediaUrl('appointment_image'))) {
                    $media     = $parentCompanyData->getFirstMedia('appointment_image');
                    $record->clearMediaCollection('appointment_image')
                        ->addMediaFromUrl(
                            $parentCompanyData->getFirstMediaUrl('appointment_image'),
                        )
                        ->usingName($media->name)
                        ->usingFileName($media->file_name)
                        ->toMediaCollection('appointment_image', config('medialibrary.disk_name'));
                }
            }
        }

        if ((!empty($payload['companyplan']) && $payload['parent_company'] == 'zevo') || (!empty($payload['companyplanSlug']) && $payload['dtExistsHidden'] && $payload['companyType'] == 'reseller')) {
            if (!empty($payload['dt_servicemode'])) {
                $dtData['dt_is_online'] = (in_array("online", $payload['dt_servicemode']) ? true : false);
                $dtData['dt_is_onsite'] = (in_array("onsite", $payload['dt_servicemode']) ? true : false);
            }
            $dtData['dt_session_update']       = (!empty($payload['dt_session_update']) ? $payload['dt_session_update'] : 0);
            $dtData['dt_advanced_booking']     = (!empty($payload['dt_advanced_booking']) ? $payload['dt_advanced_booking'] : 0);
            $dtData['dt_future_booking']       = (!empty($payload['dt_future_booking']) ? $payload['dt_future_booking'] : 14);
            $dtData['dt_max_sessions_user']    = (!empty($payload['dt_max_sessions_user']) ? $payload['dt_max_sessions_user'] : 0);
            $dtData['dt_max_sessions_company'] = (!empty($payload['dt_max_sessions_company']) ? $payload['dt_max_sessions_company'] : 0);
            $dtData['emergency_contacts']      = ((!empty($payload['emergency_contacts']) && $payload['emergency_contacts'] == 'on') ? true : false);
            $dtData['consent']                 = ((!empty($payload['get_user_consent']) && $payload['get_user_consent'] == 'on') ? true : false);
            $dtData['set_hours_by']            = ($payload['set_hours_by'] ?? 1);
            $dtData['set_availability_by']     = ($payload['set_availability_by'] ?? 1);

            $record->digitalTherapy()->create($dtData);

            //Store the ws details and services in the table
            if (!empty($payload['service'])) {
                foreach ($payload['service'] as $key => $value) {
                    foreach ($value as $serviceId) {
                        $record->digitalTherapyService()->create(['ws_id' => $key, 'service_id' => $serviceId]);
                    }
                }
            }
            $setHoursBy        = $payload['set_hours_by'];
            $setAvailabilityBy = $payload['set_availability_by'];
            if ($setHoursBy == 1 && $setAvailabilityBy == 1) {
                // to set hc user day wise slots
                if (!empty($payload['slots'])) {
                    $dayWiseSlots = [];
                    foreach ($payload['slots'] as $day => $slots) {
                        foreach ($slots['start_time'] as $key => $time) {
                            $start_time  = Carbon::createFromFormat('H:i', $time, $payload['timezone']);
                            $end_time    = Carbon::createFromFormat('H:i', $slots['end_time'][$key], $payload['timezone']);
                            $wellbeingSP = "";
                            if (isset($payload['selected_ws'][$day]) && isset($payload['selected_ws'][$day][$key])) {
                                $wellbeingSP = implode(',', $payload['selected_ws'][$day][$key]);
                            }

                            $dayWiseSlots[] = [
                                'day'        => $day,
                                'start_time' => $start_time->format('H:i:00'),
                                'end_time'   => $end_time->format('H:i:59'),
                                'ws_id'      => $wellbeingSP,
                            ];
                        }
                    }
                    $record->digitalTherapySlots()->createMany($dayWiseSlots);
                }
            } elseif ($setHoursBy == 1 && $setAvailabilityBy == 2) {
                // Company - Specific
                if (!empty($payload['specific_slots'])) {
                    $this->digitalTherapySpecificSlots()->whereNull('location_id')->delete();
                    $specificWiseSlots = [];
                    foreach ($payload['specific_slots'] as $wsId => $slotDate) {
                        foreach ($slotDate as $timeStamp => $slots_specific) {
                            foreach ($slots_specific as $slots) {
                                foreach ($slots as $key => $slot) {
                                    $slot_array          = explode('-', $slot);
                                    $specificWiseSlots[] = [
                                        'ws_id'      => $wsId,
                                        'date'       => $timeStamp,
                                        'start_time' => $slot_array[0],
                                        'end_time'   => $slot_array[1],
                                    ];
                                }
                            }
                        }
                    }
                    $record->digitalTherapySpecificSlots()->createMany($specificWiseSlots);
                }
            }
        }

        if (isset($payload['logo']) && !empty($payload['logo'])) {
            $name = $record->id . '_' . \time();
            $record
                ->clearMediaCollection('logo')
                ->addMediaFromRequest('logo')
                ->usingName($payload['logo']->getClientOriginalName())
                ->usingFileName($name . '.' . $payload['logo']->getClientOriginalExtension())
                ->preservingOriginal()
                ->toMediaCollection('logo', config('medialibrary.disk_name'));
        }

        if ($payload['companyType'] != 'zevo' && $payload['companyType'] != 'reseller') {
            $parentCompanyData = Company::find($parent_id);
            if (!empty($parentCompanyData->getFirstMediaUrl('email_header'))) {
                $media     = $parentCompanyData->getFirstMedia('email_header');
                $imageData = explode(".", $media->file_name);
                $record->clearMediaCollection('email_header')
                    ->addMediaFromUrl(
                        $parentCompanyData->getFirstMediaUrl('email_header')
                    )
                    ->usingName($media->name)
                    ->usingFileName($media->file_name . '.' . $imageData[1])
                    ->toMediaCollection('email_header', config('medialibrary.disk_name'));
            }
        }
        // create default department for company
        $defaultDept = $record->departments()->create(['name' => 'Default', 'default' => true]);

        // create default team for company
        $defaultTeam = $defaultDept->teams()->create(['company_id' => $record->id, 'name' => 'Default', 'default' => true]);

        // set branding values if domain branding is set to true
        if ($record->is_branding === true) {
            $record->branding()->create([
                'onboarding_title'         => ($payload['onboarding_title'] ?? ""),
                'onboarding_description'   => (trim($payload['onboarding_description']) ?? ""),
                'sub_domain'               => $payload['sub_domain'],
                'portal_domain'            => (!empty($payload['portal_domain']) ? $payload['portal_domain'] : null),
                'portal_title'             => (!empty($payload['portal_title']) ? $payload['portal_title'] : null),
                'portal_theme'             => (!empty($payload['portal_theme']) ? $payload['portal_theme'] : null),
                'portal_description'       => (!empty($payload['portal_description']) ? $payload['portal_description'] : null),
                'portal_sub_description'   => (!empty($payload['portal_sub_description']) ? $payload['portal_sub_description'] : null),
                'terms_url'                => (!empty($payload['terms_url']) ? $payload['terms_url'] : null),
                'privacy_policy_url'       => (!empty($payload['privacy_policy_url']) ? $payload['privacy_policy_url'] : null),
                'status'                   => 1,
                'exclude_gender_and_dob'   => ((!empty($payload['exclude_gender_and_dob']) && $payload['exclude_gender_and_dob'] == 'on') ? true : false),
                'manage_the_design_change' => ((!empty($payload['manage_the_design_change']) && $payload['manage_the_design_change'] == 'on') ? true : false),
                'dt_title'                 => (!empty($payload['dt_title']) ? $payload['dt_title'] : null),
                'dt_description'           => (!empty($payload['dt_description']) ? $payload['dt_description'] : null),
                'appointment_title'         => $payload['appointment_title'],
                'appointment_description'   => !empty($payload['appointment_description']) ? $payload['appointment_description'] : null,
            ]);

            if (!empty($payload['login_screen_logo'])) {
                $name = $record->id . '_' . \time();
                $record
                    ->clearMediaCollection('branding_logo')
                    ->addMediaFromRequest('login_screen_logo')
                    ->usingName($payload['login_screen_logo']->getClientOriginalName())
                    ->usingFileName($name . '.' . $payload['login_screen_logo']->getClientOriginalExtension())
                    ->toMediaCollection('branding_logo', config('medialibrary.disk_name'));
            }

            if (!empty($payload['login_screen_background'])) {
                $name = $record->id . '_' . \time();
                $record
                    ->clearMediaCollection('branding_login_background')
                    ->addMediaFromRequest('login_screen_background')
                    ->usingName($payload['login_screen_background']->getClientOriginalName())
                    ->usingFileName($name . '.' . $payload['login_screen_background']->getClientOriginalExtension())
                    ->toMediaCollection('branding_login_background', config('medialibrary.disk_name'));
            }

            if (!empty($payload['portal_logo_main'])) {
                $name = $record->id . '_' . \time();
                $record
                    ->clearMediaCollection('portal_logo_main')
                    ->addMediaFromRequest('portal_logo_main')
                    ->usingName($payload['portal_logo_main']->getClientOriginalName())
                    ->usingFileName($name . '.' . $payload['portal_logo_main']->getClientOriginalExtension())
                    ->toMediaCollection('portal_logo_main', config('medialibrary.disk_name'));
            }

            if (!empty($payload['portal_logo_optional'])) {
                $name = $record->id . '_' . \time();
                $record
                    ->clearMediaCollection('portal_logo_optional')
                    ->addMediaFromRequest('portal_logo_optional')
                    ->usingName($payload['portal_logo_optional']->getClientOriginalName())
                    ->usingFileName($name . '.' . $payload['portal_logo_optional']->getClientOriginalExtension())
                    ->toMediaCollection('portal_logo_optional', config('medialibrary.disk_name'));
            }

            if (!empty($payload['portal_homepage_logo_right'])) {
                $name = $record->id . '_' . \time();
                $record
                    ->clearMediaCollection('portal_homepage_logo_right')
                    ->addMediaFromRequest('portal_homepage_logo_right')
                    ->usingName($payload['portal_homepage_logo_right']->getClientOriginalName())
                    ->usingFileName($name . '.' . $payload['portal_homepage_logo_right']->getClientOriginalExtension())
                    ->toMediaCollection('portal_homepage_logo_right', config('medialibrary.disk_name'));
            }

            if (!empty($payload['portal_homepage_logo_left'])) {
                $name = $record->id . '_' . \time();
                $record
                    ->clearMediaCollection('portal_homepage_logo_left')
                    ->addMediaFromRequest('portal_homepage_logo_left')
                    ->usingName($payload['portal_homepage_logo_left']->getClientOriginalName())
                    ->usingFileName($name . '.' . $payload['portal_homepage_logo_left']->getClientOriginalExtension())
                    ->toMediaCollection('portal_homepage_logo_left', config('medialibrary.disk_name'));
            }

            if (!empty($payload['portal_background_image'])) {
                $name = $record->id . '_' . \time();
                $record
                    ->clearMediaCollection('portal_background_image')
                    ->addMediaFromRequest('portal_background_image')
                    ->usingName($payload['portal_background_image']->getClientOriginalName())
                    ->usingFileName($name . '.' . $payload['portal_background_image']->getClientOriginalExtension())
                    ->toMediaCollection('portal_background_image', config('medialibrary.disk_name'));
            }

            if (!empty($payload['portal_favicon_icon'])) {
                $name = $record->id . '_' . \time();
                $record
                    ->clearMediaCollection('portal_favicon_icon')
                    ->addMediaFromRequest('portal_favicon_icon')
                    ->usingName($payload['portal_favicon_icon']->getClientOriginalName())
                    ->usingFileName($name . '.' . $payload['portal_favicon_icon']->getClientOriginalExtension())
                    ->toMediaCollection('portal_favicon_icon', config('medialibrary.disk_name'));
            }

            if (!empty($payload['appointment_image'])) {
                $name = $record->id . '_' . \time();
                $record
                    ->clearMediaCollection('appointment_image')
                    ->addMediaFromRequest('appointment_image')
                    ->usingName($payload['appointment_image']->getClientOriginalName())
                    ->usingFileName($name . '.' . $payload['appointment_image']->getClientOriginalExtension())
                    ->toMediaCollection('appointment_image', config('medialibrary.disk_name'));
            }

            if (empty($payload['appointment_image'])) {
                $name = $record->id . '_' . \time();
                $record->clearMediaCollection('appointment_image')
                    ->addMediaFromBase64($payload['appointment_image_hidden'])
                    ->usingName('appointment-default.png')
                    ->usingFileName($name.".png")
                    ->toMediaCollection('appointment_image', config('medialibrary.disk_name'));
            }
        }

        if (!empty($payload['email_header'])) {
            $name = $record->id . '_' . \time();
            $record
                ->clearMediaCollection('email_header')
                ->addMediaFromRequest('email_header')
                ->usingName($payload['email_header']->getClientOriginalName())
                ->usingFileName($name . '.' . $payload['email_header']->getClientOriginalExtension())
                ->toMediaCollection('email_header', config('medialibrary.disk_name'));
        }

        // set survey values if survey is set to true
        if (isset($record->enable_survey) && $record->enable_survey === true) {
            $assignedsurvey = ZcSurvey::find($payload['survey']);
            if ($assignedsurvey != null && $assignedsurvey->status != "Draft") {

                $surveyData = [
                    'is_premium'           => ((!empty($payload['is_premium']) && $payload['is_premium'] == 'on') ? true : false),
                    'survey_id'            => $payload['survey'],
                    'survey_frequency'     => $payload['survey_frequency'],
                    'survey_roll_out_day'  => $payload['survey_roll_out_day'],
                ];

                if(array_key_exists('survey_roll_out_time', $payload)) {
                    $surveyData['survey_roll_out_time'] = date('H:i', strtotime($payload['survey_roll_out_time']));
                }

                $record->survey()->create($surveyData);
                $assignedsurvey->status = "Assigned";
                $assignedsurvey->save();
            } else {
                return false;
            }
        }

        // create default location for company
        $defaultLocation = $record->locations()->create([
            'country_id'    => $payload['country'],
            'state_id'      => $payload['county'],
            'name'          => $payload['location_name'],
            'address_line1' => $payload['address_line1'],
            'address_line2' => $payload['address_line2'],
            'postal_code'   => $payload['postal_code'],
            'timezone'      => $payload['timezone'],
            'default'       => true,
        ]);

        $defaultDept->departmentlocations()->sync([[
            'company_id'          => $record->id,
            'department_id'       => $defaultDept->id,
            'company_location_id' => $defaultLocation->id,
        ]]);

        $defaultTeam->teamlocation()->sync([[
            'company_location_id' => $defaultLocation->id,
            'company_id'          => $record->id,
            'department_id'       => $defaultDept->id,
            'team_id'             => $defaultTeam->id,
        ]]);

        //Add moderators
        $moderatorsData = [];
        foreach ($payload['first_name'] as $index => $firstName) {
            $moderatorsData[$index]['first_name'] = $firstName;
        }
        foreach ($payload['last_name'] as $index => $lastName) {
            $moderatorsData[$index]['last_name'] = $lastName;
        }
        foreach ($payload['email'] as $index => $email) {
            $moderatorsData[$index]['email'] = $email;
        }

        foreach ($moderatorsData as $moderatorValue) {
            //Create service subcategory
            if (($moderatorValue['first_name']) && !empty($moderatorValue['last_name']) && !empty($moderatorValue['email'])) {
                $moderatorInput = [
                    'first_name'     => $moderatorValue['first_name'],
                    'last_name'      => $moderatorValue['last_name'],
                    'email'          => $moderatorValue['email'],
                    'last_login_at'  => now()->toDateTimeString(),
                    'is_premium'     => true,
                    'can_access_app' => false,
                    'start_date'     => $payload['subscription_start_date'],
                ];

                $user = User::create($moderatorInput);
                $record->moderators()->attach($user);

                $user->teams()->attach($defaultTeam, ['company_id' => $record->id, 'department_id' => $defaultDept->id]);

                $roleSlug = (($is_reseller) ? 'reseller_super_admin' : (!is_null($record->parent_id) ? 'reseller_company_admin' : 'company_admin'));
                $role     = Role::where(['slug' => $roleSlug, 'default' => 1])->first();
                $user->roles()->attach($role);

                if ($roleSlug != 'reseller_super_admin') {
                    $role = Role::where(['slug' => 'user', 'default' => 1])->first();
                    $user->roles()->attach($role);
                }

                $dob = Carbon::parse(now(), config('app.timezone'))->subYears(30);
                $age = (int) $dob->age;

                // save user profile
                $user->profile()->create([
                    'gender'     => 'male',
                    'height'     => '100',
                    'birth_date' => $dob->format('Y-m-d'),
                    'age'        => $age,
                ]);

                // save user weight
                $user->weights()->create([
                    'weight'   => '50',
                    'log_date' => now()->toDateTimeString(),
                ]);

                // calculate bmi and store
                $bmi = 50 / pow((100 / 100), 2);

                $user->bmis()->create([
                    'bmi'      => $bmi,
                    'weight'   => 50, // kg
                    'height'   => 100, // cm
                    'age'      => 0,
                    'log_date' => now()->toDateTimeString(),
                ]);

                $userGoalData             = array();
                $userGoalData['steps']    = 6000;
                $userGoalData['calories'] = 2500;

                // create or update user goal
                $user->goal()->updateOrCreate(['user_id' => $user->getKey()], $userGoalData);

                // set true flag in all notification modules
                $notificationModules = config('zevolifesettings.notificationModules');
                if (!empty($notificationModules)) {
                    foreach ($notificationModules as $key => $value) {
                        $user->notificationSettings()->create([
                            'module' => $key,
                            'flag'   => $value,
                        ]);
                    }
                }

                // add category expertise level
                $categories = Category::get();
                if (!empty($categories)) {
                    foreach ($categories as $key => $category) {
                        $user->expertiseLevels()->attach($category, ['expertise_level' => 'beginner']);
                    }
                }

                if ($user) {
                    event(new UserRegisterEvent($user, 'added_company'));
                }
            }
        }

        if (!empty($payload['assigned_roles'])) {
            $record->resellerRoles()->sync($payload['assigned_roles']);
        }

        if (array_key_exists('members_selected', $payload)) {
            $memberSelected = $payload['members_selected'];
            foreach ($memberSelected as $key => $value) {
                $splitValue = explode('-', $value);
                $masterId   = $splitValue[0];
                $contentId  = $splitValue[count($splitValue) - 1];
                switch ($masterId) {
                    case 1:
                        $masterclass_company[] = [
                            'masterclass_id' => $contentId,
                            'company_id'     => $record->id,
                            'created_at'     => Carbon::now(),
                        ];
                        break;
                    case 4:
                        $meditation_companyInput[] = [
                            'meditation_track_id' => $contentId,
                            'company_id'          => $record->id,
                            'created_at'          => Carbon::now(),
                        ];
                        break;
                    case 7:
                        $webinar_companyInput[] = [
                            'webinar_id' => $contentId,
                            'company_id' => $record->id,
                            'created_at' => Carbon::now(),
                        ];
                        break;
                    case 2:
                        $feed_companyInput[] = [
                            'feed_id'    => $contentId,
                            'company_id' => $record->id,
                            'created_at' => Carbon::now(),
                        ];
                        break;
                    case 9:
                        $podcast_companyInput[] = [
                            'podcast_id'     => $contentId,
                            'company_id'     => $record->id,
                            'created_at'     => Carbon::now(),
                        ];
                        break;
                    default:
                        $recipe_companyInput[] = [
                            'recipe_id'  => $contentId,
                            'company_id' => $record->id,
                            'created_at' => Carbon::now(),
                        ];
                        break;
                }
            }
            if (!empty($masterclass_company)) {
                foreach (array_chunk($masterclass_company, 1000) as $masterclassCompany) {
                    $record->masterclassCompany()->sync($masterclassCompany);
                }
            }
            if (!empty($meditation_companyInput)) {
                foreach (array_chunk($meditation_companyInput, 1000) as $meditationCompany) {
                    $record->meditationcompany()->sync($meditationCompany);
                }
            }
            if (!empty($webinar_companyInput)) {
                foreach (array_chunk($webinar_companyInput, 1000) as $webinarCompany) {
                    $record->webinarcompany()->sync($webinarCompany);
                }
            }
            if (!empty($feed_companyInput)) {
                foreach (array_chunk($feed_companyInput, 1000) as $feedCompany) {
                    $record->feedcompany()->sync($feedCompany);
                }
            }
            if (!empty($recipe_companyInput)) {
                foreach (array_chunk($recipe_companyInput, 1000) as $recipeCompany) {
                    $record->recipecompany()->sync($recipeCompany);
                }
            }
            if (!empty($podcast_companyInput)) {
                foreach (array_chunk($podcast_companyInput, 1000) as $podcastCompany) {
                    $record->podcastcompany()->sync($podcastCompany);
                }
            }
        }

        //Content assigned to team
        $teamLocation = TeamLocation::where('company_id', $record->id)->select('team_id')->get()->pluck('team_id')->toArray();
        foreach ($teamLocation as $teamVal) {
            foreach ($memberSelected as $key => $value) {
                $splitValue = explode('-', $value);
                $masterId   = $splitValue[0];
                $contentId  = $splitValue[count($splitValue) - 1];
                switch ($masterId) {
                    case 1:
                        $masterclass_teamInput[] = [
                            'masterclass_id' => $contentId,
                            'team_id'        => $teamVal,
                            'created_at'     => Carbon::now(),
                        ];
                        break;
                    case 4:
                        $meditation_teamInput[] = [
                            'meditation_track_id' => $contentId,
                            'team_id'             => $teamVal,
                            'created_at'          => Carbon::now(),
                        ];
                        break;
                    case 7:
                        $webinar_teamInput[] = [
                            'webinar_id' => $contentId,
                            'team_id'    => $teamVal,
                            'created_at' => Carbon::now(),
                        ];
                        break;
                    case 2:
                        $feed_teamInput[] = [
                            'feed_id'    => $contentId,
                            'team_id'    => $teamVal,
                            'created_at' => Carbon::now(),
                        ];
                        break;
                    case 9:
                        $podcast_teamInput[] = [
                            'podcast_id'    => $contentId,
                            'team_id'       => $teamVal,
                            'created_at'    => Carbon::now(),
                        ];
                        break;
                    default:
                        $recipe_teamInput[] = [
                            'recipe_id'  => $contentId,
                            'team_id'    => $teamVal,
                            'created_at' => Carbon::now(),
                        ];
                        break;
                }
            }

            if (!empty($masterclass_teamInput)) {
                DB::table('masterclass_team')->where('team_id', $teamVal)->delete();
                foreach (array_chunk($masterclass_teamInput, 1000) as $masterclassTeam) {
                    DB::table('masterclass_team')->insert($masterclassTeam);
                }
            }
            if (!empty($meditation_teamInput)) {
                DB::table('meditation_tracks_team')->where('team_id', $teamVal)->delete();
                foreach (array_chunk($meditation_teamInput, 1000) as $meditationTeam) {
                    DB::table('meditation_tracks_team')->insert($meditationTeam);
                }
            }
            if (!empty($webinar_teamInput)) {
                DB::table('webinar_team')->where('team_id', $teamVal)->delete();
                foreach (array_chunk($webinar_teamInput, 1000) as $webinarTeam) {
                    DB::table('webinar_team')->insert($webinarTeam);
                }
            }
            if (!empty($feed_teamInput)) {
                DB::table('feed_team')->where('team_id', $teamVal)->delete();
                foreach (array_chunk($feed_teamInput, 1000) as $feedTeam) {
                    DB::table('feed_team')->insert($feedTeam);
                }
            }
            if (!empty($recipe_teamInput)) {
                DB::table('recipe_team')->where('team_id', $teamVal)->delete();
                foreach (array_chunk($recipe_teamInput, 1000) as $recipeTeam) {
                    DB::table('recipe_team')->insert($recipeTeam);
                }
            }
            if (!empty($podcast_teamInput)) {
                DB::table('podcast_team')->where('team_id', $teamVal)->delete();
                foreach (array_chunk($podcast_teamInput, 1000) as $podcastTeam) {
                    DB::table('podcast_team')->insert($podcastTeam);
                }
            }
        }

        if ($record) {
            return true;
        }

        return false;
    }

    /**
     * update record data.
     *
     * @param payload , $id
     * @return boolean
     */

    public function updateEntity($payload)
    {
        $user                 = auth()->user();
        $role                 = getUserRole($user);
        $isEap                = ((!empty($payload['is_support']) && $payload['is_support'] == 'on') ? true : false);
        $oldEnableEvent       = $this->enable_event;
        $enableEvent          = ((!empty($payload['enable_event']) && $payload['enable_event'] == 'on') ? true : false);
        $oldEapTab            = $this->eap_tab;
        $enableEapTab         = ((!empty($payload['eap_tab']) && $payload['eap_tab'] == 'on') ? true : false);
        $nowInUTC             = now(config('app.timezone'))->toDateTimeString();
        $parentCompanyDetails = [];
        $totalSessions        = Calendly::Join('user_team', 'user_team.user_id', '=', 'eap_calendly.user_id')
            ->where('user_team.company_id', $this->id)
            ->where('eap_calendly.end_time', ">=", $nowInUTC)
            ->whereNull('cancelled_at')
            ->count();

        // prevent event to be disabled for ZCA if anyongoing events are exist
        if ($oldEnableEvent != $enableEvent && !$enableEvent) {
            $nowInUTC       = now(config('app.timezone'))->toDateTimeString();
            $upComingEvents = $this->evnetBookings()
                ->where('event_booking_logs.status', '4')
                ->whereRaw("TIMESTAMP(CONCAT(event_booking_logs.booking_date, ' ', event_booking_logs.end_time)) > ?", [$nowInUTC])
                ->count('event_booking_logs.id');
            $enableEvent = (!empty($upComingEvents) ? true : false);
        }

        if (!empty($this->parent_id)) {
            $parentCompanyDetails = self::where('id', $this->parent_id)->first();
        }

        $updateData = [
            'name'                    => $payload['name'],
            'description'             => $payload['description'],
            'industry_id'             => $payload['industry'],
            'size'                    => $payload['size'],
            'subscription_start_date' => Carbon::parse($payload['subscription_start_date'])->format('Y-m-d 00:00:00'),
            'subscription_end_date'   => Carbon::parse($payload['subscription_end_date'])->format('Y-m-d 00:00:00'),
            'status'                  => (Carbon::parse($payload['subscription_start_date'])->toDateTimeString() <= Carbon::today()->toDateTimeString() && (Carbon::parse($payload['subscription_end_date'])->toDateTimeString() > Carbon::today()->toDateTimeString())),
            'is_faqs'                 => ((!empty($payload['is_faqs']) && $payload['is_faqs'] == 'on') ? true : false),
            'is_eap'                  => $isEap,
            'has_domain'              => ((!empty($payload['registration_restriction']) && $payload['registration_restriction'] == '1')) ? true : false,
            'group_restriction'       => (!empty($payload['group_restriction']) && $payload['group_restriction'] == 'on') ? $payload['group_restriction_rule'] : 0,
            'is_branding'             => ((!empty($payload['is_branding']) && $payload['is_branding'] == 'on') ? true : false),
            'enable_survey'           => ((!empty($payload['enable_survey']) && $payload['enable_survey'] == 'on') ? true : false),
            'zcsurvey_on_email'       => ((!empty($payload['zcsurvey_on_email']) && $payload['zcsurvey_on_email'] == 'on') ? true : false)
        ];
        
        if (array_key_exists('disable_sso', $payload)) {
            $updateData['disable_sso'] = ((!empty($payload['disable_sso']) && $payload['disable_sso'] == 'on') ? true : false);
        }

        if (array_key_exists('eap_tab', $payload) && $totalSessions <= 0 && ($payload['companyType'] == 'zevo' || $payload['companyType'] == 'reseller')) {
            $updateData['eap_tab'] = ((!empty($payload['eap_tab']) && $payload['eap_tab'] == 'on') ? true : false);
        }

        if ($payload['companyType'] == 'reseller' || $payload['companyType'] == 'normal') {
            $updateData['hide_content'] = ((!empty($payload['hidecontent']) && $payload['hidecontent'] == '1') ? true : ((!empty($parentCompanyDetails)) ? $parentCompanyDetails->hide_content : false));
        }

        if ($role->group == 'zevo') {
            $updateData['is_intercom'] = (!empty($payload['is_intercom']) && $payload['is_intercom'] == 'on') ? true : false;
        }

        if (!$this->is_reseller && is_null($this->parent_id)) {
            $updateData['allow_app']    = true;
            $updateData['enable_event'] = $enableEvent; // Only for zevo this checkbox will update
        } else {
            if ($role->group == 'zevo') {
                $updateData['allow_app'] = (!empty($payload['allow_app']) ? 1 : 0);
            }
        }

        $updated = $this->update($updateData);

        $companyPlan = ((!empty($payload['companyplan'])) ? $payload['companyplan'] : null);
        if (!empty($companyPlan)) {
            $this->companyplan()->sync($companyPlan);
        }

        if ((!empty($payload['companyplan']) && !$this->is_reseller && is_null($this->parent_id)) || (!empty($payload['companyplan']) && $payload['companyType'] != 'normal' && $payload['dtExistsHidden'])) {
            if (!empty($payload['dt_servicemode'])) {
                $dtData['dt_is_online'] = (in_array("online", $payload['dt_servicemode']) ? true : false);
                $dtData['dt_is_onsite'] = (in_array("onsite", $payload['dt_servicemode']) ? true : false);
            }
            $dtData['dt_session_update']       = (!empty($payload['dt_session_update']) ? $payload['dt_session_update'] : 0);
            $dtData['dt_advanced_booking']     = (!empty($payload['dt_advanced_booking']) ? $payload['dt_advanced_booking'] : 0);
            $dtData['dt_future_booking']       = (!empty($payload['dt_future_booking']) ? $payload['dt_future_booking'] : 14);
            $dtData['dt_max_sessions_user']    = (!empty($payload['dt_max_sessions_user']) ? $payload['dt_max_sessions_user'] : 0);
            $dtData['dt_max_sessions_company'] = (!empty($payload['dt_max_sessions_company']) ? $payload['dt_max_sessions_company'] : 0);

            if(array_key_exists('emergency_contacts', $payload)) {
                $dtData['emergency_contacts'] = ((!empty($payload['emergency_contacts']) && $payload['emergency_contacts'] == 'on') ? true : false);
            }
            if(array_key_exists('consent', $payload)) {
                $dtData['consent'] = ((!empty($payload['get_user_consent']) && $payload['get_user_consent'] == 'on') ? true : false);
            }
            
            $dtData['set_hours_by']            = (!empty($payload['set_hours_by']) ? $payload['set_hours_by'] : 1);
            $dtData['set_availability_by']     = (!empty($payload['set_availability_by']) ? $payload['set_availability_by'] : 1);

            //Update the ws details and services in the table
            if (!empty($payload['service'])) {
                $existingServices = $this->digitalTherapyService()->pluck('id')->toArray();
                foreach ($payload['service'] as $key => $value) {
                    foreach ($value as $serviceIndex => $serviceId) {
                        if (!empty($key)) {
                            $updateServiceIds[] = $serviceIndex;
                        }
                        $dtExists = $this->digitalTherapyService()->where('company_id', $this->id)->where('ws_id', $key)->where('service_id', $serviceId)->where('id', $serviceIndex)->first();

                        if (empty($dtExists)) {
                            $this->digitalTherapyService()->create(['ws_id' => $key, 'service_id' => $serviceId]);
                        } else {
                            $this->digitalTherapyService()->where(['ws_id' => $key, 'id' => $dtExists->id])->update(['service_id' => $serviceId]);
                        }
                    }
                }
                $removeServiceIds = array_diff($existingServices, $updateServiceIds);
                if (!empty($removeServiceIds)) {
                    $this->digitalTherapyService()->whereIn('id', $removeServiceIds)->delete();
                }
            }

            $this->digitalTherapy()->updateOrCreate(['company_id' => $this->id], $dtData);

            // to set hc user day wise slots
            $setHoursBy        = $payload['set_hours_by'];
            $setAvailabilityBy = $payload['set_availability_by'];
            if ($setHoursBy == 1 && $setAvailabilityBy == 1) {
                // Company - General
                if (!empty($payload['slots'])) {
                    $existingSlots = $this->digitalTherapySlots()->whereNull('location_id')->pluck('id')->toArray();
                    $updateSlotIds = [];

                    foreach ($payload['slots'] as $day => $slots) {
                        foreach ($slots['start_time'] as $key => $time) {
                            if (!empty($key)) {
                                $updateSlotIds[] = $key;
                            }
                            $start_time  = Carbon::createFromFormat('H:i', $time, $this->timezone);
                            $end_time    = Carbon::createFromFormat('H:i', $slots['end_time'][$key], $this->timezone);
                            $wellbeingSP = "";
                            if (isset($payload['selected_ws'][$day]) && isset($payload['selected_ws'][$day][$key])) {
                                $wellbeingSP = implode(',', $payload['selected_ws'][$day][$key]);
                            }

                            $this->digitalTherapySlots()->whereNull('location_id')->updateOrCreate([
                                'id' => ((!empty($key) && is_numeric($key)) ? $key : 0),
                            ], [
                                'day'        => $day,
                                'start_time' => $start_time->format('H:i:00'),
                                'end_time'   => $end_time->format('H:i:59'),
                                'ws_id'      => $wellbeingSP,
                            ]);
                        }
                    }

                    $removeIds = array_diff($existingSlots, $updateSlotIds);
                    if (!empty($removeIds)) {
                        $this->digitalTherapySlots()->whereNull('location_id')->whereIn('id', $removeIds)->delete();
                    }
                }
            } elseif ($setHoursBy == 2 && $setAvailabilityBy == 1) {
                /* === Add Location General Slots === */
                if (!empty($payload['tempTableRemovedSlotIds'])) {
                    $deleteTempSlots = $this->tempDigitalTherapySlots();
                    if (str_contains($payload['tempTableRemovedSlotIds'], ',')) {
                        $slotIds = explode(',', $payload['tempTableRemovedSlotIds']);
                        $deleteTempSlots->whereIn('id', $slotIds);
                    } else {
                        $slotIds = $payload['tempTableRemovedSlotIds'];
                        $deleteTempSlots->where('id', $slotIds);
                    }
                    $deleteTempSlots->delete();
                }

                $locationGeneralData = $this->tempDigitalTherapySlots()->where('company_id', $this->id)->get();
                $locationSlots       = [];
                if (!empty($locationGeneralData)) {
                    foreach ($locationGeneralData as $locationV) {
                        $locationSlots['day']         = $locationV->day;
                        $locationSlots['start_time']  = $locationV->start_time;
                        $locationSlots['end_time']    = $locationV->end_time;
                        $locationSlots['ws_id']       = $locationV->ws_id;
                        $locationSlots['location_id'] = $locationV->location_id;
                        $locationSlots['company_id']  = $locationV->company_id;
                        $this->digitalTherapySlots()->create($locationSlots);
                    }

                    $this->tempDigitalTherapySlots()->where('company_id', $this->id)->delete();
                }

                if (!empty($payload['mainTableUpdatedSlotIds'])) {
                    $deleteOldSlots = $this->digitalTherapySlots();
                    if (str_contains($payload['mainTableUpdatedSlotIds'], ',')) {
                        $slotIds = explode(',', $payload['mainTableUpdatedSlotIds']);
                        $deleteOldSlots->whereIn('id', $slotIds);
                    } else {
                        $slotIds = $payload['mainTableUpdatedSlotIds'];
                        $deleteOldSlots->where('id', $slotIds);
                    }
                    $deleteOldSlots->delete();
                }

                if (!empty($payload['mainTableRemovedSlotIds'])) {
                    $deleteMainSlots = $this->digitalTherapySlots();
                    if (str_contains($payload['mainTableRemovedSlotIds'], ',')) {
                        $slotIds = explode(',', $payload['mainTableRemovedSlotIds']);
                        $deleteMainSlots->whereIn('id', $slotIds);
                    } else {
                        $slotIds = $payload['mainTableRemovedSlotIds'];
                        $deleteMainSlots->where('id', $slotIds);
                    }
                    $deleteMainSlots->delete();
                }
                /* === Finish location general slots === */
            } elseif ($setHoursBy == 1 && $setAvailabilityBy == 2) {
                // Company - Specific
                if (!empty($payload['specific_slots'])) {
                    $this->digitalTherapySpecificSlots()->whereNull('location_id')->delete();
                    $specificWiseSlots = [];
                    foreach ($payload['specific_slots'] as $wsId => $slotDate) {
                        foreach ($slotDate as $timeStamp => $slots_specific) {
                            foreach ($slots_specific as $slots) {
                                foreach ($slots as $key => $slot) {
                                    $slot_array          = explode('-', $slot);
                                    $specificWiseSlots[] = [
                                        'ws_id'      => $wsId,
                                        'date'       => $timeStamp,
                                        'start_time' => $slot_array[0],
                                        'end_time'   => $slot_array[1],
                                    ];
                                }
                            }
                        }
                    }
                    $this->digitalTherapySpecificSlots()->createMany($specificWiseSlots);
                }
            } elseif ($setHoursBy == 2 && $setAvailabilityBy == 2) {
                // Location - Specific
                if (!empty($payload['location_specific_slots'])) {
                    $specificLocationWiseSlots = $locationIds = $existingWbsIds = [];
                    foreach ($payload['location_specific_slots'] as $locationId => $wsData) {
                        array_push($locationIds, $locationId);
                        $this->digitalTherapySpecificSlots()->where('location_id', $locationId)->delete();
                        foreach ($wsData as $wsId => $slotDate) {
                            array_push($existingWbsIds, $wsId);
                            foreach ($slotDate as $timeStamp => $slots_specific) {
                                foreach ($slots_specific as $slots) {
                                    foreach ($slots as $key => $slot) {
                                        $slot_array                  = explode('-', $slot);
                                        $specificLocationWiseSlots[] = [
                                            'ws_id'       => $wsId,
                                            'location_id' => $locationId,
                                            'date'        => $timeStamp,
                                            'start_time'  => $slot_array[0],
                                            'end_time'    => $slot_array[1],
                                        ];
                                    }
                                }
                            }
                        }
                    }
                    $this->digitalTherapySpecificSlots()->createMany($specificLocationWiseSlots);
                    $this->digitalTherapySpecificSlots()->whereNotIn('location_id', $locationIds)->delete();
                }
            }
        }

        // enableEvent is being set to false
        if ($oldEnableEvent != $enableEvent && !$enableEvent) {
            // delete all the event related notifications of the company
            Notification::where('tag', 'event')
                ->where('company_id', $this->id)
                ->delete();
        }

        // enableEapTab is being set to false
        if ($oldEapTab != $enableEapTab && !$enableEapTab && $totalSessions <= 0) {
            // delete all the calendly related notifications of the company
            Notification::where('tag', 'new-eap')
                ->where('company_id', $this->id)
                ->delete();
        }

        // Related company eap records remove deep link uri from eap_list table when eap display for this company
        if (!$isEap) {
            $getEapList = EAP::select('eap_list.deep_link_uri')
                ->join('eap_company', 'eap_company.eap_id', '=', 'eap_list.id')
                ->where('eap_company.company_id', $this->id)->get()->pluck('deep_link_uri')->toArray();

            foreach ($getEapList as $key => $value) {
                Notification::where(function ($query) use ($value) {
                    $query
                        ->where('tag', '=', 'eap')
                        ->where('deep_link_uri', '=', $value);
                })->delete();
            }
        }

        if ($this->is_reseller) {
            $childCompanies = $this->childCompanies();
            // to change start date of child which start date is lesser then parent company's start date
            $subscriptionStartDate = carbon::parse($this->subscription_start_date)->toDateTimeString();
            $childCompanies
                ->where('subscription_start_date', '<=', $subscriptionStartDate)
                ->update(['subscription_start_date' => $subscriptionStartDate]);
            // to change end date of child which end date is greater then parent company's end date
            $subscriptionEndDate = carbon::parse($this->subscription_end_date)->toDateTimeString();
            $childCompanies
                ->where('subscription_end_date', '>=', $subscriptionEndDate)
                ->update(['subscription_end_date' => $subscriptionEndDate]);
        }

        if (isset($payload['logo']) && !empty($payload['logo'])) {
            $name = $this->id . '_' . \time();
            $this
                ->clearMediaCollection('logo')
                ->addMediaFromRequest('logo')
                ->usingName($payload['logo']->getClientOriginalName())
                ->usingFileName($name . '.' . $payload['logo']->getClientOriginalExtension())
                ->toMediaCollection('logo', config('medialibrary.disk_name'));
        }

        if (isset($payload['remove_email_header']) && $payload['remove_email_header'] == 1) {
            $this->clearMediaCollection('email_header');
        }

        if (!empty($payload['email_header'])) {
            $name = $this->id . '_' . \time();
            $this
                ->clearMediaCollection('email_header')
                ->addMediaFromRequest('email_header')
                ->usingName($payload['email_header']->getClientOriginalName())
                ->usingFileName($name . '.' . $payload['email_header']->getClientOriginalExtension())
                ->toMediaCollection('email_header', config('medialibrary.disk_name'));
        }

        // Store contact us branding data
        if ($role->slug == 'super_admin' && ($this->is_reseller || (!$this->is_reseller && !is_null($this->parent_id)))) {
            $updatedBrandingContact = $this->brandingContactDetails()->updateOrCreate(['company_id' => $this->id], [
                'contact_us_header'        => ($payload['contact_us_header'] ?? ""),
                'contact_us_request'       => ($payload['contact_us_request'] ?? ""),
                'contact_us_description'   => ($payload['contact_us_description'] ? trim(str_replace(["\r\n", "&nbsp;", "&nbsp; "], "", htmlspecialchars_decode($payload['contact_us_description']))) : ""),
            ]);
            
            $this->branding()->updateOrCreate(['company_id' => $this->id], [
                'appointment_title'         => $payload['appointment_title'],
                'appointment_description'   => !empty($payload['appointment_description']) ? $payload['appointment_description'] : null,
            ]);
            
            if (!empty($payload['contact_us_image'])) {
                $name = $updatedBrandingContact->id . '_' . \time();
                $updatedBrandingContact
                    ->clearMediaCollection('contact_us_image')
                    ->addMediaFromRequest('contact_us_image')
                    ->usingName($payload['contact_us_image']->getClientOriginalName())
                    ->usingFileName($name . '.' . $payload['contact_us_image']->getClientOriginalExtension())
                    ->toMediaCollection('contact_us_image', config('medialibrary.disk_name'));
            }

            if (!empty($payload['appointment_image'])) {
                $name = $this->id . '_' . \time();
                $this
                    ->clearMediaCollection('appointment_image')
                    ->addMediaFromRequest('appointment_image')
                    ->usingName($payload['appointment_image']->getClientOriginalName())
                    ->usingFileName($name . '.' . $payload['appointment_image']->getClientOriginalExtension())
                    ->toMediaCollection('appointment_image', config('medialibrary.disk_name'));
            }

            if (empty($payload['contact_us_image']) && $role->slug == 'super_admin' &&(!$this->is_reseller && !is_null($this->parent_id))) {
                $companyBrandingContact = CompanyBrandingContactDetails::where('company_id', $this->parent_id)->first();
                if (!empty($companyBrandingContact->getFirstMediaUrl('contact_us_image'))) {
                    $media     = $companyBrandingContact->getFirstMedia('contact_us_image');
                    $updatedBrandingContact->clearMediaCollection('contact_us_image')
                        ->addMediaFromUrl(
                            $companyBrandingContact->getFirstMediaUrl('contact_us_image')
                        )
                        ->usingName($media->name)
                        ->usingFileName($media->file_name)
                        ->toMediaCollection('contact_us_image', config('medialibrary.disk_name'));
                }
            }

            if (empty($payload['appointment_image']) && $role->slug == 'super_admin' &&(!$this->is_reseller && !is_null($this->parent_id))) {
                $companyDetails = Company::where('id', $this->parent_id)->first();
                if (!empty($companyDetails->getFirstMediaUrl('appointment_image'))) {
                    $media     = $companyDetails->getFirstMedia('appointment_image');
                    $this->clearMediaCollection('appointment_image')
                        ->addMediaFromUrl(
                            $companyDetails->getFirstMediaUrl('appointment_image')
                        )
                        ->usingName($media->name)
                        ->usingFileName($media->file_name)
                        ->toMediaCollection('appointment_image', config('medialibrary.disk_name'));
                }
            }
        }
        
        if ($this->is_reseller || (!$this->is_reseller && is_null($this->parent_id))) {
            // set branding values if domain branding is set to true
            if ($this->is_branding === true) {
                if (isset($payload['remove_login_screen_logo']) && $payload['remove_login_screen_logo'] == 1) {
                    $this->clearMediaCollection('branding_logo');
                }

                if (isset($payload['remove_login_screen_background']) && $payload['remove_login_screen_background'] == 1) {
                    $this->clearMediaCollection('branding_login_background');
                }

                $portalTitle          = (!empty($payload['portal_title']) ? $payload['portal_title'] : null);
                $portalTheme          = isset($this->branding->portal_theme) ? $this->branding->portal_theme : null;
                $portalDescription    = (!empty($payload['portal_description']) ? $payload['portal_description'] : null);
                $termsUrl             = isset($this->branding->terms_url) ? $this->branding->terms_url : null;
                $privacyPolicyUrl     = isset($this->branding->privacy_policy_url) ? $this->branding->privacy_policy_url : null;


                $brandingData = [
                    'onboarding_title'         => ($payload['onboarding_title'] ?? ""),
                    'onboarding_description'   => (trim($payload['onboarding_description']) ?? ""),
                    'sub_domain'               => $payload['sub_domain'],
                    'portal_title'             => (!empty($payload['portal_title']) ? $payload['portal_title'] : $portalTitle),
                    'portal_theme'             => (!empty($payload['portal_theme']) ? $payload['portal_theme'] : $portalTheme),
                    'portal_description'       => (!empty($payload['portal_description']) ? $payload['portal_description'] : $portalDescription),
                    'portal_sub_description'   => (!empty($payload['portal_sub_description']) ? $payload['portal_sub_description'] : null),
                    'terms_url'                => (!empty($payload['terms_url']) ? $payload['terms_url'] : $termsUrl),
                    'privacy_policy_url'       => (!empty($payload['privacy_policy_url']) ? $payload['privacy_policy_url'] : $privacyPolicyUrl),
                    'status'                   => 1,
                    'dt_title'                 => (!empty($payload['dt_title']) ? $payload['dt_title'] : null),
                    'dt_description'           => (!empty($payload['dt_description']) ? $payload['dt_description'] : null),
                ];

                if(array_key_exists('exclude_gender_and_dob', $payload)) {
                    $brandingData['exclude_gender_and_dob'] = ((!empty($payload['exclude_gender_and_dob']) && $payload['exclude_gender_and_dob'] == 'on') ? true : false);
                }

                if(array_key_exists('manage_the_design_change', $payload)) {
                    $brandingData['manage_the_design_change'] = ((!empty($payload['manage_the_design_change']) && $payload['manage_the_design_change'] == 'on') ? true : false);
                }

                $this->branding()->updateOrCreate(['company_id' => $this->id], $brandingData);

                if (!empty($payload['login_screen_logo'])) {
                    $name = $this->id . '_' . \time();
                    $this
                        ->clearMediaCollection('branding_logo')
                        ->addMediaFromRequest('login_screen_logo')
                        ->usingName($payload['login_screen_logo']->getClientOriginalName())
                        ->usingFileName($name . '.' . $payload['login_screen_logo']->getClientOriginalExtension())
                        ->preservingOriginal()
                        ->toMediaCollection('branding_logo', config('medialibrary.disk_name'));
                }

                if (!empty($payload['login_screen_background'])) {
                    $name = $this->id . '_' . \time();
                    $this
                        ->clearMediaCollection('branding_login_background')
                        ->addMediaFromRequest('login_screen_background')
                        ->usingName($payload['login_screen_background']->getClientOriginalName())
                        ->usingFileName($name . '.' . $payload['login_screen_background']->getClientOriginalExtension())
                        ->preservingOriginal()
                        ->toMediaCollection('branding_login_background', config('medialibrary.disk_name'));
                }

                if (!empty($payload['portal_logo_main'])) {
                    $name = $this->id . '_' . \time();
                    $this
                        ->clearMediaCollection('portal_logo_main')
                        ->addMediaFromRequest('portal_logo_main')
                        ->usingName($payload['portal_logo_main']->getClientOriginalName())
                        ->usingFileName($name . '.' . $payload['portal_logo_main']->getClientOriginalExtension())
                        ->toMediaCollection('portal_logo_main', config('medialibrary.disk_name'));
                }

                if (!empty($payload['portal_logo_optional'])) {
                    $name = $this->id . '_' . \time();
                    $this
                        ->clearMediaCollection('portal_logo_optional')
                        ->addMediaFromRequest('portal_logo_optional')
                        ->usingName($payload['portal_logo_optional']->getClientOriginalName())
                        ->usingFileName($name . '.' . $payload['portal_logo_optional']->getClientOriginalExtension())
                        ->toMediaCollection('portal_logo_optional', config('medialibrary.disk_name'));
                }

                if (!empty($payload['portal_homepage_logo_right'])) {
                    $name = $this->id . '_' . \time();
                    $this
                        ->clearMediaCollection('portal_homepage_logo_right')
                        ->addMediaFromRequest('portal_homepage_logo_right')
                        ->usingName($payload['portal_homepage_logo_right']->getClientOriginalName())
                        ->usingFileName($name . '.' . $payload['portal_homepage_logo_right']->getClientOriginalExtension())
                        ->toMediaCollection('portal_homepage_logo_right', config('medialibrary.disk_name'));
                }

                if (!empty($payload['portal_homepage_logo_left'])) {
                    $name = $this->id . '_' . \time();
                    $this
                        ->clearMediaCollection('portal_homepage_logo_left')
                        ->addMediaFromRequest('portal_homepage_logo_left')
                        ->usingName($payload['portal_homepage_logo_left']->getClientOriginalName())
                        ->usingFileName($name . '.' . $payload['portal_homepage_logo_left']->getClientOriginalExtension())
                        ->toMediaCollection('portal_homepage_logo_left', config('medialibrary.disk_name'));
                }

                if (!empty($payload['portal_background_image'])) {
                    $name = $this->id . '_' . \time();
                    $this
                        ->clearMediaCollection('portal_background_image')
                        ->addMediaFromRequest('portal_background_image')
                        ->usingName($payload['portal_background_image']->getClientOriginalName())
                        ->usingFileName($name . '.' . $payload['portal_background_image']->getClientOriginalExtension())
                        ->toMediaCollection('portal_background_image', config('medialibrary.disk_name'));
                }

                if (!empty($payload['portal_favicon_icon'])) {
                    $name = $this->id . '_' . \time();
                    $this
                        ->clearMediaCollection('portal_favicon_icon')
                        ->addMediaFromRequest('portal_favicon_icon')
                        ->usingName($payload['portal_favicon_icon']->getClientOriginalName())
                        ->usingFileName($name . '.' . $payload['portal_favicon_icon']->getClientOriginalExtension())
                        ->toMediaCollection('portal_favicon_icon', config('medialibrary.disk_name'));
                }
            }
        }

        $oldSurveyDetails = $this->survey;
        $timezone         = ($payload['timezone'] ?? config('app.timezone'));
        $now              = now(config('app.timezone'));
        $companyNow       = now($timezone);

        // set survey values if survey is set to true
        if ($this->enable_survey === true) {
            if ($oldSurveyDetails != null) {
                if ($oldSurveyDetails->survey_id != $payload['survey']) {
                    $oldassignedsurvey = ZcSurvey::find($oldSurveyDetails->survey_id);
                    // check if previous survey isn't assinged to any other companies then set survey status to 'Published' from 'Assigned'
                    $assignedcounts = $oldassignedsurvey->surveycompany()->where('company_id', '!=', $this->id)->count();
                    if ($assignedcounts == 0 || is_null($assignedcounts)) {
                        // set servey status to published if not to assigned any
                        $oldassignedsurvey->status = 'Published';
                        $oldassignedsurvey->save();
                    }
                }

                // if survey frequency is changed then need to expire current active survey.
                if ($oldSurveyDetails->survey_frequency != $payload['survey_frequency']) {
                    //fetch last scheduled survey
                    $lastSurveySend = $this->companySurveyLog()->orderBy("id", "DESC")->first();

                    // check if last survey is still active then expire it
                    if (!is_null($lastSurveySend) && $lastSurveySend->expire_date->toDateString() >= $now->toDateString()) {
                        $lastSurveySend->expire_date = $now->toDateTimeString();
                        $lastSurveySend->save();
                    }
                }
            } else {
                // if there isn't any old survey setting then need to check if any active survey is there
                $lastSurveySend = $this->companySurveyLog()->orderBy("id", "DESC")->first();

                // check if last survey is still active then set expire date according to new survey rollout date and time
                if (!is_null($lastSurveySend) && $lastSurveySend->expire_date->toDateString() >= $now->toDateString()) {
                    $rollout_time = explode(":", $payload['survey_roll_out_time']);
                    if ($companyNow->is($payload['survey_roll_out_day'])) {
                        $previousSurveyNewExpireDate = (clone $companyNow)->setTime($rollout_time[0], $rollout_time[1], 0, 0)->subMinutes(1)->setTimezone(config('app.timezone'));
                    } else {
                        $previousSurveyNewExpireDate = (clone $companyNow)->next($payload['survey_roll_out_day'])->setTime($rollout_time[0], $rollout_time[1], 0, 0)->subMinutes(1)->setTimezone(config('app.timezone'));
                    }
                    $lastSurveySend->expire_date = $previousSurveyNewExpireDate->toDateTimeString();
                    $lastSurveySend->save();
                }
            }

            $assignedsurvey = ZcSurvey::find($payload['survey']);
            if ($assignedsurvey != null && $assignedsurvey->status != "Draft") {
                $companyId = $this->id;
                $surveyData = [
                    'is_premium'           => ((!empty($payload['is_premium']) && $payload['is_premium'] == 'on') ? true : false),
                    'survey_id'            => $payload['survey'],
                    'survey_frequency'     => $payload['survey_frequency'],
                    'survey_roll_out_day'  => $payload['survey_roll_out_day'],
                ];

                if(array_key_exists('survey_roll_out_time', $payload)) {
                    $surveyData['survey_roll_out_time'] = date('H:i', strtotime($payload['survey_roll_out_time']));
                }

                
                $this->survey()->updateOrCreate(['company_id' => $companyId], $surveyData); 
                $assignedsurvey->status = "Assigned";
                $assignedsurvey->save();
            } else {
                return false;
            }
        } else {
            // check if previous survey isn't assinged to any other companies then set survey status to 'Published' from 'Assigned'
            if ($oldSurveyDetails != null) {
                $oldassignedsurvey = ZcSurvey::find($oldSurveyDetails->survey_id);
                $assignedcounts    = $oldassignedsurvey->surveycompany()->where('company_id', '!=', $this->id)->count();
                if ($assignedcounts == 0 || is_null($assignedcounts)) {
                    $oldassignedsurvey->status = 'Published';
                    $oldassignedsurvey->save();
                }
            }
            $this->survey()->delete();
        }

        // create default location for company
        $this->locations()->where('default', true)->first()->update([
            'country_id'    => $payload['country'],
            'state_id'      => $payload['county'],
            'name'          => $payload['location_name'],
            'address_line1' => $payload['address_line1'],
            'address_line2' => $payload['address_line2'],
            'postal_code'   => $payload['postal_code'],
            'timezone'      => $payload['timezone'],
        ]);

        if (!empty($payload['assigned_roles'])) {
            $currentRoles = $this->resellerRoles()->pluck('roles.id')->toArray();
            $this->resellerRoles()->sync($payload['assigned_roles']);

            // Delete removed roles of parent company from child companies
            if ($this->is_reseller) {
                $removeRoles    = array_diff($currentRoles, $payload['assigned_roles']);
                $childCompanies = Company::Where('parent_id', $this->id)->get()->pluck('id')->toArray();
                CompanyRoles::whereIn('company_id', $childCompanies)->whereIn('role_id', $removeRoles)->delete();
            }
        }

        if (array_key_exists('members_selected', $payload)) {
            $memberSelected = $payload['members_selected'];

            $masterclass_company = [];
            $meditation_companyInput = [];
            $webinar_companyInput = [];
            $feed_companyInput = [];
            $podcast_companyInput = [];
            $recipe_companyInput = [];
            
            foreach ($memberSelected as $key => $value) {
                $splitValue = explode('-', $value);
                $masterId   = $splitValue[0];
                $contentId  = $splitValue[count($splitValue) - 1];

                if ($masterId == 1) {
                    $masterclass_company[] = [
                        'masterclass_id' => $contentId,
                        'company_id'     => $this->id,
                        'created_at'     => Carbon::now(),
                    ];
                } else if ($masterId == 4) {
                    $meditation_companyInput[] = [
                        'meditation_track_id' => $contentId,
                        'company_id'          => $this->id,
                        'created_at'          => Carbon::now(),
                    ];
                } else if ($masterId == 7) {
                    $webinar_companyInput[] = [
                        'webinar_id' => $contentId,
                        'company_id' => $this->id,
                        'created_at' => Carbon::now(),
                    ];
                } else if ($masterId == 2) {
                    $feed_companyInput[] = [
                        'feed_id'    => $contentId,
                        'company_id' => $this->id,
                        'created_at' => Carbon::now(),
                    ];
                } else if ($masterId == 9) {
                    $podcast_companyInput[] = [
                        'podcast_id' => $contentId,
                        'company_id' => $this->id,
                        'created_at' => Carbon::now(),
                    ];
                } else {
                    $recipe_companyInput[] = [
                        'recipe_id'  => $contentId,
                        'company_id' => $this->id,
                        'created_at' => Carbon::now(),
                    ];
                }
            }

            // Content assigned to team
            $teamLocation = TeamLocation::where('company_id', $this->id)->select('team_id')->get()->pluck('team_id')->toArray();

            $masterclass_teamInput = [];
            $meditation_teamInput = [];
            $webinar_teamInput = [];
            $feed_teamInput = [];
            $podcast_teamInput = [];
            $recipe_teamInput = [];

            foreach ($teamLocation as $teamVal) {
                foreach ($memberSelected as $key => $value) {
                    $splitValue = explode('-', $value);
                    $masterId   = $splitValue[0];
                    $contentId  = $splitValue[count($splitValue) - 1];
                    switch ($masterId) {
                        case 1:
                            $masterclass_teamInput[] = [
                                'masterclass_id' => $contentId,
                                'team_id'        => $teamVal,
                                'created_at'     => Carbon::now(),
                            ];
                            break;
                        case 4:
                            $meditation_teamInput[] = [
                                'meditation_track_id' => $contentId,
                                'team_id'             => $teamVal,
                                'created_at'          => Carbon::now(),
                            ];
                            break;
                        case 7:
                            $webinar_teamInput[] = [
                                'webinar_id' => $contentId,
                                'team_id'    => $teamVal,
                                'created_at' => Carbon::now(),
                            ];
                            break;
                        case 2:
                            $feed_teamInput[] = [
                                'feed_id'    => $contentId,
                                'team_id'    => $teamVal,
                                'created_at' => Carbon::now(),
                            ];
                            break;
                        case 9:
                            $podcast_teamInput[] = [
                                'podcast_id'    => $contentId,
                                'team_id'       => $teamVal,
                                'created_at'    => Carbon::now(),
                            ];
                            break;
                        default:
                            $recipe_teamInput[] = [
                                'recipe_id'  => $contentId,
                                'team_id'    => $teamVal,
                                'created_at' => Carbon::now(),
                            ];
                            break;
                    }
                }
            }

            // Company
            Bus::batch([
                new SpContentAssignFromCompanyJob($masterclass_company, $meditation_companyInput, $webinar_companyInput, $feed_companyInput, $podcast_companyInput, $recipe_companyInput, $this->id),
                new SpContentAssignToCompanyJob($masterclass_company, $meditation_companyInput, $webinar_companyInput, $feed_companyInput, $podcast_companyInput, $recipe_companyInput, $this->id)
            ])->onQueue('default')->dispatch();

            // Team
            Bus::batch([
                new SpContentAssignFromTeamJob($masterclass_teamInput, $meditation_teamInput, $webinar_teamInput, $feed_teamInput, $podcast_teamInput, $recipe_teamInput, $teamLocation),
                new SpContentAssignToTeamJob($masterclass_teamInput, $meditation_teamInput, $webinar_teamInput, $feed_teamInput, $podcast_teamInput, $recipe_teamInput, $teamLocation)
            ])->onQueue('default')->dispatch();
        }

        $moderatorsData = [];
        if (!empty($payload['first_name'])) {
            foreach ($payload['first_name'] as $index => $firstName) {
                $moderatorsData[$index]['first_name'] = $firstName;
            }
            foreach ($payload['last_name'] as $index => $lastName) {
                $moderatorsData[$index]['last_name'] = $lastName;
            }
            foreach ($payload['email'] as $index => $email) {
                $moderatorsData[$index]['email'] = $email;
            }

            foreach ($payload['id'] as $index => $logo) {
                $moderatorsData[$index]['moderator_id'] = $logo;
            }
            $moderatorsIds = [];
            foreach ($moderatorsData as $moderatorValue) {
                $moderatorId = $moderatorValue['moderator_id'];
                $findId      = strpos($moderatorId, "id");
                if ($findId === false) {
                    $moderatorInput = [
                        'first_name' => (!empty($moderatorValue['first_name']) ? $moderatorValue['first_name'] : null),
                        'last_name'  => (!empty($moderatorValue['last_name']) ? $moderatorValue['last_name'] : null),
                    ];
                    
                    User::where('id', $moderatorId)->update($moderatorInput);
                    array_push($moderatorsIds, $moderatorId);
                } else {
                    if (($moderatorValue['first_name']) && !empty($moderatorValue['last_name']) && !empty($moderatorValue['email'])) {
                        $moderatorInput = [
                            'first_name'     => (!empty($moderatorValue['first_name']) ? $moderatorValue['first_name'] : null),
                            'last_name'      => (!empty($moderatorValue['last_name']) ? $moderatorValue['last_name'] : null),
                            'email'          => (!empty($moderatorValue['email']) ? $moderatorValue['email'] : null),
                            'last_login_at'  => now()->toDateTimeString(),
                            'is_premium'     => true,
                            'can_access_app' => false,
                            'start_date'     => $payload['subscription_start_date'],
                        ];
                        $user = User::create($moderatorInput);
                        $this->moderators()->attach($user);
                        array_push($moderatorsIds, $user->id);

                        if (isset($payload['logo']) && !empty($payload['logo'])) {
                            $name = $user->id . '_' . \time();
                            $user
                                ->clearMediaCollection('logo')
                                ->addMediaFromRequest('logo')
                                ->usingName($payload['logo']->getClientOriginalName())
                                ->usingFileName($name . '.' . $payload['logo']->extension())
                                ->toMediaCollection('logo', config('medialibrary.disk_name'));
                        }

                        $roleSlug = (($this->is_reseller) ? 'reseller_super_admin' : ((!$this->is_reseller && !is_null($this->parent_id)) ? 'reseller_company_admin' : 'company_admin'));
                        $role     = Role::where('slug', $roleSlug)->first();
                        $user->roles()->attach($role);

                        // save user profile
                        $user->profile()->create([
                            'gender'     => 'male',
                            'height'     => '100',
                            'birth_date' => '1990-01-01',
                        ]);

                        // save user weight
                        $user->weights()->create([
                            'weight'   => '50',
                            'log_date' => now()->toDateTimeString(),
                        ]);

                        // calculate bmi and store
                        $bmi = 50 / pow((100 / 100), 2);

                        $user->bmis()->create([
                            'bmi'      => $bmi,
                            'weight'   => 50, // kg
                            'height'   => 100, // cm
                            'age'      => 0,
                            'log_date' => now()->toDateTimeString(),
                        ]);

                        $userGoalData             = array();
                        $userGoalData['steps']    = 6000;
                        $userGoalData['calories'] = 2500;
                        // create or update user goal
                        $userId = $user->getKey();
                        $user->goal()->updateOrCreate(['user_id' => $userId], $userGoalData);
                    
                        // add category expertise level
                        $categories = Category::get();

                        if (!empty($categories)) {
                            foreach ($categories as $key => $category) {
                                $user->expertiseLevels()->attach($category, ['expertise_level' => 'beginner']);
                            }
                        }

                        $defaultDept = $this->departments()->where(['default' => true])->first();

                        // create default team for company
                        $defaultTeam = $defaultDept->teams()->where(['default' => true])->first();

                        // attach default department and team with user
                        $user->teams()->attach($defaultTeam, ['company_id' => $this->id, 'department_id' => $defaultDept->id]);

                        // set true flag in all notification modules
                        $notificationModules = config('zevolifesettings.notificationModules');
                        if (!empty($notificationModules)) {
                            foreach ($notificationModules as $key => $value) {
                                $user->notificationSettings()->create([
                                    'module' => $key,
                                    'flag'   => $value,
                                ]);
                            }
                        }

                        if ($user) {
                            event(new UserRegisterEvent($user, 'moderator'));
                        }
                    }
                }
                $deletedIds     = CompanyModerator::whereNotIn('user_id', $moderatorsIds)->where('company_id', $this->id)->pluck('id')->toArray();
                $deletedUserIds = CompanyModerator::whereNotIn('user_id', $moderatorsIds)->where('company_id', $this->id)->pluck('user_id')->toArray();
            }
            CompanyModerator::whereIn('id', $deletedIds)->delete();
            User::whereIn('id', $deletedUserIds)->delete();
        }

        if ($updated) {
            return true;
        }

        return false;
    }

    /**
     * fatch record data by record id.
     *
     * @param $id
     * @return record data
     */

    public function getRecordDataById($id)
    {
        return self::find($id);
    }

    /**
     * delete record by record id.
     *
     * @param $id
     * @return array
     */

    public function deleteRecord()
    {
        $user           = auth()->user();
        $role           = getUserRole($user);
        $appTimezone    = config('app.timezone');
        $userTimezone   = (!empty($user->timezone) ? $user->timezone : $appTimezone);

        $query = CronofySchedule::leftJoin('session_group_users', 'session_group_users.session_id', '=', 'cronofy_schedule.id')
            ->join('users as u', 'u.id', '=', 'session_group_users.user_id')
            ->join('users as ws', 'ws.id', '=', 'cronofy_schedule.ws_id')
            ->join('companies', 'companies.id', '=', 'cronofy_schedule.company_id')
            ->join('services', 'services.id', '=', 'cronofy_schedule.service_id')
            ->join('service_sub_categories', 'service_sub_categories.id', '=', 'cronofy_schedule.topic_id')
            ->join('company_locations', 'company_locations.company_id', '=', 'cronofy_schedule.company_id')
            ->join('departments', 'departments.company_id', '=', 'cronofy_schedule.company_id')
            ->select(
                'cronofy_schedule.id',
                'companies.id AS company_id',
                'companies.name AS company_name',
                'company_locations.name as location_name',
                'services.name AS service_name',
                'service_sub_categories.name AS issue',
                'cronofy_schedule.user_id',
                'cronofy_schedule.start_time',
                'cronofy_schedule.ws_id',
                'cronofy_schedule.end_time',
                'cronofy_schedule.created_at',
                'cronofy_schedule.status',
                'cronofy_schedule.is_group',
                'cronofy_schedule.no_show',
                \DB::raw("(SELECT CONCAT(users.first_name, ' ', users.last_name) as wellbeing_specialist FROM users WHERE cronofy_schedule.ws_id = users.id) AS wellbeing_specialist_name"),
                \DB::raw("(SELECT
                COUNT(session_group_users.id) from session_group_users 
                WHERE session_group_users.session_id = cronofy_schedule.id)
                AS sc_count"),
                \DB::raw("TIMESTAMPDIFF(MINUTE,cronofy_schedule.start_time,cronofy_schedule.end_time) as duration"),
                'ws.timezone as ws_timezone',
                'cronofy_schedule.notes',
            )
            ->where('cronofy_schedule.company_id', $this->id)
            ->where('cronofy_schedule.status', '!=', 'open')
            ->where('cronofy_schedule.start_time', '!=', '0000-00-00 00:00:00')
            ->whereNull('u.deleted_at')
            ->whereNull('ws.deleted_at');

        if ($role->slug == 'super_admin' || $role->slug == 'reseller_super_admin') {
                $query->addSelect(DB::raw("COUNT(DISTINCT session_group_users.id) as users_count"));
                $query->addSelect(DB::raw("CONCAT(u.first_name,' ',u.last_name) as session_user_name"));
                $query->addSelect(DB::raw("u.email as session_user_email"));
                $query->havingRaw("sc_count = 1");
        }

        $query->groupBy('cronofy_schedule.id');
        $fetchedRecords =  $query->orderBy('cronofy_schedule.id', 'DESC')->get()->toArray();

        if (!empty($fetchedRecords)) {
            $dateTimeString = Carbon::now()->toDateTimeString();
            $sheetTitle = [
                'Company',
                'Location',
                'Service',
                'Topic',
                'Booking Date',
                'Session Date',
                'Duration',
                'Mode of Service',
                'Wellbeing Specialist',
                'User Name',
                'User Email',
                'Session Notes'
            ];
            $fileName    = "Digital_thrapy_" . $dateTimeString . '.xlsx';
            $spreadsheet = new Spreadsheet();
            $sheet       = $spreadsheet->getActiveSheet();
            $sheet->fromArray($sheetTitle, null, 'A1');
            $dtData = [];
            foreach ($fetchedRecords as $value) {
                $company['company_name'] = $value['company_name'];
                
                $scheduleUser = ScheduleUsers::where('session_id', $value['id'])->get();
                $mode                  = "";
                $companyDigitalTherapy = CompanyDigitalTherapy::where('company_id', $value['company_id'])->first();
                if ($companyDigitalTherapy->dt_is_online) {
                    $mode = 'Online';
                } elseif ($companyDigitalTherapy->dt_is_onsite) {
                    $mode = 'Onsite';
                }
                $shift  = config('zevolifesettings.shift');
                $wsUser = WsUser::where('user_id', $value['ws_id'])->first();
                
                if($value['is_group'] == 1 && count($scheduleUser) > 1){
                    $company['location_name']  = $value['location_name'];
                } else {
                    $userId = $value['user_id'];
                    if (!empty($scheduleUser) && count($scheduleUser) > 0) {
                        $userId = $scheduleUser[0]->user_id;
                    }
                    $location = User::select('company_locations.name as loc', 'departments.name as dept')->leftJoin('user_team', function ($join) {
                        $join->on('user_team.user_id', '=', 'users.id');
                    })
                    ->leftJoin('companies', function ($join) {
                        $join
                            ->on('users.id', '=', 'user_team.user_id')
                            ->on('user_team.company_id', '=', 'companies.id');
                    })
                    ->leftJoin("team_location", function ($join) {
                        $join->on("team_location.team_id", "=", "user_team.team_id");
                    })
                    ->leftJoin("company_locations", function ($join) {
                        $join->on("company_locations.id", "=", "team_location.company_location_id");
                    })->leftJoin("departments", function ($join) {
                        $join
                            ->on('user_team.user_id', '=', 'users.id')
                            ->on('user_team.department_id', '=', 'departments.id');
                    })
                    ->where('users.id', $userId)->first();
                    $company['location_name']   = $location->loc ?? $value['location_name'];
                }
                
                $company['service_name'] = $value['service_name'];
                $company['issue']        = $value['issue'];
                $company['booking_date'] = Carbon::parse($value['created_at'])->setTimezone($userTimezone)->format('M d, Y, h:i A');
                $company['session_date'] = Carbon::parse($value['start_time'])->setTimezone($userTimezone)->format('M d, Y, h:i A');
                $company['duration']                    = $value['duration'];
                $company['mode_of_service']             = $mode;
                $company['wellbeing_specialist_name']   = $value['wellbeing_specialist_name'];

                if (!$value['is_group']) {
                    if(!empty($value['user'])) {
                        $userFullName = $value['user']['first_name'] . ' ' . $value['user']['last_name'];
                        $userEmail    = $value['user']['email'];
                    } else {
                        $userFullName = $value['session_user_name'];
                        $userEmail    = $value['session_user_email'];
                    }
                } elseif ($value['is_group'] && $value['users_count'] == 1) {
                    $userFullName = $value['session_user_name'];
                    $userEmail    = $value['session_user_email'];
                }
                $company['session_user_name']  = $userFullName;
                $company['session_user_email'] = $userEmail;
                $sessionNotes    =  htmlspecialchars_decode(strip_tags($value['notes']));

                $company['session_notes']   = str_replace(array("\n", "\r"), ' ', $sessionNotes);
                $dtData[]                   = $company;
            }
            $sheet->fromArray($dtData, null, 'A2');

            $writer    = new Xlsx($spreadsheet);
            $temp_file = tempnam(sys_get_temp_dir(), $fileName);
            $source    = fopen($temp_file, 'rb');
            $writer->save($temp_file);

            $root       = config("filesystems.disks.spaces.root");
            $foldername = config('zevolifesettings.excelfolderpath');

            $uploaded = uploadFileToSpaces($source, "{$root}/{$foldername}/{$fileName}", "public");
            if (null != $uploaded && is_string($uploaded->get('ObjectURL'))) {
                $url      = $uploaded->get('ObjectURL');
                $uploaded = true;
            }
            event(new CompanyArchivedEvent($this, $user, $url, $fileName));
        }

        if ($this->is_reseller) {
            $childCompanies = $this->childCompanies()->select('id')->get();
            if ($childCompanies->isNotEmpty()) {
                $childCompanies->each(function ($company) {
                    // update assigned survey status to 'Published' if survey isn't usded by other companies
                    $surveySettings = $company->survey()->select('id', 'survey_id')->first();
                    if (!is_null($surveySettings)) {
                        $survey         = ZcSurvey::select('id')->find($surveySettings->survey_id);
                        $assignedcounts = $survey->surveycompany()->where('company_id', '!=', $this->id)->count();
                        if ($assignedcounts == 0 || is_null($assignedcounts)) {
                            $survey->status = 'Published';
                            $survey->save();
                        }
                    }
                    // delete moderators of the company
                    $company->moderators()->delete();
                    // delete members of the company
                    $company->members()->delete();
                });
            }
        }

        // update assigned survey status to 'Published' if survey isn't usded by other companies
        $surveySettings = $this->survey()->select('id', 'survey_id')->first();
        if (!is_null($surveySettings)) {
            $survey         = ZcSurvey::select('id')->find($surveySettings->survey_id);
            $assignedcounts = $survey->surveycompany()->where('company_id', '!=', $this->id)->count();
            if ($assignedcounts == 0 || is_null($assignedcounts)) {
                $survey->status = 'Published';
                $survey->save();
            }
        }

        // delete moderators of the company
        $this->moderators()->delete();
        // delete members of the company
        $this->members()->delete();
        if ($this->delete()) {
           return array('deleted' => 'true');
        }
        return array('deleted' => 'false');
    }

    /**
     * fatch record data by record id.
     *
     * @param $id
     * @return record data
     */

    public function getDefaultLocation()
    {
        return $this->locations()->where('default', true)->first();
    }

    /**
     * Set datatable for record list.
     *
     * @param payload
     * @return dataTable
     */

    public function getTeamsTableData($payload)
    {
        $list = $this->teams()->get();
        return DataTables::of($list)->make(true);
    }

    /**
     * store record data.
     *
     * @param payload
     * @return boolean
     */

    public function createModerator($payload)
    {
        // create first company moderator
        $user = User::create([
            'first_name'     => $payload['first_name'],
            'last_name'      => $payload['last_name'],
            'email'          => $payload['email'],
            'last_login_at'  => now()->toDateTimeString(),
            'is_premium'     => true,
            'can_access_app' => false,
            'start_date'     => $this->subscription_start_date,
        ]);

        $this->moderators()->attach($user);

        if (isset($payload['logo']) && !empty($payload['logo'])) {
            $name = $user->id . '_' . \time();
            $user
                ->clearMediaCollection('logo')
                ->addMediaFromRequest('logo')
                ->usingName($payload['logo']->getClientOriginalName())
                ->usingFileName($name . '.' . $payload['logo']->extension())
                ->toMediaCollection('logo', config('medialibrary.disk_name'));
        }

        $roleSlug = (($this->is_reseller) ? 'reseller_super_admin' : ((!$this->is_reseller && !is_null($this->parent_id)) ? 'reseller_company_admin' : 'company_admin'));
        $role     = Role::where('slug', $roleSlug)->first();
        $user->roles()->attach($role);

        // save user profile
        $user->profile()->create([
            'gender'     => 'male',
            'height'     => '100',
            'birth_date' => '1990-01-01',
        ]);

        // save user weight
        $user->weights()->create([
            'weight'   => '50',
            'log_date' => now()->toDateTimeString(),
        ]);

        // calculate bmi and store
        $bmi = 50 / pow((100 / 100), 2);

        $user->bmis()->create([
            'bmi'      => $bmi,
            'weight'   => 50, // kg
            'height'   => 100, // cm
            'age'      => 0,
            'log_date' => now()->toDateTimeString(),
        ]);

        $userGoalData             = array();
        $userGoalData['steps']    = 6000;
        $userGoalData['calories'] = 2500;
        // create or update user goal
        $user->goal()->updateOrCreate(['user_id' => $user->getKey()], $userGoalData);

        // add category expertise level
        $categories = Category::get();

        if (!empty($categories)) {
            foreach ($categories as $key => $category) {
                $user->expertiseLevels()->attach($category, ['expertise_level' => 'beginner']);
            }
        }

        $defaultDept = $this->departments()->where(['default' => true])->first();

        // create default team for company
        $defaultTeam = $defaultDept->teams()->where(['default' => true])->first();

        // attach default department and team with user
        $user->teams()->attach($defaultTeam, ['company_id' => $this->id, 'department_id' => $defaultDept->id]);

        $role = Role::where('slug', 'user')->first();
        $user->roles()->attach($role);

        // set true flag in all notification modules
        $notificationModules = config('zevolifesettings.notificationModules');
        if (!empty($notificationModules)) {
            foreach ($notificationModules as $key => $value) {
                $user->notificationSettings()->create([
                    'module' => $key,
                    'flag'   => $value,
                ]);
            }
        }

        if ($user) {
            event(new UserRegisterEvent($user, 'moderator'));
        }

        return true;
    }

    /**
     * Set datatable for record list.
     *
     * @param payload
     * @return dataTable
     */

    public function getModeratorsTableData($payload)
    {
        $list = $this->moderators()
            ->select('users.id', 'users.first_name', 'users.last_name', 'users.email')
            ->orderByDesc('company_moderator.updated_at');

        if (in_array('recordName', array_keys($payload)) && !empty($payload['recordName'])) {
            $list->where(DB::raw("CONCAT(users.first_name,' ',users.last_name)"), 'like', '%' . $payload['recordName'] . '%');
        }
        if (in_array('recordEmail', array_keys($payload)) && !empty($payload['recordEmail'])) {
            $list->where('users.email', 'like', '%' . $payload['recordEmail'] . '%');
        }

        $list = $list->get();

        return DataTables::of($list)
            ->addColumn('updated_at', function ($record) {
                return $record->updated_at;
            })
            ->make(true);
    }

    /**
     * Set datatable for record list.
     *
     * @param payload
     * @return dataTable
     */
    public function getLimitsTableData($payload)
    {
        if ($payload['type'] == 'challenge') {
            $hideTargetTypesCompanySettings = config('zevolifesettings.hide_target_types_company_settings');
            $list                           = $this->limits()->whereNotIn('type', $hideTargetTypesCompanySettings)->orderBy('updated_at', 'DESC')->get();
            $challenge_targets              = ChallengeTarget::where("is_excluded", 0)->pluck('name', 'short_name')->toArray();
            return DataTables::of($list)
                ->addColumn('type', function ($record) use ($challenge_targets) {
                    return ((array_key_exists($record->type, $challenge_targets)) ? $challenge_targets[$record->type] : $record->type);
                })
                ->addColumn('value', function ($record) {
                    return $record->value . " " . $record->uom;
                })
                ->make(true);
        } elseif ($payload['type'] == 'reward-point') {
            $list      = $this->companyWisePointsSetting()->orderBy('updated_at', 'DESC')->get();
            $limitText = config('zevolifesettings.portal_limits');
            return DataTables::of($list)
                ->addColumn('type', function ($record) use ($limitText) {
                    return $limitText[$record->type] ?? $record->type;
                })
                ->addColumn('value', function ($record) {
                    return $record->value . " Points";
                })
                ->make(true);
        } elseif ($payload['type'] == 'reward-daily-limit') {
            $list      = $this->companyWisePointsDailyLimit()->orderBy('updated_at', 'DESC')->get();
            $limitText = config('zevolifesettings.reward_point_labels');
            return DataTables::of($list)
                ->addColumn('type', function ($record) use ($limitText) {
                    return $limitText[$record->type] ?? $record->type;
                })
                ->addColumn('value', function ($record) {
                    return $record->value . " Per day";
                })
                ->make(true);
        }
    }

    /**
     * update limits of challenge/reward points/reward points daily limit
     *
     * @param payload
     * @return boolean
     */
    public function updateLimits($payload)
    {
        if ($payload['type'] == "challenge" && $this->allow_app) {
            $uomData                = $payload['uom'];
            $defaultchallengeLimits = config('zevolifesettings.default_limits');
            $challengeLimits        = [
                'steps'                  => ($payload['steps'] ?? $defaultchallengeLimits['steps']),
                'distance'               => ($payload['distance'] ?? $defaultchallengeLimits['distance']),
                'exercises_distance'     => ($payload['exercises_distance'] ?? $defaultchallengeLimits['exercises_distance']),
                'exercises_duration'     => ($payload['exercises_duration'] ?? $defaultchallengeLimits['exercises_duration']),
                'meditations'            => ($payload['meditations'] ?? $defaultchallengeLimits['meditations']),
                'daily_meditation_limit' => ($payload['daily_meditation_limit'] ?? $defaultchallengeLimits['daily_meditation_limit']),
                'daily_track_limit'      => ($payload['daily_track_limit'] ?? $defaultchallengeLimits['daily_track_limit']),
                'content'                => ($payload['content'] ?? $defaultchallengeLimits['content']),
                'daily_podcast_limit'    => ($payload['daily_podcast_limit'] ?? $defaultchallengeLimits['daily_podcast_limit']),
            ];

            foreach ($challengeLimits as $key => $value) {
                $this->companyWiseChallengeSett()->updateOrCreate([
                    'company_id' => $this->id,
                    'type'       => $key,
                ], [
                    'value' => $value,
                    'uom'   => $uomData[$key],
                ]);
            }
        }

        if ($payload['type'] == "reward" && ($this->is_reseller || !is_null($this->parent_id))) {
            $defaultportalPointsLimits = config('zevolifesettings.default_portal_limits');
            $portalPointsLimits        = [
                "audit_survey" => ($payload['audit_survey'] ?? $defaultportalPointsLimits['audit_survey']),
                "masterclass"  => ($payload['masterclass'] ?? $defaultportalPointsLimits['masterclass']),
                "meditation"   => ($payload['meditation'] ?? $defaultportalPointsLimits['meditation']),
                "feed"         => ($payload['feed'] ?? $defaultportalPointsLimits['feed']),
                "webinar"      => ($payload['webinar'] ?? $defaultportalPointsLimits['webinar']),
                "recipe"       => ($payload['recipe'] ?? $defaultportalPointsLimits['recipe']),
            ];

            foreach ($portalPointsLimits as $key => $value) {
                $this->companyWisePointsSetting()->updateOrCreate([
                    'company_id' => $this->id,
                    'type'       => $key,
                ], [
                    'value' => $value,
                ]);
            }
        }

        if ($payload['type'] == "reward-daily-limit" && ($this->is_reseller || !is_null($this->parent_id))) {
            $defaultRewardPointDailyLimit = config('zevolifesettings.reward_point_daily_limit');
            $rewardPointsDailyLimits      = [
                "meditation" => ($payload['dailylimit_meditation'] ?? $defaultRewardPointDailyLimit['meditation']),
                "feed"       => ($payload['dailylimit_feed'] ?? $defaultRewardPointDailyLimit['feed']),
                "webinar"    => ($payload['dailylimit_webinar'] ?? $defaultRewardPointDailyLimit['webinar']),
                "recipe"     => ($payload['dailylimit_recipe'] ?? $defaultRewardPointDailyLimit['recipe']),
            ];

            foreach ($rewardPointsDailyLimits as $key => $value) {
                $this->companyWisePointsDailyLimit()->updateOrCreate([
                    'company_id' => $this->id,
                    'type'       => $key,
                ], [
                    'value' => $value,
                ]);
            }
        }

        return true;
    }

    /**
     * Set default values of challenge/reward points/reward points daily limit
     *
     * @param payload
     * @return dataTable
     */
    public function setDefaultLimits($payload)
    {
        $type = ($payload['type'] ?? "");
        if ($type == "challenge" && $this->allow_app) {
            $defaultchallengeLimits = config('zevolifesettings.default_limits');
            $uomData                = [
                "steps"                       => "Count",
                "distance"                    => "Meter",
                "exercises_distance"          => "Meter",
                "exercises_duration"          => "Minutes",
                "meditations"                 => "Count",
                "daily_meditation_limit"      => "Count",
                "daily_track_limit"           => "Count",
                "guided_meditation_limit"     => "Count",
                "recent_meditation_limit"     => "Count",
                "most_liked_meditation_limit" => "Count",
                "most_liked_webinar_limit"    => "Count",
                "recent_webinar_limit"        => "Count",
                'content'                     => 'Count',
                'daily_webinar_limit'         => 'Count',
                'daily_podcast_limit'         => 'Count',
            ];

            foreach ($defaultchallengeLimits as $key => $value) {
                $this->companyWiseChallengeSett()->updateOrCreate([
                    'company_id' => $this->id,
                    'type'       => $key,
                ], [
                    'value' => $value,
                    'uom'   => $uomData[$key],
                ]);
            }

            return [
                'data'   => 'Default limit will be used.',
                'status' => 1,
            ];
        }

        if ($type == "reward-point" && ($this->is_reseller || !is_null($this->parent_id))) {
            $defaultportalPointsLimits = config('zevolifesettings.default_portal_limits');
            foreach ($defaultportalPointsLimits as $key => $value) {
                $this->companyWisePointsSetting()->updateOrCreate([
                    'company_id' => $this->id,
                    'type'       => $key,
                ], [
                    'value' => $value,
                ]);
            }

            return [
                'data'   => 'Default limit will be used.',
                'status' => 1,
            ];
        }

        if ($type == "reward-daily-limit" && ($this->is_reseller || !is_null($this->parent_id))) {
            $defaultRewardPointDailyLimit = config('zevolifesettings.reward_point_daily_limit');
            foreach ($defaultRewardPointDailyLimit as $key => $value) {
                if (!empty($value)) {
                    $this->companyWisePointsDailyLimit()->updateOrCreate([
                        'company_id' => $this->id,
                        'type'       => $key,
                    ], [
                        'value' => $value,
                    ]);
                }
            }

            return [
                'data'   => 'Default limit will be used.',
                'status' => 1,
            ];
        }

        return [
            'data'   => trans('labels.common_title.unauthorized_access'),
            'status' => 0,
        ];
    }

    /**
     * fatch record data by record id.
     *
     * @param $id
     * @return record data
     */

    public function getDefaultTeam()
    {
        return $this->teams()->where('default', true)->first();
    }

    /**
     * @param string $size
     * @param string $params
     *
     * @return array
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversion
     */
    public function getMediaData(string $collection, array $param): array
    {
        $return = [
            'width'  => $param['w'],
            'height' => $param['h'],
        ];
        $media = $this->getFirstMedia($collection);

        if (!is_null($media) && $media->count() > 1) {
            $param['src'] = $this->getFirstMediaUrl($collection);
        }
        $return['url'] = getThumbURL($param, 'company', $collection);
        return $return;
    }

    public function triggerZcSurvey()
    {
        $appTimeZone     = config('app.timezone');
        $nowInUTC        = now($appTimeZone);
        $currentDate     = now($this->timezone)->toDateString();
        $currentDateTime = "{$currentDate} {$this->survey_roll_out_time}";
        $zcSurvey        = ZcSurvey::find($this->survey_id, ['id', 'title']);
        $expireDate      = Carbon::parse($currentDateTime, $this->timezone)
            ->addDays(config('zevolifesettings.survey_frequency_day.' . $this->survey_frequency))
            ->setTimezone($appTimeZone)
            ->toDateTimeString();
        $rollOutDate = Carbon::parse($currentDateTime, $this->timezone)
            ->setTimezone($appTimeZone)
            ->toDateTimeString();

        $surveyLog = ZcSurveyLog::create([
            "company_id"    => $this->id,
            "survey_id"     => $this->survey_id,
            "roll_out_date" => $rollOutDate,
            "roll_out_time" => $this->survey_roll_out_time,
            "expire_date"   => $expireDate,
            "survey_to_all" => $this->survey_to_all,
        ]);

        // if survey_to_all flag is set to true means send survey to all the company users
        if ($this->survey_to_all) {
            $users = User::select('users.id', 'users.email', 'users.timezone', 'users.can_access_app', 'users.can_access_portal')
                ->join("user_team", "user_team.user_id", "=", "users.id")
                ->where("user_team.company_id", $this->id)
                ->where("users.is_blocked", false)
                ->where("users.start_date", '<=', $nowInUTC->toDateString())
                ->get();
        } else {
            // survey_to_all flag is set to false means send survey to selected users only
            $users = $this->surveyUsers()
                ->select('users.id', 'users.email', 'users.timezone', 'users.can_access_app', 'users.can_access_portal')
                ->where("users.is_blocked", false)
                ->where("users.start_date", '<=', $nowInUTC->toDateString())
                ->get();
        }

        $data = [
            'logo'        => asset('assets/dist/img/zevo-white-logo.png'),
            'sub_domain'  => "",
            'surveyLogId' => $surveyLog->id,
            'companyName' => $this->name,
        ];

        if ($this->is_branding) {
            $brandingData       = getBrandingData($this->id);
            $data['logo']       = $brandingData->company_logo;
            $data['sub_domain'] = $brandingData->sub_domain;
        }

        $surveyUserLog = [];
        foreach ($users as $user) {
            $surveyUserLog[] = [
                'survey_log_id' => $surveyLog->id,
                'user_id'       => $user->id,
            ];

            // Check Company plan restriction
            $checkAuditSurveyAccess = getCompanyPlanAccess($user, 'audit-survey');

            if ($checkAuditSurveyAccess) {
                if ($this->zcsurvey_on_email) {
                    // Send survey email to users
                    event(new SendZCUserSurveyEvent($user, $data));
                }

                // send push notification to user for audit survey
                \dispatch(new SendGeneralPushNotification($user, 'audit-survey', [
                    'push'          => true,
                    'type'          => 'Auto',
                    'scheduled_at'  => Carbon::parse($currentDateTime, $user->timezone)->setTimezone($appTimeZone)->todatetimeString(),
                    'surveyName'    => $zcSurvey->title,
                    'survey_log_id' => $surveyLog->id,
                ]));
            }
        }

        $surveyLog->surveyUserLogs()->createMany($surveyUserLog);
    }

    public function resellerDetails($payload)
    {
        $data        = ['roles' => [], 'subscription' => [], 'branding' => [], 'brandingContactDetails' => []];
        $is_reseller = (!empty($payload['is_reseller']) ? $payload['is_reseller'] : 'no');
        $company     = (!empty($payload['company']) ? $payload['company'] : 'zevo');
        $roles       = [];

        if ($is_reseller == "yes") {
            $roles   = Role::where(['group' => 'reseller', 'default' => 0])->get()->pluck('name', 'id')->toArray();
            $rsaRole = Role::where(['slug' => 'reseller_super_admin', 'default' => 1])->first();

            $roles = array_replace([$rsaRole->id => $rsaRole->name], $roles);
        } else {
            if ($company == "zevo") {
                $roles = Role::where(['group' => 'company', 'default' => 0])->get()->pluck('name', 'id')->toArray();
            } else {
                $company = $this->findOrFail($company);

                // roles
                $roles = CompanyRoles::select('roles.name', 'roles.id')
                    ->join('roles', function ($join) {
                        $join->on('roles.id', '=', 'company_roles.role_id');
                    })
                    ->where('company_id', $company->getKey())
                    ->get()
                    ->pluck('name', 'id')
                    ->toArray();
                $rcaRole = Role::where(['slug' => 'reseller_company_admin', 'default' => 1])->first();
                $roles   = array_replace([$rcaRole->id => $rcaRole->name], $roles);

                // subscription
                $now                  = now();
                $data['subscription'] = [
                    'start_date'    => Carbon::parse($company->subscription_start_date)->toDateString(),
                    'end_date'      => Carbon::parse($company->subscription_end_date)->toDateString(),
                    'company_plan'  => $company->companyplan()->pluck('cp_plan.id')->first(),
                    'enable_survey' => $company->enable_survey,
                ];

                // branding
                $branding = $company->branding()
                    ->select('id', 'onboarding_description', 'onboarding_title', 'portal_domain', 'sub_domain', 'portal_title', 'portal_description', 'portal_sub_description', 'portal_theme', 'terms_url', 'privacy_policy_url', 'exclude_gender_and_dob', 'manage_the_design_change', 'dt_title', 'dt_description', 'appointment_title', 'appointment_description')
                    ->first();
                if (!empty($branding)) {
                    $data['branding']           = $branding->toArray();
                    $branding_logo              = $company->getFirstMedia('branding_logo');
                    $branding_login_background  = $company->getFirstMedia('branding_login_background');
                    $portal_logo_main           = $company->getFirstMedia('portal_logo_main');
                    $portal_logo_optional       = $company->getFirstMedia('portal_logo_optional');
                    $portal_background_image    = $company->getFirstMedia('portal_background_image');
                    $portal_footer_logo         = $company->getFirstMedia('portal_footer_logo');
                    $portal_favicon_icon        = $company->getFirstMedia('portal_favicon_icon');
                    $portal_homepage_logo_left  = $company->getFirstMedia('portal_homepage_logo_left');
                    $portal_homepage_logo_right = $company->getFirstMedia('portal_homepage_logo_right');
                    $appointment_image          = $company->getFirstMedia('appointment_image');
        
                    if (!empty($branding_logo)) {
                        $data['branding']['branding_logo'] = [
                            'url'  => $company->getBrandingLogo(['w' => 250, 'h' => 50], false),
                            'name' => $branding_logo->name,
                        ];
                    }
                    if (!empty($branding_login_background)) {
                        $data['branding']['branding_login_background'] = [
                            'url'  => $company->getBrandingLoginBackgroundLogo(['w' => 1920, 'h' => 1280], false),
                            'name' => $branding_login_background->name,
                        ];
                    }
                    if (!empty($portal_logo_main)) {
                        $data['branding']['portal_logo_main'] = [
                            'url'  => $company->getPortalLogoMain(['w' => 200, 'h' => 100], false),
                            'name' => $portal_logo_main->name,
                        ];
                    }
                    if (!empty($portal_logo_optional)) {
                        $data['branding']['portal_logo_optional'] = [
                            'url'  => $company->getPortalLogoOptional(['w' => 250, 'h' => 100], false),
                            'name' => $portal_logo_optional->name,
                        ];
                    }
                    if (!empty($portal_background_image)) {
                        $data['branding']['portal_background_image'] = [
                            'url'  => $company->getPortalBackgroundImage(['w' => 1350, 'h' => 900], false),
                            'name' => $portal_background_image->name,
                        ];
                    }
                    if (!empty($portal_footer_logo)) {
                        $data['branding']['portal_footer_logo'] = [
                            'url'  => $company->getPortalLogoMain(['w' => 200, 'h' => 100], false),
                            'name' => $portal_footer_logo->name,
                        ];
                    }
                    if (!empty($portal_favicon_icon)) {
                        $data['branding']['portal_favicon_icon'] = [
                            'url'  => $company->getPortalFaviconIcon(['w' => 40, 'h' => 40], false),
                            'name' => $portal_favicon_icon->name,
                        ];
                    }
                    if (!empty($portal_homepage_logo_left)) {
                        $data['branding']['portal_homepage_logo_left'] = [
                            'url'  => $company->getPortalHomepageLogoLeft(['w' => 200, 'h' => 100], false),
                            'name' => $portal_homepage_logo_left->name,
                        ];
                    }
                    if (!empty($portal_homepage_logo_right)) {
                        $data['branding']['portal_homepage_logo_right'] = [
                            'url'  => $company->getPortalHomepageLogoRight(['w' => 250, 'h' => 100], false),
                            'name' => $portal_homepage_logo_right->name,
                        ];
                    }
                    if (!empty($appointment_image)) {
                        $data['branding']['appointment_image'] = [
                            'url'  => $company->getAppointmentImage(['w' => 800, 'h' => 800], false),
                            'name' => $appointment_image->name,
                        ];
                    }
                }

                $data['selectedContent'] = $this->getAllSeletedParentResellerData($company);
                $data['hide_content']    = ($company->hide_content) ? true : false;

                // Get the contact us data for portal branding
                $brandingContactDetails = $company->brandingContactDetails()->first();
                if(!empty($brandingContactDetails)){
                    $data['brandingContactDetails'] = $brandingContactDetails->toArray();

                    $contact_us_image           = $brandingContactDetails->getFirstMedia('contact_us_image');
                    if (!empty($contact_us_image)) {
                        $data['brandingContactDetails']['contact_us_image'] = [
                            'url'  => $brandingContactDetails->getContactUsImage(['w' => 800, 'h' => 800], false),
                            'name' => $contact_us_image->name,
                        ];
                    }
                }
            }

            
        }

        foreach ($roles as $key => $role) {
            $data['roles'][] = [
                'id'   => $key,
                'name' => $role,
            ];
        }

        return $data;
    }

    /**
     * To get survey details of company
     *
     * @param String $type
     * @return array
     **/
    public function getSurveyDetails($type)
    {
        $user        = auth()->user();
        $appTimeZone = config('app.timezone');
        $timezone    = (!empty($user->timezone) ? $user->timezone : config('app.timezone'));
        $now         = now($timezone);

        // fetch first and last date of survey
        if ($type == "zcsurvey") {
            $firstSurvey = $this->companySurveyLog()->select('id', 'roll_out_date')->orderBy('id')->first();
            $lastSurvey  = $this->companySurveyLog()->select('id', 'expire_date')->orderByDesc('id')->first();

            // convert date to logged in user's timezone
            $startDate = Carbon::parse($firstSurvey->roll_out_date, $appTimeZone)->setTimeZone($timezone);
            $endDate   = Carbon::parse($lastSurvey->expire_date, $appTimeZone)->setTimeZone($timezone);
        } elseif ($type == "masterclass") {
            $firstSurvey = $this->companyMcSurveyLog()->select('id', 'created_at')->orderBy('id')->first();

            // convert date to logged in user's timezone
            $startDate = Carbon::parse($firstSurvey->created_at, $appTimeZone)->setTimeZone($timezone);
            $endDate   = now($appTimeZone)->setTimeZone($timezone);
        }

        // prepare array for return data
        return [
            'status'    => true,
            'email'     => ($user->email ?? ""),
            'startDate' => $startDate->format('Y-m-d'),
            'endDate'   => (($endDate > $now) ? $now->format('Y-m-d') : $endDate->format('Y-m-d')),
        ];
    }

    /**
     * Export survey report as per selected dates and sent to entered email
     *
     * @param array $payload
     * @param String $type
     * @return array
     */
    public function exportSurveyReport($type, $payload)
    {
        $user            = auth()->user();
        $payload['type'] = $type;
        \dispatch(new ZcSurveyReportExportJob($this, $user, $payload));
        return true;
    }

    /**
     * Set team wise limit and enable auto team creation feature
     *
     * @param array $payload
     * @return boolean
     */
    public function updateTeamLimit($payload)
    {
        $user    = auth()->user();
        $updated = $this->update([
            'auto_team_creation' => (isset($payload['auto_team_creation']) ? true : false),
            'team_limit'         => (isset($payload['auto_team_creation']) ? $payload['team_limit'] : null),
        ]);

        if ($updated) {
            // if auto_team_creation is set to true then slplit team as per the limit and move extra users to default team of default department
            if ($this->auto_team_creation) {
                // fetch teams which are having more number of users to compare limit
                $company     = $this;
                $defaultTeam = $this->teams()->select('id', 'name', 'department_id')->where('default', true)->first();
                $teams       = $this->teams()
                    ->select('id', 'name')
                    ->withCount('users')
                    ->where('default', false)
                    ->having('users_count', '>', $this->team_limit)
                    ->get();

                if (!empty($teams)) {
                    $teams->each(function ($team) use ($company, $defaultTeam, $user) {
                        $members      = $team->users()->select('users.id');
                        $take         = ($members->count() - $company->team_limit);
                        $extraMembers = $members
                            ->skip($company->team_limit)
                            ->take($take)
                            ->get();
                        $extraMembersId = $extraMembers->pluck('id')->toArray();

                        // update others members team and department
                        UserTeam::whereIn('user_id', $extraMembersId)
                            ->update([
                                "department_id" => $defaultTeam->department_id,
                                "team_id"       => $defaultTeam->id,
                            ]);

                        // stop telescope entries temporary
                        Telescope::stopRecording();

                        // send team change notification to users
                        \dispatch(new SendTeamChangePushNotificationJob($extraMembers, [
                            'company_id' => $company->id,
                            'user_id'    => $user->id,
                            'new_team'   => $defaultTeam,
                        ]));

                        // auto-generate a new team as current is full and other members are being moved to default
                        $teamMember  = $team->users()->select('users.id')->first();
                        $userTeamObj = UserTeam::where('user_id', $teamMember->id)->first();
                        event('eloquent.created: App\Models\UserTeam', $userTeamObj);
                    });
                }
            }

            return true;
        }

        return false;
    }

    /**
     * Update survey users
     *
     * @param Request $request
     * @return boolean
     */
    public function setSurveyConfiguration($payload)
    {
        if (isset($payload['survey_for_all']) && $payload['survey_for_all'] == 'on') {
            // set survey_to_all flag to true
            $this->survey()->update([
                'survey_to_all' => true,
                'team_ids'      => null,
            ]);

            // remove all users from zc_survey_configs table
            $this->surveyUsers()->detach();

            return true;
        } else {
            // sync selected users
            $this->surveyUsers()->sync($payload['members_selected']);

            // get team id of all the users
            $teamIds = $this->surveyUsers()
                ->select('user_team.team_id')
                ->join('user_team', 'user_team.user_id', '=', 'users.id')
                ->groupBy('user_team.team_id')
                ->get()
                ->pluck('team_id')
                ->toArray();

            // set survey_to_all flag to false
            $this->survey()->update([
                'survey_to_all' => false,
                'team_ids'      => $teamIds,
            ]);

            return true;
        }

        return false;
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
            $subcategoryArray = [];
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
                    $subcategoryArray[] = [
                        'id'              => $subKey,
                        'subcategoryName' => $subValue,
                        'data'            => $result,
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
     * @return string
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversion
     */
    public function getPortalFaviconIconAttribute()
    {
        return $this->getPortalFaviconIcon(['w' => 40, 'h' => 40]);
    }

    /**
     * @return string
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversion
     */
    public function getPortalFaviconIconNameAttribute()
    {
        $portalFaviconIcon = $this->getFirstMedia('portal_favicon_icon');
        return !empty($portalFaviconIcon) ? $portalFaviconIcon->name : '';
    }

    /**
     * @param string $params
     *
     * @return string
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversion
     */
    public function getPortalFaviconIcon(array $params): string
    {
        $media = $this->getFirstMedia('portal_favicon_icon');
        if (!is_null($media) && $media->count() > 0) {
            $params['src'] = $this->getFirstMediaUrl('portal_favicon_icon');
        }
        return getThumbURL($params, 'company', 'portal_favicon_icon');
    }

    /**
     * @return string
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversion
     */
    public function getAppointmentImageAttribute()
    {
        return $this->getAppointmentImage(['w' => 800, 'h' => 800]);
    }

    /**
     * @return string
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversion
     */
    public function getAppointmentImageNameAttribute()
    {
        $appointmentImage = $this->getFirstMedia('appointment_image');
        return !empty($appointmentImage) ? $appointmentImage->name : '';
    }

    /**
     * @param string $params
     *
     * @return string
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversion
     */
    public function getAppointmentImage(array $params): string
    {
        $media = $this->getFirstMedia('appointment_image');
        if (!is_null($media) && $media->count() > 0) {
            $params['src'] = $this->getFirstMediaUrl('appointment_image');
        }
        return getThumbURL($params, 'company', 'appointment_image');
    }

    /**
     * Update the slots for locations
     * @param $payload array
     */
    public function saveLocationSlotsTemp($payload)
    {
        // to set hc user day wise slots
        $data = $insertUpdateData = [];
        $wellbeingSP  = "";
        if (isset($payload['wsId'])) {
            $wellbeingSP = implode(',', $payload['wsId']);
        }
        $company          = Company::find($payload['companyId']);
        $insertUpdateData = [
            'company_id'  => $payload['companyId'],
            'day'         => $payload['day'],
            'start_time'  => Carbon::createFromFormat('H:i', $payload['startTime'], $this->timezone)->format('H:i:00'),
            'end_time'    => Carbon::createFromFormat('H:i', $payload['endTime'], $this->timezone)->format('H:i:59'),
            'ws_id'       => $wellbeingSP,
            'location_id' => $payload['locationId'],
        ];

        if (!empty($payload['id']) && isset($payload['from']) && $payload['from'] == 'tempTable') {
            $data = $company->tempDigitalTherapySlots()->where('id', $payload['id'])->get();
            if (!empty($data)) {
                $company->tempDigitalTherapySlots()->where('id', $payload['id'])->update($insertUpdateData);
            }
        }

        if (isset($payload['from']) && $payload['from'] == 'mainTable' && !empty($payload['mainSlots'])) {
            $this->tempDigitalTherapySlots()->insert($insertUpdateData);
        }

        if (empty($payload['id'])) {
            $this->tempDigitalTherapySlots()->insert($insertUpdateData);
            return  DB::getPdo()->lastInsertId();
        }
    }

    /**
     * Get Availability based on company
     * @param $company
     * @param $user
     *
     * @return array
     */
    public function setDTAvailability(User $user, User $wsUser, $setHoursBy = 1, $setAvailabilityBy = 1)
    {
        $digitalTherapySlot = [];
        if ($setHoursBy == 2 && $setAvailabilityBy == 2) {
            // Location - Specific
            $userTeam               = $user->teams()->first();
            $teamLocation           = $userTeam->teamlocation()->first();
            $availabilityTimezone   = $wsUser->timezone;
            $digitalTherapySlot     = $this->digitalTherapySpecificSlots()
                ->select(
                    \DB::raw("CONCAT(DATE_FORMAT(date, '%Y-%m-%d'), ' ', start_time) AS start_time"),
                    \DB::raw("CONCAT(DATE_FORMAT(date, '%Y-%m-%d'), ' ', end_time) AS end_time")
                )
                ->where(\DB::raw("CONCAT(DATE_FORMAT(date, '%Y-%m-%d'), ' ', start_time)"), '>=', Carbon::now()->toDateString())
                ->where('location_id', $teamLocation->id)
                ->where('ws_id', $wsUser->id)
                ->orderBy('date', 'ASC')
                ->distinct()
                ->get()
                ->toArray();
        } elseif ($setHoursBy == 2 && $setAvailabilityBy == 1) {
            // Location - General
            $userTeam     = $user->teams()->first();
            $teamLocation = $userTeam->teamlocation()->first();
            $availabilityTimezone = $wsUser->timezone;
            $digitalTherapySlot   = $this->digitalTherapySlots()
                ->select(
                    'day',
                    'start_time',
                    'end_time'
                )
                ->where('location_id', $teamLocation->id)
                ->whereRaw('find_in_set(?, ws_id)', [$wsUser->id])
                ->distinct()
                ->get()
                ->toArray();
        } elseif ($setHoursBy == 1 && $setAvailabilityBy == 2) {
            // Company - Specific
            $availabilityTimezone = $wsUser->timezone;
            $digitalTherapySlot   = $this->digitalTherapySpecificSlots()
                ->select(
                    \DB::raw("CONCAT(DATE_FORMAT(date, '%Y-%m-%d'), ' ', start_time) AS start_time"),
                    \DB::raw("CONCAT(DATE_FORMAT(date, '%Y-%m-%d'), ' ', end_time) AS end_time")
                )
                ->where(\DB::raw("CONCAT(DATE_FORMAT(date, '%Y-%m-%d'), ' ', start_time)"), '>=', Carbon::now()->toDateString())
                ->whereNull('location_id')
                ->where('ws_id', $wsUser->id)
                ->orderBy('date', 'ASC')
                ->distinct()
                ->get()
                ->toArray();
        } else {
            $availabilityTimezone = $wsUser->timezone;
            $digitalTherapySlot   = $this->digitalTherapySlots()
                ->select(
                    'day',
                    'start_time',
                    'end_time'
                )
                ->whereNull('location_id')
                ->whereRaw('find_in_set(?, ws_id)', [$wsUser->id])
                ->distinct()
                ->get()
                ->toArray();
        }

        return [
            'timezone' => $availabilityTimezone,
            'data'     => $digitalTherapySlot,
        ];
    }

    /**
     * Get Availability based on location
     * @param $company
     * @param $user
     *
     * @return array
     */
    public function setLocationWiseDTAvailability($companyLocation = null, User $wsUser, $setHoursBy = 1, $setAvailabilityBy = 1)
    {
        $digitalTherapySlot = [];
        if ($setHoursBy == 2 && $setAvailabilityBy == 2) {
            // Location - Specific
            $availabilityTimezone = $wsUser->timezone;
            $digitalTherapySlot   = $this->digitalTherapySpecificSlots()
                ->select(
                    \DB::raw("CONCAT(DATE_FORMAT(date, '%Y-%m-%d'), ' ', start_time) AS start_time"),
                    \DB::raw("CONCAT(DATE_FORMAT(date, '%Y-%m-%d'), ' ', end_time) AS end_time")
                )
                ->where(\DB::raw("CONCAT(DATE_FORMAT(date, '%Y-%m-%d'), ' ', start_time)"), '>=', Carbon::now()->toDateString())
                ->where('location_id', $companyLocation)
                ->where('ws_id', $wsUser->id)
                ->orderBy('date', 'DESC')
                ->distinct()
                ->get()
                ->toArray();
        } elseif ($setHoursBy == 2 && $setAvailabilityBy == 1) {
            // Location - General
            $availabilityTimezone = $wsUser->timezone;
            $digitalTherapySlot   = $this->digitalTherapySlots()
                ->select(
                    'day',
                    'start_time',
                    'end_time'
                )
                ->where('location_id', $companyLocation)
                ->whereRaw('find_in_set(?, ws_id)', [$wsUser->id])
                ->distinct()
                ->get()
                ->toArray();
        } elseif ($setHoursBy == 1 && $setAvailabilityBy == 2) {
            // Company - Specific
            $availabilityTimezone = $wsUser->timezone;
            $digitalTherapySlot   = $this->digitalTherapySpecificSlots()
                ->select(
                    \DB::raw("CONCAT(DATE_FORMAT(date, '%Y-%m-%d'), ' ', start_time) AS start_time"),
                    \DB::raw("CONCAT(DATE_FORMAT(date, '%Y-%m-%d'), ' ', end_time) AS end_time")
                )
                ->where(\DB::raw("CONCAT(DATE_FORMAT(date, '%Y-%m-%d'), ' ', start_time)"), '>=', Carbon::now()->toDateString())
                ->whereNull('location_id')
                ->where('ws_id', $wsUser->id)
                ->orderBy('date', 'DESC')
                ->distinct()
                ->get()
                ->toArray();
        } else {
            $availabilityTimezone = $wsUser->timezone;
            $digitalTherapySlot   = $this->digitalTherapySlots()
                ->select(
                    'day',
                    'start_time',
                    'end_time'
                )
                ->whereNull('location_id')
                ->whereRaw('find_in_set(?, ws_id)', [$wsUser->id])
                ->distinct()
                ->get()
                ->toArray();
        }

        return [
            'timezone' => $availabilityTimezone,
            'data'     => $digitalTherapySlot,
        ];
    }

     /**
     * Add default banners when ZSA/RSA create company
     * @param $companyId, $companyType
     *
     * @return boolean
     */
    public function addDefaultBanners($companyId, $companyType)
    {
        $zevoBanners            = config('zevolifesettings.zevo_banners');
        $parentChildBanners     = config('zevolifesettings.portal_banners');
        if ($companyType == 'zevo') {
            foreach($zevoBanners as $zevoBannerData){
                $record = CompanyDigitalTherapyBanner::create([
                    'company_id'          =>  $companyId,
                    'description'         =>  $zevoBannerData['description'],
                    'order_priority'      =>  $zevoBannerData['order']
                ]);
                if (!empty($record)) {
                    $name = $record->id . '_' . \time();
                    $record->clearMediaCollection('banner_image')
                            ->addMediaFromUrl($zevoBannerData['image'])
                            ->usingName($name)
                            ->toMediaCollection('banner_image', config('medialibrary.disk_name'));
                }
            }
        } else {
            foreach ($parentChildBanners as $parentChildBannerData) {
                $record = CompanyDigitalTherapyBanner::create([
                    'company_id'          =>  $companyId,
                    'description'         =>  $parentChildBannerData['description'],
                    'order_priority'      =>  $parentChildBannerData['order']
                ]);
                if (!empty($record)) {
                    $name = $record->id . '_' . \time();
                    $record->clearMediaCollection('banner_image')
                            ->addMediaFromUrl($parentChildBannerData['image'])
                            ->usingName($name)
                            ->toMediaCollection('banner_image', config('medialibrary.disk_name'));
                }
            }
        }
    }
}
