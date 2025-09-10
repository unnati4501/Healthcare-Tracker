<?php declare (strict_types = 1);

namespace App\Http\Controllers\API\V4;

use App\Http\Collections\V1\CoachReviewCollection;
use App\Http\Collections\V1\CourseReviewCollection;
use App\Http\Collections\V1\HomeMeditationCollection;
use App\Http\Collections\V2\CompletedCourseCollection;
use App\Http\Collections\V2\HomeCourseCollection;
use App\Http\Collections\V2\NotStartedCourseCollection;
use App\Http\Collections\V2\OnGoingCourseCollection;
use App\Http\Collections\V4\CategoryWiseCoachCollection;
use App\Http\Collections\V4\CategoryWiseCourseCollection;
use App\Http\Collections\V4\CourceCoachDetailsCollection;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\CourseCoachReviewRequest;
use App\Http\Resources\V1\CourseRatingsResource;
use App\Http\Resources\V2\CoachDetailsByCoachResource;
use App\Http\Resources\V4\CourceBenefitInstructionResource;
use App\Http\Resources\V4\CourseDetailsResource;
use App\Http\Resources\V4\CourseLessonDetailsResource;
use App\Http\Traits\ProvidesAuthGuardTrait;
use App\Http\Traits\ServesApiTrait;
use App\Models\Badge;
use App\Models\Category;
use App\Models\Course;
use App\Models\CourseLession;
use App\Models\Notification;
use App\Models\NotificationSetting;
use App\Models\SubCategory;
use App\Models\User;
use App\Notifications\SystemAutoNotification;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    use ServesApiTrait, ProvidesAuthGuardTrait;

    /**
     * API to get limited content on home screen - home course panel - course + meditation
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function homeCoursePanel(Request $request)
    {
        try {
            // logged-in user
            $user = $this->user();

            $runningContent = [];

            // get count of course and meditation
            $runningCourseCount = $user->courseLogs()
                ->wherePivot('joined', 1)
                ->wherePivot('completed_on', '=', null)
                ->count();
            $runningMeditationCount = $user->inCompletedMeditationTracks()->count();

            // get user's running lessions with course data
            $runningCourseRecords = $user->courseLogs()
                ->wherePivot('joined', 1)
                ->wherePivot('completed_on', '=', null);

            // get user's incompleted meditation tracks data
            $runningMeditationRecords = $user->inCompletedMeditationTracks();

            // use count based on receieved data from course and mediation = API total data count must be 10 max.
            if ($runningCourseCount >= 5 && $runningMeditationCount >= 5) {
                $runningCourseRecords     = $runningCourseRecords->paginate(5);
                $runningMeditationRecords = $runningMeditationRecords->paginate(5);
            } elseif (($runningCourseCount >= 5) && ($runningMeditationCount < 5)) {
                $runningCourseRecords     = $runningCourseRecords->paginate((10 - $runningMeditationCount));
                $runningMeditationRecords = $runningMeditationRecords->paginate($runningMeditationCount);
            } elseif (($runningMeditationCount >= 5) && ($runningCourseCount < 5)) {
                $runningCourseRecords     = $runningCourseRecords->paginate($runningCourseCount);
                $runningMeditationRecords = $runningMeditationRecords->paginate((10 - $runningCourseCount));
            } else {
                $runningCourseRecords     = $runningCourseRecords->paginate($runningCourseCount);
                $runningMeditationRecords = $runningMeditationRecords->paginate($runningMeditationCount);
            }

            // collect required course data
            $runningContent['courses'] = new HomeCourseCollection($runningCourseRecords);

            // collect required meditation data
            $runningContent['meditations'] = new HomeMeditationCollection($runningMeditationRecords);

            // return response
            return $this->successResponse(
                ['data' => $runningContent],
                (count($runningCourseRecords) > 0 || count($runningMeditationRecords) > 0) ? 'Course List retrieved successfully.' : "No results"
            );
        } catch (\Exception $e) {
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * API to fetch list of courses which are not yet started by user
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function notStarted(Request $request)
    {
        try {
            // logged-in user
            $user = $this->user();

            $notStartedContent = [];

            // get paginated course data which are not started but joined by the user
            $noStartedCourseRecords = $user->courseLogs()
                ->wherePivot('joined', true)
                ->wherePivot('started_course', false)
                ->wherePivot('completed', false)
                ->paginate(config('zevolifesettings.datatable.pagination.short'));

            $total = $user->courseLogs()->wherePivot('joined', true)->count();

            // collect required data
            $return          = [];
            $return['total'] = $total;
            $return['data']  = [];
            if ($noStartedCourseRecords->count() > 0) {
                $return = new NotStartedCourseCollection($noStartedCourseRecords, $total);
            }

            // return response
            return $this->successResponse(
                $return,
                ($noStartedCourseRecords->count() > 0) ? 'Course List retrieved successfully.' : "No results"
            );
        } catch (\Exception $e) {
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * API to fetch list of courses which are completed by user
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function completed(Request $request)
    {
        try {
            // logged-in user
            $user = $this->user();

            $notStartedContent = [];

            // get paginated course data which are completed by user
            $noStartedCourseRecords = $user->courseLogs()
                ->wherePivot('completed', true)
                ->paginate(config('zevolifesettings.datatable.pagination.short'));

            $total = $user->courseLogs()->wherePivot('joined', true)->count();

            // collect required data
            $return          = [];
            $return['total'] = $total;
            $return['data']  = [];
            if ($noStartedCourseRecords->count() > 0) {
                $return = new CompletedCourseCollection($noStartedCourseRecords, $total);
            }

            // return response
            return $this->successResponse(
                $return,
                ($noStartedCourseRecords->count() > 0) ? 'Course List retrieved successfully.' : "No results"
            );
        } catch (\Exception $e) {
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * API to fetch ongoing course of current user
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function ongoingCourses(Request $request)
    {
        try {
            // logged-in user
            $user = $this->user();

            $ongoingContent = [];

            // get paginated course data which are started but not completed
            $ongoingCourseRecords = $user->courseLogs()
                ->wherePivot('joined', true)
                ->wherePivot('started_course', true)
                ->wherePivot('completed', false)
                ->paginate(config('zevolifesettings.datatable.pagination.short'));

            $total = $user->courseLogs()->wherePivot('joined', true)->count();

            $return          = [];
            $return['total'] = $total;
            $return['data']  = [];
            if ($ongoingCourseRecords->count() > 0) {
                $return = new OnGoingCourseCollection($ongoingCourseRecords, $total);
            }

            // return response
            return $this->successResponse(
                $return,
                ($ongoingCourseRecords->count() > 0) ? 'Course List retrieved successfully.' : "No results"
            );
        } catch (\Exception $e) {
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * API to fetch saved course of current user
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function saved(Request $request)
    {
        try {
            // logged-in user
            $user = $this->user();

            // get saved courseIds of user
            $courseIds = Course::join('user_course', 'courses.id', '=', 'user_course.course_id')
                ->join("sub_categories", "sub_categories.id", "=", "courses.sub_category_id")
                ->where("user_course.user_id", $user->id)
                ->where("saved", 1)
                ->pluck("courses.id")
                ->toArray();

            if (!empty($courseIds)) {
                // get paginated course data which are saved by the user
                $categoryWiseCourseData = Course::whereIn("courses.id", $courseIds)
                    ->leftJoin('user_course', function ($join) use ($user) {
                        $join->on('courses.id', '=', 'user_course.course_id')
                            ->where('user_course.user_id', '=', $user->getKey());
                    })
                    ->leftJoin(DB::raw("(SELECT course_lessions.course_id,  COUNT(DISTINCT course_lessions.course_week_id) AS moduleCount FROM course_lessions WHERE course_lessions.is_default = FALSE and course_lessions.status = TRUE  GROUP BY course_lessions.course_id) as courseModule"), "courses.id", "=", "courseModule.course_id")
                    ->join("sub_categories", "sub_categories.id", "=", "courses.sub_category_id")
                    ->select('courses.id', 'courses.title', 'courses.is_premium', 'courses.updated_at', 'courses.creator_id', "courseModule.moduleCount", "sub_categories.name as courseSubCategory")
                    ->orderBy('user_course.saved_at', 'DESC')
                    ->orderBy('courses.id', 'DESC')
                    ->groupBy('courses.id')
                    ->paginate(config('zevolifesettings.datatable.pagination.short'));

                if ($categoryWiseCourseData->count() > 0) {
                    // collect required data and return response
                    return $this->successResponse(new CategoryWiseCourseCollection($categoryWiseCourseData), 'Course list retrieved successfully.');
                } else {
                    // return empty response
                    return $this->successResponse(['data' => []], 'No results');
                }
            } else {
                // return empty response
                return $this->successResponse(['data' => []], 'No results');
            }
        } catch (\Exception $e) {
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    public function runningContent(Request $request)
    {
        try {
            $user           = $this->user();
            $runningContent = [];

            if ($request->type == 'course') {
                $runningCourseRecords = $user->courseLogs()
                    ->wherePivot('joined', 1)
                    ->wherePivot('completed', 0)
                    ->paginate(config('zevolifesettings.datatable.pagination.short'));

                $runningContent = new HomeCourseCollection($runningCourseRecords, true);

                return $this->successResponse(
                    (count($runningContent) > 0) ? $runningContent : ['data' => []],
                    (count($runningContent) > 0) ? 'Course List retrieved successfully.' : "No results"
                );
            } elseif ($request->type == 'meditation') {
                $runningMeditationRecords = $user->inCompletedMeditationTracks()->paginate(config('zevolifesettings.datatable.pagination.short'));

                $runningContent = new HomeMeditationCollection($runningMeditationRecords, true);

                return $this->successResponse(
                    (count($runningContent) > 0) ? $runningContent : ['data' => []],
                    (count($runningContent) > 0) ? 'Meditation List retrieved successfully.' : "No results"
                );
            } else {
                return $this->successResponse($runningContent, "No results");
            }
        } catch (\Exception $e) {
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * API to fetch course data by given category
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function categoryCourses(Request $request, SubCategory $subcategory)
    {
        try {
            // logged-in user
            $user = $this->user();

            // get paginated course data by category
            $categoryWiseCourseData = Course::where("sub_category_id", $subcategory->id)
                ->where("courses.status", true)
                ->leftJoin('user_course', function ($join) {
                    $join->on('courses.id', '=', 'user_course.course_id')
                        ->where('user_course.ratings', '>', 0);
                })
                ->leftJoin(DB::raw("(SELECT course_lessions.course_id,  COUNT(DISTINCT course_lessions.course_week_id) AS moduleCount FROM course_lessions WHERE course_lessions.is_default = FALSE AND course_lessions.status = TRUE GROUP BY course_lessions.course_id) as courseModule"), "courses.id", "=", "courseModule.course_id")
                ->join("sub_categories", "sub_categories.id", "=", "courses.sub_category_id")
                ->select('courses.id', 'courses.title', 'courses.is_premium', 'courses.updated_at', 'courses.creator_id', DB::raw(" sum(user_course.ratings) / count(user_course.user_id) as totalRatings"), "courseModule.moduleCount", "sub_categories.name as courseSubCategory");

            if ($request->type == "popular") {
                $categoryWiseCourseData = $categoryWiseCourseData->orderBy('totalRatings', 'DESC')->orderBy('courses.updated_at', 'DESC');
            } else {
                $categoryWiseCourseData = $categoryWiseCourseData->orderBy('courses.updated_at', 'DESC');
            }

            $categoryWiseCourseData = $categoryWiseCourseData->groupBy('courses.id')->paginate(config('zevolifesettings.datatable.pagination.short'));

            if ($categoryWiseCourseData->count() > 0) {
                // collect required data and return response
                return $this->successResponse(new CategoryWiseCourseCollection($categoryWiseCourseData), 'Course list retrieved successfully.');
            } else {
                // return empty response
                return $this->successResponse(['data' => []], 'No results');
            }
        } catch (\Exception $e) {
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * API to fetch coach details by course
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function coachDetailsByCourse(Request $request, Course $course)
    {
        try {
            $categoryWiseCoachList = Course::where("creator_id", $course->creator_id)
                ->where("courses.status", true)
                ->join("sub_categories", "sub_categories.id", "=", "courses.sub_category_id")
                ->leftJoin(DB::raw("(SELECT course_id , AVG(NULLIF(ratings ,0)) as Avgratings from user_course group by course_id order by updated_at DESC) as userCourse"), "courses.id", "=", "userCourse.course_id")
                ->select('courses.creator_id', 'sub_categories.name', 'courses.id', 'courses.updated_at', 'courses.sub_category_id', 'userCourse.*', DB::raw("AVG(NULLIF(userCourse.Avgratings ,0)) as Avgratings1"))
                ->orderBy('Avgratings1', 'DESC')
                ->orderBy('updated_at', 'DESC')
                ->groupBy('courses.sub_category_id')
                ->limit(2)
                ->get();

            if ($categoryWiseCoachList->count() > 0) {
                return $this->successResponse(new CourceCoachDetailsCollection($categoryWiseCoachList), 'Course Coach retrieved successfully.');
            } else {
                return $this->successResponse(['data' => []], 'No results');
            }
        } catch (\Exception $e) {
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * API to fetch course coach data by given category
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function courseCoachList(Request $request, SubCategory $subcategory)
    {
        try {
            // logged-in user
            $user = $this->user();
            // get paginated course coach data by category
            $categoryWiseCoachList = Course::where("sub_category_id", $subcategory->id)
                ->where("courses.status", true)
                ->join("users", "users.id", "=", "courses.creator_id")
                ->leftJoin(DB::raw("(SELECT coach_id , AVG(NULLIF(ratings ,0)) as Avgratings , COUNT(NULLIF(ratings ,0)) as Totalreview from user_coach_log group by coach_id) as userCoach"), "courses.creator_id", "=", "userCoach.coach_id")
                ->select('courses.id', 'courses.updated_at', 'courses.creator_id', DB::raw("count(courses.id) as totalCourse"), DB::raw("CONCAT(users.first_name,' ',users.last_name) as name"), 'userCoach.*')
                ->orderBy('Avgratings', 'DESC')
                ->orderBy('courses.updated_at', 'DESC')
                ->groupBy('courses.creator_id')
                ->paginate(config('zevolifesettings.datatable.pagination.short'));

            if ($categoryWiseCoachList->count() > 0) {
                // collect required data and return response
                return $this->successResponse(new CategoryWiseCoachCollection($categoryWiseCoachList), 'Coach list retrieved successfully.');
            } else {
                // return empty response
                return $this->successResponse(['data' => []], 'No results');
            }
        } catch (\Exception $e) {
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * Join the requested course to the loggedin user
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function join(Request $request, Course $course)
    {
        try {
            \DB::beginTransaction();

            // logged-in user
            $user = $this->user();

            // get user course pivot record
            $pivotExsisting = $user->courseLogs()->wherePivot('user_id', $user->getKey())->wherePivot('course_id', $course->getKey())->first();

            if (!empty($pivotExsisting)) {
                // if record exists then check if course id already joined or not
                $is_joined = $pivotExsisting->pivot->joined;

                if (!$is_joined) {
                    // update pivot if course not joined
                    $pivotExsisting->pivot->joined    = 1;
                    $pivotExsisting->pivot->joined_on = now()->toDateTimeString();
                    $pivotExsisting->pivot->save();

                    $firstLesstion = $course->courseLessions()
                        ->where('is_default', false)
                        ->where('course_lessions.status', true)
                        ->orderBy('course_week_id', 'ASC')
                        ->orderBy('id', 'ASC')
                        ->first();

                    if (!empty($firstLesstion)) {
                        $allPriorWeeks = $course->courseWeeks()->where('course_weeks.id', '<', $firstLesstion->course_week_id)->where('course_weeks.status', true)->orderBy('course_weeks.id', 'ASC')->get();

                        if (!empty($allPriorWeeks) && $allPriorWeeks->count() > 0) {
                            foreach ($allPriorWeeks as $priorWeek) {
                                $user->courseWeekLogs()->attach($priorWeek, ['course_id' => $course->getKey(), 'status' => 'completed', 'completed_at' => now()->toDateTimeString()]);
                            }
                        }

                        $user->unlockedCourseLessons()->attach($firstLesstion, ['course_week_id' => $firstLesstion->course_week_id, 'course_id' => $course->getKey()]);
                    }

                    \DB::commit();

                    $data                     = array();
                    $data['unlockedLessonId'] = (!empty($firstLesstion)) ? $firstLesstion->id : 0;
                    $data['students']         = $course->getTotalStudents();

                    return $this->successResponse(['data' => $data], "You have joined course successfully.");
                } else {
                    return $this->successResponse(['data' => []], "You have already joined this course.");
                }
            } else {
                // if not pivot record found then create new record with joined flag true
                $user->courseLogs()->attach($course, ['joined' => true, 'joined_on' => now()->toDateTimeString()]);

                $firstLesstion = $course->courseLessions()->where('is_default', false)->where('course_lessions.status', true)->orderBy('course_week_id', 'ASC')->orderBy('id', 'ASC')->first();

                if (!empty($firstLesstion)) {
                    $allPriorWeeks = $course->courseWeeks()->where('course_weeks.id', '<', $firstLesstion->course_week_id)->where('course_weeks.status', true)->orderBy('course_weeks.id', 'ASC')->get();

                    if (!empty($allPriorWeeks) && $allPriorWeeks->count() > 0) {
                        foreach ($allPriorWeeks as $priorWeek) {
                            $user->courseWeekLogs()->attach($priorWeek, ['course_id' => $course->getKey(), 'status' => 'completed', 'completed_at' => now()->toDateTimeString()]);
                        }
                    }

                    $user->unlockedCourseLessons()->attach($firstLesstion, ['course_week_id' => $firstLesstion->course_week_id, 'course_id' => $course->getKey()]);
                }

                \DB::commit();

                $data                     = array();
                $data['unlockedLessonId'] = (!empty($firstLesstion)) ? $firstLesstion->id : 0;
                $data['students']         = $course->getTotalStudents();

                return $this->successResponse(['data' => $data], "You have joined course successfully.");
            }
        } catch (\Exception $e) {
            \DB::rollback();
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * API to fetch course details data with all course lesson
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function details(Request $request, Course $course)
    {
        try {
            // get course details data with json response
            $data = array("data" => new CourseDetailsResource($course));
            return $this->successResponse($data, 'Course Info retrieved successfully');
        } catch (\Exception $e) {
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * API to fetch coach course details
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function coachDetail(Request $request, User $user)
    {
        try {
            // get coach course details data with json response
            $data = array("data" => new CoachDetailsByCoachResource($user));
            return $this->successResponse($data, 'Coach Detail Retrieved successfully.');
        } catch (\Exception $e) {
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * API to fetch course list by coach
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function coachCourses(Request $request, User $user)
    {
        try {
            // logged in user
            $loginUser = $this->user();

            // get paginated course data by coach
            $coachWiseCourseData = Course::where("creator_id", $user->id)
                ->where("courses.status", true)
                ->leftJoin('user_course', function ($join) {
                    $join->on('courses.id', '=', 'user_course.course_id')
                        ->where('user_course.ratings', '>', 0);
                })
                ->leftJoin(DB::raw("(SELECT course_lessions.course_id,  COUNT(DISTINCT course_lessions.course_week_id) AS moduleCount FROM course_lessions WHERE course_lessions.is_default = FALSE AND course_lessions.status = TRUE  GROUP BY course_lessions.course_id) as courseModule"), "courses.id", "=", "courseModule.course_id")
                ->join("sub_categories", "sub_categories.id", "=", "courses.sub_category_id")
                ->select('courses.id', 'courses.title', 'courses.is_premium', 'courses.updated_at', 'courses.creator_id', DB::raw(" sum(user_course.ratings) / count(user_course.user_id) as totalRatings"), "courseModule.moduleCount", "sub_categories.name as courseSubCategory");

            if (!empty($request->expertise)) {
                $coachWiseCourseData = $coachWiseCourseData->whereIn("courses.expertise_level", $request->expertise);
            }

            $coachWiseCourseData = $coachWiseCourseData->orderBy('courses.updated_at', 'DESC')
                ->groupBy('courses.id')
                ->paginate(config('zevolifesettings.datatable.pagination.short'));

            if ($coachWiseCourseData->count() > 0) {
                // collect course data and return json response
                return $this->successResponse(new CategoryWiseCourseCollection($coachWiseCourseData), 'Course list retrieved successfully.');
            } else {
                // return empty response
                return $this->successResponse(['data' => []], 'No results');
            }
        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function lessonStatusChange(Request $request, CourseLession $courseLession)
    {
        try {
            \DB::beginTransaction();

            // logged-in user
            $user      = $this->user();
            $companyId = !is_null($user->company->first()) ? $user->company->first()->id : null;

            $notification_setting = NotificationSetting::select('flag')
                ->where(['flag' => 1, 'user_id' => $user->getKey()])
                ->whereRaw('(`user_notification_settings`.`module` = ? OR `user_notification_settings`.`module` = ?)', ['badges', 'all'])
                ->first();

            $status = $request->status;

            $course                  = Course::find($courseLession->course_id);
            $courseWeek              = \App\Models\CourseWeek::find($courseLession->course_week_id);
            $totalLessionCountOfWeek = $courseWeek->courseLessions()->where('course_lessions.status', true)->where('is_default', false)->count();

            // get user course lession pivot record
            $pivotExsisting = $user->courseLessonLogs()->wherePivot('user_id', $user->getKey())->wherePivot('course_lession_id', $courseLession->getKey())->first();

            // get user course pivot record
            $coursePivotExsisting = $user->courseLogs()->wherePivot('user_id', $user->getKey())->wherePivot('course_id', $courseLession->course_id)->first();

            if (!empty($pivotExsisting) && $status == 'completed') {
                // if record exists and requested status is completed then check and update record
                $pivotExsisting->pivot->status       = $status;
                $pivotExsisting->pivot->completed_at = now()->toDateTimeString();

                $pivotExsisting->pivot->save();

                // get completed lession count of current week
                $totalCompletedCountOfWeek = $user->courseLessonLogs()->wherePivot('course_week_id', $courseLession->course_week_id)->wherePivot('status', 'completed')->count();

                // mark course week as completed if all lessions are completed of this week
                $thisWerkLogOfUser = $user->courseWeekLogs()->wherePivot('course_week_id', $courseWeek->getKey())->first();

                if (!empty($thisWerkLogOfUser) && $thisWerkLogOfUser->pivot->status == 'completed') {
                    $nextLesstion = null;
                } elseif ($totalCompletedCountOfWeek == $totalLessionCountOfWeek) {
                    $nextWeek = $course->courseWeeks()->where('is_default', false)->where('course_weeks.status', true)->where('course_weeks.id', '>', $courseWeek->id)->orderBy('course_weeks.id', 'ASC')->first();

                    if (!empty($nextWeek)) {
                        $lessionCountOfNextWeek = $nextWeek->courseLessions()->where('course_lessions.status', true)->where('is_default', false)->count();

                        do {
                            if ($lessionCountOfNextWeek == 0) {
                                $user->courseWeekLogs()->attach($nextWeek, ['course_id' => $course->getKey(), 'status' => 'completed', 'completed_at' => now()->toDateTimeString()]);

                                $nextWeek = $course->courseWeeks()->where('is_default', false)->where('course_weeks.status', true)->where('course_weeks.id', '>', $nextWeek->id)->orderBy('course_weeks.id', 'ASC')->first();
                                if (!empty($nextWeek)) {
                                    $lessionCountOfNextWeek = $nextWeek->courseLessions()->where('course_lessions.status', true)->where('is_default', false)->count();
                                } else {
                                    $lessionCountOfNextWeek = 1;
                                }
                            }
                        } while ($lessionCountOfNextWeek == 0);

                        if (!empty($nextWeek)) {
                            $allUnlockedIdsOfNextWeek = $user->unlockedCourseLessons()->wherePivot("course_week_id", $nextWeek->getKey())->pluck('course_lession_id')->toArray();

                            $nextLesstion = $nextWeek->courseLessions()->where('course_lessions.status', true)->where('is_default', false)->whereNotIn('id', $allUnlockedIdsOfNextWeek)->orderBy('id', 'ASC')->first();
                        }
                    }

                    $user->courseWeekLogs()->attach($courseWeek, ['course_id' => $course->getKey(), 'status' => 'completed', 'completed_at' => now()->toDateTimeString()]);
                } else {
                    $allUnlockedIdsOfThisWeek = $user->unlockedCourseLessons()->wherePivot("course_week_id", $courseWeek->getKey())->pluck('course_lession_id')->toArray();

                    $nextLesstion = $courseWeek->courseLessions()->where('course_lessions.status', true)->where('is_default', false)->whereNotIn('id', $allUnlockedIdsOfThisWeek)->orderBy('id', 'ASC')->first();
                }

                // mark course as completed if all lession are completed
                $completedCountOfCourse = $user->courseLessonLogs()->wherePivot('course_id', $course->id)->wherePivot('status', 'completed')->count();

                $totalLessionsOfCourse = $course->courseLessions()->where('is_default', false)->where('course_lessions.status', true)->count();
                if ($completedCountOfCourse == $totalLessionsOfCourse) {
                    $coursePivotExsisting->pivot->completed    = true;
                    $coursePivotExsisting->pivot->completed_on = now()->toDateTimeString();
                    $coursePivotExsisting->pivot->save();

                    $courseBadge = Badge::where("model_id", $courseLession->course_id)
                        ->where("model_name", "course")
                        ->where("type", "course")
                        ->first();

                    if (!empty($courseBadge)) {
                        $userBadgeData = $user->badges()
                            ->wherePivot("badge_id", $courseBadge->id)
                            ->wherePivot("user_id", $user->id)
                            ->first();

                        if (empty($userBadgeData)) {
                            $badgeInput = [
                                'status' => "Active",
                            ];
                            $courseBadge->badgeusers()->attach($user, $badgeInput);

                            $title   = trans('notifications.badge.course.title');
                            $message = trans('notifications.badge.course.message');
                            $message = str_replace(["#course_name#"], [$course->title], $message);

                            $notification = Notification::create([
                                'type'             => 'Auto',
                                'creator_id'       => $course->creator_id,
                                'company_id'       => $companyId,
                                'creator_timezone' => $user->timezone,
                                'title'            => $title,
                                'message'          => $message,
                                'push'             => ($notification_setting->flag ?? false),
                                'scheduled_at'     => now()->toDateTimeString(),
                                'is_mobile'        => config('notification.general_badges.masterclass.is_mobile'),
                                'is_portal'        => config('notification.general_badges.masterclass.is_portal'),
                                'deep_link_uri'    => 'zevolife://zevo/badges',
                            ]);

                            $user->notifications()->attach($notification, ['sent' => true, 'sent_on' => now()->toDateTimeString()]);

                            if (!empty($notification_setting) && $notification_setting->flag ) {
                                // send notification to all users
                                \Notification::send(
                                    $user,
                                    new SystemAutoNotification($notification, '')
                                );
                            }
                        }
                    }
                }

                if (!empty($nextLesstion)) {
                    $nexUserCourseLesson = $user->unlockedCourseLessons()->wherePivot("course_lession_id", $nextLesstion->getKey())->first();

                    if (empty($nexUserCourseLesson)) {
                        $user->unlockedCourseLessons()->attach($nextLesstion, ['course_week_id' => $nextLesstion->course_week_id, 'course_id' => $course->getKey()]);
                    }
                }

                \DB::commit();

                $data                     = array();
                $data['unlockedLessonId'] = (!empty($nextLesstion)) ? $nextLesstion->id : 0;
                $data['students']         = Course::find($courseLession->course_id)->getTotalStudents();

                return $this->successResponse(['data' => $data], "lesson status changed successfully.");
            } else {
                // if not pivot record found then create new record with requested status - started
                $user->courseLessonLogs()->attach($courseLession, [
                    'course_id'      => $courseLession->course_id,
                    'course_week_id' => $courseLession->course_week_id,
                    'status'         => $status,
                ]);

                if ($status == 'started') {
                    if (!$coursePivotExsisting->pivot->started_course) {
                        $coursePivotExsisting->pivot->started_course = true;
                        $coursePivotExsisting->pivot->save();
                    }
                }

                \DB::commit();

                $data                     = array();
                $data['unlockedLessonId'] = 0;
                $data['students']         = Course::find($courseLession->course_id)->getTotalStudents();

                return $this->successResponse(['data' => $data], "lesson status changed successfully.");
            }
        } catch (\Exception $e) {
            \DB::rollback();
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }

        return json_decode($jsonString, true);
    }

    /**
     * API to save unsave course by course
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function saveUnsaveCourse(Request $request, Course $course)
    {
        try {
            \DB::beginTransaction();
            // logged-in user
            $user   = $this->user();
            $resMsg = "";

            // fetch user course data
            $pivotExsisting = $course->courseUserLogs()->wherePivot('user_id', $user->getKey())->wherePivot('course_id', $course->getKey())->first();

            if (!empty($pivotExsisting)) {
                $saved = $pivotExsisting->pivot->saved;

                $pivotExsisting->pivot->saved    = ($saved == 1) ? 0 : 1;
                $pivotExsisting->pivot->saved_at = now()->toDateTimeString();

                $pivotExsisting->pivot->save();

                $resMsg = ($saved == 1) ? "unsaved" : "saved";
            } else {
                $resMsg = "saved";
                $course->courseUserLogs()->attach($user, ['saved' => true, 'saved_at' => now()->toDateTimeString()]);
            }

            \DB::commit();
            return $this->successResponse([], 'Course ' . $resMsg . ' successfully');
        } catch (\Exception $e) {
            \DB::rollback();
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * API to like unlike course by course
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function likeUnlikeCourse(Request $request, Course $course)
    {
        try {
            \DB::beginTransaction();
            // logged-in user
            $user   = $this->user();
            $resMsg = "";

            // fetch user course data
            $pivotExsisting = $course->courseUserLogs()->wherePivot('user_id', $user->getKey())->wherePivot('course_id', $course->getKey())->first();

            if (!empty($pivotExsisting)) {
                $liked = $pivotExsisting->pivot->liked;

                $pivotExsisting->pivot->liked = ($liked == 1) ? 0 : 1;

                $pivotExsisting->pivot->save();

                $resMsg = ($liked == 1) ? "Unliked" : "liked";
            } else {
                $resMsg = "liked";
                $course->courseUserLogs()->attach($user, ['liked' => true]);
            }

            \DB::commit();
            return $this->successResponse(['data' => ['totalLikes' => $course->getTotalLikes()]], 'Course ' . $resMsg . ' successfully');
        } catch (\Exception $e) {
            \DB::rollback();
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * API to write review and ratings for course / coach
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function reviews(CourseCoachReviewRequest $request)
    {
        try {
            \DB::beginTransaction();

            $successData   = array();
            $user          = $this->user();
            $averageRating = 0;

            // if type is course then write user review for course
            if ($request->type == "course") {
                // check course is exists or not
                $courseData = Course::find($request->id);

                if (!empty($courseData)) {
                    // get course user pivot data
                    $pivotExsisting = $courseData->courseUserLogs()->wherePivot('user_id', $user->getKey())->wherePivot('course_id', $request->id)->first();

                    if (!empty($pivotExsisting)) {
                        // check review exists or not and if exists then update user review and ratings
                        $pivotExsisting->pivot->ratings = $request->rating;

                        if (!empty($request->review)) {
                            $pivotExsisting->pivot->review = $request->review;
                        } else {
                            $pivotExsisting->pivot->review = null;
                        }

                        $pivotExsisting->pivot->save();

                        $successData['data']['id']        = $pivotExsisting->pivot->id;
                        $successData['data']['rating']    = (int) $pivotExsisting->pivot->ratings;
                        $successData['data']['review']    = (!empty($pivotExsisting->pivot->review)) ? $pivotExsisting->pivot->review : "";
                        $successData['data']['createdAt'] = Carbon::parse($pivotExsisting->pivot->created_at, config('app.timezone'))->setTimezone($user->timezone)->toAtomString();
                    } else {
                        // if review not exists then add user review and ratings
                        $courserating            = array();
                        $courserating['ratings'] = $request->rating;
                        if (!empty($request->review)) {
                            $courserating['review'] = $request->review;
                        }

                        $courseData->courseUserLogs()->attach($user, $courserating);

                        $courcecreated                    = $courseData->courseUserLogs()->wherePivot('user_id', $user->getKey())->wherePivot('course_id', $request->id)->first();
                        $successData['data']['id']        = $courcecreated->pivot->id;
                        $successData['data']['rating']    = (int) $courcecreated->pivot->ratings;
                        $successData['data']['review']    = (!empty($courcecreated->pivot->review)) ? $courcecreated->pivot->review : "";
                        $successData['data']['createdAt'] = Carbon::parse($courcecreated->pivot->created_at, config('app.timezone'))->setTimezone($user->timezone)->toAtomString();
                    }
                    \DB::commit();

                    $averageRating = $courseData->courseAverageRatings();
                } else {
                    return $this->notFoundResponse("Sorry! Course data not found");
                }
            } else {
                // if type is coach then write user review for coach

                // check coach is exists or not
                $coachData = User::find($request->id);
                if (!empty($coachData)) {
                    // get coach user pivot data
                    $pivotExsisting = $coachData->coachLogs()->wherePivot('user_id', $user->getKey())->wherePivot('coach_id', $request->id)->first();

                    if (!empty($pivotExsisting)) {
                        // check review exists or not and if exists then update user review and ratings for coach
                        $pivotExsisting->pivot->ratings = $request->rating;

                        if (!empty($request->review)) {
                            $pivotExsisting->pivot->review = $request->review;
                        } else {
                            $pivotExsisting->pivot->review = null;
                        }

                        $pivotExsisting->pivot->save();

                        $successData['data']['id']        = $pivotExsisting->pivot->id;
                        $successData['data']['rating']    = (int) $pivotExsisting->pivot->ratings;
                        $successData['data']['review']    = (!empty($pivotExsisting->pivot->review)) ? $pivotExsisting->pivot->review : "";
                        $successData['data']['createdAt'] = Carbon::parse($pivotExsisting->pivot->created_at, config('app.timezone'))->setTimezone($user->timezone)->toAtomString();
                    } else {
                        // if review not exists then add user review and ratings for coach
                        $courserating            = array();
                        $courserating['ratings'] = $request->rating;
                        if (!empty($request->review)) {
                            $courserating['review'] = $request->review;
                        }

                        $coachData->coachLogs()->attach($user, $courserating);

                        $coachcreated                     = $coachData->coachLogs()->wherePivot('user_id', $user->getKey())->wherePivot('coach_id', $request->id)->first();
                        $successData['data']['id']        = $coachcreated->pivot->id;
                        $successData['data']['rating']    = (int) $coachcreated->pivot->ratings;
                        $successData['data']['review']    = (!empty($coachcreated->pivot->review)) ? $coachcreated->pivot->review : "";
                        $successData['data']['createdAt'] = Carbon::parse($coachcreated->pivot->created_at, config('app.timezone'))->setTimezone($user->timezone)->toAtomString();
                    }
                    \DB::commit();

                    $averageRating = $coachData->coachAverageRatings();
                } else {
                    return $this->notFoundResponse("Sorry! Coach data not found");
                }
            }
            $successData['data']['averageRating'] = $averageRating;
            $successData['data']['user']          = $user->getUserDataForApi();

            return $this->successResponse($successData, 'You have successfully written review');
        } catch (\Exception $e) {
            \DB::rollback();
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * API to fetch course ratings data
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function ratings(Request $request, Course $course)
    {
        try {
            $courseData = $course->courseUserLogs()->wherePivot("ratings", ">", 0);
            if ($courseData->count() > 0) {
                $data = array("data" => new CourseRatingsResource($course));
                return $this->successResponse($data, 'Course rating Received Successfully.');
            } else {
                $data                     = array();
                $data['averageRating']    = 0;
                $data['totalUserRatings'] = 0;
                $data['five']             = 0;
                $data['four']             = 0;
                $data['three']            = 0;
                $data['two']              = 0;
                $data['one']              = 0;
                $data['isReviewed']       = false;
                $data['reviews']          = array();
                return $this->successResponse(["data" => $data], 'Course rating Received Successfully.');
            }
        } catch (\Exception $e) {
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * API to fetch course / coach review data
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function reviewsList(Request $request)
    {
        try {
            if ($request->type == "course") {
                $course = Course::find($request->id);

                if (!empty($course)) {
                    $courseData = $course->courseUserLogs()
                        ->wherePivot("course_id", $request->id)
                        ->wherePivot("ratings", ">", 0)
                        ->orderBy("user_course.updated_at", "DESC")
                        ->paginate(config('zevolifesettings.datatable.pagination.short'));

                    if ($courseData->count() > 0) {
                        return $this->successResponse(new CourseReviewCollection($courseData, true), 'Course review retrieved successfully.');
                    } else {
                        $data = array("data" => []);
                        return $this->successResponse($data, 'No results');
                    }
                } else {
                    return $this->notFoundResponse("Sorry! Course data not found");
                }
            } elseif ($request->type == "coach") {
                $coach = User::find($request->id);
                if (!empty($coach)) {
                    $coachData = $coach->coachLogs()
                        ->wherePivot('coach_id', $request->id)
                        ->wherePivot("ratings", ">", 0)
                        ->orderBy("user_coach_log.updated_at", "DESC")
                        ->paginate(config('zevolifesettings.datatable.pagination.short'));

                    if ($coachData->count() > 0) {
                        return $this->successResponse(new CoachReviewCollection($coachData, true), 'Coach review retrieved successfully.');
                    } else {
                        $data = array("data" => []);
                        return $this->successResponse($data, 'No results');
                    }
                } else {
                    return $this->notFoundResponse("Sorry! Coach data not found");
                }
            } else {
                return $this->internalErrorResponse("Invalid type request");
            }
        } catch (\Exception $e) {
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * API to fetch course benefit/instruction  data
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function benefitInstruction(Request $request, Course $course)
    {
        try {
            $data = array("data" => new CourceBenefitInstructionResource($course));
            return $this->successResponse($data, 'Course benefit/instruction retrieved successfully.');
        } catch (\Exception $e) {
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function unblockLesson(Request $request, CourseLession $courseLession)
    {
        try {
            \DB::beginTransaction();

            $user = $this->user();

            // get user course pivot record
            $pivotExsisting = $user->courseLessonLogs()->wherePivot('user_id', $user->getKey())->wherePivot('course_lession_id', $courseLession->getKey())->first();

            if (empty($pivotExsisting)) {
                // if not pivot record found then create new record with joined flag true
                $user->courseLessonLogs()->attach($courseLession, ['status' => 'started', 'course_id' => $courseLession->course_id, 'course_week_id' => $courseLession->course_week_id]);
                \DB::commit();

                return $this->successResponse(['data' => []], "Lesson unlocked successfully.");
            } else {
                return $this->successResponse(['data' => []], "You have already unlocked the lesson.");
            }
        } catch (\Exception $e) {
            \DB::rollback();
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * API to fetch course lesson details by lesson
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function lessonDetails(Request $request, CourseLession $lesson)
    {
        try {
            // get course lesson data with json response
            $data = array("data" => new CourseLessonDetailsResource($lesson));
            return $this->successResponse($data, 'Lesson detail retrieved successfully.');
        } catch (\Exception $e) {
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * API to follow/unfollow coach by coach
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function followUnfollowCoach(Request $request, User $user)
    {
        try {
            \DB::beginTransaction();

            $loginUser = $this->user();
            $resMsg    = "";
            // get pivoted coach data by user
            $pivotExsisting = $user->coachLogs()->wherePivot('user_id', $loginUser->getKey())->wherePivot('coach_id', $user->getKey())->first();

            if (!empty($pivotExsisting)) {
                // check if coach is followed / unfollowed by user
                $followed = $pivotExsisting->pivot->followed;

                $pivotExsisting->pivot->followed = ($followed == 1) ? 0 : 1;

                $pivotExsisting->pivot->save();

                $resMsg = ($followed == 1) ? "unfollowed" : "followed";
            } else {
                $resMsg = "followed";
                $user->coachLogs()->attach($loginUser, ['followed' => true]);
            }

            \DB::commit();

            $followerCount = $user->totalFollowerCount();

            return $this->successResponse(['data' => ['followerCount' => $followerCount]], 'Coach ' . $resMsg . ' successfully.');
        } catch (\Exception $e) {
            \DB::rollback();
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * API to fetch course / coach review data
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function removeReviews(Request $request)
    {
        try {
            \DB::beginTransaction();
            $averageRating = 0;
            if ($request->type == "course") {
                $courseReview = DB::table("user_course")->where("id", $request->id)->first();

                if (!empty($courseReview)) {
                    $courseData = Course::find($courseReview->course_id);

                    $data            = array();
                    $data['ratings'] = 0;
                    $data['review']  = null;

                    $update = DB::table("user_course")->where("id", $request->id)->update($data);

                    \DB::commit();
                    $averageRating = $courseData->courseAverageRatings();

                    return $this->successResponse(["data" => ["averageRating" => $averageRating]], 'Course review deleted successfully.');
                } else {
                    return $this->notFoundResponse("Sorry! requested data not found");
                }
            } elseif ($request->type == "coach") {
                $coachReview = DB::table("user_coach_log")->where("id", $request->id)->first();

                if (!empty($coachReview)) {
                    $coachData = User::find($coachReview->coach_id);

                    $data            = array();
                    $data['ratings'] = 0;
                    $data['review']  = null;

                    $update = DB::table("user_coach_log")->where("id", $request->id)->update($data);

                    \DB::commit();
                    $averageRating = $coachData->coachAverageRatings();

                    return $this->successResponse(["data" => ["averageRating" => $averageRating]], 'Coach review deleted successfully.');
                } else {
                    return $this->notFoundResponse("Sorry! requested data not found");
                }
            } else {
                return $this->internalErrorResponse("Invalid type request");
            }
        } catch (\Exception $e) {
            \DB::rollback();
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }
}
