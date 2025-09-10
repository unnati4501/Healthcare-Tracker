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
                @if(isset($back) && $back == true)
                <a class="btn btn-primary" href="{{ route('admin.contentChallenge.index') }}">
                    <i class="far fa-arrow-left me-3 align-middle">
                    </i>
                    <span class="align-middle">
                        {{ trans('labels.buttons.back') }}
                    </span>
                </a>
                @endif
            </div>
        </div>
    </div>
</div>