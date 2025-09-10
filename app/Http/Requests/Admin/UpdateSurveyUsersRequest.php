<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSurveyUsersRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return access()->allow('survey-configuration');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'survey_for_all'   => 'sometimes',
            'members_selected' => 'sometimes|nullable|required_unless:survey_for_all,on',
        ];
    }

    /**
     * Custom error messges
     *
     * @return array
     */
    public function messages()
    {
        return [
            'members_selected.required_unless' => 'Plesae select at least one user to send survey.',
        ];
    }
}
