<?php

namespace App\Models;

use App\Models\Challenge;
use App\Models\Company;
use App\Models\CompanyLocation;
use App\Models\DepartmentLocation;
use App\Models\TeamLocation;
use App\Jobs\ExportDepartmentJob;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Yajra\DataTables\Facades\DataTables;

class Department extends Model
{

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'departments';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = [
        'company_id',
        'name',
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
    protected $casts = [
        'default' => 'boolean',
    ];

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
     * @return HasMany
     */
    public function teams(): HasMany
    {
        return $this->hasMany('App\Models\Team');
    }

    /**
     * @return HasMany
     */
    public function users(): HasMany
    {
        return $this->hasMany('App\Models\User');
    }

    /**
     * One-to-Many relations with Department.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function departmentlocations()
    {
        return $this->belongsToMany(CompanyLocation::class, 'department_location', 'department_id', 'company_location_id');
    }

    public function locations(): HasMany
    {
        return $this->hasMany(DepartmentLocation::class, 'department_id');
    }

    /**
     * @return BelongsToMany
     */
    public function members(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\User', 'user_team', 'department_id', 'user_id')->withPivot('team_id', 'company_id')->withTimestamps();
    }

    /**
     * @return HasMany
     */
    public function userteams(): HasMany
    {
        return $this->hasMany('App\Models\UserTeam');
    }

    /**
     * @return BelongsToMany
     */
    public function locationWiseTeams(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\Team', 'team_location', 'department_id', 'team_id')
            ->withPivot('company_location_id', 'company_id')
            ->withTimestamps();
    }

    /**
     * Set datatable for role list.
     *
     * @param payload
     * @return dataTable
     */

    public function getTableData($payload)
    {
        $list = $this->getDepartmentList($payload);
        return DataTables::of($list['record'])
            ->skipPaging()
            ->with([
                "recordsTotal"    => $list['total'],
                "recordsFiltered" => $list['total'],
            ])
            ->addColumn('actions', function ($department) {
                return view('admin.department.listaction', compact('department'))->render();
            })
            ->rawColumns(['actions'])
            ->make(true);
    }

    /**
     * get department list for data table list.
     *
     * @param payload
     * @return roleList
     */

    public function getDepartmentList($payload)
    {
        $user    = auth()->user();
        $role    = getUserRole($user);
        $company = $user->company()->first();

        $query = $this
            ->select(
                'departments.*',
                'companies.name AS company_name'
            )
            ->withCount('teams', 'members')
            ->join('companies', function ($join) {
                $join->on('companies.id', '=', 'departments.company_id');
            });

        if ($role->group == 'reseller') {
            $query
                ->where(function ($where) use ($company) {
                    $where
                        ->where('departments.company_id', $company->id)
                        ->orWhere('companies.parent_id', $company->id);
                });
        } elseif ($role->group == 'company') {
            $query->where('departments.company_id', $company->id);
        }

        if (in_array('department', array_keys($payload)) && !empty($payload['department'])) {
            $query->where('departments.name', 'like', '%' . $payload['department'] . '%');
        }

        if (in_array('company', array_keys($payload)) && !empty($payload['company'])) {
            $query->where('departments.company_id', $payload['company']);
        }

        if (isset($payload['order']) && isset($payload['columns']) && in_array($payload['order'][0]['dir'], ['asc','desc']) && is_numeric($payload['columns'][$payload['order'][0]['column']]['data'])) {
            $column = $payload['columns'][$payload['order'][0]['column']]['data'];
            $order  = $payload['order'][0]['dir'];
            $query->orderBy($column, $order);
        } else {
            $query->orderByDesc('departments.updated_at');
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

    public function storeEntity(array $payload)
    {
        $user       = auth()->user();
        $role       = getUserRole($user);
        $company    = Company::select('companies.id', 'companies.auto_team_creation', 'companies.team_limit')->find($payload['company_id']);
        $department = $this->create([
            'name'       => $payload['name'],
            'company_id' => $payload['company_id'],
            'created_at' => Carbon::now(),
        ]);

        if ($department) {
            $departmentLocations = [];
            $locations           = $payload['location'];
            $namingConvention    = (!empty($payload['naming_convention']) ? $payload['naming_convention'] : []);
            $employeeCount       = (!empty($payload['employee_count']) ? $payload['employee_count'] : []);
            $teamLimit           = (!empty($company->team_limit) ? $company->team_limit : 1);

            // sync location of departments
            foreach ($locations as $location) {
                $auto_team_creation_meta = null;
                if (isset($namingConvention[$location])) {
                    $auto_team_creation_meta = json_encode([
                        'no_of_employee'    => $employeeCount[$location],
                        'possible_teams'    => (int) ceil($employeeCount[$location] / $teamLimit),
                        'naming_convention' => $namingConvention[$location],
                    ]);
                }

                $departmentLocations[] = [
                    'company_id'              => $department->company_id,
                    'department_id'           => $department->id,
                    'company_location_id'     => $location,
                    'auto_team_creation_meta' => $auto_team_creation_meta,
                ];
            }
            $department->departmentlocations()->sync($departmentLocations);

            // generate teams
            if ($role->group == 'company' && $company->auto_team_creation) {
                $ongoingCGChallenges = Challenge::where('company_id', $company->id)
                    ->where('challenge_type', 'company_goal')
                    ->where('finished', 0)
                    ->where('cancelled', 0)
                    ->get();

                foreach ($locations as $location) {
                    $teamsCount = (int) ceil($employeeCount[$location] / $teamLimit);
                    $teamNames  = generateUniqueTeamNames($namingConvention[$location], $teamsCount, $department->id, $location);

                    foreach ($teamNames as $teamName) {
                        // create team
                        $team = $department->teams()->create([
                            'name'       => $teamName,
                            'company_id' => $company->id,
                        ]);

                        // assign location
                        $team->teamlocation()->sync([[
                            'company_location_id' => (int) $location,
                            'company_id'          => $company->id,
                            'department_id'       => $department->id,
                        ]]);

                        // assign team to company_goal challenge if any running
                        if (!empty($ongoingCGChallenges)) {
                            $ongoingCGChallenges->each(function ($challenge) use ($team) {
                                $challenge->memberTeams()->attach($team->id);
                            });
                        }
                    }
                }
            }

            return true;
        } else {
            return false;
        }
    }

    /**
     * Update department data
     *
     * @param array payload
     * @return boolean
     */
    public function updateEntity(array $payload)
    {
        $user    = auth()->user();
        $role    = getUserRole($user);
        $company = Company::select('companies.id', 'companies.auto_team_creation', 'companies.team_limit')
            ->find($payload['company_id']);
        $updated = $this->update([
            'name' => $payload['name'],
        ]);

        if ($updated) {
            $locations        = $payload['location'];
            $deptId           = $this->id;
            $namingConvention = (!empty($payload['naming_convention']) ? $payload['naming_convention'] : []);
            $employeeCount    = (!empty($payload['employee_count']) ? $payload['employee_count'] : []);
            $teamLimit        = (!empty($company->team_limit) ? $company->team_limit : 1);

            // sync location of departments
            $departmentLocations = [];
            foreach ($locations as $location) {
                $departmentLocations[$location] = [
                    'company_id'          => $this->company_id,
                    'department_id'       => $this->id,
                    'company_location_id' => $location,
                ];
                if (isset($namingConvention[$location])) {
                    $departmentLocations[$location]['auto_team_creation_meta'] = json_encode([
                        'no_of_employee'    => $employeeCount[$location],
                        'possible_teams'    => (int) ceil($employeeCount[$location] / $teamLimit),
                        'naming_convention' => $namingConvention[$location],
                    ]);
                }
            }
            $locationStatus   = $this->departmentlocations()->sync($departmentLocations);
            $newLocations     = (!empty($locationStatus['attached']) ? $locationStatus['attached'] : []);
            $updatedLocations = (!empty($locationStatus['updated']) ? $locationStatus['updated'] : []);

            // generate teams if auto team creation is enabled
            if ($role->group == 'company' && $company->auto_team_creation) {
                if (!empty($updatedLocations)) {
                    $newLocations = array_merge($newLocations, $updatedLocations);
                }

                if (!empty($newLocations)) {
                    $ongoingCGChallenges = Challenge::where('company_id', $company->id)
                        ->where('challenge_type', 'company_goal')
                        ->where('finished', 0)
                        ->where('cancelled', 0)
                        ->get();

                    foreach ($newLocations as $location) {
                        $teamsCount = (int) ceil($employeeCount[$location] / $teamLimit);
                        $teamNames  = generateUniqueTeamNames($namingConvention[$location], $teamsCount, $deptId, $location);

                        foreach ($teamNames as $teamName) {
                            // create team
                            $team = $this->teams()->create([
                                'name'       => $teamName,
                                'company_id' => $company->id,
                            ]);

                            // assign location
                            $team->teamlocation()->sync([[
                                'company_location_id' => (int) $location,
                                'company_id'          => $company->id,
                                'department_id'       => $this->id,
                            ]]);

                            // assign team to company_goal challenge if any running
                            if (!empty($ongoingCGChallenges)) {
                                $ongoingCGChallenges->each(function ($challenge) use ($team) {
                                    $challenge->memberTeams()->attach($team->id);
                                });
                            }
                        }
                    }
                }
            }

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
    public function deleteDepartment()
    {
        if ($this->members->count() > 0) {
            return array('deleted' => 'error');
        }

        if ($this->delete()) {
            return array('deleted' => 'true');
        }
        return array('deleted' => 'error');
    }

    /**
     * get location data table.
     *
     * @param $payload , $id
     * @return array
     */

    public function getLocationTableData($id = '')
    {
        $list = Department::find($id);
        return DataTables::of($list->departmentlocations)
            ->addColumn('locationname', function ($departmentlocations) {
                return $departmentlocations->name;
            })
            ->addColumn('country', function ($departmentlocations) {
                return $departmentlocations->country->name;
            })
            ->addColumn('state', function ($departmentlocations) {
                return $departmentlocations->state->name;
            })
            ->addColumn('time_zone', function ($departmentlocations) {
                return $departmentlocations->timezone;
            })
            ->addColumn('address', function ($departmentlocations) {
                return $departmentlocations->address_line1 . " " . $departmentlocations->address_line2;
            })
            ->make(true);
    }

    public function exportDepartmentDataEntity($payload)
    {
        $user        = auth()->user();
        \dispatch(new ExportDepartmentJob($payload, $user));
        return true;
    }
}
