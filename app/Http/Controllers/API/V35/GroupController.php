<?php declare (strict_types = 1);

namespace App\Http\Controllers\API\V35;

use App\Http\Controllers\API\V33\GroupController as v33GroupController;
use App\Http\Collections\V35\CompanyMemberCollection;
use App\Http\Requests\Api\V1\GroupCreateRequest;
use App\Http\Traits\ProvidesAuthGuardTrait;
use App\Http\Traits\ServesApiTrait;
use App\Jobs\SendGroupPushNotification;
use App\Models\Group;
use App\Models\User;
use App\Models\Category;
use App\Models\SubCategory;
use App\Models\GroupMember;
use App\Http\Collections\V35\MyGroupListCollection;
use App\Http\Collections\V1\GroupMemberCollection;
use App\Http\Resources\V35\GroupDetailsResource;
use DB;
use Validator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GroupController extends v33GroupController
{
    use ServesApiTrait, ProvidesAuthGuardTrait;
    
    /**
     * Get group members listing.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function companyMembers(Request $request)
    {
        try {
            $user    = $this->user();
            $company = $user->company()->first();
            
            $membersData = User::select(\DB::raw("CONCAT(users.first_name, ' ', users.last_name) AS name"), 'users.id')
                ->join('user_team', function ($join) use ($company) {
                    $join->on('user_team.user_id', '=', 'users.id')
                        ->where('user_team.company_id', $company->id);
                })
                ->leftJoin('role_user', 'role_user.user_id', '=', 'users.id')
                ->leftJoin('roles', 'role_user.role_id', '=', 'roles.id')
                ->where('roles.slug','user')
                ->where("users.id", "!=", $user->id);

            // search by user name
            if(!empty($request['name'])){
                $membersData = $membersData->where(DB::raw("CONCAT(users.first_name,' ',users.last_name)"), 'like', '%' . $request['name'] . '%');
            }
            $membersData= $membersData->orderBy(\DB::raw("CONCAT(users.first_name,' ',users.last_name)"))
                ->paginate(config('zevolifesettings.datatable.pagination.short'));
    
            // collect required data
            $return          = [];
            $return['data']  = [];
            if ($membersData->count() > 0) {
                $return = new CompanyMemberCollection($membersData);
            }

            // return response
            return $this->successResponse(
                $return,
                ($membersData->count() > 0) ? 'Members retrieved successfully.' : "No results"
            );
        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * Create Group
     * 
     * @param GroupCreateRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(GroupCreateRequest $request)
    {
        try {
            \DB::beginTransaction();

            $members = (!empty($request->users)) ? json_decode($request->users, true) : [];

            if (count($members) > 50) {
                DB::rollback();
                return $this->invalidResponse([], "Maximum 50 participants are allowed in a Group");
            } elseif(count($members) < 2){
                DB::rollback();
                return $this->invalidResponse([], "Minimum 2 participants are allowed in a Group");
            }else{
                // logged-in user
                $user        = $this->user();
                $company_id  = !is_null($user->company->first()) ? $user->company->first()->id : null;
                $category    = Category::where('short_name', 'group')->first();
                $subCategory =  SubCategory::where(["category_id" => $category->id, "short_name" => 'recipe' ])->first();

                if (!empty($category) && !empty($subCategory)) {
                    $groupInput                    = array();
                    $groupInput['creator_id']      = $user->id;
                    $groupInput['company_id']      = $company_id;
                    $groupInput['category_id']     = $category->id;
                    $groupInput['sub_category_id'] = $subCategory->id;
                    $groupInput['created_by']      = 'User';
                    $groupInput['title']           = $request->name;
                    $groupInput['type']            = isset($request->type) ? $request->type : 'private';

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
                            'group_id'              => $group->id,
                            'status'                => "Accepted",
                            'joined_date'           => now()->toDateTimeString(),
                        ];
                        $group->members()->attach($memberIds, $membersInput);

                        $group->members()->where('user_id', $user->id)->update(['accept_decline_status'=> 1]);
                    }

                    \DB::commit();
                    // send notification to users for accept/decline the group
                    $this->dispatch(new SendGroupPushNotification($group, 'new-group'));

                    return $this->successResponse([], trans('api_messages.group.create'));
                } else {
                    return $this->internalErrorResponse("Category data not found");
                }
            }
        } catch (\Exception $e) {
            \DB::rollback();
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * Accept invitation
     * 
     * @param Request $request
     * @param Group $group
     * @return \Illuminate\Http\JsonResponse
     */
    public function acceptInvitation(Request $request, Group $group)
    {
        try {
            \DB::beginTransaction();
            $user         = $this->user();
            GroupMember::where(['user_id' => $user->id, 'group_id' => $group->id])->update(['accept_decline_status' => 1]);
            
            \DB::commit();
            return $this->successResponse([], trans('api_messages.group.invitation-accepted'));
        } catch (\Exception $e) {
            \DB::rollback();
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }

    }

    /**
     * Decline invitation
     * 
     * @param Request $request
     * @param Group $group
     * @return \Illuminate\Http\JsonResponse
     */
    public function declineInvitation(Request $request, Group $group)
    {
        try {
            \DB::beginTransaction();
            $user       = $this->user();
            GroupMember::where(['user_id' => $user->id, 'group_id' => $group->id])->update(['accept_decline_status' => 2]);
            
            \DB::commit();
            return $this->successResponse([], trans('api_messages.group.invitation-declined'));                
        } catch (\Exception $e) {
            \DB::rollback();
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * Get group list
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function exploreGroups(Request $request)
    {
        try {
            $user    = $this->user();
            $company = $user->company()->first();

            $userGroupIds = Group::join("group_members", "groups.id", "=", "group_members.group_id")
                ->where("group_members.user_id", $user->getKey())
                ->where("group_members.accept_decline_status", '!=', 2)
                ->where(function ($query) use ($company) {
                    $query->where('groups.company_id', $company->id)
                        ->orWhere('groups.company_id', null);
                })
                ->where('groups.is_visible', 1)
                ->where('groups.is_archived', 0)
                ->groupBy('group_members.group_id')
                ->pluck("groups.id")
                ->toArray();

            $publicGroupIds = Group::whereNotIn('id', $userGroupIds)
                ->where(function ($query) use ($company) {
                    $query->where('groups.company_id', $company->id)
                        ->orWhere('groups.company_id', null);
                })
                ->where('groups.type', 'public')
                ->where('groups.is_visible', 1)
                ->where('groups.is_archived', 0)
                ->pluck('groups.id')
                ->toArray();

            $groupIds = array_merge($userGroupIds, $publicGroupIds);

            if (!empty($groupIds)) {
                $groupExploreData = Group::join("group_members", function ($join) {
                    $join->on("groups.id", "=", "group_members.group_id")
                        ->where("group_members.status", "Accepted");
                })->select("groups.*", DB::raw("COUNT(group_id) as members"), 'group_members.accept_decline_status')
                    ->whereIn("groups.id", $groupIds);

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
     * Api to fetch my group list data
     *
     * @param Request $request
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
                ->where("group_members.accept_decline_status", '!=', 2)
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
                    ->select("groups.*", DB::raw("COUNT(group_id) as members"), 'group_members.accept_decline_status')
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
     * Get group details
     * 
     * @param Request $request
     * @param Group $group
     * @return \Illuminate\Http\JsonResponse
     */
    public function groupDetails(Request $request)
    {
        try {
            $group = Group::find($request->group);
            if(!empty($group)){
                $user = $this->user();
                //Check weather user has accepted or declined request
                $acceptDeclineStatus = $group->members()
                    ->wherePivot("status", "Accepted")
                    ->wherePivot("user_id", $user->getKey())
                    ->wherePivot("accept_decline_status", 2)
                    ->first();
                if(!empty($acceptDeclineStatus)){
                    return $this->notFoundResponse("This group invite has been declined");
                }

                // Display group api
                $groupExploreData = $group
                    ->leftJoin("group_members", function ($join) {
                        $join->on("groups.id", "=", "group_members.group_id")
                            ->where("group_members.status", "Accepted");
                    })
                    ->select("groups.*", DB::raw("COUNT(group_id) as members"), 'group_members.accept_decline_status')
                    ->where("groups.id", $group->id)
                    ->orderBy('groups.updated_at', 'DESC')
                    ->groupBy('group_members.group_id')
                    ->first();
                
                // get course details data with json response
                $data = array("data" => new GroupDetailsResource($groupExploreData));

                return $this->successResponse($data, 'Detail retrieved successfully.');
            } else {
                return $this->notFoundResponse("This group has been removed");
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
                ->select(DB::raw("COUNT(badge_user.id) as activeBadgesCount"))
                ->wherePivot("status", "Accepted")
                ->orderBy(\DB::raw("CONCAT(users.first_name,' ',users.last_name)"))
                ->orderBy('group_members.updated_at', 'DESC');
                
            if (!empty($group->created_by && $group->created_by == 'User')) {
                $groupMembersData = $groupMembersData->whereRaw("accept_decline_status != 2");
            }
            $groupMembersData = $groupMembersData->groupBy('group_members.user_id')->paginate(config('zevolifesettings.datatable.pagination.short'));

            $total = $group->members()
                ->join('user_team', 'user_team.user_id', '=', 'group_members.user_id')
                ->where(function ($query) use ($teamRestriction, $team, $company) {
                    if (!empty($teamRestriction)) {
                        $query->where('user_team.team_id', $team->getKey());
                    } else {
                        $query->where('user_team.company_id', $company->getKey());
                    }
                });
                if (!empty($group->created_by && $group->created_by == 'User')) {
                    $total = $total->whereRaw("accept_decline_status != 2");
                }
                $total = $total->count();

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
     * api to delete created group.
     * @param Request $request,
     * @param Group $group
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

}
