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
                @if(isset($backBtn) && $backBtn == true)
                <a class="btn btn-outline-primary" href="{{ route('admin.hrReport.index') }}">
                    <i class="far fa-arrow-left me-3 align-middle">
                    </i>
                    <span class="align-middle">
                        {{ trans('buttons.general.back') }}
                    </span>
                </a>
                @elseif(isset($freetxtBtn) && $freetxtBtn == true)
                {{-- <a class="btn btn-primary" href="{{ route('admin.hrReport.reviewFreeText') }}">
                    <span class="align-middle">
                        {{ trans('labels.hr_report.review_free_text') }}
                    </span>
                </a> --}}
                @endif
            </div>
        </div>
    </div>
</div>