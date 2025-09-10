@extends('layouts.app')

@section('after-styles')
<link href="{{ asset('assets/plugins/datatables/dataTables.bootstrap5.min.css?var='.rand()) }}" rel="stylesheet"/>
<link href="{{ asset('assets/plugins/daterangepicker/daterangepicker.css?var='.rand()) }}" rel="stylesheet"/>
<link href="{{ asset('assets/plugins/datepicker/v1.9.0/css/bootstrap-datepicker3.min.css?var='.rand()) }}" rel="stylesheet"/>
@endsection

@section('content-header')
<!-- Content Header (Page header) -->
@include('admin.zcsurvey.hr-report.breadcrumb', [
    'mainTitle' => trans('survey.hr_report.title.free_text'),
    'breadcrumb' => Breadcrumbs::render('survey.hr_report.freetext'),
    'backBtn' => true
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
                {{ Form::open(['route' => 'admin.hrReport.reviewFreeText', 'class' => 'form-horizontal', 'method' => 'get', 'role' => 'form', 'id' => 'hrReportSearch']) }}
                <div class="search-outer d-md-flex justify-content-between">
                    <div>
                        @if($isSA)
                        <div class="form-group">
                            {{ Form::select('company', $company, request()->get('company'), ['class' => 'form-control select2', 'id' => 'company', 'data-allow-clear' => 'true', 'placeholder' => 'Select company', 'data-placeholder' => 'Select company'] ) }}
                        </div>
                        <div class="form-group">
                            {{ Form::select('category', $category, request()->get('category'), ['class' => 'form-control select2', 'id' => 'category', 'placeholder' => 'Select category', 'data-placeholder' => 'Select category', 'data-allow-clear' => 'true'] ) }}
                        </div>
                        @else
                        <div class="form-group">
                            {{ Form::select('category', $category, request()->get('category'), ['class' => 'form-control select2', 'id' => 'category', 'placeholder' => 'Select category', 'data-placeholder' => 'Select category', 'data-allow-clear' => 'true'] ) }}
                        </div>
                        @endif
                        <div class="form-group daterange-outer">
                            <div class="input-daterange dateranges justify-content-between mb-0 monthranges">
                                <div class="datepicker-wrap me-0 mb-0">
                                    {{ Form::text('monthrange', null, ['id' => 'globalFromMonth', 'class' => 'form-control', 'placeholder' => 'From month', 'readonly' => true]) }}
                                    <i class="far fa-calendar">
                                    </i>
                                </div>
                                <span class="input-group-addon text-center">
                                    -
                                </span>
                                <div class="datepicker-wrap me-0 mb-0">
                                    {{ Form::text('monthrange', null, ['id' => 'globalToMonth', 'class' => 'form-control', 'placeholder' => 'To month', 'readonly' => true]) }}
                                    <i class="far fa-calendar">
                                    </i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="search-actions align-self-start">
                        <button class="me-md-4 filter-apply-btn" id="hrReportSearchSubmitFrm" type="submit">
                            {{ trans('buttons.general.apply') }}
                        </button>
                        <a class="filter-cancel-icon" href="{{ route('admin.hrReport.reviewFreeText') }}">
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
        <div class="row">
            @forelse($questions as $key => $question)
                @include('admin.zcsurvey.hr-report.free-text-block', ['index' => (($currPage * $qespagination) + ($key + 1)), '$question' => $question])
            @empty
            <section class="col-lg-12">
                <div class="alert alert-info text-center" role="alert">
                    {{ trans('survey.hr_report.messages.no_data') }}
                </div>
            </section>
            @endforelse

            @if($questions->hasPages())
            <section class="col-lg-12">
                {{ $questions->appends($queryString)->links('custom.pagination') }}
            </section>
            @endif
        </div>
    </div>
</section>
@endsection
@section('after-scripts')
<script src="{{asset('assets/plugins/datatables/jquery.dataTables.min.js?var='.rand())}}" type="text/javascript">
</script>
<script src="{{asset('assets/plugins/datatables/dataTables.bootstrap5.min.js?var='.rand())}}" type="text/javascript">
</script>
<script src="{{ asset('assets/plugins/moment/moment.min.js?var='.rand()) }}" type="text/javascript">
</script>
<script src="{{ asset('assets/plugins/daterangepicker/daterangepicker.js?var='.rand()) }}" type="text/javascript">
</script>
<script src="{{ asset('assets/plugins/datepicker/bootstrap-datepicker.min.js?var='.rand()) }}" type="text/javascript">
</script>
<script type="text/javascript">
    var _category = $('#category').val(),
        _dates = $('#dates').val(),
        _company = ($('#company').val() || ""),
        pagination = {
            value: {{ $anspagination }},
            previous: `{!! trans('buttons.pagination.previous') !!}`,
            next: `{!! trans('buttons.pagination.next') !!}`,
        },
        requestParams = {!! json_encode($requestParams) !!},
        categoryUrl = "{{ route('admin.hrReport.getCompanyWiseCategoryForReviewText', ':id') }}",
        dtUrl = "{{ route('admin.hrReport.getFreeTextAnswers', ':question') }}",
        dtOptions = {
            processing: true,
            serverSide: true,
            ajax: {
                url: ''
            },
            columns: [{
                name: 'id',
                data: 'DT_RowIndex',
                orderable: false,
                searchable: false,
            },
            @if($isSA)
            {
                data: 'company_name',
                name: 'company_name',
            },
            @endif
            {
                data: 'answers',
                name: 'answers',
            }],
            pageLength: pagination.value,
            lengthChange: false,
            searching: false,
            order: [],
            autoWidth: false,
            language: {
                paginate: {
                    previous: pagination.previous,
                    next: pagination.next,
                }
            },
        },
        dtArray = [];

    $(document).ready(function() {
        $(document).on('click', '.card-header', function(e) {
            var _id = ($(this).data('qid') || 0),
                _action = $(this).parent().hasClass('collapsed-card'),
                @if($isSA)
                company = ($('#company').val() || 0),
                @else
                company = {{ ($company_id ?? 0) }},
                @endif
                now = moment(),
                fromDate = moment($('#globalFromMonth').datepicker("getDate")),
                endDate = moment($('#globalToMonth').datepicker("getDate")).endOf('month'),
                data = {
                    category: $('#category').val(),
                    getQueryString: window.location.search
                };

            if($('#globalFromMonth').val() != "" && $('#globalToMonth').val() != "") {
                if (endDate > now) {
                    endDate = now;
                }
                data.from = fromDate.format('YYYY-MM-DD 00:00:00');
                data.to = endDate.format('YYYY-MM-DD 23:59:59');
            } else {
                $('#globalFromMonth, #globalToMonth').val('');
            }
            data.company = company;
            if(_id > 0 && _action) {
                if(dtArray[_id] == undefined) {
                    var cDtOptions = dtOptions;
                    cDtOptions.ajax.url = dtUrl.replace(':question', _id);
                    cDtOptions.ajax.data = data;
                    dtArray[_id] = $(`#answers${_id}`).DataTable(cDtOptions);
                } else {
                    dtArray[_id].draw();
                }
            }
        });
    });
</script>
<script src="{{ mix('js/hr-report/freetext.js') }}" type="text/javascript">
</script>
@endsection
