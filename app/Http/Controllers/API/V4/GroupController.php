<?php declare (strict_types = 1);

namespace App\Http\Controllers\API\V4;

use App\Http\Collections\V4\GroupListCollection;
use App\Http\Controllers\API\V1\GroupController as v1GroupController;
use App\Http\Requests\Api\V1\GroupCreateRequest;
use App\Http\Requests\Api\V1\GroupUpdateRequest;
use App\Http\Traits\ProvidesAuthGuardTrait;
use App\Http\Traits\ServesApiTrait;
use App\Jobs\SendGroupPushNotification;
use App\Models\Category;
use App\Models\Group;
use App\Models\User;
use DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GroupController extends v1GroupController
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

            $groupExploreData = Group::leftJoin("group_members", function ($join) {
                $join->on("groups.id", "=", "group_members.group_id")
                    ->where("group_members.status", "Accepted");
            })->select("groups.*", DB::raw("COUNT(group_id) as members"), 'group_members.user_id');

            if (!empty($request->subCategory) && $request->subCategory != 7) {
                $groupExploreData = $groupExploreData->where('groups.sub_category_id', $request->subCategory);
            }

            if (!empty($request->subCategory) && $request->subCategory == 7) {
                $groupExploreData = $groupExploreData->where('groups.sub_category_id', $request->subCategory)
                    ->where('group_members.user_id', $user->id);
            }

            $groupExploreData = $groupExploreData->where('groups.company_id', $company->id)
                ->orderBy('groups.updated_at', 'DESC')
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

            $checkCategory = Category::find(3);

            if (!empty($checkCategory)) {
                $groupInput                    = array();
                $groupInput['creator_id']      = $user->id;
                $groupInput['company_id']      = $company_id;
                $groupInput['category_id']     = 3;
                $groupInput['sub_category_id'] = 7;
                $groupInput['title']           = $request->name;
                $groupInput['description']     = $request->description;

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

            $checkCategory = Category::find(3);

            if (!empty($checkCategory)) {
                $groupInput                = array();
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
}
