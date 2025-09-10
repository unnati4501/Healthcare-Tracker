<?php declare (strict_types = 1);

namespace App\Http\Controllers\API\V22;

use App\Http\Collections\V22\MyGroupListCollection;
use App\Http\Controllers\API\V19\GroupController as v19GroupController;
use App\Models\Group;
use App\Models\User;
use DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GroupController extends v19GroupController
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

            $userGroupIds = Group::join("group_members", "groups.id", "=", "group_members.group_id")
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
                })->select("groups.*", DB::raw("COUNT(group_id) as members"))
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
}
