<?php

namespace App\Http\Resources\V1;

use App\Http\Traits\ProvidesAuthGuardTrait;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class MyGroupListResource extends JsonResource
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
        /** @var User $user */
        $user = $this->user();

        $loginUserData    = $this->members()->wherePivot("user_id", $user->getKey())->first();
        $fetchLastMessage = $this->groupMessages()->wherePivot('created_at', '>=', $loginUserData->pivot->created_at)->orderBy('group_messages.id', 'DESC')->limit(1)->first();

        $lastMessage          = "";
        $lastMessageTimeStamp = "";

        if (!empty($fetchLastMessage)) {
            $lastMessage = (!empty($fetchLastMessage->pivot->message)) ? $fetchLastMessage->pivot->message : "";

            if (!empty($fetchLastMessage->pivot->model_id) && !empty($fetchLastMessage->pivot->model_name)) {
                $lastMessage = $fetchLastMessage->pivot->model_name . " Content";
            }

            $lastMessageTimeStamp = Carbon::parse($fetchLastMessage->pivot->created_at, config('app.timezone'))->setTimezone($user->timezone)->toAtomString();

            if ($fetchLastMessage->pivot->deleted ) {
                if ($fetchLastMessage->pivot->user_id == $user->id) {
                    $lastMessage = "You deleted this message";
                } else {
                    $lastMessage = "This message was deleted";
                }
            }
        }

        $totalMessageCount = $this->groupMessages()->wherePivot('created_at', '>=', $loginUserData->pivot->created_at)->count();

        $readCount = \DB::table("group_messages_user_log")
            ->join('group_messages', 'group_messages.id', '=', 'group_messages_user_log.group_message_id')
            ->where('group_messages_user_log.user_id', $user->id)
            ->where('group_messages_user_log.group_id', $this->id)
            ->where('group_messages.created_at', '>=', $loginUserData->pivot->created_at)
            ->where('group_messages_user_log.read', true)
            ->count();

        $unreadCount = $totalMessageCount - $readCount;

        return [
            'id'                   => $this->id,
            'name'                 => $this->title,
            'image'                => $this->getMediaData('logo', ['w' => 320, 'h' => 320]),
            'creator'              => $this->getCreatorData(),
            'members'              => (!empty($this->members)) ? $this->members : 0,
            'lastMessage'          => $lastMessage,
            'unReadMessageCount'   => $unreadCount,
            'isNotificationMute'   => (!empty($loginUserData) && $loginUserData->pivot->notification_muted) ,
            'lastMessageTimeStamp' => $lastMessageTimeStamp,
        ];
    }
}
