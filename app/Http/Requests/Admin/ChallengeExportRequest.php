<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Request;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Challenge;


class ChallengeExportRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return access()->allow('export-inter-company-challenge');
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
            'challengeId'   => 'required|integer|exists:' . Challenge::class . ',id',
            'start_date' => 'required|date|before_or_equal:end_date',
            'end_date'   => 'required|date|after_or_equal:start_date',
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
