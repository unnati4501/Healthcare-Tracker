<?php declare (strict_types = 1);

namespace App\Http\Resources\V31;

use App\Http\Traits\ProvidesAuthGuardTrait;
use Illuminate\Http\Resources\Json\JsonResource;

class RealTimeSchedulingResource extends JsonResource
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
        return [
            'id'                         => $this->id,
            'name'                       => $this->name,
            'eventId'                    => $this->event_id,
            'creator'                    => $this->getCreatorData(),
            'wellbeingSpecialistDetails' => $this->getWellbeingSpecialistData(),
            'schedulingId'               => $this->scheduling_id,
            'schedulingId'               => $this->scheduling_id,
            'eventIdentifier'            => $this->event_identifier,
            'location'                   => $this->location,
            'eventCreatedAt'             => $this->event_created_at,
            'status'                     => $this->status,
            'createdAt'                  => $this->created_at,
            'updatedAt'                  => $this->updated_at,
        ];
    }
}
