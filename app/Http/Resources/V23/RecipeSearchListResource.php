<?php declare (strict_types = 1);

namespace App\Http\Resources\V23;

use App\Http\Traits\ProvidesAuthGuardTrait;
use Illuminate\Http\Resources\Json\JsonResource;

class RecipeSearchListResource extends JsonResource
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
        $media = "";
        if (isset($this->moduleFrom) && in_array($this->moduleFrom, ['savedList', 'search'])) {
            $media = $this->getMediaData('logo', ['w' => 1280, 'h' => 640, 'zc' => 3]);
        } else {
            $media = $this->getMediaData('logo', ['w' => 1560, 'h' => 1030, 'zc' => 3]);
        }

        return [
            'id'          => $this->id,
            'title'       => $this->title,
            'image'       => $media,
            'calories'    => $this->calories,
            'cookingTime' => $this->cooking_time,
            'chef'        => $this->getChefData(),
        ];
    }
}
