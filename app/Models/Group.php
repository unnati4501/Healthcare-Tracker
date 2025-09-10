<?php

namespace App\Models;

use App\Jobs\SendGroupPushNotification;
use App\Models\BroadcastMessage;
use App\Models\Company;
use App\Models\SubCategory;
use App\Observers\GroupObserver;
use Carbon\Carbon;
use DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Yajra\DataTables\Facades\DataTables;

class Group extends Model implements HasMedia
{

    use InteractsWithMedia;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'groups';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = [
        'creator_id',
        'company_id',
        'category_id',
        'sub_category_id',
        'model_id',
        'model_name',
        'expertise_level',
        'title',
        'type',
        'description',
        'is_visible',
        'is_archived',
        'motive',
        'who_can_join',
        'created_by',
        'deep_link_uri',
        'created_at',
        'updated_at',
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
    protected $casts = [];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [];

    /**
     * Boot model
     */
    protected static function boot()
    {
        parent::boot();

        static::observe(GroupObserver::class);
    }

    /**
     * @return BelongsTo
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo('App\Models\Category', 'category_id');
    }

    /**
     * @return BelongsTo
     */
    public function subcategory(): BelongsTo
    {
        return $this->belongsTo('App\Models\SubCategory', 'sub_category_id');
    }

    /**
     * @return BelongsToMany
     */
    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'group_members', 'group_id', 'user_id')->withPivot('status', 'joined_date', 'left_date', 'notification_muted')->withTimestamps();
    }

    /**
     * "hasMany" relation to `broadcast_messages` table
     * via `group_id` field.
     *
     * @return hasMany
     */
    public function broadcast(): hasMany
    {
        return $this->hasMany(BroadcastMessage::class, 'group_id');
    }

    /**
     * @return string
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversion
     */
    public function getLogoAttribute()
    {
        return $this->getLogo(['w' => 100, 'h' => 100]);
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
        return getThumbURL($params, 'group', 'logo');
    }

    /**
     * Set datatable for groups list.
     *
     * @param payload
     * @return dataTable
     */

    public function getTableData($payload)
    {
        $list = $this->getRecordList($payload);

        return DataTables::of($list)
            ->addColumn('updated_at', function ($record) {
                return $record->updated_at;
            })
            ->addColumn('category', function ($record) {
                return $record->subcategory->name;
            })
            ->addColumn('type', function ($record) {
                return ucfirst($record->type);
            })
            ->addColumn('logo', function ($record) {
                if (!empty($record->logo)) {
                    return '<div class="table-img table-img-l"><img src="' . $record->logo . '" alt=""></div>';
                } else {
                    return '<div class="table-img table-img-l"><img src="' . asset('assets/dist/img/boxed-bg.png') . '" alt=""></div>';
                }
            })
            ->addColumn('members', function ($record) {
                return $record->members()
                    ->join('user_team', 'user_team.user_id', '=', 'group_members.user_id')
                    ->where('user_team.company_id', auth()->user()->company->first()->id)
                    ->count();
            })
            ->addColumn('is_archived', function ($record) {
                return $record->is_archived ? 'Yes' : 'No';
            })
            ->addColumn('actions', function ($record) {
                return view('admin.group.listaction', compact('record'))->render();
            })
            ->rawColumns(['actions', 'logo'])
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
        $user = auth()->user();

        $query = self::leftJoin('sub_categories', 'groups.sub_category_id', '=', 'sub_categories.id')
            ->select('groups.id', 'groups.company_id', 'groups.sub_category_id', 'groups.title', 'groups.description', 'groups.is_archived', 'groups.updated_at', 'sub_categories.short_name', 'groups.type', 'groups.created_by')
            ->where(function ($query) use ($user) {
                if (!is_null($user->company->first())) {
                    return $query->where('groups.company_id', $user->company->first()->id)
                        ->orWhere('groups.company_id', null);
                } else {
                    return $query;
                }
            })
            ->where(function ($query) use ($payload) {
                if (isset($payload) && $payload['main'] == 0) {
                    return $query->whereIn('sub_categories.short_name', ['masterclass', 'challenge']);
                } else {
                    return $query->whereNotIn('sub_categories.short_name', ['masterclass', 'challenge']);
                }
            })
            ->orderBy('groups.updated_at', 'DESC');

        if (in_array('groupName', array_keys($payload)) && !empty($payload['groupName'])) {
            $query->where('groups.title', 'like', '%' . $payload['groupName'] . '%');
        }

        if (in_array('sub_category', array_keys($payload)) && !empty($payload['sub_category'])) {
            $query->where('groups.sub_category_id', $payload['sub_category']);
        }

        if (in_array('is_archived', array_keys($payload)) && !is_null($payload['is_archived'])) {
            $query->where('groups.is_archived', $payload['is_archived']);
        }

        if (in_array('group_type', array_keys($payload)) && !empty($payload['group_type'])) {
            if ($payload['group_type'] == 'all') {
                return $query;
            }
            $query->where('type', $payload['group_type']);
        }

        return $query->get()->filter(function ($value) use ($payload) {
            if ($payload['main'] == 0) {
                return $value->members()
                    ->join('user_team', 'user_team.user_id', '=', 'group_members.user_id')
                    ->where('user_team.company_id', auth()->user()->company->first()->id)
                    ->count() > 0;
            } else {
                return $value;
            }
        });
    }

    /**
     * store record data.
     *
     * @param payload
     * @return boolean
     */

    public function storeEntity(array $payload)
    {
        $user             = auth()->user();
        $checkGroupAccess = getCompanyPlanAccess($user, 'group');
        if (isset($payload['model_id']) && !is_null($payload['model_id'])) {
            $groupExists = self::where('model_id', $payload['model_id'])
                ->where('model_name', $payload['model_name'])
                ->first();

            if (!empty($groupExists)) {
                return;
            }

            $user = null;
        }

        $groupInput = [
            'creator_id'      => !is_null($user) ? $user->id : 1,
            'company_id'      => (!is_null($user) && !is_null($user->company->first())) ? $user->company->first()->id : null,
            'sub_category_id' => $payload['category'],
            'title'           => $payload['name'],
            'description'     => $payload['introduction'],
            'type'            => isset($payload['type']) ? $payload['type'] : 'private',
            'model_id'        => $payload['model_id'] ?? null,
            'model_name'      => $payload['model_name'] ?? null,
            'is_visible'      => $payload['is_visible'] ?? 1,
        ];

        $groups = self::create($groupInput);

        if (isset($payload['logo']) && !empty($payload['logo'])) {
            $name = $groups->id . '_' . \time();
            $groups->clearMediaCollection('logo')->addMediaFromRequest('logo')
                ->usingName($payload['logo']->getClientOriginalName())
                ->usingFileName($name . '.' . $payload['logo']->extension())
                ->toMediaCollection('logo', config('medialibrary.disk_name'));
        }

        if ($groups) {
            $members = $payload['members_selected'];

            $membersInput   = [];
            $membersInput[] = [
                'user_id'     => $user->id ?? 1,
                'group_id'    => $groups->id,
                'status'      => "Accepted",
                'joined_date' => now()->toDateTimeString(),
            ];

            foreach ($members as $value) {
                if ($value != ($user->id ?? 1)) {
                    $membersInput[] = [
                        'user_id'     => $value,
                        'group_id'    => $groups->id,
                        'status'      => "Accepted",
                        'joined_date' => now()->toDateTimeString(),
                    ];
                }
            }
            $groups->members()->sync($membersInput);

            if ($groups) {
                // dispatch job to send push notification to all user when group created
                if ($groups->is_visible && $checkGroupAccess) {
                    \dispatch(new SendGroupPushNotification($groups, "user-assigned-group"));
                    return true;
                } else {
                    return $groups;
                }
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * update record data.
     *
     * @param payload
     * @return boolean
     */

    public function updateEntity(array $payload)
    {
        $newMembers = $payload['members_selected'] ?? $this->members()->pluck('users.id')->toArray();
        $oldMembers = $this->members()->pluck('users.id')->toArray();

        $addNotificationsToMembers = \array_diff($newMembers, $oldMembers);
        $user                      = auth()->user();
        $checkGroupAccess          = getCompanyPlanAccess($user, 'group');
        $data                      = [
            'sub_category_id' => $payload['category'],
            'title'           => $payload['name'],
            'description'     => $payload['introduction'],
            'type'            => isset($payload['type']) ? $payload['type'] : 'private',
        ];

        $updated = $this->update($data);

        if (isset($payload['logo']) && !empty($payload['logo'])) {
            $name = $this->id . '_' . \time();
            $this->clearMediaCollection('logo')->addMediaFromRequest('logo')
                ->usingName($payload['logo']->getClientOriginalName())
                ->usingFileName($name . '.' . $payload['logo']->extension())
                ->toMediaCollection('logo', config('medialibrary.disk_name'));
        }

        if ($updated) {
            $members = $payload['members_selected'] ?? $this->members()->pluck('users.id')->toArray();

            $membersInput = [];

            $membersInput[$this->creator_id] = [
                'user_id'     => $this->creator_id,
                'group_id'    => $this->id,
                'status'      => "Accepted",
                'joined_date' => now()->toDateTimeString(),
            ];

            foreach ($members as $value) {
                if ($value != $this->creator_id) {
                    $membersInput[$value] = [
                        'user_id'     => $value,
                        'group_id'    => $this->id,
                        'status'      => "Accepted",
                        'joined_date' => now()->toDateTimeString(),
                    ];
                }
            }
            $this->members()->sync($membersInput);

            if ($updated) {
                if (!empty($addNotificationsToMembers) && $checkGroupAccess) {
                    // dispatch job to send push notification to all new added user in current group
                    \dispatch(new SendGroupPushNotification($this, "user-assigned-updated-group", "", "", $addNotificationsToMembers));
                }

                return true;
            } else {
                return false;
            }
        }

        return false;
    }

    /**
     * get group edit data.
     *
     * @param  $id
     * @return array
     */

    public function groupEditData()
    {
        $data                = array();
        $data['groupData']   = $this;
        $data['id']          = $this->id;
        $data['creatorData'] = User::find($this->creator_id);

        if (in_array($this->subcategory->short_name, ['masterclass', 'challenge'])) {
            $data['categories'] = SubCategory::where(['category_id' => 3])
                ->where('status', 1)
                ->where('is_excluded', 1)
                ->orderBy('id', 'ASC')
                ->get()
                ->pluck('name', 'id')
                ->toArray();
        } else {
            $data['categories'] = $this->subcategories();
        }

        $data['groupUserData'] = $this->members->pluck('id')->toArray();
        $data['companyData']   = $this->getTeamMembersData();
        $data['description']   = trim(html_entity_decode(strip_tags($this->description)), " \t\n\r\0\x0B\xC2\xA0");

        return $data;
    }

    public function getTeamMembersData($create = false)
    {
        $companies = Company::where('subscription_start_date', '<=', Carbon::now())
            ->where('allow_app', 1);
        if ($create) {
            $companies->where('subscription_end_date', '>=', Carbon::now());
        }

        $companies = $companies->get();

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
                            $explodeRoute = explode('.', \Route::currentRouteName());
                            $route        = $explodeRoute[1];

                            if ($route != 'teamChallenges' && $route != 'companyGoalChallenges' && $route != 'interCompanyChallenges') {
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
                                }

                                $teamsData[] = [
                                    'id'      => $team->id,
                                    'name'    => $team->name,
                                    'code'    => 'code: ' . $team->code,
                                    'members' => $membersData,
                                ];
                            } else {
                                if (!$team->default && !$team->users->isEmpty()) {
                                    $teamsData[] = [
                                        'id'   => $team->id,
                                        'name' => $team->name,
                                        'code' => 'code: ' . $team->code,
                                    ];
                                }
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
                    'id'            => $company->id,
                    'name'          => $company->name,
                    'companyStatus' => !($company->subscription_end_date <= Carbon::now()),
                    'departments'   => $departmentsTeams,
                ];
            }
        }
        return $companyData;
    }

    /**
     * delete record by record id.
     *
     * @param $id
     * @return array
     */

    public function deleteRecord()
    {
        $user             = auth()->user();
        $checkGroupAccess = getCompanyPlanAccess($user, 'group');
        $this->clearMediaCollection('logo');
        
        if ($checkGroupAccess || $this->created_by == 'User') {
            // dispatch job to send push notification to all user when group is deleted
            \dispatch(new SendGroupPushNotification($this, "group-deleted"));
        }

        if ($this->delete()) {
            return array('deleted' => 'true');
        }
        return array('deleted' => 'error');
    }

    /**
     * get location data table.
     *
     * @param $payload , $id
     * @return array
     */

    public function getMembersTableData()
    {
        $list = $this->members()
            ->select('users.id', 'users.first_name', 'users.last_name', 'users.email')
            ->join('user_team', 'user_team.user_id', '=', 'group_members.user_id')
            ->where('user_team.company_id', auth()->user()->company->first()->id)
            ->get();

        return DataTables::of($list)
            ->addColumn('name', function ($record) {
                return $record->first_name . " " . $record->last_name;
            })
            ->addColumn('updated_at', function ($record) {
                return $record->updated_at;
            })
            ->make(true);
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

        if (!is_null($media) && $media->count() > 1) {
            $param['src'] = $this->getFirstMediaUrl($collection, ($param['conversion'] ?? ''));
        }
        $return['url'] = getThumbURL($param, 'group', $collection);
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
            $return['image'] = $creator->getMediaData('logo', ['w' => 320, 'h' => 320, 'zc' => 3]);
        }

        return $return;
    }

    /**
     * @param
     *
     * @return array
     */
    public function getCategoryData(): array
    {
        $return = [];

        $return['id']   = $this->category->id;
        $return['name'] = $this->category->name;

        return $return;
    }

    /**
     * @return BelongsToMany
     */
    public function groupReports(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_group_reports', 'group_id', 'user_id')->withPivot('reason', 'message')->withTimestamps();
    }

    /**
     * @return HasMany
     */
    public function groupMessages(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'group_messages', 'group_id', 'user_id')->withPivot('id', 'message', 'model_id', 'model_name', 'group_message_id', 'forwarded', 'deleted', 'type', 'is_broadcast', 'broadcast_company_id')->withTimestamps();
    }

    /**
     * @return HasMany
     */
    public function groupMessagesUserDeleteLog(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'group_messages_user_delete_log', 'group_id', 'user_id')->withTimestamps();
    }

    /**
     * get location data table.
     *
     * @param $payload , $id
     * @return array
     */

    public function getReportAbuseTableData($payload)
    {
        $user = auth()->user();

        $list = $this->groupReports()
            ->join('user_team', 'user_team.user_id', '=', 'user_group_reports.user_id')
            ->where('user_team.company_id', $user->company()->first()->getKey());

        if (in_array('userName', array_keys($payload)) && !empty($payload['userName'])) {
            $list->where(DB::raw("CONCAT(users.first_name,' ',users.last_name)"), 'like', '%' . $payload['userName'] . '%')->orWhere('users.email', 'like', '%' . $payload['userName'] . '%');
        }

        $list = $list->get();
        return DataTables::of($list)
            ->addColumn('fullName', function ($record) {
                return $record->full_name;
            })
            ->addColumn('reason', function ($record) {
                return $record->pivot->reason;
            })
            ->addColumn('message', function ($record) {
                return $record->pivot->message;
            })
            ->addColumn('updated_at', function ($record) {
                return $record->updated_at;
            })
            ->make(true);
    }

    /**
     * get group subcategories.
     *
     * @param none
     * @return mixed
     */
    public function subcategories()
    {
        return SubCategory::where('category_id', 3)
            ->where('status', 1)
            ->where('is_excluded', 0)
            ->orderBy('id', 'ASC')
            ->get()
            ->pluck('name', 'id')
            ->toArray();
    }
}
