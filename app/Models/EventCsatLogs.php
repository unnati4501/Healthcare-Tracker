<?php

namespace App\Models;

use App\Models\EventBookingLogs;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Yajra\DataTables\Facades\DataTables;

class EventCsatLogs extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'event_csat_user_logs';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['user_id', 'event_booking_log_id', 'feedback_type', 'feedback'];

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
     * "BelongsTo" relation to `event_booking_logs` table
     * via `event_booking_log_id` field.
     *
     * @return BelongsTo
     */
    public function bookingLog(): BelongsTo
    {
        return $this->belongsTo(EventBookingLogs::class, 'event_booking_log_id');
    }

    /**
     * To get CSAT feedback reports
     *
     * @param Event $event
     * @param array payload
     * @return dataTable
     */
    public function getTableData($event, $payload)
    {
        $feedBackType = config('zevolifesettings.nps_feedback_type');
        $list         = $this->getRecordList($event, $payload);
        return DataTables::of($list['record'])
            ->skipPaging()
            ->with([
                "recordsTotal"    => $list['total'],
                "recordsFiltered" => $list['total'],
            ])
            ->addColumn('emoji', function ($record) {
                return getStaticNpsEmojiUrl((!empty($record->feedback_type) ? $record->feedback_type : 'very_happy'));
            })
            ->addColumn('feedback_type', function ($record) use ($feedBackType) {
                return $feedBackType[(!empty($record->feedback_type) ? $record->feedback_type : 'very_happy')];
            })
            ->make(true);
    }

    /**
     * get record list for data table list.
     *
     * @param Event $event
     * @param array records list
     */
    public function getRecordList($event, $payload)
    {
        $query = $event->csat()
            ->select(
                'event_csat_user_logs.id',
                \DB::raw("CONCAT(user.first_name, ' ', user.last_name) AS username"),
                'user.email',
                'companies.name AS company_name',
                \DB::raw('event_booking_logs.meta->>"$.presenter" AS presenter_name'),
                'event_csat_user_logs.feedback',
                'event_csat_user_logs.feedback_type',
                'event_csat_user_logs.created_at'
            )
            ->join('users AS user', 'user.id', '=', 'event_csat_user_logs.user_id')
            ->join('companies', 'companies.id', '=', 'event_booking_logs.company_id')
            ->when($payload['company'], function ($query, $company) {
                $query->where("event_booking_logs.company_id", $company);
            })
            ->when($payload['feedback'], function ($query, $feedback) {
                $query->where("event_csat_user_logs.feedback_type", $feedback);
            })
            ->when($payload['presenter'], function ($query, $presenter) {
                $query->where('event_booking_logs.presenter_user_id', $presenter);
            });

        if (isset($payload['order'])) {
            $column = $payload['columns'][$payload['order'][0]['column']]['data'];
            $order  = $payload['order'][0]['dir'];
            $query->orderBy($column, $order);
        } else {
            $query->orderByDesc('event_csat_user_logs.updated_at');
        }

        return [
            'total'  => $query->count('event_csat_user_logs.id'),
            'record' => $query->offset($payload['start'])->limit($payload['length'])->get(),
        ];
    }

    /**
     * To get CSAT feedback graph data
     *
     * @param Event $event
     * @param array payload
     * @return array
     */
    public function getCsatGraph($event, $payload)
    {
        $graphData              = [];
        $feedbackTypes          = array_reverse(config('zevolifesettings.nps_feedback_type'));
        $feedbackTypesWithClass = config('zevolifesettings.feedback_class_color');
        $totalFeedback          = $event->csat()
            ->when($payload['company'], function ($query, $company) {
                $query->where("event_booking_logs.company_id", $company);
            })
            ->when($payload['presenter'], function ($query, $presenter) {
                $query->where('event_booking_logs.presenter_user_id', $presenter);
            })
            ->count('event_csat_user_logs.id');
        $feedbacks = $event->csat()
            ->select(
                'event_csat_user_logs.feedback_type',
                \DB::raw("COUNT(event_csat_user_logs.feedback_type) as responseCount")
            )
            ->when($payload['company'], function ($query, $company) {
                $query->where("event_booking_logs.company_id", $company);
            })
            ->when($payload['presenter'], function ($query, $presenter) {
                $query->where('event_booking_logs.presenter_user_id', $presenter);
            })
            ->groupBy('event_csat_user_logs.feedback_type')
            ->get()
            ->pluck('responseCount', 'feedback_type')
            ->toArray();

        foreach ($feedbackTypes as $type => $value) {
            if (array_key_exists($type, $feedbacks)) {
                $graphData[] = [
                    'name'       => $value,
                    'class'      => $feedbackTypesWithClass[$type],
                    'percentage' => (($feedbacks[$type] / $totalFeedback) * 100),
                ];
            }
        }

        return ['data' => $graphData];
    }
}
