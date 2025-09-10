<?php

namespace App\Http\Resources\V10;

use App\Http\Traits\ProvidesAuthGuardTrait;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryWiseMasterClassResource extends JsonResource
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
        $xDeviceOs = strtolower($request->header('X-Device-Os', ""));
        $height    = 1280;
        $width     = 640;
        if ($xDeviceOs == config('zevolifesettings.PORTAL')) {
            $height = 600;
            $width  = 400;
        }
        return [
            'id'          => $this->id,
            'title'       => $this->title,
            'description' => $this->when($xDeviceOs == config('zevolifesettings.PORTAL'), $this->instructions),
            'creator'     => $this->getCreatorData(),
            'image'       => $this->getMediaData('logo', ['w' => $height, 'h' => $width, 'zc' => 3]),
        ];
    }
}
