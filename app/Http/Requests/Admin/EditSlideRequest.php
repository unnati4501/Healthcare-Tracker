<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Request;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EditSlideRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return access()->allow('update-onboarding');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $slideImageMax              = config('zevolifesettings.fileSizeValidations.app_slide.slideImage', 2048);
        $rules                      = array();
        $rules['content']           = 'required|slidedescription';
        $rules['portal_content']    = 'required_if:type,eap|slidedescription';
        $rules['slideImage']        = ['sometimes', 'nullable', 'image', 'mimes:jpg,jpeg,png', "max:{$slideImageMax}"];
        $rules['portalSlideImage']  = ['sometimes', 'nullable', 'image', 'mimes:jpg,jpeg,png', "max:{$slideImageMax}"];

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
            'content.slidedescription'          => "Max 500 characters are allowed.",
            'portal_content.slidedescription'   => "Max 500 characters are allowed.",
            'portal_content.required_if'        => "The portal text is required.",
            'slideImage.max'                    => 'The :attribute may not be greater than 2MB.',
        ];
    }
}
