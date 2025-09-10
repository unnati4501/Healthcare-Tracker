<?php

namespace App\Http\Resources\V11;

use App\Http\Traits\ProvidesAuthGuardTrait;
use carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class EventDetailsResource extends JsonResource
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
        $user      = $this->user();
        $xDeviceOs = strtolower(request()->header('X-Device-Os', ""));
        $w         = 640;
        $h         = 1280;
        if ($xDeviceOs == config('zevolifesettings.PORTAL')) {
            $w = 800;
            $h = 400;
        }

        $locationType = config('zevolifesettings.event-location-type');

        $bookingDate = $this->booking_date . ' ' . $this->start_time;

        $isAllowRegistred = true;

        if ($this->capacity != null) {
            $getBookedUsers = $this->bookedUsers()->where('event_id', $this->id)->where('is_cancelled', '0')->count();


            $isAllowRegistred = ($getBookedUsers >= $this->capacity) ? false : true;
        }

        $returnData                     = [];
        $returnData['id']               = $this->id;
        $returnData['name']             = $this->name;
        $returnData['description']      = $this->description;
        $returnData['locationType']     = $locationType[$this->location_type];
        $returnData['isAllowRegistred'] = $isAllowRegistred;
        $returnData['creator']          = $this->getCreatorData();
        $returnData['logo']             = $this->getMediaData('logo', ['w' => $w, 'h' => $h]);
        $returnData['bookingDate']      = Carbon::parse($bookingDate, config('app.timezone'))->setTimezone($user->timezone)->toDateTimeString();
        $returnData['createdAt']        = Carbon::parse($this->created_at, config('app.timezone'))->setTimezone($user->timezone)->toDateTimeString();
        return $returnData;
    }
}
