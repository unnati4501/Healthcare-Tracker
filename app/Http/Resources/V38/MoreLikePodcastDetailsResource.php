<?php

namespace App\Http\Resources\V38;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Podcast;

class MoreLikePodcastDetailsResource extends JsonResource
{

    public function toArray($request)
    {
        $xDeviceOs     = strtolower(request()->header('X-Device-Os', ""));

        $imagew         = 800;
        $imageh         = 800;
        if ($xDeviceOs == config('zevolifesettings.PORTAL')) {
            $imagew         = 800;
            $imageh         = 800;
        }
        
        return [
            'id'                => $this->id,
            'image'             => $this->getMediaData('logo', ['w' => $imagew, 'h' => $imageh, 'zc' => 3]),
            'title'             => $this->title,
            'totalDuration'     => $this->duration,
            'user'              => $this->getCoachData(),
            'podcastURL'        => $this->track_url,
            'tag'               => $this->when(($xDeviceOs != "portal" && !empty($this->caption) && $this->caption!= ""), $this->caption)
        ];
    }
}
