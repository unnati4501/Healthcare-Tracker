<input type="hidden" name="company_id" value="{{ $company_id }}">
<div class="col-xl-4">
    <div class="form-group">
        <label for="">{{trans('domain.form.labels.domain')}}</label>
        @if(!empty($domainData->domain))
            {{ Form::text('domain', old('domain',$domainData->domain), ['class' => 'form-control', 'placeholder' => trans('domain.form.placeholder.enter_domain_name'), 'id' => 'domain', 'autocomplete' => 'off']) }}
        @else
            {{ Form::text('domain', old('domain'), ['class' => 'form-control', 'placeholder' => trans('domain.form.placeholder.enter_domain_name'), 'id' => 'domain', 'autocomplete' => 'off']) }}
        @endif
    </div>
</div>