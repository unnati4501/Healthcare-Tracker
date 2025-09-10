<div class="row">
    <input id="total-form-custom-leaves" type="hidden" value="{{sizeof($customLeaveDates)}}"/>
    @foreach($customLeaveDates as $index => $customLeave)
    <div class="col-xxl-12 col-md-8 custom-leave-wrap" data-order="{{$index}}">
        <div class="form-group que-head-free-text" data-order="{{$index}}">
            <div class="qus-inline qus-inline-free-text" data-id="{{$customLeave['id']}}">
                <div class="input-group">
                        <div class="custom-leave-dates datepicker-wrap me-3">
                            {{ Form::text('from_date['.$index.']', $customLeave['from_date'] , ['id' => 'from_date_'.$index, 'class' => 'form-control custom-leave-from-date datepicker', 'placeholder' => 'Select from date', 'autocomplete' =>'off', 'aria-describedby'=>'from_date[1]-error','data-previewelement'=>1, 'disabled' => $loggedInUser->slug === 'wellbeing_specialist' ? true : false]) }}
                            <i class="far fa-calendar">
                            </i>
                        </div>
                        <div class="custom-leave-dates datepicker-wrap">
                            {{ Form::text('to_date['.$index.']', $customLeave['to_date'] , ['id' => 'to_date_'.$index, 'class' => 'form-control custom-leave-to-date datepicker', 'placeholder' => 'Select to date', 'autocomplete' =>'off', 'aria-describedby'=>'to_date[1]-error','data-previewelement'=>1, 'disabled' => $loggedInUser->slug === 'wellbeing_specialist' ? true : false]) }}
                            <i class="far fa-calendar">
                            </i>
                        </div>
                        @if($loggedInUser->slug !== 'wellbeing_specialist') 
                        <div class="align-self-center ms-2">
                            <a class="delete-custom-leave action-icon text-danger" data-id="{{$customLeave['id']}}" href="javascript:void(0);">
                                <i class="far fa-trash">
                                </i>
                            </a>
                        </div>
                        @endif
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>
@if($loggedInUser->slug !== 'wellbeing_specialist') 
<div class="mt-0 mb-4">
    <button class="btn btn-outline-primary addCustomLeaveDates" type="button">
        <i class="far fa-plus"></i> Add Leaves
    </button>
</div>
@endif