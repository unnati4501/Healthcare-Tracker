<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Request;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateChallengeMapRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return access()->allow('add-challenge-map');
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
            'name'            => ['required', 'regex:/(^([^0-9<>%$#@!*()_]*))+/', 'min:1', 'max:50'],
            'description'     => 'required|min:1|max:500',
            'total_locations' => 'required',
            'image'           => [
                'required',
                'image',
                'mimes:jpg,jpeg,png',
                "max:{$logoMax}",
                Rule::dimensions()->minWidth(1280)->minHeight(640)->ratio(2 / 1),
            ],
            'members_selected'    => 'required',
            'members_selected.*'  => 'integer'
        ];
    }

    /**
     * Custom error messages
     *
     * @return array
     */
    public function messages(): array
    {
        $messages                             = [];
        $messages['image.max']                = 'The :attribute field may not be greater than 2MB.';
        $messages['image.dimensions']         = 'The uploaded image does not match the given dimension and ratio.';
        $messages['total_locations.required'] = 'You need to select atleast one location from the map.';
        $messages['name.required'] = 'Map name should include characters and space only';
        $messages['name.regex'] = 'Map name should include characters and space only';
        return $messages;
    }
}
