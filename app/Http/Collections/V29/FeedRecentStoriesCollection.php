<?php declare (strict_types = 1);

namespace App\Http\Collections\V29;

use App\Http\Resources\V22\FeedRecentStoriesResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

class FeedRecentStoriesCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'data' => [
                'recentStories' => FeedRecentStoriesResource::collection($this->collection['recentStories']),
                'mostListened'  => FeedRecentStoriesResource::collection($this->collection['mostListened']),
                'mostWatched'   => FeedRecentStoriesResource::collection($this->collection['mostWatched']),
                'mostLiked'     => FeedRecentStoriesResource::collection($this->collection['mostLiked']),
            ],
        ];
    }

    public function withResponse($request, $response)
    {
        $jsonResponse = json_decode($response->getContent(), true);
        unset($jsonResponse['links'], $jsonResponse['meta']);
        $response->setContent(json_encode($jsonResponse));
    }
}
