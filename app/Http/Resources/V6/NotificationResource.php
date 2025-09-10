<?php

namespace App\Http\Resources\V6;

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
        $user     = $this->user();
        $timezone = ($user->timezone ?? config('app.timezone'));
        $date     = ((!empty($this->pivot->sent_on)) ? Carbon::parse($this->pivot->sent_on)->setTimezone($timezone)->toAtomString() : null);

        return [
            'id'          => $this->id,
            'title'       => $this->title,
            'message'     => $this->message,
            'date'        => $date,
            'image'       => [
                'url'    => (!empty($this->tag) ? getStaticAlertIconUrl($this->tag) : $this->logo),
                'width'  => 0,
                'height' => 0,
            ],
            'isRead'      => (($this->pivot->read) ? true : false),
            'deepLinkURL' => $this->deep_link_uri,
        ];
    }
}
