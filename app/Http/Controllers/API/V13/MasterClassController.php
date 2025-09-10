<?php declare (strict_types = 1);

namespace App\Http\Controllers\API\V13;

use App\Http\Collections\V11\CategoryWiseMasterClassCollection;
use App\Http\Controllers\API\V11\MasterClassController as v11MasterClassController;
use App\Models\Course;
use App\Models\SubCategory;
use DB;
use Illuminate\Http\Request;

class MasterClassController extends v11MasterClassController
{
    /**
     * API to fetch masterclasses by category
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function categoryMasterClass(Request $request, $subcategory)
    {
        try {
            if ($subcategory > 0) {
                $subcategoryRecords = SubCategory::find($subcategory);

                if (empty($subcategoryRecords)) {
                    return $this->notFoundResponse("Sorry! Requested data not found");
                }
            }

            $user      = $this->user();
            $company   = $user->company()->first();
            $xDeviceOs = strtolower($request->header('X-Device-Os', ""));

            $categoryWiseCourseData = Course::join('masterclass_company', function ($join) use ($company) {
                $join->on('masterclass_company.masterclass_id', '=', 'courses.id')
                    ->where('masterclass_company.company_id', $company->id);
            });

            if ($subcategory > 0) {
                $categoryWiseCourseData->where("sub_category_id", $subcategoryRecords->id);
            }

            if ($xDeviceOs == config('zevolifesettings.PORTAL')) {
                $categoryWiseCourseData->select("courses.id", "courses.title", "courses.instructions", "courses.creator_id", DB::raw("(SELECT id FROM user_course WHERE course_id = courses.id and user_id = " . $user->id . " and joined = 1 limit 1) as isEnrolled"))
                    ->where("courses.status", true);
            } else {
                $categoryWiseCourseData->select("courses.id", "courses.title", "courses.instructions", "courses.creator_id");
            }

            $categoryWiseCourseData->addSelect(DB::raw("(SELECT count(id) FROM user_course WHERE course_id = courses.id) AS totalEnrolled"));
            $categoryWiseCourseData->where("courses.status", true);
            if ($subcategory > 0) {
                $categoryWiseCourseData->orderBy('courses.created_at', 'DESC');
            } else {
                $categoryWiseCourseData->orderByRaw("`totalEnrolled` DESC, `courses`.`created_at` DESC");
            }
            $categoryWiseCourseData = $categoryWiseCourseData->groupBy('courses.id')->paginate(config('zevolifesettings.datatable.pagination.short'));

            if ($categoryWiseCourseData->count() > 0) {
                return $this->successResponse(new CategoryWiseMasterClassCollection($categoryWiseCourseData), 'Master class retrieved successfully.');
            } else {
                return $this->successResponse(['data' => []], 'No results');
            }
        } catch (\Exception $e) {
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }
}
