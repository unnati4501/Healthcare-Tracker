<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Request;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\models\Company;

class StoreCreditRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return access()->allow('manage-credits');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'user_name'          => ['required', 'regex:/(^([^0-9<>%$#@!*()_]*))+/', 'min:2', 'max:50'],
            'type'               => ['nullable'],
            'credits'            => ['required', 'integer', 'max:100'], 
            'notes'              => ['required', 'regex:/(^([^0-9<>%$#@!*()_]*))+/', 'max:500'],
            'available_credits'  => ['nullable'],
            'company_id'         => ['required', 'integer', 'exists:' . Company::class . ',id'],
        ];
    }

    /**
     * Custom error messages
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'user_name.required' => 'The Updated by field is required!',
            'user_name.max'      => 'The name may not be greater than 50 characters.',
            'user_name.regex'    => 'Please enter valid name',
            'credits.required'   => 'The Credit count field is required!',
            'credits.max'        => 'The credits may not be greater than 100',
            'credits.integer'    => 'Please enter a valid credit value',
            'notes.required'     => 'The Note field is required!',
            'notes.regex'        => 'Please enter valid Note',
            'notes.max'          => 'The note may not be greater than 500 characters.'
        ];
    }
}
