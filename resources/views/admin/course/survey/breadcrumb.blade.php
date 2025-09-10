<div class="content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6 order-last order-sm-first">
                <h1 class="m-0 text-dark">
                    {{ $appPageTitle }}({{ $type }})
                </h1>
            </div>
            @if(isset($showbackbutton) && $showbackbutton === true)
            <div class="col-sm-6 d-flex justify-content-sm-end align-items-center order-first order-sm-last">
                <div class="text-end">
                    <a class="btn btn-primary btn-sm btn-effect" href="{!! route('admin.masterclass.manageLessions', $record->course_id) !!}">
                        <i class="fas fa-chevron-left">
                        </i>
                        Back to Lessons
                    </a>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>