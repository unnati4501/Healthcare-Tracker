<div class="content-header">
    <div class="container-fluid">
        <div class="d-md-flex justify-content-between">
            <div class="align-self-center">
                <h1>{{ $mainTitle }}</h1>
                @if(!empty($breadcrumb))
                    {{ Breadcrumbs::render($breadcrumb) }}
                @endif
            </div>
            <div class="align-self-center">
                @if(isset($back) && $back == true)
                <button class="btn btn-primary" onclick="history.back()">
                    <span>
                        <i class="far fa-arrow-left me-3 align-middle">
                        </i>
                        {{ trans('labels.buttons.back') }}
                    </span>
                </button>
                @endif
            </div>
        </div>
    </div>
</div>