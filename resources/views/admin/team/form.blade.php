<div class="col-auto basic-file-upload">
    <div class="edit-profile-wrapper">
        <label>
            {{ trans('team.form.labels.logo') }}
            <span class="font-16 qus-sign-tooltip" data-toggle="help-tooltip" title="{{ getHelpTooltipText('team.logo') }}">
                <i aria-hidden="true" class="far fa-info-circle text-primary">
                </i>
            </span>
        </label>
        <div class="edit-profile-wrapper form-control h-auto border-0 p-0">
            <div class="profile-image user-img edit-photo">
                <img class="profile-image-preview" title="" height="200" id="previewImg"  src="{{ (!empty($teamData->logo) ? $teamData->logo : asset('assets/dist/img/placeholder-img.png')) }}" width="200"/>
            </div>
            <div class="edit-profile-avtar">
                {{ Form::file('logo', ['class' => 'edit-avatar', 'title' => '','id' => 'profileImage', 'data-width' => config('zevolifesettings.imageConversions.team.logo.width'), 'data-height' => config('zevolifesettings.imageConversions.team.logo.height'), 'data-ratio' => config('zevolifesettings.imageAspectRatio.team.logo'), 'data-previewelement' => 'previewImg', 'autocomplete' => 'off'])}}
                <u>{{ trans('buttons.general.browse') }}</u>
            </div>
        </div>
    </div>

    <div class="col-md-12 mt-3">
        <div class="form-group">
            @if(!empty($teamData->code))
                <div class="callout">
                    <div class="m-0">
                        {{trans('labels.team.team_code')}}:
                        <div class="fw-bold">
                            {{ $teamData->code }}
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
<div class="col-xxl-9 col-md-8">
    <div class="row">
        @if($role->group == 'zevo' || ($role->group == 'reseller' && $companiesDetails->parent_id == null))
            <div class="col-md-6">
                <div class="form-group">
                    {{ Form::label('company_name', trans('team.form.labels.company')) }}
                    @if(!empty($teamData->company_id))
                        {{ Form::select('company', $company, $teamData->company_id, ['class' => 'form-control select2','id'=>'company_id',"style"=>"width: 100%;", 'placeholder' => trans('team.form.placeholder.select_company'), 'data-placeholder' => trans('team.form.placeholder.select_company'), 'data-dependent' => 'department_id', 'autocomplete' => 'off','disabled'=>'true'] ) }}
                        <input type="hidden" name="company" value="{{$teamData->company_id}}">
                    @else
                        {{ Form::select('company', $company, old('company'), ['class' => 'form-control select2','id'=>'company_id',"style"=>"width: 100%;", 'placeholder' => trans('team.form.placeholder.select_company'), 'data-placeholder' => trans('team.form.placeholder.select_company'), 'data-dependent' => 'department_id', 'autocomplete' => 'off'] ) }}
                    @endif
                </div>
            </div>
        @else
            <input type="hidden" name="company" value="{{$companiesDetails->id}}">
        @endif
        <div class="col-md-6">
            <div class="form-group">
                <label for="">{{trans('team.form.labels.team_name')}}</label>
                @if(!empty($teamData->name))
                    {{ Form::text('name', old('name',$teamData->name), ['class' => 'form-control', 'placeholder' => trans('team.form.placeholder.enter_team_name'), 'data-placeholder' => trans('team.form.placeholder.enter_team_name'), 'id' => 'name', 'autocomplete' => 'off']) }}
                @else
                    {{ Form::text('name', old('name'), ['class' => 'form-control', 'placeholder' => trans('team.form.placeholder.enter_team_name'), 'data-placeholder' => trans('team.form.placeholder.enter_team_name'), 'id' => 'name', 'autocomplete' => 'off']) }}
                @endif
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
            {{ Form::label('department_name', trans('team.form.labels.department')) }}
            @if(!empty($teamData->department_id))
                {{ Form::select('department', $department, $teamData->department_id, ['class' => 'form-control select2','id'=>'department_id',"style"=>"width: 100%;", 'placeholder' => trans('team.form.placeholder.select_department'), 'data-placeholder' => trans('team.form.placeholder.select_department'), 'data-dependent' => 'teamlocation', 'autocomplete' => 'off','disabled'=>'true'] ) }}
                <input type="hidden" name="department" value="{{$teamData->department_id}}">
            @else
                @if($role->group == 'zevo' || ($role->group == 'reseller' && $companiesDetails->parent_id == null))
                    {{ Form::select('department', $department, old('department'), ['class' => 'form-control select2','id'=>'department_id',"style"=>"width: 100%;", 'placeholder' => trans('team.form.placeholder.select_department'), 'data-placeholder' => trans('team.form.placeholder.select_department'), 'data-dependent' => 'teamlocation', 'autocomplete' => 'off','disabled'=>'true'] ) }}
                @else
                    {{ Form::select('department', $department, old('department'), ['class' => 'form-control select2','id'=>'department_id',"style"=>"width: 100%;", 'placeholder' => trans('team.form.placeholder.select_department'), 'data-placeholder' => trans('team.form.placeholder.select_department'), 'data-dependent' => 'teamlocation', 'autocomplete' => 'off'] ) }}
                @endif
            @endif
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
            {{ Form::label('location_name', trans('team.form.labels.location')) }}
            @if(!empty($teamData->teamlocation) && !empty($teamData->teamlocation[0]->id))
                {{ Form::select('teamlocation', $location, $teamData->teamlocation[0]->id, ['class' => 'form-control select2','id'=>'teamlocation',"style"=>"width: 100%;", 'placeholder' => trans('team.form.placeholder.select_location'), 'data-placeholder' => trans('team.form.placeholder.select_location'), 'autocomplete' => 'off','disabled'=>'true'] ) }}
                <input type="hidden" name="teamlocation" value="{{$teamData->teamlocation[0]->id}}">
            @else
                {{ Form::select('teamlocation', $location, old('teamlocation'), ['class' => 'form-control select2','id'=>'teamlocation',"style"=>"width: 100%;", 'placeholder' => trans('team.form.placeholder.select_location'), 'data-placeholder' => trans('team.form.placeholder.select_location'), 'autocomplete' => 'off','disabled'=>'true'] ) }}
            @endif
            </div>
        </div>
    </div>
</div>
