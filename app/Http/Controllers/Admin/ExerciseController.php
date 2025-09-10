<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CreateExerciseRequest;
use App\Http\Requests\Admin\EditExerciseRequest;
use App\Models\Exercise;
use App\Repositories\AuditLogRepository;
use Breadcrumbs;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Class ExerciseController
 *
 * @package App\Http\Controllers\Admin
 */
class ExerciseController extends Controller
{
    /**
     * variable to store the model object
     * @var Exercise
     */
    protected $model;

    /**
     * @var AuditLogRepository $auditLogRepository
     */
    private $auditLogRepository;

    /**
     * contructor to initialize model object
     * @param Exercise $model ;
     */
    public function __construct(Exercise $model, AuditLogRepository $auditLogRepository)
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
        Breadcrumbs::for('exercise.index', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push('Exercises');
        });
        Breadcrumbs::for('exercise.create', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push('Exercises', route('admin.exercises.index'));
            $trail->push('Add Exercise');
        });
        Breadcrumbs::for('exercise.edit', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push('Exercises', route('admin.exercises.index'));
            $trail->push('Edit Exercise');
        });
    }
    /**
     * @param Request $request
     * @return View
     */
    public function index(Request $request)
    {
        $role = getUserRole();
        if (!access()->allow('manage-exercise') || $role->group != 'zevo') {
            abort(403);
        }
        try {
            $data               = array();
            $data['pagination'] = config('zevolifesettings.datatable.pagination.short');
            $data['ga_title']   = trans('page_title.exercises.exercises_list');
            return \view('admin.exercise.index', $data);
        } catch (\Exception $exception) {
            report($exception);
            return response(trans('exercise.message.something_wrong'), 400)
                ->header('Content-Type', 'text/plain');
        }
    }

    /**
     * @param Request $request
     * @return View
     */
    public function create(Request $request)
    {
        $role = getUserRole();
        if (!access()->allow('create-exercise') || $role->group != 'zevo') {
            abort(403);
        }
        try {
            $data                        = array();
            $data['type']                = config('zevolifesettings.exercise_type');
            $data['trackerExerciseData'] = getUnMappedTrackerExercises();
            $data['ga_title']            = trans('page_title.exercises.create');
            return \view('admin.exercise.create', $data);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('admin.exercises.index')->with('message', $messageData);
        }
    }

    /**
     * @param CreateExerciseRequest $request
     *
     * @return RedirectResponse
     */
    public function store(CreateExerciseRequest $request)
    {
        $user   = auth()->user();
        $role   = getUserRole();
        if (!access()->allow('create-exercise') || $role->group != 'zevo') {
            abort(403);
        }
        try {
            \DB::beginTransaction();
            $payload     = $request->all();
            $userLogData = [
                'user_id'   => $user->id,
                'user_name' => $user->full_name,
            ];
            $data = $this->model->storeEntity($payload);

            $logData = array_merge($userLogData, $payload);
            $this->auditLogRepository->created("Exercise added successfully", $logData);

            if ($data) {
                \DB::commit();
                $messageData = [
                    'data'   => trans('exercise.message.data_store_success'),
                    'status' => 1,
                ];
                return \Redirect::route('admin.exercises.index')->with('message', $messageData);
            } else {
                \DB::rollback();
                $messageData = [
                    'data'   => trans('exercise.message.something_wrong_try_again'),
                    'status' => 0,
                ];
                return \Redirect::route('admin.exercises.create')->with('message', $messageData);
            }
        } catch (\Exception $exception) {
            \DB::rollback();
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('admin.exercises.index')->with('message', $messageData);
        }
    }

    /**
     * @param Request $request
     * @return View
     */
    public function edit(Request $request, Exercise $excercise)
    {
        $role = getUserRole();
        if (!access()->allow('update-exercise') || $role->group != 'zevo') {
            abort(403);
        }
        try {
            $mappedExercises = \App\Models\TrackerExercise::join('exercise_mapping', 'tracker_exercises.id', '=', 'exercise_mapping.tracker_exercise_id')
                ->where('exercise_mapping.exercise_id', $excercise->id)
                ->select('tracker_exercises.tracker_title', 'tracker_exercises.name')
                ->get()
                ->toArray();
            $mapData = array();
            if (!empty($mappedExercises)) {
                foreach ($mappedExercises as $value) {
                    if (empty($mapData[$value['tracker_title']])) {
                        $mapData[$value['tracker_title']] = '<span class="pill-span">' . $value['name'] . '</span>';
                    } else {
                        $mapData[$value['tracker_title']] = $mapData[$value['tracker_title']] . '<span class="pill-span">' . $value['name'] . '</span>';
                    }
                }
            }
            $data                        = array();
            $data['type']                = config('zevolifesettings.exercise_type');
            $data['id']                  = $excercise->id;
            $data['exerciseData']        = $excercise;
            $data['mappedExercises']     = $mapData;
            $data['trackerExerciseData'] = getUnMappedTrackerExercises();
            $data['ga_title']            = trans('page_title.exercises.edit');
            return \view('admin.exercise.edit', $data);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('admin.exercises.index')->with('message', $messageData);
        }
    }

    /**
     * @param EditExerciseRequest $request
     *
     * @return RedirectResponse
     */
    public function update(EditExerciseRequest $request, Exercise $excercise)
    {
        $user = auth()->user();
        $role = getUserRole();
        if (!access()->allow('update-exercise') || $role->group != 'zevo') {
            abort(403);
        }
        try {
            \DB::beginTransaction();
            $userLogData = [
                'user_id'   => $user->id,
                'user_name' => $user->full_name,
            ];
            $oldUsersData  = array_merge($userLogData, $excercise->toArray());
            $data    = $excercise->updateEntity($request->all());

            $updatedUsersData   = array_merge($userLogData, $request->all());
            $finalLogs          = ['olddata' => $oldUsersData, 'newdata' => $updatedUsersData];
            $this->auditLogRepository->created("Exercise updated successfully", $finalLogs);

            if ($data) {
                \DB::commit();
                $messageData = [
                    'data'   => trans('exercise.message.data_update_success'),
                    'status' => 1,
                ];

                return \Redirect::route('admin.exercises.index')->with('message', $messageData);
            } else {
                \DB::rollback();
                $messageData = [
                    'data'   => trans('exercise.message.something_wrong_try_again'),
                    'status' => 0,
                ];
                return \Redirect::route('admin.exercises.edit', $excercise->id)->with('message', $messageData);
            }
        } catch (\Exception $exception) {
            \DB::rollback();
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('admin.exercises.index')->with('message', $messageData);
        }
    }

    /**
     * @param Request $request
     *
     * @return View
     */

    public function getExercises(Request $request)
    {
        $role = getUserRole();
        if (!access()->allow('manage-exercise') || $role->group != 'zevo') {
            return response()->json([
                'message' => trans('exercise.message.unauthorized_access'),
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
            return \Redirect::route('admin.exercises.index')->with('message', $messageData);
        }
    }

    /**
     * @param  $id
     *
     * @return View
     */

    public function delete(Exercise $excercise)
    {
        $user = auth()->user();
        $role = getUserRole();
        if (!access()->allow('delete-exercise') || $role->group != 'zevo') {
            abort(403);
        }
        try {
            $userLogData = [
                'user_id'   => $user->id,
                'user_name' => $user->full_name,
            ];
            $logs  = array_merge($userLogData, ['deleted_exercise_id' => $excercise->id,'deleted_exercise_name' => $excercise->title]);
            $this->auditLogRepository->created("Exercise deleted successfully", $logs);

            return $excercise->deleteRecord();
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('admin.exercises.index')->with('message', $messageData);
        }
    }
}
