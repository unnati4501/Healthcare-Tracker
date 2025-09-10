@extends('layouts.app')

@section('after-styles')
<link href="{{ asset('assets/plugins/datatables/dataTables.bootstrap5.min.css?var='.rand()) }}" rel="stylesheet"/>
<link href="{{ asset('assets/plugins/daterangepicker/daterangepicker.css?var='.rand()) }}" rel="stylesheet"/>
@endsection

@section('content-header')
<!-- Content Header (Page header) -->
@include('admin.zcsurvey.review-suggestion.breadcrumb', [
  'mainTitle' => trans('survey.feedback.title.index'),
  'breadcrumb' => Breadcrumbs::render('survey.feedback'),
])
<!-- /.content-header -->
@endsection

@section('content')
<section class="content">
    <div class="container-fluid">
        <div class="nav-tabs-wrap">
            <!-- .navs -->
            <ul class="nav nav-tabs tabs-line-style" id="myTab" role="tablist">
                <li class="nav-item">
                    <a aria-controls="{{ trans('survey.feedback.tabs.suggestions') }}" aria-selected="true" class="nav-link active" data-bs-toggle="tab" href="#all" id="allsuggestions" role="tab">
                        {{ trans('survey.feedback.tabs.suggestions') }}
                    </a>
                </li>
                <li class="nav-item">
                    <a aria-controls="{{ trans('survey.feedback.tabs.favorites') }}" aria-selected="false" class="nav-link" data-bs-toggle="tab" href="#favorites" id="favoritesuggestions" role="tab">
                        {{ trans('survey.feedback.tabs.favorites') }}
                    </a>
                </li>
            </ul>
            <!-- /.navs -->
            <!-- search-block -->
            <div class="card search-card">
                <div class="card-body pb-0">
                    <h4 class="d-md-none">
                        {{ trans('buttons.general.filter') }}
                    </h4>
                    {{ Form::open(['route' => 'admin.reviewSuggestion.index', 'class' => 'form-horizontal', 'method' => 'get', 'role' => 'form', 'id' => 'reviewSuggestionSearch']) }}
                    <div class="search-outer d-md-flex justify-content-between">
                        <div>
                            @if($SAOnly)
                            <div class="form-group">
                                {{ Form::select('company_id', $company, request()->get('company_id'), ['class' => "form-control select2", 'id' => 'company_id', 'placeholder' => trans('survey.feedback.filter.company'), 'data-placeholder' => trans('survey.feedback.filter.company'), 'data-allow-clear' => 'true']) }}
                            </div>
                            @endif
                            <div class="form-group">
                                <div class="datepicker-wrap mb-0">
                                    {{ Form::text('date_range', request()->get('date_range'), ['class' => 'form-control datepicker', 'id' => 'date_range', 'placeholder' => trans('survey.feedback.filter.date'), 'readonly' => true]) }}
                                    <i class="far fa-calendar">
                                    </i>
                                </div>
                            </div>
                        </div>
                        <div class="search-actions align-self-start">
                            <button class="me-md-4 filter-apply-btn" type="submit">
                                {{ trans('buttons.general.apply') }}
                            </button>
                            <a class="filter-cancel-icon" href="{{ route('admin.reviewSuggestion.index') }}">
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
            <!-- /.search-block -->
            <!-- .tab-content -->
            <div class="tab-content" id="myTabContent">
                <div aria-labelledby="allsuggestions" class="tab-pane fade show active" id="all" role="tabpanel">
                    <a class="btn btn-primary filter-btn" href="javascript:void(0);">
                        <i class="far fa-filter me-2 align-middle">
                        </i>
                        <span class="align-middle">
                            {{ trans('buttons.general.filter') }}
                        </span>
                    </a>
                    <div class="card">
                        <div class="card-body">
                            <div class="card-table-outer">
                                <div class="table-responsive">
                                    <table class="table custom-table" id="allSurveySuggestionManagment">
                                        <thead>
                                            <tr>
                                                <th class="th-btn-sm no-sort">
                                                    {{ trans('survey.feedback.table.sr_no') }}
                                                </th>
                                                <th>
                                                    {{ trans('survey.feedback.table.suggestion') }}
                                                </th>
                                                <th>
                                                    {{ trans('survey.feedback.table.survey_name') }}
                                                </th>
                                                @if($SAOnly)
                                                <th>
                                                    {{ trans('survey.feedback.table.company_name') }}
                                                </th>
                                                @endif
                                                <th class="th-btn-4">
                                                    {{ trans('survey.feedback.table.publish_date') }}
                                                </th>
                                                <th>
                                                    {{ trans('survey.feedback.table.status') }}
                                                </th>
                                                @if(!$SAOnly)
                                                <th class="text-center th-btn no-sort">
                                                    {{ trans('survey.feedback.table.action') }}
                                                </th>
                                                @endif
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
                <div aria-labelledby="favoritesuggestions" class="tab-pane fade" id="favorites" role="tabpanel">
                    <div class="card">
                        <div class="card-body">
                            <div class="card-table-outer">
                                <div class="table-responsive">
                                    <table class="table custom-table" id="favoSurveySuggestionManagment">
                                        <thead>
                                            <tr>
                                                <th class="th-btn-sm no-sort">
                                                    {{ trans('survey.feedback.table.sr_no') }}
                                                </th>
                                                <th>
                                                    {{ trans('survey.feedback.table.suggestion') }}
                                                </th>
                                                <th>
                                                    {{ trans('survey.feedback.table.survey_name') }}
                                                </th>
                                                @if($SAOnly)
                                                <th>
                                                    {{ trans('survey.feedback.table.company_name') }}
                                                </th>
                                                @endif
                                                <th>
                                                    {{ trans('survey.feedback.table.publish_date') }}
                                                </th>
                                                <th>
                                                    {{ trans('survey.feedback.table.status') }}
                                                </th>
                                                @if(!$SAOnly)
                                                <th class="text-center th-btn no-sort">
                                                    {{ trans('survey.feedback.table.action') }}
                                                </th>
                                                @endif
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
            </div>
            <!-- /.tab-content -->
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
<script src="{{asset('assets/plugins/daterangepicker/daterangepicker.js?var='.rand()) }}">
</script>
<script type="text/javascript">
    var timezone = `{{ $timezone }}`,
        date_format = `{{ $date_format }}`,
        allSurveySuggestionManagment,
        favoSurveySuggestionManagment,
        pagination = {
            value: {{ $pagination }},
            previous: `{!! trans('buttons.pagination.previous') !!}`,
            next: `{!! trans('buttons.pagination.next') !!}`,
        },
        url = {
            datatable: `{{ route('admin.reviewSuggestion.getSuggestions') }}`,
            favorite: `{{ route('admin.reviewSuggestion.suggestionAction', ':id') }}`,
        },
        messages = `{{ json_encode(trans('survey.feedback.messages')) }}`;
    messages.clear = `{{ trans('buttons.general.clear') }}`;

    $(document).ready(function() {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        allSurveySuggestionManagment = $('#allSurveySuggestionManagment').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: url.datatable,
                data: {
                    favoriteOnly: false,
                    company_id: $('#company_id').val(),
                    date_range: $('#date_range').val(),
                    getQueryString: window.location.search
                },
            },
            columns: [{
                data: 'id',
                name: 'id',
                searchable: false,
                sortable: false,
            },
            {
                data: 'suggestion',
                name: 'suggestion',
            },
            {
                data: 'survey_title',
                name: 'survey_title',
            },
            @if($SAOnly)
            {
                data: 'company_name',
                name: 'company_name',
            },
            @endif
            {
                data: 'published_date',
                name: 'published_date',
                render: function (data, type, row) {
                    return moment.utc(data).tz(timezone).format(date_format);
                }
            },
            {
                data: 'status',
                name: 'status',
                searchable: false,
            },
            @if(!$SAOnly)
            {
                data: 'actions',
                name: 'actions',
                searchable: false,
                sortable: false,
                className: 'text-center'
            }
            @endif
            ],
            paging: true,
            dom: '<<"pagination-top"lB><t><"pagination-wrap"<"pagination-left"i>p>',
            pageLength: pagination.value,
            lengthChange: false,
            searching: false,
            ordering: true,
            order: [],
            info: true,
            autoWidth: false,
            columnDefs: [{
                targets: 'no-sort',
                orderable: false,
            }],
            rowCallback: function(row, data, displayNum, displayIndex, dataIndex) {
                var pageInfo = allSurveySuggestionManagment.page.info();
                $("td:eq(0)", row).html(((pageInfo.page) * pageInfo.length) + displayIndex + 1);
                return row;
            },
            language: {
                paginate: {
                    previous: pagination.previous,
                    next: pagination.next,
                }
            },
        });
        favoSurveySuggestionManagment = $('#favoSurveySuggestionManagment').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: url.datatable,
                data: {
                    favoriteOnly: true,
                    company_id: $('#company_id').val(),
                    date_range: $('#date_range').val(),
                    getQueryString: window.location.search
                },
            },
            columns: [{
                data: 'id',
                name: 'id',
                searchable: false,
                sortable: false,
            },
            {
                data: 'suggestion',
                name: 'suggestion',
            },
            {
                data: 'survey_title',
                name: 'survey_title',
            },
            @if($SAOnly)
            {
                data: 'company_name',
                name: 'company_name',
            },
            @endif
            {
                data: 'published_date',
                name: 'published_date',
                render: function (data, type, row) {
                    return moment.utc(data).tz(timezone).format(date_format);
                }
            },
            {
                data: 'status',
                name: 'status',
                searchable: false,
            },
            @if(!$SAOnly)
            {
                data: 'actions',
                name: 'actions',
                searchable: false,
                sortable: false,
                className: 'text-center',
            }
            @endif
            ],
            paging: true,
            dom: '<<"pagination-top"lB><t><"pagination-wrap"<"pagination-left"i>p>',
            pageLength: pagination.value,
            lengthChange: false,
            searching: false,
            ordering: true,
            order: [],
            info: true,
            autoWidth: false,
            columnDefs: [{
                targets: 'no-sort',
                orderable: false,
            }],
            rowCallback: function(row, data, displayNum, displayIndex, dataIndex) {
                var pageInfo = favoSurveySuggestionManagment.page.info();
                $("td:eq(0)", row).html(((pageInfo.page) * pageInfo.length) + displayIndex + 1);
                return row;
            },
            language: {
                paginate: {
                    previous: pagination.previous,
                    next: pagination.next,
                }
            },
        });
    });
</script>
<script src="{{ mix('js/review-suggestion/index.js') }}" type="text/javascript">
</script>
@endsection
