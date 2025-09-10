<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Request;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class CloneFeedRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return access()->allow('update-story');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $user               = Auth::user();
        $role               = getUserRole($user);
        $companyData        = $user->company->first();
        $featuredImageMax   = config('zevolifesettings.fileSizeValidations.feed.featured_image', 2048);
        $audioMax           = config('zevolifesettings.fileSizeValidations.feed.audio', (100 * 1024));
        $audioBackgroundMax = config('zevolifesettings.fileSizeValidations.feed.audio_background', 2048);
        $portalHeader       = config('zevolifesettings.fileSizeValidations.feed.header_image', 2048);
        $videoMax           = config('zevolifesettings.fileSizeValidations.feed.video', (100 * 1024));

        $rules                   = array();
        $rules['name']           = "required|min:1|max:50";
        $rules['subtitle']       = "required|min:1|max:200";
        $rules['featured_image'] = [
            "sometimes",
            "nullable",
            "image",
            "mimes:jpg,jpeg,png,gif",
            "max:{$featuredImageMax}",
            Rule::dimensions()->minWidth(1280)->minHeight(640)->ratio(2 / 1),
        ];
        $rules['sub_category'] = 'required';
        $rules['health_coach'] = 'required';
        $rules['audio']        = "sometimes|nullable|mimetypes:audio/mpeg,audio/mp3,audio/m4a,audio/x-m4a|max:{$audioMax}";

        if ($role->group == 'zevo' || $role->group == 'company' || ($role->group == 'reseller' && $companyData->parent_id != null && $companyData->allow_app)) {
            $rules['audio_background'] = [
                "sometimes",
                "nullable",
                "image",
                "mimes:jpg,jpeg,png",
                "max:{$audioBackgroundMax}",
                Rule::dimensions()->minWidth(640)->minHeight(1280)->ratio(1 / 2),
            ];
        }
        if ($role->group == 'zevo' || ($role->group == 'reseller' && $companyData->allow_portal)) {
            $rules['audio_background_portal'] = [
                "sometimes",
                "nullable",
                "image",
                "mimes:jpg,jpeg,png",
                "max:{$audioBackgroundMax}",
                Rule::dimensions()->minWidth(1280)->minHeight(640)->ratio(2 / 1),
            ];
        }

        $rules['video']       = "sometimes|nullable|mimetypes:video/mp4|max:{$videoMax}";
        $rules['youtube']     = "required_if:feed_type,3|nullable|url|youtube_url";
        $rules['description'] = "sometimes|nullable";
        $rules['start_date']  = 'required|date';
        $rules['vimeo']       = "required_if:feed_type,5|nullable|url|vimeo_url";
        $rules['end_date']    = 'required|date|required_with:start_date';

        if ($role->group == 'zevo') {
            $rules['feed_company'] = 'required';
        }
        $rules['goal_tag'] = 'array|max:3';

        $rules['header_image'] = [
            "sometimes",
            "nullable",
            "image",
            "mimes:jpg,jpeg,png,gif",
            "max:{$portalHeader}",
            Rule::dimensions()->minWidth(800)->minHeight(800)->ratio(1 / 1.0),
        ];

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
            'featured_image.max'                  => 'The :attribute may not be greater than 2MB.',
            'audio.max'                           => 'The :attribute may not be greater than 100MB.',
            'audio_background.max'                => 'The :attribute may not be greater than 2MB.',
            'audio_background_portal.max'         => 'The :attribute may not be greater than 2MB.',
            'video.max'                           => 'The :attribute may not be greater than 100MB.',
            'audio.required_if'                   => 'The :attribute field is required',
            'audio_background.required_if'        => 'The :attribute field is required',
            'audio_background_portal.required_if' => 'The :attribute field is required',
            'video.required_if'                   => 'The :attribute field is required',
            'youtube.required_if'                 => 'The :attribute field is required',
            'description.required_if'             => 'The :attribute field is required',
            'feed_company.required'               => 'The :attribute is required',
            'health_coach.required'               => 'The author field is required.',
            'vimeo.required_if'                   => 'The :attribute field is required',
            'vimeo.vimeo_url'                     => 'The vimeo link is not valid url',
            'audio_background.dimensions'         => 'The uploaded image does not match the given dimension and ratio.',
            'audio_background_portal.dimensions'  => 'The uploaded image does not match the given dimension and ratio.',
            'featured_image.dimensions'           => 'The uploaded image does not match the given dimension and ratio.',
            'header_image.dimensions'             => 'The uploaded image does not match the given dimension and ratio.',
        ];
    }

    public function attributes(): array
    {
        return [
            'name'                    => 'story title',
            'subtitle'                => 'story subtitle',
            'sub_category'            => 'category',
            'youtube'                 => 'youtube link',
            'feed_company'            => 'company selection',
            'featured_image'          => 'featured',
            'vimeo'                   => 'Vimeo link',
            'audio_background'        => 'mobile audio background',
            'audio_background_portal' => 'portal audio background',
            'feed_type'               => 'story type',
        ];
    }
}
