<?php

namespace App\Http\Requests\Admin;

use App\Models\Challenge;
use Illuminate\Foundation\Http\FormRequest;

class ExportChallengeDetailsRequest extends FormRequest
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
            'challengeId'   => 'required|integer|exists:' . Challenge::class . ',id',

        ];
    }
}