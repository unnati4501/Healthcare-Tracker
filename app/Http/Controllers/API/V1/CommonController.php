<?php
declare (strict_types = 1);

namespace App\Http\Controllers\API\V1;

use App\Http\Collections\V1\CompanyDepartmentsCollection;
use App\Http\Collections\V1\CompanyTeamsCollection;
use App\Http\Collections\V1\CompanyUsersCollection;
use App\Http\Collections\V1\TeamMembersCollection;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\NpsFeedBackRequest;
use App\Http\Requests\Api\V1\ShareContentRequest;
use App\Http\Traits\ProvidesAuthGuardTrait;
use App\Http\Traits\ServesApiTrait;
use App\Jobs\SendContentSharePushNotification;
use App\Models\Team;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Validator;

class CommonController extends Controller
{
    use ServesApiTrait, ProvidesAuthGuardTrait;

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllUsers(Request $request)
    {
        try {
            $user     = $this->user();
            $company  = $user->company()->first();
            $timezone = $user->timezone ?? config('app.timezone');

            $modelId   = $request->modelId;
            $modelType = $request->modelType;

            if (!empty($modelType) & !empty($modelId)) {
                if ($modelType == 'group') {
                    $model = \App\Models\Group::find($modelId);
                } elseif ($modelType == 'challenge') {
                    $model = \App\Models\Challenge::find($modelId);
                }
            }

            $alredySelectedMembers = [];
            if (!empty($model)) {
                $alredySelectedMembers = $model->members()->pluck('users.id')->toArray();
            }

            array_unshift($alredySelectedMembers, $user->getKey());

            $userRecords = $company->members()->whereNotIn('users.id', $alredySelectedMembers);

            if (!empty($request->search)) {
                $userRecords = $userRecords->where(\DB::raw("CONCAT(first_name,' ',last_name)"), 'like', '%' . $request->search . '%');
            }

            if (!empty($request->team)) {
                $userRecords = $userRecords->wherePivotIn('team_id', $request->team);
            }

            if (!empty($request->department)) {
                $userRecords = $userRecords->wherePivotIn('department_id', $request->department);
            }

            $userRecords = $userRecords->orderBy(\DB::raw("CONCAT(first_name,' ',last_name)"))
                ->orderByDesc('users.id')
                ->paginate(config('zevolifesettings.datatable.pagination.short'));

            return $this->successResponse(
                ($userRecords->count() > 0) ? new CompanyUsersCollection($userRecords) : ['data' => []],
                ($userRecords->count() > 0) ? 'Users list retrieved successfully.' : 'No results'
            );
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
    public function getAllDepartments(Request $request)
    {
        try {
            $user     = $this->user();
            $company  = $user->company()->first();
            $timezone = $user->timezone ?? config('app.timezone');

            $deptRecords = $company->departments()->where('departments.name', 'like', '%' . $request->search . '%');

            if (!empty($request->search)) {
                $deptRecords = $deptRecords->where('departments.name', 'like', '%' . $request->search . '%');
            }

            $deptRecords = $deptRecords->orderByDesc('departments.name')
                ->paginate(config('zevolifesettings.datatable.pagination.short'));

            return $this->successResponse(
                ($deptRecords->count() > 0) ? new CompanyDepartmentsCollection($deptRecords) : ['data' => []],
                ($deptRecords->count() > 0) ? 'Departments list retrieved successfully.' : 'No results'
            );
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
    public function getAllTeams(Request $request)
    {
        try {
            $user     = $this->user();
            $company  = $user->company()->first();
            $timezone = $user->timezone ?? config('app.timezone');

            $deptRecords = $company->teams()->where('teams.name', 'like', '%' . $request->search . '%');

            if (!empty($request->search)) {
                $deptRecords = $deptRecords->where('teams.name', 'like', '%' . $request->search . '%');
            }

            $deptRecords = $deptRecords->orderByDesc('teams.name')
                ->paginate(config('zevolifesettings.datatable.pagination.short'));

            return $this->successResponse(
                ($deptRecords->count() > 0) ? new CompanyTeamsCollection($deptRecords) : ['data' => []],
                ($deptRecords->count() > 0) ? 'Teams list retrieved successfully.' : 'No results'
            );
        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * Share content as group message
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function shareContent(ShareContentRequest $request)
    {
        try {
            \DB::beginTransaction();

            // logged-in user
            $user = $this->user();

            // find group object
            $group                 = \App\Models\Group::find($request->groupId);
            $notificationModelData = array();
            // perform action if group found
            if (!empty($group)) {
                // check if requested model is found or not
                if ($request->modelType == 'feed') {
                    $model = \App\Models\Feed::find($request->modelId);
                } elseif ($request->modelType == 'course') {
                    $model = \App\Models\Course::find($request->modelId);
                } elseif ($request->modelType == 'meditation') {
                    $model = \App\Models\MeditationTrack::find($request->modelId);
                } elseif ($request->modelType == 'recipe') {
                    $model = \App\Models\Recipe::find($request->modelId);
                }

                // share content if model found
                if (!empty($model)) {
                    $group->groupMessages()->attach($user, ['model_id' => $request->modelId, 'model_name' => $request->modelType]);

                    $group->update(['updated_at' => now()->toDateTimeString()]);

                    \DB::commit();
                    $title = trans('notifications.share.title');
                    $title = str_replace(['#module_name#'], [(($request->modelType == 'feed') ? 'Stroy' : ucfirst($request->modelType))], $title);

                    $notificationModelData['title']         = $title;
                    $notificationModelData['name']          = $model->title;
                    $notificationModelData['deep_link_uri'] = (!empty($model->deep_link_uri)) ? $model->deep_link_uri : "";

                    // dispatch job to send shared content notification to specified group members
                    $this->dispatch(new SendContentSharePushNotification($group, $notificationModelData, $user));

                    return $this->successResponse(['data' => []], ucfirst($request->modelType) . " shared successfully.");
                } else {
                    \DB::rollback();
                    return $this->notFoundResponse("Sorry! Unable to find " . ucfirst($request->modelType));
                }
            }

            \DB::rollback();
            return $this->successResponse(['data' => []], "Unable to share " . ucfirst($request->modelType));
        } catch (\Exception $e) {
            \DB::rollback();
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     *store nps feed back given by the user
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeNPSFeedback(NpsFeedBackRequest $request)
    {
        try {
            \DB::beginTransaction();

            // logged-in user
            $user = $this->user();

            $userNpsData = $user->npsSurveyLinkLogs()->whereNull("survey_received_on")
                ->where("user_id", $user->id)
                ->orderBy("id", "DESC")
                ->first();

            if (!empty($userNpsData)) {
                $npsData = [
                    'rating'             => (int) $request->rating,
                    'feedback'           => $request->feedback,
                    'survey_received_on' => now()->toDateTimeString(),
                ];

                $userNpsData->update($npsData);
                \DB::commit();
                return $this->successResponse(['data' => []], "Thanks, we really appreciate your feedback.");
            } else {
                \DB::rollback();
                return $this->successResponse([], "Thanks, we really appreciate your feedback. Why not leave a review on the App store?");
            }
        } catch (\Exception $e) {
            \DB::rollback();
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * Get team members
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTeamMembers(Request $request, $team)
    {
        try {
            $validator = Validator::make(
                array_merge(['team' => $team], $request->all()),
                [
                    'team'  => 'required|integer|exists:teams,id',
                    'page'  => 'sometimes|required|integer',
                    'count' => 'sometimes|required|integer',
                ],
                [
                    'team.required' => 'Please provide team identity',
                    'team.integer'  => 'Please provide valid team identity',
                    'team.exists'   => 'Team doesn\'t exist, pleae provide valid team identity',
                ]
            );

            if (!$validator->fails()) {
                $teamMembers = Team::find($team)
                    ->users()
                    ->paginate(config('zevolifesettings.datatable.pagination.short'));

                return $this->successResponse(
                    ($teamMembers->count() > 0) ? new TeamMembersCollection($teamMembers) : ['data' => []],
                    ($teamMembers->count() > 0) ? 'Team members list retrieved successfully.' : 'No team members found'
                );
            } else {
                return $this->invalidResponse($validator->errors()->getMessages());
            }
        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }
}
