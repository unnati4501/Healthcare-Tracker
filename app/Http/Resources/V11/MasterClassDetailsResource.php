<?php

namespace App\Http\Resources\V11;

use App\Http\Collections\V6\MasterClassLessonCollection;
use App\Http\Traits\ProvidesAuthGuardTrait;
use App\Models\CourseLession;
use App\Models\Group;
use App\Models\UserLession;
use DB;
use Illuminate\Http\Resources\Json\JsonResource;

class MasterClassDetailsResource extends JsonResource
{
    use ProvidesAuthGuardTrait;

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        /** @var User $user */
        $user                = $this->user();
        $lessons             = [];
        $totalDuration       = $this->courseTotalDurarion();
        $userMasterClassData = $this->courseUserLogs()->wherePivot("user_id", $user->id)->first();
        $xDeviceOs           = strtolower($request->header('X-Device-Os', ""));

        $surrentUserLession = $user->courseLessonLogs()
            ->wherePivot('course_id', $this->id)
            ->wherePivot('user_id', $user->id)
            ->wherePivot('status', "started")
            ->orderBy("user_lession.id", "DESC")
            ->first();

        $trailerData = array();
        if ($this->has_trailer) {
            $trailerData = $this->getTrailerMediaData();
        }
        $statusText = "";

        if (!empty($userMasterClassData) && $userMasterClassData->pivot->completed && $userMasterClassData->pivot->post_survey_completed) {
            $statusText = "Completed";
        } elseif (!empty($userMasterClassData) && !$userMasterClassData->pivot->completed && $userMasterClassData->pivot->pre_survey_completed) {
            if ($userMasterClassData->pivot->started_course && empty($surrentUserLession)) {
                $statusText = "survey pending";
            } else {
                $totalCompletedLession = UserLession::selectRaw('SUM(TIME_TO_SEC(course_lessions.duration)) as totalDurarion')
                    ->join("course_lessions", "course_lessions.id", "=", "user_lession.course_lession_id")
                    ->where("user_lession.user_id", $user->id)
                    ->where("user_lession.course_id", $this->id)
                    ->where("user_lession.status", "completed")
                    ->first();
                $minuteRemain = 0;
                if (!empty($totalCompletedLession) && !empty($totalDuration)) {
                    $minuteRemain = convertSecondToMinute($totalDuration->totalDurarion - $totalCompletedLession->totalDurarion);
                }

                $statusText = $minuteRemain . " mins remaining";
            }
        }

        $lessonCount = $this->courseLessions()
            ->where('course_lessions.course_id', $this->id)
            ->where('course_lessions.status', true)
            ->count();

        $completedLessonCount = $user->courseLessonLogs()
            ->wherePivot('course_id', $this->id)
            ->wherePivot('user_id', $user->id)
            ->wherePivot('status', "completed")
            ->count();

        $isAllLessonCompleted = false;

        if ($lessonCount == $completedLessonCount) {
            $isAllLessonCompleted = true;
        }

        $mappedGroup = Group::where('model_name', 'masterclass')
            ->where('model_id', $this->id)
            ->where('is_visible', 1)
            ->where('is_archived', 0)
            ->first();

        if ($xDeviceOs == config('zevolifesettings.PORTAL')) {
            $lessonList = CourseLession::leftJoin('user_lession', function ($join) use ($user) {
                $join->on('course_lessions.id', '=', 'user_lession.course_lession_id')
                    ->where('user_lession.user_id', '=', $user->id);
            })
                ->where("course_lessions.course_id", $this->id)
                ->where("course_lessions.status", true)
                ->select("course_lessions.*", "user_lession.status as userLessonStatus", DB::raw("TIME_TO_SEC(course_lessions.duration) as courseDuration"), "user_lession.completed_at")
                ->orderBy("course_lessions.order_priority", "ASC")
                ->orderBy("course_lessions.id", "ASC")
                ->get();

            $lessons = ($lessonList->count() > 0) ? new MasterClassLessonCollection($lessonList) : [];
        }

        $xDeviceOs = strtolower($request->header('X-Device-Os', ""));
        $width     = 1280;
        $height    = 640;

        $totalDurations = (!empty($totalDuration)) ? convertSecondToMinute($totalDuration->totalDurarion) : 0;
        // commenting below code as it causing image blur issue in masterclass while showing logo as a cover image on details page
        // if ($xDeviceOs == config('zevolifesettings.PORTAL')) {
        //     $width = 200;
        //     $height  = 200;
        // }

        $return = [
            'id'                   => $this->id,
            'title'                => $this->title,
            'creator'              => $this->getCreatorData(),
            'image'                => $this->getMediaData('logo', ['w' => $width, 'h' => $height, 'zc' => 3]),
            "category"             => array("id" => (!empty($this->subCategory()->first())) ? $this->subCategory()->first()->id : "", "name" => (!empty($this->subCategory()->first())) ? $this->subCategory()->first()->name : ""),
            "totalLesson"          => $this->courseLessions()->where('status', true)->count(),
            "totalDuration"        => $totalDurations,
            "isEnrolled"           => (!empty($userMasterClassData) && $userMasterClassData->pivot->joined) ,
            "isCompleted"          => (!empty($userMasterClassData) && $userMasterClassData->pivot->completed && $userMasterClassData->pivot->post_survey_completed) ,
            "isSaved"              => (!empty($userMasterClassData) && $userMasterClassData->pivot->saved) ,
            "isLiked"              => (!empty($userMasterClassData) && $userMasterClassData->pivot->liked) ,
            "likesCount"           => $this->getTotalLikes(),
            "description"          => $this->instructions,
            "currentLessonId"      => (!empty($surrentUserLession)) ? $surrentUserLession->id : 0,
            "statusText"           => $statusText,
            "trailer"              => $this->when($this->has_trailer, $trailerData),
            "isAllLessonCompleted" => $isAllLessonCompleted,
            'lessons'              => $this->when($xDeviceOs == config('zevolifesettings.PORTAL'), $lessons),
        ];

        $courseMembers = $this->courseUserLogs()
            ->leftJoin('user_team', 'user_team.user_id', '=', 'users.id')
            ->where('user_course.joined', 1)
            ->where('user_team.company_id', $user->company()->first()->getKey())
            ->get()
            ->count();

        if (isset($mappedGroup) && ($courseMembers >= 2) && (!empty($userMasterClassData) && $userMasterClassData->pivot->joined)) {
            $isMember = $mappedGroup->members()
                ->wherePivot('group_id', $mappedGroup->getKey())
                ->wherePivot('user_id', $user->getKey())
                ->first();

            $isReported = $mappedGroup->groupReports()
                ->wherePivot('group_id', $mappedGroup->getKey())
                ->wherePivot('user_id', $user->getKey())
                ->first();

            if (!$isReported) {
                $return['groupInfo'] = [
                    'groupId'  => $mappedGroup->id,
                    'isMember' => !empty($isMember),
                ];
            }
        }

        return $return;
    }
}
