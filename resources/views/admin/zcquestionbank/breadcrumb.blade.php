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
                @if(isset($create) && $create == true)
                @permission('create-question-bank')
                <a class="btn btn-primary" href="{{ route('admin.zcquestionbank.create') }}">
                    <i class="far fa-plus me-3 align-middle">
                    </i>
                    <span class="align-middle">
                        {{ trans('survey.zcquestionbank.buttons.add') }}
                    </span>
                </a>
                @endauth
                @endif
            </div>
        </div>
    </div>
</div>