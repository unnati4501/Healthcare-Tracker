<?php

namespace App\Http\Resources\V38;

use App\Http\Traits\ProvidesAuthGuardTrait;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Podcast;

class CategoryWisePodcastResource extends JsonResource
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
        $podcast       = Podcast::find($this->id);
        $user          = $this->user();
        $loggedUserLog = $podcast->podcastUserLogs()->wherePivot('user_id', $user->getKey())->first();
        $xDeviceOs     = strtolower(request()->header('X-Device-Os', ""));
 
        $imagew         = 800;
        $imageh         = 800;
        if ($xDeviceOs == config('zevolifesettings.PORTAL')) {
            $imagew         = 800;
            $imageh         = 800;
        }

        return [
            'id'                => $this->id,
            'image'             => $podcast->getMediaData('logo', ['w' => $imagew, 'h' => $imageh, 'zc' => 3]),
            'subcategory'       => ["id" => $podcast->podcastsubcategory->id, "name" => $podcast->podcastsubcategory->name],
            'title'             => $this->title,
            'totalDuration'     => (int)$this->duration,
            'completedDuration' => ((!empty($this->duration_listened)) ? (int)$this->duration_listened : 0),
            'likes'             => (int)$this->totalLikes,
            'isLiked'           => ((!empty($loggedUserLog) && $loggedUserLog->pivot->liked)),
            'isFavorited'       => ((!empty($loggedUserLog) && $loggedUserLog->pivot->favourited)),
            'isSaved'           => ((!empty($loggedUserLog) && $loggedUserLog->pivot->saved)),
            'user'              => $podcast->getCoachData(),
            'viewCount'         => ((!empty($this->view_count)) ? (int)$this->view_count : 0),
            'podcastURL'         => $podcast->track_url,
            'tag'               => $this->when(($xDeviceOs != "portal" && !empty($this->caption)), $this->caption),
        ];
    }
}
