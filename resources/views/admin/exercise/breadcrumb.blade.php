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
            <div class="align-self-center">
                @if($create)
                  @permission('create-exercise')
                      <a class="btn btn-primary" href="{!! route('admin.exercises.create') !!}"><i class="fal fa-plus me-2"></i>{{trans('exercise.buttons.add_exercise')}}</a>
                      @endauth
                @endif
            </div>
        </div>
    </div>
</div>