<?php

namespace App\Http\Resources\V5;

use App\Http\Traits\ProvidesAuthGuardTrait;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class FeedResource extends JsonResource
{
    use ProvidesAuthGuardTrait;

    /**
     * @var identify sent response type is for grid or details page
     */
    private $type;

    /**
     * Create a new resource instance.
     *
     * @param  mixed  $resource
     * @return void
     */
    public function __construct($resource, $type)
    {
        // Ensure you call the parent constructor
        parent::__construct($resource);
        $this->resource = $resource;

        $this->type = (!empty($type) ? $type : 'grid');
    }

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        /** @var User $user */
        $user = $this->user();

        $loggedUserLog = $this->feedUserLogs()->wherePivot('user_id', $user->getKey())->first();

        $video      = $this->getMediaData('video', ['w' => 1280, 'h' => 640]);
        $youtube    = $this->getMediaData('youtube', ['w' => 1280, 'h' => 640]);
        $returnData = [];

        if ($this->type == 'details') {
            $image                     = $this->getMediaData('listing_logo', ['w' => 1280, 'h' => 640]);
            $bannerImage               = $this->getMediaData('logo', ['w' => 1280, 'h' => 640]);
            $returnData['image']       = $image;
            $returnData['bannerImage'] = $bannerImage;
        } else {
            $image               = $this->getMediaData('listing_logo', ['w' => 1280, 'h' => 640]);
            $returnData['image'] = $image;
        }

        $returnData['id']          = $this->id;
        $returnData['title']       = $this->title;
        $returnData['description'] = $this->description;
        $returnData['video']       = (!empty($video)) ? $video : (object) array();
        $returnData['youTube']     = (!empty($youtube)) ? $youtube : (object) array();
        $returnData['creator']     = $this->getCreatorData();
        $returnData['createdAt']   = Carbon::parse($this->created_at, config('app.timezone'))->setTimezone($this->timezone)->toAtomString();
        $returnData['labelTag']    = ((!empty($this->subCategory()->first())) ? $this->subCategory()->first()->name : "");
        $returnData['likes']       = $this->getTotalLikes();
        $returnData['isLiked']     = ((!empty($loggedUserLog) && $loggedUserLog->pivot->liked) ? true : false);
        $returnData['isSaved']     = ((!empty($loggedUserLog) && $loggedUserLog->pivot->saved) ? true : false);

        return $returnData;
    }
}
