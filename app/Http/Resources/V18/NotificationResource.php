<?php

namespace App\Http\Resources\V18;

use App\Http\Traits\ProvidesAuthGuardTrait;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
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
        $xDeviceOs = strtolower($request->header('X-Device-Os', ""));
        $user      = $this->user();
        $timezone  = ($user->timezone ?? config('app.timezone'));
        $date      = ((!empty($this->pivot->sent_on)) ? Carbon::parse($this->pivot->sent_on)->setTimezone($timezone)->toAtomString() : null);

        $deepLinkURL = $this->deep_link_uri;
        $message     = $this->message;
        if ($xDeviceOs == config('zevolifesettings.PORTAL')) {
            $deepLinkURL = portalDeeplinkURL($this->tag, $this->title, $this->deep_link_uri);
        }
        // __($message, [
        //      'first_name' => $user->first_name,
        // ])
        return [
            'id'          => $this->id,
            'title'       => $this->title,
            'message'     => $message,
            'date'        => $date,
            'image'       => [
                'url'    => (!empty($this->tag) ? getStaticAlertIconUrl($this->tag) : $this->logo),
                'width'  => 0,
                'height' => 0,
            ],
            'isRead'      => (bool)$this->pivot->read,
            'deepLinkURL' => $deepLinkURL,
        ];
    }
}
