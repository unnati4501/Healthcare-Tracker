<div class="col-lg-6 col-xl-4">
    <div class="form-group">
        {{ Form::label('name', trans('services.form.labels.service')) }}
        @if(!empty($serviceData->name))
            {{ Form::text('name', old('name',$serviceData->name), ['class' => 'form-control', 'placeholder' => trans('services.form.placeholders.service'), 'id' => 'name', 'autocomplete' => 'off']) }}
        @else
            {{ Form::text('name', old('name'), ['class' => 'form-control', 'placeholder' => trans('services.form.placeholders.service'), 'id' => 'name', 'autocomplete' => 'off']) }}
        @endif
    </div>
</div>
<div class="col-lg-6 col-xl-4 type_wrappers" id="logo_wrapper" >
    <div class="form-group">
        {{ Form::label('logo', trans('services.form.labels.logo')) }}
        <span class="font-16 qus-sign-tooltip" data-toggle="help-tooltip" title="{{ getHelpTooltipText('services.logo') }}">
            <i aria-hidden="true" class="far fa-info-circle text-primary">
            </i>
        </span>
        <div class="custom-file custom-file-preview">
            {{ Form::file('logo', ['class' => 'custom-file-input form-control', 'id' => 'logo', 'data-ratio' => config('zevolifesettings.imageAspectRatio.services.logo'), 'data-width' => config('zevolifesettings.imageConversions.services.logo.width'), 'data-height' => config('zevolifesettings.imageConversions.services.logo.height'), 'data-previewelement' => '#logo_preview', 'accept' => 'image/*']) }}
            <label class="file-preview-img" for="logo_preview">
                <img id="logo_preview" src="{{ ($serviceData->logo ?? asset('assets/dist/img/boxed-bg.png')) }}" width="200"/>
            </label>
            {{ Form::label('logo', ((!empty($serviceData) && !empty($serviceData->getFirstMediaUrl('logo'))) ? $serviceData->getFirstMedia('logo')->name : trans('services.form.placeholders.choose_file')), ["class" => "custom-file-label"]) }}
        </div>
    </div>
</div>
<div class="col-lg-6 col-xl-4 type_wrappers" id="icon_wrapper" >
    <div class="form-group">
        {{ Form::label('icon', trans('services.form.labels.icon')) }}
        <span class="font-16 qus-sign-tooltip" data-toggle="help-tooltip" title="{{ getHelpTooltipText('services.icon') }}">
            <i aria-hidden="true" class="far fa-info-circle text-primary">
            </i>
        </span>
        <div class="custom-file custom-file-preview">
            {{ Form::file('icon', ['class' => 'custom-file-input form-control', 'id' => 'icon', 'data-ratio' => config('zevolifesettings.imageAspectRatio.services.icon'), 'data-width' => config('zevolifesettings.imageConversions.services.icon.width'), 'data-height' => config('zevolifesettings.imageConversions.services.icon.height'), 'data-previewelement' => '#icon_preview', 'accept' => 'image/*']) }}
            <label class="file-preview-img" for="icon_preview">
                <img id="icon_preview" src="{{ ($serviceData->icon ?? asset('assets/dist/img/boxed-bg.png')) }}" width="200"/>
            </label>
            {{ Form::label('icon', ((!empty($serviceData) && !empty($serviceData->getFirstMediaUrl('icon'))) ? $serviceData->getFirstMedia('icon')->name : trans('services.form.placeholders.choose_file')), ["class" => "custom-file-label"]) }}
        </div>
    </div>
</div>
<div class="col-lg-6 col-xl-4">
    <div class="form-group">
        {{ Form::label('description', trans('services.form.labels.description')) }}
        {{ Form::textarea('description', old('description', ($serviceData->description ?? '')), ['class' => 'form-control', 'placeholder' => trans('services.form.placeholders.description'), 'id' => 'description']) }}
    </div>
</div>
<div class="col-lg-6 col-xl-4">
    <div class="form-group">
    {{ Form::label('service_type', trans('services.form.labels.service_type')) }}
    {{ Form::select('is_public', $serviceType, old('is_public', ($serviceData->is_public ??  true)), ['class' => 'form-control select2', 'id'=>'is_public', 'data-allow-clear' => 'false']) }}
    </div>
</div>

<div class="col-lg-6 col-xl-4">
    <div class="form-group">
    {{ Form::label('session_duration', trans('services.form.labels.session_duration')) }}
    <span class="font-16 qus-sign-tooltip" data-toggle="help-tooltip" title="{{ trans('services.form.tooltips.session_duration')}}">
        <i aria-hidden="true" class="far fa-info-circle text-primary">
        </i>
    </span>
    {{ Form::select('session_duration', ($sessionDurationsMins ?? 30), old('session_duration', ($serviceData->session_duration ?? 30)), ['class' => 'form-control select2','id'=>'dt_counselling_duration', 'data-allow-clear'=>'false']) }}
    </div>
</div>
<div class="col-lg-6 col-xl-4">
    <label class="custom-checkbox">
        {{ trans('services.form.labels.is_counselling') }}
        @php
            $checked = "";
            if(!empty($serviceData) && $serviceData->is_counselling ==  1){
                $checked = 'checked="checked"';
            }
        @endphp
        <input type="checkbox" class="form-control" value="yes" name="is_counselling" id="is_counselling" {{$checked}} />
        <span class="checkmark">
        </span>
        <span class="box-line">
        </span>
    </label>
</div>

</div></div>

<div class="card-inner">
    <h3 class="card-inner-title">
        {{ trans('services.form.labels.sub_categories') }}
    </h3> 
    <div class="text-end">
        <a href="javascript:void(0)" class="btn btn-primary" id="addSubcategory">
            <i class="far fa-plus me-3 align-middle">
            </i>
            {{ trans('services.buttons.add_subcategories') }}
        </a>
    </div>
    <div class="row">
        <div class="card-body">
            <div class="card-table-outer" id="serviceManagment-wrap">
                <div class="table-responsive">
                    <table class="table custom-table" id="subCategoryTbl">
                        <thead>
                            <tr>
                                <th>
                                    {{ trans('services.subcategories.table.logo') }}
                                </th>
                                <th>
                                    {{ trans('services.subcategories.table.name') }}
                                </th>
                                <th class="th-btn-4 no-sort">
                                    {{ trans('services.table.action') }}
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            {{-- @if(!empty($subCategories))
                                @include('admin.services.edit-subcategory')  
                            @else
                                @include('admin.services.subcategory') 
                            @endif --}}
                            @if(!empty($subCategories))
                            @include('admin.services.edit-subcategory', [
                                'subCatCount' => sizeof($subCategories),
                                'subCategory' => null,
                                'show_del' => '',
                            ])
                            @else
                            @include('admin.services.subcategory', [
                                'subCatCount' => '0',
                                'subCategory' => null,
                                'show_del' => '',
                            ])
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script id="subcategoriesTemplate" type="text/html">
    @include('admin.services.subcategory', [
        'subCatCount' => ':subCatCount',
        'subCategory' => null,
        'show_del' => 'show_del',
    ])
</script>
