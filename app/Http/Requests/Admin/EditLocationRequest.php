<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Request;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Company;

class EditLocationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return access()->allow('update-location');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules   = [
            'company'       => 'required|integer|exists:' . Company::class . ',id',
            'address_line1' => 'required|address|min:2|max:100',
            'address_line2' => 'sometimes|address|nullable|max:100',
            'county'        => 'required|integer',
            'country'       => 'required|integer',
            'timezone'      => 'required',
            'postal_code'   => 'required|alpha_num_spaces|max:10',
        ];

        $rules['name'] = ['required', 'min:2', 'max:100',
            Rule::unique('company_locations')
                ->where(function ($query) {
                    return $query
                        ->where('company_id', $this->location->company_id)
                        ->where('id', '!=', $this->location->id);
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
        return [];
    }
}
