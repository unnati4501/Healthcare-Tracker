<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Request;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EditUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return access()->allow('update-user');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $payload           = $this->input();
        $logoMax           = config('zevolifesettings.fileSizeValidations.user.logo', 2048);
        $gender            = implode(',', array_keys(config('zevolifesettings.gender', [])));
        $userType          = (!empty($payload['user_type']) ? $payload['user_type'] : 'user');
        $aboutMeCharsLimit = config("zevolifesettings.user_about_me_role_wise_limit.{$userType}", 200);
        $user              = auth()->user();
        $role              = getUserRole();
        $teamAccess        = getCompanyPlanAccess($user, 'team-selection');
        $regex             = '/^(https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w \.-]*)*\/?$/';
        $thisUser          = $this->route('user');
        $id                = $thisUser->id;
        $rules             = [
            'role_group'              => 'required|in:company,zevo,reseller',
            'user_type'               => 'required',
            'availability'            => 'required_if:user_type,health_coach',
            'from_date'               => 'required_if:availability,2',
            'to_date'                 => 'required_if:availability,2',
            'role'                    => 'sometimes|nullable|required_if:role_group,zevo|exclude_if:user_type,health_coach',
            'company'                 => 'required_if:role_group,reseller,company',
            'first_name'              => ['required', 'regex:/(^([^0-9<>%$#@!*()_]*))+/', 'min:2', 'max:50'], //'required|min:2|max:20',
            'last_name'               => ['required', 'regex:/(^([^0-9<>%$#@!*()_]*))+/', 'min:2', 'max:50'],
            'about'                   => "sometimes|nullable|user_about_me:$aboutMeCharsLimit",
            'logo'                    => [
                "sometimes",
                "nullable",
                "image",
                "mimes:jpg,jpeg,png",
                "max:{$logoMax}",
                Rule::dimensions()->minWidth(512)->minHeight(512)->ratio(1 / 1.0),
            ],
            'gender'                  => "required_if:user_type,wellbeing_team_lead|in:{$gender}",
            'expertise'               => 'required_if:user_type,health_coach',
            'timezone'                => 'required_if:user_type,health_coach,wellbeing_specialist,counsellor,wellbeing_team_lead',
            'slots'                   => 'required_if:user_type,health_coach',
            'slots_exist'             => 'required_if:user_type,health_coach',
            'counsellor_skills'       => 'required_if:user_type,counsellor',
            'language'                => 'required_if:user_type,wellbeing_specialist',
            'video_conferencing_mode' => 'required_if:user_type,wellbeing_specialist',
            'shift'                   => 'required_if:user_type,wellbeing_specialist',
            'video_link'              => 'required_if:user_type,wellbeing_specialist|sometimes|nullable|regex:' . $regex,
            'years_of_experience'     => 'required_if:user_type,wellbeing_specialist|sometimes|nullable|numeric|user_about_me:5',
            //'user_services'           => 'required_if:user_type,wellbeing_specialist',
            'email'                   => ['required_if:role_slug,super_admin', 'unique:users,email,' . $id, 'email_simple', 'max:255', 'email_disposable'],
            'responsibilities'        => [
                "required_if:user_type,wellbeing_specialist",
            ],
            'expertise_wbs'           => [
                "required_if:responsibilities,2,3",
            ],

        ];
        if ($role->group == 'reseller' || ($role->group == 'company' && $teamAccess)) {
            $rules['department'] = 'required_if:role_group,reseller,company';
            $rules['team']       = 'required_if:role_group,reseller,company';
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
        $payload           = $this->input();
        $userType          = (!empty($payload['user_type']) ? $payload['user_type'] : 'user');
        $aboutMeCharsLimit = config("zevolifesettings.user_about_me_role_wise_limit.{$userType}", 200);

        return [
            'logo.max'                            => 'The :attribute may not be greater than 2MB.',
            'company.required_if'                 => 'The :attribute field is required.',
            'department.required_if'              => 'The :attribute field is required.',
            'team.required_if'                    => 'The :attribute field is required.',
            'from_date.required_if'               => 'The :attribute field is required.',
            'to_date.required_if'                 => 'The :attribute field is required.',
            'expertise.required_if'               => 'The :attribute field is required when user type is wellbeing specialist.',
            'timezone.required_if'                => 'The :attribute field is required when user type is wellbeing specialist, counsellor, wellbeing consultant and clinical lead.',
            'slots.required_if'                   => 'The slots are required when user type is wellbeing specialist.',
            'slots_exist.required_if'             => 'The slots are required when user type is wellbeing specialist.',
            'role.required_if'                    => 'The :attribute field is required.',
            'role.required_unless'                => 'The :attribute field is required.',
            'about.user_about_me'                 => "The :attribute may not be greater than {$aboutMeCharsLimit} characters.",
            'logo.dimensions'                     => 'The uploaded image does not match the given dimension and ratio.',
            'counsellor_skills.required_if'       => 'The :attribute field is required when user type is counsellor.',
            'years_of_experience.user_about_me'   => "The :attribute may not be greater than 5 characters.",
            //'first_name.hyphen_spaces'      => 'The first name may only contain letters, hyphen and spaces.',
            //'last_name.hyphen_spaces'       => 'The last name may only contain letters, hyphen and spaces.',
            'first_name.required'                 => 'Please enter first name.',
            'last_name.required'                  => 'Please enter last name.',
            'first_name.regex'                    => 'Please enter valid first name.',
            'last_name.regex'                     => 'Please enter valid last name.',
            'language.required_if'                => 'The :attribute field is required when user type is wellbeing specialist.',
            'video_conferencing_mode.required_if' => 'The :attribute field is required when user type is wellbeing specialist.',
            'shift.required_if'                   => 'The :attribute field is required when user type is wellbeing specialist.',
            'video_link.required_if'              => 'The :attribute field is required when user type is wellbeing specialist.',
            'years_of_experience.required_if'     => 'The :attribute field is required when user type is wellbeing specialist.',
            //'user_services.required_if'         => 'The service type & subcategory field is required when user type is wellbeing specialist.',
            'gender.required_if'                  => 'The gender filed is required',
            'email.required_if'                   => 'The email filed is required',
            'responsibilities.required_if'        => 'The :attribute field is required when user type is wellbeing specialist.',
            'expertise_wbs.required_if'           => 'Please select the Expertise',
            'email.email_disposable'              => 'The :attribute must be a valid email address.',
        ];
    }
}
