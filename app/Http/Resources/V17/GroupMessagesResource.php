<?php

namespace App\Http\Resources\V17;

use App\Http\Traits\ProvidesAuthGuardTrait;
use App\Models\Group;
use App\Models\GroupMessage;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Resources\Json\JsonResource;

class GroupMessagesResource extends JsonResource
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

        $message      = "";
        $message      = (!empty($this->pivot->message)) ? $this->pivot->message : "";
        $isFavourited = false;
        if ($this->pivot->deleted ) {
            if ($this->pivot->user_id == $user->id) {
                $message = "You deleted this message";
            } else {
                $message = "This message was deleted";
            }
        } else {
            $userFavouriteData = DB::table("group_messages_user_log")->where("group_message_id", $this->pivot->id)->where("user_id", $user->id)->first();
            if (!empty($userFavouriteData) && $userFavouriteData->favourited ) {
                $isFavourited = true;
            }
        }

        $messageLog = DB::table("group_messages_user_log")->updateOrInsert(
            ['group_message_id' => $this->pivot->id, 'user_id' => $user->id],
            ['read' => true, 'group_id' => $this->pivot->group_id]
        );

        $dataArray                 = array();
        $dataArray['id']           = $this->pivot->id;
        $dataArray['parentId']     = (!empty($this->pivot->group_message_id)) ? $this->pivot->group_message_id : 0;
        $dataArray['message']      = $message;
        $dataArray['isForward']    = (bool)$this->pivot->forwarded ;
        $dataArray['deleted']      = (bool)$this->pivot->deleted ;
        $dataArray['isFavourited'] = $isFavourited;
        $dataArray['createdAt']    = Carbon::parse($this->pivot->created_at, config('app.timezone'))->setTimezone($user->timezone)->toAtomString();

        if ($this->pivot->is_broadcast ) {
            $dataArray['creator'] = $this->getBroadcastCreatorData();
        } else {
            $dataArray['creator'] = $this->getUserDataForApi();
        }

        if ($this->pivot->deleted ) {
            $dataArray['type'] = "message";
        } else {
            $dataArray['type'] = (!empty($this->pivot->model_id) && !empty($this->pivot->model_name)) ? "shared" : "message";
        }

        if (!empty($this->pivot->model_id) && !empty($this->pivot->model_name) && !$this->pivot->deleted) {
            $model = $this->getSharedModelData();

            if (!empty($model)) {
                $dataArray['sharedData'] = $this->setSharedModelData($model);
            } else {
                $dataArray['type']    = 'message';
                $dataArray['message'] = $this->pivot->model_name . " has been deleted.";
            }
        }

        if ($this->pivot->type == 'image' && !$this->pivot->deleted) {
            $groupMessage         = GroupMessage::find($this->pivot->id);
            $dataArray['type']    = 'image';
            $dataArray['message'] = $groupMessage->image;
        }

        if (!empty($this->pivot->group_message_id) && !$this->pivot->deleted) {
            $group              = Group::find($this->pivot->group_id);
            $groupMessageParent = $group->groupMessages()->wherePivot("id", $this->pivot->group_message_id)->first();

            $dataArray['parentItem'] = new self($groupMessageParent);
        }

        $teamRestriction = null;
        if ($this->model_name == 'challenge') {
            $teamRestriction = $this->leftJoin('challenges', 'challenges.id', '=', 'groups.model_id')
                ->where('challenges.challenge_type', 'team')
                ->where('challenges.id', $this->model_id)
                ->first();
        }

        $dataArray['groupRestriction'] = ((!empty($teamRestriction)) ? 'team' : 'company');

        return $dataArray;
    }
}
