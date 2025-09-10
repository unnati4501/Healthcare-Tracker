<?php

namespace App\Http\Resources\V1;

use App\Http\Traits\ProvidesAuthGuardTrait;
use Illuminate\Http\Resources\Json\JsonResource;

class MeditationCoachResource extends JsonResource
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
        return $this->getCoachData() + [
            'rating'      => round($this->Avgratings),
            'reviews'     => (int) $this->Totalreview,
            'meditations' => $this->totalMeditation,
        ];
    }
}
