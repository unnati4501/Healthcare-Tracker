<div class="card-inner">
    <div class="row">
        <div class="col-lg-6 col-xl-4">
            <div class="form-group">
                {{ Form::label('logo', trans('eap.form.labels.logo')) }}
                <span class="font-16 qus-sign-tooltip" data-toggle="help-tooltip" title="{{ getHelpTooltipText('eap.logo') }}">
                    <i aria-hidden="true" class="far fa-info-circle text-primary">
                    </i>
                </span>
                <div class="custom-file custom-file-preview">
                    <label class="file-preview-img" for="profileImage">
                        <img height="200" id="previewImg" src="{{ ($eap->logo ?? asset('assets/dist/img/boxed-bg.png')) }}" width="200"/>
                    </label>
                    {{ Form::file('logo', ['class' => 'custom-file-input form-control', 'id' => 'logo','data-width' => config('zevolifesettings.imageConversions.eap.logo.width'), 'data-height' => config('zevolifesettings.imageConversions.eap.logo.height'), 'data-ratio' => config('zevolifesettings.imageAspectRatio.eap.logo')]) }}
                    <label class="custom-file-label" for="logo">
                        {{ trans($media->name ?? 'eap.form.placeholder.choose_file') }}
                    </label>
                </div>
            </div>
        </div>
        <div class="col-lg-6 col-xl-4">
            <div class="form-group">
                {{ Form::label('title', trans('eap.form.labels.title')) }}
                {{ Form::text('title', old('title', ($eap->title ?? null)), ['class' => 'form-control', 'placeholder' => trans('eap.form.placeholder.title_placeholder'), 'id' => 'title', 'autocomplete' => 'off']) }}
            </div>
        </div>
        <div class="col-lg-6 col-xl-4">
            <div class="form-group">
                {{ Form::label('telephone', trans('eap.form.labels.telephone')) }}
                <div class="input-group">
                    <span class="input-group-text">
                        +
                    </span>
                    {{ Form::text('telephone', old('telephone', ($eap->telephone ?? null)), ['class' => 'form-control numeric', 'placeholder' => trans('eap.form.placeholder.telephone_placeholder'), 'id' => 'telephone', 'autocomplete' => 'off']) }}
                </div>
            </div>
        </div>
        <div class="col-lg-6 col-xl-4">
            <div class="form-group">
                {{ Form::label('email', trans('eap.form.labels.email')) }}
                {{ Form::email('email', old('email', ($eap->email ?? null)), ['class' => 'form-control', 'placeholder' => trans('eap.form.placeholder.email_placeholder'), 'id' => 'email', 'autocomplete' => 'off']) }}
            </div>
        </div>
        <div class="col-lg-6 col-xl-4">
            <div class="form-group">
                {{ Form::label('website', trans('eap.form.labels.website')) }}
                {{Form::url('website', old('website', ($eap->website ?? null)), ['class' => 'form-control', 'placeholder' => trans('eap.form.placeholder.website_placeholder'), 'id' => 'website', 'autocomplete' => 'off', 'pattern' => "https?://.+"]) }}
            </div>
        </div>
        <div class="col-lg-12 col-xl-12">
            <div class="form-group">
                {{ Form::label('description', trans('labels.eap.description')) }}
                {{ Form::textarea('description', old('description', (isset($eap->description) ? htmlspecialchars_decode($eap->description) : null)), ['class' => 'form-control basic-format-ckeditor', 'id' => 'description', 'data-errplaceholder' => '#description-error-cstm', 'data-formid' => (($edit) ? "#EAPEdit" : "#EAPAdd")]) }}
                <div id="description-error-cstm">
                </div>
                <span id="description-error" style="display: none; width: 100%; margin-top: 0.25rem; font-size: 80%; color: #f44436;">
                    {{ trans('eap.validation.description') }}
                </span>
            </div>
        </div>
        @if($loggedInUserRole->group == 'company' || ($loggedInUserRole->group == 'reseller' &&  $companyData->parent_id != null))
            @if(!empty($eap))
                <div class="col-lg-6 col-xl-6">
                    <div class="form-group">
                        {{ Form::label('locations', trans('challenges.form.labels.location')) }}
                        {{ Form::select('locations[]', $companyLocations, old('locations[]', explode(',', $eap->locations)), ['class' => 'form-control select2','id'=>'locations',"style"=>"width: 100%;", 'autocomplete' => 'off', 'multiple' => true] ) }}
                    </div>
                </div>
                <div class="col-lg-6 col-xl-6">
                    <div class="form-group">
                        {{ Form::label('department', trans('challenges.form.labels.department')) }}
                        {{ Form::select('department[]', $companyDepartment, old('department[]', explode(',', $eap->departments)), ['class' => 'form-control select2','id'=>'department',"style"=>"width: 100%;", 'autocomplete' => 'off', 'multiple' => true] ) }}
                    </div>
                </div>
                @else
                <div class="col-lg-6 col-xl-6">
                    <div class="form-group">
                        {{ Form::label('locations', trans('challenges.form.labels.location')) }}
                        {{ Form::select('locations[]', $companyLocations, old('locations[]'), ['class' => 'form-control select2','id'=>'locations',"style"=>"width: 100%;", 'multiple' => true, 'autocomplete' => 'off'] ) }}
                    </div>
                </div>
                <div class="col-lg-6 col-xl-6">
                    <div class="form-group">
                        {{ Form::label('department', trans('challenges.form.labels.department')) }}
                        {{ Form::select('department[]', $companyDepartment, old('department[]'), ['class' => 'form-control select2','id'=>'department',"style"=>"width: 100%;", "disabled" => true, 'multiple' => true, 'autocomplete' => 'off'] ) }}
                    </div>
                </div>
            @endif
        @endif
    </div>
</div>
@if($isSA)
<div class="card-inner">
    <h3 class="card-inner-title">
        {{ trans('eap.form.labels.company_visibility') }}
    </h3>
    <div class="row">
        <div class="col-lg-12">
            <div class="form-group mb-0">
                <div class="tree-multiselect-box" id="setPermissionList">
                    <select class="form-control ignore-validation" id="eap_company" multiple="multiple" name="eap_company[]">
                        @foreach($company as $rolekey => $rolevalue)
                            @foreach($rolevalue['companies'] as $key => $value)
                                @foreach($value['location'] as $locationKey => $locationValue)
                                    @foreach($locationValue['department'] as $departmentKey => $departmentValue)
                                        <option value="{{ $departmentValue['id'] .'@@@'.$locationValue['locationId']}}" data-section="{{ $rolevalue['roleType'] }}/{{$value['companyName']}}/{{$locationValue['locationName']}}" {{ (!empty($eap_companys) && in_array($departmentValue['id'], $eap_companys) && in_array($locationValue['locationId'], $eap_locations))? 'selected' : ''   }} >{{ $departmentValue['name'] }}</option>
                                    @endforeach
                                @endforeach
                            @endforeach
                        @endforeach
                    </select>
                    <span id="eap_company-error" style="display: none; width: 100%; margin-top: 0.25rem; font-size: 80%; color: #f44436;">
                        {{ trans('eap.validation.company_selection') }}
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>
@endif
