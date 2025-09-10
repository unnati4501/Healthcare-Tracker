<div class="form-group col-xl-4 offset-xl-1">
    <label for="">{{trans('labels.meditationcategory.category_name')}}</label>
    @if(!empty($meditationcategoryData->title))
        {{ Form::text('name', old('name',$meditationcategoryData->title), ['class' => 'form-control', 'placeholder' => 'Enter meditation category', 'id' => 'name', 'autocomplete' => 'off']) }}
    @else
        {{ Form::text('name', old('name'), ['class' => 'form-control', 'placeholder' => 'Enter meditation category', 'id' => 'name', 'autocomplete' => 'off']) }}
    @endif
</div>

<div class="form-group col-xl-4 offset-xl-2">
    <label for="">{{trans('labels.meditationcategory.logo')}}</label>
    <span class="font-16 qus-sign-tooltip" data-placement="auto" data-bs-toggle="tooltip" title="Select meditation category Logo">
        <i aria-hidden="true" class="far fa-info-circle text-primary">
        </i>
    </span>
    <div class="custom-file custom-file-preview">
        <label class="file-preview-img" for="profileImage" style="display: flex;">
            @if(!empty($meditationcategoryData->logo))
            <img id="previewImg" src="{{$meditationcategoryData->logo}}" width="200" height="200" />
            @else
            <img id="previewImg" src="{{asset('assets/dist/img/boxed-bg.png')}}" width="200" height="200"/>
            @endif
        </label>
        {{ Form::file('logo', ['class' => 'custom-file-input', 'id' => 'logo', 'autocomplete' => 'off','title'=>''])}}
        <label class="custom-file-label" for="logo">
            @if(!empty($meditationcategoryData->logo))
                {{$meditationcategoryData->logo}}
            @else
                Choose File
            @endif
        </label>
    </div>
</div>
