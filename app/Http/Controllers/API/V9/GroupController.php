<?php declare (strict_types = 1);

namespace App\Http\Controllers\API\V9;

use App\Http\Collections\V9\GroupMessagesCollection;
use App\Http\Collections\V9\MyGroupListCollection;
use App\Http\Controllers\API\V8\GroupController as v8GroupController;
use App\Http\Requests\Api\V1\SendMessageToGroupRequest;
use App\Http\Resources\V9\GroupDetailsResource;
use App\Http\Resources\V9\GroupMessagesResource;
use App\Http\Traits\ProvidesAuthGuardTrait;
use App\Http\Traits\ServesApiTrait;
use App\Models\Group;
use App\Models\GroupMessage;
use App\Models\SubCategory;
use App\Models\User;
use DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GroupController extends v8GroupController
{
    use ServesApiTrait, ProvidesAuthGuardTrait;

    /**
     * api to fetch group details
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function details(Request $request, Group $group)
    {
        try {
            $user = $this->user();

            $groupExploreData = $group
                ->leftJoin("group_members", function ($join) {
                    $join->on("groups.id", "=", "group_members.group_id")
                        ->where("group_members.status", "Accepted");
                })
                ->select("groups.*", DB::raw("COUNT(group_id) as members"))
                ->where("groups.id", $group->id)
                ->orderBy('groups.updated_at', 'DESC')
                ->groupBy('group_members.group_id')
                ->first();

            // get course details data with json response
            $data = array("data" => new GroupDetailsResource($groupExploreData));

            return $this->successResponse($data, 'Detail retrieved successfully.');
        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * Send message to group
     *
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
                    DB::table("group_messages_user_log")
                        ->updateOrInsert(
                            ['group_message_id' => $groupMessagesData->pivot->id, 'user_id' => $value],
                            ['read' => true, 'group_id' => $group->getKey()]
                        );
                }
            }

            // dispatch job to awarg badge to user for running challenge
            // Unread message in group notification has been disabled as an update.
            // $this->dispatch(new SendGroupPushNotification($group, 'message-in-group', '', $user->id));

            if ($groupMessagesData->count() > 0) {
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

    /**
     * Get group list
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function exploreGroups(Request $request)
    {
        try {
            $user    = $this->user();
            $company = $user->company()->first();

            $subCategory = [];
            if (!empty($request->subCategory)) {
                $subCategory = SubCategory::find($request->subCategory);
            }

            $groupIds = Group::join("group_members", "groups.id", "=", "group_members.group_id")
                ->where("group_members.user_id", $user->getKey())
                ->where(function ($query) use ($company) {
                    $query->where('groups.company_id', $company->id)
                        ->orWhere('groups.company_id', null);
                })
                ->where('groups.is_visible', 1)
                ->where('groups.is_archived', 0)
                ->groupBy('group_members.group_id')
                ->pluck("groups.id")
                ->toArray();

            if ($subCategory->short_name == 'public') {
                $groupIds = Group::whereNotIn('id', $groupIds)
                    ->where(function ($query) use ($company) {
                        $query->where('groups.company_id', $company->id)
                            ->orWhere('groups.company_id', null);
                    })
                    ->where('groups.type', 'public')
                    ->where('groups.is_visible', 1)
                    ->where('groups.is_archived', 0)
                    ->pluck('groups.id')
                    ->toArray();
            }

            if (!empty($groupIds)) {
                $groupExploreData = Group::join("group_members", function ($join) {
                    $join->on("groups.id", "=", "group_members.group_id")
                        ->where("group_members.status", "Accepted");
                })->select("groups.*", DB::raw("COUNT(group_id) as members"))
                    ->whereIn("groups.id", $groupIds);

                if (!empty($subCategory) && $subCategory->short_name != 'public') {
                    $groupExploreData = $groupExploreData->where('groups.sub_category_id', $subCategory->id);
                }

                $groupExploreData = $groupExploreData->orderBy('groups.updated_at', 'DESC')
                    ->groupBy('group_members.group_id')
                    ->paginate(config('zevolifesettings.datatable.pagination.short'));

                $total                        = $user->userUnreadMsgCount();
                $return                       = [];
                $return['unreadMessageCount'] = $total;

                $return['data'] = [];
                if ($groupExploreData->count() > 0) {
                    $return = new MyGroupListCollection($groupExploreData, $total);
                }

                // return response
                return $this->successResponse(
                    $return,
                    ($groupExploreData->count() > 0) ? 'Group List retrieved successfully.' : "No results"
                );
            } else {
                // return empty response
                return $this->successResponse(['data' => []], 'No results');
            }
        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

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

            $messageData = [];
            foreach ($groupIds as $key => $value) {
                $group   = Group::find($value);
                $message = GroupMessage::find($request->messageId);

                if (!empty($group) && !empty($message)) {
                    $forwardGroupMember = $group->members()->wherePivot('user_id', $user->getKey())->wherePivot('group_id', $group->getKey())->first();

                    $existingGroupMember = DB::table('group_members')->where("user_id", $user->getKey())->where("group_id", $message->group_id)->first();

                    if (empty($forwardGroupMember) || empty($existingGroupMember)) {
                        return $this->notFoundResponse("You are not authorized to perform this operation");
                    }

                    if ($message->deleted ) {
                        return $this->notFoundResponse("Message is already deleted");
                    }

                    $messageInput = [
                        'message'    => $message->message,
                        'model_id'   => $message->model_id,
                        'model_name' => $message->model_name,
                        'forwarded'  => 1,
                    ];

                    if ($message->type == 'image') {
                        $messageInput = [
                            'type'       => 'image',
                            'message'    => null,
                            'model_id'   => $message->model_id,
                            'model_name' => $message->model_name,
                            'forwarded'  => 1,
                        ];
                    }

                    $group->groupMessages()->attach($user, $messageInput);
                    $group->update(['updated_at' => now()->toDateTimeString()]);

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

                $groupMessagesData = $group->groupMessages()
                    ->wherePivot('user_id', '=', $user->getKey())
                    ->wherePivot('group_id', '=', $group->getKey())
                    ->orderBy('group_messages.created_at', 'DESC')
                    ->limit(1)
                    ->first();

                $messageData[$group->id] = new GroupMessagesResource($groupMessagesData);
            }

            return $this->successResponse(['data' => $messageData], trans('api_messages.group.forward'));
        } catch (\Exception $e) {
            \DB::rollback();
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * Get all messages in a group
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function groupMessages(Request $request, Group $group)
    {
        try {
            $user    = $this->user();
            $team    = $user->teams()->first();
            $company = $user->company()->first();

            $pivotExsisting = $group->members()
                ->wherePivot('user_id', $user->getKey())
                ->wherePivot('group_id', $group->getKey())
                ->first();

            if (empty($pivotExsisting)) {
                return $this->notFoundResponse("You are not authorized to perform this operation");
            }

            $userTimeZone = $user->timezone;

            $defaultTimeZone = config('app.timezone');

            $userGroupMessageDelete = $group->groupMessagesUserDeleteLog()
                ->wherePivot("group_id", $group->id)
                ->wherePivot("user_id", $user->id)
                ->orderBy("group_messages_user_delete_log.created_at", "DESC")
                ->first();

            $groupUser = $group->members()
                ->wherePivot('user_id', $user->getKey())
                ->wherePivot('group_id', $group->getKey())
                ->first();

            $teamRestriction = null;
            if ($group->model_name == 'challenge') {
                $teamRestriction = $group->leftJoin('challenges', 'challenges.id', '=', 'groups.model_id')
                    ->where('challenges.challenge_type', 'team')
                    ->where('challenges.id', $group->model_id)
                    ->first();
            }

            $groupMessagesData = $group->groupMessages()
                ->join('user_team', 'user_team.user_id', '=', 'group_messages.user_id')
                ->where(function ($query) use ($teamRestriction, $team, $company) {
                    if (!empty($teamRestriction)) {
                        $query->where('user_team.team_id', $team->getKey());
                    } else {
                        $query->where('user_team.company_id', $company->getKey());
                    }
                });

            if (!empty($userGroupMessageDelete)) {
                $groupMessagesData = $groupMessagesData->wherePivot('created_at', '>', $userGroupMessageDelete->pivot->updated_at)
                    ->orderBy('group_messages.created_at', 'DESC');
            }

            if (!empty($groupUser)) {
                $groupMessagesData = $groupMessagesData->wherePivot('created_at', '>=', $groupUser->pivot->created_at)
                    ->orderBy('group_messages.created_at', 'DESC');
            }

            // mark all messages as read
            $lastReadMsg = DB::table("group_messages_user_log")
                ->select('group_messages_user_log.group_message_id')
                ->where(['user_id' => $user->id, 'group_id' => $group->id, 'read' => true])
                ->orderByDesc('group_messages_user_log.group_message_id')
                ->first();

            // if (!empty($lastReadMsg) && !empty($lastReadMsg->group_message_id)) {
            //     $groupMessagesDataToRead = $group->groupMessages()
            //         ->join('user_team', 'user_team.user_id', '=', 'group_messages.user_id')
            //         ->where(function ($query) use ($teamRestriction, $team, $company) {
            //             if (!empty($teamRestriction)) {
            //                 $query->where('user_team.team_id', $team->getKey());
            //             } else {
            //                 $query->where('user_team.company_id', $company->getKey());
            //             }
            //         })
            //         ->wherePivot('id', '>', $lastReadMsg->group_message_id)
            //         ->orderBy('group_messages.created_at', 'DESC')
            //         ->pluck('group_messages.id')
            //         ->toArray();

            //     if (!empty($groupMessagesDataToRead)) {
            //         foreach ($groupMessagesDataToRead as $key => $messageId) {
            //             $messageLog = DB::table("group_messages_user_log")
            //                 ->updateOrInsert(
            //                     [
            //                         'group_message_id' => $messageId,
            //                         'user_id'          => $user->id,
            //                     ],
            //                     [
            //                         'read'     => true,
            //                         'group_id' => $group->id,
            //                     ]
            //                 );
            //         }
            //     }
            // } else {
            //     $groupMessagesDataToRead = $group->groupMessages()
            //         ->join('user_team', 'user_team.user_id', '=', 'group_messages.user_id')
            //         ->where(function ($query) use ($teamRestriction, $team, $company) {
            //             if (!empty($teamRestriction)) {
            //                 $query->where('user_team.team_id', $team->getKey());
            //             } else {
            //                 $query->where('user_team.company_id', $company->getKey());
            //             }
            //         })
            //         ->orderBy('group_messages.created_at', 'DESC')
            //         ->pluck('group_messages.id')
            //         ->toArray();

            //     if (!empty($groupMessagesDataToRead)) {
            //         foreach ($groupMessagesDataToRead as $key => $messageId) {
            //             $messageLog = DB::table("group_messages_user_log")
            //                 ->updateOrInsert(
            //                     [
            //                         'group_message_id' => $messageId,
            //                         'user_id'          => $user->id,
            //                     ],
            //                     [
            //                         'read'     => true,
            //                         'group_id' => $group->id,
            //                     ]
            //                 );
            //         }
            //     }
            // }

            $groupMessagesData = $groupMessagesData->paginate(config('zevolifesettings.datatable.pagination.short'));

            if ($groupMessagesData->count() > 0) {
                // collect required data and return response
                return $this->successResponse(new GroupMessagesCollection($groupMessagesData, true), 'Group messages retrieved successfully');
            } else {
                // return empty response
                return $this->successResponse(['data' => []], 'No results');
            }
        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }
}
