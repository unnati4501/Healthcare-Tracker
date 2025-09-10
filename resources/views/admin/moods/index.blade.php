@extends('layouts.app')

@section('after-styles')
<!-- DataTables -->
<link href="{{ asset('assets/plugins/datatables/dataTables.bootstrap5.min.css?var='.rand()) }}" rel="stylesheet"/>
@endsection

@section('content-header')
<!-- Content Header (Page header) -->
@include('admin.moods.breadcrumb', [
  'mainTitle' => trans('moods.title.manage'),
  'breadcrumb' => 'moods.index',
  'create' => $moodCount < 16
])
<!-- /.content-header -->
@endsection

@section('content')
<section class="content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-body">
                <div class="card-table-outer" id="moodManagement-wrap">
                    <div class="table-responsive">
                        <table class="table custom-table" id="moodManagement">
                            <thead>
                                <tr>
                                    <th>
                                        {{ trans('moods.table.updated_at') }}
                                    </th>
                                    <th class="no-sort th-btn-4">
                                        {{ trans('moods.table.logo') }}
                                    </th>
                                    <th>
                                        {{ trans('moods.table.moods') }}
                                    </th>
                                    <th class="th-btn-4 no-sort">
                                        {{ trans('moods.table.action') }}
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
@include('admin.moods.delete_modal')
@endsection
<!-- include datatable css -->
@section('after-scripts')
<!-- DataTables -->
<script src="{{ asset('assets/plugins/datatables/jquery.dataTables.min.js?var='.rand()) }}">
</script>
<script src="{{ asset('assets/plugins/datatables/dataTables.bootstrap5.min.js?var='.rand()) }}">
</script>
<script type="text/javascript">
    var url = {
        datatable: `{{ route('admin.moods.getMoods') }}`,
        delete: `{{ route('admin.moods.delete','/') }}`,
    },
    pagination = {
        value: {{ $pagination }},
        previous: `{!! trans('buttons.pagination.previous') !!}`,
        next: `{!! trans('buttons.pagination.next') !!}`,
    },
    message = {
        deleted: `{{ trans('moods.messages.deleted') }}`,
        unauthorized: `{{ trans('moods.messages.unauthorized_access') }}`,
        somethingWentWrong: `{{ trans('moods.messages.something_wrong_try_again') }}`,
    };
</script>
<script src="{{ mix('js/moods/index.js') }}">
</script>
@endsection
