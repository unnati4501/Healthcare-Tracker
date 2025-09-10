<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Request;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class EditCompanyPlanRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return access()->allow('update-company-plan');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $cpPlanId = Request()->route('cpPlan')->id;
        $rules['companyplan'] = "required|alpha_num_spaces|unique:cp_plan,name,".$cpPlanId."|min:1|max:30";
        $rules['description'] = "sometimes|nullable|max:200";
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
            'companyplan.required'         => ':attribute field is required',
            'companyplan.alpha_num_spaces' => 'The company plan may only contain letters, numbers and spaces.',
            'companyplan.max'              => ':attribute may not be greater than 30 character.',
            'companyplan.unique'           => 'The company plan has already been taken.',
            'description.max'              => ':attribute may not be greater than 200 character.',
        ];
    }

    public function attributes(): array
    {
        return [
            'companyplan' => 'Company Plan',
            'description' => 'Description',
        ];
    }
}
