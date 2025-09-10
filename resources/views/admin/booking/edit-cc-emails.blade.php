<div class="row">
    <input id="total-cc-emails" type="hidden" value="{{sizeof($customEmails)}}"/>
    @foreach($customEmails as $index => $customEmail)
    <div class="col-xxl-12 col-md-8 cc-email-wrap" data-order="{{$index}}">
        <div class="form-group que-head-free-text" data-order="{{$index}}">
            <div class="qus-inline qus-inline-free-text" data-id="{{$customEmail['id']}}">
                    <div class="custom-leave-dates me-3 w-75">
                        {{ Form::text('email['.$index.']', $customEmail['email'] , ['id' => 'email_'.$index, 'class' => 'form-control customEmailValidate', 'placeholder' => 'Add cc email', 'autocomplete' =>'off', 'aria-describedby'=>'email[1]-error','data-previewelement'=>1]) }}
                    </div>
                    <div class="align-self-center ms-2">
                        <a class="delete-cc-emails action-icon text-danger" data-id="{{$customEmail['id']}}" href="javascript:void(0);">
                            <i class="far fa-trash">
                            </i>
                        </a>
                    </div>
            </div>
        </div>
    </div>
    @endforeach
</div>
<div class="mt-0 mb-4">
    <button class="btn btn-outline-primary" id="addCCEmails" type="button">
        <i class="far fa-plus"></i> Add New CC
    </button>
</div>
