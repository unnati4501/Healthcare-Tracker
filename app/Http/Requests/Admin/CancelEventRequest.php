<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class CancelEventRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return access()->allow('cancel-event');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'cancel_reason' => 'required|introduction:250',
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
            'cancel_reason.introduction' => 'The :attribute field may not be greater than 250 characters.',
        ];
    }

    /**
     * Custom attributes name
     *
     * @return array
     */
    public function attributes()
    {
        return [
            'cancel_reason' => 'reason',
        ];
    }
}
