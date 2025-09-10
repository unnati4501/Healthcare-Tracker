<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;

class Domain extends Model
{

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'domains';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['company_id', 'domain'];

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
     * @return BelongsTo
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo('App\Models\Company');
    }

    /**
     * duplication check for department data.
     *
     * @param $payload , $id
     * @return array
     */

    public function duplicationCheck($payload, $id = '')
    {
        $domainData = Domain::where("domain", $payload['domain'])
            ->where("company_id", $payload['company_id']);
        if (!empty($id)) {
            $domainData = $domainData->where("id", "!=", $id);
        }

        return $domainData->first();
    }

    /**
     * Set datatable for role list.
     *
     * @param payload
     * @return dataTable
     */

    public function getTableData($payload)
    {
        $list = $this->getDomainList($payload);

        return DataTables::of($list)
            ->addColumn('actions', function ($domain) {
                $userAssigned =  User::join("user_team", "user_team.user_id", "=", "users.id")
                                    ->where("user_team.company_id", $domain->company_id)
                                    ->where("users.email", "LIKE", "%@".$domain->domain."%")
                                    ->get();

                return view('admin.domain.listaction', compact('domain', 'userAssigned'))->render();
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

    public function getDomainList($payload)
    {
        $userCompany = \Auth::user()->company()->first();

        $whereConditions = [];
        if ($userCompany !== null) {
            $whereConditions = [
                'domains.company_id' => $userCompany->id,
            ];
        }

        $query = Domain::where($whereConditions)
            ->orderBy('updated_at', 'DESC');

        if (in_array('domainName', array_keys($payload)) && !empty($payload['domainName'])) {
            $query->where('domain', 'like', '%' . $payload['domainName'] . '%');
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
        $domainInput = [
            'domain'       => strtolower($payload['domain']),
            'company_id'   => $payload['company_id'],
        ];

        return Domain::create($domainInput);
    }

    /**
     * update record data.
     *
     * @param payload , $id
     * @return boolean
     */

    public function updateEntity(array $payload)
    {

        $data = [
            'domain'       => strtolower($payload['domain']),
            'company_id'   => $payload['company_id'],
        ];

        return $this->update($data);
    }


    /**
     * delete record by record id.
     *
     * @param $id
     * @return array
     */

    public function deleteDepartment()
    {
        $userAssigned = User::join("user_team", "user_team.user_id", "=", "users.id")
                        ->where("user_team.company_id", $this->company_id)
                        ->where("users.email", "LIKE", "%@".$this->domain."%")
                        ->get();

        if ($userAssigned->count() > 0) {
            return array('deleted' => 'error');
        }

        if ($this->delete()) {
            return array('deleted' => 'true');
        }
        return array('deleted' => 'error');
    }
}
