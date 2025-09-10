<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AddBulkChallengeImageLibRequest;
use App\Http\Requests\Admin\CreateChallengeImageLibRequest;
use App\Http\Requests\Admin\EditChallengeImageLibRequest;
use App\Models\ChallengeImageLibrary;
use App\Models\ChallengeImageLibraryTargetType;
use Breadcrumbs;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Class ChallengeImageLibraryController
 *
 * @package App\Http\Controllers\Admin
 */
class ChallengeImageLibraryController extends Controller
{
    /**
     * variable to store the model object
     * @var model
     */
    protected $model;

    /**
     * contructor to initialize model object
     * @param ChallengeImageLibrary $model;
     */
    public function __construct(ChallengeImageLibrary $model)
    {
        $this->model = $model;
        $this->bindBreadcrumbs();
    }

    /**
     * bind breadcrumbs of challenge image library module
     */
    private function bindBreadcrumbs()
    {
        Breadcrumbs::for('challengeImageLibrary.index', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push(trans('challengeLibrary.title.manage'));
        });
        Breadcrumbs::for('challengeImageLibrary.create', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push(trans('challengeLibrary.title.manage'), route('admin.challengeImageLibrary.index'));
            $trail->push(trans('challengeLibrary.title.add'));
        });
        Breadcrumbs::for('challengeImageLibrary.edit', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push(trans('challengeLibrary.title.manage'), route('admin.challengeImageLibrary.index'));
            $trail->push(trans('challengeLibrary.title.edit'));
        });
    }

    /**
     * @param Request $request
     * @return View
     */
    public function index(Request $request)
    {
        $role = getUserRole();
        if (!access()->allow('manage-challenge-image-library') || $role->group != 'zevo') {
            abort(403);
        }

        try {
            $data                = array();
            $data['target_type'] = ChallengeImageLibraryTargetType::get()->pluck('target', 'id')->toArray();
            $data['pagination']  = config('zevolifesettings.datatable.pagination.long');
            $data['ga_title']    = trans('page_title.challenges.image_library.index');

            return \view('admin.challenge_image_library.index', $data);
        } catch (\Exception $exception) {
            report($exception);
            abort(500);
        }
    }

    /**
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function getImages(Request $request)
    {
        $role = getUserRole();
        if (!access()->allow('manage-challenge-image-library') || $role->group != 'zevo') {
            return response()->json([
                'message' => trans('challengeLibrary.messages.unauthorized'),
            ], 422);
        }
        try {
            return $this->model->getTableData($request->all());
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('challengeLibrary.messages.something_wrong_try_again'),
                'status' => 0,
            ];
            return response()->json($messageData);
        }
    }

    /**
     * @param Request $request
     * @return View
     */
    public function create(Request $request)
    {
        $role = getUserRole();
        if (!access()->allow('add-challenge-image') || $role->group != 'zevo') {
            abort(403);
        }

        try {
            $data = [
                'target_type' => ChallengeImageLibraryTargetType::get()->pluck('target', 'id')->toArray(),
                'ga_title'    => trans('page_title.challenges.image_library.add_image'),
            ];

            return \view('admin.challenge_image_library.create', $data);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('challengeLibrary.messages.something_wrong_try_again'),
                'status' => 0,
            ];
            return \Redirect::route('admin.challengeImageLibrary.index')->with('message', $messageData);
        }
    }

    /**
     * @param CreateGoalRequest $request
     *
     * @return RedirectResponse
     */
    public function store(CreateChallengeImageLibRequest $request)
    {
        $role = getUserRole();
        if (!access()->allow('add-challenge-image') || $role->group != 'zevo') {
            abort(403);
        }

        try {
            \DB::beginTransaction();
            $data = $this->model->storeEntity($request->all());
            if ($data) {
                \DB::commit();
                $messageData = [
                    'data'   => trans('challengeLibrary.messages.added'),
                    'status' => 1,
                ];
                return \Redirect::route('admin.challengeImageLibrary.index')->with('message', $messageData);
            } else {
                \DB::rollback();
                $messageData = [
                    'data'   => trans('challengeLibrary.messages.something_wrong_try_again'),
                    'status' => 0,
                ];
                return \Redirect::route('admin.challengeImageLibrary.create')->with('message', $messageData);
            }
        } catch (\Exception $exception) {
            \DB::rollback();
            report($exception);
            $messageData = [
                'data'   => trans('challengeLibrary.messages.something_wrong_try_again'),
                'status' => 0,
            ];
            return \Redirect::route('admin.challengeImageLibrary.index')->with('message', $messageData);
        }
    }

    /**
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function storeBulk(AddBulkChallengeImageLibRequest $request)
    {
        $role = getUserRole();
        if (!access()->allow('add-challenge-image') || $role->group != 'zevo') {
            abort(403);
        }

        try {
            \DB::beginTransaction();
            $data = $this->model->storeBulkEntity($request->all());
            if ($data) {
                \DB::commit();
                $messageData = [
                    'data'   => trans('challengeLibrary.messages.uploaded'),
                    'status' => 1,
                ];
                \Session::put('message', $messageData);
                return response()->json($messageData);
            } else {
                \DB::rollback();
                $messageData = [
                    'data'   => trans('challengeLibrary.messages.something_wrong_try_again'),
                    'status' => 0,
                ];
                return response()->json($messageData);
            }
        } catch (\Exception $exception) {
            \DB::rollback();
            report($exception);
            $messageData = [
                'data'   => trans('challengeLibrary.messages.something_wrong_try_again'),
                'status' => 0,
            ];
            return response()->json($messageData);
        }
    }

    /**
     * @param ChallengeImageLibrary $image
     * @return View
     */
    public function edit(ChallengeImageLibrary $image)
    {
        $role = getUserRole();
        if (!access()->allow('update-challenge-image') || $role->group != 'zevo') {
            abort(403);
        }

        try {
            $data = [
                'target_type' => ChallengeImageLibraryTargetType::get()->pluck('target', 'id')->toArray(),
                'record'      => $image,
                'ga_title'    => trans('page_title.challenges.image_library.edit_image'),
            ];

            return \view('admin.challenge_image_library.edit', $data);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('challengeLibrary.messages.something_wrong_try_again'),
                'status' => 0,
            ];
            return \Redirect::route('admin.challengeImageLibrary.index')->with('message', $messageData);
        }
    }

    /**
     * @param EditChallengeImageLibRequest $requestt, ChallengeImageLibrary $image
     *
     * @return RedirectResponse
     */
    public function update(EditChallengeImageLibRequest $request, ChallengeImageLibrary $image)
    {
        $role = getUserRole();
        if (!access()->allow('update-challenge-image') || $role->group != 'zevo') {
            abort(403);
        }

        try {
            \DB::beginTransaction();
            $data = $image->updateEntity($request->all());

            if ($data) {
                \DB::commit();
                $messageData = [
                    'data'   => trans('challengeLibrary.messages.updated'),
                    'status' => 1,
                ];

                return \Redirect::route('admin.challengeImageLibrary.index')->with('message', $messageData);
            } else {
                \DB::rollback();
                $messageData = [
                    'data'   => trans('challengeLibrary.messages.something_wrong_try_again'),
                    'status' => 0,
                ];
                return \Redirect::route('admin.challengeImageLibrary.index')->with('message', $messageData);
            }
        } catch (\Exception $exception) {
            \DB::rollback();
            report($exception);
            $messageData = [
                'data'   => trans('challengeLibrary.messages.something_wrong_try_again'),
                'status' => 0,
            ];
            return \Redirect::route('admin.challengeImageLibrary.index')->with('message', $messageData);
        }
    }

    /**
     * @param  ChallengeImageLibrary $image
     *
     * @return RedirectResponse
     */
    public function delete(ChallengeImageLibrary $image)
    {
        $role = getUserRole();
        if (!access()->allow('delete-challenge-image') || $role->group != 'zevo') {
            abort(403);
        }

        try {
            return $image->deleteRecord();
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('challengeLibrary.messages.something_wrong_try_again'),
                'status' => 0,
            ];
            return response()->json($messageData);
        }
    }
}
