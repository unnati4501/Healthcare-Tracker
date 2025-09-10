<?php

namespace App\Http\Resources\V6;

use App\Http\Traits\ProvidesAuthGuardTrait;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class InspireHistoryResource extends JsonResource
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
        $user = $this->user();

        if ($this->getTable() == 'meditation_tracks') {
            return [
                'id'    => $this->pivot->meditation_track_id,
                'title' => $this->title,
                'date'  => Carbon::parse($this->pivot->created_at, config('app.timezone'))->setTimezone($user->timezone)->toAtomString(),
                'image' => $this->getMediaData('cover', ['w' => 320, 'h' => 320, 'zc' => 3]),
            ];
        } else {
            return [
                'id'    => $this->id,
                'title' => $this->title,
                'date'  => Carbon::parse($this->pivot->completed_on, config('app.timezone'))->setTimezone($user->timezone)->toAtomString(),
                'image' => $this->getMediaData('logo', ['w' => 320, 'h' => 320, 'zc' => 3]),
            ];
        }
    }
}
