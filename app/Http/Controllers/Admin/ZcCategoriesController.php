<?php declare (strict_types = 1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CreateSurveyCategoryRequest;
use App\Http\Requests\Admin\CreateSurveySubCategoryRequest;
use App\Http\Requests\Admin\EditSurveyCategoryRequest;
use App\Http\Requests\Admin\EditSurveySubCategoryRequest;
use App\Models\Goal;
use App\Models\SurveyCategory;
use App\Models\SurveySubCategory;
use Breadcrumbs;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Class ZcCategoriesController
 *
 * @package App\Http\Controllers\Admin
 */
class ZcCategoriesController extends Controller
{
    /**
     * variable to store the model object
     * @var SurveyCategory
     */
    protected $model;
    protected $surveysubCategory;

    /**
     * contructor to initialize model object
     * @param SurveyCategory $model, SurveySubCategory $surveysubCategory
     */
    public function __construct(SurveyCategory $model, SurveySubCategory $surveysubCategory)
    {
        $this->model             = $model;
        $this->surveysubCategory = $surveysubCategory;
        $this->bindBreadcrumbs();
    }

    /**
     * bind breadcrumbs of survey categories module
     */
    private function bindBreadcrumbs()
    {
        // survey categories crud
        Breadcrumbs::for('surveycategories.index', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push('Survey Categories');
        });
        Breadcrumbs::for('surveycategories.create', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push('Survey Categories', route('admin.surveycategories.index'));
            $trail->push('Add Category');
        });
        Breadcrumbs::for('surveycategories.edit', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push('Survey Categories', route('admin.surveycategories.index'));
            $trail->push('Edit Category');
        });
        // survey subcategories crud
        Breadcrumbs::for('surveysubcategories.index', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push('Survey Categories', route('admin.surveycategories.index'));
            $trail->push('Subcategories');
        });
        Breadcrumbs::for('surveysubcategories.create', function ($trail, $categoryId) {
            $trail->push('Home', route('dashboard'));
            $trail->push('Survey Categories', route('admin.surveycategories.index'));
            $trail->push('Subcategories', route('admin.surveysubcategories.index', $categoryId));
            $trail->push('Add Subcategory');
        });
        Breadcrumbs::for('surveysubcategories.edit', function ($trail, $categoryId) {
            $trail->push('Home', route('dashboard'));
            $trail->push('Survey Categories', route('admin.surveycategories.index'));
            $trail->push('Subcategories', route('admin.surveysubcategories.index', $categoryId));
            $trail->push('Edit Subcategory');
        });
    }

    /**
     * @return View
     */
    public function index(Request $request)
    {
        $role = getUserRole();
        if (!access()->allow('manage-survey-category') || $role->group != 'zevo') {
            abort(403);
        }
        try {
            $data                           = array();
            $data['pagination']             = config('zevolifesettings.datatable.pagination.long');
            $data['surveyCategoryCount']    = $this->model->where("status", true)->count();
            $data['surveyCategoryMaxCount'] = config('zevolifesettings.zc_survey.survey_category_max_count');
            $data['ga_title']               = trans('page_title.surveycategories.surveycategories_list');
            return \view('admin.surveycategories.index', $data);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong_try_again'),
                'status' => 0,
            ];
            return \Redirect::route('admin.surveycategories.index')->with('message', $messageData);
        }
    }

    /**
     *
     * @param Request $request
     * @return Datatable
     */
    public function getCategories(Request $request)
    {
        $role = getUserRole();
        if (!access()->allow('manage-survey-category') || $role->group != 'zevo') {
            return response()->json([
                'message' => trans('labels.common_title.unauthorized_access'),
            ], 422);
        }
        try {
            return $this->model->getTableData($request->all());
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong_try_again'),
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
        if (!access()->allow('create-survey-category')) {
            abort(403);
        }

        $catCount = $this->model->where("status", true)->count();

        if ($catCount >= config('zevolifesettings.zc_survey.survey_category_max_count')) {
            return \Redirect::route('admin.surveycategories.index')->withErrors("You can not add more then " . config('zevolifesettings.zc_survey.survey_category_max_count') . " question category");
        }

        try {
            $data             = array();
            $data['ga_title'] = trans('page_title.surveycategories.create');
            $data['goalTags'] = Goal::pluck('title', 'id')->toArray();
            return \view('admin.surveycategories.create', $data);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('admin.surveycategories.index')->with('message', $messageData);
        }
    }

    /**
     * @param CreateSurveyCategoryRequest $request
     *
     * @return RedirectResponse
     */
    public function store(CreateSurveyCategoryRequest $request)
    {
        if (!access()->allow('create-survey-category')) {
            abort(403);
        }
        try {
            $data = $this->model->storeEntity($request->all());
            if ($data) {
                $messageData = [
                    'data'   => trans('labels.surveycategory.data_store_success'),
                    'status' => 1,
                ];
                return \Redirect::route('admin.surveycategories.index')->with('message', $messageData);
            } else {
                $messageData = [
                    'data'   => trans('labels.common_title.something_wrong_try_again'),
                    'status' => 0,
                ];
                return \Redirect::route('admin.surveycategories.create')->with('message', $messageData);
            }
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('admin.surveycategories.index')->with('message', $messageData);
        }
    }

    /**
     * @return View
     */
    public function edit(Request $request, SurveyCategory $surveycategory)
    {
        if (!access()->allow('update-survey-category')) {
            abort(403);
        }
        try {
            $data                 = array();
            $data['id']           = $surveycategory->id;
            $data['categoryData'] = $surveycategory;
            $data['goalTags']     = Goal::pluck('title', 'id')->toArray();
            $data['ga_title']     = trans('page_title.surveycategories.edit');
            $goal_tags            = array();
            if (!empty($surveycategory->surveyCategoryGoalTag)) {
                $goal_tags = $surveycategory->surveyCategoryGoalTag->pluck('id')->toArray();
            }
            $data['goal_tags'] = $goal_tags;
            return \view('admin.surveycategories.edit', $data);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('admin.surveycategories.index')->with('message', $messageData);
        }
    }

    /**
     * @param EditSurveyCategoryRequest $request
     *
     * @return RedirectResponse
     */
    public function update(EditSurveyCategoryRequest $request, SurveyCategory $surveycategory)
    {
        if (!access()->allow('update-survey-category')) {
            abort(403);
        }
        try {
            $data = $surveycategory->updateEntity($request->all());
            if ($data) {
                $messageData = [
                    'data'   => trans('labels.surveycategory.data_update_success'),
                    'status' => 1,
                ];
                return \Redirect::route('admin.surveycategories.index')->with('message', $messageData);
            } else {
                $messageData = [
                    'data'   => trans('labels.common_title.something_wrong_try_again'),
                    'status' => 0,
                ];
                return \Redirect::route('admin.surveycategories.edit', $surveycategory->id)->with('message', $messageData);
            }
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('admin.surveycategories.index')->with('message', $messageData);
        }
    }

    /**
     * @param  $id
     *
     * @return View
     */

    public function delete(SurveyCategory $surveycategory)
    {
        if (!access()->allow('delete-survey-category')) {
            abort(403);
        }
        try {
            return $surveycategory->deleteCategory();
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong'),
                'status' => 0,
            ];
            return \Redirect::route('admin.surveycategories.index')->with('message', $messageData);
        }
    }

    /**
     * @return View
     */
    public function createSub(SurveyCategory $surveycategory)
    {
        if (!access()->allow('create-survey-sub-category')) {
            abort(403);
        }

        $catCount = $surveycategory->subcategories()->count();

        if ($catCount >= config('zevolifesettings.zc_survey.survey_sub_category_max_count')) {
            return \Redirect::route('admin.surveysubcategories.index', $surveycategory->id)->withErrors("Max " . config('zevolifesettings.zc_survey.survey_sub_category_max_count') . " subcategories can be added under the master category.");
        }

        try {
            $categories = $this->model->get()->pluck('name', 'id')->toArray();

            $data = [
                'categories' => $categories,
            ];
            $data['ga_title'] = trans('page_title.surveysubcategories.create') . "(" . $surveycategory->display_name . ")";
            return \view('admin.surveycategories.surveysubcategories.create', $data);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong_try_again'),
                'status' => 0,
            ];
            return \Redirect::route('admin.surveysubcategories.index', $surveycategory->id)->with('message', $messageData);
        }
    }

    /**
     * @param CreateCategoryRequest $request
     * @return RedirectResponse
     */
    public function storeSub(CreateSurveySubCategoryRequest $request, SurveyCategory $surveycategory)
    {
        if (!access()->allow('create-survey-sub-category')) {
            abort(403);
        }
        try {
            $data = $this->surveysubCategory->storeEntity($request->all());
            if ($data) {
                $messageData = [
                    'data'   => 'Sub-category has been added successfully!',
                    'status' => 1,
                ];
                return \Redirect::route('admin.surveysubcategories.index', $data->category_id)->with('message', $messageData);
            } else {
                $messageData = [
                    'data'   => trans('labels.common_title.something_wrong_try_again'),
                    'status' => 0,
                ];
                return \Redirect::route('admin.surveysubcategories.create', $surveycategory->id)->with('message', $messageData);
            }
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong_try_again'),
                'status' => 0,
            ];
            return \Redirect::route('admin.surveysubcategories.index', $surveycategory->id)->with('message', $messageData);
        }
    }

    /**
     * @param Category $category
     * @return View
     */
    public function indexSub(SurveyCategory $surveycategory)
    {
        $role = getUserRole();
        if (!access()->allow('manage-survey-sub-category') || $role->group != 'zevo') {
            abort(403);
        }
        try {
            $data                         = array();
            $data['pagination']           = config('zevolifesettings.datatable.pagination.long');
            $data['isPrimum']             = array("all" => "All", "yes" => "Yes", "no" => "No");
            $data['surveySubCatCount']    = $surveycategory->subcategories()->count();
            $data['surveySubCatMaxCount'] = config('zevolifesettings.zc_survey.survey_sub_category_max_count');
            $data['ga_title']             = trans('page_title.surveysubcategories.surveysubcategories_list') . "(" . $surveycategory->display_name . ")";
            return \view('admin.surveycategories.surveysubcategories.index', $data);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong_try_again'),
                'status' => 0,
            ];
            return \Redirect::route('admin.surveycategories.index')->with('message', $messageData);
        }
    }

    /**
     *
     * @param Request $request
     * @return Datatable
     */
    public function getSubCategories(Request $request)
    {
        $role = getUserRole();
        if (!access()->allow('manage-survey-sub-category') || $role->group != 'zevo') {
            return response()->json([
                'message' => trans('labels.common_title.unauthorized_access'),
            ], 422);
        }
        try {
            return $this->surveysubCategory->getTableData($request->all());
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong_try_again'),
                'status' => 0,
            ];
            return response()->json($messageData);
        }
    }

    /**
     * @param Request $request, $id
     * @return View
     */
    public function editSub(Request $request, SurveyCategory $surveycategory, SurveySubCategory $surveysubcategory)
    {
        $role = getUserRole();
        if (!access()->allow('update-survey-sub-category') || $role->group != 'zevo') {
            abort(403);
        }
        try {
            $data                    = array();
            $data['id']              = $surveysubcategory->id;
            $data['categories']      = $this->model->get()->pluck('name', 'id')->toArray();
            $data['subCategoryData'] = $surveysubcategory;
            $data['categoryId']      = $surveysubcategory->category_id;
            $data['ga_title']        = trans('page_title.surveysubcategories.edit');
            return \view('admin.surveycategories.surveysubcategories.edit', $data);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong_try_again'),
                'status' => 0,
            ];
            return \Redirect::route('admin.surveysubcategories.edit', array($surveysubcategory->category_id, $surveysubcategory->id))->with('message', $messageData);
        }
    }

    /**
     * @param EditSubCategoryRequest $request
     * @return RedirectResponse
     */
    public function updateSub(EditSurveySubCategoryRequest $request, SurveyCategory $surveycategory, SurveySubCategory $surveysubcategory)
    {
        $role = getUserRole();
        if (!access()->allow('update-survey-sub-category') || $role->group != 'zevo') {
            abort(403);
        }
        try {
            $data = $surveysubcategory->updateEntity($request->all());
            if ($data) {
                $messageData = [
                    'data'   => 'Sub-category has been updated successfully!',
                    'status' => 1,
                ];
                return \Redirect::route('admin.surveysubcategories.index', $surveysubcategory->category_id)->with('message', $messageData);
            } else {
                $messageData = [
                    'data'   => trans('labels.common_title.something_wrong_try_again'),
                    'status' => 0,
                ];
                return \Redirect::route('admin.surveysubcategories.edit', array($surveysubcategory->category_id, $surveysubcategory->id))->with('message', $messageData);
            }
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong_try_again'),
                'status' => 0,
            ];
            return \Redirect::route('admin.surveysubcategories.edit', array($surveysubcategory->category_id, $surveysubcategory->id))->with('message', $messageData);
        }
    }

    /**
     * @param  $id
     *
     * @return View
     */
    public function deleteSub(SurveySubCategory $surveysubcategory)
    {
        $role = getUserRole();
        if (!access()->allow('delete-survey-sub-category') || $role->group != 'zevo') {
            abort(403);
        }
        try {
            return $surveysubcategory->deleteSub();
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('labels.common_title.something_wrong_try_again'),
                'status' => 0,
            ];
            return response()->json($messageData);
        }
    }
}
