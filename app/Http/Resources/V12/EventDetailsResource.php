<?php

namespace App\Http\Resources\V12;

use App\Http\Traits\ProvidesAuthGuardTrait;
use App\Models\User;
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
        $user             = $this->user();
        $xDeviceOs        = strtolower(request()->header('X-Device-Os', ""));
        $locationType     = config('zevolifesettings.event-location-type');
        $bookingDate      = $this->booking_date . ' ' . $this->start_time;
        $isAllowRegistred = true;
        $w                = 640;
        $h                = 1280;
        $diffSeconds      = (3600 * 12); // 12 Hours in seconds
        if ($xDeviceOs == config('zevolifesettings.PORTAL')) {
            $w = 800;
            $h = 800;
        }
        $meta      = json_decode($this->meta);
        $presenter = User::where('id', $this->presenter_user_id)->first();

        if ($this->capacity != null) {
            $getBookedUsers   = $this->bookedUsers()->where('event_registered_users_logs.event_booking_log_id', $this->booking_id)->where('is_cancelled', '0')->count();
            $isAllowRegistred = ($getBookedUsers >= $this->capacity) ? false : true;
        }

        $csatAvailable = false;
        if ($this->is_csat  && $this->status == '5' && $this->endDiff >= $diffSeconds) {
            $isFeedbackSubitted = $this->csat()
                ->where('event_csat_user_logs.event_booking_log_id', $this->booking_id)
                ->where('event_csat_user_logs.user_id', $user->id)
                ->count('event_csat_user_logs.id');
            $csatAvailable = ((!is_null($isFeedbackSubitted) && $isFeedbackSubitted > 0) ? false : true);
        }

        $returnData                     = [];
        $returnData['id']               = $this->id;
        $returnData['bookingId']        = $this->booking_id;
        $returnData['name']             = $this->name;
        $returnData['description']      = $this->description;
        $returnData['locationType']     = $locationType[$this->location_type];
        $returnData['isAllowRegistred'] = $isAllowRegistred;
        $returnData['creator']          = $this->getCreatorData();
        $returnData['presenter']        = [
            'id'    => (!empty($presenter) ? $presenter->getKey() : $this->presenter_user_id),
            'name'  => (!empty($presenter) ? $presenter->full_name : $meta->presenter),
            'image' => (!empty($presenter) ? $presenter->getMediaData('logo', ['w' => 600, 'h' => 600]) : [
                "width"  => 600,
                "height" => 600,
                "url"    => getDefaultFallbackImageURL("user", "user-none1"),
            ]),
        ];
        $returnData['logo']          = $this->getMediaData('logo', ['w' => $w, 'h' => $h, 'zc' => 3]);
        $returnData['bookingDate']   = Carbon::parse($bookingDate, config('app.timezone'))->setTimezone($user->timezone)->toDateTimeString();
        $returnData['createdAt']     = Carbon::parse($this->created_at, config('app.timezone'))->setTimezone($user->timezone)->toDateTimeString();
        $returnData['csatAvailable'] = $csatAvailable;

        return $returnData;
    }
}
