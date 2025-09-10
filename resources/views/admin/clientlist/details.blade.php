@extends('layouts.app')

@section('after-styles')
<link href="{{ asset('assets/plugins/datepicker/datepicker3.css?var='.rand()) }}" rel="stylesheet"/>
<link href="{{ asset('assets/plugins/datatables/dataTables.bootstrap5.min.css?var='.rand()) }}" rel="stylesheet"/>
@endsection

@section('content-header')
<!-- Content Header (Page header) -->
@include('admin.clientlist.breadcrumb', [
  'mainTitle' => trans('clientlist.title.details'),
  'breadcrumb' => Breadcrumbs::render('clientlist.details'),
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
                            <div class="session-detail-block mt-4">
                                <div class="row">
                                    <div class="col-md-4 col-sm-6">
                                        <span class="text-muted">
                                            {{ trans('clientlist.details.completed') }}
                                        </span>
                                        <span class="d-block">
                                            {{ $completedCount}}
                                        </span>
                                    </div>
                                    <div class="col-md-4 col-sm-6 mt-3 mt-sm-0">
                                        <span class="text-muted">
                                            {{ trans('clientlist.details.ongoing') }}
                                        </span>
                                        <span class="d-block">
                                            {{ $ongoingCount }}
                                        </span>
                                    </div>
                                    <div class="col-md-4 col-sm-6 mt-3 mt-md-0">
                                        <span class="text-muted">
                                            {{ trans('clientlist.details.cancelled') }}
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
                                    {{ trans('clientlist.details.cm_notes') }}
                                </h3>
                            </div>
                            <div class="col-sm-7 mt-4 mt-sm-0">
                                <div class="dashboard-card-filter">
                                    <div class="datepicker-wrap mb-0">
                                        {{ Form::text('cm_notes_filter', null, ['class' => 'form-control datepicker bg-white', 'id' => 'cm_notes_filter', 'readonly' => true]) }}
                                        <i class="far fa-calendar">
                                        </i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row notes-wrap loading-cm-notes text-center" style="display: none;">
                        <div class="col-md-12">
                            <i class="fa fa-spinner fa-spin">
                            </i>
                            {{ trans('clientlist.details.messages.loading_cm_notes') }}
                        </div>
                    </div>
                    <div class="row notes-wrap no-cm-notes text-center text-muted" style="display: none;">
                        <div class="col-md-12">
                            {{ trans('clientlist.details.messages.no_cm_notes_date') }}
                        </div>
                    </div>
                    <div class="row notes-wrap cm-notes-wrap">
                        @forelse ($internalNotes as $note)
                            @include('admin.clientlist.comment-block', ['note' => $note])
                        @empty
                        <div class="col-md-12 text-center text-muted">
                            <p>
                                {{ trans('clientlist.details.messages.no_cm_notes_date') }}
                            </p>
                        </div>
                        @endforelse
                    </div>
                </div>
                <div class="card-inner">
                    <div class="pb-3 border-bottom mb-4 ">
                        <div class="row ">
                            <div class="col-sm-5 align-self-center">
                                <h3 class="card-inner-title border-0 pb-0 mb-0">
                                    {{ trans('clientlist.details.notes') }}
                                </h3>
                            </div>
                            <div class="col-sm-7 mt-4 mt-sm-0 text-end">
                                <a class="btn btn-outline-primary" data-bs-target="#add-note-model" data-bs-toggle="modal" href="javascript:void(0);">
                                    {{ trans('clientlist.details.add_note') }}
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="row notes-wrap">
                        @forelse ($notes as $note)
                            @include('admin.clientlist.comment-block', ['note' => $note, 'ticketId'=> $ticket->id])
                        @empty
                        
                        <div class="col-md-12 text-center text-muted">
                            <p>
                                {{ trans('clientlist.details.messages.no_comments') }}
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
                                    {{ trans('clientlist.details.session_notes') }}
                                </h3>
                            </div>
                        </div>
                    </div>
                    
                    </div>
                    <div class="row notes-wrap">
                        @forelse ($sessionNotes as $note)
                            @include('admin.clientlist.session-comment-block', ['note' => $note, 'ticketId'=> $ticket->id])
                        @empty
                        
                        <div class="col-md-12 text-center text-muted">
                            <p>
                                {{ trans('clientlist.details.messages.no_comments') }}
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
                    {{ Form::open(['route' => ['admin.clientlist.details', $ticket->id], 'class' => 'form-horizontal', 'method' => 'get', 'role' => 'form', 'id' => 'userSearch']) }}
                    <div class="search-outer d-md-flex justify-content-between">
                        <div>
                            <div class="form-group">
                                {{ Form::text('session_name', request()->get('session_name'), ['class' => 'form-control', 'placeholder' => trans('clientlist.details.filters.session_name'), 'id' => 'session_name', 'autocomplete' => 'off']) }}
                            </div>
                            <div class="form-group">
                                {{ Form::select('session_status', $sessionStatus, request()->get('session_status'), ['class' => 'form-control select2', 'id' => 'session_status', 'placeholder' => trans('clientlist.details.filters.session_status'), 'data-placeholder' => trans('clientlist.details.filters.session_status'), 'data-allow-clear' => 'true'] ) }}
                            </div>
                        </div>
                        <div class="search-actions align-self-start">
                            <button class="me-md-4 filter-apply-btn" type="submit">
                                {{ trans('buttons.general.apply') }}
                            </button>
                            <a class="filter-cancel-icon" href="{{ route('admin.clientlist.details', $ticket->id) }}">
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
                                        {{ trans('clientlist.details.table.session_name') }}
                                    </th>
                                    <th>
                                        {{ trans('clientlist.details.table.duration_min') }}
                                    </th>
                                    <th>
                                        {{ trans('clientlist.details.table.status') }}
                                    </th>
                                    <th class="no-sort th-btn-2">
                                        {{ trans('clientlist.details.table.view') }}
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
    </div>
</section>
<!-- reason-model-box -->
<div class="modal fade" id="reason-model-box" role="dialog" tabindex="-1">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    {{ trans('clientlist.details.modal.cancel.title') }}
                </h5>
                <button aria-label="Close" class="close" data-bs-dismiss="modal" type="button">
                    <i class="fal fa-times">
                    </i>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="form-group col-12">
                        {{ Form::label('', trans('clientlist.details.modal.cancel.fields.cancelled_by'), ['class' => 'fw-bold']) }}:
                        {{ Form::label('', trans('clientlist.details.modal.cancel.fields.cancelled_by'), ['class' => 'f-400', 'id' => 'cancelled_by']) }}
                    </div>
                    <div class="form-group col-12">
                        {{ Form::label('', trans('clientlist.details.modal.cancel.fields.cancelled_at'), ['class' => 'fw-bold']) }}:
                        {{ Form::label('', trans('clientlist.details.modal.cancel.fields.cancelled_at'), ['class' => 'f-400', 'id' => 'cancelled_at']) }}
                    </div>
                    <div class="form-group col-12">
                        {{ Form::label('', trans('clientlist.details.modal.cancel.fields.cancelled_reason'), ['class' => 'fw-bold']) }}:
                        {{ Form::label('', trans('clientlist.details.modal.cancel.fields.cancelled_reason'), ['class' => 'f-400', 'id' => 'cancelation_reason']) }}
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
            {{ Form::open(['route' => ['admin.clientlist.add-note', $ticket->id], 'class' => 'form-horizontal', 'method' => 'POST', 'role' => 'form', 'id' => 'addNoteForm']) }}
            <div class="modal-body">
                <div class="row">
                    <div class="form-group col-12">
                        {{-- {{ Form::textarea('note', null, ['id' => 'note', 'class' => 'form-control mt-2', 'placeholder' => __('Enter note here'), 'rows' => 3]) }} --}}
                        {{ Form::textarea('note', null, ['id' => 'note', 'cols' => 10, 'class' => 'form-control h-auto basic-format-ckeditor', 'placeholder' => 'Enter notes', 'data-errplaceholder' => '#notes-error']) }}
                        {{-- <textarea name="note" class="form-control h-auto basic-format-ckeditor" id="note" rows="10" cols="80" data-errplaceholder = "#notes-error"></textarea> --}}
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
            {{ Form::open(['route' => ['admin.clientlist.edit-note'], 'class' => 'form-horizontal', 'method' => 'PATCH', 'role' => 'form', 'id' => 'editNoteForm']) }}
            {{ Form::hidden('clientId',null, ['id'=>'clientId'])}}
            {{ Form::hidden('commentId',null, ['id'=>'commentId'])}}
            {{ Form::hidden('noteFrom',null, ['id'=>'noteFrom'])}}
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
@include('admin.clientlist.delete-model')
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
<script type="text/javascript">
    var url = {
        datatable: `{{ route('admin.clientlist.client-sessions', [$ticket->id, $client->id]) }}`,
        notes: `{{ route('admin.clientlist.notes', [$ticket->id, 'internal_note']) }}`,
        getClientNote: `{{ route('admin.clientlist.get-client-note') }}`,
        deleteClientNote: `{{ route('admin.clientlist.delete', ':id') }}`,
        deleteSessionNote: `{{ route('admin.clientlist.delete-session-notes', ':id') }}`, 
    },
    timezone = `{{ $timezone }}`,
    date_format = `{{ $date_format }}`,
    pagination = {
        value: {{ $pagination }},
        previous: `{!! trans('buttons.pagination.previous') !!}`,
        next: `{!! trans('buttons.pagination.next') !!}`,
    },
    messages = {!! json_encode(trans('clientlist.details.messages')) !!};

</script>
<script src="{{ mix('js/clientlist/details.js') }}">
</script>
@endsection
