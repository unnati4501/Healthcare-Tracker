<?php

namespace App\Models;

use App\Observers\NpsProjectObserver;
use Carbon\Carbon;
use DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\hasMany;
use Yajra\DataTables\Facades\DataTables;
use App\Events\SendProjectSurveyEvent;
use App\Jobs\ExportNpsProjectJob;

class NpsProject extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'nps_project';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = [
        'creator_id',
        'company_id',
        'start_date',
        'end_date',
        'title',
        'type',
        'survey_sent',
        'status',
        'public_survey_url',
        'created_at',
        'updated_at',
    ];

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
    protected $dates = ['start_date', 'end_date', 'created_at', 'updated_at'];

    /**
     * Boot model
     */
    protected static function boot()
    {
        parent::boot();

        static::observe(NpsProjectObserver::class);
    }

    /**
     * @return BelongsTo
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo('App\Models\Company');
    }

    /**
     * @return BelongsTo
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo('App\Models\User');
    }

    /**
     * @return HasMany
     */
    public function userNpsProjectLogs(): HasMany
    {
        return $this->HasMany('App\Models\UserNpsProjectLogs', 'nps_project_id', 'id');
    }

    public function getNPSProjectTableData($payload)
    {

        $list = $this->getNpsProjectData($payload);

        return DataTables::of($list)
            ->addColumn('start_date', function ($record) {
                return Carbon::parse($record->start_date)->format(config('zevolifesettings.date_format.default_date'));
            })
            ->addColumn('end_date', function ($record) {
                return Carbon::parse($record->end_date)->format(config('zevolifesettings.date_format.default_date'));
            })
            ->addColumn('proj_status', function ($record) {
                $now = now($record->timezone)->toDateString();

                if (Carbon::parse($record->start_date)->toDateString() <= $now && Carbon::parse($record->end_date)->toDateString() >= $now) {
                    return '<span class="text-success">Active</span>';
                } elseif (Carbon::parse($record->start_date)->toDateString() > $now) {
                    return '<span class="text-warning">Upcoming</span>';
                } else {
                    return '<span class="text-danger">Expired</span>';
                }
            })
            ->addColumn('actions', function ($record) {
                $now = now($record->timezone)->toDateString();
                $stDate = Carbon::parse($record->start_date)->toDateString();
                $edDate = Carbon::parse($record->end_date)->toDateString();
                return view('admin.projectsurvey.listaction', compact('record', 'now', 'stDate', 'edDate'))->render();
            })
            ->rawColumns(['actions','proj_status'])
            ->make(true);
    }

    public function getNpsProjectData($payload)
    {
        $user = auth()->user();
        $userCompany = $user->company()->first();
        $userRole           = $user->roles()->whereIn('slug', ['super_admin','company_admin'])->first();

        $now         = \now(config('app.timezone'))->toDateString();
        $appTimeZone = config('app.timezone');

        $userNPSData = self::leftJoin('user_nps_project_logs', 'nps_project.id', '=', 'user_nps_project_logs.nps_project_id')
            ->select('nps_project.*', DB::raw("count(user_nps_project_logs.nps_project_id) as response"), "company_locations.timezone")
            ->join("companies", "companies.id", "=", "nps_project.company_id")
            ->join("company_locations", function ($join) {
                $join->on("company_locations.company_id", "=", "companies.id")
                    ->where("company_locations.default", true);
            })
            ->where('nps_project.status', true)
            ->groupBy("nps_project.id")
            ->orderBy("nps_project.id", "DESC");

        if (!empty($userRole) && $userRole->slug == "company_admin") {
            $userNPSData->where("nps_project.company_id", "=", $userCompany->id);
        } else {
            $userNPSData->whereRaw("nps_project.start_date <= DATE(CONVERT_TZ(?, ?, company_locations.timezone))",[
                $now,$appTimeZone
            ]);
        }

        if (in_array('projecttextSearch', array_keys($payload)) && !empty($payload['projecttextSearch'])) {
            $userNPSData->where("nps_project.title", "like", '%' . $payload['projecttextSearch'] . '%');
        }

        if (in_array('projectcompany', array_keys($payload)) && !empty($payload['projectcompany'])) {
            $userNPSData->where("companies.id", "=", $payload['projectcompany']);
        }

        if (in_array('start_date', array_keys($payload)) && !empty($payload['start_date'])) {
            $userNPSData->where("nps_project.start_date", ">=", $payload['start_date']);
        }

        if (in_array('end_date', array_keys($payload)) && !empty($payload['end_date'])) {
            $userNPSData->where("nps_project.start_date", "<=", $payload['end_date']);
        }

        if (in_array('projectStatus', array_keys($payload)) && !empty($payload['projectStatus']) && $payload['projectStatus'] != "all") {
            if ($payload['projectStatus'] == "active") {
                $userNPSData->where(function ($query) use ($now) {
                    $query->where("nps_project.start_date", "<=", $now)
                        ->Where("nps_project.end_date", ">=", $now);
                });
            } elseif ($payload['projectStatus'] == "upcoming") {
                $userNPSData->whereRaw("nps_project.start_date > DATE(CONVERT_TZ(?, ?, company_locations.timezone))",[
                    $now,$appTimeZone
                ]);
            } else {
                $userNPSData->whereRaw("nps_project.end_date < DATE(CONVERT_TZ(?, ?, company_locations.timezone))",[
                    $now,$appTimeZone
                ]);
            }
        }

        return $userNPSData->get();
    }

    /**
     * store record data.
     *
     * @param payload
     * @return boolean
     */

    public function storeEntity(array $payload)
    {
        $user        = \Auth::user();
        $userCompany = $user->company()->first();

        $dataInput = [
            'start_date' => $payload['start_date'],
            'end_date'   => $payload['end_date'],
            'title'      => $payload['project_name'],
            'type'       => $payload['project_type'],
            'company_id' => (!empty($userCompany)) ? $userCompany->id : "",
            'creator_id' => $user->id,
        ];

        return self::create($dataInput);
    }

    /**
     * update record data.
     *
     * @param payload
     * @return boolean
     */

    public function updateEntity(array $payload)
    {
        $data = [
            'start_date' => $payload['start_date'],
            'end_date'   => $payload['end_date'],
            'title'      => $payload['project_name'],
        ];
        $updated = $this->update($data);

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
    public function deleteRecord()
    {
        if ($this->delete()) {
            return array('deleted' => 'true');
        }
        return array('deleted' => 'error');
    }

    public function getNpsProjectUserFeedBackTableData($payload)
    {
        $list = $this->getNpsProjectUserFeedBackData($payload);
        $feedBackType = config('zevolifesettings.nps_feedback_type');

        return DataTables::of($list)
            ->addColumn('logo', function ($record) {
                $logoUrl = (!empty($record->feedback_type)) ? getStaticNpsEmojiUrl($record->feedback_type) : getDefaultFallbackImageURL('nps', 'logo');
                return '<div class="table-img-sm table-img rounded-circle"><img src="' . $logoUrl . '" alt=""></div>';
            })
            ->addColumn('feedback_emoji', function ($record) use ($feedBackType) {
                return (!empty($feedBackType[$record->feedback_type])) ? $feedBackType[$record->feedback_type] : "";
            })
            ->rawColumns(['logo'])
            ->make(true);
    }

    public function getNpsProjectUserFeedBackData($payload)
    {
        $userNPSData = $this->userNpsProjectLogs()
                            ->orderBy("user_nps_project_logs.id", "DESC");

        if ($this->type == 'system') {
            $userNPSData = $userNPSData->join("users", "users.id", "=", "user_nps_project_logs.user_id")
                            ->select("user_nps_project_logs.*", DB::raw("CONCAT(users.first_name,' ',users.last_name) as fullName"), 'users.email');
        } else {
            $userNPSData = $userNPSData->select("user_nps_project_logs.*");
        }

        if (in_array('feedBackType', array_keys($payload)) && !empty($payload['feedBackType']) && $payload['feedBackType'] != "all") {
            $userNPSData->where("user_nps_project_logs.feedback_type", "=", $payload['feedBackType']);
        }

        return $userNPSData->get();
    }

    public function triggerProjectSurvey()
    {
        $company = Company::find($this->company_id);

        $userList = User::join("user_team", "user_team.user_id", "=", "users.id")
            ->where("user_team.company_id", $this->company_id)
            ->where("users.is_blocked", false)
            ->select("users.*")
            ->get();

        $data                = array();
        $data['logo']        = asset('assets/dist/img/zevo-white-logo.png');
        $data['sub_domain']  = "";
        $data['surveyLogId'] = $this->id;
        $data['companyName'] = $company->name;
        $data['surveyName'] = $this->title;

        if ($company->is_branding) {
            $brandingData       = getBrandingData($this->company_id);
            $data['logo']       = $brandingData->company_logo;
            $data['sub_domain'] = $brandingData->sub_domain;
        }

        foreach ($userList as $value) {
            // fire send survey event
            event(new SendProjectSurveyEvent($value, $data));
        }

        $survayInput['survey_sent'] = true;
        $this->update($survayInput);
    }

    
    public function exportNpsProjectDataEntity($payload)
    {
        $user        = auth()->user();
        return \dispatch(new ExportNpsProjectJob($payload, $user));
    }
}
