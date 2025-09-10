@extends('layouts.app')

@section('after-styles')
<!-- DataTables -->
<link href="{{asset('assets/plugins/datatables/dataTables.bootstrap5.min.css?var='.rand())}}" rel="stylesheet"/>
@endsection
@section('content-header')
<!-- Content Header (Page header) -->
@include('admin.goals.breadcrumb',[
    'appPageTitle' => trans('goals.title.index_title'),
    'breadcrumb' => 'goals.index',
    'create'     => true,
    'back' => false,
])
<!-- /.content-header -->
@endsection
@section('content')
<section class="content">
    <div class="container-fluid">
        <div class="card">
            <!-- /.card-header -->
            <div class="card-body">
                <div class="card-table-outer" id="goalManagement-wrap">
                    <div class="table-responsive">
                        <table class="table custom-table" id="goalManagement">
                            <thead>
                                <tr>
                                    <th style="display: none">
                                        {{trans('goals.table.updated_at')}}
                                    </th>
                                    <th class="no-sort th-btn-2">
                                        {{trans('goals.table.tag_logo')}}
                                    </th>
                                    <th>
                                        {{trans('goals.table.tag_name')}}
                                    </th>
                                    <th>
                                        {{trans('goals.table.feed')}}
                                    </th>
                                    <th>
                                        {{trans('goals.table.masterclass')}}
                                    </th>
                                    <th>
                                        {{trans('goals.table.recipe')}}
                                    </th>
                                    <th>
                                        {{trans('goals.table.meditation')}}
                                    </th>
                                    <th>
                                        {{trans('goals.table.webinar')}}
                                    </th>
                                    <th>
                                        {{trans('goals.table.total')}}
                                    </th>
                                    <th class="th-btn-2 no-sort">
                                        {{trans('goals.table.action')}}
                                    </th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
            <!-- /.card-body -->
        </div>
    </div>
</section>
<!-- Delete Model Popup -->
@include('admin.goals.delete-model')
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
        datatable: `{{ route('admin.goals.getGoals') }}`,
        delete: `{{ route('admin.goals.delete', ':id') }}`,
    },
    pagination = {
        value: {{ $pagination }},
        previous: `{!! trans('buttons.pagination.previous') !!}`,
        next: `{!! trans('buttons.pagination.next') !!}`,
    },
    message = {
        goalsDeleted: `{{ trans('goals.modal.goals_deleted') }}`,
        action_unauthorized: `{{ trans('goals.modal.action_unauthorized') }}`,
        something_wrong_try_again: `{{ trans('goals.message.something_wrong_try_again') }}`,
    };
</script>
<script src="{{ asset('js/goals/index.js') }}" type="text/javascript">
</script>
@endsection
