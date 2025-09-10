{{ Form::hidden('role_slug', $loggedInUser->slug) }}
@if(($loggedInUser->slug == 'super_admin' && $loggedInUser->group == 'zevo') || ($loggedInUser->slug == 'company_admin' && $loggedInUser->group == 'company'))
<div class="card-inner">
    <div class="row">
        <div class="col-lg-6 col-xl-6">
            @if($role->group == 'zevo')
            <div class="form-group">
                {{ Form::label('role_group', trans('labels.user.role_group')) }}
                    @if($edit)
                    {{ Form::hidden('role_group', $role->group) }}
                    @endif
                <div>
                    @foreach($roleGroupData as $roleKey => $roleName)
                    <label class="custom-radio" for="role_group_{{ $roleKey }}">
                        {{ ucwords($roleName) }}
                        {{ Form::radio('role_group', $roleKey, ((old('role_group', false) && old('role_group') == $roleKey) ? true : (($roleKey == 'zevo') ? true : false)), ['class' => 'form-control', 'id' => 'role_group_' . $roleKey, 'disabled' => $edit]) }}
                        <span class="checkmark">
                        </span>
                        <span class="box-line">
                        </span>
                    </label>
                    @endforeach
                </div>
            </div>
            @else
            <div class="form-group">
                {{ Form::label('role_group', trans('labels.user.role_group')) }}
                {{ Form::hidden('role_group', $role->group) }}
                <div>
                    <label class="custom-radio" for="role_group_{{ $role->id }}">
                        {{ ucwords($role->group) }}
                            {{ Form::radio('role_group', $role->id, true, ['class' => 'form-control', 'id' => 'role_group_' . $role->id, 'disabled' => true]) }}
                        <span class="checkmark">
                        </span>
                        <span class="box-line">
                        </span>
                    </label>
                </div>
            </div>
            @endif
        </div>
        <div class="col-lg-6 col-xl-6" data-health-coach-wrapper="" style="display: {{ ((old('role_group', ($roleData->group ?? 'zevo')) == 'zevo') ? 'block' : 'none') }};">
            @if(!$edit)
                @if($role->group == 'zevo')
            <div class="form-group">
                {{ Form::label('user_type', trans('labels.user.user_type')) }}
                    {{ Form::select('user_type', $userTypes, old('user_type', (($edit && ($record->is_coach == true)) ? 'health_coach' : 'user')), ['class' => 'form-control select2', 'id' => 'user_type', 'data-allow-clear' => 'false']) }}
            </div>
            @else
                    {{ Form::hidden('user_type', 'user') }}
                @endif
            @else
                @if($role->group == 'zevo')
            <div class="form-group">
                {{ Form::label('user_type', trans('labels.user.user_type')) }}
                        {{ Form::select('user_type', $userTypes, old('user_type', $role->slug), ['class' => 'form-control select2', 'id' => 'user_type', 'data-allow-clear' => 'false', 'disabled' => true]) }}
            </div>
            @endif
                {{ Form::hidden('user_type', $role->slug) }}
            @endif
        </div>
    </div>
</div>
@elseif(($loggedInUser->slug == 'reseller_super_admin' && $loggedInUser->group == 'reseller') || ($loggedInUser->slug == 'reseller_company_admin' && $loggedInUser->group == 'reseller'))
    @if(!$edit)
        {{ Form::hidden('user_type', 'user') }}
    @else
        {{ Form::hidden('user_type', $role->slug) }}
    @endif
    {{ Form::hidden('role_group', $role->group) }}
    @if($role->group != 'reseller')
<div class="card-inner">
    <div class="row">
        <div class="col-lg-6 col-xl-6">
            <div class="form-group">
                {{ Form::label('role_group', trans('labels.user.role_group')) }}
                <div>
                    <label class="custom-radio" for="role_group_{{ $role->id }}">
                        {{ ucwords($role->group) }}
                                    {{ Form::radio('role_group', $role->id, true, ['class' => 'form-control', 'id' => 'role_group_' . $role->id, 'disabled' => true]) }}
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
@endif
@endif
@php
    $displayStyle = 'none';
    if((!empty(old('role_group')) && (old('role_group') == 'company' || old('role_group') == 'reseller')) || (!empty($roleData) && ($roleData->group == 'company' || $roleData->group == 'reseller')) || (!empty($record) && $record->can_access_app == true) || (!empty($role) && ($role->group ==  'company' || $role->group ==  'reseller'))) {
        $displayStyle = 'block';
    }
@endphp
<div class="card-inner" id="company_wrapper" style="display: {{ $displayStyle }};">
    <h3 class="card-inner-title">
        Company Details
    </h3>
    <div class="row">
        @if(!$edit)
        <div class="col-xxl-12 col-md-4">
            <div class="form-group">
                {{ Form::label('company', trans('labels.user.company')) }}
                @if(!empty($company))
                    {{ Form::select('company', $companies, $company->company_id, ['class' => 'form-control select2', 'id' => 'company', 'placeholder' => 'Select Company', 'data-placeholder' => 'Select Company', 'disabled' => true] ) }}
                {{ Form::hidden('company', $company->company_id) }}
                @else
                    {{ Form::select('company', $companies, null, ['class' => 'form-control select2', 'id' => 'company', 'placeholder' => 'Select Company', 'data-placeholder' => 'Select Company', 'data-dependent' => 'department'] ) }}
                @endif
            </div>
        </div>
        @if($teamSection)
        <div class="col-xxl-12 col-md-4">
            <div class="form-group">
                {{ Form::label('department', trans('labels.user.department')) }}
                @php
                    $companyId =   (!empty($company->company_id)) ? $company->company_id : ((!empty(old('company'))) ? old('company') : "");
                    $departmentId =   (!empty($company->department_id)) ? $company->department_id : ((!empty(old('department'))) ? old('department') : "");
                    $departments = (!empty($companyId) && !empty($departmentId)) ? getDepartments($companyId) : [];
                @endphp

                @if(!empty($companyId) && !empty($departmentId))
                    {{ Form::select('department', $departments, null, ['class' => 'form-control select2', 'id' => 'department', 'data-dependent' => 'team', 'placeholder' => 'Select Department', 'data-placeholder' => 'Select Department'] ) }}
                @else
                    {{ Form::select('department', [], null, ['class' => 'form-control select2', 'id' => 'department', 'placeholder' => 'Select Department', 'data-placeholder' => 'Select Department', 'data-dependent' => 'team', 'disabled' => true] ) }}
                @endif
            </div>
        </div>
        <div class="col-xxl-12 col-md-4">
            <div class="form-group">
                {{ Form::label('team', trans('labels.user.team')) }}
                @php
                    $departmentId =   (!empty($company->department_id)) ? $company->department_id : ((!empty(old('department'))) ? old('department') : "");
                    $teamId =   (!empty($company->id)) ? $company->id : ((!empty(old('team'))) ? old('team') : "");
                    $teams = (!empty($departmentId) && !empty($teamId)) ? getLimitWiseTeams($departmentId) : [];
                @endphp

                @if(!empty($departmentId) && !empty($teamId))
                    {{ Form::select('team', $teams, null, ['class' => 'form-control select2', 'id' => 'team', 'placeholder' => 'Select Team', 'data-placeholder' => 'Select Team', 'disabled' => true] ) }}
                @else
                    {{ Form::select('team', [], null, ['class' => 'form-control select2', 'id' => 'team', 'placeholder' => 'Select  team', 'data-placeholder' => 'Select Team', 'disabled' => true] ) }}
                @endif
            </div>
        </div>
        @endif
        @else
        @if(!empty($company))
        <div class="col-xxl-12 col-md-4">
            <div class="form-group">
                {{ Form::label('company', trans('labels.user.company')) }}
                @if($teamSection)
                {{ Form::select('', $companies, $company->company_id, ['class' => 'form-control select2', 'id' => 'company', 'placeholder' => 'Select Company', 'data-placeholder' => 'Select Company', 'disabled' => true] ) }}
                {{ Form::hidden('company', $company->company_id) }}
                @else
                {{ Form::select('', $companies, $userCompany->id, ['class' => 'form-control select2', 'id' => 'company', 'placeholder' => 'Select Company', 'data-placeholder' => 'Select Company', 'disabled' => true] ) }}
                {{ Form::hidden('company', $userCompany->id) }}
                @endif
            </div>
        </div>
        @if($teamSection)
        <div class="col-xxl-12 col-md-4">
            <div class="form-group">
                {{ Form::label('department', trans('labels.user.department')) }}
                @php
                    $companyId =   (!empty($company->company_id)) ? $company->company_id : ((!empty(old('company'))) ? old('company') : "");
                    $departmentId =   (!empty($company->department_id)) ? $company->department_id : ((!empty(old('department'))) ? old('department') : "");
                    $departments = (!empty($companyId) && !empty($departmentId)) ? getDepartments($companyId) : [];
                @endphp
                {{ Form::select('department', $departments, $departmentId, ['class' => 'form-control select2', 'id' => 'department', 'data-dependent' => 'team', 'placeholder' => 'Select Department', 'data-placeholder' => 'Select Department'] ) }}
            </div>
        </div>
        <div class="col-xxl-12 col-md-4">
            <div class="form-group">
                {{ Form::label('team', trans('labels.user.team')) }}
                @php
                    $departmentId =   (!empty($company->department_id)) ? $company->department_id : ((!empty(old('department'))) ? old('department') : "");
                    $teamId =   (!empty($company->id)) ? $company->id : ((!empty(old('team'))) ? old('team') : $currTeam);
                    $teams = (!empty($departmentId) && !empty($teamId)) ? getLimitWiseTeams($departmentId, $currTeam) : [];
                @endphp
                {{ Form::select('team', $teams, $teamId, ['class' => 'form-control select2', 'id' => 'team', 'placeholder' => 'Select Team', 'data-placeholder' => 'Select Team'] ) }}
            </div>
        </div>
        @endif
        @endif
        @endif
    </div>
</div>
<div class="card-inner">
    <h3 class="card-inner-title">
        Basic Details
    </h3>
    <div class="row justify-content-center justify-content-md-start">
        <div class="col-xxl-2 col-lg-3 col-md-4 basic-file-upload order-md-2">
            <label>
                Profile Pic
                <span class="font-16 qus-sign-tooltip" data-toggle="help-tooltip" title="{{ getHelpTooltipText('user.logo') }}">
                    <i aria-hidden="true" class="far fa-info-circle text-primary">
                    </i>
                </span>
            </label>
            <div class="edit-profile-wrapper edit-profile-small form-control h-auto border-0 p-0">
                <div class="profile-image user-img edit-photo">
                    <img class="profile-image-preview" id="previewImg" src="{{ (!empty($record->logo) ? $record->logo : asset('assets/dist/img/placeholder-img.png')) }}"/>
                </div>
                <div class="edit-profile-avtar">
                    {{ Form::file('logo', ['class' => 'edit-avatar', 'id' => 'profileImage', 'data-width' => config('zevolifesettings.imageConversions.user.logo.width'), 'data-height' => config('zevolifesettings.imageConversions.user.logo.height'), 'data-ratio' => config('zevolifesettings.imageAspectRatio.user.logo'), 'title' => ' ', 'data-previewelement' => '#previewImg']) }}
                    <u>
                        {{ trans('buttons.general.browse') }}
                    </u>
                </div>
            </div>
        </div>
        <div class="col-xxl-10 col-lg-9 col-md-8 col-12 order-1">
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        {{ Form::label('role', trans('labels.user.role')) }}
                        {{ Form::select('role', $companyRoles, old('role', (!empty($roleData) ? $roleData->id : null)), ['class' => 'form-control select2', 'id' => 'role', 'placeholder' => 'Select Role', 'data-placeholder' => 'Select Role', 'disabled' => (($edit) ? $role->slug == 'health_coach' || $role->slug == 'wellbeing_specialist' || $role->slug == 'counsellor' || $role->slug == 'wellbeing_team_lead' : (empty($companyRoles) ? true : false))]) }}
                        <input id="companyRolesarr" name="companyRolesarr" type="hidden" value="{{urlencode(json_encode($companyRoles))}}"/>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        {{ Form::label('first_name', trans('labels.user.first_name')) }}
                        {{ Form::text('first_name', old('first_name', ($record->first_name ?? null)), ['class' => 'form-control', 'placeholder' => 'Enter First Name', 'id' => 'first_name', 'autocomplete' => 'off']) }}
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        {{ Form::label('last_name', trans('labels.user.last_name')) }}
                        {{ Form::text('last_name', old('last_name', ($record->last_name ?? null)), ['class' => 'form-control', 'placeholder' => 'Enter Last Name', 'id' => 'last_name', 'autocomplete' => 'off']) }}
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="form-group">
                        {{ Form::label('email', trans('labels.user.email')) }}
                        {{ Form::text('email', old('email', ($record->email ?? null)), ['class' => 'form-control', 'placeholder' => 'Enter Email', 'id' => 'email', 'autocomplete' => 'off', 'disabled' => ($edit && $emaildisabled)]) }}
                    </div>
                </div>
                <div class="w-100">
                </div>
                @if($role->group == 'zevo' || ($role->group == 'zevo' && $edit && $record->user_type == 'counsellor'))
                <div class="col-md-6 counsellor_skills_section" style="display: none">
                    <div class="form-group">
                        {{ Form::label('counsellor_skills', trans('user.form.labels.counsellor_skills')) }}
                        {{ Form::select('counsellor_skills[]', $skills, ($userSkills ?? []), ['class' => 'form-control select2', 'id' => 'counsellor_skills', 'multiple' => true, 'placeholder' => '', 'data-placeholder' => trans('user.form.placeholder.select_counsellor_skills'), 'data-close-on-select' => 'false', 'data-allow-clear' => 'false']) }}
                    </div>
                </div>
                <div class="col-md-6 counsellor_skills_section cover_picture" style="display: none">
                    <div class="form-group">
                        {{ Form::label('counsellor_cover', trans('user.form.labels.counsellor_cover')) }}
                        <span class="font-16 qus-sign-tooltip" data-toggle="help-tooltip" title="{{ getHelpTooltipText('user.counsellor_cover') }}">
                            <i aria-hidden="true" class="far fa-info-circle text-primary">
                            </i>
                        </span>
                        <div class="custom-file custom-file-preview">
                            {{ Form::file('counsellor_cover', ['class' => 'custom-file-input form-control', 'id' => 'counsellor_cover', 'data-width' => config('zevolifesettings.imageConversions.user.counsellor_cover.width'), 'data-height' => config('zevolifesettings.imageConversions.user.counsellor_cover.height'), 'data-ratio' => config('zevolifesettings.imageAspectRatio.user.counsellor_cover'), 'data-previewelement' => '#counsellor_cover_preview', 'accept' => 'image/jpg,image/jpeg,image/png'])}}
                            <label class="file-preview-img" for="counsellor_cover_preview">
                                <img id="counsellor_cover_preview" src="{{ ($record->counsellor_cover ?? asset('assets/dist/img/boxed-bg.png')) }}" width="200"/>
                            </label>
                            {{ Form::label('counsellor_cover', ((!empty($record) && !empty($record->getFirstMediaUrl('counsellor_cover'))) ? $record->getFirstMedia('counsellor_cover')->name : trans('user.form.placeholder.choose_file')), ["class" => "custom-file-label"]) }}
                        </div>
                    </div>
                </div>
                @endif
                @if($edit && $role->slug != 'user')
                <div class="col-md-8">
                    <div class="reset-password-block">
                        {{ Form::label(null, trans('labels.user.reset_password')) }}
                        <a class="ms-3" href="{{ route('admin.users.resetpassword', $record->id) }}">
                            Reset
                        </a>
                        <p class="mb-0">
                            We will send you a link to the registered email address.
                        </p>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
<div class="card-inner">
    <h3 class="card-inner-title">
        Additional Information
    </h3>
    <div class="row">
        @if(!$edit)
        <div class="col-md-3 dateofbirth">
            <div class="form-group">
                {{ Form::label('date_of_birth', trans('labels.user.date_of_birth')) }}
                <div class="datepicker-wrap">
                    {{ Form::text('date_of_birth', old('date_of_birth', ($profileData->birth_date ?? '1990-01-01')), ['class' => 'form-control datepicker', 'id' => 'date_of_birth', 'autocomplete'=>'off', 'readonly' => true, 'placeholder' => 'Select date of birth']) }}
                    <i class="far fa-calendar">
                    </i>
                </div>
            </div>
        </div>
        @endif
        @if($role->slug == 'wellbeing_team_lead' || !$edit || ($loggedInUser->group == 'zevo' && $loggedInUser->slug == 'super_admin'))
        <div class="col-md-3 gendar">
            <div class="form-group">
                {{ Form::label('gender', trans('labels.user.gender')) }}
                {{ Form::select('gender', $gender, old('gender', ($profileData->gender ?? 'none')), ['class' => 'form-control select2', 'id' => 'gender', 'placeholder' => 'Select gender', 'data-placeholder' => 'Select gender', 'data-allow-clear' => 'true'] ) }}
            </div>
        </div>
        @endif 
        @if(!$edit)
        @if($personalFieldsVisibility)
        <div class="col-md-3 height">
            <div class="form-group">
                {{ Form::label('height', trans('labels.user.height')) }}
                {{ Form::text('height', old('height', ($profileData->height ?? 100)), ['class' => 'form-control', 'placeholder' => 'Enter Height(cm)', 'id' => 'height', 'autocomplete' => 'off']) }}
            </div>
        </div>
        <div class="col-md-3 weight">
            <div class="form-group">
                {{ Form::label('weight', trans('labels.user.weight')) }}
                {{ Form::text('weight', old('weight', ($weightData->weight ?? 50)), ['class' => 'form-control', 'placeholder' => 'Enter Weight(kg)', 'id' => 'weight', 'autocomplete' => 'off']) }}
            </div>
        </div>
        @endif
        @endif

        @if(!$edit)
        <div class="col-md-3" id="start_date_div" style="display: {{ $displayStyle }};">
            <div class="form-group">
                {{ Form::label('start_date', trans('labels.user.start_date')) }}
                <div class="datepicker-wrap">
                    {{ Form::text('start_date', old('start_date', ($record->start_date ?? null)), ['class' => 'form-control datepicker', 'id' => 'start_date','autocomplete'=>'off', 'readonly' => true, 'disabled' => !$editStartDate]) }}
                    <i class="far fa-calendar">
                    </i>
                </div>
            </div>
        </div>
        @endif
        <div class="col-md-12">
            <div class="form-group">
                {{ Form::label('about', trans('user.form.labels.about')) }}
                {{ Form::textarea('about', old('about', ($profileData->about ?? null)), ['id' => 'description', 'rows' => 5, 'class' => 'form-control', 'placeholder' => 'Enter Bio', 'spellcheck' => 'false']) }}
            </div>
        </div>
        <div class="col-xxl-12 col-md-6 timezone" style="display: {{ (old('role', (!empty($role) && ($role->slug == 'health_coach' || $role->slug == 'counsellor' || $role->slug == 'wellbeing_specialist' || $role->slug == 'wellbeing_team_lead') && $edit) || ($loggedInUser->slug == 'super_admin' && $edit)) ? 'block' : 'none') }};">
            <div class="form-group">
                {{ Form::label('timezone', trans('labels.user.timezone')) }}
                {{ Form::select('timezone', $timezones, old('timezone', [($selectedTimezone ?? null)]), ['class' => 'form-control select2', 'id' => 'timezone', 'placeholder' => 'Select timezone', 'data-placeholder' => 'Select timezone', 'data-allow-clear' => 'true', 'disabled' => $edit] ) }}
                @if($edit == true)
                {{ Form::hidden('timezone', $record->timezone)}}
                @endif
            </div>
        </div>
    </div>
</div>
<div data-coach-details-wrapper="" id="data-coach-details-wrapper"  style="display: {{ ((old('user_type', ((!empty($record) && $record->is_coach == true) ? 'health_coach' : '')) == 'health_coach') ? 'block' : 'none') }};">
    <div class="row">
        <div class="col-xxl-4 left-card">
            <div class="card-inner">
                <h3 class="card-inner-title">
                    Coach Other Details
                </h3>
                <div class="row">
                    <div class="col-xxl-12 col-md-4">
                        <div class="form-group">
                            {{ Form::label('availability', trans('labels.user.availability')) }}
                            {{ Form::select('availability', $availability, (old('availability', (int) (isset($record->availability_status) ? $record->availability_status : 1))), ['class' => 'form-control select2', 'id' => 'availability', 'data-allow-clear' => 'false'] ) }}
                        </div>
                    </div>
                </div>
                <!-- Start to add multiple custom leaves for wellbeing speacilist -->
                @if($edit !== true)
                <div data-availability-dates-wrapper="" style="display: {{ ((old('availability', ((!empty($record) && $record->availability_status == 2) ?? '')) == 2) ? 'block' : 'none') }};">
                    @include('admin.user.add-new-custom-leave')
                </div>
                @else
                <div data-availability-dates-wrapper="" style="display: {{ ((old('availability', ((!empty($record) && $record->availability_status == 2) ?? '')) == 2) ? '' : 'none') }};">
                    @if(!empty($customLeaveDates))
                        @include('admin.user.edit-custom-leave')
                    @else
                        @include('admin.user.add-new-custom-leave')
                    @endif
                </div>
                @endif
                <!-- End custom leaves for wellbeing speacilist -->
                <div class="row">
                    <div class="col-xxl-12 col-md-6">
                        <div class="form-group">
                            {{ Form::label('expertise', trans('labels.user.expertise')) }}
                            {{ Form::select('expertise[]', $expertise, old('expertise[]', ($userExpertise ?? [])), ['class' => 'form-control select2', 'id' => 'expertise', 'placeholder' => '', 'data-placeholder' => 'Select expertise', 'multiple' => true, 'data-close-on-select' => 'false', 'data-allow-clear' => 'false'] ) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xxl-8">
            <div class="card-inner">
                <h3 class="card-inner-title">
                    Set Availabilty
                </h3>
                <div class="table-responsive set-availability-block">
                    @foreach($hc_availability_days as $keyday => $day)
                    <div class="d-flex set-availability-box pb-1 mb-1 align-items-center" data-day-key="{{ $keyday }}">
                        <div class="set-availability-day">
                            {{ $day }}
                        </div>
                        <div class="w-100 slots-wrapper">
                            <div class="d-flex align-items-center no-data-block {{ (array_key_exists($keyday, $userSlots) ? 'd-none' : '') }}">
                                <div class="set-availability-date-time">
                                    {{ trans('labels.user.not_available') }}
                                </div>
                                <div class="d-flex set-availability-btn-area justify-content-end">
                                    <a class="add-slot action-icon text-info" href="javascript:void(0);" title="Add Slot">
                                        <i class="far fa-plus">
                                        </i>
                                    </a>
                                </div>
                            </div>
                            @if(array_key_exists($keyday, $userSlots))
                                @foreach($userSlots[$keyday] as $slot)
                                    @include('admin.user.slot-preview', [
                                        'start_time' => $slot['start_time']->format('H:i'),
                                        'end_time' => $slot['end_time']->format('H:i'),
                                        'time' => $slot['start_time']->format('h:i A') . ' - ' . $slot['end_time']->format('h:i A'),
                                        'key' => $keyday,
                                        'id' => $slot['id'],
                                    ])
                                @endforeach
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
<div data-ws-details-wrapper="" id ="data-ws-details-wrapper"  style="display: {{ ((old('user_type', ((!empty($role) && $role->slug == 'wellbeing_specialist') ? true : false)) == 'wellbeing_specialist') ? 'block' : 'none') }};">
    <div class="row">
        <div class="col-xxl-12 left-card">
            <div class="card-inner">
                <h3 class="card-inner-title">
                    Wellbeing Specialist Details
                </h3>
                <div class="row">
                    <div class="col-xxl-12 col-md-4">
                        <div class="form-group">
                            {{ Form::label('availability', trans('labels.user.availability')) }}
                            {{ Form::select('availability', $availability, (old('availability', (int) (isset($record->availability_status) ? $record->availability_status : 1))), ['class' => 'form-control select2', 'id' => 'availability', 'data-allow-clear' => 'false'] ) }}
                        </div>
                    </div>
                </div>
                <!-- Start to add multiple custom leaves for wellbeing speacilist -->
                @if($edit !== true)
                <div data-availability-dates-wrapper="" style="display: {{ ((old('availability', ((!empty($record) && $record->availability_status == 2) ?? '')) == 2) ? 'block' : 'none') }};">
                    @include('admin.user.add-new-custom-leave')
                </div>
                @else
                <div data-availability-dates-wrapper="" style="display: {{ ((old('availability', ((!empty($record) && $record->availability_status == 2) ?? '')) == 2) ? '' : 'none') }};">
                    @if(!empty($customLeaveDates))
                        @include('admin.user.edit-custom-leave')
                    @else
                        @include('admin.user.add-new-custom-leave')
                    @endif
                </div>
                @endif
                <div class="row">
                    <div class="col-xl-4 col-md-4">
                        <div class="form-group">
                            {{ Form::label('language', trans('user.form.labels.language')) }}
                            {{ Form::select('language[]', $userLanguage, old('language[]', ($language ?? [])), ['class' => 'form-control select2', 'id' => 'language', 'multiple' => true, 'placeholder' => '', 'data-placeholder' => 'Select language', 'data-close-on-select' => 'false', 'data-allow-clear' => 'false']) }}
                        </div>
                    </div>
                    <div class="col-lg-4 col-xl-4">
                        <div class="form-group">
                            {{ Form::label('years_of_experience', trans('user.form.labels.years_of_experience')) }}
                            {{ Form::text('years_of_experience', old('years_of_experience', ($wsDetails->years_of_experience ?? null)), ['class' => 'form-control', 'placeholder' => '', 'id' => 'years_of_experience', 'autocomplete' => 'off']) }}
                        </div>
                    </div>

                    <div class="col-xl-4 col-md-4">
                        <div class="form-group">
                            {{ Form::label('shift', trans('user.form.labels.shift')) }}
                            {{ Form::select('shift', $shift, (old('shift', (int) (isset($wsDetails->shift) ? $wsDetails->shift : 1))), ['class' => 'form-control select2', 'id' => 'shift', 'data-allow-clear' => 'false', 'data-placeholder' => trans('user.form.placeholder.shift'), 'placeholder' => trans('user.form.placeholder.shift')] ) }}
                        </div>
                    </div>
                </div>
                <div class="row">
                    {{-- <div class="col-lg-4 col-xl-4">
                        {{ Form::label('', '') }}
                        <div class="form-group">
                            <label class="custom-checkbox">
                                {{ trans('user.form.labels.sync_email_with_nylas') }}
                                @php
                                    $checked = "";
                                    if(!empty($record) && $record->sync_email_with_nylas == true){
                                        $checked = 'checked="checked"';
                                    }
                                @endphp
                                <input class="form-control" id="sync_email_with_nylas" name="sync_email_with_nylas" type="checkbox" value="true" {{$checked}}=""/>
                                <span class="checkmark">
                                </span>
                                <span class="box-line">
                                </span>
                            </label>
                        </div>
                    </div> --}}
                    <div class="col-xl-4 col-md-4">
                        <div class="form-group">
                            {{ Form::label('video_conferencing_mode', trans('user.form.labels.video_conferencing_mode')) }}
                            {{ Form::select('video_conferencing_mode', $video_conferencing_mode, (old('video_conferencing_mode', (int) (isset($wsDetails->conferencing_mode) ? $wsDetails->conferencing_mode : null))), ['class' => 'form-control select2', 'id' => 'video_conferencing_mode', 'data-allow-clear' => 'false', 'data-placeholder' => trans('user.form.placeholder.video_conferencing_mode'), 'placeholder' => trans('user.form.placeholder.video_conferencing_mode')] ) }}
                        </div>
                    </div>
                    <div class="col-xl-8 col-md-4 videoLink" style="display: {{ (old('user_type', (isset($wsDetails->conferencing_mode) && !empty($wsDetails->conferencing_mode)) ? 'block' : 'none')) }};">
                        <div class="form-group">
                            {{ Form::label('video_link', trans('user.form.labels.video_link')) }}
                            <div class="datepicker-wrap">
                                {{ Form::text('video_link', old('video_link', ($wsDetails->video_link ?? null)), ['class' => 'form-control', 'id' => 'video_link','autocomplete'=>'off']) }}
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-4 col-md-4">
                        <div class="form-group">
                            {{ Form::label('Responsibilities', trans('user.form.labels.responsibilities')) }}
                            {{ Form::select('responsibilities', $responsibilitiesList, (old('responsibilities', (int) (isset($wsDetails->responsibilities) ? $wsDetails->responsibilities : null))), ['class' => 'form-control select2', 'id' => 'responsibilities', 'data-allow-clear' => 'false', 'data-placeholder' => trans('user.form.placeholder.responsibilities'), 'placeholder' => trans('user.form.placeholder.responsibilities')] ) }}
                        </div>   
                    </div>
                    <div class="col-xl-4 col-md-4 expertise_wbs" style="display: {{ (old('responsibilities', (isset($wsDetails->responsibilities) && ($wsDetails->responsibilities == 2 || $wsDetails->responsibilities == 3)) ? 'block' : 'none')) }};">
                        <div class="form-group">
                            {{ Form::label('expertise', trans('user.form.labels.expertise')) }}
                            {{ Form::select('expertise_wbs[]', $expertise, old('expertise_wbs[]', ($userExpertise ?? [])), ['class' => 'form-control select2', 'id' => 'expertise_wbs', 'multiple' => true, 'placeholder' => '', 'data-placeholder' => 'Select expertise', 'data-close-on-select' => 'false', 'data-allow-clear' => 'false']) }}
                        </div>
                    </div>
                    <div class="col-xl-4 col-md-4 advance_notice_wbs" style="display: {{ (old('responsibilities', (isset($wsDetails->responsibilities) && ($wsDetails->responsibilities == 2 || $wsDetails->responsibilities == 3)) ? 'block' : 'none')) }};">
                        <div class="form-group">
                            {{ Form::label('advance_notice_period', trans('user.form.labels.advance_notice_period')) }}
                            <span class="font-16 qus-sign-tooltip" data-toggle="help-tooltip" title="{{ trans('user.messages.advance_notice_period_tooltip') }}">
                                <i aria-hidden="true" class="far fa-info-circle text-primary">
                                </i>
                            </span>
                            @if ($edit)
                            {{ Form::select('advance_notice_period', $advanceNoticePeriod, old('advance_notice_period', (($profileData->notice_period==0 || $profileData->notice_period=='') ? 48 : $profileData->notice_period)), ['class' => 'form-control select2', 'id' => 'advance_notice_period', 'data-placeholder' => 'Select Advance notice Period', 'data-close-on-select' => 'true', 'data-allow-clear' => 'false']) }}
                            @else  
                            {{ Form::select('advance_notice_period', $advanceNoticePeriod, old('advance_notice_period', 48), ['class' => 'form-control select2', 'id' => 'advance_notice_period', 'data-placeholder' => 'Select Advance notice Period', 'data-close-on-select' => 'true', 'data-allow-clear' => 'false']) }}
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-inner wbs-availability mt-2">
                <h3 class="card-inner-title">
                    Wellbeing Specialist Availabilty 
                    <span class="font-16 qus-sign-tooltip" data-toggle="help-tooltip" title="Set availability for the Digital Therapy 1:1 Sessions">
                        <i aria-hidden="true" class="far fa-info-circle text-primary">
                        </i>
                    </span>
                </h3>
                <div class="table-responsive set-availability-block">
                    @foreach($hc_availability_days as $keyday => $day)
                    <div class="d-flex set-availability-box pb-1 mb-1 align-items-center" data-day-key="{{ $keyday }}">
                        <div class="set-availability-day">
                            {{ $day }}
                        </div>
                        <div class="w-100 slots-wrapper">
                            <div class="d-flex align-items-center no-data-block {{ (array_key_exists($keyday, $userSlots) ? 'd-none' : '') }}">
                                <div class="set-availability-date-time">
                                    {{ trans('labels.user.not_available') }}
                                </div>
                                <div class="d-flex set-availability-btn-area justify-content-end">
                                    <a class="add-slot action-icon text-info" href="javascript:void(0);" title="Add Slot">
                                        <i class="far fa-plus">
                                        </i>
                                    </a>
                                </div>
                            </div>
                            @if(array_key_exists($keyday, $userSlots))
                                @foreach($userSlots[$keyday] as $slot)
                                    @include('admin.user.slot-preview', [
                                        'start_time' => $slot['start_time']->format('H:i'),
                                        'end_time' => $slot['end_time']->format('H:i'),
                                        'time' => $slot['start_time']->format('h:i A') . ' - ' . $slot['end_time']->format('h:i A'),
                                        'key' => $keyday,
                                        'id' => $slot['id'],
                                    ])
                                @endforeach
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            <div class="card-inner event-presenter-availability" style="display: {{ ((old('responsibilities', ((isset($wsDetails->responsibilities) && $wsDetails->responsibilities != 1) ? true : false))) ? 'block' : 'none') }};">
                <h3 class="card-inner-title">
                    Event Presenter Availabilty 
                    <span class="font-16 qus-sign-tooltip" data-toggle="help-tooltip" title="Set availability for the Events">
                        <i aria-hidden="true" class="far fa-info-circle text-primary">
                        </i>
                    </span>
                </h3>
                @if($edit)
                <div class="table-responsive set-availability-block">
                    @foreach($hc_availability_days as $keyday => $day)
                    <div class="d-flex set-availability-box pb-1 mb-1 align-items-center" data-day-key="{{ $keyday }}">
                        <div class="set-availability-day">
                            {{ $day }}
                        </div>
                        <div class="w-100 slots-wrapper">
                            <div class="d-flex align-items-center no-data-block {{ (array_key_exists($keyday, $presenterSlots) ? 'd-none' : '') }}">
                                <div class="set-availability-date-time">
                                    {{ trans('labels.user.not_available') }}
                                </div>
                                <div class="d-flex set-availability-btn-area justify-content-end">
                                    <a class="add-presenter-slot action-icon text-info" href="javascript:void(0);" title="Add Slot">
                                        <i class="far fa-plus">
                                        </i>
                                    </a>
                                </div>
                            </div>
                            @if(array_key_exists($keyday, $presenterSlots))
                                @foreach($presenterSlots[$keyday] as $slot)
                                    @include('admin.user.presenter-slot-preview', [
                                        'start_time' => $slot['start_time']->format('H:i'),
                                        'end_time' => $slot['end_time']->format('H:i'),
                                        'time' => $slot['start_time']->format('h:i A') . ' - ' . $slot['end_time']->format('h:i A'),
                                        'key' => $keyday,
                                        'id' => $slot['id'],
                                    ])
                                @endforeach
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
                @if(!$edit)
                <div class="table-responsive set-availability-block">
                    @foreach($hc_availability_days as $keyday => $day)
                    <div class="d-flex set-availability-box pb-1 mb-1 align-items-center" data-day-key="{{ $keyday }}">
                        <div class="set-availability-day">
                            {{ $day }}
                        </div>
                        <div class="w-100 slots-wrapper">
                            <div class="d-flex align-items-center no-data-block {{ (array_key_exists($keyday, $presenterSlots) ? 'd-none' : '') }}">
                                <div class="set-availability-date-time">
                                    {{ trans('labels.user.not_available') }}
                                </div>
                                <div class="d-flex set-availability-btn-area justify-content-end">
                                    <a class="add-presenter-slot action-icon text-info" href="javascript:void(0);" title="Add Slot">
                                        <i class="far fa-plus">
                                        </i>
                                    </a>
                                </div>
                            </div>
                            @if(array_key_exists($keyday, $presenterSlots))
                                @foreach($presenterSlots[$keyday] as $slot)
                                    @include('admin.user.presenter-slot-preview', [
                                        'start_time' => "10:00",
                                        'end_time' => "17:00",
                                        'time' => "10:00 AM - 05:00 PM",
                                        'key' => $keyday,
                                        'id' => $slot['id'],
                                    ])
                                @endforeach
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
            <div class="card-inner">
                <h3 class="card-inner-title">{{ trans('user.form.labels.service_type_subcategories') }}</h3>
                <div class="row">
                    <div class="col-lg-12">
                        <div class="form-group mb-0">
                            <div id="setPermissionList" class="tree-multiselect-box">
                                <select id="user_services" name="user_services[]" multiple="multiple" class="form-control">
                                    @foreach($servicesArray as $key => $value)
                                        @foreach($value['subService'] as $subkey => $subvalue)
                                            <option value="{{ $subvalue['id'] }}" data-section="{{ $value['serviceName'] }}" {{ (!empty($service_users) && in_array($subvalue['id'], $service_users))? 'selected' : ''   }}>{{ $subvalue['name'] }}</option>
                                        @endforeach
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <span id="user_services_error" style="display: none; width: 100%; margin-top: 0.25rem; font-size: 80%; color: #f44436;">
                            {{ trans('user.validation.services_required') }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
{{ Form::hidden('slots_exist', ((sizeof($userSlots) > 0) ? '1' : ''), ['id' => 'slots_exist']) }}
{{ Form::hidden('presenter_slots_exist', ((sizeof($presenterSlots) > 0) ? '1' : ''), ['id' => 'presenter_slots_exist']) }}
<script id="add-new-slot-template" type="text/html">
    @include('admin.user.add-new-slot')
</script>
<script id="edit-slot-template" type="text/html">
    @include('admin.user.edit-preview', [
        'start_time' => '#start_time#',
        'end_time' => '#end_time#',
        'id' => ''
    ])
</script>
<script id="preview-slot-template" type="text/html">
    @include('admin.user.slot-preview', [
        'start_time' => '#start_time#',
        'end_time' => '#end_time#',
        'time' => '#time#',
        'key' => '#key#',
        'id' => '#id#'
    ])
</script>
<script id="add-new-presenter-slot-template" type="text/html">
    @include('admin.user.add-new-presenter-slot')
</script>
<script id="edit-presenter-slot-template" type="text/html">
    @include('admin.user.edit-presenter-slot-preview', [
        'start_time' => '#start_time#',
        'end_time' => '#end_time#',
        'id' => ''
    ])
</script>
<script id="preview-presenter-slot-template" type="text/html">
    @include('admin.user.presenter-slot-preview', [
        'start_time' => '#start_time#',
        'end_time' => '#end_time#',
        'time' => '#time#',
        'key' => '#key#',
        'id' => '#id#'
    ])
</script>
@include('admin.user.remove-presenter-slot-model-box')
@include('admin.user.remove-slot-model-box')
