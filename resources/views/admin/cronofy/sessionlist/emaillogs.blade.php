@extends('layouts.app')

@section('after-styles')
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
                    {{ Form::open(['route' => ['admin.cronofy.sessions.send-session-email', $id], 'class' => 'form-horizontal zevo_form_submit', 'method' => 'PATCH', 'role' => 'form', 'id' => 'sessionEmailEdit', 'name' => 'sessionEmailEdit', 'files' => true]) }}
                                   
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
                                        <div class="form-group mt-4">
                                            {{ Form::label('reason', trans('Cronofy.session_details.form.labels.reason')) }}
                                            {{ Form::select('reason', $reasons, old('reason', ($no_show ?? 'No')), ['class' => 'form-control select2', 'id'=>'reason', 'data-allow-clear' => 'false']) }}
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @if($role->slug == 'wellbeing_specialist')
                    <div class="col-lg-12 mt-5">
                        <h5 class="mb-3 ms-4">
                            {{ trans('Cronofy.session_details.form.labels.email_body') }}
                        </h5>
                        <p>
                            <div class="col-md-12">
                                <div class="col-sm-12">
                                    <div class="form-group">
                                        {{ Form::textarea('email_message', old('email_message', (!empty($email_message) ? htmlspecialchars_decode($email_message) : null)), ['id' => 'email_message', 'cols' => 10, 'class' => 'form-control h-auto basic-format-ckeditor', 'placeholder' => trans('Cronofy.session_details.form.placeholder.enter_message'), 'data-errplaceholder' => '#email_message-error']) }}
                                        <div id="email_message-error" style="display: none; width: 100%; margin-top: 0.25rem; font-size: 80%; color: #dc3545;">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer">
                                <div class="save-cancel-wrap">
                                    <a class="btn btn-outline-primary" href="{{ route('admin.cronofy.sessions.index') }}">
                                        {{ trans('labels.buttons.cancel') }}
                                    </a>
                                    <button class="btn btn-primary" id="update_notes_btn" type="submit">
                                        {{ trans('buttons.general.send') }}
                                    </button>
                                </div>
                            </div>
                        </p>
                    </div>
                    @endif
                    {{ Form::close() }}
                </div>
                @if($role->slug == 'super_admin')
                <div class="card-table-outer" id="sessionEmailLogs-wrap">
                    <h4 class="mb-3 ms-4">
                        {{ trans('Cronofy.session_details.form.labels.email_logs') }}
                    </h4>
                    <div class="table-responsive">
                        <table class="table custom-table" id="sessionEmailLogs">
                            <thead>
                                <tr>
                                    <th class="text-center">
                                        {{ trans('Cronofy.session_details.labels.srno') }}
                                    </th>
                                    <th class="text-center">
                                        {{ trans('Cronofy.session_details.labels.datetime') }}
                                    </th>
                                    <th class="text-center">
                                        {{ trans('Cronofy.session_details.labels.reason') }}
                                    </th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
                @endif
            </div>
            
        </div>
    </div>
    <!-- /.container-fluid -->
</section>
@endsection

@section('after-scripts')
{!! JsValidator::formRequest('App\Http\Requests\Admin\EditSessionRequest','#sessionEmailEdit') !!}
<script src="{{ asset('assets/plugins/ckeditor5/ckeditor.js?var='.rand()) }}" type="text/javascript">
</script>
<script src="{{ asset('js/external/external-ckeditor.js?var='.rand()) }}" type="text/javascript">
</script>
<script src="{{ asset('assets/plugins/datatables/jquery.dataTables.min.js?var='.rand()) }}">
</script>
<script src="{{ asset('assets/plugins/datatables/dataTables.bootstrap5.min.js?var='.rand()) }}">
</script>
<script src="{{ mix('js/cronofy/sessionlist/emaillogs.js') }}" type="text/javascript"></script>
<script type="text/javascript">
    var url = {
        datatable: `{{ route('admin.cronofy.sessions.email-log-list', $cronofySchedule->id) }}`,
    },
    roleSlug = `{{$role->slug}}`,
    messages = {
        email_body_required      : `{{ trans('Cronofy.session_details.validation.email_body_required') }}`,
        email_body_lengh : `{{ trans('Cronofy.session_details.validation.email_body_lengh') }}`,
    };
</script>
@endsection