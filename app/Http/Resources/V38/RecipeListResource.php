<?php declare (strict_types = 1);

namespace App\Http\Resources\V38;

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
        $user = $this->user();

        $loggedUserLog = $this->recipeUserLogs()->wherePivot('user_id', $user->getKey())->first();

        $xDeviceOs      = strtolower(request()->header('X-Device-Os', ""));
        $headerImage    = $this->getMediaData('header_image', ['w' => 800, 'h' => 800, 'zc' => 3]);

        if ($xDeviceOs == config('zevolifesettings.PORTAL')) {
            $w = 800;
            $h = 800;
        } elseif ($xDeviceOs != config('zevolifesettings.PORTAL') && isset($this->moduleFrom) && in_array($this->moduleFrom, ['savedList', 'search'])) {
            $w = 1280;
            $h = 640;
        } else {
            $w = 640;
            $h = 1280;
        }

        return [
            'id'            => $this->id,
            'title'         => $this->title,
            'direction'     => $this->when(($xDeviceOs == "portal"), !is_null($this->description) ? $this->description : ''),
            'creator'       => $this->when(($xDeviceOs == "portal"), $this->getCreatorData()),
            'chef'          => $this->getChefData(),
            'calories'      => $this->calories,
            'cookingTime'   => (int)$this->cooking_time,
            'isLiked'       => $this->when(($xDeviceOs == "portal"), ((!empty($loggedUserLog) && $loggedUserLog->pivot->liked))),
            'isSaved'       => $this->when(($xDeviceOs == "portal"), ((!empty($loggedUserLog) && $loggedUserLog->pivot->saved))),
            'likeCount'     => $this->when(($xDeviceOs == "portal"), $this->getTotalLikes()),
            'image'         => $this->getMediaData('logo', ['w' => $w, 'h' => $h, 'zc' => 3]),
            'status'        => $this->when(($xDeviceOs == "portal"), $this->status),
            'statusDisplay' => $this->when(($xDeviceOs == "portal"), (($this->status == 1) ? trans('labels.recipe.approved') : trans('labels.recipe.unapproved'))),
            'headerImage'   => $this->when(($xDeviceOs != "portal"), $headerImage),
            'tag'           => $this->when(($xDeviceOs != "portal" && !empty($this->caption)), $this->caption)
        ];
    }
}
