<?php declare (strict_types = 1);

namespace App\Http\Controllers\API\V16;

use App\Http\Collections\V7\CategoryWiseMasterClassCollection;
use App\Http\Controllers\API\V14\MasterClassController as v14MasterClassController;
use App\Models\Course;
use App\Models\User;
use Illuminate\Http\Request;

class MasterClassController extends v14MasterClassController
{
    /**
     * API to fetch enrolled masterclass of logged in user
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function enrolledMasterClass(Request $request)
    {
        try {
            // logged-in user
            $user    = $this->user();
            $company = $user->company()->first();
            // get paginated course data by category
            $categoryWiseCourseData = Course::leftJoin('user_course', function ($join) use ($user) {
                $join->on('courses.id', '=', 'user_course.course_id')
                    ->where('user_course.user_id', '=', $user->getKey());
            })->join('masterclass_company', function ($join) use ($company) {
                $join->on('masterclass_company.masterclass_id', '=', 'courses.id')
                    ->where('masterclass_company.company_id', $company->id);
            })
                ->select("courses.id", "courses.title", "courses.creator_id")
                ->where("courses.status", true)
                ->where("user_course.completed", false)
                ->orderBy('courses.created_at', 'DESC');

            $categoryWiseCourseData = $categoryWiseCourseData->groupBy('courses.id')->paginate(config('zevolifesettings.datatable.pagination.short'));

            $totalEnrolledMasterclasses = $user->courseLogs()->wherePivot('joined', true)->count();

            $return                               = [];
            $return['totalEnrolledMasterclasses'] = $totalEnrolledMasterclasses;
            $return['data']                       = [];
            if ($categoryWiseCourseData->count() > 0) {
                $return = new CategoryWiseMasterClassCollection($categoryWiseCourseData, $totalEnrolledMasterclasses);
            }

            // return response
            return $this->successResponse(
                $return,
                ($categoryWiseCourseData->count() > 0) ? 'Master class retrieved successfully.' : "No results"
            );
        } catch (\Exception $e) {
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * API to fetch completed masterclass of logged in user
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function completedMasterClass(Request $request)
    {
        try {
            // logged-in user
            $user    = $this->user();
            $company = $user->company()->first();
            // get paginated course data by category
            $categoryWiseCourseData = Course::leftJoin('user_course', function ($join) use ($user) {
                    $join->on('courses.id', '=', 'user_course.course_id')
                        ->where('user_course.user_id', '=', $user->getKey());
            })->join('masterclass_company', function ($join) use ($company) {
                $join->on('masterclass_company.masterclass_id', '=', 'courses.id')
                    ->where('masterclass_company.company_id', $company->id);
            })
                ->select("courses.id", "courses.title", "courses.creator_id")
                ->where("courses.status", true)
                ->where("user_course.completed", true)
                ->orderBy('courses.created_at', 'DESC');

            $categoryWiseCourseData = $categoryWiseCourseData
                ->groupBy('courses.id')
                ->paginate(config('zevolifesettings.datatable.pagination.short'));

            $totalEnrolledMasterclasses = $user->courseLogs()->wherePivot('joined', true)->count();

            $return                               = [];
            $return['totalEnrolledMasterclasses'] = $totalEnrolledMasterclasses;
            $return['data']                       = [];

            if ($categoryWiseCourseData->count() > 0) {
                $return = new CategoryWiseMasterClassCollection($categoryWiseCourseData, $totalEnrolledMasterclasses);
            }

            // return response
            return $this->successResponse(
                $return,
                ($categoryWiseCourseData->count() > 0) ? 'Master class retrieved successfully.' : "No results"
            );
        } catch (\Exception $e) {
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }
}
