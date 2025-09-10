<?php
declare (strict_types = 1);

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class RegisterRequest
 *
 * @package App\Http\Requests\Auth
 */
class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'first_name'    => 'required|min:2|max:20|alpha_spaces',
            'last_name'     => 'required|min:2|max:20|alpha_spaces',
            'email'         => 'required|email|max:255|unique:users,email',
            'password'      => 'required|min:8',
            'team_code'     => 'required|numeric|exists:department_teams,code',
            'timezone'      => 'sometimes|required_with:timezone_type|string',
            'timezone_type' => 'sometimes|required_with:timezone|in:1,2,3',
            'contact_email' => "nullable|email|max:255",
            'gender'        => 'sometimes|required|string|in:m,f',
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
            'first_name.required'        => \trans('client/auth.register.form.fields.first_name.errors.required'),
            'first_name.first_last_name' => \trans('client/auth.register.form.fields.first_name.errors.first_last_name'),
            'last_name.required'         => \trans('client/auth.register.form.fields.last_name.errors.required'),
            'last_name.first_last_name'  => \trans('client/auth.register.form.fields.last_name.errors.first_last_name'),
            'email.required'             => \trans('client/auth.register.form.fields.email.errors.required'),
            'email.email'                => \trans('client/auth.register.form.fields.email.errors.email'),
            'email.unique'               => \trans('client/auth.register.form.fields.email.errors.unique'),
            'team_code.required'         => \trans('client/auth.register.form.fields.team_code.errors.required'),
            'team_code.alpha_num'        => \trans('client/auth.register.form.fields.team_code.errors.alpha_num'),
            'team_code.exists'           => \trans('client/auth.register.form.fields.team_code.errors.exists'),
            'password.required'          => \trans('client/auth.register.form.fields.password.errors.required'),
            'password.confirmed'         => \trans('client/auth.register.form.fields.password.errors.confirmed'),
            'contact_email.email'        => \trans('client/auth.register.form.fields.contact_email.errors.email'),
            'contact_email.unique'       => \trans('client/auth.register.form.fields.contact_email.errors.unique'),
            'locale_code.required'       => \trans('client/profile.form.fields.locale_code.errors.required'),
        ];
    }
}
