<?php

namespace App\Http\Collections\V38;

use App\Http\Resources\V38\MasterClassLessonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

class MasterClassLessonCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return MasterClassLessonResource::collection($this->collection);
    }

    public function withResponse($request, $response)
    {
        $jsonResponse = json_decode($response->getContent(), true);
        unset($jsonResponse['links'], $jsonResponse['meta']);
        $response->setContent(json_encode($jsonResponse));
    }
}
