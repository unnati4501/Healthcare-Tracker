<?php

namespace App\Http\Resources\V2;

use App\Http\Traits\ProvidesAuthGuardTrait;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class FeedResource extends JsonResource
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

        $loggedUserLog = $this->feedUserLogs()->wherePivot('user_id', $user->getKey())->first();

        $video   = $this->getMediaData('video', ['w' => 1280, 'h' => 640]);
        $youtube = $this->getMediaData('youtube', ['w' => 1280, 'h' => 640]);

        return [
            'id'          => $this->id,
            'title'       => $this->title,
            'description' => $this->description,
            'image'       => $this->getMediaData('logo', ['w' => 1280, 'h' => 640]),
            'video'       => (!empty($video)) ? $video : (object) array(),
            'youTube'     => (!empty($youtube)) ? $youtube : (object) array(),
            'creator'     => $this->getCreatorData(),
            'createdAt'   => Carbon::parse($this->created_at, config('app.timezone'))->setTimezone($this->timezone)->toAtomString(),
            'labelTag'    => ucwords($this->tag),
            'likes'       => $this->getTotalLikes(),
            'isLiked'     => (!empty($loggedUserLog) && $loggedUserLog->pivot->liked) ? true : false,
            'isSaved'     => (!empty($loggedUserLog) && $loggedUserLog->pivot->saved) ? true : false,
        ];
    }
}
