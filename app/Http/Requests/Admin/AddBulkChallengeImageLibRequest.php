<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Request;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AddBulkChallengeImageLibRequest extends FormRequest
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
            'upload_target_type' => 'required',
            'images'             => 'required',
            'images.*'           => [
                "image",
                "mimes:jpg,jpeg,png",
                "max:{$logoMax}",
                "filecount:images,20",
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
        $messages                      = [];
        $messages['images.image']      = 'The :attribute field must be an image.';
        $messages['images.mimes']      = 'The :attribute feild must be a file of type: jpg, jpeg, png.';
        $messages['images.max']        = 'The :attribute field may not be greater than 2MB.';
        $messages['images.dimensions'] = 'The uploaded image does not match the given dimension and ratio.';
        return $messages;
    }

    public function attributes(): array
    {
        return [
            'upload_target_type' => 'target type',
        ];
    }
}
