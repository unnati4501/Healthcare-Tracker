@extends('layouts.app')

@section('after-styles')
<link href="{{ asset('assets/plugins/tree-multiselect/tree-multiselect.css?var='.rand()) }}" rel="stylesheet"/>
<link href="{{ asset('assets/plugins/OwlCarousel2/owl.carousel.min.css?var='.rand()) }}" rel="stylesheet"/>
<link href="{{ asset('assets/plugins/OwlCarousel2/owl.theme.default.min.css?var='.rand()) }}" rel="stylesheet"/>
@endsection
@section('content-header')
<!-- Content Header (Page header) -->
@include('admin.cronofy.groupsession.breadcrumb', [
  'mainTitle' => trans('calendly.title.add_session'),
  'breadcrumb' => 'cronofy.groupsession.addsession',
  'book' => false,
  'back' => true,
])
<!-- /.content-header -->
@endsection

@section('content')
<section class="content">
    <div class="container-fluid">
        {{ Form::open(['route' => array('admin.cronofy.sessions.storeGroupSession', $sessionType), 'class' => 'form-horizontal zevo_form_submit', 'method'=>'post','role' => 'form', 'id'=>'addgroupsession']) }}
        <div class="card form-card">
            <div class="card-body">
                @include('admin.cronofy.groupsession.form', ['edit' => $edit])
            </div>
            <div class="card-footer">
                <div class="save-cancel-wrap">
                    <a class="btn btn-outline-primary" href="{!! route('admin.cronofy.sessions.index') !!}">
                        {{ trans('buttons.general.cancel') }}
                    </a>
                    <button class="btn btn-primary" id="zevo_submit_btn" type="button">
                        {{ trans('buttons.general.next') }}
                    </button>
                </div>
            </div>
        </div>
        {{ Form::close() }}
    </div>
</section>
@endsection

@section('after-scripts')
{!! $validator = JsValidator::formRequest('App\Http\Requests\Admin\CreateGroupSessionRequest','#addgroupsession') !!}
<script type="text/javascript">
    var ajaxUrl = {
        getSubCategories: '{{ route("admin.cronofy.sessions.get-sub-categories", ":id") }}',
        getUser: '{{ route("admin.cronofy.sessions.get-users", ":id") }}',
        getWSUser: '{{ route("admin.cronofy.sessions.get-ws-users", ":id") }}',
        getCompanyLocations: '{{ route("admin.cronofy.sessions.get-company-locations", ":id") }}',
    },
    data = {
        is_ws: `{{ ($role->slug == 'wellbeing_specialist') ? true : false }}`
    },
    message = {
        note_length: '{{ trans('Cronofy.group_session.message.notes_length') }}',
    };
</script>
<script src="{{ asset('assets/plugins/ckeditor5/ckeditor.js?var='.rand()) }}">
</script>
<script src="{{ asset('js/external/external-ckeditor.js?var='.rand()) }}">
</script>
<script src="{{ asset('assets/plugins/OwlCarousel2/owl.carousel.min.js?var='.rand()) }}">
</script>
<script src="{{ asset('assets/plugins/tree-multiselect/tree-multiselect.js?var='.rand()) }}" type="text/javascript">
</script>
<script src="{{ mix('js/cronofy/groupsession/create.js') }}">
</script>
@endsection
