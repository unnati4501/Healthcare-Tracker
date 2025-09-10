<?php

namespace App\Http\Resources\V6;

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
            'creator'          => $course->getCreatorData(),
            'totalLessons'     => $course->courseLessions()->where('status', true)->count(),
            'completedLessons' => $user->completedLession($course->id),
            "category"        => array("id" => (!empty($course->subCategory()->first())) ? $course->subCategory()->first()->id : "", "name" => (!empty($course->subCategory()->first())) ? $course->subCategory()->first()->name : ""),
        ];
    }
}
