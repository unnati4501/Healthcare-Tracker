<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class CreateBoradcastMessageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return access()->allow('create-broadcast-message');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'instant_broadcast'  => 'sometimes',
            'title'              => 'required|max:30',
            'schedule_date_time' => 'required_unless:instant_broadcast,on',
            'message'            => 'required|max:600',
            'group_type'         => 'required',
            'group'              => 'required',
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
            'schedule_date_time.required_unless' => 'The schedule date time field is required when instant broadcast not checked.',
        ];
    }
}
