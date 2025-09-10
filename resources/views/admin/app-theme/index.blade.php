@extends('layouts.app')

@section('after-styles')
<style type="text/css">
    .hidden { display: none; }
</style>
<link href="{{asset('assets/plugins/datatables/dataTables.bootstrap5.min.css?var='.rand())}}" rel="stylesheet"/>
@endsection
@section('content-header')
<!-- Content Header (Page header) -->
@include('admin.app-theme.breadcrumb', [
    'appPageTitle'  => trans('appthemes.title.index_title'),
    'breadcrumb'    => 'apptheme.index',
    'create'        => true,
])
<!-- /.content-header -->
@endsection
@section('content')
<section class="content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-body">
                <div class="card-table-outer" id="themeManagement-wrap">
                    <div class="table-responsive">
                        <table class="table custom-table" id="themeManagement">
                            <thead>
                                <tr>
                                    <th>
                                        {{ trans('appthemes.table.theme_name') }}
                                    </th>
                                    <th class="no-sort">
                                        {{ trans('appthemes.table.json_file') }}
                                    </th>
                                    <th class="text-center th-btn-2 no-sort">
                                        {{ trans('appthemes.table.action') }}
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
@include('admin.app-theme.delete-modal')
@endsection
@section('after-scripts')
<script src="{{asset('assets/plugins/datatables/jquery.dataTables.min.js?var='.rand())}}">
</script>
<script src="{{asset('assets/plugins/datatables/dataTables.bootstrap5.min.js?var='.rand())}}">
</script>
<script type="text/javascript">
var url = {
    datatable: `{{ route('admin.app-themes.get-teams') }}`,
    delete: `{{ route('admin.app-themes.delete', [':id']) }}`,
},
pagination = {
    value: {{ $pagination }},
    previous: `{!! trans('buttons.pagination.previous') !!}`,
    next: `{!! trans('buttons.pagination.next') !!}`,
},
message = {
    data_deleted_failed: `{{ trans('appthemes.message.data_deleted_failed') }}`,
};
</script>
<script src="{{ asset('js/appthemes/index.js') }}" type="text/javascript">
</script>
@endsection
