<?php

namespace App\Http\Resources\V17;

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
        $user    = $this->user();
        $team    = $user->teams()->first();
        $company = $user->company()->first();

        $teamRestriction = null;
        if ($this->model_name == 'challenge') {
            $teamRestriction = $this->leftJoin('challenges', 'challenges.id', '=', 'groups.model_id')
                ->where('challenges.challenge_type', 'team')
                ->where('challenges.id', $this->model_id)
                ->first();
        }

        $members = $this->members()
            ->join('user_team', 'user_team.user_id', '=', 'group_members.user_id')
            ->where(function ($query) use ($teamRestriction, $team, $company) {
                if (!empty($teamRestriction)) {
                    $query->where('user_team.team_id', $team->getKey());
                } else {
                    $query->where('user_team.company_id', $company->getKey());
                }
            })
            ->count();

        $loginUserData = $this->members()
            ->wherePivot("status", "Accepted")
            ->wherePivot("user_id", $user->getKey())
            ->first();

        $fetchLastMessage = [];
        if ($loginUserData) {
            $fetchLastMessage = $this->groupMessages()
                ->leftJoin('user_team', 'user_team.user_id', '=', 'group_messages.user_id')
                ->where(function ($query) use ($teamRestriction, $team, $company) {
                    if (!empty($teamRestriction)) {
                        $query
                            ->where('user_team.team_id', $team->getKey())
                            ->orWhere('group_messages.broadcast_company_id', $company->getKey())
                            ->orWhere(function ($subWhere) {
                                $subWhere
                                    ->where('group_messages.is_broadcast', 1)
                                    ->WhereNull('group_messages.broadcast_company_id');
                            });
                    } else {
                        $query
                            ->where('user_team.company_id', $company->getKey())
                            ->orWhere('group_messages.broadcast_company_id', $company->getKey())
                            ->orWhere(function ($subWhere) {
                                $subWhere
                                    ->where('group_messages.is_broadcast', 1)
                                    ->WhereNull('group_messages.broadcast_company_id');
                            });
                    }
                })
                ->wherePivot('created_at', '>=', $loginUserData->pivot->created_at)
                ->orderBy('group_messages.id', 'DESC')
                ->limit(1)
                ->first();
        }

        $lastMessage          = "";
        $lastMessageTimeStamp = "";
        $totalMessageCount    = 0;
        $readCount            = 0;

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

            $totalMessageCount = $this->groupMessages()
                ->leftJoin('user_team', 'user_team.user_id', '=', 'group_messages.user_id')
                ->where(function ($query) use ($teamRestriction, $team, $company) {
                    if (!empty($teamRestriction)) {
                        $query
                            ->where('user_team.team_id', $team->getKey())
                            ->orWhere('group_messages.broadcast_company_id', $company->getKey())
                            ->orWhere(function ($subWhere) {
                                $subWhere
                                    ->where('group_messages.is_broadcast', 1)
                                    ->WhereNull('group_messages.broadcast_company_id');
                            });
                    } else {
                        $query
                            ->where('user_team.company_id', $company->getKey())
                            ->orWhere('group_messages.broadcast_company_id', $company->getKey())
                            ->orWhere(function ($subWhere) {
                                $subWhere
                                    ->where('group_messages.is_broadcast', 1)
                                    ->WhereNull('group_messages.broadcast_company_id');
                            });
                    }
                })
                ->wherePivot('created_at', '>=', $loginUserData->pivot->created_at)
                ->count();

            $readCount = \DB::table("group_messages_user_log")
                ->join('group_messages', 'group_messages.id', '=', 'group_messages_user_log.group_message_id')
                ->leftJoin('user_team', 'user_team.user_id', '=', 'group_messages.user_id')
                ->where(function ($query) use ($teamRestriction, $team, $company) {
                    if (!empty($teamRestriction)) {
                        $query
                            ->where('user_team.team_id', $team->getKey())
                            ->orWhere('group_messages.broadcast_company_id', $company->getKey())
                            ->orWhere(function ($subWhere) {
                                $subWhere
                                    ->where('group_messages.is_broadcast', 1)
                                    ->WhereNull('group_messages.broadcast_company_id');
                            });
                    } else {
                        $query
                            ->where('user_team.company_id', $company->getKey())
                            ->orWhere('group_messages.broadcast_company_id', $company->getKey())
                            ->orWhere(function ($subWhere) {
                                $subWhere
                                    ->where('group_messages.is_broadcast', 1)
                                    ->WhereNull('group_messages.broadcast_company_id');
                            });
                    }
                })
                ->where('group_messages_user_log.user_id', $user->id)
                ->where('group_messages_user_log.group_id', $this->id)
                ->where('group_messages.created_at', '>=', $loginUserData->pivot->created_at)
                ->where('group_messages_user_log.read', true)
                ->count();
        }

        $unreadCount = $totalMessageCount - $readCount;

        return [
            'id'                   => $this->id,
            'name'                 => $this->title,
            'image'                => $this->getMediaData('logo', ['w' => 640, 'h' => 640, 'zc' => 3]),
            'creator'              => $this->getCreatorData(),
            'members'              => (!empty($members)) ? $members : 0,
            'lastMessage'          => $lastMessage,
            'unReadMessageCount'   => $unreadCount,
            'isNotificationMute'   => (!empty($loginUserData) && $loginUserData->pivot->notification_muted) ,
            'lastMessageTimeStamp' => $lastMessageTimeStamp,
            'isMember'             => ((!empty($loginUserData)) ),
        ];
    }
}
