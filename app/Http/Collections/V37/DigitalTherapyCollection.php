<?php declare (strict_types = 1);

namespace App\Http\Collections\V37;

use App\Http\Collections\V37\AppSlideCollection;
use App\Http\Collections\V31\ServiceListCollection;
use Illuminate\Http\Resources\Json\ResourceCollection;

class DigitalTherapyCollection extends ResourceCollection
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
                'serviceList'            => new ServiceListCollection($this['serviceList']),
                'sliders'                => new AppSlideCollection($this['sliders']),
                'allowAppoitment'        => $this['allowAppoitment'],
                'allowEmergencyContacts' => $this['allowEmergencyContacts'],
                'isPortalPopup'          => $this['isPortalPopup'],
                'isTermAccepted'         => $this['isTermAccepted'],
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
