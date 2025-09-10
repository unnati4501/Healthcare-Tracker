<?php

namespace App\Http\Resources\V6;

use App\Http\Traits\ProvidesAuthGuardTrait;
use App\Models\Group;
use App\Models\UserLession;
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
        $user = $this->user();

        $totalDuration       = $this->courseTotalDurarion();
        $userMasterClassData = $this->courseUserLogs()->wherePivot("user_id", $user->id)->first();

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

        $return = [
            'id'                   => $this->id,
            'title'                => $this->title,
            'creator'              => $this->getCreatorData(),
            'image'                => $this->getMediaData('logo', ['w' => 1280, 'h' => 640]),
            "category"             => array("id" => (!empty($this->subCategory()->first())) ? $this->subCategory()->first()->id : "", "name" => (!empty($this->subCategory()->first())) ? $this->subCategory()->first()->name : ""),
            "totalLesson"          => $this->courseLessions()->where('status', true)->count(),
            "totalDuration"        => (!empty($totalDuration)) ? convertSecondToMinute($totalDuration->totalDurarion) : 0,
            "isEnrolled"           => (!empty($userMasterClassData) && $userMasterClassData->pivot->joined) ? true : false,
            "isCompleted"          => (!empty($userMasterClassData) && $userMasterClassData->pivot->completed && $userMasterClassData->pivot->post_survey_completed) ? true : false,
            "isSaved"              => (!empty($userMasterClassData) && $userMasterClassData->pivot->saved) ? true : false,
            "isLiked"              => (!empty($userMasterClassData) && $userMasterClassData->pivot->liked) ? true : false,
            "likesCount"           => $this->getTotalLikes(),
            "description"          => $this->instructions,
            "currentLessonId"      => (!empty($surrentUserLession)) ? $surrentUserLession->id : 0,
            "statusText"           => $statusText,
            "trailer"              => $this->when($this->has_trailer, $trailerData),
            "isAllLessonCompleted" => $isAllLessonCompleted,
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
