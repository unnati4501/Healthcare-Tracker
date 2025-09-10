@extends('layouts.app')

@section('after-styles')
<!-- DataTables -->
<link href="{{asset('assets/plugins/datatables/dataTables.bootstrap5.min.css?var='.rand())}}" rel="stylesheet"/>
@endsection
@section('content-header')
<!-- Content Header (Page header) -->
@include('admin.appsettings.breadcrumb', [
    'appPageTitle' => trans('appsettings.title.index_title'),
    'breadcrumb' => 'appsettings.index',
    'changeappsetting' => true,
])
<!-- /.content-header -->
@endsection
@section('content')
<section class="content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-body">
                <div class="card-table-outer" id="roleManagement-wrap">
                    <div class="table-responsive">
                        <table id="AppSettingsManagment" class="table custom-table">
                            <thead>
                                <tr>
                                    <th class="text-center" style="display: none">{{trans('appsettings.table.updated_at')}}</th>
                                    <th class="no-sorting-arrow" width="110">
                                        {{trans('appsettings.table.sr_no')}}</th>
                                    <th>{{trans('appsettings.table.app_key')}}</th>
                                    <th>{{trans('appsettings.table.app_value')}}</th>
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
@endsection
<!-- include datatable css -->
@section('after-scripts')
<script src="{{asset('assets/plugins/datatables/jquery.dataTables.min.js?var='.rand())}}">
</script>
<script src="{{asset('assets/plugins/datatables/dataTables.bootstrap5.min.js?var='.rand())}}">
</script>
<script type="text/javascript">
var url = {
    datatable: `{{ route('admin.appsettings.getAppSettings') }}`,
},
pagination = {
    value: {{ $pagination }},
    previous: `{!! trans('buttons.pagination.previous') !!}`,
    next: `{!! trans('buttons.pagination.next') !!}`,
},
message = {
    select_group: `{{ trans('appsettings.message.select_group') }}`,
};
</script>
<script src="{{ asset('js/appsettings/index.js') }}" type="text/javascript">
</script>
@endsection