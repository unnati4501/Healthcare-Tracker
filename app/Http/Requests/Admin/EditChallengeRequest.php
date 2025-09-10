<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Request;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EditChallengeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $explodeRoute = explode('.', \Route::currentRouteName());
        $route        = $explodeRoute[1];
        return (access()->allow('create-challenge') && $route != 'interCompanyChallenges') || (access()->allow('create-inter-company-challenge') && $route == 'interCompanyChallenges');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $explodeRoute = explode('.', \Route::currentRouteName());
        $route        = $explodeRoute[1];
        $logoMax      = config('zevolifesettings.fileSizeValidations.challenge.logo', 2048);
        $rules        = array();

        if ($route == 'challenges') {
            $rules['name']             = 'required|min:2|max:50';
            $rules['logo']             = [
                'sometimes',
                'nullable',
                'image',
                'mimes:jpg,jpeg,png',
                "max:{$logoMax}",
                Rule::dimensions()->minWidth(1280)->minHeight(640)->ratio(2 / 1),
            ];
            $rules['info']             = 'required|max:500';
            $rules['badge']            = 'max:10';
            $rules['start_date']       = 'required|date';
            $rules['end_date']         = 'required_without:recursive|date';
            $rules['recursive_type']   = 'required_if:recursive,yes';
            $rules['recursive_count']  = 'required_if:recursive,yes|sometimes|nullable|integer|min:1|max:10';
            $rules['members_selected'] = 'required|min_members';
            $rules['members_selected.*']  = 'integer';
            $rules['locations']          = 'required_if:close,yes';
            $rules['department']         = 'required_if:close,yes';
        } elseif ($route == 'teamChallenges') {
            $rules['name']             = 'required|min:2|max:50';
            $rules['logo']             = [
                'sometimes',
                'nullable',
                'image',
                'mimes:jpg,jpeg,png',
                "max:{$logoMax}",
                Rule::dimensions()->minWidth(1280)->minHeight(640)->ratio(2 / 1),
            ];
            $rules['info']             = 'required|max:500';
            $rules['badge']            = 'max:10';
            $rules['start_date']       = 'required|date';
            $rules['end_date']         = 'required|date';
            $rules['members_selected'] = 'required|min_members';
            $rules['members_selected.*']  = 'integer';
        } elseif ($route == 'companyGoalChallenges') {
            $rules['name']       = 'required|min:2|max:50';
            $rules['logo']       = [
                'sometimes',
                'nullable',
                'image',
                'mimes:jpg,jpeg,png',
                "max:{$logoMax}",
                Rule::dimensions()->minWidth(1280)->minHeight(640)->ratio(2 / 1),
            ];
            $rules['info']       = 'required|max:500';
            $rules['badge']      = 'max:10';
            $rules['start_date'] = 'required|date';
            $rules['end_date']   = 'required|date';
            $rules['members_selected'] = 'required|min_members';
            $rules['members_selected.*']  = 'integer';
        } elseif ($route == 'interCompanyChallenges') {
            $rules['name'] = 'required|min:2|max:50';
            $rules['logo'] = [
                'sometimes',
                'nullable',
                'image',
                'mimes:jpg,jpeg,png',
                "max:{$logoMax}",
                Rule::dimensions()->minWidth(1280)->minHeight(640)->ratio(2 / 1),
            ];
            $rules['info']             = 'required|max:500';
            $rules['badge']            = 'max:10';
            $rules['start_date']       = 'required|date';
            $rules['end_date']         = 'required|date';
            $rules['members_selected'] = 'required|min_companies';
            $rules['members_selected.*']  = 'integer';
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
            'badge.max'                      => "Maximum 10 badges can be selected for the challenge",
            'members_selected.required'      => trans('labels.challenge.group_member_required'),
            'members_selected.min_members'   => trans('labels.challenge.group_member_min'),
            'target_units1.required_if'      => "The target units field is required when challenge category is Combined.",
            'target_units1.integer'          => "The target units must be an integer.",
            'target_units1.max'              => "The target units may not be greater than 100000000.",
            'members_selected.min_companies' => 'Please select teams from at least 2 companies.',
            'logo.max'                       => 'The :attribute may not be greater than 2MB.',
            'logo.dimensions'                => 'The uploaded image does not match the given dimension and ratio.',
            'locations.required_if'          => 'The locations field is required',
            'department.required_if'         => 'The department field is required',
        ];
    }
}
