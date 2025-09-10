<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Request;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ManagePointsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return access()->allow('add-points');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules                     = array();
        $rules['log_date']         = 'required|date';
        $rules['members_selected'] = 'required|integer|exists:users,id';
        $rules['points']           = 'required|numeric|max:100|regex:/^-?[0-9]+(?:\.[0-9]{1,2})?$/';

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
            'members_selected.required' => trans('labels.challenge.group_member_required'),
        ];
    }
}
