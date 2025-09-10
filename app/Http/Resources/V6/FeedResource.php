<?php

namespace App\Http\Resources\V6;

use App\Http\Traits\ProvidesAuthGuardTrait;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

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
        // dd($this);
        $user          = $this->user();
        $typeArray     = [1 => "AUDIO", 2 => "VIDEO", 3 => "YOUTUBE", 4 => "CONTENT"];
        $loggedUserLog = $this->feedUserLogs()->wherePivot('user_id', $user->getKey())->first();
        $mediaData     = [];
        $hasMedia      = false;
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
        $returnData['isLiked']     = ((!empty($loggedUserLog) && $loggedUserLog->pivot->liked) ? true : false);
        $returnData['isSaved']     = ((!empty($loggedUserLog) && $loggedUserLog->pivot->saved) ? true : false);
        $returnData['media']       = $this->when($hasMedia, $mediaData);
        $returnData['image']       = $this->getMediaData('featured_image', ['w' => 640, 'h' => 1280]);
        $returnData['type']        = $typeArray[$this->type];
        $returnData['category']    = [
            'id'   => $this->subcategory->id,
            'name' => $this->subcategory->name,
        ];
        $returnData['viewCount']          = ((!empty($this->view_count))? $this->view_count : 0);

        return $returnData;
    }
}
