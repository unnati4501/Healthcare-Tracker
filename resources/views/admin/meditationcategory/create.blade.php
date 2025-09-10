@extends('layouts.app')

@section('content')
@include('admin.meditationcategory.breadcrumb',['appPageTitle'=>trans('labels.meditationcategory.add_form_title')])
    <section class="content">
        <div class="container-fluid">
            <!-- Main row -->
            <div class="row">
            <!-- Left col -->
                <section class="col-lg-12">
                <!-- DIRECT CHAT -->
                    <div class="card">
                      <!-- /.card-header -->
                        {{ Form::open(['route' => 'admin.meditationcategorys.store', 'class' => 'form-horizontal', 'method'=>'post','role' => 'form', 'id'=>'meditationcategoryAdd','files' => true]) }}
                        <div class="card-body">
                                <div class="row">
                                    @include('admin.meditationcategory.form')
                                </div>
                        </div>
                      <!-- /.card-body -->
                        <div class="card-footer border-top text-center">
                            <a class="btn btn-effect btn-outline-secondary me-2 mm-w-100" href="{!! route('admin.meditationcategorys.index') !!}" >{{trans('labels.buttons.cancel')}}</a>
                            <button type="submit" class="btn btn-primary btn-effect mm-w-100">{{trans('labels.buttons.save')}}</button>
                        </div>
                      {{ Form::close() }}
                      <!-- /.card-footer-->
                    </div>
                    <!--/.direct-chat -->
                </section>
                <!-- /.Left col -->
            </div>
        </div><!-- /.container-fluid -->
    </section>
@endsection

@section('after-scripts')
    {!! JsValidator::formRequest('App\Http\Requests\Admin\CreateMeditationCatRequest','#meditationcategoryAdd') !!}
    <script type="text/javascript">


    $('input[type="file"]').change(function (e) {
        var fileName = e.target.files[0].name;
        if (fileName.length > 40) {
            fileName = fileName.substr(0, 40);
        }
        var allowedMimeTypes = ['image/png', 'image/jpeg', 'image/jpg'];
        if (!allowedMimeTypes.includes(e.target.files[0].type)) {
            toastr.error("{{trans('labels.common_title.image_valid_error')}}");
            $(e.currentTarget).empty().val('');
            $(this).parent('div').find('.custom-file-label').val('');
        } else if (e.target.files[0].size > 2097152) {
            toastr.error("{{trans('labels.common_title.image_size_2M_error')}}");
            $(e.currentTarget).empty().val('');
            $(this).parent('div').find('.custom-file-label').val('');
        } else {
            $(this).parent('div').find('.custom-file-label').html(fileName);
        }
    });

    //--------- preview image
    function readURL(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function (e) {
                $('#previewImg').attr('src', e.target.result);
            }
            reader.readAsDataURL(input.files[0]);
        }
    };
    $("#logo").change(function () {
        readURL(this);
    });
    </script>
@endsection