@extends('layouts.app')

@section('content-header')
    <!-- Content Header (Page header) -->
    @include('admin.report.realtime-wbs-availability.breadcrumb', [
        'mainTitle' => trans('realtime_wbs_availability.title.index_title'),
        'breadcrumb' => 'realtime-availability.index',
        'back' => false,
    ])
    <!-- /.content-header -->
@endsection
@section('content')
    <section class="content">
        <div class="container-fluid">
            <div class="card form-card">
                <!-- /.card-header -->
                {{ Form::open(['route' => 'admin.reports.generate-realtime-availability', 'class' => 'form-horizontal zevo_form_submit', 'method' => 'post', 'role' => 'form', 'id' => 'realtimeavailabilityAdd', 'files' => true]) }}
                <div class="card-body">
                    <div class="card-inner">
                        <div class="row">
                            <div class="col-lg-12 col-xl-6">
                                <div class="form-group">
                                    <label>{{ trans('realtime_wbs_availability.form.labels.company') }}</label>
                                    {{ Form::select('company', $companies, request()->get('company'), ['class' => 'form-control select2', 'id' => 'companyid', 'placeholder' => trans('realtime_wbs_availability.form.placeholder.select_company'), 'data-placeholder' => trans('realtime_wbs_availability.form.placeholder.select_company'), 'autocomplete' => 'off', 'data-allow-clear' => 'true', 'target-data' => 'team']) }}
                                </div>
                            </div>
                            <div class="col-lg-12 col-xl-6 location_box d-none">
                                <div class="form-group">
                                    <label>{{ trans('realtime_wbs_availability.form.labels.location') }}</label>
                                    {{ Form::select('location', [], request()->get('location'), ['class' => 'form-control select2', 'id' => 'locationid', 'placeholder' => trans('realtime_wbs_availability.form.placeholder.select_location'), 'data-placeholder' => trans('realtime_wbs_availability.form.placeholder.select_location'), 'autocomplete' => 'off', 'data-allow-clear' => 'true', 'target-data' => 'team']) }}
                                </div>
                            </div>
                            <div class="col-lg-12 col-xl-6">
                                <div class="form-group">
                                    <label>{{ trans('realtime_wbs_availability.form.labels.email') }}</label>
                                    <span class="font-16 qus-sign-tooltip" data-toggle="help-tooltip" title="Please enter the email address where you would like to receive the report">
                                        <i aria-hidden="true" class="far fa-info-circle text-primary">
                                        </i>
                                    </span>
                                    {{ Form::text('email', $loginEmail ?? request()->get('email'), ['id' => 'dtFromdate', 'class' => 'form-control', 'placeholder' => trans('realtime_wbs_availability.form.placeholder.email_address')]) }}
                                </div>
                            </div>
                            <div class='col-lg-12 col-xl-6'>
                                <div class="form-group">
                                    {{ Form::label('Wellbeing Specialists', trans('realtime_wbs_availability.form.labels.wellbeing_specialist')) }}
                                    {{ Form::select('wellbeing_specialist[]', [], null, ['class' => 'form-control select2', 'id' => 'wellbeing_specialist', 'style' => 'width: 100%;', 'multiple' => true, 'autocomplete' => 'off', 'data-placeholder' => trans('realtime_wbs_availability.form.placeholder.wellbeing_specialist')]) }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /.card-body -->
                <div class="card-footer">
                    <div class="save-cancel-wrap">
                        <a class="btn btn-outline-primary"
                            href="{{ url(app()->getLocale().'/admin/reports/realtime-availability') }}">{{ trans('buttons.general.clear') }}</a>
                        <button type="submit" class="btn btn-primary"
                            id="zevo_submit_btn">{{ trans('buttons.general.Send') }}</button>
                    </div>
                </div>
                {{ Form::close() }}
                <!-- /.card-footer-->
            </div>
        </div>
    </section>
@endsection
@section('after-scripts')
    {!! JsValidator::formRequest('App\Http\Requests\Admin\RealtimeAvailabilityRequest', '#realtimeavailabilityAdd') !!}
    <script src="{{ asset('js/external/jquery.form.min.js?var='.rand()) }}" type="text/javascript"></script>
    <script type="text/javascript">
        var urls = {
                index: `{{ route('admin.reports.realtime-availability') }}`,
                locUrl: '{{ route('admin.reports.getLocationList', ':id') }}',
                wellbeingUrl: '{{ route('admin.reports.getWellbeingSpecialist', ':id') }}',
                wbsLocationUrl: '{{ route('admin.reports.getWellbeingSpecialistLocation', [':id', ':location']) }}'
            },
            message = {
                select_wellbeing_specialist: `{{ trans('realtime_wbs_availability.form.placeholder.wellbeing_specialist') }}`,
                generate_data: `{{ trans('realtime_wbs_availability.messages.generate_data') }}`,
                something_wrong_try_again: `{{ trans('feed.message.something_wrong_try_again') }}`
            };
    </script>
    <script src="{{ asset('js/wellbeingspecialistavailability/index.js') }}" type="text/javascript"></script>
@endsection
