@extends('layouts.app')

@section('after-styles')
<link href="{{ asset('assets/plugins/datepicker/datepicker3.css?var='.rand()) }}" rel="stylesheet"/>
<link href="{{asset('assets/plugins/tree-multiselect/tree-multiselect.css?var='.rand())}}" rel="stylesheet"/>
@endsection
@section('content-header')
<!-- Content Header (Page header) -->
@include('admin.user.breadcrumb', [
    'mainTitle' => trans('user.edit_profile.title.index'),
    'breadcrumb' => Breadcrumbs::render('user.edit-profile'),
    'back' => false
])
<!-- /.content-header -->
@endsection

@section('content')
<section class="content">
    <div class="container-fluid">
        <div class="card form-card">
            {{ Form::open(['route' => ['admin.users.updateProfile'], 'class' => 'form-horizontal', 'method'=>'PATCH','role' => 'form', 'id'=>'userEdit', 'files' => true]) }}
            <div class="card-body">
                <div class="card-inner">
                    <div class="row">
                        @if(($loggedInUser->slug == 'super_admin' && $loggedInUser->group == 'zevo') || ($loggedInUser->slug == 'company_admin' && $loggedInUser->group == 'company'))
                        <div class="col-lg-2 col-xl-2">
                            <div class="form-group">
                                {{ Form::label('role_group', trans('user.edit_profile.form.labels.role_group')) }}
                                <div>
                                    <label class="custom-radio" for="role_group_{{ $roleData->id }}">
                                        {{ ucwords($roleData->group) }}
                                        {{ Form::radio('role_group', $roleData->id, true, ['class' => 'form-control', 'id' => 'role_group_' . $roleData->id, 'disabled' => true]) }}
                                        <span class="checkmark">
                                        </span>
                                        <span class="box-line">
                                        </span>
                                    </label>
                                </div>
                            </div>
                        </div>
                        {{ Form::hidden('role_group', $roleData->id) }}
                        @endif
                        @if($roleData->default == true && !empty($currentPlan))
                        @if($roleData->slug == "company_admin" || $roleData->slug == "reseller_super_admin" || $roleData->slug == "reseller_company_admin")
                        <div class="col-lg-2 col-xl-2">
                            <div class="form-group">
                                {{ Form::label('plan_type', trans('user.edit_profile.form.labels.plan_type')) }}
                                <div>
                                    <label class="custom-radio" for="plan_type_{{ $currentPlan }}">
                                        {{ ucwords($currentPlan) }}
                                        {{ Form::radio('plan_type', $currentPlan, true, ['class' => 'form-control', 'id' => 'plan_type_' . $currentPlan, 'disabled' => true]) }}
                                        <span class="checkmark">
                                        </span>
                                        <span class="box-line">
                                        </span>
                                    </label>
                                </div>
                            </div>
                        </div>
                        @endif
                        @endif
                        {{-- @if($roleData->slug == "health_coach" && $roleData->default == true)
                        <div class="col-lg-2 col-xl-2">
                            <div class="form-group">
                                {{ Form::label('health_coach', trans('user.edit_profile.form.labels.health_coach')) }}
                                <div>
                                    <label class="custom-radio" for="health_coach">
                                        {{ trans('user.edit_profile.form.labels.health_coach') }}
                                        {{ Form::radio('health_coach', true, ($record->is_coach ?? false), ['class' => 'form-control', 'id' => 'health_coach', 'disabled' => true]) }}
                                        <span class="checkmark">
                                        </span>
                                        <span class="box-line">
                                        </span>
                                    </label>
                                </div>
                            </div>
                        </div>
                        @endif --}}
                    </div>
                </div>
                @if(!empty($company))
                <div class="card-inner">
                    <h3 class="card-inner-title">
                        Company Details
                    </h3>
                    <div class="row">
                        <div class="col-xxl-12 col-md-4">
                            <div class="form-group">
                                {{ Form::label('company', trans('user.edit_profile.form.labels.company')) }}
                                {{ Form::select('', $companies, $company->company_id, ['class' => 'form-control select2', 'disabled' => true] ) }}
                            </div>
                        </div>
                        <div class="col-xxl-12 col-md-4">
                            <div class="form-group">
                                {{ Form::label('department', trans('user.edit_profile.form.labels.department')) }}
                                @php
                                    $companyId =   (!empty($company->company_id)) ? $company->company_id : ((!empty(old('company'))) ? old('company') : "");
                                    $departmentId =   (!empty($company->department_id)) ? $company->department_id : ((!empty(old('department'))) ? old('department') : "");
                                    $departments = (!empty($companyId) && !empty($departmentId)) ? getDepartments($companyId) : [];
                                @endphp
                                {{ Form::select('department', $departments, $departmentId, ['class' => 'form-control select2', 'disabled' => true] ) }}
                            </div>
                        </div>
                        <div class="col-xxl-12 col-md-4">
                            <div class="form-group">
                                {{ Form::label('team', trans('user.edit_profile.form.labels.team')) }}
                                @php
                                    $departmentId =   (!empty($company->department_id)) ? $company->department_id : ((!empty(old('department'))) ? old('department') : "");
                                    $teamId =   (!empty($company->id)) ? $company->id : ((!empty(old('team'))) ? old('team') : "");
                                    $teams = (!empty($departmentId) && !empty($teamId)) ? getTeams($departmentId) : [];
                                @endphp
                                {{ Form::select('team', $teams, $teamId, ['class' => 'form-control select2', 'disabled' => true] ) }}
                            </div>
                        </div>
                    </div>
                </div>
                @endif
                <div class="card-inner">
                    <h3 class="card-inner-title">
                        Basic Details
                    </h3>
                    <div class="row justify-content-center justify-content-md-start">
                        <div class="col-xxl-2 col-lg-3 col-md-4 basic-file-upload order-md-2">
                            <label>
                                {{ trans('user.edit_profile.form.labels.profile_picture') }}
                                <span class="font-16 qus-sign-tooltip" data-toggle="help-tooltip" title="{{ getHelpTooltipText('user.logo') }}">
                                    <i aria-hidden="true" class="far fa-info-circle text-primary">
                                    </i>
                                </span>
                            </label>
                            <div class="edit-profile-wrapper edit-profile-small form-control h-auto border-0 p-0">
                                <div class="profile-image user-img edit-photo">
                                    <img class="profile-image-preview" id="previewImg" src="{{ (!empty($record->logo) ? $record->logo : asset('assets/dist/img/placeholder-img.png')) }}"/>
                                </div>
                                <div class="edit-profile-avtar edit-profile-small">
                                    {{ Form::file('logo', ['class' => 'edit-avatar', 'id' => 'profileImage', 'data-width' => config('zevolifesettings.imageConversions.user.logo.width'), 'data-height' => config('zevolifesettings.imageConversions.user.logo.height'), 'data-ratio' => config('zevolifesettings.imageAspectRatio.user.logo'), 'data-previewelement' => '#previewImg', 'title' => ' ']) }}
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
                                        {{ Form::label('role', trans('user.edit_profile.form.labels.role')) }}
                                        @php
                                            $group = (!empty(old('group'))) ? old('group') : "";
                                            $role = (!empty(old('role'))) ? old('role') : "";
                                            $roles = (!empty($group) && !empty($role)) ? getRoles($group) : [];
                                            $group =   (!empty($roleData->group)) ? $roleData->group : ((!empty(old('group'))) ? old('group') : "");
                                            $role =   (!empty($roleData->id)) ? $roleData->id : ((!empty(old('role'))) ? old('role') : "");
                                            $roles = (!empty($group) && !empty($role)) ? getRoles($group) : [];
                                        @endphp
                                        {{ Form::select('role', ($roles ?? []), ($role ?? null), ['class' => 'form-control select2', 'disabled' => true] ) }}
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        {{ Form::label('first_name', trans('user.edit_profile.form.labels.first_name')) }}
                                        {{ Form::text('first_name', old('first_name', ($record->first_name ?? null)), ['class' => 'form-control', 'placeholder' => trans('user.edit_profile.form.placeholder.first_name'), 'id' => 'first_name', 'autocomplete' => 'off']) }}
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        {{ Form::label('last_name', trans('user.edit_profile.form.labels.last_name')) }}
                                        {{ Form::text('last_name', old('last_name', ($record->last_name ?? null)), ['class' => 'form-control', 'placeholder' => trans('user.edit_profile.form.placeholder.last_name'), 'id' => 'last_name', 'autocomplete' => 'off']) }}
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <div class="form-group">
                                        {{ Form::label('email', trans('user.edit_profile.form.labels.email')) }}
                                        {{ Form::text('email', old('email', ($record->email ?? null)), ['class' => 'form-control', 'placeholder' => trans('user.edit_profile.form.placeholder.email'), 'id' => 'email', 'autocomplete' => 'off', 'disabled' => true]) }}
                                    </div>
                                </div>
                                @if($loggedInUser->slug == 'wellbeing_specialist')
                                <div class="col-md-6 counsellor_skills_section cover_picture">
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
                                @if($roleData->slug == 'counsellor')
                                <div class="col-md-4 align-self-end">
                                    <div class="form-group">
                                        <div class="seperator-block mw-100 w-100">
                                            <span>
                                                <label class="text-secondary me-3 mb-0">
                                                    {{ trans('user.edit_profile.form.labels.total_sessions') }}
                                                </label>
                                                <strong>
                                                    {{ $totalSessions }}
                                                </strong>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                @endif
                                @if($roleData->slug == 'wellbeing_specialist' || $roleData->slug == "health_coach")
                                <div class="col-lg-12">
                                    <p><label>{{ trans('user.edit_profile.form.labels.account_status') }}</label></p>
                                    @if((!empty($wsDetails) && $wsDetails->is_cronofy == true) || (!empty($wcDetails) && $wcDetails->is_cronofy == true))
                                    <div class="badge-label badge-green"><span>{{ trans('user.edit_profile.form.labels.verified') }}</span> <i class="fal fa-check"></i></div>
                                    @else
                                    <div class="badge-label badge-red"><span>{{ trans('user.edit_profile.form.labels.unverified') }}</span> <i class="fal fa-times"></i></div>
                                    @endif
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
                        @if($loggedInUser->slug != 'wellbeing_specialist' && $loggedInUser->slug != 'health_coach' && $loggedInUser->slug != 'wellbeing_team_lead')
                        <div class="col-md-3">
                            <div class="form-group">
                                {{ Form::label('date_of_birth', trans('user.edit_profile.form.labels.date_of_birth')) }}
                                <div class="datepicker-wrap">
                                    {{ Form::text('date_of_birth', old('date_of_birth', ($birthDate ?? '1990-01-01')), ['class' => 'form-control datepickerr', 'id' => 'date_of_birth', 'autocomplete'=>'off', 'readonly' => true, 'placeholder' => trans('user.edit_profile.form.placeholder.date_of_birth')]) }}
                                    <i class="far fa-calendar">
                                    </i>
                                </div>
                            </div>
                        </div>
                        @endif
                        <div class="col-md-3">
                            <div class="form-group">
                                {{ Form::label('gender', trans('user.edit_profile.form.labels.gender')) }}
                                {{ Form::select('gender', $genders, old('gender', ($profileData->gender ?? 'none')), ['class' => 'form-control select2', 'id' => 'gender', 'placeholder' => 'Select gender', 'data-placeholder' => 'Select gender'] ) }}
                            </div>
                        </div>
                        @if($loggedInUser->slug == 'health_coach')
                        <div class="col-xxl-12 col-md-6">
                            <div class="form-group">
                                {{ Form::label('expertise', trans('labels.user.expertise')) }}
                                {{ Form::select('expertise[]', $expertise, old('expertise[]', ($userExpertise ?? [])), ['class' => 'form-control select2', 'id' => 'expertise', 'placeholder' => '', 'data-placeholder' => 'Select expertise', 'multiple' => true, 'disabled' => true] ) }}
                            </div>
                        </div>
                        @endif
                        @if($loggedInUser->slug != 'wellbeing_specialist' && $loggedInUser->slug != 'health_coach' && $loggedInUser->slug != 'wellbeing_team_lead')
                        <div class="col-md-3">
                            <div class="form-group">
                                {{ Form::label('height', trans('user.edit_profile.form.labels.height')) }}
                                {{ Form::text('height', old('height', ($profileData->height ?? 100)), ['class' => 'form-control', 'placeholder' => trans('user.edit_profile.form.placeholder.height'), 'id' => 'height', 'autocomplete' => 'off']) }}
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                {{ Form::label('weight', trans('user.edit_profile.form.labels.weight')) }}
                                {{ Form::text('weight', old('weight', ($weightData->weight ?? 50)), ['class' => 'form-control', 'placeholder' => trans('user.edit_profile.form.placeholder.weight'), 'id' => 'weight', 'autocomplete' => 'off']) }}
                            </div>
                        </div>
                        @endif
                        <div class="col-md-12">
                            <div class="form-group">
                                @if($loggedInUser->slug == 'wellbeing_specialist' || $loggedInUser->slug == 'wellbeing_team_lead' )
                                {{ Form::label('Bio', trans('user.edit_profile.form.labels.bio')) }}
                                @else
                                {{ Form::label('about', trans('user.edit_profile.form.labels.about')) }}
                                @endif
                                {{ Form::textarea('about', old('about', ($profileData->about ?? null)), ['id' => 'description', 'rows' => 5, 'class' => 'form-control', 'placeholder' => trans('user.edit_profile.form.placeholder.about'), 'spellcheck' => 'false']) }}
                            </div>
                        </div>
                        @if($loggedInUser->slug == 'wellbeing_specialist' || $loggedInUser->slug == 'health_coach' || $loggedInUser->slug == 'wellbeing_team_lead')
                        <div class="col-md-6">
                            <div class="form-group">
                                {{ Form::label('timezone', trans('labels.user.timezone')) }}
                                {{ Form::select('timezone', $timezones, old('timezone', [($selectedTimezone ?? null)]), ['class' => 'form-control select2', 'id' => 'timezone', 'placeholder' => 'Select timezone', 'data-placeholder' => 'Select timezone', 'data-allow-clear' => 'true', 'disabled' => true] ) }}
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
                @if($loggedInUser->slug == 'wellbeing_specialist')
                <div class="card-inner">
                    <h3 class="card-inner-title">
                        Wellbeing Specialist Details
                    </h3>
                    <div class="row">
                        <div class="col-xxl-12 col-md-4">
                            <div class="form-group">
                                {{ Form::label('availability', trans('labels.user.availability')) }}
                                {{ Form::select('availability', $availability, (old('availability', (int) (isset($record->availability_status) ? $record->availability_status : 1))), ['class' => 'form-control select2', 'id' => 'availability', 'data-allow-clear' => 'false', 'disabled' => $loggedInUser->slug === 'wellbeing_specialist' ? true : false] ) }}
                            </div>
                        </div>
                    </div>
                    <div data-availability-dates-wrapper="" style="display: {{ ((old('availability', ((!empty($record) && $record->availability_status == 2) ?? '')) == 2) ? '' : 'none') }};">
                        @if(!empty($customLeaveDates))
                            @include('admin.user.edit-custom-leave')
                        @else
                            @include('admin.user.add-new-custom-leave')
                        @endif
                    </div>
                    <div class="row">
                        <div class="col-xl-4 col-md-4">
                            <div class="form-group">
                                {{ Form::label('language', trans('user.form.labels.language')) }}
                            {{ Form::select('language[]', $userLanguage, old('language[]', ($language ?? [])), ['class' => 'form-control select2', 'id' => 'language', 'multiple' => true, 'placeholder' => '', 'data-placeholder' => 'Select language', 'data-close-on-select' => 'false', 'data-allow-clear' => 'false']) }}
                            </div>
                        </div>

                        <div class="col-xl-4 col-md-4">
                            <div class="form-group">
                                {{ Form::label('shift', trans('user.form.labels.shift')) }}
                            {{ Form::select('shift', $shift, (old('language', (int) (isset($wsDetails->shift) ? $wsDetails->shift : null))), ['class' => 'form-control select2', 'id' => 'shift', 'data-allow-clear' => 'false', 'data-placeholder' => trans('user.form.placeholder.shift'), 'placeholder' => trans('user.form.placeholder.shift')] ) }}
                            </div>
                        </div>
                        <div class="col-xl-4 col-md-4">
                            {{ Form::label('years_of_experience', trans('user.form.labels.years_of_experience')) }}
                            <div class="form-group">
                                {{ Form::text('years_of_experience', old('years_of_experience', ($wsDetails->years_of_experience ?? null)), ['class' => 'form-control', 'placeholder' => '', 'id' => 'years_of_experience', 'autocomplete' => 'off']) }}
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xl-4 col-md-4">
                            <div class="form-group">
                                {{ Form::label('video_conferencing_mode', trans('user.form.labels.video_conferencing_mode')) }}
                            {{ Form::select('video_conferencing_mode', $video_conferencing_mode, (old('language', (int) (isset($wsDetails->conferencing_mode) ? $wsDetails->conferencing_mode : null))), ['class' => 'form-control select2', 'id' => 'video_conferencing_mode', 'data-allow-clear' => 'false', 'data-placeholder' => trans('user.form.placeholder.video_conferencing_mode'), 'placeholder' => trans('user.form.placeholder.video_conferencing_mode'), 'disabled'=>true] ) }}
                            </div>
                        </div>
                        <div class="col-xl-8 col-md-4 videoLink" style="display: {{ (old('user_type', (isset($wsDetails->conferencing_mode) && !empty($wsDetails->conferencing_mode)) ? 'block' : 'none')) }};">
                            <div class="form-group">
                                {{ Form::label('video_link', trans('user.form.labels.video_link')) }}
                                <div class="datepicker-wrap">
                                    {{ Form::text('video_link', old('video_link', ($wsDetails->video_link ?? null)), ['class' => 'form-control', 'id' => 'video_link','autocomplete'=>'off', 'disabled'=>true]) }}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xl-4 col-md-4">
                            <div class="form-group">
                                {{ Form::label('Responsibilities', trans('user.form.labels.responsibilities')) }}
                                {{ Form::select('responsibilities', $responsibilitiesList, (old('responsibilities', (int) (isset($wsDetails->responsibilities) ? $wsDetails->responsibilities : null))), ['class' => 'form-control select2', 'id' => 'responsibilities', 'disabled'=>true, 'data-allow-clear' => 'false', 'data-placeholder' => trans('user.form.placeholder.responsibilities'), 'placeholder' => trans('user.form.placeholder.responsibilities')] ) }}
                            </div>   
                        </div>
                        <div class="col-xl-4 col-md-4 expertise_wbs" style="display: {{ (old('responsibilities', (isset($wsDetails->responsibilities) && ($wsDetails->responsibilities == 2 || $wsDetails->responsibilities == 3)) ? 'block' : 'none')) }};">
                            <div class="form-group">
                                {{ Form::label('expertise', trans('user.form.labels.expertise')) }}
                                {{ Form::select('expertise_wbs[]', $expertise, old('expertise_wbs[]', ($userExpertise ?? [])), ['class' => 'form-control select2', 'disabled'=>true, 'id' => 'expertise_wbs', 'multiple' => true, 'placeholder' => '', 'data-placeholder' => 'Select expertise', 'data-close-on-select' => 'false', 'data-allow-clear' => 'false']) }}
                            </div>
                        </div>
                        <div class="col-xl-4 col-md-4 advance_notice_wbs" style="display: {{ (old('responsibilities', (isset($wsDetails->responsibilities) && ($wsDetails->responsibilities == 2 || $wsDetails->responsibilities == 3)) ? 'block' : 'none')) }};">
                            <div class="form-group">
                                {{ Form::label('advance_notice_period', trans('user.form.labels.advance_notice_period')) }}
                                <span class="font-16 qus-sign-tooltip" data-toggle="help-tooltip" title="{{ trans('user.messages.advance_notice_period_tooltip') }}">
                                    <i aria-hidden="true" class="far fa-info-circle text-primary">
                                    </i>
                                </span>
                                {{ Form::select('advance_notice_period', $advanceNoticePeriod, old('advance_notice_period', (($profileData->notice_period==0 || $profileData->notice_period=='') ? 48 : $profileData->notice_period)), ['class' => 'form-control select2', 'disabled'=>true, 'id' => 'advance_notice_period', 'placeholder' => '', 'data-placeholder' => 'Select Advance notice Period', 'data-close-on-select' => 'false', 'data-allow-clear' => 'false']) }}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-inner">
                    <h3 class="card-inner-title">
                        Service Type & Subcategory
                    </h3>
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="form-group mb-0">
                                <div id="setPermissionList" class="tree-multiselect-box">
                                    <select id="user_services" name="user_services[]" multiple="multiple" class="form-control">
                                        @foreach($servicesArray as $key => $value)
                                            @foreach($value['subService'] as $subkey => $subvalue)
                                                @if(in_array($subvalue['id'], $service_users))
                                                <option value="{{ $subvalue['id'] }}" data-section="{{ $value['serviceName'] }}" {{ (!empty($service_users) && in_array($subvalue['id'], $service_users))? 'selected' : ''   }}>{{ $subvalue['name'] }}</option>
                                                @endif
                                            @endforeach
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            </div>
            <div class="card-footer">
                <div class="save-cancel-wrap">
                    <a class="btn btn-outline-primary" href="{{ route('dashboard') }}">
                        {{ trans('buttons.general.cancel') }}
                    </a>
                    <button class="btn btn-primary" type="submit">
                        {{ trans('buttons.general.save') }}
                    </button>
                </div>
            </div>
            {{ Form::close() }}
        </div>
    </div>
</section>
@endsection

@section('after-scripts')
{!! JsValidator::formRequest('App\Http\Requests\Admin\UpdateUserProfileRequest','#userEdit') !!}
<script src="{{asset('assets/plugins/datepicker/bootstrap-datepicker.js?var='.rand())}}" type="text/javascript">
</script>
<script src="{{ asset('assets/plugins/tree-multiselect/tree-multiselect.js?var='.rand()) }}">
</script>
<script type="text/javascript">
    var today = new Date(),
        minDOBDate = new Date(new Date().setYear(today.getFullYear() - 18)),
        messages = {!! json_encode(trans('user.edit_profile.messages')) !!},
        endDate = new Date(new Date().setYear(today.getFullYear() + 100)),
        defaultCourseImg = `{{ asset('assets/dist/img/placeholder-img.png') }}`;
    var message = {
        upload_image_dimension: '{{ trans('user.messages.upload_image_dimension') }}'
    };
</script>
<script src="{{ mix('js/users/edit-profile.js') }}" type="text/javascript">
</script>
@if((!empty($wsDetails) && $wsDetails->is_cronofy == false) || ($roleData->slug == 'health_coach' && empty($wcDetails)) || ($roleData->slug == 'health_coach' && !empty($wcDetails) && $wcDetails->is_cronofy == false))
<script type="text/javascript">
$(document).ready(function() {

    $('body').on('focus',".custom-leave-from-date", function(){
        var previewelement = $(this).attr('data-previewelement');
        $(this).datepicker({
            startDate: today,
            endDate: endDate,
            autoclose: true,
            todayHighlight: true,
            format: 'yyyy-mm-dd',
        }).on('changeDate', function () {
            $('#to_date_'+previewelement).datepicker('setStartDate', new Date($(this).val()));
            $('#'+$(this).attr('id')).valid();
        });
    });
    $('body').on('focus',".custom-leave-to-date", function(){
        var previewelement = $(this).attr('data-previewelement');
        $(this).datepicker({
            startDate: today,
            endDate: endDate,
            autoclose: true,
            todayHighlight: true,
            format: 'yyyy-mm-dd',
        }).on('changeDate', function () {
            $('#from_date_'+previewelement).datepicker('setEndDate', new Date($(this).val()));
            $('#'+$(this).attr('id')).valid();
        });
    });
    $('body.sidebar-mini').addClass('sidebar-collapse');
});
</script>
@endif
@endsection
