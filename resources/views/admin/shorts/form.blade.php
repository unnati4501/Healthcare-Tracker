<div class="card-inner">
    <div class="row">
        <div class="col-lg-6 col-xl-4">
            <div class="form-group">
                {{ Form::label('title', trans('shorts.form.labels.title')) }}
                {{ Form::text('title', old('title', ($data->title ?? null)), ['class' => 'form-control', 'placeholder' => trans('shorts.form.placeholder.name'), 'id' => 'title', 'autocomplete' => 'off']) }}
            </div>
        </div>
        <div class="col-lg-6 col-xl-4">
            <div class="form-group">
                {{ Form::label('shorts_category', trans('podcast.form.labels.subcategory_name')) }}
                {{ Form::select('shorts_category', $subcategory, ($data->sub_category_id ?? null), ['class' => 'form-control select2','id'=>'shorts_category', 'placeholder' => trans('shorts.form.placeholder.shorts_category'), 'data-placeholder' => trans('shorts.form.placeholder.shorts_category')] ) }}
            </div>
        </div>
        <div class="col-lg-6 col-xl-4">
            <div class="form-group">
                {{ Form::label('shorts_type', trans('shorts.form.labels.short_type')) }}
                {{ Form::select('shorts_type', config('zevolifesettings.shortsTrackType'), old('short_type', ($data->type ?? null)), ['class' => 'form-control select2', 'id'=>'shorts_type', 'disabled' => $edit]) }}
            </div>
        </div>
        <div class="col-lg-6 col-xl-4">
            <div class="form-group">
                {{ Form::label('header_image', trans('shorts.form.labels.header_image')) }}
                <span class="font-16 qus-sign-tooltip" data-toggle="help-tooltip" title="{{ getHelpTooltipText('shorts.header_image') }}">
                    <i aria-hidden="true" class="far fa-info-circle text-primary">
                    </i>
                </span>
                <div class="custom-file custom-file-preview">
                    {{ Form::file('header_image', ['class' => 'custom-file-input form-control', 'id' => 'header_image', 'data-width' => config('zevolifesettings.imageConversions.shorts.header_image.width'), 'data-height' => config('zevolifesettings.imageConversions.shorts.header_image.height'), 'data-ratio' => config('zevolifesettings.imageAspectRatio.shorts.header_image'), 'data-previewelement' => '#header_image_preview', 'accept' => 'image/*'])}}
                    <label class="file-preview-img" for="header_image_preview">
                        <img id="header_image_preview" src="{{ ((!empty($data) && !empty($data->getFirstMediaUrl('header_image'))) ? $data->getFirstMediaUrl('header_image') : asset('assets/dist/img/boxed-bg.png')) }}" width="200"/>
                    </label>
                    {{ Form::label('header_image', ((!empty($data) && !empty($data->getFirstMediaUrl('header_image'))) ? $data->getFirstMedia('header_image')->name : trans('shorts.form.placeholder.choose_file')), ["class" => "custom-file-label"]) }}
                </div>
            </div>
        </div>
        <div class="col-lg-6 col-xl-4">
            <div class="form-group">
                {{ Form::label('author', trans('shorts.form.labels.author')) }}
                {{ Form::select('author', $author, ($data->author_id ?? null), ['class' => 'form-control select2','id' => 'author', 'placeholder' => trans('podcast.form.placeholder.author'), 'data-placeholder' => trans('shorts.form.placeholder.author')] ) }}
            </div>
        </div>
        
        <div class="col-lg-6 col-xl-4 type_wrappers" id="vimeo_wrapper">
            <div class="form-group">
                {{ Form::label('vimeo', trans('shorts.form.labels.vimeo')) }}
                <span class="font-16 qus-sign-tooltip" data-placement="top" data-toggle="help-tooltip" title="{{ config('zevolifesettings.vimeo_hint_message_shorts.message') }}">
                    <i aria-hidden="true" class="far fa-info-circle text-primary">
                    </i>
                </span>
                {{ Form::text('vimeo', old('vimeo', (!empty($data) && !empty($data->getFirstMedia('vimeo')) ? $data->getFirstMedia('vimeo')->name : '')), ['class' => 'form-control', 'placeholder' => config('zevolifesettings.vimeo_hint_message_shorts.placeholder'), 'id' => 'shorts_vimeo', 'autocomplete' => 'off']) }}
            </div>
        </div>
        <div class="col-lg-6 col-xl-4">
            <div class="form-group">
                {{ Form::label('duration', trans('shorts.form.labels.duration')) }}
                {{ Form::text('duration', old('duration', ($data->duration ?? null)), ['class' => 'form-control numeric', 'placeholder' => trans('shorts.form.placeholder.duration'), 'id' => 'duration']) }}
            </div>
        </div>
        <div class="col-lg-6 col-xl-4">
            <div class="form-group">
                {{ Form::label('goal_tag', trans('shorts.form.labels.goal_tag')) }}
                {{ Form::select('goal_tag[]', $goalTags, ($goal_tags ?? null), ['class' => 'form-control select2','id' => 'goal_tag', 'multiple' => true, 'data-placeholder' => trans('shorts.form.placeholder.goal_tag'), 'data-allow-clear' => 'false']) }}
            </div>
        </div>
        @if($roleGroup == 'zevo')
        <div class="col-lg-6 col-xl-4">
            <div class="form-group">
                {{ Form::label('tag', trans('shorts.form.labels.tag')) }}
                {{ Form::select('tag', $tags, ($data->tag_id ?? null), ['class' => 'form-control select2', 'id' => 'tag', 'placeholder' => trans('podcast.form.placeholder.tag'), 'data-placeholder' => trans('shorts.form.placeholder.tag'), 'data-allow-clear' => 'true']) }}
            </div>
        </div>
        @endif
    </div>
</div>
<div class="card-inner">
    <h3 class="card-inner-title">
        {{ trans('shorts.form.labels.description') }}
    </h3>
    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                {{ Form::textarea('description', old('description', (isset($data->description) ? htmlspecialchars_decode($data->description) : null)), ['class' => 'form-control article-ckeditor', 'id' => 'description', 'data-errplaceholder' => '#description-error-cstm', 'data-formid' => (($edit) ? "#shortsEdit" : "#shortsAdd"), 'data-upload-path' => route('admin.ckeditor-upload.shorts-description', ['_token' => csrf_token() ])]) }}

                <span id="description-error" style="display: none; width: 100%; margin-top: 0.25rem; font-size: 80%; color: #f44436;">
                    {{ trans('shorts.validation.description_required') }}
                </span>
                <span id="description-max-error" style="display: none; width: 100%; margin-top: 0.25rem; font-size: 80%; color: #f44436;">
                    {{ trans('shorts.validation.description_max') }}
                </span>
            </div>
        </div>
    </div>
</div>
<div class="card-inner">
    <h3 class="card-inner-title">
        {{ trans('shorts.title.visibility') }}
    </h3>
    <div>
        <div id="setPermissionList" class="tree-multiselect-box">
            <select id="shorts_company" name="shorts_companys[]" multiple="multiple" class="form-control">
                @foreach($companies as $rolekey => $rolevalue)
                    @foreach($rolevalue['companies'] as $key => $value)
                        @foreach($value['location'] as $locationKey => $locationValue)
                            @foreach($locationValue['department'] as $departmentKey => $departmentValue)
                                @foreach($departmentValue['team'] as $teamKey => $teamValue)
                                    <option value="{{ $teamValue['id'] }}" data-section="{{ $rolevalue['roleType'] }}/{{$value['companyName']}}/{{$locationValue['locationName']}}/{{$departmentValue['departmentName']}}" {{ (!empty($shorts_companys) && in_array($teamValue['id'], $shorts_companys))? 'selected' : ''   }} >{{ $teamValue['name'] }}</option>
                                @endforeach
                            @endforeach
                        @endforeach
                    @endforeach
                @endforeach
            </select>
        </div>
        <span id="shorts_company-error" style="display: none; width: 100%; margin-top: 0.25rem; font-size: 80%; color: #f44436;">{{ trans('shorts.validation.company_selection') }}</span>
    </div>
</div>
<video class="d-none" id="video_duration">
</video>