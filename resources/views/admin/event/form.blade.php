<div class="card-inner">
    <div class="row justify-content-center justify-content-md-start">
        <div class="col-xxl-2 col-lg-3 col-md-4 basic-file-upload order-md-2">
            {{ Form::label('logo', trans('labels.event.select_image')) }}
            <span class="font-16 qus-sign-tooltip" data-toggle="help-tooltip" title="{{ getHelpTooltipText('event.logo') }}">
                <i aria-hidden="true" class="far fa-info-circle text-primary">
                </i>
            </span>
            <div class="edit-profile-wrapper edit-profile-small form-control h-auto border-0 p-0">
                <div class="profile-image user-img edit-photo">
                    <img class="profile-image-preview" id="eventImagePreview" src="{{ ((!empty($event) && !empty($event->logo)) ? $event->logo : asset('assets/dist/img/placeholder-img.png')) }}"/>
                </div>
                <div class="edit-profile-avtar edit-profile-small">
                    {{ Form::file('logo', ['class' => 'edit-avatar', 'id' => 'logo', 'data-previewelement' => '#eventImagePreview', 'data-width' => config('zevolifesettings.imageConversions.event.logo.width'), 'data-height' => config('zevolifesettings.imageConversions.event.logo.height'), 'data-ratio' => config('zevolifesettings.imageAspectRatio.event.logo')]) }}
                    <u>
                        {{ trans('buttons.general.browse') }}
                    </u>
                </div>
            </div>
        </div>
        <div class="col-xxl-10 col-lg-9 col-md-8 col-12 order-1">
            <div class="row">
                <div class="col-sm-6">
                    <div class="form-group">
                        {{ Form::label('name', trans('labels.event.event_name')) }}
                        {{ Form::text('name', old('name', ($event->name ?? null)), ['class' => 'form-control', 'placeholder' => 'Enter name', 'id' => 'name', 'autocomplete' => 'off', 'disabled' => $fieldDisableStatus]) }}
                    </div>
                </div>
                @if($roleType == 'zsa' || $roleType == 'rsa')
                <div class="col-sm-6">
                    <div class="form-group">
                        {{ Form::label('fees', trans('labels.event.event_fees')) }}
                        <div class="input-group symbol-end">
                            <span class="input-group-text" id="fees-addon">
                                <i aria-hidden="true" class="fal fa-euro-sign">
                                </i>
                            </span>
                            {{ Form::text('fees', old('fees', (!empty($event->fees) ? $event->fees : null)), ['class' => 'form-control numeric', 'placeholder' => 'Enter fees', 'id' => 'fees', 'autocomplete' => 'off', 'aria-describedby' => 'fees-addon']) }}
                        </div>
                    </div>
                </div>
                @endif
                <div class="col-sm-12">
                    <div class="form-group">
                        {{ Form::label('description', trans('labels.event.description')) }}
                        {{ Form::textarea('description', old('description', (!empty($event) ? htmlspecialchars_decode($event->description) : null)), ['id' => 'description', 'cols' => 10, 'class' => 'form-control h-auto basic-format-ckeditor', 'placeholder' => 'Enter description', 'data-errplaceholder' => '#description-error', 'data-formid' => (($edit) ? '#eventEdit' : '#eventAdd')]) }}
                        <div id="description-error" style="display: none; width: 100%; margin-top: 0.25rem; font-size: 80%; color: #dc3545;">
                        </div>
                    </div>
                </div>
                {{-- @if(isset($event) && $event->is_special == true) --}}
                <div class="col-sm-6" id="special_event_category">
                    <div class="form-group">
                        {{ Form::label('subcategory', trans('labels.event.subcategory_name')) }}
                        {{ Form::text('specialEventCategoryTitle', old('special_event_category_title', ($event->special_event_category_title ?? null)), ['class' => 'form-control', 'id'=>'specialEventCategoryTitle',  'autocomplete' => 'off',]) }}
                    </div>
                </div>
                {{-- @endif
                @if(isset($event) && $event->is_special == false) --}}
                <div class="col-sm-6" id="main_subcategory">
                    <div class="form-group">
                        {{ Form::label('subcategory', trans('labels.event.subcategory_name')) }}
                        {{ Form::select('subcategory', $subcategory, old('subcategory', ($event->subcategory_id ?? null)), ['class' => 'form-control select2', 'id' => 'subcategory', 'placeholder' => 'Select category', 'data-placeholder' => 'Select category', 'disabled' => $fieldDisableStatus] ) }}
                    </div>
                </div>
                {{-- @endif --}}
                <div class="col-sm-6">
                    <div class="form-group">
                        {{ Form::label('location', trans('labels.event.location')) }}
                        {{ Form::select('location', config('zevolifesettings.event-location-type'), old('location', ($event->location_type ?? null)), ['class' => 'form-control select2', 'id' => 'location', 'data-allow-clear' => 'false',  'disabled' => $fieldDisableStatus] ) }}
                    </div>
                </div>
                @if(in_array($roleType, ['rca', 'zca']))
                <div class="col-sm-6">
                    <div class="form-group">
                        {{ Form::label('', trans('labels.event.company_visibility')) }}
                        {{ Form::text('', $company->name, ['class' => 'form-control', 'disabled' => true]) }}
                        {{ Form::hidden('company_visibility[]', $company->id) }}
                    </div>
                </div>
                @endif
                <div class="col-md-6">
                    <label class="custom-checkbox no-label">
                        {{ trans('labels.event.allow_feedback') }}
                        {{ Form::checkbox('is_csat', 'on', old('is_csat', ((isset($event) && $event->is_csat == true) || $edit == false)),['id' => 'is_csat']) }}
                        <span class="checkmark">
                        </span>
                        <span class="box-line">
                        </span>
                    </label>
                </div>
            </div>
        </div>
    </div>
</div>
@if(!in_array($roleType, ['rca', 'zca']))
<div class="card-inner">
    <h3 class="card-inner-title">
        Company Visibility
    </h3>
    <div>
        <div class="tree-multiselect-box" id="eventCompanyList">
            <select class="form-control" id="event_company" multiple="multiple" name="company_visibility[]" data-errplaceholder ='#event_company-error'>
                @foreach($company as $rolekey => $rolevalue)
                    @foreach($rolevalue['companies'] as $key => $value)
                        <option value="{{ $key }}" data-section="{{ $rolevalue['roleType'] }}"  {{ (!empty($eventCompanies) && in_array($key, $eventCompanies))? 'selected' : ''   }} >{{ $value }}</option>
                    @endforeach
                @endforeach
            </select>
            <span id="event_company-error" style="display: none; width: 100%; margin-top: 0.25rem; font-size: 80%; color: #dc3545;">
                {{ trans('labels.event.company_selection') }}
            </span>
        </div>
    </div>
</div>
@endif
<div class="card-inner">
    <h3 class="card-inner-title">
        Additional Information
    </h3>
    <div class="row">
        <div class="form-group col-md-6 event-form-duration-field">
            {{ Form::label('duration', trans('labels.event.duration')) }}
            {{ Form::text('duration', old('duration', ($event->duration ?? null)), ['class' => 'form-control', 'placeholder' => 'Select duration', 'id' => 'duration', 'disabled' => $fieldDisableStatus]) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('capacity', trans('labels.event.capacity')) }}
            {{ Form::text('capacity', old('capacity', ($event->capacity ?? null)), ['class' => 'form-control numeric', 'placeholder' => 'Enter capacity', 'id' => 'capacity', 'disabled' => $fieldCapacityDisableStatus]) }}
        </div>
        @if($roleType == 'zsa')
        <div class="form-group col-md-6" id="multiplepresenter">
            {{ Form::label('Presenter(s)', trans('labels.event.presenter')) }}
            {{ Form::select('presenter[]', ($eventPresenters ?? []), old('presenter[]', ($eventPresenterIds ?? [])), ['class' => 'form-control select2-multiple', 'id' => 'presenter', 'multiple' => true, 'data-placeholder' => 'Select presenters', 'data-type' => $roleType, 'disabled' => (($roleType == 'zsa' && !$edit) ? true : false)]) }}
            {{ Form::checkbox('select_all_presenters', null, false, ['class' => 'mt-2', 'id' => 'select_all_presenters', 'disabled' => (($roleType == 'zsa' && !$edit) ? true : false)]) }}
            {{ Form::label('select_all_presenters', 'Select all presenters', ['for' => 'select_all_presenters', 'class' => 'form-check-label']) }}
        </div>
        @endif

        <div class="col-xl-4 col-lg-6 specialevent" style="display:none">
            <div class="form-group">
                {{ Form::label('date', trans('marketplace.book_event.form.labels.date')) }}
                <div class="datepicker-wrap">
                    {{ Form::text('date', old('date'), ['class' => 'form-control bg-white', 'placeholder' => 'Select date', 'id' => 'date', 'readonly' => true]) }}
                </div>
            </div>
        </div>
        <div class="col-xl-8 col-lg-12 specialevent" style="display:none">
            <div class="row time-range">
                <div class="col-xl-6 col-lg-6">
                    <div class="form-group">
                        {{ Form::label('timeFrom', trans('marketplace.book_event.form.labels.time-from')) }}
                        {{ Form::text('timeFrom', old('timeFrom'), ['class' => 'form-control bg-white time start', 'id' => 'timeFrom', 'readonly' => true]) }}
                    </div>
                </div>
                <div class="col-xl-6 col-lg-6">
                    <div class="form-group pe-none">
                        {{ Form::label('timeTo', trans('marketplace.book_event.form.labels.time-to')) }}
                        {{ Form::text('timeTo', old('timeTo'), ['class' => 'form-control time end', 'id' => 'timeTo', 'readonly' => true]) }}
                    </div>
                </div>
            </div>
        </div>
        @if($roleType != 'zsa')
        <div class="col-sm-6">
            <div class="form-group">
                {{ Form::label('presenterName', trans('marketplace.book_event.form.labels.presenter_name')) }}
                {{ Form::text('presenterName', old('presenterName', ($event->presenter ?? null)), ['class' => 'form-control', 'id' => 'presenterName', 'placeholder' => trans('marketplace.book_event.form.placeholder.presenter_name')]) }}
            </div>
        </div>
        @endif
    </div>
</div>
@if($edit)
{{ Form::hidden('referrerEditInner', $referrerEditInner) }}
{{ Form::hidden('is_special', $event->is_special, ['id'=>'is_special']) }}
@else
{{ Form::hidden('is_special', 0, ['id'=>'is_special']) }}
@endif
