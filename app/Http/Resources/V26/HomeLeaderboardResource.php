<?php

namespace App\Http\Resources\V26;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Traits\ProvidesAuthGuardTrait;

class HomeLeaderboardResource extends JsonResource
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
            'name'  => $this->name,
            'rank'  => (int) $this->rank_no,
            'steps' => (int) $this->steps,
            'image' => $this->getMediaData('logo', ['w' => 320, 'h' => 320, 'zc' => 3]),
        ];
    }
}
