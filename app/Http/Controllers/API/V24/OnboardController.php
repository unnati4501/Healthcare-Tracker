<?php declare (strict_types = 1);

namespace App\Http\Controllers\API\V24;

use App\Http\Controllers\API\V23\OnboardController as v23onboardController;
use App\Http\Requests\Api\V15\VerifyCompanyCodeRequest;
use App\Models\Company;
use App\Models\CpFeatures;
use App\Models\Department;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use DB;

class OnboardController extends v23onboardController
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
                if ($xDeviceOs == config('zevolifesettings.PORTAL') && !$company->allow_portal) {
                    // if company don't have portal access
                    return $this->notaccessFailedResponse(\trans('api_labels.auth.not_access_portal'));
                } elseif (($xDeviceOs == config('zevolifesettings.IOS') || $xDeviceOs == config('zevolifesettings.ANDROID')) && !$company->allow_app) {
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

                // Company plan Feature List Json
                $companyPlan  = $company->companyplan()->first();
                $featuresList = [];
                if (!empty($companyPlan)) {
                    $companyPlanFeature = $companyPlan->planFeatures()->select('feature_id')->get()->pluck('feature_id')->toArray();
                } else {
                    $companyPlanFeature = DB::table('cp_plan_features')->select('feature_id')->where('plan_id', 2)->get()->pluck('feature_id')->toArray();
                }
                $parentFeatures     = CpFeatures::select('id', 'parent_id', 'name', 'slug', 'manage')->where('parent_id', null)->get();

                foreach ($parentFeatures as $key => $value) {
                    $result = CpFeatures::select('id', 'name', 'slug')->where('parent_id', $value->id)->get()->toArray();
                    if (!empty($result)) {
                        $tempArray = [];
                        foreach ($result as $childkey => $childvalue) {
                            $slug             = str_replace('-', '_', $childvalue['slug']);
                            $tempArray[$slug] = (in_array($childvalue['id'], $companyPlanFeature));
                        }
                        $slug                = str_replace('-', '_', $value->slug);
                        $featuresList[$slug] = $tempArray;
                    } else {
                        $slug                = str_replace('-', '_', $value->slug);
                        $featuresList[$slug] = (in_array($value->id, $companyPlanFeature));
                    }
                }


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
