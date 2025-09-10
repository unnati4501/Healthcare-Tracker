@extends('layouts.app')

@section('after-styles')
<link href="{{asset('assets/plugins/datatables/dataTables.bootstrap5.min.css?var='.rand())}}" rel="stylesheet"/>
<link href="{{ asset('assets/plugins/datepicker/v1.9.0/css/bootstrap-datepicker3.min.css?var='.rand()) }}" rel="stylesheet"/>
@endsection
@section('content-header')
<!-- Content Header (Page header) -->
@include('admin.department.breadcrumb', [
    'appPageTitle' => trans('department.title.index_title'),
    'breadcrumb' => 'department.index',
    'create' => true
])
<!-- /.content-header -->
@endsection
@section('content')
<section class="content">
    <div class="container-fluid">
        <div class="card search-card">
            <div class="card-body pb-0">
                <h4 class="d-md-none">
                    {{trans('departments.title.search')}}
                </h4>
                {{ Form::open(['route' => 'admin.departments.index', 'class' => 'form-horizontal', 'method' => 'get', 'role' => 'form', 'id' => 'departmentSearch']) }}
                <div class="search-outer d-md-flex justify-content-between">
                    <div>
                        <div class="form-group">
                            {{ Form::text('department', request()->get('department'), ['class' => 'form-control', 'placeholder' => trans('department.filter.department'), 'id' => 'department', 'autocomplete' => 'off']) }}
                        </div>
                        @if($company_col_visibility)
                        <div class="form-group">
                            {{ Form::select('company', $companies, request()->get('company'), ['class' => 'form-control select2', 'placeholder' => trans('department.filter.select_company'), 'data-placeholder' => trans('department.filter.select_company'), 'id' => 'company', 'data-allow-clear' => 'true']) }}
                        </div>
                        @endif
                    </div>
                    <div class="search-actions align-self-start">
                        <button class="me-md-4 filter-apply-btn" type="submit">
                            {{ trans('buttons.general.apply') }}
                        </button>
                        <a class="filter-cancel-icon" href="{{ route('admin.departments.index') }}">
                            <i class="far fa-times">
                            </i>
                            <span class="d-md-none ms-2 ms-md-0">
                                {{ trans('buttons.general.reset') }}
                            </span>
                        </a>
                    </div>
                </div>
                {{ Form::close() }}
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <div class="card-table-outer">
                    <div class="dt-buttons">
                        <button class="btn btn-primary" data-end="" data-start="" id="departmentExport" data-title="Export Departments" type="button">
                            <span>
                                <i class="far fa-envelope me-3 align-middle">
                                </i>
                                {{trans('buttons.general.export')}}
                            </span>
                        </button>
                    </div>
                    <div class="table-responsive">
                        <table class="table custom-table" id="departmentManagment">
                            <thead>
                                <tr>
                                    <th class="hidden">
                                        {{ trans('department.table.updated_at') }}
                                    </th>
                                    <th>
                                        {{ trans('department.table.company') }}
                                    </th>
                                    <th>
                                        {{ trans('department.table.department') }}
                                    </th>
                                    <th class="text-center">
                                        {{ trans('department.table.teams') }}
                                    </th>
                                    <th class="text-center">
                                        {{ trans('department.table.members') }}
                                    </th>
                                    <th class="text-center th-btn-3 no-sort">
                                        {{ trans('department.table.action') }}
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- Delete model popup -->
@include('admin.department.delete-modal')
@include('admin.department.export_modal')
@endsection

@section('after-scripts')
{{-- <script src="{{ asset('js/external/jquery.form.min.js?var='.rand()) }}" type="text/javascript">
</script> --}}
<script src="{{asset('assets/plugins/moment/moment.min.js?var='.rand()) }}">
</script>
<script src="{{asset('assets/plugins/moment/moment-timezone-with-data-10-year-range.js?var='.rand()) }}">
</script>
<script src="{{asset('assets/plugins/datatables/jquery.dataTables.min.js?var='.rand())}}">
</script>
<script src="{{asset('assets/plugins/datatables/dataTables.bootstrap5.min.js?var='.rand())}}">
</script>
<script src="{{ asset('assets/plugins/datepicker/bootstrap-datepicker.min.js?var='.rand()) }}">
</script>
<script type="text/javascript">
    var timezone = `{{ $timezone }}`,
        date_format = `{{ $date_format }}`,
        loginemail = '{{ $loginemail }}',
        role  =  '{{ $role }}',
        company  =  '{{ $company }}';
    var url = {
        datatable: `{{ route('admin.departments.getDepartments') }}`,
        delete: `{{ route('admin.departments.delete', ':id') }}`,
        departmentExportUrl:`{{route('admin.departments.exportDepartments')}}`,
    },
    pagination = {
        value: {{ $pagination }},
        previous: `{!! trans('buttons.pagination.previous') !!}`,
        next: `{!! trans('buttons.pagination.next') !!}`,
    },
    condition = {
        companyColVisibility: `{{ $company_col_visibility }}`,
    },
    message = {
        departmentDeleted: `{{ trans('department.modal.department_deleted') }}`,
        departmentInUse: `{{ trans('department.modal.department_in_use') }}`,
        unableToDeleteDepartment: `{{ trans('department.modal.unable_to_delete_department') }}`,
    };
</script>
<script src="{{ asset('js/department/index.js') }}" type="text/javascript">
</script>
@endsection
