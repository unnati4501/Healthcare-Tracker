@extends('layouts.app')
@section('content-header')
<!-- Content Header (Page header) -->
@include('admin.appsettings.breadcrumb', [
    'appPageTitle' => trans('labels.app_settings.change_title'),
    'breadcrumb' => 'appsettings.changeappsetting',
    'changeappsetting' => false,
])
<!-- /.content-header -->
@endsection
@section('content')
<section class="content">
    <div class="container-fluid">
        <div class="card form-card">
            <!-- /.card-header -->
            {{ Form::open(['route' => 'admin.appsettings.store', 'class' => 'form-horizontal zevo_form_submit', 'method'=>'post','role' => 'form', 'id'=>'changeAppSettings','files' => true]) }}
            <div class="card-body">
                    <div class="row">
                        @foreach($app_settings as $key=>$value)
                        <div class="col-lg-6">
                            <div class="form-group">
                                @if($value['type'] == 'text')
                                    <label for="">{{ $value['display'] }}</label>
                                    {{ Form::text($key, old($key,(!empty($AppSettingsData[$key]))? $AppSettingsData[$key] : '' ), ['class' => 'form-control', 'placeholder' => 'Enter '.$value['display'], 'id' => $key, 'autocomplete' => 'off']) }}
                                @elseif($value['type'] == 'radio')
                                    <div>
                                        <label>{{ $value['display'] }}</label>
                                    </div>
                                    @if(!empty($AppSettingsData[$key]))
                                        <label class="custom-radio">{{ trans('appsettings.form.labels.yes') }}
                                            <input type="radio" name="{{$key}}" value="1" {{ ($AppSettingsData[$key] == 1)? 'checked' : '' }} >
                                            <span class="checkmark"></span><span class="box-line"></span>
                                        </label>
                                        <label class="custom-radio">{{ trans('appsettings.form.labels.no') }}
                                            <input type="radio" name="{{$key}}" value="0" {{ ($AppSettingsData[$key] == 0)? 'checked' : '' }} >
                                            <span class="checkmark"></span><span class="box-line"></span>
                                        </label>
                                    @else
                                        <label class="custom-radio">{{ trans('appsettings.form.labels.yes') }}
                                            <input type="radio" name="{{$key}}" value="1" {{ (old($key) == 1)? 'checked' : '' }} >
                                            <span class="checkmark"></span><span class="box-line"></span>
                                        </label>
                                        <label class="custom-radio">{{ trans('appsettings.form.labels.no') }}
                                            <input type="radio" name="{{$key}}" value="0" {{ (old($key) == 0)? 'checked' : '' }} >
                                            <span class="checkmark"></span><span class="box-line"></span>
                                        </label>
                                    @endif

                                @elseif($value['type'] == 'file')
                                    <label>{{ $value['display'] }}</label>
                                    <span class="font-16 qus-sign-tooltip" data-toggle="help-tooltip" title="{{ getHelpTooltipText("app_setting.$key") }}">
                                        <i aria-hidden="true" class="far fa-info-circle text-primary">
                                        </i>
                                    </span>
                                    <div class="custom-file custom-file-preview">
                                    <label class="file-preview-img" for="{{ $key }}" >
                                        @if(!empty($filesData->logo))
                                        <img id="previewImg" src="{{$filesData->logo}}" width="200" height="250" />
                                        @else
                                        <img id="previewImg" src="{{asset('assets/dist/img/boxed-bg.png')}}" width="200" height="300"/>
                                        @endif
                                    </label>
                                    {{ Form::file($key, ['class' => 'custom-file-input form-control', 'id' => $key, 'data-width' => config("zevolifesettings.imageConversions.app_setting.$key.width"), 'data-height' => config("zevolifesettings.imageConversions.app_setting.$key.height"), 'data-ratio' => config("zevolifesettings.imageAspectRatio.app_setting.$key"), 'autocomplete' => 'off','title'=>''])}}
                                    <label class="custom-file-label" for="{{ $key }}">
                                        @if(!empty($filesData->logo_name))
                                            {{$filesData->logo_name}}
                                        @else
                                            {{ trans('appsettings.form.labels.choose_file') }}
                                        @endif
                                    </label>

                                    </div>
                                @elseif($value['type'] == 'list')
                                    <label>{{ $value['display'] }}</label>
                                    {{ Form::select($key, $app_theme, old($key,(!empty($AppSettingsData[$key]))? $AppSettingsData[$key] : '' ), ['class' => 'form-control select2', 'id' => $key, 'autocomplete' => 'off']) }}
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
            </div>
          <!-- /.card-body -->
            <div class="card-footer">
                <div class="save-cancel-wrap">
                    <a class="btn btn-outline-primary" href="{!! route('admin.appsettings.index') !!}" >{{trans('buttons.general.cancel')}}</a>
                    <button type="submit" class="btn btn-primary" id="zevo_submit_btn">{{trans('buttons.general.save')}}</button>
                </div>
            </div>
          {{ Form::close() }}
          <!-- /.card-footer-->
        </div>
    </div>
</section>
@endsection

@section('after-scripts')
{!! JsValidator::formRequest('App\Http\Requests\Admin\AppSettingsRequest','#changeAppSettings') !!}
<script type="text/javascript">
var message = {
    image_valid_error: `{{trans('appsettings.message.image_valid_error')}}`,
    image_size_2M_error: `{{trans('appsettings.message.image_size_2M_error')}}`,
    upload_image_dimension: '{{ trans('appsettings.message.upload_image_dimension') }}',
};
</script>
<script src="{{ asset('js/appsettings/changeappsetting.js') }}" type="text/javascript">
</script>
@endsection