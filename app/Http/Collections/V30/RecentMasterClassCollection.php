<?php declare (strict_types = 1);

namespace App\Http\Collections\V30;

use App\Http\Resources\V30\RecentMasterclassResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

class RecentMasterClassCollection extends ResourceCollection
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
                'recentMasterClass' => RecentMasterclassResource::collection($this->collection['recentMasterClass']),
                'mostLiked'     => RecentMasterclassResource::collection($this->collection['mostLiked']),
                'mostEnrolled'  => RecentMasterclassResource::collection($this->collection['mostEnrolled']),
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
