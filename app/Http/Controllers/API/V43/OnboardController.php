<?php declare (strict_types = 1);

namespace App\Http\Controllers\API\V43;

use App\Http\Controllers\API\V33\OnboardController as v33OnboardController;
use App\Http\Requests\Api\V10\PortalSurveyRequest;
use App\Http\Traits\ProvidesAuthGuardTrait;
use App\Http\Traits\ServesApiTrait;
use App\Models\Company;
use App\Models\Department;
use App\Models\ZcQuestion;
use App\Models\ZcSurveyLog;
use App\Models\ZcSurveyResponse;
use Carbon\Carbon;
use DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OnboardController extends v33OnboardController
{
    use ServesApiTrait, ProvidesAuthGuardTrait;

    /**
     * Get department wise teams
     *
     * @param CompanyLocation $location
     * @param Department $department
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTemas(CompanyLocation $location, Department $department, Request $request)
    {
        try {
            $appTimezone     = config('app.timezone');
            $timezone        = ($request->header('X-User-Timezone') ?? $appTimezone);
            $now             = now($appTimezone);
            $company         = $department->company()->select('id', 'auto_team_creation', 'team_limit')->first();
            $chInvolvedTeams = [];
            $user            = Auth::guard('api')->user();
            $teamId          = (!empty($user) ? $user->teams()->select('teams.id')->first() : null);

            // get ongoing + upcoming challenge ids
            $challenge = $company->challenge()
                ->select('challenges.id', 'challenges.challenge_type')
                ->where('challenges.cancelled', false)
                ->where('challenges.finished', false)
                ->whereNotIn('challenges.challenge_type', ['inter_company', 'individual'])
                ->where(function ($query) use ($now, $appTimezone, $timezone) {
                    $query
                        ->whereRaw("CONVERT_TZ(challenges.start_date, ?, ?)  <= ? AND CONVERT_TZ(challenges.end_date, ?, ?) >= ?",[
                            $appTimezone,$timezone,$now,$appTimezone,$timezone,$now
                        ])
                        ->orWhereRaw("CONVERT_TZ(challenges.start_date, ?, ?)  >= ? AND CONVERT_TZ(challenges.end_date, ?, ?) >= ?",[
                            $appTimezone,$timezone,$now,$appTimezone,$timezone,$now
                        ]);
                })
                ->get();

            // get ongoing + upcoming inter_company challenge ids
            $icChallenge = $company->icChallenge()
                ->select('challenges.id', 'challenges.challenge_type')
                ->where('challenges.cancelled', false)
                ->where('challenges.finished', false)
                ->where(function ($query) use ($now, $appTimezone, $timezone) {
                    $query
                        ->whereRaw("CONVERT_TZ(challenges.start_date, ?, ?)  <= ? AND CONVERT_TZ(challenges.end_date, ?, ?) >= ?",[
                            $appTimezone,$timezone,$now,$appTimezone,$timezone,$now
                        ])
                        ->orWhereRaw("CONVERT_TZ(challenges.start_date, ?, ?)  >= ? AND CONVERT_TZ(challenges.end_date, ?, ?) >= ?",[
                            $appTimezone,$timezone,$now,$appTimezone,$timezone,$now
                        ]);
                })
                ->get();
            $challenge = $challenge->merge($icChallenge);

            // get involved team ids
            if (!empty($challenge)) {
                foreach ($challenge as $key => $challenge) {
                    $chTeams = $challenge->memberTeams()
                        ->select('teams.id')
                        ->where('teams.default', false)
                        ->where('teams.company_id', $company->id)
                        ->get()->pluck('', 'id')->toArray();
                    $chInvolvedTeams = ($chInvolvedTeams + $chTeams);
                }
                $chInvolvedTeams = array_keys($chInvolvedTeams);
            }

            // get teams list
            $teams = $department->teams()
                ->select('teams.id', 'teams.name', 'teams.default')
                ->whereHas('teamlocation', function ($query) use ($location) {
                    $query->where('company_locations.id', $location->id);
                })
                ->when($company->auto_team_creation, function ($query, $value) use ($company, $teamId) {
                    $query
                        ->withCount('users')
                        ->having('users_count', '<', $company->team_limit, 'or')
                        ->having('teams.default', '=', true, 'or');
                    if (!empty($teamId)) {
                        $query->having('teams.id', '=', $teamId->id, 'or');
                    }
                })
                ->where(function ($query) use ($chInvolvedTeams, $teamId) {
                    $query->whereNotIn('teams.id', $chInvolvedTeams);
                    if (!empty($teamId)) {
                        $query->orWhere('teams.id', '=', $teamId->id);
                    }
                })
                ->orderBy('teams.name')
                ->get();

            return $this->successResponse(
                ($teams->count() > 0) ? new CompanyDepartmentsCollection($teams) : ['data' => []],
                ($teams->count() > 0) ? 'Teams list retrieved successfully.' : 'No results'
            );
        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }
}