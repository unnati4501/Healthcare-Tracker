<?php

namespace App\Http\Resources\V38;

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
        $user          = $this->user();
        $loggedUserLog = $this->recipeUserLogs()->wherePivot('user_id', $user->getKey())->first();
        $xDeviceOs     = strtolower(request()->header('X-Device-Os', ""));

        $recipeSubCategories = $this->recipesubcategories()->where('status', 1)->get()->map(function ($item, $key) {
            return [
                'id'   => $item->id,
                'name' => $item->name,
            ];
        });

        $str_time = preg_replace("/^([\d]{1,2})\:([\d]{2})$/", "00:$1:$2", $this->cooking_time);
        sscanf($str_time, "%d:%d:%d", $hours, $minutes, $seconds);
        $cooking_time = $hours * 3600 + $minutes * 60 + $seconds;

        $headerImage   = $this->getMediaData('header_image', ['w' => 800, 'h' => 800, 'zc' => 3]);

        return [
            'id'            => $this->id,
            'subcategories' => $recipeSubCategories,
            'title'         => $this->title,
            'direction'     => !is_null($this->description) ? $this->description : '',
            'creator'       => $this->getCreatorData(),
            'chef'          => $this->getChefData(),
            'ingredients'   => array_values((array) json_decode($this->ingredients)),
            'nutritions'    => json_decode($this->nutritions, JSON_NUMERIC_CHECK),
            'image'         => $this->getAllMediaData('logo', ['w' => 1280, 'h' => 640, 'zc' => 3]),
            'serves'        => $this->servings,
            'calories'      => $this->calories,
            'cookingTime'   => $cooking_time,
            'isLiked'       => (!empty($loggedUserLog) && $loggedUserLog->pivot->liked) ,
            'isFavorited'   => (!empty($loggedUserLog) && $loggedUserLog->pivot->favourited) ,
            'isSaved'       => (!empty($loggedUserLog) && $loggedUserLog->pivot->saved) ,
            'totalLikes'    => $this->getTotalLikes(),
            'status'        => $this->status,
            'statusDisplay' => (($this->status == 1) ? trans('labels.recipe.approved') : trans('labels.recipe.unapproved')),
            'headerImage'   => $this->when(($xDeviceOs != "portal"), $headerImage),
            'tag'           => $this->when(($this['tag']!= "" && $xDeviceOs != "portal"), ucfirst($this['tag']))
        ];
    }
}
