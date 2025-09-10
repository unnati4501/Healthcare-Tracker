<?php declare (strict_types = 1);

namespace App\Http\Resources\V32;

use App\Http\Traits\ProvidesAuthGuardTrait;
use Illuminate\Http\Resources\Json\JsonResource;

class RealTimeSchedulingDataResource extends JsonResource
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
            'id'              => $this['id'],
            'name'            => $this['name'],
            'eventId'         => $this['event_id'],
            'schedulingId'    => $this['scheduling_id'],
            'schedulingId'    => $this['scheduling_id'],
            'eventIdentifier' => $this['event_identifier'],
            'token'           => $this['token'],
            'location'        => $this['location'],
            'eventCreatedAt'  => $this['event_created_at'],
            'status'          => $this['status'],
            'token'           => $this['token'],
            'duration'        => $this['duration'],
            'timezone'        => $this['timezone'],
            'subId'           => $this['subId'],
            'startTime'       => $this['startTime'],
            'endTime'         => $this['endTime'],
            'queryPeriod'     => $this['queryPeriod'],
            'reschedule'      => $this['reschedule'],
            'dataCenter'      => $this['dataCenter'],
            'createdAt'       => $this['created_at'],
            'updatedAt'       => $this['updated_at'],
        ];
    }
}
