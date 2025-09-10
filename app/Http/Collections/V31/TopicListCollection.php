<?php

namespace App\Http\Collections\V31;

use App\Http\Resources\V31\TopicListResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

class TopicListCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $data['data'] = [
            'topics'        => TopicListResource::collection($this['topics']),
            'description'   => $this->when(!empty($this['description']), $this['description']),
            'serviceName'   => $this->when(!empty($this['serviceName']), $this['serviceName']),
        ];
        return $data;
    }

    public function withResponse($request, $response)
    {
        $jsonResponse = json_decode($response->getContent(), true);
        unset($jsonResponse['links'], $jsonResponse['meta']);
        $response->setContent(json_encode($jsonResponse));
    }
}
