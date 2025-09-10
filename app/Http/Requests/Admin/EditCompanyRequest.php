<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Request;
use App\Models\Company;
use App\Models\CompanyBranding;
use App\Models\CompanyBrandingContactDetails;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EditCompanyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return access()->allow('update-company');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $except = function () {
            return $this->route('company')->id;
        };
        $expectDomainName = function () {
            $id      = $this->route('company')->id;
            $company = Company::find($id);
            if (!is_null($company) && !$company->is_reseller) {
                if (!is_null($company->parent_id)) {
                    $CompanyBrandingData = CompanyBranding::where('company_id', $company->parent_id)->first();
                } else {
                    $CompanyBrandingData = CompanyBranding::where('company_id', $id)->first();
                }
            } else {
                $CompanyBrandingData = CompanyBranding::where('company_id', $id)->first();
            }
            return (!empty($CompanyBrandingData->id) ? $CompanyBrandingData->id : '');
        };

        $homepageLogoLeft = function () {
            $thisId = $this->route('company')->id;
            $company    = Company::find($thisId);
            if ($company->is_reseller && is_null($company->parent_id)){
                return $company->portal_homepage_logo_left_name;
            }
           
        };
        $thisId     = $this->route('company')->id;
        $company    = Company::find($thisId);
        $loginLogoRight = function () {
            $thisId = $this->route('company')->id;
            $company    = Company::find($thisId);
            if ($company->is_reseller && is_null($company->parent_id)){
                return $company->portal_logo_main_name;
            }
        };

        $payload                    = $this->input();
        $logoMax                    = config('zevolifesettings.fileSizeValidations.company.logo', 2048);
        $brandingLogoMax            = config('zevolifesettings.fileSizeValidations.company.branding_logo', 2048);
        $brandingLoginBackgroundMax = config('zevolifesettings.fileSizeValidations.company.branding_login_background', (5 * 1024));
        $predefined_sub_domains     = implode(',', config('zevolifesettings.predefined_sub_domains'));
        $emailHeaderLogoMax         = config('zevolifesettings.fileSizeValidations.company.email_header', 2048);
        $emailHeaderLogoMinH        = config('zevolifesettings.imageConversions.company.email_header.height', 157);
        $emailHeaderLogoMaxW        = config('zevolifesettings.imageConversions.company.email_header.width', 600);
        $portalFaviconIconMax       = config('zevolifesettings.fileSizeValidations.company.portal_favicon_icon', 2048);
        $contactUsImageMax          = config('zevolifesettings.fileSizeValidations.company.contact_us_image', 2048);
        $appointmentImageMax        = config('zevolifesettings.fileSizeValidations.company.appointment_image', 2048);

        $rules = [
            'name'                     => 'required|alpha_num_spaces|min:2|max:50|unique:companies,name,' . $except(),
            'sub_domain'               => "required_if:is_branding,on|nullable|regex:/^[a-z0-9]+$/|max:150|not_in:{$predefined_sub_domains}|unique:company_branding,sub_domain," . $expectDomainName(),
            'registration_restriction' => 'required|boolean',
            'description'              => 'sometimes|nullable|max:200',
            'industry'                 => 'required|integer',
            'size'                     => 'required|max:100',
            'logo'                     => [
                "sometimes",
                "nullable",
                "image",
                "mimes:jpg,jpeg,png",
                "max:{$logoMax}",
                Rule::dimensions()->minWidth(320)->minHeight(320)->ratio(1 / 1.0),
            ],
            'subscription_start_date'  => 'required|date',
            'subscription_end_date'    => 'required|date',
            'location_name'            => 'required|min:2|max:100',
            'address_line1'            => 'required|address|min:2|max:100',
            'address_line2'            => 'sometimes|nullable|address|max:100',
            'county'                   => 'required|integer',
            'country'                  => 'required|integer',
            'timezone'                 => 'required',
            'postal_code'              => 'required|alpha_num_spaces|max:10',
            'group_restriction'        => 'sometimes',
            'group_restriction_rule'   => 'required_if:group_restriction,on',
            'onboarding_title'         => 'sometimes|nullable|max:100',
            'onboarding_description'   => 'sometimes|nullable|max:500',
            'login_screen_logo'        => [
                "sometimes",
                "nullable",
                "image",
                "mimes:jpg,jpeg,png",
                "max:{$brandingLogoMax}",
                Rule::dimensions()->minWidth(250)->minHeight(100)->ratio(2.5 / 1),
            ],
            'login_screen_background'  => [
                "sometimes",
                "nullable",
                "image",
                "mimes:jpg,jpeg,png",
                "max:{$brandingLoginBackgroundMax}",
                Rule::dimensions()->minWidth(1920)->minHeight(1280)->ratio(1.5 / 1),
            ],
            'email_header'             => [
                "sometimes",
                "nullable",
                "image",
                "mimes:jpg,jpeg,png",
                "max:{$emailHeaderLogoMax}",
                Rule::dimensions()->minWidth($emailHeaderLogoMaxW)->minHeight($emailHeaderLogoMinH),
            ],
            'survey'                   => 'required_if:enable_survey,on',
            'survey_frequency'         => 'required_if:enable_survey,on',
            'survey_roll_out_day'      => 'required_if:enable_survey,on',
            'survey_roll_out_time'     => 'required_if:enable_survey,on',
            'assigned_roles'           => 'required',
            'portal_domain'            => ['unique:company_branding,portal_domain'],
            'portal_theme'             => 'required_if:is_reseller,yes',
            'portal_logo_optional'     => [
                "image",
                "mimes:jpg,jpeg,png",
                "max:{$brandingLogoMax}",
                Rule::dimensions()->minWidth(250)->minHeight(100)->ratio(2.5 / 1),
            ],
            'portal_background_image'  => [
                "image",
                "mimes:jpg,jpeg,png",
                "max:{$brandingLoginBackgroundMax}",
                Rule::dimensions()->minWidth(1350)->minHeight(900)->ratio(1.5 / 1),
            ],
            'portal_favicon_icon'     => [
                "sometimes",
                "nullable",
                "image",
                "mimes:jpg,jpeg,png",
                "max:{$portalFaviconIconMax}",
                Rule::dimensions()->minWidth(40)->minHeight(40)->ratio(1 / 1.0),
            ],
            'portal_sub_description'   => ['sometimes', 'nullable', 'regex:/(^([^0-9<>%$#@_^]*))+/', 'max:300'],
            'dt_max_sessions_user'     => 'numeric|min:0|max:10000',
            'dt_max_sessions_company'  => 'numeric|min:0|max:10000',
            'terms_url'                => 'required_if:is_reseller,yes|url',
            'privacy_policy_url'       => 'required_if:is_reseller,yes|url',
            'companyplan'              => 'required',
            'dt_title'                 => "sometimes|nullable|max:100",
            'dt_description'           => "sometimes|nullable|max:500",
            'portal_homepage_logo_right'         => [
                "image",
                "mimes:jpg,jpeg,png",
                "max:{$brandingLogoMax}",
                Rule::dimensions()->minWidth(250)->minHeight(100)->ratio(2.5 / 1),
            ],
        ];

        $loginLogoRightRules = $homepageLogoLeftRules = [];

        $contactUsImage = function () {
            $thisId     = $this->route('company')->id;
            $company    = Company::find($thisId);
            $brandingContactUsDetails = CompanyBrandingContactDetails::where('company_id', $thisId)->orWhere('company_id', $company->parent_id)->first();
            return (!empty($brandingContactUsDetails->contact_us_image_name) ? $brandingContactUsDetails->contact_us_image_name : null);
        };

        $contactUsImageRules = [
            empty($contactUsImage()) && ($company->is_reseller || (!$company->is_reseller && !is_null($company->parent_id))) ? "required" : "",
            "image",
            "mimes:jpg,jpeg,png",
            "max:{$contactUsImageMax}",
            Rule::dimensions()->minWidth(800)->minHeight(800)->ratio(1 / 1.0),
        ];

        $appointmentImage = function () {
            $thisId     = $this->route('company')->id;
            $company    = Company::find($thisId);
            $companyDetails = Company::where('id', $thisId)->orWhere('id', $company->parent_id)->first();
            return (!empty($companyDetails->appointment_image_name) ? $companyDetails->appointment_image_name : null);
        };

        $appointmentImageRules = [
            empty($appointmentImage()) && ($company->is_reseller || (!$company->is_reseller && !is_null($company->parent_id))) ? "required" : "",
            "image",
            "mimes:jpg,jpeg,png",
            "max:{$appointmentImageMax}",
            Rule::dimensions()->minWidth(800)->minHeight(800)->ratio(1 / 1.0),
        ];
        
        $loginLogoRightRules = [
            empty($loginLogoRight()) && ($company->is_reseller && $company->parent_id == null)  ? "required" : "",
            "image",
            "mimes:jpg,jpeg,png",
            "max:{$brandingLogoMax}",
            Rule::dimensions()->minWidth(200)->minHeight(100)->ratio(2 / 1),
        ];

        $homepageLogoLeftRules = [
            empty($homepageLogoLeft()) && ($company->is_reseller && $company->parent_id == null)  ? "required" : "",
            "image",
            "mimes:jpg,jpeg,png",
            "max:{$brandingLogoMax}",
            Rule::dimensions()->minWidth(200)->minHeight(100)->ratio(2 / 1),
        ];
        $rules['portal_logo_main']          = $loginLogoRightRules;
        $rules['portal_homepage_logo_left'] = $homepageLogoLeftRules;
        $rules['contact_us_image']          = $contactUsImageRules;
        $rules['appointment_image']         = $appointmentImageRules;
        
        if(!empty($payload['companyType']) && $payload['companyType'] != 'normal'){
            $rules['dt_wellbeing_sp_ids']       = ['required_if:dtExistsHidden,1', 'required_if:companyplan,1', 'required_if:companyplan,2'];
            $rules['dt_servicemode']            = ['required_if:dtExistsHidden,1', 'required_if:companyplan,1', 'required_if:companyplan,2', 'min:1'];
        }

        if(!empty($payload['companyType']) && $payload['companyType'] != 'normal' && $payload['companyType'] != 'zevo'){
            // Contact us fields validation
            $rules['contact_us_header']         = ['required', 'regex:/(^([^<>$#@^]*))+/', 'max:50'];
            $rules['contact_us_request']        = ['required'];
            $rules['contact_us_description']    = ['sometimes', 'nullable'];
            
            // Appointment fields validation
            $rules['appointment_title']         = ['required', 'regex:/(^([^<>$#@^]*))+/', 'max:50'];
            $rules['appointment_description']   = ['sometimes', 'nullable', 'regex:/(^([^<>$#@^]*))+/', 'max:300'];            
        }

        return $rules;
    }

    /**
     * Custom error messages
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'logo.max'                           => 'The :attribute may not be greater than 2MB.',
            'login_screen_logo.max'              => 'The :attribute may not be greater than 2MB.',
            'login_screen_background.max'        => 'The :attribute may not be greater than 5MB.',
            'sub_domain.regex'                   => 'The :attribute may only contain small letters and numbers.',
            'group_restriction_rule.required_if' => 'The select rule field is required when group restriction rules is on.',
            'sub_domain.required_if'             => 'The :attribute field is required when domain branding is on.',
            'sub_domain.not_in'                  => "The :attribute is predefined and that isn't available",
            'survey.required_if'                 => 'The :attribute field is required when survey is on.',
            'survey_frequency.required_if'       => 'The :attribute field is required when survey is on.',
            'survey_roll_out_day.required_if'    => 'The :attribute field is required when survey is on.',
            'survey_roll_out_time.required_if'   => 'The :attribute field is required when survey is on.',
            'logo.dimensions'                    => 'The uploaded image does not match the given dimension and ratio.',
            'login_screen_logo.dimensions'       => 'The uploaded image does not match the given dimension and ratio.',
            'login_screen_background.dimensions' => 'The uploaded image does not match the given dimension and ratio.',
            'dt_wellbeing_sp_ids.required_if'    => 'The staff field is required',
            'dt_servicemode.required_if'         => 'Please select at least one service',
            'dt_max_sessions_user.max'           => 'Enter the value between 0 to 10000',
            'dt_max_sessions_company.max'        => 'Enter the value between 0 to 10000',
            'terms_url.required_if'              => 'The :attribute field is required',
            'privacy_policy_url.required_if'     => 'The :attribute field is required',
            'companyplan.required_if'            => 'The :attribute field is required',
            'portal_favicon_icon.required_if'    => 'The favicon icon is required field',
            'portal_favicon_icon.dimensions'     => 'The uploaded image does not match the given dimension and ratio.',
            'portal_logo_main.required'          => 'The :attribute field is required for reseller company.',
            'portal_homepage_logo_left.required' => 'The :attribute field is required for reseller company.',
            'contact_us_header.required'         => 'This field is required!',
            'contact_us_request.required'        => 'This field is required!',
            'contact_us_image.required'          => 'This field is required!',
        ];
    }

    public function attributes(): array
    {
        return [
            'onboarding_title'       => 'login screen title',
            'onboarding_description' => 'login screen description',
            'sub_domain'             => 'sub domain name',
            'survey'                 => 'survey selection',
            'terms_url'              => 'Terms of Use',
            'privacy_policy_url'     => 'Privacy Policy',
            'portal_logo_main'       => 'portal login logo right'
        ];
    }
}
