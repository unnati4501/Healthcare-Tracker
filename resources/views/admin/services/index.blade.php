@extends('layouts.app')

@section('after-styles')
<!-- DataTables -->
<link href="{{asset('assets/plugins/datatables/dataTables.bootstrap5.min.css?var='.rand())}}" rel="stylesheet"/>
@endsection

@section('content-header')
<!-- Content Header (Page header) -->
@include('admin.services.breadcrumb', [
  'mainTitle' => trans('services.title.manage'),
  'breadcrumb' => Breadcrumbs::render('services.index'),
  'create' => true
])
<!-- /.content-header -->
@endsection

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-body">
            <div class="card-table-outer" id="serviceManagment-wrap">
                <div class="table-responsive">
                    <table class="table custom-table" id="serviceManagment">
                        <thead>
                            <tr>
                                <th>
                                    {{ trans('services.table.updated_at') }}
                                </th>
                                <th class="no-sort th-btn-2">
                                </th>
                                <th>
                                    {{ trans('services.table.name') }}
                                </th>
                                <th>
                                    {{ trans('services.table.service_type') }}
                                </th>
                                <th>
                                    {{ trans('services.table.total') }}
                                </th>
                                <th>
                                    {{ trans('services.table.wellbeing_specialist') }}
                                </th>
                                <th class="th-btn-4 no-sort">
                                    {{ trans('services.table.action') }}
                                </th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@include('admin.services.delete-modal')
@include('admin.services.subcategoryvisibility-model')
@include('admin.services.wellbeing-specialist-model')

@endsection
<!-- include datatable css -->
@section('after-scripts')
<!-- DataTables -->
<script src="{{asset('assets/plugins/datatables/jquery.dataTables.min.js?var='.rand())}}">
</script>
<script src="{{asset('assets/plugins/datatables/dataTables.bootstrap5.min.js?var='.rand())}}">
</script>
<script type="text/javascript">
    var pagination = {{$pagination}};
    var servicesListUrl = '{{ route('admin.services.getServices') }}';
    var serviceDeleteUrl = "{{ route('admin.services.delete','/') }}";
    var deletedMessage = "{{ trans('services.messages.deleted') }}";
    var inUseMessage = "{{ trans('services.messages.in_use') }}";
    var unauthorizedMessage = "{{ trans('services.messages.unauthorized') }}";
    var somethingWentWrongMessage = "{{ trans('services.messages.something_wrong_try_again') }}";
</script>
<script src="{{mix('js/services/index.js')}}">
</script>
@endsection
