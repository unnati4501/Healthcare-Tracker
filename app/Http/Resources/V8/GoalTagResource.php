<?php

namespace App\Http\Resources\V8;

use App\Http\Traits\ProvidesAuthGuardTrait;
use Illuminate\Http\Resources\Json\JsonResource;

class GoalTagResource extends JsonResource
{
    use ProvidesAuthGuardTrait;

    protected static $userSelectedGoals;

    public static function using($using = [])
    {
        static::$userSelectedGoals = $using;
    }


    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $isSelected = false;
        if (in_array($this->id, static::$userSelectedGoals)) {
            $isSelected = true;
        }
        return [
            'id'         => $this->id,
            'title'      => $this->title,
            'image'      => [
                'width'  => 320,
                'height' => 320,
                'url'    => $this->getLogo(['w' => 320, 'h' => 320, 'ct' => 1, 'zc' => 3]),
            ],
            'isSelected' => $isSelected,
        ];
    }
}
