<div class="row">
    <input id="total-form-custom-leaves" type="hidden" value="1"/>
    <div class="col-xxl-12 col-md-10 custom-leave-wrap" data-order="1">
        <div class="form-group que-head-free-text" data-order="1">
            <div class="qus-inline qus-inline-free-text">
                <div class="input-group">
                    <div class="custom-leave-dates datepicker-wrap me-3">
                        {{ Form::text('from_date[1]', null , ['id' => 'from_date_1', 'class' => 'form-control custom-leave-from-date datepicker', 'placeholder' => 'Select from date','autocomplete' =>'off','aria-describedby'=>'from_date[1]-error','data-previewelement'=>1]) }}
                        <i class="far fa-calendar">
                        </i>
                    </div>
                    <div class="custom-leave-dates datepicker-wrap">
                        {{ Form::text('to_date[1]', null, ['id' => 'to_date_1', 'class' => 'form-control custom-leave-to-date datepicker', 'placeholder' => 'Select to date','autocomplete' =>'off', 'aria-describedby'=>'to_date[1]-error','data-previewelement'=>1 ]) }}
                        <i class="far fa-calendar">
                        </i>
                    </div>
                    <div class="align-self-center ms-2">
                        <a class="delete-custom-leave action-icon text-danger" href="javascript:void(0);">
                            <i class="far fa-trash">
                            </i>
                        </a>
                        {{-- <a class="action-icon text-success" href="javascript:void(0);" id="addCustomLeaveDates"  title="Add Leave">
                            <i class="far fa-plus">
                            </i>
                        </a> --}}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="mt-0 mb-4">
    <button class="btn btn-outline-primary addCustomLeaveDates" type="button">
        <i class="far fa-plus"></i> Add Leaves
    </button>
    {{-- <a class="action-icon text-success" href="javascript:void(0);" id="addCustomLeaveDates"  title="Add Ingredient">
        <i class="far fa-plus">
        </i>
    </a> --}}
</div>
