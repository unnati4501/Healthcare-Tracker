@extends('layouts.app')

@section('after-styles')
<link href="{{ asset('assets/plugins/tree-multiselect/tree-multiselect.css?var='.rand()) }}" rel="stylesheet"/>
@endsection

@section('content-header')
<!-- Content Header (Page header) -->
@include('admin.meditationtrack.breadcrumb', [
    'mainTitle' => trans('meditationtrack.title.edit'),
    'breadcrumb' => Breadcrumbs::render('meditationtracks.edit'),
])
<!-- /.content-header -->
@endsection

@section('content')
<section class="content no-default-select2">
    <div class="container-fluid">
        <div class="card form-card">
            {{ Form::open(['route' => ['admin.meditationtracks.update', $data->id], 'class' => 'form-horizontal zevo_form_submit', 'method' => 'PATCH', 'role' => 'form', 'id' => 'trackEdit', 'files' => true]) }}
            <div class="card-body">
                @include('admin.meditationtrack.form', ['edit' => true])
            </div>
            <div class="card-footer">
                <div class="save-cancel-wrap">
                    <a class="btn btn-outline-primary" href="{{ route('admin.meditationtracks.index') }}">
                        {{ trans('buttons.general.cancel') }}
                    </a>
                    <button class="btn btn-primary" onclick="formSubmit()" type="submit">
                        {{ trans('buttons.general.update') }}
                    </button>
                </div>
            </div>
            {{ Form::close() }}
        </div>
    </div>
</section>
@endsection

@section('after-scripts')
{!! JsValidator::formRequest('App\Http\Requests\Admin\EditTrackRequest','#trackEdit') !!}
<script src="{{asset('assets/plugins/moment/moment.min.js?var='.rand()) }}" type="text/javascript">
</script>
<script src="{{asset('assets/plugins/jquery.numeric/jquery.numeric.min.js?var='.rand())}}" type="text/javascript">
</script>
<script src="{{ asset('js/external/jquery.form.min.js?var='.rand()) }}" type="text/javascript">
</script>
<script src="{{ asset('assets/plugins/tree-multiselect/tree-multiselect.js?var='.rand()) }}" type="text/javascript">
</script>
<script type="text/javascript">
    var messages = {!! json_encode(trans('meditationtrack.messages')) !!},
        bg_background = `{{ asset('assets/dist/img/boxed-bg.png') }}`,
        url = {
            success: `{{ route('admin.meditationtracks.index') }}`,
        };
    var upload_image_dimension = '{{ trans('meditationtrack.messages.upload_image_dimension') }}';
    function formSubmit() {
        $('#trackEdit').valid();
        var trackCompany = $('#track_company').val().length;
        if (trackCompany == 0) {
            event.preventDefault();
            $('#trackEdit').valid();
            $('#track_company').addClass('is-invalid');
            $('#track_company-error').show();
            $('.tree-multiselect').css('border-color', '#f44436');
        } else {
            $('#track_company').removeClass('is-invalid');
            $('#track_company-error').hide();
            $('.tree-multiselect').css('border-color', '#D8D8D8');
        }
    }
</script>
<script src="{{ mix('js/meditationtrack/edit.js') }}" type="text/javascript">
</script>
@endsection
