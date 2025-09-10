<?php

namespace App\Http\Resources\V4;

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
        $user = $this->user();

        $loggedUserLog = $this->trackUserLogs()->wherePivot('user_id', $user->getKey())->first();

        $response = [
            'id'                => $this->id,
            'image'             => $this->getMediaData('cover', ['w' => 320, 'h' => 640]),
            'subcategory'       => ["id" => $this->tracksubcategory->id, "name" => $this->tracksubcategory->name],
            'title'             => $this->title,
            'isPremium'         => (($this->is_premium) ? true : false),
            'totalDuration'     => $this->duration,
            'completedDuration' => ((!empty($this->duration_listened)) ? $this->duration_listened : 0),
            'likes'             => $this->totalLikes,
            'isLiked'           => ((!empty($loggedUserLog) && $loggedUserLog->pivot->liked) ? true : false),
            'isFavorited'       => ((!empty($loggedUserLog) && $loggedUserLog->pivot->favourited) ? true : false),
            'isSaved'           => ((!empty($loggedUserLog) && $loggedUserLog->pivot->saved) ? true : false),
            'meditationURL'     => (!empty($this->track_url)) ? $this->track_url : "",
            'meditationType'    => $this->type,
            'user'              => $this->getCoachData(),
        ];

        if ($this->type == 1) {
            $response['backgroundImage'] = $this->getMediaData('background', ['w' => 640, 'h' => 1280, 'zc' => 3]);
        }

        return $response;
    }
}
