<?php declare (strict_types = 1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CreateCategoryRequest;
use App\Http\Requests\Admin\CreateCategoryTagsRequest;
use App\Http\Requests\Admin\CreateSubCategoryRequest;
use App\Http\Requests\Admin\EditCategoryTagsRequest;
use App\Http\Requests\Admin\EditSubCategoryRequest;
use App\Models\Category;
use App\Models\CategoryTags;
use App\Models\SubCategory;
use Breadcrumbs;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Repositories\AuditLogRepository;

/**
 * Class CategoriesController
 *
 * @package App\Http\Controllers\Admin
 */
class CategoriesController extends Controller
{
    /**
     * variable to store the model object
     * @var Category
     */
    protected $model;

    /**
     * variable to store the model object
     * @var SubCategory
     */
    protected $subCategory;

    /**
     * variable to store the model object
     * @var CategoryTags
     */
    protected $categoryTags;

    /**
     * @var AuditLogRepository $auditLogRepository
     */
    private $auditLogRepository;

    /**
     * contructor to initialize model object
     * @param Category $model, SubCategory $subCategory
     */
    public function __construct(Category $model, SubCategory $subCategory, CategoryTags $categoryTags, AuditLogRepository $auditLogRepository)
    {
        $this->model                = $model;
        $this->subCategory          = $subCategory;
        $this->categoryTags         = $categoryTags;
        $this->auditLogRepository   = $auditLogRepository;
        $this->bindBreadcrumbs();
    }

    /**
     * bind breadcrumbs of categories module
     */
    private function bindBreadcrumbs()
    {
        Breadcrumbs::for('categories.index', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push(trans('categories.breadcrumbs.index'));
        });
        Breadcrumbs::for('subcategories.index', function ($trail, $category) {
            $trail->push('Home', route('dashboard'));
            $trail->push(trans('categories.breadcrumbs.index'), route('admin.categories.index'));
            $trail->push(trans('categories.subcategories.breadcrumbs.index'), route('admin.subcategories.index', $category->id));
        });
        Breadcrumbs::for('subcategories.create', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push(trans('categories.breadcrumbs.index'), route('admin.categories.index'));
            $trail->push(trans('categories.subcategories.breadcrumbs.create'));
        });
        Breadcrumbs::for('subcategories.edit', function ($trail, $categoryId) {
            $trail->push('Home', route('dashboard'));
            $trail->push(trans('categories.breadcrumbs.index'), route('admin.categories.index'));
            $trail->push(trans('categories.subcategories.breadcrumbs.index'), route('admin.subcategories.index', $categoryId));
            $trail->push(trans('categories.subcategories.breadcrumbs.edit'));
        });

        // category tags
        Breadcrumbs::for('categoryTags.index', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push('Category Tags');
        });
        Breadcrumbs::for('categoryTags.create', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push('Category Tags', route('admin.categoryTags.tag-index'));
            $trail->push('Add Category Tag');
        });
        Breadcrumbs::for('categoryTags.view', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push('Category Tags', route('admin.categoryTags.tag-index'));
            $trail->push('View Category Tags');
        });
    }

    /**
     * @return View
     */
    public function index(Request $request)
    {
        $role = getUserRole();
        if (!access()->allow('manage-category') || $role->group != 'zevo') {
            abort(403);
        }
        try {
            $data               = array();
            $data['pagination'] = config('zevolifesettings.datatable.pagination.short');
            $data['ga_title']   = trans('page_title.categories.categories_list');
            return \view('admin.categories.index', $data);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('categories.messages.something_wrong_try_again'),
                'status' => 0,
            ];
            return \Redirect::route('admin.categories.index')->with('message', $messageData);
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
        if (!access()->allow('manage-category') || $role->group != 'zevo') {
            abort(403);
        }
        try {
            return $this->model->getTableData($request->all());
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('categories.messages.something_wrong_try_again'),
                'status' => 0,
            ];
            return response()->json($messageData);
        }
    }

    /**
     * @return View
     */
    public function createSub()
    {
        $role = getUserRole();
        if (!access()->allow('create-sub-category') || $role->group != 'zevo') {
            abort(403);
        }
        try {
            $categories = $this->model->get()->pluck('name', 'id')->toArray();

            $data = [
                'categories' => $categories,
            ];
            $data['ga_title'] = trans('page_title.subcategories.create');
            return \view('admin.categories.subcategories.create', $data);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('categories.subcategories.messages.something_wrong_try_again'),
                'status' => 0,
            ];
            return \Redirect::route('admin.subcategories.create')->with('message', $messageData);
        }
    }

    /**
     * @param CreateCategoryRequest $request
     * @return RedirectResponse
     */
    public function storeSub(CreateSubCategoryRequest $request)
    {
        $user = auth()->user();
        $role = getUserRole();
        if (!access()->allow('create-sub-category') || $role->group != 'zevo') {
            abort(403);
        }
        try {
            $subcategories = $this->subCategory->where('category_id', $request->category)->get()->count();
            $userLogData = [
                'user_id'   => $user->id,
                'user_name' => $user->full_name,
            ];

            if ($request->category == 6) {
                if ($subcategories >= 20) {
                    $messageData = [
                        'data'   => trans('categories.subcategories.messages.limit_20'),
                        'status' => 2,
                    ];
                    return \Redirect::route('admin.subcategories.index', $request->category)->with('message', $messageData);
                }
            } elseif ($subcategories >= 10) {
                $messageData = [
                    'data'   => trans('categories.subcategories.messages.limit_10'),
                    'status' => 2,
                ];
                return \Redirect::route('admin.subcategories.index', $request->category)->with('message', $messageData);
            }

            $data    = $this->subCategory->storeEntity($request->all());
            $logData = array_merge($userLogData, $request->all());
           
            $this->auditLogRepository->created("Subcategory added successfully", $logData);

            if ($data) {
                $messageData = [
                    'data'   => trans('categories.subcategories.messages.added'),
                    'status' => 1,
                ];
                return \Redirect::route('admin.subcategories.index', $data->category_id)->with('message', $messageData);
            } else {
                $messageData = [
                    'data'   => trans('categories.subcategories.messages.something_wrong_try_again'),
                    'status' => 0,
                ];
                return \Redirect::route('admin.subcategories.create')->with('message', $messageData);
            }
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('categories.subcategories.messages.something_wrong_try_again'),
                'status' => 0,
            ];
            return \Redirect::route('admin.subcategories.create')->with('message', $messageData);
        }
    }

    /**
     * @param Category $category
     * @return View
     */
    public function indexSub(Category $category)
    {
        $role = getUserRole();
        if (!access()->allow('manage-sub-category') || $role->group != 'zevo') {
            abort(403);
        }
        try {
            $data               = array();
            $data['pagination'] = config('zevolifesettings.datatable.pagination.short');
            $data['ga_title']   = trans('page_title.subcategories.subcategories_list', ['subcategory_name' => $category->name]);
            return \view('admin.categories.subcategories.index', $data);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('categories.subcategories.messages.something_wrong_try_again'),
                'status' => 0,
            ];
            return \Redirect::route('admin.categories.index')->with('message', $messageData);
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
        if (!access()->allow('manage-sub-category') || $role->group != 'zevo') {
            abort(403);
        }
        try {
            return $this->subCategory->getTableData($request->all());
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
     * @param Request $request, $id
     * @return View
     */
    public function editSub(Request $request, SubCategory $category)
    {
        $role = getUserRole();
        if (!access()->allow('update-sub-category') || $role->group != 'zevo') {
            abort(403);
        }
        try {
            $data             = $category->getUpdateData();
            $data['ga_title'] = trans('page_title.subcategories.edit');
            return \view('admin.categories.subcategories.edit', $data);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('categories.subcategories.messages.something_wrong_try_again'),
                'status' => 0,
            ];
            return \Redirect::route('admin.subcategories.edit', $id)->with('message', $messageData);
        }
    }

    /**
     * @param EditSubCategoryRequest $request
     * @return RedirectResponse
     */
    public function updateSub(EditSubCategoryRequest $request, SubCategory $category)
    {
        $user = auth()->user();
        $role = getUserRole();
        if (!access()->allow('update-sub-category') || $role->group != 'zevo') {
            abort(403);
        }
        try {
            $userLogData = [
                'user_id'   => $user->id,
                'user_name' => $user->full_name,
            ];
            $oldCategoriesData = array_merge($userLogData, $category->toArray());
           
            $data = $category->updateEntity($request->all());

            $updatedCategoriesData  = array_merge($userLogData, $request->all());
            $finalLogs = ['olddata' => $oldCategoriesData, 'newdata' => $updatedCategoriesData];
            $this->auditLogRepository->created("Subcategory updated successfully", $finalLogs);

            if ($data) {
                $messageData = [
                    'data'   => trans('categories.subcategories.messages.updated'),
                    'status' => 1,
                ];
                return \Redirect::route('admin.subcategories.index', $category->category_id)->with('message', $messageData);
            } else {
                $messageData = [
                    'data'   => trans('categories.subcategories.messages.something_wrong_try_again'),
                    'status' => 0,
                ];
                return \Redirect::route('admin.subcategories.edit', $category->id)->with('message', $messageData);
            }
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('categories.subcategories.messages.something_wrong_try_again'),
                'status' => 0,
            ];
            return \Redirect::back()->with('message', $messageData);
        }
    }

    /**
     * @param  SubCategory $id
     * @return json
     */
    public function deleteSub(SubCategory $category)
    {
        $user = auth()->user();
        $role = getUserRole();
        if (!access()->allow('delete-sub-category') || $role->group != 'zevo') {
            abort(403);
        }
        try {
            $userLogData = [
                'user_id'   => $user->id,
                'user_name' => $user->full_name,
            ];
            $logs  = array_merge($userLogData, ['category' => $category->name]);
            $this->auditLogRepository->created("Subcategory deleted successfully", $logs);

            return $category->deleteSub();
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
     * Category tags listing
     *
     * @param Request $request
     * @return \view
     */
    public function tagIndex(Request $request)
    {
        if (!access()->allow('manage-category-tags')) {
            abort(403);
        }

        try {
            $data = [
                'pagination' => config('zevolifesettings.datatable.pagination.short'),
                'ga_title'   => trans('page_title.categories.manage_category_tags'),
            ];

            return \view('admin.categories.tags.index', $data);
        } catch (\Exception $exception) {
            report($exception);
            abort(500);
        }
    }

    /**
     * To get pre defined tags categories
     *
     * @param Request $request
     * @return Datatable
     */
    public function getTags(Request $request)
    {
        if (!access()->allow('manage-category-tags')) {
            return response()->json([
                'message' => trans('labels.common_title.unauthorized_access'),
            ], 403);
        }

        try {
            return $this->categoryTags->getTagsTableData($request->all());
        } catch (\Exception $exception) {
            report($exception);
            return response()->json([
                'data'   => trans('categories.messages.something_wrong_try_again'),
                'status' => 0,
            ], 500);
        }
    }

    /**
     * To create tags for respective category
     *
     * @return \view
     */
    public function createTags()
    {
        if (!access()->allow('add-category-tags')) {
            abort(403);
        }

        try {
            $categories = $this->model
                ->select('id', 'name')
                ->where('has_tags', true)
                ->get()->pluck('name', 'id')->toArray();

            $data = [
                'categories' => $categories,
                'ga_title'   => trans('page_title.tags.create'),
            ];

            return \view('admin.categories.tags.create', $data);
        } catch (\Exception $exception) {
            report($exception);
            abort(500);
        }
    }

    /**
     * Store tag for respective category
     *
     * @param CreateCategoryTagsRequest $request
     * @return RedirectResponse
     */
    public function storeTags(CreateCategoryTagsRequest $request)
    {
        $user = auth()->user();
        if (!access()->allow('add-category-tags')) {
            abort(403);
        }
        try {
            $totalTags = $this->categoryTags->where('category_id', $request->category)->get()->count();
            if ($totalTags >= 15) {
                return \Redirect::route('admin.categoryTags.tag-index')->with('message', [
                    'data'   => trans('categories.tags.messages.limit_15'),
                    'status' => 2,
                ]);
            }
            $userLogData = [
                'user_id'   => $user->id,
                'user_name' => $user->full_name,
            ];
            $data    = $this->categoryTags->storeEntity($request->all());
            $logData = array_merge($userLogData, $request->all());

            $this->auditLogRepository->created("Category tag added successfully", $logData);

            if ($data) {
                return \Redirect::route('admin.categoryTags.tag-index')->with('message', [
                    'data'   => trans('categories.tags.messages.added'),
                    'status' => 1,
                ]);
            } else {
                return \Redirect::route('admin.categoryTags.create')->with('message', [
                    'data'   => trans('categories.tags.messages.something_wrong_try_again'),
                    'status' => 0,
                ]);
            }
        } catch (\Exception $exception) {
            report($exception);
            return \Redirect::route('admin.categoryTags.create')->with('message', [
                'data'   => trans('categories.tags.messages.something_wrong_try_again'),
                'status' => 0,
            ]);
        }
    }

    /**
     * @param Category $category
     * @return View
     */
    public function viewCategoryTags(Category $category)
    {
        if (!access()->allow('view-category-tags')) {
            abort(403);
        }

        try {
            // Check is category isn't available for tags return 403
            if (!$category->has_tags) {
                return \view('errors.401');
            }

            $data = [
                'category'   => $category,
                'pagination' => config('zevolifesettings.datatable.pagination.short'),
                'ga_title'   => trans('page_title.tags.view', ['category' => $category->name]),
            ];

            return \view('admin.categories.tags.view-tags', $data);
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('categories.subcategories.messages.something_wrong_try_again'),
                'status' => 0,
            ];
            return \Redirect::route('admin.categories.index')->with('message', $messageData);
        }
    }

    /**
     *
     * @param Request $request
     * @return Datatable
     */
    public function getCategoryTags(Category $category, Request $request)
    {
        if (!access()->allow('manage-sub-category')) {
            abort(403);
        }

        try {
            return $this->categoryTags->getTableData($category, $request->all());
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
     * To edit category tag
     *
     * @param CategoryTags $tag
     * @param Request $request
     * @return View
     */
    public function editTag(CategoryTags $tag, Request $request)
    {
        if (!access()->allow('update-sub-category')) {
            abort(403);
        }

        try {
            $data = [
                'tag'        => $tag,
                'categories' => $tag->category()->select('id', 'name')->pluck('name', 'id')->toArray(),
                'ga_title'   => trans('page_title.tags.edit'),
            ];

            return \view('admin.categories.tags.edit', $data);
        } catch (\Exception $exception) {
            report($exception);
            abort(500);
        }
    }

    /**
     * To edit category tag
     *
     * @param EditCategoryTagsRequest $request
     * @return RedirectResponse
     */
    public function updateTag(CategoryTags $tag, EditCategoryTagsRequest $request)
    {
        $user = auth()->user();
        if (!access()->allow('update-sub-category')) {
            abort(403);
        }

        try {
            $userLogData = [
                'user_id'   => $user->id,
                'user_name' => $user->full_name,
            ];
            $oldTagsData     = array_merge($userLogData, $tag->toArray());
            $data            = $tag->updateEntity($request->all());
            $updatedTagsData = array_merge($userLogData, $request->all());
            $finalLogs       = ['olddata' => $oldTagsData, 'newdata' => $updatedTagsData];
            $this->auditLogRepository->created("Category tag updated successfully", $finalLogs);

            if ($data) {
                return \Redirect::route('admin.categoryTags.view', $tag->category_id)->with('message', [
                    'data'   => trans('categories.tags.messages.updated'),
                    'status' => 1,
                ]);
            } else {
                return \Redirect::route('admin.categoryTags.edit', $tag->id)->with('message', [
                    'data'   => trans('categories.tags.messages.something_wrong_try_again'),
                    'status' => 0,
                ]);
            }
        } catch (\Exception $exception) {
            report($exception);
            return \Redirect::back()->with('message', [
                'data'   => trans('categories.tags.messages.something_wrong_try_again'),
                'status' => 0,
            ]);
        }
    }

    /**
     * Delete category tab
     *
     * @param CategoryTags $tag
     * @return json
     */
    public function deleteTag(CategoryTags $tag)
    {
        $user = auth()->user();
        if (!access()->allow('delete-category-tags')) {
            return response()->json([
                'data'   => trans('categories.tags.messages.unauthorized'),
                'status' => 0,
            ], 403);
        }

        try {
            $userLogData = [
                'user_id'   => $user->id,
                'user_name' => $user->full_name,
            ];
            $logs  = array_merge($userLogData, ['categoryTag' => $tag->name]);
            $this->auditLogRepository->created("Category tag deleted successfully", $logs);

            return $tag->deleteEntity();
        } catch (\Exception $exception) {
            report($exception);
            return response()->json([
                'data'   => trans('categories.tags.messages.something_wrong_try_again'),
                'status' => 0,
            ], 500);
        }
    }
}
