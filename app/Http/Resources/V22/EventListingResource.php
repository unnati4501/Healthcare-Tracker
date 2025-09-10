<?php

namespace App\Http\Resources\V22;

use App\Http\Traits\ProvidesAuthGuardTrait;
use App\Models\User;
use carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class EventListingResource extends JsonResource
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

        // get total booked users
        $totalBookedUsers = $this->bookedUsers()
            ->where('event_registered_users_logs.event_booking_log_id', $this->booking_id)
            ->where('is_cancelled', '0')
            ->count();

        // get presenter details
        $presenter = User::find($this->presenter_user_id, ['id', 'first_name', 'last_name']);

        // check allow user to register or not
        $isAllowRegistred = true;
        if ($this->capacity != null) {
            $isAllowRegistred = ($totalBookedUsers >= $this->capacity) ? false : true;
        }

        // check user is registered or not
        $isRegistered = false;
        if (!is_null($request->type) && $request->type == 'booked') {
            $isRegistered = true;
        } else {
            $isRegistered = $this->bookedUsers()
                ->where('event_registered_users_logs.event_booking_log_id', $this->booking_id)
                ->where('is_cancelled', '0')
                ->where('user_id', $user->id)
                ->count();
            $isRegistered = (($isRegistered > 0) ? true : false);
        }

        return [
            'id'               => $this->id,
            'bookingId'        => $this->booking_id,
            'name'             => $this->name,
            'description'      => $this->description,
            'logo'             => $this->getMediaData('logo', ['w' => $w, 'h' => $h, 'zc' => 3]),
            'locationType'     => $locationType[$this->location_type],
            'isAllowRegistred' => $isAllowRegistred,
            'registeredSpots'  => (!empty($this->registered_users) ? $this->registered_users : (!empty($totalBookedUsers) ? $totalBookedUsers : 0)),
            'creator'          => $this->getCreatorData(),
            'presenter'        => [
                'id'    => (!empty($presenter) ? $presenter->getKey() : $this->presenter_user_id),
                'name'  => (!empty($presenter) ? $presenter->full_name : $meta->presenter),
                'image' => (!empty($presenter) ? $presenter->getMediaData('logo', ['w' => 600, 'h' => 600]) : [
                    "width"  => 600,
                    "height" => 600,
                    "url"    => getDefaultFallbackImageURL("user", "user-none1"),
                ]),
            ],
            'isOngoing'        => $now->between($startTime, $endTime),
            'isRegistered'     => $isRegistered,
            'bookingDate'      => $startTime->toAtomString(),
            'createdAt'        => $this->created_at->setTimezone($user->timezone)->toAtomString(),
        ];
    }
}
