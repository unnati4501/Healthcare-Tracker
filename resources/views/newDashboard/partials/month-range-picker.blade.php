<div class="dashboard-card-filter input-daterange monthranges" data-tier="{{ $tier }}" id="{{ $parentId }}">
    <div>
        <div class="datepicker-wrap mb-0 form-group">
            {{ Form::text('monthrange', null, ['class' => 'input-sm form-control bg-white',' id' => $fromId, 'placeholder' => 'From month', 'readonly' => true]) }}
            <i class="far fa-calendar">
            </i>
        </div>
    </div>
    <div>
        -
    </div>
    <div>
        <div class="datepicker-wrap mb-0 form-group">
            {{ Form::text('monthrange', null, ['class' => 'input-sm form-control bg-white', 'id' => $toId, 'placeholder' => 'To month', 'readonly' => true]) }}
            <i class="far fa-calendar">
            </i>
        </div>
    </div>
    @if(isset($tooltip))
    <div>
        <a class="tooltip-icon" data-placement="bottom" data-bs-toggle="tooltip" href="javascript:void(0);" title="{{ $tooltip }}">
            <i class="fal fa-info-circle">
            </i>
        </a>
    </div>
    @endif
</div>