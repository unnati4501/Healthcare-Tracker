<?php

namespace App\Http\Collections\V11;

use App\Http\Resources\V11\MasterClassSurveyResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

class MasterClassSurveyCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return MasterClassSurveyResource::collection($this->collection);
    }

    public function withResponse($request, $response)
    {
        $jsonResponse = json_decode($response->getContent(), true);
        unset($jsonResponse['links'], $jsonResponse['meta']);
        $response->setContent(json_encode($jsonResponse));
    }
}
