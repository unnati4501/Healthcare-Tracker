<?php

namespace App\Http\Resources\V6;

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
        $user          = $this->user();
        $loggedUserLog = $this->trackUserLogs()->wherePivot('user_id', $user->getKey())->first();
        $typeArray     = array(1 => "AUDIO", 2 => "VIDEO", 3 => "YOUTUBE");

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
            'meditationURL'     => (($this->type == 3) ? $this->getFirstMedia('track')->name : $this->track_url),
            'meditationType'    => $typeArray[$this->type],
            'user'              => $this->getCoachData(),
            'viewCount'         => ((!empty($this->view_count)) ? $this->view_count : 0),
        ];

        if ($this->type == 1) {
            $response['backgroundImage'] = $this->getMediaData('background', ['w' => 640, 'h' => 1280, 'zc' => 3]);
        } elseif ($this->type == 2 || $this->type == 3) {
            $response['backgroundImage'] = $this->getMediaData('track', ['w' => 640, 'h' => 1280, 'conversion' => 'th_lg', 'zc' => 3]);
        }

        return $response;
    }
}
