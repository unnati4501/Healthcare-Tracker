<div class="content-header">
    <div class="container-fluid">
        <div class="d-md-flex justify-content-between">
            <div class="align-self-center">
                <h1>
                    {{ $mainTitle }}
                    <span class="font-18 ms-2 " data-original-title="{{ trans('survey.feedback.title.index_message') }}" data-placement="auto" data-toggle="help-tooltip" title="{{ trans('survey.feedback.title.index_message') }}">
                        <i aria-hidden="true" class="far fa-info-circle text-primary">
                        </i>
                    </span>
                </h1>
                @if(!empty($breadcrumb))
                    {!! $breadcrumb !!}
                @endif
            </div>
            <div class="align-self-center">
                {{-- @if(isset($back) && $back == true)
                <a class="back-link" href="{{ route('admin.roles.index') }}">
                    {{ trans('buttons.general.back') }}
                </a>
                @endif --}}
            </div>
        </div>
    </div>
</div>