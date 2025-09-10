<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Request;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $user              = auth()->user();
        $userType          = (($user->is_coach) ? 'health_coach' : 'user');
        $aboutMeCharsLimit = config("zevolifesettings.user_about_me_role_wise_limit.{$userType}", 200);
        $logoMax           = config('zevolifesettings.fileSizeValidations.user.logo', 2048);
        $gender            = implode(',', array_keys(config('zevolifesettings.gender', [])));
        $loggedInUser      = getUserRole($user);

        if ($loggedInUser->slug == 'wellbeing_specialist') {
            $rules['logo'] = [
                "sometimes",
                "nullable",
                "image",
                "mimes:jpg,jpeg,png",
                "max:{$logoMax}",
                Rule::dimensions()->minWidth(512)->minHeight(512)->ratio(1 / 1.0),
            ];
            $rules['first_name']       = ['required', 'regex:/(^([^0-9<>%$#@!*()_]*))+/', 'min:2', 'max:50'];
            $rules['last_name']        = ['required', 'regex:/(^([^0-9<>%$#@!*()_]*))+/', 'min:2', 'max:50'];
            $rules['counsellor_cover'] = [
                "sometimes",
                "nullable",
                "image",
                "mimes:jpg,jpeg,png",
                "max:{$logoMax}",
                Rule::dimensions()->minWidth(1280)->minHeight(640)->ratio(2 / 1),
            ];
            $rules['gender']              = "required|in:{$gender}";
            $rules['shift']               = 'required';
            $rules['years_of_experience'] = 'required|numeric|user_about_me:5';
            $rules['language']            = 'required';
            $rules['about']               = "required|max:{$aboutMeCharsLimit}";
        } elseif ($loggedInUser->slug == 'health_coach' || $loggedInUser->slug == 'wellbeing_team_lead' ) {
            $rules['logo'] = [
                "sometimes",
                "nullable",
                "image",
                "mimes:jpg,jpeg,png",
                "max:{$logoMax}",
                Rule::dimensions()->minWidth(512)->minHeight(512)->ratio(1 / 1.0),
            ];
            $rules['first_name']            = ['required', 'regex:/(^([^0-9<>%$#@!*()_]*))+/', 'min:2', 'max:50'];
            $rules['last_name']             = ['required', 'regex:/(^([^0-9<>%$#@!*()_]*))+/', 'min:2', 'max:50'];
            $rules['gender']                = "required|in:{$gender}";
            $rules['about']                 = "required|max:{$aboutMeCharsLimit}";
        } else {
            $rules = [
                'logo'            => [
                    "sometimes",
                    "nullable",
                    "image",
                    "mimes:jpg,jpeg,png",
                    "max:{$logoMax}",
                    Rule::dimensions()->minWidth(512)->minHeight(512)->ratio(1 / 1.0),
                ],
                'first_name'      => ['required', 'regex:/(^([^0-9<>%$#@!*()_]*))+/', 'min:2', 'max:50'],
                'last_name'       => ['required', 'regex:/(^([^0-9<>%$#@!*()_]*))+/', 'min:2', 'max:50'],
                'date_of_birth'   => 'required|date_format:Y-m-d',
                'height'          => 'required|integer|max:1000000',
                'weight'          => 'required|numeric|between:0,250',
                'gender'          => "required|in:{$gender}",
                'about'           => "sometimes|nullable|max:{$aboutMeCharsLimit}",
                'logo.dimensions' => 'The uploaded image does not match the given dimension and ratio.',
            ];
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
            'logo.max'                          => 'The :attribute may not be greater than 2MB.',
            /*'first_name.hyphen_spaces' => 'The first name may only contain letters, hyphen and spaces.',
            'last_name.hyphen_spaces'  => 'The last name may only contain letters, hyphen and spaces.',*/
            'first_name.required'               => 'Please enter first name.',
            'last_name.required'                => 'Please enter last name.',
            'first_name.regex'                  => 'Please enter valid first name.',
            'last_name.regex'                   => 'Please enter valid last name.',
            'language.required'                 => 'The :attribute field is required',
            'video_conferencing_mode.required'  => 'The :attribute field is required',
            'shift.required'                    => 'The :attribute field is required',
            'video_link.required'               => 'The :attribute field is required',
            'years_of_experience.required'      => 'The :attribute field is required',
            'years_of_experience.user_about_me' => "The :attribute may not be greater than 5 characters.",
            'about.required'                    => 'The :attribute field is required',
        ];
    }
}
