<?php

namespace App\Http\Resources\V6;

use App\Http\Traits\ProvidesAuthGuardTrait;
use Illuminate\Http\Resources\Json\JsonResource;

class EAPDetailResource extends JsonResource
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
            'id'          => $this->id,
            'title'       => $this->title,
            'image'       => $this->getMediaData('logo', ['w' => 500, 'h' => 500, 'zc' => 3]),
            'telephone'   => "+{$this->telephone}",
            'email'       => $this->email,
            'website'     => $this->website,
            'description' => $this->description,
        ];
    }
}
