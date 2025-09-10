@extends('layouts.app')

@section('after-styles')
<link href="{{asset('assets/plugins/datatables/dataTables.bootstrap5.min.css?var='.rand())}}" rel="stylesheet"/>
<link href="{{asset('assets/plugins/datepicker/v1.9.0/css/bootstrap-datepicker3.min.css?var='.rand())}}" rel="stylesheet"/>
<style type="text/css">
    .card-sub-title { color: #929292; font-size: 12px; }
</style>
@endsection

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6 order-last order-sm-first">
                <h1 class="m-0 text-dark">
                    {{ trans('labels.dashboard.audit.review_free_text_question') }}
                </h1>
            </div>
            <div class="col-sm-6 d-flex justify-content-sm-end align-items-center order-first order-sm-last">
                <div class="text-end">
                    <a class="btn btn-primary btn-sm btn-effect" href="{{ route('dashboard.questionReport', ([$question->category_id] + $requestParams)) }}">
                        <i class="fas fa-chevron-left">
                        </i>
                        {{ trans('labels.dashboard.audit.back_to_report') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
<section class="content">
    <div class="container-fluid">
        <div class="row">
            <section class="col-lg-12">
                <div class="card collapsed-card">
                    <div class="card-header">
                        <h3 class="align-items-center card-title d-flex mb-1">
                            <b>
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
            </section>
            <section class="col-lg-12">
                <div class="card collapsed-card" id="searchpanel">
                    <div class="card-header" data-widget="collapse">
                        <h3 class="card-title">
                            {{ trans('labels.recipe.search') }}
                        </h3>
                        <div class="card-tools">
                            <button class="btn btn-tool" data-widget="collapse" type="button">
                                <i class="fa fa-chevron-up">
                                </i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        {{ Form::open(['route' => ['dashboard.questionReportDetails', $question], 'class' => 'form-horizontal', 'method'=>'get','role' => 'form', 'id' => 'answersSearchFrm']) }}
                        <div class="row">
                            @if($role->group == 'zevo' || ($role->group == 'reseller' && $company->parent_id == null))
                            <div class="form-group col-md-4">
                                {{ Form::select('company', ($companies ?? []), request()->get('company'), ['class' => 'form-control select2', 'id'=>'company', 'placeholder' => "Select Company", 'data-placeholder' => "Select Company", 'data-allow-clear' => 'true']) }}
                            </div>
                            @else
                            <div class="form-group col-md-4" style="display: none">
                                {{ Form::select('company', $companies, $company_id, ['class' => "form-control select2", 'id'=>'company', 'placeholder' => trans('labels.dashboard.company'), 'data-placeholder' => trans('labels.dashboard.company'), 'data-allow-clear' => 'true', 'disabled'=>true, 'target-data' => 'department_id']) }}
                            </div>
                            @endif
                            <div class="form-group col-md-8">
                                <div class="input-group input-daterange monthranges">
                                    <span class="input-group-text bg-white">
                                        <i class="fal fa-calendar-alt">
                                        </i>
                                    </span>
                                    {{ Form::text('from', null, ['class' => 'input-sm form-control bg-white','id' => 'fromMonthRange', 'autocomplete' => 'off', 'placeholder' => 'From month', 'readonly' => true]) }}
                                    <span class="input-group-addon text-center">
                                        to
                                    </span>
                                    <span class="input-group-text bg-white">
                                        <i class="fal fa-calendar-alt">
                                        </i>
                                    </span>
                                    {{ Form::text('to', null, ['class' => 'input-sm form-control bg-white','id' => 'toMonthRange', 'autocomplete' => 'off', 'placeholder' => 'To month', 'readonly' => true]) }}
                                </div>
                            </div>
                        </div>
                        <div class="text-center">
                            <button class="btn btn-primary btn-effect me-2" id="answersSearchFrmSubmit" type="submit">
                                <i class="fal fa-search me-2">
                                </i>
                                {{trans('labels.buttons.submit')}}
                            </button>
                            <a class="btn btn-secondary btn-effect me-2" href="{{ route('dashboard.questionReportDetails', $question->id) }}">
                                <i class="fal fa-undo me-2">
                                </i>
                                {{trans('labels.buttons.reset')}}
                            </a>
                        </div>
                        {{ Form::close() }}
                    </div>
                </div>
                <!--/.direct-chat -->
            </section>
            <section class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover" id="questionAnswers">
                                <thead>
                                    <tr>
                                        <th class="text-center" width="10%">
                                            {{ trans('labels.dashboard.audit.sr_no') }}
                                        </th>
                                        <th width="25%">
                                            {{ trans('labels.dashboard.audit.company') }}
                                        </th>
                                        <th>
                                            {{ trans('labels.dashboard.audit.answer') }}
                                        </th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </section>
        </div>
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
            pageLength: {{ $pagination }},
            lengthChange: false,
            searching: false,
            ordering: true,
            order: [],
            info: true,
            autoWidth: false,
            stateSave: false
        });
    });
</script>
@endsection
