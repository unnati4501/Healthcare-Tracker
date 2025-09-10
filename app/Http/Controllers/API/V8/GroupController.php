<?php declare (strict_types = 1);

namespace App\Http\Controllers\API\V8;

use App\Http\Controllers\API\V6\GroupController as v6GroupController;
use App\Http\Traits\ProvidesAuthGuardTrait;
use App\Http\Traits\ServesApiTrait;
use App\Models\Group;
use App\Models\GroupMessage;
use App\Models\User;
use DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GroupController extends v6GroupController
{
    use ServesApiTrait, ProvidesAuthGuardTrait;

    /**
     * Forward messages to multiple group.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function forwardToMultipleGroups(Request $request)
    {
        try {
            $user = $this->user();

            $groupIds = $request->groupIds;

            foreach ($groupIds as $key => $value) {
                $group   = Group::find($value);
                $message = GroupMessage::find($request->messageId);

                if (!empty($group) && !empty($message)) {
                    $forwardGroupMember = $group->members()->wherePivot('user_id', $user->getKey())->wherePivot('group_id', $group->getKey())->first();

                    $existingGroupMember = DB::table('group_members')->where("user_id", $user->getKey())->where("group_id", $message->group_id)->first();

                    if (empty($forwardGroupMember) || empty($existingGroupMember)) {
                        return $this->notFoundResponse("You are not authorized to perform this operation");
                    }

                    if ($message->deleted) {
                        return $this->notFoundResponse("Message is already deleted");
                    }

                    $messageInput = [
                        'message'    => $message->message,
                        'model_id'   => $message->model_id,
                        'model_name' => $message->model_name,
                    ];

                    if ($message->type == 'image') {
                        $messageInput = [
                            'type'       => 'image',
                            'message'    => null,
                            'model_id'   => $message->model_id,
                            'model_name' => $message->model_name,
                        ];
                    }

                    \DB::beginTransaction();

                    $group->groupMessages()->attach($user, $messageInput);
                    $group->update(['updated_at' => now()->toDateTimeString()]);

                    \DB::commit();

                    if ($message->type == 'image' && !empty($message->getFirstMediaUrl('image'))) {
                        $groupMessage = GroupMessage::where('user_id', $user->id)
                            ->where('group_id', $group->id)
                            ->orderBy('id', 'DESC')
                            ->first();

                        $media     = $message->getFirstMedia('image');
                        $imageData = explode(".", $media->file_name);
                        $name      = $groupMessage->id . '_' . \time();
                        $groupMessage->clearMediaCollection('image')
                            ->addMediaFromUrl(
                                $message->getFirstMediaUrl('image'),
                                $groupMessage->getAllowedMediaMimeTypes('image')
                            )
                            ->usingName($media->name)
                            ->usingFileName($name . '.' . $imageData[1])
                            ->toMediaCollection('image', config('medialibrary.disk_name'));
                    }
                }
            }

            return $this->successResponse(['data' => []], trans('api_messages.group.forward'));
        } catch (\Exception $e) {
            \DB::rollback();
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }
}
