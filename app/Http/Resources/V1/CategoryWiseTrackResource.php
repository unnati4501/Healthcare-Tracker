<?php

namespace App\Http\Resources\V1;

use App\Http\Traits\ProvidesAuthGuardTrait;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryWiseTrackResource extends JsonResource
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

        $loggedUserLog = $this->trackUserLogs()->wherePivot('user_id', $user->getKey())->first();

        return [
            'id'                => $this->id,
            'image'             => $this->getMediaData('cover', ['w' => 320, 'h' => 640]),
            'backgroundImage'   => $this->getMediaData('background', ['w' => 640, 'h' => 1280, 'zc' => 3]),
            'category'          => array("id" => $this->tracksubcategory->id, "name" => $this->tracksubcategory->name),
            'title'             => $this->title,
            'isPremium'         => ($this->is_premium),
            'totalDuration'     => $this->duration,
            'completedDuration' => (!empty($this->duration_listened)) ? $this->duration_listened : 0,
            'likes'             => $this->totalLikes,
            'isLiked'           => (!empty($loggedUserLog) && $loggedUserLog->pivot->liked),
            'isFavorited'       => (!empty($loggedUserLog) && $loggedUserLog->pivot->favourited),
            'isSaved'           => (!empty($loggedUserLog) && $loggedUserLog->pivot->saved),
            'labelTag'          => ucwords($this->tag),
            'meditationURL'     => (!empty($this->track_url)) ? $this->track_url : "",
            'user'              => $this->getCoachData(),
        ];
    }
}
