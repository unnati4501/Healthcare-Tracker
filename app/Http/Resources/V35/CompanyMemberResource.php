<?php

namespace App\Http\Resources\V35;

use App\Http\Traits\ProvidesAuthGuardTrait;
use Illuminate\Http\Resources\Json\JsonResource;

class CompanyMemberResource extends JsonResource
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
            'id'        => $this['id'],
            'name'      => $this['name'],
            'image'     => $this->getMediaData('logo', ['w' => 512, 'h' => 512]),
        ];
    }
}
