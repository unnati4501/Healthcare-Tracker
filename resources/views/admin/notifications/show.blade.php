@extends('layouts.app')

@section('after-styles')
<!-- DataTables -->
<link href="{{asset('assets/plugins/datatables/dataTables.bootstrap5.min.css?var='.rand())}}" rel="stylesheet"/>
@endsection
@section('content-header')
<!-- Content Header (Page header) -->
@include('admin.notifications.breadcrumb', [
    'mainTitle'  => trans('notificationsettings.title.details'),
    'breadcrumb' => 'notification.details',
    'back'       => true,
    'create'     => false,
])
<!-- /.content-header -->
@endsection
@section('content')
<section class="content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-header detailed-header has-elements">
                <div class="d-flex flex-wrap">
                <div class="form-group">
                    <div>
                        <i class="fa fa-circle text-warning me-2"></i><b>{{trans('notificationsettings.title.title')}}</b>: {{$recordData->title}}</div>
                    </div>
                    <div class="form-group">
                        <div><i class="fa fa-circle text-warning me-2"></i><b>{{trans('notificationsettings.title.message')}}</b> : {!! $recordData->message !!}</div>
                    </div>
                </div>

            </div>
            <div class="card-body">
                <h3 class="card-inner-title border-0 pb-0">{{trans('notificationsettings.title.notification_recipients')}}</h3>
                <div class="card-table-outer" id="userlist-wrap">
                    <div class="table-responsive">
                        <table class="table custom-table" id="userlist">
                            <thead>
                                <tr>
                                    <th class="text-center" style="display: none">
                                        {{trans('notificationsettings.table.updated_at')}}
                                    </th>
                                    <th>
                                        {{trans('notificationsettings.table.name')}}
                                    </th>
                                    <th>
                                        {{trans('notificationsettings.table.email')}}
                                    </th>
                                    <th>
                                        {{trans('notificationsettings.table.sent')}}
                                    </th>
                                    <th>
                                        {{trans('notificationsettings.table.received')}}
                                    </th>
                                    <th>
                                        {{trans('notificationsettings.table.read')}}
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <!-- /.card-body-->
        </div>
        <!-- /.row -->
    </div>
    <!-- /.container-fluid -->
</section>
@endsection
<!-- include datatable css -->
@section('after-scripts')
<!-- DataTables -->
<script src="{{asset('assets/plugins/datatables/jquery.dataTables.min.js?var='.rand())}}">
</script>
<script src="{{asset('assets/plugins/datatables/dataTables.bootstrap5.min.js?var='.rand())}}">
</script>
<script type="text/javascript">
var url = {
    datatable: `{{ route('admin.notifications.getRecipientsList',$recordData->id) }}`,
},
pagination = {
    value: {{ $pagination }},
    previous: `{!! trans('buttons.pagination.previous') !!}`,
    next: `{!! trans('buttons.pagination.next') !!}`,
};
</script>
<script src="{{ mix('js/notifications/details.js') }}">
</script>
@endsection
