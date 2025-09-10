<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Request;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EditLabelStringRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return access()->allow('label-setting');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules   = [];

        $defaultLabelString = config('zevolifesettings.company_label_string', []);
        foreach ($defaultLabelString as $groupKey => $groups) {
            foreach ($groups as $labelKey => $labelValue) {
                if (isset($labelValue['type']) && $labelValue['type'] == 'logo') {
                    $max                              = config("zevolifesettings.fileSizeValidations.label_settings.location_logo.{$labelKey}", 2048);
                    $rules["{$groupKey}.{$labelKey}"] = ['sometimes', 'nullable', 'image', 'mimes:png', "max:{$max}"];
                } else {
                    $rules["{$groupKey}.{$labelKey}"] = ['sometimes', 'nullable', 'regex:/(^[A-Za-z ]+$)+/', "max:{$labelValue['length']}"];
                }
            }
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
        $message            = [];
        $defaultLabelString = config('zevolifesettings.company_label_string', []);
        foreach ($defaultLabelString as $groupKey => $groups) {
            foreach ($groups as $labelKey => $labelValue) {
                $message["{$groupKey}.{$labelKey}.regex"] = "The label may only contain letters and spaces.";
                $message["{$groupKey}.{$labelKey}.max"]   = "The label may not be greater than {$labelValue['length']} characters.";
            }
        }
        return $message;
    }
}
