<div class="content-header">
    <div class="container-fluid">
        <div class="d-md-flex justify-content-between">
            <div class="align-self-center">
                <h1>
                    {{ $mainTitle }}
                    @if(isset($tooltip) && $tooltip == true)
                    <span class="font-18 ms-2" data-original-title="{{ trans('survey.insights.title.index_message') }}" data-placement="auto" data-toggle="help-tooltip" title="{{ trans('survey.insights.title.index_message') }}">
                        <i aria-hidden="true" class="far fa-info-circle text-primary">
                        </i>
                    </span>
                    @endif
                </h1>
                @if(!empty($breadcrumb))
                    {!! $breadcrumb !!}
                @endif
            </div>
            <div class="align-self-center">
                @if(isset($back) && $back == true)
                <a class="btn btn-outline-primary" href="{{ route('admin.surveyInsights.index') }}">
                    <i class="far fa-arrow-left me-3 align-middle">
                    </i>
                    <span class="align-middle">
                        {{ trans('buttons.general.back') }}
                    </span>
                </a>
                @endif
            </div>
        </div>
    </div>
</div>