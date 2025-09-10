<!-- Content Header (Page header) -->
<div class="content-header">
    <div class="container-fluid">
        <div class="d-md-flex justify-content-between">
            <div class="align-self-center">
                <h1>{{ $appPageTitle }}</h1>
                @if(!empty($breadcrumb))
                    {{ Breadcrumbs::render($breadcrumb) }}
                @endif
            </div>
            @if($addCalendar)
                @if($calendarCount < 2)
                <div class="align-self-center">
                    <a class="btn btn-primary" href="{{ ($calendarCount > 0) ? route('admin.cronofy.linkCalendar') : route('admin.cronofy.authenticate') }}">
                        <i class="far fa-plus me-3 align-middle">
                        </i>
                        <span class="align-middle">
                            {{ trans('Cronofy.buttons.add_calendar') }}
                        </span>
                    </a>
                </div>
                @endif
            @endif
        </div>
    </div>
</div>