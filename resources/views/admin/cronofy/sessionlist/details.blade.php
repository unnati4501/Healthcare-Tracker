@extends('layouts.app')

@section('after-styles')
<link href="{{ asset('assets/plugins/datepicker/datepicker3.css?var='.rand()) }}" rel="stylesheet"/>
<link href="{{ asset('assets/plugins/datatables/dataTables.bootstrap5.min.css?var='.rand()) }}" rel="stylesheet"/>
@endsection

@section('content-header')
<!-- Content Header (Page header) -->
@include('admin.cronofy.sessionlist.breadcrumb', [
  'mainTitle' => trans('Cronofy.session_list.title.details'),
  'breadcrumb' => 'cronofy.sessions.details',
  'back' => true,
])
<!-- /.content-header -->
@endsection

@section('content')
<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="card form-card">              
            <div class="card-body">
                <div class="card-inner">
                    {{ Form::open(['route' => ['admin.cronofy.sessions.update', $id], 'class' => 'form-horizontal zevo_form_submit', 'method' => 'PATCH', 'role' => 'form', 'id' => 'sessionEdit', 'name' => 'sessionEdit', 'files' => true]) }}
                                   
                    <div class="d-sm-flex">
                        <div class="rounded-user-img me-4 mb-4 mb-sm-0">
                            <img src="{{ (!empty($logo) ? $logo['url'] : asset('assets/dist/img/placeholder-img.png')) }}"/>
                        </div>
                        <div class="flex-grow-1">
                            <div class="d-xl-flex">
                                <div class="flex-grow-1">
                                    <div>
                                        <h5 class="mb-1 text-primary">
                                            {{$user}}
                                        </h5>
                                        <p>
                                            {{$email}}
                                            <span class="ms-2 me-2 text-muted">
                                                |
                                            </span>
                                            {{$dob}} 
                                            <span class="ms-2 me-2 text-muted">
                                                |
                                            </span>
                                            {{$gender}} 
                                        </p>
                                        <p class="gray-600">
                                            <img src="{{ (!empty($company_logo) ? $company_logo['url'] : asset('assets/dist/img/placeholder-img.png')) }}" width="20" height="20"/>
                                            {{ $company->name }}
                                        </p>
                                    </div>
                                    <div class="d-xl-flex">
                                        <div class="session-detail-block mt-4 me-4 mb-4 flex-grow-1">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <span class="text-muted">
                                                        {{ trans('Cronofy.session_details.labels.booked_date') }}
                                                    </span>
                                                    <span class="d-block">
                                                        {{$booked_date}}
                                                    </span>
                                                </div>
                                                <div class="col-md-6 mt-3 mt-md-0">
                                                    <span class="text-muted">
                                                        {{ trans('Cronofy.session_details.labels.duration') }}
                                                    </span>
                                                    <span class="d-block">
                                                        {{$duration}} {{ trans('Cronofy.session_details.labels.minutes') }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        @if($role->slug == 'wellbeing_specialist')
                                            @if((isset($allowJoin) && $allowJoin == true) || ($status !='canceled' && $status !='rescheduled' && $end_time <= now()))
                                            <div class="form-group mt-4 me-4">
                                                {{ Form::label('no_show', trans('Cronofy.session_details.form.labels.no_show')) }}
                                                {{ Form::select('no_show', array('No'=>'No', 'Yes'=>'Yes'), old('no_show', ($no_show ?? 'No')), ['class' => 'form-control select2', 'id'=>'no_show', 'data-allow-clear' => 'false']) }}
                                            </div>
                                            @endif
                                            @if($status !='canceled' && $status !='rescheduled' && $end_time <= now())
                                            <div class="form-group mt-4">
                                                {{ Form::label('score', trans('Cronofy.session_details.form.labels.score')) }}
                                                {{ Form::select('score', $scoreData, $score, ['class' => 'form-control select2', 'id'=>'score', 'data-allow-clear' => 'false']) }}
                                            </div>
                                            @endif
                                        @endif
                                    </div>
                                </div>
                                <div class="session-btn-wrap">
                                    @if(isset($allowJoin) && $allowJoin == true)
                                    <a class="btn btn-primary btn-sm me-3" href="{{ $join_url }}" target="_blank">
                                        {{ trans('Cronofy.session_details.buttons.join') }}
                                    </a>
                                    @endif
                                    @if(isset($allowUpdate) && $allowUpdate == true)
                                    <a class="btn btn-primary btn-sm me-3" href="{{ route('admin.cronofy.sessions.reschedule-session', $id) }}">
                                        {{ trans('Cronofy.session_details.buttons.reschedule') }}
                                    </a>
                                    <a class="btn btn-outline-primary btn-sm" data-id="{{$id}}" href="javaScript:void(0)" id="cancelSessionModel">Cancel</a> 
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    <hr class="mt-5 mb-5">
                        <div class="row mb-4">
                            <div class="col-lg-12">
                                <h5 class="mb-3">
                                    {{ trans('Cronofy.session_list.form.labels.notes') }}
                                </h5>
                                <p>
                                    <div class="col-md-12">
                                        @include('admin.cronofy.sessionlist.form', ['edit' => true])
                                    </div>
                                    <h5>
                                        {{ trans('Cronofy.session_details.labels.user_notes') }}
                                    </h5>
                                    <div class="col-md-12">
                                        <div class="col-sm-12">
                                            <div class="form-group">
                                                {{$user_notes}}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-footer">
                                        <div class="save-cancel-wrap">
                                            <div></div>
                                            <button class="btn btn-primary" id="update_notes_btn" type="submit">
                                                {{ trans('buttons.general.update') }}
                                            </button>
                                        </div>
                                    </div>
                                </p>
                            </div>
                        </div>
                        @if($status == 'canceled')
                        <div class="session-detail-block">
                            <h6 class="mb-4">
                                {{ trans('Cronofy.session_details.labels.cancellation_details') }}
                            </h6>
                            <div class="row">
                                <div class="mb-3 col-lg-6">
                                    <span class="text-muted">
                                        {{ trans('Cronofy.session_details.labels.cancelled_by') }}
                                    </span>
                                    <span class="d-block">
                                        {{$cancelled_by}}
                                    </span>
                                </div>
                                <div class="mb-3 col-lg-6">
                                    <span class="text-muted">
                                        {{ trans('Cronofy.session_details.labels.cancelled_at') }}
                                    </span>
                                    <span class="d-block">
                                        {{$cancelled_at}}
                                    </span>
                                </div>
                                <div class="mb-2 col-lg-12">
                                    <span class="text-muted">
                                        {{ trans('Cronofy.session_details.labels.cancelled_reason') }}
                                    </span>
                                    <span class="d-block">
                                        {{$cancelled_reason}}
                                    </span>
                                </div>
                            </div>
                        </div>
                        @endif
                    </hr>
                    {{ Form::close() }}
                </div>
            </div>
        </div>

        <!-- .session-attachments -->
        @if($role->slug == 'wellbeing_specialist')
            @include('admin.cronofy.sessionlist.attachments')
        @endif
        <!-- /.session-attachments -->


    </div>
    <!-- /.container-fluid -->
</section>
<div class="modal fade" data-backdrop="static" data-bid="0" data-keyboard="false" id="cancel-session-model-box" role="dialog" tabindex="-1">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    {{ __('Cancel Session?') }}
                </h5>
                <button aria-label="Close" class="close" data-bs-dismiss="modal" type="button">
                    <i class="fal fa-times">
                    </i>
                </button>
            </div>
            <div class="modal-body">
                <p>
                    {{ __('Are you sure, you want to cancel the session?') }}
                </p>
                {{ Form::open(['action' => null, 'class' => 'form-horizontal', 'method' => 'post', 'role' => 'form', 'id' => 'cancelSessionForm']) }}
                <div class="row">
                    <div class="form-group col-12">
                        {{ Form::textarea('cancelled_reason', null, ['id' => 'cancelled_reason', 'class' => 'form-control mt-2', 'placeholder' => __('Enter reason for cancel the session'), 'rows' => 3]) }}
                    </div>
                </div>
                {{ Form::close() }}
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline-primary" data-bs-dismiss="modal" type="button">
                    {{ trans('buttons.general.no') }}
                </button>
                <button class="btn btn-primary" id="session-cancel-model-box-confirm" type="button">
                    {{ trans('buttons.general.yes') }}
                </button>
            </div>
        </div>
    </div>
</div>

@include('admin.cronofy.sessionlist.delete_modal')
@include('admin.cronofy.sessionlist.upload_modal')
@endsection
@section('after-scripts')
{!! JsValidator::formRequest('App\Http\Requests\Admin\EditSessionRequest','#sessionEdit') !!}
{!! $validator = JsValidator::formRequest('App\Http\Requests\Admin\AddBulkSessionAttachmentsRequest','#bulkUploadAttachmentFrm') !!}
<script src="{{ asset('assets/plugins/datatables/jquery.dataTables.min.js?var='.rand()) }}">
</script>
<script src="{{ asset('assets/plugins/datatables/dataTables.bootstrap5.min.js?var='.rand()) }}">
</script>
<script src="{{ asset('assets/plugins/ckeditor5/ckeditor.js?var='.rand()) }}" type="text/javascript">
</script>
<script src="{{ asset('js/external/external-ckeditor.js?var='.rand()) }}" type="text/javascript">
</script>
<script src="{{ mix('js/cronofy/sessionlist/edit.js') }}" type="text/javascript"></script>
<script src="{{ asset('js/external/jquery.form.min.js?var='.rand()) }}">
</script>
<script type="text/javascript">
    var url = {
        getAttachmentsDatatableUrl: `{{ route('admin.cronofy.sessions.get-attachments', $id) }}`,
        cancelSession: `{{route('admin.cronofy.sessions.cancel-session',':bid')}}`,
        deleteAttachment: `{{ route('admin.cronofy.sessions.delete-attachment','/') }}`,
    },
    maxImagesLimit = {{ config('zevolifesettings.session_attachment_max_upload_limit', 3) }},
    messages = {
        cancelled_success      : `{{ trans('Cronofy.session_list.messages.cancelled') }}`,
        cancel_reason_required : `{{ trans('Cronofy.session_list.validation.cancel_reason_required') }}`,
        cancelled_error        : `{{ trans('Cronofy.session_list.messages.something_wrong_try_again') }}`,
        deleted                : `{{ trans('Cronofy.session_details.attachments.messages.deleted') }}`,
        somethingWentWrong     : `{{ trans('Cronofy.session_details.attachments.messages.something_wrong_try_again') }}`,
        image_valid_error      : `{{ trans('Cronofy.session_details.attachments.messages.image_valid_error') }}`,
        image_size_5M_error    : `{{ trans('Cronofy.session_details.attachments.messages.image_size_5M_error') }}`,
        notes_length            : `{{ trans('Cronofy.session_details.messages.notes_length') }}`
    };
</script>
@endsection