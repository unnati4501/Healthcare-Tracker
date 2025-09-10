<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Request;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateChallengeImageLibRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return access()->allow('add-challenge-image');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $logoMax = config('zevolifesettings.fileSizeValidations.challenge_library.image', (2 * 1024));

        return [
            'target_type' => 'required',
            'image'       => [
                'required',
                'image',
                'mimes:jpg,jpeg,png',
                "max:{$logoMax}",
                Rule::dimensions()->minWidth(1280)->minHeight(640)->ratio(2 / 1),
            ],
        ];
    }

    /**
     * Custom error messages
     *
     * @return array
     */
    public function messages(): array
    {
        $messages              = [];
        $messages['image.max'] = 'The :attribute field may not be greater than 2MB.';
        $messages['image.dimensions'] = 'The uploaded image does not match the given dimension and ratio.';
        return $messages;
    }
}
