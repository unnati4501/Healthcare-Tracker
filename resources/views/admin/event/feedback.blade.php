@extends('layouts.app')

@section('after-styles')
<link href="{{asset('assets/plugins/datatables/dataTables.bootstrap5.min.css?var='.rand())}}" rel="stylesheet"/>
<link href="{{asset('assets/plugins/datepicker/datepicker3.css?var='.rand())}}" rel="stylesheet"/>
@endsection

@section('content-header')
<!-- Content Header (Page header) -->
<div class="content-header">
    <div class="container-fluid">
        <div class="d-md-flex justify-content-between">
            <div class="align-self-center">
                <h1>
                    {{ $eventSubcategory->name }}
                    <span class="fal fa-long-arrow-right">
                    </span>
                    {{ $event->name }}
                </h1>
                {!! Breadcrumbs::render('event.feedback') !!}
            </div>
            <div class="align-self-center">
                <a class="btn btn-outline-primary" href="{{ route('admin.event.index', '#events-tab') }}">
                    <i class="far fa-arrow-left me-3 align-middle">
                    </i>
                    <span class="align-middle">
                        {{ __('Back to events') }}
                    </span>
                </a>
            </div>
        </div>
    </div>
</div>
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
                {{ Form::open(['route' => ['admin.event.feedback', $event->id], 'class' => 'form-horizontal', 'method' => 'get', 'role' => 'form', 'id' => 'feedbackSearch']) }}
                <div class="search-outer d-md-flex justify-content-between">
                    <div>
                        <div class="form-group">
                            @if(in_array($roleType, ['zca', 'rca']))
                            {{ Form::text(null, $company->name, ['class' => 'form-control', 'disabled' => true]) }}
                            {{ Form::hidden('company', $company->id, ['id' => 'company']) }}
                            @else
                            {{ Form::select('company', $companies, request()->get('company'), ['class' => 'form-control select2', 'id' => 'company', 'placeholder' => 'Select Company', 'data-placeholder' => 'Select Company']) }}
                            @endif
                        </div>
                        <div class="form-group">
                            {{ Form::select('presenters', $presenters, request()->get('presenters'), ['class' => 'form-control select2', 'id' => 'presenters', 'placeholder' => 'Select Presenter', 'data-placeholder' => 'Select Presenter']) }}
                        </div>
                        <div class="form-group">
                            {{ Form::select('feedback', $feedback, request()->get('feedback'), ['class' => 'form-control select2', 'id' => 'feedback', 'placeholder' => 'Select FeedBack Type', 'data-placeholder' => 'Select FeedBack Type']) }}
                        </div>
                    </div>
                    <div class="search-actions align-self-start">
                        <button class="me-md-4 filter-apply-btn" type="submit">
                            {{ trans('buttons.general.apply') }}
                        </button>
                        <a class="filter-cancel-icon" href="{{ route('admin.event.feedback', $event->id) }}">
                            <i class="far fa-times">
                            </i>
                            <span class="d-md-none ms-2 ms-md-0">
                                {{ trans('buttons.general.reset') }}
                            </span>
                        </a>
                    </div>
                    {{ Form::close() }}
                </div>
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
        <div class="card">
            <div class="card-body">
                <div class="text-center" id="graph-loader">
                    <i class="fa fa-spinner fa-spin">
                    </i>
                    loading graph...
                </div>
                <div id="graph-area">
                </div>
                <div class="card-table-outer">
                    <div class="table-responsive">
                        <table class="table custom-table" id="eventFeedbackManagment">
                            <thead>
                                <tr>
                                    <th>
                                        {{trans('event.feedback.table.company_name')}}
                                    </th>
                                    <th>
                                        {{trans('event.feedback.table.presenter_name')}}
                                    </th>
                                    <th class="text-center ignore-export" style="width: 8%;">
                                        {{trans('event.feedback.table.emoji')}}
                                    </th>
                                    <th>
                                        {{trans('event.feedback.table.feedback_type')}}
                                    </th>
                                    <th>
                                        {{trans('event.feedback.table.notes')}}
                                    </th>
                                    <th>
                                        {{trans('event.feedback.table.date')}}
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
<script src="{{asset('assets/plugins/moment/moment.min.js?var='.rand()) }}">
</script>
<script src="{{asset('assets/plugins/moment/moment-timezone-with-data-10-year-range.js?var='.rand()) }}">
</script>
<script src="{{asset('assets/plugins/datatables/jquery.dataTables.min.js?var='.rand())}}">
</script>
<script src="{{asset('assets/plugins/datatables/dataTables.bootstrap5.min.js?var='.rand())}}">
</script>

<script src="{{asset('assets/plugins/datatables/jszip.min.js?var='.rand())}}">
</script>
<script src="{{asset('assets/plugins/datepicker/bootstrap-datepicker.js?var='.rand())}}">
</script>
<script id="graphTemplate" type="text/html">
    <p class="m-0 font-19 text-center">{{ trans('event.feedback.title.graph_title') }}</p>
    <div class="mb-4" style="margin-top: 85px;">
        <div class="progress experience-score-bar">#bar#</div>
        <ul class="experience-score-legend">#legend#</ul>
    </div>
</script>
<script id="graphBarTemplate" type="text/html">
    <div aria-valuemax="100" aria-valuemin="0" data-bs-html="true" aria-valuenow="#percentage#" class="progress-bar #feedbackClass#" data-placement="bottom" data-bs-toggle="tooltip" role="progressbar" style="width: #percentage#%" title="#tooltip#">
        #percentage#%
    </div>
</script>
<script id="graphLegendTemplate" type="text/html">
    <li class="#feedbackClass#">
        #feedbackName#
    </li>
</script>
<script type="text/javascript">
    var timezone = `{{ $timezone }}`,
        date_format = `{{ $date_format }}`,
        pagination = {
            value: {{ $pagination }},
            previous: `{!! trans('buttons.pagination.previous') !!}`,
            next: `{!! trans('buttons.pagination.next') !!}`,
        };

    function loadGraph() {
        $('#graph-loader').show();
        $('#graph-area').empty();
        $.ajax({
            url: '{{ route('admin.event.feedback', $event->id) }}',
            type: 'POST',
            dataType: 'json',
            data: {
                type: 'graph',
                company: $("#company").val(),
                presenter: $("#presenters").val(),
            },
        })
        .done(function(data) {
            console.log(data);
            var graphTemplate  = $('#graphTemplate').text().trim(),
                graphBarTemplate = $('#graphBarTemplate').text().trim(),
                graphLegendTemplate = $('#graphLegendTemplate').text().trim(),
                bars = legends = "";

            if(data.data && data.data.length > 0) {
                var length = (data.data.length - 1);
                $(data.data).each(function(index, bar) {
                    bars += graphBarTemplate
                        .replace(/\#feedbackClass#/g, bar.class + ((length == index && bar.percentage <= 5) ? " small-bar-size" : ""))
                        .replace(/\#percentage#/g, bar.percentage.toFixed(2))
                        .replace(/\#tooltip#/g, `${bar.name}: ${bar.percentage.toFixed(2)}%`);
                    legends += graphLegendTemplate
                        .replace(/\#feedbackClass#/g, bar.class)
                        .replace(/\#feedbackName#/g, bar.name);
                });
                graphTemplate = graphTemplate.replace('#bar#', bars).replace('#legend#', legends);
                $('#graph-area').html(graphTemplate);
            }
        })
        .fail(function(error) {
            toastr.error('Failed to load event experience score graph');
        })
        .always(function() {
            $('#graph-loader').hide();
            $('[data-bs-toggle="tooltip"]').tooltip();
        });
    }

    $(document).ready(function() {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // to load graph
        loadGraph();

        $('#eventFeedbackManagment').DataTable({
            processing: true,
            serverSide: true,
            destroy: true,
            ajax: {
                type: 'POST',
                url: '{{ route('admin.event.feedback', $event->id) }}',
                data: {
                    type: 'listing',
                    company: $("#company").val(),
                    presenter: $("#presenters").val(),
                    feedback: $("#feedback").val(),
                    getQueryString: window.location.search
                },
            },
            columns: [{
                data: 'company_name',
                name: 'company_name',
                visible: '{{ (($roleType == "rca" || $roleType == "zca") ? false : true) }}',
            }, {
                data: 'presenter_name',
                name: 'presenter_name',
            }, {
                data: 'emoji',
                name: 'emoji',
                className: 'text-center',
                searchable: false,
                sortable: false,
                render: function(data, type, row) {
                    return `<img class="tbl-user-img img-circle" src="${data}" width="70" />`;
                }
            }, {
                data: 'feedback_type',
                name: 'feedback_type',
            }, {
                data: 'feedback',
                name: 'feedback',
            }, {
                data: 'created_at',
                name: 'created_at',
                render: function(data, type, row) {
                    return moment.utc(data).tz(timezone).format(date_format);
                }
            }],
            pageLength: pagination.value,
            lengthChange: true,
            lengthMenu: [[25, 50, 100], [25, 50, 100]],
            searching: false,
            order: [],
            autoWidth: false,
            columnDefs: [{
                targets: 'no-sort',
                orderable: false,
            }],
            dom: '<<"pagination-top"lB><t><"pagination-wrap"<"pagination-left"i>p>',
            language: {
                paginate: {
                    previous: pagination.previous,
                    next: pagination.next,
                },
                lengthMenu: "Entries per page _MENU_",
            },
            buttons: [{
                extend: 'excel',
                text: `<i class="far fa-file-excel me-3 align-middle"></i> Export to excel`,
                className: 'btn btn-primary',
                title: `Event CSAT(Feedback) ${Date.now()}`,
                download: 'open',
                orientation:'landscape',
                exportOptions: {
                    columns: ':visible:not(.ignore-export)',
                    order : 'current'
                }
            }]
        });
    });
</script>
@endsection
