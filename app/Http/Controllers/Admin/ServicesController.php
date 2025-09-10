<?php declare (strict_types = 1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CreateServiceRequest;
use App\Http\Requests\Admin\EditServiceRequest;
use App\Models\Service;
use App\Models\ServiceSubCategory;
use Breadcrumbs;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Class ServicesController
 *
 * @package App\Http\Controllers\Admin
 */
class ServicesController extends Controller
{
    /**
     * variable to store the model object
     * @var Service
     */
    protected $model;

    /**
     * variable to store the model object
     * @var ServicSubCategory
     */
    protected $serviceSubCategory;

    /**
     * contructor to initialize model object
     * @param Category $model
     */
    public function __construct(Service $model)
    {
        $this->model = $model;
        $this->bindBreadcrumbs();
    }

    /**
     * bind breadcrumbs of categories module
     */
    private function bindBreadcrumbs()
    {
        Breadcrumbs::for ('services.index', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push(trans('services.breadcrumbs.index'));
        });
        Breadcrumbs::for ('services.create', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push(trans('services.breadcrumbs.index'), route('admin.services.index'));
            $trail->push(trans('services.breadcrumbs.create'));
        });
        Breadcrumbs::for ('services.edit', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push(trans('services.breadcrumbs.index'), route('admin.services.index'));
            $trail->push(trans('services.breadcrumbs.edit'));
        });
    }

    /**
     * @return View
     */
    public function index(Request $request)
    {
        if (!access()->allow('manage-services')) {
            abort(403);
        }
        try {
            $data               = array();
            $data['pagination'] = config('zevolifesettings.datatable.pagination.short');
            $data['ga_title']   = trans('page_title.services.index');
            return \view('admin.services.index', $data);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('services.messages.something_wrong_try_again'),
                'status' => 0,
            ];
            return \Redirect::route('admin.services.index')->with('message', $messageData);
        }
    }

    /**
     * @param Request $request
     * @return Datatable
     */
    public function getServices(Request $request)
    {
        if (!access()->allow('manage-services')) {
            abort(403);
        }
        try {
            return $this->model->getTableData($request->all());
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('services.messages.something_wrong_try_again'),
                'status' => 0,
            ];
            return response()->json($messageData);
        }
    }

    /**
     * @param  Service $id
     * @return json
     */
    public function delete(Service $service)
    {
        if (!access()->allow('delete-services')) {
            abort(403);
        }
        try {
            return $service->deleteService();
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('categories.subcategories.messages.something_wrong_try_again'),
                'status' => 0,
            ];
            return response()->json($messageData);
        }
    }

    /**
     * @return View
     */
    public function create()
    {
        if (!access()->allow('create-services')) {
            abort(403);
        }
        try {
            $services     = $this->model->get()->pluck('name', 'id')->toArray();
            $serviceTypes = array(1 => 'Public', 0 => 'Private');
            $data         = [
                'services'    => $services,
                'serviceType' => $serviceTypes,
            ];
            $data['sessionDurationsMins'] = config('cronofy.dtSessionRulesMins');
            $data['ga_title']             = trans('page_title.services.create');
            return \view('admin.services.create', $data);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('categories.subcategories.messages.something_wrong_try_again'),
                'status' => 0,
            ];
            return \Redirect::route('admin.services.create')->with('message', $messageData);
        }
    }

    /**
     * @param CreateServiceRequest $request
     * @return RedirectResponse
     */
    public function store(CreateServiceRequest $request)
    {
        if (!access()->allow('create-services')) {
            return response()->json([
                'message' => trans('labels.common_title.unauthorized_access'),
            ], 401);
        }
        try {
            $data = $this->model->storeEntity($request->all());
            if ($data) {
                $messageData = [
                    'data'   => trans('services.messages.added'),
                    'status' => 1,
                ];
                return \Redirect::route('admin.services.index')->with('message', $messageData);
            } else {
                $messageData = [
                    'data'   => trans('services.messages.something_wrong_try_again'),
                    'status' => 0,
                ];
                return \Redirect::route('admin.services.create')->with('message', $messageData);
            }
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('services.messages.something_wrong_try_again'),
                'status' => 0,
            ];
            return \Redirect::route('admin.services.create')->with('message', $messageData);
        }
    }

    /**
     * @param Request $request, $id
     * @return View
     */
    public function edit(Request $request, Service $service)
    {
        if (!access()->allow('update-services')) {
            abort(403);
        }
        try {
            $data          = $service->getUpdateData();
            $subCategories = $service->serviceSubCategory()->where(['service_id' => $service->id])->get();
            $data['subCategories'] = $subCategories;
            $data['sessionDurationsMins'] = config('cronofy.dtSessionRulesMins');
            $data['ga_title']             = trans('page_title.services.edit');
            return \view('admin.services.edit', $data);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data' => trans('services.messages.something_wrong_try_again'),
                'status' => 0,
            ];
            return \Redirect::route('admin.services.edit', $id)->with('message', $messageData);
        }
    }

    /**
     * @param EditSubCategoryRequest $request
     * @return RedirectResponse
     */
    public function update(EditServiceRequest $request, Service $service)
    {
        if (!access()->allow('update-services')) {
            return response()->json([
                'message' => trans('labels.common_title.unauthorized_access'),
                'status'  => false,
            ], 401);
        }
        try {
            $data = $service->updateEntity($request->all());
            if ($data) {
                $messageData = [
                    'data'   => trans('services.messages.updated'),
                    'status' => 1,
                ];
                return \Redirect::route('admin.services.index', $service->category_id)->with('message', $messageData);
            } else {
                $messageData = [
                    'data'   => trans('services.messages.something_wrong_try_again'),
                    'status' => 0,
                ];
                return \Redirect::route('admin.services.edit', $service->id)->with('message', $messageData);
            }
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('services.messages.something_wrong_try_again'),
                'status' => 0,
            ];
            return \Redirect::back()->with('message', $messageData);
        }
    }
}
