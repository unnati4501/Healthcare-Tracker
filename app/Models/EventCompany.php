<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\hasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Yajra\DataTables\Facades\DataTables;

class EventCompany extends Model
{
    use SoftDeletes;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'event_companies';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['event_id', 'company_id'];

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
    protected $dates = [
        'created_at',
        'updated_at',
    ];

    /**
     * "BelongsTo" relation to `event` table
     * via `event_id` field.
     *
     * @return BelongsTo
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo('App\Models\Event', 'event_id');
    }

    /**
     * "BelongsTo" relation to `company` table
     * via `company_id` field.
     *
     * @return BelongsTo
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo('App\Models\Company', 'company_id');
    }

    /**
     * "hasMany" relation to `event_booking_logs` table
     * via `company_id` field.
     *
     * @return hasMany
     */
    public function bookingLogs(): hasMany
    {
        return $this->hasMany('App\Models\EventBookingLogs', 'company_id', 'company_id');
    }

    /**
     * Set datatable for get view event companies.
     *
     * @param payload
     * @return dataTable
     */
    public function getTableData($event, $payload)
    {
        $appTimezone = config('app.timezone');
        $eventStatus = config('zevolifesettings.event-status-master');
        $list        = $this->getRecordList($event, $payload);
        return DataTables::of($list['record'])
            ->skipPaging()
            ->with([
                "recordsTotal"    => $list['total'],
                "recordsFiltered" => $list['total'],
            ])
            ->addColumn('presenter', function ($record) use ($event) {
                if (in_array($record->status, [4, 5]) && !is_null($record->presenter_name)) {
                    return $record->presenter_name;
                }

                $eventPresenters = $event->presenters()
                    ->where('company_id', $record->company_id)
                    ->select(
                        'event_presenters.user_id',
                        \DB::raw("CONCAT(users.first_name, ' ', users.last_name) AS name")
                    )
                    ->join('users', 'users.id', '=', 'event_presenters.user_id')
                    ->groupBy('event_presenters.user_id')
                    ->get()->pluck('name')->toArray();
                $slicedPresenters = array_slice($eventPresenters, 0, 4);
                $totalPresenters  = sizeof($eventPresenters);
                if ($totalPresenters > 4) {
                    $slicedPresenters[] = "<a href='javascript:void(0);' class='previewPresenters' data-rowdata='" . base64_encode(json_encode($eventPresenters)) . "'> " . ($totalPresenters - 4) . " more</a>";
                }
                return implode(', ', $slicedPresenters);
            })
            ->addColumn('duration', function ($record) use ($appTimezone) {
                if (!is_null($record->booking_date)) {
                    $data = Carbon::parse("{$record->booking_date} {$record->start_time}", $appTimezone)
                        ->setTimezone($record->timezone)->format(config('zevolifesettings.date_format.default_date'));
                    $startTime = Carbon::parse("{$record->booking_date} {$record->start_time}", $appTimezone)
                        ->setTimezone($record->timezone)->format('h:i A');
                    $endTime = Carbon::parse("{$record->booking_date} {$record->end_time}", $appTimezone)
                        ->setTimezone($record->timezone)->format('h:i A');
                    return "{$data}<br/>{$startTime} - {$endTime}";
                } else {
                    return convertToHoursMins(timeToDecimal($record->duration), false, '%s %s');
                }
            })
            ->addColumn('status', function ($record) use ($eventStatus) {
                $status = $eventStatus[$record->status];
                return "<span class='badge " . $status['class'] . "'>" . $status['text'] . "</span>";
            })
            ->addColumn('actions', function ($record) use ($payload) {
                $editUrl = route('admin.event.edit', [$record->event_id, $record->id]);
                if (!empty($payload['referrer']) && in_array($payload['referrer'], ["bookingPage", "detailsPage"])) {
                    $editUrl = route('admin.event.edit', [$record->event_id, $record->id, 'referrer' => $payload['referrer'], 'referrerid' => (!empty($payload['referrerid']) ? $payload['referrerid'] : null)]);
                }
                return view('admin.event.details-listaction', compact('record', 'editUrl'))->render();
            })
            ->rawColumns(['presenter', 'duration', 'status', 'actions'])
            ->make(true);
    }

    /**
     * get record list for data table list.
     *
     * @param payload
     * @return roleList
     */
    public function getRecordList($event, $payload)
    {
        $user        = auth()->user();
        $appTimezone = config('app.timezone');
        $timezone    = (!empty($user->timezone) ? $user->timezone : $appTimezone);

        $query = $this
            ->select(
                'event_companies.*',
                'companies.name AS company_name',
                'events.duration',
                \DB::raw('event_booking_logs.meta->>"$.presenter" AS presenter_name'),
                'event_booking_logs.booking_date',
                'event_booking_logs.start_time',
                'event_booking_logs.end_time'
            )
            ->selectRaw("? as timezone",[$timezone])
            ->leftJoin('event_booking_logs', function ($join) {
                $join
                    ->on('event_booking_logs.event_id', '=', 'event_companies.event_id')
                    ->whereColumn('event_booking_logs.company_id', 'event_companies.company_id')
                    ->where('event_booking_logs.is_cancelled', 0);
            })
            ->join('events', function ($join) {
                $join->on('events.id', '=', 'event_companies.event_id');
            })
            ->join('companies', function ($join) {
                $join->on('companies.id', '=', 'event_companies.company_id');
            })
            ->leftJoin('event_presenters', function ($join) {
                $join
                    ->on('event_presenters.event_id', '=', 'event_companies.event_id')
                    ->whereColumn('event_presenters.company_id', 'event_companies.company_id');
            })
            ->where('event_companies.event_id', $event->id)
            ->when($payload['eventPresenter'], function ($query, $string) {
                $query->whereRaw("(CASE event_companies.status
                    WHEN 4 THEN event_booking_logs.presenter_user_id
                    WHEN 5 THEN event_booking_logs.presenter_user_id
                    ELSE event_presenters.user_id
                END) = ?",[$string]);
            })
            ->when($payload['eventCompany'], function ($query, $string) {
                $query->where('event_companies.company_id', $string);
            })
            ->when($payload['eventStatus'], function ($query, $string) {
                $query->where('event_companies.status', $string);
            })
            ->groupBy('event_companies.company_id');

        if (!empty($payload['fromdate']) && !empty($payload['todate'])) {
            $fromdate = Carbon::parse($payload['fromdate'], $timezone)->setTime(0, 0, 0, 0)->setTimezone($appTimezone)->toDateTimeString();
            $todate   = Carbon::parse($payload['todate'], $timezone)->setTime(23, 59, 59, 0)->setTimezone($appTimezone)->toDateTimeString();
            $query
                ->whereIn('event_companies.status', [4, 5])
                ->where(function ($where) use ($fromdate, $todate) {
                    $where
                        ->whereRaw("TIMESTAMP(CONCAT(event_booking_logs.booking_date, ' ', event_booking_logs.start_time)) BETWEEN ? AND ?",[
                            $fromdate, $todate
                        ])
                        ->orWhereRaw("TIMESTAMP(CONCAT(event_booking_logs.booking_date, ' ', event_booking_logs.end_time)) BETWEEN ? AND ?",[
                            $fromdate, $todate
                        ]);
                });
        }

        if (isset($payload['order'])) {
            $column = $payload['columns'][$payload['order'][0]['column']]['data'];
            $order  = $payload['order'][0]['dir'];
            $query->orderBy($column, $order);
        } else {
            $query->orderByDesc('event_companies.id');
        }

        return [
            'total'  => $query->get()->count(),
            'record' => $query->offset($payload['start'])->limit($payload['length'])->get(),
        ];
    }
}
