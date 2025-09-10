<?php declare (strict_types = 1);

namespace App\Http\Controllers\API\V9;

use App\Http\Collections\V6\CategoryWiseMasterClassCollection as v6CategoryWiseMasterClassCollection;
use App\Http\Controllers\API\V7\MasterClassController as v7MasterClassController;
use App\Http\Resources\V6\MasterClassDetailsResource;
use App\Models\Course;
use App\Models\MasterClassComapany;
use App\Models\SubCategory;
use Illuminate\Http\Request;

class MasterClassController extends v7MasterClassController
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
                ->select("courses.id", "courses.title", "courses.creator_id")
                ->where("courses.status", true)
                ->orderBy('courses.created_at', 'DESC');

            $categoryWiseCourseData = $categoryWiseCourseData->groupBy('courses.id')->paginate(config('zevolifesettings.datatable.pagination.short'));

            if ($categoryWiseCourseData->count() > 0) {
                return $this->successResponse(new v6CategoryWiseMasterClassCollection($categoryWiseCourseData), 'Master class retrieved successfully.');
            } else {
                return $this->successResponse(['data' => []], 'No results');
            }
        } catch (\Exception $e) {
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * API to save get details of masterclass
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function masterClassDetail(Request $request, Course $course)
    {
        try {
            if (!$course->status) {
                return $this->notFoundResponse('Masterclass is not published yet.');
            }

            $company         = $this->user()->company()->first();
            $isComapanyExist = MasterClassComapany::where(['masterclass_id' => $course->id, 'company_id' => $company->id])->count();
            if (empty($isComapanyExist)) {
                return $this->notFoundResponse("Masterclass isn't published for your company or has been removed.");
            }

            $data = array("data" => new MasterClassDetailsResource($course));
            return $this->successResponse($data, 'Course Info retrieved successfully');
        } catch (\Exception $e) {
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * API to fetch saved masterclasses
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function savedMasterClass(Request $request)
    {
        try {
            $user    = $this->user();
            $company = $user->company()->first();

            $categoryWiseCourseData = Course::select("courses.id", "courses.title", "courses.creator_id")
                ->leftJoin('user_course', function ($join) use ($user) {
                    $join->on('courses.id', '=', 'user_course.course_id')
                        ->where('user_course.user_id', '=', $user->getKey());
                })
                ->join('masterclass_company', function ($join) use ($company) {
                    $join->on('masterclass_company.masterclass_id', '=', 'courses.id')
                        ->where('masterclass_company.company_id', $company->id);
                })
                ->where("courses.status", true)
                ->where("user_course.saved", true)
                ->orderBy('courses.created_at', 'DESC');

            $categoryWiseCourseData = $categoryWiseCourseData->groupBy('courses.id')->paginate(config('zevolifesettings.datatable.pagination.short'));

            if ($categoryWiseCourseData->count() > 0) {
                return $this->successResponse(new v6CategoryWiseMasterClassCollection($categoryWiseCourseData), 'Master class retrieved successfully.');
            } else {
                return $this->successResponse(['data' => []], 'No results');
            }
        } catch (\Exception $e) {
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }
}
