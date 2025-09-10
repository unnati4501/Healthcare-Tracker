@extends('layouts.app')

@section('after-styles')
<!-- DataTables -->
<link href="{{asset('assets/plugins/datatables/dataTables.bootstrap5.min.css?var='.rand())}}" rel="stylesheet"/>
<link href="{{asset('assets/plugins/datepicker/datepicker3.css?var='.rand())}}" rel="stylesheet"/>
@endsection

@section('content-header')
<!-- Content Header (Page header) -->
@include('admin.challenge.breadcrumb', [
  'mainTitle' => $pageTitle,
  'breadcrumb' => $route . '.index',
  'create' => true
])
<!-- /.content-header -->
@endsection

@section('content')
<section class="content">
    <div class="container-fluid">
        <div class="card search-card">
            <div class="card-body pb-0">
                {{ Form::open(['route' => 'admin.'.$route.'.index', 'class' => 'form-horizontal', 'method'=>'get','role' => 'form']) }}
                <div class="search-outer d-md-flex justify-content-between">
                    <div>
                        <div class="form-group">
                            {{ Form::text('challengeName', request()->get('challengeName'), ['class' => 'form-control', 'placeholder' => trans('challenges.filter.name'), 'id' => 'challengeName', 'autocomplete' => 'off']) }}
                        </div>
                        <div class="form-group">
                            {{ Form::select('challengeStatus', $challengeStatusData, request()->get('challengeStatus'), ['class' => 'form-control select2','id'=>'challengeStatus',"style"=>"width: 100%;", 'placeholder' => '', 'data-placeholder' => trans('challenges.filter.status'), 'autocomplete' => 'off'] ) }}
                        </div>
                        @if(isset($route) && $route == 'challenges')
                        <div class="form-group">
                            {{ Form::select('recursive', $recursive, request()->get('recursive'), ['class' => 'form-control select2','id'=>'recursive',"style"=>"width: 100%;", 'placeholder' => '', 'data-placeholder' => trans('challenges.filter.recursive'), 'autocomplete' => 'off'] ) }}
                        </div>
                        @else
                        <div class="form-group">
                            {{ Form::select('challengeCategory', $challengeCategoryData, request()->get('challengeCategory'), ['class' => 'form-control select2','id'=>'challengeCategory',"style"=>"width: 100%;", 'placeholder' => '', 'data-placeholder' => trans('challenges.filter.category'), 'autocomplete' => 'off'] ) }}
                        </div>
                        @endif
                    </div>
                    <div class="search-actions align-self-start">
                        <button class="me-md-4 filter-apply-btn" type="submit">
                            {{ trans('buttons.general.apply') }}
                        </button>
                        <a class="filter-cancel-icon" href="{{ route('admin.'.$route.'.index') }}">
                            <i class="far fa-times">
                            </i>
                        </a>
                    </div>
                </div>
                {{ Form::close() }}
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <div class="card-table-outer" id="challengeManagment-wrap">
                    <div class="table-responsive">
                        <table class="table custom-table" id="challengeManagment">
                            <thead>
                                <tr>
                                    <th>
                                        {{ trans('challenges.table.updated_at') }}
                                    </th>
                                    <th class="no-sort th-btn-4">
                                        {{ trans('challenges.table.logo') }}
                                    </th>
                                    <th>
                                        {{ trans('challenges.table.name') }}
                                    </th>
                                    <th>
                                        {{ trans('challenges.table.category') }}
                                    </th>
                                    <th>
                                        {{ trans('challenges.table.target') }}
                                    </th>
                                    <th>
                                        {{ trans('challenges.table.dates') }}
                                    </th>
                                    <th>
                                        {{ trans('challenges.table.recursive') }}
                                    </th>
                                    <th>
                                        {{ trans('challenges.table.status') }}
                                    </th>
                                    <th class="no-sort th-btn-4">
                                        {{ trans('challenges.table.action') }}
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
@include('admin.challenge.delete_modal')
@include('admin.challenge.export_modal')
@include('admin.challenge.cancel_modal')
@endsection
@section('after-scripts')
<script src="{{ asset('js/external/jquery.form.min.js?var='.rand()) }}">
</script>
<script src="{{asset('assets/plugins/datatables/jquery.dataTables.min.js?var='.rand())}}">
</script>
<script src="{{asset('assets/plugins/datatables/dataTables.bootstrap5.min.js?var='.rand())}}">
</script>
<script src="{{asset('assets/plugins/moment/moment.min.js?var='.rand()) }}">
</script>
<script src="{{asset('assets/plugins/moment/moment-timezone-with-data-10-year-range.js?var='.rand()) }}">
</script>
<script src="{{asset('assets/plugins/datepicker/bootstrap-datepicker.js?var='.rand())}}">
</script>
<script type="text/javascript">
    var timezone = '{{ $timezone }}',
    date_format = '{{ $date_format }}',
    currentRoute = '{{ $route }}',
    loginemail = '{{ $loginemail }}',
    startDate = '{{ config('zevolifesettings.challenge_set_date.before') }}',
    endDate = '{{ config('zevolifesettings.challenge_set_date.after') }}',
    url = {
        datatable: `{{ route('admin.'.$route.'.getChallenges') }}`,
        delete: `{{ route('admin.'.$route.'.delete','/') }}`,
        exportHistory: `{{ route('admin.interCompanyChallenges.getexporthistory','/') }}`,
        setAccurateData: ` {{ route('admin.interCompanyChallenges.setaccuratedata','/') }}`, 
        cancelChallenge: `{{ route('admin.challenges.cancel','/') }}`,
    },
    pagination = {
        value: {{ $pagination }},
        previous: `{!! trans('buttons.pagination.previous') !!}`,
        next: `{!! trans('buttons.pagination.next') !!}`,
    },
    message = {
        inUse: `{{ trans('challenges.messages.in_use') }}`,
        deleted: `{{ trans('challenges.messages.deleted') }}`,
        unauthorized: `{{ trans('challenges.messages.unauthorized') }}`,
        somethingWentWrong: `{{ trans('challenges.messages.something_wrong_try_again') }}`,
        cancellation: `{{ trans('challenges.messages.cancel_message') }}`,
    };
</script>
<script src="{{ mix('js/challenges/index.js') }}">
</script>
@endsection
