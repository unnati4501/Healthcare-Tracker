@extends('layouts.app')

@section('after-styles')
<link href="{{ asset('assets/plugins/datatables/dataTables.bootstrap5.min.css?var='.rand()) }}" rel="stylesheet"/>
@endsection
@section('content-header')
<!-- Content Header (Page header) -->
@include('admin.broadcast-message.breadcrumb', [
    'appPageTitle' => trans('broadcast.title.index_title'),
    'breadcrumb' => 'broadcast.index',
    'create' => true,
])
<!-- /.content-header -->
@endsection
@section('content')
<section class="content">
    <div class="container-fluid">
        <div class="card search-card">
            <div class="card-body pb-0">
                <h4 class="d-md-none">
                    {{ trans('broadcast.title.filter') }}
                </h4>
                {{ Form::open(['route' => 'admin.broadcast-message.index', 'class' => 'form-horizontal', 'method' => 'get', 'role' => 'form', 'id' => 'broadcastSearch']) }}
                <div class="search-outer d-md-flex justify-content-between">
                    <div>
                        <div class="form-group">
                            {{ Form::text('title', request()->get('title'), ['class' => 'form-control', 'placeholder' => trans('broadcast.filter.search_by_title'), 'id' => 'title', 'autocomplete' => 'off']) }}
                        </div>
                        <div class="form-group">
                            {{ Form::select('group_type', $groupsType, request()->get('group_type'), ['class' => 'form-control select2', 'id' => 'group_type', 'placeholder' => trans('broadcast.filter.select_group_type'), 'data-placeholder' => trans('broadcast.filter.select_group_type'), 'data-allowclear' => 'true'] ) }}
                        </div>
                        <div class="form-group">
                            {{ Form::text('group_name', request()->get('group_name'), ['class' => 'form-control', 'placeholder' => trans('broadcast.filter.search_by_group_name'), 'id' => 'group_name', 'autocomplete' => 'off']) }}
                        </div>
                        <div class="form-group">
                            {{ Form::select('status', $broadcastStatus, request()->get('status'), ['class' => 'form-control select2', 'id' => 'status', 'placeholder' => trans('broadcast.filter.select_broadcast_type'), 'data-placeholder' => trans('broadcast.filter.select_broadcast_type'), 'data-allowclear' => 'true'] ) }}
                        </div>
                    </div>
                    <div class="search-actions align-self-start">
                        <button class="me-md-4 filter-apply-btn" type="submit">
                            {{ trans('buttons.general.apply') }}
                        </button>
                        <a class="filter-cancel-icon" href="{{ route('admin.broadcast-message.index') }}">
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
                <div class="card-table-outer" id="broadcastManagment-wrap">
                    <div class="table-responsive">
                        <table class="table custom-table" id="broadcastManagment">
                            <thead>
                                <tr>
                                    <th>
                                        {{ trans('broadcast.table.title') }}
                                    </th>
                                    <th style="width: 30%;">
                                        {{ trans('broadcast.table.message') }}
                                    </th>
                                    <th>
                                        {{ trans('broadcast.table.created_at') }}
                                    </th>
                                    <th>
                                        {{ trans('broadcast.table.group_type') }}
                                    </th>
                                    <th>
                                        {{ trans('broadcast.table.group_name') }}
                                    </th>
                                    <th>
                                        {{ trans('broadcast.table.status') }}
                                    </th>
                                    <th class="th-btn-2 no-sort">
                                        {{ trans('broadcast.table.action') }}
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
@include('admin.broadcast-message.delete-modal')
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
<script type="text/javascript">
var timezone = `{{ $timezone }}`
    format = `{{ $format }}`;
var url = {
    datatable: `{{ route('admin.broadcast-message.get-broadcasts') }}`,
    delete: `{{ route('admin.broadcast-message.delete', '/') }}`,
},
pagination = {
    value: {{ $pagination }},
    previous: `{!! trans('buttons.pagination.previous') !!}`,
    next: `{!! trans('buttons.pagination.next') !!}`,
},
message = {
    broadcast_deleted: `{{ trans('broadcast.message.broadcast_deleted') }}`,
    delete_broadcast_message: `{{ trans('broadcast.message.delete_broadcast_message') }}`,
    failed_delete_broadcast: `{{ trans('broadcast.message.failed_delete_broadcast') }}`,
};
</script>
<script src="{{ asset('js/broadcast/index.js') }}" type="text/javascript">
</script>
@endsection
