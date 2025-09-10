<?php declare (strict_types = 1);

namespace App\Http\Controllers\API\V33;

use App\Http\Controllers\API\V27\GroupController as v27GroupController;
use App\Http\Requests\Api\V1\SendMessageToGroupRequest;
use App\Http\Resources\V9\GroupMessagesResource;
use App\Http\Traits\ProvidesAuthGuardTrait;
use App\Http\Traits\ServesApiTrait;
use App\Models\Group;
use App\Models\GroupMessage;
use App\Models\User;
use DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GroupController extends v27GroupController
{
    use ServesApiTrait, ProvidesAuthGuardTrait;
    /**
     * Send message to group
     *
     * @param SendMessageToGroupRequest $request, Group $group
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendGroupMessages(SendMessageToGroupRequest $request, Group $group)
    {
        try {
            $user = $this->user();

            $pivotExsisting = $group->members()
                ->wherePivot('user_id', $user->getKey())
                ->wherePivot('group_id', $group->getKey())
                ->first();

            if (empty($pivotExsisting)) {
                return $this->notFoundResponse("You are not authorized to perform this operation");
            }

            if ($request->type == 'message') {
                $messageInput = [
                    'message' => $request->message,
                ];
            } else {
                $messageInput = [
                    'type' => 'image',
                ];
            }

            if (!empty($request->parentId)) {
                $groupParentData = $group->groupMessages()
                    ->wherePivot('id', '=', $request->parentId)
                    ->wherePivot('deleted', false)
                    ->first();

                if (empty($groupParentData)) {
                    return $this->notFoundResponse("This message was already deleted");
                }

                $messageInput['group_message_id'] = $request->parentId;
            }

            $group->groupMessages()->attach($user, $messageInput);
            $group->update(['updated_at' => now()->toDateTimeString()]);

            $groupMessage = GroupMessage::where('user_id', $user->id)
                ->where('group_id', $group->id)
                ->orderBy('id', 'DESC')
                ->first();

            if (isset($request->imageUrl) && !empty($request->imageUrl)) {
                $name      = $groupMessage->id . '_' . \time();
                $extension = explode('.', $request->imageName);
                $groupMessage->clearMediaCollection('image')
                    ->addMediaFromUrl($request->imageUrl)
                    ->usingName($request->imageName)
                    ->usingFileName($name . '.' . $extension[1])
                    ->toMediaCollection('image', config('medialibrary.disk_name'));
            }

            if (!empty($request->lastMessageId)) {
                $groupMessagesData = $group->groupMessages()
                    ->wherePivot('id', '>', $request->lastMessageId)
                    ->orderBy('group_messages.created_at', 'DESC')
                    ->first();
            } else {
                $groupMessagesData = $group->groupMessages()
                    ->wherePivot('user_id', '=', $user->getKey())
                    ->wherePivot('group_id', '=', $group->getKey())
                    ->orderBy('group_messages.created_at', 'DESC')
                    ->limit(1)
                    ->first();
            }

            if (!empty($groupMessagesData) && !empty($request->userIds)) {
                foreach ($request->userIds as $key => $value) {
                    if (!empty($value)) {
                        DB::table("group_messages_user_log")
                            ->updateOrInsert(
                                ['group_message_id' => $groupMessagesData->pivot->id, 'user_id' => $value],
                                ['read' => true, 'group_id' => $group->getKey()]
                            );
                    }
                }
            }

            // dispatch job to awarg badge to user for running challenge
            // Unread message in group notification has been disabled as an update.
            // $this->dispatch(new SendGroupPushNotification($group, 'message-in-group', '', $user->id));

            if ($groupMessagesData->count() > 0) {
                UpdatePointContentActivities('group', $groupMessage->id, $user->id, 'sending_message');
                // collect required data and return response
                return $this->successResponse(['data' => new GroupMessagesResource($groupMessagesData)], 'Message sent successfully.');
            } else {
                // return empty response
                return $this->successResponse(['data' => []], 'Message sent successfully.');
            }
        } catch (\Exception $e) {
            \DB::rollback();
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }
}
