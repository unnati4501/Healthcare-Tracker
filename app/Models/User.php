<?php

namespace App\Models;

use App\Events\AdminRegisterEvent;
use App\Events\UserRegisterEvent;
use App\Http\Traits\UserAccess;
use App\Jobs\ExportNpsReportJob;
use App\Jobs\ExportUserActivityJob;
use App\Jobs\ExportUserRegistrationReportJob;
use App\Jobs\ExportUserTrackerHistoryJob;
use App\Jobs\SendGeneralPushNotification;
use App\Jobs\SendMoodPushNotificationJob;
use App\Jobs\SendTeamChangePushNotification;
use App\Jobs\UsageReportExportJob;
use App\Models\Badge;
use App\Models\Calendly;
use App\Models\CompanyRoles;
use App\Models\CronofySchedule;
use App\Models\Department;
use App\Models\DigitalTherapyService;
use App\Models\EapCsatLogs;
use App\Models\EventBookingLogs;
use App\Models\EventRegisteredUserLog;
use App\Models\Feed;
use App\Models\HealthCoachUser;
use App\Models\MasterclassCsatLogs;
use App\Models\MeditationTrack;
use App\Models\Podcast;
use App\Models\Role;
use App\Models\Team;
use App\Models\UserDeviceHistory;
use App\Models\UserPointLog;
use App\Models\UsersStepsAuthenticatorAvg;
use App\Models\UserTeam;
use App\Models\WsUser;
use App\Models\ZcSurveyResponse;
use App\Models\ZcSurveyReviewSuggestion;
use App\Models\ZdTicket;
use App\Models\Shorts;
use App\Models\EventPresenterSlots;
use App\Models\Group;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Yajra\DataTables\Facades\DataTables;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable implements HasMedia, JWTSubject
{
    use HasApiTokens, Notifiable, InteractsWithMedia, UserAccess;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'timezone',
        'is_timezone',
        'is_coach',
        'is_premium',
        'is_blocked',
        'can_access_app',
        'can_access_portal',
        'email_verified_at',
        'password',
        'remember_token',
        'last_login_at',
        'last_activity_at',
        'step_last_sync_date_time',
        'exercise_last_sync_date_time',
        'hs_show_banner',
        'hs_remind_survey',
        'hs_reminded_at',
        'social_id',
        'social_type',
        'saml_token',
        'start_date',
        'availability_status',
        'consent_privacy',
        'consent_terms_conditions',
        'consent_data',
        'avg_steps',
        'deleted_at',
        'reset_password_count'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'confirm_token',
        'dvreset_token',
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'is_coach'          => 'boolean',
        'is_premium'        => 'boolean',
        'is_blocked'        => 'boolean',
        'can_access_app'    => 'boolean',
        'email_verified_at' => 'datetime',
        'password'          => 'hashed',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['last_login_at', 'last_activity_at', 'email_verified_at', 'step_last_sync_date_time', 'exercise_last_sync_date_time', 'deleted_at'];

    /**
     * @return HasMany
     */
    public function challengeWiseUserLogData(): HasMany
    {
        return $this->hasMany(ChallengeWiseUserLogData::class);
    }

    /**
     * @return HasOne
     */
    public function profile(): HasOne
    {
        return $this->hasOne('App\Models\UserProfile');
    }

    /**
     * @return HasMany
     */
    public function AauthAcessToken(){
        return $this->hasMany('\App\OauthAccessToken');
    }
    /**
     * @return HasOne
     */
    public function wsuser(): HasOne
    {
        return $this->hasOne('App\Models\WsUser');
    }

    /**
     * @return HasOne
     */
    public function healthCoachUser(): HasOne
    {
        return $this->hasOne('App\Models\HealthCoachUser');
    }

    /**
     * @return HasMany
     */
    public function devices(): HasMany
    {
        return $this->hasMany('App\Models\UserDevice');
    }

    /**
     * @return HasMany
     */
    public function cronofyCalendar(): HasMany
    {
        return $this->hasMany('App\Models\CronofyCalendar');
    }

    /**
     * @return HasMany
     */
    public function cronofyAuthenticate(): HasMany
    {
        return $this->hasMany('App\Models\CronofyAuthenticate');
    }

    /**
     * @return BelongsTo
     */
    public function company(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\Company', 'user_team', 'user_id', 'company_id')->withPivot('department_id', 'team_id')->withTimestamps();
    }

    /**
     * @return BelongsTo
     */
    public function department(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\Department', 'user_team', 'user_id', 'department_id')->withPivot('company_id', 'team_id')->withTimestamps();
    }

    /**
     * One-to-Many relations with users services.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function userservices(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\ServiceSubCategory', 'users_services', 'user_id', 'service_id');
    }

    /**
     * @return HasMany
     */
    public function weights(): HasMany
    {
        return $this->hasMany('App\Models\UserWeight');
    }

    /**
     * @return BelongsToMany
     */
    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class, UserTeam::class, 'user_id', 'team_id')
            ->withPivot('company_id', 'department_id')
            ->withTimestamps()
            ->using(UserTeam::class);
    }

    /**
     * @return BelongsToMany
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\Role');
    }

    /**
     * @return HasMany
     */
    public function groups(): HasMany
    {
        return $this->hasMany(Group::class, 'creator_id');
    }

    /**
     * @return BelongsToMany
     */
    public function myGroups(): BelongsToMany
    {
        return $this->belongsToMany(
            Group::class,
            'group_members',
            'user_id',
            'group_id'
        )->withPivot(
            'status',
            'joined_date',
            'left_date',
            'notification_muted'
        )->withTimestamps();
    }

    /**
     * @return BelongsToMany
     */
    public function companyAccess(): BelongsToMany
    {
        return $this->belongsToMany(
            'App\Models\Company',
            'company_moderator',
            'user_id',
            'company_id'
        )->withTimestamps();
    }

    /**
     * @return BelongsToMany
     */
    public function expertiseLevels(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\Category', 'user_expertise_level', 'user_id', 'category_id')->withPivot('expertise_level')->withTimestamps();
    }

    /**
     * @return BelongsToMany
     */
    public function healthCocahExpertise(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\SubCategory', 'health_coach_expertises', 'user_id', 'expertise_id')->withTimestamps();
    }

    /**
     * @return hasMany
     */
    public function healthCocahSlots(): hasMany
    {
        return $this->hasMany('App\Models\HealthCoachSlots', 'user_id', 'id');
    }

    /**
     * @return hasMany
     */
    public function healthCocahAvailability(): hasMany
    {
        return $this->hasMany('App\Models\HealthCoachAvailability', 'user_id', 'id');
    }

    /**
     * @return BelongsToMany
     */
    public function counsellorSkills(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\SubCategory', 'counsellor_skills', 'user_id', 'skill_id')->withTimestamps();
    }

    /**
     * 'hasMany' relation using 'user_point_logs'
     * table via 'user_id' field.
     *
     * @return hasMany
     */
    public function rewardPointLogs(): HasMany
    {
        return $this->hasMany(UserPointLog::class, 'user_id');
    }

    /**
     * 'hasMany' relation using 'courses'
     * table via 'creator_id' field.
     *
     * @return hasMany
     */
    public function masterclassAuthor(): HasMany
    {
        return $this->hasMany(Course::class, 'creator_id');
    }

    /**
     * 'hasMany' relation using 'event_registered_users_logs'
     * table via 'user_id' field.
     *
     * @return hasMany
     */
    public function registeredEvents(): HasMany
    {
        return $this->hasMany(EventRegisteredUserLog::class, 'user_id');
    }

    /**
     * 'hasMany' relation using 'eap_calendly'
     * table via 'user_id' field.
     *
     * @return hasMany
     */
    public function bookedSessions(): HasMany
    {
        return $this->hasMany(Calendly::class, 'user_id');
    }

    /**
     * 'hasMany' relation using 'cronofy_schedule'
     * table via 'user_id' field.
     *
     * @return hasMany
     */
    public function bookedCronofySessions(): HasMany
    {
        return $this->hasMany(CronofySchedule::class, 'user_id');
    }

    /**
     * 'hasMany' relation using 'eap_calendly'
     * table via 'user_id' field.
     *
     * @return hasMany
     */
    public function myWsClients(): HasMany
    {
        return $this->hasMany(CronofySchedule::class, 'ws_id');
    }

    /**
     * 'hasMany' relation using 'eap_calendly'
     * table via 'user_id' field.
     *
     * @return hasMany
     */
    public function myClients(): HasMany
    {
        return $this->hasMany(Calendly::class, 'therapist_id');
    }

    /**
     * @return hasMany
     */
    public function eventPresenterSlots(): hasMany
    {
        return $this->hasMany('App\Models\EventPresenterSlots', 'user_id', 'id');
    }

    /**
     * 'hasMany' relation using 'eap_tickets'
     * table via 'user_id' field.
     *
     * @return hasMany
     */
    public function myZdTickets(): HasMany
    {
        return $this->hasMany(ZdTicket::class, 'user_id');
    }

    /**
     * @param $email
     *
     * @return model
     */
    public static function findByEmail($email)
    {
        return self::where('email', $email)->first();
    }

    /**
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * @return array
     */
    public function getJWTCustomClaims(): array
    {
        return [
            'email' => $this->email,
            'fname' => $this->profile->first_name,
            'lname' => $this->profile->last_name,
        ];
    }

    /**
     * Create a new token for the user.
     *
     * @return string
     */
    public function saveToken($payLoad, array $options = [])
    {
        $token = hash_hmac('sha256', Str::random(40), 'hashKey');

        try {
            DB::table('password_resets')->insert([
                'email'      => $payLoad['email'],
                'token'      => $token,
                'created_at' => Carbon::now(),
            ]);
        } catch (\Exception $exception) {
            return $exception->getMessage();
        }

        return $token;
    }

    /**
     * Specifies the user's FCM token
     *
     * @return string
     */
    public function routeNotificationForFcm()
    {
        return $this->devices()->whereNotNull('device_token')->pluck('device_token')->toArray();
    }

    public function resetPassword($payLoad)
    {
        $user = User::where('email', $payLoad['email'])->first();

        if (!empty($user)) {
            $password_resetData = DB::table('password_resets')->where('token', $payLoad['token'])->where('email', $payLoad['email'])->first();

            if (!empty($password_resetData)) {
                $user->password = bcrypt($payLoad['password']);
                $user->save();
                DB::table('password_resets')->where('email', $payLoad['email'])->delete();
                return ['status' => 'success'];
            } else {
                return ['status' => 'tokenerror'];
            }
        } else {
            return ['status' => 'emailerror'];
        }
    }

    public function updatePassword($user, $input)
    {
        if (\Hash::check($input['current_password'], auth()->user()->password)) {
            $user->password = bcrypt($input['password']);
            $user->save();
            return true;
        } else {
            return false;
        }
    }

    /**
     * Set datatable for record list.
     *
     * @param payload
     * @return dataTable
     */

    public function getTableData($payload)
    {
        $user = auth()->user();
        $role = getUserRole($user);
        $list = $this->getRecordList($payload);
        return DataTables::of($list['record'])
            ->skipPaging()
            ->with([
                "recordsTotal"    => $list['total'],
                "recordsFiltered" => $list['total'],
            ])
            ->addColumn('is_coach', function ($record) {
                return (!empty($record->is_coach) && $record->is_coach == 1) ? 'Yes' : 'No';
            })
            ->addColumn('actions', function ($record) use ($role) {
                if ($record->roleSlug == 'wellbeing_specialist' || $record->roleSlug == 'health_coach') {
                    $wsUser = $record->wsuser()->first();
                    $wcUser = $record->healthCoachUser()->first();
                    return view('admin.user.listaction', compact('record', 'role', 'wsUser', 'wcUser'))->render();
                } else {
                    return view('admin.user.listaction', compact('record', 'role'))->render();
                }
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
        $user    = auth()->user();
        $company = $user->company()->first();
        $role    = getUserRole($user);
        if (!empty($payload['role'])) {
            $selectedRole = Role::find($payload['role']);
        }
        $query   = $this
            ->leftJoin('user_team', function ($join) {
                $join->on('user_team.user_id', '=', 'users.id');
            })
            ->leftJoin('companies', function ($join) {
                $join
                    ->on('users.id', '=', 'user_team.user_id')
                    ->on('user_team.company_id', '=', 'companies.id');
            })
            ->leftJoin('teams', function ($join) {
                $join
                    ->on('users.id', '=', 'user_team.user_id')
                    ->on('user_team.team_id', '=', 'teams.id');
            })
            ->leftJoin('role_user', function ($join) {
                $join->on('role_user.user_id', '=', 'users.id');
            })
            ->leftJoin('roles', function ($join) {
                $join->on('roles.id', '=', 'role_user.role_id');
            });
        if (!empty($selectedRole) && $selectedRole->slug == 'wellbeing_specialist') {
            $query =  $query->leftJoin('ws_user', function ($join) {
                $join->on('users.id', '=', 'ws_user.user_id');
            });

            if ((in_array('wbsStatus', array_keys($payload)) && !empty($payload['wbsStatus']) && $payload['wbsStatus'] == 'unassigned') && 
                (in_array('responsibility', array_keys($payload)) && !empty($payload['responsibility']))) {
                    $query =  $query->leftJoin('event_presenters', function ($join) {
                        $join->on('users.id', '=', 'event_presenters.user_id');
                    });
                    $query =  $query->leftJoin('digital_therapy_services', function ($join) {
                        $join->on('users.id', '=', 'digital_therapy_services.ws_id');
                    });
            }
        }
        $query =    $query->select(
                'users.id',
                'users.email',
                'companies.name as companyName',
                'teams.name as teamName',
                'users.updated_at',
                'users.is_blocked',
                'roles.name AS roleName',
                'roles.slug AS roleSlug',
                DB::raw("CONCAT(users.first_name, ' ', users.last_name) as fullName"),
                DB::raw("(SELECT count(id) FROM user_device_history WHERE user_id = users.id) AS step_sync_count")
            )
            ->where('users.email', '!=', 'superadmin@grr.la')
            ->where('users.id', '!=', $user->id)
            ->whereNull('users.deleted_at')
            ->where(function ($where) use ($role, $company) {
                if ($role->group == 'company') {
                    $where->where('user_team.company_id', '=', $company->id);
                } elseif ($role->group == 'reseller') {
                    $where
                        ->where('user_team.company_id', '=', $company->id)
                        ->orWhere('companies.parent_id', '=', $company->id);
                }
            })
            ->groupBy('users.id');

        if (in_array('recordName', array_keys($payload)) && !empty($payload['recordName'])) {
            $query->whereRaw("CONCAT(users.first_name,' ',users.last_name) like ?", ['%' . $payload['recordName'] . '%']);
        }

        if (in_array('recordEmail', array_keys($payload)) && !empty($payload['recordEmail'])) {
            $query->whereRaw("users.email like ?", ['%' . $payload['recordEmail'] . '%']);
        }

        if (in_array('role', array_keys($payload)) && !empty($payload['role'])) {
            $query->where('roles.id', $payload['role']);
        }

        if (in_array('recordStatus', array_keys($payload)) && !empty($payload['recordStatus']) && $payload['recordStatus'] == 'blocked') {
            $query->where('users.is_blocked', true);
        }

        if (in_array('recordStatus', array_keys($payload)) && !empty($payload['recordStatus']) && $payload['recordStatus'] == 'active') {
            $query->where('users.is_blocked', false);
        }

        if (in_array('company', array_keys($payload)) && !empty($payload['company'])) {
            $query->where('user_team.company_id', $payload['company']);
        }

        if (in_array('team', array_keys($payload)) && !empty($payload['team'])) {
            $query->where('user_team.team_id', $payload['team']);
        }

        if (!empty($selectedRole) && $selectedRole->slug == 'wellbeing_specialist') {
            if (in_array('wbsStatus', array_keys($payload)) && !empty($payload['wbsStatus']) && $payload['wbsStatus'] == 'verified') {
                $query->where('ws_user.is_cronofy', true);
            }
            if (in_array('wbsStatus', array_keys($payload)) && !empty($payload['wbsStatus']) && $payload['wbsStatus'] == 'unverified') {
                $query->where('ws_user.is_cronofy', false);
            }
            if (in_array('responsibility', array_keys($payload)) && !empty($payload['responsibility'])) {
                $query->where('ws_user.responsibilities', $payload['responsibility']);
            }

            if ((in_array('wbsStatus', array_keys($payload)) && !empty($payload['wbsStatus']) && $payload['wbsStatus'] == 'unassigned') && 
            (in_array('responsibility', array_keys($payload)) && !empty($payload['responsibility']))) {
                $query->where('ws_user.is_cronofy', true);
                $query->addSelect(DB::raw("COUNT(DISTINCT digital_therapy_services.id) as dt_assigned_count"));
                $query->addSelect(DB::raw("COUNT(DISTINCT event_presenters.id) as event_assigned_count"));

                if ($payload['responsibility'] == 1) {
                    $query->havingRaw("dt_assigned_count <= 0");
                } elseif($payload['responsibility'] == 2) {
                    $query->havingRaw("event_assigned_count <= 0");
                } else {
                    $query->havingRaw("dt_assigned_count <= 0");
                    $query->orHavingRaw("event_assigned_count <= 0");
                }
            }
        }

        if (isset($payload['order']) && isset($payload['columns']) && in_array($payload['order'][0]['dir'], ['asc','desc']) && is_numeric($payload['columns'][$payload['order'][0]['column']]['data'])) {
            $column = $payload['columns'][$payload['order'][0]['column']]['data'];
            $order  = $payload['order'][0]['dir'];
            $query->orderBy($column, $order);
        } else {
            $query->orderByDesc('users.id');
        }

        $data              = [];
        $data['total']     = $query->get()->count();
        $payload['length'] = (!empty($payload['length']) ? $payload['length'] : config('zevolifesettings.datatable.pagination.short'));
        $payload['length'] = (($payload['length'] == '-1') ? $data['total'] : $payload['length']);
        $data['record']    = $query->offset($payload['start'])->limit($payload['length'])->get();

        return $data;
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
     * @param string $params
     *
     * @return string
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversion
     */
    public function getLogo(array $params): string
    {
        $isSA      = getUserRole($this);
        $isSA      = (($isSA->pivot->role_id == 1) ? 1 : 0);
        $media     = $this->getFirstMedia('logo');
        $gender    = ($this->profile->gender ?? 'male');
        $avatarSeq = random_int(1, 4);
        if (!is_null($media) && $media->count() > 0) {
            $params['src'] = $this->getFirstMediaUrl('logo');
        }

        if (empty($params['src'])) {
            if ($isSA > 0) {
                $params['src'] = config('zevolifesettings.static_image.user.super_admin');
            } else {
                $params['src'] = getDefaultFallbackImageURL("user", "user-$gender$avatarSeq");
            }
        }

        return getThumbURL($params, 'user', "user", "user-$gender$avatarSeq");
    }

    /**
     * @return string
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversion
     */
    public function getCoverImageAttribute()
    {
        return $this->getCoverImage(['w' => 1280, 'h' => 640]);
    }

    /**
     * @param string $params
     *
     * @return string
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversion
     */
    public function getCoverImage(array $params): string
    {
        $media = $this->getFirstMedia('coverImage');
        if (!is_null($media) && $media->count() > 0) {
            $params['src'] = $this->getFirstMediaUrl('coverImage');
        }
        return getThumbURL($params, 'user', 'coverImage');
    }

    /**
     * @return string
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversion
     */
    public function getCounsellorCoverAttribute()
    {
        return $this->getCounsellorCover(['w' => 1280, 'h' => 640]);
    }

    /**
     * @param string $params
     *
     * @return string
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversion
     */
    public function getCounsellorCover(array $params): string
    {
        $media = $this->getFirstMedia('counsellor_cover');
        if (!is_null($media) && $media->count() > 0) {
            $params['src'] = $this->getFirstMediaUrl('counsellor_cover');
        }
        return getThumbURL($params, 'user', 'counsellor_cover');
    }

    /**
     * @param string $collection
     * @param array $param
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

        $isSA = getUserRole($this);
        $isSA = (($isSA->pivot->role_id == 1) ? 1 : 0);

        if (!is_null($media) && $media->count() > 1) {
            $param['src'] = $this->getFirstMediaUrl($collection);
        }
        if ($collection == 'logo') {
            $return['isProfileImageSet'] = false;
        }
        if (empty($param['src'])) {
            if ($collection == 'logo') {
                if ($isSA > 0) {
                    $param['src'] = config('zevolifesettings.static_image.user.super_admin');
                } else {
                    $userProfile                 = UserProfile::where('user_id', $this->id)->first();
                    $gender                      = ($userProfile->gender ?? 'male');
                    $avatarSeq                   = random_int(1, 4);
                    $collection                  = "user-$gender$avatarSeq";
                    $param['src']                = getDefaultFallbackImageURL("user", $collection);
                    $return['isProfileImageSet'] = true;
                }
            } else {
                $param['src'] = getDefaultFallbackImageURL('user', $collection);
            }
        }

        $return['url'] = getThumbURL($param, 'user', $collection);
        if (!empty($param['mI']) && $param['mI'] == 1) {
            unset($return['isProfileImageSet']);
        }
        return $return;
    }

    /**
     * store record data.
     *
     * @param payload
     * @return boolean
     */

    public function storeEntity($payload)
    {
        $user                = auth()->user();
        $userRole            = getUserRole($user);
        $userTimezone        = ((!empty($payload['timezone'])) ? $payload['timezone'] : config('zevolifesettings.default_timezone'));
        $isTimezone          = false;
        $oldMexicoTimezone   = config('zevolifesettings.mexico_city_timezone.old_timezone');
        $newMexicoTimezone   = config('zevolifesettings.mexico_city_timezone.new_timezone');
        $advanceNoticePeriod = 0;

        if (strcasecmp($userTimezone, $oldMexicoTimezone) === 0) {
            $userTimezone = $newMexicoTimezone;
            $isTimezone   = true;
        }
        $record = self::create([
            'first_name'     => $payload['first_name'],
            'last_name'      => $payload['last_name'],
            'email'          => $payload['email'],
            'last_login_at'  => now()->toDateTimeString(),
            'is_coach'       => ((!empty($payload['user_type']) && $payload['user_type'] == 'health_coach') ? true : false),
            'is_premium'     => true,
            'can_access_app' => false,
            'timezone'       => $userTimezone,
            'is_timezone'    => $isTimezone,
        ]);

        if (isset($payload['logo']) && !empty($payload['logo'])) {
            $name = $record->id . '_' . \time();
            $record
                ->clearMediaCollection('logo')
                ->addMediaFromRequest('logo')
                ->usingName($name)
                ->usingFileName($name . '.' . $payload['logo']->extension())
                ->toMediaCollection('logo', config('medialibrary.disk_name'));
        }

        // attach selected role to user
        if (!empty($payload['role'])) {
            $role = Role::find($payload['role']);
            if (!empty($role)) {
                // if role is company admin / reseller company admin then attach user to selected company
                if ((($payload['role_group'] == 'reseller' && $role->slug == 'reseller_company_admin') || ($payload['role_group'] == 'company' && $role->slug == 'company_admin')) && !empty($payload['company'])) {
                    $record->companyAccess()->attach($payload['company']);
                }
                $record->roles()->attach($role);
            }
        } else {
            // attach app user role to new user
            if ($payload['role_group'] == 'zevo' && $payload['user_type'] == 'health_coach') {
                $role = Role::where(['slug' => 'health_coach', 'default' => 1])->first();
            } elseif ($payload['role_group'] == 'zevo' && $payload['user_type'] == 'counsellor') {
                $role = Role::where(['slug' => 'counsellor', 'default' => 1])->first();
            } elseif ($payload['role_group'] == 'zevo' && $payload['user_type'] == 'wellbeing_specialist') {
                $role = Role::where(['slug' => 'wellbeing_specialist', 'default' => 1])->first();
            } elseif ($payload['role_group'] == 'zevo' && $payload['user_type'] == 'wellbeing_team_lead') {
                $role = Role::where(['slug' => 'wellbeing_team_lead', 'default' => 1])->first();
            } else {
                $role = Role::where(['slug' => 'user', 'default' => 1])->first();

                // set app access to true if user type is user
                $userCompany = Company::find($payload['company']);
                $record->update(['can_access_app' => true, 'can_access_portal' => ((!empty($userCompany) && $userCompany->allow_portal) ? $userCompany->allow_portal : false)]);
            }
            $record->roles()->attach($role);
        }

        if ($payload['role_group'] == 'zevo' && $payload['user_type'] == 'counsellor') {
            // Set skills of counsellor users
            $record->counsellorSkills()->sync($payload['counsellor_skills']);

            // Set cover image of counsellor
            if (isset($payload['counsellor_cover']) && !empty($payload['counsellor_cover'])) {
                $name = $record->id . '_' . \time();
                $record
                    ->clearMediaCollection('counsellor_cover')
                    ->addMediaFromRequest('counsellor_cover')
                    ->usingName($payload['counsellor_cover']->getClientOriginalName())
                    ->usingFileName($name . '.' . $payload['counsellor_cover']->extension())
                    ->toMediaCollection('counsellor_cover', config('medialibrary.disk_name'));
            }
        } elseif ($payload['role_group'] == 'zevo' && $payload['user_type'] == 'wellbeing_specialist') {
            // Set cover image of counsellor
            if (isset($payload['counsellor_cover']) && !empty($payload['counsellor_cover'])) {
                $name = $record->id . '_' . \time();
                $record
                    ->clearMediaCollection('counsellor_cover')
                    ->addMediaFromRequest('counsellor_cover')
                    ->usingName($payload['counsellor_cover']->getClientOriginalName())
                    ->usingFileName($name . '.' . $payload['counsellor_cover']->extension())
                    ->toMediaCollection('counsellor_cover', config('medialibrary.disk_name'));
            }

            $record->wsuser()->create([
                'language'            => !empty($payload['language']) ? implode(',', $payload['language']) : '',
                'conferencing_mode'   => $payload['video_conferencing_mode'],
                'video_link'          => $payload['video_link'],
                'shift'               => $payload['shift'],
                'years_of_experience' => $payload['years_of_experience'],
                'responsibilities'    => $payload['responsibilities'],
            ]);

            if (!empty($payload['user_services'])) {
                $record->userservices()->sync($payload['user_services']);
            }

            // Set expertise of wellbeing_specialist
            if (isset($payload['expertise_wbs']) && !empty($payload['expertise_wbs'])) {
                $record->healthCocahExpertise()->sync($payload['expertise_wbs']);
            }
        }

        $team       = (!empty($payload['team'])) ? $payload['team'] : '';
        $department = (!empty($payload['department'])) ? $payload['department'] : '';

        if ($userRole->group == 'company' && !getCompanyPlanAccess($user, 'team-selection')) {
            $team       = 1;
            $department = 1;
        }

        if (($payload['role_group'] == 'company' || $payload['role_group'] == 'reseller') && !empty($payload['company']) && !empty($department) && !empty($team)) {
            // attach team to new user
            $team = Team::select('id', 'company_id', 'default')
                ->withCount('users')
                ->with(['company' => function ($query) {
                    $query->select('id', 'auto_team_creation', 'team_limit');
                }])
                ->find($team);
            if (!$team->default && $team->company->auto_team_creation == true && (($team->users_count + 1) > $team->company->team_limit)) {
                return [
                    'status'  => false,
                    'message' => 'Team limit is reached, Please select another team.',
                ];
            }
            $record->teams()->attach($team, ['company_id' => $payload['company'], 'department_id' => $department]);
        }

        // set coach fields if user is wellbeing specialist
        if ($record->is_coach || ($payload['role_group'] == 'zevo' && $payload['user_type'] == 'wellbeing_specialist')) {
            // set availability of hc user
            if ($record->is_coach || ($payload['role_group'] == 'zevo' && $payload['user_type'] == 'wellbeing_specialist')) {
                $userTimezone = $payload['timezone'];
                $isTimezone   = false;
                if (strcasecmp($userTimezone, $oldMexicoTimezone) === 0) {
                    $userTimezone = $newMexicoTimezone;
                    $isTimezone   = true;
                }
                $record->update([
                    'availability_status' => $payload['availability'],
                    'timezone'            => $userTimezone,
                    'is_timezone'         => $isTimezone,
                ]);

                // Insert into health_coach_user table
                $record->healthCoachUser()->create();

                // set custom leave dates of hc user
                $customLeavesData = [];
                if ($payload['availability'] == 2 && !empty($payload['from_date']) && !empty($payload['to_date'])) {
                    foreach ($payload['from_date'] as $fromDateIndex => $fromDate) {
                        $from_date                                     = Carbon::parse($fromDate, $userTimezone)->setTime(0, 0, 0, 0)->setTimezone(config('app.timezone'))->toDateTimeString();
                        $customLeavesData[$fromDateIndex]['from_date'] = $from_date;
                    }
                    foreach ($payload['to_date'] as $toDateIndex => $toDate) {
                        $to_date                                   = Carbon::parse($toDate, $userTimezone)->setTime(23, 59, 59, 0)->setTimezone(config('app.timezone'))->toDateTimeString();
                        $customLeavesData[$toDateIndex]['to_date'] = $to_date;
                    }

                    foreach ($customLeavesData as $customLeave) {
                        $record->healthCocahAvailability()->create([
                            'status'      => 0,
                            'from_date'   => $customLeave['from_date'],
                            'to_date'     => $customLeave['to_date'],
                            'update_from' => 'profile',
                        ]);
                    }
                }

                if ($payload['user_type'] == 'health_coach') {
                    // set expertise of hc user
                    $record->healthCocahExpertise()->sync($payload['expertise']);
                }

                if (in_array($payload['responsibilities'], [2, 3])) {
                    $advanceNoticePeriod = $payload['advance_notice_period'];
                }

            }

            // to set hc user day wise slots
            if (!empty($payload['slots'])) {
                $dayWiseSlots = [];
                foreach ($payload['slots'] as $day => $slots) {
                    foreach ($slots['start_time'] as $key => $time) {
                        $start_time     = Carbon::createFromFormat('H:i', $time, $userTimezone);
                        $end_time       = Carbon::createFromFormat('H:i', $slots['end_time'][$key], $userTimezone);
                        $dayWiseSlots[] = [
                            'day'        => $day,
                            'start_time' => $start_time->format('H:i:00'),
                            'end_time'   => $end_time->format('H:i:59'),
                        ];
                    }
                }
                $record->healthCocahSlots()->createMany($dayWiseSlots);
            }

            // to set hc user day wise slots
            if (!empty($payload['presenter_slots'])) {
                $dayWisePresenterSlots = [];
                foreach ($payload['presenter_slots'] as $day => $slots) {
                    foreach ($slots['start_time'] as $key => $time) {
                        $start_time     = Carbon::createFromFormat('H:i', $time, $userTimezone);
                        $end_time       = Carbon::createFromFormat('H:i', $slots['end_time'][$key], $userTimezone);
                        $dayWisePresenterSlots[] = [
                            'day'        => $day,
                            'start_time' => $start_time->format('H:i:00'),
                            'end_time'   => $end_time->format('H:i:59'),
                        ];
                    }
                }
                $record->eventPresenterSlots()->createMany($dayWisePresenterSlots);
            }
        }

        // calculate user age
        $birth_date = Carbon::parse($payload['date_of_birth'], config('app.timezone'))->setTime(0, 0, 0);
        $now        = now()->setTime(0, 0, 0);
        $age        = $now->diffInYears($birth_date);
        $weight     = (!empty($payload['weight']) ? $payload['weight'] : 50);
        $height     = (!empty($payload['height']) ? $payload['height'] : 100);

        // save user profile
        $record->profile()->create([
            'gender'        => $payload['gender'],
            'height'        => $height,
            'birth_date'    => $payload['date_of_birth'],
            'about'         => $payload['about'],
            'age'           => $age,
            'notice_period' => $advanceNoticePeriod,
        ]);

        // save user weight
        $record->weights()->create([
            'weight'   => $weight,
            'log_date' => now()->toDateTimeString(),
        ]);

        // calculate bmi and store
        $bmi = $weight / pow(($height / 100), 2);

        $record->bmis()->create([
            'bmi'      => $bmi,
            'weight'   => $weight, // kg
            'height'   => $height, // cm
            'age'      => $age,
            'log_date' => now()->toDateTimeString(),
        ]);

        // set true flag in all notification modules
        $notificationModules = config('zevolifesettings.notificationModules');
        if (!empty($notificationModules)) {
            foreach ($notificationModules as $key => $value) {
                $record->notificationSettings()->create([
                    'module' => $key,
                    'flag'   => $value,
                ]);
            }
        }

        // create or update to set user goal data
        $userGoalData = [
            'steps'    => 6000,
            'calories' => (($payload['gender'] == "male") ? 2500 : 2000),

        ];
        $record->goal()->updateOrCreate(['user_id' => $record->getKey()], $userGoalData);

        // update start date accordingly company's start date
        if ($record && $record->company()->count() > 0) {
            $record->update(['start_date' => (isset($payload['start_date']) ? $payload['start_date'] : $record->company()->first()->subscription_start_date)]);
        }

        // sent registration email to user if user created successfully
        if ($record) {
            event(new UserRegisterEvent($record));
            $record->syncWithSurveyUsers();
            return ['status' => true];
        }

        return ['status' => false];
    }

    /**
     * update record data.
     *
     * @param payload , $id
     * @return boolean
     */

    public function updateEntity($payload)
    {
        $appTimeZone       = config('app.timezone');
        $oldMexicoTimezone = config('zevolifesettings.mexico_city_timezone.old_timezone');
        $newMexicoTimezone = config('zevolifesettings.mexico_city_timezone.new_timezone');
        $isTimezone        = false;
        $data              = [
            'first_name' => $payload['first_name'],
            'last_name'  => $payload['last_name'],
        ];

        // Allow to update emails to only ZSA
        if ($payload['role_slug'] == 'super_admin') {
            $data['email'] = $payload['email'];
        }

        $updated = $this->update($data);

        // update user name in meta of event booking logs
        EventBookingLogs::where('presenter_user_id', $this->id)->update([
            'meta->presenter' => $this->full_name,
        ]);

        if (isset($payload['logo']) && !empty($payload['logo'])) {
            $name = $this->id . '_' . \time();
            $this
                ->clearMediaCollection('logo')
                ->addMediaFromRequest('logo')
                ->usingName($name)
                ->usingFileName($name . '.' . $payload['logo']->extension())
                ->toMediaCollection('logo', config('medialibrary.disk_name'));
        }
        //Get the previous role of user
        $getPreviousUserRole = User::select('roles.id', 'roles.name', 'roles.slug')
            ->leftJoin('role_user', 'role_user.user_id', '=', 'users.id')
            ->leftJoin('roles', 'roles.id', '=', 'role_user.role_id')
            ->where('users.id', $this->id)
            ->first();

        $this->roles()->detach();
        $this->companyAccess()->detach();

        // attach selected role to user
        if (!empty($payload['role'])) {
            $role = Role::find($payload['role']);
            if (!empty($role)) {
                // if role is company admin / rca then attach user to selected company
                if ((($payload['role_group'] == 'reseller' && ($role->slug == 'reseller_company_admin' || $role->slug == 'reseller_super_admin')) || ($payload['role_group'] == 'company' && $role->slug == 'company_admin')) && !empty($payload['company'])) {
                    $this->companyAccess()->attach($payload['company']);
                }
                $this->roles()->attach($role);

                //Send email to portal users when role is changed to administrator
                if ((($payload['role_group'] == 'company' && ($role->slug == 'reseller_company_admin' || $role->slug == 'reseller_super_admin')) || ($payload['role_group'] == 'company' && $role->slug == 'company_admin')) && !empty($payload['company'])) {
                    //Add condition to check previous role and current role are not same
                    if (!is_null($getPreviousUserRole) && $role->slug != $getPreviousUserRole->slug) {
                        $userData = User::find($this->id);
                        event(new AdminRegisterEvent($userData, 'changed_user_permission'));
                    }
                }
            }
        } else {
            // attach app user role to new user
            if ($payload['role_group'] == 'zevo' && $payload['user_type'] == 'health_coach') {
                $role = Role::where(['slug' => 'health_coach', 'default' => 1])->first();
            } elseif ($payload['role_group'] == 'zevo' && $payload['user_type'] == 'counsellor') {
                $role = Role::where(['slug' => 'counsellor', 'default' => 1])->first();
            } elseif ($payload['role_group'] == 'zevo' && $payload['user_type'] == 'wellbeing_specialist') {
                $role = Role::where(['slug' => 'wellbeing_specialist', 'default' => 1])->first();
            } elseif ($payload['role_group'] == 'zevo' && $payload['user_type'] == 'wellbeing_team_lead') {
                $role = Role::where(['slug' => 'wellbeing_team_lead', 'default' => 1])->first();
            } else {
                $role = Role::where(['slug' => 'user', 'default' => 1])->first();

                // set app access to true if user type is user
                $this->update(['can_access_app' => true]);
            }
            $this->roles()->attach($role);
        }

        if (($payload['role_group'] == 'company' || $payload['role_group'] == 'reseller') && !empty($payload['company']) && !empty($payload['department']) && !empty($payload['team'])) {
            //check if user is attached with team and role user
            if ($this->teams()->count() > 0) {
                $oldTeamId = $this->teams()->pluck('team_id')->first();
                $oldDeptId = $this->teams()->first()->department_id;

                if ($oldTeamId != $payload['team']) {
                    $team = Team::select('id', 'company_id', 'default')
                        ->withCount('users')
                        ->with(['company' => function ($query) {
                            $query->select('id', 'auto_team_creation', 'team_limit');
                        }])
                        ->find($payload['team']);
                    if (!$team->default && $team->company->auto_team_creation == true && (($team->users_count + 1) > $team->company->team_limit)) {
                        return [
                            'status'  => false,
                            'message' => 'Team limit is reached, Please select another team.',
                        ];
                    }

                    $this->teams()->detach();
                    $this->teams()->attach($payload['team'], ['company_id' => $payload['company'], 'department_id' => $payload['department']]);
                    $this->syncWithSurveyUsers(true);
                }
            }

            $groupRestriction = $this->company->first()->group_restriction;
            $adminOfGroups    = $this->groups()->get();

            if ($oldDeptId != $payload['department']) {
                ZcSurveyResponse::where(['user_id' => $this->id, 'department_id' => $oldDeptId])
                    ->update(['department_id' => $payload['department']]);
                ZcSurveyReviewSuggestion::where(['user_id' => $this->id, 'department_id' => $oldDeptId])
                    ->update(['department_id' => $payload['department']]);

                if ($groupRestriction == 1 && $role->slug == 'user') {
                    foreach ($adminOfGroups as $key => $value) {
                        $randomGroupMember = $value->members()->whereNotIn('users.id', [$this->id])->first();
                        $this->groups()->where('id', $value->id)->first()->members()->detach([$this->id]);
                        if (!empty($randomGroupMember)) {
                            $this->groups()->where('id', $value->id)->update(['creator_id' => $randomGroupMember->id]);
                        }
                    }
                }
            }

            if ($oldTeamId != $payload['team']) {
                if ($groupRestriction == 2 && $role->slug == 'user') {
                    foreach ($adminOfGroups as $key => $value) {
                        $randomGroupMember = $value->members()->whereNotIn('users.id', [$this->id])->first();
                        $this->groups()->where('id', $value->id)->first()->members()->detach([$this->id]);
                        if (!empty($randomGroupMember)) {
                            $this->groups()->where('id', $value->id)->update(['creator_id' => $randomGroupMember->id]);
                        }
                    }
                }

                $newTeam = $this->teams()->first();
                \dispatch(new SendTeamChangePushNotification($this, $newTeam->name, 1, $payload['team']));

                // check if user is switched to default team then remove this user from all the challenge type group
                if ($newTeam->default) {
                    removeUserFromChallengeTypeGroups($this, $payload['company']);
                }
            }
        }

        // set coach fields if user is wellbeing specialist
        if ($this->is_coach || ($payload['role_group'] == 'zevo' && $payload['user_type'] == 'wellbeing_specialist')) {
            if ($this->is_coach || ($payload['role_group'] == 'zevo' && $payload['user_type'] == 'wellbeing_specialist')) {
                $now          = now($appTimeZone)->setTime(0, 0, 0, 0)->toDateTimeString();
                $userTimezone = $payload['timezone'];
                $isTimezone   = false;
                if (strcasecmp($userTimezone, $oldMexicoTimezone) === 0) {
                    $userTimezone = $newMexicoTimezone;
                    $isTimezone   = true;
                }
                // set availability of hc user
                $this->update([
                    'availability_status' => "{$payload['availability']}",
                    'timezone'            => $userTimezone,
                    'is_timezone'         => $isTimezone,
                ]);
                $customLeavesData = [];
                // set custom leave dates of hc user
                if ($payload['availability'] == 2 && (!empty($payload['from_date']) && !empty($payload['to_date']))) {
                    foreach ($payload['from_date'] as $fromDateIndex => $fromDate) {
                        $from_date                                     = Carbon::parse($fromDate, $userTimezone)->setTime(0, 0, 0, 0)->setTimezone(config('app.timezone'))->toDateTimeString();
                        $customLeavesData[$fromDateIndex]['from_date'] = $from_date;
                    }
                    foreach ($payload['to_date'] as $toDateIndex => $toDate) {
                        $to_date                                   = Carbon::parse($toDate, $userTimezone)->setTime(23, 59, 59, 0)->setTimezone(config('app.timezone'))->toDateTimeString();
                        $customLeavesData[$toDateIndex]['to_date'] = $to_date;
                    }
                    $oldAvailability = $this
                        ->healthCocahAvailability()
                        ->where('update_from', 'profile')
                        ->where('user_id', $this->id)
                        ->count();
                    if ($oldAvailability >= 0) {
                        $this
                            ->healthCocahAvailability()
                            ->where('update_from', 'profile')
                            ->where('status', 0)
                            ->where('user_id', $this->id)
                            ->delete();
                    }
                    foreach ($customLeavesData as $customLeave) {
                        $this->healthCocahAvailability()->create([
                            'status'      => 0,
                            'from_date'   => $customLeave['from_date'],
                            'to_date'     => $customLeave['to_date'],
                            'update_from' => 'profile',
                        ]);
                    }
                } else {
                    // check if user set available/unavailable from custom leave and any future dates are set from profile previous then make those all to available by deleting those records
                    $this
                        ->healthCocahAvailability()
                        ->where('update_from', 'profile')
                        ->where('status', 0)
                        ->whereRaw("(CONVERT_TZ(from_date, ? , ?) >= ?)", [
                            'UTC', $appTimeZone, $now
                        ])
                        ->delete();
                }

                if ($this->is_coach) {
                    // set expertise of hc user
                    $this->healthCocahExpertise()->sync($payload['expertise']);
                }
            }

            // to set hc user day wise slots
            if (!empty($payload['slots'])) {
                $existingSlots = $this->healthCocahSlots->pluck('id')->toArray();
                $updateSlotIds = [];
                foreach ($payload['slots'] as $day => $slots) {
                    foreach ($slots['start_time'] as $key => $time) {
                        if (!empty($key)) {
                            $updateSlotIds[] = $key;
                        }
                        $start_time = Carbon::createFromFormat('H:i', $time, $this->timezone);
                        $end_time   = Carbon::createFromFormat('H:i', $slots['end_time'][$key], $this->timezone);

                        $this->healthCocahSlots()->updateOrCreate([
                            'id' => ((!empty($key) && is_numeric($key)) ? $key : 0),
                        ], [
                            'day'        => $day,
                            'start_time' => $start_time->format('H:i:00'),
                            'end_time'   => $end_time->format('H:i:59'),
                        ]);
                    }
                }

                $removeIds = array_diff($existingSlots, $updateSlotIds);
                if (!empty($removeIds)) {
                    $this->healthCocahSlots()->whereIn('id', $removeIds)->delete();
                }
            }

            // Set event presenter slots
            if (!empty($payload['presenter_slots'])) {
                $existingPresenterSlots = $this->eventPresenterSlots->pluck('id')->toArray();
                $updatePresenterSlotIds = [];
                foreach ($payload['presenter_slots'] as $day => $slots) {
                    foreach ($slots['start_time'] as $key => $time) {
                        if (!empty($key)) {
                            $updatePresenterSlotIds[] = $key;
                        }
                        $start_time = Carbon::createFromFormat('H:i', $time, $this->timezone);
                        $end_time   = Carbon::createFromFormat('H:i', $slots['end_time'][$key], $this->timezone);

                        $this->eventPresenterSlots()->updateOrCreate([
                            'id' => ((!empty($key) && is_numeric($key)) ? $key : 0),
                        ], [
                            'day'        => $day,
                            'start_time' => $start_time->format('H:i:00'),
                            'end_time'   => $end_time->format('H:i:59'),
                        ]);
                    }
                }

                $removePresenterSlotIds = array_diff($existingPresenterSlots, $updatePresenterSlotIds);
                if (!empty($removePresenterSlotIds)) {
                    $this->eventPresenterSlots()->whereIn('id', $removePresenterSlotIds)->delete();
                }
            }
        }

        if ($payload['role_group'] == 'zevo' && $payload['user_type'] == 'counsellor') {
            // Set skills of counsellor users
            $this->counsellorSkills()->sync($payload['counsellor_skills']);

            // Set cover image of counsellor
            if (isset($payload['counsellor_cover']) && !empty($payload['counsellor_cover'])) {
                $name = $this->id . '_' . \time();
                $this
                    ->clearMediaCollection('counsellor_cover')
                    ->addMediaFromRequest('counsellor_cover')
                    ->usingName($payload['counsellor_cover']->getClientOriginalName())
                    ->usingFileName($name . '.' . $payload['counsellor_cover']->extension())
                    ->toMediaCollection('counsellor_cover', config('medialibrary.disk_name'));
            }

            $userTimezone = $payload['timezone'];
            $isTimezone   = false;
            if (strcasecmp($userTimezone, $oldMexicoTimezone) === 0) {
                $userTimezone = $newMexicoTimezone;
                $isTimezone   = true;
            }

            $this->update([
                'timezone'    => $userTimezone,
                'is_timezone' => $isTimezone,
            ]);
        } elseif ($payload['role_group'] == 'zevo' && $payload['user_type'] == 'wellbeing_specialist') {
            // Set cover image of counsellor
            if (isset($payload['counsellor_cover']) && !empty($payload['counsellor_cover'])) {
                $name = $this->id . '_' . \time();
                $this
                    ->clearMediaCollection('counsellor_cover')
                    ->addMediaFromRequest('counsellor_cover')
                    ->usingName($payload['counsellor_cover']->getClientOriginalName())
                    ->usingFileName($name . '.' . $payload['counsellor_cover']->extension())
                    ->toMediaCollection('counsellor_cover', config('medialibrary.disk_name'));
            }

            $this->wsuser()->update([
                'language'            => !empty($payload['language']) ? implode(',', $payload['language']) : '',
                'conferencing_mode'   => $payload['video_conferencing_mode'],
                'video_link'          => $payload['video_link'],
                'shift'               => $payload['shift'],
                'years_of_experience' => $payload['years_of_experience'],
                'responsibilities'    => $payload['responsibilities'],
            ]);

            if (in_array($payload['responsibilities'], [2, 3])) {
                // Advance notifice period update when WBS
                $this->profile()->update([
                    'notice_period' => $payload['advance_notice_period'],
                ]);
            }

            // Set expertise of wellbeing_specialist
            if (isset($payload['expertise_wbs']) && !empty($payload['expertise_wbs'])) {
                $this->healthCocahExpertise()->sync($payload['expertise_wbs']);
            }

            if (!empty($payload['user_services'])) {
                $this->userservices()->sync($payload['user_services']);

                $existingServices = $this->userservices()
                    ->select('service_sub_categories.service_id')
                    ->groupBy('service_sub_categories.service_id')
                    ->get()
                    ->pluck('service_id')
                    ->toArray();

                if (!empty($existingServices)) {
                    $removeServices = DigitalTherapyService::whereNotIn('service_id', $existingServices)
                        ->where('ws_id', $this->id)
                        ->select('service_id')
                        ->distinct()
                        ->get()
                        ->pluck('service_id')
                        ->toArray();

                    if (!empty($removeServices)) {
                        DigitalTherapyService::whereIn('service_id', $removeServices)
                            ->where('ws_id', $this->id)
                            ->delete();
                    }
                }

            }
        }

        // save user profile
        $this->profile()->update([
            'gender' => !empty($payload['gender']) ? $payload['gender'] : 'none',
            'about'  => $payload['about'],
        ]);

        $loggedInUserCompany = $this->company()->first();
        if ($updated && $this->surveyUserLogs()->count() == 0 && !$this->is_coach && !is_null($loggedInUserCompany)) {
            $this->update(['start_date' => isset($payload['start_date']) ? $payload['start_date'] : $loggedInUserCompany->subscription_start_date]);
        }

        if ($updated) {
            return ['status' => true];
        }

        return ['status' => false];
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
        $this->clearMediaCollection('logo');
        if ($this->delete()) {
            // remove survey logs entry
            $this->surveyUserLogs()->delete();

            return array('deleted' => 'true');
        }
        return array('deleted' => 'error');
    }

    /**
     * update record data.
     *
     * @param payload , $id
     * @return boolean
     */

    public function updateStatus()
    {
        if ($this->is_blocked == 1) {
            $updated = $this->update(['is_blocked' => false]);
        } else {
            $updated = $this->update(['is_blocked' => true]);
        }

        return $updated;
    }

    /**
     * @return BelongsToMany
     */
    public function notifications(): BelongsToMany
    {
        return $this->belongsToMany('\App\Models\Notification')
            ->withPivot('read')
            ->withPivot('sent')
            ->withPivot('read_at')
            ->withPivot('sent_on')
            ->withTimestamps();
    }

    /**
     * @return string
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversion
     */
    public function getFullNameAttribute(): string
    {
        return $this->first_name . " " . $this->last_name;
    }

    /**
     * @return HasMany
     */
    public function bmis(): HasMany
    {
        return $this->hasMany('App\Models\UserBmi');
    }

    /**
     * @return array
     */
    public function getExtraFields(): array
    {
        return [
            'user_premium'              => $this->is_premium,
            'unread_notification_count' => $this->userUnreadNotifactionCount(),
            'unread_message_count'      => $this->userUnreadMsgCount(),
            'stepLastSyncDateTime'      => (!empty($this->step_last_sync_date_time)) ? Carbon::parse($this->step_last_sync_date_time, config('app.timezone'))->setTimezone($this->timezone)->toAtomString() : "",
            'exerciseLastSyncDateTime'  => (!empty($this->exercise_last_sync_date_time)) ? Carbon::parse($this->exercise_last_sync_date_time, config('app.timezone'))->setTimezone($this->timezone)->toAtomString() : "",
            'healthScoreAvailable'      => (!empty($this->hs_show_banner) && $this->hs_show_banner == true) ? true : false,
        ];
    }

    /**
     * update user profile data.
     *
     * @param array $payload
     * @return boolean
     */
    public function updateEntityProfile($payload)
    {
        $user         = auth()->user();
        $loggedInUser = getUserRole($user);
        $appTimezone  = config('app.timezone');
        $timezone     = (!empty($this->timezone) ? $this->timezone : $appTimezone);
        $now          = now($timezone)->toDateString();

        if ($loggedInUser->slug == 'wellbeing_specialist') {
            $this->wsuser()->update([
                'language'            => !empty($payload['language']) ? implode(',', $payload['language']) : '',
                'shift'               => $payload['shift'],
                'years_of_experience' => $payload['years_of_experience'],
                'is_profile'          => true,
            ]);
        } elseif ($loggedInUser->slug == 'health_coach') {
            $this->healthCoachUser()->updateOrCreate([
                'user_id' => ((!empty($user->id) && is_numeric($user->id)) ? $user->id : 0),
            ], [
                'is_profile' => true,
            ]);
        }

        $userArray = [
            'first_name' => $payload['first_name'],
            'last_name'  => $payload['last_name'],
        ];

        $updated = $this->update($userArray);

        if ($updated) {
            if ($loggedInUser->slug == 'wellbeing_specialist') {
                $userProfileArray = [
                    'gender' => $payload['gender'],
                    'about'  => $payload['about'],
                ];

                if (isset($payload['counsellor_cover']) && !empty($payload['counsellor_cover'])) {
                    $name = $this->id . '_' . \time();
                    $this
                        ->clearMediaCollection('counsellor_cover')
                        ->addMediaFromRequest('counsellor_cover')
                        ->usingName($payload['counsellor_cover']->getClientOriginalName())
                        ->usingFileName($name . '.' . $payload['counsellor_cover']->extension())
                        ->toMediaCollection('counsellor_cover', config('medialibrary.disk_name'));
                }
            } else {
                $userProfileArray = [
                    'gender'     => $payload['gender'],
                    'height'     => (isset($payload['height']) && !empty($payload['height']) ? $payload['height'] : null),
                    'birth_date' => (isset($payload['date_of_birth']) && !empty($payload['date_of_birth']) ? $payload['date_of_birth'] : null),
                    'about'      => $payload['about'],
                ];
            }

            // save user profile
            $this->profile()->update($userProfileArray);

            if ($loggedInUser->slug != 'wellbeing_specialist' && $loggedInUser->slug != 'health_coach' && $loggedInUser->slug != 'wellbeing_team_lead') {
                // delete all weight entry for current day before insert
                $this->weights()
                    ->whereRaw("DATE(CONVERT_TZ(user_weight.log_date, ?, ?)) = ?",[
                        $appTimezone,$timezone,$now
                    ])
                    ->delete();

                // save user weight
                $this->weights()->create([
                    'weight'   => $payload['weight'],
                    'log_date' => now()->toDateTimeString(),
                ]);
            }

            // update user name in meta of event booking logs
            EventBookingLogs::where('presenter_user_id', $this->id)->update([
                'meta->presenter' => $this->full_name,
            ]);

            // update profile picture
            if (isset($payload['logo']) && !empty($payload['logo'])) {
                $name = $this->id . '_' . \time();
                $this
                    ->clearMediaCollection('logo')
                    ->addMediaFromRequest('logo')
                    ->usingName($name)
                    ->usingFileName($name . '.' . $payload['logo']->extension())
                    ->toMediaCollection('logo', config('medialibrary.disk_name'));
            }

            return true;
        }

        return false;
    }

    /**
     * @return BelongsToMany
     */
    public function feedLogs(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\Feed', 'feed_user', 'user_id', 'feed_id')->withPivot('saved', 'liked', 'view_count', 'favourited', 'favourited_at')->withTimestamps();
    }

    /**
     * @return BelongsToMany
     */
    public function webinarLogs(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\Webinar', 'webinar_user', 'user_id', 'webinar_id')->withPivot('saved', 'liked', 'view_count', 'favourited', 'favourited_at')->withTimestamps();
    }

    /**
     * @return BelongsToMany
     */
    public function courseLogs(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\Course', 'user_course', 'user_id', 'course_id')->withPivot('id', 'saved', 'liked', 'ratings', 'review', 'joined', 'joined_on', 'completed_on', 'completed', 'post_survey_completed', 'post_survey_completed_on', 'pre_survey_completed', 'pre_survey_completed_on', 'started_course', 'favourited', 'favourited_at')->withTimestamps();
    }

    /**
     * "hasMany" relation to `masterclass_csat_user_logs` table
     * via `user_id` field.
     *
     * @return hasMany
     */
    public function courseCsat(): hasMany
    {
        return $this->hasMany(MasterclassCsatLogs::class, 'user_id');
    }

    public function userUnreadNotifactionCount()
    {
        $defaultTimeZone = config('app.timezone');
        $xDeviceOs       = strtolower(Request()->header('X-Device-Os', ""));
        $today           = now($this->timezone)->toDateTimeString();
        $dayStartFrom    = now($this->timezone)->subDays(7)->todatetimeString();

        $unreadCount = $this->notifications()
            ->wherePivot('read', false)
            ->wherePivot('sent', 1)
            ->whereRaw("CONVERT_TZ(notification_user.sent_on, ?, ?) >= ?",[
                $defaultTimeZone,$this->timezone,$dayStartFrom
            ])
            ->whereRaw("CONVERT_TZ(notification_user.sent_on, ?, ?) <= ?",[
                $defaultTimeZone,$this->timezone,$today
            ]);
        if ($xDeviceOs == config('zevolifesettings.PORTAL')) {
            $unreadCount->where('notifications.is_portal', true);
        } else {
            $unreadCount->where('notifications.is_mobile', true);
        }
        $unreadCount = $unreadCount->count();
        return (!empty($unreadCount)) ? $unreadCount : 0;
    }

    /**
     * @return BelongsToMany
     */
    public function badges(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\Badge', 'badge_user', 'user_id', 'badge_id')->withPivot('status', 'expired_at', 'created_at', 'level')->withTimestamps();
    }

    /**
     * @return HasMany
     */
    public function notificationSettings(): HasMany
    {
        return $this->hasMany('App\Models\NotificationSetting');
    }

    /**
     * @return HasOne
     */
    public function goal(): HasOne
    {
        return $this->hasOne('App\Models\UserGoal');
    }

    /**
     * @return BelongsToMany
     */

    public function coachLogs(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\User', 'user_coach_log', 'coach_id', 'user_id')->withPivot('id', 'followed', 'liked', 'ratings', 'review')->withTimestamps();
    }

    public function getUserDataForApi()
    {
        $return          = [];
        $return['id']    = $this->getKey();
        $return['name']  = $this->full_name;
        $return['image'] = $this->getMediaData('logo', ['w' => 320, 'h' => 320]);

        return $return;
    }

    public function getBroadcastCreatorData()
    {
        $company = $this->company()->select('companies.id', 'companies.name')->first();
        return [
            'id'    => $this->getKey(),
            'name'  => (!is_null($company) ? "{$company->name} Admin" : "Team Zevo"),
            'image' => $this->getMediaData('logo', ['w' => 320, 'h' => 320]),
        ];
    }

    public function courseLessonLogs(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\CourseLession', 'user_lession', 'user_id', 'course_lession_id')->withPivot('course_id', 'course_week_id', 'status', 'completed_at')->withTimestamps();
    }

    /**
     * @return Integer
     */
    public function completedLession(Int $courseId): int
    {
        return $this->courseLessonLogs()->wherePivot('course_id', $courseId)->wherePivot('status', 'completed')->wherePivot('completed_at', '!=', null)->count();
    }

    /**
     * @return Integer
     */
    public function runningLession(Int $courseId): int
    {
        $lession   = $this->courseLessonLogs()->wherePivot('course_id', $courseId)->wherePivot('status', 'started')->wherePivot('completed_at', '=', null)->first();
        return (!empty($lession)) ? $lession->getKey() : 0;
    }

    /**
     * @return BelongsToMany
     */
    public function inCompletedMeditationTracks(): BelongsToMany
    {
        return $this->belongsToMany(MeditationTrack::class, 'user_incompleted_tracks', 'user_id', 'meditation_track_id')->withPivot('duration_listened')->withTimestamps();
    }

    /**
     * @return BelongsToMany
     */
    public function completedMeditationTracks(): BelongsToMany
    {
        return $this->belongsToMany(MeditationTrack::class, 'user_listened_tracks', 'user_id', 'meditation_track_id')->withPivot('duration_listened')->withTimestamps();
    }

    /**
     * @return BelongsToMany
     */
    public function unlockedCourseLessons(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\CourseLession', 'unlocked_user_course_lessons', 'user_id', 'course_lession_id')->withPivot('course_week_id')->withTimestamps();
    }

    /**
     * @param
     *
     * @return integer
     */
    public function coachAverageRatings()
    {
        $ratingsTotal = $this->coachLogs()->wherePivot('ratings', '>', 0)->selectRaw('count(user_id) as totalUser,sum(ratings) as ratings')->groupBy('coach_id')->first();

        $ratings = 0;
        if (!empty($ratingsTotal)) {
            $ratings = round($ratingsTotal->ratings / $ratingsTotal->totalUser);
        }

        return $ratings;
    }

    public function totalFollowerCount()
    {
        return $this->coachLogs()->wherePivot('followed', true)->count();
    }

    /**
     * @return HasMany
     */
    public function steps(): HasMany
    {
        return $this->hasMany('App\Models\UserStep');
    }

    /**
     * @return HasMany
     */
    public function contentPoints(): HasMany
    {
        return $this->hasMany('App\Models\ContentChallengePointHistory');
    }

    /**
     * @return BelongsToMany
     */
    public function exercises(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\Exercise', 'user_exercise', 'user_id', 'exercise_id')
            ->withPivot(['id', 'exercise_key', 'calories', 'distance', 'duration', 'start_date', 'end_date', 'tracker', 'deleted_at'])
            ->using('App\Models\UserExercise')
            ->withTimestamps();
    }

    /**
     * @return HasMany
     */
    public function groupMessages(): BelongsToMany
    {
        return $this->belongsToMany(Group::class, 'group_messages', 'user_id', 'group_id')->withPivot('message', 'model_id', 'model_name', 'read', 'is_broadcast', 'broadcast_company_id')->withTimestamps();
    }

    public function getSharedModelData()
    {
        // check if requested model is found or not

        if ($this->pivot->model_name == 'feed') {
            $model = \App\Models\Feed::find($this->pivot->model_id);
        } elseif ($this->pivot->model_name == 'masterclass') {
            $model = \App\Models\Course::find($this->pivot->model_id);
        } elseif ($this->pivot->model_name == 'meditation') {
            $model = \App\Models\MeditationTrack::find($this->pivot->model_id);
        } elseif ($this->pivot->model_name == 'recipe') {
            $model = \App\Models\Recipe::find($this->pivot->model_id);
        } elseif ($this->pivot->model_name == 'webinar') {
            $model = \App\Models\Webinar::find($this->pivot->model_id);
        } elseif ($this->pivot->model_name == 'badge') {
            $model = \App\Models\Badge::leftJoin('badge_user', 'badge_user.badge_id', '=', 'badges.id')
                ->where('badge_user.id', $this->pivot->model_id)
                ->select('badges.id', 'badges.type', 'badge_user.id as badgeUserId', 'badges.title')
                ->first();
        } elseif ($this->pivot->model_name == 'podcast') {
            $model = \App\Models\Podcast::find($this->pivot->model_id);
        } elseif ($this->pivot->model_name == 'short') {
            $model = \App\Models\Shorts::find($this->pivot->model_id);
        }

        if (!empty($model)) {
            return $model;
        } else {
            return array();
        }
    }

    public function setSharedModelData($model)
    {
        $collection_name = (($model instanceof MeditationTrack) ? 'cover' : (($model instanceof Feed) ? 'featured_image' : 'logo'));
        if ($model instanceof Shorts) {
            $collection_name = 'header_image';
        }
        $deeplinkURI = $model instanceof Badge ? 'zevolife://zevo/badge/' . $model->badgeUserId : $model->deep_link_uri;

        if ($model->type == 'masterclass') {
            $defaultMasterclass = Badge::where('type', 'masterclass')->where('is_default', true)->first();
            $image              = $defaultMasterclass->getMediaData('logo', ['w' => 320, 'h' => 320, 'zc' => 1]);
        } else {
            $image = $model->getMediaData($collection_name, ['w' => 320, 'h' => 320, 'zc' => 1]);
        }
        return [
            'id'          => $model->id,
            'title'       => $model->title,
            'image'       => $image,
            'deeplinkURI' => (!empty($deeplinkURI)) ? $deeplinkURI : "",
        ];
    }

    // get distance data for user for given date - for running Challenge
    public function getDistance($date, $appTimezone, $userTimezone)
    {
        return $this->steps()
            ->whereRaw("DATE(CONVERT_TZ(user_step.log_date, ?, ?)) = ?",[
                $appTimezone,$userTimezone,$date
            ])
            ->sum('distance');
    }

    // get steps data for user for given date - for running Challenge
    public function getSteps($date, $appTimezone, $userTimezone)
    {
        return $this->steps()
            ->whereRaw("DATE(CONVERT_TZ(user_step.log_date, ?, ?)) = ?",[
                $appTimezone,$userTimezone,$date
            ])
            ->sum('steps');
    }

    // get distance/duration of exercise data for user for given date - for running Challenge
    public function getExercises($date, $appTimezone, $userTimezone, $uom, $modelId)
    {
        $column = 'duration';
        if ($uom == 'meter') {
            $column = 'distance';
        }

        $points = $this->exercises()
            ->whereRaw("DATE(CONVERT_TZ(user_exercise.start_date, ?, ?)) = ?",[
                $appTimezone,$userTimezone,$date
            ])
            ->whereNull('user_exercise.deleted_at')
            ->where('user_exercise.exercise_id', $modelId)
            ->sum($column);

        if ($column == 'duration') {
            $points = ($points / 60);
        }

        return $points;
    }

    // get meditation count data for user for given date - for running Challenge
    public function getMeditation($date, $appTimezone, $userTimezone)
    {
        return $this->completedMeditationTracks()
            ->whereRaw("DATE(CONVERT_TZ(user_listened_tracks.created_at, ?, ?)) = ?",[
                $appTimezone,$userTimezone,$date
            ])
            ->count();
    }

    // get distance data for user for given date - for running Challenge
    public function getDistancePointsInChallenge($pointCalcRules, $startDate, $endDate, $appTimezone, $userTimezone)
    {
        $count = $this->steps()
            ->whereRaw("DATE(CONVERT_TZ(user_step.log_date, ?, ?)) >= ?",[
                $appTimezone,$userTimezone,$startDate
            ])
            ->whereRaw("DATE(CONVERT_TZ(user_step.log_date, ?, ?)) <= ?",[
                $appTimezone,$userTimezone,$endDate
            ])
            ->sum('distance');

        return round($count / $pointCalcRules['distance'], 2);
    }

    // get steps data for user for given date - for running Challenge
    public function getStepsPointsInChallenge($pointCalcRules, $startDate, $endDate, $appTimezone, $userTimezone)
    {
        $count = $this->steps()
            ->whereRaw("DATE(CONVERT_TZ(user_step.log_date, ?, ?)) >= ?",[
                $appTimezone,$userTimezone,$startDate
            ])
            ->whereRaw("DATE(CONVERT_TZ(user_step.log_date, ?, ?)) <= ?",[
                $appTimezone,$userTimezone,$endDate
            ])
            ->sum('steps');

        return round($count / $pointCalcRules['steps'], 2);
    }

    // get steps data for user for given date - for running Challenge
    public function getContentPointsInChallenge($pointCalcRules, $startDate, $endDate, $appTimezone, $userTimezone)
    {
        $count = $this->contentPoints()
            ->whereRaw("DATE(CONVERT_TZ(content_challenge_point_history.log_date, ?, ?)) >= ?",[
                $appTimezone,$userTimezone,$startDate
            ])
            ->whereRaw("DATE(CONVERT_TZ(content_challenge_point_history.log_date, ?, ?)) <= ?",[
                $appTimezone,$userTimezone,$endDate
            ])
            ->sum('points');

        return round($count, 2);
    }

    // get distance/duration of exercise data for user for given date - for running Challenge
    public function getExercisesPointsInChallenge($pointCalcRules, $startDate, $endDate, $appTimezone, $userTimezone, $uom, $modelId)
    {
        $column = 'duration';
        $point  = 'exercises_duration';
        if ($uom == 'meter') {
            $column = 'distance';
            $point  = 'exercises_distance';
        }
        $count = $this->exercises()
            ->where(function ($q) use ($startDate, $endDate, $appTimezone, $userTimezone) {
                $q->whereRaw("DATE(CONVERT_TZ(user_exercise.start_date, ?, ?)) >= ?",[
                    $appTimezone,$userTimezone,$startDate
                ])
                ->whereRaw("DATE(CONVERT_TZ(user_exercise.start_date, ?, ?)) <= ?",[
                    $appTimezone,$userTimezone,$endDate
                ]);
            })
            ->whereNull('user_exercise.deleted_at')
            ->where('user_exercise.exercise_id', $modelId)
            ->sum($column);

        if ($column == 'duration') {
            $count = ($count / 60);
        }

        return round($count / $pointCalcRules[$point], 2);
    }

    // get meditation count data for user for given date - for running Challenge
    public function getMeditationPointsInChallenge($pointCalcRules, $startDate, $endDate, $appTimezone, $userTimezone)
    {
        $count = $this->completedMeditationTracks()
            ->whereRaw("DATE(CONVERT_TZ(user_listened_tracks.created_at, ?, ?)) >= ?",[
                $appTimezone,$userTimezone,$startDate
            ])
            ->whereRaw("DATE(CONVERT_TZ(user_listened_tracks.created_at, ?, ?)) <= ?",[
                $appTimezone,$userTimezone,$endDate
            ])
            ->count();

        return round($count / $pointCalcRules['meditations'], 2);
    }

    // get total points of user for given date - for running Challenge
    public function getTotalPointsInChallenge($challenge, $pointCalcRules, $challengeRulesData)
    {
        $appTimezone  = config('app.timezone');
        $userTimezone = $this->timezone;
        $startDate    = Carbon::parse($challenge->start_date, $appTimezone)->setTimezone($userTimezone)->toDateString();
        $endDate      = Carbon::parse($challenge->end_date, $appTimezone)->setTimezone($userTimezone)->toDateString();

        $pointsArr = [];

        $usersExtraPoints = $challenge->challengeWiseManualPoints()->where('user_id', $this->getKey())->sum('points');

        $pointsArr[] = $usersExtraPoints;

        foreach ($challengeRulesData as $rule) {
            if ($rule->short_name == 'distance') {
                $userData = $this->getDistancePointsInChallenge($pointCalcRules, $startDate, $endDate, $appTimezone, $userTimezone);
            } elseif ($rule->short_name == 'steps') {
                $userData = $this->getStepsPointsInChallenge($pointCalcRules, $startDate, $endDate, $appTimezone, $userTimezone);
            } elseif ($rule->short_name == 'exercises' && $rule->model_name == 'Exercise') {
                $userData = $this->getExercisesPointsInChallenge($pointCalcRules, $startDate, $endDate, $appTimezone, $userTimezone, $rule->uom, $rule->model_id);
            } elseif ($rule->short_name == 'meditations') {
                $userData = $this->getMeditationPointsInChallenge($pointCalcRules, $startDate, $endDate, $appTimezone, $userTimezone, $rule->uom);
            } elseif ($rule->short_name == 'content') {
                $userData = $this->getContentPointsInChallenge($pointCalcRules, $startDate, $endDate, $appTimezone, $userTimezone, $rule->uom);
            }

            $pointsArr[] = $userData;
        }

        return array_sum($pointsArr);
    }

    // get distance data for user for given date - for running Challenge - for Badge
    public function getDistancePointsForBadgeCalculation($startDate, $endDate, $appTimezone, $userTimezone)
    {
        return $this->steps()
            ->whereRaw("DATE(CONVERT_TZ(user_step.log_date, ?, ?)) >= ?",[
                $appTimezone,$userTimezone,$startDate
            ])
            ->whereRaw("DATE(CONVERT_TZ(user_step.log_date, ?, ?)) <= ?",[
                $appTimezone,$userTimezone,$endDate
            ])
            ->sum('distance');
    }

    // get steps data for user for given date - for running Challenge - for Badge
    public function getStepsPointsForBadgeCalculation($startDate, $endDate, $appTimezone, $userTimezone)
    {
        return $this->steps()
            ->whereRaw("DATE(CONVERT_TZ(user_step.log_date, ?, ?)) >= ?",[
                $appTimezone,$userTimezone,$startDate
            ])
            ->whereRaw("DATE(CONVERT_TZ(user_step.log_date, ?, ?)) <= ?",[
                $appTimezone,$userTimezone,$endDate
            ])
            ->sum('steps');
    }

    // get distance/duration of exercise data for user for given date - for running Challenge - for Badge
    public function getExercisesPointsForBadgeCalculation($startDate, $endDate, $appTimezone, $userTimezone, $uom, $modelId)
    {
        $column = 'duration';
        if ($uom == 'meter') {
            $column = 'distance';
        }
        $points = $this->exercises()
            ->where(function ($q) use ($startDate, $endDate, $appTimezone, $userTimezone) {
                $q->whereRaw("DATE(CONVERT_TZ(user_exercise.start_date, ?, ?)) >= ?",[
                    $appTimezone,$userTimezone,$startDate
                ])
                ->whereRaw("DATE(CONVERT_TZ(user_exercise.start_date, ?, ?)) <= ?",[
                    $appTimezone,$userTimezone,$endDate
                ]);
            })
            ->whereNull('user_exercise.deleted_at')
            ->where('user_exercise.exercise_id', $modelId)
            ->sum($column);

        if ($column == 'duration') {
            $points = ($points / 60);
        }

        return $points;
    }

    // get meditation count data for user for given date - for running Challenge
    public function getMeditationPointsForBadgeCalculation($startDate, $endDate, $appTimezone, $userTimezone)
    {
        return $this->completedMeditationTracks()
            ->whereRaw("DATE(CONVERT_TZ(user_listened_tracks.created_at, ?, ?)) >= ?",[
                $appTimezone,$userTimezone,$startDate
            ])
            ->whereRaw("DATE(CONVERT_TZ(user_listened_tracks.created_at, ? , ? )) <= ?",[
                $appTimezone,$userTimezone,$endDate
            ])
            ->count();
    }

    // get distance data for user for given date - for completed Challenge
    public function getDistanceHistory($challenge, $date, $appTimezone, $userTimezone)
    {
        return $challenge->challengeHistorySteps()
            ->whereRaw("DATE(CONVERT_TZ(freezed_challenge_steps.log_date, ? , ? )) = ?",[
                $appTimezone,$userTimezone,$date
            ])
            ->where('user_id', $this->id)
            ->sum('distance');
    }

    // get steps data for user for given date - for completed Challenge
    public function getStepsHistory($challenge, $date, $appTimezone, $userTimezone)
    {
        return $challenge->challengeHistorySteps()
            ->whereRaw("DATE(CONVERT_TZ(freezed_challenge_steps.log_date, ? , ?)) = ?",[
                $appTimezone,$userTimezone,$date
            ])
            ->where('user_id', $this->id)
            ->sum('steps');
    }

    // Get Content point For user based on give date - challenge
    public function getContentPointHistory($challenge, $date, $appTimezone, $userTimezone)
    {
        return $challenge->contentChallengePointHistory()
            ->whereRaw("DATE(CONVERT_TZ(content_challenge_point_history.log_date, ? , ?)) = ?",[
                $appTimezone,$userTimezone,$date
            ])
            ->where('user_id', $this->id)
            ->sum('points');
    }

    // get distance/duration of exercise data for user for given date - for completed Challenge
    public function getExercisesHistory($challenge, $date, $appTimezone, $userTimezone, $uom, $modelId)
    {
        $column = 'duration';
        if ($uom == 'meter') {
            $column = 'distance';
        }
        $points = $challenge->challengeHistoryExercises()
            ->where(function ($q) use ($date, $appTimezone, $userTimezone) {
                $q->whereRaw("DATE(CONVERT_TZ(freezed_challenge_exercise.start_date, ? , ?)) <= ?",[
                    $appTimezone,$userTimezone,$date
                ])
                ->whereRaw("DATE(CONVERT_TZ(freezed_challenge_exercise.end_date, ?, ?)) >= ?",[
                    $appTimezone,$userTimezone,$date
                ]);
            })
            ->where('user_id', $this->id)
            ->where('exercise_id', $modelId)
            ->sum($column);

        if ($column == 'duration') {
            $points = ($points / 60);
        }

        return $points;
    }

    // get meditation count data for user for given date - for completed Challenge
    public function getMeditationHistory($challenge, $date, $appTimezone, $userTimezone)
    {
        return $challenge->challengeHistoryInspires()
            ->whereRaw("DATE(CONVERT_TZ(freezed_challenge_inspire.log_date, ? , ? )) = ?",[
                $appTimezone,$userTimezone,$date
            ])
            ->where('user_id', $this->id)
            ->count();
    }

    // get distance data for user for given date - for completed Challenge
    public function getDistancePointsInChallengeHistory($challenge, $participant, $pointCalcRules, $startDate, $endDate, $appTimezone, $userTimezone)
    {
        $count = $challenge->challengeHistorySteps()
            ->where('user_id', $participant->user_id)
            ->sum('distance');

        return round($count / $pointCalcRules['distance'], 1);
    }

    // get steps data for user for given date - for completed Challenge
    public function getStepsPointsInChallengeHistory($challenge, $participant, $pointCalcRules, $startDate, $endDate, $appTimezone, $userTimezone)
    {
        $count = $challenge->challengeHistorySteps()
            ->where('user_id', $participant->user_id)
            ->sum('steps');

        return round($count / $pointCalcRules['steps'], 1);
    }

    // get distance/duration of exercise data for user for given date - for completed Challenge
    public function getExercisesPointsInChallengeHistory($challenge, $participant, $pointCalcRules, $startDate, $endDate, $appTimezone, $userTimezone, $uom, $modelId)
    {
        $column = 'duration';
        $point  = 'exercises_duration';
        if ($uom == 'meter') {
            $column = 'distance';
            $point  = 'exercises_distance';
        }
        $count = $challenge->challengeHistoryExercises()
            ->where('user_id', $participant->user_id)
            ->sum($column);

        if ($column == 'duration') {
            $count = ($count / 60);
        }

        return round($count / $pointCalcRules[$point], 1);
    }

    // get meditation count data for user for given date - for completed Challenge
    public function getMeditationPointsInChallengeHistory($challenge, $participant, $pointCalcRules, $startDate, $endDate, $appTimezone, $userTimezone)
    {
        $count = $challenge->challengeHistoryInspires()
            ->where('user_id', $participant->user_id)
            ->count();

        return round($count / $pointCalcRules['meditations'], 1);
    }

    // get meditation count data for user for given date - for completed Challenge
    public function getContentPointsInChallengeHistory($challenge, $participant, $pointCalcRules, $startDate, $endDate, $appTimezone)
    {
        $count = $challenge->contentChallengePointHistory()
            ->where('user_id', $participant->user_id)
            ->sum('points');

        return round($count, 1);
    }

    // get total points of user for given date - for completed Challenge
    public function getTotalPointsInChallengeHistory($challenge, $pointCalcRules, $challengeRulesData, $participant)
    {
        $appTimezone  = config('app.timezone');
        $userTimezone = $this->timezone;
        $startDate    = Carbon::parse($challenge->start_date, $appTimezone)->setTimezone($userTimezone)->toDateString();
        $endDate      = Carbon::parse($challenge->end_date, $appTimezone)->setTimezone($userTimezone)->toDateString();

        $pointsArr = [];

        $usersExtraPoints = $challenge->challengeWiseManualPoints()->where('user_id', $participant->user_id)->sum('points');

        $pointsArr[] = $usersExtraPoints;

        foreach ($challengeRulesData as $rule) {
            if ($rule->short_name == 'distance') {
                $userData = $this->getDistancePointsInChallengeHistory($challenge, $participant, $pointCalcRules, $startDate, $endDate, $appTimezone, $userTimezone);
            } elseif ($rule->short_name == 'steps') {
                $userData = $this->getStepsPointsInChallengeHistory($challenge, $participant, $pointCalcRules, $startDate, $endDate, $appTimezone, $userTimezone);
            } elseif ($rule->short_name == 'exercises' && $rule->model_name == 'Exercise') {
                $userData = $this->getExercisesPointsInChallengeHistory($challenge, $participant, $pointCalcRules, $startDate, $endDate, $appTimezone, $userTimezone, $rule->uom, $rule->model_id);
            } elseif ($rule->short_name == 'meditations') {
                $userData = $this->getMeditationPointsInChallengeHistory($challenge, $participant, $pointCalcRules, $startDate, $endDate, $appTimezone, $userTimezone, $rule->uom);
            } elseif ($rule->short_name == 'content') {
                $userData = $this->getContentPointsInChallengeHistory($challenge, $participant, $pointCalcRules, $startDate, $endDate, $appTimezone, $userTimezone, $rule->uom);
            }

            $pointsArr[] = $userData;
        }

        return array_sum($pointsArr);
    }

    /**
     * @return HasMany
     */
    public function npsSurveyLinkLogs(): HasOne
    {
        return $this->hasOne('App\Models\UserNpsLogs');
    }

    /**
     * @return void
     */
    public function sendSurveyLink($updateSentLink = false): void
    {
        $notification_setting = $this
            ->notificationSettings()
            ->select('flag')
            ->where('flag', 1)
            ->where(function ($query) {
                $query->where('module', '=', 'nps')
                    ->orWhere('module', '=', 'all');
            })
            ->first();

        $checkAuditSurveyAccess = getCompanyPlanAccess($this, 'audit-survey');
        if ($checkAuditSurveyAccess) {
            \dispatch(new SendGeneralPushNotification($this, 'survey-feedback', [
                'push' => ($notification_setting->flag ?? false),
            ]));
        }

        // save app survey links log from user
        if ($updateSentLink) {
            if ($this->can_access_portal) {
                $userNpsData = $this->npsSurveyLinkLogs()->whereNull("survey_received_on")
                    ->where("user_id", $this->id)
                    ->where("is_portal", '1')
                    ->orderBy("id", "DESC")
                    ->first();

                if (!empty($userNpsData)) {
                    $userNpsData->update([
                        'survey_sent_on' => now()->toDateTimeString(),
                        'is_portal'      => '1',
                    ]);
                }
            }

            if ($this->can_access_app) {
                $userNpsDataApp = $this->npsSurveyLinkLogs()->whereNull("survey_received_on")
                    ->where("user_id", $this->id)
                    ->where("is_portal", '0')
                    ->orderBy("id", "DESC")
                    ->first();

                if (!empty($userNpsDataApp)) {
                    $userNpsDataApp->update([
                        'survey_sent_on' => now()->toDateTimeString(),
                        'is_portal'      => '0',
                    ]);
                }
            }
        } else {
            if ($this->can_access_portal) {
                $this->npsSurveyLinkLogs()->create([
                    'survey_sent_on' => now()->toDateTimeString(),
                    'is_portal'      => '1',
                ]);
            }
            if ($this->can_access_app) {
                $this->npsSurveyLinkLogs()->create([
                    'survey_sent_on' => now()->toDateTimeString(),
                    'is_portal'      => '0',
                ]);
            }
        }
    }

    public function getUserStepsTableData($payload)
    {
        $list = $this->getUserStepsData($payload);

        return DataTables::of($list['record'])
            ->addColumn('step_authentication', function ($record) {
                $startUserAuthenticatorDate = config('zevolifesettings.stepAuthenticatorDate');

                if ($record->log_date < $startUserAuthenticatorDate) {
                    return '-';
                }
                $logDate  = Carbon::parse($record->log_date)->toDateString() . ' 00:00:00';
                $avgSteps = UsersStepsAuthenticatorAvg::select('steps_avg')
                    ->where('user_id', $record->user_id)
                    ->where('log_date', $logDate)
                    ->first();
                $result = '<span class="status-symbol red-color"></span>';
                if (!empty($avgSteps)) {
                    if ($avgSteps->steps_avg > 0 && $record->steps < 35000) {
                        $findAvg = (($avgSteps->steps_avg - $record->steps) / $avgSteps->steps_avg) * 100;
                        if ($findAvg >= 25) {
                            $result = '<span class="status-symbol yellow-color"></span>';
                        } elseif ($findAvg < 25 && $findAvg > -25) {
                            $result = '<span class="status-symbol green-color"></span>';
                        } elseif ($findAvg <= -25 && $findAvg > -50) {
                            $result = '<span class="status-symbol yellow-color"></span>';
                        } elseif ($findAvg <= -50) {
                            $result = '<span class="status-symbol red-color"></span>';
                        }
                    }
                } else {
                    return '-';
                }
                return $result;
            })
            ->skipPaging()
            ->with([
                "recordsTotal"    => $list['total'],
                "recordsFiltered" => $list['total'],
            ])
            ->rawColumns(['step_authentication'])
            ->make(true);
    }

    public function getUserStepsData($payload)
    {
        $user        = auth()->user();
        $appTimezone = config('app.timezone');
        $timezone    = (!empty($user->timezone) ? $user->timezone : $appTimezone);

        $userStepsData = DB::table('user_step')
            ->select('user_step.*', DB::raw("CONCAT(users.first_name,' ',users.last_name) as fullName"), 'users.email', 'users.avg_steps')
            ->join("users", "users.id", "=", "user_step.user_id");

        if (in_array('searchText', array_keys($payload)) && !empty($payload['searchText'])) {
            $userStepsData
                ->whereRaw("CONCAT(users.first_name,' ',users.last_name) like ?", ['%' . $payload['searchText'] . '%'])
                ->orWhereRaw("users.email like ?", ['%' . $payload['searchText'] . '%']);
        }

        if ((isset($payload['fromdate']) && !empty($payload['fromdate'] && strtotime($payload['fromdate']) !== false)) && (isset($payload['todate']) && !empty($payload['todate'] && strtotime($payload['todate']) !== false))) {
            $fromdate = Carbon::parse($payload['fromdate'], $timezone)->setTime(0, 0, 0, 0)->setTimezone($appTimezone)->toDateTimeString();
            $todate   = Carbon::parse($payload['todate'], $timezone)->setTime(23, 59, 59, 0)->setTimezone($appTimezone)->toDateTimeString();
            $userStepsData
                ->where(function ($where) use ($fromdate, $todate) {
                    $where
                        ->whereRaw("TIMESTAMP(user_step.log_date) BETWEEN ? AND ?", [$fromdate, $todate]);
                });
        }

        $orderColumn = array("user_step.id", "fullName", "users.email", "user_step.tracker", "user_step.steps", "user_step.distance", "user_step.calories", "user_step.log_date", "user_step.created_at");
        $orName      = $orderColumn[$payload['order'][0]['column']];

        if (!empty($orName)) {
            $userStepsData->orderBy($orName, $payload['order'][0]['dir']);
        } else {
            $userStepsData->orderBy('user_step.id', 'DESC');
        }

        $data           = array();
        $data['total']  = $userStepsData->count();
        $data['record'] = $userStepsData->offset($payload['start'])->limit($payload['length'])->get();

        return $data;
    }

    public function getUserExercisesTableData($payload)
    {
        $list = $this->getUserExercisesData($payload);

        return DataTables::of($list['record'])
            ->skipPaging()
            ->with([
                "recordsTotal"    => $list['total'],
                "recordsFiltered" => $list['total'],
            ])
            ->make(true);
    }

    public function getUserExercisesData($payload)
    {
        $user        = auth()->user();
        $appTimezone = config('app.timezone');
        $timezone    = (!empty($user->timezone) ? $user->timezone : $appTimezone);

        $userExercisesData = DB::table('user_exercise')
            ->select('user_exercise.*', DB::raw("CONCAT(users.first_name,' ',users.last_name) as fullName"), 'users.email', 'exercises.title')
            ->join("users", "users.id", "=", "user_exercise.user_id")
            ->join("exercises", "exercises.id", "=", "user_exercise.exercise_id")
            ->whereNull("user_exercise.deleted_at");

        if (in_array('searchText', array_keys($payload)) && !empty($payload['searchText'])) {
            $userExercisesData
                ->whereRaw("CONCAT(users.first_name,' ',users.last_name) like ?", ['%' . $payload['searchText'] . '%'])
                ->orWhereRaw("users.email like ?", ['%' . $payload['searchText'] . '%']);
        }

        if ((isset($payload['fromdate']) && !empty($payload['fromdate'] && strtotime($payload['fromdate']) !== false)) && (isset($payload['todate']) && !empty($payload['todate'] && strtotime($payload['todate']) !== false))) {
            $fromdate = Carbon::parse($payload['fromdate'], $timezone)->setTime(0, 0, 0, 0)->setTimezone($appTimezone)->toDateTimeString();
            $todate   = Carbon::parse($payload['todate'], $timezone)->setTime(23, 59, 59, 0)->setTimezone($appTimezone)->toDateTimeString();
            $userExercisesData
                ->where(function ($where) use ($fromdate, $todate) {
                    $where
                        ->whereRaw("TIMESTAMP(user_exercise.start_date) BETWEEN ? AND ?", [$fromdate, $todate])
                        ->orWhereRaw("TIMESTAMP(user_exercise.end_date) BETWEEN ? AND ?", [$fromdate, $todate]);
                });
        }

        $orderColumn = array("user_exercise.id", "fullName", "users.email", "user_exercise.tracker", "exercises.title", "user_exercise.distance", "user_exercise.calories", "user_exercise.duration", "user_exercise.start_date", "user_exercise.created_at");
        $orName      = $orderColumn[$payload['order'][0]['column']];

        if (!empty($orName)) {
            $userExercisesData->orderBy($orName, $payload['order'][0]['dir']);
        } else {
            $userExercisesData->orderBy('user_exercise.id', 'DESC');
        }

        $data           = array();
        $data['total']  = $userExercisesData->count();
        $data['record'] = $userExercisesData->offset($payload['start'])->limit($payload['length'])->get();

        return $data;
    }

    public function getUserMeditationsTableData($payload)
    {
        $list = $this->getUserMeditationsData($payload);

        return DataTables::of($list['record'])
            ->skipPaging()
            ->with([
                "recordsTotal"    => $list['total'],
                "recordsFiltered" => $list['total'],
            ])
            ->make(true);
    }

    public function getUserMeditationsData($payload)
    {
        $user        = auth()->user();
        $appTimezone = config('app.timezone');
        $timezone    = (!empty($user->timezone) ? $user->timezone : $appTimezone);

        $userExercisesData = DB::table('user_listened_tracks')
            ->select('user_listened_tracks.*', DB::raw("CONCAT(users.first_name,' ',users.last_name) as fullName"), 'users.email', 'meditation_tracks.title')
            ->join("users", "users.id", "=", "user_listened_tracks.user_id")
            ->join("meditation_tracks", "meditation_tracks.id", "=", "user_listened_tracks.meditation_track_id");

        if (in_array('searchText', array_keys($payload)) && !empty($payload['searchText'])) {
            $userExercisesData
                ->whereRaw("CONCAT(users.first_name,' ',users.last_name) like ?", ['%' . $payload['searchText'] . '%'])
                ->orWhereRaw("users.email like ?", ['%' . $payload['searchText'] . '%']);
        }

        if ((isset($payload['fromdate']) && !empty($payload['fromdate'] && strtotime($payload['fromdate']) !== false)) && (isset($payload['todate']) && !empty($payload['todate'] && strtotime($payload['todate']) !== false))) {
            $fromdate = Carbon::parse($payload['fromdate'], $timezone)->setTime(0, 0, 0, 0)->setTimezone($appTimezone)->toDateTimeString();
            $todate   = Carbon::parse($payload['todate'], $timezone)->setTime(23, 59, 59, 0)->setTimezone($appTimezone)->toDateTimeString();
            $userExercisesData
                ->where(function ($where) use ($fromdate, $todate) {
                    $where
                        ->whereRaw("TIMESTAMP(user_listened_tracks.created_at) BETWEEN ? AND ?", [$fromdate, $todate]);
                });
        }

        $orderColumn = array("user_listened_tracks.id", "fullName", "users.email", "meditation_tracks.title", "user_listened_tracks.duration_listened", "user_listened_tracks.created_at");
        $orName      = $orderColumn[$payload['order'][0]['column']];

        if (!empty($orName)) {
            $userExercisesData->orderBy($orName, $payload['order'][0]['dir']);
        } else {
            $userExercisesData->orderBy('user_listened_tracks.id', 'DESC');
        }

        $data           = array();
        $data['total']  = $userExercisesData->count();
        $data['record'] = $userExercisesData->offset($payload['start'])->limit($payload['length'])->get();

        return $data;
    }

    public function userUnreadMsgCount()
    {
        $company = $this->company()->first();

        $totalMessageCount = $readCount = $unreadCount = 0;
        $groupIds          = \DB::table("group_members")
            ->where('user_id', $this->id)
            ->pluck('created_at', 'group_id')
            ->toArray();

        if (!empty($groupIds)) {
            foreach ($groupIds as $groupId => $JoinedDate) {
                $totalGMessageCount = \DB::table('group_messages')
                    ->join('user_team', 'user_team.user_id', '=', 'group_messages.user_id')
                    ->where('user_team.company_id', $company->getKey())
                    ->where('group_messages.created_at', '>=', $JoinedDate)
                    ->where('group_messages.group_id', $groupId)
                    ->count();

                $readGCount = \DB::table("group_messages_user_log")
                    ->join('group_messages', 'group_messages.id', '=', 'group_messages_user_log.group_message_id')
                    ->join('user_team', 'user_team.user_id', '=', 'group_messages.user_id')
                    ->where('user_team.company_id', $company->getKey())
                    ->where('group_messages.created_at', '>=', $JoinedDate)
                    ->where('group_messages_user_log.user_id', $this->id)
                    ->where('group_messages_user_log.group_id', $groupId)
                    ->where('group_messages_user_log.read', true)
                    ->count();

                $totalMessageCount += $totalGMessageCount;
                $readCount += $readGCount;
            }
        }

        $unreadCount = $totalMessageCount - $readCount;

        return (!empty($unreadCount)) ? $unreadCount : 0;
    }

    public function getUserNPSTableData($payload)
    {
        $list         = $this->getUserNPSData($payload);
        $feedBackType = config('zevolifesettings.nps_feedback_type');

        return DataTables::of($list)
            ->addColumn('logo', function ($record) {
                $logoUrl = (!empty($record->feedback_type)) ? getStaticNpsEmojiUrl($record->feedback_type) : getDefaultFallbackImageURL('nps', 'logo');
                return '<div class="table-img-sm table-img rounded-circle"><img src="' . $logoUrl . '" alt=""></div>';
            })
            ->addColumn('feedback_emoji', function ($record) use ($feedBackType) {
                return (!empty($feedBackType[$record->feedback_type])) ? $feedBackType[$record->feedback_type] : "";
            })
            ->rawColumns(['logo'])
            ->make(true);
    }

    public function exportNpsDataEntity($payload)
    {
        $user     = auth()->user();
        return \dispatch(new ExportNpsReportJob($payload, $user));
    }

    public function getUserNPSData($payload)
    {
        $role        = getUserRole();
        $companyData = auth()->user()->company()->get()->first();
        $userNPSData = DB::table('user_nps_survey_logs')
            ->select('user_nps_survey_logs.*', DB::raw("CONCAT(users.first_name,' ',users.last_name) as fullName"), 'users.email', 'users.timezone', 'companies.name as companyName')
            ->join("users", "users.id", "=", "user_nps_survey_logs.user_id")
            ->join("user_team", "user_team.user_id", "=", "users.id")
            ->join("companies", "companies.id", "=", "user_team.company_id")
            ->whereNotNull('survey_received_on')
            ->where('is_portal', $payload['isPortal']);

        if ($role->group == 'reseller' && $companyData->parent_id == null) {
            $childCompany = Company::select('id')->where('parent_id', $companyData->id)->orWhere('id', $companyData->id)->get()->pluck('id')->toArray();
            $userNPSData->whereIn('companies.id', $childCompany);
        }
        $userNPSData->orderBy("user_nps_survey_logs.id", "DESC");

        if (in_array('company', array_keys($payload)) && !empty($payload['company'])) {
            $userNPSData->where("companies.id", "=", $payload['company']);
        }

        if (in_array('feedBackType', array_keys($payload)) && !empty($payload['feedBackType']) && $payload['feedBackType'] != "all") {
            $userNPSData->where("user_nps_survey_logs.feedback_type", "=", $payload['feedBackType']);
        }

        return $userNPSData->get();
    }

    public function getUserCourseData($payload)
    {
        $list = $this->getUserCourseRecordList($payload);

        return DataTables::of($list)
            ->addColumn('logo', function ($record) {
                $coverImage = $record->logo;
                $coverImage = (!empty($coverImage) ? $coverImage : asset('assets/dist/img/boxed-bg.png'));
                return '<img class="tbl-user-img img-circle elevation-2" src="' . $coverImage . '" width="70" />';
            })
            ->addColumn('joined_on', function ($record) {
                return $record->pivot->joined_on;
            })
            ->addColumn('category', function ($record) {
                return $record->subcategory->name;
            })
            ->rawColumns(['logo'])
            ->make(true);
    }

    public function getUserCourseRecordList($payload)
    {
        $userCourseData = $this->courseLogs()
            ->wherePivot('joined', true)
            ->orderBy('user_course.joined_on');

        if ($payload['type'] == "enrolled") {
            $userCourseData->wherePivot('completed', false);
        } else {
            $userCourseData->wherePivot('completed', true);
        }

        return $userCourseData->get();
    }

    public function getUserChallangeData($payload)
    {
        $list     = $this->getUserChallangeRecordList($payload);
        $timezone = $this->timezone ?? config('app.timezone');

        return DataTables::of($list)
            ->addColumn('logo', function ($record) {
                $coverImage = $record->logo;
                if (!empty($coverImage)) {
                    return '<img class="tbl-user-img img-circle elevation-2" src="' . $coverImage . '" width="70" />';
                } else {
                    return '<img class="tbl-user-img img-circle elevation-2" src="' . asset('assets/dist/img/boxed-bg.png') . '" width="70" />';
                }
            })
            ->addColumn('updatedAt', function ($record) {
                return $record->updatedAt;
            })
            ->addColumn('date', function ($record) use ($timezone) {
                return Carbon::parse($record->start_date)->setTimezone($timezone)->format(config('zevolifesettings.date_format.default_datetime')) . ' - ' . Carbon::parse($record->end_date)->setTimezone($timezone)->format(config('zevolifesettings.date_format.default_datetime'));
            })
            ->rawColumns(['logo'])
            ->make(true);
    }

    public function getUserChallangeRecordList($payload)
    {
        $timezone             = $this->timezone;
        $exploreChallengeData = Challenge::
            leftJoin("challenge_participants", "challenges.id", "=", "challenge_participants.challenge_id")
            ->select("challenges.*", "challenge_participants.updated_at as updatedAt")
            ->where("challenges.cancelled", false)
            ->where("challenge_participants.status", "Accepted")
            ->where(function ($query) {
                $query->where("challenge_participants.user_id", $this->id)
                    ->orWhere("challenge_participants.team_id", $this->teams()->first()->id);
            })
            ->orderBy('challenges.id', 'DESC')
            ->groupBy('challenges.id');

        if ($payload['type'] == "completed") {
            $exploreChallengeData->where('end_date', '<', now($timezone)->toDateTimeString());
        } else {
            $exploreChallengeData->where('end_date', '>=', now($timezone)->toDateTimeString());
        }

        return $exploreChallengeData->get();
    }

    /**
     * @return BelongsToMany
     */
    public function userTrackrLogs(): BelongsToMany
    {
        return $this->belongsToMany(MeditationTrack::class, 'user_meditation_track_logs', 'user_id', 'meditation_track_id')->withPivot('id', 'saved', 'liked', 'favourited')->withTimestamps();
    }

    public function courseWeekLogs(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\CourseWeek', 'user_course_week', 'user_id', 'course_week_id')->withPivot('course_id', 'status', 'completed_at')->withTimestamps();
    }

    /**
     * @return BelongsToMany
     */
    public function recipeLogs(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\Recipe', 'recipe_user', 'user_id', 'recipe_id')
            ->withPivot('saved', 'saved_at', 'favourited', 'favourited_at', 'liked')
            ->withTimestamps();
    }

    /**
     * @return BelongsToMany
     */
    public function userPodcastLogs(): BelongsToMany
    {
        return $this->belongsToMany(Podcast::class, 'user_podcast_logs', 'user_id', 'podcast_id')->withPivot('id', 'saved', 'liked', 'favourited')->withTimestamps();
    }

    /**
     * @return BelongsToMany
     */
    public function inCompletedPodcasts(): BelongsToMany
    {
        return $this->belongsToMany(Podcast::class, 'user_incompleted_podcasts', 'user_id', 'podcast_id')->withPivot('duration_listened')->withTimestamps();
    }

    /**
     * @return BelongsToMany
     */
    public function completedPodcasts(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\Podcast', 'user_listened_podcasts', 'user_id', 'podcast_id')->withPivot('duration_listened')->withTimestamps();
    }

    /**
     * @return BelongsToMany
     */
    public function userShortsLogs(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\Shorts', 'shorts_user', 'user_id', 'short_id')->withPivot('id', 'saved', 'liked', 'favourited','view_count')->withTimestamps();
    }

    /**
     * @return void
     */
    public function sendMoodSurvey(): void
    {
        $notification_setting = $this
            ->notificationSettings()
            ->select('flag')
            ->where('flag', 1)
            ->where(function ($query) {
                $query->where('module', '=', 'moods')
                    ->orWhere('module', '=', 'all');
            })
            ->first();

        $date = date('Y-m-d 09:59:00');
        $time = Carbon::parse($date, $this->timezone)
            ->setTimezone(config('app.timezone'))
            ->todatetimeString();

        \dispatch(new SendMoodPushNotificationJob($this, 'mood', [
            'type'         => 'Manual',
            'push'         => ($notification_setting->flag ?? false),
            'scheduled_at' => $time,
        ]));
    }

    /**
     * @return HasMany
     */
    public function surveyUserLogs(): HasMany
    {
        return $this->HasMany('App\Models\ZcSurveyUserLog', 'user_id', 'id');
    }

    /**
     * @return BelongsTo
     */
    public function userGoalTags(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\Goal', 'user_goal_tags', 'user_id', 'goal_id')->withTimestamps();
    }

    /**
     * This function will return companies and roles according to role
     *
     * @param $payload - array
     *
     * @return array
     */
    public function getRoleWiseCompanies($payload)
    {
        $role    = ($payload['role_group'] ?? "");
        $company = ($payload['company'] ?? 0);
        $data    = [];

        if (empty($company)) {
            if ($role == 'zevo') {
                $data['roles'] = Role::select('name', 'id')
                    ->where('group', 'zevo')
                    ->whereNotIn('slug', ['user', 'health_coach'])
                    ->get()->pluck('name', 'id')->toArray();
            } elseif ($role == 'company') {
                $data['companies'] = Company::select('id', 'name')
                    ->where('is_reseller', 0)
                    ->whereNull('parent_id')
                    ->get()->pluck('name', 'id')->toArray();
            } elseif ($role == 'reseller') {
                $data['companies'] = Company::select('id', 'name')
                    ->where(function ($where) {
                        $where
                            ->where('is_reseller', 1)
                            ->orWhereNotNull('parent_id');
                    })
                    ->get()->pluck('name', 'id')->toArray();
            }
        } else {
            $company             = Company::find($company, ['companies.id', 'companies.is_reseller', 'companies.parent_id']);
            $data['departments'] = Department::select('name', 'id')->where('company_id', $company->id)->pluck('name', 'id')->toArray();
            $data['roles']       = CompanyRoles::select('roles.id', 'roles.name')
                ->join('roles', function ($join) {
                    $join->on('roles.id', '=', 'company_roles.role_id');
                })
                ->where('company_roles.company_id', $company->id)
                ->get()->pluck('name', 'id')->toArray();
            if (!$company->is_reseller && is_null($company->parent_id)) {
                $data['roles'] = array_replace([2 => 'Zevo Company Admin'], $data['roles']);
            } elseif ($company->is_reseller && is_null($company->parent_id)) {
                $rsaRole       = Role::where(['slug' => 'reseller_super_admin', 'default' => 1])->first();
                $data['roles'] = array_replace([$rsaRole->id => $rsaRole->name], $data['roles']);
            } elseif (!$company->is_reseller && !is_null($company->parent_id)) {
                $rcaRole       = Role::where(['slug' => 'reseller_company_admin', 'default' => 1])->first();
                $data['roles'] = array_replace([$rcaRole->id => $rcaRole->name], $data['roles']);
            }
        }

        return $data;
    }

    /**
     * Set Table data for Tracker change history
     *
     * @param array $payload
     * @param array $user
     *
     * @return array
     */
    public function getTrackerHistoryTableData($payload, $user)
    {
        $list = $this->getTrackerHistoryRecordList($payload, $user);
        return DataTables::of($list['record'])
            ->skipPaging()
            ->with([
                "recordsTotal"    => $list['total'],
                "recordsFiltered" => $list['total'],
            ])
            ->addColumn('tracker_name', function ($record) {
                return $record->tracker;
            })
            ->addColumn('tracker_change_date_time', function ($record) {
                return date(config('zevolifesettings.date_format.moment_default_datetimesecond'), strtotime($record->log_date));
            })
            ->rawColumns(['actions'])
            ->make(true);
    }

    /**
     * Get Records for Tracker change history
     *
     * @param array $payload
     * @param array $user
     *
     * @return array
     */
    public function getTrackerHistoryRecordList($payload, $user)
    {
        $query = UserDeviceHistory::select('id', 'user_id', 'tracker', 'log_date')
            ->where('user_id', $user->id);

        if (in_array('trackername', array_keys($payload)) && !empty($payload['trackername'])) {
            $query->where("tracker", 'like', '%' . $payload['trackername'] . '%');
        }

        if ((isset($payload['fromdate']) && !empty($payload['fromdate'] && strtotime($payload['fromdate']) !== false)) && (isset($payload['todate']) && !empty($payload['todate'] && strtotime($payload['todate']) !== false))) {
            $fromdate = Carbon::parse($payload['fromdate'])->setTime(0, 0, 0, 0)->toDateTimeString();
            $todate   = Carbon::parse($payload['todate'])->setTime(23, 59, 59, 0)->toDateTimeString();

            $query
                ->where(function ($where) use ($fromdate, $todate) {
                    $where
                        ->whereRaw("TIMESTAMP(log_date) BETWEEN ? AND ?", [$fromdate, $todate])
                        ->orWhereRaw("TIMESTAMP(log_date) BETWEEN ? AND ?", [$fromdate, $todate]);
                });
        }

        if (isset($payload['order'])) {
            $column = $payload['columns'][$payload['order'][0]['column']]['data'];
            $order  = $payload['order'][0]['dir'];
            switch ($column) {
                case 'tracker_change_date_time':
                    $columnName = "log_date";
                    break;
                case 'tracker_name':
                    $columnName = "tracker";
                    break;
                default:
                    $columnName = "log_date";
                    break;
            }
            $query->orderBy($columnName, $order);
        } else {
            $query->orderByDesc('log_date');
        }

        $data              = [];
        $data['total']     = $query->get()->count();
        $payload['length'] = (!empty($payload['length']) ? $payload['length'] : config('zevolifesettings.datatable.pagination.short'));
        $data['record']    = $query->offset($payload['start'])->limit($payload['length'])->get();
        return $data;
    }

    /**
     * Sync user with company survey users if any survey is active for the company and
     * send survey to all is set to false
     *
     * @param boolean $touch
     * @return void
     */
    public function syncWithSurveyUsers($touch = false)
    {
        $company = $this->company()->select('companies.id')->first();
        if (!empty($company)) {
            $survey = $company->survey()
                ->select('zc_survey_settings.id', 'zc_survey_settings.survey_to_all', 'zc_survey_settings.team_ids')
                ->first();
            if (!empty($survey) && !$survey->survey_to_all) {
                $team = $this->teams()->select('teams.id')->first();
                if (in_array($team->id, $survey->team_ids)) {
                    $company->surveyUsers()->sync($this, false);
                } elseif ($touch) {
                    $company->surveyUsers()->detach($this);
                }
            }
        }
    }

    /**
     * "hasMany" relation to `eap_csat_user_logs` table
     * via `user_id` field.
     *
     * @return hasMany
     */
    public function eapCsat(): hasMany
    {
        return $this->hasMany(EapCsatLogs::class, 'user_id');
    }

    /**
     * Set datatable for user registration record list.
     *
     * @param payload
     * @return dataTable
     */

    public function getUserRegistrationTableData($payload)
    {
        $list = $this->getUserRegistrationRecordList($payload);
        return DataTables::of($list['record'])
            ->skipPaging()
            ->with([
                "recordsTotal"    => $list['total'],
                "recordsFiltered" => $list['total'],
            ])
            ->make(true);
    }

    /**
     * get user registration record list for data table list.
     *
     * @param payload
     * @return recordList
     */

    public function getUserRegistrationRecordList($payload)
    {
        $user        = auth()->user();
        $company     = $user->company()->first();
        $role        = getUserRole($user);
        $appTimezone = config('app.timezone');
        $timezone    = (!empty($user->timezone) ? $user->timezone : $appTimezone);
        $query       = $this
            ->leftJoin('user_team', function ($join) {
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
            })
            ->leftJoin("departments", function ($join) {
                $join
                    ->on('user_team.user_id', '=', 'users.id')
                    ->on('user_team.department_id', '=', 'departments.id');
            })
            ->leftJoin('teams', function ($join) {
                $join
                    ->on('users.id', '=', 'user_team.user_id')
                    ->on('user_team.team_id', '=', 'teams.id');
            })
            ->leftJoin('role_user', function ($join) {
                $join->on('role_user.user_id', '=', 'users.id');
            })
            ->leftJoin('roles', function ($join) {
                $join->on('roles.id', '=', 'role_user.role_id');
            })
            ->select(
                'users.id',
                'users.email',
                'companies.name as companyName',
                'teams.name as teamName',
                'users.updated_at',
                'users.is_blocked',
                'roles.name AS roleName',
                'roles.group AS roleGroup',
                'departments.name AS departmentName',
                'company_locations.name AS locationName',
                'users.created_at',
                DB::raw("CONCAT(users.first_name, ' ', users.last_name) as fullName"),
                DB::raw("(SELECT count(id) FROM user_device_history WHERE user_id = users.id) AS step_sync_count")
            )
            ->where('users.email', '!=', 'superadmin@grr.la')
            ->where('users.id', '!=', $user->id)
            ->where(function ($where) use ($role, $company) {
                if ($role->group == 'company') {
                    $where->where('user_team.company_id', '=', $company->id);
                } elseif ($role->group == 'reseller') {
                    $where
                        ->where('user_team.company_id', '=', $company->id)
                        ->orWhere('companies.parent_id', '=', $company->id);
                }
            })
            ->groupBy('users.id');

        if (in_array('company', array_keys($payload)) && !empty($payload['company'])) {
            $query->where('user_team.company_id', $payload['company']);
        }
        if ((in_array('fromDate', array_keys($payload)) && isset($payload['fromDate']) && !empty($payload['fromDate']) && strtotime($payload['fromDate']) !== false) && (in_array('toDate', array_keys($payload)) && isset($payload['toDate']) && !empty($payload['toDate']) && strtotime($payload['toDate']) !== false)) {
            $fromDate = Carbon::parse($payload['fromDate'], $timezone)->setTime(0, 0, 0, 0)->setTimezone($appTimezone)->toDateTimeString();
            $toDate   = Carbon::parse($payload['toDate'], $timezone)->setTime(23, 59, 59, 0)->setTimezone($appTimezone)->toDateTimeString();
            $query->whereBetween('users.created_at', [$fromDate, $toDate]);
        }

        if (in_array('rolename', array_keys($payload)) && !empty($payload['rolename'])) {
            $query->where('roles.id', $payload['rolename']);
        }

        if (in_array('rolegroup', array_keys($payload)) && !empty($payload['rolegroup'])) {
            $query->where('roles.group', $payload['rolegroup']);
        }

        if (isset($payload['order'])) {
            $column = $payload['columns'][$payload['order'][0]['column']]['data'];
            $order  = $payload['order'][0]['dir'];
            $query->orderBy($column, $order);
        } else {
            $query->orderByDesc('users.id');
        }

        $data              = [];
        $data['total']     = $query->get()->count();
        $payload['length'] = (!empty($payload['length']) ? $payload['length'] : config('zevolifesettings.datatable.pagination.short'));
        $payload['length'] = (($payload['length'] == '-1') ? $data['total'] : $payload['length']);
        $data['record']    = $query->offset($payload['start'])->limit($payload['length'])->get();

        return $data;
    }

    /**
     * get user registration record list for data table list.
     *
     * @param payload
     * @return recordList
     */
    public function updateSlot($payload)
    {
        $response = [];
        // to set hc user day wise slots
        if (!empty($payload['slots'])) {
            $existingSlots = $this->healthCocahSlots->pluck('id')->toArray();
            $updateSlotIds = [];
            foreach ($payload['slots'] as $day => $slots) {
                foreach ($slots['start_time'] as $key => $time) {
                    if (!empty($key)) {
                        $updateSlotIds[] = $key;
                    }
                    $start_time = Carbon::createFromFormat('H:i', $time, $this->timezone);
                    $end_time   = Carbon::createFromFormat('H:i', $slots['end_time'][$key], $this->timezone);

                    $response = $this->healthCocahSlots()->updateOrCreate([
                        'id' => ((!empty($key) && is_numeric($key)) ? $key : 0),
                    ], [
                        'day'        => $day,
                        'start_time' => $start_time->format('H:i:00'),
                        'end_time'   => $end_time->format('H:i:59'),
                    ]);
                }
            }

            $removeIds = array_diff($existingSlots, $updateSlotIds);
            if (!empty($removeIds)) {
                $this->healthCocahSlots()->whereIn('id', $removeIds)->delete();
            }
        }
        if (!empty($response)) {
            return array('status' => 'true');
        }
        return array('status' => 'false');
    }

    /**
     * get user registration record list for data table list.
     *
     * @param payload
     * @return recordList
     */
    public function updatePresenterSlot($payload)
    {
        $response = [];
        // to set hc user day wise slots
        if (!empty($payload['presenter_slots'])) {
            $existingSlots = $this->eventPresenterSlots->pluck('id')->toArray();
            $updateSlotIds = [];
            foreach ($payload['presenter_slots'] as $day => $slots) {
                foreach ($slots['start_time'] as $key => $time) {
                    if (!empty($key)) {
                        $updateSlotIds[] = $key;
                    }
                    $start_time = Carbon::createFromFormat('H:i', $time, $this->timezone);
                    $end_time   = Carbon::createFromFormat('H:i', $slots['end_time'][$key], $this->timezone);

                    $response = $this->eventPresenterSlots()->updateOrCreate([
                        'id' => ((!empty($key) && is_numeric($key)) ? $key : 0),
                    ], [
                        'day'        => $day,
                        'start_time' => $start_time->format('H:i:00'),
                        'end_time'   => $end_time->format('H:i:59'),
                    ]);
                }
            }

            $removeIds = array_diff($existingSlots, $updateSlotIds);
            if (!empty($removeIds)) {
                $this->eventPresenterSlots()->whereIn('id', $removeIds)->delete();
            }
        }
        if (!empty($response)) {
            return array('status' => 'true');
        }
        return array('status' => 'false');
    }

    public function exportUserRegistrationDataEntity($payload)
    {
        $user     = auth()->user();
        return \dispatch(new ExportUserRegistrationReportJob($payload, $user));
    }

    public function exportTrackerDataEntity($payload, $user)
    {
        return \dispatch(new ExportUserTrackerHistoryJob($payload, $user));
    }

    public function exportUserActivityDataEntity($payload)
    {
        $user     = auth()->user();
        return \dispatch(new ExportUserActivityJob($payload, $user));
    }

    /**
     * get usage report
     * @param $payload
     *
     * @return object
     */
    public function getUsageReport($payload)
    {
        $list = $this->getUsageReportRecordList($payload);

        return DataTables::of($list)
            ->addColumn('company_name', function ($record) {
                return $record->company_name;
            })
            ->addColumn('location', function ($record) {
                return $record->location;
            })
            ->addColumn('registed_user', function ($record) {
                return $record->totalUsers;
            })
            ->addColumn('active_7_days', function ($record) {
                return $record->activeUsersForLast7Days;
            })
            ->addColumn('active_30_days', function ($record) {
                return $record->activeUsersForLast30Days;
            })
            ->rawColumns([])
            ->make(true);
    }

    /**
     * get usage report
     * @param $payload
     *
     * @return object
     */
    public function getUsageReportRecordList($payload)
    {
        $user       = auth()->user();
        $timezone   = $user->timezone ?? null;
        $timezone   = !empty($timezone) ? $timezone : config('app.timezone');
        $last30Days = Carbon::parse(now()->toDateTimeString())->setTimeZone($timezone)->subDays(30)->format('Y-m-d 00:00:00');
        $last7Days  = Carbon::parse(now()->toDateTimeString())->setTimeZone($timezone)->subDays(7)->format('Y-m-d 00:00:00');

        $query = $this
            ->select(
                'companies.name as company_name',
                'company_locations.name as location',
                \DB::raw('COUNT(users.id) as totalUsers')
            )
            ->selectRaw("IFNULL(SUM(DATE(CONVERT_TZ(users.last_activity_at, ? , ?)) >= ?), 0) as activeUsersForLast7Days",[
                'UTC',$timezone,$last7Days
            ])
            ->selectRaw("IFNULL(SUM(DATE(CONVERT_TZ(users.last_activity_at, ? , ?)) >= ?), 0) as activeUsersForLast30Days",[
                'UTC',$timezone,$last30Days
            ])
            ->leftJoin('user_team', 'user_team.user_id', '=', 'users.id')
            ->leftJoin('team_location', 'team_location.team_id', '=', 'user_team.team_id')
            ->leftJoin('company_locations', 'company_locations.id', '=', 'team_location.company_location_id')
            ->leftJoin('companies', 'companies.id', '=', 'user_team.company_id')
            ->whereNotNull('companies.id')
            ->groupBy('company_locations.id');

        if (isset($payload['company']) && !empty($payload['company'])) {
            $query->where('companies.id', $payload['company']);
        }

        if (isset($payload['location']) && !empty($payload['location'])) {
            $query->where('company_locations.id', $payload['location']);
        }

        return $query->get();
    }

    /**
     * get usage report
     * @param $payload
     *
     * @return object
     */
    public function ExportUsageReport($payload)
    {
        $user    = auth()->user();
        $records = $this->getUsageReportRecordList($payload);
        $email   = ($payload['email'] ?? $user->email);

        if ($records) {
            // Generate usage export report
            \dispatch(new UsageReportExportJob($records->toArray(), $email, $user));
            return true;
        }
    }
}
