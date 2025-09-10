<?php

namespace App\Http\Resources\V32;

use App\Http\Traits\ProvidesAuthGuardTrait;
use App\Models\MapProperties;
use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;

class ChallengeOtherUserMapResource extends JsonResource
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
        $user        = User::where('id', $this->id)->first();
        $mapProperty = MapProperties::where('map_id', $this->map_id)->get();
        $lat         = null;
        $lng         = null;
        $swapSteps   = 0;
        foreach ($mapProperty as $property) {
            $properties       = json_decode($property->properties);
            $previousSteps    = (array_key_exists('previous_steps', (array) $properties)) ? (int) $properties->previous_steps : 0;
            $previousDistance = (array_key_exists('previous_distance', (array) $properties)) ? (int) $properties->previous_distance : 0;
            $currentSteps     = (array_key_exists('steps', (array) $properties)) ? (int) $properties->steps : 0;
            $currentDistance  = (array_key_exists('distance', (array) $properties)) ? (int) $properties->distance : 0;
            $totalSteps       = (int) $previousSteps + $currentSteps;
            $totalDistance    = $previousDistance + $currentDistance;
            $totalCount       = (int) $this->steps;
            if ($totalSteps >= $totalCount && $swapSteps <= $totalCount) {
                $lat = (array_key_exists('lat', (array) $properties)) ? $properties->lat : 0;
                $lng = (array_key_exists('lng', (array) $properties)) ? $properties->lng : 0;
                $swapSteps = $totalSteps;
                break;
            }
        }

        // if ($this->shortName == 'steps') {
        //     $isCompleted = ($totalCount >= $totalSteps) ? true : false;
        // } elseif ($this->shortName == 'distance') {
        //     $totalDistance = $totalDistance * 1000;
        //     $isCompleted = ($totalCount >= $totalDistance) ? true : false;
        // }

        return [
            'id'           => $this->id,
            'profileImage' => $user->getMediaData('logo', ['w' => 512, 'h' => 512, 'zc' => 3]),
            'name'         => $this->first_name . ' ' . $this->last_name,
            'lat'          => $lat,
            'lng'          => $lng,
            'rank'         => $this->rank,
            'steps'        => $this->steps,
        ];
    }
}
