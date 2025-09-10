<?php declare (strict_types = 1);

namespace App\Http\Controllers\API\V3;

use App\Http\Controllers\API\V2\CommonController as v2CommonController;
use App\Http\Controllers\Controller;
use App\Http\Traits\ProvidesAuthGuardTrait;
use App\Http\Traits\ServesApiTrait;
use App\Models\Team;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Validator;
use App\Http\Collections\V3\TeamMembersCollection;

class CommonController extends v2CommonController
{
    use ServesApiTrait, ProvidesAuthGuardTrait;

    /**
     * Get team members
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTeamMembers(Request $request, $team)
    {
        try {
            $user            = $this->user();
            $userTeamId = $user->teams()->first()->id;

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

            if ($team != $userTeamId) {
                return $this->notFoundResponse("Sorry!, it seems that your team has been changed.");
            }
            
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
