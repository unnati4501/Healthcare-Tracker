<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CreateMoodRequest;
use App\Http\Requests\Admin\EditMoodRequest;
use App\Models\Mood;
use App\Repositories\AuditLogRepository;
use Breadcrumbs;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Class MoodsController
 *
 * @package App\Http\Controllers\Admin
 */
class MoodsController extends Controller
{
    /**
     * variable to store the model object
     * @var model
     */
    protected $model;

    /**
     * @var AuditLogRepository $auditLogRepository
     */
    private $auditLogRepository;

    /**
     * contructor to initialize model object
     * @param Mood $model;
     */
    public function __construct(Mood $model, AuditLogRepository $auditLogRepository)
    {
        $this->model              = $model;
        $this->auditLogRepository = $auditLogRepository;
        $this->bindBreadcrumbs();
    }

    /**
     * bind breadcrumbs of moods module
     */
    private function bindBreadcrumbs()
    {
        Breadcrumbs::for('moods.index', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push(trans('moods.title.manage'));
        });
        Breadcrumbs::for('moods.create', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push(trans('moods.title.manage'), route('admin.moods.index'));
            $trail->push(trans('moods.title.add'));
        });
        Breadcrumbs::for('moods.edit', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push(trans('moods.title.manage'), route('admin.moods.index'));
            $trail->push(trans('moods.title.edit'));
        });
    }

    /**
     * @param Request $request
     * @return View
     */
    public function index(Request $request)
    {
        $role = getUserRole();
        if (!access()->allow('manage-moods') || $role->group != 'zevo') {
            abort(403);
        }

        try {
            $data               = array();
            $data['pagination'] = config('zevolifesettings.datatable.pagination.short');
            $data['moodCount']  = $this->model->get()->count();
            $data['ga_title']   = trans('page_title.moods.moods_list');
            return \view('admin.moods.index', $data);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('moods.messages.something_wrong_try_again'),
                'status' => 0,
            ];
            return \Redirect::route('admin.moods.index')->with('message', $messageData);
        }
    }

    /**
     * @param Request $request
     * @return View
     */
    public function create(Request $request)
    {
        $role = getUserRole();
        if (!access()->allow('create-moods') || $role->group != 'zevo') {
            abort(403);
        }

        if ($this->model->get()->count() >= 16) {
            $messageData = [
                'data'   => trans('moods.messages.limit'),
                'status' => 2,
            ];
            return \Redirect::route('admin.moods.index')->with('message', $messageData);
        }

        try {
            $data             = array();
            $data['ga_title'] = trans('page_title.moods.create');
            return \view('admin.moods.create', $data);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('moods.messages.something_wrong_try_again'),
                'status' => 0,
            ];
            return \Redirect::route('admin.moods.index')->with('message', $messageData);
        }
    }

    /**
     * @param CreateMoodRequest $request
     *
     * @return RedirectResponse
     */
    public function store(CreateMoodRequest $request)
    {
        $user = auth()->user();
        $role = getUserRole();
        if (!access()->allow('create-moods') || $role->group != 'zevo') {
            abort(403);
        }

        if ($this->model->get()->count() >= 16) {
            abort(403, trans('moods.messages.limit'));
        }

        try {
            \DB::beginTransaction();

            $userLogData = [
                'user_id'   => $user->id,
                'user_name' => $user->full_name,
            ];
            $data = $this->model->storeEntity($request->all());

            $logData = array_merge($userLogData, $request->all());
            $this->auditLogRepository->created("Mood added successfully", $logData);

            if ($data) {
                \DB::commit();
                $messageData = [
                    'data'   => trans('moods.messages.created'),
                    'status' => 1,
                ];
                return \Redirect::route('admin.moods.index')->with('message', $messageData);
            } else {
                \DB::rollback();
                $messageData = [
                    'data'   => trans('moods.messages.something_wrong_try_again'),
                    'status' => 0,
                ];
                return \Redirect::route('admin.moods.create')->with('message', $messageData);
            }
        } catch (\Exception $exception) {
            \DB::rollback();
            report($exception);
            $messageData = [
                'data'   => trans('moods.messages.something_wrong_try_again'),
                'status' => 0,
            ];
            return \Redirect::route('admin.moods.index')->with('message', $messageData);
        }
    }

    /**
     * @param Request $request , Mood $mood
     * @return View
     */
    public function edit(Request $request, Mood $mood)
    {
        $role = getUserRole();
        if (!access()->allow('update-moods') || $role->group != 'zevo') {
            abort(403);
        }

        try {
            $data             = array();
            $data             = $mood->getUpdateData();
            $data['ga_title'] = trans('page_title.moods.edit');
            return \view('admin.moods.edit', $data);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('moods.messages.something_wrong_try_again'),
                'status' => 0,
            ];
            return \Redirect::route('admin.moods.index')->with('message', $messageData);
        }
    }

    /**
     * @param EditMoodRequest $request, Mood $mood
     *
     * @return RedirectResponse
     */
    public function update(EditMoodRequest $request, Mood $mood)
    {
        $user = auth()->user();
        $role = getUserRole();
        if (!access()->allow('update-moods') || $role->group != 'zevo') {
            abort(403);
        }

        try {
            \DB::beginTransaction();
            $payload = $request->all();

            $userLogData = [
                'user_id'   => $user->id,
                'user_name' => $user->full_name,
            ];
            $oldUsersData  = array_merge($userLogData, $mood->toArray());
            $data    = $mood->updateEntity($payload);

            $updatedUsersData   = array_merge($userLogData, $payload);
            $finalLogs          = ['olddata' => $oldUsersData, 'newdata' => $updatedUsersData];
            $this->auditLogRepository->created("Mood updated successfully", $finalLogs);

            if ($data) {
                \DB::commit();
                $messageData = [
                    'data'   => trans('moods.messages.updated'),
                    'status' => 1,
                ];

                return \Redirect::route('admin.moods.index')->with('message', $messageData);
            } else {
                \DB::rollback();
                $messageData = [
                    'data'   => trans('moods.messages.something_wrong_try_again'),
                    'status' => 0,
                ];
                return \Redirect::route('admin.moods.edit', $mood->id)->with('message', $messageData);
            }
        } catch (\Exception $exception) {
            \DB::rollback();
            report($exception);
            $messageData = [
                'data'   => trans('moods.messages.something_wrong_try_again'),
                'status' => 0,
            ];
            return \Redirect::route('admin.moods.index')->with('message', $messageData);
        }
    }

    /**
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function getMoods(Request $request)
    {
        $role = getUserRole();
        if (!access()->allow('manage-moods') || $role->group != 'zevo') {
            return response()->json([
                'message' => trans('moods.messages.unauthorized_access'),
            ], 422);
        }
        try {
            return $this->model->getTableData($request->all());
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('moods.messages.something_wrong_try_again'),
                'status' => 0,
            ];
            return response()->json($messageData);
        }
    }

    /**
     * @param  Mood $mood
     *
     * @return RedirectResponse
     */
    public function delete(Mood $mood)
    {
        $user = auth()->user();
        $role = getUserRole();
        if (!access()->allow('delete-moods') || $role->group != 'zevo') {
            abort(403);
        }

        try {
            $userLogData = [
                'user_id'   => $user->id,
                'user_name' => $user->full_name,
            ];
            $logs  = array_merge($userLogData, ['deleted_mood_id' => $mood->id,'deleted_mood_name' => $mood->title]);
            $this->auditLogRepository->created("Mood deleted successfully", $logs);

            return $mood->deleteRecord();
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('moods.messages.something_wrong_try_again'),
                'status' => 0,
            ];
            return response()->json($messageData);
        }
    }
}
