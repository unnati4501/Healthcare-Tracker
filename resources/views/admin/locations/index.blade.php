@extends('layouts.app')

@section('after-styles')
<link href="{{asset('assets/plugins/datatables/dataTables.bootstrap5.min.css?var='.rand())}}" rel="stylesheet"/>
<link href="{{ asset('assets/plugins/datepicker/v1.9.0/css/bootstrap-datepicker3.min.css?var='.rand()) }}" rel="stylesheet"/>
@endsection
@section('content-header')
<!-- Content Header (Page header) -->
@include('admin.locations.breadcrumb', [
    'mainTitle'   => trans('location.title.index_title'),
    'breadcrumb'  => 'location.index',
    'create'      => true,
])
<!-- /.content-header -->
@endsection
@section('content')
<section class="content">
    <div class="container-fluid">
        <div class="card search-card">
            <div class="card-body pb-0">
                {{ Form::open(['route' => 'admin.locations.index', 'class' => 'form-horizontal', 'method' => 'get', 'role' => 'form', 'id' => 'companySearch']) }}
                <div class="search-outer d-md-flex justify-content-between">
                    <div>
                        <div class="form-group">
                            {{ Form::text('locationName', request()->get('locationName'), ['class' => 'form-control', 'placeholder' => trans('location.filter.search_name'), 'data-placeholder' => trans('location.filter.search_name'), 'id' => 'locationName', 'autocomplete' => 'off']) }}
                        </div>
                        @if($role->group == 'zevo' || ($role->group == 'reseller' && $companiesDetails->parent_id == null))
                        <div class="form-group">
                            {{ Form::select('company', $companies, request()->get('company'), ['class' => 'form-control select2', 'id'=>'companyid', 'placeholder' => trans('team.filter.select_company'),  'data-placeholder' => trans('team.filter.select_company'), 'autocomplete' => 'off', 'data-allow-clear' => 'true',  'target-data' => 'team'] ) }}
                        </div>
                        @else
                        <div class="form-group">
                            {{ Form::select('company', $companies, $company_id, ['class' => 'form-control select2','id'=>'companyid', 'placeholder' => trans('team.filter.select_company'), 'data-placeholder' => trans('team.filter.select_company'), 'autocomplete' => 'off', 'target-data' => 'team', 'disabled' => true] ) }}
                        </div>
                        @endif
                        <div class="form-group">
                            {{ Form::select('country', $countries, request()->get('country'), ['class' => 'form-control select2','id'=>'country', 'placeholder' => trans('location.filter.search_country'), 'data-placeholder' => trans('location.filter.search_country'), 'target-data' => 'timezone', 'autocomplete' => 'off'] ) }}
                        </div>
                        <div class="form-group">
                            {{ Form::select('timezone', ($timezone ?? []), request()->get('timezone'), ['class' => 'form-control select2','id'=>'timezone',"style"=>"width: 100%;", 'placeholder' => trans('location.filter.search_timezone'), 'data-placeholder'=>trans('location.filter.search_timezone'), 'autocomplete' => 'off', 'disabled' => true] ) }}
                        </div>
                    </div>
                    <div class="search-actions align-self-start">
                        <button class="me-md-4 filter-apply-btn" type="submit">
                            {{ trans('buttons.general.apply') }}
                        </button>
                        <a class="filter-cancel-icon" href="{{ route('admin.locations.index') }}">
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
                        <button class="btn btn-primary" data-end="" data-start="" id="locationExport" data-title="Export Locations" type="button">
                            <span>
                                <i class="far fa-envelope me-3 align-middle">
                                </i>
                                {{trans('buttons.general.export')}}
                            </span>
                        </button>
                    </div>
                    <div class="table-responsive">
                        <table class="table custom-table" id="locationManagment">
                            <thead>
                                <tr>
                                    <th class="hidden">
                                        {{trans('location.table.updated_at')}}
                                    </th>
                                    <th>
                                        {{trans('location.table.company')}}
                                    </th>
                                    <th>
                                        {{trans('location.table.location_name')}}
                                    </th>
                                    <th>
                                        {{trans('location.table.country')}}
                                    </th>
                                    <th>
                                        {{trans('location.table.county')}}
                                    </th>
                                    <th>
                                        {{trans('location.table.timezone')}}
                                    </th>
                                    <th>
                                        {{trans('location.table.address')}}
                                    </th>
                                    <th class="text-center th-btn-2 no-sort">
                                        {{trans('location.table.action')}}
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
<!-- Delete location model popup -->
@include('admin.locations.delete-modal')
@include('admin.locations.export_modal')
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
    var loginemail = '{{ $loginemail }}';
    var url = {
        datatable: `{{ route('admin.locations.getLocations') }}`,
        delete: `{{ route('admin.locations.delete', ':id') }}`,
        timezoneUrl: '{{ route("admin.ajax.timezones", ":id") }}',
        locationExportUrl:`{{route('admin.locations.exportLocations')}}`,
        stateUrl : '{{ route("admin.ajax.states", ":id") }}'
    },
    condition = {
        timezone: `{{ request()->get('timezone') }}`,
        companyColVisibility: `{{ $company_col_visibility }}`,
    },
    pagination = {
        value: {{ $pagination }},
        previous: `{!! trans('buttons.pagination.previous') !!}`,
        next: `{!! trans('buttons.pagination.next') !!}`,
    },
    message = {
        locationDeleted: `{{ trans('location.modal.location_deleted') }}`,
        locationInUse: `{{ trans('location.modal.location_in_use') }}`,
        unableToDeleteLocation: `{{ trans('location.modal.unable_to_delete_location') }}`,
    };
</script>
<script src="{{ asset('js/location/index.js') }}" type="text/javascript">
</script>
@endsection
