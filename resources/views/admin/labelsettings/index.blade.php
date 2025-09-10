@extends('layouts.app')

@section('after-styles')
<link href="{{asset('assets/plugins/datatables/dataTables.bootstrap5.min.css?var='.rand())}}" rel="stylesheet"/>
@endsection
@section('content-header')
<!-- Content Header (Page header) -->
@include('admin.labelsettings.breadcrumb', [
    'appPageTitle'  => trans('labelsettings.title.index_title'),
    'breadcrumb'    => 'labelsettings.index',
    'changelabel'   => true,
    'defautlBtn'    => false,
])
<!-- /.content-header -->
@endsection
@section('content')
<section class="content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-body">
                <div class="card-table-outer" id="labelstrings-wrap">
                    <div class="table-responsive">
                        <table class="table custom-table" id="labelstrings">
                            <thead>
                                <tr>
                                    <th class="d-none">
                                        {{ trans('labelsettings.table.updated_at') }}
                                    </th>
                                    <th>
                                        {{trans('labelsettings.table.module')}}
                                    </th>
                                    <th>
                                        {{trans('labelsettings.table.field_name')}}
                                    </th>
                                    <th>
                                        {{trans('labelsettings.table.label_name')}}
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
@endsection
@section('after-scripts')
<script src="{{asset('assets/plugins/datatables/jquery.dataTables.min.js?var='.rand())}}" type="text/javascript">
</script>
<script src="{{asset('assets/plugins/datatables/dataTables.bootstrap5.min.js?var='.rand())}}" type="text/javascript">
</script>
<script type="text/javascript">
    var url = {
        datatable: `{{ route('admin.labelsettings.getlabelstrings') }}`,
    },
    pagination = {
        value: {{ $pagination }},
        previous: `{!! trans('buttons.pagination.previous') !!}`,
        next: `{!! trans('buttons.pagination.next') !!}`,
    };
</script>
<script src="{{ asset('js/labelsettings/index.js') }}" type="text/javascript">
</script>
@endsection
