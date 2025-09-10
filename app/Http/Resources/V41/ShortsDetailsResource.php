<?php declare (strict_types = 1);

namespace App\Http\Resources\V41;

use App\Http\Traits\ProvidesAuthGuardTrait;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Shorts;

class ShortsDetailsResource extends JsonResource
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
        $short               = Shorts::find($this['id']);
        $totalDuration       = $short['duration'];
        $user                = $this->user();
        $loggedUserLog       = $short->shortsUserLogs()->wherePivot('user_id', $user->getKey())->first();
        $w                   = config('zevolifesettings.imageConversions.shorts.header_image.width');
        $h                   = config('zevolifesettings.imageConversions.shorts.header_image.height');
        $totalDuration       = (!empty($totalDuration)) ? (int) $totalDuration : 0;
        $totalDurationInMins = (!empty($totalDuration)) ? convertSecondToMinute($totalDuration) : 0;
        $headerImage         = $short->getMediaData('header_image', ['w' => $w, 'h' => $h, 'zc' => 3]);
        return [
            'id'                => $this['id'],
            'title'             => $this['title'],
            'description'       => $this['description'],
            'creator'           => $short->getCreatorData(),
            'subcategory'       => ["id" => $short->shortssubcategory->id, "name" => $short->shortssubcategory->name],
            'viewCount'         => ((!empty($this['view_count'])) ? $this['view_count'] : 0),
            "likesCount"        => $short->getTotalLikes(),
            'isLiked'           => (!empty($loggedUserLog) && $loggedUserLog->pivot->liked),
            'isSaved'           => (!empty($loggedUserLog) && $loggedUserLog->pivot->saved),
            'isFavorited'       => (!empty($loggedUserLog) && $loggedUserLog->pivot->favourited),
            'isViewed'          => (!empty($loggedUserLog) && ($loggedUserLog->pivot->view_count >= 2)),
            'duration'          => $totalDuration,
            'durationInMinutes' => $totalDurationInMins,
            'media'             => $short->getShortsMediaData(),
            'headerImage'       => $headerImage,
        ];
    }
}
