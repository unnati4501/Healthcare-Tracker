<?php

namespace App\Http\Resources\V12;

use App\Http\Traits\ProvidesAuthGuardTrait;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class UpdatedUserExerciseResource extends JsonResource
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
        $user         = $this->user();
        $appTimezone  = config('app.timezone');
        $userTimezone = $user->timezone;
        $exercise     = $this->exercise()->select('show_map', 'title')->first();
        $routeImage   = (($exercise->show_map ) ? $this->getRouteMediaData('logo', ['w' => 320, 'h' => 160]) : '');

        return [
            'id'          => $this->id,
            'title'       => $exercise->title,
            'image'       => $this->getMediaData('logo', ['w' => 320, 'h' => 320]),
            'duration'    => $this->duration,
            'distance'    => $this->distance,
            'calories'    => $this->calories,
            'routeImage'  => ((!empty($routeImage)) ? $routeImage : (object) []),
            'startAt'     => Carbon::parse($this->start_date, $appTimezone)->setTimezone($userTimezone)->toAtomString(),
            'exerciseKey' => $this->exercise_key,
        ];
    }
}
