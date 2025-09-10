@extends('layouts.app')

@section('after-styles')
<link href="{{asset('assets/plugins/datatables/dataTables.bootstrap5.min.css?var='.rand())}}" rel="stylesheet"/>
<link href="{{ asset('assets/plugins/datepicker/v1.9.0/css/bootstrap-datepicker3.min.css?var='.rand()) }}" rel="stylesheet"/>
@endsection
@section('content-header')
<!-- Content Header (Page header) -->
@include('admin.team.breadcrumb', [
    'appPageTitle' => trans('team.title.index_title'),
    'breadcrumb' => 'team.index',
    'create' => true,
    'setLimit' => true,
])
<!-- /.content-header -->
@endsection
@section('content')

<section class="content">
    <div class="container-fluid">
        <div class="card search-card">
            <div class="card-body pb-0">
                <h4 class="d-md-none">
                    {{trans('team.title.search')}}
                </h4>
                {{ Form::open(['route' => 'admin.teams.index', 'class' => 'form-horizontal', 'method'=>'get','role' => 'form', 'id'=>'teamSearch']) }}
                <div class="search-outer d-md-flex justify-content-between">
                    <div>
                        <div class="form-group">
                            {{ Form::text('teamName', request()->get('teamName'), ['class' => 'form-control', 'placeholder' => trans('team.form.placeholder.team'), 'id' => 'teamName', 'autocomplete' => 'off']) }}
                        </div>
                        @if($role->group == 'zevo' || ($role->group == 'reseller' && $companiesDetails->parent_id == null))
                        <div class="form-group">
                            {{ Form::select('company', $companies, request()->get('company'), ['class' => 'form-control select2', 'id'=>'company', 'placeholder' => trans('team.filter.select_company'),  'data-placeholder' => trans('team.filter.select_company'), 'autocomplete' => 'off', 'data-allow-clear' => 'true',  'target-data' => 'team'] ) }}
                        </div>
                        @else
                        <div class="form-group">
                            {{ Form::select('company', $companies, $company_id, ['class' => 'form-control select2','id'=>'company', 'placeholder' => trans('team.filter.select_company'), 'data-placeholder' => trans('team.filter.select_company'), 'autocomplete' => 'off', 'target-data' => 'team', 'disabled' => true] ) }}
                        </div>
                        @endif
                    </div>
                    <div class="search-actions align-self-start">
                        <button class="me-md-4 filter-apply-btn" type="submit">
                            {{ trans('buttons.general.apply') }}
                        </button>
                        <a class="filter-cancel-icon" href="{{ route('admin.teams.index') }}">
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
                        <button class="btn btn-primary" data-end="" data-start="" id="teamExport" data-title="Export Teams" type="button">
                            <span>
                                <i class="far fa-envelope me-3 align-middle">
                                </i>
                                {{trans('buttons.general.export')}}
                            </span>
                        </button>
                    </div>
                    <div class="table-responsive">
                        <table class="table custom-table" id="teamManagment">
                            <thead>
                                <tr>
                                    <th>
                                        {{ trans('team.table.updated_at') }}
                                    </th>
                                    <th>
                                        {{ trans('team.table.company') }}
                                    </th>
                                    <th>
                                        {{ trans('team.table.logo') }}
                                    </th>
                                    <th>
                                        {{ trans('team.table.team') }}
                                    </th>
                                    <th>
                                        {{ trans('team.table.team_member') }}
                                    </th>
                                    <th class="th-btn-2 no-sort">
                                        {{ trans('team.table.action') }}
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
<!-- Delete Model Popup -->
@include('admin.team.delete-model')
@if($hasOngoingChallenge)
<!-- Set Team Limit Model Popup -->
@include('admin.team.setteamlimit-model')
@endif
@include('admin.department.export_modal')
@endsection

@section('after-scripts')
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
        role = '{{ $role }}',
        company = '{{ $company }}';
    var url = {
        datatable: `{{ route('admin.teams.getTeams') }}`,
        delete: `{{ route('admin.teams.delete', ':id') }}`,
        teamExportUrl:`{{route('admin.teams.exportTeams')}}`,
    },
    condition = {
        companyNameVisibility: '{{ (($company_id == null || ( $role->group == 'reseller' && $companiesDetails->parent_id == null)) ? true : false)  }}',
    },
    pagination = {
        value: {{ $pagination }},
        previous: `{!! trans('buttons.pagination.previous') !!}`,
        next: `{!! trans('buttons.pagination.next') !!}`,
    },
    message = {
        teamDeleted: `{{ trans('team.modal.team_deleted') }}`,
        teamInUse: `{{ trans('team.modal.team_in_use') }}`,
        unableToDeleteTeam: `{{ trans('team.modal.unable_to_delete_team') }}`,
    };
</script>
<script src="{{ asset('js/team/index.js') }}" type="text/javascript">
</script>
@endsection
