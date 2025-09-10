<?php

namespace App\Http\Resources\V10;

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
        if ($request->header('X-Device-Os') == config('zevolifesettings.PORTAL')) {
            $collectionType = Str::contains($request->path(), 'onboard') ? 'slideImage' : 'slideImagePortal';
        }
        return [
            'id'      => $this->id,
            'content' => $this->content,
            'image'   => $this->getMediaData($collectionType, ['ct' => 1, 'zc' => 3]),
        ];
    }
}
