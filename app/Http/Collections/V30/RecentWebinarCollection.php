<?php declare (strict_types = 1);

namespace App\Http\Collections\V30;

use App\Http\Resources\V30\RecentWebinarResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

class RecentWebinarCollection extends ResourceCollection
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
                'recentWebinars'     => RecentWebinarResource::collection($this->collection['recentWebinars']),
                'mostLiked'          => RecentWebinarResource::collection($this->collection['mostLiked']),
                'mostPlayed'         => RecentWebinarResource::collection($this->collection['mostPlayed']),
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
