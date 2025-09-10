<!-- Content Header (Page header) -->
<div class="content-header">
    <div class="container-fluid">
        <div class="d-md-flex justify-content-between">
            <div class="align-self-center">
                <h1>{{ $mainTitle }}</h1>
                @if(!empty($breadcrumb))
                    {{ Breadcrumbs::render($breadcrumb) }}
                @endif
            </div>
            @if($back)
            <div class="align-self-center">
              <a class="btn btn-outline-primary" href="{!! route('admin.notifications.index') !!}">
                  <i class="far fa-arrow-left me-3 align-middle"></i> {{trans('buttons.general.back')}}
              </a>
            </div>
            @endif
            @if($create)
            <div class="text-end">
                @permission('create-notification')
                <a class="btn btn-primary" href="{!! route('admin.notifications.create') !!}"><i class="fal fa-plus me-2"></i>{{trans('notificationsettings.buttons.add_notification')}}</a>
                @endauth
            </div>
            @endif
        </div>
    </div>
</div>