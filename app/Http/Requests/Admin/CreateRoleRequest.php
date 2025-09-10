<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Request;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateRoleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return access()->allow('create-role');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules                   = array();
        $rules['group']          = 'required';
        $rules['name']           = ['required', 'regex:/(^[A-Za-z ]+$)+/', 'min:2', 'max:50'];
        $rules['description']    = 'sometimes|nullable|max:200';
        $rules['set_privileges'] = 'required';

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
            'group.required'          => trans('labels.role.validation.group_required'),
            'name.required'           => trans('labels.role.validation.name_required'),
            'name.regex'              => trans('labels.role.validation.name_regex'),
            'name.unique'             => trans('labels.role.validation.name_unique'),
            'set_privileges.required' => trans('labels.role.validation.set_privileges_required'),
        ];
    }
}
