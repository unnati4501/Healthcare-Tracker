<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CreateRoleRequest;
use App\Http\Requests\Admin\EditRoleRequest;
use App\Models\Role;
use App\Repositories\AuditLogRepository;
use Breadcrumbs;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

/**
 * Class RolesController
 *
 * @package App\Http\Controllers\Admin
 */
class RolesController extends Controller
{
    /**
     * variable to store the model object
     * @var Role
     */
    protected $model;

    /**
     * @var AuditLogRepository $auditLogRepository
     */
    private $auditLogRepository;

    /**
     * contructor to initialize model object
     * @param Roles $model ;
     */
    public function __construct(Role $model, AuditLogRepository $auditLogRepository)
    {
        $this->model              = $model;
        $this->auditLogRepository = $auditLogRepository;
        $this->bindBreadcrumbs();
    }

    /**
     * bind breadcrumbs of role module
     */
    private function bindBreadcrumbs()
    {
        Breadcrumbs::for('role.index', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push('Roles');
        });
        Breadcrumbs::for('role.create', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push('Roles', route('admin.roles.index'));
            $trail->push('Add Role');
        });
        Breadcrumbs::for('role.edit', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push('Roles', route('admin.roles.index'));
            $trail->push('Edit Role');
        });
    }

    /**
     * @return View
     */
    public function index(Request $request)
    {
        $role = getUserRole();
        if (!access()->allow('manage-role') || $role->group != 'zevo') {
            abort(403);
        }
        try {
            $data = [
                'roleGroupData' => config('zevolifesettings.role_group'),
                'pagination'    => config('zevolifesettings.datatable.pagination.short'),
                'ga_title'      => trans('page_title.roles.roles_list'),
            ];
            return \view('admin.roles.index', $data);
        } catch (\Exception $exception) {
            report($exception);
            abort(500);
        }
    }

    /**
     * @return View
     */
    public function create(Request $request)
    {
        $role = getUserRole();
        if (!access()->allow('create-role') || $role->group != 'zevo') {
            abort(403);
        }
        try {
            $data                   = array();
            $data['roleGroupData']  = config('zevolifesettings.role_group');
            $data['permissionData'] = $this->model->getPermissionData();
            $data['ga_title']       = trans('page_title.roles.create');

            return \view('admin.roles.create', $data);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong_try_again'),
                'status' => 0,
            ];
            return \Redirect::route('admin.roles.index')->with('message', $messageData);
        }
    }

    /**
     * @param CreateRoleRequest $request
     *
     * @return RedirectResponse
     */
    public function store(CreateRoleRequest $request)
    {
        $user = auth()->user();
        $role = getUserRole();
        if (!access()->allow('create-role') || $role->group != 'zevo') {
            abort(403);
        }
        try {
            $payload = $request->all();
            $userLogData = [
                'user_id'   => $user->id,
                'user_name' => $user->full_name,
            ];
            $validator = Validator::make($payload, [
                'name' => [function ($attribute, $value, $fail) use ($payload) {
                    $duplicationCheck = $this->model->duplicationCheck($payload);
                    if (!empty($duplicationCheck)) {
                        $fail(':error');
                    }
                }],
            ]);

            if ($validator->fails()) {
                return redirect()->back()->withErrors(trans('roles.messages.role_exist'));
            }

            $data    = $this->model->storeEntity($request->all());

            $logData = array_merge($userLogData, $payload);
            $this->auditLogRepository->created("Role added successfully", $logData);

            if ($data) {
                $messageData = [
                    'data'   => trans('roles.messages.data_store_success'),
                    'status' => 1,
                ];
                return \Redirect::route('admin.roles.index')->with('message', $messageData);
            } else {
                $messageData = [
                    'data'   => trans('labels.common_title.something_wrong_try_again'),
                    'status' => 0,
                ];
                return \Redirect::route('admin.roles.create')->with('message', $messageData);
            }
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong_try_again'),
                'status' => 0,
            ];
            return \Redirect::route('admin.roles.index')->with('message', $messageData);
        }
    }

    /**
     * @return View
     */
    public function edit(Request $request, $id)
    {
        $role = getUserRole();
        if (!access()->allow('update-role') || $role->group != 'zevo' || in_array($id, [1, 2, 3])) {
            abort(403);
        }
        try {
            $data                   = array();
            $data['id']             = $id;
            $data['roleData']       = $this->model->getRoleDataById($id);
            $group                  = $data['roleData']->group;
            $data['roleGroupData']  = config('zevolifesettings.role_group');
            $data['permissionData'] = $this->model->getPermissionData($group);
            $data['permissions']    = $data['roleData']->permissions()->pluck('permission_id')->toArray();
            $data['ga_title']       = trans('page_title.roles.edit');
            return \view('admin.roles.edit', $data);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong_try_again'),
                'status' => 0,
            ];
            return \Redirect::route('admin.roles.index')->with('message', $messageData);
        }
    }

    /**
     * @param EditRoleRequest $request
     *
     * @return RedirectResponse
     */
    public function update(EditRoleRequest $request, Role $role)
    {
        $user               = auth()->user();
        $loggedInuserRole   = getUserRole();
        $roleId             = $role->id;
        if (!access()->allow('update-role') || $loggedInuserRole->group != 'zevo' || in_array($roleId, [1, 2, 3])) {
            abort(403);
        }
        try {
            $userLogData = [
                'user_id'   => $user->id,
                'user_name' => $user->full_name,
            ];
            $oldRolesData = array_merge($userLogData, $role->toArray());
            $payload = $request->all();

            $validator = Validator::make($payload, [
                'name' => [function ($attribute, $value, $fail) use ($payload, $roleId) {
                    $duplicationCheck = $this->model->duplicationCheck($payload, $roleId);
                    if (!empty($duplicationCheck)) {
                        $fail(':error');
                    }
                }],
            ]);

            if ($validator->fails()) {
                return redirect()->back()->withErrors(trans('roles.messages.role_exist'));
            }

            $data = $this->model->updateEntity($request->all(), $roleId);

            $newRolesData = array_merge($userLogData, $request->all());
            $finalLogs = ['olddata' => $oldRolesData, 'newdata' => $newRolesData];
            $this->auditLogRepository->created("Role updated successfully", $finalLogs);

            if ($data) {
                $messageData = [
                    'data'   => trans('roles.messages.data_update_success'),
                    'status' => 1,
                ];
                return \Redirect::route('admin.roles.index')->with('message', $messageData);
            } else {
                $messageData = [
                    'data'   => trans('labels.common_title.something_wrong_try_again'),
                    'status' => 0,
                ];
                return \Redirect::route('admin.roles.edit', $id)->with('message', $messageData);
            }
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong_try_again'),
                'status' => 0,
            ];
            return \Redirect::route('admin.roles.index')->with('message', $messageData);
        }
    }

    /**
     * @param Request $request
     *
     * @return View
     */

    public function getRoles(Request $request)
    {
        $role = getUserRole();
        if (!access()->allow('manage-role') || $role->group != 'zevo') {
            abort(403);
        }
        try {
            return $this->model->getTableData($request->all());
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong_try_again'),
                'status' => 0,
            ];
            return \Redirect::route('admin.roles.index')->with('message', $messageData);
        }
    }

    /**
     * @param  $id
     *
     * @return View
     */

    public function delete(Role $role)
    {
        $user             = auth()->user();
        $roleId           = $role->id;
        $loggedInUserRole = getUserRole();
        if (!access()->allow('delete-role') || $loggedInUserRole->group != 'zevo' || in_array($roleId, [1, 2, 3])) {
            abort(403);
        }
        try {
            $userLogData = [
                'user_id'   => $user->id,
                'user_name' => $user->full_name,
            ];
            $logs  = array_merge($userLogData, ['role' => $role->name]);
            $this->auditLogRepository->created("Role deleted Successfully", $logs);
            return $role->deleteUserRole();
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong_try_again'),
                'status' => 0,
            ];
            return \Redirect::route('admin.roles.index')->with('message', $messageData);
        }
    }
}
