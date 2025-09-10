<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Company;
use App\Models\SubCategory;
use App\Models\User;
use Breadcrumbs;
use Illuminate\Http\Request;

class ContentReportController extends Controller
{

    /**
     * variable to store the model object
     * @var SubCategory $subCategory
     */
    protected $subCategory;

    /**
     * constructor to initialize variables
     */
    public function __construct(SubCategory $subCategory)
    {
        $this->bindBreadcrumbs();
        $this->subCategory = $subCategory;
    }

    /*
     * Bind breadcrumbs of role module
     */
    public function bindBreadcrumbs()
    {
        Breadcrumbs::for('contentreport.index', function ($trail) {
            $trail->push('Home', route('dashboard'));
            $trail->push('Content Report');
        });
    }

    /**
     * @param Request $request
     * @return View
     * @throws Exception
     */
    public function index(Request $request)
    {
        $user                           = auth()->user();
        $timezone                       = (!empty($user->timezone) ? $user->timezone : config('app.timezone'));
        $role                           = getUserRole($user);
        $checkPlanAccessForReseller     = getDTAccessForParentsChildCompany($user, 'explore');
        if (!access()->allow('content-report') || ($role->group == 'reseller' &&  !$checkPlanAccessForReseller)) {
            abort(403);
        }

        try {
            $subcategoryData = [];
            $contentType     = config('zevolifesettings.company_content_content_report');
            $loginemail      = ($user->email ?? "");

            // check if zevo then show all reseller parent and child companies
            if ($role->group == 'zevo') {
                $companies = Company::pluck('name', 'id')
                    ->toArray();
            } else {
                $company   = $user->company()->first();
                $companies = Company::where(function ($query) use ($company) {
                        $query->where('id', $company->id)->orWhere('parent_id', $company->id);
                })
                    ->pluck('name', 'id')
                    ->toArray();
            }

            $type = request()->get('type');
            if (!empty($type)) {
                $subcategoryData = SubCategory::where('category_id', $type)->get()->pluck('name', 'id')->toArray();
            }

            $data = [
                'timezone'        => $timezone,
                'companies'       => $companies,
                'contentType'     => $contentType,
                'subcategoryData' => $subcategoryData,
                'loginemail'      => $loginemail,
                'pagination'      => config('zevolifesettings.datatable.pagination.long'),
                'ga_title'        => trans('page_title.reports.content-report'),
            ];

            return \view('admin.report.contentreport.index', $data);
        } catch (\Exception $exception) {
            report($exception);
            abort(500);
        }
    }

    /**
     * @param Category $category
     * @return Array
     * @throws Exception
     */
    public function getCategoryList(Category $category)
    {
        try {
            $subCategoryArray = $category->subcategories()->get()->pluck('name', 'id')->toArray();

            if (!empty($subCategoryArray) && $subCategoryArray) {
                $data = [
                    'status' => 1,
                    'data'   => $subCategoryArray,
                ];
            } else {
                $data = [
                    'status' => 0,
                    'data'   => [],
                ];
            }
            return $data;
        } catch (\Exception $exception) {
            report($exception);
            abort(500);
        }
    }

    /**
     * @param Request $request
     * @return Array
     * @throws Exception
     */
    public function getContentReport(Request $request)
    {
        $role = getUserRole();
        if (!access()->allow('content-report') || $role->group == 'company') {
            $messageData = [
                'data'   => trans('contentreport.message.unauthorized_access'),
                'status' => 0,
            ];
            return response()->json($messageData, 401);
        }
        try {
            return $this->subCategory->getContentReport($request->all());
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('contentreport.message.something_wrong_try_again'),
                'status' => 0,
            ];
            return response()->json($messageData, 500);
        }
    }

    /**
     * @param Request $request
     * @return Array
     * @throws Exception
     */
    public function exportContentReport(Request $request)
    {
        $role = getUserRole();
        if (!access()->allow('content-report') || $role->group == 'company') {
            $messageData = [
                'data'   => trans('contentreport.message.unauthorized_access'),
                'status' => 0,
            ];
            return response()->json($messageData, 401);
        }
        try {
            \DB::beginTransaction();
            $data = $this->subCategory->exportContentReport($request->all());
            if ($data) {
                \DB::commit();
                $messageData = [
                    'data'   => trans('contentreport.message.report_generate_in_background'),
                    'status' => 1,
                ];
            } else {
                \DB::rollback();
                $messageData = [
                    'data'   => trans('contentreport.message.no_records_found'),
                    'status' => 0,
                ];
            }
            return $messageData;
        } catch (\Exception $exception) {
            report($exception);
            $messageData = [
                'data'   => trans('contentreport.message.something_wrong_try_again'),
                'status' => 0,
            ];
            return response()->json($messageData, 500);
        }
    }
}
