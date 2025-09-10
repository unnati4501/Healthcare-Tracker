<?php declare (strict_types = 1);

namespace App\Http\Resources\V9;

use App\Http\Traits\ProvidesAuthGuardTrait;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryWiseChallengeImageLibraryListResources extends JsonResource
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
        return [
            'id'    => $this->id,
            'image' => $this->getMediaData('image', ['w' => 1280, 'h' => 640]),
        ];
    }
}
