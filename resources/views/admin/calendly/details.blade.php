@extends('layouts.app')

@section('after-styles')
<link href="{{ asset('assets/plugins/datepicker/datepicker3.css?var='.rand()) }}" rel="stylesheet"/>
@endsection

@section('content-header')
<!-- Content Header (Page header) -->
@include('admin.calendly.breadcrumb', [
  'mainTitle' => trans('calendly.title.details'),
  'breadcrumb' => 'calendly.details',
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
                                        </p>
                                    </div>
                                    <div class="session-detail-block mt-4 mb-4">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <span class="text-muted">
                                                    Booked Date
                                                </span>
                                                <span class="d-block">
                                                    {{$booked_date}}
                                                </span>
                                            </div>
                                            <div class="col-md-6 mt-3 mt-md-0">
                                                <span class="text-muted">
                                                    Duration
                                                </span>
                                                <span class="d-block">
                                                    {{$duration}} Minutes
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="session-btn-wrap">
                                    @if(isset($allowJoin) && $allowJoin == true)
                                    <a class="btn btn-primary btn-sm me-3" href="{{ $join_url }}" target="_blank">
                                        Join
                                    </a>
                                    @endif
                                    @if(isset($allowUpdate) && $allowUpdate == true)
                                    <a class="btn btn-primary btn-sm me-3" href="{{ $reschedule_url }}" target="_blank">
                                        Reschedule
                                    </a>
                                    <a class="btn btn-outline-primary btn-sm" href="{{ $cancel_url }}" target="_blank">
                                        Cancel
                                    </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    <hr class="mt-5 mb-5">
                        <div class="row mb-4">
                            <div class="col-lg-12">
                                <h5 class="mb-3">
                                    {{ trans('calendly.form.labels.notes') }}
                                </h5>
                                <p>
                                    {{ Form::open(['route' => ['admin.sessions.update', $id], 'class' => 'form-horizontal zevo_form_submit', 'method' => 'PATCH', 'role' => 'form', 'id' => 'sessionEdit', 'name' => 'sessionEdit', 'files' => true]) }}
                                    <div class="col-md-12">
                                        @include('admin.calendly.form', ['edit' => true])
                                    </div>
                                    <div class="card-footer">
                                        <div class="save-cancel-wrap">
                                            <button class="btn btn-primary" id="update_notes_btn" type="submit">
                                                {{ trans('buttons.general.update') }}
                                            </button>
                                        </div>
                                    </div>
                                    {{ Form::close() }}
                                </p>
                            </div>
                        </div>
                        @if($status == 'canceled')
                        <div class="session-detail-block">
                            <h6 class="mb-4">
                                Cancellation Details
                            </h6>
                            <div class="row">
                                <div class="mb-3 col-lg-6">
                                    <span class="text-muted">
                                        Cancelled By
                                    </span>
                                    <span class="d-block">
                                        {{$cancelled_by}}
                                    </span>
                                </div>
                                <div class="mb-3 col-lg-6">
                                    <span class="text-muted">
                                        Cancelled At
                                    </span>
                                    <span class="d-block">
                                        {{$cancelled_at}}
                                    </span>
                                </div>
                                <div class="mb-2 col-lg-12">
                                    <span class="text-muted">
                                        Cancelled Reason
                                    </span>
                                    <span class="d-block">
                                        {{$cancelled_reason}}
                                    </span>
                                </div>
                            </div>
                        </div>
                        @endif
                    </hr>
                </div>
            </div>
            
        </div>
    </div>
    <!-- /.container-fluid -->
</section>
@endsection

@section('after-scripts')
{!! JsValidator::formRequest('App\Http\Requests\Admin\EditSessionRequest','#sessionEdit') !!}
<script src="{{ asset('assets/plugins/ckeditor5/ckeditor.js?var='.rand()) }}" type="text/javascript">
</script>
<script src="{{ asset('js/external/external-ckeditor.js?var='.rand()) }}" type="text/javascript">
</script>
<script src="{{ mix('js/calendly/edit.js') }}" type="text/javascript"></script>
@endsection