@extends('layouts.app')

@section('after-styles')
<!-- DataTables -->
<link href="{{asset('assets/plugins/datepicker/datepicker3.css?var='.rand())}}" rel="stylesheet"/>
@endsection

@section('content')

@include('admin.companies.breadcrumb',['mainTitle' => trans('labels.company.create_moderator')])
<section class="content">
    <div class="container-fluid">
        <!-- Main row -->
        <div class="row">
            <!-- Left col -->
            <section class="col-lg-12">
                <!-- DIRECT CHAT -->
                <div class="card">
                    <!-- /.card-header -->
                    {{ Form::open(['route' => ['admin.companies.storeModerator', $company->id], 'class' => 'form-horizontal', 'method'=>'PATCH','role' => 'form', 'id'=>'storeModerator']) }}
                    <div class="card-body">
                        <div class="row">
                            @include('admin.companies.moderatorform')
                        </div>
                    </div>
                    <!-- /.card-body -->
                    <div class="card-footer border-top text-center">
                        <a class="btn btn-effect btn-outline-secondary me-2 mm-w-100" href="{!! route('admin.companies.index') !!}">
                            {{ trans('labels.buttons.cancel') }}
                        </a>
                        <button class="btn btn-primary btn-effect mm-w-100" type="submit">
                            {{ trans('labels.buttons.update') }}
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
    {!! JsValidator::formRequest('App\Http\Requests\Admin\CreateCompanyModeratorRequest','#storeModerator') !!}
<script type="text/javascript">
</script>
@endsection
