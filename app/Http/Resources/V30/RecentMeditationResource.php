<?php

namespace App\Http\Resources\V30;

use App\Http\Traits\ProvidesAuthGuardTrait;
use Illuminate\Http\Resources\Json\JsonResource;

class RecentMeditationResource extends JsonResource
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
        $user           = $this->user();
        $xDeviceOs      = strtolower(request()->header('X-Device-Os', ""));
        $loggedUserLog  = $this->trackUserLogs()->wherePivot('user_id', $user->getKey())->first();
        $typeArray      = config('zevolifesettings.meditation_track_list');
        $w              = 640;
        $h              = 1280;
        $imagew         = 640;
        $imageh         = 1280;
        $youtubeBaseUrl = config('zevolifesettings.youtubeappurl');
        $vimeoBaseUrl   = config('zevolifesettings.vimeoappurl');
        if ($xDeviceOs == config('zevolifesettings.PORTAL')) {
            $w              = 1000;
            $h              = 800;
            $imagew         = 800;
            $imageh         = 800;
            $youtubeBaseUrl = config('zevolifesettings.youtubeembedurl');
            $vimeoBaseUrl   = config('zevolifesettings.vimeoembedurl');
        }
        $returnData                     = [];
        $returnData['id']               = $this->id;
        $returnData['title']            = $this->title;
        $returnData['subcategory']      = ["id" => $this->tracksubcategory->id, "name" => $this->tracksubcategory->name];
        $returnData['isPremium']        = (($this->is_premium) ? true : false);
        $returnData['totalDuration']    = $this->duration;
        $returnData['completedDuration']= ((!empty($this->duration_listened)) ? $this->duration_listened : 0);
        $returnData['likes']            = $this->totalLikes;
        $returnData['isLiked']          = ((!empty($loggedUserLog) && $loggedUserLog->pivot->liked) ? true : false);
        $returnData['isFavorited']      = ((!empty($loggedUserLog) && $loggedUserLog->pivot->favourited) ? true : false);
        $returnData['isSaved']          = ((!empty($loggedUserLog) && $loggedUserLog->pivot->saved) ? true : false);
        //$returnData['type']           = $this->type;
        $returnData['meditationType']   = $typeArray[$this->type];
        $returnData['audioType']        = $this->audio_type;
        $returnData['user']             = $this->getCoachData();
        //$returnData['image']          = $this->getMediaData('background', ['w' => $w, 'h' => $h, 'zc' => 3]);
        $returnData['image']            = $this->getMediaData('cover', ['w' => $imagew, 'h' => $imageh, 'zc' => 3]);

        if ($this->type == 1) {
            $returnData['backgroundImage'] = $this->getMediaData('background', ['w' => $w, 'h' => $h, 'zc' => 3]);
            $returnData['meditationURL']   = $this->track_url;
        } elseif ($this->type == 2 || $this->type == 3) {
            $returnData['backgroundImage'] = $this->getMediaData('track', ['w' => $w, 'h' => $h, 'conversion' => 'th_lg', 'zc' => 3]);
            $returnData['meditationURL']   = (($this->type == 3) ? $youtubeBaseUrl . $this->getFirstMedia('track')->getCustomProperty('ytid') : $this->track_url);
        } elseif ($this->type == 4) {
            $returnData['backgroundImage'] = $this->getMediaData('track', ['w' => $w, 'h' => $h, 'conversion' => 'th_lg', 'zc' => 3]);
            $returnData['meditationURL']   = $vimeoBaseUrl . $this->getFirstMedia('track')->getCustomProperty('vmid');
        }
        return $returnData;
    }
}
