<?php

namespace App\Http\Resources\V20;

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
        $user         = $this->user();
        $xDeviceOs    = strtolower(request()->header('X-Device-Os', ""));
        $locationType = config('zevolifesettings.event-location-type');
        $meta         = json_decode($this->meta);
        $w            = 640;
        $h            = 1280;
        $diffSeconds  = (3600 * 12); // 12 Hours in seconds
        if ($xDeviceOs == config('zevolifesettings.PORTAL')) {
            $w = 800;
            $h = 800;
        }

        // booking date
        $now               = now($user->timezone);
        $durationInMinutes = timeToDecimal($this->duration);
        $startTime         = Carbon::parse("{$this->booking_date} {$this->start_time}", config('app.timezone'))
            ->setTimezone($user->timezone);
        $endTime = $startTime->copy()->addMinutes($durationInMinutes);

        $durationInHumanFormat = "1 minute left";
        $nowInUTC              = now(config('app.timezone'));
        $diff                  = Carbon::parse("{$this->booking_date} {$this->start_time}", config('app.timezone'))->diffInSeconds($nowInUTC);
        if ($diff > 60) {
            $durationInHumanFormat = Carbon::parse("{$this->booking_date} {$this->start_time}", config('app.timezone'))->diffForHumans();
            $durationInHumanFormat = str_replace("from now", "left", $durationInHumanFormat);
        }

        // get presenter details
        $presenter = User::find($this->presenter_user_id, ['id', 'first_name', 'last_name']);

        // get total booked users
        $totalBookedUsers = $this->bookedUsers()
            ->where('event_registered_users_logs.event_booking_log_id', $this->booking_id)
            ->where('is_cancelled', '0')
            ->count();

        // check allow user to register or not
        $isAllowRegistred = true;
        if ($this->capacity != null) {
            $isAllowRegistred = ($totalBookedUsers >= $this->capacity) ? false : true;
        }

        // check csat avilable for the event booking
        $csatAvailable = false;
        if ($this->is_csat  && $this->status == '5' && $this->endDiff >= $diffSeconds) {
            $isFeedbackSubitted = $this->csat()
                ->where('event_csat_user_logs.event_booking_log_id', $this->booking_id)
                ->where('event_csat_user_logs.user_id', $user->id)
                ->count('event_csat_user_logs.id');
            $csatAvailable = ((!is_null($isFeedbackSubitted) && $isFeedbackSubitted > 0) ? false : true);
        }

        // check user is registered or not
        $isRegistered = $this->bookedUsers()
            ->where('event_registered_users_logs.event_booking_log_id', $this->booking_id)
            ->where('is_cancelled', '0')
            ->where('user_id', $user->id)
            ->count();
        $isRegistered = (($isRegistered > 0) ? true : false);

        return [
            'id'                    => $this->id,
            'bookingId'             => $this->booking_id,
            'name'                  => $this->name,
            'description'           => $this->description,
            'logo'                  => $this->getMediaData('logo', ['w' => $w, 'h' => $h, 'zc' => 3]),
            'locationType'          => $locationType[$this->location_type],
            'isAllowRegistred'      => $isAllowRegistred,
            'creator'               => $this->getCreatorData(),
            'presenter'             => [
                'id'    => (!empty($presenter) ? $presenter->getKey() : $this->presenter_user_id),
                'name'  => (!empty($presenter) ? $presenter->full_name : $meta->presenter),
                'image' => (!empty($presenter) ? $presenter->getMediaData('logo', ['w' => 600, 'h' => 600]) : [
                    "width"  => 600,
                    "height" => 600,
                    "url"    => getDefaultFallbackImageURL("user", "user-none1"),
                ]),
            ],
            'csatAvailable'         => $csatAvailable,
            'durationInHumanFormat' => $durationInHumanFormat,
            'participants'          => (!empty($totalBookedUsers) ? $totalBookedUsers : 0),
            'duration'              => $durationInMinutes,
            'isOngoing'             => $now->between($startTime, $endTime),
            'isRegistered'          => $isRegistered,
            'bookingDate'           => $startTime->toAtomString(),
            'createdAt'             => $this->created_at->setTimezone($user->timezone)->toAtomString(),
        ];
    }
}
