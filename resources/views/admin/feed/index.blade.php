@extends('layouts.app')

@section('after-styles')
<link href="{{asset('assets/plugins/datatables/dataTables.bootstrap5.min.css?var='.rand())}}" rel="stylesheet"/>
@endsection
@section('content-header')
<!-- Content Header (Page header) -->
@include('admin.feed.breadcrumb',[
    'appPageTitle' => trans('feed.title.index_title'),
    'breadcrumb' => 'feed.index',
    'create'     => true,
    'back'       => false,
    'edit'       => false,
])
<!-- /.content-header -->
@endsection
@section('content')
<section class="content">
    <div class="container-fluid">
        <div class="card search-card">
            <div class="card-body pb-0">
            <h4 class="d-md-none">{{ trans('feed.title.filter') }}</h4>
            {{ Form::open(['route' => 'admin.feeds.index', 'class' => 'form-horizontal', 'method'=>'get','role' => 'form', 'id'=>'feedSearch']) }}
            <div class="search-outer d-md-flex justify-content-between">
                <div>
                    <div class="form-group">
                        {{ Form::text('feedName', request()->get('feedName'), ['class' => 'form-control', 'placeholder' => trans('feed.filter.search_by_name'), 'id' => 'feedName', 'autocomplete' => 'off']) }}
                    </div>
                    @if($isSA)
                    <div class="form-group">
                        {{ Form::select('feedCompany', $company, request()->get('feedCompany'), ['class' => 'form-control select2', 'id'=>'feedCompany', 'placeholder' => trans('feed.filter.created_by_company'), 'data-placeholder' => trans('feed.filter.created_by_company'), 'data-allow-clear' => 'true']) }}
                    </div>
                    @endif
                    <div class="form-group">
                        {{ Form::select('recordCategory', $subcategories, request()->get('recordCategory'), ['class' => 'form-control select2', 'id'=>'recordCategory', 'placeholder' => trans('feed.filter.select_category'), 'data-placeholder' => trans('feed.filter.select_category'), 'data-allow-clear' => 'true'] ) }}
                    </div>
                    <div class="form-group">
                        {{ Form::select('sheduled_content', $sheduled_contentData, request()->get('sheduled_content'), ['class' => 'form-control select2', 'id'=>'sheduled_content', 'placeholder' => trans('feed.filter.select_scheduled_type'), 'data-placeholder'=> trans('feed.filter.select_scheduled_type'), 'data-allow-clear' => 'true'] ) }}
                    </div>
                    <div class="form-group">
                        {{ Form::select('type', $feedType, (request()->get('type')), ['class' => 'form-control select2','id'=>'type',"style"=>"width: 100%;", 'placeholder' => trans('feed.filter.select_feed_type'), 'data-placeholder' => trans('feed.filter.select_feed_type'), 'autocomplete' => 'off', 'data-allow-clear' => 'true'] ) }}
                    </div>
                    @if($roleGroup == 'zevo')
                    <div class="form-group">
                        {{ Form::select('tag', $tags, (request()->get('tag') ?? null), ['class' => 'form-control select2', 'id' => 'tag', 'placeholder' => trans('feed.filter.tag'), 'data-placeholder' => trans('feed.filter.tag'), 'data-allow-clear' => 'true'] ) }}
                    </div>
                    @endif
                </div>
                <div class="search-actions align-self-start">
                    <button class="me-md-4 filter-apply-btn" type="submit">
                        {{trans('buttons.general.apply')}}
                    </button>
                    <a class="filter-cancel-icon" href="{{ route('admin.feeds.index') }}">
                        <i class="far fa-times"></i><span class="d-md-none ms-2 ms-md-0">{{trans('buttons.general.reset')}}</span>
                    </a>
                </div>
            </div>
            {{ Form::close() }}
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <div class="card-table-outer" id="feedManagement-wrap">
                    <div class="table-responsive">
                    <table class="table custom-table" id="feedManagment">
                        <thead>
                            <tr>
                                <th class="no-sort th-btn-2">
                                    {{ trans('feed.table.logo') }}
                                </th>
                                <th>
                                    {{ trans('feed.table.title') }}
                                </th>
                                <th class="{{ (!$visabletocompanyVisibility) ? 'hidden' : '' }}">
                                    {{ trans('feed.table.company_title') }}
                                </th>
                                <th class="{{ (!$visabletocompanyVisibility) ? 'hidden' : '' }}">
                                    {{ trans('feed.table.visible_to_company') }}
                                </th>
                                <th>
                                    {{ trans('feed.table.tag') }}
                                </th>
                                <th>
                                    {{ trans('feed.table.sub_category') }}
                                </th>
                                <th>
                                    {{ trans('feed.table.author') }}
                                </th>
                                <th>
                                    {{ trans('feed.table.start_date') }}
                                </th>
                                <th>
                                    {{ trans('feed.table.end_date') }}
                                </th>
                                <th class="{{ (!$companyColVisibility) ? 'hidden' : '' }}">
                                    {{ trans('feed.table.total_likes') }}
                                </th>
                                <th>
                                    {{ trans('feed.table.sticky') }}
                                </th>
                                <th class="{{ (!$companyColVisibility) ? 'hidden' : '' }}">
                                    {{ trans('feed.table.view_count') }}
                                </th>
                                <th class="th-btn-2">
                                    {{ trans('feed.table.action') }}
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
<!-- Delete Model Popup -->
@include('admin.feed.delete-model')
<!-- Stikcy Model Popup -->
@include('admin.feed.sticky-model')
<!-- UN-Stikcy Model Popup -->
@include('admin.feed.unsticky-model')
<!-- UN-Stikcy Model Popup -->
@include('admin.feed.visiblecompany-model')
@endsection
@section('after-scripts')
<script src="{{asset('assets/plugins/moment/moment.min.js?var='.rand()) }}">
</script>
<script src="{{asset('assets/plugins/moment/moment-timezone-with-data-10-year-range.js?var='.rand()) }}">
</script>
<script src="{{asset('assets/plugins/datatables/jquery.dataTables.min.js?var='.rand())}}">
</script>
<script src="{{asset('assets/plugins/datatables/dataTables.bootstrap5.min.js?var='.rand())}}">
</script>
<script type="text/javascript">
    var timezone = `{{ $timezone }}`,
        date_format = `{{ $date_format }}`;
    var url = {
        datatable: `{{ route('admin.feeds.getFeeds') }}`,
        delete: `{{ route('admin.feeds.delete', ':id') }}`,
        stickunstick: `{{ route('admin.feeds.stickUnstick', ':id') }}`,
    },
    condition = {
        visibletocompanyvisibility: '{{ $visabletocompanyVisibility }}',
        companyColVisibility: '{{ $companyColVisibility }}',
    },
    pagination = {
        value: {{ $pagination }},
        previous: `{!! trans('buttons.pagination.previous') !!}`,
        next: `{!! trans('buttons.pagination.next') !!}`,
    },
    message = {
        feed_deleted: `{{ trans('feed.modal.feed_deleted') }}`,
        feed_in_use: `{{ trans('feed.modal.feed_in_use') }}`,
        unable_to_feed_group: `{{ trans('feed.modal.unable_to_feed_group') }}`,
        failed_feed_action: `{{ trans('feed.modal.failed_feed_action') }}`,
    },
    roleGroup = `{{ $roleGroup }}`;
</script>
<script src="{{ asset('js/feed/index.js') }}" type="text/javascript">
</script>
@endsection
