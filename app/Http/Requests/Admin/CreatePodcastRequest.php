<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Request;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\User;
use App\models\SubCategory;
use App\Models\CategoryTags;

class CreatePodcastRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return access()->allow('create-meditation-library');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $logoMax      = config('zevolifesettings.fileSizeValidations.podcasts.cover', 2048);
        $trackMax      = config('zevolifesettings.fileSizeValidations.podcasts.track', (100 * 1024));

        $rules                      = array();
        $rules['name']              = 'required|alpha_num_spaces|min:2|max:100';
        $rules['podcast_subcategory'] = 'required|integer|exists:sub_categories,id';
        $rules['duration']          = 'required|integer';
        $rules['health_coach']      = 'required|integer|exists:' . User::class . ',id';
        $rules['podcast_logo']  = [
            "required",
            "image",
            "mimes:jpg,jpeg,png",
            "max:{$logoMax}",
            Rule::dimensions()->minWidth(800)->minHeight(800)->ratio(1 / 1.0),
        ];
        $rules['track_audio']       = "required|mimetypes:audio/mpeg,audio/mp3,audio/m4a,audio/x-m4a|max:{$trackMax}";
        $rules['goal_tag']          = 'array|max:3';
        $rules['podcast_company']   = 'required';
        $rules['podcast_company.*'] = 'integer';
        $rules['tag']               = 'nullable|integer|exists:' . CategoryTags::class . ',id';
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
            'podcast_logo.max'                    => 'The :attribute may not be greater than 2MB.',
            'track_audio.max'                     => 'The :attribute may not be greater than 100MB.',
            'track_audio.required_if'             => 'The :attribute field is required.',
            'track_audio.mimetypes'               => 'The :attribute must be a file of type: MP3.',
            'duration.between'                    => 'The :attribute must be between 1 second to 1 hour.',
            'podcast_company.required'            => 'The :attribute is required',
        ];
    }

    public function attributes()
    {
        return [
            'name'                    => 'name',
            'duration'                => 'track duration',
            'podcast_subcategory'       => 'category',
            'track_audio'             => 'audio track file',
            'podcst_company'          => 'company selection',
            'health_coach'            => 'wellbeing specialist',
            'podcast_logo'            => 'logo'
        ];
    }
}
