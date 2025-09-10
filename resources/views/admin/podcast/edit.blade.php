@extends('layouts.app')

@section('after-styles')
<link href="{{ asset('assets/plugins/tree-multiselect/tree-multiselect.css?var='.rand()) }}" rel="stylesheet"/>
@endsection

@section('content-header')
<!-- Content Header (Page header) -->
@include('admin.podcast.breadcrumb', [
    'mainTitle' => trans('podcast.title.edit'),
    'breadcrumb' => Breadcrumbs::render('podcasts.edit'),
])
<!-- /.content-header -->
@endsection

@section('content')
<section class="content no-default-select2">
    <div class="container-fluid">
        <div class="card form-card">
            {{ Form::open(['route' => ['admin.podcasts.update', $data->id], 'class' => 'form-horizontal zevo_form_submit', 'method' => 'PATCH', 'role' => 'form', 'id' => 'podcastEdit', 'files' => true]) }}
            <div class="card-body">
                @include('admin.podcast.form', ['edit' => true])
            </div>
            <div class="card-footer">
                <div class="save-cancel-wrap">
                    <a class="btn btn-outline-primary" href="{{ route('admin.podcasts.index') }}">
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
{!! JsValidator::formRequest('App\Http\Requests\Admin\EditPodcastRequest','#podcastEdit') !!}
<script src="{{asset('assets/plugins/moment/moment.min.js?var='.rand()) }}" type="text/javascript">
</script>
<script src="{{asset('assets/plugins/jquery.numeric/jquery.numeric.min.js?var='.rand())}}" type="text/javascript">
</script>
<script src="{{ asset('js/external/jquery.form.min.js?var='.rand()) }}" type="text/javascript">
</script>
<script src="{{ asset('assets/plugins/tree-multiselect/tree-multiselect.js?var='.rand()) }}" type="text/javascript">
</script>
<script type="text/javascript">
    var messages = {!! json_encode(trans('podcast.messages')) !!},
        bg_background = `{{ asset('assets/dist/img/boxed-bg.png') }}`,
        url = {
            success: `{{ route('admin.podcasts.index') }}`,
        };
    var upload_image_dimension = '{{ trans('podcast.messages.upload_image_dimension') }}';
    function formSubmit() {
        $('#podcastEdit').valid();
        var trackCompany = $('#podcast_company').val().length;
        if (trackCompany == 0) {
            event.preventDefault();
            $('#podcastEdit').valid();
            $('#podcast_company').addClass('is-invalid');
            $('#podcast_company-error').show();
            $('.tree-multiselect').css('border-color', '#f44436');
        } else {
            $('#podcast_company').removeClass('is-invalid');
            $('#podcast_company-error').hide();
            $('.tree-multiselect').css('border-color', '#D8D8D8');
        }
    }
</script>
<script src="{{ mix('js/podcast/edit.js') }}" type="text/javascript">
</script>
@endsection
