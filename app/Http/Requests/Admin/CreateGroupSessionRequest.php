<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Request;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class CreateGroupSessionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return access()->allow('create-sessions');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $user    = Auth::user();
        $role    = getUserRole($user);

        if ($role->group == 'company') {
            return [
                'service'      => 'required',
                'sub_category' => 'required',
                'ws_user'      => 'required',
                'notes'        => 'sometimes|nullable',
                'add_users'    => 'required|min:1',
            ];
        } else {
            return [
                'service'      => 'required',
                'sub_category' => 'required',
                'company'      => 'required',
                'notes'        => 'sometimes|nullable',
                'add_users'    => 'required|min:1',
                'location'     => 'sometimes|nullable'
            ];
        }
    }

    /**
     * Custom error messages
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'add_users.required' => 'Minimum 1 user is required to make the booking',
            'ws_user.required'   => 'Please select Wellbeing Specialist',
        ];
    }
}
