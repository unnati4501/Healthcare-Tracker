<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Request;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreatePersonalChallengeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return access()->allow('create-personal-challenge');
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

        $rules['logo'] = [
            'required',
            'image',
            'mimes:jpg,jpeg,png',
            "max:{$logoMax}",
            Rule::dimensions()->minWidth(1280)->minHeight(640)->ratio(2 / 1),
        ];
        $rules['name']          = 'required|max:100';
        $rules['description']   = 'required|max:500';
        $rules['duration']      = 'required|integer|max:365';
        $rules['challengetype'] = 'required';
        $rules['type']          = 'required';
        $rules['task']          = 'required_if:type,streak|max:50';
        $rules['target_value']  = 'required_if:challengetype,challenge|nullable|integer|max:100000000';

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
            'logo.dimensions'          => 'The uploaded image does not match the given dimension and ratio.',
            'target_value.required_if' => 'The :attribute field is required',
            'target_value.max'         => "The :attribute may not be greater than 100000000",
        ];
    }

    public function attributes()
    {
        return [
            'logo'         => 'Logo',
            'name'         => 'Challenge name',
            'description'  => 'Description',
            'duration'     => 'Duration',
            'task'         => 'Task',
            'target_value' => 'target value',
        ];
    }
}
