@extends('layouts.app')

@section('after-styles')
<!-- DataTables -->
<link href="{{asset('assets/plugins/datatables/dataTables.bootstrap5.min.css?var='.rand())}}" rel="stylesheet"/>
@endsection
@section('content-header')
<!-- Content Header (Page header) -->
@include('admin.group.breadcrumb',[
    'appPageTitle' => trans('group.title.reportabuse'),
    'breadcrumb'   => 'group.reportabuse',
    'create'       => false,
    'back'         => true,
    'edit'         => false,
    'string'       => '',
])
<!-- /.content-header -->
@endsection
@section('content')
<section class="content">
    <div class="container-fluid">
        <div class="card search-card">
            <div class="card-body pb-0">
                <h4 class="d-md-none">
                    Filter
                </h4>
                {{ Form::open(['route' => ['admin.groups.reportAbuse',$groupData->id], 'class' => 'form-horizontal', 'method'=>'get','role' => 'form', 'id'=>'userSearch']) }}
                <div class="search-outer d-md-flex justify-content-between">
                    <div>
                        <div class="form-group">
                            {{ Form::text('userName', request()->get('userName'), ['class' => 'form-control', 'placeholder' => 'Search By Name / Email', 'id' => 'userName', 'autocomplete' => 'off']) }}
                        </div>
                    </div>
                    <div class="search-actions align-self-start">
                        <button class="me-md-4 filter-apply-btn" type="submit">
                            {{trans('buttons.general.apply')}}
                        </button>
                        <a class="filter-cancel-icon" href="{{ route('admin.groups.reportAbuse',$groupData->id) }}">
                            <i class="far fa-times">
                            </i>
                            <span class="d-md-none ms-2 ms-md-0">
                                {{trans('buttons.general.cancel')}}
                            </span>
                        </a>
                    </div>
                </div>
                {{ Form::close() }}
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <div class="card-table-outer" id="reportAbuse-wrap">
                    <div class="table-responsive">
                        <table class="table custom-table" id="reportAbuse">
                            <thead>
                                <tr>
                                    <th class="text-center" style="display: none">
                                        Updated At
                                    </th>
                                    <th>
                                        {{trans('labels.group.userName')}}
                                    </th>
                                    <th>
                                        {{trans('labels.group.email')}}
                                    </th>
                                    <th>
                                        {{trans('labels.group.reason')}}
                                    </th>
                                    <th>
                                        {{trans('labels.group.feedback')}}
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
<script src="{{asset('assets/plugins/datatables/jquery.dataTables.min.js?var='.rand())}}">
</script>
<script src="{{asset('assets/plugins/datatables/dataTables.bootstrap5.min.js?var='.rand())}}">
</script>
<script type="text/javascript">
    var url = {
        datatable: `{{ route('admin.groups.getReportAbuseList',$groupData->id) }}`,
    },
    pagination = {
        value: {{ $pagination }},
        previous: `{!! trans('buttons.pagination.previous') !!}`,
        next: `{!! trans('buttons.pagination.next') !!}`,
    };
</script>
<script src="{{ asset('js/group/reportabuse.js') }}" type="text/javascript">
</script>
@endsection
