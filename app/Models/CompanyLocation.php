<?php

namespace App\Models;

use App\Models\Department;
use App\Models\DepartmentLocation;
use App\Models\Team;
use App\Models\TeamLocation;
use App\Jobs\ExportLocationJob;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Yajra\DataTables\Facades\DataTables;

class CompanyLocation extends Model
{

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'company_locations';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = [
        'company_id',
        'country_id',
        'state_id',
        'city_id',
        'name',
        'address_line1',
        'address_line2',
        'postal_code',
        'timezone',
        'default',
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
    protected $casts = ['default' => 'boolean'];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [];

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
    public function country(): BelongsTo
    {
        return $this->belongsTo('App\Models\Country');
    }

    /**
     * @return BelongsTo
     */
    public function state(): BelongsTo
    {
        return $this->belongsTo('App\Models\State');
    }

    /**
     * @return BelongsTo
     */
    public function timezone(): BelongsTo
    {
        return $this->belongsTo('App\Models\Timezone');
    }

    /**
     * @return HasMany
     */
    public function departmentLocation(): HasMany
    {
        return $this->hasMany('App\Models\DepartmentLocation');
    }

    /**
     * @return HasMany
     */
    public function teamLocation(): HasMany
    {
        return $this->hasMany('App\Models\TeamLocation');
    }

    /**
     * 'BelongsToMany' relation using 'department_location'
     * table via 'company_location_id'
     *
     * @return BelongsToMany
     */
    public function departments(): BelongsToMany
    {
        return $this->belongsToMany(Department::class, DepartmentLocation::class, 'company_location_id', 'department_id');
    }

    /**
     * 'BelongsToMany' relation using 'team_location'
     * table via 'company_location_id'
     *
     * @return BelongsToMany
     */
    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class, TeamLocation::class, 'company_location_id', 'team_id');
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
        return DataTables::of($list['record'])
            ->skipPaging()
            ->with([
                "recordsTotal"    => $list['total'],
                "recordsFiltered" => $list['total'],
            ])
            ->addColumn('actions', function ($record) {
                return view('admin.locations.listaction', compact('record'))->render();
            })
            ->rawColumns(['actions'])
            ->make(true);
    }

    /**
     * get record list for data table list.
     *
     * @param payload
     * @return recordList
     */

    public function getRecordList($payload)
    {
        $user    = auth()->user();
        $role    = getUserRole($user);
        $company = $user->company()->first();

        $query = $this
            ->select(
                'company_locations.*',
                \DB::raw("CONCAT(company_locations.address_line1, ' ', IFNULL(company_locations.address_line2, '')) AS address"),
                'companies.name AS company_name',
                'countries.name AS country_name',
                'states.name AS state_name'
            )
            ->withCount('departmentLocation', 'teamLocation')
            ->join('companies', function ($join) {
                $join->on('companies.id', '=', 'company_locations.company_id');
            })
            ->join('countries', function ($join) {
                $join->on('countries.id', '=', 'company_locations.country_id');
            })
            ->join('states', function ($join) {
                $join->on('states.id', '=', 'company_locations.state_id');
            });

        if ($role->group == 'reseller') {
            $query
                ->where(function ($where) use ($company) {
                    $where
                        ->where('company_locations.company_id', $company->id)
                        ->orWhere('companies.parent_id', $company->id);
                });
        } elseif ($role->group == 'company') {
            $query->where('company_locations.company_id', $company->id);
        }

        if (in_array('companyId', array_keys($payload)) && !empty($payload['companyId'])) {
            $query->where('company_locations.company_id', $payload['companyId']);
        }

        if (in_array('locationName', array_keys($payload)) && !empty($payload['locationName'])) {
            $query->where('company_locations.name', 'like', '%' . $payload['locationName'] . '%');
        }

        if (in_array('timezone', array_keys($payload)) && !empty($payload['timezone'])) {
            $query->where('company_locations.timezone', 'like', '%' . $payload['timezone'] . '%');
        }
        if (in_array('country', array_keys($payload)) && !empty($payload['country'])) {
            $query->where('company_locations.country_id', $payload['country']);
        }

        if (isset($payload['order']) && isset($payload['columns']) && in_array($payload['order'][0]['dir'], ['asc','desc']) && is_numeric($payload['columns'][$payload['order'][0]['column']]['data'])) {
            $column = $payload['columns'][$payload['order'][0]['column']]['data'];
            $order  = $payload['order'][0]['dir'];
            $query->orderBy($column, $order);
        } else {
            $query->orderByDesc('company_locations.updated_at');
        }

        return [
            'total'  => $query->get()->count(),
            'record' => $query->offset($payload['start'])->limit($payload['length'])->get(),
        ];
    }

    /**
     * store record data.
     *
     * @param payload
     * @return boolean
     */

    public function storeEntity($payload)
    {
        $data = [
            'company_id'    => $payload['company'],
            'country_id'    => $payload['country'],
            'state_id'      => $payload['county'],
            'name'          => $payload['name'],
            'address_line1' => $payload['address_line1'],
            'address_line2' => $payload['address_line2'],
            'postal_code'   => $payload['postal_code'],
            'timezone'      => $payload['timezone'],
            'default'       => false,
        ];

        $record = self::create($data);

        if ($record) {
            return true;
        }

        return false;
    }

    /**
     * update record data.
     *
     * @param payload , $id
     * @return boolean
     */

    public function updateEntity($payload)
    {

        $data = [
            'country_id'    => $payload['country'],
            'state_id'      => $payload['county'],
            'name'          => $payload['name'],
            'address_line1' => $payload['address_line1'],
            'address_line2' => $payload['address_line2'],
            'postal_code'   => $payload['postal_code'],
            'timezone'      => $payload['timezone'],
        ];

        $updated = $this->update($data);

        if ($updated) {
            return true;
        }

        return false;
    }

    /**
     * fatch record data by record id.
     *
     * @param $id
     * @return record data
     */

    public function getRecordDataById($id)
    {
        return self::find($id);
    }

    /**
     * delete record by record id.
     *
     * @param $id
     * @return array
     */

    public function deleteRecord()
    {
        if ($this->departmentLocation->count() > 0 || $this->teamLocation->count() > 0) {
            return array('deleted' => 'error');
        }

        if ($this->delete()) {
            return array('deleted' => 'true');
        }
        return array('deleted' => 'error');
    }

    /**
     * duplication check for department data.
     *
     * @param $payload , $id
     * @return array
     */

    public function duplicationCheck($payload, $id = '')
    {
        $data = CompanyLocation::where("name", $payload['name'])
            ->where("company_id", $payload['company']);
        if (!empty($id)) {
            $data = $data->where("id", "!=", $id);
        }
        return $data->first();
    }

    public function exportLocationDataEntity($payload)
    {
        $user        = auth()->user();
        return \dispatch(new ExportLocationJob($payload, $user));
    }
}
