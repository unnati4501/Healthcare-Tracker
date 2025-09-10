@extends('layouts.app')

@section('content')
@include('admin.user.breadcrumb',['mainTitle'=>trans('labels.user.change_pass')])
<section class="content">
    <div class="container-fluid">
        <!-- Main row -->
        <div class="row">
            <!-- Left col -->
            <section class="col-lg-12">
                <!-- DIRECT CHAT -->
                <div class="card">
                    <!-- /.card-header -->
                    {{ Form::open(['route' => ['admin.users.changeuserpasswordprocess', $user->getKey()], 'class' => 'form-horizontal', 'method'=>'post','role' => 'form', 'id'=>'changePassword']) }}
                    <div class="card-body">
                        <div class="row">
                            <div class="col-xl-4 offset-xl-1">
                                <div class="row">
                                    {{--
                                    <div class="form-group col-md-6 col-xl-12">
                                        <label for="">
                                            {{ trans('labels.user.current_pass') }}
                                        </label>
                                        <input autocomplete="off" class="form-control" id="old_password" name="old_password" type="password"/>
                                    </div>
                                    --}}
                                    <div class="form-group col-md-6 col-xl-12">
                                        <label for="">
                                            {{ trans('labels.user.new_pass') }}
                                        </label>
                                        <input autocomplete="off" class="form-control" id="password" name="password" type="password"/>
                                    </div>
                                    <div class="form-group col-md-6 col-xl-12">
                                        <label for="">
                                            {{ trans('labels.user.confirm_new_password') }}
                                        </label>
                                        <input autocomplete="off" class="form-control" id="password_confirmation" name="password_confirmation" type="password"/>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- /.card-body -->
                    <div class="card-footer border-top text-center">
                        <a class="btn btn-effect btn-outline-secondary me-2 mm-w-100" href="{!! route('dashboard') !!}">
                            {{trans('labels.buttons.cancel')}}
                        </a>
                        <button class="btn btn-primary btn-effect mm-w-100" type="submit">
                            {{trans('labels.buttons.update')}}
                        </button>
                    </div>
                    {{ Form::close() }}
                    <!-- /.card-footer-->
                </div>
                <!--/.direct-chat -->
            </section>
            <!-- /.Left col -->
        </div>
    </div>
    <!-- /.container-fluid -->
</section>
@endsection

@section('after-scripts')
    {!! JsValidator::formRequest('App\Http\Requests\Admin\ChangePasswordRequest','#changePassword') !!}
<script type="text/javascript">
</script>
@endsection
