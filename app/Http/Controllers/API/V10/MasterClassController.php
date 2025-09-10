<?php declare (strict_types = 1);

namespace App\Http\Controllers\API\V10;

use App\Http\Collections\V10\CategoryWiseMasterClassCollection as v10CategoryWiseMasterClassCollection;
use App\Http\Controllers\API\V9\MasterClassController as v9MasterClassController;
use App\Http\Resources\V6\MasterClassDetailsResource;
use App\Models\Course;
use App\Models\MasterClassComapany;
use App\Models\SubCategory;
use Illuminate\Http\Request;

class MasterClassController extends v9MasterClassController
{
    /**
     * API to fetch masterclasses by category
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function categoryMasterClass(Request $request, SubCategory $subcategory)
    {
        try {
            $user    = $this->user();
            $company = $user->company()->first();

            $categoryWiseCourseData = Course::where("sub_category_id", $subcategory->id)
                ->join('masterclass_company', function ($join) use ($company) {
                    $join->on('masterclass_company.masterclass_id', '=', 'courses.id')
                        ->where('masterclass_company.company_id', $company->id);
                })
                ->select("courses.id", "courses.title", "courses.instructions", "courses.creator_id")
                ->where("courses.status", true)
                ->orderBy('courses.created_at', 'DESC');

            $categoryWiseCourseData = $categoryWiseCourseData->groupBy('courses.id')->paginate(config('zevolifesettings.datatable.pagination.short'));

            if ($categoryWiseCourseData->count() > 0) {
                return $this->successResponse(new v10CategoryWiseMasterClassCollection($categoryWiseCourseData), 'Master class retrieved successfully.');
            } else {
                return $this->successResponse(['data' => []], 'No results');
            }
        } catch (\Exception $e) {
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }
}
