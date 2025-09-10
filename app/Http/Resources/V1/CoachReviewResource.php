<?php

namespace App\Http\Resources\V1;

use App\Http\Traits\ProvidesAuthGuardTrait;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class CoachReviewResource extends JsonResource
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

        return [
            'id'        => $this->pivot->id,
            'rating'    => $this->pivot->ratings,
            'review'    => (!empty($this->pivot->review))? $this->pivot->review : "",
            'createdAt' => Carbon::parse($this->pivot->created_at, config('app.timezone'))->setTimezone($user->timezone)->toAtomString(),
            'user'      => $this->getUserDataForApi(),
        ];
    }
}
