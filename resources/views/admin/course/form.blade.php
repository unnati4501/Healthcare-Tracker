<div class="card-inner">
    <div class="row justify-content-center justify-content-md-start">
        <div class="col-xxl-2 col-lg-3 col-md-4 basic-file-upload order-md-2">
            {{ Form::label('logo', trans('masterclass.form.labels.logo')) }}
            <span class="font-16 qus-sign-tooltip" data-toggle="help-tooltip" title="{{ getHelpTooltipText('course.logo') }}">
                <i aria-hidden="true" class="far fa-info-circle text-primary">
                </i>
            </span>
            <div class="edit-profile-wrapper edit-profile-small form-control h-auto border-0 p-0">
                <div class="profile-image user-img edit-photo">
                    <img id="previewImg" src="{{ ((!empty($record->logo)) ? $record->logo : asset('assets/dist/img/placeholder-img.png')) }}"/>
                </div>
                <div class="edit-profile-avtar edit-profile-small">
                    {{ Form::file('logo', ['class' => 'edit-avatar', 'id' => 'logo', 'data-previewelement' => '#previewImg', 'data-width' => config('zevolifesettings.imageConversions.course.logo.width'), 'data-height' => config('zevolifesettings.imageConversions.course.logo.height'), 'data-ratio' => config('zevolifesettings.imageAspectRatio.course.logo'), 'title' => ' '])}}
                    <u>
                        {{ trans('buttons.general.browse') }}
                    </u>
                </div>
            </div>
        </div>
        <div class="col-xxl-10 col-lg-9 col-md-8 col-12 order-1">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        {{ Form::label('category', trans('masterclass.form.labels.category')) }}
                        {{ Form::select('sub_category', $subcategories, old('sub_category', ($record->sub_category_id ?? null)), ['class' => 'form-control select2', 'id' => 'sub_category', 'placeholder' => trans('masterclass.form.placeholder.category'), 'data-placeholder' => trans('masterclass.form.placeholder.category'), 'data-allow-clear' => 'true']) }}
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        {{ Form::label('title', trans('masterclass.form.labels.title')) }}
                        {{ Form::text('title', old('title', ($record->title ?? null)), ['class' => 'form-control', 'placeholder' => trans('masterclass.form.placeholder.title'), 'id' => 'title', 'autocomplete' => 'off']) }}
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        {{ Form::label('health_coach', trans('masterclass.form.labels.author')) }}
                        {{ Form::select('health_coach', $healthcoach, old('health_coach', ($record->creator_id ?? null)), ['class' => 'form-control select2', 'id' => 'health_coach', 'placeholder' => trans('masterclass.form.placeholder.author'), 'data-placeholder' => trans('masterclass.form.placeholder.author')] ) }}
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="custom-checkbox no-label">
                            {{ trans('masterclass.form.labels.has_trailer') }}
                            {{ Form::checkbox('has_trailer', true, old('has_trailer', ($record->has_trailer ?? true)), ['class' => 'form-control', 'id' => 'has_trailer']) }}
                            <span class="checkmark">
                            </span>
                            <span class="box-line">
                            </span>
                        </label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        {{ Form::label('goal_tag', trans('masterclass.form.labels.goal_tag')) }}
                        {{ Form::select('goal_tag[]',$goalTags, ($goal_tags ?? null), ['class' => 'form-control select2', 'id' => 'goal_tag', 'multiple' => true, 'data-placeholder' => trans('masterclass.form.placeholder.goal_tag'), 'data-allow-clear' => 'false']) }}
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        {{ Form::label('goal_tag', trans('masterclass.form.labels.tag')) }}
                        {{ Form::select('tag', $tags, ($record->tag_id ?? null), ['class' => 'form-control select2', 'id' => 'tag', 'placeholder' => trans('masterclass.form.placeholder.tag'), 'data-placeholder' => trans('masterclass.form.placeholder.tag'), 'data-allow-clear' => 'true']) }}
                    </div>
                </div>
                <div class="col-sm-6" id="trailer_type_wrapper">
                    <div class="form-group">
                        {{ Form::label('trailer_type', trans('masterclass.form.labels.trailer_type')) }}
                        {{ Form::select('trailer_type', config('zevolifesettings.masterclass_trailer_type'), old('trailer_type', ($record->trailer_type ?? 1)), ['class' => 'form-control select2', 'id' => 'trailer_type', 'placeholder' => trans('masterclass.form.placeholder.trailer_type'), 'data-placeholder' => trans('masterclass.form.placeholder.trailer_type'), 'disabled' => $edit] ) }}
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        {{ Form::label('Header Image', trans('masterclass.form.labels.header_image')) }}
                        <span class="font-16 qus-sign-tooltip" data-toggle="help-tooltip" title="{{ getHelpTooltipText('feed.header_image') }}">
                            <i aria-hidden="true" class="far fa-info-circle text-primary">
                            </i>
                        </span>
                        <div class="custom-file custom-file-preview">
                            <label class="file-preview-img" for="header_image_preview" style="display: flex;">
                                <img id="header_image_preview" src="{{ ((!empty($record) && !empty($record->getFirstMediaUrl('header_image'))) ? $record->getFirstMediaUrl('header_image') : asset('assets/dist/img/boxed-bg.png')) }}" width="200"/>
                            </label>
                            {{ Form::file('header_image', ['class' => 'custom-file-input form-control', 'id' => 'header_image', 'data-width' => config('zevolifesettings.imageConversions.course.header_image.width'), 'data-height' => config('zevolifesettings.imageConversions.course.header_image.height'), 'data-ratio' => config('zevolifesettings.imageAspectRatio.course.header_image'), 'autocomplete' => 'off','title'=>'', 'data-previewelement' => '#header_image_preview']) }}
                            {{ Form::label('header_image', ((!empty($record) && !empty($record->getFirstMediaUrl('header_image'))) ? $record->getFirstMedia('header_image')->name : trans('feed.form.placeholder.choose_file')), ["class" => "custom-file-label"]) }}
                        </div>
                    </div>
                </div>
                <!-- audio -->
                <div class="col-md-12" id="trailer_audio_wrapper">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                {{ Form::label('trailer_audio', trans('masterclass.form.labels.audio')) }}
                                <span class="font-16 qus-sign-tooltip" data-toggle="help-tooltip" title="{{ getSizeHelpTooltipText('course.trailer_audio') }}">
                                    <i aria-hidden="true" class="far fa-info-circle text-primary">
                                    </i>
                                </span>
                                <div class="custom-file">
                                    {{ Form::file('trailer_audio', ['class' => 'custom-file-input form-control', 'id' => 'trailer_audio']) }}
                                    <label class="custom-file-label" for="trailer_audio">
                                        {{ ((!empty($record) && !empty($record->getFirstMediaUrl('trailer_audio'))) ? $record->getFirstMedia('trailer_audio')->name : trans('masterclass.form.placeholder.choose-file')) }}
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                {{ Form::label('trailer_audio_background', trans('masterclass.form.labels.audio_background')) }}
                                <span class="font-16 qus-sign-tooltip" data-toggle="help-tooltip" title="{{ getHelpTooltipText('course.trailer_background') }}">
                                    <i aria-hidden="true" class="far fa-info-circle text-primary">
                                    </i>
                                </span>
                                <div class="custom-file custom-file-preview">
                                    <label class="file-preview-img" for="trailer_audio_background_preview" style="display: flex;">
                                        <img class="profile-image-preview" id="trailer_audio_background_preview" src="{{ ((!empty($record->trailer_background)) ? $record->trailer_background : asset('assets/dist/img/boxed-bg.png')) }}" width="200"/>
                                    </label>
                                    {{ Form::file('trailer_audio_background', ['class' => 'custom-file-input form-control', 'id' => 'trailer_audio_background', 'data-width' => config('zevolifesettings.imageConversions.course.trailer_background.width'), 'data-height' => config('zevolifesettings.imageConversions.course.trailer_background.height'), 'data-ratio' => config('zevolifesettings.imageAspectRatio.course.trailer_background'), 'data-previewelement' => '#trailer_audio_background_preview'])}}
                                    <label class="custom-file-label" for="trailer_audio_background">
                                        {{ ((!empty($record) && !empty($record->getFirstMediaUrl('trailer_background'))) ? $record->getFirstMedia('trailer_background')->name : trans('masterclass.form.placeholder.choose-file')) }}
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                {{ Form::label('trailer_audio_background_portal', trans('masterclass.form.labels.audio_background_portal')) }}
                                <span class="font-16 qus-sign-tooltip" data-toggle="help-tooltip" title="{{ getHelpTooltipText('course.trailer_background_portal') }}">
                                    <i aria-hidden="true" class="far fa-info-circle text-primary">
                                    </i>
                                </span>
                                <div class="custom-file custom-file-preview">
                                    <label class="file-preview-img" for="trailer_audio_background_portal_preview" style="display: flex;">
                                        <img class="profile-image-preview" id="trailer_audio_background_portal_preview" src="{{ ($record->trailer_background_portal ?? asset('assets/dist/img/boxed-bg.png')) }}" width="200"/>
                                    </label>
                                    {{ Form::file('trailer_audio_background_portal', ['class' => 'custom-file-input form-control', 'id' => 'trailer_audio_background_portal', 'data-width' => config('zevolifesettings.imageConversions.course.trailer_background_portal.width'), 'data-height' => config('zevolifesettings.imageConversions.course.trailer_background_portal.height'), 'data-ratio' => config('zevolifesettings.imageAspectRatio.course.trailer_background_portal'), 'data-previewelement' => '#trailer_audio_background_portal_preview'])}}
                                    <label class="custom-file-label" for="trailer_audio_background_portal">
                                        {{ ((!empty($record) && !empty($record->getFirstMediaUrl('trailer_background_portal'))) ? $record->getFirstMedia('trailer_background_portal')->name : trans('masterclass.form.placeholder.choose-file')) }}
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /.audio -->
                <!-- video -->
                <div class="form-group col-sm-6" id="trailer_video_wrapper" style="display: none;">
                    {{ Form::label('trailer_video', trans('masterclass.form.labels.video')) }}
                    <span class="font-16 qus-sign-tooltip" data-toggle="help-tooltip" title="{{ getSizeHelpTooltipText('course.trailer_video') }}">
                        <i aria-hidden="true" class="far fa-info-circle text-primary">
                        </i>
                    </span>
                    <div class="custom-file">
                        {{ Form::file('trailer_video', ['class' => 'custom-file-input form-control', 'id' => 'trailer_video']) }}
                        <label class="custom-file-label" for="trailer_video">
                            {{ ((!empty($record) && !empty($record->getFirstMediaUrl('trailer_video'))) ? $record->getFirstMedia('trailer_video')->name : trans('masterclass.form.placeholder.choose-file')) }}
                        </label>
                    </div>
                </div>
                <!-- /.video -->
                <!-- youtube -->
                <div class="col-md-12" id="trailer_youtube_wrapper" style="display: none;">
                    <div class="form-group">
                        {{ Form::label('trailer_youtube', trans('masterclass.form.labels.youtube')) }}
                        <span class="font-16 qus-sign-tooltip" data-placement="top" data-toggle="help-tooltip" title="{{ config('zevolifesettings.youtube_hint_message.message') }}">
                            <i aria-hidden="true" class="far fa-info-circle text-primary">
                            </i>
                        </span>
                        {{ Form::text('trailer_youtube', old('trailer_youtube', (!empty($record) && $record->trailer_type == 3 && !empty($record->getFirstMedia('track')) ? config('zevolifesettings.youtubeappurl').$record->getFirstMedia('track')->getCustomProperty('ytid') : '')), ['class' => 'form-control', 'placeholder' => config('zevolifesettings.youtube_hint_message.placeholder'), 'id' => 'trailer_youtube', 'autocomplete' => 'off']) }}
                    </div>
                </div>
                <!-- /.youtube -->
                <!-- vimeo -->
                <div class="col-md-12" id="trailer_vimeo_wrapper" style="display: none;">
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group">
                                {{ Form::label('track_vimeo', trans('masterclass.form.labels.vimeo_background')) }}
                                <span class="font-16 qus-sign-tooltip" data-toggle="help-tooltip" title="{{ getHelpTooltipText('course.track_thumbnail') }}">
                                    <i aria-hidden="true" class="far fa-info-circle text-primary">
                                    </i>
                                </span>
                                <div class="custom-file custom-file-preview">
                                    <label class="file-preview-img" for="track_vimeo_preview" style="display: flex;">
                                        <img class="profile-image-preview" id="track_vimeo_preview" src="{{ ($record->track_vimeo ?? asset('assets/dist/img/boxed-bg.png')) }}" width="200"/>
                                    </label>
                                    {{ Form::file('track_vimeo', ['class' => 'custom-file-input form-control', 'id' => 'track_vimeo', 'data-width' => config('zevolifesettings.imageConversions.course.track_thumbnail.width'), 'data-height' => config('zevolifesettings.imageConversions.course.track_thumbnail.height'), 'data-ratio' => config('zevolifesettings.imageAspectRatio.course.track_thumbnail'), 'data-previewelement' => '#track_vimeo_preview'])}}
                                    <label class="custom-file-label" for="track_vimeo">
                                        {{ ((!empty($record) && !empty($record->getFirstMediaUrl('track_vimeo'))) ? $record->getFirstMedia('track_vimeo')->name : trans('masterclass.form.placeholder.choose-file')) }}
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                {{ Form::label('trailer_vimeo', trans('masterclass.form.labels.vimeo')) }}
                                <span class="font-16 qus-sign-tooltip" data-placement="top" data-toggle="help-tooltip" title="{{ config('zevolifesettings.vimeo_hint_message.message') }}">
                                    <i aria-hidden="true" class="far fa-info-circle text-primary">
                                    </i>
                                </span>
                                <div class="custom-file">
                                    {{ Form::text('trailer_vimeo', old('trailer_vimeo', (!empty($record) && $record->trailer_type == 4 && !empty($record->getFirstMedia('track')) ? config('zevolifesettings.vimeoappurl') . $record->getFirstMedia('track')->getCustomProperty('vmid') : '')), ['class' => 'form-control', 'placeholder' => config('zevolifesettings.vimeo_hint_message.placeholder'), 'id' => 'trailer_vimeo', 'autocomplete' => 'off']) }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /.vimeo -->
            </div>
        </div>
    </div>
</div>
<div class="card-inner">
    <h3 class="card-inner-title">
        {{ trans('masterclass.form.labels.description') }}
    </h3>
    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                {{ Form::textarea('description', old('description', (isset($record->instructions) ? htmlspecialchars_decode($record->instructions) : null)), ['class' => 'form-control article-ckeditor', 'id' => 'description', 'data-errplaceholder' => '#description-error-cstm', 'data-formid' => (($edit) ? "#courseEdit" : "#courseAdd"), 'data-upload-path' => route('admin.ckeditor-upload.feed-description', ['_token' => csrf_token() ])]) }}

                <span id="description-error" style="display: none; width: 100%; margin-top: 0.25rem; font-size: 80%; color: #f44436;">
                    {{ trans('masterclass.validation.description_required') }}
                </span>
                <span id="description-max-error" style="display: none; width: 100%; margin-top: 0.25rem; font-size: 80%; color: #f44436;">
                    {{ trans('masterclass.validation.description_max') }}
                </span>
            </div>
        </div>
    </div>
</div>
<div class="card-inner">
    <h3 class="card-inner-title">
        {{ trans('masterclass.form.labels.company_visibility') }}
    </h3>
    <div>
        <div class="tree-multiselect-box" id="setPermissionList">
            <select class="form-control" id="masterclass_company" multiple="multiple" name="masterclass_company[]">
                @foreach($companies as $rolekey => $rolevalue)
                    @foreach($rolevalue['companies'] as $key => $value)
                        @foreach($value['location'] as $locationKey => $locationValue)
                            @foreach($locationValue['department'] as $departmentKey => $departmentValue)
                                @foreach($departmentValue['team'] as $teamKey => $teamValue)
                                    <option value="{{ $teamValue['id'] }}" data-section="{{ $rolevalue['roleType'] }}/{{$value['companyName']}}/{{$locationValue['locationName']}}/{{$departmentValue['departmentName']}}" {{ (!empty($masterclass_company) && in_array($teamValue['id'], $masterclass_company))? 'selected' : ''   }} >{{ $teamValue['name'] }}</option>
                                @endforeach
                            @endforeach
                        @endforeach
                    @endforeach
                @endforeach
            </select>
            <span id="masterclass_company-error" style="display: none; width: 100%; margin-top: 0.25rem; font-size: 80%; color: #f44436;">
                {{ trans('labels.course.company_selection') }}
            </span>
        </div>
    </div>
</div>