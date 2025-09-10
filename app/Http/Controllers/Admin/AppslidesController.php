<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CreateSlideRequest;
use App\Http\Requests\Admin\EditSlideRequest;
use App\Models\AppSlide;
use Breadcrumbs;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Class AppslidesController
 *
 * @package App\Http\Controllers\Admin
 */
class AppslidesController extends Controller
{
    /**
     * variable to store the model object
     * @var AppSlide
     */
    protected $model;

    /**
     * contructor to initialize model object
     * @param AppSlide $model ;
     */
    public function __construct(AppSlide $model)
    {
        $this->model = $model;
        $this->bindBreadcrumbs();
    }

    /*
     * Bind breadcrumbs of role module
     */
    public function bindBreadcrumbs()
    {
        Breadcrumbs::for('appslides.index', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push('Onboarding');
        });
    }

    /**
     * @return View
     */
    public function index(Request $request)
    {
        $role = getUserRole();
        if (!access()->allow('manage-onboarding') || $role->group != 'zevo') {
            abort(403);
        }
        try {
            $data                          = array();
            $data['pagination']            = config('zevolifesettings.datatable.pagination.short');
            $data['onBoardingappCount']    = $this->model->onBoardingCount('app');
            $data['onBoardingportalCount'] = $this->model->onBoardingCount('portal');
            $data['onBoardingeapCount']    = $this->model->onBoardingCount('eap');
            $data['ga_title']              = trans('page_title.appslides.appslides_list');
            return \view('admin.slides.index', $data);
        } catch (\Exception $exception) {
            report($exception);
            return response(trans('labels.common_title.something_wrong'), 400)
                ->header('Content-Type', 'text/plain');
        }
    }

    /**
     * @return View
     */
    public function create(Request $request, $type = 'app')
    {
        $role = getUserRole();
        if (!access()->allow('create-onboarding') || $role->group != 'zevo') {
            abort(403);
        }
        try {
            Breadcrumbs::for('appslides.add', function ($trail) use ($type) {
                $trail->push('Home', route('dashboard'));
                $trail->push('Onboarding', route('admin.appslides.index', '#' . $type));
                $trail->push('Add Onboarding');
            });

            $onBoardingCnt = $this->model->onBoardingCount($type);
            if (($type == 'eap' && $onBoardingCnt >= 5) || (($type == 'app' || $type == 'portal') && $onBoardingCnt >= 3)) {
                $messageData = [
                    'data'   => trans('appslides.validation.onboarding_max'),
                    'status' => 0,
                ];
                return \Redirect::route('admin.appslides.index')->with('message', $messageData);
            }
            $data                     = array();
            $data['ga_title']         = trans('page_title.appslides.create');
            $data['type']             = $type;
            $data['collectionType']   = 'app_slide.slideImage';
            $data['slideImageWidth']  = config('zevolifesettings.imageConversions.app_slide.slideImage.width');
            $data['slideImageHeight'] = config('zevolifesettings.imageConversions.app_slide.slideImage.height');
            $data['slideImageRatio']  = config('zevolifesettings.imageAspectRatio.app_slide.slideImage');
            $data['dataround'] = 'no';
            if ($type == 'portal') {
                $data['collectionType']   = 'app_slide.slideImagePortal';
                $data['slideImageWidth']  = config('zevolifesettings.imageConversions.app_slide.slideImagePortal.width');
                $data['slideImageHeight'] = config('zevolifesettings.imageConversions.app_slide.slideImagePortal.height');
                $data['slideImageRatio']  = config('zevolifesettings.imageAspectRatio.app_slide.slideImagePortal');
                $data['dataround'] = 'yes';
            } elseif ($type == 'eap') {
                $data['collectionType']   = 'app_slide.slideImage';
                $data['slideImageWidth']  = config('zevolifesettings.imageConversions.app_slide.slideImage.width');
                $data['slideImageHeight'] = config('zevolifesettings.imageConversions.app_slide.slideImage.height');
                $data['slideImageRatio']  = config('zevolifesettings.imageAspectRatio.app_slide.slideImage');
                $data['dataround'] = 'no';
            }

            return \view('admin.slides.create', $data);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('admin.appslides.index')->with('message', $messageData);
        }
    }

    /**
     * @param CreateSlideRequest     $request
     *
     * @return RedirectResponse
     */
    public function store(CreateSlideRequest $request)
    {
        $role = getUserRole();
        if (!access()->allow('create-onboarding') || $role->group != 'zevo') {
            abort(403);
        }
        try {
            DB::beginTransaction();
            $data = $this->model->storeEntity($request->all());
            if ($data) {
                $messageData = [
                    'data'   => trans('appslides.message.data_store_success'),
                    'status' => 1,
                ];
                DB::commit();
                return \Redirect::route('admin.appslides.index', array('#' . $request->type))->with('message', $messageData);
            } else {
                $messageData = [
                    'data'   => trans('appslides.message.something_wrong_try_again'),
                    'status' => 0,
                ];
                DB::rollBack();
                return \Redirect::route('admin.appslides.create')->with('message', $messageData);
            }
        } catch (\Exception $exception) {
            report($exception);
            DB::rollBack();
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('admin.appslides.index')->with('message', $messageData);
        }
    }

    /**
     * @return View
     */
    public function edit(Request $request, $id)
    {
        $role = getUserRole();
        if (!access()->allow('update-onboarding') || $role->group != 'zevo') {
            abort(403);
        }
        try {
            $data                     = array();
            $data['id']               = $id;
            $data['appSlideData']     = $this->model->getSlideDataById($id);
            $data['ga_title']         = trans('page_title.appslides.edit');
            $data['collectionType']   = 'app_slide.slideImage';
            $data['slideImageWidth']  = config('zevolifesettings.imageConversions.app_slide.slideImage.width');
            $data['slideImageHeight'] = config('zevolifesettings.imageConversions.app_slide.slideImage.height');
            $data['slideImageRatio']  = config('zevolifesettings.imageAspectRatio.app_slide.slideImage');
            $type                     = $data['appSlideData']['type'];
            $data['type']             = $type;
            Breadcrumbs::for('appslides.edit', function ($trail) use ($type) {
                $trail->push('Home', route('dashboard'));
                $trail->push('Onboarding', route('admin.appslides.index', '#' . $type));
                $trail->push('Edit Onboarding');
            });
            $data['dataround'] = 'no';
            if ($type == 'portal') {
                $data['collectionType']   = 'app_slide.slideImagePortal';
                $data['slideImageWidth']  = config('zevolifesettings.imageConversions.app_slide.slideImagePortal.width');
                $data['slideImageHeight'] = config('zevolifesettings.imageConversions.app_slide.slideImagePortal.height');
                $data['slideImageRatio']  = config('zevolifesettings.imageAspectRatio.app_slide.slideImagePortal');
                $data['dataround'] = 'yes';
            }
            return \view('admin.slides.edit', $data);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('admin.appslides.index')->with('message', $messageData);
        }
    }

    /**
     * @param EditSlideRequest $request
     *
     * @return RedirectResponse
     */
    public function update(EditSlideRequest $request, $id)
    {
        $role = getUserRole();
        if (!access()->allow('update-onboarding') || $role->group != 'zevo') {
            abort(403);
        }
        try {
            DB::beginTransaction();
            $data = $this->model->updateEntity($request->all(), $id);
            if ($data) {
                $messageData = [
                    'data'   => trans('appslides.message.data_update_success'),
                    'status' => 1,
                ];
                DB::commit();
                return \Redirect::route('admin.appslides.index', array('#' . $request->type))->with('message', $messageData);
            } else {
                $messageData = [
                    'data'   => trans('appslides.message.something_wrong_try_again'),
                    'status' => 0,
                ];
                DB::rollBack();
                return \Redirect::route('admin.appslides.edit', $id)->with('message', $messageData);
            }
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            DB::rollBack();
            return \Redirect::route('admin.appslides.index')->with('message', $messageData);
        }
    }

    /**
     * @param Request $request
     *
     * @return View
     */

    public function getSlides(Request $request)
    {
        $role = getUserRole();
        if (!access()->allow('manage-onboarding') || $role->group != 'zevo') {
            return response()->json([
                'message' => trans('appslides.message.unauthorized_access'),
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
            return \Redirect::route('admin.appslides.index')->with('message', $messageData);
        }
    }

    /**
     * @param  $id
     *
     * @return View
     */

    public function delete(AppSlide $slide)
    {
        $role = getUserRole();
        if (!access()->allow('delete-onboarding') || $role->group != 'zevo') {
            abort(403);
        }

        try {
            return $slide->deleteAppSlide();
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('admin.appslides.index')->with('message', $messageData);
        }
    }

    public function reorderingScreen(Request $request)
    {
        try {
            \DB::beginTransaction();
            $data = [
                'status'  => false,
                'message' => '',
            ];
            $positions = $request->input('positions', []);
            if (!empty($positions)) {
                $updated = $this->model->reorderingLesson($positions, $request->type);

                if ($updated) {
                    $data['status']  = true;
                    $data['message'] = trans('appslides.message.order_update_success');
                } else {
                    $data['message'] = trans('appslides.message.failed_update_order');
                }
            } else {
                $data['message'] = trans('appslides.message.nothing_change_order');
            }

            (($data['status']) ? \DB::commit() : \DB::rollback());
            return $data;
        } catch (\Exception $exception) {
            \DB::rollback();
            report($exception);
            return [
                'status'  => false,
                'message' => trans('appslides.message.something_wrong'),
            ];
        }
    }
}
