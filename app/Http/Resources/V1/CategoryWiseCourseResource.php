<?php

namespace App\Http\Resources\V1;

use App\Http\Traits\ProvidesAuthGuardTrait;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryWiseCourseResource extends JsonResource
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

        $loggedUserLog = $this->courseUserLogs()->wherePivot('user_id', $user->getKey())->first();

        $ratings = $this->courseAverageRatings();

        $totalDurarion = $this->courseTotalDurarion();

        return [
            'id'          => $this->id,
            'title'       => $this->title,
            'image'       => $this->getMediaData('logo', ['w' => 1280, 'h' => 640]),
            'rating'      => $ratings,
            'duration'    => (!empty($totalDurarion)) ? (int) $totalDurarion->totalDurarion : 0,
            'isSaved'     => (!empty($loggedUserLog) && $loggedUserLog->pivot->saved),
            'isLiked'     => (!empty($loggedUserLog) && $loggedUserLog->pivot->liked),
            'isJoined'    => (!empty($loggedUserLog) && $loggedUserLog->pivot->joined),
            'likes'       => $this->getTotalLikes(),
            'labelTag'    => ucwords($this->tag),
            'isPremium'   => ($this->is_premium),
            'moduleCount' => (!empty($this->moduleCount)) ? (int) $this->moduleCount : 0,
            'coach'       => $this->getCreatorData(),
        ];
    }
}
