@extends('layouts.app')

@section('after-styles')
<!-- DataTables -->
<link href="{{ asset('assets/plugins/datatables/dataTables.bootstrap5.min.css?var='.rand()) }}" rel="stylesheet"/>
@endsection

@section('content-header')
<!-- Content Header (Page header) -->
@include('admin.admin-alerts.breadcrumb', [
  'mainTitle' => trans('page_title.admin-alerts.index'),
  'breadcrumb' => 'admin-alerts.index'
])
<!-- /.content-header -->
@endsection

@section('content')
<section class="content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-body">
                <div class="card-table-outer" id="adminAlertList-wrap">
                    <div class="table-responsive">
                        <table class="table custom-table" id="adminAlertList">
                            <thead>
                                <tr>
                                    <th>
                                        {{ trans('adminalert.form.labels.alert_name') }}
                                    </th>
                                    <th>
                                        {{ trans('adminalert.form.labels.notify_users') }}
                                    </th>
                                    <th class="text-center th-btn-3 no-sort">
                                        {{ trans('adminalert.form.labels.action') }}
                                    </th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@include('admin.admin-alerts.userlist-model')
@endsection

<!-- include datatable css -->
@section('after-scripts')
<!-- DataTables -->
<script src="{{asset('assets/plugins/datatables/jquery.dataTables.min.js?var='.rand())}}" type="text/javascript">
</script>
<script src="{{asset('assets/plugins/datatables/dataTables.bootstrap5.min.js?var='.rand())}}" type="text/javascript">
</script>
<script type="text/javascript">
    var url = {
        datatable: `{{ route('admin.admin-alerts.getAdminAlerts') }}`,
    },
    pagination = {
        value: {{ $pagination }},
        previous: `{!! trans('buttons.pagination.previous') !!}`,
        next: `{!! trans('buttons.pagination.next') !!}`,
    };
</script>
<script src="{{ mix('js/admin-alerts/index.js') }}">
</script>
@endsection
