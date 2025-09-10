<?php

namespace App\Http\Resources\V33;

use App\Http\Traits\ProvidesAuthGuardTrait;
use Illuminate\Http\Resources\Json\JsonResource;

class ContentChallengeActivityResource extends JsonResource
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
        $activity = $this->contentChallengeActivity()->select('activity', 'daily_limit', 'points_per_action')->get();
        return [
            'id'          => $this->id,
            'title'       => $this->category,
            'description' => $this->when(!is_null($this->description), $this->description),
            'value'       => $this->when(!empty($activity), $activity),
        ];
    }
}
