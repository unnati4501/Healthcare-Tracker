@extends('layouts.app')

@section('after-styles')
<link href="{{ asset('assets/plugins/datatables/dataTables.bootstrap5.min.css?var='.rand()) }}" rel="stylesheet"/>
@endsection

@section('content-header')
<!-- Content Header (Page header) -->
@include('admin.podcast.breadcrumb', [
    'mainTitle' => trans('podcast.title.index'),
    'breadcrumb' => Breadcrumbs::render('podcasts.index'),
    'create' => true
])
<!-- /.content-header -->
@endsection

@section('content')
<section class="content">
    <div class="container-fluid">
        <!-- search-block -->
        <div class="card search-card">
            <div class="card-body pb-0">
                <h4 class="d-md-none">
                    {{ trans('buttons.general.filter') }}
                </h4>
                {{ Form::open(['route' => 'admin.podcasts.index', 'class' => 'form-horizontal', 'method' => 'get', 'role' => 'form', 'id' => 'podcastSearch']) }}
                <div class="search-outer d-md-flex justify-content-between">
                    <div>
                        <div class="form-group">
                            {{ Form::text('podcastName', request()->get('podcastName'), ['class' => 'form-control', 'placeholder' => trans('podcast.filter.name'), 'id' => 'podcastName', 'autocomplete' => 'off']) }}
                        </div>
                        <div class="form-group">
                            {{ Form::select('coach', $healthcoach, request()->get('coach'), ['class' => 'form-control select2','id' => 'coach', 'placeholder' => trans('podcast.filter.coach'), 'data-placeholder' => trans('podcast.filter.coach')] ) }}
                        </div>
                        <div class="form-group">
                            {{ Form::select('subcategory', $podcastSubcategory, request()->get('subcategory'), ['class' => 'form-control select2','id' => 'subcategory', 'placeholder' => trans('podcast.filter.category'), 'data-placeholder' => trans('podcast.filter.category')] ) }}
                        </div>
                        @if($roleGroup == 'zevo')
                        <div class="form-group">
                            {{ Form::select('tag', $tags, (request()->get('tag') ?? null), ['class' => 'form-control select2', 'id' => 'tag', 'placeholder' => trans('podcast.filter.tag'), 'data-placeholder' => trans('podcast.filter.tag'), 'data-allow-clear' => 'true'] ) }}
                        </div>
                        @endif
                    </div>
                    <div class="search-actions align-self-start">
                        <button class="me-md-4 filter-apply-btn" type="submit">
                            {{ trans('buttons.general.apply') }}
                        </button>
                        <a class="filter-cancel-icon" href="{{ route('admin.podcasts.index') }}">
                            <i class="far fa-times">
                            </i>
                            <span class="d-md-none ms-2 ms-md-0">
                                {{ trans('buttons.general.reset') }}
                            </span>
                        </a>
                    </div>
                </div>
                {{ Form::close() }}
            </div>
        </div>
        <a class="btn btn-primary filter-btn" href="javascript:void(0);">
            <i class="far fa-filter me-2 align-middle">
            </i>
            <span class="align-middle">
                {{ trans('buttons.general.filter') }}
            </span>
        </a>
        <!-- /.search-block -->
        <!-- grid -->
        <div class="card">
            <div class="card-body">
                <div class="card-table-outer">
                    <div class="table-responsive">
                        <table class="table custom-table" id="podcastManagment">
                            <thead>
                                <tr>
                                    <th class="d-none">
                                        {{ trans('podcast.table.updated_at') }}
                                    </th>
                                    <th class="no-sort th-btn-2">
                                        {{ trans('podcast.table.logo') }}
                                    </th>
                                    <th>
                                        {{ trans('podcast.table.title') }}
                                    </th>
                                    <th>
                                        {{ trans('podcast.table.visible_to_company') }}
                                    </th>
                                    <th>
                                        {{ trans('podcast.table.duration') }}
                                    </th>
                                    <th>
                                        {{ trans('podcast.table.subcategory_name') }}
                                    </th>
                                    <th>
                                        {{ trans('podcast.table.tag') }}
                                    </th>
                                    <th>
                                        {{ trans('podcast.table.health_coach') }}
                                    </th>
                                    <th>
                                        {{ trans('podcast.table.created_at') }}
                                    </th>
                                    <th>
                                        {{ trans('podcast.table.total_likes') }}
                                    </th>
                                    <th>
                                        {{ trans('podcast.table.view_count') }}
                                    </th>
                                    <th class="th-btn-2 no-sort">
                                        {{ trans('podcast.table.actions') }}
                                    </th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <!-- /.gridrid -->
    </div>
</section>
<!-- .modals -->
@include('admin.podcast.index-modals')
<!-- /.modals -->
@endsection
@section('after-scripts')
<script src="{{asset('assets/plugins/moment/moment.min.js?var='.rand()) }}" type="text/javascript">
</script>
<script src="{{asset('assets/plugins/moment/moment-timezone-with-data-10-year-range.js?var='.rand()) }}" type="text/javascript">
</script>
<script src="{{asset('assets/plugins/datatables/jquery.dataTables.min.js?var='.rand())}}" type="text/javascript">
</script>
<script src="{{asset('assets/plugins/datatables/dataTables.bootstrap5.min.js?var='.rand())}}" type="text/javascript">
</script>
<script type="text/javascript">
    var timezone = `{{ $timezone }}`,
        date_format = `{{ $date_format }}`,
        url = {
            datatable: `{{ route('admin.podcasts.getPodcasts') }}`,
            delete: `{{ route('admin.podcasts.delete', ':id') }}`
        },
        pagination = {
            value: {{ $pagination }},
            previous: `{!! trans('buttons.pagination.previous') !!}`,
            next: `{!! trans('buttons.pagination.next') !!}`,
        },
        roleGroup = `{{ $roleGroup }}`,
        messages = {!! json_encode(trans('podcast.messages')) !!};
</script>
<script src="{{ mix('js/podcast/index.js') }}" type="text/javascript">
</script>
@endsection
