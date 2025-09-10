<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class DashboardTypewiseChallengeRequest extends FormRequest
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
            'type'         => 'required|string',
            'comapnyId'    => 'sometimes|nullable|numeric',
            'departmentId' => 'sometimes|nullable|numeric',
            'age1'         => 'sometimes|nullable|numeric',
            'age2'         => 'sometimes|nullable|numeric',
        ];
    }
}
