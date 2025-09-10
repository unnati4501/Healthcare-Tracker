<?php

namespace App\Http\Resources\V38;

use App\Http\Traits\ProvidesAuthGuardTrait;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Podcast;

class RecentPodcastResource extends JsonResource
{
    use ProvidesAuthGuardTrait;

    /**
     * Create a new resource instance.
     *
     * @param  mixed  $resource
     * @return void
     */
    public function __construct($resource)
    {
        // Ensure you call the parent constructor
        parent::__construct($resource);
        $this->resource = $resource;
    }

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $podcast        = Podcast::find($this['id']);
        $user           = $this->user();
        $xDeviceOs      = strtolower(request()->header('X-Device-Os', ""));
        $loggedUserLog  = $podcast->podcastUserLogs()->wherePivot('user_id', $user->getKey())->first();
        $imagew         = 800;
        $imageh         = 800;
        if ($xDeviceOs == config('zevolifesettings.PORTAL')) {
            $imagew         = 800;
            $imageh         = 800;
        }
        $returnData                     = [];
        $returnData['id']               = $this['id'];
        $returnData['title']            = $this['title'];
        $returnData['subcategory']      = ["id" => $podcast->podcastsubcategory->id, "name" => $podcast->podcastsubcategory->name];
        $returnData['totalDuration']    = (int)$this['duration'];
        $returnData['completedDuration']= ((!empty($this['duration_listened'])) ? (int)$this['duration_listened'] : 0);
        $returnData['likes']            = (int)$this['totalLikes'];
        $returnData['isLiked']          = ((!empty($loggedUserLog) && $loggedUserLog->pivot->liked));
        $returnData['isFavorited']      = ((!empty($loggedUserLog) && $loggedUserLog->pivot->favourited));
        $returnData['isSaved']          = ((!empty($loggedUserLog) && $loggedUserLog->pivot->saved));
        $returnData['user']             = $podcast->getCoachData();
        $returnData['image']            = $podcast->getMediaData('logo', ['w' => $imagew, 'h' => $imageh, 'zc' => 3]);
        $returnData['podcastURL']       = $podcast->track_url;
        $returnData['tag']              = $this->when(($xDeviceOs != "portal" && !empty($this['caption'])), $this['caption']);

        return $returnData;
    }
}
