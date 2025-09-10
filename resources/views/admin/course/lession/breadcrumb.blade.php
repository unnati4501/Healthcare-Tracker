<div class="content-header">
    <div class="container-fluid">
        <div class="d-md-flex justify-content-between">
            <div class="align-self-center">
                <h1>
                    {{ $mainTitle }}
                </h1>
                @if(!empty($breadcrumb))
                    {!! $breadcrumb !!}
                @endif
            </div>
            <div class="align-self-center">
                @if(isset($create))
                <a class="btn btn-primary" href="{{ route('admin.masterclass.createLession', (!empty($record) ? $record->course_id : $course->id)) }}">
                    <i class="far fa-plus me-3 align-middle">
                    </i>
                    <span class="align-middle">
                        {{ trans('masterclass.lesson.buttons.add') }}
                    </span>
                </a>
                @endif
                @if(isset($backToMC))
                <a class="btn btn-outline-primary" href="{{ route('admin.masterclass.index') }}">
                    <i class="far fa-arrow-left me-3 align-middle">
                    </i>
                    <span class="align-middle">
                        {{ trans('labels.buttons.back') }}
                    </span>
                </a>
                @endif
                <!-- .back to lessons -->
                @if(isset($back))
                <a class="back-link" href="{{ route('admin.masterclass.manageLessions', (!empty($record) ? $record->course_id : $course->id)) }}">
                    {{ trans('labels.buttons.back') }}
                </a>
                @endif
                <!-- /.back to lessons -->
                <!-- .survey buttons -->
                @if(isset($allow_add_survey_button) && $allow_add_survey_button == true)
                <div class="text-end">
                    <a class="btn btn-primary" href="{{ route('admin.masterclass.addSurveys', (!empty($record) ? $record->course_id : $course->id)) }}">
                        <i class="far fa-plus">
                        </i>
                        {{ trans('masterclass.survey.buttons.add_survey') }}
                    </a>
                </div>
                @elseif(isset($allow_remove_survey_button) && $allow_remove_survey_button == true)
                <div class="text-end">
                    <a class="btn btn-outline-primary" data-bs-target="#remove-survey-model-box" data-bs-toggle="modal" href="javascript:void(0);">
                        <i class="far fa-trash-alt">
                        </i>
                        {{ trans('masterclass.survey.buttons.remove_survey') }}
                    </a>
                </div>
                @endif
                <!-- /.survey buttons -->
            </div>
        </div>
    </div>
</div>
