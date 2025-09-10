<?php

namespace App\Models;

use App\Jobs\ExportChallengeActivityReportJob;
use App\Jobs\ExportChallengeDetailJob;
use App\Jobs\ExportChallengeUserActivityReportJob;
use App\Jobs\ExportInterCompanyReportJob;
use App\Jobs\IntercompanyChallengeJob;
use App\Jobs\SendChallengePushNotification;
use App\Jobs\SendDeletePushNotifications;
use App\Jobs\SendGroupPushNotification;
use App\Models\ChallengeExportHistory;
use App\Models\ChallengeParticipant;
use App\Models\Company;
use App\Models\ContentChallenge;
use App\Models\Department;
use App\Models\DepartmentLocation;
use App\Models\Group;
use App\Models\NotificationSetting;
use App\Models\SubCategory;
use App\Models\User;
use App\Models\UserTeam;
use App\Notifications\SystemAutoNotification;
use App\Observers\ChallengeObserver;
use Carbon\Carbon;
use DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Laravel\Telescope\Telescope;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Yajra\DataTables\Facades\DataTables;

class Challenge extends Model implements HasMedia
{

    use InteractsWithMedia;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'challenges';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = [
        'creator_id',
        'company_id',
        'challenge_category_id',
        'challenge_type',
        'parent_id',
        'timezone',
        'title',
        'description',
        'start_date',
        'end_date',
        'close',
        'finished',
        'cancelled',
        'recurring',
        'is_badge',
        'recurring_count',
        'recurring_type',
        'deep_link_uri',
        'challenge_end_at',
        'freezed_data_at',
        'library_image_id',
        'map_id',
        'locations',
        'departments',
        'deleted_by',
        'deleted_reason',
        'iteration',
        'job_finished',
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
    protected $casts = [
        'close'     => 'boolean',
        'finished'  => 'boolean',
        'cancelled' => 'boolean',
        'recurring' => 'boolean',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['start_date', 'end_date', 'challenge_end_at', 'freezed_data_at'];

    /**
     * Boot model
     */
    protected static function boot()
    {
        parent::boot();

        static::observe(ChallengeObserver::class);
    }

    /**
     * Custom builder instantiator. newEloquentBuilder is part
     * of Laravel.
     */
    public function newEloquentBuilder($query)
    {
        return new \App\Builders\BaseBuilder($query);
    }

    /**
     * @return BelongsTo
     */
    public function challengecategory(): BelongsTo
    {
        return $this->belongsTo('App\Models\ChallengeCategory', 'challenge_category_id');
    }

    /**
     * @return BelongsTo
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo('App\Models\Company', 'company_id');
    }

    /**
     * @return HasMany
     */
    public function challengeRules(): HasMany
    {
        return $this->hasMany('App\Models\ChallengeRule');
    }

    /**
     * @return exporthistroy
     */
    public function challengeExportHistory(): HasMany
    {
        return $this->hasMany('App\Models\ChallengeExportHistory');
    }

    /**
     * @return BelongsToMany
     */
    public function challengeBadges(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\Badge', 'challenge_badges', 'challenge_id', 'badge_id')->withTimestamps();
    }

    /**
     * @return BelongsToMany
     */
    public function ongoingChallengeBadges(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\Badge', 'challenge_ongoing_badge_users', 'challenge_id', 'badge_id')->withTimestamps();
    }

    /**
     * @return BelongsToMany
     */
    public function members(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\User', 'challenge_participants', 'challenge_id', 'user_id')->withPivot('status')->withTimestamps();
    }

    /**
     * @return BelongsToMany
     */
    public function memberTeams(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\Team', 'challenge_participants', 'challenge_id', 'team_id')->withPivot('status', 'company_id')->withTimestamps();
    }

    /**
     * @return BelongsToMany
     */
    public function memberCompanies(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\Company', 'challenge_participants', 'challenge_id', 'company_id')->withPivot('status', 'team_id')->withTimestamps();
    }

    /**
     * @return BelongsToMany
     */
    public function membersHistory(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\User', 'freezed_challenge_participents', 'challenge_id', 'user_id')->withTimestamps();
    }

    /**
     * @return BelongsToMany
     */
    public function memberTeamsHistory(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\Team', 'freezed_challenge_participents', 'challenge_id', 'team_id')->withPivot('company_id')->withTimestamps();
    }

    /**
     * @return BelongsToMany
     */
    public function memberCompaniesHistory(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\Company', 'freezed_challenge_participents', 'challenge_id', 'company_id')->withTimestamps();
    }

    /**
     * @return BelongsToMany
     */
    public function assignBadgeToOngoingChallenge(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\Badge', 'challenge_ongoing_badges', 'challenge_id', 'badge_id')->withTimestamps();
    }

    /**
     * @return HasMany
     */
    public function challengeHistoryTeamParticipents(): HasMany
    {
        return $this->hasMany(ChallengeHistoryTeamParticipents::class);
    }

    /**
     * @return HasMany
     */
    public function challengeWiseManualPoints(): HasMany
    {
        return $this->hasMany('App\Models\ChallengeExtraPoint');
    }

    /**
     * @return BelongsTo
     */
    public function libraryImage(): BelongsTo
    {
        return $this->belongsTo('App\Models\ChallengeImageLibrary', 'library_image_id');
    }

    /**
     * @return BelongsTo
     */
    public function mapLibrary(): BelongsTo
    {
        return $this->belongsTo('App\Models\ChallengeMapLibrary', 'map_id');
    }

    /**
     * @return string
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversion
     */
    public function getLogoAttribute()
    {
        return $this->getLogo(['w' => 320, 'h' => 160]);
    }

    /**
     * @return string
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversion
     */
    public function getLogoNameAttribute()
    {
        $media = null;
        if (!is_null($this->library_image_id)) {
            $media = $this->libraryimage()->withTrashed()->first()->getFirstMedia('image');
        } else {
            $media = $this->getFirstMedia('logo');
        }
        return (!empty($media) ? $media->name : 'Choose File');
    }

    /**
     * @param string $params
     *
     * @return string
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversion
     */
    public function getLogo(array $params): string
    {
        if (!is_null($this->library_image_id)) {
            $media = $this->libraryimage()->withTrashed()->first()->getFirstMedia('image');
        } else {
            $media = $this->getFirstMedia('logo');
        }

        if (!is_null($media) && $media->count() > 0) {
            $params['src'] = $media->getURL();
        }
        return getThumbURL($params, 'challenge', 'logo');
    }

    /**
     * @param null|string $part
     *
     * @return array
     */
    public function getAllowedMediaMimeTypes( ? string $part) : array
    {
        $mimeTypes = [
            'logo'  => [
                'image/jpeg',
                'image/jpg',
                'image/png',
            ],
            'video' => [
                'video/mp4',
                'video/webm',
            ],
        ];

        return \in_array($part, ['logo', 'video'], true)
        ? $mimeTypes[$part]
        : $mimeTypes;
    }

    /**
     * Set datatable for groups list.
     *
     * @param payload
     * @return dataTable
     */
    public function getTableData($payload)
    {
        $explodeRoute     = explode('.', \Route::currentRouteName());
        $route            = $explodeRoute[1];
        $payload['route'] = $route;
        $list             = $this->getRecordList($payload);
        $user             = auth()->user();
        $timezone         = $user->timezone ?? config('app.timezone');

        return DataTables::of($list['record'])
            ->skipPaging()
            ->with([
                "recordsTotal"    => $list['total'],
                "recordsFiltered" => $list['total'],
            ])
            ->addColumn('updated_at', function ($record) {
                return $record->updated_at;
            })
            ->addColumn('title', function ($record) use ($route) {
                $company   = \Auth::user()->company->first();
                $companyId = isset($company) ? $company->id : null;

                if ($route != 'interCompanyChallenges') {
                    if (!access()->allow('view-challenge') || $record->company_id != $companyId) {
                        return $record->title;
                    } else {
                        return '<a href="' . route("admin.$route.details", $record->id) . '">' . $record->title . '</a>';
                    }
                }

                if ($route == 'interCompanyChallenges') {
                    if (!access()->allow('view-inter-company-challenge')) {
                        return $record->title;
                    } else {
                        return '<a href="' . route("admin.$route.details", $record->id) . '">' . $record->title . '</a>';
                    }
                }
            })
            ->addColumn('challengecategory', function ($record) {
                return $record['challengecategory']['name'];
            })
            ->addColumn('target', function ($record) {
                $targets = $record['challengeRules']->map(function ($item) {
                    return $item->challengeTarget->name;
                })->toArray();
                return implode(', ', $targets);
            })
            ->addColumn('challengeDate', function ($record) use ($timezone) {
                return Carbon::parse($record->start_date)->setTimezone($timezone)->format(config('zevolifesettings.date_format.default_datetime')) . " - " . Carbon::parse($record->end_date)->setTimezone($timezone)->format(config('zevolifesettings.date_format.default_datetime'));
            })
            ->addColumn('recurring', function ($record) {
                if ($record->recurring == 1) {
                    return 'Yes';
                } else {
                    return 'No';
                }
            })
            ->addColumn('logo', function ($record) {
                $logo = $record->getLogoAttribute();
                return '<div class="table-img table-img-l"><img src="' . $logo . '" /></div>';
            })
            ->addColumn('challengeStatus', function ($record) use ($timezone) {
                $start_date = Carbon::parse($record->start_date)->setTimezone($timezone)->toDateTimeString();
                $endDate    = Carbon::parse($record->end_date)->setTimezone($timezone)->toDateTimeString();
                $now        = now($timezone)->toDateTimeString();

                if ($record->finished) {
                    $statusClass = 'text-success';
                    $status      = 'Completed';
                } elseif ($record->cancelled) {
                    $statusClass = 'text-danger';
                    $status      = 'Cancelled';
                } elseif ($start_date > $now) {
                    $statusClass = 'text-warning';
                    $status      = 'Upcoming';
                } elseif ($start_date <= $now && $endDate >= $now) {
                    $statusClass = 'text-primary';
                    $status      = 'Ongoing';
                } else {
                    $statusClass = 'text-success';
                    $status      = 'Completed';
                }
                return '<span class=' . $statusClass . '>' . $status . '</span>';
            })
            ->addColumn('actions', function ($record) use ($timezone, $route) {
                $start_date = Carbon::parse($record->start_date)->setTimezone($timezone)->toDateTimeString();
                $endDate    = Carbon::parse($record->end_date)->setTimezone($timezone)->toDateTimeString();
                $now        = now($timezone)->toDateTimeString();
                $company    = \Auth::user()->company->first();
                $companyId  = isset($company) ? $company->id : null;

                return view('admin.challenge.listaction', compact('record', 'endDate', 'start_date', 'now', 'route', 'companyId'))->render();
            })
            ->rawColumns(['actions', 'logo', 'title', 'challengeStatus'])
            ->make(true);
    }

    /**
     * get record list for data table list.
     *
     * @param payload
     * @return roleList
     */
    public function getRecordList($payload)
    {
        $user     = auth()->user();
        $company  = $user->company->first();
        $timezone = config('app.timezone');

        if ($payload['route'] == 'challenges') {
            $challenge_type = 'individual';
        } elseif ($payload['route'] == 'teamChallenges') {
            $challenge_type = 'team';
        } elseif ($payload['route'] == 'companyGoalChallenges') {
            $challenge_type = 'company_goal';
        } elseif ($payload['route'] == 'interCompanyChallenges') {
            $challenge_type = 'inter_company';
        }

        $query = self::where('challenge_type', $challenge_type)
            ->with([
                'challengecategory' => function ($query) {
                    $query->select('id', 'name');
                },
                'challengeRules'    => function ($query) {
                    $query->select('id', 'challenge_id', 'challenge_target_id')
                        ->with(['challengeTarget' => function ($query) {
                            $query->select('id', 'name');
                        }]);
                },
                'media',
            ])
            ->select(
                'challenges.id',
                'challenges.creator_id',
                'challenges.company_id',
                'challenges.challenge_category_id',
                'challenges.parent_id',
                'challenges.library_image_id',
                'challenges.challenge_type',
                'challenges.timezone',
                'challenges.title',
                'challenges.start_date',
                'challenges.end_date',
                'challenges.challenge_end_at',
                'challenges.freezed_data_at',
                'challenges.close',
                'challenges.finished',
                'challenges.cancelled',
                'challenges.recurring',
                'challenges.recurring_count',
                'challenges.recurring_type',
                'challenges.recurring_completed',
                'challenges.created_at',
                'challenges.updated_at'
            )
            ->where(function ($query) use ($payload, $user) {
                if ($payload['route'] != 'interCompanyChallenges') {
                    $query->where('company_id', $user->company->first()->id)
                        ->orWhere('company_id', null);
                } else {
                    $query->where('company_id', null);
                }
            });

        if ($payload['route'] == 'interCompanyChallenges' && isset($company)) {
            $query = $query
                ->with('memberCompanies')
                ->whereHas('memberCompanies', function ($query) use ($company) {
                    $query->where('company_id', $company->id);
                });
        }

        if (in_array('challengeStatus', array_keys($payload)) && !empty($payload['challengeStatus'])) {
            if ($payload['challengeStatus'] == "ongoing") {
                $query->where('start_date', '<=', now($timezone)->toDateTimeString())->where('end_date', '>=', now($timezone)->toDateTimeString())->where("cancelled", false);
            } elseif ($payload['challengeStatus'] == "upcoming") {
                $query->where('start_date', '>', now($timezone)->toDateTimeString())->where("cancelled", false);
            } elseif ($payload['challengeStatus'] == "finished") {
                $query->where('end_date', '<', now($timezone)->toDateTimeString())->where("cancelled", false);
            } elseif ($payload['challengeStatus'] == "cancelled") {
                $query->where("cancelled", true);
            }
        }

        if (in_array('challengeName', array_keys($payload)) && !empty($payload['challengeName'])) {
            $query->where('title', 'like', '%' . $payload['challengeName'] . '%');
        }

        if (in_array('recursive', array_keys($payload)) && !empty($payload['recursive'])) {
            if ($payload['recursive'] == "yes") {
                $query->where('recurring', '=', 1);
            } elseif ($payload['recursive'] == "no") {
                $query->where('recurring', '=', 0);
            }
        }

        if (in_array('challengeCategory', array_keys($payload)) && !empty($payload['challengeCategory'])) {
            $query->where('challenge_category_id', $payload['challengeCategory']);
        }
        $query = $query->orderBy('updated_at', 'DESC');
        $data              = [];
        $data['total']     = $query->get()->count();
        $payload['length'] = (!empty($payload['length']) ? $payload['length'] : config('zevolifesettings.datatable.pagination.short'));
        $payload['length'] = (($payload['length'] == '-1') ? $data['total'] : $payload['length']);
        $data['record']    = $query->offset($payload['start'])->limit($payload['length'])->get();

        return $data;
    }

    /**
     * store record data.
     *
     * @param payload
     * @return boolean
     */
    public function storeEntity(array $payload)
    {
        $user         = auth()->user();
        $userTimeZone = $user->timezone;
        $dayValue     = config('zevolifesettings.recurring_day_value');

        // dates
        $start_date = Carbon::parse($payload['start_date'], $userTimeZone)->setTime(0, 0, 0)->setTimezone(\config('app.timezone'));

        if (!empty($payload['recursive']) && $payload['recursive'] == "yes") {
            $edDate   = date('Y-m-d', strtotime($payload['start_date'] . ' + ' . ($dayValue[$payload['recursive_type']] - 1) . ' days'));
            $end_date = Carbon::parse($edDate, $userTimeZone)->setTime(23, 59, 59)->setTimezone(\config('app.timezone'));
        } else {
            $end_date = Carbon::parse($payload['end_date'], $userTimeZone)->setTime(23, 59, 59)->setTimezone(\config('app.timezone'));
        }

        // it's related to map challenge
        $payload['challenge_category'] = (empty($payload['select_map'])) ? $payload['challenge_category'] : 1;

        $challengeInput = [
            'creator_id'            => $user->id,
            'company_id'            => !is_null(\Auth::user()->company->first()) ? \Auth::user()->company->first()->id : null,
            'challenge_category_id' => $payload['challenge_category'],
            'timezone'              => $userTimeZone,
            'title'                 => $payload['name'],
            'description'           => $payload['info'],
            'start_date'            => $start_date,
            'end_date'              => $end_date,
            'challenge_end_at'      => $end_date,
            'close'                 => (!empty($payload['close']) && $payload['close'] == 'yes') ? 0 : 1,
            'is_badge'              => (!empty($payload['ongoing_challenge_badge']) && $payload['ongoing_challenge_badge']) ? 1 : 0,
            'map_id'                => ($payload['select_map'] ?? null),
        ];

        if (!empty($payload['recursive']) && $payload['recursive'] == "yes") {
            $challengeInput['recurring']       = 1;
            $challengeInput['recurring_count'] = $payload['recursive_count'];
            $challengeInput['recurring_type']  = $payload['recursive_type'];
        }

        if ($payload['route'] == 'challenges') {
            $challengeInput['challenge_type'] = 'individual';
        } elseif ($payload['route'] == 'teamChallenges') {
            $challengeInput['challenge_type'] = 'team';
        } elseif ($payload['route'] == 'companyGoalChallenges') {
            $challengeInput['challenge_type'] = 'company_goal';
        } elseif ($payload['route'] == 'interCompanyChallenges') {
            $challengeInput['challenge_type'] = 'inter_company';
        }

        if ($challengeInput['close'] == 0 && ($payload['route'] == 'challenges')) {
            $challengeInput['locations']   = (!empty($payload['locations']) ? implode(',', $payload['locations']) : '');
            $challengeInput['departments'] = (!empty($payload['department']) ? implode(',', $payload['department']) : '');
        }

        $challenges = self::create($challengeInput);

        if (isset($payload['logo']) && !empty($payload['logo'])) {
            $name = $challenges->id . '_' . \time();
            $challenges->clearMediaCollection('logo')->addMediaFromRequest('logo')
                ->usingName($payload['logo']->getClientOriginalName())
                ->usingFileName($name . '.' . $payload['logo']->extension())
                ->toMediaCollection('logo', config('medialibrary.disk_name'));
        }

        if ($challenges) {
            $challenges_rule                             = array();
            $challenges_rule[0]['challenge_category_id'] = $payload['challenge_category'];
            $challenges_rule[0]['challenge_target_id']   = $payload['target_type'];
            $challenges_rule[0]['target']                = $payload['target_units'] ?? 0;
            $challenges_rule[0]['uom']                   = $payload['unite'];

            if ($payload['target_type'] == 4) {
                $challenges_rule[0]['model_id']   = $payload['excercise_type'];
                $challenges_rule[0]['model_name'] = 'Exercise';
            }

            if ($payload['target_type'] == 6) {
                //Target type = Content
                $contentIds                                  = (!empty($payload['content_challenge_ids']) ? implode(',', $payload['content_challenge_ids']) : null);
                $challenges_rule[0]['content_challenge_ids'] = $contentIds;
                $challenges_rule[0]['model_name']            = 'Content';
            }

            if ($payload['challenge_category'] == 4 || $payload['challenge_category'] == 5) {
                $challenges_rule[1]['challenge_category_id'] = $payload['challenge_category'];
                $challenges_rule[1]['challenge_target_id']   = $payload['target_type1'];
                $challenges_rule[1]['target']                = $payload['target_units1'] ?? 0;
                $challenges_rule[1]['uom']                   = $payload['unite1'];

                if ($payload['target_type1'] == 4) {
                    $challenges_rule[1]['model_id']   = $payload['excercise_type1'];
                    $challenges_rule[1]['model_name'] = 'Exercise';
                }

                if ($payload['target_type1'] == 6) {
                    //Target type = Content
                    $contentIds                                  = (!empty($payload['content_challenge_ids1']) ? implode(',', $payload['content_challenge_ids1']) : null);
                    $challenges_rule[1]['content_challenge_ids'] = $contentIds;
                    $challenges_rule[1]['model_name']            = 'Content';
                }
            }

            foreach ($challenges_rule as $value) {
                $challenges->challengeRules()->create($value);
            }

            // Assign ongoing badge to ongoing challenge
            if (!empty($payload['ongoing_challenge_badge']) && $payload['ongoing_challenge_badge'] && $payload['challenge_category'] != 4 && ($payload['target_type'] == 1 || $payload['target_type'] == 2)) {
                $target     = $payload['target'];
                $badgeArray = [];
                foreach ($target as $key => $value) {
                    if (!empty($payload['badge'][$key]) && $value && $payload['in_days'][$key]) {
                        $badgeArray[] = [
                            'challenge_id'        => $challenges->id,
                            'challenge_target_id' => $payload['challenge_category'],
                            'badge_id'            => $payload['badge'][$key],
                            'target'              => $value,
                            'in_days'             => $payload['in_days'][$key],
                        ];
                    }
                }
                $challenges->assignBadgeToOngoingChallenge()->sync($badgeArray);
            }

            $members      = isset($payload['members_selected']) ? $payload['members_selected'] : [];
            $membersInput = [];
            foreach ($members as $value) {
                if ($payload['route'] == 'challenges') {
                    $membersInput[] = [
                        'challenge_id' => $challenges->id,
                        'user_id'      => $value,
                    ];
                } elseif ($payload['route'] == 'teamChallenges' || $payload['route'] == 'companyGoalChallenges') {
                    $membersInput[] = [
                        'challenge_id' => $challenges->id,
                        'team_id'      => $value,
                    ];
                } elseif ($payload['route'] == 'interCompanyChallenges') {
                    $membersInput[] = [
                        'challenge_id' => $challenges->id,
                        'team_id'      => $value,
                        'company_id'   => \App\Models\Team::find($value)->company()->first()->id,
                    ];
                }
            }

            if ($payload['route'] == 'challenges') {
                $challenges->members()->sync($membersInput);
            } else {
                $challenges->memberTeams()->sync($membersInput);
            }

            // Check company plan access
            $checkChallengeAccess = getCompanyPlanAccess($user, 'my-challenges');

            if ($challenges) {
                // dispatch job to send push notification to all user when course created
                $challenges->autoGenerateGroups();
                if ($checkChallengeAccess) {
                    \dispatch(new SendChallengePushNotification($challenges, "challenge-created"));
                }

                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function challengeEditData()
    {
        $data     = array();
        $user     = auth()->user();
        $timezone = $user->timezone ?? config('app.timezone');

        $data['challengeData'] = $this;

        if (!empty($data['challengeData']->start_date)) {
            $data['challengeData']->start_date1 = Carbon::parse($data['challengeData']->start_date)->setTimezone($timezone)->toDateTimeString();
        }

        if (!empty($data['challengeData']->end_date)) {
            $data['challengeData']->end_date1 = Carbon::parse($data['challengeData']->end_date)->setTimezone($timezone)->toDateTimeString();
        }

        $data['id']          = $this->id;
        $data['creatorData'] = User::find($this->creator_id);

        $explodeRoute = explode('.', \Route::currentRouteName());
        $route        = $explodeRoute[1];

        if ($route == 'challenges') {
            $data['groupUserData'] = $this->members->pluck('id')->toArray();
        } else {
            $data['groupUserData']    = $this->memberTeams->pluck('id')->toArray();
            $data['participatedComp'] = $this->memberTeams->pluck('company_id')->toArray();
        }

        $groupObj                     = new Group();
        $data['recurring_type']       = config('zevolifesettings.recurring_type');
        $data['challenge_categories'] = \App\Models\ChallengeCategory::where("is_excluded", 0)->pluck('name', 'id')->toArray();
        $data['challenge_targets']    = \App\Models\ChallengeTarget::where("is_excluded", 0)->pluck('name', 'id')->toArray();
        $data['exercises']            = \App\Models\Exercise::pluck('title', 'id')->toArray();
        $data['companyData']          = $groupObj->getTeamMembersData();
        foreach ($data['companyData'] as $value) {
            $company   = \Auth::user()->company->first();
            $companyId = isset($company) ? $company->id : null;
            if ($value['id'] == $companyId) {
                $data['departmentData'] = $value['departments'];
            }
        }

        if ($this->close == 0 && ($this->challenge_type == 'individual')) {
            $payload['value']       = (!empty($this->departments) ? explode(',', $this->departments) : []);
            $data['departmentData'] = $this->getMemberData($payload, $this->company_id);
        }

        $data['uoms']                   = array();
        $data['uom_data']               = config('zevolifesettings.uom');
        $data['badgesData']             = $this->challengeBadges->pluck('id')->toArray();
        $targetIds                      = $this->challengeRules->pluck('challenge_target_id')->toArray();
        $data['challengeOngoingBadges'] = $this->assignBadgeToOngoingChallenge()->select('challenge_ongoing_badges.challenge_target_id', 'challenge_ongoing_badges.badge_id', 'challenge_ongoing_badges.target', 'challenge_ongoing_badges.in_days')->get()->toArray();
        $data['badges']                 = Badge::whereIn("challenge_target_id", $targetIds)->where("type", "challenge")->pluck('title', 'id')->toArray();
        $data['dayValue']               = config('zevolifesettings.recurring_day_value');
        $data['allowTargetUnitEdit']    = true;
        $data['locationDepartmentEdit'] = false;
        $data['hideOpenChallenge']      = ($this->map_id != '') ? 'hide' : '';
        $data['contentCategories']      = \App\Models\ContentChallenge::pluck('category', 'id')->toArray();

        if (Carbon::now()->setTimezone($timezone)->toDateTimeString() < $data['challengeData']->start_date1) {
            $data['allowTargetUnitEdit'] = false;
        }
        if ($this->start_date <= now($timezone)->toDateTimeString() && $this->end_date >= now($timezone)->toDateTimeString()) {
            $data['locationDepartmentEdit'] = true;
        }
        return $data;
    }

    /**
     * update record data.
     *
     * @param payload
     * @return boolean
     */

    public function updateEntity(array $payload)
    {
        $user                     = auth()->user();
        $userTimeZone             = $user->timezone;
        $dayValue                 = config('zevolifesettings.recurring_day_value');
        $newAddedGroupMembersList = array();

        // dates
        $start_date = Carbon::parse($payload['start_date'], $userTimeZone)->setTime(0, 0, 0)->setTimezone(\config('app.timezone'));

        if (!empty($payload['recursive']) && $payload['recursive'] == "yes") {
            $edDate   = date('Y-m-d', strtotime($payload['start_date'] . ' + ' . ($dayValue[$payload['recursive_type']] - 1) . ' days'));
            $end_date = Carbon::parse($edDate, $userTimeZone)->setTime(23, 59, 59)->setTimezone(\config('app.timezone'));
        } else {
            $end_date = Carbon::parse($payload['end_date'], $userTimeZone)->setTime(23, 59, 59)->setTimezone(\config('app.timezone'));
        }

        $challengeInput = [
            'title'            => $payload['name'],
            'description'      => $payload['info'],
            'start_date'       => $start_date,
            'end_date'         => $end_date,
            'challenge_end_at' => $end_date,
        ];
        if (!empty($payload['recursive']) && $payload['recursive'] == "yes") {
            $challengeInput['recurring']       = 1;
            $challengeInput['recurring_count'] = $payload['recursive_count'];
            $challengeInput['recurring_type']  = $payload['recursive_type'];
        } else {
            $challengeInput['recurring']       = 0;
            $challengeInput['recurring_count'] = null;
            $challengeInput['recurring_type']  = null;
        }

        if ($this->close == 0 && ($this->challenge_type == 'individual')) {
            if (!empty($payload['locations'])) {
                $challengeInput['locations'] = implode(',', $payload['locations']);
            }
            if (!empty($payload['department'])) {
                $challengeInput['departments'] = implode(',', $payload['department']);
            }
        }

        $updated = $this->update($challengeInput);

        if (isset($payload['logo']) && !empty($payload['logo'])) {
            $name = $this->id . '_' . \time();
            $this->clearMediaCollection('logo')->addMediaFromRequest('logo')
                ->usingName($payload['logo']->getClientOriginalName())
                ->usingFileName($name . '.' . $payload['logo']->extension())
                ->toMediaCollection('logo', config('medialibrary.disk_name'));
        }

        if ($updated) {
            $members = isset($payload['members_selected']) ? $payload['members_selected'] : [];
            if ($this->challenge_type == 'individual') {
                $oldMembers = $this->members()->pluck('users.id')->toArray();
            } else {
                $oldMembers = $this->memberTeams()->pluck('teams.id')->toArray();
            }

            $deletedMembers               = \array_diff($oldMembers, $members);
            $addNotificationsToMembers    = \array_diff($members, $oldMembers);

            $membersInput = [];
            if (!empty($members)) {
                foreach ($members as $value) {
                    if ($payload['route'] == 'challenges') {
                        $membersInput[] = [
                            'challenge_id' => $this->id,
                            'user_id'      => $value,
                        ];
                    } elseif ($payload['route'] == 'teamChallenges' || $payload['route'] == 'companyGoalChallenges') {
                        $membersInput[] = [
                            'challenge_id' => $this->id,
                            'team_id'      => $value,
                        ];
                    } elseif ($payload['route'] == 'interCompanyChallenges') {
                        $membersInput[] = [
                            'challenge_id' => $this->id,
                            'team_id'      => $value,
                            'company_id'   => \App\Models\Team::find($value)->company()->first()->id,
                        ];
                    }
                }
            }

            // Assign ongoing badge to ongoing challenge
            if (!empty($payload['ongoing_challenge_badge']) && $payload['ongoing_challenge_badge']) {
                $target     = $payload['target'];
                $badgeArray = [];

                foreach ($target as $key => $value) {
                    if (!empty($payload['badge'][$key]) && $value && $payload['in_days'][$key]) {
                        $badgeArray[] = [
                            'challenge_id'        => $this->id,
                            'challenge_target_id' => $this->challenge_category_id,
                            'badge_id'            => $payload['badge'][$key],
                            'target'              => $value,
                            'in_days'             => $payload['in_days'][$key],
                        ];
                    }
                }
                $this->assignBadgeToOngoingChallenge()->sync($badgeArray);
            }

            if ($payload['route'] == 'challenges') {
                $this->members()->detach();
                $this->members()->sync($membersInput);
            } else {
                $this->memberTeams()->detach();
                $this->memberTeams()->sync($membersInput);
            }

            $groupExists = Group::where('model_name', 'challenge')
                ->where('model_id', $this->id)
                ->first();
            if (!empty($groupExists)) {
                if ($this->challenge_type == 'individual') {
                    $members = $this->members()->where('status', 'Accepted')->get()->pluck('id')->toArray();
                } elseif ($this->challenge_type == 'team') {
                    $teamData = $this->memberTeams()->get();
                    $members  = $teamData->transform(function ($value) {
                        return $value->users()->get()->pluck('id');
                    })->filter(function ($value) {
                        return !is_null($value);
                    })->flatten();
                } elseif ($this->challenge_type == 'company_goal') {
                    $teamData = $this->memberTeams()->get();
                    $members  = $teamData->transform(function ($value) {
                        return $value->users()->get()->pluck('id');
                    })->flatten();
                } else {
                    $teamData = $this->memberTeams()->get();
                    $members  = $teamData->transform(function ($value) {
                        return $value->users()->get()->pluck('id');
                    })->filter(function ($value) {
                        return !is_null($value);
                    })->flatten();
                }
                $newMembers     = array(1);
                $membersInput   = [];
                $membersInput[] = [
                    'user_id'     => 1,
                    'group_id'    => $groupExists->id,
                    'status'      => "Accepted",
                    'joined_date' => now()->toDateTimeString(),
                ];
                foreach ($members as $value) {
                    $newMembers[]         = $value;
                    $membersInput[$value] = [
                        'user_id'     => $value,
                        'group_id'    => $groupExists->id,
                        'status'      => "Accepted",
                        'joined_date' => now()->toDateTimeString(),
                    ];
                }

                $oldGroupMembersList = $groupExists->members()
                    ->wherePivot("status", "Accepted")
                    ->pluck('users.id')
                    ->toArray();
                $newAddedGroupMembersList = \array_diff($newMembers, $oldGroupMembersList);

                $groupExists->members()->sync($membersInput);
            } else {
                $this->autoGenerateGroups();
            }

            // Check company plan access
            $checkChallengeAccess = getCompanyPlanAccess($user, 'my-challenges');
            $checkGroupAccess     = getCompanyPlanAccess($user, 'group');

            if ($updated) {
                // challenge-updated-removed notification has been disabled for inter-company challenge as an update.
                if ($checkChallengeAccess) {
                    if (!empty($deletedMembers) && ($this->challenge_type == 'team')) {
                        \dispatch(new SendChallengePushNotification($this, "challenge-updated-removed", "", $deletedMembers));
                    }

                    if (!empty($addNotificationsToMembers)) {
                        \dispatch(new SendChallengePushNotification($this, "challenge-created-updated", "", $addNotificationsToMembers));
                    }
                }

                if (!empty($newAddedGroupMembersList) && !empty($groupExists) && $checkGroupAccess) {
                    \dispatch(new SendGroupPushNotification($groupExists, "user-assigned-updated-group", "", "", $newAddedGroupMembersList));
                }

                return true;
            } else {
                return false;
            }
        }

        return false;
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

        $title   = trans('notifications.challenge.challenge-deleted.title');
        $message = trans('notifications.challenge.challenge-deleted.message');
        $message = str_replace(["#challenge_name#"], [$this->title], $message);
        $user    = auth()->user();
        // Check company plan access
        $checkChallengeAccess = getCompanyPlanAccess($user, 'my-challenges');

        if ($this->challenge_type == 'individual') {
            $membersData = $this->members()->where('status', 'Accepted')->get();
        } else {
            $teamData    = $this->memberTeams()->get();
            $membersData = $teamData->transform(function ($value) {
                return $value->users()->get();
            })->flatten();
        }

        $data = [
            'title'            => $title,
            'message'          => $message,
            'membersData'      => $membersData,
            'creator_id'       => $this->creator_id,
            'company_id'       => $this->company_id,
            'creator_timezone' => $this->timezone,
            'is_mobile'        => config('notification.challenge.deleted.is_mobile'),
            'is_portal'        => config('notification.challenge.deleted.is_portal'),
            'string'           => 'challenge-deleted',
            'module'           => 'challenges',
        ];

        if ($checkChallengeAccess) {
            // dispatch job to send push notification to related users when challenge is deleted
            \dispatch(new SendDeletePushNotifications($data));
        }

        if ($this->delete()) {
            return array('deleted' => 'true');
        }
        return array('deleted' => 'error');
    }

    public function getParticipantMembersData()
    {
        $companies       = Company::all();
        $participantUser = $this->members->pluck('id')->toArray();

        $participantTeam = array();
        if ($this->challenge_type != 'individual') {
            $participantTeam = $this->memberTeams->pluck('id')->toArray();
        }

        // get companies, departments, teams and users
        $companyData = [];
        foreach ($companies as $company) {
            $depts = $company->departments;

            if (!$depts->isEmpty()) {
                $departmentsTeams = [];
                foreach ($depts as $dept) {
                    $teams = $dept->teams;

                    if (!$teams->isEmpty()) {
                        $teamsData = [];
                        foreach ($teams as $team) {
                            $members     = $team->users;
                            $membersData = [];
                            if (!$members->isEmpty()) {
                                foreach ($members as $member) {
                                    if ($this->challenge_type == 'individual') {
                                        if (in_array($member->id, $participantUser)) {
                                            $membersData[] = [
                                                'id'   => $member->id,
                                                'name' => $member->first_name . " " . $member->last_name,
                                            ];
                                        }
                                    } else {
                                        $membersData[] = [
                                            'id'   => $member->id,
                                            'name' => $member->first_name . " " . $member->last_name,
                                        ];
                                    }
                                }
                            }
                            if ($this->challenge_type != 'individual') {
                                if (in_array($team->id, $participantTeam)) {
                                    $teamsData[] = [
                                        'id'      => $team->id,
                                        'name'    => $team->name,
                                        'code'    => 'code: ' . $team->code,
                                        'members' => $membersData,
                                    ];
                                }
                            } else {
                                $teamsData[] = [
                                    'id'      => $team->id,
                                    'name'    => $team->name,
                                    'code'    => 'code: ' . $team->code,
                                    'members' => $membersData,
                                ];
                            }
                        }

                        $departmentsTeams[] = [
                            'id'    => $dept->id,
                            'name'  => $dept->name,
                            'teams' => $teamsData,
                        ];
                    }
                }
                $companyData[] = [
                    'id'          => $company->id,
                    'name'        => $company->name,
                    'departments' => $departmentsTeams,
                ];
            }
        }
        return $companyData;
    }

    public function updatePoints($payload)
    {
        if (!empty($payload['members_selected']) && !empty($payload['points_target'])) {
            foreach ($payload['points_target'] as $value) {
                foreach ($payload['members_selected'] as $key => $value1) {
                    if (isset($payload['points'][$key]) && $payload['points'][$key] != "" && $payload['points'][$key] != null) {
                        $user         = User::find((int) $value1);
                        $userTimeZone = $user->timezone;

                        $log_date = Carbon::parse($payload['log_date'], $userTimeZone)->setTime(0, 0, 0)->setTimezone(\config('app.timezone'))->toDateTimeString();

                        $points           = array();
                        $points['points'] = $payload['points'][$key];

                        $this->challengeWiseManualPoints()->updateOrCreate([
                            'user_id'             => $user->getKey(),
                            'logged_at'           => $log_date,
                            'challenge_id'        => $this->getKey(),
                            'challenge_target_id' => $value,
                        ], $points);

                        //Sending points notifications
                        $message = trans('notifications.challenge.challenge-points-added.message', [
                            'points' => ($points['points'] == 1 ? '1 point was' : $points['points'] . ' points were'),
                        ]);
                        $title = trans('notifications.challenge.challenge-points-added.title', [
                            'challenge_name' => $this->title,
                        ]);
                        $deepLink         = 'zevolife://zevo/challenge/leaderboad/' . $this->id;
                        $notificationData = [
                            'type'             => 'Auto',
                            'creator_id'       => $this->creator_id,
                            'company_id'       => $this->company_id,
                            'creator_timezone' => $this->timezone,
                            'title'            => $title,
                            'message'          => $message,
                            'push'             => true,
                            'scheduled_at'     => now()->toDateTimeString(),
                            'deep_link_uri'    => $deepLink,
                            'is_mobile'        => true,
                            'is_portal'        => false,
                            'tag'              => 'challenge',
                        ];

                        $notification = Notification::create($notificationData);
                        $user->notifications()->attach($notification, ['sent' => true, 'sent_on' => now()->toDateTimeString()]);

                        $notificationFlag = User::where("users.id", $user->id)->select(DB::raw('1 AS notification_flag'))->get()->first();
                        if ($notificationFlag->notification_flag) {
                            // send notification to selected users
                            \Notification::send(
                                $user,
                                new SystemAutoNotification($notification, 'points-added')
                            );
                        }
                    }
                }
            }
            return true;
        }

        return false;
    }

    /**
     * get participate data table.
     *
     * @param $payload , $id
     * @return array
     */
    public function getMembersTableData($payload)
    {
        $challengeHistory = $this->challengeHistory;

        $upcoming = false;
        if (now()->toDateTimeString() < $this->start_date) {
            $upcoming = true;
        }

        if (!empty($challengeHistory) && !$upcoming) {
            $challengeParticipantsWithPoints = $this->challengeWiseUserPoints()
                ->join('freezed_challenge_participents', 'freezed_challenge_participents.user_id', '=', 'challenge_wise_user_ponits.user_id')
                ->select('challenge_wise_user_ponits.id', 'freezed_challenge_participents.user_id', 'freezed_challenge_participents.participant_name', 'challenge_wise_user_ponits.challenge_id', 'challenge_wise_user_ponits.points', 'challenge_wise_user_ponits.rank')
                ->with(['user' => function ($query) {
                    $query->select('id', 'first_name', 'last_name');
                }])
                ->where('freezed_challenge_participents.challenge_id', $this->id)
                ->where('challenge_wise_user_ponits.challenge_id', $this->id)
                ->groupBy('challenge_wise_user_ponits.user_id');

            if (in_array('search', array_keys($payload)) && !empty($payload['search']['value'])) {
                $challengeParticipantsWithPoints->where('freezed_challenge_participents.participant_name', 'like', '%' . $payload['search']['value'] . '%');
            }

            if (isset($payload['order'])) {
                $column = $payload['columns'][$payload['order'][0]['column']]['data'];
                $order  = $payload['order'][0]['dir'];
                $challengeParticipantsWithPoints->orderBy($column, $order);
            } else {
                $challengeParticipantsWithPoints
                    ->orderBy('challenge_wise_user_ponits.rank', 'ASC')
                    ->orderBy('challenge_wise_user_ponits.user_id', 'ASC');
            }

            if (!empty($payload['exportFrom']) && $payload['exportFrom'] == 'challenge-detail') {
                return array('list' => $challengeParticipantsWithPoints->get()->toArray());
            }

            $total  = $challengeParticipantsWithPoints->get()->count();
            $record = $challengeParticipantsWithPoints->offset($payload['start'])->limit($payload['length'])->get();

            return DataTables::of($record)
                ->skipPaging()
                ->with([
                    "recordsTotal"    => $total,
                    "recordsFiltered" => $total,
                ])
                ->addColumn('participant_name', function ($record) {
                    return (!empty($record->user)) ? $record->participant_name : 'Deleted';
                })
                ->addColumn('logo', function ($record) {
                    $user = User::find($record->user_id);
                    $logo = (!empty($user->logo) ? $user->logo : asset('app_assets/onboard1.png'));
                    return '<div class="table-img table-img-l"><img data=' . $record->user_id . ' src=' . $logo . ' width="70" /></div>';
                })
                ->rawColumns(['logo'])
                ->make(true);
        } else {
            $acceptedMemberList = $this->members()->where('status', 'Accepted')->pluck('user_id')->implode(',');
            $list               = $this->members()
                ->select(
                    'users.id',
                    \DB::raw("CONCAT(users.first_name, ' ', users.last_name) AS participant_name")
                )->selectRaw(
                    "IF(FIND_IN_SET(user_id, ?) > 0, 0, 'NA') AS rank"
                ,[$acceptedMemberList])->selectRaw(
                    "IF(FIND_IN_SET(user_id, ?) > 0, 0, 'NA') AS points"
                ,[$acceptedMemberList]);

            if (in_array('search', array_keys($payload)) && !empty($payload['search']['value'])) {
                $list->where(DB::raw("CONCAT(users.first_name,' ',users.last_name)"), 'like', '%' . $payload['search']['value'] . '%');
            }

            if (isset($payload['order'])) {
                $column = $payload['columns'][$payload['order'][0]['column']]['data'];
                $order  = $payload['order'][0]['dir'];
                $list->orderBy($column, $order);
            }

            if (!empty($payload['exportFrom']) && $payload['exportFrom'] == 'challenge-detail') {
                return array('challenge_type' => 'upcoming', 'list' => $list->get()->toArray());
            }
            $total  = $list->get()->count();
            $record = $list->offset($payload['start'])->limit($payload['length'])->get();

            return DataTables::of($record)
                ->skipPaging()
                ->with([
                    "recordsTotal"    => $total,
                    "recordsFiltered" => $total,
                ])
                ->addColumn('logo', function ($record) {
                    $logo = $record->logo;
                    $logo = (!empty($logo) ? $logo : asset('app_assets/onboard1.png'));
                    return "<div class='table-img table-img-l'><img src='{$logo}' width='70'/></div>";
                })
                ->rawColumns(['logo'])
                ->make(true);
        }
    }

    /**
     * get participate data table for Intercompany / Team / Company Goal challenge.
     *
     * @param $payload , $id
     * @return array
     */
    public function getMembersOthersTableData($payload)
    {
        $challengeHistory = $this->challengeHistory;
        $company          = \Auth::user()->company->first();
        $companyId        = !empty($company) ? $company->id : null;
        $upcoming         = false;
        if (now()->toDateTimeString() < $this->start_date) {
            $upcoming = true;
        }
        if (!empty($challengeHistory) && !$upcoming) {
            $challengeParticipantsWithPoints = $this->challengeWiseUserPoints()
                ->join('freezed_team_challenge_participents', 'freezed_team_challenge_participents.user_id', '=', 'challenge_wise_user_ponits.user_id')
                ->select('challenge_wise_user_ponits.id', 'freezed_team_challenge_participents.user_id', 'freezed_team_challenge_participents.participant_name', 'challenge_wise_user_ponits.challenge_id', 'challenge_wise_user_ponits.points', 'challenge_wise_user_ponits.rank')
                ->with(['user' => function ($query) {
                    $query->select('id', 'first_name', 'last_name');
                }])
                ->where('freezed_team_challenge_participents.challenge_id', $this->id)
                ->where('challenge_wise_user_ponits.challenge_id', $this->id);
            if ($this->challenge_type == 'inter_company' && $companyId != null) {
                $challengeParticipantsWithPoints->where('challenge_wise_user_ponits.company_id', $companyId);
            }
            $challengeParticipantsWithPoints = $challengeParticipantsWithPoints->groupBy('challenge_wise_user_ponits.user_id');

            if (in_array('search', array_keys($payload)) && !empty($payload['search']['value'])) {
                $challengeParticipantsWithPoints->where('freezed_team_challenge_participents.participant_name', 'like', '%' . $payload['search']['value'] . '%');
            }

            if (isset($payload['order'])) {
                $column = $payload['columns'][$payload['order'][0]['column']]['data'];
                $order  = $payload['order'][0]['dir'];
                $challengeParticipantsWithPoints->orderBy($column, $order);
            } else {
                $challengeParticipantsWithPoints
                    ->orderBy('challenge_wise_user_ponits.rank', 'ASC')
                    ->orderBy('challenge_wise_user_ponits.user_id', 'ASC');
            }
            if (!empty($payload['exportFrom']) && $payload['exportFrom'] == 'challenge-detail') {
                return array('list' => $challengeParticipantsWithPoints->get()->toArray());
            }
            $total  = $challengeParticipantsWithPoints->get()->count();
            $record = $challengeParticipantsWithPoints->offset($payload['start'])->limit($payload['length'])->get();

            return DataTables::of($record)
                ->skipPaging()
                ->with([
                    "recordsTotal"    => $total,
                    "recordsFiltered" => $total,
                ])
                ->addColumn('participant_name', function ($record) {
                    return (!empty($record->user)) ? $record->participant_name : 'Deleted';
                })
                ->addColumn('logo', function ($record) {
                    $logo = (isset($record->user->logo) ? $record->user->logo : asset('app_assets/onboard1.png'));
                    return "<div class='table-img table-img-l'><img src='{$logo}' width='70'/></div>";
                })
                ->rawColumns(['logo'])
                ->make(true);
        } else {
            $acceptedMemberList = $this->memberTeams();
            if ($this->challenge_type == 'inter_company' && $companyId != null) {
                $acceptedMemberList->where('challenge_participants.company_id', $companyId);
            }

            $acceptedMemberList = $acceptedMemberList->where('status', 'Accepted')->pluck('team_id')->toArray();

            $list = User::join('user_team', 'user_team.user_id', '=', 'users.id')
                ->whereIn('user_team.team_id', $acceptedMemberList)
                ->select(
                    'users.id',
                    \DB::raw("CONCAT(users.first_name, ' ', users.last_name) AS participant_name"),
                    \DB::raw("0 AS rank"),
                    \DB::raw("0 AS points")
                );

            if (in_array('search', array_keys($payload)) && !empty($payload['search']['value'])) {
                $list->where(DB::raw("CONCAT(users.first_name,' ',users.last_name)"), 'like', '%' . $payload['search']['value'] . '%');
            }

            if(!empty($payload['exportFrom']) && $payload['exportFrom'] == 'challenge-detail'){
                return array('challenge_type' => 'upcoming', 'list' => $list->get()->toArray());
            }
            $total  = $list->get()->count();
            $record = $list->offset($payload['start'])->limit($payload['length'])->get();
            
            return DataTables::of($record)
                ->skipPaging()
                ->with([
                    "recordsTotal"    => $total,
                    "recordsFiltered" => $total,
                ])
                ->addColumn('logo', function ($record) {
                    $logo = $record->logo;
                    $logo = (!empty($logo) ? $logo : asset('app_assets/onboard1.png'));
                    return "<div class='table-img table-img-l'><img src='{$logo}' width='70'/></div>";
                })
                ->rawColumns(['logo'])
                ->make(true);
        }
    }

    /**
     * get particepants team data table.
     *
     * @param $payload , $id
     * @return array
     */
    public function getTeamMembersTableData($payload)
    {
        $challengeHistory = $this->challengeHistory;
        $company          = \Auth::user()->company->first();
        $companyId        = !empty($company) ? $company->id : null;
        $upcoming         = false;
        if (now()->toDateTimeString() < $this->start_date) {
            $upcoming = true;
        }

        if (!empty($challengeHistory) && !$upcoming) {
            $challengeParticipantsWithPoints = $this->challengeWiseTeamPoints()
                ->join('freezed_challenge_participents', 'freezed_challenge_participents.team_id', '=', 'challenge_wise_team_ponits.team_id')
                ->select('freezed_challenge_participents.team_id', 'freezed_challenge_participents.participant_name', 'challenge_wise_team_ponits.challenge_id', 'challenge_wise_team_ponits.points', 'challenge_wise_team_ponits.rank')->where('freezed_challenge_participents.challenge_id', $this->id)
                ->where('challenge_wise_team_ponits.challenge_id', $this->id);
            if ($this->challenge_type == 'inter_company' && $companyId != null) {
                $challengeParticipantsWithPoints->where('challenge_wise_team_ponits.company_id', $companyId);
            }
            $challengeParticipantsWithPoints = $challengeParticipantsWithPoints->orderBy('challenge_wise_team_ponits.rank', 'ASC')
                ->orderBy('challenge_wise_team_ponits.team_id', 'ASC')
                ->groupBy('challenge_wise_team_ponits.team_id');

            if (in_array('search', array_keys($payload)) && !empty($payload['search']['value'])) {
                $challengeParticipantsWithPoints->where('freezed_challenge_participents.participant_name', 'like', '%' . $payload['search']['value'] . '%');
            }

            $challengeParticipantsWithPoints = $challengeParticipantsWithPoints->get();
            if (!empty($payload['exportFrom']) && $payload['exportFrom'] == 'challenge-detail') {
                return array('list' => $challengeParticipantsWithPoints->toArray());
            }
            return DataTables::of($challengeParticipantsWithPoints)
                ->addColumn('name', function ($record) {
                    $team = Team::find($record->team_id);
                    return (!empty($team)) ? $record->participant_name : 'Deleted';
                })
                ->addColumn('totalUsers', function ($record) {
                    $team       = Team::find($record->team_id);
                    $totalUsers = !empty($team) ? $team->users()->count() : 0;
                    return (!empty($totalUsers)) ? $totalUsers : 0;
                })
                ->addColumn('totalPoints', function ($record) {
                    return number_format((float) $record->points, 1, '.', '');
                })
                ->addColumn('rank', function ($record) {
                    return $record->rank;
                })
                ->addColumn('logo', function ($record) {

                    $team = Team::find($record->team_id);

                    if (!empty($team->logo)) {
                        return '<div class="table-img table-img-l"><img src=' . $team->logo . '" width="70" /></div>';
                    } else {
                        return '<div class="table-img table-img-l"><img src=' . asset('assets/dist/img/boxed-bg.png') . '" width="70" /></div>';
                    }
                })
                ->rawColumns(['logo'])
                ->make(true);
        } else {
            $list = $this->memberTeams();
            if ($this->challenge_type == 'inter_company' && $companyId != null) {
                $list->where('challenge_participants.company_id', $companyId);
            }
            if (in_array('search', array_keys($payload)) && !empty($payload['search']['value'])) {
                $list->where('teams.name', 'like', '%' . $payload['search']['value'] . '%');
            }

            $list = $list->get();
            if (!empty($payload['exportFrom']) && $payload['exportFrom'] == 'challenge-detail') {
                return array('challenge_type' => 'upcoming', 'list' => $list->toArray());
            }
            return DataTables::of($list)
                ->addColumn('name', function ($record) {
                    return (!empty($record)) ? $record->name : 'Deleted';
                })
                ->addColumn('totalUsers', function ($record) {
                    if ($record) {
                        $totalUsers = $record->users()->count();
                    }
                    return (!empty($totalUsers)) ? $totalUsers : 0;
                })
                ->addColumn('totalPoints', function () {
                    return 0;
                })
                ->addColumn('rank', function () {
                    return 0;
                })
                ->addColumn('logo', function ($record) {
                    if (!empty($record->logo)) {
                        return '<div class="table-img table-img-l"><img src=' . $record->logo . '" width="70" /></div>';
                    } else {
                        return '<div class="table-img table-img-l"><img src=' . asset('assets/dist/img/boxed-bg.png') . '" width="70" /></div>';
                    }
                })
                ->rawColumns(['logo'])
                ->make(true);
        }
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

        if ($collection == 'logo') {
            if (!is_null($this->library_image_id)) {
                $media = $this->libraryimage()->withTrashed()->first()->getFirstMedia('image');
            } else {
                $media = $this->getFirstMedia('logo');
            }

            if (!is_null($media) && $media->count() > 0) {
                $param['src'] = $media->getURL();
            }
        } else {
            $media = $this->getFirstMedia($collection);
            if (!is_null($media) && $media->count() > 1) {
                $param['src'] = $this->getFirstMediaUrl($collection);
            }
        }

        $return['url'] = getThumbURL($param, 'challenge', $collection);
        return $return;
    }

    /**
     * @param
     *
     * @return array
     */
    public function getCreatorData(): array
    {
        $return  = [];
        $creator = User::find($this->creator_id);

        if (!empty($creator)) {
            $return['id']    = $creator->getKey();
            $return['name']  = $creator->full_name;
            $return['image'] = $creator->getMediaData('logo', ['w' => 320, 'h' => 320]);
        }

        return $return;
    }

    /**
     * @return void
     */
    public function autoCancelChallengeNoParticipant(): void
    {
        $startTime = Carbon::parse($this->start_date, \config('app.timezone'))
            ->setTimezone($this->timezone);
        $currentTime = now($this->timezone);
        // Check company plan access
        $user                 = User::where("id", $this->creator_id)->first();
        $checkChallengeAccess = getCompanyPlanAccess($user, 'my-challenges');

        if ($currentTime->toDateTimeString() >= $startTime->toDateTimeString()) {
            if ($this->challenge_type == 'individual') {
                $members = $this->members()->where('status', 'Accepted')->get();
            } elseif ($this->challenge_type == 'company_goal' || $this->challenge_type == 'team') {
                $members = $this->memberTeams()->where('status', 'Accepted')->get();
            } else {
                $members = $this->memberCompanies()->groupBy('challenge_participants.company_id')->get();
            }

            if ($members->count() < 2) {
                $cancelled = $this->update(['cancelled' => true, 'deleted_reason' => 'System Auto Deleted']);

                if ($cancelled) {
                    // update participant mapping in challenge wise user data table
                    if ($this->challenge_type == 'individual') {
                        $this->members()->where('status', 'Accepted')->update(['status' => 'Expired']);
                    } elseif ($this->challenge_type == 'company_goal' || $this->challenge_type == 'team') {
                        $this->memberTeams()->where('status', 'Accepted')->update(['status' => 'Expired']);
                    } else {
                        $this->memberCompanies()->where('challenge_participants.status', 'Accepted')->update(['challenge_participants.status' => 'Expired']);
                    }

                    if ($this->challenge_type == 'individual') {
                        $members = $this->members()->get();
                    } else {
                        $teamData = $this->memberTeams()->get();
                        $members  = $teamData->transform(function ($value) {
                            return $value->users()->get();
                        })->flatten();
                    }

                    Group::where('model_name', 'challenge')
                        ->where('model_id', $this->getKey())
                        ->delete();

                    if ($checkChallengeAccess) {
                        // dispatch job to SendChallengePushNotification
                        \dispatch(new SendChallengePushNotification($this, 'challenge-auto-cancelled', '', $members));
                    }
                }
            }
        }
    }

    /**
     * @return HasOne
     */
    public function challengeHistory(): HasOne
    {
        return $this->hasOne('App\Models\ChallengeHistory');
    }

    /**
     * @return HasMany
     */
    public function challengeHistorySteps(): HasMany
    {
        return $this->hasMany('App\Models\ChallengeHistorySteps');
    }

    /**
     * @return HasMany
     */
    public function challengeHistoryExercises(): HasMany
    {
        return $this->hasMany('App\Models\ChallengeHistoryExercises');
    }

    /**
     * @return HasMany
     */
    public function challengeHistoryInspires(): HasMany
    {
        return $this->hasMany('App\Models\ChallengeHistoryInspires');
    }

    /**
     * @return HasMany
     */
    public function contentChallengePointHistory(): HasMany
    {
        return $this->hasMany('App\Models\ContentChallengePointHistory');
    }

    /**
     * @return HasMany
     */
    public function challengeUserExercisesHistory(): HasMany
    {
        return $this->hasMany('App\Models\ChallengeUserExercisesHistory');
    }

    /**
     * @return HasMany
     */
    public function challengeUserInspireHistory(): HasMany
    {
        return $this->hasMany('App\Models\ChallengeUserInspireHistory');
    }

    /**
     * @return HasMany
     */
    public function challengeUserStepsHistory(): HasMany
    {
        return $this->hasMany('App\Models\ChallengeUserStepsHistory');
    }

    /**
     * @return HasMany
     */
    public function challengeHistoryParticipants(): HasMany
    {
        return $this->hasMany('App\Models\ChallengeHistoryParticipants');
    }

    /**
     * @return HasMany
     */
    public function challengeHistorySettings(): HasMany
    {
        return $this->hasMany('App\Models\ChallengeSettingHistory');
    }

    /**
     * @return true
     */
    public function freezeHistoryAndFinishChallenge()
    {
        // Check company plan access
        // do evey process in challenger timezone
        if (!empty($this->creator_id) && !empty($this->start_date) && !empty($this->end_date)) {
            $challengerTimezone = $this->timezone;

            $now = \now($challengerTimezone);

            $challengeEndDate = Carbon::parse($this->end_date, config('app.timezone'))->setTimezone($challengerTimezone);

            // individual challenge will be completed according to challenge timezone
            if ($now->toDateTimeString() >= $challengeEndDate->toDateTimeString()) {
                $company              = \App\Models\Company::find($this->company_id);
                $checkChallengeAccess = getCompanyPlanAccess([], 'my-challenges', $company);
                $pointCalcRules       = (!empty($company) && $company->companyWiseChallengeSett()->count() > 0) ? $company->companyWiseChallengeSett()->pluck('value', 'type')->toArray() : config('zevolifesettings.default_limits');

                $challengeRulesData = $this->challengeRules()->join('challenge_targets', 'challenge_targets.id', '=', 'challenge_rules.challenge_target_id')->select('challenge_rules.*', 'challenge_targets.short_name', 'challenge_targets.name')->get();
                if ($this->challenge_type == "individual") {
                    // challenge participants data
                    $participants = $this->members()->where('status', 'Accepted')->get();

                    // freeze history
                    $this->collectAndDumpChallengeDataInHisroty($pointCalcRules, $participants, $challengeRulesData);

                    if ($checkChallengeAccess) {
                        // send finished notification
                        $this->sendFinishedNotifications();
                    }

                    // get winners of challenge
                    $winners = $this->getWinners($pointCalcRules, $participants, $challengeRulesData);

                    // send notifications to challenge winners
                    if (!empty($winners) && $winners->count() > 0) {
                        $userIds = $winners->pluck("id")->toArray();
                        $this->sendNotificationsToAllWinners($winners);
                        $loosingUsers = $this->members()->wherePivot('status', 'Accepted')->wherePivotIn('user_id', $userIds, 'and', 'NotIn')->get();
                    } else {
                        $loosingUsers = $this->members()->wherePivot('status', 'Accepted')->get();
                    }

                    if ($checkChallengeAccess && !empty($loosingUsers) && $loosingUsers->count() > 0) {
                        $this->sendNotificationToLooser($loosingUsers);
                    }
                } elseif ($this->challenge_type == "team") {
                    // challenge participants data
                    $participantTeam = $this->memberTeams()->get();

                    // freeze history
                    $this->collectAndDumpTeamChallengeDataInHisroty($pointCalcRules, $participantTeam, $challengeRulesData);

                    if ($checkChallengeAccess) {
                        // send finished notification
                        $this->sendTeamFinishedNotifications($participantTeam);
                    }

                    // get winners of challenge
                    $winners = $this->getWinnersForTeamChallenge($pointCalcRules, $participantTeam, $challengeRulesData);

                    // send notifications to challenge winners
                    if (!empty($winners) && $winners->count() > 0) {
                        $teamIds = $winners->pluck("id")->toArray();

                        if ($checkChallengeAccess) {
                            $this->sendNotificationsToAllTeamWinners($winners);
                        }

                        $loosingTeams = $this->memberTeams()->wherePivotIn('team_id', $teamIds, 'and', 'NotIn')->get();
                    } else {
                        $loosingTeams = $this->memberTeams()->get();
                    }

                    if ($checkChallengeAccess && !empty($loosingTeams) && $loosingTeams->count() > 0) {
                        $this->sendNotificationToLooserTeamsUser($loosingTeams);
                    }
                } elseif ($this->challenge_type == "company_goal") {
                    // challenge participants data
                    $participantTeam = $this->memberTeams()->get();

                    // freeze history
                    $this->collectAndDumpTeamChallengeDataInHisroty($pointCalcRules, $participantTeam, $challengeRulesData);

                    if ($checkChallengeAccess) {
                        // send finished notification
                        $this->sendTeamFinishedNotifications($participantTeam);
                    }

                    // get winners of challenge
                    $winners = $this->getWinnersForCompanyChallenge($pointCalcRules, $participantTeam, $challengeRulesData);

                    // send notifications to challenge winners
                    if (!empty($winners) && $winners->count() > 0) {
                        $teamIds = $winners->pluck("id")->toArray();

                        if ($checkChallengeAccess) {
                            $this->sendNotificationsToAllTeamWinners($winners);
                        }

                        $loosingTeams = $this->memberTeams()->wherePivotIn('team_id', $teamIds, 'and', 'NotIn')->get();
                    } else {
                        $loosingTeams = $this->memberTeams()->get();
                    }

                    if ($checkChallengeAccess && !empty($loosingTeams) && $loosingTeams->count() > 0) {
                        $this->sendNotificationToLooserTeamsUser($loosingTeams);
                    }
                } elseif ($this->challenge_type == "inter_company") {
                    // check if challenge type is inter company then calculate point
                    $participantCompany = $this->memberCompanies()->groupBy('challenge_participants.company_id')->get();

                    $this->collectAndDumpInterCompanyChallengeDataInHisroty($pointCalcRules, $participantCompany, $challengeRulesData);

                    if (!empty($participantCompany) && $participantCompany->count() > 0) {
                        foreach ($participantCompany as $value) {
                            $participantTeam = $this->memberTeams()->wherePivot('company_id', $value->getKey())->get();
                            if ($checkChallengeAccess) {
                                // send finished notification
                                $this->sendTeamFinishedNotifications($participantTeam);
                            }
                        }
                    }

                    // get winners of challenge
                    $winners = $this->getWinnersForInterCompanyChallenge($pointCalcRules, $participantCompany, $challengeRulesData);

                    // send notifications to challenge winners
                    if (!empty($winners) && $winners->count() > 0) {
                        $companyIds = $winners->pluck("id")->toArray();

                        if ($checkChallengeAccess) {
                            $this->sendNotificationsToAllInterCompanyWinners($winners);
                        }

                        $loosingTeams = $this->memberTeams()->wherePivotIn('company_id', $companyIds, 'and', 'NotIn')->get();
                    } else {
                        $loosingTeams = $this->memberTeams()->get();
                    }

                    if ($checkChallengeAccess && !empty($loosingTeams) && $loosingTeams->count() > 0) {
                        $this->sendNotificationToLooserTeamsUser($loosingTeams);
                    }
                }

                // mark challenge as finished
                $this->update(['finished' => true]);
            }
        }
    }

    public function autoCreateRecurringChallenge()
    {
        $getSubChallengeData = Challenge::where("parent_id", $this->id)
            ->orderBy("id", "DESC");

        $totalSubChallengeCount = $getSubChallengeData->count();
        $dayValue               = config('zevolifesettings.recurring_day_value');

        if ($totalSubChallengeCount > 0) {
            $latestSubChallenge = $getSubChallengeData->limit(1)->first();
            $endDate            = $latestSubChallenge->challenge_end_at;
            $challengeData      = $latestSubChallenge;
        } else {
            $endDate       = $this->challenge_end_at;
            $challengeData = $this;
        }

        $futureDate = Carbon::parse(\now(), \config('app.timezone'))->setTimezone($challengeData->timezone)->addDays(1)->toDateString();

        $challengeEnds = Carbon::parse($endDate, \config('app.timezone'))->setTimezone($challengeData->timezone)->toDateString();

        if ($futureDate == $challengeEnds && $totalSubChallengeCount < $this->recurring_count) {
            $nextDay         = Carbon::parse($endDate, \config('app.timezone'))->setTimezone($challengeData->timezone)->addDays(1)->toDateString();
            $challenge_start = Carbon::parse($nextDay, $challengeData->timezone)->setTime(0, 0, 0)->setTimezone(\config('app.timezone'));

            $edDate        = date('Y-m-d', strtotime($nextDay . ' + ' . ($dayValue[$this->recurring_type] - 1) . ' days'));
            $challenge_end = Carbon::parse($edDate, $challengeData->timezone)->setTime(23, 59, 59)->setTimezone(\config('app.timezone'));

            $challengeInput = [
                'creator_id'            => $challengeData->creator_id,
                'company_id'            => (!empty($challengeData->company_id)) ? $challengeData->company_id : null,
                'challenge_category_id' => $challengeData->challenge_category_id,
                'parent_id'             => $this->id,
                'timezone'              => $challengeData->timezone,
                'title'                 => $challengeData->title,
                'description'           => $challengeData->description,
                'start_date'            => $challenge_start,
                'end_date'              => $challenge_end,
                'challenge_end_at'      => $challenge_end,
                'close'                 => 0,
            ];

            $challenges = self::create($challengeInput);

            if (!empty($challengeData->getFirstMediaUrl('logo'))) {
                $media     = $challengeData->getFirstMedia('logo');
                $imageData = explode(".", $media->file_name);
                $name      = $challenges->id . '_' . \time();
                $challenges->clearMediaCollection('logo')
                    ->addMediaFromUrl(
                        $challengeData->getFirstMediaUrl('logo'),
                        $challenges->getAllowedMediaMimeTypes('image')
                    )
                    ->usingName($media->name)
                    ->usingFileName($name . '.' . $imageData[1])
                    ->toMediaCollection('logo', config('medialibrary.disk_name'));
            }

            $challengeRuleData = $challengeData->challengeRules;

            foreach ($challengeRuleData as $value) {
                $challenges_rule                          = array();
                $challenges_rule['challenge_category_id'] = $value['challenge_category_id'];
                $challenges_rule['challenge_target_id']   = $value['challenge_target_id'];
                $challenges_rule['target']                = $value['target'];
                $challenges_rule['uom']                   = $value['uom'];
                if ($value['challenge_target_id'] == 4) {
                    $challenges_rule['model_id']   = $value['excercise_type'];
                    $challenges_rule['model_name'] = 'Exercise';
                }

                $challenges->challengeRules()->create($challenges_rule);
            }

            if ($challengeData->challengeBadges->count() > 0) {
                foreach ($challengeData->challengeBadges as $value) {
                    $badgeInput[] = [
                        'challenge_id' => $challenges->id,
                        'badge_id'     => $value->pivot->badge_id,
                    ];
                }
                $challenges->challengeBadges()->sync($badgeInput);
            }

            if (($totalSubChallengeCount + 1) == $this->recurring_count) {
                $this->recurring_completed = 1;
                $this->save();
            }
        }
    }

    public function collectAndDumpChallengeDataInHisroty($pointCalcRules, $participants, $challengeRulesData)
    {

        $challengeHistory             = [];
        $challengeHistoryParticipants = [];
        $challengeHistorySteps        = [];
        $challengeHistoryExercises    = [];
        $challengeHistoryInspires     = [];

        $challengeHistoryStepsWithPoints         = [];
        $challengeHistoryStepsWithPointsDiff     = [];
        $challengeHistoryExercisesWithPoints     = [];
        $challengeHistoryExercisesWithPointsDiff = [];
        $challengeHistoryInspiresWithPoints      = [];
        $challengeHistoryInspiresWithPointsDiff  = [];

        $challengeHistorySettings   = [];
        $challengeHistoryUserPoints = [];
        $userWisePoints             = [];

        $appTimezone = config('app.timezone');

        // challenge history data
        $challengeHistory['challenge_id']          = $this->getKey();
        $challengeHistory['creator_id']            = $this->creator_id;
        $challengeHistory['challenge_category_id'] = $this->challenge_category_id;
        $challengeHistory['timezone']              = $this->timezone;
        $challengeHistory['title']                 = $this->title;
        $challengeHistory['description']           = $this->description;
        $challengeHistory['start_date']            = $this->start_date;
        $challengeHistory['end_date']              = $this->end_date;

        if (!empty($pointCalcRules)) {
            $sttIndex = 0;
            foreach ($pointCalcRules as $key => $value) {
                $challengeHistorySettings[$sttIndex]['challenge_id'] = $this->getKey();
                $challengeHistorySettings[$sttIndex]['type']         = $key;
                $challengeHistorySettings[$sttIndex]['value']        = $value;
                $challengeHistorySettings[$sttIndex]['uom']          = 'Count';
                if ($key == 'distance' || $key == 'exercises_distance') {
                    $challengeHistorySettings[$sttIndex]['uom'] = 'Meter';
                } elseif ($key == 'exercises_duration') {
                    $challengeHistorySettings[$sttIndex]['uom'] = 'Minutes';
                }
                $sttIndex++;
            }
        }

        if (!empty($participants) && $participants->count() > 0) {
            $stepIndex = $inspireIndex = $exerciseIndex = 0;

            foreach ($participants as $key => $participant) {
                $userTimezone = $participant->timezone;

                $challengeHistoryParticipants[$key]['challenge_id']     = $this->getKey();
                $challengeHistoryParticipants[$key]['user_id']          = $participant->getKey();
                $challengeHistoryParticipants[$key]['participant_name'] = $participant->full_name;

                $startDateTime = Carbon::parse($this->start_date, $appTimezone)->setTimezone($userTimezone)->toDateString();
                $endDateTime   = Carbon::parse($this->end_date, $appTimezone)->setTimezone($userTimezone)->toDateString();

                // get user wise points
                if ($challengeRulesData->count() > 0) {
                    $points = $participant->getTotalPointsInChallenge($this, $pointCalcRules, $challengeRulesData);

                    $challengeHistoryUserPoints[$key]['user_id'] = $participant->getKey();
                    $challengeHistoryUserPoints[$key]['team_id'] = $participant->teams()->first()->getKey();
                    $challengeHistoryUserPoints[$key]['points']  = $points;
                    $challengeHistoryUserPoints[$key]['rank']    = 0;

                    $userWisePoints[$participant->getKey()] = $points;
                }

                $insertedStepsData = false;
                foreach ($challengeRulesData as $rule) {
                    // get user wise steps and distance data
                    // if insertedStepsData == true it will make array for steps data. This is set specially for combined type of challenge where one rule can be steps and one can be distance
                    if (($rule->short_name == 'distance' || $rule->short_name == 'steps') && !$insertedStepsData) {
                        $memberSteps = $participant
                            ->steps()
                            ->whereRaw("DATE(CONVERT_TZ(log_date, ?, ?)) >= ?",[
                                $appTimezone, $userTimezone, $startDateTime
                            ])
                            ->whereRaw("DATE(CONVERT_TZ(log_date, ?, ?)) <= ?",[
                                $appTimezone, $userTimezone, $endDateTime
                            ])
                            ->get();

                        if (!empty($memberSteps) && $memberSteps->count() > 0) {
                            foreach ($memberSteps as $key => $memberStep) {
                                $challengeHistorySteps[$stepIndex]['challenge_id'] = $this->getKey();
                                $challengeHistorySteps[$stepIndex]['user_id']      = $participant->getKey();
                                $challengeHistorySteps[$stepIndex]['tracker']      = $memberStep->tracker;
                                $challengeHistorySteps[$stepIndex]['steps']        = $memberStep->steps;
                                $challengeHistorySteps[$stepIndex]['distance']     = $memberStep->distance;
                                $challengeHistorySteps[$stepIndex]['calories']     = $memberStep->calories;
                                $challengeHistorySteps[$stepIndex]['log_date']     = Carbon::parse($memberStep->log_date)->toDateTimeString();

                                $challengeHistoryStepsWithPoints[$stepIndex] = $challengeHistorySteps[$stepIndex];

                                $challengeHistoryStepsWithPoints[$stepIndex]['points'] = (!empty($userWisePoints[$participant->getKey()])) ? $userWisePoints[$participant->getKey()] : 0;

                                $stepIndex++;
                            }

                            $insertedStepsData = true;
                        }
                    } elseif ($rule->short_name == 'meditations') {
                        // get user wise meditations data
                        $memberInspires = $participant
                            ->completedMeditationTracks()
                            ->whereRaw("DATE(CONVERT_TZ(user_listened_tracks.created_at, ?, ?)) >= ?",[
                                $appTimezone,$userTimezone, $startDateTime
                            ])
                            ->whereRaw("DATE(CONVERT_TZ(user_listened_tracks.created_at, ?, ?)) <= ?",[
                                $appTimezone, $userTimezone, $endDateTime 
                            ])
                            ->get();

                        if (!empty($memberInspires) && $memberInspires->count() > 0) {
                            foreach ($memberInspires as $key => $memberInspire) {
                                $challengeHistoryInspires[$inspireIndex]['challenge_id']        = $this->getKey();
                                $challengeHistoryInspires[$inspireIndex]['user_id']             = $participant->getKey();
                                $challengeHistoryInspires[$inspireIndex]['meditation_track_id'] = $memberInspire->pivot->meditation_track_id;
                                $challengeHistoryInspires[$inspireIndex]['duration_listened']   = $memberInspire->pivot->duration_listened;
                                $challengeHistoryInspires[$inspireIndex]['log_date']            = Carbon::parse($memberInspire->pivot->created_at)->toDateTimeString();

                                $challengeHistoryInspiresWithPoints[$inspireIndex] = $challengeHistoryInspires[$inspireIndex];

                                $challengeHistoryInspiresWithPoints[$inspireIndex]['points'] = (!empty($userWisePoints[$participant->getKey()])) ? $userWisePoints[$participant->getKey()] : 0;

                                $inspireIndex++;
                            }
                        }
                    } elseif ($rule->short_name == 'exercises' && $rule->model_name == 'Exercise') {
                        // get user wise exercises data
                        $memberExercises = $participant->exercises()
                            ->where(function ($q) use ($startDateTime, $endDateTime, $userTimezone, $appTimezone) {
                                $q->whereDate(\DB::raw("CONVERT_TZ(user_exercise.start_date, '{$appTimezone}', '{$userTimezone}')"), '>=', $startDateTime)
                                    ->whereDate(\DB::raw("CONVERT_TZ(user_exercise.start_date, '{$appTimezone}', '{$userTimezone}')"), '<=', $endDateTime);
                            })
                            ->whereNull('user_exercise.deleted_at')
                            ->where('user_exercise.exercise_id', $rule->model_id)
                            ->get();

                        if (!empty($memberExercises) && $memberExercises->count() > 0) {
                            foreach ($memberExercises as $key => $memberExercise) {
                                $challengeHistoryExercises[$exerciseIndex]['challenge_id'] = $this->getKey();

                                $challengeHistoryExercises[$exerciseIndex]['user_id'] = $participant->getKey();

                                $challengeHistoryExercises[$exerciseIndex]['exercise_id'] = $memberExercise->pivot->exercise_id;

                                $challengeHistoryExercises[$exerciseIndex]['tracker'] = $memberExercise->pivot->tracker;

                                $challengeHistoryExercises[$exerciseIndex]['calories'] = $memberExercise->pivot->calories;

                                $challengeHistoryExercises[$exerciseIndex]['distance'] = $memberExercise->pivot->distance;

                                $challengeHistoryExercises[$exerciseIndex]['duration'] = $memberExercise->pivot->duration;

                                $challengeHistoryExercises[$exerciseIndex]['start_date'] = $memberExercise->pivot->start_date;

                                $challengeHistoryExercises[$exerciseIndex]['end_date'] = $memberExercise->pivot->end_date;

                                $challengeHistoryExercisesWithPoints[$exerciseIndex] = $challengeHistoryExercises[$exerciseIndex];

                                $challengeHistoryExercisesWithPoints[$exerciseIndex]['points'] = (!empty($userWisePoints[$participant->getKey()])) ? $userWisePoints[$participant->getKey()] : 0;

                                $exerciseIndex++;
                            }
                        }
                    }
                }
            }

            if (!empty($challengeHistoryStepsWithPoints)) {
                $challengeStepOldData = $this->challengeHistorySteps()->get()->toArray();
                if (!empty($challengeStepOldData)) {
                    foreach ($challengeHistoryStepsWithPoints as $key => $value) {
                        $insertDataFlag = true;
                        foreach ($challengeStepOldData as $val) {
                            if ($value['user_id'] == $val['user_id'] && $value['tracker'] == $val['tracker'] && $value['steps'] == $val['steps'] && $value['distance'] == $val['distance'] && $value['calories'] == $val['calories'] && $value['log_date'] == $val['log_date']) {
                                $insertDataFlag = false;
                            }
                        }
                        if ($insertDataFlag) {
                            $challengeHistoryStepsWithPointsDiff[] = $value;
                        }
                    }
                } else {
                    $challengeHistoryStepsWithPointsDiff = $challengeHistoryStepsWithPoints;
                }
            }

            if (!empty($challengeHistoryExercisesWithPoints)) {
                $challengeExercisesOldData = $this->challengeHistoryExercises()->get()->toArray();
                if (!empty($challengeExercisesOldData)) {
                    foreach ($challengeHistoryExercisesWithPoints as $key => $value) {
                        $insertDataFlag = true;
                        foreach ($challengeExercisesOldData as $val) {
                            if ($value['user_id'] == $val['user_id'] && $value['exercise_id'] == $val['exercise_id'] && $value['tracker'] == $val['tracker'] && $value['duration'] == $val['duration'] && $value['distance'] == $val['distance'] && $value['calories'] == $val['calories'] && $value['start_date'] == $val['start_date'] && $value['end_date'] == $val['end_date']) {
                                $insertDataFlag = false;
                            }
                        }
                        if ($insertDataFlag) {
                            $challengeHistoryExercisesWithPointsDiff[] = $value;
                        }
                    }
                } else {
                    $challengeHistoryExercisesWithPointsDiff = $challengeHistoryExercisesWithPoints;
                }
            }

            if (!empty($challengeHistoryInspiresWithPoints)) {
                $challengeInspiresOldData = $this->challengeHistoryInspires()->get()->toArray();
                if (!empty($challengeInspiresOldData)) {
                    foreach ($challengeHistoryInspiresWithPoints as $key => $value) {
                        $insertDataFlag = true;
                        foreach ($challengeInspiresOldData as $val) {
                            if ($value['user_id'] == $val['user_id'] && $value['meditation_track_id'] == $val['meditation_track_id'] && $value['duration_listened'] == $val['duration_listened'] && $value['log_date'] == $val['log_date']) {
                                $insertDataFlag = false;
                            }
                        }
                        if ($insertDataFlag) {
                            $challengeHistoryInspiresWithPointsDiff[] = $value;
                        }
                    }
                } else {
                    $challengeHistoryInspiresWithPointsDiff = $challengeHistoryInspiresWithPoints;
                }
            }

            // delete all freezed data for this challenge
            $this->challengeHistory()->delete();
            $this->challengeHistoryParticipants()->delete();
            $this->challengeHistorySteps()->delete();
            $this->challengeHistoryExercises()->delete();
            $this->challengeHistoryInspires()->delete();
            $this->challengeWiseUserPoints()->delete();
            $this->challengeHistorySettings()->delete();

            // re-dump all data for this challenge to get final data for completed challenge
            $this->challengeHistory()->create($challengeHistory);
            $this->challengeHistoryParticipants()->createMany($challengeHistoryParticipants);

            if (!empty($challengeHistorySteps)) {
                $this->challengeHistorySteps()->createMany($challengeHistorySteps);
            }

            if (!empty($challengeHistoryExercises)) {
                $this->challengeHistoryExercises()->createMany($challengeHistoryExercises);
            }

            if (!empty($challengeHistoryInspires)) {
                $this->challengeHistoryInspires()->createMany($challengeHistoryInspires);
            }

            if (!empty($challengeHistoryStepsWithPointsDiff)) {
                $this->challengeUserStepsHistory()->createMany($challengeHistoryStepsWithPointsDiff);
            }

            if (!empty($challengeHistoryExercisesWithPointsDiff)) {
                $this->challengeUserExercisesHistory()->createMany($challengeHistoryExercisesWithPointsDiff);
            }

            if (!empty($challengeHistoryInspiresWithPointsDiff)) {
                $this->challengeUserInspireHistory()->createMany($challengeHistoryInspiresWithPointsDiff);
            }

            // get user points in descending order
            uasort($userWisePoints, "getDescendingArray");

            if (!empty($challengeHistoryUserPoints) && !empty($userWisePoints)) {
                $challengeUserPoints = [];
                foreach ($challengeHistoryUserPoints as $key => $challengeHistoryUserPoint) {
                    $userRank = $this->getUserRankInChallengeUsingPoints($userWisePoints, $challengeHistoryUserPoint['user_id']);

                    $challengeHistoryUserPoint['rank'] = $userRank;

                    array_push($challengeUserPoints, $challengeHistoryUserPoint);
                }

                $this->challengeWiseUserPoints()->createMany($challengeUserPoints);
            }

            $this->challengeHistorySettings()->createMany($challengeHistorySettings);
        }
    }

    /**
     * @return integer
     */
    public function getUserRankInChallengeUsingPoints($userWisePoints, $participantId): int
    {
        $position = $userData = 0;
        if (!empty($userWisePoints)) {
            $pointsForPosition = array_unique($userWisePoints);
            if (array_key_exists($participantId, $userWisePoints)) {
                if (!empty($pointsForPosition)) {
                    foreach ($pointsForPosition as $userData) {
                        ++$position;
                        if ($userWisePoints[$participantId] == $userData) {
                            break;
                        }
                    }
                }
            }
        }

        return $position;
    }

    public function sendFinishedNotifications()
    {
        $user     = auth()->user();
        $title    = trans('notifications.challenge.challenge-finished.title');
        $message  = trans('notifications.challenge.challenge-finished.message');
        $message  = str_replace(["#challenge_name#"], [$this->title], $message);
        $deepLink = 'zevolife://zevo/challenge/leaderboad/' . $this->id;

        // Check company plan access
        $checkChallengeAccess = getCompanyPlanAccess($user, 'my-challenges');
        $notification         = Notification::create([
            'type'             => 'Auto',
            'creator_id'       => $this->creator_id,
            'company_id'       => $this->company_id,
            'creator_timezone' => $this->timezone,
            'title'            => $title,
            'message'          => $message,
            'push'             => true,
            'scheduled_at'     => now()->toDateTimeString(),
            'deep_link_uri'    => $deepLink,
            'is_mobile'        => config('notification.challenge.finished.is_mobile'),
            'is_portal'        => config('notification.challenge.finished.is_portal'),
            'tag'              => 'challenge',
        ]);

        $pushMembers = [];
        $members     = $this->members()->where('status', 'Accepted')->get();

        foreach ($members as $member) {
            $member->notifications()->attach($notification, ['sent' => true, 'sent_on' => now()->toDateTimeString()]);

            $userNotification = NotificationSetting::select('flag')
                ->where(['flag' => 1, 'user_id' => $member->getKey()])
                ->whereRaw('(`user_notification_settings`.`module` = ? OR `user_notification_settings`.`module` = ?)', ['challenges', 'all'])
                ->first();
            $sendPush = (isset($userNotification->flag) && $userNotification->flag);

            if ($sendPush) {
                $pushMembers[] = $member;
            }
        }

        if (!empty($pushMembers) && $checkChallengeAccess) {
            // send notification to all users
            \Notification::send(
                $pushMembers,
                new SystemAutoNotification($notification, 'challenge-finished')
            );
        }
    }

    public function sendStartReminderNotification()
    {
        try {
            // Check company plan access
            $user                 = User::where("id", $this->creator_id)->first();
            $checkChallengeAccess = getCompanyPlanAccess($user, 'my-challenges');
            if ($this->challenge_type == 'individual') {
                $members = $this->members()->where('status', 'Accepted')->get();
            } else {
                $teamData = $this->memberTeams()->get();
                $members  = $teamData->transform(function ($value) {
                    return $value->users()->get();
                })->flatten();
            }

            if (!empty($members)) {
                $title   = trans('notifications.challenge.challenge-start-reminder.title');
                $message = trans('notifications.challenge.challenge-start-reminder.message');
                $message = str_replace(["#challenge_name#"], [$this->title], $message);

                if ($checkChallengeAccess) {
                    $notification = Notification::create([
                        'type'             => 'Auto',
                        'creator_id'       => $this->creator_id,
                        'company_id'       => $this->company_id,
                        'creator_timezone' => $this->timezone,
                        'title'            => $title,
                        'message'          => $message,
                        'push'             => true,
                        'scheduled_at'     => now()->toDateTimeString(),
                        'deep_link_uri'    => 'zevolife://zevo/challenge/' . $this->id . '/upcoming',
                        'is_mobile'        => config('notification.personal_challenge.today_reminder.is_mobile'),
                        'is_portal'        => config('notification.personal_challenge.today_reminder.is_portal'),
                        'tag'              => 'challenge',
                    ]);
                }
                $challengeStartReminderPushUsers = [];

                foreach ($members as $member) {
                    $userRemindered = $member->challengeWiseUserLogData()
                        ->where('user_id', $member->getKey())
                        ->where('challenge_id', $this->getKey())
                        ->whereNotNull('start_remindered_at')
                        ->first();

                    $teamId    = $member->teams()->first()->id;
                    $companyId = $member->company()->first()->id;
                    $team      = \App\Models\Team::find($teamId);

                    $teamUserRemindered = $team->challengeWiseTeamLogData()
                        ->where('team_id', $teamId)
                        ->where('user_id', $member->getKey())
                        ->where('challenge_id', $this->getKey())
                        ->whereNotNull('start_remindered_at')
                        ->first();

                    if (($this->challenge_type == 'individual' && empty($userRemindered)) || ($this->challenge_type != 'individual' && empty($teamUserRemindered))) {
                        $userTimeZone = $member->timezone;
                        $start_date   = Carbon::parse($this->start_date, \config('app.timezone'))->setTimezone($userTimeZone);

                        $currentTime = \now($userTimeZone);

                        $diffInHours = $start_date->diffInHours($currentTime);

                        if ($diffInHours <= 12) {
                            if ($checkChallengeAccess) {
                                $member->notifications()->attach($notification, ['sent' => true, 'sent_on' => now()->toDateTimeString()]);

                                $userNotification = NotificationSetting::select('flag')
                                    ->where(['flag' => 1, 'user_id' => $member->getKey()])
                                    ->whereRaw('(`user_notification_settings`.`module` = ? OR `user_notification_settings`.`module` = ?)', ['challenges', 'all'])
                                    ->first();

                                if ($userNotification->flag ?? false) {
                                    $challengeStartReminderPushUsers[] = $member;
                                }
                            }

                            if ($this->challenge_type == 'individual') {
                                $member->challengeWiseUserLogData()->updateOrCreate([
                                    'user_id'      => $member->getKey(),
                                    'challenge_id' => $this->getKey(),
                                ], [
                                    'start_remindered_at' => now(\config('app.timezone'))->toDateTimeString(),
                                ]);
                            } elseif ($this->challenge_type == 'company_goal' || $this->challenge_type == 'team') {
                                $team->challengeWiseTeamLogData()->updateOrCreate([
                                    'team_id'      => $teamId,
                                    'user_id'      => $member->getKey(),
                                    'challenge_id' => $this->getKey(),
                                ], [
                                    'start_remindered_at' => now(\config('app.timezone'))->toDateTimeString(),
                                ]);
                            } else {
                                $team->challengeWiseTeamLogData()->updateOrCreate([
                                    'company_id'   => $companyId,
                                    'team_id'      => $teamId,
                                    'user_id'      => $member->getKey(),
                                    'challenge_id' => $this->getKey(),
                                ], [
                                    'start_remindered_at' => now(\config('app.timezone'))->toDateTimeString(),
                                ]);
                            }
                        }
                    }
                }

                // send notification to all users
                if (!empty($challengeStartReminderPushUsers)) {
                    \Notification::send(
                        $challengeStartReminderPushUsers,
                        new SystemAutoNotification($notification, 'challenge-started-reminder')
                    );
                }
            }
        } catch (\Exception $exception) {
            report($exception);
            throw $exception;
        }
    }

    public function sendFinishReminderNotification()
    {
        try {
            $challengeId  = $this->id;
            $challengeEnd = $this->end_date;
            $appTimezone  = config('app.timezone');

            if ($this->challenge_type == 'individual') {
                $members = $this->members()->where('status', 'Accepted')->get();
            } else {
                $teamData = $this->memberTeams()->get();
                $members  = $teamData->transform(function ($value) use ($challengeId, $appTimezone, $challengeEnd) {
                    $sentUserId = ChallengeWiseUserLogData::where("challenge_id", $challengeId)
                        ->where("team_id", $value->id)
                        ->whereNotNull('end_remindered_at')
                        ->pluck('user_id')
                        ->toArray();
                    if (!empty($sentUserId)) {
                        return $value->users()
                            ->wherePivotIn("user_id", $sentUserId, "and", "NotIn")
                            ->where(\DB::raw("TIMESTAMPDIFF(HOUR,CONVERT_TZ(now(), @@session.time_zone,users.timezone),CONVERT_TZ('{$challengeEnd}', '{$appTimezone}',users.timezone))"), '<=', 12)
                            ->get();
                    } else {
                        return $value->users()
                            ->where(\DB::raw("TIMESTAMPDIFF(HOUR,CONVERT_TZ(now(), @@session.time_zone,users.timezone),CONVERT_TZ('{$challengeEnd}', '{$appTimezone}',users.timezone))"), '<=', 12)
                            ->get();
                    }
                })->flatten();
            }
            if (!empty($members)) {
                $challenger = User::find($this->creator_id);
                $end_date   = Carbon::parse($this->end_date, \config('app.timezone'))
                    ->setTimezone($challenger->timezone)
                    ->format(config('zevolifesettings.date_format.default_datetime'));

                $title   = trans('notifications.challenge.challenge-end-reminder.title');
                $message = trans('notifications.challenge.challenge-end-reminder.message');
                $message = str_replace(["#challenge_name#", "#end_time#"], [$this->title, $end_date], $message);

                // Check company plan access
                $checkChallengeAccess = getCompanyPlanAccess($challenger, 'my-challenges');

                if ($checkChallengeAccess) {
                    $notification = Notification::create([
                        'type'             => 'Auto',
                        'creator_id'       => $this->creator_id,
                        'company_id'       => $this->company_id,
                        'creator_timezone' => $this->timezone,
                        'title'            => $title,
                        'message'          => $message,
                        'push'             => true,
                        'scheduled_at'     => now()->toDateTimeString(),
                        'deep_link_uri'    => $this->deep_link_uri,
                        'is_mobile'        => config('notification.personal_challenge.today_reminder.is_mobile'),
                        'is_portal'        => config('notification.personal_challenge.today_reminder.is_portal'),
                        'tag'              => 'challenge',
                    ]);
                }
                $challengeEndReminderPushUsers = array();

                foreach ($members as $member) {
                    $userRemindered     = array();
                    $teamUserRemindered = array();

                    if ($this->challenge_type == 'individual') {
                        $userRemindered = $member->challengeWiseUserLogData()
                            ->where('user_id', $member->getKey())
                            ->where('challenge_id', $this->getKey())
                            ->whereNotNull('end_remindered_at')
                            ->first();
                    }

                    $teamId    = $member->teams()->first()->id;
                    $companyId = $member->company()->first()->id;
                    $team      = \App\Models\Team::find($teamId);

                    if (($this->challenge_type == 'individual' && empty($userRemindered)) || ($this->challenge_type != 'individual' && empty($teamUserRemindered))) {
                        $userTimeZone = $member->timezone;
                        $end_date     = Carbon::parse($this->end_date, \config('app.timezone'))->setTimezone($userTimeZone);

                        $currentTime = \now($userTimeZone);

                        $diffInHours = $currentTime->diffInHours($end_date);
                        if ($diffInHours <= 12) {
                            if ($checkChallengeAccess) {
                                $member->notifications()->attach($notification, ['sent' => true, 'sent_on' => now()->toDateTimeString()]);

                                $userNotification = NotificationSetting::select('flag')
                                    ->where(['flag' => 1, 'user_id' => $member->getKey()])
                                    ->whereRaw('(`user_notification_settings`.`module` = ? OR `user_notification_settings`.`module` = ?)', ['challenges', 'all'])
                                    ->first();

                                if ($userNotification->flag ?? false) {
                                    $challengeEndReminderPushUsers[] = $member;
                                }
                            }

                            if ($this->challenge_type == 'individual') {
                                $member->challengeWiseUserLogData()->updateOrCreate([
                                    'user_id'      => $member->getKey(),
                                    'challenge_id' => $this->getKey(),
                                ], [
                                    'end_remindered_at' => now(\config('app.timezone'))->toDateTimeString(),
                                ]);
                            } elseif ($this->challenge_type == 'company_goal' || $this->challenge_type == 'team') {
                                $team->challengeWiseTeamLogData()->updateOrCreate([
                                    'team_id'      => $teamId,
                                    'user_id'      => $member->getKey(),
                                    'challenge_id' => $this->getKey(),
                                ], [
                                    'end_remindered_at' => now(\config('app.timezone'))->toDateTimeString(),
                                ]);
                            } else {
                                $team->challengeWiseTeamLogData()->updateOrCreate([
                                    'company_id'   => $companyId,
                                    'team_id'      => $teamId,
                                    'user_id'      => $member->getKey(),
                                    'challenge_id' => $this->getKey(),
                                ], [
                                    'end_remindered_at' => now(\config('app.timezone'))->toDateTimeString(),
                                ]);
                            }
                        }
                    }
                }

                if (!empty($challengeEndReminderPushUsers)) {
                    // send notification to all users
                    \Notification::send(
                        $challengeEndReminderPushUsers,
                        new SystemAutoNotification($notification, 'challenge-end-reminder')
                    );
                }
            }
        } catch (\Exception $exception) {
            report($exception);
            throw $exception;
        }
    }

    public function getWinners($pointCalcRules, $participants, $challengeRulesData)
    {
        $appTimezone = config('app.timezone');
        $winners     = array();
        $count       = 0;
        if ($this->short_name == 'fastest') {
            foreach ($participants as $participant) {
                $userTimezone = $participant->timezone;
                $startDate    = Carbon::parse($this->start_date, $appTimezone)->setTimezone($userTimezone);
                $endDate      = Carbon::parse($this->end_date, $appTimezone)->setTimezone($userTimezone);
                $daysRange    = \createDateRange($startDate, $endDate);

                $totalTargetCount = 0;
                $dateTimeArr      = [];

                foreach ($challengeRulesData as $rule) {
                    $targetCompleted = false;
                    $totalTarget     = 0;
                    foreach ($daysRange as $day) {
                        if (!$targetCompleted) {
                            if ($rule->short_name == 'distance') {
                                $records = $participant->steps()->where(\DB::raw("DATE(CONVERT_TZ(user_step.log_date, '{$appTimezone}', '{$userTimezone}'))"), '=', $day->toDateString())->orderBy('user_step.created_at')->get();

                                foreach ($records as $record) {
                                    $totalTarget += $record->distance;

                                    if ($totalTarget >= $rule->target) {
                                        $dateTime = $record->created_at;

                                        $dateTimeArr[] = $dateTime->diffInSeconds($this->start_date);

                                        $targetCompleted = true;
                                        break;
                                    }
                                }
                            } elseif ($rule->short_name == 'steps') {
                                $records = $participant->steps()->where(\DB::raw("DATE(CONVERT_TZ(user_step.log_date, '{$appTimezone}', '{$userTimezone}'))"), '=', $day->toDateString())->orderBy('user_step.created_at')->get();

                                foreach ($records as $record) {
                                    $totalTarget += $record->steps;

                                    if ($totalTarget >= $rule->target) {
                                        $dateTime = $record->created_at;

                                        $dateTimeArr[] = $dateTime->diffInSeconds($this->start_date);

                                        $targetCompleted = true;
                                        break;
                                    }
                                }
                            } elseif ($rule->short_name == 'exercises' && $rule->model_name == 'Exercise') {
                                $records = $participant->exercises()
                                    ->whereDate(\DB::raw("CONVERT_TZ(user_exercise.start_date, '{$appTimezone}', '{$userTimezone}')"), $day->toDateString())
                                    ->whereNull('user_exercise.deleted_at')
                                    ->where('user_exercise.exercise_id', $rule->model_id)
                                    ->orderBy('user_exercise.created_at')
                                    ->get();

                                foreach ($records as $record) {
                                    $totalTarget += ($rule->uom == 'meter') ? $record->distance : ($record->duration / 60);

                                    if ($totalTarget >= $rule->target) {
                                        $dateTime = $record->pivot->created_at;

                                        $dateTimeArr[] = $dateTime->diffInSeconds($this->start_date);

                                        $targetCompleted = true;
                                        break;
                                    }
                                }
                            } elseif ($rule->short_name == 'meditations') {
                                $records = $participant->completedMeditationTracks()->where(\DB::raw("DATE(CONVERT_TZ(user_listened_tracks.created_at, '{$appTimezone}', '{$userTimezone}'))"), '=', $day->toDateString())->orderBy('user_listened_tracks.created_at')->get();

                                foreach ($records as $record) {
                                    $totalTarget += 1;

                                    if ($totalTarget >= $rule->target) {
                                        $dateTime = $record->pivot->created_at;

                                        $dateTimeArr[] = $dateTime->diffInSeconds($this->start_date);

                                        $targetCompleted = true;
                                        break;
                                    }
                                }
                            }
                        }
                    }
                }

                if (array_sum($dateTimeArr) > 0) {
                    $usersData[$participant->id] = array_sum($dateTimeArr);
                }
            }

            if (!empty($usersData)) {
                asort($usersData, true);

                $winnerIds             = [];
                $pointsOnFirstPosition = array_values($usersData)[0];

                foreach ($usersData as $userId => $points) {
                    if ($points == $pointsOnFirstPosition) {
                        $winnerIds[] = $userId;
                    }
                }

                $winners = (!empty($winnerIds)) ? User::find($winnerIds) : [];
            }
        } elseif ($this->short_name == 'streak') {
            foreach ($participants as $participant) {
                $userTimezone = $participant->timezone;
                $startDate    = Carbon::parse($this->start_date, $appTimezone)->setTimezone($userTimezone);
                $endDate      = Carbon::parse($this->end_date, $appTimezone)->setTimezone($userTimezone);
                $daysRange    = \createDateRange($startDate, $endDate);

                $totalTargetCount = 0;
                foreach ($challengeRulesData as $rule) {
                    $totalCompletedDays = 0;
                    foreach ($daysRange as $day) {
                        if ($rule->short_name == 'distance') {
                            $count = $participant->getDistance($day->toDateString(), $appTimezone, $userTimezone);
                        } elseif ($rule->short_name == 'steps') {
                            $count = $participant->getSteps($day->toDateString(), $appTimezone, $userTimezone);
                        } elseif ($rule->short_name == 'exercises' && $rule->model_name == 'Exercise') {
                            $count = $participant->getExercises($day->toDateString(), $appTimezone, $userTimezone, $rule->uom, $rule->model_id);
                        } elseif ($rule->short_name == 'meditations') {
                            $count = $participant->getMeditation($day->toDateString(), $appTimezone, $userTimezone, $rule->uom);
                        }

                        if ($count >= $rule->target) {
                            $totalCompletedDays++;
                        }
                    }

                    if ($totalCompletedDays == count($daysRange)) {
                        $totalTargetCount++;
                    }
                }

                if ($totalTargetCount == $challengeRulesData->count()) {
                    $winnerIds[] = $participant->id;
                }
            }

            $winners = (!empty($winnerIds)) ? User::find($winnerIds) : [];
        } elseif ($this->short_name == 'most' || $this->short_name == 'combined' || $this->short_name == 'combined_most' || $this->short_name == 'first_to_reach') {
            foreach ($participants as $participant) {
                $userTimezone = $participant->timezone;
                $startDate    = Carbon::parse($this->start_date, $appTimezone)->setTimezone($userTimezone)->toDateString();
                $endDate      = Carbon::parse($this->end_date, $appTimezone)->setTimezone($userTimezone)->toDateString();

                if ($this->short_name == 'most') {
                    $pointsArr = [];
                    foreach ($challengeRulesData as $rule) {
                        if ($rule->short_name == 'distance') {
                            $userRulePoint = $participant->getDistancePointsInChallenge($pointCalcRules, $startDate, $endDate, $appTimezone, $userTimezone);

                            if ($userRulePoint > 0) {
                                $pointsArr[] = $userRulePoint;
                            }
                        } elseif ($rule->short_name == 'steps') {
                            $userRulePoint = $participant->getStepsPointsInChallenge($pointCalcRules, $startDate, $endDate, $appTimezone, $userTimezone);

                            if ($userRulePoint > 0) {
                                $pointsArr[] = $userRulePoint;
                            }
                        } elseif ($rule->short_name == 'exercises' && $rule->model_name == 'Exercise') {
                            $userRulePoint = $participant->getExercisesPointsInChallenge($pointCalcRules, $startDate, $endDate, $appTimezone, $userTimezone, $rule->uom, $rule->model_id);

                            if ($userRulePoint > 0) {
                                $pointsArr[] = $userRulePoint;
                            }
                        } elseif ($rule->short_name == 'meditations') {
                            $userRulePoint = $participant->getMeditationPointsInChallenge($pointCalcRules, $startDate, $endDate, $appTimezone, $userTimezone, $rule->uom);

                            if ($userRulePoint > 0) {
                                $pointsArr[] = $userRulePoint;
                            }
                        }
                    }
                } else {
                    $pointsArr = [];
                    foreach ($challengeRulesData as $rule) {
                        if ($rule->short_name == 'distance') {
                            $userRulePoint = $participant->getDistancePointsInChallenge($pointCalcRules, $startDate, $endDate, $appTimezone, $userTimezone);

                            $dataDone = $userRulePoint * $pointCalcRules['distance'];

                            if ($dataDone >= $rule->target) {
                                $pointsArr[] = $userRulePoint;
                            }
                        } elseif ($rule->short_name == 'steps') {
                            $userRulePoint = $participant->getStepsPointsInChallenge($pointCalcRules, $startDate, $endDate, $appTimezone, $userTimezone);

                            $dataDone = $userRulePoint * $pointCalcRules['steps'];

                            if ($dataDone >= $rule->target) {
                                $pointsArr[] = $userRulePoint;
                            }
                        } elseif ($rule->short_name == 'exercises' && $rule->model_name == 'Exercise') {
                            $userRulePoint = $participant->getExercisesPointsInChallenge($pointCalcRules, $startDate, $endDate, $appTimezone, $userTimezone, $rule->uom, $rule->model_id);

                            $dataDone = ($rule->uom == 'meter') ? $userRulePoint * $pointCalcRules['exercises_distance'] : $userRulePoint * $pointCalcRules['exercises_duration'];

                            if ($dataDone >= $rule->target) {
                                $pointsArr[] = $userRulePoint;
                            }
                        } elseif ($rule->short_name == 'meditations') {
                            $userRulePoint = $participant->getMeditationPointsInChallenge($pointCalcRules, $startDate, $endDate, $appTimezone, $userTimezone, $rule->uom);

                            $dataDone = $userRulePoint * $pointCalcRules['meditations'];

                            if ($dataDone >= $rule->target) {
                                $pointsArr[] = $userRulePoint;
                            }
                        }
                    }
                }

                if (count($pointsArr) == $challengeRulesData->count()) {
                    // consider user as winner nominee if completed all targets
                    $usersData[$participant->id] = array_sum($pointsArr);
                }
            }

            if (!empty($usersData)) {
                arsort($usersData, true); // sorting descending wise of user points

                $winnerIds             = [];
                $pointsOnFirstPosition = array_values($usersData)[0];

                foreach ($usersData as $userId => $points) {
                    if ($points == $pointsOnFirstPosition) {
                        $winnerIds[] = $userId;
                    }
                }

                $winners = (!empty($winnerIds)) ? User::find($winnerIds) : [];
            }
        }
        return $winners;
    }

    /**
     * @return true
     */
    public function firstToReachTargetAndFinish()
    {
        // do evey process in challenger timezone
        if (!empty($this->creator_id)) {
            $company              = \App\Models\Company::find($this->company_id);
            $checkChallengeAccess = getCompanyPlanAccess([], 'my-challenges', $company);

            $pointCalcRules = (!empty($company) && $company->companyWiseChallengeSett()->count() > 0) ? $company->companyWiseChallengeSett()->pluck('value', 'type')->toArray() : config('zevolifesettings.default_limits');

            $challengeRulesData = $this->challengeRules()->join('challenge_targets', 'challenge_targets.id', '=', 'challenge_rules.challenge_target_id')->select('challenge_rules.*', 'challenge_targets.short_name', 'challenge_targets.name')->get();

            if ($this->challenge_type == "individual") {
                // challenge participants data
                $participants = $this->members()->where('status', 'Accepted')->get();

                // get winners of challenge
                $winners = $this->getWinners($pointCalcRules, $participants, $challengeRulesData);

                // send notifications to challenge winners
                if (!empty($winners) && $winners->count() > 0) {
                    $userIds = $winners->pluck("id")->toArray();

                    // freeze history
                    $this->collectAndDumpChallengeDataInHisroty($pointCalcRules, $participants, $challengeRulesData);

                    if ($checkChallengeAccess) {
                        // send finished notification
                        $this->sendFinishedNotifications();

                        $this->sendNotificationsToAllWinners($winners);
                    }

                    $loosingUsers = $this->members()->wherePivot('status', 'Accepted')->wherePivotIn('user_id', $userIds, 'and', 'NotIn')->get();

                    if ($checkChallengeAccess && !empty($loosingUsers) && $loosingUsers->count() > 0) {
                        $this->sendNotificationToLooser($loosingUsers);
                    }

                    // mark challenge as finished
                    $this->update(['finished' => true, 'end_date' => now()->toDateTimeString()]);
                }
            } elseif ($this->challenge_type == "inter_company") {
                // check if challenge type is inter company then calculate point
                $participantCompany = $this->memberCompanies()->groupBy('challenge_participants.company_id')->get();

                // get winners of challenge
                $winners = $this->getWinnersForInterCompanyChallenge($pointCalcRules, $participantCompany, $challengeRulesData);

                if (!empty($winners) && $winners->count() > 0) {
                    $companyIds = $winners->pluck("id")->toArray();

                    $this->collectAndDumpInterCompanyChallengeDataInHisroty($pointCalcRules, $participantCompany, $challengeRulesData);

                    if ($checkChallengeAccess && !empty($participantCompany) && $participantCompany->count() > 0) {
                        foreach ($participantCompany as $value) {
                            $participantTeam = $this->memberTeams()->wherePivot('company_id', $value->getKey())->get();
                            // send finished notification
                            $this->sendTeamFinishedNotifications($participantTeam);
                        }
                    }

                    if ($checkChallengeAccess) {
                        $this->sendNotificationsToAllInterCompanyWinners($winners);
                    }

                    $loosingTeams = $this->memberTeams()->wherePivotIn('company_id', $companyIds, 'and', 'NotIn')->get();

                    if ($checkChallengeAccess && !empty($loosingTeams) && $loosingTeams->count() > 0) {
                        $this->sendNotificationToLooserTeamsUser($loosingTeams);
                    }

                    // mark challenge as finished
                    $this->update(['finished' => true, 'end_date' => now()->toDateTimeString()]);
                }
            } else {
                // challenge participants data
                $participantTeam = $this->memberTeams()->get();

                if ($this->challenge_type == "team") {
                    // get winners of challenge
                    $winners = $this->getWinnersForTeamChallenge($pointCalcRules, $participantTeam, $challengeRulesData);
                } elseif ($this->challenge_type == "company_goal") {
                    // get winners of challenge
                    $winners = $this->getWinnersForCompanyChallenge($pointCalcRules, $participantTeam, $challengeRulesData);
                }
                // send notifications to challenge winners
                if (!empty($winners) && $winners->count() > 0) {
                    $teamIds = $winners->pluck("id")->toArray();

                    // freeze history
                    $this->collectAndDumpTeamChallengeDataInHisroty($pointCalcRules, $participantTeam, $challengeRulesData);

                    if ($checkChallengeAccess) {
                        // send finished notification
                        $this->sendTeamFinishedNotifications($participantTeam);

                        $this->sendNotificationsToAllTeamWinners($winners);
                    }

                    $loosingTeams = $this->memberTeams()->wherePivotIn('team_id', $teamIds, 'and', 'NotIn')->get();
                    if ($checkChallengeAccess && !empty($loosingTeams) && $loosingTeams->count() > 0) {
                        $this->sendNotificationToLooserTeamsUser($loosingTeams);
                    }

                    // mark challenge as finished
                    $this->update(['finished' => true, 'end_date' => now()->toDateTimeString()]);
                }
            }
        }

        return true;
    }

    public function sendNotificationsToAllWinners($winners)
    {
        $user = auth()->user();
        // Check company plan access
        $checkChallengeAccess = getCompanyPlanAccess($user, 'my-challenges');
        $title                = trans('notifications.challenge.challenge-won.title');
        $message              = trans('notifications.challenge.challenge-won.message.individual.non-recurring');
        $badgeData            = Badge::where("challenge_type_slug", "individual")->first();
        $challengesId         = array();
        if ($this->recurring || !empty($this->parent_id)) {
            $message = trans('notifications.challenge.challenge-won.message.individual.recurring');
            if (!empty($this->parent_id)) {
                $challengesId   = self::where("parent_id", $this->parent_id)->pluck("id")->toArray();
                $challengesId[] = $this->parent_id;
            }
        }

        $message = str_replace(["#challenge_name#"], [$this->title], $message);

        foreach ($winners as $winner) {
            $level = 0;
            if (!empty($badgeData)) {
                if ($this->recurring || !empty($this->parent_id)) {
                    $message = trans('notifications.challenge.challenge-won.message.individual.recurring');

                    if (!empty($this->parent_id)) {
                        $latestRecord = $winner->badges()
                            ->wherePivot("badge_id", $badgeData->id)
                            ->wherePivot("user_id", $winner->id)
                            ->wherePivotIn('model_id', $challengesId)
                            ->wherePivot("model_name", 'challenge')
                            ->orderBy("badge_user.id", "DESC")
                            ->limit(1)
                            ->first();
                        if (!empty($latestRecord)) {
                            $level = (int) $latestRecord->pivot->level + 1;
                        } else {
                            $level = 1;
                        }
                    } else {
                        $level = 1;
                    }

                    $message = str_replace(["#challenge_name#", "#level_no#"], [$this->title, $level], $message);
                }

                $badgeInput = [
                    'status'     => "Active",
                    'model_id'   => $this->id,
                    'model_name' => 'challenge',
                    'level'      => $level,
                ];
                $badgeData->badgeusers()->attach($winner->id, $badgeInput);
            }

            $notification = Notification::create([
                'type'             => 'Auto',
                'creator_id'       => $this->creator_id,
                'company_id'       => $this->company_id,
                'creator_timezone' => $this->timezone,
                'title'            => $title,
                'message'          => __($message, ['first_name' => $winner->first_name]),
                'push'             => true,
                'scheduled_at'     => now()->toDateTimeString(),
                'deep_link_uri'    => $this->deep_link_uri,
                'is_mobile'        => config('notification.personal_challenge.won.is_mobile'),
                'is_portal'        => config('notification.personal_challenge.won.is_portal'),
                'tag'              => 'challenge',
            ]);

            $winner->notifications()->attach($notification, ['sent' => true, 'sent_on' => now()->toDateTimeString()]);

            $userNotification = NotificationSetting::select('flag')
                ->where(['flag' => 1, 'user_id' => $winner->id])
                ->whereRaw('(`user_notification_settings`.`module` = ? OR `user_notification_settings`.`module` = ?)', ['challenges', 'all'])
                ->first();

            $winner->challengeWiseUserLogData()->updateOrCreate([
                'user_id'      => $winner->getKey(),
                'challenge_id' => $this->getKey(),
            ], [
                'is_winner' => true,
                'won_at'    => now(\config('app.timezone'))->toDateTimeString(),
            ]);

            if ($checkChallengeAccess) {
                if ($userNotification->flag ?? false) {
                    // send notification to all users
                    \Notification::send(
                        $winner,
                        new SystemAutoNotification($notification, 'challenge-won')
                    );
                }
            }
        }
    }

    /**
     * @return true
     */
    public function expireBadges()
    {
        // expire all badges related to this challenge
        \DB::table('badge_user')
            ->where('model_id', $this->id)
            ->where('model_name', 'challenge')
            ->where('status', 'Active')
            ->update(['status' => 'Expired', 'expired_at' => now()->toDateTimeString()]);
        return true;
    }

    /**
     * @return HasMany
     */
    public function challengeWiseUserPoints(): HasMany
    {
        return $this->hasMany('App\Models\ChallengeWiseUserPoints');
    }

    /**
     * @return HasMany
     */
    public function challengeWiseTeamPoints(): HasMany
    {
        return $this->hasMany('App\Models\ChallengeWiseTeamPoints');
    }

    /**
     * @return HasMany
     */
    public function challengeWiseCompanyPoints(): HasMany
    {
        return $this->hasMany('App\Models\ChallengeWiseCompanyPoints');
    }

    /**
     * @return true
     */
    public function freezeChallengeDataAfterStart()
    {
        // do evey process in challenger timezone
        if (!empty($this->creator_id) && !empty($this->start_date) && !empty($this->end_date)) {
            $challengeTimezone = $this->timezone;

            $now = \now($challengeTimezone);

            $challengeStartDate = Carbon::parse($this->start_date, config('app.timezone'))->setTimezone($challengeTimezone);

            $challengeEndDate = Carbon::parse($this->end_date, config('app.timezone'))->setTimezone($challengeTimezone);

            // individual challenge will be completed according to challenge timezone
            if ($now->toDateTimeString() >= $challengeStartDate->toDateTimeString() && $now->toDateTimeString() <= $challengeEndDate->toDateTimeString()) {
                $company = \App\Models\Company::find($this->company_id);

                $pointCalcRules = (!empty($company) && $company->companyWiseChallengeSett()->count() > 0) ? $company->companyWiseChallengeSett()->pluck('value', 'type')->toArray() : config('zevolifesettings.default_limits');

                $challengeRulesData = $this->challengeRules()->join('challenge_targets', 'challenge_targets.id', '=', 'challenge_rules.challenge_target_id')->select('challenge_rules.*', 'challenge_targets.short_name', 'challenge_targets.name')->get();

                $history = $this->challengeHistory;

                // freeze history
                if (empty($history)) {
                    if ($this->challenge_type == 'individual') {
                        // check if challenge type is individual then calculate point
                        // challenge participants data
                        $participants = $this->members()->where('status', 'Accepted')->get();
                        $this->collectAndDumpChallengeDataInHisroty($pointCalcRules, $participants, $challengeRulesData);
                    } elseif ($this->challenge_type == 'inter_company') {
                        // check if challenge type is inter company then calculate point
                        $participantCompany = $this->memberCompanies()->groupBy('challenge_participants.company_id')->get();
                        $this->collectAndDumpInterCompanyChallengeDataInHisroty($pointCalcRules, $participantCompany, $challengeRulesData);
                    } else {
                        // check if challenge type is team / company_goal then calculate point
                        $participantTeam = $this->memberTeams()->get();

                        $this->collectAndDumpTeamChallengeDataInHisroty($pointCalcRules, $participantTeam, $challengeRulesData);
                    }
                }

                $this->update(['freezed_data_at' => now()->toDateTimeString()]);
            }
        }
    }

    /**
     * @return true
     */
    public function freezeChallengeData()
    {
        // do evey process in challenger timezone
        if (!empty($this->creator_id) && !empty($this->start_date) && !empty($this->end_date)) {
            $challengeTimezone = $this->timezone;

            $now = \now($challengeTimezone);

            $challengeStartDate = Carbon::parse($this->start_date, config('app.timezone'))->setTimezone($challengeTimezone);

            $challengeEndDate = Carbon::parse($this->end_date, config('app.timezone'))->setTimezone($challengeTimezone);

            // individual challenge will be completed according to challenge timezone
            if ($now->toDateTimeString() >= $challengeStartDate->toDateTimeString() && $now->toDateTimeString() <= $challengeEndDate->toDateTimeString()) {
                $company = \App\Models\Company::find($this->company_id);

                $pointCalcRules = (!empty($company) && $company->companyWiseChallengeSett()->count() > 0) ? $company->companyWiseChallengeSett()->pluck('value', 'type')->toArray() : config('zevolifesettings.default_limits');

                // challenge participants data
                $participants = $this->members()->where('status', 'Accepted')->get();

                $challengeRulesData = $this->challengeRules()->join('challenge_targets', 'challenge_targets.id', '=', 'challenge_rules.challenge_target_id')->select('challenge_rules.*', 'challenge_targets.short_name', 'challenge_targets.name')->get();

                $history = $this->challengeHistory;

                // freeze history
                if (!empty($history)) {

                    if ($this->challenge_type == 'individual') {
                        // check if challenge type is individual then calculate point
                        // challenge participants data
                        $participants = $this->members()->where('status', 'Accepted')->get();
                        $this->collectAndDumpChallengeDataInHisroty($pointCalcRules, $participants, $challengeRulesData);
                    } elseif ($this->challenge_type == 'inter_company') {
                        // check if challenge type is inter company then calculate point
                        $participantCompany = $this->memberCompanies()->groupBy('challenge_participants.company_id')->get();

                        $this->collectAndDumpInterCompanyChallengeDataInHisroty($pointCalcRules, $participantCompany, $challengeRulesData);
                    } else {
                        // check if challenge type is team / company_goal then calculate point
                        $participantTeam = $this->memberTeams()->get();

                        $this->collectAndDumpTeamChallengeDataInHisroty($pointCalcRules, $participantTeam, $challengeRulesData);
                    }
                }

                $this->update(['freezed_data_at' => now()->toDateTimeString()]);
            }
        }
    }

    public function collectAndDumpTeamChallengeDataInHisroty($pointCalcRules, $participantTeam, $challengeRulesData)
    {

        $challengeHistory             = [];
        $challengeHistoryParticipants = [];
        $challengeHistorySteps        = [];
        $challengeHistoryExercises    = [];
        $challengeHistoryInspires     = [];

        $challengeHistoryStepsWithPoints         = [];
        $challengeHistoryStepsWithPointsDiff     = [];
        $challengeHistoryExercisesWithPoints     = [];
        $challengeHistoryExercisesWithPointsDiff = [];
        $challengeHistoryInspiresWithPoints      = [];
        $challengeHistoryInspiresWithPointsDiff  = [];

        $challengeHistorySettings         = [];
        $challengeHistoryUserPoints       = [];
        $challengeTeamPoints              = [];
        $userWisePoints                   = [];
        $teamWisePoints                   = [];
        $ChallengeHistoryTeamParticipents = [];

        $appTimezone = config('app.timezone');

        // challenge history data
        $challengeHistory['challenge_id']          = $this->getKey();
        $challengeHistory['creator_id']            = $this->creator_id;
        $challengeHistory['challenge_category_id'] = $this->challenge_category_id;
        $challengeHistory['challenge_type']        = $this->challenge_type;
        $challengeHistory['timezone']              = $this->timezone;
        $challengeHistory['title']                 = $this->title;
        $challengeHistory['description']           = $this->description;
        $challengeHistory['start_date']            = $this->start_date;
        $challengeHistory['end_date']              = $this->end_date;

        if (!empty($pointCalcRules)) {
            $sttIndex = 0;
            foreach ($pointCalcRules as $key => $value) {
                $challengeHistorySettings[$sttIndex]['challenge_id'] = $this->getKey();
                $challengeHistorySettings[$sttIndex]['type']         = $key;
                $challengeHistorySettings[$sttIndex]['value']        = $value;
                $challengeHistorySettings[$sttIndex]['uom']          = 'Count';
                if ($key == 'distance' || $key == 'exercises_distance') {
                    $challengeHistorySettings[$sttIndex]['uom'] = 'Meter';
                } elseif ($key == 'exercises_duration') {
                    $challengeHistorySettings[$sttIndex]['uom'] = 'Minutes';
                }
                $sttIndex++;
            }
        }

        if (!empty($participantTeam) && $participantTeam->count() > 0) {
            $stepIndex = $inspireIndex = $exerciseIndex = $historyUsers = $historyTeamUsers = 0;

            foreach ($participantTeam as $key1 => $teamParticipant) {
                $challengeHistoryParticipants[$key1]['challenge_id']     = $this->getKey();
                $challengeHistoryParticipants[$key1]['team_id']          = $teamParticipant->getKey();
                $challengeHistoryParticipants[$key1]['participant_name'] = $teamParticipant->name;
                $totalParticipant                                        = 0;
                $totalTeamPoints                                         = 0;
                if (!empty($teamParticipant->users) && $teamParticipant->users->count() > 0) {
                    foreach ($teamParticipant->users as $participant) {
                        $ChallengeHistoryTeamParticipents[$historyTeamUsers]['user_id']          = $participant->getKey();
                        $ChallengeHistoryTeamParticipents[$historyTeamUsers]['team_id']          = $participant->teams()->first()->getKey();
                        $ChallengeHistoryTeamParticipents[$historyTeamUsers]['participant_name'] = $participant->full_name;
                        $ChallengeHistoryTeamParticipents[$historyTeamUsers]['timezone']         = $participant->timezone;
                        $ChallengeHistoryTeamParticipents[$historyTeamUsers++]['challenge_type'] = $this->challenge_type;

                        $userTimezone = $participant->timezone;

                        $startDateTime = Carbon::parse($this->start_date, $appTimezone)->setTimezone($userTimezone)->toDateString();
                        $endDateTime   = Carbon::parse($this->end_date, $appTimezone)->setTimezone($userTimezone)->toDateString();

                        // get user wise points
                        if ($challengeRulesData->count() > 0) {
                            $points = $participant->getTotalPointsInChallenge($this, $pointCalcRules, $challengeRulesData);

                            $challengeHistoryUserPoints[$historyUsers]['user_id'] = $participant->getKey();
                            $challengeHistoryUserPoints[$historyUsers]['team_id'] = $participant->teams()->first()->getKey();
                            $challengeHistoryUserPoints[$historyUsers]['points']  = $points;
                            $challengeHistoryUserPoints[$historyUsers]['rank']    = 0;

                            $userWisePoints[$participant->getKey()] = $points;
                            $totalTeamPoints += $points;
                            $totalParticipant++;
                            $historyUsers++;
                        }

                        $insertedStepsData = false;
                        foreach ($challengeRulesData as $rule) {
                            // get user wise steps and distance data
                            // if insertedStepsData == true it will make array for steps data. This is set specially for combined type of challenge where one rule can be steps and one can be distance
                            if (($rule->short_name == 'distance' || $rule->short_name == 'steps') && !$insertedStepsData) {
                                $memberSteps = $participant
                                    ->steps()
                                    ->whereDate(\DB::raw("CONVERT_TZ(log_date, '{$appTimezone}', '{$userTimezone}')"), '>=', $startDateTime)
                                    ->whereDate(\DB::raw("CONVERT_TZ(log_date, '{$appTimezone}', '{$userTimezone}')"), '<=', $endDateTime)
                                    ->get();

                                if (!empty($memberSteps) && $memberSteps->count() > 0) {
                                    foreach ($memberSteps as $memberStep) {
                                        $challengeHistorySteps[$stepIndex]['challenge_id'] = $this->getKey();
                                        $challengeHistorySteps[$stepIndex]['user_id']      = $participant->getKey();
                                        $challengeHistorySteps[$stepIndex]['tracker']      = $memberStep->tracker;
                                        $challengeHistorySteps[$stepIndex]['steps']        = $memberStep->steps;
                                        $challengeHistorySteps[$stepIndex]['distance']     = $memberStep->distance;
                                        $challengeHistorySteps[$stepIndex]['calories']     = $memberStep->calories;
                                        $challengeHistorySteps[$stepIndex]['log_date']     = Carbon::parse($memberStep->log_date)->toDateTimeString();

                                        $challengeHistoryStepsWithPoints[$stepIndex] = $challengeHistorySteps[$stepIndex];

                                        $challengeHistoryStepsWithPoints[$stepIndex]['points'] = (!empty($userWisePoints[$participant->getKey()])) ? $userWisePoints[$participant->getKey()] : 0;

                                        $stepIndex++;
                                    }

                                    $insertedStepsData = true;
                                }
                            } elseif ($rule->short_name == 'meditations') {
                                // get user wise meditations data
                                $memberInspires = $participant
                                    ->completedMeditationTracks()
                                    ->where(\DB::raw("DATE(CONVERT_TZ(user_listened_tracks.created_at, '{$appTimezone}', '{$userTimezone}'))"), '>=', $startDateTime)
                                    ->where(\DB::raw("DATE(CONVERT_TZ(user_listened_tracks.created_at, '{$appTimezone}', '{$userTimezone}'))"), '<=', $endDateTime)
                                    ->get();

                                if (!empty($memberInspires) && $memberInspires->count() > 0) {
                                    foreach ($memberInspires as $memberInspire) {
                                        $challengeHistoryInspires[$inspireIndex]['challenge_id']        = $this->getKey();
                                        $challengeHistoryInspires[$inspireIndex]['user_id']             = $participant->getKey();
                                        $challengeHistoryInspires[$inspireIndex]['meditation_track_id'] = $memberInspire->pivot->meditation_track_id;
                                        $challengeHistoryInspires[$inspireIndex]['duration_listened']   = $memberInspire->pivot->duration_listened;
                                        $challengeHistoryInspires[$inspireIndex]['log_date']            = Carbon::parse($memberInspire->pivot->created_at)->toDateTimeString();

                                        $challengeHistoryInspiresWithPoints[$inspireIndex] = $challengeHistoryInspires[$inspireIndex];

                                        $challengeHistoryInspiresWithPoints[$inspireIndex]['points'] = (!empty($userWisePoints[$participant->getKey()])) ? $userWisePoints[$participant->getKey()] : 0;

                                        $inspireIndex++;
                                    }
                                }
                            } elseif ($rule->short_name == 'exercises' && $rule->model_name == 'Exercise') {
                                // get user wise exercises data
                                $memberExercises = $participant->exercises()
                                    ->where(function ($q) use ($startDateTime, $endDateTime, $userTimezone, $appTimezone) {
                                        $q->whereDate(\DB::raw("CONVERT_TZ(user_exercise.start_date, '{$appTimezone}', '{$userTimezone}')"), '>=', $startDateTime)
                                            ->whereDate(\DB::raw("CONVERT_TZ(user_exercise.start_date, '{$appTimezone}', '{$userTimezone}')"), '<=', $endDateTime);
                                    })
                                    ->whereNull('user_exercise.deleted_at')
                                    ->where('user_exercise.exercise_id', $rule->model_id)
                                    ->get();

                                if (!empty($memberExercises) && $memberExercises->count() > 0) {
                                    foreach ($memberExercises as $key => $memberExercise) {
                                        $challengeHistoryExercises[$exerciseIndex]['challenge_id'] = $this->getKey();

                                        $challengeHistoryExercises[$exerciseIndex]['user_id'] = $participant->getKey();

                                        $challengeHistoryExercises[$exerciseIndex]['exercise_id'] = $memberExercise->pivot->exercise_id;

                                        $challengeHistoryExercises[$exerciseIndex]['tracker'] = $memberExercise->pivot->tracker;

                                        $challengeHistoryExercises[$exerciseIndex]['calories'] = $memberExercise->pivot->calories;

                                        $challengeHistoryExercises[$exerciseIndex]['distance'] = $memberExercise->pivot->distance;

                                        $challengeHistoryExercises[$exerciseIndex]['duration'] = $memberExercise->pivot->duration;

                                        $challengeHistoryExercises[$exerciseIndex]['start_date'] = $memberExercise->pivot->start_date;

                                        $challengeHistoryExercises[$exerciseIndex]['end_date'] = $memberExercise->pivot->end_date;

                                        $challengeHistoryExercisesWithPoints[$exerciseIndex] = $challengeHistoryExercises[$exerciseIndex];

                                        $challengeHistoryExercisesWithPoints[$exerciseIndex]['points'] = (!empty($userWisePoints[$participant->getKey()])) ? $userWisePoints[$participant->getKey()] : 0;

                                        $exerciseIndex++;
                                    }
                                }
                            }
                        }
                    }
                }

                $teamAvgPoints = 0;
                if ($this->challenge_type == "team") {
                    $teamAvgPoints = (!empty($totalParticipant) && !empty($totalTeamPoints)) ? $totalTeamPoints / $totalParticipant : 0;
                } elseif ($this->challenge_type == "company_goal") {
                    $teamAvgPoints = $totalTeamPoints;
                }

                $challengeTeamPoints[$key1]['team_id']      = $teamParticipant->getKey();
                $challengeTeamPoints[$key1]['points']       = $teamAvgPoints;
                $challengeTeamPoints[$key1]['rank']         = 0;
                $teamWisePoints[$teamParticipant->getKey()] = $teamAvgPoints;
            }

            if (!empty($challengeHistoryStepsWithPoints)) {
                $challengeStepOldData = $this->challengeHistorySteps()->get()->toArray();
                if (!empty($challengeStepOldData)) {
                    foreach ($challengeHistoryStepsWithPoints as $value) {
                        $insertDataFlag = true;
                        foreach ($challengeStepOldData as $val) {
                            if ($value['user_id'] == $val['user_id'] && $value['tracker'] == $val['tracker'] && $value['steps'] == $val['steps'] && $value['distance'] == $val['distance'] && $value['calories'] == $val['calories'] && $value['log_date'] == $val['log_date']) {
                                $insertDataFlag = false;
                            }
                        }
                        if ($insertDataFlag) {
                            $challengeHistoryStepsWithPointsDiff[] = $value;
                        }
                    }
                } else {
                    $challengeHistoryStepsWithPointsDiff = $challengeHistoryStepsWithPoints;
                }
            }

            if (!empty($challengeHistoryExercisesWithPoints)) {
                $challengeExercisesOldData = $this->challengeHistoryExercises()->get()->toArray();
                if (!empty($challengeExercisesOldData)) {
                    foreach ($challengeHistoryExercisesWithPoints as $value) {
                        $insertDataFlag = true;
                        foreach ($challengeExercisesOldData as $val) {
                            if ($value['user_id'] == $val['user_id'] && $value['exercise_id'] == $val['exercise_id'] && $value['tracker'] == $val['tracker'] && $value['duration'] == $val['duration'] && $value['distance'] == $val['distance'] && $value['calories'] == $val['calories'] && $value['start_date'] == $val['start_date'] && $value['end_date'] == $val['end_date']) {
                                $insertDataFlag = false;
                            }
                        }
                        if ($insertDataFlag) {
                            $challengeHistoryExercisesWithPointsDiff[] = $value;
                        }
                    }
                } else {
                    $challengeHistoryExercisesWithPointsDiff = $challengeHistoryExercisesWithPoints;
                }
            }

            if (!empty($challengeHistoryInspiresWithPoints)) {
                $challengeInspiresOldData = $this->challengeHistoryInspires()->get()->toArray();
                if (!empty($challengeInspiresOldData)) {
                    foreach ($challengeHistoryInspiresWithPoints as $value) {
                        $insertDataFlag = true;
                        foreach ($challengeInspiresOldData as $val) {
                            if ($value['user_id'] == $val['user_id'] && $value['meditation_track_id'] == $val['meditation_track_id'] && $value['duration_listened'] == $val['duration_listened'] && $value['log_date'] == $val['log_date']) {
                                $insertDataFlag = false;
                            }
                        }
                        if ($insertDataFlag) {
                            $challengeHistoryInspiresWithPointsDiff[] = $value;
                        }
                    }
                } else {
                    $challengeHistoryInspiresWithPointsDiff = $challengeHistoryInspiresWithPoints;
                }
            }

            // delete all freezed data for this challenge
            $this->challengeHistory()->delete();
            $this->challengeHistoryParticipants()->delete();
            $this->challengeHistorySteps()->delete();
            $this->challengeHistoryExercises()->delete();
            $this->challengeHistoryInspires()->delete();
            $this->challengeWiseUserPoints()->delete();
            $this->challengeWiseTeamPoints()->delete();
            $this->challengeHistorySettings()->delete();
            $this->challengeHistoryTeamParticipents()->delete();

            // re-dump all data for this challenge to get final data for completed challenge
            $this->challengeHistory()->create($challengeHistory);
            $this->challengeHistoryParticipants()->createMany($challengeHistoryParticipants);
            $this->challengeHistoryTeamParticipents()->createMany($ChallengeHistoryTeamParticipents);

            if (!empty($challengeHistorySteps)) {
                $this->challengeHistorySteps()->createMany($challengeHistorySteps);
            }

            if (!empty($challengeHistoryExercises)) {
                $this->challengeHistoryExercises()->createMany($challengeHistoryExercises);
            }

            if (!empty($challengeHistoryInspires)) {
                $this->challengeHistoryInspires()->createMany($challengeHistoryInspires);
            }

            if (!empty($challengeHistoryStepsWithPointsDiff)) {
                $this->challengeUserStepsHistory()->createMany($challengeHistoryStepsWithPointsDiff);
            }

            if (!empty($challengeHistoryExercisesWithPointsDiff)) {
                $this->challengeUserExercisesHistory()->createMany($challengeHistoryExercisesWithPointsDiff);
            }

            if (!empty($challengeHistoryInspiresWithPointsDiff)) {
                $this->challengeUserInspireHistory()->createMany($challengeHistoryInspiresWithPointsDiff);
            }

            // get user points in descending order
            uasort($userWisePoints, "getDescendingArray");
            uasort($teamWisePoints, "getDescendingArray");

            if (!empty($challengeTeamPoints) && !empty($teamWisePoints)) {
                $challengeTeamAvgPoints = [];
                foreach ($challengeTeamPoints as $challengeHistoryUserPoint) {
                    $teamRank = $this->getUserRankInChallengeUsingPoints($teamWisePoints, $challengeHistoryUserPoint['team_id']);

                    $challengeHistoryUserPoint['rank'] = $teamRank;

                    array_push($challengeTeamAvgPoints, $challengeHistoryUserPoint);
                }

                $this->challengeWiseTeamPoints()->createMany($challengeTeamAvgPoints);
            }

            if (!empty($challengeHistoryUserPoints) && !empty($userWisePoints)) {
                $challengeUserPoints = [];
                foreach ($challengeHistoryUserPoints as $challengeHistoryUserPoint) {
                    $userRank = $this->getUserRankInChallengeUsingPoints($userWisePoints, $challengeHistoryUserPoint['user_id']);

                    $challengeHistoryUserPoint['rank'] = $userRank;

                    array_push($challengeUserPoints, $challengeHistoryUserPoint);
                }

                $this->challengeWiseUserPoints()->createMany($challengeUserPoints);
            }

            $this->challengeHistorySettings()->createMany($challengeHistorySettings);
        }
    }

    public function sendTeamFinishedNotifications($teamData)
    {
        $title       = trans('notifications.challenge.challenge-finished.title');
        $message     = trans('notifications.challenge.challenge-finished.message');
        $message     = str_replace(["#challenge_name#"], [$this->title], $message);
        $deepLink    = 'zevolife://zevo/challenge/leaderboad/' . $this->id;
        $pushMembers = array();
        if (!empty($teamData) && $teamData->count() > 0) {
            $notification = Notification::create([
                'type'             => 'Auto',
                'creator_id'       => $this->creator_id,
                'company_id'       => $this->company_id,
                'creator_timezone' => $this->timezone,
                'title'            => $title,
                'message'          => $message,
                'push'             => true,
                'scheduled_at'     => now()->toDateTimeString(),
                'deep_link_uri'    => $deepLink,
                'is_mobile'        => config('notification.challenge.finished.is_mobile'),
                'is_portal'        => config('notification.challenge.finished.is_portal'),
                'tag'              => 'challenge',
            ]);

            foreach ($teamData as $value) {
                if (!empty($value->users) && $value->users->count() > 0) {
                    foreach ($value->users as $member) {
                        $companyId = $member->company()->first()->id;
                        $team      = $value;

                        if ($this->challenge_type == 'company_goal' || $this->challenge_type == 'team') {
                            $team->challengeWiseTeamLogData()->updateOrCreate([
                                'team_id'      => $value->getKey(),
                                'user_id'      => $member->getKey(),
                                'challenge_id' => $this->getKey(),
                            ], [
                                'finished_at' => now(\config('app.timezone'))->toDateTimeString(),
                            ]);
                        } else {
                            $team->challengeWiseTeamLogData()->updateOrCreate([
                                'company_id'   => $companyId,
                                'team_id'      => $value->getKey(),
                                'user_id'      => $member->getKey(),
                                'challenge_id' => $this->getKey(),
                            ], [
                                'finished_at' => now(\config('app.timezone'))->toDateTimeString(),
                            ]);
                        }

                        $member->notifications()->attach($notification, ['sent' => true, 'sent_on' => now()->toDateTimeString()]);

                        $userNotification = NotificationSetting::select('flag')
                            ->where(['flag' => 1, 'user_id' => $member->getKey()])
                            ->whereRaw('(`user_notification_settings`.`module` = ? OR `user_notification_settings`.`module` = ?)', ['challenges', 'all'])
                            ->first();

                        if ($userNotification->flag ?? false) {
                            // send notification to all users
                            $pushMembers[] = $member;
                        }
                    }
                }
            }

            if (!empty($pushMembers)) {
                \Notification::send(
                    $pushMembers,
                    new SystemAutoNotification($notification, 'challenge-finished')
                );
            }
        }
    }

    public function getWinnersForTeamChallengeOld($pointCalcRules, $participantTeam, $challengeRulesData)
    {

        $appTimezone = config('app.timezone');

        $winners   = array();
        $teamData  = array();

        if ($this->short_name == 'most' || $this->short_name == 'combined' || $this->short_name == 'combined_most' || $this->short_name == 'first_to_reach') {
            if (!empty($participantTeam) && $participantTeam->count() > 0) {
                foreach ($participantTeam as $teamMembers) {
                    $avgTargetByTeamRule = array();
                    foreach ($challengeRulesData as $rule) {
                        $ruleWiseTotal = 0;
                        if (!empty($teamMembers->users) && $teamMembers->users->count() > 0) {
                            foreach ($teamMembers->users as $participant) {
                                $userTimezone = $participant->timezone;
                                $startDate    = Carbon::parse($this->start_date, $appTimezone)->setTimezone($userTimezone)->toDateString();
                                $endDate      = Carbon::parse($this->end_date, $appTimezone)->setTimezone($userTimezone)->toDateString();

                                if ($rule->short_name == 'distance') {
                                    $userRulePoint = $participant->getDistancePointsInChallenge($pointCalcRules, $startDate, $endDate, $appTimezone, $userTimezone);
                                    $dataDone      = $userRulePoint * $pointCalcRules['distance'];

                                    $ruleWiseTotal += $dataDone;
                                } elseif ($rule->short_name == 'steps') {
                                    $userRulePoint = $participant->getStepsPointsInChallenge($pointCalcRules, $startDate, $endDate, $appTimezone, $userTimezone);
                                    $dataDone      = $userRulePoint * $pointCalcRules['steps'];

                                    $ruleWiseTotal += $dataDone;
                                } elseif ($rule->short_name == 'exercises' && $rule->model_name == 'Exercise') {
                                    $userRulePoint = $participant->getExercisesPointsInChallenge($pointCalcRules, $startDate, $endDate, $appTimezone, $userTimezone, $rule->uom, $rule->model_id);
                                    $dataDone      = ($rule->uom == 'meter') ? $userRulePoint * $pointCalcRules['exercises_distance'] : $userRulePoint * $pointCalcRules['exercises_duration'];

                                    $ruleWiseTotal += $dataDone;
                                } elseif ($rule->short_name == 'meditations') {
                                    $userRulePoint = $participant->getMeditationPointsInChallenge($pointCalcRules, $startDate, $endDate, $appTimezone, $userTimezone, $rule->uom);
                                    $dataDone      = $userRulePoint * $pointCalcRules['meditations'];

                                    $ruleWiseTotal += $dataDone;
                                }
                            }
                        }

                        $avgTargetByTeamRule[] = (!empty($teamMembers->users) && $teamMembers->users->count() > 0) ? $ruleWiseTotal / $teamMembers->users->count() : 0;
                    }

                    if ($this->short_name == 'most' && !empty($avgTargetByTeamRule[0])) {
                        $teamData[$teamMembers->id] = $avgTargetByTeamRule[0];
                    } elseif ($this->short_name == 'first_to_reach') {
                        if ($avgTargetByTeamRule[0] >= $challengeRulesData[0]->target) {
                            $teamData[$teamMembers->id] = $avgTargetByTeamRule[0];
                        }
                    } elseif ($this->short_name == 'combined') {
                        if ($avgTargetByTeamRule[0] >= $challengeRulesData[0]->target && $avgTargetByTeamRule[1] >= $challengeRulesData[1]->target) {
                            $teamData[$teamMembers->id] = array_sum($avgTargetByTeamRule);
                        }
                    } elseif ($this->short_name == 'combined_most' && !empty($avgTargetByTeamRule[0])) {
                        $teamData[$teamMembers->id] = $avgTargetByTeamRule[0];
                    }
                }
            }
            if (!empty($teamData)) {
                arsort($teamData, true); // sorting descending wise of user points

                $winnerIds             = [];
                $pointsOnFirstPosition = array_values($teamData)[0];

                foreach ($teamData as $teamId => $points) {
                    if ($this->short_name == 'most' || $this->short_name == 'first_to_reach' || $this->short_name == 'combined' || $this->short_name == 'combined_most') {
                        if ($points == $pointsOnFirstPosition) {
                            $winnerIds[] = $teamId;
                        }
                    } else {
                        $winnerIds[] = $teamId;
                    }
                }

                $winners = (!empty($winnerIds)) ? Team::find($winnerIds) : [];
            }
        }
        return $winners;
    }

    public function getWinnersForCompanyChallengeOld($pointCalcRules, $participantTeam, $challengeRulesData)
    {

        $appTimezone = config('app.timezone');

        $winners       = array();
        $companyTotal1 = $companyTotal2 = 0;
        if ($this->short_name == 'combined' || $this->short_name == 'combined_most' || $this->short_name == 'first_to_reach') {
            if (!empty($participantTeam) && $participantTeam->count() > 0) {
                foreach ($participantTeam as $teamMembers) {
                    $avgTargetByTeamRule = array();
                    foreach ($challengeRulesData as $rule) {
                        $ruleWiseTotal = 0;
                        if (!empty($teamMembers->users) && $teamMembers->users->count() > 0) {
                            foreach ($teamMembers->users as $participant) {
                                $userTimezone = $participant->timezone;
                                $startDate    = Carbon::parse($this->start_date, $appTimezone)->setTimezone($userTimezone)->toDateString();
                                $endDate      = Carbon::parse($this->end_date, $appTimezone)->setTimezone($userTimezone)->toDateString();

                                if ($rule->short_name == 'distance') {
                                    $userRulePoint = $participant->getDistancePointsInChallenge($pointCalcRules, $startDate, $endDate, $appTimezone, $userTimezone);
                                    $dataDone      = $userRulePoint * $pointCalcRules['distance'];

                                    $ruleWiseTotal += $dataDone;
                                } elseif ($rule->short_name == 'steps') {
                                    $userRulePoint = $participant->getStepsPointsInChallenge($pointCalcRules, $startDate, $endDate, $appTimezone, $userTimezone);
                                    $dataDone      = $userRulePoint * $pointCalcRules['steps'];

                                    $ruleWiseTotal += $dataDone;
                                } elseif ($rule->short_name == 'exercises' && $rule->model_name == 'Exercise') {
                                    $userRulePoint = $participant->getExercisesPointsInChallenge($pointCalcRules, $startDate, $endDate, $appTimezone, $userTimezone, $rule->uom, $rule->model_id);
                                    $dataDone      = ($rule->uom == 'meter') ? $userRulePoint * $pointCalcRules['exercises_distance'] : $userRulePoint * $pointCalcRules['exercises_duration'];

                                    $ruleWiseTotal += $dataDone;
                                } elseif ($rule->short_name == 'meditations') {
                                    $userRulePoint = $participant->getMeditationPointsInChallenge($pointCalcRules, $startDate, $endDate, $appTimezone, $userTimezone, $rule->uom);
                                    $dataDone      = $userRulePoint * $pointCalcRules['meditations'];

                                    $ruleWiseTotal += $dataDone;
                                }
                            }
                        }

                        $avgTargetByTeamRule[] = $ruleWiseTotal;
                    }
                    if (!empty($avgTargetByTeamRule)) {
                        $companyTotal1 += $avgTargetByTeamRule[0];
                        if (!empty($avgTargetByTeamRule[1])) {
                            $companyTotal2 += $avgTargetByTeamRule[1];
                        }
                    }
                }
            }
            if ($this->short_name == 'first_to_reach' && $companyTotal1 >= $challengeRulesData[0]->target) {
                $winners = $participantTeam;
            } elseif ($this->short_name == 'combined' && $companyTotal1 >= $challengeRulesData[0]->target && $companyTotal2 >= $challengeRulesData[1]->target) {
                $winners = $participantTeam;
            }
        } elseif ($this->short_name == 'streak') {
            $userTimezone       = $this->timezone;
            $startDate          = Carbon::parse($this->start_date, $appTimezone)->setTimezone($userTimezone);
            $endDate            = Carbon::parse($this->end_date, $appTimezone)->setTimezone($userTimezone);
            $daysRange          = \createDateRange($startDate, $endDate);
            $targetAchivePerDay = 0;
            foreach ($daysRange as $day) {
                $totalTargetValueByDay = 0;
                foreach ($challengeRulesData as $rule) {
                    if (!empty($participantTeam) && $participantTeam->count() > 0) {
                        foreach ($participantTeam as $teamMembers) {
                            if (!empty($teamMembers->users) && $teamMembers->users->count() > 0) {
                                foreach ($teamMembers->users as $participant) {
                                    if ($rule->short_name == 'distance') {
                                        $count = $participant->getDistance($day->toDateString(), $appTimezone, $userTimezone);
                                    } elseif ($rule->short_name == 'steps') {
                                        $count = $participant->getSteps($day->toDateString(), $appTimezone, $userTimezone);
                                    } elseif ($rule->short_name == 'exercises' && $rule->model_name == 'Exercise') {
                                        $count = $participant->getExercises($day->toDateString(), $appTimezone, $userTimezone, $rule->uom, $rule->model_id);
                                    } elseif ($rule->short_name == 'meditations') {
                                        $count = $participant->getMeditation($day->toDateString(), $appTimezone, $userTimezone, $rule->uom);
                                    }

                                    $totalTargetValueByDay += $count;
                                }
                            }
                        }
                    }
                    if ($totalTargetValueByDay >= $rule->target) {
                        $targetAchivePerDay++;
                    }
                }
            }
            if ($targetAchivePerDay == count($daysRange)) {
                $winners = $participantTeam;
            }
        }
        return $winners;
    }

    public function sendNotificationsToAllTeamWinners($winners)
    {
        $user = auth()->user();
        // Check company plan access
        $checkChallengeAccess = getCompanyPlanAccess($user, 'my-challenges');
        // for team and company goal challenge
        $badgeData = Badge::where("challenge_type_slug", $this->challenge_type)->first();

        if (!empty($winners) && $winners->count() > 0) {
            $title = trans('notifications.challenge.challenge-won.title');
            foreach ($winners as $value) {
                $pushMembers = array();
                $message     = "";
                $level       = 0;
                if ($this->challenge_type == "team") {
                    $challengeWinCountByTeam = DB::table('challenge_wise_user_log')
                        ->join('challenges', 'challenge_wise_user_log.challenge_id', '=', 'challenges.id')
                        ->select(DB::raw("count(challenge_wise_user_log.challenge_id) as challengeWon"))
                        ->where("challenges.challenge_type", "team")
                        ->where("challenge_wise_user_log.is_winner", true)
                        ->where("challenge_wise_user_log.team_id", $value->id)
                        ->groupBy("challenge_wise_user_log.challenge_id")
                        ->get();
                    if ($challengeWinCountByTeam->count() > 0) {
                        $level = $challengeWinCountByTeam->count() + 1;
                    } else {
                        $level = 1;
                    }
                    $title   = trans('notifications.challenge.challenge-won.team_title');
                    $message = trans('notifications.challenge.challenge-won.message.team');
                    $message = str_replace(["#challenge_name#", "#level_no#"], [$this->title, $level], $message);
                } else {
                    $companyDetails = Company::find($this->company_id);
                    $title          = trans('notifications.challenge.challenge-won.company_goal_title');
                    $message        = trans('notifications.challenge.challenge-won.message.company_goal');
                    $message        = str_replace(["#company_name#", "#challenge_name#"], [$companyDetails->name, $this->title], $message);
                    $level          = 0;
                }

                if ($checkChallengeAccess) {
                    $notification = Notification::create([
                        'type'             => 'Auto',
                        'creator_id'       => $this->creator_id,
                        'company_id'       => $this->company_id,
                        'creator_timezone' => $this->timezone,
                        'title'            => $title,
                        'message'          => $message,
                        'push'             => true,
                        'scheduled_at'     => now()->toDateTimeString(),
                        'deep_link_uri'    => $this->deep_link_uri,
                        'is_mobile'        => config('notification.team_company_goal_challenge.won.is_mobile'),
                        'is_portal'        => config('notification.team_company_goal_challenge.won.is_portal'),
                        'tag'              => 'challenge',
                    ]);
                }

                if (!empty($value->users) && $value->users->count() > 0) {
                    foreach ($value->users as $winner) {
                        $badgeInput = [
                            'status'     => "Active",
                            'model_id'   => $this->id,
                            'model_name' => 'challenge',
                            'level'      => $level,
                        ];
                        $badgeData->badgeusers()->attach($winner->id, $badgeInput);

                        if ($checkChallengeAccess) {
                            $winner->notifications()->attach($notification, ['sent' => true, 'sent_on' => now()->toDateTimeString()]);

                            $userNotification = NotificationSetting::select('flag')
                                ->where(['flag' => 1, 'user_id' => $winner->id])
                                ->whereRaw('(`user_notification_settings`.`module` = ? OR `user_notification_settings`.`module` = ?)', ['challenges', 'all'])
                                ->first();

                            if ($userNotification->flag ?? false) {
                                // send notification to all users
                                $pushMembers[] = $winner;
                            }
                        }

                        $value->challengeWiseTeamLogData()->updateOrCreate([
                            'team_id'      => $value->getKey(),
                            'user_id'      => $winner->getKey(),
                            'challenge_id' => $this->getKey(),
                        ], [
                            'is_winner' => true,
                            'won_at'    => now(\config('app.timezone'))->toDateTimeString(),
                        ]);
                    }
                }

                if (!empty($pushMembers) && $checkChallengeAccess) {
                    \Notification::send(
                        $pushMembers,
                        new SystemAutoNotification($notification, 'challenge-won')
                    );
                }
            }
        }
    }

    /**
     * Scope a query to only include popular users.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeTypeWiseChallenge($query, $type)
    {
        return $query->where('challenge_type', $type);
    }

    /**
     * get particepants company data table.
     *
     * @param $payload , $id
     * @return array
     */
    public function getCompanyMembersTableData($payload)
    {
        $challengeHistory = $this->challengeHistory;

        $upcoming = false;
        if (now()->toDateTimeString() < $this->start_date) {
            $upcoming = true;
        }

        if (!empty($challengeHistory) && !$upcoming) {
            $challengeParticipantsWithPoints = $this->challengeWiseCompanyPoints()
                ->join('freezed_challenge_participents', 'freezed_challenge_participents.company_id', '=', 'challenge_wise_company_points.company_id')
                ->leftJoin('companies', 'freezed_challenge_participents.company_id', '=', 'companies.id')
                ->select('freezed_challenge_participents.company_id', 'freezed_challenge_participents.participant_name', 'challenge_wise_company_points.challenge_id', 'challenge_wise_company_points.points', 'challenge_wise_company_points.rank')
                ->where('freezed_challenge_participents.challenge_id', $this->id)
                ->where('challenge_wise_company_points.challenge_id', $this->id)
                ->orderBy('challenge_wise_company_points.rank', 'ASC')
                ->orderBy('challenge_wise_company_points.company_id', 'ASC')
                ->groupBy('challenge_wise_company_points.company_id');

            if (in_array('search', array_keys($payload)) && !empty($payload['search']['value'])) {
                $challengeParticipantsWithPoints->where('companies.name', 'like', '%' . $payload['search']['value'] . '%');
            }

            $challengeParticipantsWithPoints = $challengeParticipantsWithPoints->get();
            if (!empty($payload['exportFrom']) && $payload['exportFrom'] == 'challenge-detail') {
                return array('list' => $challengeParticipantsWithPoints->toArray());
            }
            return DataTables::of($challengeParticipantsWithPoints)
                ->addColumn('logo', function ($record) {
                    $company = Company::find($record->company_id);

                    if (!empty($company->logo)) {
                        return '<div class="table-img table-img-l"><img src=' . $company->logo . '" width="70" /></div>';
                    } else {
                        return '<div class="table-img table-img-l"><img src=' . asset('assets/dist/img/boxed-bg.png') . '" width="70" /></div>';
                    }
                })
                ->addColumn('name', function ($record) {
                    $company = Company::find($record->company_id);
                    return (!empty($company)) ? $company->name : 'Deleted';
                })
                ->addColumn('totalTeams', function ($record) {
                    if ($record) {
                        $totalTeams = \DB::table('freezed_team_challenge_participents')
                            ->where('challenge_id', $this->id)
                            ->where('company_id', $record->company_id)
                            ->distinct('team_id')
                            ->pluck('team_id')
                            ->count();
                    }
                    return (!empty($totalTeams)) ? $totalTeams : 0;
                })
                ->addColumn('totalUsers', function ($record) {
                    if ($record) {
                        $totalUsers = \DB::table('freezed_team_challenge_participents')
                            ->where('challenge_id', $this->id)
                            ->where('company_id', $record->company_id)
                            ->count();
                    }
                    return (!empty($totalUsers)) ? $totalUsers : 0;
                })
                ->addColumn('totalPoints', function ($record) {
                    return number_format((float) $record->points, 1, '.', '');
                })
                ->addColumn('rank', function ($record) {
                    return $record->rank;
                })
                ->rawColumns(['logo'])
                ->make(true);
        } else {
            $list = $this->memberCompanies();

            if (in_array('search', array_keys($payload)) && !empty($payload['search']['value'])) {
                $list->where('companies.name', 'like', '%' . $payload['search']['value'] . '%');
            }

            $list = $list->groupBy('company_id')->get();
            if (!empty($payload['exportFrom']) && $payload['exportFrom'] == 'challenge-detail') {
                return array('challenge_type' => 'upcoming', 'list' => $list->toArray());
            }
            return DataTables::of($list)
                ->addColumn('logo', function ($record) {
                    if (!empty($record->logo)) {
                        return '<div class="table-img table-img-l"><img src=' . $record->logo . '" width="70" /></div>';
                    } else {
                        return '<div class="table-img table-img-l"><img src=' . asset('assets/dist/img/boxed-bg.png') . '" width="70" /></div>';
                    }
                })
                ->addColumn('name', function ($record) {
                    return (!empty($record)) ? $record->name : 'Deleted';
                })
                ->addColumn('totalTeams', function ($record) {
                    if ($record) {
                        $totalTeams = $this->memberTeams()->where('challenge_participants.company_id', $record->id)->pluck('team_id')->count();
                    }
                    return (!empty($totalTeams)) ? $totalTeams : 0;
                })
                ->addColumn('totalUsers', function ($record) {
                    if ($record) {
                        $totalTeams = $this->memberTeams()->where('challenge_participants.company_id', $record->id)->pluck('team_id');
                        $totalUsers = \DB::table('user_team')->whereIn('team_id', $totalTeams)->count();
                    }
                    return (!empty($totalUsers)) ? $totalUsers : 0;
                })
                ->addColumn('totalPoints', function () {
                    return 0;
                })
                ->addColumn('rank', function () {
                    return 0;
                })
                ->rawColumns(['logo'])
                ->make(true);
        }
    }

    public function collectAndDumpInterCompanyChallengeDataInHisroty($pointCalcRules, $participantCompany, $challengeRulesData)
    {

        $challengeHistory             = [];
        $challengeHistoryParticipants = [];
        $challengeHistorySteps        = [];

        $challengeHistoryStepsWithPoints     = [];
        $challengeHistoryStepsWithPointsDiff = [];

        $challengeHistorySettings         = [];
        $challengeHistoryUserPoints       = [];
        $challengeTeamPoints              = [];
        $challengeCompanyPoints           = [];
        $userWisePoints                   = [];
        $teamWisePoints                   = [];
        $companyWisePoints                = [];
        $ChallengeHistoryTeamParticipents = [];

        $appTimezone = config('app.timezone');

        // challenge history data
        $challengeHistory['challenge_id']          = $this->getKey();
        $challengeHistory['creator_id']            = $this->creator_id;
        $challengeHistory['challenge_category_id'] = $this->challenge_category_id;
        $challengeHistory['challenge_type']        = $this->challenge_type;
        $challengeHistory['timezone']              = $this->timezone;
        $challengeHistory['title']                 = $this->title;
        $challengeHistory['description']           = $this->description;
        $challengeHistory['start_date']            = $this->start_date;
        $challengeHistory['end_date']              = $this->end_date;

        if (!empty($pointCalcRules)) {
            $sttIndex = 0;
            foreach ($pointCalcRules as $key => $value) {
                $challengeHistorySettings[$sttIndex]['challenge_id'] = $this->getKey();
                $challengeHistorySettings[$sttIndex]['type']         = $key;
                $challengeHistorySettings[$sttIndex]['value']        = $value;
                $challengeHistorySettings[$sttIndex]['uom']          = 'Count';
                if ($key == 'distance' || $key == 'exercises_distance') {
                    $challengeHistorySettings[$sttIndex]['uom'] = 'Meter';
                } elseif ($key == 'exercises_duration') {
                    $challengeHistorySettings[$sttIndex]['uom'] = 'Minutes';
                }
                $sttIndex++;
            }
        }

        if (!empty($participantCompany) && $participantCompany->count() > 0) {
            $companyTeamMembers = $historyUsers = $stepIndex = $historyTeamUsers = 0;
            foreach ($participantCompany as $Companykey => $Companyvalue) {
                $companyTeamPoints = 0;
                $participantTeam   = $this->memberTeams()->wherePivot('company_id', $Companyvalue->getKey())->get();

                if (!empty($participantTeam) && $participantTeam->count() > 0) {
                    foreach ($participantTeam as $teamParticipant) {
                        $challengeHistoryParticipants[$companyTeamMembers]['challenge_id']     = $this->getKey();
                        $challengeHistoryParticipants[$companyTeamMembers]['team_id']          = $teamParticipant->getKey();
                        $challengeHistoryParticipants[$companyTeamMembers]['company_id']       = $Companyvalue->getKey();
                        $challengeHistoryParticipants[$companyTeamMembers]['participant_name'] = $teamParticipant->name;
                        $totalParticipant                                                      = 0;
                        $totalTeamPoints                                                       = 0;

                        if (!empty($teamParticipant->users) && $teamParticipant->users->count() > 0) {
                            foreach ($teamParticipant->users as $participant) {
                                $ChallengeHistoryTeamParticipents[$historyTeamUsers]['user_id']          = $participant->getKey();
                                $ChallengeHistoryTeamParticipents[$historyTeamUsers]['team_id']          = $participant->teams()->first()->getKey();
                                $ChallengeHistoryTeamParticipents[$historyTeamUsers]['company_id']       = $Companyvalue->getKey();
                                $ChallengeHistoryTeamParticipents[$historyTeamUsers]['participant_name'] = $participant->full_name;
                                $ChallengeHistoryTeamParticipents[$historyTeamUsers]['timezone']         = $participant->timezone;
                                $ChallengeHistoryTeamParticipents[$historyTeamUsers++]['challenge_type'] = $this->challenge_type;

                                $userTimezone = $participant->timezone;

                                $startDateTime = Carbon::parse($this->start_date, $appTimezone)->setTimezone($userTimezone)->toDateString();
                                $endDateTime   = Carbon::parse($this->end_date, $appTimezone)->setTimezone($userTimezone)->toDateString();

                                // get user wise points
                                if ($challengeRulesData->count() > 0) {
                                    $points = $participant->getTotalPointsInChallenge($this, $pointCalcRules, $challengeRulesData);

                                    $challengeHistoryUserPoints[$historyUsers]['user_id']    = $participant->getKey();
                                    $challengeHistoryUserPoints[$historyUsers]['team_id']    = $participant->teams()->first()->getKey();
                                    $challengeHistoryUserPoints[$historyUsers]['company_id'] = $Companyvalue->getKey();
                                    $challengeHistoryUserPoints[$historyUsers]['points']     = $points;
                                    $challengeHistoryUserPoints[$historyUsers]['rank']       = 0;

                                    $userWisePoints[$participant->getKey()] = $points;
                                    $totalTeamPoints += $points;
                                    $totalParticipant++;
                                    $historyUsers++;
                                }

                                $insertedStepsData = false;
                                foreach ($challengeRulesData as $rule) {
                                    // get user wise steps and distance data
                                    // if insertedStepsData == true it will make array for steps data. This is set specially for combined type of challenge where one rule can be steps and one can be distance
                                    if (($rule->short_name == 'distance' || $rule->short_name == 'steps') && !$insertedStepsData) {
                                        $memberSteps = $participant
                                            ->steps()
                                            ->whereDate(\DB::raw("CONVERT_TZ(log_date, '{$appTimezone}', '{$userTimezone}')"), '>=', $startDateTime)
                                            ->whereDate(\DB::raw("CONVERT_TZ(log_date, '{$appTimezone}', '{$userTimezone}')"), '<=', $endDateTime)
                                            ->get();

                                        if (!empty($memberSteps) && $memberSteps->count() > 0) {
                                            foreach ($memberSteps as $key => $memberStep) {
                                                $challengeHistorySteps[$stepIndex]['challenge_id'] = $this->getKey();
                                                $challengeHistorySteps[$stepIndex]['user_id']      = $participant->getKey();
                                                $challengeHistorySteps[$stepIndex]['tracker']      = $memberStep->tracker;
                                                $challengeHistorySteps[$stepIndex]['steps']        = $memberStep->steps;
                                                $challengeHistorySteps[$stepIndex]['distance']     = $memberStep->distance;
                                                $challengeHistorySteps[$stepIndex]['calories']     = $memberStep->calories;
                                                $challengeHistorySteps[$stepIndex]['log_date']     = Carbon::parse($memberStep->log_date)->toDateTimeString();

                                                $challengeHistoryStepsWithPoints[$stepIndex] = $challengeHistorySteps[$stepIndex];

                                                $challengeHistoryStepsWithPoints[$stepIndex]['points'] = (!empty($userWisePoints[$participant->getKey()])) ? $userWisePoints[$participant->getKey()] : 0;

                                                $stepIndex++;
                                            }

                                            $insertedStepsData = true;
                                        }
                                    }
                                }
                            }
                        }

                        $teamAvgPoints = 0;
                        $teamAvgPoints = (!empty($totalParticipant) && !empty($totalTeamPoints)) ? $totalTeamPoints / $totalParticipant : 0;

                        $challengeTeamPoints[$companyTeamMembers]['team_id']    = $teamParticipant->getKey();
                        $challengeTeamPoints[$companyTeamMembers]['company_id'] = $Companyvalue->getKey();
                        $challengeTeamPoints[$companyTeamMembers]['points']     = $teamAvgPoints;
                        $challengeTeamPoints[$companyTeamMembers++]['rank']     = 0;
                        $teamWisePoints[$teamParticipant->getKey()]             = $teamAvgPoints;
                        $companyTeamPoints += $teamAvgPoints;
                    }
                }
                $companyAvgPoints = 0;
                $companyAvgPoints = (!empty($companyTeamPoints) && $participantTeam->count() > 0) ? $companyTeamPoints / $participantTeam->count() : 0;

                $challengeCompanyPoints[$Companykey]['company_id'] = $Companyvalue->getKey();
                $challengeCompanyPoints[$Companykey]['points']     = $companyAvgPoints;
                $challengeCompanyPoints[$Companykey]['rank']       = 0;
                $companyWisePoints[$Companyvalue->getKey()]        = $companyAvgPoints;
            }

            if (!empty($challengeHistoryStepsWithPoints)) {
                $challengeStepOldData = $this->challengeHistorySteps()->get()->toArray();
                if (!empty($challengeStepOldData)) {
                    foreach ($challengeHistoryStepsWithPoints as $key => $value) {
                        $insertDataFlag = true;
                        foreach ($challengeStepOldData as $val) {
                            if ($value['user_id'] == $val['user_id'] && $value['tracker'] == $val['tracker'] && $value['steps'] == $val['steps'] && $value['distance'] == $val['distance'] && $value['calories'] == $val['calories'] && $value['log_date'] == $val['log_date']) {
                                $insertDataFlag = false;
                            }
                        }
                        if ($insertDataFlag) {
                            $challengeHistoryStepsWithPointsDiff[] = $value;
                        }
                    }
                } else {
                    $challengeHistoryStepsWithPointsDiff = $challengeHistoryStepsWithPoints;
                }
            }

            // delete all freezed data for this challenge
            $this->challengeHistory()->delete();
            $this->challengeHistoryParticipants()->delete();
            $this->challengeHistorySteps()->delete();
            $this->challengeWiseUserPoints()->delete();
            $this->challengeWiseTeamPoints()->delete();
            $this->ChallengeWiseCompanyPoints()->delete();
            $this->challengeHistorySettings()->delete();
            $this->challengeHistoryTeamParticipents()->delete();

            // re-dump all data for this challenge to get final data for completed challenge
            $this->challengeHistory()->create($challengeHistory);
            $this->challengeHistoryParticipants()->createMany($challengeHistoryParticipants);
            $this->challengeHistoryTeamParticipents()->createMany($ChallengeHistoryTeamParticipents);

            if (!empty($challengeHistorySteps)) {
                $this->challengeHistorySteps()->createMany($challengeHistorySteps);
            }

            if (!empty($challengeHistoryStepsWithPointsDiff)) {
                $this->challengeUserStepsHistory()->createMany($challengeHistoryStepsWithPointsDiff);
            }

            // get user points in descending order
            uasort($userWisePoints, "getDescendingArray");
            uasort($teamWisePoints, "getDescendingArray");
            uasort($companyWisePoints, "getDescendingArray");

            if (!empty($challengeCompanyPoints) && !empty($companyWisePoints)) {
                $challengeCompAvgPoints = [];
                foreach ($challengeCompanyPoints as $challengeHistoryComPoint) {
                    $teamRank = $this->getUserRankInChallengeUsingPoints($companyWisePoints, $challengeHistoryComPoint['company_id']);

                    $challengeHistoryComPoint['rank'] = $teamRank;

                    array_push($challengeCompAvgPoints, $challengeHistoryComPoint);
                }

                $this->ChallengeWiseCompanyPoints()->createMany($challengeCompAvgPoints);
            }

            if (!empty($challengeTeamPoints) && !empty($teamWisePoints)) {
                $challengeTeamAvgPoints = [];
                foreach ($challengeTeamPoints as $challengeHistoryUserPoint) {
                    $teamRank = $this->getUserRankInChallengeUsingPoints($teamWisePoints, $challengeHistoryUserPoint['team_id']);

                    $challengeHistoryUserPoint['rank'] = $teamRank;

                    array_push($challengeTeamAvgPoints, $challengeHistoryUserPoint);
                }

                $this->challengeWiseTeamPoints()->createMany($challengeTeamAvgPoints);
            }

            if (!empty($challengeHistoryUserPoints) && !empty($userWisePoints)) {
                $challengeUserPoints = [];
                foreach ($challengeHistoryUserPoints as $challengeHistoryUserPoint) {
                    $userRank = $this->getUserRankInChallengeUsingPoints($userWisePoints, $challengeHistoryUserPoint['user_id']);

                    $challengeHistoryUserPoint['rank'] = $userRank;

                    array_push($challengeUserPoints, $challengeHistoryUserPoint);
                }

                $this->challengeWiseUserPoints()->createMany($challengeUserPoints);
            }

            $this->challengeHistorySettings()->createMany($challengeHistorySettings);
        }
    }

    public function getWinnersForInterCompanyChallengeOld($pointCalcRules, $participantCompany, $challengeRulesData)
    {

        $appTimezone = config('app.timezone');

        $winners     = array();
        $companyData = array();

        if ($this->short_name == 'most' || $this->short_name == 'first_to_reach') {
            if (!empty($participantCompany) && $participantCompany->count() > 0) {
                foreach ($participantCompany as $Companyvalue) {
                    $participantTeam           = $this->memberTeams()->wherePivot('company_id', $Companyvalue->getKey())->get();
                    $totalAvgTargetAllTeamRule = array();
                    if (!empty($participantTeam) && $participantTeam->count() > 0) {
                        foreach ($participantTeam as $teamMembers) {
                            foreach ($challengeRulesData as $ruleKey => $rule) {
                                $totalTeamAvg  = 0;
                                $ruleWiseTotal = 0;
                                if (!empty($teamMembers->users) && $teamMembers->users->count() > 0) {
                                    foreach ($teamMembers->users as $participant) {
                                        $userTimezone = $participant->timezone;
                                        $startDate    = Carbon::parse($this->start_date, $appTimezone)->setTimezone($userTimezone)->toDateString();
                                        $endDate      = Carbon::parse($this->end_date, $appTimezone)->setTimezone($userTimezone)->toDateString();

                                        if ($rule->short_name == 'distance') {
                                            $userRulePoint = $participant->getDistancePointsInChallenge($pointCalcRules, $startDate, $endDate, $appTimezone, $userTimezone);
                                            $dataDone      = $userRulePoint * $pointCalcRules['distance'];

                                            $ruleWiseTotal += $dataDone;
                                        } elseif ($rule->short_name == 'steps') {
                                            $userRulePoint = $participant->getStepsPointsInChallenge($pointCalcRules, $startDate, $endDate, $appTimezone, $userTimezone);
                                            $dataDone      = $userRulePoint * $pointCalcRules['steps'];
                                            $ruleWiseTotal += $dataDone;
                                        }
                                    }
                                }

                                $totalTeamAvg = (!empty($teamMembers->users) && $teamMembers->users->count() > 0) ? $ruleWiseTotal / $teamMembers->users->count() : 0;

                                if (!isset($totalAvgTargetAllTeamRule[$ruleKey])) {
                                    $totalAvgTargetAllTeamRule[$ruleKey] = $totalTeamAvg;
                                } else {
                                    $totalAvgTargetAllTeamRule[$ruleKey] = $totalAvgTargetAllTeamRule[$ruleKey] + $totalTeamAvg;
                                }
                            }
                        }
                    }
                    if ($this->short_name == 'most' && !empty($totalAvgTargetAllTeamRule[0])) {
                        $companyAvg                           = (!empty($totalAvgTargetAllTeamRule[0]) && $participantTeam->count() > 0) ? $totalAvgTargetAllTeamRule[0] / $participantTeam->count() : 0;
                        $companyData[$Companyvalue->getKey()] = $companyAvg;
                    } elseif ($this->short_name == 'first_to_reach' && !empty($totalAvgTargetAllTeamRule[0])) {
                        $companyAvg = (!empty($totalAvgTargetAllTeamRule[0]) && $participantTeam->count() > 0) ? $totalAvgTargetAllTeamRule[0] / $participantTeam->count() : 0;
                        if ($companyAvg >= $challengeRulesData[0]->target) {
                            $companyData[$Companyvalue->getKey()] = $companyAvg;
                        }
                    }
                }
            }
            if (!empty($companyData)) {
                arsort($companyData, true); // sorting descending wise of user points

                $winnerIds             = [];
                $pointsOnFirstPosition = array_values($companyData)[0];

                foreach ($companyData as $compId => $points) {
                    if ($this->short_name == 'most' || $this->short_name == 'first_to_reach') {
                        if ($points == $pointsOnFirstPosition) {
                            $winnerIds[] = $compId;
                        }
                    } else {
                        $winnerIds[] = $compId;
                    }
                }

                $winners = (!empty($winnerIds)) ? Company::find($winnerIds) : [];
            }
        }
        return $winners;
    }

    public function sendNotificationsToAllInterCompanyWinners($winners)
    {
        $user = auth()->user();
        // Check company plan access
        $checkChallengeAccess = getCompanyPlanAccess($user, 'my-challenges');
        $badgeData            = Badge::where("challenge_type_slug", "inter_company")->first();
        $pushMembers          = array();
        if (!empty($winners) && $winners->count() > 0) {
            $title   = trans('notifications.challenge.challenge-won.intercompany_title');
            $message = trans('notifications.challenge.challenge-won.message.inter_company.company_level');
            $message = str_replace(["#challenge_name#"], [$this->title], $message);

            if ($checkChallengeAccess) {
                $notification = Notification::create([
                    'type'             => 'Auto',
                    'creator_id'       => $this->creator_id,
                    'company_id'       => $this->company_id,
                    'creator_timezone' => $this->timezone,
                    'title'            => $title,
                    'message'          => $message,
                    'push'             => true,
                    'scheduled_at'     => now()->toDateTimeString(),
                    'deep_link_uri'    => $this->deep_link_uri,
                    'is_mobile'        => config('notification.intercompany_challenge.won.is_mobile'),
                    'is_portal'        => config('notification.intercompany_challenge.won.is_portal'),
                    'tag'              => 'challenge',
                ]);
            }

            foreach ($winners as $compValue) {
                $topScore = $this->challengeWiseTeamPoints()
                    ->select("challenge_wise_team_ponits.team_id", DB::raw("max(points) as max"))
                    ->where("challenge_wise_team_ponits.points", ">", 0)
                    ->where("challenge_wise_team_ponits.company_id", $compValue->getKey())
                    ->groupBy("challenge_wise_team_ponits.team_id")
                    ->orderBy("max", 'DESC')
                    ->pluck('max')
                    ->first();

                $topScorerTeam = [];
                if ($topScore > 0) {
                    $topScorerTeam = $this->challengeWiseTeamPoints()
                        ->select("challenge_wise_team_ponits.team_id")
                        ->where("challenge_wise_team_ponits.company_id", $compValue->getKey())
                        ->where("challenge_wise_team_ponits.points", $topScore)
                        ->groupBy("challenge_wise_team_ponits.team_id")
                        ->get()
                        ->pluck("team_id")
                        ->toArray();
                }

                $participantTeam = $this->memberTeams()->wherePivot('company_id', $compValue->getKey())->get();
                if (!empty($participantTeam) && $participantTeam->count() > 0) {
                    foreach ($participantTeam as $value) {
                        if (!empty($value->users) && $value->users->count() > 0) {
                            foreach ($value->users as $winner) {
                                $badgeInput = [
                                    'status'     => "Active",
                                    'model_id'   => $this->id,
                                    'model_name' => 'challenge',
                                ];
                                $badgeData->badgeusers()->attach($winner->id, $badgeInput);

                                if ($checkChallengeAccess) {
                                    $winner->notifications()->attach($notification, ['sent' => true, 'sent_on' => now()->toDateTimeString()]);

                                    $userNotification = NotificationSetting::select('flag')
                                        ->where(['flag' => 1, 'user_id' => $winner->id])
                                        ->whereRaw('(`user_notification_settings`.`module` = ? OR `user_notification_settings`.`module` = ?)', ['challenges', 'all'])
                                        ->first();
                                    $sendPush = ($userNotification->flag ?? false);

                                    if ($sendPush) {
                                        // send notification to all users
                                        $pushMembers[] = $winner;
                                    }
                                }

                                $value->challengeWiseTeamLogData()->updateOrCreate([
                                    'company_id'   => $compValue->getKey(),
                                    'team_id'      => $value->getKey(),
                                    'user_id'      => $winner->getKey(),
                                    'challenge_id' => $this->getKey(),
                                ], [
                                    'is_winner' => true,
                                    'won_at'    => now(\config('app.timezone'))->toDateTimeString(),
                                ]);

                                if (in_array($value->id, $topScorerTeam)) {
                                    $teamAlertNotificationTitle   = trans('notifications.challenge.challenge-won.intercompany_title');
                                    $teamAlertNotificationMessage = trans('notifications.challenge.challenge-won.message.inter_company.team_level');
                                    $teamAlertNotificationMessage = str_replace(["#user_name#", "#company_name#", "#challenge_name#"], [$winner->full_name, $compValue->name, $this->title], $teamAlertNotificationMessage);

                                    if ($checkChallengeAccess) {
                                        $teamAlertNotification = Notification::create([
                                            'type'             => 'Auto',
                                            'creator_id'       => $this->creator_id,
                                            'company_id'       => $this->company_id,
                                            'creator_timezone' => $this->timezone,
                                            'title'            => $teamAlertNotificationTitle,
                                            'message'          => $teamAlertNotificationMessage,
                                            'push'             => true,
                                            'scheduled_at'     => now()->toDateTimeString(),
                                            'deep_link_uri'    => $this->deep_link_uri,
                                            'is_mobile'        => config('notification.individual_challenge.won.is_mobile'),
                                            'is_portal'        => config('notification.individual_challenge.won.is_portal'),
                                            'tag'              => 'challenge',
                                        ]);

                                        $winner->notifications()->attach($teamAlertNotification, ['sent' => true, 'sent_on' => now()->toDateTimeString()]);

                                        if ($sendPush) {
                                            \Notification::send(
                                                $winner,
                                                new SystemAutoNotification($teamAlertNotification, 'challenge-won')
                                            );
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }

            if (!empty($pushMembers) && $checkChallengeAccess) {
                \Notification::send(
                    $pushMembers,
                    new SystemAutoNotification($notification, 'challenge-won')
                );
            }
        }
    }

    public function sendNotificationToLooser($loosingUsers)
    {
        $user = auth()->user();
        // Check company plan access
        $checkChallengeAccess = getCompanyPlanAccess($user, 'my-challenges');
        $title                = trans('notifications.challenge.challenge-loss.title');
        $message              = trans('notifications.challenge.challenge-loss.message.individual');
        $message              = str_replace(["#challenge_name#"], [$this->title], $message);
        if ($checkChallengeAccess) {
            foreach ($loosingUsers as $looser) {
                $notification = Notification::create([
                    'type'             => 'Auto',
                    'creator_id'       => $this->creator_id,
                    'company_id'       => $this->company_id,
                    'creator_timezone' => $this->timezone,
                    'title'            => $title,
                    'message'          => __($message, ['first_name' => $looser->first_name]),
                    'push'             => true,
                    'scheduled_at'     => now()->toDateTimeString(),
                    'deep_link_uri'    => $this->deep_link_uri,
                    'is_mobile'        => config('notification.individual_challenge.won.is_mobile'),
                    'is_portal'        => config('notification.individual_challenge.won.is_portal'),
                    'tag'              => 'challenge',
                ]);

                $looser->notifications()->attach($notification, ['sent' => true, 'sent_on' => now()->toDateTimeString()]);

                $userNotification = NotificationSetting::select('flag')
                    ->where(['flag' => 1, 'user_id' => $looser->getKey()])
                    ->whereRaw('(`user_notification_settings`.`module` = ? OR `user_notification_settings`.`module` = ?)', ['challenges', 'all'])
                    ->first();

                if ($userNotification->flag ?? false) {
                    \Notification::send(
                        $looser,
                        new SystemAutoNotification($notification, 'challenge-loss')
                    );
                }
            }
        }
    }

    public function sendNotificationToLooserTeamsUser($loosingTeams)
    {
        // Check company plan access
        $title   = trans('notifications.challenge.challenge-loss.title');
        $message = "";
        if ($this->challenge_type == "team") {
            $message = trans('notifications.challenge.challenge-loss.message.team');
            $message = str_replace(["#challenge_name#"], [$this->title], $message);
        } elseif ($this->challenge_type == "company_goal") {
            $companyDetails = Company::find($this->company_id);
            $message        = trans('notifications.challenge.challenge-loss.message.company_goal');
            $message        = str_replace(["#company_name#", "#challenge_name#"], [$companyDetails->name, $this->title], $message);
        } else {
            $message = trans('notifications.challenge.challenge-loss.message.inter_company');
            $message = str_replace(["#challenge_name#"], [$this->title], $message);
        }

        if (!empty($loosingTeams) && $loosingTeams->count() > 0) {
            foreach ($loosingTeams as $value) {
                if (!empty($value->users) && $value->users->count() > 0) {
                    foreach ($value->users as $member) {
                        $notification = Notification::create([
                            'type'             => 'Auto',
                            'creator_id'       => $this->creator_id,
                            'company_id'       => $this->company_id,
                            'creator_timezone' => $this->timezone,
                            'title'            => $title,
                            'message'          => __($message, [
                                'first_name' => $member->first_name,
                            ]),
                            'push'             => true,
                            'scheduled_at'     => now()->toDateTimeString(),
                            'deep_link_uri'    => $this->deep_link_uri,
                            'is_mobile'        => config('notification.team_company_goal_challenge.won.is_mobile'),
                            'is_portal'        => config('notification.team_company_goal_challenge.won.is_portal'),
                            'tag'              => 'challenge',
                        ]);

                        $member->notifications()->attach($notification, ['sent' => true, 'sent_on' => now()->toDateTimeString()]);

                        $userNotification = NotificationSetting::select('flag')
                            ->where(['flag' => 1, 'user_id' => $member->id])
                            ->whereRaw('(`user_notification_settings`.`module` = ? OR `user_notification_settings`.`module` = ?)', ['challenges', 'all'])
                            ->first();

                        if ($userNotification->flag ?? false) {
                            \Notification::send(
                                $member,
                                new SystemAutoNotification($notification, 'challenge-loss')
                            );
                        }
                    }
                }
            }
        }
    }

    public function getICReportTableData($payload)
    {
        $list      = $this->getICReportRecordList($payload);
        $datatable = DataTables::of($list)
            ->addColumn('points', function ($record) {
                return round($record->points, 1);
            });

        if ($datatable == "company") {
            $datatable
                ->addColumn('total_teams', function ($record) {
                    return numberFormatShort($record->total_teams);
                })
                ->addColumn('total_users', function ($record) {
                    return numberFormatShort($record->total_users);
                });
        }

        return $datatable->rawColumns([])->make(true);
    }

    /**
     * get record list for data table list.
     *
     * @param payload
     * @return roleList
     */

    public function getICReportRecordList($payload)
    {
        try {
            $type        = ($payload['type'] ?? 'company');
            $challengeId = $payload['challenge'];
            $companyId   = ($payload['company'] ?? 0);
            $challenge   = self::find($challengeId);

            if (!$challenge->finished && !$challenge->cancelled) {
                $challengeExecuteStatus = $challenge->checkExecutionRequired();
                if ($challengeExecuteStatus) {
                    $pointCalcRules = config('zevolifesettings.default_limits');
                    $procedureData  = [
                        config('app.timezone'),
                        $challenge->id,
                        $pointCalcRules['steps'],
                        $pointCalcRules['distance'],
                        $pointCalcRules['exercises_distance'],
                        $pointCalcRules['exercises_duration'],
                        $pointCalcRules['meditations'],
                    ];
                    DB::select('CALL sp_inter_comp_challenge_pointcalculation(?, ?, ?, ?, ?, ?, ?)', $procedureData);
                }
            }

            if ($type == "company") {
                $query = DB::table('challenge_wise_company_points')
                    ->join('companies', 'companies.id', '=', 'challenge_wise_company_points.company_id')
                    ->where('challenge_wise_company_points.challenge_id', $challengeId)
                    ->where(DB::raw("round(challenge_wise_company_points.points, 1)"), '>', 0)
                    ->select(
                        'challenge_wise_company_points.rank',
                        'challenge_wise_company_points.points',
                        'companies.name AS company_name',
                        DB::raw("(SELECT COUNT(DISTINCT team_id) FROM challenge_wise_user_ponits where challenge_id = challenge_wise_company_points.challenge_id and company_id = challenge_wise_company_points.company_id) AS total_teams"),
                        DB::raw("(SELECT COUNT(DISTINCT user_id) FROM challenge_wise_user_ponits where challenge_id = challenge_wise_company_points.challenge_id and company_id = challenge_wise_company_points.company_id) AS total_users")
                    );

                if (!empty($companyId)) {
                    $query->where('challenge_wise_company_points.company_id', $companyId);
                }
            } else {
                $query = DB::table('challenge_wise_team_ponits')
                    ->join('companies', 'companies.id', '=', 'challenge_wise_team_ponits.company_id')
                    ->join('teams', 'teams.id', '=', 'challenge_wise_team_ponits.team_id')
                    ->where('challenge_wise_team_ponits.challenge_id', $challengeId)
                    ->where(DB::raw("round(challenge_wise_team_ponits.points, 1)"), '>', 0)
                    ->select(
                        'challenge_wise_team_ponits.rank',
                        'teams.name AS team_name',
                        'companies.name AS company_name',
                        'challenge_wise_team_ponits.points'
                    );

                if (!empty($companyId)) {
                    $query->where('challenge_wise_team_ponits.company_id', $companyId);
                }
            }

            return $query->get();
        } catch (\Illuminate\Database\QueryException $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong_try_again'),
                'status' => 0,
            ];
            return response()->json($messageData);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong_try_again'),
                'status' => 0,
            ];
            return response()->json($messageData);
        }
    }

    public function getICCompanies($payload)
    {
        $challengeId = ($payload['challengeId'] ?? 0);

        return DB::table('challenge_wise_company_points')
            ->join('companies', 'companies.id', '=', 'challenge_wise_company_points.company_id')
            ->where(['challenge_wise_company_points.challenge_id' => $challengeId])
            ->select('companies.name', 'companies.id')
            ->pluck('companies.name', 'companies.id');
    }

    public function getWinnersForInterCompanyChallenge($pointCalcRules, $participantCompany, $challengeRulesData)
    {

        $appTimezone = config('app.timezone');

        $winners     = array();
        $companyData = array();

        if ($this->short_name == 'most' || $this->short_name == 'first_to_reach') {
            if (!empty($participantCompany) && $participantCompany->count() > 0) {
                foreach ($participantCompany as $Companyvalue) {
                    $participantTeam           = $this->memberTeams()->wherePivot('company_id', $Companyvalue->getKey())->get();
                    $totalAvgTargetAllTeamRule = array();
                    if (!empty($participantTeam) && $participantTeam->count() > 0) {
                        foreach ($participantTeam as $teamMembers) {
                            foreach ($challengeRulesData as $ruleKey => $rule) {
                                $totalTeamAvg  = 0;
                                $ruleWiseTotal = 0;
                                if (!empty($teamMembers->users) && $teamMembers->users->count() > 0) {
                                    $ParticipatedUserId = array();
                                    $ParticipatedUserId = $teamMembers->users->pluck('id')->toArray();
                                    if ($rule->short_name == 'distance') {
                                        $ruleWiseTotal = DB::table('user_step')
                                            ->join('users', 'users.id', '=', 'user_step.user_id')
                                            ->where(\DB::raw("DATE(CONVERT_TZ(user_step.log_date, '{$appTimezone}',users.timezone))"), '>=', \DB::raw("DATE(CONVERT_TZ('{$this->start_date}', '{$appTimezone}',users.timezone))"))
                                            ->where(\DB::raw("DATE(CONVERT_TZ(user_step.log_date, '{$appTimezone}',users.timezone))"), '<=', \DB::raw("DATE(CONVERT_TZ('{$this->end_date}', '{$appTimezone}',users.timezone))"))
                                            ->whereIn("user_step.user_id", $ParticipatedUserId)
                                            ->sum('user_step.distance');
                                    } elseif ($rule->short_name == 'steps') {
                                        $ruleWiseTotal = DB::table('user_step')
                                            ->join('users', 'users.id', '=', 'user_step.user_id')
                                            ->where(\DB::raw("DATE(CONVERT_TZ(user_step.log_date, '{$appTimezone}',users.timezone))"), '>=', \DB::raw("DATE(CONVERT_TZ('{$this->start_date}', '{$appTimezone}',users.timezone))"))
                                            ->where(\DB::raw("DATE(CONVERT_TZ(user_step.log_date, '{$appTimezone}',users.timezone))"), '<=', \DB::raw("DATE(CONVERT_TZ('{$this->end_date}', '{$appTimezone}',users.timezone))"))
                                            ->whereIn("user_step.user_id", $ParticipatedUserId)
                                            ->sum('user_step.steps');
                                    }
                                }

                                $totalTeamAvg = (!empty($teamMembers->users) && $teamMembers->users->count() > 0) ? $ruleWiseTotal / $teamMembers->users->count() : 0;

                                if (!isset($totalAvgTargetAllTeamRule[$ruleKey])) {
                                    $totalAvgTargetAllTeamRule[$ruleKey] = $totalTeamAvg;
                                } else {
                                    $totalAvgTargetAllTeamRule[$ruleKey] = $totalAvgTargetAllTeamRule[$ruleKey] + $totalTeamAvg;
                                }
                            }
                        }
                    }
                    if ($this->short_name == 'most' && !empty($totalAvgTargetAllTeamRule[0])) {
                        $companyAvg                           = (!empty($totalAvgTargetAllTeamRule[0]) && $participantTeam->count() > 0) ? $totalAvgTargetAllTeamRule[0] / $participantTeam->count() : 0;
                        $companyData[$Companyvalue->getKey()] = $companyAvg;
                    } elseif ($this->short_name == 'first_to_reach' && !empty($totalAvgTargetAllTeamRule[0])) {
                        $companyAvg = (!empty($totalAvgTargetAllTeamRule[0]) && $participantTeam->count() > 0) ? $totalAvgTargetAllTeamRule[0] / $participantTeam->count() : 0;
                        if ($companyAvg >= $challengeRulesData[0]->target) {
                            $companyData[$Companyvalue->getKey()] = $companyAvg;
                        }
                    }
                }
            }
            if (!empty($companyData)) {
                arsort($companyData, true); // sorting descending wise of user points

                $winnerIds             = [];
                $pointsOnFirstPosition = array_values($companyData)[0];

                foreach ($companyData as $compId => $points) {
                    if ($this->short_name == 'most' || $this->short_name == 'first_to_reach') {
                        if ($points == $pointsOnFirstPosition) {
                            $winnerIds[] = $compId;
                        }
                    } else {
                        $winnerIds[] = $compId;
                    }
                }

                $winners = (!empty($winnerIds)) ? Company::find($winnerIds) : [];
            }
        }
        return $winners;
    }

    public function getWinnersForTeamChallenge($pointCalcRules, $participantTeam, $challengeRulesData)
    {

        $appTimezone = config('app.timezone');

        $winners   = array();
        $teamData  = array();

        if ($this->short_name == 'most' || $this->short_name == 'combined' || $this->short_name == 'combined_most' || $this->short_name == 'first_to_reach') {
            if (!empty($participantTeam) && $participantTeam->count() > 0) {
                foreach ($participantTeam as $teamMembers) {
                    $avgTargetByTeamRule = array();
                    foreach ($challengeRulesData as $rule) {
                        $ruleWiseTotal = 0;
                        if (!empty($teamMembers->users) && $teamMembers->users->count() > 0) {
                            $ParticipatedUserId = array();
                            $ParticipatedUserId = $teamMembers->users->pluck('id')->toArray();

                            if ($rule->short_name == 'distance') {
                                $ruleWiseTotal = DB::table('user_step')
                                    ->join('users', 'users.id', '=', 'user_step.user_id')
                                    ->where(\DB::raw("DATE(CONVERT_TZ(user_step.log_date, '{$appTimezone}',users.timezone))"), '>=', \DB::raw("DATE(CONVERT_TZ('{$this->start_date}', '{$appTimezone}',users.timezone))"))
                                    ->where(\DB::raw("DATE(CONVERT_TZ(user_step.log_date, '{$appTimezone}',users.timezone))"), '<=', \DB::raw("DATE(CONVERT_TZ('{$this->end_date}', '{$appTimezone}',users.timezone))"))
                                    ->whereIn("user_step.user_id", $ParticipatedUserId)
                                    ->sum('user_step.distance');
                            } elseif ($rule->short_name == 'steps') {
                                $ruleWiseTotal = DB::table('user_step')
                                    ->join('users', 'users.id', '=', 'user_step.user_id')
                                    ->where(\DB::raw("DATE(CONVERT_TZ(user_step.log_date, '{$appTimezone}',users.timezone))"), '>=', \DB::raw("DATE(CONVERT_TZ('{$this->start_date}', '{$appTimezone}',users.timezone))"))
                                    ->where(\DB::raw("DATE(CONVERT_TZ(user_step.log_date, '{$appTimezone}',users.timezone))"), '<=', \DB::raw("DATE(CONVERT_TZ('{$this->end_date}', '{$appTimezone}',users.timezone))"))
                                    ->whereIn("user_step.user_id", $ParticipatedUserId)
                                    ->sum('user_step.steps');
                            } elseif ($rule->short_name == 'exercises' && $rule->model_name == 'Exercise') {
                                $column = 'duration';
                                if ($rule->uom == 'meter') {
                                    $column = 'distance';
                                }

                                $ruleWiseTotal = DB::table('user_exercise')
                                    ->join('users', 'users.id', '=', 'user_exercise.user_id')
                                    ->where(\DB::raw("DATE(CONVERT_TZ(user_exercise.start_date, '{$appTimezone}',users.timezone))"), '>=', \DB::raw("DATE(CONVERT_TZ('{$this->start_date}', '{$appTimezone}',users.timezone))"))
                                    ->where(\DB::raw("DATE(CONVERT_TZ(user_exercise.start_date, '{$appTimezone}',users.timezone))"), '<=', \DB::raw("DATE(CONVERT_TZ('{$this->end_date}', '{$appTimezone}',users.timezone))"))
                                    ->whereIn("user_exercise.user_id", $ParticipatedUserId)
                                    ->where('user_exercise.exercise_id', $rule->model_id)
                                    ->whereNull('user_exercise.deleted_at')
                                    ->sum($column);

                                if ($column == 'duration') {
                                    $ruleWiseTotal = ($ruleWiseTotal / 60);
                                }
                            } elseif ($rule->short_name == 'meditations') {
                                $ruleWiseTotal = DB::table('user_listened_tracks')
                                    ->join('users', 'users.id', '=', 'user_listened_tracks.user_id')
                                    ->where(\DB::raw("DATE(CONVERT_TZ(user_listened_tracks.created_at, '{$appTimezone}',users.timezone))"), '>=', \DB::raw("DATE(CONVERT_TZ('{$this->start_date}', '{$appTimezone}',users.timezone))"))
                                    ->where(\DB::raw("DATE(CONVERT_TZ(user_listened_tracks.created_at, '{$appTimezone}',users.timezone))"), '<=', \DB::raw("DATE(CONVERT_TZ('{$this->end_date}', '{$appTimezone}',users.timezone))"))
                                    ->whereIn("user_listened_tracks.user_id", $ParticipatedUserId)
                                    ->count();
                            }
                        }

                        $avgTargetByTeamRule[] = (!empty($teamMembers->users) && $teamMembers->users->count() > 0) ? $ruleWiseTotal / $teamMembers->users->count() : 0;
                    }

                    if ($this->short_name == 'most' && !empty($avgTargetByTeamRule[0])) {
                        $teamData[$teamMembers->id] = $avgTargetByTeamRule[0];
                    } elseif ($this->short_name == 'first_to_reach') {
                        if ($avgTargetByTeamRule[0] >= $challengeRulesData[0]->target) {
                            $teamData[$teamMembers->id] = $avgTargetByTeamRule[0];
                        }
                    } elseif ($this->short_name == 'combined') {
                        if ($avgTargetByTeamRule[0] >= $challengeRulesData[0]->target && $avgTargetByTeamRule[1] >= $challengeRulesData[1]->target) {
                            $teamData[$teamMembers->id] = array_sum($avgTargetByTeamRule);
                        }
                    } elseif ($this->short_name == 'combined_most' && !empty($avgTargetByTeamRule[0])) {
                        $teamData[$teamMembers->id] = $avgTargetByTeamRule[0];
                    }
                }
            }
            if (!empty($teamData)) {
                arsort($teamData, true); // sorting descending wise of user points

                $winnerIds             = [];
                $pointsOnFirstPosition = array_values($teamData)[0];

                foreach ($teamData as $teamId => $points) {
                    if ($this->short_name == 'most' || $this->short_name == 'first_to_reach' || $this->short_name == 'combined' || $this->short_name == 'combined_most') {
                        if ($points == $pointsOnFirstPosition) {
                            $winnerIds[] = $teamId;
                        }
                    } else {
                        $winnerIds[] = $teamId;
                    }
                }

                $winners = (!empty($winnerIds)) ? Team::find($winnerIds) : [];
            }
        }
        return $winners;
    }

    public function getWinnersForCompanyChallenge($pointCalcRules, $participantTeam, $challengeRulesData)
    {

        $appTimezone   = config('app.timezone');
        $count         = 0;
        $companyTotal1 = $companyTotal2 = 0;
        if ($this->short_name == 'combined' || $this->short_name == 'first_to_reach') {
            if (!empty($participantTeam) && $participantTeam->count() > 0) {
                foreach ($participantTeam as $teamMembers) {
                    $avgTargetByTeamRule = array();
                    foreach ($challengeRulesData as $rule) {
                        $ruleWiseTotal = 0;
                        if (!empty($teamMembers->users) && $teamMembers->users->count() > 0) {
                            $ParticipatedUserId = array();
                            $ParticipatedUserId = $teamMembers->users->pluck('id')->toArray();

                            if ($rule->short_name == 'distance') {
                                $ruleWiseTotal = DB::table('user_step')
                                    ->join('users', 'users.id', '=', 'user_step.user_id')
                                    ->where(\DB::raw("DATE(CONVERT_TZ(user_step.log_date, '{$appTimezone}',users.timezone))"), '>=', \DB::raw("DATE(CONVERT_TZ('{$this->start_date}', '{$appTimezone}',users.timezone))"))
                                    ->where(\DB::raw("DATE(CONVERT_TZ(user_step.log_date, '{$appTimezone}',users.timezone))"), '<=', \DB::raw("DATE(CONVERT_TZ('{$this->end_date}', '{$appTimezone}',users.timezone))"))
                                    ->whereIn("user_step.user_id", $ParticipatedUserId)
                                    ->sum('user_step.distance');
                            } elseif ($rule->short_name == 'steps') {
                                $ruleWiseTotal = DB::table('user_step')
                                    ->join('users', 'users.id', '=', 'user_step.user_id')
                                    ->where(\DB::raw("DATE(CONVERT_TZ(user_step.log_date, '{$appTimezone}',users.timezone))"), '>=', \DB::raw("DATE(CONVERT_TZ('{$this->start_date}', '{$appTimezone}',users.timezone))"))
                                    ->where(\DB::raw("DATE(CONVERT_TZ(user_step.log_date, '{$appTimezone}',users.timezone))"), '<=', \DB::raw("DATE(CONVERT_TZ('{$this->end_date}', '{$appTimezone}',users.timezone))"))
                                    ->whereIn("user_step.user_id", $ParticipatedUserId)
                                    ->sum('user_step.steps');
                            } elseif ($rule->short_name == 'exercises' && $rule->model_name == 'Exercise') {
                                $column = 'duration';
                                if ($rule->uom == 'meter') {
                                    $column = 'distance';
                                }

                                $ruleWiseTotal = DB::table('user_exercise')
                                    ->join('users', 'users.id', '=', 'user_exercise.user_id')
                                    ->where(\DB::raw("DATE(CONVERT_TZ(user_exercise.start_date, '{$appTimezone}',users.timezone))"), '>=', \DB::raw("DATE(CONVERT_TZ('{$this->start_date}', '{$appTimezone}',users.timezone))"))
                                    ->where(\DB::raw("DATE(CONVERT_TZ(user_exercise.start_date, '{$appTimezone}',users.timezone))"), '<=', \DB::raw("DATE(CONVERT_TZ('{$this->end_date}', '{$appTimezone}',users.timezone))"))
                                    ->whereIn("user_exercise.user_id", $ParticipatedUserId)
                                    ->where('user_exercise.exercise_id', $rule->model_id)
                                    ->whereNull('user_exercise.deleted_at')
                                    ->sum($column);

                                if ($column == 'duration') {
                                    $ruleWiseTotal = ($ruleWiseTotal / 60);
                                }
                            } elseif ($rule->short_name == 'meditations') {
                                $ruleWiseTotal = DB::table('user_listened_tracks')
                                    ->join('users', 'users.id', '=', 'user_listened_tracks.user_id')
                                    ->where(\DB::raw("DATE(CONVERT_TZ(user_listened_tracks.created_at, '{$appTimezone}',users.timezone))"), '>=', \DB::raw("DATE(CONVERT_TZ('{$this->start_date}', '{$appTimezone}',users.timezone))"))
                                    ->where(\DB::raw("DATE(CONVERT_TZ(user_listened_tracks.created_at, '{$appTimezone}',users.timezone))"), '<=', \DB::raw("DATE(CONVERT_TZ('{$this->end_date}', '{$appTimezone}',users.timezone))"))
                                    ->whereIn("user_listened_tracks.user_id", $ParticipatedUserId)
                                    ->count();
                            }
                        }

                        $avgTargetByTeamRule[] = $ruleWiseTotal;
                    }
                    if (!empty($avgTargetByTeamRule)) {
                        $companyTotal1 += $avgTargetByTeamRule[0];
                        if (!empty($avgTargetByTeamRule[1])) {
                            $companyTotal2 += $avgTargetByTeamRule[1];
                        }
                    }
                }
            }
            if ($this->short_name == 'first_to_reach' && $companyTotal1 >= $challengeRulesData[0]->target) {
                $winners = $participantTeam;
            } elseif ($this->short_name == 'combined' && $companyTotal1 >= $challengeRulesData[0]->target && $companyTotal2 >= $challengeRulesData[1]->target) {
                $winners = $participantTeam;
            }
        } elseif ($this->short_name == 'streak') {
            $userTimezone       = $this->timezone;
            $startDate          = Carbon::parse($this->start_date, $appTimezone)->setTimezone($userTimezone);
            $endDate            = Carbon::parse($this->end_date, $appTimezone)->setTimezone($userTimezone);
            $daysRange          = \createDateRange($startDate, $endDate);
            $targetAchivePerDay = 0;
            foreach ($daysRange as $day) {
                $totalTargetValueByDay = 0;
                foreach ($challengeRulesData as $rule) {
                    if (!empty($participantTeam) && $participantTeam->count() > 0) {
                        foreach ($participantTeam as $teamMembers) {
                            if (!empty($teamMembers->users) && $teamMembers->users->count() > 0) {
                                $ParticipatedUserId = array();
                                $ParticipatedUserId = $teamMembers->users->pluck('id')->toArray();

                                if ($rule->short_name == 'distance') {
                                    $count = DB::table('user_step')
                                        ->join('users', 'users.id', '=', 'user_step.user_id')
                                        ->where(\DB::raw("DATE(CONVERT_TZ(user_step.log_date, '{$appTimezone}',users.timezone))"), '=', $day->toDateString())
                                        ->whereIn("user_step.user_id", $ParticipatedUserId)
                                        ->sum('user_step.distance');
                                } elseif ($rule->short_name == 'steps') {
                                    $count = DB::table('user_step')
                                        ->join('users', 'users.id', '=', 'user_step.user_id')
                                        ->where(\DB::raw("DATE(CONVERT_TZ(user_step.log_date, '{$appTimezone}',users.timezone))"), '=', $day->toDateString())
                                        ->whereIn("user_step.user_id", $ParticipatedUserId)
                                        ->sum('user_step.steps');
                                } elseif ($rule->short_name == 'exercises' && $rule->model_name == 'Exercise') {
                                    $column = 'duration';
                                    if ($rule->uom == 'meter') {
                                        $column = 'distance';
                                    }

                                    $count = DB::table('user_exercise')
                                        ->join('users', 'users.id', '=', 'user_exercise.user_id')
                                        ->where(\DB::raw("DATE(CONVERT_TZ(user_exercise.start_date, '{$appTimezone}',users.timezone))"), '=', $day->toDateString())
                                        ->whereIn("user_exercise.user_id", $ParticipatedUserId)
                                        ->where('user_exercise.exercise_id', $rule->model_id)
                                        ->whereNull('user_exercise.deleted_at')
                                        ->sum($column);

                                    if ($column == 'duration') {
                                        $count = ($count / 60);
                                    }
                                } elseif ($rule->short_name == 'meditations') {
                                    $count = DB::table('user_listened_tracks')
                                        ->join('users', 'users.id', '=', 'user_listened_tracks.user_id')
                                        ->where(\DB::raw("DATE(CONVERT_TZ(user_listened_tracks.created_at, '{$appTimezone}',users.timezone))"), '=', $day->toDateString())
                                        ->whereIn("user_listened_tracks.user_id", $ParticipatedUserId)
                                        ->count();
                                }
                                $totalTargetValueByDay += $count;
                            }
                        }
                    }
                    if ($totalTargetValueByDay >= $rule->target) {
                        $targetAchivePerDay++;
                    }
                }
            }
            if ($targetAchivePerDay == count($daysRange)) {
                $winners = $participantTeam;
            }
        }
        return $winners;
    }

    public function getChallengeList($payload)
    {
        $timezone        = config('app.timezone');
        $challengeStatus = ($payload['challengeStatus'] ?? "");
        $challengeType   = ($payload['challengeType'] ?? "");

        $challengeList = $this->where("cancelled", false)
            ->where("challenge_type", $challengeType);

        if ($challengeStatus == "ongoing") {
            $challengeList->where('start_date', '<=', now($timezone)->toDateTimeString())->where('end_date', '>=', now($timezone)->toDateTimeString());
        } else {
            $challengeList->where('finished', true);
        }

        return $challengeList->select("id", "title")
            ->pluck("title", "id")
            ->toArray();
    }

    public function getChallengeParticipantList($payload)
    {
        $challengeData = self::find($payload['challenge']);
        $teamList      = array();
        $companyList   = array();
        if ($challengeData->challenge_type == "individual") {
            $teamList[$challengeData->company_id] = DB::table("freezed_challenge_participents")
                ->join("user_team", "user_team.user_id", "=", "freezed_challenge_participents.user_id")
                ->join("teams", "teams.id", "=", "user_team.team_id")
                ->where("freezed_challenge_participents.challenge_id", $payload['challenge'])
                ->select("teams.id", "teams.name")
                ->groupBy("teams.id")
                ->pluck("teams.name", "teams.id")
                ->toArray();
        } elseif ($challengeData->challenge_type == "inter_company") {
            foreach ($challengeData->memberCompaniesHistory as $value) {
                $teamList[$value->id] = $challengeData
                    ->memberTeamsHistory()
                    ->wherePivot("company_id", $value->id)
                    ->pluck("teams.name", "teams.id")
                    ->toArray();

                $companyList[$value->id] = $value->name;
            }
        } else {
            $teamList[$challengeData->company_id] = $challengeData
                ->memberTeamsHistory()
                ->pluck("teams.name", "teams.id")
                ->toArray();
        }

        if ($challengeData->challenge_type != "inter_company") {
            $company                   = Company::find($challengeData->company_id);
            $companyList[$company->id] = $company->name;
        }

        return array("companyList" => $companyList, "teamList" => $teamList);
    }

    public function getChallengeSummaryTableData($payload)
    {
        $challengeStatus = $payload['challengeStatus'];
        $challenge       = challenge::find($payload['challenge']);
        $pointCalcRules  = [];
        if (!empty($challenge)) {
            if ($challenge->challenge_type != 'inter_company') {
                if ($challengeStatus == "ongoing") {
                    $companyWiseChallengeSett = $challenge->company->companyWiseChallengeSett();
                } else {
                    $companyWiseChallengeSett = $challenge->challengeHistorySettings();
                }

                if ($companyWiseChallengeSett->count() > 0) {
                    $pointCalcRules = $companyWiseChallengeSett->pluck('value', 'type')->toArray();
                }
            }
        }

        if (empty($pointCalcRules)) {
            $pointCalcRules = config('zevolifesettings.default_limits');
        }

        $list      = $this->getChallengeSummaryRecordList($payload);
        $datatable = DataTables::of($list['record'])
            ->skipPaging()
            ->with([
                "recordsTotal"    => $list['total'],
                "recordsFiltered" => $list['total'],
            ])
            ->addColumn('userName', function ($record) {
                return (!empty($record->userName)) ? $record->userName : '';
            })
            ->addColumn('email', function ($record) {
                return (!empty($record->email)) ? $record->email : '';
            })
            ->addColumn('company', function ($record) {
                return (!empty($record->company)) ? $record->company : '';
            })
            ->addColumn('valueCount', function ($record) {
                return (!empty($record->valueCount)) ? round($record->valueCount, 1) : 0;
            })
            ->addColumn('points', function ($record) use ($pointCalcRules) {
                $point = 0;
                if ($record->type == "steps") {
                    $point = ($record->valueCount / $pointCalcRules[$record->type]);
                } elseif ($record->type == 'distance') {
                    $point = ($record->valueCount / $pointCalcRules[$record->type]);
                } elseif ($record->type == 'meditations') {
                    $point = ($record->valueCount / $pointCalcRules[$record->type]);
                } elseif ($record->type == 'exercises') {
                    $key   = (($record->uom == 'meter') ? 'exercises_distance' : 'exercises_duration');
                    $count = $record->valueCount;
                    $point = ($count / $pointCalcRules[$key]);
                }
                return round($point, 1);
            });

        return $datatable->rawColumns(['userName', 'email', 'company', 'valueCount', 'points'])->make(true);
    }

    /**
     * get record list for data table list.
     *
     * @param payload
     * @return roleList
     */

    public function getChallengeSummaryRecordList($payload)
    {
        $challengeId = $payload['challenge'];
        $challenge   = self::find($challengeId);
        $record      = array();
        if (!empty($challenge)) {
            $challengeRulesData = $challenge->challengeRules()->join('challenge_targets', 'challenge_targets.id', '=', 'challenge_rules.challenge_target_id')->select('challenge_rules.*', 'challenge_targets.short_name', 'challenge_targets.name')->get();
            if ($challenge->challenge_type == "individual") {
                $queryArray = array();

                foreach ($challengeRulesData as $value) {
                    if ($value->short_name == "steps" || $value->short_name == "distance") {
                        $columnName = "freezed_challenge_steps." . $value->short_name;

                        $queryArray[] = DB::table("freezed_challenge_steps")
                            ->select(DB::raw("sum({$columnName})"))
                            ->whereRaw("freezed_challenge_steps.challenge_id = ".$challenge->id)
                            ->whereRaw("freezed_challenge_steps.user_id = freezed_challenge_participents.user_id");
                    } elseif ($value->short_name == 'meditations') {
                        $queryArray[] = DB::table("freezed_challenge_inspire")
                            ->select(DB::raw("count(freezed_challenge_inspire.meditation_track_id)"))
                            ->whereRaw("freezed_challenge_inspire.challenge_id = ".$challenge->id)
                            ->whereRaw("freezed_challenge_inspire.user_id = freezed_challenge_participents.user_id");
                    } elseif ($value->short_name == 'exercises' && $value->model_name == 'Exercise') {
                        $column = 'freezed_challenge_exercise.duration';
                        if ($value->uom == 'meter') {
                            $column = 'freezed_challenge_exercise.distance';
                        }

                        $exerciseQuery = DB::table("freezed_challenge_exercise")
                            ->whereRaw("freezed_challenge_exercise.challenge_id = ".$challenge->id)
                            ->whereRaw("freezed_challenge_exercise.exercise_id = ".$value->model_id)
                            ->whereRaw("freezed_challenge_exercise.user_id = freezed_challenge_participents.user_id");
                        if ($value->uom == "meter") {
                            $exerciseQuery
                                ->select(DB::raw(" sum({$column})"));
                        } else {
                            $exerciseQuery
                                ->select(DB::raw("sum({$column}) / 60 "));
                        }
                        $queryArray[] = $exerciseQuery;
                    }
                }
                if (count($queryArray) > 0) {
                    $firstRuleData = DB::table("freezed_challenge_participents")
                        ->select(
                            DB::raw(" CONCAT(users.first_name, ' ',users.last_name) as userName"),
                            "users.email",
                            "teams.name as team",
                            DB::raw("'{$challengeRulesData[0]->short_name}' as type"),
                            DB::raw("'{$challengeRulesData[0]->uom}' as uom"),
                            DB::raw("({$queryArray[0]->toSql()}) as valueCount"),
                            DB::raw(" (select challenge_wise_user_ponits.points from challenge_wise_user_ponits where challenge_wise_user_ponits.challenge_id = {$challenge->id} and challenge_wise_user_ponits.user_id = freezed_challenge_participents.user_id) points ")
                        )
                        ->join("user_team", "user_team.user_id", "=", "freezed_challenge_participents.user_id")
                        ->join("users", "users.id", "=", "user_team.user_id")
                        ->join("teams", "teams.id", "=", "user_team.team_id")
                        ->where("freezed_challenge_participents.challenge_id", $challenge->id);

                    if (!empty($payload['team'])) {
                        $firstRuleData->where("teams.id", $payload['team']);
                    }

                    if ($challengeRulesData->count() >= 2 && count($queryArray) >= 2) {
                        $secondRuleData = DB::table("freezed_challenge_participents")
                            ->select(
                                DB::raw(" CONCAT(users.first_name, ' ',users.last_name) as userName"),
                                "users.email",
                                "teams.name as team",
                                DB::raw("'{$challengeRulesData[1]->short_name}' as type"),
                                DB::raw("'{$challengeRulesData[1]->uom}' as uom"),
                                DB::raw("({$queryArray[1]->toSql()}) as valueCount"),
                                DB::raw(" (select challenge_wise_user_ponits.points from challenge_wise_user_ponits where challenge_wise_user_ponits.challenge_id = {$challenge->id} and challenge_wise_user_ponits.user_id = freezed_challenge_participents.user_id) points ")
                            )
                            ->join("user_team", "user_team.user_id", "=", "freezed_challenge_participents.user_id")
                            ->join("users", "users.id", "=", "user_team.user_id")
                            ->join("teams", "teams.id", "=", "user_team.team_id")
                            ->where("freezed_challenge_participents.challenge_id", $challenge->id);

                        if (!empty($payload['team'])) {
                            $secondRuleData->where("teams.id", $payload['team']);
                        }
                    }

                    if ($challengeRulesData->count() >= 2 && count($queryArray) >= 2) {
                        $record = $firstRuleData->union($secondRuleData);
                    } else {
                        $record = $firstRuleData;
                    }

                    if (isset($payload['order'])) {
                        $column = $payload['columns'][$payload['order'][0]['column']]['data'];
                        $order  = $payload['order'][0]['dir'];
                        $record->orderBy($column, $order);
                    }

                    return [
                        'total'  => $record->get()->count(),
                        'record' => (!empty($payload['length']) && $payload['length'] > 0) ? $record->offset($payload['start'])->limit($payload['length'])->get() : $record->get(),
                    ];
                } else {
                    return [
                        'total'  => 0,
                        'record' => [],
                    ];
                }
            } else {
                $queryArray  = array();
                $companyGoal = ($challenge->challenge_type == "company_goal") ? 'true' : 'false';

                foreach ($challengeRulesData as $value) {
                    if ($value->short_name == "steps" || $value->short_name == "distance") {
                        $columnName = "freezed_challenge_steps." . $value->short_name;

                        $queryArray[] = DB::table("freezed_challenge_steps")
                            ->leftJoin("freezed_team_challenge_participents", "freezed_challenge_steps.user_id", "=", "freezed_team_challenge_participents.user_id")
                            ->select(DB::raw(" if('{$companyGoal}' = 'true',
                                            sum({$columnName}),
                                            sum({$columnName})
                                            /
                                                (
                                                select count(freezed_team_challenge_participents.user_id)
                                                    from freezed_team_challenge_participents
                                                    where freezed_team_challenge_participents.team_id = freezed_challenge_participents.team_id
                                                    and freezed_team_challenge_participents.challenge_id = {$challenge->id}
                                                )
                                            ) "))
                            ->whereRaw("freezed_challenge_steps.challenge_id = ".$challenge->id)
                            ->whereRaw("freezed_team_challenge_participents.challenge_id = ".$challenge->id)
                            ->whereRaw("freezed_team_challenge_participents.team_id = freezed_challenge_participents.team_id");
                    } elseif ($value->short_name == 'meditations') {
                        $queryArray[] = DB::table("freezed_challenge_inspire")
                            ->leftJoin("freezed_team_challenge_participents", "freezed_challenge_inspire.user_id", "=", "freezed_team_challenge_participents.user_id")
                            ->select(DB::raw(" if('{$companyGoal}' = 'true',
                                            count(freezed_challenge_inspire.meditation_track_id),
                                            count(freezed_challenge_inspire.meditation_track_id)
                                            /
                                                (
                                                select count(freezed_team_challenge_participents.user_id)
                                                from freezed_team_challenge_participents
                                                where freezed_team_challenge_participents.team_id = freezed_challenge_participents.team_id
                                                and freezed_team_challenge_participents.challenge_id = {$challenge->id}
                                                )
                                            ) "))
                            ->whereRaw("freezed_challenge_inspire.challenge_id = ".$challenge->id)
                            ->whereRaw("freezed_team_challenge_participents.challenge_id = ".$challenge->id)
                            ->whereRaw("freezed_team_challenge_participents.team_id = freezed_challenge_participents.team_id");
                    } elseif ($value->short_name == 'exercises' && $value->model_name == 'Exercise') {
                        $column = 'freezed_challenge_exercise.duration';
                        if ($value->uom == 'meter') {
                            $column = 'freezed_challenge_exercise.distance';
                        }

                        $exerciseQuery = DB::table("freezed_challenge_exercise")
                            ->whereRaw("freezed_challenge_exercise.exercise_id  = ".$value->model_id)
                            ->leftJoin("freezed_team_challenge_participents", "freezed_challenge_exercise.user_id", "=", "freezed_team_challenge_participents.user_id")
                            ->whereRaw("freezed_challenge_exercise.challenge_id = ".$challenge->id)
                            ->whereRaw("freezed_team_challenge_participents.challenge_id = ".$challenge->id)
                            ->whereRaw("freezed_team_challenge_participents.team_id = freezed_challenge_participents.team_id");
                        if ($value->uom == "meter") {
                            $exerciseQuery
                                ->select(DB::raw(" if('{$companyGoal}' = 'true',
                                                sum({$column}),
                                                sum({$column})
                                                /
                                                (
                                                    select count(freezed_team_challenge_participents.user_id)
                                                    from freezed_team_challenge_participents
                                                    where freezed_team_challenge_participents.team_id = freezed_challenge_participents.team_id
                                                    and freezed_team_challenge_participents.challenge_id = {$challenge->id}
                                                )
                                            ) "));
                        } else {
                            $exerciseQuery
                                ->select(DB::raw(" if('{$companyGoal}' = 'true',
                                            sum({$column}),
                                            sum({$column})
                                            /
                                            (
                                                select count(freezed_team_challenge_participents.user_id)
                                                from freezed_team_challenge_participents
                                                where freezed_team_challenge_participents.team_id = freezed_challenge_participents.team_id
                                                and freezed_team_challenge_participents.challenge_id = ?
                                            )
                                        ) / 60 ", [$challenge->id]));
                        }
                        $queryArray[] = $exerciseQuery;
                    }
                }
                if (count($queryArray) > 0) {
                    $firstRuleData = DB::table("freezed_challenge_participents")
                        ->select(
                            "companies.name as company",
                            "teams.name as team",
                            DB::raw("'{$challengeRulesData[0]->short_name}' as type"),
                            DB::raw("'{$challengeRulesData[0]->uom}' as uom"),
                            DB::raw("({$queryArray[0]->toSql()}) as valueCount"),
                            DB::raw(" (select challenge_wise_team_ponits.points from challenge_wise_team_ponits where challenge_wise_team_ponits.challenge_id = {$challenge->id} and challenge_wise_team_ponits.team_id = freezed_challenge_participents.team_id) points ")
                        )
                        ->join("teams", "teams.id", "=", "freezed_challenge_participents.team_id")
                        ->join("companies", "companies.id", "=", "teams.company_id")
                        ->where("freezed_challenge_participents.challenge_id", $challenge->id);

                    if ($challenge->challenge_type == "inter_company" && !empty($payload['company'])) {
                        $firstRuleData->where("companies.id", $payload['company']);
                    }

                    if (!empty($payload['team'])) {
                        $firstRuleData->where("teams.id", $payload['team']);
                    }

                    if ($challengeRulesData->count() >= 2 && count($queryArray) >= 2) {
                        $secondRuleData = DB::table("freezed_challenge_participents")
                            ->select(
                                "companies.name as company",
                                "teams.name as team",
                                DB::raw("'{$challengeRulesData[1]->short_name}' as type"),
                                DB::raw("'{$challengeRulesData[1]->uom}' as uom"),
                                DB::raw("({$queryArray[1]->toSql()}) as valueCount"),
                                DB::raw(" (select challenge_wise_team_ponits.points from challenge_wise_team_ponits where challenge_wise_team_ponits.challenge_id = {$challenge->id} and challenge_wise_team_ponits.team_id = freezed_challenge_participents.team_id) points ")
                            )
                            ->join("teams", "teams.id", "=", "freezed_challenge_participents.team_id")
                            ->join("companies", "companies.id", "=", "teams.company_id")
                            ->where("freezed_challenge_participents.challenge_id", $challenge->id);

                        if ($challenge->challenge_type == "inter_company" && !empty($payload['company'])) {
                            $secondRuleData->where("companies.id", $payload['company']);
                        }

                        if (!empty($payload['team'])) {
                            $secondRuleData->where("teams.id", $payload['team']);
                        }
                    }

                    if ($challengeRulesData->count() >= 2 && count($queryArray) >= 2) {
                        $record = $firstRuleData->union($secondRuleData);
                    } else {
                        $record = $firstRuleData;
                    }

                    if (isset($payload['order'])) {
                        $column = $payload['columns'][$payload['order'][0]['column']]['data'];
                        $order  = $payload['order'][0]['dir'];
                        $record->orderBy($column, $order);
                    }

                    return [
                        'total'  => $record->get()->count(),
                        'record' => (!empty($payload['length']) && $payload['length'] > 0) ? $record->offset($payload['start'])->limit($payload['length'])->get() : $record->get(),
                    ];
                } else {
                    return [
                        'total'  => 0,
                        'record' => [],
                    ];
                }
            }
        } else {
            return [
                'total'  => 0,
                'record' => [],
            ];
        }
    }

    public function getChallengeDetailsTableData($payload)
    {
        $challengeStatus = $payload['challengeStatus'];
        $challenge       = challenge::find($payload['challenge']);
        $pointCalcRules  = [];
        if ($challenge->challenge_type != 'inter_company') {
            if ($challengeStatus == "ongoing") {
                $companyWiseChallengeSett = $challenge->company->companyWiseChallengeSett();
            } else {
                $companyWiseChallengeSett = $challenge->challengeHistorySettings();
            }

            if ($companyWiseChallengeSett->count() > 0) {
                $pointCalcRules = $companyWiseChallengeSett->pluck('value', 'type')->toArray();
            }
        }

        if (empty($pointCalcRules)) {
            $pointCalcRules = config('zevolifesettings.default_limits');
        }

        $list      = $this->getChallengeDetailsRecordList($payload);
        $datatable = DataTables::of($list['record'])
            ->skipPaging()
            ->with([
                "recordsTotal"    => $list['total'],
                "recordsFiltered" => $list['total'],
            ])
            ->addColumn('valueCount', function ($record) {
                return (!empty($record->valueCount)) ? round($record->valueCount, 1) : 0;
            })
            ->addColumn('points', function ($record) use ($pointCalcRules) {
                $point = 0;
                if ($record->type == "steps") {
                    $point = ($record->userVal / $pointCalcRules[$record->type]);
                } elseif ($record->type == 'distance') {
                    $point = ($record->userVal / $pointCalcRules[$record->type]);
                } elseif ($record->type == 'meditations') {
                    $point = ($record->userVal / $pointCalcRules[$record->type]);
                } elseif ($record->type == 'exercises') {
                    $key   = (($record->uom == 'meter') ? 'exercises_distance' : 'exercises_duration');
                    $count = $record->userVal;
                    $point = ($count / $pointCalcRules[$key]);
                }
                return round($point, 1);
            });

        return $datatable->rawColumns([])->make(true);
    }

    /**
     * get record list for data table list.
     *
     * @param payload
     * @return roleList
     */

    public function getChallengeDetailsRecordList($payload)
    {
        $challengeId = $payload['challenge'];
        $challenge   = self::find($challengeId);
        if (!empty($challenge)) {
            $challengeRulesData = $challenge->challengeRules()
                ->join('challenge_targets', 'challenge_targets.id', '=', 'challenge_rules.challenge_target_id')
                ->select(
                    'challenge_rules.id',
                    'challenge_rules.challenge_target_id',
                    'challenge_rules.uom',
                    'challenge_rules.model_id',
                    'challenge_rules.model_name',
                    'challenge_targets.short_name',
                    'challenge_targets.name'
                )
                ->get();

            $record = array();

            $queryArray = array();

            foreach ($challengeRulesData as $value) {
                if ($value->short_name == "steps" || $value->short_name == "distance") {
                    $columnName = "freezed_challenge_steps." . $value->short_name;

                    $selectQuery = DB::table("freezed_challenge_steps")
                        ->select(
                            DB::raw("'{$value->uom}' as uom"),
                            DB::raw("'0' as model_id"),
                            DB::raw("sum({$columnName}) as userVal"),
                            "freezed_challenge_steps.tracker",
                            "freezed_challenge_steps.log_date",
                            "freezed_challenge_steps.created_at",
                            "freezed_challenge_steps.user_id",
                            DB::raw("'{$value->short_name}' as columnName")
                        )
                        ->whereRaw("freezed_challenge_steps.challenge_id = ".$challenge->id);

                    $queryArray[] = $selectQuery
                        ->groupBy("user_id")
                        ->groupBy(DB::raw("DATE(log_date)"));
                } elseif ($value->short_name == 'meditations') {
                    $queryArray[] = DB::table("freezed_challenge_inspire")
                        ->select(
                            DB::raw("'{$value->uom}' as uom"),
                            DB::raw("'0' as model_id"),
                            DB::raw("count(freezed_challenge_inspire.meditation_track_id) as userVal"),
                            DB::raw("'NA' as tracker"),
                            "freezed_challenge_inspire.log_date",
                            "freezed_challenge_inspire.created_at",
                            "freezed_challenge_inspire.user_id",
                            DB::raw("'{$value->short_name}' as columnName")
                        )
                        ->whereRaw("freezed_challenge_inspire.challenge_id = ".$challenge->id)
                        ->groupBy("user_id")
                        ->groupBy(DB::raw("DATE(log_date)"));
                } elseif ($value->short_name == 'exercises' && $value->model_name == 'Exercise') {
                    $column = 'freezed_challenge_exercise.duration';
                    if ($value->uom == 'meter') {
                        $column = 'freezed_challenge_exercise.distance';
                    }

                    $exerciseQuery = DB::table("freezed_challenge_exercise")
                        ->whereRaw("freezed_challenge_exercise.challenge_id = ".$challenge->id)
                        ->whereRaw("freezed_challenge_exercise.exercise_id = ".$value->model_id)
                        ->groupBy("user_id")
                        ->groupBy(DB::raw("DATE(start_date)"));
                    if ($value->uom == "meter") {
                        $exerciseQuery
                            ->select(
                                DB::raw("'{$value->uom}' as uom"),
                                DB::raw("freezed_challenge_exercise.exercise_id as model_id"),
                                DB::raw("sum({$column}) userVal"),
                                "freezed_challenge_exercise.tracker",
                                "freezed_challenge_exercise.start_date as log_date",
                                "freezed_challenge_exercise.created_at",
                                "freezed_challenge_exercise.user_id",
                                DB::raw("'distance' as columnName")
                            );
                    } else {
                        $exerciseQuery
                            ->select(
                                DB::raw("'{$value->uom}' as uom"),
                                DB::raw("freezed_challenge_exercise.exercise_id as model_id"),
                                DB::raw("ROUND((sum({$column}) / 60), 2) userVal"),
                                "freezed_challenge_exercise.tracker",
                                "freezed_challenge_exercise.start_date as log_date",
                                "freezed_challenge_exercise.created_at",
                                "freezed_challenge_exercise.user_id",
                                DB::raw("'duration' as columnName")
                            );
                    }
                    $queryArray[] = $exerciseQuery;
                }
            }

            if (count($queryArray) > 0) {
                if ($challenge->challenge_type == "individual") {
                    $firstRuleData = DB::table("freezed_challenge_participents")
                        ->select(
                            DB::raw(" CONCAT(users.first_name, ' ',users.last_name) as userName"),
                            "users.email",
                            "teams.name as team",
                            "companies.name as company",
                            DB::raw("'{$challengeRulesData[0]->short_name}' as type"),
                            "valTable.*",
                            DB::raw(" (select challenge_wise_user_ponits.points from challenge_wise_user_ponits where challenge_wise_user_ponits.challenge_id = {$challenge->id} and challenge_wise_user_ponits.user_id = freezed_challenge_participents.user_id) points ")
                        )
                        ->join(DB::raw("({$queryArray[0]->toSql()}) as valTable"), "valTable.user_id", "freezed_challenge_participents.user_id")
                        ->join("user_team", "user_team.user_id", "=", "freezed_challenge_participents.user_id")
                        ->join("users", "users.id", "=", "user_team.user_id")
                        ->join("teams", "teams.id", "=", "user_team.team_id")
                        ->join("companies", "companies.id", "=", "teams.company_id")
                        ->where("freezed_challenge_participents.challenge_id", $challenge->id);

                    if (!empty($payload['team'])) {
                        $firstRuleData->where("teams.id", $payload['team']);
                    }

                    if (!empty($payload['userrecordsearch'])) {
                        $userrecordsearch = $payload['userrecordsearch'];
                        $firstRuleData->where(function ($query) use ($userrecordsearch) {
                            $query->where(DB::raw(" CONCAT(users.first_name, ' ',users.last_name)"), "LIKE", "%" . $userrecordsearch . "%")
                                ->orWhere('users.email', "LIKE", "%" . $userrecordsearch . "%");
                        });
                    }

                    if ($challengeRulesData->count() >= 2 && count($queryArray) >= 2) {
                        $secondRuleData = DB::table("freezed_challenge_participents")
                            ->select(
                                DB::raw(" CONCAT(users.first_name, ' ',users.last_name) as userName"),
                                "users.email",
                                "teams.name as team",
                                "companies.name as company",
                                DB::raw("'{$challengeRulesData[1]->short_name}' as type"),
                                "valTable.*",
                                DB::raw(" (select challenge_wise_user_ponits.points from challenge_wise_user_ponits where challenge_wise_user_ponits.challenge_id = {$challenge->id} and challenge_wise_user_ponits.user_id = freezed_challenge_participents.user_id) points ")
                            )
                            ->join(DB::raw("({$queryArray[1]->toSql()}) as valTable"), "valTable.user_id", "freezed_challenge_participents.user_id")
                            ->join("user_team", "user_team.user_id", "=", "freezed_challenge_participents.user_id")
                            ->join("users", "users.id", "=", "user_team.user_id")
                            ->join("teams", "teams.id", "=", "user_team.team_id")
                            ->join("companies", "companies.id", "=", "teams.company_id")
                            ->where("freezed_challenge_participents.challenge_id", $challenge->id);

                        if (!empty($payload['team'])) {
                            $secondRuleData->where("teams.id", $payload['team']);
                        }

                        if (!empty($payload['userrecordsearch'])) {
                            $userrecordsearch = $payload['userrecordsearch'];
                            $secondRuleData->where(function ($query) use ($userrecordsearch) {
                                $query->where(DB::raw(" CONCAT(users.first_name, ' ',users.last_name)"), "LIKE", "%" . $userrecordsearch . "%")
                                    ->orWhere('users.email', "LIKE", "%" . $userrecordsearch . "%");
                            });
                        }
                    }

                    if ($challengeRulesData->count() >= 2 && count($queryArray) >= 2) {
                        $record = $firstRuleData->union($secondRuleData);
                    } else {
                        $record = $firstRuleData;
                    }

                    if (isset($payload['order'])) {
                        $column = $payload['columns'][$payload['order'][0]['column']]['data'];
                        $order  = $payload['order'][0]['dir'];
                        $record->orderBy($column, $order);
                    }

                    return [
                        'total'  => $record->get()->count('id'),
                        'record' => (!empty($payload['length']) && $payload['length'] > 0) ? $record->offset($payload['start'])->limit($payload['length'])->get() : $record->get(),
                    ];
                } else {
                    $firstRuleData = DB::table("freezed_challenge_participents")
                        ->select(
                            DB::raw(" CONCAT(users.first_name, ' ',users.last_name) as userName"),
                            "users.email",
                            "teams.name as team",
                            "companies.name as company",
                            DB::raw("'{$challengeRulesData[0]->short_name}' as type"),
                            "valTable.*",
                            DB::raw(" (select challenge_wise_user_ponits.points from challenge_wise_user_ponits where challenge_wise_user_ponits.challenge_id = {$challenge->id} and challenge_wise_user_ponits.user_id = user_team.user_id) points")
                        )
                        ->join("user_team", "user_team.team_id", "=", "freezed_challenge_participents.team_id")
                        ->join(DB::raw("({$queryArray[0]->toSql()}) as valTable"), "valTable.user_id", "user_team.user_id")
                        ->join("users", "users.id", "=", "user_team.user_id")
                        ->join("teams", "teams.id", "=", "user_team.team_id")
                        ->join("companies", "companies.id", "=", "teams.company_id")
                        ->where("freezed_challenge_participents.challenge_id", $challenge->id)
                        ->where("freezed_challenge_participents.challenge_id", $challenge->id);

                    if ($challenge->challenge_type == "inter_company" && !empty($payload['company'])) {
                        $firstRuleData->where("companies.id", $payload['company']);
                    }

                    if (!empty($payload['team'])) {
                        $firstRuleData->where("teams.id", $payload['team']);
                    }

                    if (!empty($payload['userrecordsearch'])) {
                        $userrecordsearch = $payload['userrecordsearch'];
                        $firstRuleData->where(function ($query) use ($userrecordsearch) {
                            $query->where(DB::raw(" CONCAT(users.first_name, ' ',users.last_name)"), "LIKE", "%" . $userrecordsearch . "%")
                                ->orWhere('users.email', "LIKE", "%" . $userrecordsearch . "%");
                        });
                    }

                    if ($challengeRulesData->count() >= 2 && count($queryArray) >= 2) {
                        $secondRuleData = DB::table("freezed_challenge_participents")
                            ->select(
                                DB::raw(" CONCAT(users.first_name, ' ',users.last_name) as userName"),
                                "users.email",
                                "teams.name as team",
                                "companies.name as company",
                                DB::raw("'{$challengeRulesData[1]->short_name}' as type"),
                                "valTable.*",
                                DB::raw(" (select challenge_wise_user_ponits.points from challenge_wise_user_ponits where challenge_wise_user_ponits.challenge_id = {$challenge->id} and challenge_wise_user_ponits.user_id = user_team.user_id) points ")
                            )
                            ->join("user_team", "user_team.team_id", "=", "freezed_challenge_participents.team_id")
                            ->join(DB::raw("({$queryArray[1]->toSql()}) as valTable"), "valTable.user_id", "user_team.user_id")
                            ->join("users", "users.id", "=", "user_team.user_id")
                            ->join("teams", "teams.id", "=", "user_team.team_id")
                            ->join("companies", "companies.id", "=", "teams.company_id")
                            ->where("freezed_challenge_participents.challenge_id", $challenge->id)
                            ->where("freezed_challenge_participents.challenge_id", $challenge->id);

                        if ($challenge->challenge_type == "inter_company" && !empty($payload['company'])) {
                            $secondRuleData->where("companies.id", $payload['company']);
                        }

                        if (!empty($payload['team'])) {
                            $secondRuleData->where("teams.id", $payload['team']);
                        }

                        if (!empty($payload['userrecordsearch'])) {
                            $userrecordsearch = $payload['userrecordsearch'];
                            $secondRuleData->where(function ($query) use ($userrecordsearch) {
                                $query->where(DB::raw(" CONCAT(users.first_name, ' ',users.last_name)"), "LIKE", "%" . $userrecordsearch . "%")
                                    ->orWhere('users.email', "LIKE", "%" . $userrecordsearch . "%");
                            });
                        }
                    }

                    if ($challengeRulesData->count() >= 2 && count($queryArray) >= 2) {
                        $record = $firstRuleData->union($secondRuleData);
                    } else {
                        $record = $firstRuleData;
                    }

                    if (isset($payload['order'])) {
                        $column = $payload['columns'][$payload['order'][0]['column']]['data'];
                        $order  = $payload['order'][0]['dir'];
                        $record->orderBy($column, $order);
                    }

                    return [
                        'total'  => $record->get()->count('id'),
                        'record' => (!empty($payload['length']) && $payload['length'] > 0) ? $record->offset($payload['start'])->limit($payload['length'])->get() : $record->get(),
                    ];
                }
            } else {
                return [
                    'total'  => 0,
                    'record' => [],
                ];
            }
        } else {
            $record['total']  = 0;
            $record['record'] = [];
        }

        return $record;
    }

    public function getChallengeDailySummaryTableData($payload)
    {
        $challengeStatus = $payload['challengeStatus'];
        $challengeInfo   = self::find($payload['challenge']);
        $pointCalcRules  = [];
        if (!empty($challengeInfo) && $challengeInfo->challenge_type != 'inter_company') {
            if ($challengeStatus == "ongoing") {
                $companyWiseChallengeSett = $challengeInfo->company->companyWiseChallengeSett();
            } else {
                $companyWiseChallengeSett = $challengeInfo->challengeHistorySettings();
            }

            if ($companyWiseChallengeSett->count() > 0) {
                $pointCalcRules = $companyWiseChallengeSett->pluck('value', 'type')->toArray();
            }
        }

        if (empty($pointCalcRules)) {
            $pointCalcRules = config('zevolifesettings.default_limits');
        }

        $list      = $this->getChallengeDetailsRecordList($payload);
        $datatable = DataTables::of($list['record'])
            ->skipPaging()
            ->with([
                "recordsTotal"    => $list['total'],
                "recordsFiltered" => $list['total'],
            ])
            ->addColumn('valueCount', function ($record) {
                return (!empty($record->valueCount)) ? round($record->valueCount, 1) : 0;
            })
            ->addColumn('trackerchange', function ($record) use ($challengeInfo) {
                if ($record->columnName == 'steps') {
                    $trackerChange = \DB::table('challenge_user_steps_history')
                        ->where('challenge_id', $challengeInfo->id)
                        ->where('user_id', $record->user_id)
                        ->where('log_date', $record->log_date)
                        ->distinct('tracker')
                        ->count();
                } elseif ($record->columnName == 'exercises') {
                    $trackerChange = \DB::table('challenge_user_exercise_history')
                        ->where('challenge_id', $challengeInfo->id)
                        ->where('user_id', $record->user_id)
                        ->where('start_date', $record->log_date)
                        ->distinct('tracker')
                        ->count();
                }

                return (!empty($trackerChange)) ? $trackerChange : 0;
            })
            ->addColumn('points', function ($record) use ($pointCalcRules) {
                $point = 0;
                if ($record->type == "steps") {
                    $point = ($record->userVal / $pointCalcRules[$record->type]);
                } elseif ($record->type == 'distance') {
                    $point = ($record->userVal / $pointCalcRules[$record->type]);
                } elseif ($record->type == 'meditations') {
                    $point = ($record->userVal / $pointCalcRules[$record->type]);
                } elseif ($record->type == 'exercises') {
                    $key   = (($record->uom == 'meter') ? 'exercises_distance' : 'exercises_duration');
                    $count = $record->userVal;
                    $point = ($count / $pointCalcRules[$key]);
                }
                return round($point, 1);
            })
            ->addColumn('log_date', function ($record) {
                return Carbon::parse($record->log_date)->toDateString();
            })
            ->addColumn('actions', function ($record) use ($challengeInfo, $challengeStatus) {
                $logDate = Carbon::parse($record->log_date)->toDateString();
                return view('admin.report.viewuserhistoryaction', compact('record', 'challengeInfo', 'logDate', 'challengeStatus'))->render();
            });

        return $datatable->rawColumns(['trackerchange', 'points', 'log_date', 'actions'])->make(true);
    }

    public function getUserDailyHistoryTableData($payload)
    {
        $type            = $payload['type'];
        $uom             = $payload['uom'];
        $challengeStatus = $payload['challengeStatus'];
        $challengeInfo   = self::find($payload['challenge_id']);
        $pointCalcRules  = [];
        if ($challengeInfo->challenge_type != 'inter_company') {
            if ($challengeStatus == "ongoing") {
                $companyWiseChallengeSett = $challengeInfo->company->companyWiseChallengeSett();
            } else {
                $companyWiseChallengeSett = $challengeInfo->challengeHistorySettings();
            }

            if ($companyWiseChallengeSett->count() > 0) {
                $pointCalcRules = $companyWiseChallengeSett->pluck('value', 'type')->toArray();
            }
        }

        if (empty($pointCalcRules)) {
            $pointCalcRules = config('zevolifesettings.default_limits');
        }

        $list = $this->getUserDailyHistoryRecordList($payload);
        return DataTables::of($list)
            ->addColumn('points', function ($record) use ($pointCalcRules, $type, $uom) {
                $point = 0;
                if ($type == "steps") {
                    $point = ($record->achivedValue / $pointCalcRules[$type]);
                } elseif ($type == 'distance') {
                    $point = ($record->achivedValue / $pointCalcRules[$type]);
                } elseif ($type == 'meditations') {
                    $point = ($record->achivedValue / $pointCalcRules[$type]);
                } elseif ($type == 'exercises') {
                    $key = (($uom == 'meter') ? 'exercises_distance' : 'exercises_duration');
                    $count = $record->achivedValue;
                    $point = ($count / $pointCalcRules[$key]);
                }
                return round($point, 1);
            })
            ->make(true);
    }

    public function getUserDailyHistoryRecordList($payload)
    {
        $tableData  = array();
        $type       = $payload['type'];
        $uctype     = ucfirst($payload['type']);
        $columnName = $payload['columnName'];
        if ($payload['type'] == "steps" || $payload['type'] == "distance") {
            $tableData = DB::table("challenge_user_steps_history")
                ->where("user_id", $payload['user_id'])
                ->where("challenge_id", $payload['challenge_id'])
                ->where(DB::raw("DATE(log_date)"), $payload['logdate'])
                ->select("tracker", DB::raw("'$uctype' as type"), DB::raw("$type as achivedValue"), "points", "created_at");

            if (!empty($payload['trackerFilter'])) {
                $tableData->where("tracker", $payload['trackerFilter']);
            }
            $tableData = $tableData->get();
        } elseif ($payload['type'] == "exercises") {
            $tableData = DB::table("challenge_user_exercise_history")
                ->where("user_id", $payload['user_id'])
                ->where("challenge_id", $payload['challenge_id'])
                ->where(function ($query) use ($payload) {
                    if (!empty($payload['modelId'])) {
                        $query->where("exercise_id", $payload['modelId']);
                    }
                })
                ->where(DB::raw("DATE(start_date)"), $payload['logdate'])
                ->select("tracker", DB::raw("'$uctype' as type"), DB::raw("if('$columnName' = 'duration', ROUND(($columnName / 60), 2) , $columnName) as achivedValue"), "points", "created_at");
            if (!empty($payload['trackerFilter'])) {
                $tableData->where("tracker", $payload['trackerFilter']);
            }
            $tableData = $tableData->get();
        } elseif ($payload['type'] == "meditations") {
            $tableData = DB::table("challenge_user_inspire_history")
                ->where("user_id", $payload['user_id'])
                ->where("challenge_id", $payload['challenge_id'])
                ->where(DB::raw("DATE(log_date)"), $payload['logdate'])
                ->select(DB::raw("'NA' as tracker"), DB::raw("'$uctype' as type"), DB::raw("COUNT(meditation_track_id) as achivedValue"), "points", "created_at")
                ->groupBy(DB::raw("DATE_FORMAT(created_at,'%Y-%m-%d %H:%i')"))
                ->get();
        }

        return $tableData;
    }

    public function autoGenerateGroups()
    {
        if ($this->challenge_type == 'individual') {
            $members = $this->members()->where('status', 'Accepted')->get()->pluck('id')->toArray();
            if (count($members) < 5) {
                return;
            }
        } elseif ($this->challenge_type == 'team') {
            $teamData = $this->memberTeams()->get();
            $members  = $teamData->transform(function ($value) {
                if ($value->users()->count() < 2) {
                    return;
                }
                return $value->users()->get()->pluck('id');
            })->filter(function ($value) {
                return !is_null($value);
            })->flatten();
        } elseif ($this->challenge_type == 'company_goal') {
            $teamData = $this->memberTeams()->get();
            $members  = $teamData->transform(function ($value) {
                return $value->users()->get()->pluck('id');
            })->flatten();
        } else {
            $teamData = $this->memberTeams()->get();
            $members  = $teamData->transform(function ($value) {
                if ($value->company()->first()->members()->count() < 2) {
                    return;
                }
                return $value->users()->get()->pluck('id');
            })->filter(function ($value) {
                return !is_null($value);
            })->flatten();
        }

        if (!empty($members)) {
            $subCategory = SubCategory::where('short_name', 'challenge')->first();

            $groupPayload = [
                'name'             => $this->title,
                'category'         => $subCategory->id,
                'introduction'     => $this->description,
                'members_selected' => $members,
                'model_id'         => (!empty($this->parent_id) ? $this->parent_id : $this->id),
                'model_name'       => 'challenge',
                'is_visible'       => 0,
            ];

            $groupModel = new Group();
            $group      = $groupModel->storeEntity($groupPayload);

            if (!empty($group)) {
                if (!empty($this->getFirstMediaUrl('logo'))) {
                    $media     = $this->getFirstMedia('logo');
                    $imageData = explode(".", $media->file_name);
                    $name      = $group->id . '_' . \time();
                    $group->clearMediaCollection('logo')
                        ->addMediaFromUrl(
                            $this->getFirstMediaUrl('logo'),
                            $this->getAllowedMediaMimeTypes('logo')
                        )
                        ->usingName($media->name)
                        ->usingFileName($name . '.' . $imageData[1])
                        ->toMediaCollection('logo', config('medialibrary.disk_name'));
                }

                \dispatch(new SendGroupPushNotification($group, "user-assigned-group"));
            }
        }
    }

    public function archiveAutoGenerateGroups()
    {
        Group::select('groups.id')
            ->where('model_name', 'challenge')
            ->where('model_id', $this->id)
            ->where('is_visible', 1)
            ->where('is_archived', 0)
            ->get()
            ->each(function ($group) {
                $group->update(['is_archived' => 1]);
            });
    }

    public function checkExecutionRequired()
    {
        $challengeExecuteStatus = false;

        if (!empty($this->freezed_data_at)) {
            $challengeRulesData = $this->challengeRules()->join('challenge_targets', 'challenge_targets.id', '=', 'challenge_rules.challenge_target_id')->select('challenge_rules.*', 'challenge_targets.short_name', 'challenge_targets.name')->get();

            $userIds = array();

            if ($this->challenge_type == "individual") {
                $userIds = $this->members->pluck('id')->toArray();
            } else {
                $userIds = ChallengeParticipant::join('user_team', 'user_team.team_id', '=', 'challenge_participants.team_id')
                    ->where("challenge_participants.challenge_id", $this->id)
                    ->pluck('user_team.user_id')
                    ->toArray();
            }

            if (!empty($userIds)) {
                $insertedStepsData = false;
                foreach ($challengeRulesData as $rule) {
                    $checkDataSync = array();
                    if (($rule->short_name == 'distance' || $rule->short_name == 'steps') && !$insertedStepsData) {
                        $insertedStepsData = true;
                        $checkDataSync     = UserStep::whereIn("user_id", $userIds)
                            ->where("created_at", ">=", $this->freezed_data_at)
                            ->first();
                    } elseif ($rule->short_name == 'meditations') {
                        $checkDataSync = UserListenedTrack::whereIn("user_id", $userIds)
                            ->where("created_at", ">=", $this->freezed_data_at)
                            ->first();
                    } elseif ($rule->short_name == 'exercises' && $rule->model_name == 'Exercise') {
                        $checkDataSync = UserExercise::whereIn("user_id", $userIds)
                            ->where("created_at", ">=", $this->freezed_data_at)
                            ->where('user_exercise.exercise_id', $rule->model_id)
                            ->first();
                    }

                    if (!empty($checkDataSync)) {
                        $challengeExecuteStatus = true;
                    }
                }
            }
        } else {
            $challengeExecuteStatus = true;
        }

        return $challengeExecuteStatus;
    }

    public function exportDataEntity($payload)
    {
        $user         = auth()->user();
        $challengeId  = $payload['challengeId'];
        $explodeRoute = explode('.', \Route::currentRouteName());
        $route        = $explodeRoute[1];
        Telescope::stopRecording();

        $challenge    = self::find($challengeId);
        $participants = [];
        if (!empty($payload['exportFrom']) && $payload['exportFrom'] == 'challenge-detail') {
            // Export data from challenge details
            if ($route != 'challenges') {
                // Intercompany, team and company goal challenge
                if ($route == 'interCompanyChallenges') {
                    $companiesWithPoints     = $challenge->getCompanyMembersTableData($payload);
                    $participants['company'] = $companiesWithPoints;
                }
                $teamsWithPoints         = $challenge->getTeamMembersTableData($payload);
                $membersWithPoints       = $challenge->getMembersOthersTableData($payload);
                $participants['team']    = $teamsWithPoints;
                $participants['members'] = $membersWithPoints;
            } elseif ($route == 'challenges') {
                // Individual challenge
                $individualChallegeMembersData = $challenge->getMembersTableData($payload);
                $participants['members']       = $individualChallegeMembersData;
            }
            \dispatch(new ExportChallengeDetailJob($challenge, $user, $payload, $participants));
            return true;
        } else {
            $challengeRulesData = $challenge->challengeRules()->join('challenge_targets', 'challenge_targets.id', '=', 'challenge_rules.challenge_target_id')->select('challenge_targets.short_name', 'challenge_targets.name')->first();

            if ($challengeRulesData) {
                $columnName = "user_step." . $challengeRulesData->short_name;
                $targetType = $challengeRulesData->name;

                $getChallengeUser = DB::table('freezed_challenge_steps')
                    ->select('user_id')
                    ->WHERE('challenge_id', $challengeId)
                    ->groupBy('user_id')
                    ->get()->pluck('user_id')->toArray();

                $getChallengeUser = (!empty($getChallengeUser)) ? $getChallengeUser : [0];

                if ($getChallengeUser) {
                    // Generate intercompany challenge export report
                    \dispatch(new IntercompanyChallengeJob($getChallengeUser, $challenge, $targetType, $payload, $user, $columnName));
                    return true;
                }
            }
        }

        return false;
    }
    /**
     * @param
     *
     * @return array
     */
    public function getChallengeCreatedData(): array
    {
        $return  = [];
        $creator = User::find($this->creator_id);

        if (!empty($creator)) {
            $return['id']    = $creator->getKey();
            $return['name']  = $creator->full_name;
            $return['image'] = $creator->getMediaData('logo', ['w' => 320, 'h' => 320, 'mI' => 1]);
        }

        return $return;
    }

    /**
     * @param
     *
     * @return boolean
     */
    public function cancelRecord($payload)
    {
        $user           = auth()->user();
        $challengeInput = [
            'cancelled'      => 1,
            'deleted_by'     => $user->id,
            'deleted_reason' => $payload['reason'],
        ];
        $updated = $this->update($challengeInput);

        if ($updated) {
            return array('cancellation' => true);
        }
        return array('cancellation' => 'error');
    }

    /**
     * @param $payload
     *
     * @return boolean
     */
    public function getDepartment($payload, $companyId)
    {
        $locationArray = isset($payload['value']) ? $payload['value'] : [];
        $departments   = [];
        if (!empty($locationArray)) {
            $companyDepartment = DepartmentLocation::whereIn('company_location_id', $locationArray)
                ->where('company_id', $companyId)
                ->select('department_id')
                ->get()
                ->pluck('department_id')
                ->toArray();

            $departments = Department::whereIn('id', $companyDepartment)->select('id', 'name')->get()->pluck('name', 'id')->toArray();
        }
        return $departments;
    }

    /**
     * @param $payload
     *
     * @return boolean
     */
    public function getMemberData($payload, $companyId)
    {
        $departmentArray  = isset($payload['value']) ? $payload['value'] : [];
        $departmentsTeams = [];
        if (!empty($departmentArray)) {
            $getDepartment = Department::whereIn('id', $departmentArray)
                ->where('company_id', $companyId)
                ->get();

            if (!$getDepartment->isEmpty()) {
                foreach ($getDepartment as $department) {
                    $getTeams = $department->teams;
                    if (!$getTeams->isEmpty()) {
                        $teamsData = [];
                        foreach ($getTeams as $team) {
                            $members     = $team->users;
                            $membersData = [];
                            if (!$members->isEmpty()) {
                                foreach ($members as $member) {
                                    if ($member->can_access_app && !$member->is_blocked) {
                                        $membersData[] = [
                                            'id'   => $member->id,
                                            'name' => $member->first_name . " " . $member->last_name,
                                        ];
                                    }
                                }
                                $teamsData[] = [
                                    'id'      => $team->id,
                                    'name'    => $team->name,
                                    'code'    => 'code: ' . $team->code,
                                    'members' => $membersData,
                                ];
                            }
                        }
                        $departmentsTeams[] = [
                            'id'    => $department->id,
                            'name'  => $department->name,
                            'teams' => $teamsData,
                        ];
                    }
                }
            }
        }
        return $departmentsTeams;
    }

    public function exportICDataEntity($payload)
    {
        $user     = auth()->user();
        return \dispatch(new ExportInterCompanyReportJob($payload, $user));
    }

    public function exportChallengeActivityDataEntity($payload)
    {
        $user     = auth()->user();
        return \dispatch(new ExportChallengeActivityReportJob($payload, $user));
    }

    /**
     * Export Challenge User Activity
     *
     * @param $payload
     * @return array
     */
    public function exportChallengeUserActivity($payload)
    {
        $user    = auth()->user();
        $records = $this->getUserDailyHistoryRecordList($payload);
        $email   = ($payload['email'] ?? $user->email);
        if ($records) {
            // Generate Content export report
            \dispatch(new ExportChallengeUserActivityReportJob($records, $email, $user));
            return true;
        }
    }
}
