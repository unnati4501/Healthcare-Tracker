<?php

namespace App\Models;

use Auth;
use Carbon\Carbon;
use DB;
use App\Jobs\OccupationalHealthReportExportJob;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Yajra\DataTables\Facades\DataTables;

class OccupationalHealthReferral extends Model implements HasMedia
{
    use InteractsWithMedia;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'occupational_health_referral';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = [
        'cronofy_schedule_id',
        'created_by',
        'log_date',
        'is_confirmed',
        'confirmation_date',
        'note',
        'is_attended',
        'wellbeing_specialist_ids',
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
     * store record data.
     *
     * @param payload
     * @return boolean
     */
    public function storeEntity($payload, $cronofySchedule)
    {
        $user = auth()->user();
        $record = self::create([
            'cronofy_schedule_id'       => $cronofySchedule->id,
            'created_by'                => $user->id,
            'is_confirmed'              => $payload['is_confirmed'],
            'is_attended'               => $payload['is_attended'],
            'note'                      => $payload['note'],
            'confirmation_date'         => Carbon::parse($payload['confirmation_date'])->format('Y-m-d'),
            'log_date'                  => Carbon::parse($payload['log_date'])->format('Y-m-d'),
            'wellbeing_specialist_ids'  => $payload['wellbeing_specialist_ids'],
        ]);
        if ($record) {
            return true;
        }
        return false;
    }

    /**
     * get reports for occupational health
     *
     * @param array $payload
     * @return records
     */
    public function getOccupationalHealthReport($payload)
    {
        $list = $this->getOccupationalHealthRecords($payload);

        return DataTables::of($list)
            ->addColumn('note', function ($record) {
                return (strlen($record->note) > 20) ? substr($record->note,0,20)."<a href='javascript:void(0)' data-toggle='modal' data-target='#diplay-note-model' class='diplay-note-model' id='note-".$record->id."' data-content='".$record->note."'>...</a>" : $record->note;
            })
            ->rawColumns(['actions', 'note'])
            ->make(true);
    }

    /**
     * get records for occupational health
     *
     * @param array $payload
     * @return records
     */
    public function getOccupationalHealthRecords($payload)
    {
        $user        = auth()->user();
        $role        = getUserRole($user);
        $appTimezone = config('app.timezone');
        $timezone    = (!empty($user->timezone) ? $user->timezone : $appTimezone);
        $query = self::join('cronofy_schedule', 'cronofy_schedule.id', '=', 'occupational_health_referral.cronofy_schedule_id')
            ->join('users as user', 'cronofy_schedule.user_id', '=', 'user.id')
            ->join('users as wellbeingteamlead', 'occupational_health_referral.created_by', '=', 'wellbeingteamlead.id')
            ->join('users as wellbeingspecialist', 'occupational_health_referral.wellbeing_specialist_ids', '=', 'wellbeingspecialist.id')
            ->join('companies', 'companies.id', '=', 'cronofy_schedule.company_id')
            ->select(
                'occupational_health_referral.id',
                DB::raw("CONCAT(user.first_name, ' ', user.last_name) AS user_name"),
                'user.email AS user_email',
                'companies.name as company_name',
                DB::raw("DATE_FORMAT(occupational_health_referral.log_date,'%m/%d/%Y') as log_date"),
                'occupational_health_referral.is_confirmed',
                DB::raw("DATE_FORMAT(occupational_health_referral.confirmation_date,'%m/%d/%Y') as confirmation_date"),
                'occupational_health_referral.note',
                'occupational_health_referral.is_attended',
                DB::raw("CONCAT(wellbeingspecialist.first_name, ' ', wellbeingspecialist.last_name) AS ws_name"),
                DB::raw("CONCAT(wellbeingteamlead.first_name, ' ', wellbeingteamlead.last_name) AS referred_by"),
            );
        if(!empty($role) && $role->slug == 'wellbeing_team_lead'){
            $query->where('occupational_health_referral.created_by', $user->id);
        }

        if (in_array('userName', array_keys($payload)) && !empty($payload['userName'])) {
            $query->where(DB::raw("CONCAT(user.first_name,' ',user.last_name)"), 'like', '%' . $payload['userName'] . '%');
        }

        if (in_array('company', array_keys($payload)) && !empty($payload['company'])) {
            $query->where('cronofy_schedule.company_id', $payload['company']);
        }

        if (in_array('wellbeingSpecialist', array_keys($payload)) && !empty($payload['wellbeingSpecialist'])) {
            $query->where('occupational_health_referral.wellbeing_specialist_ids', $payload['wellbeingSpecialist']);
        }

        if ((isset($payload['fromDate']) && !empty($payload['fromDate'] && strtotime($payload['fromDate']) !== false)) && (isset($payload['toDate']) && !empty($payload['toDate'] && strtotime($payload['toDate']) !== false))) {
            $fromdate = Carbon::parse($payload['fromDate'], $timezone)->setTime(0, 0, 0, 0)->setTimezone($appTimezone)->toDateTimeString();
            $todate   = Carbon::parse($payload['toDate'], $timezone)->setTime(23, 59, 59, 0)->setTimezone($appTimezone)->toDateTimeString();
            $query->whereRaw("TIMESTAMP(occupational_health_referral.log_date) BETWEEN ? AND ?", [$fromdate, $todate]);
        }

        if (isset($payload['order'])) {
            $column = $payload['columns'][$payload['order'][0]['column']]['data'];
            $order  = $payload['order'][0]['dir'];
            $query->orderBy($column, $order);
        } else {
            $query->orderByDesc('occupational_health_referral.updated_at');
        }

        return $query->get();
    }

    /**
     * Export Report list
     *
     * @param $payload
     * @return array
     */
    public function exportOccupationalHealthReport($payload)
    {
        $user    = auth()->user();
        $records = $this->getOccupationalHealthRecords($payload);
        $email   = ($payload['email'] ?? $user->email);

        if ($records) {
            // Generate occupational health export report
            \dispatch(new OccupationalHealthReportExportJob($records->toArray(), $email, $user));
            return true;
        }
    }
}
