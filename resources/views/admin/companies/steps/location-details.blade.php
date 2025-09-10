<div class="row">
    <div class="col-lg-6 col-xl-4">
        <div class="form-group">
            {{ Form::label('location_name', trans('labels.company.location_name')) }}
            {{ Form::text('location_name', old('location_name', ($companyLocData->name ?? null)) , ['class' => 'form-control', 'placeholder' => 'Enter Location Name', 'autocomplete' => 'off']) }}
        </div>
    </div>
    <div class="col-lg-6 col-xl-4">
        <div class="form-group">
            {{ Form::label('postal_code', trans('labels.company.postal_code')) }}
            {{ Form::text('postal_code', old('postal_code', ($companyLocData->postal_code ?? null)) , ['class' => 'form-control', 'placeholder' => 'Enter Postal Code', 'autocomplete' => 'off']) }}
        </div>
    </div>
    <div class="col-lg-6 col-xl-4">
        <div class="form-group">
            {{ Form::label('address_line1', trans('labels.company.address_line1')) }}
            {{ Form::text('address_line1', old('address_line1', ($companyLocData->address_line1 ?? null)) , ['class' => 'form-control', 'placeholder' => 'Enter Address Line1', 'autocomplete' => 'off']) }}
        </div>
    </div>
    <div class="col-lg-6 col-xl-4">
        <div class="form-group">
            {{ Form::label('address_line2', trans('labels.company.address_line2')) }}
            {{ Form::text('address_line2', old('address_line2', ($companyLocData->address_line2 ?? null)) , ['class' => 'form-control', 'placeholder' => 'Enter Address Line2', 'autocomplete' => 'off']) }}
        </div>
    </div>
    <div class="col-lg-6 col-xl-4">
        <div class="form-group">
            {{ Form::label('country', trans('labels.company.country_id')) }}
            {{ Form::select('country', $countries, old('country', ($companyLocData->country_id ?? null)), ['class' => 'form-control select2', 'id' => 'country_id', 'placeholder' => 'Select country', 'data-placeholder' => 'Select country', 'data-dependent' => 'state_id', 'target-data' => 'timezone', 'data-allow-clear' => 'true'] ) }}
        </div>
    </div>
    <div class="col-lg-6 col-xl-4">
        <div class="form-group">
            {{ Form::label('county', trans('labels.company.state_id')) }}
            @php
                $countryId =   (!empty($companyLocData->country_id)) ? $companyLocData->country_id : ((!empty(old('country'))) ? old('country') : "");
                $stateId =   (!empty($companyLocData->state_id)) ? $companyLocData->state_id : ((!empty(old('county'))) ? old('county') : "");
                $states = (!empty($countryId) && !empty($stateId)) ? getStates($countryId) : [];
            @endphp
            {{ Form::select('county', ($states ?? []), old('county', ($stateId ?? null)), ['class' => 'form-control select2', 'id' => 'state_id', 'placeholder' => 'Select county', 'data-placeholder' => 'Select county', 'data-allow-clear' => 'true']) }}
        </div>
    </div>
    <div class="col-lg-6 col-xl-4">
        <div class="form-group">
            {{ Form::label('timezone', trans('labels.company.timezone')) }}
            @php
                $countryId =   (!empty($companyLocData->country_id)) ? $companyLocData->country_id : ((!empty(old('country'))) ? old('country') : "");
                $timezone =   (!empty($companyLocData->timezone)) ? $companyLocData->timezone : ((!empty(old('timezone'))) ? old('timezone') : "");
                $timezones = (!empty($countryId) && !empty($timezone)) ? getTimezones($countryId) : [];
            @endphp
            {{ Form::select('timezone', ($timezones ?? []), old('timezone', ($timezone ?? null)), ['class' => 'form-control select2', 'id' => 'timezone', 'placeholder' => 'Select timezone', 'data-placeholder' => 'Select timezone']) }}
        </div>
    </div>
</div>