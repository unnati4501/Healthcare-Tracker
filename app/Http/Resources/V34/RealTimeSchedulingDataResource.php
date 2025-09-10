<?php declare (strict_types = 1);

namespace App\Http\Resources\V34;

use App\Http\Traits\ProvidesAuthGuardTrait;
use App\Models\Service;
use App\Models\ServiceSubCategory;
use App\Models\User;
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

        $wellbeingSpecialist = User::where('id', $this['ws_id'])->select('id', \DB::raw("CONCAT(users.first_name, ' ', users.last_name) AS name"))->first()->toArray();
        $services            = Service::where('id', $this['service_id'])->select('id', 'name')->first()->toArray();
        $subCategory         = ServiceSubCategory::where('id', $this['topic_id'])->select('id', 'name')->first()->toArray();

        return [
            'id'              => $this['id'],
            'wsDetails'       => $this->when(!empty($wellbeingSpecialist), $wellbeingSpecialist),
            'services'        => $this->when(!empty($services), $services),
            'subCategory'     => $this->when(!empty($subCategory), $subCategory),
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
            'bufferBefore'    => $this['bufferBefore'],
            'bufferAfter'     => $this['bufferAfter'],
            'createdAt'       => $this['created_at'],
            'updatedAt'       => $this['updated_at'],
        ];
    }
}
