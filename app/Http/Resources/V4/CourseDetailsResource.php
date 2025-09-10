<?php

namespace App\Http\Resources\V4;

use App\Http\Resources\V4\CategoryWiseCourseResource;
use App\Http\Traits\ProvidesAuthGuardTrait;
use Illuminate\Http\Resources\Json\JsonResource;

class CourseDetailsResource extends JsonResource
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
        $courseObj = $this->leftJoin("sub_categories", "sub_categories.id", "=", "courses.sub_category_id")
                    ->where("courses.id", $this->id)
                    ->select("courses.*", "sub_categories.name as courseSubCategory")
                    ->first();
        $courseData = new CategoryWiseCourseResource($courseObj);

        $introduction = $this->defaultLesstion();

        $userCourseLessonData            = $introduction->courseUserLessonData($user->getKey());
        $introductionData                = array();
        $introductionData['lessonId']    = $introduction->id;
        $introductionData['title']       = $introduction->title;
        $introductionData['isLocked']    = false;
        $introductionData['isCompleted'] = (!empty($userCourseLessonData) && $userCourseLessonData->pivot->status == "completed") ? true : false;
        $introductionData['isRunning']   = (!empty($userCourseLessonData) && $userCourseLessonData->pivot->status == "started") ? true : false;

        $timelineData = $this->getTimeLineData($user);

        return [
            'course'       => $courseData,
            "totalLessons" => $this->totalCoursePublishLessionCount(),
            "students"     => $this->getTotalStudents(),
            "introduction" => $introductionData,
            "timeline"     => $timelineData,
        ];
    }
}
