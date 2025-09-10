@extends('layouts.app')

@section('after-styles')
<link href="{{ asset('assets/plugins/datatables/dataTables.bootstrap5.min.css?var='.rand()) }}" rel="stylesheet"/>
@endsection

@section('content-header')
<!-- Content Header (Page header) -->
@include('admin.meditationtrack.breadcrumb', [
    'mainTitle' => trans('meditationtrack.title.index'),
    'breadcrumb' => Breadcrumbs::render('meditationtracks.index'),
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
                {{ Form::open(['route' => 'admin.meditationtracks.index', 'class' => 'form-horizontal', 'method' => 'get', 'role' => 'form', 'id' => 'trackSearch']) }}
                <div class="search-outer d-md-flex justify-content-between">
                    <div>
                        <div class="form-group">
                            {{ Form::text('trackName', request()->get('trackName'), ['class' => 'form-control', 'placeholder' => trans('meditationtrack.filter.name'), 'id' => 'trackName', 'autocomplete' => 'off']) }}
                        </div>
                        <div class="form-group">
                            {{ Form::select('coach', $healthcoach, request()->get('coach'), ['class' => 'form-control select2','id' => 'coach', 'placeholder' => trans('meditationtrack.filter.coach'), 'data-placeholder' => trans('meditationtrack.filter.coach')] ) }}
                        </div>
                        <div class="form-group">
                            {{ Form::select('subcategory', $meditationsubcategory, request()->get('subcategory'), ['class' => 'form-control select2','id' => 'subcategory', 'placeholder' => trans('meditationtrack.filter.category'), 'data-placeholder' => trans('meditationtrack.filter.category')] ) }}
                        </div>
                        <div class="form-group">
                            {{ Form::select('type', $meditationTrackType, (request()->get('type') ?? 1), ['class' => 'form-control select2', 'id' => 'type', 'placeholder' => trans('meditationtrack.filter.track_type'), 'data-placeholder' => trans('meditationtrack.filter.track_type'), 'data-allow-clear' => 'true'] ) }}
                        </div>
                        @if($roleGroup == 'zevo')
                        <div class="form-group">
                            {{ Form::select('tag', $tags, (request()->get('tag') ?? null), ['class' => 'form-control select2', 'id' => 'tag', 'placeholder' => trans('meditationtrack.filter.tag'), 'data-placeholder' => trans('meditationtrack.filter.tag'), 'data-allow-clear' => 'true'] ) }}
                        </div>
                        @endif
                    </div>
                    <div class="search-actions align-self-start">
                        <button class="me-md-4 filter-apply-btn" type="submit">
                            {{ trans('buttons.general.apply') }}
                        </button>
                        <a class="filter-cancel-icon" href="{{ route('admin.meditationtracks.index') }}">
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
                        <table class="table custom-table" id="trackManagment">
                            <thead>
                                <tr>
                                    <th class="d-none">
                                        {{ trans('meditationtrack.table.updated_at') }}
                                    </th>
                                    <th class="no-sort th-btn-2">
                                        {{ trans('meditationtrack.table.cover') }}
                                    </th>
                                    <th>
                                        {{ trans('meditationtrack.table.title') }}
                                    </th>
                                    <th>
                                        {{ trans('meditationtrack.table.visible_to_company') }}
                                    </th>
                                    <th>
                                        {{ trans('meditationtrack.table.duration') }}
                                    </th>
                                    <th>
                                        {{ trans('meditationtrack.table.subcategory_name') }}
                                    </th>
                                    <th>
                                        {{ trans('meditationtrack.table.tag') }}
                                    </th>
                                    <th>
                                        {{ trans('meditationtrack.table.health_coach') }}
                                    </th>
                                    <th>
                                        {{ trans('meditationtrack.table.created_at') }}
                                    </th>
                                    <th>
                                        {{ trans('meditationtrack.table.total_likes') }}
                                    </th>
                                    <th>
                                        {{ trans('meditationtrack.table.view_count') }}
                                    </th>
                                    <th class="th-btn-2 no-sort">
                                        {{ trans('meditationtrack.table.actions') }}
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
@include('admin.meditationtrack.index-modals')
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
            datatable: `{{ route('admin.meditationtracks.getMeditationTrack') }}`,
            delete: `{{ route('admin.meditationtracks.delete', ':id') }}`
        },
        pagination = {
            value: {{ $pagination }},
            previous: `{!! trans('buttons.pagination.previous') !!}`,
            next: `{!! trans('buttons.pagination.next') !!}`,
        },
        roleGroup = `{{ $roleGroup }}`,
        messages = {!! json_encode(trans('meditationtrack.messages')) !!};
</script>
<script src="{{ mix('js/meditationtrack/index.js') }}" type="text/javascript">
</script>
@endsection
