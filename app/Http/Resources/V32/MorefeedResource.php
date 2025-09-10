<?php

namespace App\Http\Resources\V32;

use App\Http\Traits\ProvidesAuthGuardTrait;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class MorefeedResource extends JsonResource
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
        $typeArray     = config('zevolifesettings.type_array');
        $loggedUserLog = $this->feedUserLogs()->wherePivot('user_id', $user->getKey())->first();
        $mediaData     = [];
        $hasMedia      = false;

        $xDeviceOs = strtolower(request()->header('X-Device-Os', ""));
        $w         = 1280;
        $h         = 640;
        $contentW  = 800;
        $contentH  = 800;
        if ($xDeviceOs == config('zevolifesettings.PORTAL')) {
            $w = 800;
            $h = 800;
        }
        $typeIcon = config('zevolifesettings.type_icon');
        if ($this->type != 4) {
            $mediaData = $this->getFeedMediaData();
            $hasMedia  = true;
        }

        $returnData                = [];
        $returnData['id']          = $this->id;
        $returnData['title']       = $this->title;
        $returnData['media']       = $this->when($hasMedia, $mediaData);
        $returnData['typeImage']   = [
            'url'    => getStaticAlertIconUrl($typeIcon[$this->type]),
            'width'  => 0,
            'height' => 0,
        ];
        $returnData['image']    = $this->getMediaData('featured_image', ['w' => $w, 'h' => $h]);
        $returnData['type']     = $typeArray[$this->type];

        return $returnData;
    }
}
