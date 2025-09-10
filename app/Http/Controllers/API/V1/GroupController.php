<?php
declare (strict_types = 1);

namespace App\Http\Controllers\API\V1;

use App\Http\Collections\V1\GroupListCollection;
use App\Http\Collections\V1\GroupMemberCollection;
use App\Http\Collections\V1\GroupMessagesCollection;
use App\Http\Collections\V1\MyGroupListCollection;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\AddMemberToGroupRequest;
use App\Http\Requests\Api\V1\GroupCreateRequest;
use App\Http\Requests\Api\V1\GroupReportRequest;
use App\Http\Requests\Api\V1\GroupUpdateRequest;
use App\Http\Requests\Api\V1\SendMessageToGroupRequest;
use App\Http\Resources\V1\GroupDetailsResource;
use App\Http\Resources\V1\GroupInfoResource;
use App\Http\Traits\ProvidesAuthGuardTrait;
use App\Http\Traits\ServesApiTrait;
use App\Jobs\SendGroupPushNotification;
use App\Models\Category;
use App\Models\Group;
use App\Models\GroupMessage;
use App\Models\User;
use DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/*use Validator;*/

class GroupController extends Controller
{
    use ServesApiTrait, ProvidesAuthGuardTrait;

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function exploreGroups(Request $request)
    {
        try {
            // logged-in user
            $user    = $this->user();
            $company = $user->company()->first();

            $groupExploreData = Group::
                leftJoin("group_members", function ($join) {
                    $join->on("groups.id", "=", "group_members.group_id")
                    ->where("group_members.status", "Accepted");
                })
                ->select("groups.*", DB::raw("COUNT(group_id) as members"));

            if (!empty($request->ids)) {
                $groupExploreData = $groupExploreData->whereIn('groups.category_id', $request->ids);
            }

            $groupExploreData = $groupExploreData->where('groups.company_id', $company->id)->orderBy('groups.updated_at', 'DESC')
                ->groupBy('group_members.group_id')
                ->paginate(config('zevolifesettings.datatable.pagination.short'));

            if ($groupExploreData->count() > 0) {
                // collect required data and return response
                return $this->successResponse(new GroupListCollection($groupExploreData), 'Group List retrieved successfully');
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
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(GroupCreateRequest $request)
    {
        try {
            \DB::beginTransaction();
            // logged-in user
            $user       = $this->user();
            $company_id = !is_null($user->company->first()) ? $user->company->first()->id : null;

            $checkCategory = Category::find($request->categoryId);

            if (!empty($checkCategory)) {
                $groupInput                = array();
                $groupInput['creator_id']  = $user->id;
                $groupInput['company_id']  = $company_id;
                $groupInput['category_id'] = $request->categoryId;
                $groupInput['title']       = $request->name;
                $groupInput['description'] = $request->description;

                $group = Group::create($groupInput);

                // update user profile image if not empty
                if ($request->hasFile('image')) {
                    $name = $group->getKey() . '_' . \time();
                    $group->clearMediaCollection('logo')
                        ->addMediaFromRequest('image')
                        ->usingName($request->file('image')->getClientOriginalName())
                        ->usingFileName($name . '.' . $request->file('image')->extension())
                        ->toMediaCollection('logo', config('medialibrary.disk_name'));
                }

                $memberIds = json_decode($request->users);

                array_unshift($memberIds, $user->id);

                if ($memberIds) {
                    $membersInput = [
                        'group_id'    => $group->id,
                        'status'      => "Accepted",
                        'joined_date' => now()->toDateTimeString(),
                    ];

                    $group->members()->attach($memberIds, $membersInput);
                }

                \DB::commit();
                // dispatch job to awarg badge to user for running challenge
                $this->dispatch(new SendGroupPushNotification($group, 'new-group'));

                return $this->successResponse([], 'Group Created Successfully');
            } else {
                return $this->notFoundResponse("Category data not found");
            }
        } catch (\Exception $e) {
            \DB::rollback();
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(GroupUpdateRequest $request, Group $group)
    {
        try {
            \DB::beginTransaction();
            // logged-in user
            $user = $this->user();

            if ($group->creator_id != $user->getKey()) {
                return $this->notFoundResponse("You are not authorized to update this group");
            }

            $checkCategory = Category::find($request->categoryId);

            if (!empty($checkCategory)) {
                $groupInput                = array();
                $groupInput['category_id'] = $request->categoryId;
                $groupInput['title']       = $request->name;
                $groupInput['description'] = $request->description;

                $updated = $group->update($groupInput);

                // update user profile image if not empty
                if ($request->hasFile('image')) {
                    $name = $group->getKey() . '_' . \time();
                    $group->clearMediaCollection('logo')
                        ->addMediaFromRequest('image')
                        ->usingName($request->file('image')->getClientOriginalName())
                        ->usingFileName($name . '.' . $request->file('image')->extension())
                        ->toMediaCollection('logo', config('medialibrary.disk_name'));
                }

                \DB::commit();
                return $this->successResponse([], 'Group Updated Successfully');
            } else {
                return $this->notFoundResponse("Category data not found");
            }
        } catch (\Exception $e) {
            \DB::rollback();
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function addMembersToGroup(AddMemberToGroupRequest $request, Group $group)
    {
        try {
            \DB::beginTransaction();
            // logged-in user
            $user = $this->user();

            if ($group->creator_id != $user->getKey()) {
                return $this->notFoundResponse("You are not authorized to add member to this group");
            }

            $memberIds = $request->members;

            if ($memberIds) {
                $membersInput = [
                    'group_id'    => $group->id,
                    'status'      => "Accepted",
                    'joined_date' => now()->toDateTimeString(),
                ];

                $group->members()->attach($memberIds, $membersInput);
            }

            \DB::commit();

            if (!empty($memberIds)) {
                // dispatch job to send push notification to all user when course created
                \dispatch(new SendGroupPushNotification($group, "user-assigned-updated-group", "", "", $memberIds));
            }

            $membersCount = $group->members()->count();

            return $this->successResponse(['data' => ['members' => $membersCount]], trans('api_messages.group.add'));
        } catch (\Exception $e) {
            \DB::rollback();
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function removeMembersFromGroup(Request $request, Group $group, User $user)
    {
        try {
            \DB::beginTransaction();
            // logged-in user
            $loggedInUser = $this->user();

            if ($group->creator_id != $loggedInUser->getKey()) {
                return $this->notFoundResponse("You are not authorized to remove member from this group");
            }

            if ($user->getKey() == $loggedInUser->getKey()) {
                return $this->notFoundResponse("You are not authorized to perform this operation");
            }

            $group->members()->detach([$user->id]);
            \DB::commit();

            $membersCount = $group->members()->count();

            return $this->successResponse(['data' => ['members' => $membersCount]], trans('api_messages.group.remove'));
        } catch (\Exception $e) {
            \DB::rollback();
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function groupMessages(Request $request, Group $group)
    {
        try {
            // logged-in user
            $user = $this->user();

            $pivotExsisting = $group->members()->wherePivot('user_id', $user->getKey())->wherePivot('group_id', $group->getKey())->first();

            if (empty($pivotExsisting)) {
                return $this->notFoundResponse("You are not authorized to perform this operation");
            }

            $userTimeZone = $user->timezone;

            $defaultTimeZone = config('app.timezone');

            $userGroupMessageDelete = $group->groupMessagesUserDeleteLog()->wherePivot("group_id", $group->id)->wherePivot("user_id", $user->id)->orderBy("group_messages_user_delete_log.created_at", "DESC")->first();

            $groupUser = $group->members()->wherePivot('user_id', $user->getKey())->wherePivot('group_id', $group->getKey())->first();

            $groupMessagesData = $group->groupMessages();

            if (!empty($userGroupMessageDelete)) {
                $groupMessagesData = $groupMessagesData->wherePivot('created_at', '>', $userGroupMessageDelete->pivot->updated_at)->orderBy('group_messages.created_at', 'DESC');
            }

            if (!empty($groupUser)) {
                $groupMessagesData = $groupMessagesData->wherePivot('created_at', '>=', $groupUser->pivot->created_at)->orderBy('group_messages.created_at', 'DESC');
            }

            // mark all messages as read
            $lastReadMsg = DB::table("group_messages_user_log")->select('group_messages_user_log.group_message_id')->where(['user_id' => $user->id, 'group_id' => $group->id, 'read' => true])->orderByDesc('group_messages_user_log.group_message_id')->first();

            if (!empty($lastReadMsg) && !empty($lastReadMsg->group_message_id)) {
                $groupMessagesDataToRead = $group->groupMessages()->wherePivot('id', '>', $lastReadMsg->group_message_id)->orderBy('group_messages.created_at', 'DESC')->pluck('group_messages.id')->toArray();

                if (!empty($groupMessagesDataToRead)) {
                    foreach ($groupMessagesDataToRead as $key => $messageId) {
                        $messageLog = DB::table("group_messages_user_log")->updateOrInsert(
                            ['group_message_id' => $messageId, 'user_id' => $user->id],
                            ['read' => true, 'group_id' => $group->id]
                        );
                    }
                }
            } else {
                $groupMessagesDataToRead = $group->groupMessages()->orderBy('group_messages.created_at', 'DESC')->pluck('group_messages.id')->toArray();

                if (!empty($groupMessagesDataToRead)) {
                    foreach ($groupMessagesDataToRead as $key => $messageId) {
                        $messageLog = DB::table("group_messages_user_log")->updateOrInsert(
                            ['group_message_id' => $messageId, 'user_id' => $user->id],
                            ['read' => true, 'group_id' => $group->id]
                        );
                    }
                }
            }

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
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendGroupMessages(SendMessageToGroupRequest $request, Group $group)
    {
        try {
            \DB::beginTransaction();
            // logged-in user
            $user = $this->user();

            $pivotExsisting = $group->members()->wherePivot('user_id', $user->getKey())->wherePivot('group_id', $group->getKey())->first();

            if (empty($pivotExsisting)) {
                return $this->notFoundResponse("You are not authorized to perform this operation");
            }

            $messageInput = [
                'message' => $request->message,
            ];

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

            \DB::commit();

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
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function favUnfavGroupMessage(Request $request, GroupMessage $message)
    {
        try {
            \DB::beginTransaction();

            $loginUser = $this->user();
            $resMsg    = "";

            $existingGroupMember = DB::table('group_members')->where("user_id", $loginUser->getKey())->where("group_id", $message->group_id)->first();

            if (empty($existingGroupMember)) {
                return $this->notFoundResponse("You are not authorized to perform this operation");
            }

            // get pivoted coach data by user
            $pivotExsisting = $message->groupMessagesUserLog()->wherePivot('user_id', $loginUser->getKey())->wherePivot('group_message_id', $message->getKey())->first();

            if (!empty($pivotExsisting)) {
                // check if coach is followed / unfollowed by user
                $favourited = $pivotExsisting->pivot->favourited;

                $pivotExsisting->pivot->favourited = ($favourited == 1) ? 0 : 1;

                $pivotExsisting->pivot->save();

                $resMsg = ($favourited == 1) ? "unstarred" : "starred";
            } else {
                $resMsg             = "starred";
                $data               = array();
                $data['favourited'] = true;
                $data['group_id']   = $message->group_id;
                $message->groupMessagesUserLog()->attach($loginUser, $data);
            }
            \DB::commit();
            $msg = (($resMsg == "starred") ? trans('api_messages.group.favorite') : trans('api_messages.group.unfavorite'));
            return $this->successResponse([], $msg);
        } catch (\Exception $e) {
            \DB::rollback();
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function forwardGroupMessages(Request $request, Group $group, GroupMessage $message)
    {
        try {
            \DB::beginTransaction();
            // logged-in user
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

            $group->groupMessages()->attach($user, $messageInput);
            $group->update(['updated_at' => now()->toDateTimeString()]);

            \DB::commit();

            return $this->successResponse(['data' => []], trans('api_messages.group.forward'));
        } catch (\Exception $e) {
            \DB::rollback();
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteGroupMessage(Request $request, GroupMessage $message)
    {
        try {
            \DB::beginTransaction();
            // logged-in user
            $user   = $this->user();
            $resMsg = "";

            if ($message->user_id != $user->getKey()) {
                return $this->notFoundResponse("You are not authorized to perform this operation");
            }

            $existingGroupMember = DB::table('group_members')->where("user_id", $user->getKey())->where("group_id", $message->group_id)->first();

            if (empty($existingGroupMember)) {
                return $this->notFoundResponse("You are not authorized to perform this operation");
            }

            $message->deleted = 1;

            $message->save();

            \DB::commit();
            return $this->successResponse([], trans('api_messages.group.message-delete'));
        } catch (\Exception $e) {
            \DB::rollback();
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function groupMembers(Request $request, Group $group)
    {
        try {
            // logged-in user
            $user = $this->user();

            $groupMembersData = $group->members()->leftJoin("badge_user", function ($join) {
                $join->on("group_members.user_id", "=", "badge_user.user_id")
                    ->where("badge_user.status", "Active");
            })
                ->select("group_members.*", DB::raw("COUNT(badge_user.id) as activeBadgesCount"))
                ->wherePivot("status", "Accepted")
                ->orderBy(\DB::raw("CONCAT(users.first_name,' ',users.last_name)"))
            // ->orderByDesc('users.id')
                ->orderBy('group_members.updated_at', 'DESC')
                ->groupBy('group_members.user_id')
                ->paginate(config('zevolifesettings.datatable.pagination.short'));

            $total = $group->members()->count();

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
 * api to fetch group details
 *
 * @return \Illuminate\Http\JsonResponse
 */
    public function details(Request $request, Group $group)
    {
        try {
            // logged-in user
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
 * api to join the group
 *
 * @return \Illuminate\Http\JsonResponse
 */
    public function joinGroup(Request $request, Group $group)
    {
        try {
            \DB::beginTransaction();
            // logged-in user
            $user = $this->user();

            // fetch user group data
            $pivotExsisting = $group->members()->wherePivot('user_id', $user->getKey())->wherePivot('group_id', $group->getKey())->first();

            if (!empty($pivotExsisting)) {
                if ($pivotExsisting->pivot->status != "Accepted") {
                    $pivotExsisting->pivot->status      = "Accepted";
                    $pivotExsisting->pivot->joined_date = now()->toDateTimeString();
                }
                $pivotExsisting->pivot->save();
            } else {
                $data                = array();
                $data['status']      = "Accepted";
                $data['joined_date'] = now()->toDateTimeString();
                $group->members()->attach($user, $data);
            }

            \DB::commit();
            // dispatch job to awarg badge to user for running challenge
            // User joined group notification has been disabled as an update.
            // $this->dispatch(new SendGroupPushNotification($group, 'user-joined-group', $user->full_name, $user->id));

            $membersCount = $group->members()->count();

            return $this->successResponse(['data' => ['members' => $membersCount]], trans('api_messages.group.join'));
        } catch (\Exception $e) {
            \DB::rollback();
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

/**
 * api to mute / unmute group notification
 *
 * @return \Illuminate\Http\JsonResponse
 */
    public function muteNotification(Request $request, Group $group)
    {
        try {
            \DB::beginTransaction();

            $user           = $this->user();
            $message        = trans('api_messages.group.mute');
            $pivotExsisting = $group
                ->members()
                ->wherePivot('user_id', $user->getKey())
                ->wherePivot('group_id', $group->getKey())
                ->first();

            if (!empty($pivotExsisting)) {
                $notification_muted                        = $pivotExsisting->pivot->notification_muted;
                $pivotExsisting->pivot->notification_muted = ($notification_muted == 1) ? 0 : 1;
                $pivotExsisting->pivot->save();

                if ($notification_muted == 1) {
                    $message = trans('api_messages.group.unmute');
                }
            } else {
                return $this->notFoundResponse("You are not authorized to perform this operation");
            }

            \DB::commit();

            return $this->successResponse([], $message);
        } catch (\Exception $e) {
            \DB::rollback();
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

/**
 * api to report abuse content for perticular group
 *
 * @return \Illuminate\Http\JsonResponse
 */
    public function report(GroupReportRequest $request, Group $group)
    {
        try {
            \DB::beginTransaction();
            // logged-in user
            $user = $this->user();

            if ($group->creator_id == $user->getKey()) {
                return $this->notFoundResponse("You are not authorized to perform this operation.");
            }

            // fetch user group data
            $pivotExsisting = $group->groupReports()->wherePivot('user_id', $user->getKey())->wherePivot('group_id', $group->getKey())->first();

            if (!empty($pivotExsisting)) {
                $pivotExsisting->pivot->reason  = $request->reportTitle;
                $pivotExsisting->pivot->message = $request->description;

                $pivotExsisting->pivot->save();
            } else {
                $data            = array();
                $data['reason']  = $request->reportTitle;
                $data['message'] = $request->description;
                $group->groupReports()->attach($user, $data);
            }

            $group->members()->detach([$user->id]);

            \DB::commit();

            return $this->successResponse([], trans('api_messages.group.report'));
        } catch (\Exception $e) {
            \DB::rollback();
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

/**
 * api to fetch user favourited message of the group
 *
 * @return \Illuminate\Http\JsonResponse
 */
    public function starredMessages(Request $request, Group $group)
    {
        try {
            // logged-in user
            $user = $this->user();

            // fetch last clear chat history date and time of user for that group
            $userGroupMessageDelete = $group->groupMessagesUserDeleteLog()->wherePivot("group_id", $group->id)->wherePivot("user_id", $user->id)->orderBy("group_messages_user_delete_log.created_at", "DESC")->first();

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
 * api to delete created group.
 *
 * @return \Illuminate\Http\JsonResponse
 */
    public function delete(Request $request, Group $group)
    {
        try {
            \DB::beginTransaction();
            // logged-in user
            $loggedInUser = $this->user();

            // check user is creater of group or not if user is not creater of group then not allow to perform operation
            if ($group->creator_id != $loggedInUser->getKey()) {
                return $this->notFoundResponse("You are not authorized to remove this group");
            }

            $group->deleteRecord();
            \DB::commit();

            return $this->successResponse([], trans('api_messages.group.group-deleted'));
        } catch (\Exception $e) {
            \DB::rollback();
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

/**
 * api to leave group
 *
 * @return \Illuminate\Http\JsonResponse
 */
    public function leave(Request $request, Group $group)
    {
        try {
            \DB::beginTransaction();
            // logged-in user
            $loggedInUser = $this->user();

            // check user is creater of group or not if user creater of group then not allow to perform operation
            if ($group->creator_id == $loggedInUser->getKey()) {
                return $this->notFoundResponse("You are not authorized to perform this operation");
            }

            $group->members()->detach([$loggedInUser->id]);
            \DB::commit();

            // dispatch job to awarg badge to user for running challenge
            // Member leaves group notification has been disabled as an update.
            // $this->dispatch(new SendGroupPushNotification($group, 'user-leaves-group', $loggedInUser->full_name));

            $membersCount = $group->members()->count();

            return $this->successResponse(['data' => ['members' => $membersCount]], trans('api_messages.group.leave'));
        } catch (\Exception $e) {
            \DB::rollback();
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
            // logged-in user
            $user    = $this->user();
            $company = $user->company()->first();

            // get all group ids which is joined and created by me
            $groupIds = Group::join("group_members", "groups.id", "=", "group_members.group_id")
                ->where("group_members.user_id", $user->getKey())
                ->where('groups.company_id', $company->id)
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

    /**
     * api to fetch group details for given group.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function groupInfo(Request $request, Group $group)
    {
        try {
            // logged-in user
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

            // get group details data with json response
            $data = array("data" => new GroupInfoResource($groupExploreData));

            return $this->successResponse($data, 'Detail retrieved successfully.');
        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * clear all chat history for logged in user for given group
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function clearAllGroupMessage(Request $request, Group $group)
    {
        try {
            \DB::beginTransaction();
            // logged-in user
            $user = $this->user();

            // chheck user is member of given group or not
            $memberExsisting = $group->members()->wherePivot('user_id', $user->getKey())->wherePivot('group_id', $group->getKey())->first();

            if (!empty($memberExsisting)) {
                $pivotExsisting = $group->groupMessagesUserDeleteLog()->wherePivot('user_id', $user->getKey())->wherePivot('group_id', $group->getKey())->first();

                if (!empty($pivotExsisting)) {
                    $pivotExsisting->pivot->updated_at = now()->toDateTimeString();

                    $pivotExsisting->pivot->save();
                } else {
                    $group->groupMessagesUserDeleteLog()->attach($user);
                }

                \DB::commit();
                return $this->successResponse([], 'All group message deleted successfully.');
            } else {
                return $this->notFoundResponse("You are not authorized to perform this operation");
            }
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
            // logged-in user
            $user = $this->user();

            // check user is memeber of the group or not
            $groupMember = $group->members()->wherePivot('user_id', $user->getKey())->wherePivot('group_id', $group->getKey())->first();

            if (empty($groupMember)) {
                // if user is not member of group then return required response
                return $this->notFoundResponse("You are not authorized to perform this operation");
            }

            $groupMessagesData = $group->groupMessages();

            if (!empty($groupMember)) {
                $groupMessagesData = $groupMessagesData->wherePivot('created_at', '>=', $groupMember->pivot->created_at);
            }

            if (!empty($request->messageId)) {
                $groupMessagesData = $groupMessagesData->wherePivot('id', '>', $request->messageId)->orderBy('group_messages.created_at', 'DESC')->get();
            } else {
                $groupMessagesData = $groupMessagesData->orderBy('group_messages.created_at', 'DESC')->paginate(config('zevolifesettings.datatable.pagination.short'));
            }

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
}
