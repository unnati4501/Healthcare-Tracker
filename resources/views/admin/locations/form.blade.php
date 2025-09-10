@if($role->group == 'zevo' || ($role->group == 'reseller' && $user_company && $user_company->is_reseller == true))
<div class="col-md-4">
    <div class="form-group">
    {{ Form::label('company_name', trans('location.form.labels.company')) }}
    @if(!empty($locationData->company_id))
        {{ Form::select('company', $company, $locationData->company_id, ['class' => 'form-control select2','id'=>'company_id',"style"=>"width: 100%;", 'data-placeholder' => trans('location.form.placeholder.select_company'), 'placeholder' => trans('location.form.placeholder.select_company'), 'data-dependent' => 'multilocation', 'autocomplete' => 'off','disabled'=>'true'] ) }}
        <input type="hidden" name="company" value="{{$locationData->company_id}}">
    @else
        {{ Form::select('company', $company, null, ['class' => 'form-control select2','id'=>'company_id',"style"=>"width: 100%;", 'data-placeholder' => trans('location.form.placeholder.select_company'), 'placeholder' => trans('location.form.placeholder.select_company'), 'autocomplete' => 'off'] ) }}
    @endif
    </div>
</div>
@else
    <input type="hidden" name="company" value="{{ $user_company->id }}">
@endif
<div class="col-md-4">
    <div class="form-group">
    {{ Form::label('name', trans('location.form.labels.location_name')) }}
    @if(!empty($locationData->name))
        {{ Form::text('name', $locationData->name , ['class' => 'form-control', 'placeholder' => trans('location.form.placeholder.enter_location_name'), 'autocomplete' => 'off']) }}
    @else
        {{ Form::text('name', old('location_name') , ['class' => 'form-control', 'placeholder' => trans('location.form.placeholder.enter_location_name'), 'autocomplete' => 'off']) }}
    @endif
    </div>
</div>
<div class="col-md-4">
    <div class="form-group">
    {{ Form::label('address_line1', trans('location.form.labels.address_line1')) }}
    @if(!empty($locationData->address_line1))
        {{ Form::text('address_line1', $locationData->address_line1 , ['class' => 'form-control', 'placeholder' => trans('location.form.placeholder.enter_address_line1'), 'autocomplete' => 'off']) }}
    @else
        {{ Form::text('address_line1', old('address_line1') , ['class' => 'form-control', 'placeholder' => trans('location.form.placeholder.enter_address_line1'), 'autocomplete' => 'off']) }}
    @endif
    </div>
</div>
<div class="col-md-4">
    <div class="form-group">
    {{ Form::label('address_line2', trans('location.form.labels.address_line2')) }}
    @if(!empty($locationData->address_line2))
        {{ Form::text('address_line2', $locationData->address_line2 , ['class' => 'form-control', 'placeholder' => trans('location.form.placeholder.enter_address_line2'), 'autocomplete' => 'off']) }}
    @else
        {{ Form::text('address_line2', old('address_line2') , ['class' => 'form-control', 'placeholder' => trans('location.form.placeholder.enter_address_line2'), 'autocomplete' => 'off']) }}
    @endif
    </div>
</div>
<div class="col-md-4">
    <div class="form-group">
    {{ Form::label('country_id', trans('location.form.labels.country')) }}
    @if(!empty($locationData->country_id))
        {{ Form::select('country', $countries, $locationData->country_id, ['class' => 'form-control select2','id'=>'country_id',"style"=>"width: 100%;", 'placeholder' => trans('location.form.placeholder.select_country'), 'data-placeholder' => trans('location.form.placeholder.select_country'), 'data-dependent' => 'state_id', 'target-data' => 'timezone', 'autocomplete' => 'off'] ) }}
    @else
        {{ Form::select('country', $countries, null, ['class' => 'form-control select2','id'=>'country_id',"style"=>"width: 100%;", 'data-placeholder' => trans('location.form.placeholder.select_country'),'placeholder' => trans('location.form.placeholder.select_country'), 'data-dependent' => 'state_id', 'target-data' => 'timezone', 'autocomplete' => 'off'] ) }}
    @endif
    </div>
</div>
<div class="col-md-4">
    <div class="form-group">
    {{ Form::label('timezone', trans('location.form.labels.timezone')) }}

    @php
        $countryId =   (!empty($locationData->country_id)) ? $locationData->country_id : ((!empty(old('country_id'))) ? old('country_id') : "");
        $timezone =   (!empty($locationData->timezone)) ? $locationData->timezone : ((!empty(old('timezone'))) ? old('timezone') : "");
        $timezones = (!empty($countryId) && !empty($timezone)) ? getTimezones($countryId) : [];
    @endphp

    @if(!empty($countryId) && !empty($timezone))
        {{ Form::select('timezone', $timezones, $timezone, ['class' => 'form-control select2', 'id'=>'timezone', "style"=>"width: 100%;", 'placeholder' => trans('location.form.placeholder.select_timezone'), 'data-placeholder' => trans('location.form.placeholder.select_timezone'), 'autocomplete' => 'off'] ) }}
    @else
        {{ Form::select('timezone', [], null, ['class' => 'form-control select2', 'id'=>'timezone', "style"=>"width: 100%;", 'placeholder' => trans('location.form.placeholder.select_timezone'), 'data-placeholder' => trans('location.form.placeholder.select_timezone'), 'disabled', true, 'autocomplete' => 'off'] ) }}
    @endif
    </div>
</div>
<div class="col-md-4">
    <div class="form-group">
    {{ Form::label('state_id', trans('location.form.labels.county')) }}

    @php
        $countryId =   (!empty($locationData->country_id)) ? $locationData->country_id : ((!empty(old('country_id'))) ? old('country_id') : "");
        $stateId =   (!empty($locationData->state_id)) ? $locationData->state_id : ((!empty(old('state_id'))) ? old('state_id') : "");
        $states = (!empty($countryId) && !empty($stateId)) ? getStates($countryId) : [];
    @endphp

    @if(!empty($countryId) && !empty($stateId))
        {{ Form::select('county', $states, $stateId, ['class' => 'form-control select2', 'id'=>'state_id', "style"=>"width: 100%;", 'placeholder' => trans('location.form.placeholder.select_county'), 'data-placeholder' => trans('location.form.placeholder.select_county'), 'autocomplete' => 'off'] ) }}
    @else
        {{ Form::select('county', [], null, ['class' => 'form-control select2', 'id'=>'state_id', "style"=>"width: 100%;", 'placeholder' => trans('location.form.placeholder.select_county'), 'data-placeholder' => trans('location.form.placeholder.select_county'), 'disabled', true, 'autocomplete' => 'off'] ) }}
    @endif
    </div>
</div>
<div class="col-md-4">
    <div class="form-group">
    {{ Form::label('postal_code', trans('location.form.labels.postal_code')) }}
    @if(!empty($locationData->postal_code))
        {{ Form::text('postal_code', $locationData->postal_code , ['class' => 'form-control', 'placeholder' => trans('location.form.placeholder.enter_postal_code'), 'autocomplete' => 'off']) }}
    @else
        {{ Form::text('postal_code', old('postal_code') , ['class' => 'form-control', 'placeholder' => trans('location.form.placeholder.enter_postal_code'), 'autocomplete' => 'off']) }}
    @endif
    </div>
</div>