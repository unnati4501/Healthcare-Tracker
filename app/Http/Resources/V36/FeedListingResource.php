<?php

namespace App\Http\Resources\V36;

use App\Http\Traits\ProvidesAuthGuardTrait;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Feed;

class FeedListingResource extends JsonResource
{
    use ProvidesAuthGuardTrait;

    /**
     * Create a new resource instance.
     *
     * @param  mixed  $resource
     * @return void
     */
    public function __construct($resource)
    {
        // Ensure you call the parent constructor
        parent::__construct($resource);
        $this->resource = $resource;
    }

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $feed = Feed::find($this['id']);
        $xDeviceOs     = strtolower(request()->header('X-Device-Os', ""));
        $isPortal      = false;
        if ($xDeviceOs == config('zevolifesettings.PORTAL')) {
            $isPortal       = true;
        }
        if ($this['type'] != 0) {
            $loggedUserLog = $feed->feedUserLogs()->wherePivot('user_id', $this->user()->getKey())->first();
            $typeArray     = config('zevolifesettings.type_array');
            $hasMedia      = false;
            $mediaData     = [];
            $isHomeListing = false;
            $w             = ($isHomeListing) ? 320 : 800;
            $h             = ($isHomeListing) ? 640 : 800;
            if ($xDeviceOs == config('zevolifesettings.PORTAL')) {
                $w              = 800;
                $h              = 800;
            }
            $typeIcon = config('zevolifesettings.type_icon');
            if ($this['type'] != 4) {
                $mediaData = $feed->getFeedMediaData();
                $hasMedia  = true;
            }
            $returnData                = [];
            $returnData['id']          = $this['id'];
            $returnData['type']        = $typeArray[$this['type']];
            $returnData['description'] = $this->when(($this['type'] == 4 && $isPortal), $this['description']);
            $returnData['title']       = $this['title'];
            $returnData['image']       = $feed->getMediaData('featured_image', ['w' => $w, 'h' => $h, 'zc' => 3]);
            $returnData['category']    = $this->when(($isPortal), [
                'id'   => $this['sub_category_id'],
                'name' => (isset($this['sub_category_name']) ? $this['sub_category_name'] : (isset($feed->courseSubCategory) ? $feed->courseSubCategory : '')),
            ]);
            $returnData['typeImage'] = [
                'url'    => getStaticAlertIconUrl($typeIcon[$this['type']]),
                'width'  => 0,
                'height' => 0,
            ];
            $returnData['likes']        = $this->when(($isPortal), $feed->getTotalLikes());
            $returnData['isStick']      = $this->when(($isPortal), $this['is_stick']);
            $returnData['media']        = $this->when($hasMedia && $isPortal, $mediaData);
            $returnData['isLiked']      = $this->when(($isPortal), (!empty($loggedUserLog) && $loggedUserLog->pivot->liked));
            $returnData['isSaved']      = $this->when(($isPortal), (!empty($loggedUserLog) && $loggedUserLog->pivot->saved));
            $returnData['isFavorited']  = $this->when(($isPortal), (!empty($loggedUserLog) && $loggedUserLog->pivot->favourited));
            $returnData['viewCount']    = $this->when(($isPortal), ((!empty($this['view_count'])) ? (int) $this['view_count'] : 0));
            $returnData['creator']      = $this->when(($isPortal), $feed->getCreatorData());
            $returnData['newTag']       = $this->when(($isPortal), (Carbon::now() < Carbon::parse($this['start_date'])->addDays(5)));
            $returnData['headerImage']  = $this->when((!$isPortal), $feed->getMediaData('header_image', ['w' => 800, 'h' => 800]));
        } elseif ($this['type'] == 0) {
            $returnData['id']           = $this['booking_log_id'];
            $returnData['type']         = 'event';
            $returnData['title']        = $this['name'];
            $returnData['description']  = $this->when(($isPortal), $this->description);
            $returnData['image']        = $this->getMediaData('logo', ['w' => 1280, 'h' => 640, 'zc' => 3]);
            $returnData['typeImage']    = [
                'url'    => getStaticAlertIconUrl('event'),
                'width'  => 0,
                'height' => 0,
            ];
            $returnData['category'] = $this->when(($isPortal), [
                'id'   => 0,
                'name' => '',
            ]);
            $returnData['likes']        = $this->when(($isPortal), 0);
            $returnData['isStick']      = $this->when(($isPortal), false);
            $returnData['isLiked']      = $this->when(($isPortal), false);
            $returnData['isSaved']      = $this->when(($isPortal), false);
            $returnData['viewCount']    = $this->when(($isPortal), 0);
            $returnData['headerImage']  = $this->when((!$isPortal), $feed->getMediaData('header_image', ['w' => 800, 'h' => 800]));
        }
        $returnData['tag'] = $this->when(($this['tag']!= ""), ucfirst($this['tag'])); 
        return $returnData;
    }
}
