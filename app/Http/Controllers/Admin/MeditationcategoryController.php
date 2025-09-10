<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CreateMeditationCatRequest;
use App\Http\Requests\Admin\EditMeditationCatRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use App\Models\MeditationCategory;
use Illuminate\Support\Facades\Validator;
use Illuminate\Contracts\Validation\Rule;
use JsValidator;

/**
 * Class MeditationcategoryController
 *
 * @package App\Http\Controllers\Admin
 */
class MeditationcategoryController extends Controller
{
    /**
     * variable to store the model object
     * @var MeditationCategory
     */
    protected $model;

    /**
     * contructor to initialize model object
     * @param MeditationCategory $model ;
     */
    public function __construct(MeditationCategory $model)
    {
        $this->model = $model;
    }


    /**
     * @param Request $request
     * @return View
     */
    public function index(Request $request)
    {
        if (!access()->allow('manage-meditation-category')) {
            abort(403);
        }
        try {
            $data = array();
            $data['pagination'] = config('zevolifesettings.datatable.pagination.short');

            return \view('admin.meditationcategory.index', $data);
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
        if (!access()->allow('create-meditation-category')) {
            abort(403);
        }
        try {
            $data = array();

            return \view('admin.meditationcategory.create', $data);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('admin.meditationcategorys.index')->with('message', $messageData);
        }
    }

    /**
     * @param CreateMeditationCatRequest $request
     *
     * @return RedirectResponse
     */
    public function store(CreateMeditationCatRequest $request)
    {
        try {
            \DB::beginTransaction();
            $payload = $request->all();
            $data = $this->model->storeEntity($payload);
            if ($data) {
                \DB::commit();
                $messageData = [
                    'data'   => trans('labels.meditationcategory.data_store_success'),
                    'status' => 1,
                ];
                return \Redirect::route('admin.meditationcategorys.index')->with('message', $messageData);
            } else {
                \DB::rollback();
                $messageData = [
                    'data'   => trans('labels.common_title.something_wrong_try_again'),
                    'status' => 0,
                ];
                return \Redirect::route('admin.meditationcategorys.create')->with('message', $messageData);
            }
        } catch (\Exception $exception) {
            \DB::rollback();
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('admin.meditationcategorys.index')->with('message', $messageData);
        }
    }

    /**
     * @param Request $request
     * @return View
     */
    public function edit(Request $request, MeditationCategory $meditationcat)
    {
        if (!access()->allow('update-meditation-category')) {
            abort(403);
        }
        try {
            $data = array();
            $data['id'] = $meditationcat->id;
            $data['meditationcategoryData'] = $meditationcat;

            return \view('admin.meditationcategory.edit', $data);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('admin.meditationcategorys.index')->with('message', $messageData);
        }
    }

    /**
     * @param EditMeditationCatRequest $request
     *
     * @return RedirectResponse
     */
    public function update(EditMeditationCatRequest $request, MeditationCategory $meditationcat)
    {
        try {
            \DB::beginTransaction();
            $data = $meditationcat->updateEntity($request->all());
            if ($data) {
                \DB::commit();
                $messageData = [
                    'data'   => trans('labels.meditationcategory.data_update_success'),
                    'status' => 1,
                ];

                return \Redirect::route('admin.meditationcategorys.index')->with('message', $messageData);
            } else {
                \DB::rollback();
                $messageData = [
                    'data'   => trans('labels.common_title.something_wrong_try_again'),
                    'status' => 0,
                ];
                return \Redirect::route('admin.meditationcategorys.edit', $meditationcat->id)->with('message', $messageData);
            }
        } catch (\Exception $exception) {
            \DB::rollback();
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('admin.meditationcategorys.index')->with('message', $messageData);
        }
    }

    /**
     * @param Request $request
     *
     * @return View
     */

    public function getMeditationCategorys(Request $request)
    {
        try {
            return $this->model->getTableData($request->all());
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('admin.meditationcategorys.index')->with('message', $messageData);
        }
    }

    /**
     * @param  $id
     *
     * @return View
     */

    public function delete(MeditationCategory $meditationcat)
    {
        if (!access()->allow('delete-meditation-category')) {
            abort(403);
        }
        try {
            return $meditationcat->deleteRecord();
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('admin.meditationcategorys.index')->with('message', $messageData);
        }
    }
}
