<?php declare (strict_types = 1);

namespace App\Http\Controllers\API\V27;

use App\Http\Collections\V27\MyGroupListCollection;
use App\Http\Controllers\API\V24\GroupController as v24GroupController;
use App\Http\Resources\V27\GroupDetailsResource;
use App\Models\Group;
use App\Models\User;
use DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GroupController extends v24GroupController
{
    /**
     * Get group details
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
