<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Request;
use App\Models\Company;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EditDepartmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return access()->allow('update-department');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $payload    = $this->input();
        $user       = auth()->user();
        $role       = getUserRole($user);
        $department = $this->route('department');
        $company    = Company::select('companies.id', 'companies.auto_team_creation')->find(@$payload['company_id']);

        $rules = [
            'name'       => ['required', 'min:2', 'max:50',
                Rule::unique('departments', 'name')->where('company_id', @$payload['company_id'])->ignore($department),
            ],
            'location'   => 'required',
            'company_id' => 'required',
        ];

        if ($role->group == 'company' && (is_null($company) || ($company && $company->auto_team_creation))) {
            $rules['employee_count']    = 'sometimes|nullable|min:1|max:10000';
            $rules["naming_convention"] = "sometimes|nullable|min:1|max:30";
        }

        return $rules;
    }

    /**
     * @return array
     */
    public function attributes(): array
    {
        return [
            'name'     => 'department name',
            'location' => 'company location',
        ];
    }
}
