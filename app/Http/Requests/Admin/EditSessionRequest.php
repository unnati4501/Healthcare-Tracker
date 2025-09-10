<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EditSessionRequest extends FormRequest
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
        return [
            'notes'             => 'sometimes|nullable|introduction:2500',
            'no_show'           => 'nullable',
            'reason'            => 'nullable',
            'email_message'     => 'required|sometimes|nullable|introduction:5000',
            'score'             => 'nullable',
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
            'notes.introduction'         => 'The :attribute field may not be greater than 1000 characters.',
            'email_message.introduction' => 'The :attribute field may not be greater than 5000 characters.',
        ];
    }

    public function attributes()
    {
        return [
            'notes'         => 'Notes',
            'email_message' => 'Email Body'
        ];
    }
}
