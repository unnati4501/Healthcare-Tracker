<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class CpPlan extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'cp_plan';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'group',
        'default',
        'status',
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
    protected $dates = ['created_at', 'updated_at'];

    /**
     * One-to-Many relations with Company Plan.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function plancompany(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\Company', 'cp_company_plans', 'plan_id', 'company_id');
    }

    public function planFeatures(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\CpFeatures', 'cp_plan_features', 'plan_id', 'feature_id');
    }

    /**
     * Set datatable for groups list.
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
            ->addColumn('updated_at', function ($record) {
                return $record->updated_at;
            })
            ->addColumn('company_plan', function ($record) {
                return $record->name;
            })
            ->addColumn('mapped_companies', function ($record) {
                return $record->plancompany()->count();
            })
            ->addColumn('actions', function ($record) {
                $attechCount = $record->plancompany()->count();
                return view('admin.companyplan.listaction', compact('record', 'attechCount'))->render();
            })
            ->rawColumns(['actions'])
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
        $query = $this
            ->select(
                'cp_plan.id',
                'cp_plan.name',
                'cp_plan.group',
                'cp_plan.default'
            )
            ->where(['cp_plan.status' => 1]);
            if(!empty($payload['grouptype'])){
                $query = $query->where(['cp_plan.group' => $payload['grouptype']]);
            }
            $query = $query->orderByDesc('cp_plan.updated_at', 'DESC');

        if (in_array('companyplan', array_keys($payload)) && !empty($payload['companyplan'])) {
            $query->where('cp_plan.name', 'like', '%' . $payload['companyplan'] . '%');
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
            'name'        => $payload['companyplan'],
            'slug'        => str_slug($payload['companyplan']),
            'description' => $payload['description'],
            'group'       => $payload['group'],
        ];

        $companyPlan = self::create($data);

        if (!empty($payload['members_selected'])) {
            $companyPlan->planFeatures()->sync($payload['members_selected']);
        }

        if ($companyPlan) {
            return true;
        }
        return false;
    }

    /**
     * Get Company plan data
     * @return View
     */
    public function getEditCompanyPlan()
    {
        $data['cpPlan']       = $this;
        $data['id']           = $this->id;
        $data['groupType']    = config('zevolifesettings.company_plan_group_type');
        $data['type']         = $this->group;
        $data['ga_title']     = trans('page_title.companyplans.edit');
        $data['planFeatures'] = $this->planFeatures->pluck('id')->toArray();

        return $data;
    }

    /**
     * update record data.
     *
     * @param payload
     * @return boolean
     */
    public function updateEntity($payload)
    {
        $data = [
            'name'        => $payload['companyplan'],
            'slug'        => str_slug($payload['companyplan']),
            'description' => $payload['description'],
        ];

        $updated = $this->update($data);

        if (!empty($payload['members_selected'])) {
            $this->planFeatures()->sync($payload['members_selected']);
        }

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
            return array('deleted' => 'true', 'alreadyUse' => 'false');
        }
        return array('deleted' => 'error', 'alreadyUse' => 'false');
    }
}
