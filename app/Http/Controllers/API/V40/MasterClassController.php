<?php declare (strict_types = 1);

namespace App\Http\Controllers\API\V40;

use App\Http\Controllers\API\V38\MasterClassController as v38MasterClassController;
use App\Http\Collections\V40\MasterClassLessonCollection;
use App\Models\Course;
use App\Models\CourseLession;
use App\Models\SubCategory;
use DB;
use Illuminate\Http\Request;
use App\Traits\PaginationTrait;

class MasterClassController extends v38MasterClassController
{
    use PaginationTrait;
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
