@extends('layouts.app')

@section('content-header')
<!-- Content Header (Page header) -->
@include('admin.challenge_image_library.breadcrumb', [
  'mainTitle' => trans('challengeLibrary.title.add'),
  'breadcrumb' => 'challengeImageLibrary.create'
])
<!-- /.content-header -->
@endsection

@section('content')
<section class="content">
    <div class="container-fluid">
        {{ Form::open(['route' => 'admin.challengeImageLibrary.store', 'class' => 'form-horizontal zevo_form_submit', 'method'=>'post','role' => 'form', 'id'=>'AddChallengeImage', 'files' => true]) }}
        <div class="card form-card">
            <div class="card-body">
                <div class="card-inner">
                    <div class="row">
                        @include('admin.challenge_image_library.form', ['edit' => false])
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <div class="save-cancel-wrap">
                    <a class="btn btn-outline-primary" href="{!! route('admin.challengeImageLibrary.index') !!}">
                        {{ trans('buttons.general.cancel') }}
                    </a>
                    <button class="btn btn-primary" id="zevo_submit_btn" type="submit">
                        {{ trans('buttons.general.save') }}
                    </button>
                </div>
            </div>
        </div>
        {{ Form::close() }}
    </div>
</section>
@endsection

@section('after-scripts')
{!! $validator = JsValidator::formRequest('App\Http\Requests\Admin\CreateChallengeImageLibRequest','#AddChallengeImage') !!}
<script type="text/javascript">
    var image_valid_error = '{{ trans('challengeLibrary.messages.image_valid_error') }}';
    var image_size_2M_error = '{{ trans('challengeLibrary.messages.image_size_2M_error') }}';
    var upload_image_dimension = '{{ trans('challengeLibrary.messages.upload_image_dimension') }}';
</script>
<script src="{{ mix('js/challengeLibrary/createEdit.js') }}">
</script>
@endsection
