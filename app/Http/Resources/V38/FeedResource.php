<?php

namespace App\Http\Resources\V38;

use App\Http\Traits\ProvidesAuthGuardTrait;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Collections\V38\MorefeedCollection;

class FeedResource extends JsonResource
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
        $typeArray     = config('zevolifesettings.type_array');
        $loggedUserLog = $this->feedUserLogs()->wherePivot('user_id', $user->getKey())->first();
        $mediaData     = [];
        $hasMedia      = false;

        $xDeviceOs = strtolower(request()->header('X-Device-Os', ""));
        $w         = 640;
        $h         = 1280;
        $contentW  = 800;
        $contentH  = 800;
        $isPortal  = false;
        if ($xDeviceOs == config('zevolifesettings.PORTAL')) {
            $w          = 800;
            $h          = 800;
            $isPortal   = true;
        }
        $typeIcon = config('zevolifesettings.type_icon');
        if ($this->type != 4) {
            $mediaData = $this->getFeedMediaData();
            $hasMedia  = true;
        }

        $returnData                = [];
        $returnData['id']          = $this->id;
        $returnData['title']       = $this->title;
        $returnData['description'] = $this->when(($this->type == 4), $this->description);
        $returnData['creator']     = $this->getCreatorData();
        $returnData['createdAt']   = Carbon::parse($this->created_at, config('app.timezone'))->setTimezone($this->timezone)->toAtomString();
        $returnData['likes']       = $this->getTotalLikes();
        $returnData['isLiked']     = ((!empty($loggedUserLog) && $loggedUserLog->pivot->liked));
        $returnData['isFavorited'] = ((!empty($loggedUserLog) && $loggedUserLog->pivot->favourited));
        $returnData['isSaved']     = ((!empty($loggedUserLog) && $loggedUserLog->pivot->saved));
        $returnData['media']       = $this->when($hasMedia, $mediaData);
        $returnData['typeImage']   = [
            'url'    => getStaticAlertIconUrl($typeIcon[$this->type]),
            'width'  => 0,
            'height' => 0,
        ];
        $returnData['image']    = $this->getMediaData('featured_image', ['w' => $w, 'h' => $h]);
        $returnData['type']     = $typeArray[$this->type];
        $returnData['category'] = [
            'id'   => $this->subcategory->id,
            'name' => $this->subcategory->name,
        ];
        $returnData['headerImage'] = $this->when((($isPortal && $this->type == 4) || (!$isPortal)), $this->getMediaData('header_image', ['w' => $contentW, 'h' => $contentH]));
        $returnData['viewCount']   = ((!empty($this->view_count)) ? $this->view_count : 0);
        $returnData['moreStories'] = new MorefeedCollection($this->getMoreStories(), false);

        return $returnData;
    }
}
