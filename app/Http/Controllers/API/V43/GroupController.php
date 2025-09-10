<?php declare (strict_types = 1);

namespace App\Http\Controllers\API\V43;

use App\Http\Controllers\API\V35\GroupController as v35GroupController;
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

class GroupController extends v35GroupController
{
    use ServesApiTrait, ProvidesAuthGuardTrait;

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
}