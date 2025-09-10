@extends('layouts.app')

@section('after-styles')
<!------------------------------- PAGE SCRIPTS START --------------------------->
<!-- Range-slider -->
<link href="{{asset('assets/plugins/range-slider/ion.rangeSlider.min.css?var='.rand())}}" rel="stylesheet"/>
<!-- easy-responsive-tabs -->
<link href="{{asset('assets/plugins/easy-responsive-tabs2/easy-responsive-tabs.css?var='.rand())}}" rel="stylesheet"/>
<!-- step -->
<link href="{{asset('assets/plugins/step/jquery.steps.css?var='.rand())}}" rel="stylesheet"/>
<!------------------------------- PAGE SCRIPTS START --------------------------->
{{-- <link href="https://fonts.googleapis.com/css?family=Raleway:400,500,600,700&display=swap?var=<?= rand() ?>" rel="stylesheet"/> --}}
@endsection

@section('content-header')
<!-- Content Header (Page header) -->
@include('admin.zcquestionbank.breadcrumb', [
    'mainTitle' => trans('survey.zcquestionbank.title.add'),
    'breadcrumb' => Breadcrumbs::render('zcquestionbank.create'),
])
<!-- /.content-header -->
@endsection

@section('content')
<section class="content">
    <div class="container-fluid">
        <div class="card form-card height-auto">
            <div class="card-header detailed-header">
                <div class="form-group mb-0">
                    {{ Form::label('', 'Question Type') }}
                </div>
                <div>
                    <label class="custom-radio">
                        <i class="far fa-line-height me-2">
                        </i>
                        Short Answer
                        {{ Form::radio('questionType', 'short_answer', true, ['class' => 'form-control']) }}
                        <span class="checkmark">
                        </span>
                        <span class="box-line">
                        </span>
                    </label>
                    <label class="custom-radio">
                        <i class="fas fa-list me-2">
                        </i>
                        Choice
                        {{ Form::radio('questionType', 'choice', false, ['class' => 'form-control']) }}
                        <span class="checkmark">
                        </span>
                        <span class="box-line">
                        </span>
                    </label>
                </div>
            </div>
            <div id="shortAnswersContent" style="display: block;">
                @include('admin.zcquestionbank.create-tabs.free-text')
            </div>
            <div id="choiceContent" style="display: none;">
                @include('admin.zcquestionbank.create-tabs.choice')
            </div>
        </div>
    </div>
    <!--/. container-fluid -->
</section>

@include('admin.zcquestionbank.common.modal-box')
@endsection
@section('after-scripts')
<script>
    var singleAddMessage = 'Question has been added';
    var singleDeleteMessage = 'Question has been deleted';
    var multiDeleteMessage = 'All question has been deleted';
    var upload_image_dimension = `{{ trans('survey.messages.upload_image_dimension') }}`;
    var defaultQuestionImg = `{{ asset('assets/dist/img/73a90acaae2b1ccc0e969709665bc62f.png') }}`;
</script>
<script src="{{mix('js/jquery.serializejson.min.js')}}">
</script>
<!------------------------------- PAGE SCRIPTS START --------------------------->
<script src="{{asset('assets/plugins/easy-responsive-tabs2/easyResponsiveTabs.js?var='.rand())}}">
</script>
<!-- bootstrap color picker -->
<script src="{{mix('js/zccolor.js')}}">
</script>
<!-- Range-slider -->
<script src="{{asset('assets/plugins/range-slider/ion.rangeSlider.min.js')}}">
</script>
<!-- Steps js -->
<script src="{{asset('assets/plugins/step/jquery.steps.js?var='.rand())}}">
</script>
<!------------------------------- PAGE SCRIPTS END --------------------------->
<!-- free-text js -->
<script src="{{mix('js/questions/free-text.js')}}">
</script>
<!-- choice js -->
<script src="{{mix('js/questions/choice.js')}}">
</script>
<script src="{{ asset('js/external/jquery.form.min.js?var='.rand()) }}">
</script>
@include('admin.zcquestionbank.common.commonjs')
{!! $freeTextRules->selector('#free-text') !!}
{!! $choiceRules->selector('#choice-question') !!}
<script>
    $(document).ready(function () {
        UpdateGridPart();//run when page first loads

        $('input[name="questionType"]').on('change', function() {
            if($(this).val() == "short_answer"){
                $("#choiceContent").hide();
                $("#shortAnswersContent").show();
            }else{
                $("#choiceContent").show();
                $("#shortAnswersContent").hide();
            }
        });
    });

    $(window).resize(function () {
        UpdateGridPart();//run on every window resize
    });

    function thisHeight(){
        return $(this).height();
    }
    function UpdateGridPart() {
        $(".rsb-with-grid").each(function() {
            var thisULMax = Math.max.apply(Math, $(this).find(".rsb-grid-part").map(thisHeight));
            $(this).height(thisULMax);
        });
    }
</script>
@endsection
