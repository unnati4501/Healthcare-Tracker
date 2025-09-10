@extends('layouts.app')

@section('after-styles')
<link href="{{ asset('assets/plugins/datatables/dataTables.bootstrap5.min.css?var='.rand()) }}" rel="stylesheet"/>
<link href="{{ asset('assets/plugins/OwlCarousel2/owl.carousel.min.css?var='.rand()) }}" rel="stylesheet"/>
<link href="{{ asset('assets/plugins/OwlCarousel2/owl.theme.default.min.css?var='.rand()) }}" rel="stylesheet"/>
@endsection

@section('content-header')
<!-- Content Header (Page header) -->
@include('admin.marketplace.breadcrumb', [
    'mainTitle' => trans('marketplace.title.index'),
    'breadcrumb' => Breadcrumbs::render('marketplace.index'),
])
<!-- /.content-header -->
@endsection

@section('content')
<section class="content">
    <div class="container-fluid">
        <div class="nav-tabs-wrap">
            <div class="tab-content" id="marketPlaceTabContent">
                <div aria-labelledby="bookings-tab" class="tab-pane fade show active" id="bookings-tab" role="tabpanel">
                    <!-- .search-block -->
                    <div class="card search-card">
                        <div class="card-body pb-0">
                            <h4 class="d-md-none">
                                {{ trans('buttons.general.filter') }}
                            </h4>
                            {{ Form::open(['route' => 'admin.marketplace.index', 'class' => 'form-horizontal', 'method' => 'get','role' => 'form', 'id' => 'marketplaceSearch']) }}
                            <div class="search-outer d-md-flex justify-content-between">
                                <div>
                                    <div class="form-group">
                                        {{ Form::text('bookingTabEventName', request()->get('bookingTabEventName'), ['class' => 'form-control', 'placeholder' => trans('marketplace.filter.name'), 'id' => 'bookingTabEventName', 'autocomplete' => 'off']) }}
                                    </div>
                                    <div class="form-group">
                                        @if($companyDisable)
                                        {{ Form::text('', $company->name, ['class' => 'form-control', 'placeholder' => trans('marketplace.filter.company'), 'disabled' => true]) }}
                                        {{ Form::hidden('bookingTabEventCompany', $company->id, ['id' => 'bookingTabEventCompany']) }}
                                        @else
                                        {{ Form::select('bookingTabEventCompany', $comapanies, request()->get('bookingTabEventCompany'), ['class' => 'form-control select2', 'id' => 'bookingTabEventCompany', 'placeholder' => trans('marketplace.filter.company'), 'data-placeholder' => trans('marketplace.filter.company'), 'data-allow-clear' => 'true']) }}
                                        @endif
                                    </div>
                                    <div class="form-group">
                                        {{ Form::select('bookingTabEventPresenter', $presenters, request()->get('bookingTabEventPresenter'), ['class' => 'form-control select2', 'id' => 'bookingTabEventPresenter', 'placeholder' => trans('marketplace.filter.presenter'), 'data-placeholder' => trans('marketplace.filter.presenter'), 'data-allow-clear' => 'true']) }}
                                    </div>
                                </div>
                                <div class="search-actions align-self-start">
                                    <button class="me-md-4 filter-apply-btn" type="submit">
                                        {{ trans('buttons.general.apply') }}
                                    </button>
                                    <a class="filter-cancel-icon" href="javascript:void(0);" id="resetSearch">
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
                    <!-- .category slider -->
                    <div class="tabs-wraper tabs-wrapper-small">
                        <div class="owl-carousel owl-theme arrow-theme" id="bookings-category-carousel">
                            @foreach($tabCategory as $key => $category)
                            <div class="item text-center {{ (empty($key) ? 'selected' : '') }}" data-id="{{ $category->id }}">
                                <a href="javascript:void(0);">
                                    <span class="d-block category-name">
                                        {{ $category->name }}
                                    </span>
                                </a>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    <!-- /.category slider -->
                    <!-- .result div -->
                    <div class="text-center m-3" data-events-loading-block="" style="display: block;">
                        <span class="far fa-spinner fa-spin ms-2">
                        </span>
                        {{ trans('marketplace.messages.loading_events') }}
                    </div>
                    <div class="text-center m-3" data-no-events-block="" style="display: none;">
                        {{ trans('marketplace.messages.no_result_found') }}
                    </div>
                    <div class="row mt-4 mb-4 d-none" data-events-block="">
                    </div>
                    <div class="mt-4 text-center" data-loadmore-block="" style="display: none;">
                        <a class="btn btn-primary" data-loadmore-control="" href="javascript:void(0);" type="button">
                            {{ trans('marketplace.messages.load_more_events') }}
                        </a>
                    </div>
                    <!-- /.result div -->
                </div>
            </div>
        </div>
    </div>
</section>
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
<script src="{{ asset('assets/plugins/OwlCarousel2/owl.carousel.min.js?var='.rand()) }}">
</script>
<script type="text/javascript">
    var timezone = '{{ $timezone }}',
        labels = {
            loadMore: "{{ trans('marketplace.messages.load_more_events') }}",
            loadingText: "<span class='far fa-spinner fa-spin'></span>",
        },
        urls = {
            eventsByCategory: "{{ route('admin.marketplace.get-events') }}",
        },
        dataTableConf = {
            pagination: {
                value: {{ $pagination }},
                previous: `{!! trans('buttons.pagination.previous') !!}`,
                next: `{!! trans('buttons.pagination.next') !!}`,
            },
        },
        visibleCompany = {{ !empty($company) && ($company->is_reseller || !is_null($company->parent_id)) ? 'true' : 'false' }};
</script>
<script src="{{ mix('js/marketplace/marketplace.js') }}">
</script>
@endsection
