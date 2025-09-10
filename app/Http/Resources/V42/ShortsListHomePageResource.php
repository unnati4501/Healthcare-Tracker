<?php declare (strict_types = 1);

namespace App\Http\Resources\V42;

use App\Http\Traits\ProvidesAuthGuardTrait;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Shorts;

class ShortsListHomePageResource extends JsonResource
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
        $totalDuration       = $this->duration;
        $user                = $this->user();
        $loggedUserLog       = $this->shortsUserLogs()->wherePivot('user_id', $user->getKey())->first();
        $w                   = config('zevolifesettings.imageConversions.shorts.header_image.width');
        $h                   = config('zevolifesettings.imageConversions.shorts.header_image.height');
        $totalDuration       = (!empty($totalDuration)) ? (int) $totalDuration : 0;
        $totalDurationInMins = (!empty($totalDuration)) ? convertSecondToMinute($totalDuration) : 0;
        $headerImage         = $this->getMediaData('header_image', ['w' => $w, 'h' => $h, 'zc' => 3]);
        return [
            'id'                => $this->id,
            'title'             => $this->title,
            'creator'           => $this->getCreatorData(),
            'subcategory'       => ["id" => $this->shortssubcategory->id, "name" => $this->shortssubcategory->name],
            'viewCount'         => ((!empty($this->view_count)) ? $this->view_count : 0),
            "likesCount"        => $this->getTotalLikes(),
            'isLiked'           => (!empty($loggedUserLog) && $loggedUserLog->pivot->liked),
            'isSaved'           => (!empty($loggedUserLog) && $loggedUserLog->pivot->saved),
            'isFavorited'       => (!empty($loggedUserLog) && $loggedUserLog->pivot->favourited),
            'duration'          => $totalDuration,
            'durationInMinutes' => $totalDurationInMins,
            'media'             => $this->getShortsMediaData(),
            'headerImage'       => $headerImage,
        ];
    }
}
