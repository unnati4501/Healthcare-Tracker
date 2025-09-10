<?php

namespace App\Models;

use App\Jobs\SendNewEapNotification;
use App\Models\Calendly;
use App\Models\User;
use App\Models\ZdTicketComment;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\JsonResponse;
use Yajra\DataTables\Facades\DataTables;

class ZdTicket extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'eap_tickets';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = [
        'ticket_id',
        'user_id',
        'company_id',
        'therapist_id',
        'custom_fields',
        'status',
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'custom_fields' => 'object',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [];

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
        return $this->belongsTo(User::class, 'therapist');
    }

    /**
     * 'hasMany' relation using 'eap_ticket_comments'
     * table via 'ticket_id' field.
     *
     * @return hasMany
     */
    public function comments(): HasMany
    {
        return $this->hasMany(ZdTicketComment::class, 'ticket_id');
    }

    /**
     * 'hasMany' relation using 'eap_calendly'
     * table via 'therapist_id' field.
     *
     * @return hasMany
     */
    public function sessions(): HasMany
    {
        return $this->hasMany(Calendly::class, 'therapist_id');
    }

    /**
     * To sync ticket details with zevo based
     * on received data from zendesk trigger
     *
     * @param ZendeskAPI $zdClient
     * @param array $payload
     * @return JsonResponse
     */
    public function updateOrCreateTicket($zdClient, $payload)
    {
        // find zevo user based on requester email of ticket
        $user = User::select('id')
            ->whereRaw("lower(users.email) = ?", [$payload['ticketRequestEmail']])
            ->first();

        // find zevo therapist based on selected therapist of ticket
        $therapist = null;
        if (!empty($payload['ticketCstmFldTherapistEmail'])) {
            $therapist = User::select('id')
                ->whereRaw("lower(users.email) = ?", [$payload['ticketCstmFldTherapistEmail']])
                ->first();
        }

        if (empty($therapist)) {
            return;
        }

        $checkAccess = false; // Set Default flag for company plan
        if (!empty($user)) {
            $company = $user->company()->select('companies.id')->first();
            if (empty($company)) {
                return;
            }
            // Check EAP Access of company plan
            $checkAccess = getCompanyPlanAccess($user, 'eap');
        }

        // update or create ticket data
        $ticket = $this
            ->updateOrCreate(['ticket_id' => $payload['ticketId']], [
                'user_id'       => (!empty($user) ? $user->id : null),
                'therapist_id'  => (!empty($therapist) ? $therapist->id : null),
                'company_id'    => (!empty($user) ? $company->id : null),
                'custom_fields' => [
                    'ReasonOfChat'            => ($payload['ticketCstmFldReasonOfChat'] ?? ''),
                    'PrefForTherapist'        => ($payload['ticketCstmFldPrefForTherapist'] ?? ''),
                    'TherapistCalendlyHandle' => ($payload['ticketCstmFldTherapistCalendlyHandle'] ?? ''),
                    'TicketRequestEmail'      => $payload['ticketRequestEmail'],
                ],
                'status'        => $payload['ticketStatus'],
            ]);

        if ($ticket) {
            if ($checkAccess && $ticket->wasRecentlyCreated) {
                // send notification only once when counselor has been assigned
                 dispatch(new SendNewEapNotification($ticket, 'assigned'));
            }

            $comments = $zdClient->tickets()->comments()->findAll(['ticket_id' => $ticket->ticket_id]);
            if (!empty($comments) && isset($comments->comments) && !empty($comments->comments) && sizeof($comments->comments) > 1) {
                foreach ($comments->comments as $key => $comment) {
                    if ($key > 0 && $comment->type == "Comment" && !empty($comment->html_body)) {
                        $ticket->comments()->updateOrCreate([
                            'comment_id' => $comment->id,
                        ], [
                            'user_id' => null,
                            'type'    => 'internal_note',
                            'comment' => $comment->html_body,
                        ]);
                    }
                }
            }
        }
    }

    /**
     * Get client list
     *
     * @param array payload
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
            ->addColumn('upcoming', function ($record) {
                return numberFormatShort($record->upcoming);
            })
            ->addColumn('completed_session', function ($record) {
                return numberFormatShort($record->completed_session);
            })
            ->addColumn('actions', function ($record) use ($role) {
                return view('admin.clientlist.listaction', compact('record', 'role'))->render();
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
        $query       = $this
            ->select(
                'eap_tickets.id',
                'eap_tickets.therapist_id',
                \DB::raw("CONCAT(users.first_name, ' ', users.last_name) AS client_name"),
                'users.email',
                'companies.name AS company_name'
            )
            ->selectRaw("(SELECT IFNULL(count('id'), 0) FROM eap_calendly WHERE eap_calendly.user_id = eap_tickets.user_id AND cancelled_at IS NULL AND  CONVERT_TZ(eap_calendly.start_time, ?, ?) >= ?) AS upcoming",[
                'UTC',$timezone,$now
            ])
            ->selectRaw("(SELECT IFNULL(count('id'), 0) FROM eap_calendly WHERE eap_calendly.user_id = eap_tickets.user_id AND cancelled_at IS NULL AND  CONVERT_TZ(eap_calendly.end_time, ?, ?) <= ?) AS completed_session",[
                'UTC',$timezone,$now
            ])
            ->join('eap_calendly', 'eap_calendly.user_id', '=', 'eap_tickets.user_id')
            ->join('companies', 'companies.id', '=', 'eap_tickets.company_id')
            ->join('users', 'users.id', '=', 'eap_tickets.user_id')
            ->where(function ($query) use ($role, $user) {
                if ($role->slug == 'counsellor') {
                    $query->where('eap_calendly.therapist_id', $user->id)
                        ->where('eap_tickets.therapist_id', $user->id);
                }
            })
            ->groupBy('eap_tickets.user_id')
            ->when(($payload['name'] ?? null), function ($when, $string) {
                $when->where(\DB::raw("CONCAT(users.first_name,' ',users.last_name)"), 'like', "%{$string}%");
            })
            ->when(($payload['email'] ?? null), function ($when, $string) {
                $when->where('users.email', 'LIKE', "%{$string}%");
            })
            ->when(($payload['company'] ?? null), function ($when, $string) {
                $when->where('eap_tickets.company_id', $string);
            });

        if (isset($payload['order'])) {
            $column = $payload['columns'][$payload['order'][0]['column']]['data'];
            $order  = $payload['order'][0]['dir'];
            $query->orderBy($column, $order);
        } else {
            $query->orderByDesc('eap_tickets.id');
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

        return DataTables::of($list['record'])
            ->skipPaging()
            ->with([
                "recordsTotal"    => $list['total'],
                "recordsFiltered" => $list['total'],
            ])
            ->addColumn('duration', function ($record) {
                return Carbon::parse($record->end_time)->diffInMinutes($record->start_time);
            })
            ->addColumn('status', function ($record) {
                $status = "";
                if ($record->status == 'active') {
                    if (Carbon::parse($record->start_time) > Carbon::now()) {
                        $status = '<span class="text-warning">Upcoming</span>';
                    }
                    if (Carbon::parse($record->end_time) < Carbon::now()) {
                        $status = '<span class="text-muted">Completed</span>';
                    }
                    $startDate = Carbon::parse($record->start_time);
                    $endDate   = Carbon::parse($record->end_time);
                    if (Carbon::now()->between($startDate, $endDate)) {
                        $status = '<span class="text-success">Ongoing</span>';
                    }
                }
                if ($record->status == 'canceled') {
                    $status = '<span class="text-danger">Cancelled</span>';
                }
                if ($record->status == 'rescheduled') {
                    $status = '<span class="text-muted">Rescheduled</span>';
                }
                return $status;
            })
            ->addColumn('view', function ($record) {
                if (!is_null($record->cancelled_at)) {
                    return view('admin.clientlist.sessions-listaction', compact('record'))->render();
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
        $nowInUTC = now(config('appTimezone'))->toDateTimeString();

        $query = $client
            ->bookedSessions()
            ->select(
                'eap_calendly.id',
                'eap_calendly.name AS session_name',
                \DB::raw('0 AS duration'),
                'eap_calendly.start_time',
                'eap_calendly.end_time',
                'eap_calendly.status',
                'eap_calendly.cancelled_by',
                'eap_calendly.cancelled_at',
                'eap_calendly.cancelled_reason'
            )
            ->where('eap_calendly.therapist_id', $user->id)
            ->when(($payload['name'] ?? null), function ($query, $name) {
                $query->whereRaw('eap_calendly.name LIKE ?', "%{$name}%");
            })
            ->when(($payload['status'] ?? null), function ($query, $status) use ($nowInUTC) {
                if ($status == 'upcoming') {
                    $query
                        ->where('eap_calendly.start_time', '>=', $nowInUTC)
                        ->where('eap_calendly.status', '!=', 'canceled');
                } elseif ($status == 'ongoing') {
                    $query
                        ->where('eap_calendly.start_time', '<=', $nowInUTC)
                        ->where('eap_calendly.end_time', '>=', $nowInUTC)
                        ->where('eap_calendly.status', '!=', 'canceled');
                } elseif ($status == 'completed') {
                    $query
                        ->where('eap_calendly.end_time', '<=', $nowInUTC)
                        ->where('eap_calendly.status', '!=', 'canceled');
                } elseif ($status == 'rescheduled') {
                    $query->where('eap_calendly.status', 'rescheduled');
                } else {
                    $query->where('eap_calendly.status', 'canceled');
                }
            });

        if (isset($payload['order'])) {
            $column = $payload['columns'][$payload['order'][0]['column']]['data'];
            $order  = $payload['order'][0]['dir'];
            $query->orderBy($column, $order);
        } else {
            $query->orderByDesc('eap_calendly.id');
        }

        return [
            'total'  => $query->get()->count(),
            'record' => $query->offset($payload['start'])->limit($payload['length'])->get(),
        ];
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
            'user_id'    => $user->id,
            'comment_id' => null,
            'type'       => 'counsellor',
            'comment'    => $payload['note'],
        ]);
        if ($note) {
            return true;
        }
        return false;
    }
}
