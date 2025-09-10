<?php declare (strict_types = 1);

namespace App\Http\Resources\V11;

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

        $str_time = preg_replace("/^([\d]{1,2})\:([\d]{2})$/", "00:$1:$2", $this->cooking_time);
        sscanf($str_time, "%d:%d:%d", $hours, $minutes, $seconds);
        $cooking_time = $hours * 3600 + $minutes * 60 + $seconds;

        $xDeviceOs      = strtolower(request()->header('X-Device-Os', ""));

        $w = 640;
        $h = 1280;
        if ($xDeviceOs == config('zevolifesettings.PORTAL')) {
            $w = 800;
            $h = 800;
        }

        return [
            'id'            => $this->id,
            'title'         => $this->title,
            'direction'     => !is_null($this->description) ? $this->description : '',
            'creator'       => $this->getCreatorData(),
            'chef'          => $this->getChefData(),
            'calories'      => $this->calories,
            'cookingTime'   => $cooking_time,
            'isLiked'       => ((!empty($loggedUserLog) && $loggedUserLog->pivot->liked) ),
            'isSaved'       => ((!empty($loggedUserLog) && $loggedUserLog->pivot->saved) ),
            'likeCount'     => $this->getTotalLikes(),
            'image'         => $this->getMediaData('logo', ['w' => $w, 'h' => $h, 'zc' => 3]),
            'status'        => $this->status,
            'statusDisplay' => (($this->status == 1) ? trans('labels.recipe.approved') : trans('labels.recipe.unapproved')),
        ];
    }
}
