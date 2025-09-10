<?php declare (strict_types = 1);

namespace App\Http\Resources\V1;

use App\Http\Traits\ProvidesAuthGuardTrait;
use Illuminate\Http\Resources\Json\JsonResource;

class RecipeListResource extends JsonResource
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
        // $recipeCategories = $this->recipeCategories()->get();
        $user = $this->user();

        $loggedUserLog = $this->recipeUserLogs()->wherePivot('user_id', $user->getKey())->first();

        return [
            'id'        => $this->id,
            'title'     => $this->title,
            'creator'   => $this->getCreatorData(),
            'chef'      => $this->getChefData(),
            'isLiked'   => (!empty($loggedUserLog) && $loggedUserLog->pivot->liked) ,
            'isSaved'   => (!empty($loggedUserLog) && $loggedUserLog->pivot->saved) ,
            'likeCount' => $this->getTotalLikes(),
            'image'         => $this->getMediaData('logo', ['w' => 640, 'h' => 1280, 'zc' => 3]),
        ];
    }
}
