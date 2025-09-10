@extends('layouts.app')

@section('after-styles')
<link href="{{ asset('assets/plugins/datepicker/datepicker3.css?var='.rand()) }}" rel="stylesheet"/>
<link href="{{ asset('assets/plugins/datatables/dataTables.bootstrap5.min.css?var='.rand()) }}" rel="stylesheet"/>
@endsection

@section('content-header')
<!-- Content Header (Page header) -->
@include('admin.cronofy.clientlist.breadcrumb', [
  'mainTitle' => trans('Cronofy.client_list.title.health_referral'),
  'breadcrumb' => Breadcrumbs::render('cronofy.clientlist.health-referral'),
  'back' => true,
])
<!-- /.content-header -->
@endsection

@section('content')
<section class="content">
    <div class="container-fluid">
        <!-- .client-details -->
        <div class="card form-card height-auto">
            <div class="card-body">
                <div class="card-inner">
                    <div class="d-sm-flex">
                        <div class="rounded-user-img me-4 mb-4 mb-sm-0">
                            <img src="{{ $client->logo }}"/>
                        </div>
                        <div class="flex-grow-1">
                            <div>
                                <h5 class="mb-1 text-primary">
                                    {{ $client->full_name }}
                                </h5>
                                <p class="mb-2">
                                    {{ $client->email }}
                                    <span class="ms-2 me-2 text-muted">
                                        |
                                    </span>
                                    {{ $dob }}
                                    <span class="ms-2 me-2 text-muted">
                                        |
                                    </span>
                                    {{ $gender }}
                                </p>
                                <p class="gray-600">
                                    <i class="far fa-building me-2">
                                    </i>
                                    {{ $clientCompany->name }}
                                </p>
                            </div>
                            <div class="align-items-center d-flex mt-4 flex-wrap">
                                <div class="flex-grow-1 flex-shrink-0 me-3 session-detail-block">
                                    <div class="row">
                                        <div class="col-md-4 col-sm-6">
                                            <span class="text-muted">
                                                {{ trans('Cronofy.client_list.details.completed') }}
                                            </span>
                                            <span class="d-block">
                                                {{ $completedCount}}
                                            </span>
                                        </div>
                                        <div class="col-md-4 col-sm-6 mt-3 mt-sm-0">
                                            <span class="text-muted">
                                                {{ trans('Cronofy.client_list.details.ongoing') }}
                                            </span>
                                            <span class="d-block">
                                                {{ $ongoingCount }}
                                            </span>
                                        </div>
                                        <div class="col-md-4 col-sm-6 mt-3 mt-md-0">
                                            <span class="text-muted">
                                                {{ trans('Cronofy.client_list.details.cancelled') }}
                                            </span>
                                            <span class="d-block">
                                                {{ $cancelledCount }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div><!-- /.client-details -->

            <!-- /.health referral form -->
            <div class="card form-card">
                {{ Form::open(['route' => ['admin.cronofy.clientlist.store-health-referral', $cronofySchedule->id], 'class' => 'form-horizontal', 'method' => 'post', 'role' => 'form', 'id' => 'healthReferral']) }}
                <div class="card-body">
                    <div class="card-inner">
                        <h3 class="card-inner-title">
                            Additional Details
                        </h3>
                        <div class="row">
                            <div class="col-lg-6 col-xl-4">
                                <div class="form-group">
                                    {{ Form::label('date', trans('Cronofy.client_list.health_referral.form.labels.date')) }}
                                    <div class="datepicker-wrap">
                                        {{ Form::text('log_date', date('Y-m-d'), ['class' => 'form-control datepicker', 'placeholder' => trans('Cronofy.client_list.health_referral.form.placeholder.date'), 'id' => 'log_date', 'readonly' => true]) }}
                                        <i class="far fa-calendar">
                                        </i>
                                    </div>
                                </div>            
                            </div>
                            <div class="col-lg-6 col-xl-4">
                                <div class="form-group">
                                    {{ Form::label('confirmation_client', trans('Cronofy.client_list.health_referral.form.labels.confirmation_client')) }}
                                    {{ Form::select('is_confirmed', array('Yes'=>'Yes', 'No'=>'No'), 'No', ['class' => 'form-control select2', 'id' => 'confirmation_client', 'data-allow-clear' => 'false']) }}
                                </div>            
                            </div>
                            <div class="col-lg-6 col-xl-4">
                                <div class="form-group">
                                    {{ Form::label('confirmation_date', trans('Cronofy.client_list.health_referral.form.labels.confirmation_date')) }}
                                    <div class="datepicker-wrap">
                                        {{ Form::text('confirmation_date' ,date('Y-m-d') , ['class' => 'form-control datepicker', 'id' => 'confirmation_date', 'readonly' => true, 'placeholder' => trans('Cronofy.client_list.health_referral.form.placeholder.confirmation_date')]) }}
                                        <i class="far fa-calendar">
                                        </i>
                                    </div>
                                </div>            
                            </div>
                            <div class="col-lg-6 col-xl-12">
                                <div class="form-group">
                                    {{ Form::label('note', trans('Cronofy.client_list.health_referral.form.labels.note')) }}
                                    {{ Form::textarea('note', null , ['class' => 'form-control', 'placeholder' => trans('Cronofy.client_list.health_referral.form.placeholder.note'), 'id' => 'note', 'autocomplete' => 'off']) }}
                                </div>            
                            </div>
                            <div class="col-lg-6 col-xl-4">
                                <div class="form-group">
                                    {{ Form::label('attend', trans('Cronofy.client_list.health_referral.form.labels.attend')) }}
                                    {{ Form::select('is_attended', array('Yes'=>'Yes', 'No'=>'No'), 'No', ['class' => 'form-control select2', 'id' => 'attend', 'data-allow-clear' => 'false']) }}
                                </div>            
                            </div>
                            <div class="col-lg-6 col-xl-4">
                                <div class="form-group">
                                    {{ Form::label('wellbeing_specialist', trans('Cronofy.client_list.health_referral.form.labels.wellbeing_specialist')) }}
                                    {{ Form::select('wellbeing_specialist_ids', $wellbeingSpecilists, null , ['class' => 'form-control select2', 'id' => 'wellbeing_specialist_ids', 'placeholder' => trans('Cronofy.client_list.health_referral.form.placeholder.wellbeing_specialist'), 'data-placeholder' => trans('Cronofy.client_list.health_referral.form.placeholder.wellbeing_specialist'), 'data-allow-clear' => 'true']) }}
                                </div>            
                            </div>
                            </div> 
                        </div>
                    </div>
                    <div class="card-footer">
                        <div class="save-cancel-wrap">
                            <a class="btn btn-outline-primary" href="{{ route('admin.cronofy.clientlist.index') }}">
                                {{ trans('buttons.general.cancel') }}
                            </a>
                            <button class="btn btn-primary" onclick="formSubmit()" type="submit">
                                {{ trans('buttons.general.save') }}
                            </button>
                        </div>
                    </div>
                {{ Form::close() }}
            </div><!-- /.health referral form -->

        </div><!-- /.container fluid --> 
</section>
@endsection
@section('after-scripts')
{!! JsValidator::formRequest('App\Http\Requests\Admin\CreateHealthReferralRequest', '#healthReferral') !!}
<!-- DataTables -->
<script src="{{ asset('assets/plugins/moment/moment.min.js?var='.rand()) }}">
</script>
<script src="{{ asset('assets/plugins/moment/moment-timezone-with-data-10-year-range.js?var='.rand()) }}">
</script>
<script src="{{ asset('assets/plugins/datepicker/bootstrap-datepicker.js?var='.rand()) }}" type="text/javascript">
</script>
<script src="{{ asset('js/external/jquery.form.min.js?var='.rand()) }}">
</script>
<script type="text/javascript">
    var timezone = `{{ $timezone }}`,
    today = new Date(),
    date_format = `{{ $date_format }}`,
    loginemail = '{{ $loginemail }}';
    pagination = {
        value: `{{ $pagination }}`,
        previous: `{!! trans('buttons.pagination.previous') !!}`,
        next: `{!! trans('buttons.pagination.next') !!}`,
    },
    messages = {!! json_encode(trans('Cronofy.client_list.details.messages')) !!};
    $(document).ready(function() {
        $('#healthReferral').validate().settings.ignore = "";
        $('#log_date, #confirmation_date').datepicker({
            todayHighlight: true,
            format: 'yyyy-mm-dd',
            autoclose: true,
        });
    });

    function formSubmit() {
        $('#healthReferral').valid();
    }
</script>
@endsection
