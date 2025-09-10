<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTeamAssignmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return access()->allow('team-assignment');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules                    = array();
        $rules['fromteammembers'] = 'required_without:toteammembers|string|nullable';
        $rules['toteammembers']   = 'required_without:fromteammembers|string|nullable';
        $rules['fromdepartment']  = 'required|numeric';
        $rules['todepartment']    = 'required|numeric';
        $rules['fromteam']        = 'required|numeric';
        $rules['toteam']          = 'required|numeric';
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
            'fromteammembers.required_without' => "Please select members",
            'toteammembers.required_without'   => "Please select members",
            'fromteammembers.string'           => "Please select valid members",
            'toteammembers.string'             => "Please select valid members",
            'fromdepartment.required'          => "The department field is required",
            'fromdepartment.numeric'           => "Please select valid values for department field",
            'fromteam.required'                => "The team field is required",
            'fromteam.numeric'                 => "Please select valid values for team field",
            'todepartment.required'            => "The department field is required",
            'todepartment.numeric'             => "Please select valid values for department field",
            'toteam.required'                  => "The team field is required",
            'toteam.numeric'                   => "Please select valid values for team field",
        ];
    }
}
