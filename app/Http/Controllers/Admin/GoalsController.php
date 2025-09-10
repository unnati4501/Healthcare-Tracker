<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CreateGoalRequest;
use App\Http\Requests\Admin\EditGoalRequest;
use App\Models\Goal;
use App\Repositories\AuditLogRepository;
use Breadcrumbs;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Class GoalsController
 *
 * @package App\Http\Controllers\Admin
 */
class GoalsController extends Controller
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
     * @param Goal $model;
     */
    public function __construct(Goal $model, AuditLogRepository $auditLogRepository)
    {
        $this->model              = $model;
        $this->auditLogRepository = $auditLogRepository;
        $this->bindBreadcrumbs();
    }

    /*
     * Bind breadcrumbs of role module
     */
    public function bindBreadcrumbs()
    {
        Breadcrumbs::for('goals.index', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push('Goals');
        });
        Breadcrumbs::for('goals.create', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push('Goals', route('admin.goals.index'));
            $trail->push('Add Goal');
        });
        Breadcrumbs::for('goals.edit', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push('Goals', route('admin.goals.index'));
            $trail->push('Edit Goal');
        });
        Breadcrumbs::for('goals.view', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push('Goals', route('admin.goals.index'));
            $trail->push('Goal Mapped Tags');
        });
    }

    /**
     * @param Request $request
     * @return View
     */
    public function index(Request $request)
    {
        $role = getUserRole();
        if (!access()->allow('manage-goal-tags') || $role->group != 'zevo') {
            abort(403);
        }

        try {
            $data               = array();
            $data['pagination'] = config('zevolifesettings.datatable.pagination.short');
            $data['goalCount']  = $this->model->get()->count();
            $data['ga_title']   = trans('page_title.goals.goals_list');

            return \view('admin.goals.index', $data);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('goals.message.something_wrong_try_again'),
                'status' => 0,
            ];
            return \Redirect::route('admin.goals.index')->with('message', $messageData);
        }
    }

    /**
     * @param Request $request
     * @return View
     */
    public function create(Request $request)
    {
        $role = getUserRole();
        if (!access()->allow('create-goal-tags') || $role->group != 'zevo') {
            abort(403);
        }

        if ($this->model->get()->count() >= 10) {
            $messageData = [
                'data'   => trans('goals.message.more_then_10goaltags'),
                'status' => 2,
            ];
            return \Redirect::route('admin.goals.index')->with('message', $messageData);
        }

        try {
            $data             = array();
            $data['ga_title'] = trans('page_title.goals.create');
            return \view('admin.goals.create', $data);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('goals.message.something_wrong_try_again'),
                'status' => 0,
            ];
            return \Redirect::route('admin.goals.index')->with('message', $messageData);
        }
    }

    /**
     * @param CreateGoalRequest $request
     *
     * @return RedirectResponse
     */
    public function store(CreateGoalRequest $request)
    {
        $user = auth()->user();
        $role = getUserRole();
        if (!access()->allow('create-goal-tags') || $role->group != 'zevo') {
            abort(403);
        }

        if ($this->model->get()->count() >= 10) {
            abort(403, trans('goals.message.more_then_10goaltags'));
        }

        try {
            \DB::beginTransaction();

            $userLogData = [
                'user_id'   => $user->id,
                'user_name' => $user->full_name,
            ];
            $data = $this->model->storeEntity($request->all());

            $logData = array_merge($userLogData, $request->all());
            $this->auditLogRepository->created("Goal added successfully", $logData);

            if ($data) {
                \DB::commit();
                $messageData = [
                    'data'   => trans('goals.message.data_store_success'),
                    'status' => 1,
                ];
                return \Redirect::route('admin.goals.index')->with('message', $messageData);
            } else {
                \DB::rollback();
                $messageData = [
                    'data'   => trans('goals.message.something_wrong_try_again'),
                    'status' => 0,
                ];
                return \Redirect::route('admin.goals.create')->with('message', $messageData);
            }
        } catch (\Exception $exception) {
            \DB::rollback();
            report($exception);
            $messageData = [
                'data'   => trans('goals.message.something_wrong_try_again'),
                'status' => 0,
            ];
            return \Redirect::route('admin.goals.index')->with('message', $messageData);
        }
    }

    /**
     * @param Request $request , Goal $goal
     * @return View
     */
    public function edit(Request $request, Goal $goal)
    {
        $role = getUserRole();
        if (!access()->allow('update-goal-tags') || $role->group != 'zevo') {
            abort(403);
        }

        try {
            $data             = $goal->getUpdateData();
            $data['ga_title'] = trans('page_title.goals.edit');
            return \view('admin.goals.edit', $data);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('goals.message.something_wrong_try_again'),
                'status' => 0,
            ];
            return \Redirect::route('admin.goals.index')->with('message', $messageData);
        }
    }

    /**
     * @param EditGoalRequest $request, Goal $goal
     *
     * @return RedirectResponse
     */
    public function update(EditGoalRequest $request, Goal $goal)
    {
        $user = auth()->user();
        $role = getUserRole();
        if (!access()->allow('update-goal-tags') || $role->group != 'zevo') {
            abort(403);
        }

        try {
            \DB::beginTransaction();
            $payload = $request->all();
            $userLogData = [
                'user_id'   => $user->id,
                'user_name' => $user->full_name,
            ];
            $oldUsersData       = array_merge($userLogData, $goal->toArray());
            $data               = $goal->updateEntity($payload);
            $updatedUsersData   = array_merge($userLogData, $payload);
            $finalLogs          = ['olddata' => $oldUsersData, 'newdata' => $updatedUsersData];
            $this->auditLogRepository->created("Goal updated successfully", $finalLogs);

            if ($data) {
                \DB::commit();
                $messageData = [
                    'data'   => trans('goals.message.data_update_success'),
                    'status' => 1,
                ];

                return \Redirect::route('admin.goals.index')->with('message', $messageData);
            } else {
                \DB::rollback();
                $messageData = [
                    'data'   => trans('goals.message.something_wrong_try_again'),
                    'status' => 0,
                ];
                return \Redirect::route('admin.goals.index')->with('message', $messageData);
            }
        } catch (\Exception $exception) {
            \DB::rollback();
            report($exception);
            $messageData = [
                'data'   => trans('goals.message.something_wrong_try_again'),
                'status' => 0,
            ];
            return \Redirect::route('admin.goals.index')->with('message', $messageData);
        }
    }

    /**
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function getGoals(Request $request)
    {
        $role = getUserRole();
        if (!access()->allow('manage-goal-tags') || $role->group != 'zevo') {
            return response()->json([
                'message' => trans('goals.message.unauthorized_access'),
            ], 422);
        }
        try {
            return $this->model->getTableData($request->all());
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('goals.message.something_wrong_try_again'),
                'status' => 0,
            ];
            return response()->json($messageData);
        }
    }

    /**
     * @param  Goal $goal
     *
     * @return RedirectResponse
     */
    public function delete(Goal $goal)
    {
        $user = auth()->user();
        $role = getUserRole();
        if (!access()->allow('delete-goal-tags') || $role->group != 'zevo') {
            abort(403);
        }

        $totalContent = $this->model->getAssociatedGoalTags($goal->id);

        if ($totalContent->count() > 0) {
            abort(403);
        }

        try {
            $userLogData = [
                'user_id'   => $user->id,
                'user_name' => $user->full_name,
            ];
            $logs  = array_merge($userLogData, ['deleted_goal_id' => $goal->id,'deleted_goal_name' => $goal->title]);
            $this->auditLogRepository->created("Goal deleted successfully", $logs);

            return $goal->deleteRecord();
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('goals.message.something_wrong_try_again'),
                'status' => 0,
            ];
            return response()->json($messageData);
        }
    }

    /**
     * @param  Goal $goal
     *
     * @return RedirectResponse
     */
    public function show(Goal $goal, Request $request)
    {
        $role = getUserRole();
        if (!access()->allow('view-goal-tags') || $role->group != 'zevo') {
            abort(403);
        }
        try {
            $data            = array();
            $data['goal_id'] = $goal->id;
            $data['tagType'] = array(
                'feed'       => trans('goals.table.feed'),
                'recipe'     => trans('goals.table.recipe'),
                'meditation' => trans('goals.table.meditation'),
                'course'     => trans('goals.table.masterclass'),
                'webinar'    => trans('goals.table.webinar'),
            );
            $data['pagination'] = config('zevolifesettings.datatable.pagination.short');
            $data['goalCount']  = $this->model->get()->count();
            $data['ga_title']   = ucfirst($goal->title . ' ' . trans('page_title.goals.goals_view'));
            return \view('admin.goals.view', $data);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('goals.message.something_wrong_try_again'),
                'status' => 0,
            ];
            return \Redirect::route('admin.goals.index')->with('message', $messageData);
        }
    }

    /**
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function getGoalsTags(Request $request)
    {
        try {
            return $this->model->getTagsTableData($request->all());
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('goals.message.something_wrong_try_again'),
                'status' => 0,
            ];
            return response()->json($messageData);
        }
    }

    /**
     * @param  $id
     * @param  $type
     * @return RedirectResponse
     */
    public function deletetag($id, $type = null)
    {
        $role = getUserRole();
        if (!access()->allow('dismantle-goal-tags') || $role->group != 'zevo') {
            abort(403);
        }

        try {
            return $this->model->deleteTypeRecord($id, $type);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('goals.message.something_wrong_try_again'),
                'status' => 0,
            ];
            return response()->json($messageData);
        }
    }
}
