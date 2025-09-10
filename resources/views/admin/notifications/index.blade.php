@extends('layouts.app')
@section('after-styles')
<!-- DataTables -->
<link href="{{asset('assets/plugins/datatables/dataTables.bootstrap5.min.css?var='.rand())}}" rel="stylesheet"/>
@endsection
@section('content-header')
<!-- Content Header (Page header) -->
@include('admin.notifications.breadcrumb', [
    'mainTitle'  => trans('notificationsettings.title.index_title'),
    'breadcrumb' => 'notification.index',
    'back'       => false,
    'create'     => true,
])
<!-- /.content-header -->
@endsection
@section('content')
<section class="content">
    <div class="container-fluid">
        <div class="card search-card">
            <div class="card-body pb-0">
                <h4 class="d-md-none">{{ trans('notificationsettings.title.filter') }}</h4>
                {{ Form::open(['route' => 'admin.notifications.index', 'class' => 'form-horizontal', 'method'=>'get','notification' => 'form', 'id'=>'notificationSearch']) }}
                <div class="search-outer d-md-flex justify-content-between">
                    <div>
                        <div class="form-group">
                            {{ Form::text('recordTitle', request()->get('recordTitle'), ['class' => 'form-control', 'placeholder' => trans('notificationsettings.filter.search_by_title'), 'id' => 'recordTitle', 'autocomplete' => 'off']) }}
                        </div>
                        <div class="form-group">
                            {{ Form::text('recordMsg', request()->get('recordMsg'), ['class' => 'form-control', 'placeholder' => trans('notificationsettings.filter.search_by_message'), 'id' => 'recordMsg', 'autocomplete' => 'off']) }}
                        </div>
                    </div>
                    <div class="search-actions align-self-start">
                        <button class="me-md-4 filter-apply-btn" type="submit">{{trans('buttons.general.apply')}}</button>
                        <a class="filter-cancel-icon" href="{{ route('admin.notifications.index') }}">
                            <i class="far fa-times"></i>
                        <span class="d-md-none ms-2 ms-md-0">{{trans('buttons.general.reset')}}</span>
                    </a>
                    </div>
                </div>
                {{ Form::close() }}
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <div class="card-table-outer" id="notificationManagment-wrap">
                    <div class="table-responsive">
                        <table class="table custom-table" id="notificationManagment">
                            <thead>
                                <tr>
                                    <th class="text-center" style="display: none">
                                        {{ trans('notificationsettings.table.updated_at') }}
                                    </th>
                                    <th>
                                        {{ trans('notificationsettings.table.title') }}
                                    </th>
                                    <th>
                                        {{ trans('notificationsettings.table.creator') }}
                                    </th>
                                    <th>
                                        {{ trans('notificationsettings.table.message') }}
                                    </th>
                                    <th class="text-center th-btn-2 no-sort">
                                        {{trans('notificationsettings.table.action')}}
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <!-- /.card-body -->
        </div>
    </div>
</section>
@include('admin.notifications.delete-modal')
@endsection
<!-- include datatable css -->
@section('after-scripts')
<script src="{{asset('assets/plugins/datatables/jquery.dataTables.min.js?var='.rand())}}">
</script>
<script src="{{asset('assets/plugins/datatables/dataTables.bootstrap5.min.js?var='.rand())}}">
</script>
<script type="text/javascript">
var url = {
    datatable: `{{ route('admin.notifications.getNotifications') }}`,
    delete: `{{route('admin.notifications.delete','/')}}`,
},
pagination = {
    value: {{ $pagination }},
    previous: `{!! trans('buttons.pagination.previous') !!}`,
    next: `{!! trans('buttons.pagination.next') !!}`,
},
message = {
    notification_deleted: `{{ trans('notificationsettings.message.notification_deleted') }}`,
    notification_in_use: `{{ trans('notificationsettings.message.notification_in_use') }}`,
    delete_error: `{{ trans('notificationsettings.message.delete_error') }}`,
};
</script>
<script src="{{ mix('js/notifications/index.js') }}">
</script>
@endsection
