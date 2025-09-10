<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Request;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EditMoodRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return access()->allow('update-moods');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $logoMax            = config('zevolifesettings.fileSizeValidations.personalChallenge.logo', 2048);
        $payload            = $this->input();
        $payload['routeId'] = $this->route('mood')->id;
        $rules              = array();

        $rules['logo'] = [
            'sometimes',
            'image',
            'mimes:jpg,jpeg,png',
            "max:{$logoMax}",
            Rule::dimensions()->minWidth(320)->minHeight(320)->ratio(1 / 1.0),
        ];
        $rules['title'] = ['required', 'regex:/(^[A-Za-z ]+$)+/', 'max:15',
            Rule::unique('moods')
                ->where(function ($query) use ($payload) {
                    return $query->where('title', @$payload['title'])
                        ->where('id', '!=', $payload['routeId']);
                })];

        return $rules;
    }

    public function attributes()
    {
        return [
            'logo'  => 'mood logo',
            'title' => 'mood name',
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
            'title.unique'    => 'The mood name already exists.',
            'logo.max'        => 'The mood logo may not be greater than 2 MB.',
            'logo.dimensions' => 'The uploaded image does not match the given dimension and ratio.',
        ];
    }
}
