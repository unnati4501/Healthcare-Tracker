<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Request;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateCompanyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return access()->allow('create-company');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
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
            'name'                     => 'required|alpha_num_spaces|min:2|max:50|unique:companies,name',
            'registration_restriction' => 'required|boolean',
            'description'              => 'sometimes|nullable|max:200',
            'industry'                 => 'required|integer',
            'size'                     => 'required|max:100',
            'logo'                     => [
                "required",
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
            'sub_domain'               => "required_if:is_branding,on|nullable|regex:/^[a-z0-9]+$/|max:150|unique:company_branding,sub_domain|not_in:{$predefined_sub_domains}",
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
            'survey'                   => 'required_if:enable_survey,on',
            'survey_frequency'         => 'required_if:enable_survey,on',
            'survey_roll_out_day'      => 'required_if:enable_survey,on',
            'survey_roll_out_time'     => 'required_if:enable_survey,on',
            'assigned_roles'           => 'required',
            'parent_company'           => 'required_if:is_reseller,yes',
            'email_header'             => [
                "sometimes",
                "nullable",
                "image",
                "mimes:jpg,jpeg,png",
                "max:{$emailHeaderLogoMax}",
                Rule::dimensions()->minWidth($emailHeaderLogoMaxW)->minHeight($emailHeaderLogoMinH),
            ],
            'companyplan'              => 'required',
            //dev.zevowork.com ,  'max:150', 'regex:/^(http:\/\/www\.|https:\/\/www\.|http:\/\/|https:\/\/)?[a-z0-9]+([\-\.]{1}[a-z0-9]+)*\.[a-z]{2,5}(:[0-9]{1,5})?(\/.*)?$/'
            // 'portal_title'             => 'required_if:is_reseller,yes',
            'portal_domain'            => 'required_if:is_reseller,yes',
            'portal_theme'             => 'required_if:is_reseller,yes',
            'portal_logo_main'         => [
                "required_if:is_reseller,yes",
                "image",
                "mimes:jpg,jpeg,png",
                "max:{$brandingLogoMax}",
                Rule::dimensions()->minWidth(200)->minHeight(100)->ratio(2 / 1),
            ],
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
            'portal_sub_description'   => ['sometimes', 'nullable', 'regex:/(^([^0-9<>%$#@_^]*))+/', 'max:300'],
            'parent_company'           => 'required_if:is_reseller,no',
            'dt_max_sessions_user'     => 'numeric|min:0|max:10000',
            'dt_max_sessions_company'  => 'numeric|min:0|max:10000',
            'terms_url'                => 'required_if:is_reseller,yes|url',
            'privacy_policy_url'       => 'required_if:is_reseller,yes|url',
            'portal_favicon_icon'      => [
                "required_if:is_reseller,yes",
                "image",
                "mimes:jpg,jpeg,png",
                "max:{$portalFaviconIconMax}",
                Rule::dimensions()->minWidth(40)->minHeight(40)->ratio(1 / 1.0),
            ],
            'dt_title'              => "sometimes|nullable|max:100",
            'dt_description'        => "sometimes|nullable|max:500",
            'portal_homepage_logo_right'     => [
                "image",
                "mimes:jpg,jpeg,png",
                "max:{$brandingLogoMax}",
                Rule::dimensions()->minWidth(250)->minHeight(100)->ratio(2.5 / 1),
            ],
            'portal_homepage_logo_left'     => [
                "required_if:is_reseller,yes",
                "image",
                "mimes:jpg,jpeg,png",
                "max:{$brandingLogoMax}",
                Rule::dimensions()->minWidth(200)->minHeight(100)->ratio(2 / 1),
            ],
        ];
        
        $contactUsImageRules = [
            "required_if:is_reseller,yes",
            "image",
            "mimes:jpg,jpeg,png",
            "max:{$contactUsImageMax}",
            Rule::dimensions()->minWidth(800)->minHeight(800)->ratio(1 / 1.0),
        ];

        $appointmentImageRules = [
            "sometimes",
            "nullable",
            "image",
            "mimes:jpg,jpeg,png",
            "max:{$appointmentImageMax}",
            Rule::dimensions()->minWidth(800)->minHeight(800)->ratio(1 / 1.0),
        ];
        
        if($payload['companyType'] != 'normal'){
            $rules['dt_wellbeing_sp_ids']       = ['required_if:dtExistsHidden,1', 'required_if:companyplan,1', 'required_if:companyplan,2'];
            $rules['dt_servicemode']            = ['required_if:dtExistsHidden,1', 'required_if:companyplan,1', 'required_if:companyplan,2', 'min:1'];
        }

        if($payload['companyType'] != 'normal' && $payload['companyType'] != 'zevo'){
            $rules['contact_us_header']         = ['required', 'regex:/(^([^<>$#@^]*))+/', 'max:50'];
            $rules['contact_us_request']        = ['required'];
            $rules['contact_us_description']    = ['sometimes', 'nullable'];
            $rules['contact_us_image']          = $contactUsImageRules;

            //Appointment fields validation
            $rules['appointment_title']         = ['required', 'regex:/(^([^<>$#@^]*))+/', 'max:50'];
            $rules['appointment_description']   = ['sometimes', 'nullable', 'regex:/(^([^<>$#@^]*))+/', 'max:500'];
            $rules['appointment_image']         = $appointmentImageRules;
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
            'logo.max'                                  => 'The :attribute may not be greater than 2MB.',
            'login_screen_logo.max'                     => 'The :attribute may not be greater than 2MB.',
            'login_screen_background.max'               => 'The :attribute may not be greater than 5MB.',
            'sub_domain.regex'                          => 'The :attribute may only contain small letters and numbers.',
            'group_restriction_rule.required_if'        => 'The select rule field is required when group restriction rules is on.',
            'sub_domain.required_if'                    => 'The :attribute field is required when domain branding is on.',
            'sub_domain.not_in'                         => "The :attribute is predefined and that isn't available",
            'survey.required_if'                        => 'The :attribute field is required when survey is on.',
            'survey_frequency.required_if'              => 'The :attribute field is required when survey is on.',
            'survey_roll_out_day.required_if'           => 'The :attribute field is required when survey is on.',
            'survey_roll_out_time.required_if'          => 'The :attribute field is required when survey is on.',
            'portal_title.required_if'                  => 'The :attribute field is required for reseller company.',
            'portal_domain.required_if'                 => 'The :attribute field is required for reseller company.',
            'portal_theme.required_if'                  => 'The :attribute field is required for reseller company.',
            'portal_logo_main.required_if'              => 'The :attribute field is required for reseller company.',
            'portal_description.required_if'            => 'The :attribute field is required for reseller company.',
            'logo.dimensions'                           => 'The uploaded image does not match the given dimension and ratio.',
            'login_screen_logo.dimensions'              => 'The uploaded image does not match the given dimension and ratio.',
            'login_screen_background.dimensions'        => 'The uploaded image does not match the given dimension and ratio.',
            'portal_logo_main.dimensions'               => 'The uploaded image does not match the given dimension and ratio.',
            'portal_logo_optional.dimensions'           => 'The uploaded image does not match the given dimension and ratio.',
            'portal_background_image.dimensions'        => 'The uploaded image does not match the given dimension and ratio.',
            'companyplan.required_if'                   => 'The :attribute field is required',
            'parent_company.required_if'                => 'The :attribute field is required',
            'dt_wellbeing_sp_ids.required_if'           => 'The staff field is required',
            'dt_servicemode.required_if'                => 'Please select at least one service',
            'dt_max_sessions_user.max'                  => 'Enter the value between 0 to 10000',
            'dt_max_sessions_company.max'               => 'Enter the value between 0 to 10000',
            'terms_url.required_if'                     => 'The :attribute field is required',
            'privacy_policy_url.required_if'            => 'The :attribute field is required',
            'portal_favicon_icon.required_if'           => 'The favicon icon is required field',
            'portal_favicon_icon.dimensions'            => 'The uploaded image does not match the given dimension and ratio.',
            'portal_homepage_logo_left.required_if'     => 'The :attribute field is required for reseller company.',
            'portal_homepage_logo_right.dimensions'     => 'The uploaded image does not match the given dimension and ratio.',
            'portal_homepage_logo_left.dimensions'      => 'The uploaded image does not match the given dimension and ratio.',
            'contact_us_header.required'                => 'This field is required!',
            'contact_us_request.required'               => 'This field is required!',
            'contact_us_image.required'                 => 'This field is required!',
        ];
    }

    public function attributes(): array
    {
        return [
            'onboarding_title'       => 'login screen title',
            'onboarding_description' => 'login screen description',
            'sub_domain'             => 'sub domain name',
            'survey'                 => 'survey selection',
            'companyplan'            => 'company plan',
            'terms_url'              => 'Terms of Use',
            'privacy_policy_url'     => 'Privacy Policy',
            'portal_logo_main'       => 'portal login logo right'
        ];
    }
}
