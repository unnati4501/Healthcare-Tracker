<?php declare (strict_types = 1);

namespace App\Http\Collections\V30;

use App\Http\Resources\V30\RecentMeditationResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

class RecentMeditationCollection extends ResourceCollection
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
                'recentMeditations'  => RecentMeditationResource::collection($this->collection['recentMeditations']),
                'mostLiked'          => RecentMeditationResource::collection($this->collection['mostLiked']),
                'guidedMeditations'  => RecentMeditationResource::collection($this->collection['guidedMeditations']),
                'mostPlayed'         => RecentMeditationResource::collection($this->collection['mostPlayed']),
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
