<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AppSettingsRequest;
use App\Models\AppSetting;
use App\Models\AppTheme;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Breadcrumbs;

/**
 * Class AppsettingsController
 *
 * @package App\Http\Controllers\Admin
 */
class AppsettingsController extends Controller
{
    /**
     * variable to store the model object
     * @var AppSetting
     */
    protected $model;

    /**
     * contructor to initialize model object
     * @param AppSetting $model ;
     */
    public function __construct(AppSetting $model)
    {
        $this->model = $model;
        $this->bindBreadcrumbs();
    }

    /*
     * Bind breadcrumbs of role module
     */
    public function bindBreadcrumbs()
    {
        Breadcrumbs::for('appsettings.index', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push('AppSettings');
        });
        Breadcrumbs::for('appsettings.changeappsetting', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push('AppSettings', route('admin.appsettings.index'));
            $trail->push('Change AppSettings');
        });
    }

    /**
     * @return View
     */
    public function index(Request $request)
    {
        $role = getUserRole();
        if (!access()->allow('manage-app-settings') || $role->group != 'zevo') {
            abort(403);
        }
        try {
            $data               = array();
            $data['pagination'] = config('zevolifesettings.datatable.pagination.short');
            $data['ga_title']   = trans('page_title.appsettings.appsettings_list');
            return \view('admin.appsettings.index', $data);
        } catch (\Exception $exception) {
            report($exception);
            abort(500);
        }
    }

    /**
     * @param Request $request
     *
     * @return View
     */

    public function getAppSettings(Request $request)
    {
        $role = getUserRole();
        if (!access()->allow('manage-app-settings') || $role->group != 'zevo') {
            return response()->json([
                'message' => trans('appsettings.message.unauthorized_access'),
            ], 422);
        }
        try {
            return $this->model->getTableData($request->all());
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('appsettings.message.something_wrong_try_again'),
                'status' => 0,
            ];
            return \Redirect::route('admin.appsettings.index')->with('message', $messageData);
        }
    }

    public function changeSettings(Request $request)
    {
        $role = getUserRole();
        if (!access()->allow('update-app-settings') || $role->group != 'zevo') {
            abort(403);
        }
        try {
            $data = [
                'app_settings'    => config('zevolifesettings.app_settings'),
                'app_theme'       => AppTheme::all()->pluck('name', 'slug')->toArray(),
                'AppSettingsData' => $this->model->getAllSettings(),
                'filesData'       => $this->model->getAllMediaSettings(),
                'ga_title'        => trans('page_title.appsettings.changeSettings'),
            ];

            return \view('admin.appsettings.changesettings', $data);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('appsettings.message.something_wrong_try_again'),
                'status' => 0,
            ];
            return \Redirect::route('admin.appsettings.index')->with('message', $messageData);
        }
    }

    /**
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function store(AppSettingsRequest $request)
    {
        $role = getUserRole();
        if (!access()->allow('update-app-settings') || $role->group != 'zevo') {
            abort(403);
        }
        try {
            DB::beginTransaction();
            $payLoad = $request->all();
            unset($payLoad['_token']);
            $data = $this->model->storeUpdateEntity($payLoad);
            if ($data) {
                DB::commit();
                $messageData = [
                    'data'   => trans('appsettings.message.data_store_success'),
                    'status' => 1,
                ];
                return \Redirect::route('admin.appsettings.index')->with('message', $messageData);
            } else {
                DB::rollBack();
                $messageData = [
                    'data'   => trans('appsettings.message.something_wrong_try_again'),
                    'status' => 0,
                ];
                return \Redirect::route('admin.appsettings.changeSettings')->with('message', $messageData);
            }
        } catch (\Exception $exception) {
            report($exception);
            DB::rollBack();
            $messageData = [
                'data'   => trans('appsettings.message.something_wrong_try_again'),
                'status' => 0,
            ];
            return \Redirect::route('admin.appsettings.index')->with('message', $messageData);
        }
    }
}
