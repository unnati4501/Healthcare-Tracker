<?php

namespace App\Http\Resources\V1;

use App\Http\Traits\ProvidesAuthGuardTrait;
use Illuminate\Http\Resources\Json\JsonResource;

class HomeMeditationResource extends JsonResource
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
        /** @var User $user */
        $user = $this->user();

        return [
            'id'                => $this->id,
            'title'             => $this->title,
            'image'             => $this->getMediaData('cover', ['w' => 1280, 'h' => 640]),
            'category'          => $this->getSubCategoryData(),
            'totalDuration'     => $this->duration,
            'completedDuration' => $this->pivot->duration_listened,
        ];
    }
}
