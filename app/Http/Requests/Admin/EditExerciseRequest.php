<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Request;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EditExerciseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return access()->allow('update-exercise');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $excercise     = $this->route('excercise');
        $id            = $excercise->id;
        $logoMax       = config('zevolifesettings.fileSizeValidations.exercise.logo', 2048);
        $backgroundMax = config('zevolifesettings.fileSizeValidations.exercise.background', 2048);

        $rules                = array();
        $rules['name']        = ['required', 'unique:exercises,title,' . $id, 'alpha_spaces', 'min:2', 'max:40'];
        $rules['description'] = 'sometimes|nullable|max:200';
        $rules['type']        = 'required';
        $rules['logo']        = [
            'sometimes',
            'nullable',
            'image',
            'mimes:jpg,jpeg,png',
            "max:{$logoMax}",
            Rule::dimensions()->minWidth(320)->minHeight(320)->ratio(1 / 1.0),
        ];
        $rules['background'] = [
            'sometimes',
            'nullable',
            'image',
            'mimes:jpg,jpeg,png',
            "max:{$backgroundMax}",
            Rule::dimensions()->minWidth(2560)->minHeight(1280)->ratio(2 / 1),
        ];
        $rules['calories'] = 'required|integer|max:500';

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
            'logo.max'              => 'The :attribute may not be greater than 2MB.',
            'background.max'        => 'The :attribute may not be greater than 2MB.',
            'logo.dimensions'       => 'The uploaded image does not match the given dimension and ratio.',
            'background.dimensions' => 'The uploaded image does not match the given dimension and ratio.',
        ];
    }
}
