<?php

namespace App\Models;

use App\Events\UpcomingSessionEmailEvent;
use App\Models\User;
use App\Models\CronofySchedule;
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

class CronofySessionEmailLogs extends Model implements HasMedia
{
    use InteractsWithMedia;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'cronofy_session_email_logs';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = [
        'cronofy_schedule_id',
        'reason',
        'email_message',
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'meta'              => 'object',
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
     * "BelongsTo" relation to `cronofy_schedule` table
     * via `cronofy_schedule_id` field.
     *
     * @return BelongsTo
     */
    public function cronofySchedule(): BelongsTo
    {
        return $this->belongsTo(cronofySchedule::class, 'cronofy_schedule_id');
    }

    /**
     * WS send email to users
     *
     * @param array $payload
     * @return boolean
     */
    public function storeEmailLogs($payload, $cronofyScheduleData)
    {
        $logs = $this->create([
            'reason'                => $payload['reason'],
            'email_message'         => $payload['email_message'],
            'cronofy_schedule_id'   => $cronofyScheduleData->id,
        ]);
        if ($logs) {
            $user                   = User::find($cronofyScheduleData->user_id);
            $wellbeingSpecialist    = User::find($cronofyScheduleData->ws_id);
            $company                = $user->company()->select('companies.id')->first();
            $emailData  = [
                'reason'                => $payload['reason'],
                'email_message'         => $payload['email_message'],
                'company'               => (!empty($company->id) ? $company->id : null),
                'email'                 => $user->email,
                'wbsName'               => $wellbeingSpecialist->first_name,
                'serviceName'           => $cronofyScheduleData->name,
            ];
            event(new UpcomingSessionEmailEvent($emailData));
            return true;
        }
        return false;
    }

    /**
     * Set datatable for record list.
     * @param object $cronofySchedule
     * @param array $payload
     * @return dataTable
     */
    public function getEmailLogs($cronofySchedule, $payload)
    {
        $user = auth()->user();
        $list = $this->getEmailLogsRecordList($cronofySchedule, $payload);
        return DataTables::of($list)
        ->addColumn('reason', function ($record) {
            $resons = config('zevolifesettings.session_email_reasons');
            return $resons[$record->reason];
        })
        ->addColumn('created_at', function ($record) use ($user) {
            return Carbon::parse($record->created_at)->setTimezone($user->timezone)->format('M d, Y H:i');
        })
        ->make(true);
    }

    /**
     * get records list for datatable.
     * @param object $cronofySchedule
     * @param array $payload
     * @return array
     */
    public function getEmailLogsRecordList($cronofySchedule, $payload)
    {
        $query = self::where('cronofy_session_email_logs.cronofy_schedule_id', $cronofySchedule->id)
            ->select(
                'cronofy_session_email_logs.reason',
                'cronofy_session_email_logs.created_at'
            );
        return $query
            ->orderBy('cronofy_session_email_logs.id')
            ->get();
    }
}
