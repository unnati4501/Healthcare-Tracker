<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class CategoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $level = 'beginner';
        $user  = \Auth::guard('api')->user();
        
        if (!empty($user)) {
            $expertiseLevel = $user->expertiseLevels()->wherePivot('user_id', $user->id)->wherePivot('category_id', $this->id)->first();
            $level = (!empty($expertiseLevel)) ? $expertiseLevel->pivot->expertise_level : 'beginner';
        }

        return [
            'id'         => $this->id,
            'name'       => $this->name,
            'short_name' => $this->short_name,
            'level'      => $level,
            'image'      => [
                'url'    => $this->logo,
                'width'  => 200,
                'height' => 200,
            ],
        ];
    }
}
