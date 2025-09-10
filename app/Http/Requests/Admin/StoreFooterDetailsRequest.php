<?php

namespace App\Http\Requests\Admin;

use App\Models\Company;
use App\Http\Requests\Request;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreFooterDetailsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return access()->allow('portal-footer');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $brandingLogoMax = config('zevolifesettings.fileSizeValidations.company.portal_footer_text', 2048);
        $footerImage = function () {
            $companyId      = $this->route('company')->id;
            $company        = Company::find($companyId);
            if(!empty($company->parent_id)){
                $parentCompany    = Company::find($company->parent_id);
            }
    
            if (!empty($company) && !empty($company->getFirstMedia('portal_footer_logo'))){
                return $company->portal_footer_logo_name;
            } else if (!empty($parentCompany) && !empty($parentCompany->getFirstMedia('portal_footer_logo'))){
                return $parentCompany->portal_footer_logo_name;
            } else {
                return "";
            }
        };

        $footerImageRules = [
            empty($footerImage()) ? "required" : "nullable",
            "image",
            "mimes:jpg,jpeg,png",
            "max:{$brandingLogoMax}",
            Rule::dimensions()->minWidth(180)->minHeight(60)->ratio(3 / 1),
        ];
        return [
            'footer_text'               => ['required', 'regex:/(^([^\'\=<>^#@"]*))+/', 'min:2', 'max:200'],
            'portal_footer_logo'        => $footerImageRules,
            'header1'                   => 'max:50',
            'header2'                   => 'max:50',
            'header3'                   => 'max:50',
            'col1key.*'                 => 'max:50|required_with:col1value.*',
            'col2key.*'                 => 'max:50|required_with:col2value.*',
            'col3key.*'                 => 'max:50|required_with:col3value.*',
            'col1value.*'               => 'max:500|required_with:col1key.*',
            'col2value.*'               => 'max:500|required_with:col2key.*',
            'col3value.*'               => 'max:500|required_with:col3key.*',
            'portal_footer_header_text' => 'sometimes|nullable',
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
            'header1.max'               => 'The header may not be greater than 50 characters.',
            'header2.max'               => 'The header may not be greater than 50 characters.',
            'header3.max'               => 'The header may not be greater than 50 characters.',
            'col1key.*.max'             => 'The key may not be greater than 50 characters.',
            'col2key.*.max'             => 'The key may not be greater than 50 characters.',
            'col3key.*.max'             => 'The key may not be greater than 50 characters.',
            'col1value.*.max'           => 'The value may not be greater than 500 characters.',
            'col2value.*.max'           => 'The value may not be greater than 500 characters.',
            'col3value.*.max'           => 'The value may not be greater than 500 characters.',
            'col1value.*.url'           => 'The value must be a valid url.',
            'col2value.*.url'           => 'The value must be a valid url.',
            'col3value.*.url'           => 'The value must be a valid url.',
            'col1key.*.required_with'   => 'The Column 1 Key field is required when Value is present.',
            'col2key.*.required_with'   => 'The Column 2 Key field is required when Value is present.',
            'col3key.*.required_with'   => 'The Column 3 Key field is required when Value is present.',
            'col1value.*.required_with' => 'The Column 1 Value field is required when Key is present.',
            'col2value.*.required_with' => 'The Column 2 Value field is required when Key is present.',
            'col3value.*.required_with' => 'The Column 3 Value field is required when Key is present.',
        ];
    }
}
