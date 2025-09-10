<?php

namespace App\Http\Resources\V29;

use App\Http\Traits\ProvidesAuthGuardTrait;
use Illuminate\Http\Resources\Json\JsonResource;

class ChallengeMapResource extends JsonResource
{
    use ProvidesAuthGuardTrait;

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $properties       = json_decode($this->properties);
        $image            = $this->getMediaData('propertyimage', ['w' => 512, 'h' => 512]);
        $locationType     = (array_key_exists('locationType', (array) $properties)) ? (int) $properties->locationType : 2;
        $isCompleted      = false;
        $previousSteps    = (array_key_exists('previous_steps', (array) $properties)) ? (int) $properties->previous_steps : 0;
        $previousDistance = (array_key_exists('previous_distance', (array) $properties)) ? (int) $properties->previous_distance : 0;
        $currentSteps     = (array_key_exists('steps', (array) $properties)) ? (int) $properties->steps : 0;
        $currentDistance  = (array_key_exists('distance', (array) $properties)) ? (int) $properties->distance : 0;
        $totalSteps       = $previousSteps + $currentSteps;
        $totalDistance    = $previousDistance + $currentDistance;

        if ($this->shortName == 'steps') {
            $isCompleted = ($this->totalCount >= $totalSteps) ? true : false;
        } elseif ($this->shortName == 'distance') {
            $isCompleted = ($this->totalCount >= $totalDistance) ? true : false;
        }

        return [
            'id'           => $this->id,
            'lat'          => (array_key_exists('lat', (array) $properties)) ? $properties->lat : 0,
            'lng'          => (array_key_exists('lng', (array) $properties)) ? $properties->lng : 0,
            'steps'        => $currentSteps,
            'distance'     => $currentDistance * 1000,
            'image'        => $this->when($locationType == 1, $image),
            'locationName' => (array_key_exists('locationName', (array) $properties)) ? $properties->locationName : '',
            'locationType' => $locationType, // [ 1 => 'Main Location', 2 => 'Sub Location' ]
            'isCompleted'  => $isCompleted,
        ];
    }
}
