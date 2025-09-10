<?php

namespace App\Models;

use App\Models\Calendly;
use App\Models\User;
use Carbon\Carbon;
use App\Jobs\ExportCounsellorFeedbackReportJob;
use DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Yajra\DataTables\Facades\DataTables;

class EapCsatLogs extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'eap_csat_user_logs';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['user_id', 'company_id', 'eap_calendy_id', 'feedback_type', 'feedback'];

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
     * "BelongsTo" relation to `eap_calendly` table
     * via `eap_calendy_id` field.
     *
     * @return BelongsTo
     */
    public function calendly(): BelongsTo
    {
        return $this->belongsTo(Calendly::class, 'eap_calendy_id');
    }

    /**
     * To get EAP CSAT feedback graph data
     *
     * @param Event $event
     * @param array payload
     * @return array
     */
    public function getCsatGraph($payload)
    {
        $graphData              = [];
        $feedbackTypes          = array_reverse(config('zevolifesettings.nps_feedback_type'));
        $feedbackTypesWithClass = config('zevolifesettings.feedback_class_color');
        $totalFeedback          = $this
            ->join('eap_calendly', 'eap_calendly.id', '=', 'eap_csat_user_logs.eap_calendy_id')
            ->join('users', 'users.id', '=', 'eap_calendly.therapist_id')
            ->join('companies', 'companies.id', '=', 'eap_csat_user_logs.company_id');

        if (in_array('duration', array_keys($payload)) && !empty($payload['duration'])) {
            $totalFeedback = $totalFeedback
                ->where(function ($totalFeedback) use ($payload) {
                    $last24Hours = Carbon::now()->subDays()->toDateTimeString();
                    $last7Days   = Carbon::now()->subDays(7)->toDateTimeString();
                    $last30Days  = Carbon::now()->subDays(30)->toDateTimeString();

                    if ($payload['duration'] == 'last_24') {
                        $totalFeedback->where('eap_csat_user_logs.created_at', '>=', $last24Hours);
                    } elseif ($payload['duration'] == 'last_7') {
                        $totalFeedback->where('eap_csat_user_logs.created_at', '>=', $last7Days);
                    } elseif ($payload['duration'] == 'last_30') {
                        $totalFeedback->where('eap_csat_user_logs.created_at', '>=', $last30Days);
                    } else {
                        $totalFeedback->whereNotNull('eap_csat_user_logs.created_at');
                    }
                });
        }

        if (in_array('company', array_keys($payload)) && !empty($payload['company'])) {
            $totalFeedback = $totalFeedback->where("eap_csat_user_logs.company_id", $payload['company']);
        }

        if (in_array('counsellor', array_keys($payload)) && !empty($payload['counsellor'])) {
            $totalFeedback = $totalFeedback->where(function ($query) use ($payload) {
                $query->where('first_name', "like", "%" . $payload['counsellor'] . "%");
                $query->orWhere('last_name', "like", "%" . $payload['counsellor'] . "%");
                $query->orWhere('email', 'like', '%' . $payload['counsellor'] . '%');
            });
        }
        if (in_array('feedback', array_keys($payload)) && !empty($payload['feedback']) && $payload['feedback'] != 'all') {
            $totalFeedback = $totalFeedback->where("eap_csat_user_logs.feedback_type", $payload['feedback']);
        }
        $totalFeedback = $totalFeedback->count('eap_csat_user_logs.id');

        $feedbacks = $this
            ->select(
                'eap_csat_user_logs.feedback_type',
                \DB::raw("COUNT(eap_csat_user_logs.feedback_type) as responseCount")
            )
            ->join('eap_calendly', 'eap_calendly.id', '=', 'eap_csat_user_logs.eap_calendy_id')
            ->join('users', 'users.id', '=', 'eap_calendly.therapist_id')
            ->join('companies', 'companies.id', '=', 'eap_csat_user_logs.company_id');
        if (in_array('duration', array_keys($payload)) && !empty($payload['duration'])) {
            $feedbacks = $feedbacks
                ->where(function ($feedbacks) use ($payload) {
                    $last24Hours = Carbon::now()->subDays()->toDateTimeString();
                    $last7Days   = Carbon::now()->subDays(7)->toDateTimeString();
                    $last30Days  = Carbon::now()->subDays(30)->toDateTimeString();

                    if ($payload['duration'] == 'last_24') {
                        $feedbacks->where('eap_csat_user_logs.created_at', '>=', $last24Hours);
                    } elseif ($payload['duration'] == 'last_7') {
                        $feedbacks->where('eap_csat_user_logs.created_at', '>=', $last7Days);
                    } elseif ($payload['duration'] == 'last_30') {
                        $feedbacks->where('eap_csat_user_logs.created_at', '>=', $last30Days);
                    } else {
                        $feedbacks->whereNotNull('eap_csat_user_logs.created_at');
                    }
                });
        }

        if (in_array('company', array_keys($payload)) && !empty($payload['company'])) {
            $feedbacks = $feedbacks->where("eap_csat_user_logs.company_id", $payload['company']);
        }

        if (in_array('counsellor', array_keys($payload)) && !empty($payload['counsellor'])) {
            $feedbacks = $feedbacks->where(function ($query) use ($payload) {
                $query->where('first_name', "like", "%" . $payload['counsellor'] . "%");
                $query->orWhere('last_name', "like", "%" . $payload['counsellor'] . "%");
                $query->orWhere('email', 'like', '%' . $payload['counsellor'] . '%');
            });
        }
        if (in_array('feedback', array_keys($payload)) && !empty($payload['feedback']) && $payload['feedback'] != 'all') {
            $feedbacks = $feedbacks->where("eap_csat_user_logs.feedback_type", $payload['feedback']);
        }

        $feedbacks->groupBy('eap_csat_user_logs.feedback_type');
        $feedbacks = $feedbacks->get()->pluck('responseCount', 'feedback_type')->toArray();

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

    /**
     * To get eap feedback list
     *
     * @param array payload
     * @return dataTable
     */
    public function getTableData($payload)
    {
        $feedBackType = config('zevolifesettings.nps_feedback_type');
        $list         = $this->getRecordList($payload);

        return DataTables::of($list['record'])
            ->skipPaging()
            ->with([
                "recordsTotal"    => $list['total'],
                "recordsFiltered" => $list['total'],
            ])
            ->addColumn('duration', function ($record) {
                return Carbon::parse($record->end_time)->diffInMinutes($record->start_time);
            })
            ->addColumn('emoji', function ($record) {
                return getStaticNpsEmojiUrl((!empty($record->feedback_type) ? $record->feedback_type : 'very_happy'));
            })
            ->addColumn('feedback_type', function ($record) use ($feedBackType) {
                return $feedBackType[(!empty($record->feedback_type) ? $record->feedback_type : 'very_happy')];
            })
            ->make(true);
    }
    /**
     * Get the record list from eap_csat_user_logs table
     */
    public function getRecordList($payload)
    {
        $query = self::with('user')
            ->select(
                'eap_csat_user_logs.id',
                'companies.name AS company_name',
                \DB::raw("CONCAT(users.first_name, ' ', users.last_name) AS counsellor_name"),
                'users.email as counsellor_email',
                'eap_calendly.end_time',
                'eap_calendly.start_time',
                \DB::raw('0 as duration'),
                'eap_csat_user_logs.feedback as feedback_text',
                'eap_csat_user_logs.feedback_type',
                'eap_csat_user_logs.created_at'
            )
            ->join('eap_calendly', 'eap_calendly.id', '=', 'eap_csat_user_logs.eap_calendy_id')
            ->join('users', 'users.id', '=', 'eap_calendly.therapist_id')
            ->join('companies', 'companies.id', '=', 'eap_csat_user_logs.company_id')
        ;
        if (in_array('duration', array_keys($payload)) && !empty($payload['duration'])) {
            $query = $query
                ->where(function ($query) use ($payload) {
                    $last24Hours = Carbon::now()->subDays()->toDateTimeString();
                    $last7Days   = Carbon::now()->subDays(7)->toDateTimeString();
                    $last30Days  = Carbon::now()->subDays(30)->toDateTimeString();

                    if ($payload['duration'] == 'last_24') {
                        $query->where('eap_csat_user_logs.created_at', '>=', $last24Hours);
                    } elseif ($payload['duration'] == 'last_7') {
                        $query->where('eap_csat_user_logs.created_at', '>=', $last7Days);
                    } elseif ($payload['duration'] == 'last_30') {
                        $query->where('eap_csat_user_logs.created_at', '>=', $last30Days);
                    } else {
                        $query->whereNotNull('eap_csat_user_logs.created_at');
                    }
                });
        }

        if (in_array('company', array_keys($payload)) && !empty($payload['company'])) {
            $query = $query->where("eap_csat_user_logs.company_id", $payload['company']);
        }
        if (in_array('counsellor', array_keys($payload)) && !empty($payload['counsellor'])) {
            $query->where(DB::raw("CONCAT(users.first_name,' ',users.last_name)"), 'like', '%' . $payload['counsellor'] . '%');
        }
        if (in_array('feedback', array_keys($payload)) && !empty($payload['feedback']) && $payload['feedback'] != 'all') {
            $query = $query->where("eap_csat_user_logs.feedback_type", $payload['feedback']);
        }

        $query->orderByDesc('eap_csat_user_logs.updated_at');

        if (isset($payload['order'])) {
            $column = $payload['columns'][$payload['order'][0]['column']]['data'];
            $order  = $payload['order'][0]['dir'];
            $query->orderBy($column, $order);
        } else {
            $query->orderByDesc('eap_csat_user_logs.updated_at')->get();
        }
        return [
            'total'  => $query->count('eap_csat_user_logs.id'),
            'record' => $query->offset($payload['start'])->limit($payload['length'])->get(),
        ];
    }

    public function exportCounsellorFeedbackDataEntity($payload)
    {
        $user        = auth()->user();
        return \dispatch(new ExportCounsellorFeedbackReportJob($payload, $user));
    }
}
