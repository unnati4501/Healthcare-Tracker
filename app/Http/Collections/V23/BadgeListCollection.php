<?php declare (strict_types = 1);

namespace App\Http\Collections\V23;

use App\Http\Resources\V23\BadgeListResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

class BadgeListCollection extends ResourceCollection
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
                'totalAchievement' => $this->collection['totalAchievement'],
                'generalBadge'     => BadgeListResource::collection($this->collection['generalBadge']),
                'challengeBadge'   => BadgeListResource::collection($this->collection['challengeBadge']),
                'masterclassBadge' => BadgeListResource::collection($this->collection['masterclassBadge']),
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
