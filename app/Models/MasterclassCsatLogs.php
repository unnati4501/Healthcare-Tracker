<?php

namespace App\Models;

use App\Models\Course;
use App\Models\User;
use App\Jobs\ExportMasterclassFeedbackReportJob;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Yajra\DataTables\Facades\DataTables;

class MasterclassCsatLogs extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'masterclass_csat_user_logs';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['user_id', 'course_id', 'feedback_type', 'feedback'];

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
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    /**
     * To get masterclass feedback list
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
            ->addColumn('emoji', function ($record) {
                return getStaticNpsEmojiUrl((!empty($record->feedback_type) ? $record->feedback_type : 'very_happy'));
            })
            ->addColumn('feedback_type', function ($record) use ($feedBackType) {
                return $feedBackType[(!empty($record->feedback_type) ? $record->feedback_type : 'very_happy')];
            })
            ->make(true);
    }

    /**
     * To get masterclass feedback list
     *
     * @param Event $event
     * @param array records list
     */
    public function getRecordList($payload)
    {
        $query = $this
            ->select(
                'masterclass_csat_user_logs.id',
                'sub_categories.name AS category_name',
                'companies.name AS company_name',
                \DB::raw("CONCAT(users.first_name, ' ', users.last_name) AS author_name"),
                'courses.title AS course_title',
                'masterclass_csat_user_logs.feedback',
                'masterclass_csat_user_logs.feedback_type',
                'masterclass_csat_user_logs.created_at'
            )
            ->join('courses', 'courses.id', '=', 'masterclass_csat_user_logs.course_id')
            ->join('users', 'users.id', '=', 'courses.creator_id')
            ->join('companies', 'companies.id', '=', 'masterclass_csat_user_logs.company_id')
            ->join('sub_categories', 'sub_categories.id', 'courses.sub_category_id')
            ->when($payload['category'], function ($query, $category) {
                $query->where('courses.sub_category_id', $category);
            })
            ->when($payload['course'], function ($query, $courseId) {
                $query->where('courses.id', $courseId);
            })
            ->when($payload['company'], function ($query, $company) {
                $query->where("masterclass_csat_user_logs.company_id", $company);
            })
            ->when($payload['author'], function ($query, $author) {
                $query->where('courses.creator_id', $author);
            })
            ->when($payload['feedback'], function ($query, $feedback) {
                if ($feedback != 'all') {
                    $query->where("masterclass_csat_user_logs.feedback_type", $feedback);
                }
            });

        if (isset($payload['order'])) {
            $column = $payload['columns'][$payload['order'][0]['column']]['data'];
            $order  = $payload['order'][0]['dir'];
            $query->orderBy($column, $order);
        } else {
            $query->orderByDesc('masterclass_csat_user_logs.updated_at');
        }

        return [
            'total'  => $query->count('masterclass_csat_user_logs.id'),
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
    public function getCsatGraph($payload)
    {
        $graphData              = [];
        $feedbackTypes          = array_reverse(config('zevolifesettings.nps_feedback_type'));
        $feedbackTypesWithClass = config('zevolifesettings.feedback_class_color');
        $totalFeedback          = $this
            ->join('courses', 'courses.id', '=', 'masterclass_csat_user_logs.course_id')
            ->join('users', 'users.id', '=', 'courses.creator_id')
            ->join('companies', 'companies.id', '=', 'masterclass_csat_user_logs.company_id')
            ->join('sub_categories', 'sub_categories.id', 'courses.sub_category_id')
            ->when($payload['category'], function ($query, $category) {
                $query->where('courses.sub_category_id', $category);
            })
            ->when($payload['course'], function ($query, $courseId) {
                $query->where('courses.id', $courseId);
            })
            ->when($payload['company'], function ($query, $company) {
                $query->where("masterclass_csat_user_logs.company_id", $company);
            })
            ->when($payload['author'], function ($query, $author) {
                $query->where('courses.creator_id', $author);
            })
            ->when($payload['feedback'], function ($query, $feedback) {
                if ($feedback != 'all') {
                    $query->where("masterclass_csat_user_logs.feedback_type", $feedback);
                }
            })
            ->count('masterclass_csat_user_logs.id');

        $feedbacks = $this
            ->select(
                'masterclass_csat_user_logs.feedback_type',
                \DB::raw("COUNT(masterclass_csat_user_logs.feedback_type) as responseCount")
            )
            ->join('courses', 'courses.id', '=', 'masterclass_csat_user_logs.course_id')
            ->join('users', 'users.id', '=', 'courses.creator_id')
            ->join('companies', 'companies.id', '=', 'masterclass_csat_user_logs.company_id')
            ->join('sub_categories', 'sub_categories.id', 'courses.sub_category_id')
            ->when($payload['category'], function ($query, $category) {
                $query->where('courses.sub_category_id', $category);
            })
            ->when($payload['course'], function ($query, $courseId) {
                $query->where('courses.id', $courseId);
            })
            ->when($payload['company'], function ($query, $company) {
                $query->where("masterclass_csat_user_logs.company_id", $company);
            })
            ->when($payload['author'], function ($query, $author) {
                $query->where('courses.creator_id', $author);
            })
            ->when($payload['feedback'], function ($query, $feedback) {
                if ($feedback != 'all') {
                    $query->where("masterclass_csat_user_logs.feedback_type", $feedback);
                }
            })
            ->groupBy('masterclass_csat_user_logs.feedback_type')
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


    public function exportMasterclassFeedbackDataEntity($payload)
    {
        $user        = auth()->user();
        return \dispatch(new ExportMasterclassFeedbackReportJob($payload, $user));
    }
}
