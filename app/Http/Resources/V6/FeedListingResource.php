<?php

namespace App\Http\Resources\V6;

use App\Http\Traits\ProvidesAuthGuardTrait;
use Illuminate\Http\Resources\Json\JsonResource;

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
        $loggedUserLog = $this->feedUserLogs()->wherePivot('user_id', $this->user()->getKey())->first();
        $typeArray     = [1 => "AUDIO", 2 => "VIDEO", 3 => "YOUTUBE", 4 => "CONTENT"];
        $isHomeListing = true;

        $returnData             = [];
        $returnData['id']       = $this->id;
        $returnData['type']     = $typeArray[$this->type];
        $returnData['title']    = $this->title;
        $returnData['image']    = $this->getMediaData('featured_image', (($isHomeListing) ? ['w' => 320, 'h' => 640] : ['w' => 1280, 'h' => 640]));
        $returnData['category'] = [
            'id'   => $this->sub_category_id,
            'name' => (isset($this->sub_category_name) ? $this->sub_category_name : (isset($this->courseSubCategory) ? $this->courseSubCategory : '')),
        ];
        $returnData['likes']     = $this->getTotalLikes();
        $returnData['isLiked']   = ((!empty($loggedUserLog) && $loggedUserLog->pivot->liked) ? true : false);
        $returnData['isSaved']   = ((!empty($loggedUserLog) && $loggedUserLog->pivot->saved) ? true : false);
        $returnData['viewCount'] = ((!empty($this->view_count)) ? $this->view_count : 0);

        return $returnData;
    }
}
