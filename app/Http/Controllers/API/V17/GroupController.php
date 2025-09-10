<?php declare (strict_types = 1);

namespace App\Http\Controllers\API\V17;

use App\Http\Collections\V17\GroupMessagesCollection;
use App\Http\Collections\V17\MyGroupListCollection;
use App\Http\Controllers\API\V9\GroupController as v9GroupController;
use App\Models\Group;
use App\Models\SubCategory;
use App\Models\User;
use DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GroupController extends v9GroupController
{
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

            if (!empty($subCategory) && $subCategory->short_name == 'public') {
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
