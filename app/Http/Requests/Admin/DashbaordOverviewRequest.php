<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class DashbaordOverviewRequest extends FormRequest
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
            'comapnyId'    => 'sometimes|nullable|numeric',
            'departmentId' => 'sometimes|nullable|numeric',
            'age1'         => 'sometimes|nullable|numeric',
            'age2'         => 'sometimes|nullable|numeric',
        ];
    }
}
