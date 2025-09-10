<?php

namespace App\Models;

use App\Models\Notification;
use App\Models\User;
use App\Notifications\SystemAutoNotification;
use Auth;
use Carbon\Carbon;
use DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Yajra\DataTables\Facades\DataTables;

class Calendly extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'eap_calendly';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'user_id',
        'therapist_id',
        'event_identifier',
        'start_time',
        'end_time',
        'location',
        'notes',
        'cancel_url',
        'reschedule_url',
        'event_created_at',
        'cancelled_by',
        'cancelled_at',
        'cancelled_reason',
        'status',
        'reminder_at',
        'created_at',
        'updated_at',
    ];

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
    protected $dates = [
        'start_time',
        'end_time',
        'event_created_at',
        'cancelled_at',
        'reminder_at',
        'created_at',
        'updated_at',
    ];

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = true;

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
     * via `therapist_id` field.
     *
     * @return BelongsTo
     */
    public function therapist(): BelongsTo
    {
        return $this->belongsTo(User::class, 'therapist_id');
    }

    /**
     * Set datatable for record list.
     *
     * @param payload
     * @return dataTable
     */
    public function getTableData($payload)
    {
        $list = $this->getRecordList($payload);
        return DataTables::of($list)
            ->addColumn('updated_at', function ($record) {
                return Carbon::parse($record->updated_at)->toDateTimeString();
            })
            ->addColumn('name', function ($record) {
                return $record->name;
            })
            ->addColumn('user', function ($record) {
                if (!empty($record->user)) {
                    return $record->user->first_name . ' ' . $record->user->last_name;
                }
                return '-';
            })
            ->addColumn('email', function ($record) {
                return $record->user->email;
            })
            ->addColumn('therapist', function ($record) {
                return $record->therapist->first_name . ' ' . $record->therapist->last_name;
            })
            ->addColumn('company', function ($record) {
                $company = $record->user->company->first();
                if (!empty($company)) {
                    return $company->name;
                } else {
                    return '-';
                }
            })
            ->addColumn('duration', function ($record) {
                return Carbon::parse($record->end_time)->diffInMinutes($record->start_time);
            })
            ->addColumn('datetime', function ($record) {
                $user      = Auth::user();
                $startTime = Carbon::parse($record->start_time)->setTimezone($user->timezone)->format(config('zevolifesettings.date_format.default_datetime_24hours'));
                $endTime   = Carbon::parse($record->end_time)->setTimezone($user->timezone)->format(config('zevolifesettings.date_format.default_time_24_hours'));
                return $startTime . ' - ' . $endTime;
            })
            ->addColumn('status', function ($record) {
                $status = $record->status;
                if ($record->status == 'active') {
                    if (Carbon::parse($record->start_time) > Carbon::now()) {
                        $status = 'Upcoming';
                    }
                    if (Carbon::parse($record->end_time) < Carbon::now()) {
                        $status = 'Completed';
                    }
                    $startDate = Carbon::parse($record->start_time);
                    $endDate   = Carbon::parse($record->end_time);
                    if (Carbon::now()->between($startDate, $endDate)) {
                        $status = 'Ongoing';
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
                $role = getUserRole();
                if ($status == 'Ongoing' && !empty($record->location) && $role->slug == 'counsellor') {
                    return '<p><a class="btn btn-primary" href="' . $record->location . '" target="_blank">Join</a> </p> <p><a class="btn btn-success" data-id="' . $record->id . '" href="javaScript:void(0)" id="completeModal">Complete</a> </p>';
                }
                return $status;
            })
            ->addColumn('actions', function ($record) {
                return view('admin.calendly.listaction', compact('record'))->render();
            })
            ->rawColumns(['status', 'actions'])
            ->make(true);
    }

    /**
     * get records list for datatable.
     *
     * @param payload
     * @return array
     */
    public function getRecordList($payload)
    {

        $query = self::with('user', 'therapist')
            ->where(function ($query) {
                $user = Auth::user();
                $role = getUserRole();
                if ($role->slug == 'counsellor') {
                    $query->where('eap_calendly.therapist_id', $user->id);
                }
            })
            ->select(
                'eap_calendly.id',
                'eap_calendly.name',
                'eap_calendly.user_id',
                'eap_calendly.therapist_id',
                'eap_calendly.start_time',
                'eap_calendly.end_time',
                'eap_calendly.location',
                'eap_calendly.notes',
                'eap_calendly.cancel_url',
                'eap_calendly.status',
                'eap_calendly.updated_at'
            );

        if (in_array('email', array_keys($payload)) && !empty($payload['email'])) {
            $query = $query
                ->whereHas('user', function ($query) use ($payload) {
                    $query->where('email', 'LIKE', '%' . $payload['email'] . '%');
                });
        }

        if (in_array('user', array_keys($payload)) && !empty($payload['user'])) {
            $query = $query
                ->whereHas('user', function ($query) use ($payload) {
                    $query->where(\DB::raw("CONCAT(users.first_name,' ',users.last_name)"), 'like', "%{$payload['user']}%");
                });
        }

        if (in_array('company', array_keys($payload)) && !empty($payload['company'])) {
            $query = $query
                ->whereHas('user', function ($query) use ($payload) {
                    $query->with('company')
                        ->whereHas('company', function ($q) use ($payload) {
                            $q->where('companies.id', $payload['company']);
                        });
                });
        }

        if (in_array('duration', array_keys($payload)) && !empty($payload['duration'])) {
            $query = $query
                ->where(function ($query) use ($payload) {
                    $now = Carbon::now()->toDateTimeString();
                    $query->where('start_time', '>=', $now);
                    $next24Hours = Carbon::now()->addDay()->toDateTimeString();
                    $next7Days   = Carbon::now()->addDay(7)->toDateTimeString();
                    $next30Days  = Carbon::now()->addDay(30)->toDateTimeString();
                    $next60Days  = Carbon::now()->addDay(60)->toDateTimeString();
                    if ($payload['duration'] == 'next_24') {
                        $query->where('start_time', '<=', $next24Hours);
                    } elseif ($payload['duration'] == 'next_7') {
                        $query->where('start_time', '<=', $next7Days);
                    } elseif ($payload['duration'] == 'next_30') {
                        $query->where('start_time', '<=', $next30Days);
                    } else {
                        $query->where('start_time', '<=', $next60Days);
                    }
                });
        }

        if (in_array('status', array_keys($payload)) && !empty($payload['status'])) {
            $query = $query
                ->where(function ($query) use ($payload) {
                    $now = Carbon::now()->toDateTimeString();
                    if ($payload['status'] == 'upcoming') {
                        $query->where('start_time', '>=', $now)
                            ->where('status', '!=', 'rescheduled')
                            ->where('status', '!=', 'canceled');
                    } elseif ($payload['status'] == 'ongoing') {
                        $query->where('start_time', '<=', $now)
                            ->where('end_time', '>=', $now)
                            ->where('status', '!=', 'rescheduled')
                            ->where('status', '!=', 'canceled');
                    } elseif ($payload['status'] == 'completed') {
                        $query->where('end_time', '<=', $now)
                            ->where('status', '!=', 'rescheduled')
                            ->where('status', '!=', 'canceled');
                    } elseif ($payload['status'] == 'rescheduled') {
                        $query->where('status', 'rescheduled');
                    } else {
                        $query->where('status', 'canceled');
                    }
                });
        }

        return $query
            ->orderBy('eap_calendly.updated_at', 'DESC')
            ->get();
    }

    /**
     * get details for calendly session.
     *
     * @param calendly
     * @return array
     */
    public function getDetails($calendly)
    {
        $startDate   = Carbon::parse($calendly->start_time);
        $endDate     = Carbon::parse($calendly->end_time);
        $joinCheck   = Carbon::now()->between($startDate, $endDate) && $calendly->status != 'canceled' && $calendly->status != 'rescheduled' && $calendly->status != 'completed';
        $updateCheck = $calendly->status == 'canceled' || $calendly->status == 'rescheduled' || $startDate < Carbon::now() || !(Carbon::parse($calendly->start_time)->diffInHours(Carbon::now()) <= 3);
        $role        = getUserRole();

        if ($role->slug != 'counsellor') {
            $joinCheck = $updateCheck = false;
        }

        return [
            'id'               => $calendly->id,
            'user'             => $calendly->user->first_name . ' ' . $calendly->user->last_name,
            'email'            => $calendly->user->email,
            'dob'              => $calendly->user->profile->birth_date->format(config('zevolifesettings.date_format.default_date')),
            'booked_date'      => Carbon::parse($calendly->event_created_at)->setTimezone($calendly->user->timezone)->format('M d, Y H:i A'),
            'duration'         => Carbon::parse($calendly->end_time)->diffInMinutes($calendly->start_time),
            'cancel_url'       => $calendly->cancel_url,
            'reschedule_url'   => $calendly->reschedule_url,
            'join_url'         => $calendly->location,
            'notes'            => $calendly->notes ?? '-',
            'logo'             => $calendly->user->getMediaData('logo', ['w' => 512, 'h' => 512, 'zc' => 3]),
            'allowJoin'        => $joinCheck,
            'allowUpdate'      => $updateCheck,
            'cancelled_by'     => $calendly->cancelled_by,
            'cancelled_at'     => Carbon::parse($calendly->cancelled_at)->setTimezone($calendly->user->timezone)->format('M d, Y H:i A'),
            'cancelled_reason' => $calendly->cancelled_reason ?? '-',
            'status'           => $calendly->status,
        ];
    }
    /**
     * get Send notification to users after EAP Session after completed.
     *
     * @param calendly
     * @return array
     */
    public function sendEapCompleteNotificaion()
    {
        $deep_link_uri         = $title         = $message         = "";
        $scheduledAt           = now()->toDateTimeString();
        $extraNotificationData = [
            'type'         => 'Auto',
            'scheduled_at' => $scheduledAt,
            'push'         => true,
            'eap_title'    => $this->name,
        ];

        $user = $this->user()
            ->select('users.id', 'users.first_name', 'users.last_name', 'users.timezone', 'user_notification_settings.flag AS notification_flag', 'user_team.company_id')
            ->leftJoin('user_notification_settings', function ($join) {
                $join->on('user_notification_settings.user_id', '=', 'users.id')
                    ->where('user_notification_settings.flag', '=', 1)
                    ->whereRaw('(`user_notification_settings`.`module` = ? OR `user_notification_settings`.`module` = ?)', ['events', 'all']);
            })
            ->leftJoin('user_team', 'user_team.user_id', '=', 'users.id')
            ->groupBy('users.id')
            ->first();

        $title   = trans('notifications.eap.eap-completed.title');
        $message = trans('notifications.eap.eap-completed.message', [
            'first_name' => $user->first_name,
            'eap_title'  => $this->name,
        ]);

        $deep_link_uri = trans(config('zevolifesettings.deeplink_uri.eap_completed'), [
            'id' => $this->id,
        ]);

        $checkAlreadySend = Notification::where('deep_link_uri', $deep_link_uri)
            ->where('company_id', $user->company_id)
            ->where('creator_id', $user->id)
            ->select('id')
            ->count();

        if ($user && $checkAlreadySend <= 0) {
            $notificationData = [
                'creator_id'    => $user->id,
                'company_id'    => $user->company_id,
                'title'         => $title,
                'message'       => $message,
                'push'          => (!empty($user->notification_flag) ? $user->notification_flag : false),
                'deep_link_uri' => $deep_link_uri,
                'is_mobile'     => true,
                'is_portal'     => true,
                'tag'           => 'eap-completed',
            ] + $extraNotificationData;

            $notification = Notification::create($notificationData);

            if ($notification) {
                $user->notifications()
                    ->attach($notification, ['sent' => true, 'sent_on' => $scheduledAt]);

                if ($notification->push && $notification->type == 'Auto') {
                    \Notification::send(
                        $user,
                        new SystemAutoNotification($notification, 'eap-completed')
                    );
                }
            }
        }
    }



    /**
     * "BelongsToMany" relation to `eap_csat_user_logs` table
     * via `eap_calendy_id` field.
     *
     * @return hasMany
     */
    public function csat(): BelongsToMany
    {
        return $this->belongsToMany(User::class, EapCsatLogs::class, 'eap_calendy_id')
            ->withPivot('company_id', 'feedback_type', 'feedback')
            ->withTimestamps();
    }

    /**
     * Update session notes data.
     * @param array $payload
     * @return boolean
     */
    public function updateNotes($payload)
    {
        $updated = $this->update([
            'notes' => $payload['notes'],
        ]);
        if ($updated) {
            return true;
        }
        return false;
    }

    /**
     * delete record by record id.
     *
     * @param $id
     * @return array
     */

    public function deleteNote($id)
    {
        if (!empty($id)) {
            $deleted = DB::table('eap_calendly')->where('id', $id)->update(['notes' => null]);
            if ($deleted) {
                return array('deleted' => 'true');
            } else {
                return array('deleted' => 'error');
            }
        } else {
            return array('deleted' => 'error');
        }
    }
}
