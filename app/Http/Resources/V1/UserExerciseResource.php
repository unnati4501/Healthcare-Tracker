<?php

namespace App\Http\Resources\V1;

use App\Http\Traits\ProvidesAuthGuardTrait;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class UserExerciseResource extends JsonResource
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
        /** @var User $user */
        $user         = $this->user();
        $userTimezone = $user->timezone;
        $appTimezone  = config('app.timezone');
        $routeImage   = (($this->show_map ) ? $this->pivot->getRouteMediaData('logo', ['w' => 320, 'h' => 160]) : '');

        return [
            'id'          => $this->pivot->id,
            'title'       => $this->title,
            'image'       => $this->getMediaData('logo', ['w' => 320, 'h' => 320]),
            'duration'    => $this->pivot->duration,
            'distance'    => $this->pivot->distance,
            'calories'    => $this->pivot->calories,
            'routeImage'  => ((!empty($routeImage)) ? $routeImage : (object) array()),
            'startAt'     => Carbon::parse($this->pivot->start_date, $appTimezone)->setTimezone($userTimezone)->toAtomString(),
            'exerciseKey' => $this->pivot->exercise_key,
        ];
    }
}
