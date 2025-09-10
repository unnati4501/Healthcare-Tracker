@extends('layouts.app')

@section('after-styles')
<!------------------------------- PAGE SCRIPTS START --------------------------->
<link href="{{asset('assets/plugins/range-slider/ion.rangeSlider.min.css?var='.rand())}}" rel="stylesheet"/>
<!-- easy-responsive-tabs -->
<link href="{{asset('assets/plugins/easy-responsive-tabs2/easy-responsive-tabs.css?var='.rand())}}" rel="stylesheet"/>
<!-- step -->
<link href="{{asset('assets/plugins/step/jquery.steps.css?var='.rand())}}" rel="stylesheet"/>
<!------------------------------- PAGE SCRIPTS START --------------------------->
{{-- <link href="https://fonts.googleapis.com/css?family=Raleway:400,500,600,700&display=swap?var=<?= rand() ?>" rel="stylesheet"/> --}}
<style type="text/css">
    .no-pointer-event { pointer-events: none; }
</style>
@endsection

@section('content-header')
<!-- Content Header (Page header) -->
@include('admin.zcquestionbank.breadcrumb', [
    'mainTitle' => trans('survey.zcquestionbank.title.edit'),
    'breadcrumb' => Breadcrumbs::render('zcquestionbank.edit'),
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
                        {{ Form::radio('questionType', 'short_answer', ($type == 'free-text'), ['class' => 'form-control', 'disabled' => true]) }}
                        <span class="checkmark">
                        </span>
                        <span class="box-line">
                        </span>
                    </label>
                    <label class="custom-radio">
                        <i class="fas fa-list me-2">
                        </i>
                        Choice
                        {{ Form::radio('questionType', 'choice', ($type == 'choice'), ['class' => 'form-control', 'disabled' => true]) }}
                        <span class="checkmark">
                        </span>
                        <span class="box-line">
                        </span>
                    </label>
                </div>
            </div>
            @if($type == 'free-text')
            <div id="shortAnswersContent">
                @include('admin.zcquestionbank.edit-tabs.free-text')
            </div>
            @elseif($type == 'choice')
            <div id="choiceContent">
                @include('admin.zcquestionbank.edit-tabs.choice')
            </div>
            @endif
        </div>
    </div>
</section>
@include('admin.zcquestionbank.common.modal-box')
@endsection
@section('after-scripts')
<script>
    $(document).ready(function () {
        $("form").bind("keypress", function (e) {
            if (e.keyCode == 13) {
                return false;
            }
        });
    });
</script>
<script>
    var singleAddMessage = 'Question has been added';
    var singleDeleteMessage = 'Question has been delete';
    var multiDeleteMessage = 'All question has been deleted';
    var upload_image_dimension = `{{ trans('survey.messages.upload_image_dimension') }}`;
    var defaultQuestionImg = `{{ asset('assets/dist/img/73a90acaae2b1ccc0e969709665bc62f.png') }}`;
</script>
<script src="{{mix('js/jquery.serializejson.min.js')}}">
</script>
<!------------------------------- PAGE SCRIPTS START --------------------------->
<script src="{{asset('assets/plugins/easy-responsive-tabs2/easyResponsiveTabs.js?var='.rand())}}">
</script>
<!-- Range-slider -->
<script src="{{asset('assets/plugins/range-slider/ion.rangeSlider.min.js')}}">
</script>
<!-- Steps js -->
<script src="{{asset('assets/plugins/step/jquery.steps.js?var='.rand())}}">
</script>
<script src="{{mix('js/zccolor.js')}}">
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
@endsection
