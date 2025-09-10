<?php

namespace App\Http\Collections\V10;

use App\Http\Resources\V10\PortalSurveyResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

class PortalSurveyCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return PortalSurveyResource::collection($this->collection);
    }

    public function withResponse($request, $response)
    {
        $jsonResponse = json_decode($response->getContent(), true);
        unset($jsonResponse['links'], $jsonResponse['meta']);
        $response->setContent(json_encode($jsonResponse));
    }
}
