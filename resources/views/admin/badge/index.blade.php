@extends('layouts.app')

@section('after-styles')
<link href="{{asset('assets/plugins/datatables/dataTables.bootstrap5.min.css?var='.rand())}}" rel="stylesheet"/>
@endsection
@section('content-header')
<!-- Content Header (Page header) -->
@include('admin.badge.breadcrumb', [
    'appPageTitle' => trans('badge.title.index_title'),
    'breadcrumb' => 'badge.index',
    'create' => true,
    'back' => false,
])
<!-- /.content-header -->
@endsection
@section('content')
<section class="content">
    <div class="container-fluid">
        @if($isSA)
        <div class="card search-card">
            <div class="card-body pb-0">
                <h4 class="d-md-none">
                    {{trans('badge.title.filter')}}
                </h4>
                {{ Form::open(['route' => 'admin.badges.index', 'class' => 'form-horizontal', 'method'=>'get','role' => 'form', 'id'=>'badgeSearch']) }}
                <div class="search-outer d-md-flex justify-content-between">
                    <div>
                        <div class="form-group">
                            {{ Form::text('badgeName', request()->get('badgeName'), ['class' => 'form-control', 'placeholder' => trans('badge.filter.search_by_name'), 'id' => 'badgeName', 'autocomplete' => 'off']) }}
                        </div>
                        <div class="form-group">
                            {{ Form::select('badgeType', $badgeTypes, request()->get('badgeType'), ['class' => 'form-control select2','id'=>'badgeType', 'placeholder' => trans('badge.filter.select_badge_type'), 'data-placeholder'=> trans('badge.filter.select_badge_type'), 'data-allowclear' => 'true'] ) }}
                        </div>
                    </div>
                    <div class="search-actions align-self-start">
                        <button class="me-md-4 filter-apply-btn" type="submit">
                            {{trans('buttons.general.apply')}}
                        </button>
                        <a class="filter-cancel-icon" href="{{ route('admin.badges.index') }}">
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
        @endif
        <div class="card">
            <div class="card-body">
                <div class="card-table-outer" id="badgeManagment-wrap">
                    <div class="table-responsive">
                        <table class="table custom-table" id="badgeManagment">
                            <thead>
                                <tr>
                                    <th class="text-center" style="display: none">
                                        {{trans('badge.form.labels.updated_at')}}
                                    </th>
                                    <th class="text-center no-sort th-btn-2">
                                        {{trans('badge.form.labels.logo')}}
                                    </th>
                                    <th>
                                        {{trans('badge.form.labels.title')}}
                                    </th>
                                    <th>
                                        {{trans('badge.form.labels.badge_type')}}
                                    </th>
                                    @if($isSA)
                                    <th>
                                        {{trans('badge.form.labels.challenge_activity')}}
                                    </th>
                                    @else
                                    <th>
                                        {{trans('badge.form.labels.challenge')}}
                                    </th>
                                    @endif
                                    <th style="{{ empty($isSA) ? 'display: none' : '' }}">
                                        {{trans('badge.form.labels.badge_target')}}
                                    </th>
                                    <th>
                                        {{trans('badge.form.labels.awarded_badge')}}
                                    </th>
                                    <th class="th-btn-2 no-sort" style="{{ empty($isSA) ? 'display: none' : '' }}">
                                        {{trans('badge.form.labels.action')}}
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
<!-- Delete model popup -->
@include('admin.badge.delete-model')
@endsection
@section('after-scripts')
<script src="{{asset('assets/plugins/datatables/jquery.dataTables.min.js?var='.rand())}}">
</script>
<script src="{{asset('assets/plugins/datatables/dataTables.bootstrap5.min.js?var='.rand())}}">
</script>
<script type="text/javascript">
    var url = {
        datatable: `{{ route('admin.badges.getBadges') }}`,
        delete: `{{ route('admin.badges.delete', ':id') }}`,
    },
    condition = {
        isSA: '{{ !empty($isSA) ? true : false }}',
    },
    pagination = {
        value: {{ $pagination }},
        previous: `{!! trans('buttons.pagination.previous') !!}`,
        next: `{!! trans('buttons.pagination.next') !!}`,
    },
    message = {
        badge_deleted: `{{ trans('badge.modal.badge_deleted') }}`,
        badge_in_use: `{{ trans('badge.modal.badge_in_use') }}`,
        unable_to_badge: `{{ trans('badge.modal.unable_to_badge') }}`,
    };
</script>
<script src="{{ asset('js/badge/index.js') }}" type="text/javascript">
</script>
@endsection
