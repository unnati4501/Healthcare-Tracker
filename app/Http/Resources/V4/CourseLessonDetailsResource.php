<?php

namespace App\Http\Resources\V4;

use App\Http\Traits\ProvidesAuthGuardTrait;
use Illuminate\Http\Resources\Json\JsonResource;

class CourseLessonDetailsResource extends JsonResource
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

        $userCourseLessonData = $this->courseUserLessonData($user->getKey());

        $video                  = $this->getMediaData('video', ['w' => 1280, 'h' => 640]);
        $youtube                = $this->getMediaData('youtube', ['w' => 1280, 'h' => 640]);
        $courseData             = array();
        $courseData['id']       = $this->course->id;
        $courseData['title']    = $this->course->title;
        $courseData['students'] = $this->course->getTotalStudents();
        $courseData['labelTag'] = (!empty($this->course->subCategory()->first())) ? $this->course->subCategory()->first()->name : "";
        $courseData['coach']    = $this->course->getCreatorData();

        return [
            'id'          => $this->id,
            'title'       => $this->title,
            'image'       => $this->getMediaData('logo', ['w' => 1280, 'h' => 640]),
            'video'       => (!empty($video)) ? $video : (object) array(),
            'youTube'     => (!empty($youtube)) ? $youtube : (object) array(),
            'description' => (!empty($this->description)) ? $this->description : "",
            'isRunning'   => (!empty($userCourseLessonData) && $userCourseLessonData->pivot->status == "started") ? true : false,
            'isCompleted' => (!empty($userCourseLessonData) && $userCourseLessonData->pivot->status == "completed") ? true : false,
            'course'      => $courseData,
        ];
    }
}
