<?php

namespace App\Models;

use App\Jobs\BroadcastMessageToGroup;
use App\Models\Company;
use App\Models\Group;
use App\Models\User;
use App\Observers\BroadcastMessageObserver;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Yajra\DataTables\Facades\DataTables;

class BroadcastMessage extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'broadcast_messages';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = [
        'company_id',
        'user_id',
        'group_id',
        'group_type',
        'type',
        'status',
        'title',
        'message',
        'scheduled_at',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'scheduled_at',
    ];

    /**
     * Boot model
     */
    protected static function boot()
    {
        parent::boot();
        static::observe(BroadcastMessageObserver::class);
    }

    /**
     * "BelongsTo" relation to `companies` table
     * via `company_id` field.
     *
     * @return BelongsTo
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    /**
     * "BelongsTo" relation to `users` table
     * via `user_id` field.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * "BelongsTo" relation to `groups` table
     * via `group_id` field.
     *
     * @return BelongsTo
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class, 'group_id');
    }

    /**
     * Get groups by group type
     *
     * @return string
     */
    public function getGroups($payload)
    {
        $return    = "";
        $groups    = [];
        $user      = auth()->user();
        $type      = (!empty($payload['type']) ? $payload['type'] : '');
        $company   = $user->company()->select('companies.id')->first();
        $modelName = [
            'inter_company' => 'challenge',
            'team'          => 'challenge',
            'company_goal'  => 'challenge',
            'individual'    => 'challenge',
            'masterclass'   => 'masterclass',
        ];

        $groups = Group::select('groups.id', 'groups.title')
            ->when($type, function ($query, $groupType) use ($modelName, $company) {
                if (isset($modelName[$groupType])) {
                    $query->where('groups.model_name', $modelName[$groupType]);
                    if ($modelName[$groupType] == 'challenge') {
                        $query->join('challenges', function ($join) use ($groupType, $company) {
                            $join
                                ->on('challenges.id', '=', 'groups.model_id')
                                ->where('challenges.challenge_type', $groupType);
                            if (!is_null($company) && in_array($groupType, ['team', 'company_goal', 'individual'])) {
                                if ($groupType == 'individual') {
                                    $moderators = $company->moderators()->select('users.id')->get()->pluck('id')->toArray();
                                    $join->whereIn('challenges.creator_id', $moderators);
                                } else {
                                    $join->where('challenges.company_id', $company->id);
                                }
                            }
                        });
                    }

                    if ($modelName[$groupType] == 'masterclass' && !is_null($company)) {
                        $mcIds = MasterClassComapany::select('id', 'masterclass_id')
                                ->where('company_id', $company->id)
                                ->get()
                                ->pluck('masterclass_id');
                        $query->whereIn('groups.model_id', $mcIds);
                    }
                }

                if (!is_null($company) && in_array($groupType, ['private', 'public'])) {
                    $query
                        ->whereNull('groups.model_id')
                        ->whereNull('groups.model_name')
                        ->where('groups.type', $groupType)
                        ->where('groups.company_id', $company->id);
                }
            })
            ->where('groups.is_visible', 1)
            ->where('groups.is_archived', 0)
            ->orderByDesc('groups.id')
            ->get();

        $groups->each(function ($group) use (&$return, $type, $company) {
            if ($type == 'masterclass') {
                $count = $group->members()
                    ->join('user_team', 'user_team.user_id', '=', 'group_members.user_id')
                    ->when($company, function ($query, $co) {
                        $query->where('user_team.company_id', $co->id);
                    })
                    ->count();
                if ($count > 0) {
                    $return .= "<option value='{$group->id}'>{$group->title}</option>";
                }
            } else {
                $return .= "<option value='{$group->id}'>{$group->title}</option>";
            }
        });

        return $return;
    }

    /**
     * Set datatable for broadcast message list.
     *
     * @param payload
     * @return DataTables
     */
    public function getTableData($payload)
    {
        $appTimezone = config('app.timezone');
        $now         = now($appTimezone);
        $list        = $this->getRecordList($payload);
        return DataTables::of($list['record'])
            ->skipPaging()
            ->addColumn('message', function ($record) {
                $message = mb_strimwidth($record->message, 0, 150, '...');
                return "<span title='{$record->message}'>{$message}</span>";
            })
            ->addColumn('group_type', function ($record) {
                $groupType = config('zevolifesettings.broadcast_group_type');
                return $groupType[$record->group_type];
            })
            ->addColumn('status', function ($record) {
                $status = config('zevolifesettings.broadcast_status_type');
                return $status[$record->status];
            })
            ->addColumn('created_at', function ($record) use ($appTimezone) {
                return Carbon::parse("{$record->created_at}", $appTimezone)->toDateTimeString();
            })
            ->addColumn('actions', function ($record) use ($now, $appTimezone) {
                $diff = 0;
                if (!is_null($record->scheduled_at)) {
                    $scheduledAt = Carbon::parse("{$record->scheduled_at}", $appTimezone)->toDateTimeString();
                    $diff        = $now->diffInMinutes($scheduledAt, false);
                }
                $allowEdit = ($diff > 15 && $record->status == 1);
                return view('admin.broadcast-message.listaction', compact('record', 'allowEdit'))->render();
            })
            ->with([
                "recordsTotal"    => $list['total'],
                "recordsFiltered" => $list['total'],
            ])
            ->rawColumns(['message', 'actions'])
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
        $user    = auth()->user();
        $company = $user->company()->first();
        $query   = $this
            ->select(
                'broadcast_messages.id',
                'broadcast_messages.title',
                'broadcast_messages.message',
                'broadcast_messages.group_type',
                'broadcast_messages.group_id',
                'broadcast_messages.status',
                \DB::raw("IFNULL(groups.title, 'Group Deleted') AS group_name"),
                'broadcast_messages.created_at',
                'broadcast_messages.scheduled_at'
            )
            ->leftJoin('groups', 'groups.id', '=', 'broadcast_messages.group_id')
            ->where('broadcast_messages.company_id', (!is_null($company) ? $company->id : null))
            ->when($payload['title'], function ($query, $title) {
                $query->where('broadcast_messages.title', 'like', "%{$title}%");
            })
            ->when($payload['group_type'], function ($query, $groupType) {
                $query->where('broadcast_messages.group_type', $groupType);
            })
            ->when($payload['group_name'], function ($query, $groupName) {
                $query->where('groups.title', 'like', "%{$groupName}%");
            })
            ->when($payload['status'], function ($query, $status) {
                $query->where('broadcast_messages.status', $status);
            });

        if (isset($payload['order'])) {
            $column = $payload['columns'][$payload['order'][0]['column']]['data'];
            $order  = $payload['order'][0]['dir'];
            $query->orderBy($column, $order);
        } else {
            $query->orderByDesc('broadcast_messages.id');
        }

        return [
            'total'  => $query->count('broadcast_messages.id'),
            'record' => $query->offset($payload['start'])->limit($payload['length'])->get(),
        ];
    }

    /**
     * To store a broadcase message.
     *
     * @param array payload
     * @return boolean
     */
    public function storeEntity(array $payload)
    {
        $user        = auth()->user();
        $appTimezone = config('app.timezone');
        $timezone    = (!empty($user->timezone) ? $user->timezone : $appTimezone);
        $company     = $user->company()->select('companies.id')->first();
        $type        = ((isset($payload['instant_broadcast']) && $payload['instant_broadcast'] == 'on') ? 'instant' : 'scheduled');

        $message = $this->create([
            'company_id'   => (!is_null($company) ? $company->id : null),
            'user_id'      => $user->id,
            'group_id'     => $payload['group'],
            'group_type'   => $payload['group_type'],
            'title'        => $payload['title'],
            'message'      => $payload['message'],
            'type'         => $type,
            'scheduled_at' => (($type == 'scheduled') ? Carbon::parse($payload['schedule_date_time'], $timezone)->setTimezone($appTimezone)->toDateTimeString() : null),
            'status'       => ($type == 'instant' ? 2 : 1),
        ]);

        if ($message) {
            if ($message->type == 'instant') {
                // Dispatch job to broadcast message to group
                dispatch(new BroadcastMessageToGroup($message));
            }

            return [
                'message' => (($message->type == 'instant') ? 'Message Broadcasted successfully' : 'Broadcast Message scheduled successfully'),
            ];
        }
        return false;
    }

    /**
     * To update a broadcase message.
     *
     * @param  array $payload
     * @return booelan
     */
    public function updateEntity(array $payload)
    {
        $user        = auth()->user();
        $appTimezone = config('app.timezone');
        $timezone    = (!empty($user->timezone) ? $user->timezone : $appTimezone);
        $type        = ((isset($payload['instant_broadcast']) && $payload['instant_broadcast'] == 'on') ? 'instant' : 'scheduled');

        $message = $this->update([
            'title'        => $payload['title'],
            'message'      => $payload['message'],
            'type'         => $type,
            'scheduled_at' => (($type == 'scheduled') ? Carbon::parse($payload['schedule_date_time'], $timezone)->setTimezone($appTimezone)->toDateTimeString() : null),
            'status'       => ($type == 'instant' ? 2 : 1),
        ]);

        if ($message) {
            if ($this->type == 'instant') {
                // Dispatch job to broadcast message to group
                dispatch(new BroadcastMessageToGroup($this));
            }

            return [
                'message' => (($this->type == 'instant') ? 'Message Broadcasted successfully' : 'Broadcast Message scheduled successfully'),
            ];
        }
        return false;
    }

    /**
     * To delete broadcast message
     *
     * @return array
     */
    public function deleteRecord()
    {
        $data = ['deleted' => false];
        if ($this->delete()) {
            $data['deleted'] = true;
        }
        return $data;
    }
}
