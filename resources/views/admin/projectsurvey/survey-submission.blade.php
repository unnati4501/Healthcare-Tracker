@extends('layouts.project-survey')

@section('before-styles')
<link href="{{ asset('assets/plugins/step/jquery.steps.css?var='.rand()) }}" rel="stylesheet"/>
@endsection

@section('content')
@if($enableSurvey == true)
<section class="container">
    {{ Form::open(['route' => ["submitProjectSurvey", $surveyId], 'method'=>'post', 'role' => 'form', 'id'=>'submitProjectSurvey']) }}
    <div id="userFeedback" class="user-question-main question-feedback-page">
        <h3></h3>
        <section class="step-box" data-step="0">
            <h4 class="text-center question-feedback-anounce">Your Opinion Matter to us!</h4>
            <div class="question-feedback-area">
              <div class="ans-main-area question-type-one m-0-a">
                <div class="text-center">
                  <h2 class="question-text">How happy were you with Zevo</h2>
                </div>

                <div class="cu-fade slow choices-main-box">
                  <!-- item-box -->
                  @foreach($feedBackType as $key => $value)
                  <?php $logoUrl = (!empty($key)) ? getStaticNpsEmojiUrl($key) : getDefaultFallbackImageURL('nps', 'logo'); ?>
                  <label class="choices-item-box">
                    <input type="radio" name="feedBack" value="{{$key}}" id="feedBack">
                    <div class="markarea" style="background: hsl(0deg 0% 96% / 76%);">
                      <span class="checkmark animated tada faste"></span>
                      <div class="choices-item-img">
                        <img class="" src="{{$logoUrl}}" alt="">
                      </div>
                    </div>
                  </label>
                  @endforeach
                  <!-- /.item-box -->
                </div>
              </div>
            </div>
      </section>
      <h3><span></span></h3>
      <section class="step-box" data-step="1">
        <h4 class="text-center question-feedback-anounce">Your Opinion Matter to us!</h4>
        <!-- row -->
        <div class="question-feedback-area">
          <div class="ans-main-area question-type-one m-0-a">
            <div class="text-center">
              <h1 class="question-text">Tell us more about why you choose your rating</h1>
            </div>
            <div class="form-group ans-textarea">
              <label>Enter your message</label>
              <textarea class="form-control cu-fade slow" id="feedBackNote" rows="5" name="feedBackNote"></textarea>
            </div>
            <!-- <div class="anonymous">Your answer will be logged as Anonymous user</div> -->
            <div class="ans-submit-btn-area mb-3">
              <!-- <button type="submit" class="btn btn-effect btn-primary-two m-w-100 cu-fade slow">Confirm</button> -->
            </div>
          </div>
        </div>
      </section>
    </div>
    {{ Form::close() }}
</section>
@endif
@endsection
@section('after-scripts')
{!! $validator = JsValidator::formRequest('App\Http\Requests\Admin\StoreProjectSurveyResponse', '#submitProjectSurvey') !!}
<script src="{{ asset('assets/plugins/step/jquery.steps.js?var='.rand()) }}">
</script>
<!-- <script src="{{ mix('js/projectSurveySubmit.js') }}"> -->
</script>
<script type="text/javascript">
  $(document).ready(function() {
    $("#userFeedback").steps({
        headerTag: "h3",
        bodyTag: "section",
        transitionEffect: "fade",
        autoFocus: true,
        onStepChanging: function(event, currentIndex, newIndex) {
              if (newIndex < currentIndex) {
                return true;
            }
            $('.toast').remove();
            var stepIsValid = true,
                validator = $('#submitProjectSurvey').validate();
            $(':input', `[data-step="${currentIndex}"]`).each(function() {
                var xy = validator.element(this);
                stepIsValid = stepIsValid && (typeof xy == 'undefined' || xy);
            });
            if (!stepIsValid) {
                toastr.error('Please select an option.');
            }
            return stepIsValid;
        },
        onFinished: function(event, currentIndex) {
          $('#submitProjectSurvey').submit();
            // $('a[href="#finish"]').parents('li').attr('aria-disabled', true).addClass('disabled');
            // var _url = $('#submitProjectSurvey').attr('action'),
            //     _data = $('#submitProjectSurvey').serialize();
            // $.ajax({
            //     url: _url,
            //     type: 'POST',
            //     dataType: 'json',
            //     data: _data,
            // }).done(function(data) {
            //     $('.toast').remove();
            //     if (data && data.status === 1) {
            //         $('body').addClass('body-scroll-remover');
            //         $('#survey_submitted').fadeIn('slow');
            //     } else {
            //         toastr.error(data.message || "Failed to submit the survey, please try again!");
            //     }
            // }).fail(function(error) {
            //     $('.toast').remove();
            //     if (error.hasOwnProperty('responseJSON')) {
            //         if (error.responseJSON.status == 0) {
            //             toastr.error(error.responseJSON.message);
            //         } else if (error.responseJSON.status == 2) {
            //             // toastr.error('This survey has been expired.');
            //             window.location.reload();
            //         } else if (error.responseJSON.status == 3) {
            //             // toastr.error('You have already submitted the survey.');
            //             window.location.reload();
            //         } else if (error.responseJSON.status == 4) {
            //             // toastr.error('You have already submitted the survey.');
            //             $('body').addClass('body-scroll-remover');
            //             $('#comapny_expired').fadeIn('slow');
            //         } else {
            //             toastr.error("Something went wrong, please try again!");
            //         }
            //     } else {
            //         toastr.error("Something went wrong, please try again!");
            //     }
            // }).always(function() {
            //     $('a[href="#finish"]').parents('li').removeAttr('aria-disabled').removeClass('disabled');
            // });
        }
    });
    $(document).on('click', '.closePreview', function(e) {
        e.preventDefault();
    });
    $(document).on('change, click', 'input[type="radio"][data-skip-on-selection="true"]', function(e) {
        setTimeout(function() {
            $("#userQuestion").steps('next');
        }, 150);
    });
    $('[data-key-validation="true"]').on('keyup', function(e) {
        $(this).valid();
    });
});
</script>
@endsection
