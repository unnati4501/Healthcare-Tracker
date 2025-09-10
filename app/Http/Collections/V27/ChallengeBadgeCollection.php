<?php declare (strict_types = 1);

namespace App\Http\Collections\V27;

use App\Http\Resources\V27\ChallengeBadgeResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ChallengeBadgeCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return ChallengeBadgeResource::collection($this->collection);
    }

    public function withResponse($request, $response)
    {
        $jsonResponse = json_decode($response->getContent(), true);
        unset($jsonResponse['links'], $jsonResponse['meta']);
        $response->setContent(json_encode($jsonResponse));
    }
}
