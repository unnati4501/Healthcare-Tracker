<div class="card-inner">
    <div class="row">
        <div class="col-lg-12 col-xl-8">
            <div class="form-group">
                <label for="">
                    @if($type == 'eap')
                    {{trans('appslides.form.labels.mobile_image')}}
                    @else
                    {{trans('appslides.form.labels.select_image')}}
                    @endif
                </label>
                <span class="font-16 qus-sign-tooltip" data-toggle="help-tooltip" title="{{ getHelpTooltipText($collectionType) }}">
                    <i aria-hidden="true" class="far fa-info-circle text-primary">
                    </i>
                </span>
                <div class="custom-file custom-file-preview">
                    <label class="file-preview-img" for="slideImage" style="display: flex;">
                        @if(!empty($appSlideData->logo))
                        <img id="previewImg" src="{{$appSlideData->logo}}" width="200" height="200" />
                        @else
                        <img id="previewImg" src="{{asset('assets/dist/img/boxed-bg.png')}}" width="200" height="200"/>
                        @endif
                    </label>
                    {{ Form::file('slideImage', ['class' => 'custom-file-input form-control', 'id' => 'slideImage', 'data-width' => $slideImageWidth, 'data-height' => $slideImageHeight, 'data-ratio' => $slideImageRatio, 'data-round' => $dataround, 'autocomplete' => 'off', 'accept' => 'image/*'])}}
                    <label class="custom-file-label" for="slideImage">
                        @if(!empty($appSlideData->logo_name))
                            {{$appSlideData->logo_name}}
                        @else
                            {{trans('appslides.form.labels.choosefile')}}
                        @endif
                    </label>

                </div>
            </div>
        </div>
        @if($type == 'eap')
        <div class="col-lg-12 col-xl-8">
            <div class="form-group">
                <label for="">
                    {{trans('appslides.form.labels.portal_image')}}
                </label>
                <span class="font-16 qus-sign-tooltip" data-toggle="help-tooltip" title="{{ getHelpTooltipText($collectionType) }}">
                    <i aria-hidden="true" class="far fa-info-circle text-primary">
                    </i>
                </span>
                <div class="custom-file custom-file-preview">
                    <label class="file-preview-img" for="portalSlideImage" style="display: flex;">
                        @if(!empty($appSlideData->portal_logo))
                        <img id="portalPreviewImg" src="{{$appSlideData->portal_logo}}" width="200" height="200" />
                        @else
                        <img id="portalPreviewImg" src="{{asset('assets/dist/img/boxed-bg.png')}}" width="200" height="200"/>
                        @endif
                    </label>
                    {{ Form::file('portalSlideImage', ['class' => 'custom-file-input form-control', 'id' => 'portalSlideImage', 'data-width' => $slideImageWidth, 'data-height' => $slideImageHeight, 'data-ratio' => $slideImageRatio, 'data-round' => $dataround, 'autocomplete' => 'off', 'accept' => 'image/*'])}}
                    <label class="custom-file-label" for="portalSlideImage">
                        @if(!empty($appSlideData->portal_logo_name))
                            {{$appSlideData->portal_logo_name}}
                        @else
                            {{trans('appslides.form.labels.choosefile')}}
                        @endif
                    </label>

                </div>
            </div>
        </div>
        @endif
        <div class="col-lg-12 col-xl-12">
            <div class="form-group mobile-content">
                @if($type == 'eap')
                <label>{{trans('appslides.form.labels.mobile_content')}}</label>
                @else
                <label>{{trans('appslides.form.labels.content')}}</label>
                @endif
                <textarea class="form-control" name="content" id="content" placeholder="{{trans('appslides.form.placeholder.enter')}}">{{ old('content', htmlspecialchars_decode(@$appSlideData->content)) }}</textarea>
                <span id="content-error" style="display: none; width: 100%; margin-top: 0.25rem; font-size: 80%; color: #f44436;">{{trans('appslides.validation.content_required')}}</span>
                <span id="content-max-error" style="display: none; width: 100%; margin-top: 0.25rem; font-size: 80%; color: #f44436;">{{trans('appslides.validation.content_max')}}</span>
            </div>
        </div>
        @if($type == 'eap')
        <div class="col-lg-12 col-xl-12">
            <div class="form-group portal-content">
                <label>{{trans('appslides.form.labels.portal_content')}}</label>
                <textarea class="form-control" name="portal_content" id="portal_content" placeholder="{{trans('appslides.form.placeholder.enter')}}">{{ old('portal_content', htmlspecialchars_decode(@$appSlideData->portal_content)) }}</textarea>
                <span id="portal-content-error" style="display: none; width: 100%; margin-top: 0.25rem; font-size: 80%; color: #f44436;">{{trans('appslides.validation.portal_content_required')}}</span>
                <span id="portal-content-max-error" style="display: none; width: 100%; margin-top: 0.25rem; font-size: 80%; color: #f44436;">{{trans('appslides.validation.portal_content_max')}}</span>
            </div>
        </div>
        @endif
    </div>
</div>
{{ Form::hidden('type', (isset($appSlideData->type)?$appSlideData->type:$type), ['id' => 'type']) }}