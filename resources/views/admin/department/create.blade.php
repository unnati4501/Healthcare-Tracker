@extends('layouts.app')

@section('content-header')
<!-- Content Header (Page header) -->
@include('admin.department.breadcrumb', [
    'appPageTitle' => trans('department.title.add_form_title'),
    'breadcrumb' => 'department.create',
    'create' => false
])
<!-- /.content-header -->
@endsection
@section('content')
<section class="content">
    <div class="container-fluid">
        {{ Form::open(['route' => 'admin.departments.store', 'class' => 'form-horizontal zevo_form_submit', 'method' => 'post', 'role' => 'form', 'id' => 'departmentAdd']) }}
        <div class="card form-card">
            <div class="card-body">
                <div class="row justify-content-center justify-content-md-start">
                    @include('admin.department.form', ['edit' => false])
                </div>
            </div>
            <div class="card-footer">
                <div class="save-cancel-wrap">
                    <a class="btn btn-outline-primary" href="{!! route('admin.departments.index') !!}">
                        {{ trans('buttons.general.cancel') }}
                    </a>
                    <button class="btn btn-primary" type="submit">
                        {{ trans('buttons.general.save') }}
                    </button>
                </div>
            </div>
        </div>
        {{ Form::close() }}
    </div>
</section>
@endsection

@section('after-scripts')
{!! JsValidator::formRequest('App\Http\Requests\Admin\CreateDepartmentRequest', '#departmentAdd') !!}
<script src="{{ asset('js/external/jquery.form.min.js?var='.rand()) }}">
</script>
<script type="text/javascript">
    var companyLocationUrl = '{{ route("admin.ajax.companyLocation", ":id") }}',
        companieswithTeamLimit = {!! $companieswithTeamLimit !!}
        departmentUrl = '{{ route('admin.departments.index') }}';
    var data = {
        oldCompanyId: `{{old('company_id')}}`,
    }
    validation = {
        processing: `{{ trans('department.message.processing') }}`,
    },
    message = {
        employeeCountError: `{{ trans('department.validation.employee_count_error') }}`,
        employeeCountLength: `{{ trans('department.validation.employee_count_length') }}`,
        employeeCountGreater: `{{ trans('department.validation.employee_count_greater') }}`,
        teamUnique: `{{ trans('department.validation.team_unique') }}`,
        somethingWrongTryAgain: `{{ trans('department.message.something_wrong_try_again') }}`,
    };
</script>
<script src="{{ asset('js/department/create-department-index.js') }}">
</script>
@if($askForAutoTeamCreation)
<script src="{{ mix('js/department/create-department.js') }}">
</script>
@endif
@endsection
