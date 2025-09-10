<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CreateMoodTagRequest;
use App\Http\Requests\Admin\EditMoodTagRequest;
use App\Models\MoodTag;
use App\Repositories\AuditLogRepository;
use Breadcrumbs;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Class MoodTagsController
 *
 * @package App\Http\Controllers\Admin
 */
class MoodTagsController extends Controller
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
    public function __construct(MoodTag $model, AuditLogRepository $auditLogRepository)
    {
        $this->model              = $model;
        $this->auditLogRepository = $auditLogRepository;
        $this->bindBreadcrumbs();
    }

    /**
     * bind breadcrumbs of mood tags module
     */
    private function bindBreadcrumbs()
    {
        Breadcrumbs::for('moodTags.index', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push(trans('moods.tags.title.manage'));
        });
        Breadcrumbs::for('moodTags.create', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push(trans('moods.tags.title.manage'), route('admin.moodTags.index'));
            $trail->push(trans('moods.tags.title.add'));
        });
        Breadcrumbs::for('moodTags.edit', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push(trans('moods.tags.title.manage'), route('admin.moodTags.index'));
            $trail->push(trans('moods.tags.title.edit'));
        });
    }

    /**
     * @param Request $request
     * @return View
     */
    public function index(Request $request)
    {
        $role = getUserRole();
        if (!access()->allow('manage-mood-tags') || $role->group != 'zevo') {
            abort(403);
        }

        try {
            $data               = array();
            $data['pagination'] = config('zevolifesettings.datatable.pagination.short');
            $data['tagCount']   = $this->model->get()->count();
            $data['ga_title']   = trans('page_title.moodTags.moodTags_list');
            return \view('admin.moodTags.index', $data);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('moods.tags.messages.something_wrong_try_again'),
                'status' => 0,
            ];
            return \Redirect::route('admin.moodTags.index')->with('message', $messageData);
        }
    }

    /**
     * @param Request $request
     * @return View
     */
    public function create(Request $request)
    {
        $role = getUserRole();
        if (!access()->allow('create-mood-tags') || $role->group != 'zevo') {
            abort(403);
        }

        if ($this->model->get()->count() >= 16) {
            $messageData = [
                'data'   => trans('moods.tags.messages.limit'),
                'status' => 2,
            ];
            return \Redirect::route('admin.moodTags.index')->with('message', $messageData);
        }

        try {
            $data             = array();
            $data['ga_title'] = trans('page_title.moodTags.create');
            return \view('admin.moodTags.create', $data);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('moods.tags.messages.something_wrong_try_again'),
                'status' => 0,
            ];
            return \Redirect::route('admin.moodTags.index')->with('message', $messageData);
        }
    }

    /**
     * @param CreateMoodTagRequest $request
     *
     * @return RedirectResponse
     */
    public function store(CreateMoodTagRequest $request)
    {
        $user = auth()->user();
        $role = getUserRole();
        if (!access()->allow('create-mood-tags') || $role->group != 'zevo') {
            abort(403);
        }

        if ($this->model->get()->count() >= 16) {
            abort(403, trans('moods.tags.messages.limit'));
        }

        try {
            \DB::beginTransaction();

            $userLogData = [
                'user_id'   => $user->id,
                'user_name' => $user->full_name,
            ];
            $data = $this->model->storeEntity($request->all());

            $logData = array_merge($userLogData, $request->all());
            $this->auditLogRepository->created("Mood tag added successfully", $logData);

            if ($data) {
                \DB::commit();
                $messageData = [
                    'data'   => trans('moods.tags.messages.created'),
                    'status' => 1,
                ];
                return \Redirect::route('admin.moodTags.index')->with('message', $messageData);
            } else {
                \DB::rollback();
                $messageData = [
                    'data'   => trans('moods.tags.messages.something_wrong_try_again'),
                    'status' => 0,
                ];
                return \Redirect::route('admin.moodTags.create')->with('message', $messageData);
            }
        } catch (\Exception $exception) {
            \DB::rollback();
            report($exception);
            $messageData = [
                'data'   => trans('moods.tags.messages.something_wrong_try_again'),
                'status' => 0,
            ];
            return \Redirect::route('admin.moodTags.index')->with('message', $messageData);
        }
    }

    /**
     * @param Request $request , MoodTag $moodTag
     * @return View
     */
    public function edit(Request $request, MoodTag $moodTag)
    {
        $role = getUserRole();
        if (!access()->allow('update-mood-tags') || $role->group != 'zevo') {
            abort(403);
        }

        try {
            $data             = array();
            $data             = $moodTag->getUpdateData();
            $data['ga_title'] = trans('page_title.moodTags.edit');
            return \view('admin.moodTags.edit', $data);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('moods.tags.messages.something_wrong_try_again'),
                'status' => 0,
            ];
            return \Redirect::route('admin.moodTags.index')->with('message', $messageData);
        }
    }

    /**
     * @param EditMoodTagRequest $request, MoodTag $moodTag
     *
     * @return RedirectResponse
     */
    public function update(EditMoodTagRequest $request, MoodTag $moodTag)
    {
        $user = auth()->user();
        $role = getUserRole();
        if (!access()->allow('update-mood-tags') || $role->group != 'zevo') {
            abort(403);
        }

        try {
            \DB::beginTransaction();
            $payload = $request->all();

            $userLogData = [
                'user_id'   => $user->id,
                'user_name' => $user->full_name,
            ];
            $oldUsersData  = array_merge($userLogData, $moodTag->toArray());
            $data    = $moodTag->updateEntity($payload);

            $updatedUsersData   = array_merge($userLogData, $payload);
            $finalLogs          = ['olddata' => $oldUsersData, 'newdata' => $updatedUsersData];
            $this->auditLogRepository->created("Mood tag updated successfully", $finalLogs);

            if ($data) {
                \DB::commit();
                $messageData = [
                    'data'   => trans('moods.tags.messages.updated'),
                    'status' => 1,
                ];

                return \Redirect::route('admin.moodTags.index')->with('message', $messageData);
            } else {
                \DB::rollback();
                $messageData = [
                    'data'   => trans('moods.tags.messages.something_wrong_try_again'),
                    'status' => 0,
                ];
                return \Redirect::route('admin.moodTags.edit', $moodTag->id)->with('message', $messageData);
            }
        } catch (\Exception $exception) {
            \DB::rollback();
            report($exception);
            $messageData = [
                'data'   => trans('moods.tags.messages.something_wrong_try_again'),
                'status' => 0,
            ];
            return \Redirect::route('admin.moodTags.index')->with('message', $messageData);
        }
    }

    /**
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function getMoodTags(Request $request)
    {
        $role = getUserRole();
        if (!access()->allow('manage-mood-tags') || $role->group != 'zevo') {
            return response()->json([
                'message' => trans('moods.tags.messages.unauthorized_access'),
            ], 422);
        }
        try {
            return $this->model->getTableData($request->all());
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('moods.tags.messages.something_wrong_try_again'),
                'status' => 0,
            ];
            return response()->json($messageData);
        }
    }

    /**
     * @param  MoodTag $moodTag
     *
     * @return RedirectResponse
     */
    public function delete(MoodTag $moodTag)
    {
        $user = auth()->user();
        $role = getUserRole();
        if (!access()->allow('delete-mood-tags') || $role->group != 'zevo') {
            abort(403);
        }

        try {
            $userLogData = [
                'user_id'   => $user->id,
                'user_name' => $user->full_name,
            ];
            $logs  = array_merge($userLogData, ['deleted_tag_id' => $moodTag->id,'deleted_tag_name' => $moodTag->tag]);
            $this->auditLogRepository->created("Mood tag deleted successfully", $logs);

            return $moodTag->deleteRecord();
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('moods.tags.messages.something_wrong_try_again'),
                'status' => 0,
            ];
            return response()->json($messageData);
        }
    }
}
