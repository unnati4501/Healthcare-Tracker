@extends('layouts.app')

@section('after-styles')
<!-- DataTables -->
<link href="{{asset('assets/plugins/datatables/dataTables.bootstrap5.min.css?var='.rand())}}" rel="stylesheet"/>
@endsection

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6 order-last order-sm-first">
                <h1 class="m-0 text-dark">
                    Wellbeing Survey Questioners
                </h1>
            </div>
        </div>
    </div>
</div>
<section class="content">
    <div class="container-fluid">
        <!-- Main row -->
        <div class="row">
            <!-- Left col -->
            <section class="col-lg-12">
                <!-- DIRECT CHAT -->
                <div class="card collapsed-card" id="collapseCard">
                    <div class="card-header" data-widget="collapse">
                        <h3 class="card-title">
                            {{trans('labels.common_title.search')}}
                        </h3>
                        <div class="card-tools">
                            <button class="btn btn-tool" data-widget="collapse" type="button">
                                <i class="fa fa-chevron-up">
                                </i>
                            </button>
                        </div>
                    </div>
                    <!-- /.card-header -->
                    <div class="card-body">
                        {{ Form::open(['route' => 'admin.questions.index', 'class' => 'form-horizontal', 'method'=>'get','category' => 'form', 'id'=>'questionSearch']) }}
                        <div class="row">
                            <div class="form-group col-md-3">
                                {{ Form::text('question', request()->get('question'), ['class' => 'form-control', 'placeholder' => 'Search By Question', 'id' => 'question', 'autocomplete' => 'off']) }}
                            </div>
                            <div class="form-group col-md-3">
                                {{ Form::select('category', $categories, request()->get('category'), ['class' => 'form-control select2','id'=>'category',"style"=>"width: 100%;", 'placeholder' => '', 'data-placeholder'=>'Select Category', 'target-data' => 'sub_category', 'autocomplete' => 'off'] ) }}
                            </div>
                            <div class="form-group col-md-3">
                                @if(!empty(request()->get('category')))
                                {{ Form::select('sub_category', $sub_categories, request()->get('sub_category'), ['class' => 'form-control select2','id'=>'sub_category',"style"=>"width: 100%;", 'placeholder' => '', 'data-placeholder'=>'Select Sub-category', 'target-data' => 'sub_category', 'autocomplete' => 'off'] ) }}
                                @else
                                {{ Form::select('sub_category', [], request()->get('sub_category'), ['class' => 'form-control select2','id'=>'sub_category',"style"=>"width: 100%;", 'placeholder' => '', 'data-placeholder'=>'Select Sub-category', 'autocomplete' => 'off', 'disabled'=>true] ) }}
                                @endif
                            </div>
                            <div class="form-group col-md-3">
                                {{ Form::select('question_type', $question_types, request()->get('question_type'), ['class' => 'form-control select2','id'=>'question_type',"style"=>"width: 100%;", 'placeholder' => '', 'data-placeholder'=>'Select Question Type', 'autocomplete' => 'off'] ) }}
                            </div>
                        </div>
                        <div class="text-center">
                            <button class="btn btn-primary btn-effect me-2" type="submit">
                                <i class="fal fa-search me-2">
                                </i>
                                {{trans('labels.buttons.submit')}}
                            </button>
                            <a class="btn btn-secondary btn-effect me-2" href="{{ route('admin.questions.index') }}">
                                <i class="fal fa-undo me-2">
                                </i>
                                {{trans('labels.buttons.reset')}}
                            </a>
                        </div>
                        {{ Form::close() }}
                    </div>
                    <!-- /.card-body -->
                    <!-- /.card-footer-->
                </div>
                <!--/.direct-chat -->
            </section>
            <!-- /.Left col -->
        </div>
        <!-- /.row (main row) -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <!-- /.card-header -->
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover" id="questionManagment">
                                <thead>
                                    <tr>
                                        <th class="text-center" style="display: none">
                                            Updated at
                                        </th>
                                        <th>
                                            Category
                                        </th>
                                        <th>
                                            Sub category
                                        </th>
                                        <th>
                                            Questions
                                        </th>
                                        <th class="text-center no-sort th-btn-2">
                                            Images
                                        </th>
                                        <th>
                                            Question type
                                        </th>
                                        <th class="text-center th-btn-2 no-sort">
                                            Action
                                        </th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                    <!-- /.card-body -->
                </div>
                <!-- /.card -->
            </div>
            <!-- /.col -->
        </div>
        <!-- /.row -->
    </div>
    <!-- /.container-fluid -->
</section>
<!-- Modal -->
<div aria-hidden="true" aria-labelledby="exampleModalCenterTitle" class="modal fade" id="questionShow" role="dialog" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    View preview
                </h5>
                <button aria-label="Close" class="close" data-bs-dismiss="modal" type="button">
                    <span aria-hidden="true">
                        ×
                    </span>
                </button>
            </div>
            <div class="modal-body" id="questionModelData">
            </div>
        </div>
    </div>
</div>
<!-- ./Modal -->
@endsection
<!-- include datatable css -->
@section('after-scripts')
<style type="text/css">
    .hidden{
        display: none;
      }
</style>
<!-- DataTables -->
<script src="{{asset('assets/plugins/datatables/jquery.dataTables.min.js?var='.rand())}}">
</script>
<script src="{{asset('assets/plugins/datatables/dataTables.bootstrap5.min.js?var='.rand())}}">
</script>
<script type="text/javascript">
    var pagination = {{$pagination}};
    var hsQuestionListUrl = '{{ route('admin.questions.getQuestions') }}';
    var hsQuestionShowUrl = '{{ route('admin.questions.show','/')}}';
    var hsSubCategoryUrl = '{{ route("admin.ajax.hsSubCategories", ":id") }}';
</script>
<script src="{{mix('js/hsQuestions.js')}}">
</script>
@endsection
