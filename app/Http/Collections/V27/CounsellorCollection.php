<?php declare (strict_types = 1);

namespace App\Http\Collections\V27;

use App\Http\Collections\V10\AppSlideCollection;
use App\Http\Resources\V27\CounsellorResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

class CounsellorCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $eapTickets = [];
        if (!empty($this['eapTickets'])) {
            $eapTickets = new CounsellorResource($this['eapTickets']);
        }

        return [
            'data' => [
                'eapTickets'         => $this->when(!empty($eapTickets), $eapTickets),
                'sliders'            => new AppSlideCollection($this['sliders']),
                'showBookingHistory' => $this['showBookingHistory'],
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
