<?php

namespace App\Models;

use App\Events\SendEmailConsentEvent;
use App\Jobs\ExportClientJob;
use App\Jobs\ExportClientUserNotesReportJob;
use App\Jobs\ExportDigitalTherapyReportJob;
use App\Jobs\SendConsentPushNotification;
use App\Jobs\SendGroupSessionPushNotification;
use App\Models\Company;
use App\Models\CompanyDigitalTherapy;
use App\Models\ScheduleUsers;
use App\Models\ServiceSubCategory;
use App\Models\User;
use App\Models\WsClientNote;
use Auth;
use Carbon\Carbon;
use DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Yajra\DataTables\Facades\DataTables;

class CronofySchedule extends Model implements HasMedia
{
    use InteractsWithMedia;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'cronofy_schedule';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = [
        'event_id',
        'scheduling_id',
        'name',
        'created_by',
        'user_id',
        'company_id',
        'ws_id',
        'service_id',
        'topic_id',
        'is_group',
        'is_consent',
        'event_identifier',
        'token',
        'start_time',
        'end_time',
        'location',
        'notes',
        'user_notes',
        'event_created_at',
        'cancelled_by',
        'cancelled_at',
        'cancelled_reason',
        'reminder_at',
        'meta',
        'timezone',
        'status',
        'no_show',
        'score',
        'is_reminder_sent',
        'location_id',
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'meta' => 'object',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['created_at', 'updated_at'];

    /**
     * Custom builder instantiator. newEloquentBuilder is part
     * of Laravel.
     */
    public function newEloquentBuilder($query)
    {
        return new \App\Builders\BaseBuilder($query);
    }

    /**
     * One-to-Many relations with Users.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function scheduleUsers(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\User', 'session_group_users', 'session_id', 'user_id');
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
     * "BelongsTo" relation to `users` table
     * via `cancelled_by` field.
     *
     * @return BelongsTo
     */
    public function cancelledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    /**
     * "BelongsTo" relation to `users` table
     * via `ws_id` field.
     *
     * @return BelongsTo
     */
    public function wellbeingSpecialist(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ws_id');
    }

    /**
     * "BelongsTo" relation to `session_group_user` table
     * via `session_id` field.
     *
     * @return BelongsTo
     */
    public function sessionGroupUser(): BelongsTo
    {
        return $this->belongsTo(ScheduleUsers::class, 'session_id');
    }

    /**
     * One-to-Many relations with Feed.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function inviteSequence(): belongsToMany
    {
        return $this->belongsToMany(User::class, 'session_invite_sequence_user_logs', 'session_id', 'user_id')->withPivot('sequence');
    }

    /**
     * 'hasMany' relation using 'ws_client_comments'
     * table via 'ticket_id' field.
     *
     * @return hasMany
     */
    public function comments(): HasMany
    {
        return $this->hasMany(WsClientNote::class, 'cronofy_schedule_id');
    }

    /**
     * One-to-Many relations with session attachments.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function sessionAttachments(): HasMany
    {
        return $this->hasMany(CronofySessionAttachments::class, 'cronofy_schedule_id');
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

        $xDeviceOs = strtolower(Request()->header('X-Device-Os', ""));
        if ($collection == 'audio_background' && $xDeviceOs == config()->get('zevolifesettings.PORTAL')) {
            $collection = 'audio_background_portal';
        }

        $media = $this->getFirstMedia($collection);

        if (($collection == 'audio_background_portal' || $collection == 'audio_background') && is_null($media) && empty($media)) {
            $collection = ($xDeviceOs == config()->get('zevolifesettings.PORTAL')) ? 'audio_background' : 'audio_background_portal';
            $media      = $this->getFirstMedia($collection);
        }

        if (!is_null($media) && $media->count() > 1) {
            $param['src'] = $this->getFirstMediaUrl($collection, ($param['conversion'] ?? ''));
        }

        if ($media->mime_type == 'image/gif') {
            $return['url'] = $this->getFirstMediaUrl($collection);
        } else {
            $return['url'] = getThumbURL($param, 'feed', $collection);
        }

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
        $creator = User::find($this->user_id);

        if (!empty($creator)) {
            $return['id']    = $creator->getKey();
            $return['name']  = $creator->full_name;
            $return['image'] = $creator->getMediaData('logo', ['w' => 320, 'h' => 320]);
        }

        return $return;
    }

    /**
     * @param
     *
     * @return array
     */
    public function getWellbeingSpecialistData(): array
    {
        $return  = [];
        $creator = User::find($this->ws_id);

        if (!empty($creator)) {
            $return['id']    = $creator->getKey();
            $return['name']  = $creator->full_name;
            $return['image'] = $creator->getMediaData('logo', ['w' => 320, 'h' => 320]);
        }

        return $return;
    }

    /**
     * Get client list
     *
     * @param array payload
     * @return dataTable
     */
    public function getTableData($payload)
    {
        $user        = auth()->user();
        $role        = getUserRole($user);
        $list        = $this->getRecordList($payload);
        return DataTables::of($list['record'])
            ->skipPaging()
            ->with([
                "recordsTotal"    => $list['total'],
                "recordsFiltered" => $list['total'],
            ])
            ->addColumn('upcoming', function ($record) use ($role)  {
                if ($role->slug == 'wellbeing_specialist') {
                    return numberFormatShort($record->upcomingws);
                } else {
                    return numberFormatShort($record->upcomingsa);
                }
            })
            ->addColumn('completed_session', function ($record) use ($role)  {
                if ($role->slug == 'wellbeing_specialist') {
                    return numberFormatShort($record->completed_sessionws);
                } else {
                    return numberFormatShort($record->completed_sessionsa);
                }
            })
            ->addColumn('cancelled_session', function ($record) {
                return numberFormatShort($record->cancelled_sessions);
            })
            ->addColumn('short_cancel', function ($record) {
                return numberFormatShort($record->short_cancel);
            })
            ->addColumn('actions', function ($record) use ($role) {
                return view('admin.cronofy.clientlist.listaction', compact('record', 'role'))->render();
            })
            ->rawColumns(['actions'])
            ->make(true);
    }

    /**
     * get client list data table
     *
     * @param array payload
     * @return array
     */
    public function getRecordList($payload)
    {
        $user        = auth()->user();
        $role        = getUserRole($user);
        $appTimezone = config('app.timezone');
        $timezone    = (!empty($user->timezone) ? $user->timezone : $appTimezone);
        $now         = now($timezone)->toDateTimeString();

        $query = $this
            ->select(
                'cronofy_schedule.id',
                'cronofy_schedule.ws_id',
                'cronofy_schedule.is_group',
                \DB::raw("(SELECT CONCAT(users.first_name, ' ', users.last_name) as wellbeing_specialist FROM users WHERE cronofy_schedule.ws_id = users.id) AS wellbeing_specialist"),
                \DB::raw("CONCAT(users.first_name, ' ', users.last_name) AS client_name"),
                'users.email',
                'company_locations.name as location_name',
                'companies.name AS company_name',
                \DB::raw("(SELECT IFNULL(count('cronofy_schedule.id'), 0) FROM cronofy_schedule left join session_group_users 
                on session_group_users.session_id = cronofy_schedule.id 
                join users as ws on ws.id = cronofy_schedule.ws_id WHERE session_group_users.user_id = users.id AND cronofy_schedule.cancelled_at IS NOT NULL AND ws.deleted_at IS NULL AND (cronofy_schedule.status = 'canceled' OR cronofy_schedule.status = 'rescheduled')) as cancelled_sessions"),
                \DB::raw("(SELECT IFNULL(count('cronofy_schedule.id'), 0) FROM cronofy_schedule left join session_group_users 
                on session_group_users.session_id = cronofy_schedule.id 
                join users as ws on ws.id = cronofy_schedule.ws_id WHERE session_group_users.user_id = users.id AND ws.deleted_at IS NULL AND cronofy_schedule.no_show = 'Yes' AND cronofy_schedule.status = 'booked') AS no_show"),
                \DB::raw("(SELECT IFNULL(count('cronofy_schedule.id'), 0) FROM cronofy_schedule left join session_group_users 
                on session_group_users.session_id = cronofy_schedule.id
                join users as ws on ws.id = cronofy_schedule.ws_id WHERE session_group_users.user_id = users.id AND ws.deleted_at IS NULL AND cronofy_schedule.cancelled_at IS NOT NULL AND cronofy_schedule.status = 'short_canceled') as short_cancel"),
            )
            ->selectRaw("(SELECT IFNULL(count('cronofy_schedule.id'), 0) FROM cronofy_schedule left join session_group_users 
                on session_group_users.session_id = cronofy_schedule.id 
                join users as ws on ws.id = cronofy_schedule.ws_id WHERE session_group_users.user_id = users.id AND cronofy_schedule.cancelled_at IS NULL AND ws.deleted_at IS NULL AND CONVERT_TZ(cronofy_schedule.start_time, ?, ?) >= ? AND cronofy_schedule.ws_id = ? AND (cronofy_schedule.status = 'booked' OR cronofy_schedule.status = 'upcoming' )) AS upcomingws",[
                    'UTC',$timezone,$now,$user->id
                ])
            ->selectRaw("(SELECT IFNULL(count('cronofy_schedule.id'), 0) FROM cronofy_schedule left join session_group_users 
                on session_group_users.session_id = cronofy_schedule.id 
                join users as ws on ws.id = cronofy_schedule.ws_id WHERE session_group_users.user_id = users.id AND cronofy_schedule.cancelled_at IS NULL AND ws.deleted_at IS NULL AND  CONVERT_TZ(cronofy_schedule.start_time, ?, ?) >= ? AND (cronofy_schedule.status = 'booked' OR cronofy_schedule.status = 'upcoming' )) AS upcomingsa",[
                    'UTC',$timezone,$now
                ])
            ->selectRaw("(SELECT IFNULL(count('cronofy_schedule.id'), 0) FROM cronofy_schedule left join session_group_users 
                on session_group_users.session_id = cronofy_schedule.id 
                join users as ws on ws.id = cronofy_schedule.ws_id WHERE session_group_users.user_id = users.id AND cronofy_schedule.cancelled_at IS NULL AND ws.deleted_at IS NULL AND (CONVERT_TZ(cronofy_schedule.end_time, ?,  ?) <= ? or cronofy_schedule.status = 'completed') AND cronofy_schedule.status NOT IN ('canceled', 'rescheduled', 'open', 'short_canceled') and cronofy_schedule.no_show = 'No' AND cronofy_schedule.ws_id = ?) as completed_sessionws",[
                    'UTC',$timezone,$now,$user->id
                ])
            ->selectRaw("(SELECT IFNULL(count('cronofy_schedule.id'), 0) FROM cronofy_schedule left join session_group_users 
                on session_group_users.session_id = cronofy_schedule.id 
                join users as ws on ws.id = cronofy_schedule.ws_id
                WHERE session_group_users.user_id = users.id
                 AND cronofy_schedule.cancelled_at IS NULL AND (CONVERT_TZ(cronofy_schedule.end_time, ?,  ?) <= ? or cronofy_schedule.status = 'completed') AND ws.deleted_at IS NULL AND cronofy_schedule.status NOT IN ('canceled', 'rescheduled', 'open', 'short_canceled') and cronofy_schedule.no_show = 'No') as completed_sessionsa",[
                    'UTC',$timezone,$now
                 ])
            ->selectRaw("(SELECT IFNULL(count('cronofy_schedule.id'), 0) FROM cronofy_schedule left join session_group_users 
                 on session_group_users.session_id = cronofy_schedule.id 
                 join users as ws on ws.id = cronofy_schedule.ws_id WHERE session_group_users.user_id = users.id AND cronofy_schedule.cancelled_at IS NOT NULL AND ws.deleted_at IS NULL AND (cronofy_schedule.status = 'canceled' OR cronofy_schedule.status = 'rescheduled') AND cronofy_schedule.ws_id = ?) as cancelled_sessionsws",[
                    $user->id
                 ])
            ->selectRaw("(SELECT IFNULL(count('cronofy_schedule.id'), 0) FROM cronofy_schedule left join session_group_users 
                 on session_group_users.session_id = cronofy_schedule.id 
                 join users as ws on ws.id = cronofy_schedule.ws_id WHERE session_group_users.user_id = users.id AND ws.deleted_at IS NULL AND cronofy_schedule.no_show = 'Yes' AND cronofy_schedule.status = 'booked' AND cronofy_schedule.ws_id = ? ) AS no_show_ws",[
                    $user->id
                 ])    
            ->selectRaw("(SELECT IFNULL(count('cronofy_schedule.id'), 0) FROM cronofy_schedule left join session_group_users 
                 on session_group_users.session_id = cronofy_schedule.id 
                 join users as ws on ws.id = cronofy_schedule.ws_id WHERE session_group_users.user_id = users.id AND ws.deleted_at IS NULL AND cronofy_schedule.cancelled_at IS NOT NULL AND cronofy_schedule.status = 'short_canceled' AND cronofy_schedule.ws_id = ?) as short_cancelws",[
                    $user->id
                 ])
            ->join('session_group_users', 'session_group_users.session_id', '=', 'cronofy_schedule.id')
            ->join('users', 'users.id', '=', 'session_group_users.user_id')
            ->join('users as ws', 'ws.id', '=', 'cronofy_schedule.ws_id')
            ->join('user_team', 'users.id', '=', 'user_team.user_id')
            ->join('companies', 'companies.id', '=', 'user_team.company_id')
            ->join('team_location', 'team_location.team_id', '=', 'user_team.team_id')
            ->join('company_locations', 'company_locations.id', '=', 'team_location.company_location_id')
            ->where(function ($query) use ($role, $user) {
                if ($role->slug == 'wellbeing_specialist') {
                    $query->where('cronofy_schedule.ws_id', $user->id);
                }
            })
            ->where('cronofy_schedule.status', '!=', 'open')
            ->whereNull('users.deleted_at')
            ->whereNull('ws.deleted_at')
            ->groupBy('session_group_users.user_id');

        if (in_array('name', array_keys($payload)) && !empty($payload['name'])) {
            $query->whereRaw("CONCAT(users.first_name,' ',users.last_name) like ?", ['%' . $payload['name'] . '%']);
        }

        if (in_array('email', array_keys($payload)) && !empty($payload['email'])) {
            $query->where('users.email', 'LIKE', "%{$payload['email']}%");
        }

        if (in_array('ws', array_keys($payload)) && !empty($payload['ws'])) {
            $query->where('ws_id', $payload['ws']);
        }

        if (in_array('location', array_keys($payload)) && !empty($payload['location'])) {
            $query->where('company_locations.id', $payload['location']);
        }

        if (in_array('company', array_keys($payload)) && !empty($payload['company'])) {
            $query->where('companies.id', $payload['company']);
        }

        //Exclude the sessions which have upcoming, cancelled or completed sessions count = 0
        if ($role->slug == 'wellbeing_specialist') {
            $query->havingRaw("`cancelled_sessionsws` > 0 ")
                ->orHavingRaw("`upcomingws` > 0 ")
                ->orHavingRaw("`completed_sessionws` > 0 ")
                ->orHavingRaw("`no_show_ws` > 0 ")
                ->orHavingRaw("`short_cancelws` > 0 ");
        }

        if (isset($payload['order'])) {
            $column = $payload['columns'][$payload['order'][0]['column']]['data'];
            $order  = $payload['order'][0]['dir'];
            if ($column == 'completed_session') {
                $columnName = ($role->slug == 'wellbeing_specialist') ? 'completed_sessionws' : 'completed_sessionsa';
                $query->orderBy($columnName, $order);
            } elseif ($column == 'upcoming') {
                $columnName = ($role->slug == 'wellbeing_specialist') ? 'upcomingws' : 'upcomingsa';
                $query->orderBy($columnName, $order);
            } elseif ($column == 'cancelled_sessions') {
                $columnName = ($role->slug == 'wellbeing_team_lead') ? 'cancelled_sessions' : 'cancelled_sessionsws';
                $query->orderBy($columnName, $order);
            } elseif ($column == 'no_show') {
                $columnName = ($role->slug == 'wellbeing_team_lead') ? 'no_show' : 'no_show_ws';
                $query->orderBy($columnName, $order);
            } elseif ($column == 'short_cancel') {
                $columnName = ($role->slug == 'wellbeing_team_lead') ? 'short_cancel' : 'short_cancelws';
                $query->orderBy($columnName, $order);
            } else {
                $query->orderBy($column, $order);
            }
        } else {
            $query->orderByDesc('cronofy_schedule.id');
        }

        return [
            'total'  => $query->get()->count(),
            'record' => $query->offset($payload['start'])->limit($payload['length'])->get(),
        ];
    }

    /**
     * Set datatable for record list.
     *
     * @param payload
     * @return dataTable
     */
    public function getSessionData($payload)
    {
        $user = Auth::user();
        $role = getUserRole($user);
        $list = $this->getSessionRecordList($payload);
        return DataTables::of($list['record'])
            ->skipPaging()
            ->addColumn('updated_at', function ($record) {
                return Carbon::parse($record->updated_at)->toDateTimeString();
            })
            ->addColumn('name', function ($record) {
                return $record->name;
            })
            ->addColumn('client_name', function ($record) {
                if (!$record->is_group) {
                    return $record->user->first_name . ' ' . $record->user->last_name;
                }

                if ($record->is_group && $record->users_count == 1) {
                    return $record->session_user_name;
                }
                return null;
            })
            ->addColumn('client_email', function ($record) {
                if (!$record->is_group) {
                    return $record->user->email;
                }

                if ($record->is_group && $record->users_count == 1) {
                    return $record->session_user_email;
                }
                return null;
            })
            ->addColumn('client_timezone', function ($record) {
                if (!$record->is_group) {
                    return $record->user->timezone;
                }

                if ($record->is_group && $record->users_count == 1) {
                    return $record->session_user_timezone;
                }
                return null;
            })
            ->addColumn('sub_category', function ($record) {
                $subCategories = ServiceSubCategory::where('id', $record->topic_id)->select('name')->first();
                if (!empty($subCategories)) {
                    return $subCategories->name;
                }
                return '-';
            })
            ->addColumn('wellbeing_specialist', function ($record) {
                return $record->wellbeingSpecialist->first_name . ' ' . $record->wellbeingSpecialist->last_name;
            })
            ->addColumn('company', function ($record) {
                $company = Company::where('id', $record->company_id)->first();
                if (!empty($company)) {
                    return $company->name;
                }
                return '-';
            })
            ->addColumn('participants', function ($record) use ($payload) {
                $role         = getUserRole();
                $scheduleUser = ScheduleUsers::join('users', 'users.id', '=', 'session_group_users.user_id')->where('session_id', $record->id)->select(DB::raw("CONCAT(users.first_name, ' ', users.last_name) AS name"), 'users.email')->whereNull('users.deleted_at')->distinct()->get()->toArray();
                $totalUsers   = sizeof($scheduleUser);
                if ($totalUsers > 0) {
                    if (($role->slug == 'wellbeing_team_lead' || $role->slug == 'wellbeing_specialist') && $payload['tab'] == 'group') {
                        return "<a href='javascript:void(0);' title='View Participants' class='preview_participants' data-rowdata='" . base64_encode(json_encode($scheduleUser)) . "' data-cid='" . $record->id . "'> " . $totalUsers . "</a>";
                    } else {
                        return $totalUsers;
                    }
                }
            })
            ->addColumn('start_time', function ($record) {
                $user      = Auth::user();
                $startDate = Carbon::parse($record->start_time)->setTimezone($user->timezone)->toDateString();
                $startTime = Carbon::parse($record->start_time)->setTimezone($user->timezone)->format('h:i A');
                $endTime   = Carbon::parse($record->end_time)->setTimezone($user->timezone)->format('h:i A');
                return $startDate . ' ' . $startTime . ' - ' . $endTime;
            })
            ->addColumn('wellbeing_specialist', function ($record) {
                $wellbeingSpecialist = $record->getWellbeingSpecialistData();
                return $wellbeingSpecialist['name'];
            })
            ->addColumn('status', function ($record) {
                $status = $record->status;
                if ($record->status == 'booked') {
                    if (Carbon::parse($record->start_time) > Carbon::now()) {
                        $status = 'Upcoming';
                    }
                    if ((Carbon::parse($record->end_time) < Carbon::now()) && $record->no_show == 'No') {
                        $status = 'Completed';
                    }
                    if ((Carbon::parse($record->end_time) < Carbon::now()) && $record->no_show == 'Yes') {
                        $status = 'No Show';
                    }
                    $startDate = Carbon::parse($record->start_time);
                    $endDate   = Carbon::parse($record->end_time);
                    if (Carbon::now()->between($startDate, $endDate) && $record->no_show == 'No') {
                        $status = 'Ongoing';
                    }
                    if (Carbon::now()->between($startDate, $endDate) && $record->no_show == 'Yes') {
                        $status = 'No Show';
                    }
                }
                if ($record->status == 'canceled') {
                    $status = 'Cancelled';
                }
                if ($record->status == 'rescheduled') {
                    $status = 'Rescheduled';
                }
                if ($record->status == 'completed') {
                    $status = 'Completed';
                }
                if ($record->status == 'short_canceled') {
                    $status = 'Short Cancel';
                }
                $role = getUserRole();
                if ($status == 'Ongoing' && !empty($record->location) && $role->slug == 'wellbeing_specialist') {
                    return '<p><a class="btn btn-primary" href="' . $record->location . '" target="_blank">Join</a> </p> <p><a class="btn btn-success" data-id="' . $record->id . '" href="javaScript:void(0)" id="completeModal">Complete</a> </p>';
                }
                return $status;
            })
            ->addColumn('actions', function ($record) use ($role) {
                $status = $record->status;
                if ($record->status == 'booked') {
                    if (Carbon::parse($record->start_time) > Carbon::now()) {
                        $status = 'Upcoming';
                    }
                    if ((Carbon::parse($record->end_time) < Carbon::now()) && $record->no_show == 'No') {
                        $status = 'Completed';
                    }
                    if ((Carbon::parse($record->end_time) < Carbon::now()) && $record->no_show == 'Yes') {
                        $status = 'No Show';
                    }
                    $startDate = Carbon::parse($record->start_time);
                    $endDate   = Carbon::parse($record->end_time);
                    if (Carbon::now()->between($startDate, $endDate) && $record->no_show == 'No') {
                        $status = 'Ongoing';
                    }
                    if (Carbon::now()->between($startDate, $endDate) && $record->no_show == 'Yes') {
                        $status = 'No Show';
                    }
                }
                if ($record->status == 'canceled') {
                    $status = 'Cancelled';
                }
                if ($record->status == 'rescheduled') {
                    $status = 'Rescheduled';
                }
                if ($record->status == 'completed') {
                    $status = 'Completed';
                }
                if ($record->status == 'short_canceled') {
                    $status = 'Short Cancel';
                }
                return view('admin.cronofy.sessionlist.listaction', compact('record', 'role', 'status'))->render();
            })
            ->with([
                "recordsTotal"    => $list['total'],
                "recordsFiltered" => $list['total'],
            ])
            ->rawColumns(['status', 'actions', 'participants'])
            ->make(true);
    }

    /**
     * get records list for datatable.
     *
     * @param payload
     * @return array
     */
    public function getSessionRecordList($payload)
    {
        $role  = getUserRole();
        $query = $this
            ->leftJoin('session_group_users', 'session_group_users.session_id', '=', 'cronofy_schedule.id')
            ->join('users as u', 'u.id', '=', 'session_group_users.user_id')
            ->join('users as ws', 'ws.id', '=', 'cronofy_schedule.ws_id')
            ->join('companies', 'companies.id', '=', 'cronofy_schedule.company_id')
            ->where(function ($query) {
                $user = Auth::user();
                $role = getUserRole();
                if ($role->slug == 'wellbeing_specialist') {
                    $query->where('cronofy_schedule.ws_id', $user->id);
                } elseif ($role->group == 'company' && $role->slug == 'company_admin') {
                    $company = $user->company()->first();
                    $query->where('cronofy_schedule.company_id', $company->id);
                } elseif ($role->group == 'reseller') {
                    $company = $user->company()->first();
                    if (!is_null($company->parent_id)) {
                        $query->where('cronofy_schedule.company_id', $company->id);
                    } else {
                        $childCompanies = Company::select('id')->where('id', $company->id)->orWhere('parent_id', $company->id)->pluck('id')->toArray();
                        $companies      = Company::select(DB::raw('DISTINCT(companies.id)'), 'cp_features.slug')->leftJoin('cp_company_plans', 'companies.id', '=', 'cp_company_plans.company_id')
                            ->leftJoin('cp_plan', 'cp_plan.id', '=', 'cp_company_plans.plan_id')
                            ->leftJoin('cp_plan_features', 'cp_plan_features.plan_id', '=', 'cp_plan.id')
                            ->leftJoin('cp_features', 'cp_features.id', '=', 'cp_plan_features.feature_id')
                            ->whereIn('companies.id', $childCompanies)
                            ->where('cp_features.slug', 'digital-therapy')
                            ->pluck('companies.id')->toArray();

                        if (sizeof($companies) > 0) {
                            $query->whereIn('cronofy_schedule.company_id', $companies);
                        }
                    }
                }
            })
            ->whereNull('u.deleted_at')
            ->whereNull('ws.deleted_at')
            ->select(
                'cronofy_schedule.id',
                'cronofy_schedule.name',
                'cronofy_schedule.service_id',
                'cronofy_schedule.topic_id',
                'cronofy_schedule.created_by',
                'cronofy_schedule.is_group',
                'cronofy_schedule.user_id',
                'cronofy_schedule.ws_id',
                'cronofy_schedule.company_id',
                'cronofy_schedule.start_time',
                'cronofy_schedule.end_time',
                'cronofy_schedule.location',
                'cronofy_schedule.notes',
                'cronofy_schedule.status',
                'cronofy_schedule.no_show',
                'cronofy_schedule.updated_at',
                \DB::raw("(SELECT
                COUNT(session_group_users.id) from session_group_users 
                WHERE session_group_users.session_id = cronofy_schedule.id)
                AS sc_count"),
            );

        $query = $query->where('cronofy_schedule.status', '!=', 'open')
                ->where('cronofy_schedule.start_time', '!=', '0000-00-00 00:00:00');

        if (in_array('service', array_keys($payload)) && !empty($payload['service'])) {
            $query->where('cronofy_schedule.service_id', $payload['service']);
        }

        if (in_array('sub_category', array_keys($payload)) && !empty($payload['sub_category'])) {
            $query->where('cronofy_schedule.topic_id', $payload['sub_category']);
        }

        if (in_array('company', array_keys($payload)) && !empty($payload['company'])) {
            $query->where('cronofy_schedule.company_id', $payload['company']);
        }

        if (in_array('ws', array_keys($payload)) && !empty($payload['ws'])) {
            $query->where('cronofy_schedule.ws_id', $payload['ws']);
        }

        if (in_array('user', array_keys($payload)) && !empty($payload['user'])) {
            $query->where(function ($where) use ($payload) {
                $where
                    ->orWhereRaw("CONCAT(u.first_name,' ',u.last_name) like ?", ['%' . $payload['user'] . '%'])
                    ->orWhereRaw("u.email like ?", ['%' . $payload['user'] . '%']);
            });
        }

        if (in_array('status', array_keys($payload)) && !empty($payload['status'])) {
            $query = $query
                ->where(function ($query) use ($payload) {
                    $now = Carbon::now()->toDateTimeString();
                    if ($payload['status'] == 'upcoming') {
                        $query->where('start_time', '>=', $now)
                            ->where('cronofy_schedule.status', '!=', 'rescheduled')
                            ->where('cronofy_schedule.status', '!=', 'canceled')
                            ->where('cronofy_schedule.status', '!=', 'short_canceled');
                    } elseif ($payload['status'] == 'ongoing') {
                        $query->where('start_time', '<=', $now)
                            ->where('end_time', '>=', $now)
                            ->where('cronofy_schedule.status', 'booked')
                            ->where('cronofy_schedule.no_show', '=', 'No');
                    } elseif ($payload['status'] == 'completed') {
                        $query->where(function ($query) use ($now) {
                            $query
                                ->where('end_time', '<=', $now)
                                ->orWhere('cronofy_schedule.status', 'completed');
                        })
                            ->whereNotIn('cronofy_schedule.status', ['canceled', 'rescheduled', 'open', 'short_canceled'])
                            ->where('no_show', '=', 'No');
                    } elseif ($payload['status'] == 'no_show') {
                        $query->where('no_show', '=', 'Yes')
                        ->where('cronofy_schedule.status', 'booked');
                    } elseif ($payload['status'] == 'rescheduled') {
                        $query->where('cronofy_schedule.status', 'rescheduled');
                    } elseif ($payload['status'] == 'short_canceled') {
                        $query->where('cronofy_schedule.status', 'short_canceled');
                    } else {
                        $query->where('cronofy_schedule.status', 'canceled');
                    }
                });
        }
        if ($role->slug == 'wellbeing_team_lead' || $role->slug == 'wellbeing_specialist') {
            if ($payload['tab'] == 'single') {
                $query->addSelect(DB::raw("COUNT(DISTINCT session_group_users.id) as users_count"));
                $query->addSelect(DB::raw("CONCAT(u.first_name,' ',u.last_name) as session_user_name"));
                $query->addSelect(DB::raw("u.email as session_user_email"));
                $query->addSelect(DB::raw("u.timezone as session_user_timezone"));
                $query->havingRaw("sc_count = 1");
            } else {
                $query->havingRaw("sc_count > 1");
            }
        }
        $query->groupBy('cronofy_schedule.id');
        if (isset($payload['order'])) {
            $column = $payload['columns'][$payload['order'][0]['column']]['data'];
            $order  = $payload['order'][0]['dir'];
            switch ($column) {
                case "sub_category":
                    $query->orderBy('cronofy_schedule.topic_id', $order);
                    break;
                case "company":
                    $query->orderBy('cronofy_schedule.company_id', $order);
                    break;
                case "wellbeing_specialist":
                    $query->orderBy('cronofy_schedule.ws_id', $order);
                    break;
                case "client_name":
                    if ($role->slug == 'wellbeing_team_lead') {
                        $query->orderBy('session_user_name', $order);
                    }
                    break;
                case "client_email":
                    if ($role->slug == 'wellbeing_team_lead') {
                        $query->orderBy('session_user_email', $order);
                    }
                    break;
                case "client_timezone":
                    if ($role->slug == 'wellbeing_team_lead') {
                        $query->orderBy('session_user_timezone', $order);
                    }
                    break;
                default:
                    $query->orderBy($column, $order);
            }
        } else {
            $query->orderByDesc('cronofy_schedule.id');
        }

        return [
            'total'  => $query->get()->count(),
            'record' => $query->offset($payload['start'])->limit($payload['length'])->get(),
        ];
    }

    /**
     * Get client's session list
     *
     * @param User $client
     * @param array $payload
     * @return dataTable
     */
    public function getClientSessions($client, $payload)
    {
        $list = $this->getClientSessionList($client, $payload);

        return DataTables::of($list)
            ->addColumn('duration', function ($record) {
                return Carbon::parse($record->end_time)->diffInMinutes($record->start_time);
            })
            ->addColumn('status', function ($record) {
                $status = "";
                if ($record->status == 'booked') {
                    if (Carbon::parse($record->start_time) > Carbon::now()) {
                        $status = '<span class="text-warning">Upcoming</span>';
                    }
                    if ((Carbon::parse($record->end_time) < Carbon::now()) && $record->no_show == 'No') {
                        $status = '<span class="text-success">Completed</span>';
                    }
                    if ((Carbon::parse($record->end_time) < Carbon::now()) && $record->no_show == 'Yes') {
                        $status = 'No Show';
                    }
                    $startDate = Carbon::parse($record->start_time);
                    $endDate   = Carbon::parse($record->end_time);
                    if (Carbon::now()->between($startDate, $endDate) && $record->no_show == 'No') {
                        $status = '<span class="text-warning">Ongoing</span>';
                    }
                    if (Carbon::now()->between($startDate, $endDate) && $record->no_show == 'Yes') {
                        $status = 'No Show';
                    }
                }
                if ($record->status == 'canceled') {
                    $status = '<span class="text-danger">Cancelled</span>';
                }
                if ($record->status == 'rescheduled') {
                    $status = '<span class="text-muted">Rescheduled</span>';
                }
                if ($record->status == 'completed') {
                    $status = '<span class="text-success">Completed</span>';
                }
                if ($record->status == 'short_canceled') {
                    $status = '<span class="text-danger">Short Cancel</span>';
                }
                return $status;
            })
            ->addColumn('view', function ($record) {
                if (!is_null($record->cancelled_at)) {
                    return view('admin.cronofy.clientlist.sessions-listaction', compact('record'))->render();
                }
                return '';
            })
            ->rawColumns(['duration', 'status', 'view'])
            ->make(true);
    }

    /**
     * Get client's session from table
     *
     * @param User $client
     * @param array $payload
     * @return array
     */
    public function getClientSessionList($client, $payload)
    {
        $user     = auth()->user();
        $role     = getUserRole($user);
        $nowInUTC = now(config('appTimezone'))->toDateTimeString();

        $query = $client
            ->bookedCronofySessions()
            ->select(
                'cronofy_schedule.id',
                'cronofy_schedule.name AS session_name',
                \DB::raw('0 AS duration'),
                'cronofy_schedule.start_time',
                'cronofy_schedule.end_time',
                'cronofy_schedule.status',
                'cronofy_schedule.cancelled_by',
                'cronofy_schedule.cancelled_at',
                'cronofy_schedule.cancelled_reason',
                'cronofy_schedule.no_show'
            );

        if ($role->slug == 'wellbeing_specialist') {
            $query->where('cronofy_schedule.ws_id', $user->id);
        }
        $query = $query->where('cronofy_schedule.status', '!=', 'open')
            ->when(($payload['name'] ?? null), function ($query, $name) {
                $query->whereRaw('cronofy_schedule.name LIKE ?', "%{$name}%");
            })
            ->when(($payload['status'] ?? null), function ($query, $status) use ($nowInUTC) {
                if ($status == 'upcoming') {
                    $query
                        ->where('cronofy_schedule.start_time', '>=', $nowInUTC)
                        ->where('cronofy_schedule.status', '!=', 'canceled')
                        ->where('cronofy_schedule.status', '!=', 'rescheduled');
                } elseif ($status == 'ongoing') {
                    $query
                        ->where('cronofy_schedule.start_time', '<=', $nowInUTC)
                        ->where('cronofy_schedule.end_time', '>=', $nowInUTC)
                        ->where('cronofy_schedule.status', 'booked')
                        ->where('cronofy_schedule.no_show', '=', 'No');
                } elseif ($status == 'completed') {
                    $query->where(function ($query) use ($nowInUTC) {
                        $query
                            ->where('cronofy_schedule.end_time', '<=', $nowInUTC)
                            ->orWhere('cronofy_schedule.status', 'completed');
                    })
                        ->whereNotIn('cronofy_schedule.status', ['canceled', 'rescheduled', 'open', 'short_canceled'])
                        ->where('cronofy_schedule.no_show', '=', 'No');
                } elseif ($status == 'no_show') {
                    $query->where('cronofy_schedule.no_show', '=', 'Yes');
                } elseif ($status == 'rescheduled') {
                    $query->where('cronofy_schedule.status', 'rescheduled');
                } elseif ($status == 'short_canceled') {
                    $query->where('cronofy_schedule.status', 'short_canceled');
                } else {
                    $query->where('cronofy_schedule.status', 'canceled');
                }
            });

        if (isset($payload['order'])) {
            $column = $payload['columns'][$payload['order'][0]['column']]['data'];
            $order  = $payload['order'][0]['dir'];
            $query->orderBy($column, $order);
        } else {
            $query->orderByDesc('cronofy_schedule.id');
        }

        return $query
            ->limit(config('zevolifesettings.datatable.pagination.long'))
            ->get();
    }

    /**
     * get reports for digital therapy
     *
     * @param array $payload
     * @return records
     */
    public function getDigitalTherapyReport($payload)
    {
        $list = $this->getDigitalTherapyRecords($payload);
        return DataTables::of($list)
            ->addColumn('location', function ($record) {
                $scheduleUser = ScheduleUsers::where('session_id', $record->id)->get();

                if ($record->is_group && count($scheduleUser) > 1) {
                    return $record->location_name;
                } else {
                    $userId = $record->user_id;
                    if (!empty($scheduleUser) && count($scheduleUser) > 0) {
                        $userId = $scheduleUser[0]->user_id;
                    }
                    $userLocation = User::select('company_locations.name')->leftJoin('user_team', function ($join) {
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
                        ->where('users.id', $userId)->first();
                    return $userLocation->name ?? $record->location_name;
                }
            })
            ->addColumn('client_name', function ($record) {
                if (!$record->is_group) {
                    if (!empty($record->user)) {
                        $userFullName = $record->user->first_name . ' ' . $record->user->last_name;
                    } else {
                        $userFullName = $record->session_user_name;
                    }
                    return $userFullName;
                } elseif ($record->is_group && $record->users_count == 1) {
                    return $record->session_user_name;
                }
                return null;
            })
            ->addColumn('client_email', function ($record) {
                if (!$record->is_group) {
                    if (!empty($record->user)) {
                        $userEmail = $record->user->email;
                    } else {
                        $userEmail = $record->session_user_email;
                    }
                    return $userEmail;
                } elseif ($record->is_group && $record->users_count == 1) {
                    return $record->session_user_email;
                }
                return null;
            })
            ->addColumn('department_name', function ($record) {
                $scheduleUser = ScheduleUsers::where('session_id', $record->id)->get();

                if ($record->is_group && count($scheduleUser) > 1) {
                    return $record->department_name;
                } else {
                    $userId = $record->user_id;
                    if (!empty($scheduleUser) && count($scheduleUser) > 0) {
                        $userId = $scheduleUser[0]->user_id;
                    }
                    $userDepartment = User::select('departments.name')->leftJoin('user_team', function ($join) {
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
                        ->where('users.id', $userId)->first();
                    return $userDepartment->name;
                }
            })
            ->addColumn('mode_of_service', function ($record) {
                $mode                  = "";
                $companyDigitalTherapy = CompanyDigitalTherapy::where('company_id', $record->company_id)->first();
                if ($companyDigitalTherapy->dt_is_online) {
                    $mode = 'Online';
                } elseif ($companyDigitalTherapy->dt_is_onsite) {
                    $mode = 'Onsite';
                }
                return $mode;
            })
            ->addColumn('ws_shift', function ($record) {
                $shift = config('zevolifesettings.shift');
                return $shift[$record->wellbeingSpecialist->wsuser->shift];
            })
            ->addColumn('number_of_participants', function ($record) {
                $scheduleUser = ScheduleUsers::join('users', 'users.id', '=', 'session_group_users.user_id')->where('session_id', $record->id)->select(DB::raw("CONCAT(users.first_name,' ',users.last_name) AS name"), 'users.email')->whereNull('users.deleted_at')->distinct()->get()->toArray();
                $totalUsers   = sizeof($scheduleUser);
                if ($totalUsers > 0) {
                    return "<a href='javascript:void(0);' title='View Participants' class='preview_participants' data-rowdata='" . base64_encode(json_encode($scheduleUser)) . "' data-cid='" . $record->id . "'> " . $totalUsers . "</a>";
                }
            })
            ->addColumn('status', function ($record) {
                $status = $record->status;
                if (strtolower($record->status) == 'booked') {
                    if (Carbon::parse($record->start_time) > Carbon::now()) {
                        $status = 'Upcoming';
                    }
                    if ((Carbon::parse($record->end_time) < Carbon::now()) && $record->no_show == 'No') {
                        $status = 'Completed';
                    }
                    if ((Carbon::parse($record->end_time) < Carbon::now()) && $record->no_show == 'Yes') {
                        $status = 'No Show';
                    }
                    $startDate = Carbon::parse($record->start_time);
                    $endDate   = Carbon::parse($record->end_time);
                    if (Carbon::now()->between($startDate, $endDate) && $record->no_show == 'No') {
                        $status = 'Ongoing';
                    }
                    if (Carbon::now()->between($startDate, $endDate) && $record->no_show == 'Yes') {
                        $status = 'No Show';
                    }
                }
                if ($record->status == 'canceled') {
                    $status = 'Cancelled';
                }
                if ($record->status == 'rescheduled') {
                    $status = 'Rescheduled';
                }
                if ($record->status == 'completed') {
                    $status = 'Completed';
                }
                if ($record->status == 'short_canceled') {
                    $status = 'Short Cancel';
                }
                return $status;
            })
            ->addColumn('created_by', function ($record) {
                $userRecord = User::find($record->created_by);
                $role       = getUserRole($userRecord);
                return ($role->slug == 'company_admin') ? 'User' : $role->name;
            })
            ->rawColumns(['actions', 'number_of_participants'])
            ->make(true);
    }

    /**
     * get records for digital therapy
     *
     * @param array $payload
     * @return records
     */
    public function getDigitalTherapyRecords($payload)
    {
        $user        = auth()->user();
        $role        = getUserRole($user);
        $appTimezone = config('app.timezone');
        $timezone    = (!empty($user->timezone) ? $user->timezone : $appTimezone);

        $query = $this
            ->leftJoin('session_group_users', 'session_group_users.session_id', '=', 'cronofy_schedule.id')
            ->join('users as u', 'u.id', '=', 'session_group_users.user_id')
            ->join('users as ws', 'ws.id', '=', 'cronofy_schedule.ws_id')
            ->join('companies', 'companies.id', '=', 'cronofy_schedule.company_id')
            ->join('services', 'services.id', '=', 'cronofy_schedule.service_id')
            ->join('service_sub_categories', 'service_sub_categories.id', '=', 'cronofy_schedule.topic_id')
            ->join('company_locations', 'company_locations.company_id', '=', 'cronofy_schedule.company_id')
            ->join('departments', 'departments.company_id', '=', 'cronofy_schedule.company_id')
            ->join('role_user', 'role_user.user_id', '=', 'cronofy_schedule.created_by')
            ->join('roles', 'roles.id', '=', 'role_user.role_id')
            ->select(
                'cronofy_schedule.id',
                'companies.id AS company_id',
                'companies.name AS company_name',
                'company_locations.name as location_name',
                'departments.name as department_name',
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
                'cronofy_schedule.created_by',
                \DB::raw("(SELECT CONCAT(users.first_name, ' ', users.last_name) as wellbeing_specialist FROM users WHERE cronofy_schedule.ws_id = users.id) AS wellbeing_specialist_name"),
                \DB::raw("(SELECT
                COUNT(session_group_users.id) from session_group_users 
                WHERE session_group_users.session_id = cronofy_schedule.id)
                AS sc_count"),
                \DB::raw("TIMESTAMPDIFF(MINUTE,cronofy_schedule.start_time,cronofy_schedule.end_time) as duration"),
                'ws.timezone as ws_timezone'
            )
            ->where('cronofy_schedule.status', '!=', 'open')
            ->where('cronofy_schedule.start_time', '!=', '0000-00-00 00:00:00')
            ->whereNull('u.deleted_at')
            ->whereNull('ws.deleted_at');


        if (in_array('dtService', array_keys($payload)) && !empty($payload['dtService'])) {
            $query->where('cronofy_schedule.service_id', $payload['dtService']);
        }

        if (in_array('company', array_keys($payload)) && !empty($payload['company'])) {
            $query->where('cronofy_schedule.company_id', $payload['company']);
        }

        if ((isset($payload['dtFromdate']) && !empty($payload['dtFromdate'] && strtotime($payload['dtFromdate']) !== false)) && (isset($payload['dtTodate']) && !empty($payload['dtTodate'] && strtotime($payload['dtTodate']) !== false))) {
            $fromdate = Carbon::parse($payload['dtFromdate'], $timezone)->setTime(0, 0, 0, 0)->setTimezone($appTimezone)->toDateTimeString();
            $todate   = Carbon::parse($payload['dtTodate'], $timezone)->setTime(23, 59, 59, 0)->setTimezone($appTimezone)->toDateTimeString();
            $query
                ->where(function ($where) use ($fromdate, $todate) {
                    $where
                        ->whereRaw("TIMESTAMP(cronofy_schedule.start_time) BETWEEN ? AND ?", [$fromdate, $todate])
                        ->orWhereRaw("TIMESTAMP(cronofy_schedule.end_time) BETWEEN ? AND ?", [$fromdate, $todate]);
                });
        }

        if (in_array('dtStatus', array_keys($payload)) && !empty($payload['dtStatus'])) {
            $query = $query
                ->where(function ($query) use ($payload) {
                    $now = Carbon::now()->toDateTimeString();
                    if ($payload['dtStatus'] == 'upcoming') {
                        $query->where('start_time', '>=', $now)
                            ->where('cronofy_schedule.status', '!=', 'rescheduled')
                            ->where('cronofy_schedule.status', '!=', 'canceled')
                            ->where('cronofy_schedule.status', '!=', 'short_canceled');
                    } elseif ($payload['dtStatus'] == 'ongoing') {
                        $query
                            ->where('cronofy_schedule.start_time', '<=', $now)
                            ->where('cronofy_schedule.end_time', '>=', $now)
                            ->where('cronofy_schedule.status', 'booked')
                            ->where('cronofy_schedule.no_show', '=', 'No');
                    } elseif ($payload['dtStatus'] == 'completed') {
                        $query->where(function ($query) use ($now) {
                            $query
                                ->where('cronofy_schedule.end_time', '<=', $now)
                                ->orWhere('cronofy_schedule.status', 'completed');
                        })
                            ->whereNotIn('cronofy_schedule.status', ['canceled', 'rescheduled', 'open', 'short_canceled'])
                            ->where('cronofy_schedule.no_show', '=', 'No');
                    } elseif ($payload['dtStatus'] == 'no_show') {
                        $query->where('cronofy_schedule.no_show', '=', 'Yes')
                        ->where('cronofy_schedule.status', 'booked');
                    } elseif ($payload['dtStatus'] == 'rescheduled') {
                        $query->where('cronofy_schedule.status', 'rescheduled');
                    } elseif ($payload['dtStatus'] == 'short_canceled') {
                        $query->where('cronofy_schedule.status', 'short_canceled');
                    } else {
                        $query->where('cronofy_schedule.status', 'canceled');
                    }
                });
        }
        if (in_array('user', array_keys($payload)) && !empty($payload['user']) && $role->slug == 'wellbeing_team_lead' && $payload['tab'] == 'single') {
            $query->where(function ($where) use ($payload) {
                $where
                    ->whereRaw("CONCAT(u.first_name,' ',u.last_name) like ?", ['%' . $payload['user'] . '%'])
                    ->orWhereRaw("u.email like ?", ['%' . $payload['user'] . '%']);   
            });
        }

        if (in_array('created_by', array_keys($payload)) && !empty($payload['created_by'])) {
            $query->where('roles.slug', $payload['created_by']);
        }
        
        // Filter the DT report by wellbeing Specialist if the loggedin user role is ZSA/WTL
        if (!empty($role) && ($role->slug == 'wellbeing_team_lead' || ($role->group == 'zevo' && $role->slug == 'super_admin'))) {
            if (in_array('wellbeingSpecialist', array_keys($payload)) && !empty($payload['wellbeingSpecialist'])) {
                $query->where('cronofy_schedule.ws_id', $payload['wellbeingSpecialist']);
            }
        }
        if ($role->slug == 'wellbeing_team_lead') {
            if ($payload['tab'] == 'single') {
                $query->addSelect(DB::raw("COUNT(DISTINCT session_group_users.id) as users_count"));
                $query->addSelect(DB::raw("CONCAT(u.first_name,' ',u.last_name) as session_user_name"));
                $query->addSelect(DB::raw("u.email as session_user_email"));
                $query->havingRaw("sc_count = 1");
            } else {
                $query->havingRaw("sc_count > 1");
            }
        }

        $query->groupBy('cronofy_schedule.id');
        if (isset($payload['order'])) {
            $column = $payload['columns'][$payload['order'][0]['column']]['data'];
            $order  = $payload['order'][0]['dir'];
            $query->orderBy($column, $order);
        } else {
            $query->orderByDesc('cronofy_schedule.updated_at');
        }

        return $query->orderBy('cronofy_schedule.id', 'DESC')->get();
    }

    /**
     * Add note for client
     *
     * @param array $payload
     * @return boolean
     */
    public function storeNote($payload)
    {
        $user = auth()->user();
        $note = $this->comments()->create([
            'user_id' => $user->id,
            'comment' => $payload['note'],
        ]);
        if ($note) {
            return true;
        }
        return false;
    }

    public function deleteNote($id)
    {
        if (!empty($id)) {
            $deleted = \DB::table('cronofy_schedule')->where('id', $id)->update(['notes' => null]);
            if ($deleted) {
                return array('deleted' => 'true');
            } else {
                return array('deleted' => 'error');
            }
        } else {
            return array('deleted' => 'error');
        }
    }

    /**
     * get details for calendly session.
     *
     * @param calendly
     * @return array
     */
    public function getSessionDetails($cronofySchedule)
    {
        $startDate   = Carbon::parse($cronofySchedule->start_time);
        $endDate     = Carbon::parse($cronofySchedule->end_time);
        $joinCheck   = Carbon::now()->between($startDate, $endDate) && $cronofySchedule->status != 'canceled' && $cronofySchedule->status != 'rescheduled' && $cronofySchedule->status != 'completed';
        $updateCheck = $cronofySchedule->status == 'canceled' || $cronofySchedule->status == 'rescheduled' || $startDate < Carbon::now() || Carbon::parse($cronofySchedule->start_time)->diffInHours(Carbon::now()) <= 3 ? false : true;
        $role        = getUserRole();

        if ($role->slug != 'wellbeing_specialist') {
            $joinCheck = $updateCheck = false;
        }
        if ($cronofySchedule->company_id != '') {
            $company = Company::where('id', $cronofySchedule->company_id)->first();
        } else {
            $company = $cronofySchedule->user->company->first();
        }
        $companyogo = $company->getMediaData('logo', ['w' => 15, 'h' => 15, 'zc' => 3]);

        return [
            'id'               => $cronofySchedule->id,
            'user'             => $cronofySchedule->user->first_name . ' ' . $cronofySchedule->user->last_name,
            'email'            => $cronofySchedule->user->email,
            'dob'              => Carbon::parse($cronofySchedule->user->profile->birth_date)->format(config('zevolifesettings.date_format.default_date')),
            'gender'           => ucfirst($cronofySchedule->user->profile->gender),
            'company'          => $company,
            'company_logo'     => $companyogo,
            'booked_date'      => Carbon::parse($cronofySchedule->event_created_at)->setTimezone($cronofySchedule->user->timezone)->format('M d, Y H:i A'),
            'duration'         => Carbon::parse($cronofySchedule->end_time)->diffInMinutes($cronofySchedule->start_time),
            'end_time'         => Carbon::parse($cronofySchedule->end_time)->format('Y-m-d h:i:s'),
            'cancel_url'       => $cronofySchedule->cancel_url,
            'reschedule_url'   => $cronofySchedule->reschedule_url,
            'join_url'         => $cronofySchedule->location,
            'notes'            => $cronofySchedule->notes ?? '-',
            'user_notes'       => $cronofySchedule->user_notes ?? '-',
            'no_show'          => $cronofySchedule->no_show ?? 'No',
            'logo'             => $cronofySchedule->user->getMediaData('logo', ['w' => 512, 'h' => 512, 'zc' => 3]),
            'allowJoin'        => $joinCheck,
            'allowUpdate'      => $updateCheck,
            'cancelled_by'     => !empty($cronofySchedule->cancelledBy->full_name) ? $cronofySchedule->cancelledBy->full_name : null,
            'cancelled_at'     => Carbon::parse($cronofySchedule->cancelled_at)->setTimezone($cronofySchedule->user->timezone)->format('M d, Y H:i A'),
            'cancelled_reason' => $cronofySchedule->cancelled_reason ?? '-',
            'status'           => $cronofySchedule->status,
            'score'            => $cronofySchedule->score ?? 0,
        ];
    }

    /**
     * get Send reminder notification to users before 15 minutes of session starts.
     * @param
     * @return array
     */
    public function sendSessionStartReminderNotification()
    {
        try {
            $notificationUser = User::select('users.*', 'user_notification_settings.flag AS notification_flag')
                ->leftJoin('user_notification_settings', function ($join) {
                    $join->on('user_notification_settings.user_id', '=', 'users.id')
                        ->where('user_notification_settings.flag', '=', 1)
                        ->whereRaw('(`user_notification_settings`.`module` = ? OR `user_notification_settings`.`module` = ?)', ['digital-therapy', 'all']);
                })
                ->whereRaw('users.id IN ( SELECT user_id FROM `session_group_users` WHERE session_id = ? )', [$this->id])
                ->where('is_blocked', false)
                ->groupBy('users.id')
                ->get()
                ->toArray();

            // dispatch job to send push notification to all user when group session created
            \dispatch(new SendGroupSessionPushNotification($this, "session-start-reminder", $notificationUser, ''));
        } catch (\Exception $exception) {
            throw $exception;
        }
    }

    public function exportDigitalTherapyReport($payload)
    {
        $user = auth()->user();
        $role = getUserRole($user);
        $tab  = 'none';
        if ($role->slug == 'wellbeing_team_lead') {
            $tab = $payload['tab'];
        }
        $records = $this->getDigitalTherapyRecords($payload);
        $email   = ($payload['email'] ?? $user->email);

        return \dispatch(new ExportDigitalTherapyReportJob($records->toArray(), $tab, $email, $user));
    }

    /**
     * Get Send email to users after cronofy Session completed.
     *
     * @param cronofySchedule
     * @return array
     */
    public function sendSessionCompleteEmailConsent()
    {
        $getSessionUserDetails = $this->scheduleUsers()->select(
            'session_group_users.user_id',
            'session_group_users.session_id',
            'users.first_name',
            'users.email',
            'cronofy_schedule.id',
            'cronofy_schedule.ws_id',
            'cronofy_schedule.is_consent'
        )
            ->leftJoin('cronofy_schedule', 'cronofy_schedule.id', '=', 'session_group_users.session_id')
            ->whereNull('session_group_users.cancelled_at')
            ->where('cronofy_schedule.is_consent', false)
            ->get();

        foreach ($getSessionUserDetails as $session) {
            $userId = $session->user_id;
            $wsId   = $session->ws_id;
            $user   = User::where('id', $userId)->first();
            if (!empty($user)) {
                $company = $user->company()->select('companies.id')->first();
                if (!empty($company)) {
                    $digitalTherapy = $company->digitalTherapy()->select('company_digital_therapy.consent', 'company_digital_therapy.consent_url')->first();
                    if ($digitalTherapy->consent) {
                        $isConsent = $this->leftJoin('session_group_users', 'session_group_users.session_id', '=', 'cronofy_schedule.id')
                            ->where('session_group_users.user_id', $userId)
                            ->where('cronofy_schedule.ws_id', $wsId)
                            ->where('cronofy_schedule.is_consent', true)
                            ->count();

                        if ($isConsent <= 0) {
                            $sessionData = [
                                'sessionId'  => $session->id,
                                'company'    => (!empty($company->id) ? $company->id : null),
                                'email'      => $session->email,
                                'userName'   => $session->first_name,
                                'consentUrl' => $digitalTherapy->consent_url,
                            ];

                            event(new SendEmailConsentEvent($sessionData));
                        }
                    }
                }
            }
        }
    }

    /**
     * Send consent form to client
     *
     * @param cronofySchedule
     * @return array
     */
    public function sendConsent($payload)
    {
        $notificationUserForConsent = User::select('users.*', 'user_notification_settings.flag AS notification_flag')
            ->leftJoin('user_notification_settings', function ($join) {
                $join->on('user_notification_settings.user_id', '=', 'users.id')
                    ->where('user_notification_settings.flag', '=', 1)
                    ->whereRaw('(`user_notification_settings`.`module` = ? OR `user_notification_settings`.`module` = ?)', ['digital-therapy', 'all']);
            })
            ->where('user_id', $this->user_id)
            ->where('is_blocked', false)
            ->first();
        $newScheduleDetails = CronofySchedule::where('id', $this->id)->select('id', 'created_by', 'user_id', 'company_id', 'ws_id', 'name', 'location', 'is_group', 'meta')->first();

        // dispatch job to send push notification to all user when group session created
        \dispatch(new SendConsentPushNotification($newScheduleDetails, "consent-form-receive", $notificationUserForConsent, 'backend'));
        return true;
    }

    /**
     * Export wellbeing specialist and user notes
     * @param $payload
     * @param $cronofySchedule
     * @return view
     */
    public function exportNotesDataEntity($payload, $cronofySchedule)
    {
        $user     = auth()->user();
        return \dispatch(new ExportClientUserNotesReportJob($payload, $user, $cronofySchedule));
    }

    /**
     * get Export client list data table
     *
     * @param array payload
     * @return array
     */
    public function getExportRecordList($payload)
    {
        $user        = auth()->user();
        $role        = getUserRole($user);
        $appTimezone = config('app.timezone');
        $timezone    = (!empty($user->timezone) ? $user->timezone : $appTimezone);
        $now         = now($timezone)->toDateTimeString();

        $query = $this
            ->select(
                \DB::raw("CONCAT(users.first_name, ' ', users.last_name) AS client_name"),
                'users.email',
                'companies.name AS company_name',
                'company_locations.name as location_name',
                \DB::raw("IFNULL((SELECT IFNULL(count('cronofy_schedule.id'), 0) FROM cronofy_schedule left join session_group_users 
                on session_group_users.session_id = cronofy_schedule.id 
                join users as ws on ws.id = cronofy_schedule.ws_id WHERE session_group_users.user_id = users.id AND cronofy_schedule.cancelled_at IS NOT NULL AND ws.deleted_at IS NULL AND (cronofy_schedule.status = 'canceled' OR cronofy_schedule.status = 'rescheduled')), '0') as cancelled"),
                \DB::raw("IFNULL((SELECT IFNULL(count('cronofy_schedule.id'), 0) FROM cronofy_schedule left join session_group_users 
                on session_group_users.session_id = cronofy_schedule.id
                join users as ws on ws.id = cronofy_schedule.ws_id WHERE session_group_users.user_id = users.id AND ws.deleted_at IS NULL AND cronofy_schedule.cancelled_at IS NOT NULL AND cronofy_schedule.status = 'short_canceled'), '0') as short_cancel"),
                \DB::raw("IFNULL((SELECT IFNULL(count('cronofy_schedule.id'), 0) FROM cronofy_schedule left join session_group_users 
                on session_group_users.session_id = cronofy_schedule.id 
                join users as ws on ws.id = cronofy_schedule.ws_id WHERE session_group_users.user_id = users.id AND ws.deleted_at IS NULL AND cronofy_schedule.no_show = 'Yes' AND cronofy_schedule.status = 'booked'), '0') AS no_show"),
            )
            ->selectRaw("IFNULL((SELECT IFNULL(count('cronofy_schedule.id'), 0) FROM cronofy_schedule left join session_group_users 
                on session_group_users.session_id = cronofy_schedule.id 
                join users as ws on ws.id = cronofy_schedule.ws_id
                WHERE session_group_users.user_id = users.id
                 AND cronofy_schedule.cancelled_at IS NULL AND (CONVERT_TZ(cronofy_schedule.end_time, ?,  ?) <= ? or cronofy_schedule.status = 'completed') AND ws.deleted_at IS NULL AND cronofy_schedule.status NOT IN ('canceled', 'rescheduled', 'open', 'short_canceled') and cronofy_schedule.no_show = 'No'), '0') as completed",[
                    'UTC',$timezone,$now
                 ])
            ->selectRaw("IFNULL((SELECT IFNULL(count('cronofy_schedule.id'), 0) FROM cronofy_schedule left join session_group_users 
                 on session_group_users.session_id = cronofy_schedule.id 
                 join users as ws on ws.id = cronofy_schedule.ws_id WHERE session_group_users.user_id = users.id AND cronofy_schedule.cancelled_at IS NULL AND ws.deleted_at IS NULL AND  CONVERT_TZ(cronofy_schedule.start_time, ?, ?) >= ? AND (cronofy_schedule.status = 'booked' OR cronofy_schedule.status = 'upcoming' )), '0') as upcoming",[
                    'UTC',$timezone,$now
                 ])
            ->join('session_group_users', 'session_group_users.session_id', '=', 'cronofy_schedule.id')
            ->join('users', 'users.id', '=', 'session_group_users.user_id')
            ->join('users as ws', 'ws.id', '=', 'cronofy_schedule.ws_id')
            ->join('user_team', 'users.id', '=', 'user_team.user_id')
            ->join('companies', 'companies.id', '=', 'user_team.company_id')
            ->join('team_location', 'team_location.team_id', '=', 'user_team.team_id')
            ->join('company_locations', 'company_locations.id', '=', 'team_location.company_location_id')
            ->where(function ($query) use ($role, $user) {
                if ($role->slug == 'wellbeing_specialist') {
                    $query->where('cronofy_schedule.ws_id', $user->id);
                }
            })
            ->where('cronofy_schedule.status', '!=', 'open')
            ->whereNull('users.deleted_at')
            ->whereNull('ws.deleted_at')
            ->groupBy('session_group_users.user_id');

        if (in_array('name', array_keys($payload)) && !empty($payload['name'])) {
            $query->whereRaw("CONCAT(users.first_name,' ',users.last_name) like ?", ['%' . $payload['name'] . '%']);
        }

        if (in_array('searchemail', array_keys($payload)) && !empty($payload['searchemail'])) {
            $query->where('users.email', 'LIKE', "%{$payload['searchemail']}%");
        }

        if (in_array('ws', array_keys($payload)) && !empty($payload['ws'])) {
            $query->where('ws_id', $payload['ws']);
        }

        if (in_array('location', array_keys($payload)) && !empty($payload['location'])) {
            $query->where('company_locations.id', $payload['location']);
        }

        if (in_array('company', array_keys($payload)) && !empty($payload['company'])) {
            $query->where('companies.id', $payload['company']);
        }

        if (isset($payload['order'])) {
            $column = $payload['columns'][$payload['order'][0]['column']]['data'];
            $order  = $payload['order'][0]['dir'];
            if ($column == 'completed_session') {
                $columnName = ($role->slug == 'wellbeing_specialist') ? 'completed_sessionws' : 'completed_sessionsa';
                $query->orderBy($columnName, $order);
            } elseif ($column == 'upcoming') {
                $columnName = ($role->slug == 'wellbeing_specialist') ? 'upcomingws' : 'upcomingsa';
                $query->orderBy($columnName, $order);
            } elseif ($column == 'cancelled_sessions') {
                $columnName = ($role->slug == 'wellbeing_team_lead') ? 'cancelled_sessions' : 'cancelled_sessions';
                $query->orderBy($columnName, $order);
            } elseif ($column == 'no_show') {
                $columnName = ($role->slug == 'wellbeing_team_lead') ? 'no_show_ws' : 'no_show';
                $query->orderBy($columnName, $order);
            } else {
                $query->orderBy($column, $order);
            }
        } else {
            $query->orderByDesc('cronofy_schedule.id');
        }

        return $query->get();
    }

    /**
     * Export client data for super admin
     * @param $payload
     * @param $cronofy
     * @return view
     */
    public function exportClientData($payload)
    {
        $user             = auth()->user();
        $records          = $this->getExportRecordList($payload);
        $email            = ($payload['email'] ?? $user->email);

        return \dispatch(new ExportClientJob($records->toArray(), $email, $user));
    }

    /**
     * Update session notes data.
     * @param array $payload
     * @return boolean
     */
    public function updateSessionNotes($payload)
    {
        $updated = $this->where('id', $payload['commentId'])->update(['notes' => $payload['notes']]);
        if ($updated) {
            return true;
        }
        return false;
    }
}
