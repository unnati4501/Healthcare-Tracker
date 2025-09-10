<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CreatenotificationRequest;
use App\Models\Company;
use App\Models\Notification;
use Breadcrumbs;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Class NotificationsController
 *
 * @package App\Http\Controllers\Admin
 */
class NotificationsController extends Controller
{
    /**
     * variable to store the model object
     * @var Notification
     */
    protected $model;

    /**
     * contructor to initialize model object
     * @param Notification $model ;
     */
    public function __construct(Notification $model)
    {
        $this->model = $model;
        $this->bindBreadcrumbs();
    }

    /**
     * bind breadcrumbs of challenges module
     */
    private function bindBreadcrumbs()
    {
        Breadcrumbs::for('notification.index', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push('Notifications');
        });
        Breadcrumbs::for('notification.add', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push('Notifications', route('admin.notifications.index'));
            $trail->push('Create Notification');
        });
        Breadcrumbs::for('notification.details', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push('Notifications', route('admin.notifications.index'));
            $trail->push('Notification Details');
        });
    }

    /**
     * @param Request $request
     * @return View
     */
    public function index(Request $request)
    {
        if (!access()->allow('manage-notification')) {
            abort(403);
        }
        try {
            $data               = array();
            $data['pagination'] = config('zevolifesettings.datatable.pagination.short');
            $data['ga_title']   = trans('page_title.notifications.notifications_list');
            return \view('admin.notifications.index', $data);
        } catch (\Exception $exception) {
            report($exception);
            return response(trans('labels.common_title.something_wrong'), 400)
                ->header('Content-Type', 'text/plain');
        }
    }

    /**
     * @param Request $request
     * @return View
     */
    public function create(Request $request)
    {
        if (!access()->allow('create-notification')) {
            abort(403);
        }
        try {
            $data                = array();
            $data['companyData'] = getTeamMembersData();

            $company = \Auth::user()->company->first();
            if (!is_null($company)) {
                if ($company->is_reseller) {
                    $companyIds = Company::select('id')
                        ->where('id', $company->id)
                        ->orWhere('parent_id', $company->id)
                        ->get()
                        ->pluck('id')
                        ->toArray();
                    $resellerCompaniesList = [];
                    foreach ($data['companyData'] as $value) {
                        if (in_array($value['id'], $companyIds)) {
                            $resellerCompaniesList[] = $value;
                        }
                    }
                    $data['companyData'] = $resellerCompaniesList;
                } else {
                    foreach ($data['companyData'] as $value) {
                        if ($value['id'] == $company->id) {
                            $data['departmentData'] = $value['departments'];
                        }
                    }
                }
            }
            $data['ga_title'] = trans('page_title.notifications.create');
            return \view('admin.notifications.create', $data);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('admin.notifications.index')->with('message', $messageData);
        }
    }

    /**
     * @param CreatenotificationRequest $request
     *
     * @return RedirectResponse
     */
    public function store(CreatenotificationRequest $request)
    {
        if (!access()->allow('create-notification')) {
            abort(403);
        }
        try {
            \DB::beginTransaction();
            $payload = $request->all();
            $data    = $this->model->storeEntity($payload);
            if ($data) {
                \DB::commit();
                $messageData = [
                    'data'   => trans('labels.notification.data_store_success'),
                    'status' => 1,
                ];
                return \Redirect::route('admin.notifications.index')->with('message', $messageData);
            } else {
                \DB::rollback();
                $messageData = [
                    'data'   => trans('labels.common_title.something_wrong_try_again'),
                    'status' => 0,
                ];
                return \Redirect::route('admin.notifications.create')->with('message', $messageData);
            }
        } catch (UnreachableUrl $exception) {
            \DB::rollback();
            report($exception);
            return redirect()->back()->withInput()->withErrors("Invalid youtube link");
        } catch (\Exception $exception) {
            \DB::rollback();
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('admin.notifications.index')->with('message', $messageData);
        }
    }

    /**
     * @param Request $request
     *
     * @return RedirectResponse
     */

    public function getNotifications(Request $request)
    {
        if (!access()->allow('manage-notification')) {
            return response()->json([
                'message' => trans('labels.common_title.unauthorized_access'),
            ], 422);
        }
        try {
            return $this->model->getTableData($request->all());
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('admin.notifications.index')->with('message', $messageData);
        }
    }

    /**
     * @param  Notification $notification
     *
     * @return RedirectResponse
     */

    public function delete(Notification $notification)
    {
        try {
            return $notification->deleteRecord();
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('admin.notifications.index')->with('message', $messageData);
        }
    }

    /**
     * @param  Notification $notification
     *
     * @return View
     */

    public function getDetails(Notification $notification)
    {
        if (!access()->allow('manage-notification')) {
            return response()->json([
                'message' => trans('labels.common_title.unauthorized_access'),
            ], 422);
        }
        try {
            $data               = array();
            $data['record']     = $notification;
            $data['pagination'] = config('zevolifesettings.datatable.pagination.short');
            $data['ga_title']   = trans('page_title.notifications.details');
            return \view('admin.notifications.details', $data);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('admin.notifications.index')->with('message', $messageData);
        }
    }

    /**
     * @return View
     */
    public function show(Notification $notification, Request $request)
    {
        if (!access()->allow('view-notification')) {
            abort(403);
        }

        try {
            $data               = array();
            $data['recordData'] = $notification;
            $data['pagination'] = config('zevolifesettings.datatable.pagination.short');
            $data['ga_title']   = trans('page_title.notifications.details');
            return \view('admin.notifications.show', $data);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('admin.notifications.index')->with('message', $messageData);
        }
    }

    /**
     * @param Request $request
     *
     * @return RedirectResponse
     */

    public function getRecipientsList(Notification $notification, Request $request)
    {
        try {
            return $notification->getRecipientsData($request->all());
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('admin.notifications.index')->with('message', $messageData);
        }
    }
}
