<?php declare (strict_types = 1);

namespace App\Http\Controllers\API\V38;

use App\Http\Controllers\API\V37\MasterClassController as v37MasterClassController;
use App\Http\Collections\V38\RecentMasterClassCollection;
use App\Http\Collections\V38\CategoryWiseMasterClassCollection;
use App\Http\Collections\V38\MasterClassLessonCollection;
use App\Models\Course;
use App\Models\CourseLession;
use App\Models\SubCategory;
use DB;
use Illuminate\Http\Request;
use App\Traits\PaginationTrait;

class MasterClassController extends v37MasterClassController
{
    use PaginationTrait;
    /**
     * Get recent masterclass
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function recentMasterClasses(Request $request)
    {
        try {
            $user       = $this->user();
            $team       = $user->teams()->first();
            $data       = Course::select(
                'courses.*',
                "sub_categories.name as courseSubCategory",
                DB::raw('IFNULL(sum(user_course.liked),0) AS most_liked')
            )
                ->join('masterclass_team', function ($join) use ($team) {
                    $join->on('courses.id', '=', 'masterclass_team.masterclass_id')
                        ->where('masterclass_team.team_id', '=', $team->getKey());
                })
                ->leftJoin('user_course', 'user_course.course_id', '=', 'courses.id')
                ->join('sub_categories', function ($join) {
                    $join->on('sub_categories.id', '=', 'courses.sub_category_id');
                });
                $data->addSelect(DB::raw("CASE
                    WHEN courses.caption = 'New' then 0
                    WHEN courses.caption = 'Popular' then 1
                    ELSE 2
                    END AS caption_order"
                ));
                $data = $data->where("courses.status", true)
                ->orderBy('caption_order', 'ASC')
                ->orderBy('courses.updated_at', 'DESC')
                ->orderBy('courses.id', 'DESC')
                ->groupBy('courses.id')
                ->paginate(config('zevolifesettings.datatable.pagination.short'));

            // Collect required data and return response
            if ($data->count() > 0) {
                return $this->successResponse(new RecentMasterClassCollection($data, true), 'Recent masterclass retrived successfully');
            } else {
                return $this->successResponse(['data' => []], 'No results');
            }
        } catch (\Exception $e) {
            \DB::rollback();
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

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
                $categoryWiseCourseData->select("courses.id", "courses.title", "courses.instructions", "courses.creator_id", DB::raw("(SELECT id FROM user_course WHERE course_id = courses.id and user_id = " . $user->id . " and joined = 1 limit 1) as isEnrolled"))
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

    /**
     * API to fetch saved masterclasses
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function savedMasterClass(Request $request)
    {
        try {
            $user    = $this->user();
            $team    = $user->teams()->first();

            $categoryWiseCourseData = Course::select("courses.id", "courses.title", "courses.creator_id", "courses.caption")
                ->leftJoin('user_course', function ($join) use ($user) {
                    $join->on('courses.id', '=', 'user_course.course_id')
                        ->where('user_course.user_id', '=', $user->getKey());
                })
                ->join('masterclass_team', function ($join) use ($team) {
                    $join->on('masterclass_team.masterclass_id', '=', 'courses.id')
                        ->where('masterclass_team.team_id', $team->id);
                });
            $categoryWiseCourseData->addSelect(DB::raw("CASE
                WHEN courses.caption = 'New' then 0
                WHEN courses.caption = 'Popular' then 1
                ELSE 2
                END AS caption_order"));
                
            $categoryWiseCourseData->where("courses.status", true)
                ->where("user_course.saved", true)
                ->orderBy('caption_order', 'ASC')
                ->orderBy('user_course.saved_at', 'DESC')
                ->orderBy('courses.created_at', 'DESC');

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
            $team    = $user->teams()->first();
            // get paginated course data by category
            $categoryWiseCourseData = Course::leftJoin('user_course', function ($join) use ($user) {
                $join->on('courses.id', '=', 'user_course.course_id')
                    ->where('user_course.user_id', '=', $user->getKey());
            })->join('masterclass_team', function ($join) use ($team) {
                $join->on('masterclass_team.masterclass_id', '=', 'courses.id')
                    ->where('masterclass_team.team_id', $team->id);
            })
            ->select("courses.id", "courses.title", "courses.creator_id", "courses.caption");
            $categoryWiseCourseData->addSelect(DB::raw("CASE
                WHEN courses.caption = 'New' then 0
                WHEN courses.caption = 'Popular' then 1
                ELSE 2
                END AS caption_order"));
            $categoryWiseCourseData = $categoryWiseCourseData->where("courses.status", true)
                ->where("user_course.joined", true)
                ->where("user_course.completed", false)
                ->orderBy('caption_order', 'ASC')
                ->orderBy('courses.created_at', 'DESC');

            $categoryWiseCourseData = $categoryWiseCourseData->groupBy('courses.id')->paginate(config('zevolifesettings.datatable.pagination.short'));

            $totalEnrolledMasterclasses = Course::join('user_course', function ($join) use ($user) {
                $join->on('courses.id', '=', 'user_course.course_id')
                    ->where('user_course.user_id', '=', $user->getKey());
            })->join('masterclass_team', function ($join) use ($team) {
                $join->on('masterclass_team.masterclass_id', '=', 'courses.id')
                    ->where('masterclass_team.team_id', $team->id);
            })->select(DB::raw('COUNT(DISTINCT courses.id) as counts'))
                ->where('user_course.joined', true)
                ->first()
                ->toArray();

            $return                               = [];
            $return['totalEnrolledMasterclasses'] = (int) $totalEnrolledMasterclasses['counts'];
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
            $team    = $user->teams()->first();
            // get paginated course data by category
            $categoryWiseCourseData = Course::leftJoin('user_course', function ($join) use ($user) {
                $join->on('courses.id', '=', 'user_course.course_id')
                    ->where('user_course.user_id', '=', $user->getKey());
            })->join('masterclass_team', function ($join) use ($team) {
                $join->on('masterclass_team.masterclass_id', '=', 'courses.id')
                    ->where('masterclass_team.team_id', $team->id);
            })
            ->select("courses.id", "courses.title", "courses.creator_id", "courses.caption");
            $categoryWiseCourseData->addSelect(DB::raw("CASE
                WHEN courses.caption = 'New' then 0
                WHEN courses.caption = 'Popular' then 1
                ELSE 2
                END AS caption_order"));
            $categoryWiseCourseData = $categoryWiseCourseData->where("courses.status", true)
                ->where("user_course.completed", true)
                ->orderBy('caption_order', 'ASC')
                ->orderBy('courses.created_at', 'DESC');

            $categoryWiseCourseData = $categoryWiseCourseData->groupBy('courses.id')->paginate(config('zevolifesettings.datatable.pagination.short'));

            $totalEnrolledMasterclasses = Course::join('user_course', function ($join) use ($user) {
                $join->on('courses.id', '=', 'user_course.course_id')
                    ->where('user_course.user_id', '=', $user->getKey());
            })->join('masterclass_team', function ($join) use ($team) {
                $join->on('masterclass_team.masterclass_id', '=', 'courses.id')
                    ->where('masterclass_team.team_id', $team->id);
            })->select(DB::raw('COUNT(DISTINCT courses.id) as counts'))
                ->where('user_course.joined', true)
                ->first()
                ->toArray();

            $return                               = [];
            $return['totalEnrolledMasterclasses'] = (int) $totalEnrolledMasterclasses['counts'];
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
     * API to get lessions
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getLessonsList(Request $request, Course $course)
    {
        try {
            $user               = $this->user();
            $totalDuration      = $course->courseTotalDurarion();
            $surrentUserLession = $user->courseLessonLogs()
                ->wherePivot('course_id', $course->id)
                ->wherePivot('user_id', $user->id)
                ->wherePivot('status', "started")
                ->orderBy("user_lession.id", "DESC")
                ->first();
            $userMasterClassData = $course->courseUserLogs()->wherePivot("user_id", $user->id)->first();

            $data = array();

            $data['info']['id']              = $course->id;
            $data['info']['title']           = $course->title;
            $data['info']['duration']        = (!empty($totalDuration) && !empty($totalDuration->totalDurarion)) ? convertSecondToMinute($totalDuration->totalDurarion) : 0;
            $data['info']['currentLessonId'] = (!empty($surrentUserLession)) ? $surrentUserLession->id : 0;
            $data['info']['isCompleted']     = (!empty($userMasterClassData) && $userMasterClassData->pivot->completed && $userMasterClassData->pivot->post_survey_completed) ;

            if (!empty($userMasterClassData) && $userMasterClassData->pivot->completed && $userMasterClassData->pivot->post_survey_completed) {
                $lessonNotCompleted = CourseLession::leftJoin('user_lession', function ($join) use ($user) {
                    $join->on('course_lessions.id', '=', 'user_lession.course_lession_id')
                        ->where('user_lession.user_id', '=', $user->getKey());
                })
                    ->select("course_lessions.*", "user_lession.id as userLessionId")
                    ->where("course_lessions.course_id", $course->id)
                    ->where("course_lessions.status", true)
                    ->whereNull("user_lession.id")
                    ->get();
                if ($lessonNotCompleted->count() > 0) {
                    $user->courseLessonLogs()->attach($lessonNotCompleted, [
                        'course_id'    => $course->id,
                        'status'       => "completed",
                        'completed_at' => now()->toDateTimeString(),
                    ]);
                }
            }

            $lessonList = CourseLession::leftJoin('user_lession', function ($join) use ($user) {
                $join->on('course_lessions.id', '=', 'user_lession.course_lession_id')
                    ->where('user_lession.user_id', '=', $user->getKey());
            })
                ->where("course_lessions.course_id", $course->id)
                ->where("course_lessions.status", true)
                ->select("course_lessions.*", "user_lession.status as userLessonStatus", DB::raw("TIME_TO_SEC(course_lessions.duration) as courseDuration"), "user_lession.completed_at")
                ->orderBy("course_lessions.order_priority", "ASC")
                ->orderBy("course_lessions.id", "ASC")
                ->get();

            $data['lessons'] = ($lessonList->count() > 0) ? new MasterClassLessonCollection($lessonList) : [];
            // get course details data with json response
            $data = array("data" => $data);
            return $this->successResponse($data, 'Lesson list retrieved successfully');
        } catch (\Exception $e) {
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }
}
