<?php

namespace App\Http\Resources\V8;

use Illuminate\Http\Resources\Json\JsonResource;

class RecommendationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $image     = array();
        $xDeviceOs = strtolower($request->header('X-Device-Os', ""));
        $type      = $this->goalContentType;
        if ($this->goalContentType == "meditation") {
            $image = $this->getMediaData('cover', ['w' => 1280, 'h' => 640, 'zc' => 3]);
        } elseif (in_array($this->goalContentType, ['feed', 'feed_youtube', 'feed_video', 'feed_audio', 'feed_vimeo'])) {
            $type  = 'feed';
            $image = $this->getMediaData('featured_image', ['w' => 1280, 'h' => 640, 'zc' => 3]);
        } elseif ($this->goalContentType == "recipe") {
            $image = $this->getMediaData('logo', ['w' => 1280, 'h' => 640, 'zc' => 3]);
        } elseif ($this->goalContentType == "masterclass") {
            $image = $this->getMediaData('logo', ['w' => 1280, 'h' => 640, 'zc' => 3]);
        } elseif ($this->goalContentType == "webinar") {
            $image = $this->getMediaData('logo', ['w' => 1280, 'h' => 640, 'zc' => 3]);
        }

        return [
            'id'          => $this->id,
            'name'        => $this->title,
            'type'        => ((in_array($this->goalContentType, ['feed', 'feed_youtube', 'feed_video', 'feed_audio', 'feed_vimeo'])) ? 'feed' : $this->goalContentType),
            'typeImage'   => [
                'url'    => getStaticAlertIconUrl($this->goalContentType),
                'width'  => 0,
                'height' => 0,
            ],
            'image'       => $image,
            'category'    => (!empty($this->sub_category_id)) ? $this->sub_category_id : 0,
            'deepLinkURI' => $this->when($xDeviceOs == config('zevolifesettings.PORTAL'), portalDeeplinkURL($type, '', $this->deep_link_uri)),
        ];
    }
}
