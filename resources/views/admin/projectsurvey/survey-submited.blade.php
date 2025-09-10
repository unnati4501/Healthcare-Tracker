@extends('layouts.project-survey')

@section('before-styles')

@endsection

@section('content')
 <section class="container">
    <div id="userFeedback" class="user-question-main question-feedback-page">
      <!--------------------- Qus-1 --------------------->
      <h3><span></span></h3>
      <section class="step-box">
        <h4 class="text-center question-feedback-anounce  mt-5">Thank you for Completing the Feedback Form</h4>
        <!-- row -->
        <div class="question-feedback-area mt-5">
          <div class="ans-main-area question-type-one m-0-a">
            <div class="text-center">
              <img class="mb-4" src="{{ asset('assets/dist/img/feedback/feedback-thank-you.svg') }}" alt="">
              <h1 class="question-text mb-4">We really appreciate
                your feedback</h1>
              <a href="https://www.yopmail.com/" class="btn btn-effect btn-primary m-w-100" >Close</a>
            </div>
          </div>
        </div>
      </section>
      <!--------------------- ./Qus-1 --------------------->

    </div>
  </section>
@endsection
@section('after-scripts')

<script type="text/javascript">
  $(document).ready(function() {

});
</script>
@endsection
