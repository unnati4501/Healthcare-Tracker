<?php

namespace App\Http\Resources\V37;

use App\Http\Traits\ProvidesAuthGuardTrait;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryWiseTrackResource extends JsonResource
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
        $loggedUserLog = $this->trackUserLogs()->wherePivot('user_id', $user->getKey())->first();
        $typeArray     = config('zevolifesettings.meditation_track_list');

        $xDeviceOs      = strtolower(request()->header('X-Device-Os', ""));

        $w = 640;
        $h = 1280;
        $imagew = 640;
        $imageh = 1280;
        $youtubeURL = $this->getFirstMedia('track')->name;
        if ($xDeviceOs == config('zevolifesettings.PORTAL')) {
            $w = 1000;
            $h = 800;
            $imagew = 800;
            $imageh = 800;
            $youtubeURL = 'https://www.youtube.com/embed/' . $this->getFirstMedia('track')->getCustomProperty('ytid');
        }

        $response = [
            'id'                => $this->id,
            'image'             => $this->getMediaData('cover', ['w' => $imagew, 'h' => $imageh, 'zc' => 3]),
            'subcategory'       => ["id" => $this->tracksubcategory->id, "name" => $this->tracksubcategory->name],
            'title'             => $this->title,
            'isPremium'         => (($this->is_premium)),
            'totalDuration'     => $this->duration,

            'completedDuration' => ((!empty($this->duration_listened)) ? $this->duration_listened : 0),
            'likes'             => $this->totalLikes,

            'isLiked'           => ((!empty($loggedUserLog) && $loggedUserLog->pivot->liked)),
            'isFavorited'       => ((!empty($loggedUserLog) && $loggedUserLog->pivot->favourited)),
            'isSaved'           => ((!empty($loggedUserLog) && $loggedUserLog->pivot->saved)),
            'meditationType'    => $typeArray[$this->type],
            'audioType'         => $this->audio_type,
            'user'              => $this->getCoachData(),
            'viewCount'         => ((!empty($this->view_count)) ? $this->view_count : 0),
        ];

        if ($this->type == 1) {
            $response['backgroundImage'] = $this->getMediaData('background', ['w' => $w, 'h' => $h, 'zc' => 3]);
            $response['meditationURL'] = $this->track_url;
        } elseif ($this->type == 2 || $this->type == 3) {
            $response['backgroundImage'] = $this->getMediaData('track', ['w' => $w, 'h' => $h, 'conversion' => 'th_lg', 'zc' => 3]);
            $response['meditationURL'] = (($this->type == 3) ? $youtubeURL : $this->track_url);
        } elseif ($this->type == 4) {
            $response['backgroundImage'] = $this->getMediaData('track', ['w' => $w, 'h' => $h, 'conversion' => 'th_lg', 'zc' => 3]);
            if ($xDeviceOs == config('zevolifesettings.PORTAL')) {
                $response['meditationURL'] = config('zevolifesettings.vimeoembedurl') . $this->getFirstMedia('track')->getCustomProperty('vmid');
            } else {
                $response['meditationURL'] = config('zevolifesettings.vimeoappurl') . $this->getFirstMedia('track')->getCustomProperty('vmid');
            }
        }

        $response['headerImage']        = $this->getMediaData('header_image', ['w' => 800, 'h' => 800, 'zc' => 3]);
        return $response;
    }
}
