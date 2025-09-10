<?php declare (strict_types = 1);

namespace App\Http\Controllers\API\V43;

use App\Http\Controllers\API\V40\MasterClassController as v40MasterClassController;
use App\Http\Collections\V40\MasterClassLessonCollection;
use App\Models\Course;
use App\Models\CourseLession;
use App\Models\SubCategory;
use DB;
use Illuminate\Http\Request;
use App\Traits\PaginationTrait;

class MasterClassController extends v40MasterClassController
{
    use PaginationTrait;

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
            $team      = $user->teams()->first();
            $xDeviceOs = strtolower($request->header('X-Device-Os', ""));

            $categoryWiseCourseData = Course::join('masterclass_team', function ($join) use ($team) {
                $join->on('masterclass_team.masterclass_id', '=', 'courses.id')
                    ->where('masterclass_team.team_id', $team->id);
            });

            if ($subcategory > 0) {
                $categoryWiseCourseData->where("sub_category_id", $subcategoryRecords->id);
            } elseif ($subcategory == 0) {
                $categoryWiseCourseData->join('user_course', 'courses.id', '=', 'user_course.course_id')
                    ->join('sub_categories', 'sub_categories.id', '=', 'courses.sub_category_id')
                    ->where("user_course.user_id", $user->id)
                    ->where(["favourited" => 1, "sub_categories.status" => 1]);
                    //->orderByRaw("`courses`.`updated_at` DESC");
            }

            if ($xDeviceOs == config('zevolifesettings.PORTAL')) {
                $categoryWiseCourseData->select("courses.id", "courses.title", "courses.instructions", "courses.creator_id" 
                )
                ->selectRaw("(SELECT id FROM user_course WHERE course_id = courses.id and user_id = ? and joined = 1 limit 1) as isEnrolled",[
                    $user->id
                ])
                    ->where("courses.status", true);
            } else {
                $categoryWiseCourseData->select("courses.id", "courses.title", "courses.instructions", "courses.creator_id", "courses.caption");
            }
            $categoryWiseCourseData->addSelect(DB::raw("CASE
                WHEN courses.caption = 'New' then 0
                WHEN courses.caption = 'Popular' then 1
                ELSE 2
                END AS caption_order"));
            $categoryWiseCourseData->addSelect(DB::raw("(SELECT count(id) FROM user_course WHERE course_id = courses.id) AS totalEnrolled"));
            $categoryWiseCourseData->where("courses.status", true);
            if ($subcategory > 0) {
                $categoryWiseCourseData->orderBy('caption_order', 'ASC')->orderBy('courses.created_at', 'DESC');
            } else {
                $categoryWiseCourseData->orderBy('caption_order', 'ASC')->orderByRaw("`totalEnrolled` DESC, `courses`.`created_at` DESC");
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