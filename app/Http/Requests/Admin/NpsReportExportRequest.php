<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Request;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class NpsReportExportRequest extends FormRequest
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
            'email'      => 'nullable|email',
            'start_date' => 'nullable|date',
            'end_date'   => 'nullable|date',
        ];
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

    public function attributes(): array
    {
        return [
            'start_date' => 'From date',
            'end_date'  => 'To date'
        ];
    }
}
