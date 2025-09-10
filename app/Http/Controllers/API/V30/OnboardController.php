<?php declare (strict_types = 1);

namespace App\Http\Controllers\API\V30;

use App\Http\Controllers\API\V25\OnboardController as v25onboardController;
use App\Http\Requests\Api\V15\VerifyCompanyCodeRequest;
use App\Models\Company;
use App\Models\CpFeatures;
use App\Models\Department;
use DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OnboardController extends v25onboardController
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
}
