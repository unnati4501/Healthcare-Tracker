<?php

namespace App\Models;

use App\Models\Company;
use App\Models\CompanyRoles;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Yajra\DataTables\Facades\DataTables;

class Role extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'roles';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'slug',
        'group',
        'description',
    ];

    /**
     * @return BelongsToMany
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    /**
     * @return BelongsToMany
     */
    public function associatedCompanies(): BelongsToMany
    {
        return $this->belongsToMany(Company::class, CompanyRoles::class);
    }

    /**
     * Set datatable for role list.
     *
     * @param payload
     * @return dataTable
     */

    public function getTableData($payload)
    {
        $list = $this->getRoleList($payload);
        return DataTables::of($list)
            ->addColumn('actions', function ($role) {
                return view('admin.roles.listaction', compact('role'))->render();
            })
            ->rawColumns(['actions'])
            ->make(true);
    }

    /**
     * get role list for data table list.
     *
     * @param payload
     * @return roleList
     */
    public function getRoleList($payload)
    {
        $query = $this
            ->withCount(['users', 'associatedCompanies'])
            ->where('default', 0)
            ->orderByDesc('updated_at');

        if (in_array('roleName', array_keys($payload)) && !empty($payload['roleName'])) {
            $query->where('name', 'like', '%' . $payload['roleName'] . '%');
        }

        if (in_array('roleGroup', array_keys($payload)) && !empty($payload['roleGroup'])) {
            $query->where('group', 'like', '%' . $payload['roleGroup'] . '%');
        }

        return $query->get();
    }

    /**
     * store role data.
     *
     * @param payload
     * @return boolean
     */
    public function storeEntity($payload)
    {
        $roleObj = new Role();

        $roleObj->name        = $payload['name'];
        $roleObj->slug        = str_replace(' ', '_', strtolower($payload['name']));
        $roleObj->group       = $payload['group'];
        $roleObj->description = $payload['description'];
        $data = $roleObj->save();

        $roleObj->permissions()->sync($payload['members_selected']);

        if ($data) {
            return true;
        }

        return false;
    }

    /**
     * update role data.
     *
     * @param payload , $id
     * @return boolean
     */

    public function updateEntity($payload, $id)
    {
        $roleData = Role::where("id", $id)->first();

        if (!empty($roleData)) {
            $roleData->name        = $payload['name'];
            $roleData->slug        = str_replace(' ', '_', strtolower($payload['name']));
            $roleData->description = $payload['description'];

            $data = $roleData->save();

            $payload['members_selected'] = isset($payload['members_selected']) ? $payload['members_selected'] : [];
            $roleData->permissions()->sync($payload['members_selected']);

            if ($data) {
                return true;
            }
        }

        return false;
    }

    /**
     * fatch role data by role id.
     *
     * @param $id
     * @return role data
     */

    public function getRoleDataById($id)
    {
        return Role::where("id", $id)->first();
    }

    /**
     * delete role by role id.
     *
     * @param $id
     * @return array
     */

    public function deleteUserRole()
    {
        $roleData = Role::where("id", $this->id)->first();

        if ($roleData->users->count() > 0) {
            return array('deleted' => 'error');
        }

        if ($roleData->delete()) {
            return array('deleted' => 'true');
        }
        return array('deleted' => 'error');
    }

    public function duplicationCheck($payload, $id = '')
    {
        $roleData = Role::where("name", $payload['name'])
            ->where("group", $payload['group']);
        if (!empty($id)) {
            $roleData = $roleData->where("id", "!=", $id);
        }

        return $roleData->first();
    }

    /**
     * @return mixed
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\Permission', 'permission_role', 'role_id', 'permission_id')->withTimestamps();
    }

    /**
     * Get permission treeview list for role add edit.
     *
     * @param none
     * @return array
     */
    public function getPermissionData($group = 'zevo'): array
    {
        $parentPermissions = Permission::get()
            ->where('parent_id', null);

        $permissionsLists = [];

        foreach ($parentPermissions as $value) {
            $childPermissions = Permission::where('permissions.parent_id', $value->id)
                ->leftJoin('permission_role', 'permission_role.permission_id', '=', 'permissions.id')
                ->leftJoin('roles', 'permission_role.role_id', '=', 'roles.id')
                ->where('roles.group', $group)
                ->where('roles.default', 1)
                ->where('permissions.status', 1)
                ->select('permissions.id', 'permissions.display_name')
                ->distinct('permissions.id')
                ->get()
                ->toArray();

            if (!empty($childPermissions)) {
                $permissionsLists[] = [
                    'id'           => $value->id,
                    'display_name' => $value->display_name,
                    'children'     => $childPermissions,
                ];
            }
        }

        return $permissionsLists;
    }
}
