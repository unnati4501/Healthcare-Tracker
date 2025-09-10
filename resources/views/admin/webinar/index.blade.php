@extends('layouts.app')

@section('after-styles')
<link href="{{asset('assets/plugins/datatables/dataTables.bootstrap5.min.css?var='.rand())}}" rel="stylesheet"/>
<style type="text/css">
    .model-close-custom { position: absolute; top: -10px; right: -13px; padding: 2px 9px; background: #00a7d2; z-index: 2; border-radius: 50%; height: 30px; width: 30px; color: white; font-size: 18px; cursor: pointer; }
</style>
@endsection
@section('content-header')
<!-- Content Header (Page header) -->
@include('admin.webinar.breadcrumb',[
    'mainTitle'  => trans('webinar.title.index_title'),
    'breadcrumb' => 'webinar.index',
    'create'     => true,
])
<!-- /.content-header -->
@endsection
@section('content')

<section class="content">
    <div class="container-fluid">
        <div class="card search-card">
            <div class="card-body pb-0">
                <h4 class="d-md-none">
                    {{trans('webinar.title.search')}}
                </h4>
                {{ Form::open(['route' => 'admin.webinar.index', 'class' => 'form-horizontal', 'method'=>'get','role' => 'form', 'id'=>'webinarSearch']) }}
                <div class="search-outer d-md-flex justify-content-between">
                    <div>
                        <div class="form-group">
                            {{ Form::text('webinarname', request()->get('webinarname'), ['class' => 'form-control', 'placeholder' => trans('webinar.filter.search_by_webinar_name'), 'id' => 'webinarname', 'autocomplete' => 'off']) }}
                        </div>
                        <div class="form-group">
                            {{ Form::select('author', $author, request()->get('author'), ['class' => 'form-control select2', 'id'=>'author', 'style'=>'width: 100%;', 'autocomplete' => 'off', 'placeholder' => trans('webinar.filter.select_author'), 'data-placeholder' => trans('webinar.filter.select_author'), 'data-allow-clear' => 'true']) }}
                        </div>
                        <div class="form-group">
                            {{ Form::select('sub_category', $webinarSubCategories, request()->get('sub_category'), ['class' => 'form-control select2', 'id'=>'sub_category', 'style'=>'width: 100%;', 'autocomplete' => 'off', 'placeholder' => trans('webinar.filter.select_sub_category'), 'data-placeholder' => trans('webinar.filter.select_sub_category'), 'data-allow-clear' => 'true']) }}
                        </div>
                        <div class="form-group">
                            {{ Form::select('type', $webinarTrackType, (request()->get('type')), ['class' => 'form-control select2','id'=>'type',"style"=>"width: 100%;", 'placeholder' => trans('webinar.filter.select_webinar_type'), 'data-placeholder' => trans('webinar.filter.select_webinar_type'), 'autocomplete' => 'off', 'data-allow-clear' => 'true'] ) }}
                        </div>
                        @if($roleGroup == 'zevo')
                        <div class="form-group">
                            {{ Form::select('tag', $tags, (request()->get('tag') ?? null), ['class' => 'form-control select2', 'id' => 'tag', 'placeholder' => trans('webinar.filter.tag'), 'data-placeholder' => trans('webinar.filter.tag'), 'data-allow-clear' => 'true'] ) }}
                        </div>
                        @endif
                    </div>
                    <div class="search-actions align-self-start">
                        <button class="me-md-4 filter-apply-btn" type="submit">
                            {{trans('buttons.general.apply')}}
                        </button>
                        <a class="filter-cancel-icon" href="{{ route('admin.webinar.index') }}">
                            <i class="far fa-times">
                            </i>
                            <span class="d-md-none ms-2 ms-md-0">
                                {{trans('buttons.general.reset')}}
                            </span>
                        </a>
                    </div>
                </div>
                {{ Form::close() }}
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <div class="card-table-outer">
                    <div class="table-responsive">
                        <table class="table custom-table" id="webinarManagment">
                            <thead>
                                <tr>
                                    <th class="text-center" style="display: none">
                                        {{trans('webinar.table.updated_at')}}
                                    </th>
                                    <th class="sorting_1 no-sort th-btn-4">
                                        {{trans('webinar.table.webinar_cover')}}
                                    </th>
                                    <th>
                                        {{trans('webinar.table.webinar_name')}}
                                    </th>
                                    <th>
                                        {{trans('webinar.table.duration_second')}}
                                    </th>
                                    <th>
                                        {{trans('webinar.table.category')}}
                                    </th>
                                    <th>
                                        {{ trans('webinar.table.visible_to_company') }}
                                    </th>
                                    <th>
                                        {{ trans('webinar.table.tag') }}
                                    </th>
                                    <th>
                                        {{trans('webinar.table.author')}}
                                    </th>
                                    <th>
                                        {{trans('webinar.table.created_at')}}
                                    </th>
                                    <th>
                                        {{trans('webinar.table.total_likes')}}
                                    </th>
                                    <th>
                                        {{trans('webinar.table.view_count')}}
                                    </th>
                                    <th>
                                        {{trans('webinar.table.action')}}
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
<!-- Record Delete Model Popup -->
@include('admin.webinar.delete-model')
<!-- Watch Video Model Popup -->
@include('admin.webinar.watchvideo-model')
<!-- Company Visibility Model Popup -->
@include('admin.webinar.companyvisibility-model')
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
        datatable: `{{ route('admin.webinar.getwebinar') }}`,
        delete: `{{ route('admin.webinar.delete', ':id') }}`,
    },
    pagination = {
        value: {{ $pagination }},
        previous: `{!! trans('buttons.pagination.previous') !!}`,
        next: `{!! trans('buttons.pagination.next') !!}`,
    },
    message = {
        webinarDeleted: `{{ trans('webinar.modal.webinar_deleted') }}`,
        failedDeleteWebinar: `{{ trans('webinar.modal.failed_delete_webinar') }}`,
    },
    roleGroup = `{{ $roleGroup }}`;
</script>
<script src="{{ asset('js/webinar/index.js') }}" type="text/javascript">
</script>
@endsection
