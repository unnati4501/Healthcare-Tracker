<div class="row">
    <input id="total-cc-emails" type="hidden" value="1"/>
    <div class="col-xxl-12 col-md-10 cc-email-wrap" data-order="1">
        <div class="form-group que-head-free-text" data-order="1">
            <div class="qus-inline qus-inline-free-text">
                    <div class="custom-leave-dates me-3 w-75">
                        {{ Form::text('email[1]', null , ['id' => 'email_1', 'class' => 'form-control customEmailValidate', 'placeholder' => 'Add CC','autocomplete' =>'off','aria-describedby'=>'cc_email[1]-error','data-previewelement'=>1]) }}
                    </div>
                    <div class="align-self-center ms-2">
                        <a class="delete-cc-emails action-icon text-danger" href="javascript:void(0);">
                            <i class="far fa-trash">
                            </i>
                        </a>
                    </div>
            </div>
        </div>
    </div>
</div>
<div class="mt-0 mb-4">
    <button class="btn btn-outline-primary" id="addCCEmails" type="button">
        <i class="far fa-plus"></i> Add New CC
    </button>
</div>
