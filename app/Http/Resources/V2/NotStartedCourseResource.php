<?php

namespace App\Http\Resources\V2;

use App\Http\Traits\ProvidesAuthGuardTrait;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class NotStartedCourseResource extends JsonResource
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
        $user    = $this->user();

        $lession = $this->join("course_weeks", "course_weeks.course_id", "=", "courses.id")
                    ->leftJoin("course_lessions", "course_lessions.course_week_id", "=", "course_weeks.id")
                    ->where("courses.id", $this->getKey())
                    ->where("course_weeks.status", 1)
                    ->where("course_lessions.is_default", false)
                    ->select('course_lessions.*')
                    ->orderBy('course_lessions.id', 'ASC')
                    ->first();

        return [
            'id'               => $this->id,
            'title'            => $this->title,
            'image'            => $this->getMediaData('logo', ['w' => 1280, 'h' => 640]),
            'isPremium'        => ($this->is_premium) ? true : false,
            'isJoined'         => ($this->pivot->joined) ? true : false,
            'purchasedOn'      => ($this->pivot->joined_on) ? Carbon::parse($this->pivot->joined_on, config('app.timezone'))->setTimezone($user->timezone)->toAtomString() : null,
            'joinedOn'         => ($this->pivot->joined_on) ? Carbon::parse($this->pivot->joined_on, config('app.timezone'))->setTimezone($user->timezone)->toAtomString() : null,
            'totalLessons'     => $this->totalCoursePublishLessionCount(),
            'completedLessons' => $user->completedLession($this->id),
            'coach'            => $this->getCreatorData(),
            'lessonId'         => !empty($lession) ? $lession->id : 0,
        ];
    }
}
