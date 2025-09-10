<?php

namespace App\Models;

use App\Jobs\SendEapPushNotifications;
use App\Models\DepartmentLocation;
use App\Models\EapCompany;
use App\Models\EapDepartment;
use App\Models\EAPOrderPriority;
use App\Models\User;
use App\Observers\EapObserver;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\hasMany;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Yajra\DataTables\Facades\DataTables;

class EAP extends Model implements HasMedia
{
    use InteractsWithMedia;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'eap_list';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = [
        'creator_id',
        'company_id',
        'title',
        'telephone',
        'email',
        'website',
        'description',
        'deep_link_uri',
        'locations',
        'departments',
        'is_rca',
        'is_stick',
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
    protected $dates = [
        'created_at',
        'updated_at',
    ];

    /**
     * Boot model
     */
    protected static function boot()
    {
        parent::boot();

        static::observe(EapObserver::class);
    }

    /**
     * Custom builder instantiator. newEloquentBuilder is part
     * of Laravel.
     */
    public function newEloquentBuilder($query)
    {
        return new \App\Builders\BaseBuilder($query);
    }

    /**
     * One-to-Many relations with Company
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function eapcompany(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\Company', 'eap_company', 'eap_id', 'company_id')
            ->withTimestamps();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function eapList(): HasMany
    {
        return $this->hasMany('App\Models\EapCompany', 'eap_id');
    }

    /**
     * @return BelongsToMany
     */
    public function eapUserLogs(): BelongsToMany
    {
        return $this
            ->belongsToMany('App\Models\User', 'eap_logs', 'eap_id', 'user_id')
            ->withPivot('view_count')
            ->withTimestamps();
    }

    /**
     * @return hasMany
     */
    public function eapOrder(): hasMany
    {
        return $this->hasMany('App\Models\EAPOrderPriority', 'eap_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function eapDepartment(): HasMany
    {
        return $this->hasMany('App\Models\EapDepartment', 'eap_id');
    }

    /**
     * @return string
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversion
     */
    public function getLogoAttribute()
    {
        return $this->getLogo(['h' => 100, 'w' => 100]);
    }

    /**
     * @param string $size
     *
     * @return string
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversion
     */
    public function getLogo(array $params): string
    {
        $media = $this->getFirstMedia('logo');
        if (!is_null($media) && $media->count() > 0) {
            $params['src'] = $this->getFirstMediaUrl('logo');
        }
        return getThumbURL($params, 'eap', 'logo');
    }

    /**
     * @param string $collection
     * @param string $size
     *
     * @return array
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversion
     */
    public function getMediaData(string $collection, array $param): array
    {
        $return = [
            'width'  => $param['w'],
            'height' => $param['h'],
        ];
        $media = $this->getFirstMedia($collection);

        if (!is_null($media) && $media->count() > 1) {
            $param['src'] = $this->getFirstMediaUrl($collection);
        }
        $return['url'] = getThumbURL($param, 'recipe', $collection);
        return $return;
    }

    /**
     * Set datatable for role list.
     *
     * @param payload
     * @return dataTable
     */

    public function getTableData($payload)
    {
        $user      = auth()->user();
        $role      = getUserRole();
        $list      = $this->getRecordList($payload);
        $companyId = "";
        if ($role->group != 'zevo') {
            $companyId = $user->company->first()->id;
        }
        return DataTables::of($list)
            ->addColumn('email', function ($ea) {
                return !empty($ea->email) ? $ea->email : 'N/A';
            })
            ->addColumn('website', function ($ea) {
                return !empty($ea->website) ? $ea->website : 'N/A';
            })
            ->addColumn('title', function ($ea) {
                return $ea->title;
            })
            ->addColumn('companiesName', function ($record) use ($role, $user) {
                $companyData = $user->company()->get()->first();
                if (($role->group == 'zevo') || ($role->group == 'reseller' && !empty($companyData))) {
                    if ($role->group == 'zevo') {
                        $companies = $record->eapcompany()->select('companies.name', DB::raw("( CASE WHEN companies.is_reseller = true AND companies.parent_id IS NULL THEN 'Parent' WHEN companies.is_reseller = false AND companies.parent_id IS NOT NULL THEN 'Child' ELSE 'Zevo' END ) AS group_type"))->get()->toArray();
                    } elseif ($role->group == 'reseller') {
                        if ($record->company_id == $companyData->id) {
                            $companies = $record->eapcompany()->select('companies.name', DB::raw("( CASE WHEN companies.is_reseller = true AND companies.parent_id IS NULL THEN 'Parent' ELSE 'Child' END ) AS group_type"))->get()->toArray();
                        } else {
                            $companies = $record->eapcompany()->select('companies.name', DB::raw("( CASE WHEN companies.is_reseller = true AND companies.parent_id IS NULL THEN 'Parent' ELSE 'Child' END ) AS group_type"))->where('companies.id', $companyData->id)->get()->toArray();
                        }
                    }
                    $totalCompanies   = sizeof($companies);

                    if ($totalCompanies > 0) {
                        return "<a href='javascript:void(0);' title='View Companies' class='preview_companies' data-rowdata='" . base64_encode(json_encode($companies)) . "' data-cid='" . $record->id . "'> " . $totalCompanies . "</a>";
                    }
                }
                return "";
            })
            ->addColumn('view_count', function ($ea) {
                return !empty($ea->view_count) ? $ea->view_count : 0;
            })
            ->addColumn('logo', function ($ea) {
                return '<div class="table-img table-img-l"><img src="' . $ea->logo . '" alt=""></div>';
            })
            ->addColumn('telephone', function ($ea) {
                return "+{$ea->telephone}";
            })
            ->addColumn('sticky', function ($ea) use ($role) {
                return view('admin.eap.sticky', compact('ea', 'role'))->render();
            })
            ->addColumn('actions', function ($ea) use ($companyId) {
                return view('admin.eap.listaction', compact('ea', 'companyId'))->render();
            })
            ->rawColumns(['logo', 'email', 'companiesName', 'website', 'description', 'actions', 'sticky'])
            ->make(true);
    }

    /**
     * get record list for data table list.
     *
     * @param payload
     * @return roleList
     */

    public function getRecordList($payload)
    {
        $company_id = null;
        $role       = getUserRole();
        $user       = auth()->user();
        $company    = $user->company()->first();
        if ($role->group != 'zevo') {
            $company_id = $company->id;
        }
        $query = $this
            ->select(
                'eap_list.*'
            );

        if ($role->group == 'zevo') {
            $query->addSelect(DB::raw('SUM(eap_logs.view_count) AS view_count'));
        } elseif ($role->group == 'company') {
            $query->selectRaw('(SELECT SUM(eap_logs.view_count) AS view_count FROM `eap_logs` LEFT JOIN user_team ON user_team.user_id = eap_logs.user_id WHERE eap_logs.eap_id = eap_list.id AND user_team.company_id = ?) AS view_count',[
                $company_id
            ]);
        } elseif ($role->group == 'reseller') {
            if ($company->parent_id != null) {
                $query->selectRaw('(SELECT SUM(eap_logs.view_count) AS view_count FROM `eap_logs` LEFT JOIN user_team ON user_team.user_id = eap_logs.user_id WHERE eap_logs.eap_id = eap_list.id AND user_team.company_id = ? AS view_count',[
                    $company_id
                ]);
            } else {
                $allcompanies = Company::where('parent_id', $company_id)->orWhere('id', $company_id)->get()->pluck('id')->toArray();

                $query->selectRaw('(SELECT SUM(eap_logs.view_count) AS view_count FROM `eap_logs` LEFT JOIN user_team ON user_team.user_id = eap_logs.user_id WHERE eap_logs.eap_id = eap_list.id AND user_team.company_id IN (?)) AS view_count',[
                    implode(',', $allcompanies)
                ]);
            }
        }

        if ($role->group == 'zevo') {
            $query->addSelect(DB::raw("CASE
                            WHEN eap_list.company_id IS NULL then 0
                            ELSE 1
                           END AS is_order"));
        } elseif ($role->group == 'reseller' && $company->parent_id == null) {
            $query->selectRaw("CASE
            WHEN eap_list.company_id = ? then 0
            WHEN eap_list.company_id IS NULL then 1
            ELSE 2
            END AS is_order",[
                $company->id
            ]);
        }

        if ($role->group != 'zevo' || ($role->group == 'reseller' && $company->parent_id != null)) {
            $query->addSelect(DB::raw('IFNULL(eap_order_priority.order_priority, 0) AS order_priority'));
        }
        $query->leftJoin('eap_logs', function ($join) {
            $join->on('eap_logs.eap_id', '=', 'eap_list.id');
        });

        if ($role->group != 'zevo' || ($role->group == 'reseller' && $company->parent_id != null)) {
            $query->leftJoin('eap_order_priority', function ($join) use ($company_id) {
                $join->on('eap_order_priority.eap_id', '=', 'eap_list.id')->where('eap_order_priority.company_id', '=', $company_id);
            });
        }

        if ($role->group == 'zevo') {
            $query->where('eap_list.company_id', $company_id);
        }

        $query->groupBy('eap_list.id');

        if ($role->group == 'company') {
            $query->orwhereRaw('(FIND_IN_SET(?, (SELECT GROUP_CONCAT(`eap_company`.`company_id`) from `eap_company` where `eap_id` = `eap_list`.`id`)))', [$company_id]);
        } elseif ($role->group == 'reseller') {
            $query->whereRaw('(FIND_IN_SET(?, (SELECT GROUP_CONCAT(`eap_company`.`company_id`) from `eap_company` where `eap_id` = `eap_list`.`id`)))', [$company_id]);
            if ($company->parent_id == null) {
                $query->orWhere('eap_list.company_id', $company_id);
            }
        }

        $query->orderBy('is_stick', 'DESC');
        if ($role->group == 'zevo') {
            $query->orderByDesc('eap_list.updated_at');
        } elseif ($role->group == 'reseller' && $company->parent_id == null) {
            $query->orderBy("is_order", 'ASC');
            $query->orderByDesc('eap_list.updated_at');
        } else {
            $query->orderByRaw('eap_order_priority.order_priority = 0 DESC, eap_order_priority.order_priority ASC');
        }

        return $query->get();
    }

    /**
     * store record data.
     *
     * @param payload
     * @return boolean
     */

    public function storeEntity(array $payload)
    {
        $user      = auth()->user();
        $role      = getUserRole();
        $company   = $user->company()->first();
        $companyId = null;
        if ($role->group != 'zevo') {
            $companyId = $company->id;
        }

        $teamInput = [
            'creator_id'  => $user->id,
            'company_id'  => $companyId,
            'title'       => $payload['title'],
            'telephone'   => $payload['telephone'],
            'email'       => $payload['email'],
            'website'     => $payload['website'],
            'description' => $payload['description'],
            'is_rca'      => 0,
        ];

        $teamInput['locations']   = (!empty($payload['locations']) ? implode(',', $payload['locations']) : null);
        $teamInput['departments'] = (!empty($payload['department']) ? implode(',', $payload['department']) : null);

        if ($role->group == 'company' || ($role->group == 'reseller' && $company->parent_id != null)) {
            if (!empty($payload['locations']) || !empty($payload['departments'])) {
                $teamInput['is_rca'] = 1;
            }
        }

        $eap = self::create($teamInput);

        if (isset($payload['logo']) && !empty($payload['logo'])) {
            $name = $eap->id . '_' . \time();
            $eap->clearMediaCollection('logo')
                ->addMediaFromRequest('logo')
                ->usingName($payload['logo']->getClientOriginalName())
                ->usingFileName($name . '.' . $payload['logo']->extension())
                ->toMediaCollection('logo', config('medialibrary.disk_name'));
        }

        if (!empty($companyId)) {
            $orderPriority = EAPOrderPriority::where('company_id', $companyId)->max('order_priority');
            $orderPriority = (!empty($orderPriority) ? ($orderPriority + 1) : 1);
            EAPOrderPriority::create([
                'eap_id'         => $eap->id,
                'company_id'     => $companyId,
                'order_priority' => $orderPriority,
            ]);
        }

        if ($role->group == 'company' || ($role->group == 'reseller' && $company->parent_id != null)) {
            $eapCompanyInput[] = [
                'eap_id'     => $eap->id,
                'company_id' => $companyId,
                'created_at' => Carbon::now(),
            ];
            $eap->eapcompany()->sync($eapCompanyInput);
        }

        if ($role->group == 'zevo' || ($role->group == 'reseller' && $company->parent_id == null)) {
            $newCompanies          = $newDepartments          = $newD          = [];
            foreach ($payload['eap_company'] as $eapCompany) {
                $explodeEapCompny = explode("@@@", $eapCompany);
                $departmentIds    = $explodeEapCompny[0];
                $locationIds      = $explodeEapCompny[1];
                $companyIds       = DepartmentLocation::where('department_id', $departmentIds)->where('company_location_id', $locationIds)->select('company_id')->get()->first();
                $department       = $eap->eapDepartment()->select('eap_department.id', 'eap_department.department_id')->where('location_id', $locationIds)->where('department_id', $departmentIds)->get()->first();
                array_push($newCompanies, $companyIds->company_id);
                if (!empty($department)) {
                    array_push($newDepartments, $department->id);
                }
                array_push($newD, (int) $departmentIds);
                $eap->eapList()->updateOrCreate(['eap_id' => $eap->id, 'company_id' => $companyIds->company_id], [
                    'company_id' => $companyIds->company_id,
                ]);

                $eap->eapDepartment()->updateOrCreate(['eap_id' => $eap->id, 'location_id' => $locationIds, 'department_id' => $departmentIds], [
                    'location_id'   => $locationIds,
                    'department_id' => $departmentIds,
                ]);
            }
        }

        if ($eap) {
            $notificationUser = User::select('users.*')
                ->join("user_team", "user_team.user_id", "=", "users.id");

            if (!empty($teamInput['departments']) || !empty($teamInput['locations'])) {
                $notificationUser = $notificationUser->leftJoin("team_location", function ($join) {
                    $join->on("team_location.team_id", "=", "user_team.team_id");
                })
                    ->leftJoin("company_locations", function ($join) {
                        $join->on("company_locations.id", "=", "team_location.company_location_id");
                    })
                    ->leftJoin("departments", function ($join) {
                        $join
                            ->on('user_team.user_id', '=', 'users.id')
                            ->on('user_team.department_id', '=', 'departments.id');
                    });
                if (!empty($payload['department'])) {
                    $notificationUser->whereIn("user_team.department_id", $payload['department']);
                }
                if (!empty($payload['locations'])) {
                    $notificationUser->whereIn("company_locations.id", $payload['locations']);
                }
            }
            $notificationUser = $notificationUser->whereRaw('user_team.company_id IN ( SELECT company_id FROM `eap_company` WHERE eap_id = ?)', [$eap->id]);
            if ($role->group == 'zevo' || ($role->group == 'reseller' && $company->parent_id == null)) {
                $notificationUser = $notificationUser->whereRaw('user_team.department_id IN ( SELECT department_id FROM `eap_department` WHERE eap_id = ?)', [$eap->id]);
            }
            $notificationUser = $notificationUser->where("users.is_blocked", false)
                ->groupBy('users.id')
                ->get();

            \dispatch(new SendEapPushNotifications($eap, "eap-created", $notificationUser, ''));
            return true;
        } else {
            return false;
        }
    }

    /**
     * get eap edit data.
     *
     * @param  $id
     * @return array
     */

    public function eapEditData($id)
    {
        $data       = array();
        $data['id'] = $id;

        $company = $data['teamData']->company;

        $department = array();
        if (!empty($company->departments)) {
            foreach ($company->departments as $value) {
                $department[$value->id] = $value->name;
            }
        }
        $data['department'] = $department;

        $location = array();

        if (!empty($data['teamData']->department->departmentlocations)) {
            foreach ($data['teamData']->department->departmentlocations as $value) {
                $location[$value->id] = $value->name;
            }
        }
        $data['location']              = $location;
        $data['company'][$company->id] = $company->name;

        return $data;
    }

    /**
     * update record data.
     *
     * @param payload
     * @return boolean
     */
    public function updateEntity(array $payload)
    {
        $role      = getUserRole();
        $company   = auth()->user()->company()->first();
        $data = [
            'title'       => $payload['title'],
            'telephone'   => $payload['telephone'],
            'email'       => $payload['email'],
            'website'     => $payload['website'],
            'description' => $payload['description'],
            'locations'   => null,
            'departments' => null,
            'is_rca'      => 0,
        ];
        if (!empty($payload['locations'])) {
            $data['locations'] = implode(',', $payload['locations']);
        }
        if (!empty($payload['department'])) {
            $data['departments'] = implode(',', $payload['department']);
        }

        if ($role->group == 'company' || ($role->group == 'reseller' && $company->parent_id != null)) {
            if (!empty($payload['locations']) || !empty($payload['departments'])) {
                $data['is_rca'] = 1;
            }
        }

        $this->update($data);

        if (isset($payload['logo']) && !empty($payload['logo'])) {
            $name = $this->id . '_' . \time();
            $this->clearMediaCollection('logo')
                ->addMediaFromRequest('logo')
                ->usingName($payload['logo']->getClientOriginalName())
                ->usingFileName($name . '.' . $payload['logo']->extension())
                ->toMediaCollection('logo', config('medialibrary.disk_name'));
        }

        if (!empty($payload['eap_company']) && ($role->group == 'zevo' || ($role->group == 'reseller' && $company->parent_id == null))) {
            $existingComapnies     = $this->eapcompany()->pluck('companies.id')->toArray();
            $existingDepartments   = $this->eapDepartment()->select('eap_department.department_id', 'eap_department.id')->pluck("eap_department.id")->toArray();
            $existingDepartmentIds = $this->eapDepartment()->select('eap_department.department_id')->pluck("eap_department.department_id")->toArray();
            $newCompanies          = $newDepartments          = $newD          = [];

            foreach ($payload['eap_company'] as $eapCompany) {
                $explodeEapCompny = explode("@@@", $eapCompany);
                $departmentIds    = $explodeEapCompny[0];
                $locationIds      = $explodeEapCompny[1];
                $companyIds       = DepartmentLocation::where('department_id', $departmentIds)->where('company_location_id', $locationIds)->select('company_id')->get()->first();
                $department       = $this->eapDepartment()->select('eap_department.id', 'eap_department.department_id')->where('location_id', $locationIds)->where('department_id', $departmentIds)->get()->first();
                array_push($newCompanies, $companyIds->company_id);
                if (!empty($department)) {
                    array_push($newDepartments, $department->id);
                }
                array_push($newD, (int) $departmentIds);
                $this->eapList()->updateOrCreate(['eap_id' => $this->id, 'company_id' => $companyIds->company_id], [
                    'company_id' => $companyIds->company_id,
                ]);

                $this->eapDepartment()->updateOrCreate(['eap_id' => $this->id, 'location_id' => $locationIds, 'department_id' => $departmentIds], [
                    'location_id'   => $locationIds,
                    'department_id' => $departmentIds,
                ]);
            }
            $removedAssociatedComps = array_diff($existingComapnies, $newCompanies);
            $removedAssociateddepts = array_diff($existingDepartments, $newDepartments);
            $currentAddedDepts      = array_diff($newD, $existingDepartmentIds);

            if (!empty($currentAddedDepts)) {
                $addedUserIds = User::select('users.*')
                    ->leftjoin("user_team", "user_team.user_id", "=", "users.id")
                    ->whereRaw(DB::raw('user_team.company_id IN ( SELECT company_id FROM `eap_company` WHERE eap_id = ? )'),[
                        $this->id
                    ])
                    ->whereIn("user_team.department_id", $currentAddedDepts)
                    ->where("users.is_blocked", false)
                    ->groupBy('users.id')
                    ->get();

                \dispatch(new SendEapPushNotifications($this, "eap-created", $addedUserIds, ''));
            }
            if (!empty($removedAssociateddepts)) {
                $removedDepartments = $this->eapDepartment()->select('eap_department.department_id')->whereIn('eap_department.id', $removedAssociateddepts)->pluck("eap_department.department_id")->toArray();
                $removedUserIds     = User::select('users.*')
                    ->leftjoin("user_team", "user_team.user_id", "=", "users.id")
                    ->whereRaw(DB::raw('user_team.company_id IN ( SELECT company_id FROM `eap_company` WHERE eap_id = ? )'),[
                        $this->id
                    ])
                    ->whereIn("user_team.department_id", $removedDepartments)
                    ->groupBy('users.id')
                    ->get()
                    ->pluck('id')
                    ->toArray();

                Notification::Join('notification_user', 'notification_user.notification_id', '=', 'notifications.id')
                    ->whereIn('notification_user.user_id', $removedUserIds)
                    ->where(function ($query) {
                        $query
                            ->where('notifications.tag', 'eap')
                            ->where('notifications.deep_link_uri', $this->deep_link_uri);
                    })
                    ->delete();
            }
            $this->eapDepartment()->where('eap_id', $this->id)->whereIn('id', $removedAssociateddepts)->delete();
            $this->eapList()->where('eap_id', $this->id)->whereIn('company_id', $removedAssociatedComps)->delete();
        }
        return true;
    }

    /**
     * delete record by record id.
     *
     * @param $id
     * @return array
     */

    public function deleteRecord()
    {
        $orderDetails = $this->eapOrder->first();
        if ($this->delete()) {
            if (!empty($orderDetails)) {
                EAPOrderPriority::where('company_id', $this->company_id)
                    ->where('order_priority', '>', $orderDetails->order_priority)
                    ->decrement('order_priority', 1);
            }
            return array('deleted' => true, 'message' => trans('eap.message.deleted_success'));
        }
        return array('deleted' => false, 'message' => trans('eap.message.deleted_error'));
    }

    /**
     * To reordering EAP records
     *
     * @param  $positions: array
     *
     * @return bool
     */
    public function reorderingEap($positions)
    {
        $user    = auth()->user();
        $company = $user->company()->first();
        $updated = false;
        foreach ($positions as $key => $position) {
            $updated = EAPOrderPriority::updateOrCreate([
                'eap_id'     => (int) $key,
                'company_id' => $company->id,
            ], [
                'order_priority' => (int) $position['newPosition'],
            ]);
        }
        return $updated;
    }

    /**
     * @param $payload
     *
     * @return boolean
     */
    public function getDepartment($payload, $role, $companyId)
    {
        $locationArray = isset($payload['value']) ? $payload['value'] : [];
        $departments   = [];
        if (!empty($locationArray)) {
            $companyDepartment = DepartmentLocation::whereIn('company_location_id', $locationArray);
            if (($role->group == 'company' || $role->group == 'reseller') && sizeof($companyId) > 0) {
                $companyDepartment = $companyDepartment->whereIn('company_id', $companyId);
            }
            $companyDepartment = $companyDepartment->select('department_id')
                ->get()
                ->pluck('department_id')
                ->toArray();

            $departments = Department::whereIn('id', $companyDepartment)->select('id', 'name')->get()->pluck('name', 'id')->toArray();
        }
        return $departments;
    }

    /**
     * @param $action (stick/unstick)
     *
     * @return boolean
     */
    public function stickUnstick($action)
    {
        return $this->update(['is_stick' => (($action == 'stick') ? 1 : 0)]);
    }
}
