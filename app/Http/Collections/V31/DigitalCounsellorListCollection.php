<?php

namespace App\Http\Collections\V31;

use App\Http\Resources\V31\DigitalCounsellorListResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

class DigitalCounsellorListCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $xDeviceOs = strtolower($request->header('X-Device-Os', ""));
        return [
            'data'      => DigitalCounsellorListResource::collection($this['data']),
            'topicName' => $this->when(!empty($this['topicName'] && $xDeviceOs == config('zevolifesettings.PORTAL')), $this['topicName']),
        ];
    }

    public function withResponse($request, $response)
    {
        $jsonResponse = json_decode($response->getContent(), true);
        unset($jsonResponse['links'], $jsonResponse['meta']);
        $response->setContent(json_encode($jsonResponse));
    }
}
