<div class="card-inner">
    <div class="row">
        <div class="col-lg-6 col-xl-4">
            <div class="form-group">
                {{ Form::label('logo', trans('surveycategories.form.labels.logo')) }}
                <span class="font-16 qus-sign-tooltip" data-toggle="help-tooltip" title="{{ getHelpTooltipText('surveycategory.logo') }}">
                    <i aria-hidden="true" class="far fa-info-circle text-primary">
                    </i>
                </span>
                @if(!empty($categoryData) && $categoryData->logo)
                <span class="font-16 float-end">
                    <a class="badge bg-secondary remove-logo-media" data-action="logo" href="javascript:void(0);" title="{{ trans('surveycategories.form.placeholder.remove_logo') }}">
                        <i aria-hidden="true" class="fa fa-times">
                        </i>
                        {{ trans('surveycategories.buttons.remove') }}
                    </a>
                </span>
                @endif
                <div class="custom-file custom-file-preview">
                    {{ Form::file('logo', ['class' => 'custom-file-input form-control', 'id' => 'logo', 'data-previewelement' => '#logo_preview', 'data-width' => config('zevolifesettings.imageConversions.surveycategory.logo.width'), 'data-height' => config('zevolifesettings.imageConversions.surveycategory.logo.height'), 'data-ratio' => config('zevolifesettings.imageAspectRatio.surveycategory.logo'), 'accept' => 'image/*'])}}
                    <label class="file-preview-img d-flex" for="logo_preview">
                        <img id="logo_preview" src="{{ ($categoryData->logo ?? asset('assets/dist/img/boxed-bg.png')) }}" width="200"/>
                    </label>
                    {{ Form::label('logo', ((!empty($categoryData) && $categoryData->getFirstMediaUrl('logo') != null) ? $categoryData->getFirstMedia('logo')->name : ((!empty($categoryData) && $categoryData->logo)? 'zevohealth.png' : trans('surveycategories.form.placeholder.choose_file'))), ["class" => "custom-file-label"]) }}
                </div>
            </div>
        </div>
        <div class="col-lg-6 col-xl-4">
            <div class="form-group">
                {{ Form::label('display_name', trans('surveycategories.form.labels.category')) }}
                {{ Form::text('display_name', old('display_name', ($categoryData->display_name ?? null)), ['class' => 'form-control', 'placeholder' => trans('surveycategories.form.placeholder.category'), 'id' => 'display_name', 'autocomplete' => 'off']) }}
            </div>
        </div>
        <div class="col-lg-6 col-xl-4">
            <div class="form-group">
                @permission('manage-goal-tags')
                {{ Form::label('goal_tag', trans('surveycategories.form.labels.goal')) }}
                {{ Form::select('goal_tag[]', $goalTags, ($goal_tags ?? null), ['class' => 'form-control select2', 'id' => 'goal_tag', 'multiple' => true, 'data-placeholder' => trans('surveycategories.form.placeholder.goal')]) }}
                @endauth
            </div>
        </div>
    </div>
</div>