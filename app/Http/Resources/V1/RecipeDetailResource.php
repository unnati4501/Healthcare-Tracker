<?php

namespace App\Http\Resources\V1;

use App\Http\Traits\ProvidesAuthGuardTrait;
use Illuminate\Http\Resources\Json\JsonResource;

class RecipeDetailResource extends JsonResource
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
        $user = $this->user();

        $loggedUserLog = $this->recipeUserLogs()->wherePivot('user_id', $user->getKey())->first();

        $recipeCategories = $this->recipeCategories->map(function ($item, $key) {
            return [
                'id'    => $item->id,
                'title' => $item->display_name,
            ];
        })->all();

        $str_time = preg_replace("/^([\d]{1,2})\:([\d]{2})$/", "00:$1:$2", $this->cooking_time);
        sscanf($str_time, "%d:%d:%d", $hours, $minutes, $seconds);
        $cooking_time = $hours * 3600 + $minutes * 60 + $seconds;

        return [
            'id'          => $this->id,
            'categories'  => $recipeCategories,
            'title'       => $this->title,
            'direction'   => !is_null($this->description) ? $this->description : '',
            'creator'     => $this->getCreatorData(),
            'chef'        => $this->getChefData(),
            'ingredients' => array_values((array) json_decode($this->ingredients)),
            'nutritions'  => json_decode($this->nutritions, JSON_NUMERIC_CHECK),
            'image'         => $this->getAllMediaData('logo', ['w' => 1280, 'h' => 640]),
            'serves'      => $this->servings,
            'calories'    => $this->calories,
            'cookingTime' => $cooking_time,
            'isLiked'     => (!empty($loggedUserLog) && $loggedUserLog->pivot->liked) ,
            'isSaved'     => (!empty($loggedUserLog) && $loggedUserLog->pivot->saved) ,
            'totalLikes'  => $this->getTotalLikes(),
        ];
    }
}
