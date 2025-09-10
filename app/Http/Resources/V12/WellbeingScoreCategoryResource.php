<?php

namespace App\Http\Resources\V12;

use App\Http\Traits\ProvidesAuthGuardTrait;
use Illuminate\Http\Resources\Json\JsonResource;

class WellbeingScoreCategoryResource extends JsonResource
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
        $w = 600;
        $h = 600;

        return [
            'name'  => $this->display_name,
            'score' => $this->percentage,
            'color' => portalSurveyColorCode($this->percentage),
            'image' => [
                'width'  => $w,
                'height' => $h,
                'url'    => $this->category->getLogo(['w' => $w, 'h' => $h, 'zc' => 3]),
            ],
        ];
    }
}
