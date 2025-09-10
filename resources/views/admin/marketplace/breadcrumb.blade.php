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
                <a class="btn btn-outline-primary" href="{{ route('admin.marketplace.index', ['#booked-tab']) }}">
                    <i class="far fa-arrow-left me-3 align-middle">
                    </i>
                    <span class="align-middle">
                        {{ trans('buttons.general.back') }}
                    </span>
                </a>
                @endif
                @if(isset($backToBookingTab) && $backToBookingTab == true)
                <a class="btn btn-outline-primary" href="{{ route('admin.marketplace.index') }}">
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