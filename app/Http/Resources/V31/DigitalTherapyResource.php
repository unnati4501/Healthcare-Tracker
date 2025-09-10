<?php declare (strict_types = 1);

namespace App\Http\Resources\V31;

use App\Http\Traits\ProvidesAuthGuardTrait;
use Illuminate\Http\Resources\Json\JsonResource;

class DigitalTherapyResource extends JsonResource
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
        $w = 800;
        $h = 800;
        return [
            'id'   => $this->id,
            'name' => $this->name,
            'logo' => $this->getMediaData('logo', ['w' => $w, 'h' => $h, 'zc' => 3]),
            'icon' => $this->getMediaData('icon', ['w' => $w, 'h' => $h, 'zc' => 3]),
        ];
    }
}
