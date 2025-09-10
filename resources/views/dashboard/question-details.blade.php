@extends('layouts.app')

@section('after-styles')
<link href="{{ asset('assets/plugins/datatables/dataTables.bootstrap5.min.css?var='.rand()) }}" rel="stylesheet"/>
<link href="{{ asset('assets/plugins/datepicker/v1.9.0/css/bootstrap-datepicker3.min.css?var='.rand()) }}" rel="stylesheet"/>
<style type="text/css">
    .card-sub-title { color: #929292; font-size: 12px; }
</style>
@endsection

@section('content-header')
<!-- Content Header (Page header) -->
@include('dashboard.question-report-breadcrumb', [
    'mainTitle' => trans('dashboard.audit.headings.review_free_text_question'),
    'breadcrumb' => Breadcrumbs::render('dashboard.question_report.details', route('dashboard.questionReport', ([$question->category_id] + $requestParams))),
    'backToReport' => route('dashboard.questionReport', ([$question->category_id] + $requestParams))
])
<!-- /.content-header -->
@endsection

@section('content')
<section class="content">
    <div class="container-fluid">
        <div class="card collapsed-card">
            <div class="card-header detailed-header">
                <h3 class="align-items-center card-title d-flex mb-1">
                    <b class="me-2">
                        Q.
                    </b>
                    {{ $question->title }}
                </h3>
                <div class="card-sub-title">
                    <span>
                        {{ $question->category->display_name }}
                    </span>
                    <i class="fal fa-long-arrow-right ms-1 me-1">
                    </i>
                    <span>
                        {{ $question->subcategory->display_name }}
                    </span>
                </div>
            </div>
        </div>
        <!-- search-block -->
        <div class="card search-card">
            <div class="card-body pb-0">
                <h4 class="d-md-none">
                    {{ trans('buttons.general.filter') }}
                </h4>
                {{ Form::open(['route' => ['dashboard.questionReportDetails', $question], 'class' => 'form-horizontal', 'method'=>'get','role' => 'form', 'id' => 'answersSearchFrm']) }}
                <div class="search-outer d-md-flex justify-content-between">
                    <div>
                        @if($role->group == 'zevo' || ($role->group == 'reseller' && $company->parent_id == null))
                        <div class="form-group">
                            {{ Form::select('company', ($companies ?? []), request()->get('company'), ['class' => 'form-control select2', 'id'=>'company', 'placeholder' => "Select Company", 'data-placeholder' => "Select Company", 'data-allow-clear' => 'true']) }}
                        </div>
                        @else
                        <div class="form-group" style="display: none">
                            {{ Form::select('company', $companies, $company_id, ['class' => "form-control select2", 'id'=>'company', 'placeholder' => trans('labels.dashboard.company'), 'data-placeholder' => trans('labels.dashboard.company'), 'data-allow-clear' => 'true', 'disabled'=>true, 'target-data' => 'department_id']) }}
                        </div>
                        @endif
                        <div class="form-group daterange-outer" >
                            <div class="input-daterange justify-content-between mb-0 monthranges">
                                <div class="datepicker-wrap me-0 mb-0">
                                    {{ Form::text('from', null, ['class' => 'input-sm form-control bg-white','id' => 'fromMonthRange', 'autocomplete' => 'off', 'placeholder' => 'From month', 'readonly' => true]) }}
                                    <i class="far fa-calendar">
                                    </i>
                                </div>
                                <span class="input-group-addon text-center">
                                    -
                                </span>
                                <div class="datepicker-wrap me-0 mb-0">
                                    {{ Form::text('to', null, ['class' => 'input-sm form-control bg-white','id' => 'toMonthRange', 'autocomplete' => 'off', 'placeholder' => 'To month', 'readonly' => true]) }}
                                    <i class="far fa-calendar">
                                    </i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="search-actions align-self-start">
                        <button class="me-md-4 filter-apply-btn" type="submit">
                            {{ trans('buttons.general.apply') }}
                        </button>
                        <a class="filter-cancel-icon" href="{{ route('dashboard.questionReportDetails', $question->id) }}">
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
        <!-- listing -->
        <div class="card">
            <div class="card-body">
                <div class="card-table-outer">
                    <div class="table-responsive">
                        <table class="table custom-table" id="questionAnswers">
                            <thead>
                                <tr>
                                    <th class="th-btn-2 text-center">
                                        {{ trans('dashboard.audit.details_table.sr_no') }}
                                    </th>
                                    <th width="th-btn-4">
                                        {{ trans('dashboard.audit.details_table.company') }}
                                    </th>
                                    <th>
                                        {{ trans('dashboard.audit.details_table.answer') }}
                                    </th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <!-- /.listing -->
    </div>
</section>
@endsection
@section('after-scripts')
<script src="{{asset('assets/plugins/datatables/jquery.dataTables.min.js?var='.rand())}}">
</script>
<script src="{{asset('assets/plugins/datatables/dataTables.bootstrap5.min.js?var='.rand())}}">
</script>
<script src="{{ asset('assets/plugins/moment/moment.min.js?var='.rand()) }}">
</script>
<script src="{{ asset('assets/plugins/datepicker/bootstrap-datepicker.min.js?var='.rand()) }}">
</script>
<script type="text/javascript">
    var pagination = {
        value: {{ $pagination }},
        previous: `{!! trans('buttons.pagination.previous') !!}`,
        next: `{!! trans('buttons.pagination.next') !!}`,
    };
    $(document).ready(function() {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        var _requestParams = {!! json_encode($requestParams) !!};

        $('.monthranges').datepicker({
            format: "M, yyyy",
            startView: 1,
            minViewMode: 1,
            maxViewMode: 2,
            clearBtn: false,
            autoclose: true,
            endDate: ((moment().isSame(moment().endOf('month'), 'date')) ? moment().endOf('month').add(1, 'd').toDate() : moment().endOf('month').toDate())
        });
        $('#fromMonthRange').datepicker("setDate", ((_requestParams.from != undefined) ? moment(_requestParams.from).toDate() : moment().subtract(5, 'months').toDate()));
        $('#toMonthRange').datepicker("setDate", ((_requestParams.to != undefined) ? moment(_requestParams.to).toDate() : moment().toDate()));

        var _company = $('#company').val().trim(),
            _fromMonthRange = $('#fromMonthRange').val().trim(),
            _toMonthRange = $('#toMonthRange').val().trim();
        if(_company != '' || _fromMonthRange != '' || _toMonthRange != '') {
            $('#searchpanel').removeClass('collapsed-card');
        } else {
            $('#searchpanel').addClass('collapsed-card');
        }

        $(document).on('click', '#answersSearchFrmSubmit', function(e) {
            e.preventDefault();
            var action = $('#answersSearchFrm').attr('action'),
                now = moment(),
                fromDate = moment($('#fromMonthRange').datepicker("getDate")),
                endDate = moment($('#toMonthRange').datepicker("getDate")).endOf('month'),
                company = ($('#company').val() || 0),
                data = {};
            if (endDate > now) {
                endDate = now;
            }
            data.from = fromDate.format('YYYY-MM-DD 00:00:00');
            data.to = endDate.format('YYYY-MM-DD 23:59:59');
            if(company > 0) {
                data.company = company;
            }
            var qString = $.param(data);
            window.location.href = action + ((qString != '') ? '?' + qString : '');
        });

        $('#questionAnswers').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                type: 'POST',
                url: '{{ route('dashboard.questionAnswers', $question->id) }}',
                data: function(data) {
                    var now = moment(),
                        fromDate = moment($('#fromMonthRange').datepicker("getDate")),
                        endDate = moment($('#toMonthRange').datepicker("getDate")).endOf('month');
                    data.tier =  4;
                    data.companyId = $('#company').val();
                    if (endDate > now) {
                        endDate = now;
                    }
                    data.fromDate = fromDate.format('YYYY-MM-DD 00:00:00');
                    data.endDate = endDate.format('YYYY-MM-DD 23:59:59');
                    return data;
                },
            },
            columns: [{
                name: 'id',
                data: 'DT_RowIndex',
                class: 'text-center',
                sortable: false,
            }, {
                data: 'company_name',
                name: 'company_name',
                @if($role->group != 'zevo')
                visible: false
                @endif
            }, {
                data: 'answer_value',
                name: 'answer_value'
            }],
            paging: true,
            dom: '<<"pagination-top"lB><t><"pagination-wrap"<"pagination-left"i>p>',
            pageLength: pagination.value,
            lengthChange: false,
            searching: false,
            ordering: true,
            order: [],
            info: true,
            autoWidth: false,
            language: {
                paginate: {
                    previous: pagination.previous,
                    next: pagination.next,
                }
            },
        });
    });
</script>
@endsection
