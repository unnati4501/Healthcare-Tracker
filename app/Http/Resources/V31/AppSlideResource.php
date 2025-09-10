<?php

namespace App\Http\Resources\V31;

use Illuminate\Http\Resources\Json\JsonResource;
use Str;

class AppSlideResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $collectionType = 'slideImage';
        return [
            'id'            => $this->id,
            'content'       => ($request->header('X-Device-Os') == config('zevolifesettings.PORTAL')) ? $this->portal_content : $this->content,
            'image'         => ($request->header('X-Device-Os') == config('zevolifesettings.PORTAL')) ? $this->getMediaData('portalSlideImage', ['ct' => 1, 'zc' => 3]) : $this->getMediaData($collectionType, ['ct' => 1, 'zc' => 3]),
        ];
    }
}
