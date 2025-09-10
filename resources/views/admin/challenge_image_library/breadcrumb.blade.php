<div class="content-header">
    <div class="container-fluid">
        <div class="d-md-flex justify-content-between">
            <div class="align-self-center">
                <h1>
                    {{ $mainTitle }}
                </h1>
                {{ Breadcrumbs::render($breadcrumb) }}
            </div>
            <div class="align-self-center">
                @if(isset($create) && $create == true)
                @permission('add-challenge-image')
                <a class="btn btn-outline-primary" data-bs-target="#bulk-upload-model-box" data-bs-toggle="modal" href="javascript:vodi(0);">
                    <i class="far fa-upload me-3 align-middle">
                    </i>
                    <span class="align-middle">
                        {{ trans('challengeLibrary.buttons.upload') }}
                    </span>
                </a>
                <a class="btn btn-primary" href="{!! route('admin.challengeImageLibrary.create') !!}">
                    <i class="far fa-plus me-3 align-middle">
                    </i>
                    <span class="align-middle">
                        {{ trans('challengeLibrary.buttons.add') }}
                    </span>
                </a>
                @endauth
                @endif
            </div>
        </div>
    </div>
</div>