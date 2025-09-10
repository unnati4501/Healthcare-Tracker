@extends('layouts.app')

@section('after-styles')
<link href="{{ asset('assets/plugins/datepicker/datepicker3.css?var='.rand()) }}" rel="stylesheet"/>
<link href="{{ asset('assets/plugins/datatables/dataTables.bootstrap5.min.css?var='.rand()) }}" rel="stylesheet"/>
@endsection

@section('content-header')
<!-- Content Header (Page header) -->
@include('admin.cronofy.clientlist.breadcrumb', [
  'mainTitle' => trans('Cronofy.client_list.title.details'),
  'breadcrumb' => Breadcrumbs::render('cronofy.clientlist.details'),
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
                            <div class="">
                                @if($isConsent == true)
                                    @if($isConsentFormSent == true)
                                        @if($nextToKinInfo)
                                        <a class=" btn btn-outline-primary next-kin-info-btn" data-bs-toggle="modal" href="javascript:void(0);">
                                            {{ trans('Cronofy.client_list.details.access_next_to_kin') }}
                                        </a>
                                        @endif
                                        <div class="badge-label badge-green"><span>{{ trans('Cronofy.client_list.details.consent_received') }}</span> <i class="fal fa-check"></i></div>
                                        
                                    @else
                                        <div class="badge-label badge-red"><span>{{ trans('Cronofy.client_list.details.consent_not_received') }}</span> <i class="fal fa-times"></i></div>
                                        <a data-bs-target="#send-consent-model" data-bs-toggle="modal" href="javascript:void(0);" style="border-radius: 10px;">
                                            {{ trans('Cronofy.client_list.details.notify_client') }}
                                        </a>
                                    @endif
                                @endif
                            </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- /.client-details -->
        <!-- .notes -->
        <div class="card form-card height-auto">
            <div class="card-body">
                <div class="card-inner">
                    <div class="pb-3 border-bottom mb-4 ">
                        <div class="row ">
                            <div class="col-sm-5 align-self-center">
                                <h3 class="card-inner-title border-0 pb-0 mb-0">
                                    {{ trans('Cronofy.client_list.details.wellbeing_specialist') }}
                                </h3>
                            </div>
                            <div class="col-sm-7 mt-4 mt-sm-0 text-end">
                                @if($role->slug == 'wellbeing_team_lead')
                                <a class="btn btn-outline-primary me-2 export-notes" id="exportWsNotes" data-type="wsNotes" data-title="{{trans('Cronofy.client_list.details.modal.export.ws_notes')}}" data-bs-toggle="modal" href="javascript:void(0);">
                                    <span>
                                        <i class="far fa-envelope me-3 align-middle">
                                        </i>
                                        {{trans('buttons.general.export')}}
                                    </span>
                                </a>
                                @endif
                                <a class="btn btn-outline-primary" data-bs-target="#add-note-model" data-bs-toggle="modal" href="javascript:void(0);">
                                    {{ trans('Cronofy.client_list.details.add_note') }}
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="row notes-wrap">
                        @forelse ($notes as $note)
                            @include('admin.cronofy.clientlist.comment-block', ['note' => $note, 'scheduleId'=> $cronofySchedule->id])
                        @empty
                        
                        <div class="col-md-12 text-center text-muted">
                            <p>
                                {{ trans('Cronofy.client_list.details.messages.no_comments') }}
                            </p>
                        </div>
                        @endforelse
                        @if($notes->hasPages())
                        <section class="col-lg-12">
                            {{ $notes->appends($queryString)->links('custom.pagination') }}
                        </section>
                        @endif
                    </div>
                </div>

                <div class="card-inner">
                    <div class="pb-3 border-bottom mb-4 ">
                        <div class="row ">
                            <div class="col-sm-5 align-self-center">
                                <h3 class="card-inner-title border-0 pb-0 mb-0">
                                    {{ trans('Cronofy.client_list.details.user_notes') }}
                                </h3>
                            </div>
                            @if($role->slug == 'wellbeing_team_lead')
                            <div class="col-sm-7 mt-4 mt-sm-0 text-end">
                                <a class="btn btn-outline-primary me-2 export-notes" id="exportUserNotes" data-type="userNotes" data-title="{{trans('Cronofy.client_list.details.modal.export.user_notes')}}" data-bs-toggle="modal" href="javascript:void(0);">
                                    <span>
                                        <i class="far fa-envelope me-3 align-middle">
                                        </i>
                                        {{trans('buttons.general.export')}}
                                    </span>
                                </a>
                            </div>
                            @endif
                        </div>
                    </div>
                    
                    </div>
                    <div class="row notes-wrap">
                        @forelse ($userNotes as $note)
                            @include('admin.cronofy.clientlist.user-notes-comment-block', ['note' => $note, 'scheduleId'=> $cronofySchedule->id])
                        @empty
                        
                        <div class="col-md-12 text-center text-muted">
                            <p>
                                {{ trans('Cronofy.client_list.details.messages.no_comments') }}
                            </p>
                        </div>
                        @endforelse
                        @if($userNotes->hasPages())
                        <section class="col-lg-12">
                            {{ $userNotes->appends($queryString)->links('custom.pagination') }}
                        </section>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        <!-- /.notes -->
        <!-- .client-sessions -->
        <div class="card ">
            <!-- search-block -->
            <div class="card search-card">
                <div class="card-body pb-0">
                    <h4 class="d-md-none">
                        {{ trans('buttons.general.filter') }}
                    </h4>
                    {{ Form::open(['route' => ['admin.cronofy.clientlist.details', $cronofySchedule->id], 'class' => 'form-horizontal', 'method' => 'get', 'role' => 'form', 'id' => 'userSearch']) }}
                    <div class="search-outer d-md-flex justify-content-between">
                        <div>
                            <div class="form-group">
                                {{ Form::text('session_name', request()->get('session_name'), ['class' => 'form-control', 'placeholder' => trans('Cronofy.session_list.table.name'), 'id' => 'session_name', 'autocomplete' => 'off']) }}
                            </div>
                            <div class="form-group">
                                {{ Form::select('session_status', $sessionStatus, request()->get('session_status'), ['class' => 'form-control select2', 'id' => 'session_status', 'placeholder' => trans('Cronofy.client_list.details.filters.session_status'), 'data-placeholder' => trans('Cronofy.client_list.details.filters.session_status'), 'data-allow-clear' => 'true'] ) }}
                            </div>
                        </div>
                        <div class="search-actions align-self-start">
                            <button class="me-md-4 filter-apply-btn" type="submit">
                                {{ trans('buttons.general.apply') }}
                            </button>
                            <a class="filter-cancel-icon" href="{{ route('admin.cronofy.clientlist.details', $cronofySchedule->id) }}">
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
            <div class="card-body">
                <div class="card-table-outer">
                    <div class="table-responsive">
                        <table class="table custom-table" id="clientSessionsManagement">
                            <thead>
                                <tr>
                                    <th>
                                        {{ trans('Cronofy.session_list.table.name') }}
                                    </th>
                                    <th>
                                        {{ trans('Cronofy.session_list.table.duration') }}
                                    </th>
                                    <th>
                                        {{ trans('Cronofy.session_list.table.status') }}
                                    </th>
                                    <th class="no-sort th-btn-2">
                                        {{ trans('Cronofy.session_list.table.view') }}
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
        <!-- /.client-sessions -->

        <!-- .session-attachments -->
        @include('admin.cronofy.sessionlist.attachments')
        <!-- /.session-attachments -->
    </div>
</section>
<!-- reason-model-box -->
<div class="modal fade" id="reason-model-box" role="dialog" tabindex="-1">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    {{ trans('Cronofy.client_list.details.modal.cancel.title') }}
                </h5>
                <button aria-label="Close" class="close" data-bs-dismiss="modal" type="button">
                    <i class="fal fa-times">
                    </i>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="form-group col-12">
                        {{ Form::label('', trans('Cronofy.client_list.details.modal.cancel.fields.cancelled_by'), ['class' => 'fw-bold']) }}:
                        {{ Form::label('', trans('Cronofy.client_list.details.modal.cancel.fields.cancelled_by'), ['class' => 'f-400', 'id' => 'cancelled_by']) }}
                    </div>
                    <div class="form-group col-12">
                        {{ Form::label('', trans('Cronofy.client_list.details.modal.cancel.fields.cancelled_at'), ['class' => 'fw-bold']) }}:
                        {{ Form::label('', trans('Cronofy.client_list.details.modal.cancel.fields.cancelled_at'), ['class' => 'f-400', 'id' => 'cancelled_at']) }}
                    </div>
                    <div class="form-group col-12">
                        {{ Form::label('', trans('Cronofy.client_list.details.modal.cancel.fields.cancelled_reason'), ['class' => 'fw-bold']) }}:
                        {{ Form::label('', trans('Cronofy.client_list.details.modal.cancel.fields.cancelled_reason'), ['class' => 'f-400', 'id' => 'cancelation_reason']) }}
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline-primary" data-bs-dismiss="modal" type="button">
                    {{ trans('buttons.general.close') }}
                </button>
            </div>
        </div>
    </div>
</div>
<!-- /.reason-model-box -->
<!-- reason-model-box -->
<div class="modal fade" data-focus="false" data-backdrop="static" data-bid="0" id="add-note-model" role="dialog" tabindex="-1">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    {{ __('Add Note') }}
                </h5>
                <button aria-label="Close" class="close" data-bs-dismiss="modal" type="button">
                    <i class="fal fa-times">
                    </i>
                </button>
            </div>
            {{ Form::open(['route' => ['admin.cronofy.clientlist.add-note', $cronofySchedule->id], 'class' => 'form-horizontal', 'method' => 'POST', 'role' => 'form', 'id' => 'addNoteForm']) }}
            <div class="modal-body">
                <div class="row">
                    <div class="form-group col-12">
                        {{-- {{ Form::textarea('note', null, ['id' => 'note', 'class' => 'form-control mt-2', 'placeholder' => __('Enter note here'), 'rows' => 3]) }} --}}
                        {{ Form::textarea('note', null, ['id' => 'note', 'cols' => 10, 'class' => 'form-control h-auto notes-add-ckeditor', 'placeholder' => 'Enter notes', 'data-errplaceholder' => '#notes-error']) }}
                        <div id="notes-error" style="display: none; width: 100%; font-size: 80%; color: #dc3545;">
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline-primary" data-bs-dismiss="modal" type="button">
                    {{ trans('buttons.general.cancel') }}
                </button>
                <button class="btn btn-primary" id="add_notes_btn" type="submit">
                    {{ trans('buttons.general.save') }}
                </button>
            </div>
            {{ Form::close() }}
        </div>
    </div>
</div>
<!-- /.reason-model-box -->
<!-- Email Consent Form -->
<div class="modal fade" data-focus="false" data-backdrop="static" data-bid="0" id="send-consent-model" role="dialog" tabindex="-1">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    {{ __('Notify Client') }}
                </h5>
                <button aria-label="Close" class="close" data-bs-dismiss="modal" type="button">
                    <i class="fal fa-times">
                    </i>
                </button>
            </div>
            {{ Form::open(['route' => ['admin.cronofy.clientlist.send-consent', $cronofySchedule->id], 'class' => 'form-horizontal', 'method' => 'POST', 'role' => 'form', 'id' => 'sendConsentForm']) }}
            <div class="modal-body">
                <strong>{{ $client->full_name }}</strong> will receive a notification to fill the consent form
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline-primary" data-bs-dismiss="modal" type="button">
                    {{ trans('buttons.general.cancel') }}
                </button>
                <button class="btn btn-primary" id="send_email_consent" type="submit">
                    {{ trans('buttons.general.send') }}
                </button>
            </div>
            {{ Form::close() }}
        </div>
    </div>
</div>
<!-- /.Email Consent Form -->
<!-- reason-edit-model-box -->
<div class="modal fade" data-focus="false" data-backdrop="static" data-bid="0" id="edit-note-model" role="dialog" tabindex="-1">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    {{ __('Edit Note') }}
                </h5>
                <button aria-label="Close" class="close" data-bs-dismiss="modal" type="button">
                    <i class="fal fa-times">
                    </i>
                </button>
            </div>
            {{ Form::open(['route' => ['admin.cronofy.clientlist.edit-note'], 'class' => 'form-horizontal', 'method' => 'PATCH', 'role' => 'form', 'id' => 'editNoteForm']) }}
            {{ Form::hidden('clientId',null, ['id'=>'clientId'])}}
            {{ Form::hidden('commentId',null, ['id'=>'commentId'])}}
            {{ Form::hidden('noteFrom',null, ['id'=>'noteFrom'])}}
            {{ Form::hidden('noteFromTable',null, ['id'=>'noteFromTable'])}}
            <div class="modal-body">
                <div class="row">
                    <div class="form-group col-12">
                        {{ Form::textarea('notes', null, ['id' => 'notes', 'cols' => 10, 'class' => 'form-control h-auto notes-ckeditor', 'placeholder' => 'Enter notes', 'data-errplaceholder' => '#notes-error']) }}
                        <div id="edit-notes-error" style="display: none; width: 100%; font-size: 80%; color: #dc3545;">
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline-primary" data-bs-dismiss="modal" type="button">
                    {{ trans('buttons.general.cancel') }}
                </button>
                <button class="btn btn-primary" id="edit_notes_btn" type="submit">
                    {{ trans('buttons.general.save') }}
                </button>
            </div>
            {{ Form::close() }}
        </div>
    </div>
</div>
<!-- /.reason-edit-model-box -->
<!-- Delete Model Popup -->
@include('admin.cronofy.clientlist.delete-model')
<!-- Export Model Popup -->
@include('admin.cronofy.clientlist.export-model')


<!-- Kin info access confirmation -->
<div class="modal fade" data-focus="false" data-backdrop="static" data-bid="0" id="access-kin-info-modal" role="dialog" tabindex="-1">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    {{ __('Want to access Next of Kin info ?') }}
                </h5>
                <button aria-label="Close" class="close" data-bs-dismiss="modal" type="button">
                    <i class="fal fa-times">
                    </i>
                </button>
            </div>
             <div class="modal-body">
                Please note this information should only be accessed in case of emergency. An email notification will be sent to the Clinical Lead when you access this information.
                <input type = "hidden" id="user_id" value = "{{$client->id}}">
                <input type = "hidden" id="wellbeing_specialist_id" value = "{{$cronofySchedule->ws_id}}">
            </div>
            <div class="modal-footer">
                <div></div>
                <button class="btn btn-primary" id="send_kin_access_email" type="button">
                    {{ __('I want to proceed') }}
                </button>
            </div>
        </div>
    </div>
</div>
<!-- /.Kin info access confirmation -->

<!-- Kin info details -->
<div class="modal fade" data-focus="false" data-backdrop="static" data-bid="0" id="kin-info-details-modal" role="dialog" tabindex="-1">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    {{ __('Next of Kin information') }}
                </h5>
                <button aria-label="Close" class="close" data-bs-dismiss="modal" type="button">
                    <i class="fal fa-times">
                    </i>
                </button>
            </div>
            <div class="modal-body" id="kin-details">
                <table>
                    <tr>
                        <td>Client Name : </td>
                        <td>{{$client->full_name}}</td>
                    </tr>
                </table>
                <br/>
                <strong>Kin Details</strong>
                <table class="table-borderless w-100 table-sm">
                    @if($nextToKinInfo)
                    <tr>
                        <td>Name : </td>
                        <td>{{$nextToKinInfo['fullname']}}</td>
                    </tr>
                    <tr>
                        <td>Contact Number : </td>
                        <td>{{$nextToKinInfo['contact_no']}}</td>
                    </tr>
                    <tr>
                        <td>Relation : </td>
                        <td>{{$nextToKinInfo['relation']}}</td>
                    </tr>
                    @endif
                </table>
            </div>
            <div class="modal-footer text-center">
                <div></div>
                <button class="btn btn-primary" id="send_email_consent" onclick="selectElementContents( document.getElementById('kin-details') );" type="button">
                    {{ __('Copy Details') }}
                </button>
            </div>
        </div>
    </div>
</div>
<!-- /.Kin info access details -->
@endsection
@section('after-scripts')
{!! JsValidator::formRequest('App\Http\Requests\Admin\AddCounsellorNotesRequest', '#addNoteForm') !!}
<!-- DataTables -->
<script src="{{ asset('assets/plugins/datatables/jquery.dataTables.min.js?var='.rand()) }}">
</script>
<script src="{{ asset('assets/plugins/datatables/dataTables.bootstrap5.min.js?var='.rand()) }}">
</script>
<script src="{{ asset('assets/plugins/moment/moment.min.js?var='.rand()) }}">
</script>
<script src="{{ asset('assets/plugins/moment/moment-timezone-with-data-10-year-range.js?var='.rand()) }}">
</script>
<script src="{{ asset('assets/plugins/datepicker/bootstrap-datepicker.js?var='.rand()) }}" type="text/javascript">
</script>
<script src="{{ asset('assets/plugins/ckeditor5/ckeditor.js?var='.rand()) }}" type="text/javascript">
</script>
<script src="{{ asset('js/external/external-ckeditor.js?var='.rand()) }}" type="text/javascript">
</script>
<script src="{{ asset('js/external/jquery.form.min.js?var='.rand()) }}">
</script>
<script type="text/javascript">
    var url = {
        datatable: `{{ route('admin.cronofy.clientlist.client-sessions', [$cronofySchedule->id, $client->id]) }}`,
        notes: `{{ route('admin.cronofy.clientlist.notes', $cronofySchedule->id) }}`,
        getClientNote: `{{ route('admin.cronofy.clientlist.get-client-note') }}`,
        deleteClientNote: `{{ route('admin.cronofy.clientlist.delete', ':id') }}`,
        deleteSessionNote: `{{ route('admin.cronofy.clientlist.delete-session-notes', ':id') }}`, 
        notesExportUrl:`{{route('admin.cronofy.clientlist.exportNotes', $cronofySchedule->id)}}`,
        getAttachmentsDatatableUrl: `{{ route('admin.cronofy.clientlist.get-attachments', $cronofySchedule->id) }}`,
        sendEmailForAccessKinInfo: `{{ route('admin.cronofy.clientlist.send-email-for-accss-kin-info') }}`,
        clientDetais: `{{ route('admin.cronofy.clientlist.details', [$cronofySchedule->id]) }}`,
    },
    timezone = `{{ $timezone }}`,
    date_format = `{{ $date_format }}`,
    is_kin_accessed =  `{{ $is_kin_accessed ?? 0 }}`,
    loginemail = '{{ $loginemail }}';
    pagination = {
        value: `{{ $pagination }}`,
        previous: `{!! trans('buttons.pagination.previous') !!}`,
        next: `{!! trans('buttons.pagination.next') !!}`,
        attachmentesPerPage : `{{ $clientAttachmentPerPageValue }}`,
    },
    notes_length = `{{ trans('Cronofy.session_details.messages.notes_length') }}`,
    messages = {!! json_encode(trans('Cronofy.client_list.details.messages')) !!};

    // function to copy next to kin details
    function selectElementContents(el) {
        var body = document.body,
        range, sel;
        if (document.createRange && window.getSelection) {
            range = document.createRange();
            sel = window.getSelection();
            sel.removeAllRanges();
            range.selectNodeContents(el);
            sel.addRange(range);
        }
        document.execCommand("Copy");
    }
</script>
<script src="{{ mix('js/cronofy/clientlist/details.js') }}">
</script>
@endsection
