<?php

namespace App\Http\Resources\V1;

use App\Http\Traits\ProvidesAuthGuardTrait;
use App\Models\Course;
use Illuminate\Http\Resources\Json\JsonResource;

class HomeCourseResource extends JsonResource
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

        $course = Course::find($this->pivot->course_id);

        return [
            'id'               => $course->id,
            'title'            => $course->title,
            'image'            => $course->getMediaData('logo', ['w' => 320, 'h' => 320, 'zc' => 3]),
            'totalLessons'     => $course->courseLessions()->where('is_default', false)->count(),
            'completedLessons' => $user->completedLession($course->id),
            'runningLessonId'  => $user->runningLession($course->id),
        ];
    }
}
