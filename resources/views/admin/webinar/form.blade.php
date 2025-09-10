<div class="card-inner">
    <div class="row">
        <div class="col-lg-6 col-xl-4">
            <div class="form-group">
                {{ Form::label('title', trans('webinar.form.labels.webinar_name')) }}
                {{ Form::text('title', old('title', ($data->title ?? null)), ['class' => 'form-control', 'placeholder' => trans('webinar.form.placeholder.enter_webinar_name'), 'id' => 'title', 'autocomplete' => 'off']) }}
            </div>
        </div>
        <div class="col-lg-6 col-xl-4">
            <div class="form-group">
                {{ Form::label('webinar_category', trans('webinar.form.labels.category')) }}
                {{ Form::select('webinar_category', $subcategory, ($data->sub_category_id ?? null), ['class' => 'form-control select2','id'=>'webinar_category', 'placeholder' => trans('webinar.form.placeholder.select_category'), 'data-placeholder' => trans('webinar.form.placeholder.select_category'), 'autocomplete' => 'off'] ) }}
            </div>
        </div>

        <div class="col-lg-6 col-xl-4">
            <div class="form-group">
                {{ Form::label('webinar_type', trans('webinar.form.labels.webinar_type')) }}
                {{ Form::select('webinar_type', config('zevolifesettings.webinarTrackType'), old('webinar_type', ($data->type ?? null)), ['class' => 'form-control select2', 'id'=>'webinar_type', 'disabled' => $edit]) }}
            </div>
        </div>
        <div class="col-lg-6 col-xl-4">
            <div class="form-group">
                {{ Form::label('webinar_cover', trans('webinar.form.labels.webinar_cover')) }}
                <span class="font-16 qus-sign-tooltip" data-toggle="help-tooltip" title="{{ getHelpTooltipText('webinar.cover') }}">
                    <i aria-hidden="true" class="far fa-info-circle text-primary">
                    </i>
                </span>
                <div class="custom-file custom-file-preview">
                    <label class="file-preview-img d-flex" for="webinar_cover_preview">
                        <img id="webinar_cover_preview" src="{{ ($data->logo ?? asset('assets/dist/img/boxed-bg.png')) }}" width="200"/>
                    </label>
                    {{ Form::file('webinar_cover', ['class' => 'custom-file-input form-control', 'id' => 'webinar_cover', 'data-width' => config('zevolifesettings.imageConversions.webinar.cover.width'), 'data-height' => config('zevolifesettings.imageConversions.webinar.cover.height'), 'data-ratio' => config('zevolifesettings.imageAspectRatio.webinar.cover'), 'data-previewelement' => '#webinar_cover_preview', 'accept' => 'image/*'])}}
                    {{ Form::label('webinar_cover', ((!empty($data) && !empty($data->getFirstMediaUrl('logo'))) ? $data->getFirstMedia('logo')->name : trans('webinar.form.placeholder.choose_file')), ["class" => "custom-file-label"]) }}
                </div>
            </div>
        </div>
        <div class="col-lg-6 col-xl-4 type_wrappers" id="video_wrapper">
            <div class="form-group">
                {{ Form::label('webinar_file', trans('webinar.form.labels.webinar_file')) }}
                <span class="font-16 qus-sign-tooltip" data-toggle="help-tooltip" title="{{ getSizeHelpTooltipText('webinar.track') }}">
                    <i aria-hidden="true" class="far fa-info-circle text-primary">
                    </i>
                </span>
                <div class="custom-file">
                    {{ Form::file('webinar_file', ['class' => 'custom-file-input form-control', 'id' => 'webinar_file', 'accept' => 'video/*']) }}
                    {{ Form::label('webinar_file', ((!empty($data) && ($data->type == 1) && !empty($data->getFirstMediaUrl('track'))) ? $data->getFirstMedia('track')->name : trans('webinar.form.placeholder.choose_file')), ["class" => "custom-file-label"]) }}
                </div>
            </div>
        </div>
        <div class="col-lg-6 col-xl-4 type_wrappers" id="youtube_wrapper" style="display: none;">
            <div class="form-group">
                {{ Form::label('webinar_youtube', trans('webinar.form.labels.youtube')) }}
                <span class="font-16 qus-sign-tooltip" data-placement="top" data-toggle="help-tooltip" title="{{ config('zevolifesettings.youtube_hint_message.message') }}">
                    <i aria-hidden="true" class="far fa-info-circle text-primary">
                    </i>
                </span>
                {{ Form::text('webinar_youtube', old('webinar_youtube', ((!empty($data) && ($data->type == 2) && !empty($data->getFirstMedia('track'))) ? config('zevolifesettings.youtubeappurl').$data->getFirstMedia('track')->getCustomProperty('ytid') : null)), ['class' => 'form-control', 'placeholder' => config('zevolifesettings.youtube_hint_message.placeholder'), 'id' => 'webinar_youtube']) }}
            </div>
        </div>
        <div class="col-lg-6 col-xl-4 type_wrappers" id="vimeo_wrapper" style="display: none;">
            <div class="form-group">
                {{ Form::label('webinar_vimeo', trans('webinar.form.labels.vimeo')) }}
                <span class="font-16 qus-sign-tooltip" data-placement="top" data-toggle="help-tooltip" title="{{ config('zevolifesettings.vimeo_hint_message.message') }}">
                    <i aria-hidden="true" class="far fa-info-circle text-primary">
                    </i>
                </span>
                {{ Form::text('webinar_vimeo', old('webinar_vimeo', (!empty($data) && $data->type == 3 && !empty($data->getFirstMedia('track')) ? config('zevolifesettings.vimeoappurl').$data->getFirstMedia('track')->getCustomProperty('vmid') : '')), ['class' => 'form-control', 'placeholder' => config('zevolifesettings.vimeo_hint_message.placeholder'), 'id' => 'webinar_vimeo', 'autocomplete' => 'off']) }}
            </div>
        </div>
        <div class="col-lg-6 col-xl-4">
            <div class="form-group">
                {{ Form::label('duration', trans('webinar.form.labels.duration_second')) }}
                {{ Form::text('duration', old('duration', ($data->duration ?? null)), ['class' => 'form-control', 'placeholder' => trans('webinar.form.placeholder.track_duration'), 'readonly'=>'true', 'id' => 'duration', 'autocomplete' => 'off']) }}
            </div>
        </div>
        <div class="col-lg-6 col-xl-4">
            <div class="form-group">
                {{ Form::label('author', trans('webinar.form.labels.author')) }}
                {{ Form::select('author', $author, ($data->author_id ?? null), ['class' => 'form-control select2','id'=>'author', 'placeholder' => trans('webinar.form.placeholder.select_author'), 'data-placeholder' => trans('webinar.form.placeholder.select_author'), 'autocomplete' => 'off'] ) }}
            </div>
        </div>
        <div class="col-lg-6 col-xl-4">
            <div class="form-group">
                {{ Form::label('goal_tag', trans('webinar.form.labels.goal_tag')) }}
                {{ Form::select('goal_tag[]',$goalTags, ($goal_tags ?? null), ['class' => 'form-control select2','id'=>'goal_tag',"style"=>"width: 100%;", 'multiple'=>true, 'autocomplete' => 'off','data-placeholder' => trans('webinar.form.placeholder.select_goal_tags')] ) }}
            </div>
        </div>
        @if($roleGroup == 'zevo')
        <div class="col-lg-6 col-xl-4">
            <div class="form-group">
                {{ Form::label('tag', trans('feed.form.labels.tag')) }}
                {{ Form::select('tag', $tags, ($data->tag_id ?? null), ['class' => 'form-control select2', 'id' => 'tag', 'placeholder' => trans('feed.form.placeholder.tag'), 'data-placeholder' => trans('feed.form.placeholder.tag'), 'data-allow-clear' => 'true']) }}
            </div>
        </div>
        @endif
        <div class="col-lg-6 col-xl-4">
            <div class="form-group">
                {{ Form::label('Header Image', trans('webinar.form.labels.header_image')) }}
                <span class="font-16 qus-sign-tooltip" data-toggle="help-tooltip" title="{{ getHelpTooltipText('webinar.header_image') }}">
                    <i aria-hidden="true" class="far fa-info-circle text-primary">
                    </i>
                </span>
                <div class="custom-file custom-file-preview">
                    <label class="file-preview-img" for="header_image_preview" style="display: flex;">
                        <img id="header_image_preview" src="{{ ((!empty($data) && !empty($data->getFirstMediaUrl('header_image'))) ? $data->getFirstMediaUrl('header_image') : asset('assets/dist/img/boxed-bg.png')) }}" width="200"/>
                    </label>
                    {{ Form::file('header_image', ['class' => 'custom-file-input form-control', 'id' => 'header_image', 'data-width' => config('zevolifesettings.imageConversions.webinar.header_image.width'), 'data-height' => config('zevolifesettings.imageConversions.webinar.header_image.height'), 'data-ratio' => config('zevolifesettings.imageAspectRatio.webinar.header_image'), 'autocomplete' => 'off','title'=>'', 'data-previewelement' => '#header_image_preview', 'accept' => 'image/*']) }}
                    {{ Form::label('header_image', ((!empty($data) && !empty($data->getFirstMediaUrl('header_image'))) ? $data->getFirstMedia('header_image')->name : trans('webinar.form.placeholder.choose_file')), ["class" => "custom-file-label"]) }}
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card-inner">
    <h3 class="card-inner-title">{{ trans('webinar.form.labels.company_visibility') }}</h3>
    <div>
        <div id="setPermissionList" class="tree-multiselect-box">
            <select id="webinar_companys" name="webinar_company[]" multiple="multiple" class="form-control">
                @foreach($companies as $rolekey => $rolevalue)
                    @foreach($rolevalue['companies'] as $key => $value)
                        @foreach($value['location'] as $locationKey => $locationValue)
                            @foreach($locationValue['department'] as $departmentKey => $departmentValue)
                                @foreach($departmentValue['team'] as $teamKey => $teamValue)
                                    <option value="{{ $teamValue['id'] }}" data-section="{{ $rolevalue['roleType'] }}/{{$value['companyName']}}/{{$locationValue['locationName']}}/{{$departmentValue['departmentName']}}" {{ (!empty($webinar_companys) && in_array($teamValue['id'], $webinar_companys))? 'selected' : ''   }} >{{ $teamValue['name'] }}</option>
                                @endforeach
                            @endforeach
                        @endforeach
                    @endforeach
                @endforeach
            </select>
        </div>
        <span id="webinar_companys-error" style="display: none; width: 100%; margin-top: 0.25rem; font-size: 80%; color: #f44436;">{{ trans('webinar.validation.company_selection') }}</span>
    </div>
</div>
<video class="d-none" id="video_duration">
</video>