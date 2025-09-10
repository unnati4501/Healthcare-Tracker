<?php declare (strict_types = 1);

namespace App\Http\Controllers\API\V30;

use App\Http\Collections\V30\RecentMasterClassCollection;
use App\Http\Controllers\API\V21\MasterClassController as v21MasterClassController;
use App\Models\Course;
use DB;
use Illuminate\Http\Request;

class MasterClassController extends v21MasterClassController
{
    /**
     * Get recent masterclass
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function recentMasterClasses(Request $request)
    {
        try {
            $user                      = $this->user();
            $company                   = $user->company()->first();
            $timezone                  = $user->timezone ?? config('app.timezone');
            $data['recentMasterClass'] = $this->getRecentMasertClassList();
            $data['mostEnrolled']      = $this->getMostEnrolledMasterClassList();
            $data['mostLiked']         = Course::select(
                'courses.*',
                "sub_categories.name as courseSubCategory",
                DB::raw('IFNULL(sum(user_course.liked),0) AS most_liked')
            )
                ->join('masterclass_company', function ($join) use ($company) {
                    $join->on('courses.id', '=', 'masterclass_company.masterclass_id')
                        ->where('masterclass_company.company_id', '=', $company->getKey());
                })
                ->leftJoin('user_course', 'user_course.course_id', '=', 'courses.id')
                ->join('sub_categories', function ($join) {
                    $join->on('sub_categories.id', '=', 'courses.sub_category_id');
                })
                ->where("courses.status", true)
                ->orderBy('most_liked', 'DESC')
                ->groupBy('courses.id')
                ->having('most_liked', '>', '0')
                ->limit(10)
                ->get();
            //->shuffle();
            // Collect required data and return response
            return $this->successResponse(new RecentMasterClassCollection($data), 'recent masterclass listed successfully');
        } catch (\Exception $e) {
            \DB::rollback();
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * Get recent masterclass [Most Listened, Most Watched, Latest Articles]
     *
     * @return \Illuminate\Http\JsonResponse
     */
    private function getRecentMasertClassList($type = "")
    {
        try {
            $user     = $this->user();
            $company  = $user->company()->first();
            $timezone = $user->timezone ?? config('app.timezone');

            $records = Course::select(
                'courses.*',
                "sub_categories.name as courseSubCategory",
                //DB::raw('IFNULL(sum(user_course.view_count),0) AS view_count'),
                DB::raw('IFNULL(sum(user_course.liked),0) AS most_liked')
            )
                ->join('masterclass_company', function ($join) use ($company) {
                    $join->on('courses.id', '=', 'masterclass_company.masterclass_id')
                        ->where('masterclass_company.company_id', '=', $company->getKey());
                })
                ->leftJoin('user_course', 'user_course.course_id', '=', 'courses.id')
                ->join('sub_categories', function ($join) {
                    $join->on('sub_categories.id', '=', 'courses.sub_category_id');
                })
                ->where("courses.status", true);

            $records->orderBy('courses.updated_at', 'DESC');
            $records = $records->groupBy('courses.id')
                ->limit(10)
                ->get()
                ->shuffle();

            return $records;
        } catch (\Exception $e) {
            \DB::rollback();
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * Get enrolled masterclass
     *
     * @return \Illuminate\Http\JsonResponse
     */
    private function getMostEnrolledMasterClassList($type = "")
    {
        try {
            $user     = $this->user();
            $company  = $user->company()->first();
            $timezone = $user->timezone ?? config('app.timezone');

            $records = Course::select(
                'courses.*',
                "sub_categories.name as courseSubCategory",
                //DB::raw('IFNULL(sum(user_course.view_count),0) AS view_count'),
                DB::raw('IFNULL(sum(user_course.liked),0) AS most_liked')
            )
                ->join('masterclass_company', function ($join) use ($company) {
                    $join->on('courses.id', '=', 'masterclass_company.masterclass_id')
                        ->where('masterclass_company.company_id', '=', $company->getKey());
                })
                ->leftJoin('user_course', 'user_course.course_id', '=', 'courses.id')
                ->join('sub_categories', function ($join) {
                    $join->on('sub_categories.id', '=', 'courses.sub_category_id');
                })
                ->where("courses.status", true)
                ->where("user_course.joined", true)
                ->where("user_course.completed", false);

            $records->orderBy('courses.created_at', 'DESC');
            $records = $records->groupBy('courses.id')
                ->limit(10)
                ->get()
                ->shuffle();

            return $records;
        } catch (\Exception $e) {
            \DB::rollback();
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }
}
