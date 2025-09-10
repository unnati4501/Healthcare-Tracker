<?php

namespace App\Http\Collections\V1;

use App\Http\Traits\ProvidesAuthGuardTrait;
use Illuminate\Http\Resources\Json\ResourceCollection;

class CourceCoachDetailsCollection extends ResourceCollection
{
    use ProvidesAuthGuardTrait;

    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $coachData              = array();
        $coachData              = $this->collection[0]->getCreatorDataWithAbout();
        $coachData['courses']   = $this->collection[0]->getCourseCountByCreator();
        $coachData['expertise'] = $this->pluck("name")->toArray();

        return [
            'data' => $coachData,
        ];
    }

    public function withResponse($request, $response)
    {
        $jsonResponse = json_decode($response->getContent(), true);
        unset($jsonResponse['links'], $jsonResponse['meta']);
        $response->setContent(json_encode($jsonResponse));
    }
}
