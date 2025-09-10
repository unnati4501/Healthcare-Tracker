@extends('layouts.app')

@section('after-styles')
<!-- DataTables -->
<link href="{{asset('assets/plugins/datatables/dataTables.bootstrap5.min.css?var='.rand())}}" rel="stylesheet"/>
@endsection
@section('content-header')
<!-- Content Header (Page header) -->
@include('admin.goals.breadcrumb',[
    'appPageTitle' => $ga_title,
    'breadcrumb' => 'goals.view',
    'create'     => false,
    'back' => true,
])
<!-- /.content-header -->
@endsection
@section('content')
<section class="content">
    <div class="container-fluid">
        <div class="card search-card">
            <div class="card-body pb-0">
            <h4 class="d-md-none">{{ trans('goals.title.search') }}</h4>
            {{ Form::open(['route' => ['admin.goals.view',$goal_id], 'class' => 'form-horizontal', 'method'=>'get','role' => 'form', 'id'=>'feedSearch']) }}
            <div class="search-outer d-md-flex justify-content-between">
                <div>
                    <div class="form-group">
                        {{ Form::text('title', request()->get('title'), ['class' => 'form-control', 'placeholder' => trans('goals.filter.search_by_title'), 'id' => 'title', 'autocomplete' => 'off']) }}
                    </div>
                    <div class="form-group">
                        {{ Form::select('type', $tagType, request()->get('type'), ['class' => 'form-control select2', 'id'=>'tagtype', 'placeholder' => trans('goals.filter.select_content_type'), 'data-placeholder' => trans('goals.filter.select_content_type'), 'data-allow-clear' => 'true'] ) }}
                    </div>
                </div>
                <div class="search-actions align-self-start">
                    <button class="me-md-4 filter-apply-btn" type="submit">
                        {{trans('buttons.general.apply')}}
                    </button>
                    <a class="filter-cancel-icon" href="{!! route('admin.goals.view',$goal_id) !!}">
                        <i class="far fa-times">
                        </i>
                        <span class="d-md-none ms-2 ms-md-0">{{trans('buttons.general.reset')}}</span>
                    </a>
                </div>
            </div>
            {{ Form::close() }}
            </div>
        </div>
        <div class="card">
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
                                        {{trans('goals.table.image')}}
                                    </th>
                                    <th>
                                        {{trans('goals.table.title')}}
                                    </th>
                                    <th>
                                        {{trans('goals.table.content_type')}}
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
<!-- Unmapped Model Popup -->
@include('admin.goals.unmapped-model')
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
        datatable: `{{ route('admin.goals.getGoalTags') }}`,
        delete: `{{ route('admin.goals.deletetag','/') }}`,
    },
    data = {
        goal_id: `{{$goal_id}}`,
    },
    pagination = {
        value: {{ $pagination }},
        previous: `{!! trans('buttons.pagination.previous') !!}`,
        next: `{!! trans('buttons.pagination.next') !!}`,
    },
    message = {
        goal_tag_unmapped_successfully: `{{ trans('goals.message.goal_tag_unmapped_successfully') }}`,
        action_unauthorized: `{{ trans('goals.modal.action_unauthorized') }}`,
        something_wrong_try_again: `{{ trans('goals.message.something_wrong_try_again') }}`,
    };
</script>
<script src="{{ asset('js/goals/view-mapped.js') }}" type="text/javascript">
</script>
@endsection