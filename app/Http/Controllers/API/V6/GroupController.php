<?php declare (strict_types = 1);

namespace App\Http\Controllers\API\V6;

use App\Http\Collections\V1\GroupMemberCollection;
use App\Http\Collections\V6\GroupMessagesCollection;
use App\Http\Collections\V6\MyGroupListCollection;
use App\Http\Controllers\API\V5\GroupController as v5GroupController;
use App\Http\Requests\Api\V1\SendMessageToGroupRequest;
use App\Http\Resources\V6\GroupDetailsResource;
use App\Http\Traits\ProvidesAuthGuardTrait;
use App\Http\Traits\ServesApiTrait;
use App\Models\Group;
use App\Models\GroupMessage;
use App\Models\User;
use DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GroupController extends v5GroupController
{
    use ServesApiTrait, ProvidesAuthGuardTrait;

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

            if (!empty($groupIds)) {
                $groupExploreData = Group::leftJoin("group_members", function ($join) {
                    $join->on("groups.id", "=", "group_members.group_id")
                        ->where("group_members.status", "Accepted");
                })->select("groups.*", DB::raw("COUNT(group_id) as members"))
                    ->whereIn("groups.id", $groupIds);

                if (!empty($request->subCategory)) {
                    $groupExploreData = $groupExploreData->where('groups.sub_category_id', $request->subCategory);
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
     * Get Archived group list
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function archivedGroups(Request $request)
    {
        try {
            $user    = $this->user();
            $company = $user->company()->first();

            $groupIds = Group::join("group_members", "groups.id", "=", "group_members.group_id")
                ->where("group_members.user_id", $user->getKey())
                ->where(function ($query) use ($company) {
                    $query->where('groups.company_id', $company->id)
                        ->orWhere('groups.company_id', null);
                })
                ->where('groups.is_visible', 1)
                ->where('groups.is_archived', 1)
                ->groupBy('group_members.group_id')
                ->pluck("groups.id")
                ->toArray();

            if (!empty($groupIds)) {
                $groupExploreData = Group::leftJoin("group_members", function ($join) {
                    $join->on("groups.id", "=", "group_members.group_id")
                        ->where("group_members.status", "Accepted");
                })->select("groups.*", DB::raw("COUNT(group_id) as members"))
                    ->whereIn("groups.id", $groupIds);

                if (!empty($request->subCategory)) {
                    $groupExploreData = $groupExploreData->where('groups.sub_category_id', $request->subCategory);
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

            \DB::beginTransaction();

            $group->groupMessages()->attach($user, $messageInput);
            $group->update(['updated_at' => now()->toDateTimeString()]);

            \DB::commit();

            $groupMessage = GroupMessage::where('user_id', $user->id)
                ->where('group_id', $group->id)
                ->orderBy('id', 'DESC')
                ->first();

            if (isset($request->image) && !empty($request->image)) {
                $name = $groupMessage->id . '_' . \time();
                $groupMessage->clearMediaCollection('image')
                    ->addMediaFromRequest('image')
                    ->usingName($request->image->getClientOriginalName())
                    ->usingFileName($name . '.' . $request->image->extension())
                    ->toMediaCollection('image', config('medialibrary.disk_name'));
            }

            if (!empty($request->lastMessageId)) {
                $groupMessagesData = $group->groupMessages()
                    ->wherePivot('id', '>', $request->lastMessageId)
                    ->orderBy('group_messages.created_at', 'DESC')
                    ->get();
            } else {
                $groupMessagesData = $group->groupMessages()
                    ->wherePivot('user_id', '=', $user->getKey())
                    ->wherePivot('group_id', '=', $group->getKey())
                    ->orderBy('group_messages.created_at', 'DESC')
                    ->limit(1)
                    ->get();
            }

            // dispatch job to awarg badge to user for running challenge
            // Unread message in group notification has been disabled as an update.
            // $this->dispatch(new SendGroupPushNotification($group, 'message-in-group', '', $user->id));

            if ($groupMessagesData->count() > 0) {
                // collect required data and return response
                return $this->successResponse(new GroupMessagesCollection($groupMessagesData), 'Message sent successfully.');
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
     * Display list of starred messages
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function starredMessages(Request $request, Group $group)
    {
        try {
            // logged-in user
            $user = $this->user();

            // fetch last clear chat history date and time of user for that group
            $userGroupMessageDelete = $group->groupMessagesUserDeleteLog()
                ->wherePivot("group_id", $group->id)
                ->wherePivot("user_id", $user->id)
                ->orderBy("group_messages_user_delete_log.created_at", "DESC")
                ->first();

            // query to fetch all starred message of user
            $groupMessagesData = $group->groupMessages()
                ->join("group_messages_user_log", "group_messages.id", "=", "group_messages_user_log.group_message_id")
                ->where('group_messages_user_log.user_id', '=', $user->id)
                ->where('group_messages_user_log.favourited', true)
                ->wherePivot('deleted', false);

            // condition to get all message after given date and time of clear chat history
            if (!empty($userGroupMessageDelete)) {
                $groupMessagesData = $groupMessagesData->wherePivot('created_at', '>', $userGroupMessageDelete->pivot->updated_at);
            }

            $groupMessagesData = $groupMessagesData->orderBy('group_messages.created_at', 'DESC')
                ->paginate(config('zevolifesettings.datatable.pagination.short'));

            if ($groupMessagesData->count() > 0) {
                // collect required data and return response
                return $this->successResponse(new GroupMessagesCollection($groupMessagesData, true), 'Group messages retrieved successfully.');
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
     * Get group members listing.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function groupMembers(Request $request, Group $group)
    {
        try {
            $user    = $this->user();
            $team    = $user->teams()->first();
            $company = $user->company()->first();

            $teamRestriction = null;
            if ($group->model_name == 'challenge') {
                $teamRestriction = $group->leftJoin('challenges', 'challenges.id', '=', 'groups.model_id')
                    ->where('challenges.challenge_type', 'team')
                    ->where('challenges.id', $group->model_id)
                    ->first();
            }

            $groupMembersData = $group->members()
                ->leftJoin("badge_user", function ($join) {
                    $join->on("group_members.user_id", "=", "badge_user.user_id")
                        ->where("badge_user.status", "Active");
                })
                ->join('user_team', 'user_team.user_id', '=', 'group_members.user_id')
                ->where(function ($query) use ($teamRestriction, $team, $company) {
                    if (!empty($teamRestriction)) {
                        $query->where('user_team.team_id', $team->getKey());
                    } else {
                        $query->where('user_team.company_id', $company->getKey());
                    }
                })
                ->select("group_members.*", DB::raw("COUNT(badge_user.id) as activeBadgesCount"))
                ->wherePivot("status", "Accepted")
                ->orderBy(\DB::raw("CONCAT(users.first_name,' ',users.last_name)"))
                ->orderBy('group_members.updated_at', 'DESC')
                ->groupBy('group_members.user_id')
                ->paginate(config('zevolifesettings.datatable.pagination.short'));

            $total = $group->members()
                ->join('user_team', 'user_team.user_id', '=', 'group_members.user_id')
                ->where(function ($query) use ($teamRestriction, $team, $company) {
                    if (!empty($teamRestriction)) {
                        $query->where('user_team.team_id', $team->getKey());
                    } else {
                        $query->where('user_team.company_id', $company->getKey());
                    }
                })
                ->count();

            // collect required data
            $return          = [];
            $return['total'] = $total;
            $return['data']  = [];
            if ($groupMembersData->count() > 0) {
                $return = new GroupMemberCollection($groupMembersData, $total);
            }

            // return response
            return $this->successResponse(
                $return,
                ($groupMembersData->count() > 0) ? 'Group members retrieved successfully.' : "No results"
            );
        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * Forward messages to group.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function forwardGroupMessages(Request $request, Group $group, GroupMessage $message)
    {
        try {
            $user = $this->user();

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

            return $this->successResponse(['data' => []], trans('api_messages.group.forward'));
        } catch (\Exception $e) {
            \DB::rollback();
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * get latest message after given message id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getLatestMessage(Request $request, Group $group)
    {
        try {
            $user    = $this->user();
            $team    = $user->teams()->first();
            $company = $user->company()->first();

            $teamRestriction = null;
            if ($group->model_name == 'challenge') {
                $teamRestriction = $group->leftJoin('challenges', 'challenges.id', '=', 'groups.model_id')
                    ->where('challenges.challenge_type', 'team')
                    ->where('challenges.id', $group->model_id)
                    ->first();
            }

            // check user is memeber of the group or not
            $groupMember = $group->members()
                ->wherePivot('user_id', $user->getKey())
                ->wherePivot('group_id', $group->getKey())
                ->first();

            if (empty($groupMember)) {
                // if user is not member of group then return required response
                return $this->notFoundResponse("You are not authorized to perform this operation");
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

            // $groupMessagesDataToRead = $groupMessagesData;

            if (!empty($groupMember)) {
                $groupMessagesData = $groupMessagesData->wherePivot('created_at', '>=', $groupMember->pivot->created_at);
            }

            if (!empty($request->messageId)) {
                $groupMessagesData = $groupMessagesData->wherePivot('id', '>', $request->messageId)
                    ->orderBy('group_messages.created_at', 'DESC')
                    ->get();
            } else {
                $groupMessagesData = $groupMessagesData->orderBy('group_messages.created_at', 'DESC')
                    ->paginate(config('zevolifesettings.datatable.pagination.short'));
            }

            // $groupMessagesDataToRead = $groupMessagesDataToRead->orderBy('group_messages.created_at', 'DESC')
            //     ->pluck('group_messages.id')
            //     ->toArray();

            // if (!empty($groupMessagesDataToRead)) {
            //     foreach ($groupMessagesDataToRead as $key => $messageId) {
            //         $messageLog = DB::table("group_messages_user_log")
            //             ->updateOrInsert(
            //                 [
            //                     'group_message_id' => $messageId,
            //                     'user_id'          => $user->id,
            //                 ],
            //                 [
            //                     'read'     => true,
            //                     'group_id' => $group->id,
            //                 ]
            //             );
            //     }
            // }

            if ($groupMessagesData->count() > 0) {
                // collect required data and return response
                return $this->successResponse(new GroupMessagesCollection($groupMessagesData), 'Group messages retrieved successfully');
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
     * api to fetch my group list data
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function myGroupsList(Request $request)
    {
        try {
            $user    = $this->user();
            $company = $user->company()->first();

            // get all group ids which is joined and created by me
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

            if (!empty($groupIds)) {
                $groupExploreData = Group::
                    leftJoin("group_members", function ($join) {
                        $join->on("groups.id", "=", "group_members.group_id")
                        ->where("group_members.status", "Accepted");
                    })
                    ->select("groups.*", DB::raw("COUNT(group_id) as members"))
                    ->whereIn("groups.id", $groupIds);

                if (!empty($request->search) && $request->search != 'all') {
                    $groupExploreData = $groupExploreData->where("groups.title", "LIKE", "%" . $request->search . "%");
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
}
