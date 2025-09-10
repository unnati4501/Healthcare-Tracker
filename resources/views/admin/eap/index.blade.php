@extends('layouts.app')

@section('after-styles')
<link href="{{asset('assets/plugins/datatables/dataTables.bootstrap5.min.css?var='.rand())}}" rel="stylesheet"/>
@if($reordering == false)
<link href="{{ asset('assets/plugins/datatables/extensions/ColReorder/css/rowReorder.dataTables.min.css?var='.rand()) }}" rel="stylesheet"/>
@endif
@endsection
@section('content-header')
<!-- Content Header (Page header) -->
@include('admin.eap.breadcrumb',[
    'appPageTitle' => trans('eap.title.index_title'),
    'breadcrumb' => 'eap.index',
    'create'     => true,
    'introduction' => true,
])
<!-- /.content-header -->
@endsection
@section('content')
<section class="content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-body">
                <div class="card-table-outer" id="eapManagment-wrap">
                    <div class="table-responsive">
                        <table class="table custom-table" id="eapManagment">
                            <thead>
                                <tr>
                                    <th class="text-center hidden">
                                        {{ trans('eap.table.updated_at') }}
                                    </th>
                                    <th class="text-center no-sort th-btn-2">
                                    </th>
                                    <th class="allow-reorder">
                                        {{ trans('eap.table.title') }}
                                    </th>
                                    <th style="{{ (!$visabletocompanyVisibility) ? 'display: none' : '' }}">
                                        {{ trans('eap.table.visible_to_company') }}
                                    </th>
                                    <th class="text-center allow-reorder">
                                        {{ trans('eap.table.view_count') }}
                                    </th>
                                    <th class="allow-reorder">
                                        {{ trans('eap.table.telephone') }}
                                    </th>
                                    <th class="allow-reorder">
                                        {{ trans('eap.table.email') }}
                                    </th>
                                    <th class="allow-reorder">
                                        {{ trans('eap.table.website') }}
                                    </th>
                                    <th>
                                        {{ trans('eap.table.created_at') }}
                                    </th>
                                    <th>
                                        {{ trans('eap.table.sticky') }}
                                    </th>
                                    <th class="text-center th-btn-3 no-sort">
                                        {{ trans('eap.table.action') }}
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
<tbody>
</tbody>
<!-- Delete Model Popup -->
@include('admin.eap.delete-modal')
<!-- Company visibility Model Popup -->
@include('admin.eap.companyvisible-modal')
<!-- Support Sticky Model Popup -->
@include('admin.eap.sticky-model')
<!-- Support UnSticky Model Popup -->
@include('admin.eap.unsticky-model')
@endsection
@section('after-scripts')
<script src="{{asset('assets/plugins/moment/moment.min.js?var='.rand()) }}" type="text/javascript">
</script>
<script src="{{asset('assets/plugins/moment/moment-timezone-with-data-10-year-range.js?var='.rand()) }}" type="text/javascript">
</script>
<script src="{{asset('assets/plugins/datatables/jquery.dataTables.min.js?var='.rand())}}">
</script>
<script src="{{asset('assets/plugins/datatables/dataTables.bootstrap5.min.js?var='.rand())}}">
</script>
@if($reordering == false)
<script src="{{asset('assets/plugins/datatables/extensions/ColReorder/js/dataTables.rowReorder.min.js?var='.rand())}}">
</script>
@endif
<script type="text/javascript">
var timezone   = `{{ $timezone }}`,
    date_format = `{{ $date_format }}`;
    url = {
        datatable: `{{ route('admin.support.getEaps') }}`,
        reorderingEap: `{{ route('admin.support.reorderingEap') }}`,
        delete: `{{ route('admin.support.delete', ':id') }}`,
        stickunstick: `{{ route('admin.support.stickUnstick', ':id') }}`,
    },
    data = {
        reordering: `{!! $reordering !!}`,
        roworder: `{!! (($reordering == true) ? false : true) !!}`,
        visabletocompanyVisibility: `{{ $visabletocompanyVisibility }}`,
    },
    pagination = {
        value: {{ $pagination }},
        previous: `{!! trans('buttons.pagination.previous') !!}`,
        next: `{!! trans('buttons.pagination.next') !!}`,
    },
    message = {
        something_went_wrong: `{{ trans('eap.message.something_went_wrong') }}`,
        delete_error: `{{ trans('eap.message.delete_error') }}`,
    };
</script>
<script src="{{ asset('js/eap/index.js') }}" type="text/javascript">
</script>
@endsection
