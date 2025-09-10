<?php

namespace App\Http\Resources\V1;

use App\Http\Traits\ProvidesAuthGuardTrait;
use Illuminate\Http\Resources\Json\JsonResource;

class ExerciseResource extends JsonResource
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
        $uom = [];
        if ($this->type == 'both') {
            $uom[] = 'meter';
            $uom[] = 'minutes';
        } else {
            $uom[] = $this->type;
        }

        return [
            'id'           => $this->id,
            'title'        => $this->title,
            'image'        => $this->getMediaData('logo', ['w' => 320, 'h' => 320]),
            'coverImage'   => $this->getMediaData('background', ['w' => 2560, 'h' => 1280]),
            'showMap'      => (($this->show_map) ),
            'showDistance' => ($this->type == 'meter' || $this->type == 'both') ?: false,
            'uom'          => $uom,
            'calories'     => $this->calories,
        ];
    }
}
