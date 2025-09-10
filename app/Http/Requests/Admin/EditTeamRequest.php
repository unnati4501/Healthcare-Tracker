<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Request;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EditTeamRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return access()->allow('update-team');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $logoMax               = config('zevolifesettings.fileSizeValidations.team.logo', 2048);
        $rules                 = array();
        $rules['name']         = ['required', 'min:2', 'max:50'];
        $rules['company']      = 'required|integer';
        $rules['department']   = 'required|integer';
        $rules['teamlocation'] = 'required|integer';
        $rules['logo']         = [
            'sometimes',
            'nullable',
            'image',
            'mimes:jpg,jpeg,png',
            "max:{$logoMax}",
            Rule::dimensions()->minWidth(512)->minHeight(512)->ratio(1 / 1.0),
        ];

        $rules['name'] = ['required', 'min:2', 'max:50',
            Rule::unique('teams')
                ->where(function ($query) {
                    return $query
                        ->where('company_id', $this->team->company_id)
                        ->where('id', '!=', $this->team->id);
                })];

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
            'logo.max'        => 'The :attribute may not be greater than 2MB.',
            'logo.dimensions' => 'The uploaded image does not match the given dimension and ratio.',
        ];
    }
}
