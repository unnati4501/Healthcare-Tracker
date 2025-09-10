<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminAlert;
use App\Models\AdminAlertUsers;
use Breadcrumbs;
use Illuminate\Http\Request;
use App\Http\Requests\Admin\UpdateAdminAlertRequest;

/**
 * Class AdminAlertController
 *
 * @package App\Http\Controllers\Admin
 */
class AdminAlertController extends Controller
{
    /**
     * variable to store the model object
     * @var AdminAlert
     */
    protected $model;

    /**
     * contructor to initialize model object
     */
    public function __construct(AdminAlert $model)
    {
        $this->model = $model;
        $this->bindBreadcrumbs();
    }

    /**
     * bind breadcrumbs of course module
     */
    private function bindBreadcrumbs()
    {
        Breadcrumbs::for ('admin-alerts.index', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push('Admin Alerts');
        });
        Breadcrumbs::for ('admin-alerts.edit', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push('Admin Alerts', route('admin.admin-alerts.index'));
            $trail->push('Edit Admin Alert');
        });
    }

    /**
     * @param Request $request
     * @return View
     */
    public function index(Request $request)
    {
        if (!access()->allow('manage-admin-alert')) {
            abort(403);
        }

        try {
            $user       = auth()->user();
            $role       = getUserRole($user);
            $data = [
                'role'       => $role->slug,
                'pagination' => config('zevolifesettings.datatable.pagination.short'),
                'ga_title'   => trans('page_title.admin-alerts.index'),
            ];

            return \view('admin.admin-alerts.index', $data);
        } catch (\Exception $exception) {
            abort(500);
        }
    }

    /**
     * @param Request $request
     *
     * @return View
     */

     public function getAdminAlerts(Request $request)
     {
        if (!access()->allow('manage-admin-alert')) {
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
             return response($messageData, 500)->header('Content-Type', 'application/json');
         }
     }

    /**
     * Display the edit form with users in html
     * @param AdminAlert $adminAlert
     * @param Request $request
     * @return View
     */
    public function editAdminAlert(AdminAlert $adminAlert, Request $request)
    {
        if (!access()->allow('edit-admin-alert')) {
            abort(403);
        }

        try {
            $user                   = auth()->user();
            $role                   = getUserRole($user);
            $adminAlertUsers        = $adminAlert->users()->get();
            $getLastInsertedUser    = AdminAlertUsers::select('id')->orderBy('id', 'DESC')->first();
            $data = [
                'role'                  => $role->slug,
                'record'                => $adminAlert,
                'adminAlertUsers'       => $adminAlertUsers,
                'getLastInsertedUser'   => $getLastInsertedUser ?? 0,
                'pagination'            => config('zevolifesettings.datatable.pagination.short'),
                'ga_title'              => trans('page_title.admin-alerts.edit_admin_alert'),
            ];
            return \view('admin.admin-alerts.edit', $data);
        } catch (\Exception $exception) {
            abort(500);
        }
    }

    /**
     * Update the admin alerts with users
     * @param AdminAlert $adminAlert
     * @param UpdateAdminAlertRequest $request
     * @return View
     */
    public function updateAdminAlert(AdminAlert $adminAlert, UpdateAdminAlertRequest $request)
    {
        if (!access()->allow('edit-admin-alert')) {
            abort(403);
        }
        try {
            \DB::beginTransaction();
            $data = $adminAlert->updateEntity($request->all());
            if ($data) {
                \DB::commit();
                $messageData = [
                    'data'   => trans('adminalert.message.data_update_success'),
                    'status' => 1,
                ];
                return \Redirect::route('admin.admin-alerts.index')->with('message', $messageData);

            } else {
                \DB::rollback();
                $messageData = [
                    'data'   => trans('adminalert.message.something_wrong_try_again'),
                    'status' => 0,
                ];
                return \Redirect::route('admin.admin-alerts.index')->with('message', $messageData);
            }
        } catch (\Exception $exception) {
            \DB::rollback();
            report($exception);
            return response()->json([
                'status'  => 0,
                'message' => trans('adminalert.message.something_wrong_try_again'),
            ], 500);
        }
    }
}
