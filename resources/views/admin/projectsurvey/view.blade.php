@extends('layouts.app')
@section('after-styles')
<!-- DataTables -->
<link href="{{asset('assets/plugins/datatables/dataTables.bootstrap5.min.css?var='.rand())}}" rel="stylesheet"/>
@endsection
@section('content-header')
<!-- Content Header (Page header) -->
@include('admin.projectsurvey.breadcrumb',[
    'mainTitle'      => trans('customersatisfaction.projectsurvey.title.index_title')." (".$npsProject->title.")" ,
    'breadcrumb'     => 'projectsurvey.index',
    'showbackbutton' => true,
])
<!-- /.content-header -->
@endsection
@section('content')
<section class="content">
    <div class="container-fluid">
        <div class="card search-card">
            <div class="card-body pb-0">
                <h4 class="d-md-none">{{ trans('customersatisfaction.projectsurvey.title.filter') }}</h4>
                <div class="search-outer d-md-flex justify-content-between">
                    <div>
                        <div class="form-group">
                            {{ Form::select('feedBackType', $feedBackType, request()->get('feedBackType'), ['class' => 'form-control select2', 'id'=>'feedBackType', 'style'=>'width: 100%;', 'autocomplete' => 'off', 'placeholder' => trans('customersatisfaction.projectsurvey.filter.select_feedback_type'), 'data-placeholder' => trans('customersatisfaction.projectsurvey.filter.select_feedback_type'), 'data-allow-clear' => 'true']) }}
                        </div>
                    </div>
                    <div class="search-actions align-self-start">
                      <button type="button" class="me-md-4 filter-apply-btn" id="customerFeedBack">{{trans('buttons.general.apply')}}</button>
                      <a href="javascript:void(0)" id="resetcustomerFeedBack" class="filter-cancel-icon"><i class="far fa-times"></i><span class="d-md-none ms-2 ms-md-0">{{trans('buttons.general.reset')}}</span></a>
                    </div>
                </div>
            </div>
        </div>
        <div class="card">
            <!-- /.card-header -->
            <div class="card-body">
                <h3 class="card-inner-title">{{ (!empty($company))? $company->name : "" }}</h3>
                <div class="card-table-outer" id="projectActivities-wrap">
                    <div class="table-responsive">
                        <table id="challengeUserActivity" class="table custom-table">
                            <thead>
                                <tr>
                                    <th class="text-center" style="display: none">{{trans('customersatisfaction.projectsurvey.table.id')}}</th>
                                    <th>{{trans('customersatisfaction.projectsurvey.table.logo')}}</th>
                                    <th>{{trans('customersatisfaction.projectsurvey.table.feedback_type')}}</th>
                                    <th>{{trans('customersatisfaction.projectsurvey.table.notes')}}</th>
                                    <th>{{trans('customersatisfaction.projectsurvey.table.date')}}</th>
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
<script src="{{asset('assets/plugins/datatables/jquery.dataTables.min.js?var='.rand())}}">
</script>
<script src="{{asset('assets/plugins/datatables/dataTables.bootstrap5.min.js?var='.rand())}}">
</script>
<script src="{{asset('assets/plugins/moment/moment.min.js?var='.rand()) }}">
    </script>
<script src="{{asset('assets/plugins/moment/moment-timezone-with-data-10-year-range.js?var='.rand()) }}">
    </script>

<script src="{{asset('assets/plugins/datatables/jszip.min.js?var='.rand())}}">
</script>
<script type="text/javascript">
var timezone = '{{ $timezone }}',
    date_format = '{{ $date_format }}';
var url = {
    datatable: `{{ route("admin.projectsurvey.getNpsProjectUserFeedBackTableData",$npsProject->id) }}`,
},
pagination = {
    value: {{ $pagination }},
    previous: `{!! trans('buttons.pagination.previous') !!}`,
    next: `{!! trans('buttons.pagination.next') !!}`,
},
button = {
    export: '<i class="far fa-file-excel me-3 align-middle"></i> {{ trans('customersatisfaction.buttons.export_to_excel') }}',
},
message = {
    survey_link_copied: `{{ trans('customersatisfaction.message.survey_link_copied') }}`,
};
</script>
<script src="{{ mix('js/customersatisfaction/projectsurvey.js') }}" type="text/javascript">
</script>
@endsection