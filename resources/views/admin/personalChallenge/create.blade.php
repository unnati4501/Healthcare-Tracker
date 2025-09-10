@extends('layouts.app')

@section('after-styles')
<style type="text/css">
    .ingriadiant-make-editable tbody tr:last-child td.show_del .ingriadiant-remove {
        display: block !important;
    }
    .ingriadiant-make-editable tbody tr:last-child td.show_del #ingriadiantAdd {
        display: none !important;
    }
    
    input::-webkit-outer-spin-button,
    input::-webkit-inner-spin-button {
      -webkit-appearance: none;
      margin: 0;
    }
</style>
@endsection

@section('content-header')
<!-- Content Header (Page header) -->
@include('admin.personalChallenge.breadcrumb', [
  'mainTitle' => $mailTitle,
  'breadcrumb' => 'personalChallenges.create'
])
<!-- /.content-header -->
@endsection

@section('content')
<section class="content">
    <div class="container-fluid">
        {{ Form::open(['route' => 'admin.personalChallenges.store', 'class' => 'form-horizontal zevo_form_submit', 'method'=>'post','role' => 'form', 'id'=>'personalChallengeAdd','files' => true]) }}
        <div class="card form-card">
            <div class="card-body">
                @include('admin.personalChallenge.form')
            </div>
            <div class="card-footer">
                <div class="save-cancel-wrap">
                    <a class="btn btn-outline-primary" href="{!! route('admin.personalChallenges.index') !!}">
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
<!-- /.container-fluid -->
@endsection

@section('after-scripts')
{!! JsValidator::formRequest('App\Http\Requests\Admin\CreatePersonalChallengeRequest','#personalChallengeAdd') !!}
<script type="text/javascript">
    var image_valid_error = '{{ trans('personalChallenge.messages.image_valid_error') }}';
    var image_size_2M_error = '{{ trans('personalChallenge.messages.image_size_2M_error') }}';
    var upload_image_dimension = '{{ trans('personalChallenge.messages.upload_image_dimension') }}';
    var challenge_type = '';
    var type = '';
</script>
<script src="{{ mix('js/personalChallenge/createEdit.js') }}">
</script>
@endsection
