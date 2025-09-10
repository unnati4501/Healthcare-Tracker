@extends('layouts.app')

@section('after-styles')
<!-- DataTables -->
<link href="{{ asset('assets/plugins/datatables/dataTables.bootstrap5.min.css?var='.rand()) }}" rel="stylesheet"/>
@endsection

@section('content-header')
<!-- Content Header (Page header) -->
@include('admin.cronofy.clientlist.breadcrumb', [
  'mainTitle' => trans('Cronofy.client_list.title.index'),
  'breadcrumb' => Breadcrumbs::render('cronofy.clientlist.index'),
])
<!-- /.content-header -->
@endsection

@section('content')
<section class="content">
    <div class="container-fluid">
        <!-- .search-block -->
        <div class="card search-card">
            <div class="card-body pb-0">
                {{ Form::open(['route' => 'admin.cronofy.clientlist.index', 'class' => 'form-horizontal', 'method' => 'get', 'role' => 'frmClientList']) }}
                <div class="search-outer d-md-flex justify-content-between">
                    <div>
                        <div class="form-group">
                            {{ Form::text('name', request()->get('name'), ['class' => 'form-control', 'placeholder' => trans('Cronofy.client_list.filters.name'), 'id' => 'name', 'autocomplete' => 'off']) }}
                        </div>
                        <div class="form-group">
                            {{ Form::text('email', request()->get('email'), ['class' => 'form-control', 'placeholder' => trans('Cronofy.client_list.filters.email'), 'id' => 'email', 'autocomplete' => 'off']) }}
                        </div>
                        @if($role == 'super_admin')
                        <div class="form-group">
                            {{ Form::select('ws', $getWellbeingSpecialist, request()->get('ws'), ['class' => 'form-control select2', 'id' => 'ws', 'placeholder' => "", 'data-placeholder' => "Select wellbeing specialist", 'data-allow-clear' => 'true']) }}
                        </div>
                        @endif
                        <div class="form-group">
                            {{ Form::select('company', $companies, request()->get('company'), ['class' => 'form-control select2', 'id' => 'company', 'placeholder' => trans('Cronofy.client_list.filters.company'), 'data-placeholder' => trans('Cronofy.client_list.filters.company'), 'data-allow-clear' => 'true']) }}
                        </div>
                        <div class="form-group">
                            {{ Form::select('location', $companyLocation, request()->get('location'), ['class' => 'form-control select2', 'id' => 'location', 'disabled' => (Request()->get('company') != null ? false : true ), 'placeholder' => trans('Cronofy.client_list.filters.location'), 'data-placeholder' => trans('Cronofy.client_list.filters.location'), 'data-allow-clear' => 'true']) }}
                        </div>
                    </div>
                    <div class="search-actions align-self-start">
                        <button class="me-md-4 filter-apply-btn" type="submit">
                            {{ trans('buttons.general.apply') }}
                        </button>
                        <a class="filter-cancel-icon" href="{{ route('admin.cronofy.clientlist.index') }}">
                            <i class="far fa-times">
                            </i>
                        </a>
                    </div>
                </div>
                {{ Form::close() }}
            </div>
        </div>
        <a class="btn btn-primary filter-btn" href="javascript:void(0);">
            <i class="far fa-filter me-2 align-middle">
            </i>
            <span class="align-middle">
                {{ trans('buttons.general.filter') }}
            </span>
        </a>
        <!-- /.search-block -->
        <!-- .grid strt -->
        <div class="card">
            <div class="card-body">
                <div class="card-table-outer">
                    @if($role == 'super_admin' || $role == 'wellbeing_team_lead')
                    <div class="dt-buttons">
                        <button class="btn btn-primary exportClientList" data-end="" data-start="" data-tab="steps" id="exportClientList" data-title="Export Client" type="button">
                            <span>
                                <i class="far fa-envelope me-3 align-middle">
                                </i>
                                {{trans('buttons.general.export')}}
                            </span>
                        </button>
                    </div>
                    @endif
                    <div class="table-responsive">
                        <table class="table custom-table" id="clientListManagement">
                            <thead>
                                <tr>
                                    <th>
                                        {{ trans('Cronofy.client_list.table.client_name') }}
                                    </th>
                                    <th>
                                        {{ trans('Cronofy.client_list.table.email') }}
                                    </th>
                                    <th>
                                        {{ trans('Cronofy.client_list.table.company_name') }}
                                    </th>
                                    <th>
                                        {{ trans('Cronofy.client_list.table.location_name') }}
                                    </th>
                                    <th class="text-center">
                                        {{ trans('Cronofy.client_list.table.completed_session') }}
                                    </th>
                                    <th class="text-center">
                                        {{ trans('Cronofy.client_list.table.upcoming') }}
                                    </th>
                                    <th>
                                        {{ trans('Cronofy.client_list.table.cancelled_session') }}
                                    </th>
                                    <th>
                                        {{ trans('Cronofy.client_list.table.short_canceled') }}
                                    </th>
                                    <th>
                                        {{ trans('Cronofy.client_list.table.no_show') }}
                                    </th>
                                    <th class="no-sort th-btn-2">
                                        {{ trans('Cronofy.client_list.table.action') }}
                                    </th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <!-- /.grid strt -->
    </div>
</section>
@include('admin.cronofy.clientlist.export-clientlist')
@endsection
<!-- include datatable css -->
@section('after-scripts')
<!-- DataTables -->
<script src="{{ asset('js/external/jquery.form.min.js?var='.rand()) }}" type="text/javascript">
</script>
<script src="{{ asset('assets/plugins/datatables/jquery.dataTables.min.js?var='.rand()) }}">
</script>
<script src="{{ asset('assets/plugins/datatables/dataTables.bootstrap5.min.js?var='.rand()) }}">
</script>
<script type="text/javascript">
    var loginemail = '{{ $loginemail }}';
    var url = {
        datatable: `{{ route('admin.cronofy.clientlist.get-clients') }}`,
        getLocationByCompany: `{{ route('admin.cronofy.clientlist.get-location', ":id") }}`,
        exportClient: `{{ route('admin.cronofy.clientlist.export-client') }}`,
    },
    pagination = {
        value: {{ $pagination }},
        previous: `{!! trans('buttons.pagination.previous') !!}`,
        next: `{!! trans('buttons.pagination.next') !!}`,
        entry_per_page: `{!! trans('buttons.pagination.entry_per_page') !!}`,
    },
    message = {
        noDataExists : `{{ trans('Cronofy.client_list.messages.no_data_exists') }}`,
        somethingWentWrong: `{{ trans('Cronofy.messages.something_wrong') }}`,
    };
    role = `{{ $role }}`;
</script>
<script src="{{ mix('js/cronofy/clientlist/index.js') }}">
</script>
@endsection
