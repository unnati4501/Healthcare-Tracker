<div class="card-inner">
    <div class="row">
        <div class="col-lg-6 col-xl-4">
            <div class="form-group">
                {{ Form::label('name', trans('feed.form.labels.title')) }}
                {{ Form::text('name', old('name', ($feedData->title ?? null)), ['class' => 'form-control', 'placeholder' => trans('feed.form.placeholder.enter_feed_name'), 'id' => 'name', 'autocomplete' => 'off' , 'disabled' => ($clone ? true : false)]) }}
            </div>
        </div>
        <div class="col-lg-6 col-xl-4">
            <div class="form-group">
            {{ Form::label('subtitle', trans('feed.form.labels.subtitle')) }}
            {{ Form::text('subtitle', old('subtitle', ($feedData->subtitle ?? null)), ['class' => 'form-control', 'placeholder' => trans('feed.form.placeholder.enter_feed_subtitle'), 'id' => 'subtitle', 'autocomplete' => 'off',  'disabled' => ($clone ? true : false)]) }}
            </div>
        </div>
        <div class="col-lg-6 col-xl-4">
            <div class="form-group">
            {{ Form::label('subcategories', trans('feed.form.labels.sub_category')) }}
            {{ Form::select('sub_category', $subcategories, old('category_id', ($feedData->sub_category_id ?? null)), ['class' => 'form-control select2', 'id'=>'sub_category', 'placeholder' => trans('feed.form.placeholder.select_category'), 'data-placeholder' => trans('feed.form.placeholder.select_category'),  'disabled' => ($clone ? true : false)]) }}
            </div>
        </div>
        @if(!$edit || !$isSA || ($edit && $isSA && $is_visible))
        <div class="col-lg-6 col-xl-4">
            <div class="form-group">
            {{ Form::label('health_coach', trans('feed.form.labels.author')) }}
            {{ Form::select('health_coach', $healthcoach, old('health_coach', ($feedData->creator_id ?? null)), ['class' => 'form-control select2','id'=>'health_coach','placeholder' => trans('feed.form.placeholder.select_author'), 'data-placeholder' => trans('feed.form.placeholder.select_author'),  'disabled' => ($clone ? true : false)]) }}
            </div>
        </div>
        @else
        <div class="col-lg-6 col-xl-4">
            <div class="form-group">
            {{ Form::label('health_coach', trans('feed.form.labels.author')) }}
            {{ Form::text(null, $healthcoach[$feedData->creator_id], ['class' => 'form-control', 'disabled' => 'disabled']) }}
            <input name="health_coach" type="hidden" value="{{ $feedData->creator_id }}"/>
            </div>
        </div>
        @endif
        <div class="col-lg-6 col-xl-4">
            <div class="form-group">
                {{ Form::label('', trans('feed.form.labels.featured_image')) }}
                <span class="font-16 qus-sign-tooltip" data-toggle="help-tooltip" title="{{ getHelpTooltipText('feed.featured_image') }}">
                    <i aria-hidden="true" class="far fa-info-circle text-primary">
                    </i>
                </span>
                <div class="custom-file custom-file-preview">
                    <label class="file-preview-img">
                        <img id="featuredpreviewImg" src="{{ ((!empty($feedData) && !empty($feedData->getFirstMediaUrl('featured_image'))) ? $feedData->getFirstMediaUrl('featured_image') : asset('assets/dist/img/boxed-bg.png')) }}" width="200"/>
                    </label>
                    {{ Form::file('featured_image', ['class' => 'custom-file-input form-control', 'id' => 'featured_image', 'data-width' => config('zevolifesettings.imageConversions.feed.featured_image.width'), 'data-height' => config('zevolifesettings.imageConversions.feed.featured_image.height'), 'data-ratio' => config('zevolifesettings.imageAspectRatio.feed.featured_image'), 'data-previewelement' => '#featuredpreviewImg',  'disabled' => ($clone ? true : false)]) }}
                    {{ Form::label('featured_image', ((!empty($feedData) && !empty($feedData->getFirstMediaUrl('featured_image'))) ? $feedData->getFirstMedia('featured_image')->name : trans('feed.form.placeholder.choose_file')), ['class' => 'custom-file-label']) }}
                </div>
            </div>
        </div>
        <div class="col-lg-6 col-xl-4">
            <div class="form-group">
            {{ Form::label('timezone', trans('feed.form.labels.timezone')) }}
            {{ Form::select('timezone', $timezoneArray, old('timezone', ($feedData->timezone ?? null)), ['class' => 'form-control select2', 'id'=>'timezone', 'placeholder' => trans('feed.form.placeholder.select_timezone'), 'data-placeholder' => trans('feed.form.placeholder.select_timezone'),  'disabled' => ($clone ? true : false)] ) }}
            <small>
                {{ trans('feed.message.timezone_field_message') }}
            </small>
            </div>
        </div>

        <div class="col-lg-6 col-xl-4">
                <div class="form-group">
            {{ Form::label('start_date', trans('feed.form.labels.start_date_time')) }}
            {{ Form::text('start_date', old('start_date', (!empty($feedData->start_date) ? date('Y-m-d H:i', strtotime($feedData->start_date)) : null)), ['class' => 'form-control', 'placeholder' => trans('feed.form.placeholder.enter_start_date_time'), 'id' => 'start_date', 'autocomplete' => 'off']) }}
            </div>
        </div>
        <div class="col-lg-6 col-xl-4">
                <div class="form-group">
            {{ Form::label('end_date', trans('feed.form.labels.end_date_time')) }}
            {{ Form::text('end_date', old('end_date', (!empty($feedData->end_date) ? date('Y-m-d H:i', strtotime($feedData->end_date)) : null)), ['class' => 'form-control', 'placeholder' => trans('feed.form.placeholder.enter_end_date_time'), 'id' => 'end_date', 'autocomplete' => 'off']) }}
            </div>
        </div>
        @permission('manage-goal-tags')
        <div class="col-lg-6 col-xl-4">
                <div class="form-group">
            {{ Form::label('goal_tag', trans('feed.form.labels.goal_tag')) }}
            {{ Form::select('goal_tag[]',$goalTags, ($goal_tags ?? null), ['class' => 'form-control select2','id'=>'goal_tag',"style"=>"width: 100%;", 'multiple'=>true, 'autocomplete' => 'off','data-placeholder' => trans('feed.form.placeholder.select_goal_tags'),  'disabled' => ($clone ? true : false)] ) }}
            </div>
        </div>
        @endauth
        <div class="col-lg-6 col-xl-4">
            <div class="form-group">
            {{ Form::label('feed_type', trans('feed.form.labels.feed_type')) }}
            {{ Form::select('feed_type', $feed_types, old('feed_type', ($feedData->type ?? null)), ['class' => 'form-control select2', 'id'=>'feed_type', 'placeholder' => trans('feed.form.placeholder.select_feed_type'), 'data-placeholder' => trans('feed.form.placeholder.select_feed_type'), 'disabled' => ($edit || $clone)]) }}
            </div>
        </div>
        @if($roleGroup == 'zevo')
        <div class="col-lg-6 col-xl-4">
            <div class="form-group">
                {{ Form::label('tag', trans('feed.form.labels.tag')) }}
                {{ Form::select('tag', $tags, ($feedData->tag_id ?? null), ['class' => 'form-control select2', 'id' => 'tag', 'placeholder' => trans('feed.form.placeholder.tag'), 'data-placeholder' => trans('feed.form.placeholder.tag'), 'data-allow-clear' => 'true']) }}
            </div>
        </div>
        @endif
        <div class="col-lg-4 col-md-6" id="content_wrapper_header">
            <div class="form-group">
                {{ Form::label('Header Image', trans('feed.form.labels.header_image')) }}
                <span class="font-16 qus-sign-tooltip" data-toggle="help-tooltip" title="{{ getHelpTooltipText('feed.header_image') }}">
                    <i aria-hidden="true" class="far fa-info-circle text-primary">
                    </i>
                </span>
                <div class="custom-file custom-file-preview">
                    <label class="file-preview-img" for="header_image_preview" style="display: flex;">
                        <img id="header_image_preview" src="{{ ((!empty($feedData) && !empty($feedData->getFirstMediaUrl('header_image'))) ? $feedData->getFirstMediaUrl('header_image') : asset('assets/dist/img/boxed-bg.png')) }}" width="200"/>
                    </label>
                    {{ Form::file('header_image', ['class' => 'custom-file-input form-control', 'id' => 'header_image', 'data-width' => config('zevolifesettings.imageConversions.feed.header_image.width'), 'data-height' => config('zevolifesettings.imageConversions.feed.header_image.height'), 'data-ratio' => config('zevolifesettings.imageAspectRatio.feed.header_image'), 'autocomplete' => 'off','title'=>'', 'data-previewelement' => '#header_image_preview', 'disabled' => ($clone ? true : false)]) }}
                    {{ Form::label('header_image', ((!empty($feedData) && !empty($feedData->getFirstMediaUrl('header_image'))) ? $feedData->getFirstMedia('header_image')->name : trans('feed.form.placeholder.choose_file')), ["class" => "custom-file-label"]) }}
                </div>
            </div>
        </div>
    </div>
    <div id="audio_wrapper" class="type_wrappers"  style="display: none;">
        <div class="row">
            <div class="col-lg-6 col-xl-4">
                <div class="form-group">
                    {{ Form::label('audio', trans('feed.form.labels.audio')) }}
                    <span class="font-16 qus-sign-tooltip" data-toggle="help-tooltip" title="{{ getSizeHelpTooltipText('feed.audio') }}">
                        <i aria-hidden="true" class="far fa-info-circle text-primary">
                        </i>
                    </span>
                    <div class="custom-file">
                        {{ Form::file('audio', ['class' => 'custom-file-input form-control', 'id' => 'audio', 'disabled' => ($clone ? true : false)]) }}
                        {{ Form::label('audio', ((!empty($feedData) && !empty($feedData->getFirstMediaUrl('audio'))) ? $feedData->getFirstMedia('audio')->name : trans('feed.form.placeholder.choose_file')), ["class" => "custom-file-label"]) }}
                    </div>
                </div>
            </div>
            @if($appBackgroundImage)
            <div class="col-lg-6 col-xl-4">
                <div class="form-group">
                    {{ Form::label('audio_background', trans('feed.form.labels.audio_background')) }}
                    <span class="font-16 qus-sign-tooltip" data-toggle="help-tooltip" title="{{ getHelpTooltipText('feed.audio_background') }}">
                        <i aria-hidden="true" class="far fa-info-circle text-primary">
                        </i>
                    </span>
                    <div class="custom-file custom-file-preview">
                        <label class="file-preview-img" for="audio_background_preview" style="display: flex;">
                            <img id="audio_background_preview" src="{{ ((!empty($feedData) && !empty($feedData->getFirstMediaUrl('audio_background'))) ? $feedData->getFirstMediaUrl('audio_background') : asset('assets/dist/img/boxed-bg.png')) }}" width="200"/>
                        </label>
                        {{ Form::file('audio_background', ['class' => 'custom-file-input form-control', 'id' => 'audio_background', 'data-width' => config('zevolifesettings.imageConversions.feed.audio_background.width'), 'data-height' => config('zevolifesettings.imageConversions.feed.audio_background.height'), 'data-ratio' => config('zevolifesettings.imageAspectRatio.feed.audio_background'), 'autocomplete' => 'off','title'=>'', 'data-previewelement' => '#audio_background_preview', 'disabled' => ($clone ? true : false)]) }}
                        {{ Form::label('audio_background', ((!empty($feedData) && !empty($feedData->getFirstMediaUrl('audio_background'))) ? $feedData->getFirstMedia('audio_background')->name : trans('feed.form.placeholder.choose_file')), ["class" => "custom-file-label"]) }}
                    </div>
                </div>
            </div>
            @endif
            @if($portalBackgroundImage)
            <div class="col-lg-6 col-xl-4">
                <div class="form-group">
                    {{ Form::label('audio_background_portal', trans('feed.form.labels.audio_background_portal')) }}
                    <span class="font-16 qus-sign-tooltip" data-toggle="help-tooltip" title="{{ getHelpTooltipText('feed.audio_background_portal') }}">
                        <i aria-hidden="true" class="far fa-info-circle text-primary">
                        </i>
                    </span>
                    <div class="custom-file custom-file-preview">
                        <label class="file-preview-img" for="audio_background_portal_preview" style="display: flex;">
                            <img id="audio_background_portal_preview" src="{{ ((!empty($feedData) && !empty($feedData->getFirstMediaUrl('audio_background_portal'))) ? $feedData->getFirstMediaUrl('audio_background_portal') : asset('assets/dist/img/boxed-bg.png')) }}" width="200"/>
                        </label>
                        {{ Form::file('audio_background_portal', ['class' => 'custom-file-input form-control', 'id' => 'audio_background_portal', 'data-width' => config('zevolifesettings.imageConversions.feed.audio_background_portal.width'), 'data-height' => config('zevolifesettings.imageConversions.feed.audio_background_portal.height'), 'data-ratio' => config('zevolifesettings.imageAspectRatio.feed.audio_background_portal'), 'autocomplete' => 'off','title'=>'', 'data-previewelement' => '#audio_background_portal_preview', 'disabled' => ($clone ? true : false)]) }}
                        {{ Form::label('audio_background_portal', ((!empty($feedData) && !empty($feedData->getFirstMediaUrl('audio_background_portal'))) ? $feedData->getFirstMedia('audio_background_portal')->name : trans('feed.form.placeholder.choose_file')), ["class" => "custom-file-label"]) }}
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
    <div id="video_wrapper" class="type_wrappers" style="display: none;">
        <div class="row">
            <div class="col-lg-12 col-xl-8">
                <div class="form-group">
                    {{ Form::label('video', trans('feed.form.labels.video')) }}
                    <span class="font-16 qus-sign-tooltip" data-toggle="help-tooltip" title="{{ getSizeHelpTooltipText('feed.video') }}">
                        <i aria-hidden="true" class="far fa-info-circle text-primary">
                        </i>
                    </span>
                    <div class="custom-file">
                        {{ Form::file('video', ['class' => 'custom-file-input form-control', 'id' => 'video', 'disabled' => ($clone ? true : false)]) }}
                        {{ Form::label('video', ((!empty($feedData) && !empty($feedData->getFirstMediaUrl('video'))) ? $feedData->getFirstMedia('video')->name : trans('feed.form.placeholder.choose_file')), ["class" => "custom-file-label"]) }}
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div id="youtube_wrapper" class="type_wrappers" style="display: none;">
        <div class="row">
             <div class="col-lg-12 col-xl-8">
                <div class="form-group">
                    {{ Form::label('youtube', trans('feed.form.labels.youtube')) }}
                    <span class="font-16 qus-sign-tooltip" data-placement="top" data-toggle="help-tooltip" title="{{ config('zevolifesettings.youtube_hint_message.message') }}">
                        <i aria-hidden="true" class="far fa-info-circle text-primary">
                        </i>
                    </span>
                    {{ Form::text('youtube', old('youtube', ((!empty($feedData) && !empty($feedData->getFirstMedia('youtube'))) ? config('zevolifesettings.youtubeappurl').$feedData->getFirstMedia('youtube')->getCustomProperty('ytid') : null)), ['class' => 'form-control', 'placeholder' => config('zevolifesettings.youtube_hint_message.placeholder'), 'id' => 'youtube','disabled' => ($clone ? true : false)]) }}
                </div>
            </div>
        </div>
    </div>
    <div id="content_wrapper" class="type_wrappers" style="display: none;">
        <div class="row">

            <div class="col-lg-12 col-xl-12">
                <div class="form-group">
                    {{ Form::label('description', trans('feed.form.labels.description')) }}
                    {{ Form::textarea('description', old('description', (isset($feedData->description) ? htmlspecialchars_decode($feedData->description) : null)), ['class' => 'form-control article-ckeditor', 'id' => 'description', 'data-errplaceholder' => '#description-error', 'data-formid' => (($edit) ? "#feedEdit" : "#feedAdd"), 'data-upload-path' => route('admin.ckeditor-upload.feed-description', ['_token' => csrf_token() ]), 'disabled' => ($clone ? true : false)]) }}
                    <div>
                        <small>
                            {{ trans('feed.message.fullscreen_mode_for_description') }}

                            <i class="fas fa-arrows-alt" style="transform: rotate(45deg);">
                            </i>
                            {{ trans('feed.message.from_toolbar') }}
                        </small>
                    </div>
                    <span id="description-error">
                    </span>
                </div>
            </div>
        </div>
    </div>
    <div id="vimeo_wrapper" class="type_wrappers" style="display: none;">
        <div class="row">
            <div class="col-lg-12 col-xl-8">
                <div class="form-group">
            {{ Form::label('vimeo', trans('feed.form.labels.vimeo')) }}
            <span class="font-16 qus-sign-tooltip" data-placement="top" data-toggle="help-tooltip" title="{{ config('zevolifesettings.vimeo_hint_message.message') }}">
                <i aria-hidden="true" class="far fa-info-circle text-primary">
                </i>
            </span>
            {{ Form::text('vimeo', old('vimeo', (!empty($feedData) && $feedData->type == 5 && !empty($feedData->getFirstMedia('vimeo')) ? config('zevolifesettings.vimeoappurl') . $feedData->getFirstMedia('vimeo')->getCustomProperty('vmid') : '')), ['class' => 'form-control', 'placeholder' => config('zevolifesettings.vimeo_hint_message.placeholder'), 'id' => 'vimeo', 'autocomplete' => 'off', 'disabled' => ($clone ? true : false)]) }}
            </div>
            </div>
        </div>
    </div>
</div>
@if($isSA)
<div class="card-inner">
    <h3 class="card-inner-title">{{ trans('feed.form.labels.company_visibility') }}</h3>
    <div class="row">
        <div class="col-lg-12">
            <div class="form-group mb-0">
                <div id="setPermissionList" class="tree-multiselect-box">
                    <select id="feed_company" name="feed_company[]" multiple="multiple" class="form-control">
                        @foreach($company as $rolekey => $rolevalue)
                            @foreach($rolevalue['companies'] as $key => $value)
                                @foreach($value['location'] as $locationKey => $locationValue)
                                    @foreach($locationValue['department'] as $departmentKey => $departmentValue)
                                        @foreach($departmentValue['team'] as $teamKey => $teamValue)
                                            <option value="{{ $teamValue['id'] }}" data-section="{{ $rolevalue['roleType'] }}/{{$value['companyName']}}/{{$locationValue['locationName']}}/{{$departmentValue['departmentName']}}" {{ (!empty($feed_companys) && in_array($teamValue['id'], $feed_companys))? 'selected' : ''   }} >{{ $teamValue['name'] }}</option>
                                        @endforeach
                                    @endforeach
                                @endforeach
                            @endforeach
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>
</div>
@endif
