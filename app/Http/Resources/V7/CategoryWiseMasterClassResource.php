<?php

namespace App\Http\Resources\V7;

use App\Http\Traits\ProvidesAuthGuardTrait;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryWiseMasterClassResource extends JsonResource
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

        $cousreLog = $user->courseLogs()->where('course_id', $this->id)->first();

        return [
            'id'               => $this->id,
            'title'            => $this->title,
            'creator'          => $this->getCreatorData(),
            'totalLessons'     => $this->courseLessions()->where('status', true)->count(),
            'completedLessons' => $user->completedLession($this->id),
            'joinedOn'         => $cousreLog->pivot->joined_on,
            'completedOn'      => $this->when($cousreLog->pivot->completed_on, $cousreLog->pivot->completed_on),
            'image'            => $this->getMediaData('logo', ['w' => 1280, 'h' => 640, 'zc' => 3]),
        ];
    }
}
