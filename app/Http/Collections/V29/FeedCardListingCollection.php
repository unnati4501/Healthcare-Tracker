<?php declare (strict_types = 1);

namespace App\Http\Collections\V29;

use App\Http\Resources\V22\FeedRecentStoriesResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

class FeedCardListingCollection extends ResourceCollection
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
            'category' => $this->collection['category'],
            'data'     => FeedRecentStoriesResource::collection($this->collection['data']),
        ];
    }

    public function withResponse($request, $response)
    {
        $jsonResponse = json_decode($response->getContent(), true);
        unset($jsonResponse['links'], $jsonResponse['meta']);
        $response->setContent(json_encode($jsonResponse));
    }
}
