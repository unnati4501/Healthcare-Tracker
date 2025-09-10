<?php declare (strict_types = 1);

namespace App\Http\Resources\V38;

use App\Http\Traits\ProvidesAuthGuardTrait;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Webinar;

class WebinarListResource extends JsonResource
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
        $xDeviceOs           = strtolower(request()->header('X-Device-Os', ""));
        $user                = $this->user();
        $loggedUserLog       = $this->webinarUserLogs()->wherePivot('user_id', $user->getKey())->first();
        $w                   = 640;
        $h                   = 1280;
        $totalDuration       = (!empty($totalDuration)) ? (int) $totalDuration : 0;
        $totalDurationInMins = (!empty($totalDuration)) ? convertSecondToMinute($totalDuration) : 0;
        if ($xDeviceOs == config('zevolifesettings.PORTAL')) {
            $w             = 800;
            $h             = 800;
            $totalDuration = (!empty($totalDuration)) ? convertSecondToMinute($totalDuration) : 0;
        }
        $headerImage            = $this->getMediaData('header_image', ['w' => 800, 'h' => 800, 'zc' => 3]);
        return [
            'id'                => $this->id,
            'title'             => $this->title,
            'creator'           => $this->getCreatorData(),
            'subcategory'       => ["id" => $this->webinarsubcategory->id, "name" => $this->webinarsubcategory->name],
            'image'             => $this->getMediaData('logo', ['w' => $w, 'h' => $h, 'zc' => 3]),
            'viewCount'         => ((!empty($this->view_count)) ? $this->view_count : 0),
            "likesCount"        => $this->getTotalLikes(),
            'isLiked'           => ((!empty($loggedUserLog) && $loggedUserLog->pivot->liked)),
            'isSaved'           => ((!empty($loggedUserLog) && $loggedUserLog->pivot->saved)),
            'isFavorited'       => ((!empty($loggedUserLog) && $loggedUserLog->pivot->favourited)),
            'duration'          => $totalDuration,
            'durationInMinutes' => $totalDurationInMins,
            'media'             => $this->getWebinarMediaData(),
            'headerImage'       => $headerImage,
            'tag'               => $this->when(($xDeviceOs != "portal" && !empty($this->caption)), $this->caption)
        ];
    }
}
