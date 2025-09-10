<?php declare (strict_types = 1);

namespace App\Http\Resources\V12;

use App\Http\Traits\ProvidesAuthGuardTrait;
use Illuminate\Http\Resources\Json\JsonResource;

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
        $typeArray           = [1 => "VIDEO", 2 => "YOUTUBE"];
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

        return [
            'id'                => $this->id,
            'title'             => $this->title,
            'creator'           => $this->getCreatorData(),
            'subcategory'       => ["id" => $this->webinarsubcategory->id, "name" => $this->webinarsubcategory->name],
            'image'             => $this->getMediaData('logo', ['w' => $w, 'h' => $h, 'zc' => 3]),
            'viewCount'         => ((!empty($this->view_count)) ? $this->view_count : 0),
            "likesCount"        => $this->getTotalLikes(),
            'isLiked'           => ((!empty($loggedUserLog) && $loggedUserLog->pivot->liked) ),
            'isSaved'           => ((!empty($loggedUserLog) && $loggedUserLog->pivot->saved) ),
            'duration'          => $totalDuration,
            'durationInMinutes' => $totalDurationInMins,
            'media'             => $this->getWebinarMediaData(),
        ];
    }
}
