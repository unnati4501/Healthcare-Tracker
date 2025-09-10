<?php

namespace App\Http\Collections\V36;

use Illuminate\Http\Resources\Json\ResourceCollection;
use App\Http\Resources\V36\MoreLikePodcastDetailsResource;
use Illuminate\Support\Facades\Auth;
use App\Models\Podcast;
use DB;

class PodcastDetailsCollection extends ResourceCollection
{
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
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $user          = \Auth::guard('api')->user();
        $loggedUserLog = $this->podcastUserLogs()->wherePivot('user_id', $user->getKey())->first();
        $xDeviceOs     = strtolower(request()->header('X-Device-Os', ""));
    
        $imagew         = 800;
        $imageh         = 800;
        if ($xDeviceOs == config('zevolifesettings.PORTAL')) {
            $imagew         = 800;
            $imageh         = 800;
        }

        return [
            'id'                => $this->id,
            'image'             => $this->getMediaData('logo', ['w' => $imagew, 'h' => $imageh, 'zc' => 3]),
            'subcategory'       => ["id" => $this->podcastsubcategory->id, "name" => $this->podcastsubcategory->name],
            'title'             => $this->title,
            'totalDuration'     => $this->duration,
            'completedDuration' => ((!empty($this->duration_listened)) ? $this->duration_listened : 0),
            'likes'             => $this->totalLikes,
            'isLiked'           => ((!empty($loggedUserLog) && $loggedUserLog->pivot->liked)),
            'isFavorited'       => ((!empty($loggedUserLog) && $loggedUserLog->pivot->favourited)),
            'isSaved'           => ((!empty($loggedUserLog) && $loggedUserLog->pivot->saved)),
            'user'              => $this->getCoachData(),
            'viewCount'         => ((!empty($this->view_count)) ? $this->view_count : 0),
            'podcastURL'        => $this->track_url,
            'moreLikePodcasts'  => $this->when(($this->moreLikePodcasts->count() > 0), MoreLikePodcastDetailsResource::collection($this->moreLikePodcasts)),
        ];
    }

    public function withResponse($request, $response)
    {
        $jsonResponse = json_decode($response->getContent(), true);
        unset($jsonResponse['links'], $jsonResponse['meta']);
        $response->setContent(json_encode($jsonResponse));
    }
}
