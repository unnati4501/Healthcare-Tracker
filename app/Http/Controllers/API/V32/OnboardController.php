<?php declare (strict_types = 1);

namespace App\Http\Controllers\API\V32;

use App\Http\Controllers\API\V30\OnboardController as v30onboardController;
use App\Http\Collections\V10\PortalSurveyCollection;
use App\Http\Requests\Api\V15\VerifyCompanyCodeRequest;
use App\Models\Company;
use App\Models\CpFeatures;
use App\Models\Department;
use App\Models\ZcQuestion;
use App\Models\ZcSurveyLog;
use App\Models\ZcSurveyUserLog;
use DB;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OnboardController extends v30onboardController
{
    /**
     * Verified company code
     *
     * @param VerifyCompanyCodeRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function verifyCompanyCode(VerifyCompanyCodeRequest $request)
    {
        try {
            $company = Company::select('id', 'subscription_start_date', 'subscription_end_date', 'allow_app', 'allow_portal')->where('code', $request->code)->first();
            if (empty($company)) {
                return $this->invalidResponse([
                    'code' => ['The selected company code is invalid.'],
                ], 'The given data is invalid.');
            } else {
                $xDeviceOs = strtolower($request->header('X-Device-Os', ""));
                $timezone  = strtolower($request->header('X-User-Timezone', config('app.timezone')));

                // Check condition for access portal and app
                if ($xDeviceOs == config('zevolifesettings.PORTAL') && !$company->allow_portal ) {
                    // if company don't have portal access
                    return $this->notaccessFailedResponse(\trans('api_labels.auth.not_access_portal'));
                } elseif (($xDeviceOs == config('zevolifesettings.IOS') || $xDeviceOs == config('zevolifesettings.ANDROID')) && !$company->allow_app ) {
                    return $this->notaccessFailedResponse(\trans('api_labels.auth.not_access_app'));
                }

                // validate company's subscription status
                $now = now($timezone)->setTimezone(config('app.timezone'))->toDateTimeString();
                if (!($company->subscription_start_date <= $now && $company->subscription_end_date >= $now)) {
                    return $this->notaccessFailedResponse(\trans('auth.company_status'));
                }

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

                // get all locations of company
                $locationList = $company->locations()
                    ->select('company_locations.id', 'company_locations.name')
                    ->withCount(['departmentLocation', 'teamLocation'])
                    ->having('department_location_count', '>', 0, 'and')
                    ->having('team_location_count', '>', 0, 'and')
                    ->orderBy('company_locations.name')
                    ->get();
                $isMultipleLocationAvailable = (count($locationList) > 1) ;

                // Company plan Feature List Json
                $featuresList = getCompanyPlanRecordsForVerifyCompanyCode($company);

                return $this->successResponse([
                    'data' => [
                        'id'                 => $company->id,
                        'companyLabelString' => $labelStrings,
                        'default'            => [
                            'isMultipleLocationAvailable' => $isMultipleLocationAvailable,
                            'location'                    => [
                                'id'   => $location->id,
                                'name' => $location->name,
                            ],
                            'department'                  => [
                                'id'   => $department->id,
                                'name' => $department->name,
                            ],
                            'team'                        => [
                                'id'   => $team->id,
                                'name' => $team->name,
                            ],
                        ],
                        'planFeatureList'    => $featuresList,
                    ],
                ], "Selected company code is valid.");
            }
        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * Retrieve survey by survey log id
     *
     * @param ZcSurveyLog $surveyLog
     * @return \Illuminate\Http\JsonResponse
     */
    public function survey(? ZcSurveyLog $surveyLog, Request $request)
    {
        try {
            // check if surveyLog is not passed then show error
            if (is_null($surveyLog->getKey())) {
                return $this->invalidResponse([
                    'surveyLogId' => [
                        'The survey log id field is required.',
                    ],
                ], 'The given data is invalid.', 422);
            }

            $user    = $this->user();
            $company = $user->company()
                ->select('companies.id', 'companies.subscription_start_date', 'companies.subscription_end_date')
                ->first();
            $xDeviceOs   = strtolower($request->header('X-Device-Os', ""));
            $checkAccess = getCompanyPlanAccess($user, 'audit-survey');

            if (!$checkAccess) { // && $xDeviceOs != config('zevolifesettings.PORTAL')
                return $this->notFoundResponse('Survey is disabled for this company.');
            }

            // check company id of survey and logged in users both are same
            if ($surveyLog->company_id != $company->id) {
                return $this->notFoundResponse('It seems survey is not available for your company.');
            }

            $timezone                = (!empty($user->timezone) ? $user->timezone : config('app.timezone'));
            $now                     = now($timezone)->toDateTimeString();
            $subscription_start_date = Carbon::parse($company->subscription_start_date, config('app.timezone'))
                ->setTimezone($timezone)->toDateTimeString();
            $subscription_end_date = Carbon::parse($company->subscription_end_date, config('app.timezone'))
                ->setTimezone($timezone)->toDateTimeString();

            // check company subscription is active or not
            if ($now > $subscription_start_date && $now < $subscription_end_date) {
                $surveyExpireDate = Carbon::parse($surveyLog->expire_date, config('app.timezone'))
                    ->setTimezone($timezone)->toDateTimeString();
                // check survey is active or not
                if ($surveyExpireDate > $now) {
                    $userSurveyLog = $surveyLog->surveyUserLogs()->where('user_id', $user->id)->first();

                    // check if survey_to_all is set to false and user isn't present in survey user logs then prevent user to avail the survey as user isn't selected in survey config
                    if (!$surveyLog->survey_to_all && is_null($userSurveyLog)) {
                        return $this->notFoundResponse('It seems survey is not available for you.');
                    }

                    // check user already submitted survey or not
                    // if (isset($userSurveyLog) && !is_null($userSurveyLog->survey_submitted_at)) {
                    //     return $this->notFoundResponse('Survey already submitted.');
                    // }

                    $surveyQuestions = ZcQuestion::leftJoin('zc_survey_questions', 'zc_questions.id', '=', 'zc_survey_questions.question_id')
                        ->Join('zc_question_types', 'zc_question_types.id', '=', 'zc_survey_questions.question_type_id')
                        ->where('zc_survey_questions.survey_id', $surveyLog->survey_id)
                        ->orderBy("zc_survey_questions.order_priority", "ASC")
                        ->select(
                            'zc_questions.id',
                            'zc_survey_questions.survey_id',
                            'zc_survey_questions.question_id',
                            'zc_questions.question_type_id',
                            'zc_questions.title',
                            'zc_questions.status',
                            'zc_questions.created_at',
                            'zc_questions.updated_at'
                        )
                        ->get();

                    $userSurveyCount = ZcSurveyUserLog::where('user_id', $user->id)->count();

                    return $this->successResponse([
                        'data' => [
                            'surveyId'          => $surveyLog->id,
                            'alreadySubmitted'  => (isset($userSurveyLog) && !is_null($userSurveyLog->survey_submitted_at)) ,
                            'questions'         => (($surveyQuestions->count() > 0) ? new PortalSurveyCollection($surveyQuestions) : []),
                            'feedbackAvailable' => $userSurveyCount > 1 
                        ],
                    ], 'Survey retrieved successfully.');
                } else {
                    return $this->notFoundResponse('This survey has expired.');
                }
            } else {
                return $this->notFoundResponse('Your company subscription has expired. Please contact your admin or the Zevo Account Manager');
            }
        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }
}
