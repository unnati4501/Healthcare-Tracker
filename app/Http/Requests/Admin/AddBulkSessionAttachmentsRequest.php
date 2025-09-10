<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Request;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AddBulkSessionAttachmentsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $attachmentMax = config('zevolifesettings.fileSizeValidations.session.attachment', (5 * 1024));

        return [
            'attachments'    => 'required',
            'attachments.*'  => [
                "mimes:jpg,jpeg,png,pdf,doc,docx,txt",
                "max:{$attachmentMax}",
                "filecount:attachments,3",
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
        $messages['attachments.mimes'] = 'The :attribute field must be a file of type: jpg, jpeg, png, pdf.';
        $messages['attachments.max']   = 'The :attribute field may not be greater than 5MB.';
        return $messages;
    }
}
