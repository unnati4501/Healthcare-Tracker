@extends('layouts.app')
@section('content-header')
<!-- Content Header (Page header) -->
@include('admin.department.breadcrumb', [
    'appPageTitle' => trans('department.title.edit_form_title'),
    'breadcrumb' => 'department.edit',
    'create' => false
])
<!-- /.content-header -->
@endsection
@section('content')
<section class="content">
    <div class="container-fluid">
        {{ Form::open(['route' => ['admin.departments.update', $department->id], 'class' => 'form-horizontal zevo_form_submit', 'method'=>'PATCH', 'role' => 'form', 'id' => 'departmentEdit']) }}
        <div class="card form-card">
            <div class="card-body">
                <div class="row justify-content-center justify-content-md-start">
                    @include('admin.department.form', ['edit' => true])
                </div>
            </div>
            <div class="card-footer">
                <div class="save-cancel-wrap">
                    <a class="btn btn-outline-primary" href="{!! route('admin.departments.index') !!}">
                        {{ trans('buttons.general.cancel') }}
                    </a>
                    <button class="btn btn-primary" type="submit">
                        {{ trans('buttons.general.update') }}
                    </button>
                </div>
            </div>
        </div>
        {{ Form::close() }}
    </div>
</section>
@endsection

@section('after-scripts')
{!! JsValidator::formRequest('App\Http\Requests\Admin\EditDepartmentRequest', '#departmentEdit') !!}
<script src="{{ asset('js/external/jquery.form.min.js?var='.rand()) }}">
</script>
<script type="text/javascript">
    var companieswithTeamLimit = {!! $companieswithTeamLimit !!},
        locationsBeingUsed = {!! $locationsBeingUsed !!},
        askForAutoTeamCreation = '{{ $askForAutoTeamCreation }}',
        departmentUrl = '{{ route('admin.departments.index') }}';
    var error = {
        deptRemove: `{{ trans('labels.department.dept_location_error') }}`,
    },
    validation = {
        processing: `{{ trans('department.message.processing') }}`,
    },
    message = {
        employeeCountError: `{{ trans('department.validation.employee_count_error') }}`,
        employeeCountLength: `{{ trans('department.validation.employee_count_length') }}`,
        employeeCountGreater: `{{ trans('department.validation.employee_count_greater') }}`,
        teamUnique: `{{ trans('department.validation.team_unique') }}`,
        somethingWrongTryAgain: `{{ trans('department.message.something_wrong_try_again') }}`,
        namingConvention: `{{ trans('department.validation.naming_convention') }}`,
        namingConventionParam: `{{ trans('department.validation.naming_convention_param') }}`,
    };
</script>
<script src="{{ asset('js/department/edit-department-index.js') }}">
</script>
@if($askForAutoTeamCreation)
<script src="{{ mix('js/department/edit-department.js') }}">
</script>
@endif
@endsection
