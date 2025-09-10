@extends('layouts.app')

@section('after-styles')
@if($isShowContentType)
<link href="{{asset('assets/plugins/tree-multiselect/tree-multiselect.css?var='.rand())}}" rel="stylesheet"/>
@endif
<link href="{{asset('assets/plugins/datepicker/datepicker3.css?var='.rand())}}" rel="stylesheet"/>
{{-- <link href="{{asset('assets/plugins/timepicker/bootstrap-timepicker.min.css?var='.rand())}}" rel="stylesheet"/> --}}
<link href="{{asset('assets/plugins/datatables/dataTables.bootstrap5.min.css?var='.rand())}}" rel="stylesheet"/>
<style type="text/css">
    #survey_roll_out_time.form-control[readonly] { background-color: transparent; }
</style>
@endsection

@section('content-header')
<!-- Content Header (Page header) -->
@include('admin.companies.breadcrumb', [
    'mainTitle'   => trans('company.title.edit'),
    'breadcrumb'  => Breadcrumbs::render('companies.edit'),
    'companyType' => $companyType
])
<!-- /.content-header -->
@endsection

@section('content')
<section class="content">
    <div class="container-fluid">
            {{ Form::open(['route' => ['admin.companies.update', [$companyType, $recordData->id]], 'class' => 'form-horizontal zevo_form_submit', 'method' => 'PATCH', 'role' => 'form', 'id' => 'companyEdit', 'files' => true]) }}
            {{ Form::hidden('companyType', $companyType, ['id'=>'companyType'])}}
            {{ Form::hidden('isChild', (!is_null($recordData->parent_id) && $companyType != 'zevo' ? 1 : 0 ), ['id'=>'isChild'])}}
            @include('admin.companies.form', ['edit' => true])
            {{-- <div class="card-footer">
                <div class="save-cancel-wrap">
                    <a class="btn btn-outline-primary" href="{{ route('admin.companies.index',$companyType) }}">
                        {{ trans('buttons.general.cancel') }}
                    </a>
                    @if($isShowContentType)
                    <button class="btn btn-primary" onclick="formSubmit();" type="submit">
                        {{trans('labels.buttons.update')}}
                    </button>
                    @else
                    <button class="btn btn-primary" type="submit">
                        {{trans('labels.buttons.update')}}
                    </button>
                    @endif
                </div>
            </div> --}}
            {{ Form::close() }}
    </div>
</section>
<div class="modal fade" data-id="0" id="remove-media-model-box" role="dialog" tabindex="-1">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title remove-media-title">
                </h5>
                <button aria-label="Close" class="close" data-bs-dismiss="modal" type="button">
                    <i class="fal fa-times">
                    </i>
                </button>
            </div>
            <div class="modal-body">
                <p class="remove-media-message">
                </p>
            </div>
            <div class="modal-footer">
                {{ Form::hidden('remove_media_type', '', ['id' => 'remove_media_type']) }}
                <button class="btn btn-outline-primary" data-bs-dismiss="modal" type="button">
                    {{ trans('labels.buttons.cancel') }}
                </button>
                <button class="btn btn-primary" id="remove-media-confirm" type="button">
                    Remove
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" data-id="0" id="remove-moderator-box" role="dialog" tabindex="-1">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    Remove Moderator?
                </h5>
                <button aria-label="Close" class="close" data-bs-dismiss="modal" type="button">
                    <i class="fal fa-times">
                    </i>
                </button>
            </div>
            <div class="modal-body">
                <p>
                    Are you sure to remove moderator from the company?
                </p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline-primary" data-bs-dismiss="modal" type="button">
                    {{ trans('buttons.general.cancel') }}
                </button>
                <button class="btn btn-primary" id="remove-moderator-confirm" type="button">
                    {{ trans('buttons.general.remove') }}
                </button>
            </div>
        </div>
    </div>
</div>
@include('admin.companies.steps.digitaltherapy.location-general-slots.add-new-slot-modal')
@endsection

@section('after-scripts')
{!! JsValidator::formRequest('App\Http\Requests\Admin\EditCompanyRequest','#companyEdit') !!}
<script src="{{ asset('assets/plugins/step/jquery.steps.js?var='.rand()) }}"></script>
<script src="{{asset('assets/plugins/datepicker/bootstrap-datepicker.js?var='.rand())}}">
</script>
{{-- <script src="{{asset('assets/plugins/timepicker/bootstrap-timepicker.js?var='.rand())}}">
</script> --}}
<script src="{{ asset('assets/plugins/jonthornton-timepicker/jquery.timepicker.min.js?var='.rand()) }}">
</script>
<script src="{{asset('assets/plugins/moment/moment.min.js?var='.rand()) }}">
</script>
<script src="{{asset('assets/plugins/moment/moment-timezone-with-data-10-year-range.js?var='.rand()) }}"></script>
{{-- <script src="{{ asset('assets/plugins/jonthornton-timepicker/jquery.datepair.min.js?var='.rand()) }}">
</script> --}}
<script src="{{asset('assets/plugins/datatables/jquery.dataTables.min.js?var='.rand())}}">
</script>
<script src="{{asset('assets/plugins/datatables/dataTables.bootstrap5.min.js?var='.rand())}}">
</script>
@if($isShowContentType)
<script src="{{ asset('assets/plugins/tree-multiselect/tree-multiselect.js?var='.rand()) }}">
</script>
@endif
<script src="{{ asset('assets/plugins/event-calendar/jquery.bs.calendar.js?var='.rand()) }}">
</script>
<script src="{{ asset('assets/plugins/tinymce/tinymce.min.js?var='.rand()) }}"></script>
<script type="text/javascript">
    var companyEditForm = $("#companyEdit");
    var stateUrl = '{{ route("admin.ajax.states", ":id") }}',
        tzUrl = '{{ route("admin.ajax.timezones", ":id") }}',
        max_end_date = new Date('{{ $max_end_date }}'),
        start_start_date = new Date('{{ $start_start_date }}'),
        end_start_date = new Date('{{ $end_start_date }}'),
        today = new Date(),
        endDate = new Date(new Date().setYear(today.getFullYear() + 100)),
        minDOBDate = new Date(new Date().setYear(today.getFullYear() - 18)),
        company_roles = $('#assigned_roles').val(),
        users_attached_role = {!! $usersAttachedRole !!},
        roleGroup = '{{ $user_role->group }}',
        contentValidateURL = '{{ route("admin.ajax.checkcompaniescontentvalidate") }}',
        moderatorDatatableURL = '{{ route("admin.companies.getCompanyModerators", $recordData->id) }}',
        emailValidateURL = '{{ route("admin.ajax.checkEmailExists") }}',
        upcomingSurveyDetails = '{{ route("admin.companies.getUpcomingSurveyDetails") }}',
        wellbeingSpecialist = `<?php echo json_encode($wellbeingSp); ?>`,
        locationList = `<?php echo json_encode($companyLocation); ?>`,
        getStaffServices = '{{ route("admin.companies.getStaffServices") }}',
        hoursby = '{{ !empty($companyDT) ? $companyDT->set_hours_by : '1' }}',
        availabilityby = '{{ !empty($companyDT) ? $companyDT->set_availability_by : '1' }}',
        isDtIncludedInCPlan = '{{ route("admin.ajax.checkdtexists") }}',
        getDtLocationSlotsURL = '{{ route("admin.ajax.dt-location-slots") }}';
        saveLocationSlotsTemp = '{{ route("admin.companies.save-locationwise-slots-temp") }}',
        companyId = '{{ $recordData->id }}',
        getSpecificSlots = "{{ route('admin.companies.get-specific-slot', '/') }}",
        deleteTempSlots = "{{ route('admin.companies.delete-temp-slots','/') }}",
        getLocationSpecificWbsList = "{{ route('admin.ajax.get-wbs-list') }}",
        dtSpecificArray = `<?php echo json_encode($dtSpecificArray); ?>`,
        dtLocationSpecificArray = `<?php echo json_encode($dtLocationSpecificArray); ?>`;
        getLocationGeneralAvabilities = '{{ route("admin.ajax.get-location-general-avabilities") }}',
        contactUsDescriptionValid = true;
    var message = {
        upload_image_dimension      : '{{ trans('company.messages.upload_image_dimension') }}',
        first_name_required         : '{{ trans('company.validation.first_name_required') }}',
        last_name_required          : '{{ trans('company.validation.last_name_required') }}',
        email_required              : '{{ trans('company.validation.email_required') }}',
        min_2_characters_first_name : '{{ trans('company.validation.min_2_characters_first_name') }}',
        min_2_characters_last_name  : '{{ trans('company.validation.min_2_characters_last_name') }}',
        valid_first_name            : '{{ trans('company.validation.valid_first_name') }}',
        valid_last_name             : '{{ trans('company.validation.valid_last_name') }}',
        valid_email                 : '{{ trans('company.validation.valid_email') }}',
        email_exists                : '{{ trans('company.validation.email_exists') }}',
    },
    stepObj,
    pagination = {
        value: {{ $pagination }},
        previous: `{!! trans('buttons.pagination.previous') !!}`,
        next: `{!! trans('buttons.pagination.next') !!}`,
    },
    validateTitle = function() {
            $('#name').valid();
        }
    function readURL(input, selector) {
        if (input != null && input.files.length > 0) {
            var reader = new FileReader();
            reader.onload = function (e) {
                // Validation for image max height / width and Aspected Ratio
                var image = new Image();
                image.src = e.target.result;
                image.onload = function () {
                    var imageWidth = $(input).data('width');
                    var imageHeight = $(input).data('height');
                    var ratio = $(input).data('ratio');
                    var round = $(input).data('round');
                    if(round == undefined) {
                        round = 'no';
                    }
                    var aspectedRatio = ratio;
                    var ratioSplit = ratio.split(':');
                    var newWidth = ratioSplit[0];
                    var newHeight = ratioSplit[1];
                    if(round == 'yes') {
                        var ratioGcd = gcdRound(this.width, this.height, newHeight, newWidth);
                    } else {
                        var ratioGcd = gcd(this.width, this.height, newHeight, newWidth);
                    }
                    if (($(input).data('previewelement') != '#emailheader_preview') && ((this.width < imageWidth && this.height < imageHeight) || ratioGcd != aspectedRatio)) {
                        $(input).empty().val('');
                        $(input).parent('div').find('.custom-file-label').html('Choose File');
                        $(input).parent('div').find('.invalid-feedback').remove();
                        $(selector).removeAttr('src');
                        toastr.error(message.upload_image_dimension);
                        readURL(null, selector);
                    }
                    if (($(input).data('previewelement') == '#emailheader_preview') && (this.width < imageWidth || this.height < imageHeight  || ratioGcd != aspectedRatio)) {
                        $(input).empty().val('');
                        $(input).parent('div').find('.custom-file-label').html('Choose File');
                        $(input).parent('div').find('.invalid-feedback').remove();
                        $(selector).removeAttr('src');
                        toastr.error(message.upload_image_dimension);
                        readURL(null, selector);
                    }
                }
                $(selector).attr('src', e.target.result);
            }
            reader.readAsDataURL(input.files[0]);
        } else {
            $(selector).removeAttr('src');
        }
    }

    $(document).ready(function() {
        $(function() {
            var hideContent = `{{!is_null($recordData->parent_id)}}`;
            if(hideContent){
                $('#hidecontent').attr('disabled', true);
                $('#exclude_gender_and_dob').attr('disabled', true);
            }

            @if(!$recordData->is_reseller && !is_null($recordData->parent_id))
                $('#manage_the_design_change').prop('disabled', true);
            @endif

            //$('#companyplanHidden').prop('disabled', false);

            $(document).on('click', '.edit_moderator', function(e) {
                e.preventDefault();
                var moderatorId = ($(this).data('id') || 0);
                var modetype = ($(this).data('modetype') || 'existing');
                $("#first_name_"+moderatorId).show();
                $("#span_first_name_"+moderatorId).hide();
                $("#last_name_"+moderatorId).show();
                $("#span_last_name_"+moderatorId).hide();
                if(modetype == 'new'){
                    $("#email_"+moderatorId).show();
                    $("#span_email_"+moderatorId).hide();
                }else{
                    $("#email_"+moderatorId).hide();
                    $("#span_email_"+moderatorId).show();
                }
                $("#save_moderator_"+moderatorId).show();
                $("#edit_moderator_"+moderatorId).hide();
            });

            var editor = tinymce.init({
                selector: "#contact_us_description",
                branding: false,
                menubar:false,
                statusbar: false,
                plugins: "code,link,lists,advlist",
                toolbar: 'formatselect | bold italic forecolor backcolor permanentpen formatpainter alignleft aligncenter alignright alignjustify  | numlist bullist outdent indent | removeformat | code | link',
                forced_root_block : true,
                paste_as_text : true,
                setup: function (editor) {
                    editor.on('change redo undo', function () {
                        tinymce.triggerSave();
                        var editor = tinymce.get('contact_us_description');
                        var content = $(editor.getContent()).text().replace(/[\r\n]+/g, "").trim();
                        var contentLength = $(editor.getContent()).text().replace(/[\r\n]+/g, "").trim().length;
                        var patt = /^(^([^<>$#@^]*))+$/;
                        $('#contact_us_description-error').hide();
                        $('#contact_us_description-format-error').hide();
                        $('.tox-tinymce').removeClass('is-invalid').css('border-color', '');
                        if (contentLength >  300) {
                            $('#contact_us_description-error').show();
                            $('#contact_us_description-format-error').hide();
                            $('.tox-tinymce').addClass('is-invalid').css('border-color', '#f44436');
                            $('#contact_us_description-error').addClass('invalid-feedback');
                            $('#contact_us_description-format-error').removeClass('invalid-feedback');
                        } else if (!patt.test(content)) {
                            $('#contact_us_description-error').hide();
                            $('#contact_us_description-format-error').show();
                            $('.tox-tinymce').addClass('is-invalid').css('border-color', '#f44436');
                            $('#contact_us_description-error').removeClass('invalid-feedback');
                            $('#contact_us_description-format-error').addClass('invalid-feedback');
                        } else {
                            $('#contact_us_description-error').hide();
                            $('#contact_us_description-format-error').hide();
                            $('.tox-tinymce').addClass('is-invalid').css('border-color', '');
                            $('#contact_us_description-error').removeClass('invalid-feedback');
                            $('#contact_us_description-format-error').removeClass('invalid-feedback');
                        }
                    });
                }
            });

            if(roleGroup != 'zevo'){
                tinymce.activeEditor.setMode('readonly');
            }

            $(document).on('click', '.save_moderator', function(e) {
                e.preventDefault();
                var moderatorId = ($(this).data('id') || 0);
                var first_name = $("#first_name_"+moderatorId).val().trim();
                var last_name = $("#last_name_"+moderatorId).val().trim();
                var email      = $("#email_"+moderatorId).val().trim();
                $('#first_name_'+moderatorId+'-error').remove();
                $('#last_name_'+moderatorId+'-error').remove();
                $('#email_'+moderatorId+'-error').remove();
                var res = [];
                var emailexists = [];

                var getAllPreviousEmails =  $('.email').map(function() {
                    return this.value;
                }).get();
                
                /*var getAllPreviousEmails = getAllPreviousEmails.filter(function(el){
                return el != email;
                });*/
                getAllPreviousEmails.splice(moderatorId,1);
                var arraycontainsturtles = (getAllPreviousEmails.indexOf(email));

                if(email!= ''){
                    $.ajax({
                        url: emailValidateURL,
                        method: 'post',
                        data: {
                            'email' : email,
                            'moderatorId' : moderatorId ,
                        },
                        async: false,
                        //dataType: 'json',
                        success: function(result) {
                            if(result == 'exists'){
                                emailexists.push("email");
                                if(emailexists.length > 0 || arraycontainsturtles != -1){
                                    $('#email_'+moderatorId+'-error').remove();
                                    $('#email_'+moderatorId).after('<div id="email_'+moderatorId+'-error" class="error error-feedback">'+message.email_exists+'</div>');
                                }
                            }else if(result == 'disposable'){
                                emailexists.push("email");
                                if(emailexists.length > 0 || arraycontainsturtles != -1){
                                    $('#email_'+moderatorId+'-error').remove();
                                    $('#email_'+moderatorId).after('<div id="email_'+moderatorId+'-error" class="error error-feedback">Disposable email not allow</div>');
                                }
                            } else {
                                removeFrmArr(emailexists, 'email');
                            }
                        },
                        error: function(data){
                            //error
                        }
                    });
                }
                var regEx = /^[a-z \s]+$/i;
                if(first_name == ''){
                    $('#first_name_'+moderatorId).after('<div id="first_name_'+moderatorId+'-error" class="error error-feedback">'+message.first_name_required+'</div>');
                    res.push("first_name");
                }else if(first_name.length < 2){
                    $('#first_name_'+moderatorId).after('<div id="first_name_'+moderatorId+'-error" class="error error-feedback">'+message.min_2_characters_first_name+'</div>');
                    res.push("first_name");
                }else if((regEx.test(first_name)) == false) {
                    $('#first_name_'+moderatorId).after('<div id="first_name_'+moderatorId+'-error" class="error error-feedback">'+message.valid_first_name+'</div>');
                    res.push("first_name");
                }else{
                    removeFrmArr(res, 'first_name');
                }

                if(last_name == ''){
                    $('#last_name_'+moderatorId).after('<div id="last_name_'+moderatorId+'-error" class="error error-feedback">'+message.last_name_required+'</div>');
                    res.push("last_name");
                }else if(last_name.length < 2){
                    $('#last_name_'+moderatorId).after('<div id="last_name_'+moderatorId+'-error" class="error error-feedback">'+message.min_2_characters_last_name+'</div>');
                    res.push("last_name");
                }else if((regEx.test(last_name)) == false) {
                    $('#last_name_'+moderatorId).after('<div id="last_name_'+moderatorId+'-error" class="error error-feedback">'+message.valid_last_name+'</div>');
                    res.push("last_name");
                }else{
                    removeFrmArr(res, 'last_name');
                }
                var regexEmail = /^([\w-\.]+)@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.)|(([\w-]+\.)+))([a-zA-Z]{2,4}|[0-9]{1,3})(\]?)$/;

                if(email == ''){
                    $('#email_'+moderatorId).after('<div id="email_'+moderatorId+'-error" class="error error-feedback">'+message.email_required+'</div>');
                    res.push("email");
                }else if (!regexEmail.test(email)) {
                    $('#email_'+moderatorId).after('<div id="email_'+moderatorId+'-error" class="error error-feedback">'+message.valid_email+'</div>');
                    res.push("email");
                }else if ( arraycontainsturtles != -1 && emailexists.length == 0) {
                    $('#email_'+moderatorId).after('<div id="email_'+moderatorId+'-error" class="error error-feedback">'+message.email_exists+'</div>');
                    res.push("email");
                }else  {
                    removeFrmArr(res, 'email');
                }
                if(res.length <= 0 &&  emailexists.length <=0){
                    $("#span_first_name_"+moderatorId).html(first_name);
                    $("#first_name_"+moderatorId).attr('value',first_name);
                    $("#span_last_name_"+moderatorId).html(last_name);
                    $("#last_name_"+moderatorId).attr('value',last_name);
                    $("#span_email_"+moderatorId).html(email);
                    $("#email_"+moderatorId).attr('value',email);
                    $("#first_name_"+moderatorId).hide();
                    $("#span_first_name_"+moderatorId).show();
                    $("#last_name_"+moderatorId).hide();
                    $("#span_last_name_"+moderatorId).show();
                    $("#email_"+moderatorId).hide();
                    $("#span_email_"+moderatorId).show();
                    $("#save_moderator_"+moderatorId).hide();
                    $("#edit_moderator_"+moderatorId).show();
                }

                if($('.error-feedback').length <= 0){
                    $('.actions ul li a[href="#next"]').removeClass("disabled");
                }else{
                    $('.actions ul li a[href="#next"]').addClass("disabled");
                }
            });
            let rowno = 0;
            $(document).on('click', '.add_moderator', function(e) {
                e.preventDefault();
                    var newRow = `<tr class="moderators-wrap" data-row-id="${rowno}">
                    <td class="align-top"><span style="display:none" id="span_first_name_${rowno}"></span><input type="text" maxlength="50" id="first_name_${rowno}" class="form-control" placeholder="First Name" name="first_name[${rowno}]" value=""><input type="hidden" id="id_${rowno}" class="form-control" name="id[${rowno}]" value="id${rowno}"></td>
                    <td class="align-top"><span style="display:none" id="span_last_name_${rowno}"></span><input type="text" maxlength="50" id="last_name_${rowno}" class="form-control" placeholder="Last Name" name="last_name[${rowno}]" value=""></td>
                    <td class="align-top"><span style="display:none" id="span_email_${rowno}"></span><input type="text" class="form-control email" id="email_${rowno}" name="email[${rowno}]" placeholder="Email" maxLength="50" value=""></td>
                    <td class=" no-sort text-center">
                        <a class="action-icon edit_moderator" style="display:none;" id="edit_moderator_${rowno}" href="javascript:void(0);" title="Edit" data-id="${rowno}" data-modetype="new">
                            <i class="far fa-edit">
                            </i>
                        </a>
                        <a class="action-icon save_moderator" id="save_moderator_${rowno}" href="javascript:void(0);" title="Save" data-id="${rowno}">
                            <i class="far fa-save">
                            </i>
                        </a>
                        <a class="action-icon delete_moderator danger" href="javascript:void(0);" title="Delete" data-id="${rowno}">
                            <i class="far fa-trash-alt">
                            </i>
                        </a></td>
                    </tr>`;
                $('#moderatorsManagment tr:last').after(newRow);
                rowno++;
            });

        var _sub_domain = $('#sub_domain').val().trim();
        if(_sub_domain != "") {
            var domainUrl = generateBrandingUrl(_sub_domain, "{{ app()->environment() }}");
            if(domainUrl != "" && domainUrl != undefined) {
                $("#subdomainUrl")
                    .attr({
                        href: domainUrl,
                        target: '_blank'
                    })
                    .text(domainUrl);
            }
        }

        $('#assigned_roles').on('select2:unselecting', function(e) {
            if($.inArray(e.params.args.data.id, company_roles) !== -1 && $.inArray(parseInt(e.params.args.data.id), users_attached_role) !== -1) {
                $('.toast').remove();
                toastr.error("Role can not be remove as it's associated with the company user.");
                e.preventDefault();
            }
        });

        $("#subscription_start_date,#subscription_end_date").keypress(function(e) {
            e.preventDefault();
        });

        $('#subscription_start_date').datepicker({
            startDate: start_start_date,
            endDate: max_end_date,
            autoclose: true,
            todayHighlight: true,
            format: 'yyyy-mm-dd',
        }).on('changeDate', function () {
            let subEndDate = $(this).val();
            subEndDate = moment(subEndDate, "YYYY-MM-DD");
            subEndDate.add(1, 'days');
            $('#subscription_end_date').datepicker('setStartDate', new Date(subEndDate));
            $('#subscription_start_date, #subscription_end_date').valid();            $('#subscription_start_date, #subscription_end_date').valid();
        });

        $('#subscription_end_date').datepicker({
            @if(!$recordData->is_reseller && !is_null($recordData->parent_id))
            startDate: start_start_date,
            endDate: max_end_date,
            @else
            startDate: end_start_date,
            endDate: max_end_date,
            @endif
            autoclose: true,
            todayHighlight: true,
            format: 'yyyy-mm-dd',
        }).on('changeDate', function () {
            $('#subscription_start_date').datepicker('setEndDate', new Date($(this).val()));
            $('#subscription_start_date, #subscription_end_date').valid();
        });



        $(document).on('keyup change', '#sub_domain', function(e) {
            var _value = $(this).val().trim();
            if(_value != '' && $("#sub_domain").valid()) {
                var domainUrl = generateBrandingUrl(_value, "{{ app()->environment() }}");
                $("#subdomainUrl")
                    .attr({
                        href: domainUrl,
                        target: '_blank'
                    })
                    .text(domainUrl);
            } else {
                $("#subdomainUrl")
                    .attr({
                        href: 'javascript:void(0);',
                        target: '_self'
                    })
                    .text("{{ trans('labels.company.domain_preview_note') }}");
            }
        });
        // to keep county and timezone field enabled when editing locations.
        var country = $('#country').val();
        if (country != '' && country != undefined) {
            $('#country').trigger('change');
        }

        // to populate county and timezone field after redirecting with error.
        setTimeout(function(){
            var county = "{{old('state')}}";
            if (county != '' && county != undefined) {
                $('#state').select2('val', county);
            }
            var timezone = "{{old('timezone')}}";
            if (timezone != '' && timezone != undefined) {
                $('#timezone').select2('val', timezone);
            }
        }, 1000);

        $('#group_restriction').on('click', function(e){
            if(this.checked){
                $('#group_restriction_rule_block').show();
            } else {
                $('#group_restriction_rule_block').hide();
            }
        });

        $('#is_branding').on('change', function(e) {
            if($(this).is(":checked")) {
                $('#branding_wrapper').fadeIn();
            } else {
                $('#branding_wrapper').hide();
            }
        });

        $('#enable_survey').on('change', function(e) {
            if($(this).is(":checked")) {
                $('#survey_wrapper').fadeIn('slow');
            } else {
                $('#survey_wrapper').hide();
            }
        });

        $(document).on('click', '.remove-media', function(e) {
            var _action = $(this).data('action'),
                _text = $(this).data('text');
            if(_action) {
                $('#remove-media-model-box .remove-media-title').html(`Remove ${_text}`);
                $('#remove-media-model-box .remove-media-message').html(`Are you sure you want to remove ${_text}?`);
                $('#remove-media-model-box #remove_media_type').val(_action);
                $('#remove-media-model-box').modal('show');
            }
        });

        $(document).on('click', '#remove-media-confirm', function (e) {
            var _action = $('#remove-media-model-box #remove_media_type').val();
            if(_action != "") {
                var _selector = `input[type="hidden"][name="remove_branding_${_action}"]`,
                    _length = $('#companyEdit').find(_selector).length,
                    _element = _action,
                    _previewElement = $(`#${_element}`).data('previewelement');

                if(_length > 0) { $(_selector).remove(); }

                $(`#${_element}`).empty().val('');
                $(`#${_element}`).parent('div').find('.custom-file-label').html('Choose File');
                readURL(null, _previewElement);

                $('#companyEdit').prepend(`<input name="remove_${_action}" type="hidden" value="1" />`);
                $(`.remove-media[data-action="${_action}"]`).remove();
            }
            $('#remove-media-model-box').modal('hide');
        });

        $(document).on('hidden.bs.modal', '#remove-media-model-box', function (e) {
            $('#remove-media-model-box .remove-media-title, #remove-media-model-box .remove-media-message').html('');
            $('#remove-media-model-box #remove_media_type').val('');
        });

        $(document).on('change', '.select2', function(e) {
            stateUrl = stateUrl;
            tzUrl = tzUrl;
            if ($(this).val() != '' && $(this).val() != null) {
                if ($(this).attr("id") == 'country_id' && $(this).attr('data-dependent') == 'state_id') {
                    var select = $(this).attr("id");
                    var value = $(this).val();
                    var dependent = $(this).attr('data-dependent');
                    var _token = $('input[name="_token"]').val();

                    url = stateUrl.replace(':id', value);

                    $.ajax({
                        url: url,
                        method: 'get',
                        data: {
                            _token: _token
                        },
                        success: function(result) {
                            $('#' + dependent).empty();
                            $('#' + dependent).attr('disabled', false);
                            $('#' + dependent).val('').trigger('change').append('<option value="">Select</option>');
                            $('#' + dependent).removeClass('is-valid');
                            $.each(result.result, function(key, value) {
                                $('#' + dependent).append('<option value="' + value.id + '">' + value.name + '</option>');
                            });
                            if (Object.keys(result.result).length == 1) {
                                $.each(result.result, function(key, value) {
                                    $('#' + dependent).select2('val', value.id);
                                });
                            }
                        }
                    })
                }
            }

            if ($(this).val() != '' && $(this).val() != null) {
                if ($(this).attr("id") == 'country_id' && $(this).attr('target-data') == 'timezone') {
                    var select = $(this).attr("id");
                    var value = $(this).val();
                    var tzDependent = $(this).attr('target-data');
                    var _token = $('input[name="_token"]').val();

                    url = tzUrl.replace(':id', value);

                    $.ajax({
                        url: url,
                        method: 'get',
                        data: {
                            _token: _token
                        },
                        success: function(result) {
                            $('#' + tzDependent).empty();
                            $('#' + tzDependent).attr('disabled', false);
                            $('#' + tzDependent).val('').trigger('change').append('<option value="">Select</option>');
                            $('#' + tzDependent).removeClass('is-valid');
                            $.each(result.result, function(key, value) {
                                $('#' + tzDependent).append('<option value="' + value.id + '">' + value.name + '</option>');
                            });
                            if (Object.keys(result.result).length == 1) {
                                $.each(result.result, function(key, value) {
                                    $('#' + tzDependent).select2('val', value.id);
                                });
                            }
                        }
                    })
                }
            }
        });

        $(document).on('change', '#logo, #login_screen_logo, #login_screen_background, #email_header, #portal_logo_main, #portal_logo_optional, #portal_homepage_logo_right, #portal_homepage_logo_left, #portal_background_image, #portal_favicon_icon, #contact_us_image, #appointment_image', function (e) {
            var previewElement = $(this).data('previewelement');
            if(e.target.files.length > 0) {
                var id = e.target.id,
                    fileName = e.target.files[0].name,
                    allowedMimeTypes = ['image/png', 'image/jpeg', 'image/jpg'];

                if (!allowedMimeTypes.includes(e.target.files[0].type)) {
                    toastr.error("{{trans('labels.common_title.image_valid_error')}}");
                    $(e.currentTarget).empty().val('');
                    $(this).parent('div').find('.custom-file-label').html('Choose File');
                    readURL(null, previewElement);
                } else if ($.inArray(id, ['logo', 'login_screen_logo', 'portal_logo_main', 'portal_logo_optional', 'portal_homepage_logo_right', 'portal_homepage_logo_left', 'portal_favicon_icon', 'contact_us_image', 'appointment_image']) !== -1 && e.target.files[0].size > 2097152) {
                    toastr.error("{{trans('labels.common_title.image_size_2M_error')}}");
                    $(e.currentTarget).empty().val('');
                    $(this).parent('div').find('.custom-file-label').html('Choose File');
                    readURL(null, previewElement);
                } else if ($.inArray(id, ['login_screen_background', 'portal_background_image']) !== -1 && e.target.files[0].size > 2097152) {
                    toastr.error("{{trans('labels.common_title.image_size_5M_error')}}");
                    $(e.currentTarget).empty().val('');
                    $(this).parent('div').find('.custom-file-label').html('Choose File');
                    readURL(null, previewElement);
                } else {
                    readURL(e.target, previewElement);
                    $(this).parent('div').find('.custom-file-label').html(fileName);
                    if(id == 'login_screen_logo') { $(`input[name="remove_branding_logo"], .remove-branding-media[data-action="logo"]`).remove(); }
                    if(id == 'login_screen_background') { $(`input[name="remove_branding_background"], .remove-branding-media[data-action="background"]`).remove(); }
                }
            } else {
                $(this).parent('div').find('.custom-file-label').html('Choose File');
                readURL(null, previewElement);
            }
        });

        $(document).on('change', '#is_premium', function(e) {
            var hasPremium = $(this).is(":checked");
            $('#survey').empty().append('<option value="0">Loading....<option>').val('0');
            $('#survey').select2('destroy').select2();
            $.ajax({
                url: '{{ route('admin.ajax.getSurveys') }}',
                type: 'GET',
                dataType: 'html',
                data: {hasPremium: hasPremium},
            })
            .done(function(data) {
                $('#survey').empty().append(data).val('');
            })
            .fail(function() {
                alert('Failed to load surveys');
            })
            .always(function() {
                $('#survey').select2('destroy').select2();
            });

        });
        if(roleGroup == 'reseller'){
            // Showing tooptip for subscription end date field for child companies
            $('#subscription_end_date_tooltip').removeClass('d-none');
        }
    });

    companyEditForm.validate().settings.ignore = ':disabled,:hidden';
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    $(function() {
        $('#allow_app').on('change', function(e) {
            if($(this).is(":checked")) {
                $('#disable_sso').prop({'checked': false, 'disabled': true});
            } else {
                $('#disable_sso').prop({'disabled': false});
            }
        });

        if($("#allow_app").is(":checked") == true){
            $('#disable_sso').prop({'checked': false, 'disabled': true});
        }

        /*if($('#isChild').val() == 1){
                $('#enable_survey').prop({'disabled': true}).trigger('change');
        }*/
        
        $('.actions ul li a[href="#previous"]').addClass("disabled");
        $("#group_content").treeMultiselect({
            enableSelectAll: true,
            searchable: true,
            startCollapsed: true,
            onChange: function (allSelectedItems, addedItems, removedItems) {
                var selectedMembers = $('#group_content').val().length;
                if (selectedMembers == 0) {
                    $('#companyEdit').valid();
                    $('#group_content-min-error').hide();
                    $('#group_content-error').show();
                    $('.tree-multiselect').css('border-color', '#f44436');
                } else {
                    $('#group_content-error').hide();
                    $('#group_content-min-error').hide();
                    $('#group_content-max-error').hide();
                    $('.tree-multiselect').css('border-color', '#D8D8D8');
                }
            }
        });

        $("#survey_roll_out_time").timepicker({
            'showDuration': true,
            'timeFormat': 'H:i',
            'step': 15,
            'useSelect': true,
        });

       
        if($("#dt_wellbeing_sp_ids :selected").length == 0){
            $("#staffService").hide();
        }

        $("#dt_wellbeing_sp_ids").on("select2:unselect", function (e) {
            var value = e.params.data.id;
            $("#staffServiceManagment #staff-row-"+value).remove();
            $("#staffServiceManagment #"+value).remove();
            $('#wshours-'+value).remove();
            if($('#staffServiceManagment tbody tr').length == 0){
                $("#staffService").hide();
                $("#dt_wellbeing_sp_ids-error").show();
                var _hoursby = $('#set_hours_by').val();
                var _availabilityby = $('#set_availability_by').val();
                if (_hoursby == 1 && _availabilityby == 2) {
                    $('#set_wellbeing_hours').hide();
                }
            }
            $("span.ws_"+value).remove();
            $("#dtspecificwshours tr[id='ws_" + value + "']").remove();
            $('.ws_hidden_fields > input[value='+ value +']').remove();

            var list = $(".wsIdsLocationWise").val().split(',');
            list.splice(list.indexOf(value), 1);
            list.join(',');
            $(".wsIdsLocationWise").val(list);
            $('.location_specific_wellbeing_sp_ids option[value='+value+']').detach();
            
            $("#locationHoursManagment").find('tbody > tr > td.location_ws_column').map(function(){ 
                var spanCount = $('#'+this.id).find('span').length;
                if(spanCount == 1){
                    $('#'+this.id).find('span').length
                    var spanText = $('#'+this.id).find('span').text();
                    $('#'+this.id).find('span').html(spanText.replace(/^,/, ''))
                }
            })
        });
        
         $(document).on('select2:selecting', '#dt_wellbeing_sp_ids', function(e) {
            var _value = e.params.args.data.id;
            var wbsOption = e.params.args.data.text;
            var _token = $('input[name="_token"]').val();
            var html = '';
            var wellbeinghoursHtml = '';

       
            $.ajax({
                url: getStaffServices,
                method: 'post',
                data: {
                    _token: _token,
                    value: _value,
                },
                success: function(result) {
                    $("#staffService").show();
                    $.each(result.staffServices, function (key, value) 
                    {
                        html+= '<tr id='+key+'><td>' + value.staffName + '</td><td id="staff-service-'+key+'">';
                        wellbeinghoursHtml += '<tr id="wshours-'+key+'"">';
                        wellbeinghoursHtml += '<td>' + value.staffName + '</td><td><a class="action-icon text-danger slot-specific" title="{{ trans('buttons.general.tooltip.no-avability-set') }}"><i class="far fa-exclamation-circle"></i></a><a class="action-icon bs-calendar-slidebar" href="javascript:;" id="'+key+'" data-toggle="canvas" data-target="#bs-canvas-right" aria-expanded="false" aria-controls="bs-canvas-right" title="{{ trans('buttons.general.tooltip.edit') }}"><i class="far fa-edit"></i></a><a class="action-icon" href="javascript:;" title=""> - </a></td>';
                        $.each(value.services, function (k, v) 
                        { 
                            html+= '<span class="service-badge" id="service_'+key+'_'+k+'">'+v+'<i class="fal fa-times" data-sid="'+k+'"  data-wsid="'+key+'"></i><input type="hidden" name="service['+key+'][]" value="'+k+'"></span>';
                        });
                        html+= '</td></tr>';
                        wellbeinghoursHtml += '</td></tr>';
                        // $("#locationHoursManagment").find('tbody > tr > td.location_ws_column').append("<span class='ws_"+key+" badge bg-secondary'>"+value.staffName+"</span>");
                    });

                    $("#locationHoursManagment").find('tbody > tr > td.location_ws_column').map(function(){ 
                        var spanCount = $('#'+this.id).find('span').length;
                        if(spanCount == 1){
                            $('#'+this.id).find('span').length
                            var spanText = $('#'+this.id).find('span').text();
                            $('#'+this.id).find('span').html(spanText.replace(/^,/, ''))
                        }
                    })
                    $('#staffServiceManagment').append(html);
                    $('#dtwellbeinghours tbody').append(wellbeinghoursHtml);
                    $("#dt_wellbeing_sp_ids-error").hide();
                    var _hoursby = $('#set_hours_by').val();
                    var _availabilityby = $('#set_availability_by').val();
                    if (_hoursby == 1 && _availabilityby == 2) {
                        $('#set_wellbeing_hours').show();
                    }
                }
            });
        });

        $(document).on('click', '#staffServiceManagment .fa-times', function(e) {
            var sid = ($(this).data('sid') || 0);
            var wsid = ($(this).data('wsid') || 0);
            $("#service_"+wsid+"_"+sid).remove();
            if($("td#staff-service-"+wsid).html().trim().length == 0){
                $("#staff-row-"+wsid).remove();
                $('#wshours-'+wsid).remove();
                $("#"+wsid).remove();
                var option_value = $('#dt_wellbeing_sp_ids option[value="'+ wsid +'"]');
                option_value.prop('selected', false);
                $('#dt_wellbeing_sp_ids').trigger('change.select2');
            }
            
            if($('#staffServiceManagment tbody tr').length == 0){
                $("#dt_wellbeing_sp_ids").val('').select2()
                $("#staffService").hide();
            }
        });

        $('#moderatorsManagment').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: moderatorDatatableURL,
                data: {
                    status: 1,
                    recordName: $('#recordName').val(),
                    recordEmail: $('#recordEmail').val(),
                    getQueryString: window.location.search
                },
            },
            columns: [
            {
                data: 'first_name',
                name: 'first_name',
                className: "align-top",
                render: function(data, type, row) {
                    return `<span id="span_first_name_${row.id}">${row.first_name}</span>`
                    +`<input type="text" placeholder="First Name" class="form-control" maxlength="50" style="display:none;" id="first_name_${row.id}" name="first_name[${row.id}]" value="${row.first_name}">`
                    +`<input type="hidden" class="form-control" style="display:none;" id="id_${row.id}" name="id[${row.id}]" value="${row.id}">`; 
                     
                }

            },
            {
                data: 'last_name',
                name: 'last_name',
                className: "align-top",
                render: function(data, type, row) {
                    return `<span id="span_last_name_${row.id}">${row.last_name}</span>`
                    +`<input type="text" placeholder="Last Name" class="form-control" maxlength="50" style="display:none;" id="last_name_${row.id}" name="last_name[${row.id}]" value="${row.last_name}">`; 
                }

            }, {
                data: 'email',
                name: 'email',
                className: "align-top",
                render: function(data, type, row) {
                    return `<span id="span_email_${row.id}">${row.email}</span>`
                    +`<input type="text" placeholder="Email" class="form-control" style="display:none;" id="email_${row.id}" name="email[${row.id}]" value="${row.email}">`; 
                }
            },
            {
                data: 'action',
                name: 'action',
                className: 'no-sort text-center',
                render: function(data, type, row, meta) {
                    var delete_class = "";
                    if(meta.row == 0){
                        delete_class= "hide";
                    }
                    return `<a class="action-icon edit_moderator" id="edit_moderator_${row.id}" href="javascript:void(0);" title="Edit" data-id="${row.id}">
                        <i class="far fa-edit">
                        </i>
                    </a>
                    <a class="action-icon save_moderator" id="save_moderator_${row.id}" style="display:none;" href="javascript:void(0);" title="Save" data-id="${row.id}">
                        <i class="far fa-save">
                        </i>
                    </a>
                    <a class="action-icon delete_moderator danger ${delete_class}" href="javascript:void(0);" title="Delete" data-id="${row.id}">
                        <i class="far fa-trash-alt">
                        </i>
                    </a>`;
                }
            }
            ],
            "fnCreatedRow": function( data, type, row){
                $(data).addClass('moderators-wrap')
                $(data).attr('data-row-id', type.id )
            },
            paging: false,
            dom: '<<"pagination-top"lB><t><"pagination-wrap"<"pagination-left"i>p>',
            //pageLength: pagination.value,
            lengthChange: false,
            searching: false,
            ordering: false,
            /*order: [
                [0, 'desc']
            ],*/
            info: false,
            autoWidth: false,
            columnDefs: [{
                targets: 'no-sort',
                orderable: false,
            }],
            language: {
                /*paginate: {
                    previous: pagination.previous,
                    next: pagination.next,
                }*/
            },
        });
    });

    if($("#is_branding").is(":checked") == true ){
        $(".domainBranding-content").removeClass('d-none');
        if($("#companyType").val() != 'zevo'){
            $(".portalBranding-content").removeClass('d-none');
        }
    }else{
        $(".domainBranding-content").addClass('d-none');
        $(".portalBranding-content").addClass('d-none');
    }

    if($("#enable_survey").is(":checked") == true){
        $(".enableSurvey-content").removeClass('d-none');
    }else{
        $(".enableSurvey-content").addClass('d-none');
    }

    $(document).on('change', 'input[id="is_branding"]', function(e) {
        if($(this).is(":checked")) {
            $(".domainBranding-content").removeClass('d-none');
        } else {
            $(".domainBranding-content").addClass('d-none');
        }
    });

    $(document).on('change', 'input[id="enable_survey"]', function(e) {
        if($(this).is(":checked")) {
            $(".enableSurvey-content").removeClass('d-none');
        } else {
            $(".enableSurvey-content").addClass('d-none');
        }
    });

    if($('#companyType').val() != 'zevo'){
        checkDtExists();
    }else{
        if( $('#companyplan').find(':selected').text() == 'Digital Therapy with Challenge' || $('#companyplan').find(':selected').text() == 'Digital Therapy'){
        $(".enableDigitalTherapy-content").removeClass('d-none');
        $(".dt-banners").removeClass('d-none');
        var slug = $('#companyplan').find(':selected').text();
        $("#companyplanSlug").val(slug.split(' ').join('-').toLowerCase()); 
        $("#dtExistsHidden").val(1);
        }else{
            $(".enableDigitalTherapy-content").addClass('d-none');
            $(".dt-banners").addClass('d-none');
            $("#dtExistsHidden").val(0);
        }  
    }
    //
    /*var dtExists = false;
    $.ajax({
        url: isDtIncludedInCPlan,
        method: 'get',
        data: {
            'planId': $('#companyplan').val()
        },
        success: function(result) {
            if(result == 1){
                $(".enableDigitalTherapy-content").removeClass('d-none');
                $(".dt-banners").removeClass('d-none');
                var slug = $("#companyplan").find(':selected').text();
                $("#companyplanSlug").val(slug.split(' ').join('-').toLowerCase());
                dtExists = true;
            } else {
                $(".enableDigitalTherapy-content").addClass('d-none');
                $(".dt-banners").addClass('d-none');
                dtExists = false;
            }
        }
    });*/

    /*if( $('#companyplan').find(':selected').text() == 'Digital Therapy with Challenge' || $('#companyplan').find(':selected').text() == 'Digital Therapy'){
        $(".enableDigitalTherapy-content").removeClass('d-none');
        $(".dt-banners").removeClass('d-none');
        var slug = $('#companyplan').find(':selected').text();
        $("#companyplanSlug").val(slug.split(' ').join('-').toLowerCase()); 
        $("#dtExistsHidden").val(1);
    }else{
        $(".enableDigitalTherapy-content").addClass('d-none');
        $(".dt-banners").addClass('d-none');
        $("#dtExistsHidden").val(0);
    }*/
    /*if($('#companyplan').find(':selected').text() == 'Digital Therapy with Challenge' || 
        $('#companyplan').find(':selected').text() == 'Digital Therapy'  ||
        $('#companyplan').find(':selected').text() == 'Portal Digital Therapy' || 
        $('#companyplan').find(':selected').text() == 'Portal Standard with Digital Therapy'){
        $(".enableDigitalTherapy-content").removeClass('d-none');
        $(".dt-banners").removeClass('d-none');
        var slug = $('#companyplan').find(':selected').text();
        $("#companyplanSlug").val(slug.split(' ').join('-').toLowerCase());
    }else{
        $(".enableDigitalTherapy-content").addClass('d-none');
        $(".dt-banners").addClass('d-none');
    }*/

    /*$(document).on('change', 'input[id="eap_tab"]', function(e) {
        if($(this).is(":checked") || ($("#companyplan").val() == 1 || $("#companyplan").val() == 2)) {
            $(".enableDigitalTherapy-content").removeClass('d-none');
        } else {
            $(".enableDigitalTherapy-content").addClass('d-none');
        }
    });*/

    if($("#companyType").val() == 'zevo'){
        $(document).on('change', '#companyplan', function(e) {
            if($(this).find(':selected').text() == 'Digital Therapy with Challenge' || $(this).find(':selected').text() == 'Digital Therapy'){
                $(".enableDigitalTherapy-content").removeClass('d-none');
                $("#dtExistsHidden").val(1);
            }else{
                $(".enableDigitalTherapy-content").addClass('d-none');
                $("#dtExistsHidden").val(0);
            }
            var slug = $(this).find(':selected').text();
            $("#companyplanSlug").val(slug.split(' ').join('-').toLowerCase());
        });
    }
    var dtExists = false;
    if($("#companyType").val() != 'zevo'){
        var _token = $('input[name="_token"]').val();
        $(document).on('change', '#companyplan', function(e) {
                checkDtExists();
                /*$.ajax({
                url: isDtIncludedInCPlan,
                method: 'get',
                data: {
                    'planId': $(this).val()
                },
                success: function(result) {
                    if(result == 1){
                        $(".enableDigitalTherapy-content").removeClass('d-none');
                        $(".dt-banners").removeClass('d-none');
                        var slug = $("#companyplan").find(':selected').text();
                        $("#companyplanSlug").val(slug.split(' ').join('-').toLowerCase());
                        dtExists = true;
                    } else {
                        $(".enableDigitalTherapy-content").addClass('d-none');
                        $(".dt-banners").addClass('d-none');
                        dtExists = false;
                    }
                }
            });*/
            /*if($(this).find(':selected').text()  == 'Portal Digital Therapy' || $(this).find(':selected').text()  == 'Portal Standard with Digital Therapy'){
                $(".enableDigitalTherapy-content").removeClass('d-none');
                $(".dt-banners").removeClass('d-none');
            }else{
                $(".enableDigitalTherapy-content").addClass('d-none');
                $(".dt-banners").addClass('d-none');
            }
            var slug = $(this).find(':selected').text();
            $("#companyplanSlug").val(slug.split(' ').join('-').toLowerCase());*/
        });
    }

    $(document).on('click', '.delete_moderator', function(e) {
        // var dt = $('#moderatorsManagment').DataTable();
        // var totalRows = dt.data().count();
        // if(totalRows == 1){
        //     toastr.error("There must be at lease one moderator for the company");
        //     e.preventDefault();
        // }else{
            var _id = ($(this).data('id') || 0);
            $('#remove-moderator-box').modal('show');
            $('#remove-moderator-box').attr('data-id',_id);
        //}
    });

    $(document).on('click', '#remove-moderator-confirm', function(e) {
        var id = $("#remove-moderator-box").attr("data-id");
        if (id) {
            $("tr").filter("[data-row-id='" + id + "']").remove();
            $('#remove-moderator-box').modal('hide');
                // $('.actions ul li a[href="#next"]').removeClass("disabled");
            if($('.error-feedback').length > 0){
                $('.actions ul li a[href="#next"]').addClass("disabled");
            }
        }
    });

    stepObj = $("#companyAddStep").steps({
        headerTag: "h3",
        bodyTag: "div",
        transitionEffect: "fade",
        autoFocus: true,
        enableCancelButton: false,
        startIndex: 0,
        labels: {
            next: "Next",
            previous: "Previous",
            finish: "Finish",
            cancel: "Cancel",
        },
        onStepChanging: function(event, currentIndex, newIndex) {
            var portalTabValid  = true;
            var dtTabValid      = true;
            var today = new Date().getFullYear()+'-'+("0"+(new Date().getMonth()+1)).slice(-2)+'-'+("0"+new Date().getDate()).slice(-2)

            if(currentIndex == 3){
                
                var editor = tinymce.get('contact_us_description');
                var content = $(editor.getContent()).text().replace(/[\r\n]+/g, "").trim();
                var contentLength = $(editor.getContent()).text().replace(/[\r\n]+/g, "").trim().length;
                var patt = /^(^([^<>$#@^]*))+$/;

                if (contentLength >  300) {
                    portalTabValid = false;
                    $('#contact_us_description-error').show();
                    $('#contact_us_description-format-error').hide();
                    $('.tox-tinymce').addClass('is-invalid').css('border-color', '#f44436');
                    $('#contact_us_description-error').addClass('invalid-feedback');
                    $('#contact_us_description-format-error').removeClass('invalid-feedback');
                } else if (!patt.test(content)) {
                    portalTabValid = false;
                    $('#contact_us_description-error').hide();
                    $('#contact_us_description-format-error').show();
                    $('.tox-tinymce').addClass('is-invalid').css('border-color', '#f44436');
                    $('#contact_us_description-error').removeClass('invalid-feedback');
                    $('#contact_us_description-format-error').addClass('invalid-feedback');
                } else {
                    $('#contact_us_description-error').hide();
                    $('#contact_us_description-format-error').hide();
                    $('.tox-tinymce').addClass('is-invalid').css('border-color', '');
                    $('#contact_us_description-error').removeClass('invalid-feedback');
                    $('#contact_us_description-format-error').removeClass('invalid-feedback');
                    portalTabValid = true;
                }
            }
            // Check the business hours validation for DT tab
            if(
            ($("#enable_survey").is(":checked") == true && currentIndex == 6 && $("#dtExistsHidden").val() == true && $("#companyType").val() != 'zevo') || 
            ($("#enable_survey").is(":checked") == false && currentIndex == 5 && $("#dtExistsHidden").val() == true && $("#companyType").val() != 'zevo') || 
            ($("#enable_survey").is(":checked") == false && $("#is_branding").is(":checked") == true && currentIndex == 4 && $("#dtExistsHidden").val() == true && $("#companyType").val() == 'zevo') ||
            ($("#enable_survey").is(":checked") == true && $("#is_branding").is(":checked") == true && currentIndex == 5 && $("#dtExistsHidden").val() == true && $("#companyType").val() == 'zevo') ||
            ($("#enable_survey").is(":checked") == false && $("#is_branding").is(":checked") == false && currentIndex == 3 && $("#dtExistsHidden").val() == true && $("#companyType").val() == 'zevo') ||
            ($("#enable_survey").is(":checked") == true && $("#is_branding").is(":checked") == false && currentIndex == 4 && $("#dtExistsHidden").val() == true && $("#companyType").val() == 'zevo')
            ){
                //Check data is exists for the all company and locations
                $("#slot-error").hide();
                $("#slot-wbs-error").hide();
                $("#slot-location-error").hide();
                $("#slot-wbs-error, #slot-location-error, #slot-error").html("");

                var wellbeingSp = $('#dt_wellbeing_sp_ids').val();
                if ($("#set_hours_by").val() == 1 && $("#set_availability_by").val() == 1 && $("#slots_exist").val().length == 0 && wellbeingSp.length > 0 && $("#companyType").val() != 'normal'){
                    dtTabValid = false;
                    $("#slot-error").show();
                    $("#slot-error").html("Please set business hours for atleast one week day");
                } else if ($("#set_hours_by").val() == 1 && $("#set_availability_by").val() == 2 && wellbeingSp.length > 0 && $("#companyType").val() != 'normal') {
                    var companyValid = false;
                    if($( "div.hiddenfields .general-slots" ).length){
                        $( "div.hiddenfields .general-slots" ).each(function() {
                            var specificSlotDate    = $( this ).attr( "data-date" );
                            var currentDateUpdated  = new Date(today.replace(/-/g,'/'));  
                            var specificDateUpdated = new Date(specificSlotDate.replace(/-/g,'/'));
                            if (specificDateUpdated >= currentDateUpdated) {
                                companyValid = true;
                            }
                        });
                    }
                    if(companyValid == false){
                        event.preventDefault();
                        dtTabValid = false;
                        $("#slot-wbs-error").show();
                        $("#slot-wbs-error").html("Please set business hours for atleast one wellbeing specialist");
                    }
                } else if ($("#set_hours_by").val() == 2 && $("#set_availability_by").val() == 2 && wellbeingSp.length > 0 && $("#companyType").val() != 'normal'){ //  && $(".hiddenfields .location-slots").length == 0
                    var locationValid = false;
                    if($( "div.hiddenfields .location-slots" ).length){
                        $( "div.hiddenfields .location-slots" ).each(function() {
                            var specificSlotDate    = $( this ).attr( "data-date" );
                            var currentDateUpdated  = new Date(today.replace(/-/g,'/'));  
                            var specificDateUpdated = new Date(specificSlotDate.replace(/-/g,'/'));
                            if (specificDateUpdated >= currentDateUpdated) {
                                locationValid = true;
                            }
                        });
                    }
                    if(locationValid == false){
                        dtTabValid = false;
                        event.preventDefault();
                        $("#slot-location-error").show();
                        $("#slot-location-error").html("Please set business hours for atleast one location");
                    }               
                } else if ($("#set_hours_by").val() == 2 && $("#set_availability_by").val() == 1 && $("#slotExistsForAnyLocation").val() == 1 && $(".slots-wrapper-location .preview-slot-block-location").length == 0 && wellbeingSp.length > 0 && $("#companyType").val() != 'normal'){  //&& $(".slots-wrapper-location .preview-slot-block-location").length == 0
                    dtTabValid = false;
                     $("#slot-location-error").show();
                     $("#slot-location-error").html("Please set business hours for atleast one location");
                } else if ($("#set_hours_by").val() == 2 && $("#set_availability_by").val() == 1 && $("#slotExistsForAnyLocation").val() == 0 && wellbeingSp.length > 0 && $("#companyType").val() != 'normal'){  //&& $(".slots-wrapper-location .preview-slot-block-location").length == 0
                    var _token = $('input[name="_token"]').val();
                    $.ajax({
                        url: getLocationGeneralAvabilities,
                        method: 'post',
                        async: false,
                        data: {
                            _token: _token,
                            'set_hours_by': $('#set_hours_by').val(),
                            'set_availability_by' : $('#set_availability_by').val(),
                            'company_id' :  $('#company_id').val(),
                        },
                        success: function(result) {
                            if (result == false){
                                event.preventDefault();
                                dtTabValid = false;
                                $("#slot-location-error").show();
                                $("#slot-location-error").html("Please set business hours for atleast one location");
                            } else{
                                dtTabValid = true;
                            }
                        }
                    });
                } else {
                    dtTabValid = true;
                }
            }
            if (currentIndex > newIndex) {
                return true;
            }

            if(currentIndex == 3 && portalTabValid == false){
                return false;
            }
            
            if(dtTabValid == false){
                return false;
            }
            return  companyEditForm.valid();
        },
        onStepChanged: function(event, currentIndex, priorIndex, dtExists) {
            $('.companySteps').removeClass('current').hide();
            if(currentIndex == 1 && priorIndex == 0){
                $(".companyDetails-content").addClass("completed");
                $(".moderatorsDetails-content").addClass("active");
                $('#companyAddStep-p-1').show().addClass('current');
                if($('.error-feedback').length <= 0){
                    $('.actions ul li a[href="#next"]').removeClass("disabled");
                }else{
                    $('.actions ul li a[href="#next"]').addClass("disabled");
                }
                $('.actions ul li a[href="#previous"]').removeClass("disabled");
            }else if(currentIndex == 2 && priorIndex == 1 ){
                if($("#is_branding").is(":checked") == true) {
                    //domain branding 
                    $('#companyAddStep-p-2').show().addClass('current');
                    $(".domainBranding-content").addClass("active");
                } else if($("#enable_survey").is(":checked") == true){
                    //survey branding
                    $('#companyAddStep-p-4').show().addClass('current');
                    $(".enableSurvey-content").addClass("active");
                }else{
                    $('#companyAddStep-p-5').show().addClass('current');
                    $(".enableLocation-content").addClass("active");
                }
                $(".moderatorsDetails-content").addClass("completed");
                
            }else if(currentIndex == 3 && priorIndex == 2 && $("#companyType").val() != 'zevo'){
                if($("#is_branding").is(":checked") == true ) {
                    //domain branding 
                    $('#companyAddStep-p-3').show().addClass('current');
                    $(".portalBranding-content").addClass("active");
                } else if($("#enable_survey").is(":checked") == true){
                    //survey branding
                    $('#companyAddStep-p-4').show().addClass('current');
                    $(".enableSurvey-content").addClass("active");
                }else {
                    //location branding
                    $('#companyAddStep-p-5').show().addClass('current');
                    $(".enableLocation-content").addClass("active");
                }
                $(".domainBranding-content").addClass("completed");
            }else if(currentIndex == 3 && priorIndex == 2 && $("#companyType").val() == 'zevo'){
                if($('.domainBranding-content').hasClass( "active" ) == true) { 
                    //domain branding
                    if($("#enable_survey").is(":checked") == true){
                        $('#companyAddStep-p-4').show().addClass('current');
                        $(".enableSurvey-content").addClass("active");
                        $(".domainBranding-content").addClass("completed");
                    }else{
                        $('#companyAddStep-p-5').show().addClass('current');
                        $(".enableLocation-content").addClass("active");
                        $(".domainBranding-content").addClass("completed");
                    }

                }else if($('.enableSurvey-content').hasClass( "active" ) == true ) {
                    //domain branding 
                    $('#companyAddStep-p-5').show().addClass('current');
                    $(".enableLocation-content").addClass("active");
                    $(".enableSurvey-content").addClass("completed");
                }else if($('#companyplan').find(':selected').text() == 'Digital Therapy with Challenge' || $('#companyplan').find(':selected').text() == 'Digital Therapy') {
                    //domain branding 
                    $('#companyAddStep-p-6').show().addClass('current');
                    $(".enableDigitalTherapy-content").addClass("active");
                    $(".enableLocation-content").addClass("completed");
                }else {
                    //location branding
                    $('#companyAddStep-p-7').show().addClass('current');
                    $(".enableManageContent-content").addClass("active");
                    $(".enableLocation-content").addClass("completed");
                    $('.actions ul li a[href="#next"]').parent().hide();
                    $('.actions ul li a[href="#finish"]').parent().show();
                }
            }else if(currentIndex == 4 && priorIndex == 3 && $("#companyType").val() != 'zevo'){
                if($("#enable_survey").is(":checked") == true) {
                    //domain branding 
                    $('#companyAddStep-p-4').show().addClass('current');
                    $(".enableSurvey-content").addClass("active");
                } else {
                    //location branding
                    $('#companyAddStep-p-5').show().addClass('current');
                    $(".enableLocation-content").addClass("active");
                }
                $(".portalBranding-content").addClass("completed");
            }else if(currentIndex == 4 && priorIndex == 3 && $("#companyType").val() == 'zevo'){
                if($('.domainBranding-content').hasClass( "active" ) == true && $("#enable_survey").is(":checked") == true) {
                    //domain branding 
                    /*$('#companyAddStep-p-4').show().addClass('current');
                    $(".enableSurvey-content").addClass("active");
                    $(".domainBranding-content").addClass("completed");*/
                    $('#companyAddStep-p-5').show().addClass('current');
                    $(".enableLocation-content").addClass("active");
                    $(".enableSurvey-content").addClass("completed");
                }else if($('.enableLocation-content').hasClass( "active" ) == true && ($('.enableDigitalTherapy-content').hasClass( "active" ) == false && ($('#companyplan').find(':selected').text() == 'Digital Therapy with Challenge' || $('#companyplan').find(':selected').text() == 'Digital Therapy')) ) {
                    $('#companyAddStep-p-6').show().addClass('current');
                    $(".enableDigitalTherapy-content").addClass("active");
                    $(".enableLocation-content").addClass("completed");
                }else if($('.enableDigitalTherapy-content').hasClass( "active" ) == true ) {
                        $('#companyAddStep-p-7').show().addClass('current');
                        $(".enableManageContent-content").addClass("active");
                        $('.actions ul li a[href="#next"]').parent().hide();
                        $('.actions ul li a[href="#finish"]').parent().show();
                        $(".enableDigitalTherapy-content").addClass("completed");
                }else {
                    $('#companyAddStep-p-7').show().addClass('current');
                    $(".enableManageContent-content").addClass("active");
                    $(".enableLocation-content").addClass("completed");
                    $('.actions ul li a[href="#next"]').parent().hide();
                    $('.actions ul li a[href="#finish"]').parent().show();
                }
            }else if(currentIndex == 5 && priorIndex == 4 && $("#companyType").val() != 'zevo'){
                    //location branding
                    if($('.enableDigitalTherapy-content').hasClass( "active" ) == true ) {
                        $('#companyAddStep-p-7').show().addClass('current');
                        $(".enableManageContent-content").addClass("active");
                        $('.actions ul li a[href="#next"]').parent().hide();
                        $('.actions ul li a[href="#finish"]').parent().show();
                        $(".enableDigitalTherapy-content").addClass("completed");
                    }else{
                        if($("#enable_survey").is(":checked") == true){
                            $('#companyAddStep-p-5').show().addClass('current');
                            $(".enableLocation-content").addClass("active");
                            $(".enableSurvey-content").addClass("completed");
                        }else if($("#dtExistsHidden").val() == true) {
                            $('#companyAddStep-p-6').show().addClass('current');
                            $(".enableDigitalTherapy-content").addClass("active");
                            $(".enableLocation-content").addClass("completed");
                        }else {
                            $('#companyAddStep-p-7').show().addClass('current');
                            $(".enableManageContent-content").addClass("active");
                            $('.actions ul li a[href="#next"]').parent().hide();
                            $('.actions ul li a[href="#finish"]').parent().show();
                            $(".enableLocation-content").addClass("completed");
                        }
                    }
                    
            }else if(currentIndex == 5 && priorIndex == 4 && $("#companyType").val() == 'zevo'){
                //location branding
                if($('.enableLocation-content').hasClass( "active" ) == true && ($('.enableDigitalTherapy-content').hasClass( "active" ) == false && ($('#companyplan').find(':selected').text() == 'Digital Therapy with Challenge' || $('#companyplan').find(':selected').text() == 'Digital Therapy'))) {
                    $('#companyAddStep-p-6').show().addClass('current');
                    $(".enableDigitalTherapy-content").addClass("active");
                    $(".enableLocation-content").addClass("completed");
                }else if($('.enableDigitalTherapy-content').hasClass( "active" ) == true) {
                    $('#companyAddStep-p-7').show().addClass('current');
                    $(".enableManageContent-content").addClass("active");
                    $('.actions ul li a[href="#next"]').parent().hide();
                    $('.actions ul li a[href="#finish"]').parent().show();
                    $(".enableDigitalTherapy-content").addClass("completed");
                }else{
                    $('#companyAddStep-p-7').show().addClass('current');
                    $(".enableManageContent-content").addClass("active");
                    $('.actions ul li a[href="#next"]').parent().hide();
                    $('.actions ul li a[href="#finish"]').parent().show();
                    $(".enableLocation-content").addClass("completed");
                }
                
        }else if(currentIndex == 6 && priorIndex == 5  && $("#companyType").val() != 'zevo'){
                //location branding
                if($('.enableDigitalTherapy-content').hasClass( "active" ) == false &&  $("#dtExistsHidden").val() == true &&  $('#companyType').val() == 'reseller') {
                    //domain branding 
                    $('#companyAddStep-p-6').show().addClass('current');
                    $(".enableDigitalTherapy-content").addClass("active");
                    $(".enableLocation-content").addClass("completed");
                } else {
                    //location branding
                    if($('.enableDigitalTherapy-content').hasClass( "active" ) == true &&  $("#dtExistsHidden").val() == true  && $("#companyType").val() == 'reseller'){
                       // console.log("1111",  $("#dtExistsHidden").val());
                   // console.log("2222", $('.enableDigitalTherapy-content').hasClass( "active" ));
                        $(".enableDigitalTherapy-content").addClass("completed"); 
                        $('#companyAddStep-p-7').show().addClass('current');
                        $(".enableManageContent-content").addClass("active");
                        $('.actions ul li a[href="#next"]').parent().hide();
                        $('.actions ul li a[href="#finish"]').parent().show();   
                    } else if($('.enableDigitalTherapy-content').hasClass( "active" ) == false &&  $("#dtExistsHidden").val() == true && $("#companyType").val() == 'normal'){
                        $('#companyAddStep-p-6').show().addClass('current');
                        $(".enableLocation-content").addClass("completed");
                        $(".enableDigitalTherapy-content").addClass("active");
                    } else if($('.enableDigitalTherapy-content').hasClass( "active" ) == true && $("#companyType").val() == 'normal'){
                        $('#companyAddStep-p-7').show().addClass('current');
                        $(".enableDigitalTherapy-content").addClass("completed");
                        $(".enableManageContent-content").addClass("active");
                        $('.actions ul li a[href="#next"]').parent().hide();
                        $('.actions ul li a[href="#finish"]').parent().show();
                    } else{
                        $('#companyAddStep-p-7').show().addClass('current');
                        $(".enableManageContent-content").addClass("active");
                        $(".enableLocation-content").addClass("completed");
                        $('.actions ul li a[href="#next"]').parent().hide();
                        $('.actions ul li a[href="#finish"]').parent().show();
                    }
                }
            }else if(currentIndex == 6 && priorIndex == 5 && $("#companyType").val() == 'zevo'){
                //location branding
                
                if($('#companyplan').find(':selected').text() == 'Digital Therapy' ||  $('#companyplan').find(':selected').text() == 'Digital Therapy with Challenge') {
                    //domain branding 
                    $('#companyAddStep-p-7').show().addClass('current');
                    $(".enableManageContent-content").addClass("active");
                    $('.actions ul li a[href="#next"]').parent().hide();
                    $('.actions ul li a[href="#finish"]').parent().show();
                    $(".enableDigitalTherapy-content").addClass("completed");
                } 
                
            }else if(currentIndex == 7 && priorIndex == 6){
                //location branding
                $('#companyAddStep-p-7').show().addClass('current');
                $(".enableManageContent-content").addClass("active");
                $('.actions ul li a[href="#next"]').parent().hide();
                $('.actions ul li a[href="#finish"]').parent().show();
                if( $("#dtExistsHidden").val() == true && $("#companyType").val() != 'zevo')  {
                    $(".enableDigitalTherapy-content").addClass("completed");
                }else if($("#companyType").val() == 'zevo' && ($('#companyplan').find(':selected').text() == 'Digital Therapy' ||  $('#companyplan').find(':selected').text() == 'Digital Therapy with Challenge')  ) { 
                    $(".enableDigitalTherapy-content").addClass("completed");
                }else{
                    $(".enableLocation-content").addClass("completed");
                }
            }else{

            }
            /*if($('.enableSurvey-content').hasClass( "active" ) == true ){
                $("#group_content").treeMultiselect();
            }*/

            //Prev Fun
            if(currentIndex == 0 && priorIndex == 1){
                $(".companyDetails-content").addClass("active");
                $(".companyDetails-content").removeClass("completed");
                $(".moderatorsDetails-content").removeClass("active");
                $('#companyAddStep-p-0').show().addClass('current');
                $('.actions ul li a[href="#next"]').removeClass("disabled");
                $('.actions ul li a[href="#previous"]').addClass("disabled");
            }else if(currentIndex == 1 && priorIndex == 2 ){
                if($("#is_branding").is(":checked") == true) {
                    //domain branding 
                    $('#companyAddStep-p-2').hide().removeClass('current');
                    $(".domainBranding-content").removeClass("active");
                } else if($("#enable_survey").is(":checked") == true){
                    //survey branding
                    $('#companyAddStep-p-4').hide().removeClass('current');
                    $(".enableSurvey-content").removeClass("active");
                }else{
                    $('#companyAddStep-p-5').hide().removeClass('current');
                    $(".enableLocation-content").removeClass("active");
                }
                $(".moderatorsDetails-content").addClass("active");
                $(".moderatorsDetails-content").removeClass("completed");
                $('#companyAddStep-p-1').show().addClass('current');
                
            }else if(currentIndex == 2 && priorIndex == 3 &&  $("#companyType").val() != 'zevo'){
                if($("#is_branding").is(":checked") == true ) {
                    //domain branding 
                    $('#companyAddStep-p-3').hide().removeClass('current');
                    $(".portalBranding-content").removeClass("active");
                } else if($("#enable_survey").is(":checked") == true){
                    //survey branding
                    $('#companyAddStep-p-4').hide().removeClass('current');
                    $(".enableSurvey-content").removeClass("active");
                }else {
                    //location branding
                    $('#companyAddStep-p-5').hide().removeClass('current');
                    $(".enableLocation-content").removeClass("active");
                }
                $(".domainBranding-content").removeClass("completed");
                $(".domainBranding-content").addClass("active");
                $('#companyAddStep-p-2').show().addClass('current');
                
            }else if(currentIndex == 2 && priorIndex == 3 &&  $("#companyType").val() == 'zevo'){
                if($("#is_branding").is(":checked") == true ) { 
                    //domain branding
                    if($("#enable_survey").is(":checked") == true){
                        $('#companyAddStep-p-4').hide().removeClass('current');
                        $(".enableSurvey-content").removeClass("active");
                        $(".domainBranding-content").removeClass("completed");
                        $(".domainBranding-content").addClass("active");
                        $('#companyAddStep-p-2').show().addClass('current');
                    }else{
                        $('#companyAddStep-p-5').hide().removeClass('current');
                        $(".enableLocation-content").removeClass("active");
                        $(".domainBranding-content").removeClass("completed");
                        $(".domainBranding-content").addClass("active");
                        $('#companyAddStep-p-2').show().addClass('current');
                    }
                }else if($("#enable_survey").is(":checked") == true) {
                    //domain branding 
                    $('#companyAddStep-p-5').hide().removeClass('current');
                    $(".enableLocation-content").removeClass("active");
                    $(".enableSurvey-content").removeClass("completed");
                    $(".enableSurvey-content").addClass("active");
                    $('#companyAddStep-p-4').show().addClass('current');
                }else if($('#companyplan').find(':selected').text() == 'Digital Therapy' ||  $('#companyplan').find(':selected').text() == 'Digital Therapy with Challenge' ) {
                    //domain branding 
                    $('#companyAddStep-p-6').hide().removeClass('current');
                    $(".enableDigitalTherapy-content").removeClass("active");
                    $(".enableLocation-content").addClass("active");
                    $('#companyAddStep-p-5').show().addClass('current');
                    $(".enableLocation-content").removeClass("completed");
                }else {
                    //location branding
                    $('#companyAddStep-p-7').hide().removeClass('current');
                    $(".enableManageContent-content").removeClass("active");
                    $(".enableLocation-content").removeClass("completed");
                    $(".enableLocation-content").addClass("active");
                    $('#companyAddStep-p-5').show().addClass('current');
                    $('.actions ul li a[href="#next"]').parent().show();
                    $('.actions ul li a[href="#finish"]').parent().hide();
                }
            }else if(currentIndex == 3 && priorIndex == 4 &&  $("#companyType").val() != 'zevo'){
                if($("#enable_survey").is(":checked") == true) {
                    //domain branding 
                    $('#companyAddStep-p-4').hide().removeClass('current');
                    $(".enableSurvey-content").removeClass("active");
                } else {
                    //location branding
                    $('#companyAddStep-p-5').hide().removeClass('current');
                    $(".enableLocation-content").removeClass("active");
                }
                $(".portalBranding-content").removeClass("completed");
                $(".portalBranding-content").addClass("active");
                $('#companyAddStep-p-3').show().addClass('current');
            }else if(currentIndex == 3 && priorIndex == 4 && $("#companyType").val() == 'zevo'){
                if($('.enableManageContent-content').hasClass( "active" ) == true) {
                    if($('#companyplan').find(':selected').text() == 'Digital Therapy' || $('#companyplan').find(':selected').text() == 'Digital Therapy with Challenge'){
                        $('#companyAddStep-p-7').hide().removeClass('current');
                        $(".enableManageContent-content").removeClass("active");
                        $(".enableDigitalTherapy-content").removeClass("completed");
                        $(".enableDigitalTherapy-content").addClass("active");
                        $("#companyAddStep-p-6").show().addClass('current');
                    }else{
                        $('#companyAddStep-p-7').hide().removeClass('current');
                        $(".enableManageContent-content").removeClass("active");
                        $(".enableLocation-content").removeClass("completed");
                        $(".enableLocation-content").addClass("active");
                        $("#companyAddStep-p-5").show().addClass('current');
                    }
                }else if($('.enableLocation-content').hasClass( "active" ) == false && ($('#companyplan').find(':selected').text() == 'Digital Therapy' || $('#companyplan').find(':selected').text() == 'Digital Therapy with Challenge')){
                    $('#companyAddStep-p-6').hide().removeClass('current');
                    $(".enableDigitalTherapy-content").removeClass("active");
                    $(".enableLocation-content").removeClass("completed");
                    $(".enableLocation-content").addClass("active");
                    $("#companyAddStep-p-5").show().addClass('current');
                }else if($('.enableLocation-content').hasClass( "active" ) == true){
                    if($('.enableDigitalTherapy-content').hasClass( "active" ) == true &&  ($('#companyplan').find(':selected').text() == 'Digital Therapy' || $('#companyplan').find(':selected').text() == 'Digital Therapy with Challenge')){
                        $('#companyAddStep-p-6').hide().removeClass('current');
                        $(".enableDigitalTherapy-content").removeClass("active");
                        $(".enableLocation-content").removeClass("completed");
                        $(".enableLocation-content").addClass("active");
                        $("#companyAddStep-p-5").show().addClass('current');
                    }else if($("#enable_survey").is(":checked") == true){
                        $('#companyAddStep-p-5').hide().removeClass('current');
                        $(".enableLocation-content").removeClass("active");
                        $(".enableSurvey-content").removeClass("completed");
                        $(".enableSurvey-content").addClass("active");
                        $("#companyAddStep-p-4").show().addClass('current');
                    }
                    /*$('#companyAddStep-p-6').hide().removeClass('current');
                    $(".enableDigitalTherapy-content").removeClass("active");
                    $(".enableLocation-content").removeClass("completed");
                    $(".enableLocation-content").addClass("active");
                    $("#companyAddStep-p-5").show().addClass('current');*/
                }else{
                    $('#companyAddStep-p-6').hide().removeClass('current');
                    $(".enableDigitalTherapy-content").removeClass("active");
                    $(".enableLocation-content").removeClass("completed");
                    $(".enableLocation-content").addClass("active");
                    $("#companyAddStep-p-5").show().addClass('current');
                }
            }else if(currentIndex == 4 && priorIndex == 5  && $("#companyType").val() != 'zevo'){
                    //location branding
                    if($('.enableDigitalTherapy-content').hasClass( "active" ) == true && $("#enable_survey").is(":checked") == false){
                        $('#companyAddStep-p-6').hide().removeClass('current');
                        $(".enableDigitalTherapy-content").removeClass("active");
                        $(".enableLocation-content").removeClass("completed");
                        $(".enableLocation-content").addClass("active");
                        $('#companyAddStep-p-5').show().addClass('current');
                    } else if($('.enableDigitalTherapy-content').hasClass( "active" ) == false && $("#enable_survey").is(":checked") == true){
                        $('#companyAddStep-p-5').hide().removeClass('current');
                        $(".enableLocation-content").removeClass("active");
                        $(".enableSurvey-content").removeClass("completed");
                        $(".enableSurvey-content").addClass("active");
                        $('#companyAddStep-p-4').show().addClass('current');
                    } else {
                        $('#companyAddStep-p-7').hide().removeClass('current');
                        $(".enableManageContent-content").removeClass("active");
                        $(".enableLocation-content").removeClass("completed");
                        $(".enableLocation-content").addClass("active");
                        $('#companyAddStep-p-5').show().addClass('current');
                    }
            }else if(currentIndex == 4 && priorIndex == 5 && $("#companyType").val() == 'zevo'){
                //location branding
                if($('.enableManageContent-content').hasClass( "active" ) == true && ($('#companyplan').find(':selected').text() == 'Digital Therapy' || $('#companyplan').find(':selected').text() == 'Digital Therapy with Challenge')) {
                    $('#companyAddStep-p-7').hide().removeClass('current');
                    $(".enableManageContent-content").removeClass("active");
                    $('.actions ul li a[href="#next"]').parent().show();
                    $('.actions ul li a[href="#finish"]').parent().hide();
                    $('#companyAddStep-p-6').show().addClass('current');
                    $(".enableDigitalTherapy-content").addClass("active");
                    $(".enableDigitalTherapy-content").removeClass("completed");
                }else if($('#companyplan').find(':selected').text() == 'Digital Therapy' || $('#companyplan').find(':selected').text() == 'Digital Therapy with Challenge'){
                    $('#companyAddStep-p-6').hide().removeClass('current');
                    $(".enableDigitalTherapy-content").removeClass("active");
                    $('#companyAddStep-p-5').show().addClass('current');
                    $(".enableLocation-content").addClass("active");
                    $(".enableLocation-content").removeClass("completed");
                }else{
                    $('#companyAddStep-p-7').hide().removeClass('current');
                    $(".enableManageContent-content").removeClass("active");
                    $('.actions ul li a[href="#next"]').parent().show();
                    $('.actions ul li a[href="#finish"]').parent().hide();
                    $('#companyAddStep-p-5').show().addClass('current');
                    $(".enableLocation-content").addClass("active");
                    $(".enableLocation-content").removeClass("completed");
                }
            }else if(currentIndex == 5 && priorIndex == 6 && $("#companyType").val() != 'zevo'){
                //location branding
                if($('.enableDigitalTherapy-content').hasClass( "active" ) == true && $("#enable_survey").is(":checked") == true) {
                    //domain branding 
                    $('#companyAddStep-p-6').hide().removeClass('current');
                    $(".enableDigitalTherapy-content").removeClass("active");
                    $(".enableLocation-content").removeClass("completed");
                    $(".enableLocation-content").addClass("active");
                    $('#companyAddStep-p-5').show().addClass('current');
                } if($('.enableDigitalTherapy-content').hasClass( "active" ) == true && $("#enable_survey").is(":checked") == false) {
                    //location branding
                    $('#companyAddStep-p-7').hide().removeClass('current');
                    $(".enableManageContent-content").removeClass("active");
                    $('.actions ul li a[href="#next"]').parent().show();
                    $('.actions ul li a[href="#finish"]').parent().hide();
                    $(".enableDigitalTherapy-content").addClass("active");
                    $(".enableDigitalTherapy-content").removeClass("completed");
                    $('#companyAddStep-p-6').show().addClass('current');
                }else {
                    //location branding
                    $('#companyAddStep-p-7').hide().removeClass('current');
                    $(".enableManageContent-content").removeClass("active");
                    $('.actions ul li a[href="#next"]').parent().show();
                    $('.actions ul li a[href="#finish"]').parent().hide();
                    $(".enableLocation-content").addClass("active");
                    $(".enableLocation-content").removeClass("completed");
                    $('#companyAddStep-p-5').show().addClass('current');
                }
            }else if(currentIndex == 5 && priorIndex == 6 && $("#companyType").val() == 'zevo'){
                //location branding
                $('#companyAddStep-p-7').hide().removeClass('current');
                $(".enableManageContent-content").removeClass("active");
                $('.actions ul li a[href="#next"]').parent().hide();
                $('.actions ul li a[href="#finish"]').parent().show();
                if($('#companyplan').find(':selected').text() == 'Digital Therapy' ||  $('#companyplan').find(':selected').text() == 'Digital Therapy with Challenge') {
                    $(".enableDigitalTherapy-content").removeClass("completed");
                    $('#companyAddStep-p-6').show().addClass('current');
                    $(".enableDigitalTherapy-content").addClass("active");
                    $('.actions ul li a[href="#next"]').parent().show();
                    $('.actions ul li a[href="#finish"]').parent().hide();
                }else{
                    $(".enableLocation-content").removeClass("completed");
                    $(".enableLocation-content").addClass("active");
                    $('#companyAddStep-p-5').show().addClass('current');
                }
            }else if(currentIndex == 6 && priorIndex == 7){
                //location branding
                $('#companyAddStep-p-7').hide().removeClass('current');
                $(".enableManageContent-content").removeClass("active");
                $('.actions ul li a[href="#next"]').parent().show();
                $('.actions ul li a[href="#finish"]').parent().hide();
                if($('#dtExistsHidden').val() == true) {
                    $(".enableDigitalTherapy-content").removeClass("completed");
                    $(".enableDigitalTherapy-content").addClass("active");
                    $('#companyAddStep-p-6').show().addClass('current');
                }else{
                    $(".enableLocation-content").removeClass("completed");
                    $(".enableLocation-content").addClass("active");
                    $('#companyAddStep-p-5').show().addClass('current');
                }
            }else{

            }
        },
        onCanceled: function(event) {
            // console.log("Step canceled");
        },
        onFinishing: async function(event, currentIndex) {
            // console.log("Step finishing");
        },
        onFinished: function(event, currentIndex) {
            var selectedMembers = $('#group_content').val().length;
            var _token = $('input[name="_token"]').val();
            if (selectedMembers != 0) {
                if(companyEditForm.valid() == true) {
                    event.preventDefault();
                }
                $.ajax({
                    url: contentValidateURL,
                    method: 'post',
                    data: {
                        _token: _token,
                        'content': $('#group_content').val()
                    },
                    success: function(result) {
                        if(result == 0){
                            event.preventDefault();
                            $('#companyEdit').valid();
                            $('#group_content-min-error').hide();
                            $('#group_content-error').show();
                            $('.tree-multiselect').css('border-color', '#f44436');
                        } else {
                            if(companyEditForm.valid() == true) {
                                companyEditForm.submit();
                            }
                            $('#group_content-error').hide();
                            $('#group_content-min-error').hide();
                            $('#group_content-max-error').hide();
                            $('.tree-multiselect').css('border-color', '#D8D8D8');
                        }
                    }
                });
            } else {
                    event.preventDefault();
                    companyEditForm.valid();
                    $('#group_content-min-error').hide();
                    $('#group_content-error').show();
                    $('.tree-multiselect').css('border-color', '#f44436');
            }
        },
    });

    $('#survey_roll_out_day').change(displayUpcomingSurveyDetails);
    $('#survey_roll_out_time').change(displayUpcomingSurveyDetails);
    $('#survey_frequency').change(displayUpcomingSurveyDetails);
    $('#subscription_start_date').change(displayUpcomingSurveyDetails);

    $(document).on('click', '#addLocationSlots', function(t) {
        $("#slotExistsForAnyLocation").val(1);
        $('.page-loader-wrapper').show();
        var locationModalBox = '#add-location-slot-model-box';
        $("#add-location-slot-model-box #modal_title").html("Set " +$(this).attr('data-locationname')+ " Hours");
        $("#add-location-slot-model-box").attr('data-backdrop', 'static');
        $(locationModalBox).attr("data-id", $(this).data('id'));
        $.ajax({
            url: getDtLocationSlotsURL,
            method: 'post',
            data: {
                _token: _token,
                'locationId': $(this).data('id'),
                'companyId' : $(this).data('companyid'),
                'removedIds': $("#locationHoursManagment .mainTableRemovedSlotIds").val(),
                'updatedIds': $("#locationHoursManagment .mainTableUpdatedSlotIds").val()
            },
            success: function(result) {
                $('.page-loader-wrapper').hide();
                $(locationModalBox).modal('show');
                if(result){
                    $(locationModalBox).find(".set-availability-block").html("").append(result);
                } else {
                    $(locationModalBox).find(".set-availability-block").html("No slots available for this location");
                }
            }
        });
    });
});

function removeFrmArr(array, element) {
    return array.filter(e => e !== element);
}
</script>
@if($isShowContentType)
<script type="text/javascript">
    $(document).ready(function(){
        /*$(function() {
    $("#group_content").treeMultiselect({
        enableSelectAll: true,
        searchable: true,
        startCollapsed: true,
        onChange: function (allSelectedItems, addedItems, removedItems) {
            var selectedMembers = $('#group_content').val().length;
            if (selectedMembers == 0) {
                $('#companyEdit').valid();
                $('#group_content-min-error').hide();
                $('#group_content-error').show();
                $('.tree-multiselect').css('border-color', '#f44436');
            } else {
                $('#group_content-error').hide();
                $('#group_content-min-error').hide();
                $('#group_content-max-error').hide();
                $('.tree-multiselect').css('border-color', '#D8D8D8');
            }
        }
    });
});*/
});
function formSubmit() {
    var selectedMembers = $('#group_content').val().length;
    var _token = $('input[name="_token"]').val();
    if (selectedMembers != 0) {
        if($('#companyEdit').valid() == true) {
            event.preventDefault();
        }
        $.ajax({
            url: contentValidateURL,
            method: 'post',
            data: {
                _token: _token,
                'content': $('#group_content').val()
            },
            success: function(result) {
                if(result == 0){
                    event.preventDefault();
                    $('#companyEdit').valid();
                    $('#group_content-min-error').hide();
                    $('#group_content-error').show();
                    $('.tree-multiselect').css('border-color', '#f44436');
                } else {
                    if($('#companyEdit').valid() == true) {
                        $('#companyEdit').submit();
                    }
                    $('#group_content-error').hide();
                    $('#group_content-min-error').hide();
                    $('#group_content-max-error').hide();
                    $('.tree-multiselect').css('border-color', '#D8D8D8');
                }
            }
        });
    } else {
        event.preventDefault();
        $('#companyEdit').valid();
        $('#group_content-min-error').hide();
        $('#group_content-error').show();
        $('.tree-multiselect').css('border-color', '#f44436');
    }
}

function displayUpcomingSurveyDetails(){
    var survey_day = $('#survey_roll_out_day').val();
    var roll_out_time = $('#survey_roll_out_time').val();
    if(survey_day != '' && survey_day != undefined){
        $.ajax({
            url: upcomingSurveyDetails,
            type: 'POST',
            dataType: 'json',
            data: {
                survey_roll_out_day: survey_day,
                roll_out_time : roll_out_time,
                company:  $('#company_id').val(),
                survey_frequency : $('#survey_frequency').val(),
                subscription_start_date : $('#subscription_start_date').val(),
            },
        }).done(function(data) {
            if(data['upcomingRollOutDay'] != null && data['upcomingExpiredDay'] != null){
                $('#upcomingSurveyDetails').show();
                $('span#upRollout').html(data['upcomingRollOutDay']);
                $('span#upExpire').html(data['upcomingExpiredDay']);
            }
        }).fail(function(error) {
        });
    }
}
function checkDtExists(){
    //var dtExists = false;
    $.ajax({
        url: isDtIncludedInCPlan,
        method: 'get',
        data: {
            'planId': $('#companyplan').val()
        },
        success: function(result) {
            if(result == 1){
                $(".enableDigitalTherapy-content").removeClass('d-none');
                $(".dt-banners").removeClass('d-none');
                var slug = $("#companyplan").find(':selected').text();
                $("#companyplanSlug").val(slug.split(' ').join('-').toLowerCase());
                dtExists = true;
                $("#dtExistsHidden").val(1);
            } else {
                $(".enableDigitalTherapy-content").addClass('d-none');
                $(".dt-banners").addClass('d-none');
                dtExists = false;
                $("#dtExistsHidden").val(0);
            }
        }
    });
}
</script>
{{-- <script src="{{ mix('js/company/edit.js') }}" async="async">
</script> --}}
<script src="{{ mix('js/cronofy/slots.js') }}"></script>
<script src="{{ mix('js/cronofy/custom-specific-date.js') }}"></script>
<script src="{{ mix('js/company/location-slots.js') }}"></script>
@endif
@endsection
