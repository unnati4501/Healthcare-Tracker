<h3>
</h3>
<section class="step-box" data-lid="{{ $lesson->id }}" data-step="{{ $step }}">
    <div class="row align-items-center">
        <div class="col-lg-12 align-self-center text-center">
            <div class="ans-main-area question-type-one m-0-a">
                <div class="text-center">
                    <p class="question-text-title mb-4">
                        {{ trans('masterclass.view.labels.view_lesson', ['current' => $step + 1, 'total' => $total_lessons]) }}
                    </p>
                    <h2 class="question-text">
                        {{ $lesson->title }}
                    </h2>
                </div>
                <div class="cu-fade slow choices-main-box">
                    @if($lesson->type == 1)
                    <div class="mus-player">
                        <img src="{{ $lesson->getLessonData(['w' => 640, 'h' => 320, 'zc' => 1, 'conversion' => 'audio_background']) }}"/>
                        <audio class="audio-player" controls="" controlslist="nodownload">
                            <source src="{{ $lesson->getFirstMediaUrl('audio') }}">
                            </source>
                        </audio>
                    </div>
                    @elseif($lesson->type == 2)
                    <div class="normal-video">
                        <video class="img-thumbnail" controls="" controlslist="nodownload" poster="{{ $lesson->getLessonData(['w' => 1024, 'h' => 576, 'zc' => 1, 'conversion' => 'th_lg']) }}">
                            <source src="{{ $lesson->getFirstMediaUrl('video') }}" type="video/mp4">
                                Your browser does not support the video element.
                            </source>
                        </video>
                    </div>
                    @elseif($lesson->type == 3)
                    <div class="text-center w-100 you-tube-videoarea">
                        <iframe allowfullscreen="" frameborder="0" height="450" src="https://www.youtube.com/embed/{{ $lesson->getFirstMedia('youtube')->getCustomProperty('ytid') }}?playsinline=1&rel=0&showinfo=0&color=white" width="100%">
                        </iframe>
                    </div>
                    @elseif($lesson->type == 4)
                    <div class="view-text-area">
                        {!! $lesson->description !!}
                    </div>
                    @elseif($lesson->type == 5)
                    <div class="text-center w-100 you-tube-videoarea">
                        <iframe allowfullscreen="" frameborder="0" height="450" src="https://player.vimeo.com/video/{{ $lesson->getFirstMedia('vimeo')->getCustomProperty('vmid') }}?playsinline=1&rel=0&showinfo=0&color=white" width="100%">
                        </iframe>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>