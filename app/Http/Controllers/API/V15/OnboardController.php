<?php declare (strict_types = 1);

namespace App\Http\Controllers\API\V15;

use App\Http\Collections\V6\CompanyDepartmentsCollection;
use App\Http\Collections\V15\CompanyLocationsCollection;
use App\Http\Controllers\API\V14\OnboardController as v14onboardController;
use App\Http\Requests\Api\V15\SubmitSurveyFeedbackRequest;
use App\Http\Requests\Api\V15\VerifyCompanyCodeRequest;
use App\Models\challenge;
use App\Models\Company;
use App\Models\CompanyLocation;
use App\Models\Department;
use App\Models\ZcSurveyLog;
use App\Models\ZcSurveyReviewSuggestion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OnboardController extends v14onboardController
{
    /**
     * Submit Survey Feedback Response
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function submitSurveyFeedback(SubmitSurveyFeedbackRequest $request)
    {
        try {
            \DB::beginTransaction();
            $user         = $this->user();
            $company      = $user->company->first();
            $departmentId = $company->departments->first()->id;

            $zcSurveyLog = ZcSurveyLog::select('id')
                ->where('id', $request->surveyId)
                ->where('company_id', $company->id)
                ->orderBy('id', 'DESC')
                ->first();

            if (!$zcSurveyLog) {
                return $this->notFoundResponse('It seems survey is not available for your company.');
            }

            $checkAlreadySubmitted = ZcSurveyReviewSuggestion::select('id')
                ->where('survey_log_id', $zcSurveyLog->id)
                ->where('company_id', $company->id)
                ->where('user_id', $user->id)
                ->first();

            if ($checkAlreadySubmitted) {
                return $this->notFoundResponse('Survey feedback already submitted.');
            }

            $stored = ZcSurveyReviewSuggestion::create([
                'user_id'       => $user->id,
                'company_id'    => $company->id,
                'department_id' => $departmentId,
                'survey_log_id' => $zcSurveyLog->id,
                'comment'       => $request->feedback,
            ]);

            if ($stored) {
                \DB::commit();
                return $this->successResponse(['data' => []], 'Feedback has been submitted successfully!');
            }
        } catch (\Exception $e) {
            \DB::rollback();
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * Get companies locations
     *
     * @param VerifyCompanyCodeRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function verifyCompanyCode(VerifyCompanyCodeRequest $request)
    {
        try {
            $company = Company::select('id')->where('code', $request->code)->first();
            if (empty($company)) {
                return $this->invalidResponse([
                    'code' => ['The selected company code is invalid.'],
                ], 'The given data is invalid.');
            } else {
                $labelStrings           = [];
                $defaultLabelString     = config('zevolifesettings.company_label_string', []);
                $companyWiseLabelString = $company->companyWiseLabelString()->pluck('label_name', 'field_name')->toArray();

                // iterate default labels loop and check is label's custom value is set then user custom value else default value
                foreach ($defaultLabelString as $groupKey => $groups) {
                    foreach ($groups as $labelKey => $labelValue) {
                        $label = ($companyWiseLabelString[$labelKey] ?? $labelValue['default_value']);
                        if (in_array($labelKey, ['location_logo', 'department_logo'])) {
                            $label = $company->getMediaData($labelKey, ['w' => 60, 'h' => 60, 'zc' => 3, 'ct' => 1]);
                        }
                        $labelStrings[$labelKey] = $label;
                    }
                }

                // get default location, department, and team value
                $team       = $company->teams()->where('default', 1)->first();
                $department = $team->department()->select('id', 'name')->first();
                $location   = $team->teamlocation()->select('company_locations.id', 'company_locations.name')->first();

                return $this->successResponse([
                    'data' => [
                        'id'                 => $company->id,
                        'companyLabelString' => $labelStrings,
                        'default'            => [
                            'location'   => [
                                'id'   => $location->id,
                                'name' => $location->name,
                            ],
                            'department' => [
                                'id'   => $department->id,
                                'name' => $department->name,
                            ],
                            'team'       => [
                                'id'   => $team->id,
                                'name' => $team->name,
                            ],
                        ],
                    ],
                ], "Selected company code is valid.");
            }
        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * Verify a company code
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getLocations(Company $company, Request $request)
    {
        try {
            // if company id is empty get user's company
            if (empty($company->id)) {
                // check if user not logged in then send unauthorized response
                if (!$this->guard()->check()) {
                    return $this->unauthorizedResponse('You are not authorized to perform this request.');
                } else {
                    $user    = $this->user();
                    $company = $user->company()->select('companies.id')->first();
                }
            }

            // get company wise locations
            $locations = $company->locations()
                ->select('company_locations.id', 'company_locations.name')
                ->withCount(['departmentLocation', 'teamLocation'])
                ->having('department_location_count', '>', 0, 'and')
                ->having('team_location_count', '>', 0, 'and')
                ->orderBy('company_locations.name')
                ->get();

            return $this->successResponse(
                ($locations->count() > 0) ? new CompanyLocationsCollection($locations) : ['data' => []],
                ($locations->count() > 0) ? 'Locations list retrieved successfully.' : 'No results'
            );
        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * Get location wise departments
     *
     * @param CompanyLocation $location
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDepartments(CompanyLocation $location, Request $request)
    {
        try {
            // get departments list
            $departments = $location->departments()
                ->select('departments.id', 'departments.name')
                ->withCount('teams')
                ->orderBy('departments.name')
                ->having('teams_count', '>', 0)
                ->get();

            return $this->successResponse(
                ($departments->count() > 0) ? new CompanyDepartmentsCollection($departments) : ['data' => []],
                ($departments->count() > 0) ? 'Departments list retrieved successfully.' : 'No results'
            );
        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

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

            // get ongoing + upcoming challenge ids
            $challenge = $company->challenge()
                ->select('challenges.id', 'challenges.challenge_type')
                ->where('challenges.cancelled', false)
                ->where('challenges.finished', false)
                ->whereNotIn('challenges.challenge_type', ['inter_company', 'individual'])
                ->where(function ($query) use ($now, $appTimezone, $timezone) {
                    $query
                        ->whereRaw("CONVERT_TZ(challenges.start_date, '{$appTimezone}', '{$timezone}')  <= '{$now}' AND CONVERT_TZ(challenges.end_date, '{$appTimezone}', '{$timezone}') >= '{$now}'")
                        ->orWhereRaw("CONVERT_TZ(challenges.start_date, '{$appTimezone}', '{$timezone}')  >= '{$now}' AND CONVERT_TZ(challenges.end_date, '{$appTimezone}', '{$timezone}') >= '{$now}'");
                })
                ->get();

            // get ongoing + upcoming inter_company challenge ids
            $icChallenge = $company->icChallenge()
                ->select('challenges.id', 'challenges.challenge_type')
                ->where('challenges.cancelled', false)
                ->where('challenges.finished', false)
                ->where(function ($query) use ($now, $appTimezone, $timezone) {
                    $query
                        ->whereRaw("CONVERT_TZ(challenges.start_date, '{$appTimezone}', '{$timezone}')  <= '{$now}' AND CONVERT_TZ(challenges.end_date, '{$appTimezone}', '{$timezone}') >= '{$now}'")
                        ->orWhereRaw("CONVERT_TZ(challenges.start_date, '{$appTimezone}', '{$timezone}')  >= '{$now}' AND CONVERT_TZ(challenges.end_date, '{$appTimezone}', '{$timezone}') >= '{$now}'");
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
                ->when($company->auto_team_creation, function ($query, $value) use ($company) {
                    $query
                        ->withCount('users')
                        ->having('users_count', '<', $company->team_limit, 'or')
                        ->having('teams.default', '=', true, 'or');
                })
                ->whereNotIn('teams.id', $chInvolvedTeams)
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
