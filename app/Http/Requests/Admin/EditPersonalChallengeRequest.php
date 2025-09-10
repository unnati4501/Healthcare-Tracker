<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Request;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EditPersonalChallengeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return access()->allow('update-personal-challenge');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $logoMax = config('zevolifesettings.fileSizeValidations.personalChallenge.logo', 2048);
        $rules   = array();
        $payload = $this->input();

        $rules['logo']        = [
            'sometimes',
            'image',
            'mimes:jpg,jpeg,png',
            "max:{$logoMax}",
            Rule::dimensions()->minWidth(1280)->minHeight(640)->ratio(2 / 1),
        ];
        $rules['name']        = 'required|max:100';
        $rules['description'] = 'required|max:500';
        if (!empty($payload['type']) && $payload['type'] == 'streak') {
            $rules['task'] = 'required|max:50';
        }

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
            'logo.dimensions' => 'The uploaded image does not match the given dimension and ratio.',
        ];
    }

    public function attributes()
    {
        return [
            'logo'        => 'Logo',
            'name'        => 'Challenge name',
            'description' => 'Description',
        ];
    }
}
