@extends('layouts.app')

@section('after-styles')
@if($isShowContentType)
<link href="{{asset('assets/plugins/tree-multiselect/tree-multiselect.css?var='.rand())}}" rel="stylesheet"/>
@endif
<link href="{{asset('assets/plugins/datepicker/datepicker3.css?var='.rand())}}" rel="stylesheet"/>
<link href="{{asset('assets/plugins/timepicker/bootstrap-timepicker.min.css?var='.rand())}}" rel="stylesheet"/>
<link href="{{asset('assets/plugins/datatables/dataTables.bootstrap5.min.css?var='.rand())}}" rel="stylesheet"/>

<style type="text/css">
    .prevent-events { pointer-events: none; }
</style>
@endsection

@section('content-header')
<!-- Content Header (Page header) -->
@include('admin.companies.breadcrumb', [
    'mainTitle'  => trans('company.title.add'),
    'breadcrumb' => Breadcrumbs::render('companies.create'),
    'companyType'=> $companyType
])
<!-- /.content-header -->
@endsection

@section('content')
<section class="content">
    <div class="container-fluid">
            {{ Form::open(['route' => ['admin.companies.store', $companyType], 'class' => 'form-horizontal zevo_form_submit', 'method' => 'post', 'role' => 'form', 'id' => 'companyAdd', 'files' => true]) }}
            {{ Form::hidden('companyType', $companyType, ['id'=>'companyType'])}}
            {{ Form::hidden('isChild', ($companyType != 'zevo' ? 1 : 0 ), ['id'=>'isChild'])}}
            @include('admin.companies.form', ['edit' => false])
            {{ Form::close() }}
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
</section>
@endsection

@section('after-scripts')
<script src="{{ asset('assets/plugins/step/jquery.steps.js?var='.rand()) }}">
</script>
<script src="{{asset('assets/plugins/datepicker/bootstrap-datepicker.js?var='.rand())}}">
</script>
<script src="{{asset('assets/plugins/timepicker/bootstrap-timepicker.js?var='.rand())}}">
</script>
<script src="{{ asset('assets/plugins/jonthornton-timepicker/jquery.timepicker.min.js?var='.rand()) }}">
</script>
<script src="{{asset('assets/plugins/moment/moment.min.js?var='.rand()) }}">
</script>
<script src="{{asset('assets/plugins/moment/moment-timezone-with-data-10-year-range.js?var='.rand()) }}"></script>
<script src="{{ asset('assets/plugins/jonthornton-timepicker/jquery.datepair.min.js?var='.rand()) }}">
</script>
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
{!! JsValidator::formRequest('App\Http\Requests\Admin\CreateCompanyRequest','#companyAdd') !!}
<script type="text/javascript">
    var start = new Date(),
        end = new Date('{{ $maxEndDate }}'),
        stateUrl = '{{ route("admin.ajax.states", ":id") }}',
        tzUrl = '{{ route("admin.ajax.timezones", ":id") }}',
        contentValidateURL = '{{ route("admin.ajax.checkcompaniescontentvalidate") }}',
        emailValidateURL = '{{ route("admin.ajax.checkEmailExists") }}',
        upcomingSurveyDetails = '{{ route("admin.companies.getUpcomingSurveyDetails") }}',
        roleGroup = '{{ $user_role->group }}',
        today = new Date(),
        endDate = new Date(new Date().setYear(today.getFullYear() + 100)),
        minDOBDate = new Date(new Date().setYear(today.getFullYear() - 18)),
        wellbeingSpecialist = `<?php echo json_encode($wellbeingSp); ?>`,
        getStaffServices = '{{ route("admin.companies.getStaffServices") }}',
        portalDomainFlag = true,
        hoursby = 1,
        availabilityby = 1,
        dtSpecificArray = `<?php echo json_encode($dtSpecificArray); ?>`,
        isDtIncludedInCPlan = '{{ route("admin.ajax.checkdtexists") }}';
    var message = {
        upload_image_dimension: '{{ trans('company.messages.upload_image_dimension') }}',
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
    $companyAddForm = $("#companyAdd"),
    stepObj,
    validateTitle = function() {
            $('#name').valid();
        }
    function readURL(input, previewElement) {
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
                    if(round == undefined){
                        round = 'no';
                    }
                    var aspectedRatio = ratio;
                    var ratioSplit = ratio.split(':');
                    var newWidth = ratioSplit[0];
                    var newHeight = ratioSplit[1];
                    if(round == 'yes'){
                        var ratioGcd = gcdRound(this.width, this.height, newHeight, newWidth);
                    } else {
                        var ratioGcd = gcd(this.width, this.height, newHeight, newWidth);
                    }
                    if(($(input).data('previewelement') != '#emailheader_preview') && ((this.width < imageWidth && this.height < imageHeight) || ratioGcd != aspectedRatio)){
                        $(input).empty().val('');
                        $(input).parent('div').find('.custom-file-label').html('Choose File');
                        $(input).parent('div').find('.invalid-feedback').remove();
                        $(previewElement).removeAttr('src');
                        toastr.error(message.upload_image_dimension);
                        readURL(null, previewElement);
                    }
                    if(($(input).data('previewelement') == '#emailheader_preview') && (this.width < imageWidth || this.height < imageHeight  || ratioGcd != aspectedRatio)){
                        $(input).empty().val('');
                        $(input).parent('div').find('.custom-file-label').html('Choose File');
                        $(input).parent('div').find('.invalid-feedback').remove();
                        $(previewElement).removeAttr('src');
                        toastr.error(message.upload_image_dimension);
                        readURL(null, previewElement);
                    }
                }
                $(previewElement).attr('src', e.target.result);
            }
            reader.readAsDataURL(input.files[0]);
        } else {
            $(input).parent('div').find('.custom-file-label').html('Choose File');
            $(previewElement).removeAttr('src');
        }
    }

    // Set contact us branding data
    function setBrandingsContactDetailsValue(data) {
        $('#contact_us_request, #contact_us_header, #contact_us_description, #contact_us_image').attr('disabled', false);
        if(data.id) {
            if(data.contact_us_request) {
                $('#contact_us_request').val(data.contact_us_request).trigger('change');
            }
            if(data.contact_us_header) {
                $('#contact_us_header').val(data.contact_us_header);
            }
            if(data.contact_us_description) {
                tinymce.activeEditor.setContent(data.contact_us_description);
            }
        } else {
            $('#contact_us_request').val('');
            $("#contact_us_request").trigger('change');
        }

        if(data.contact_us_image) {
            $('#contact_us_image').next('.custom-file-label').html(data.contact_us_image.name);
        } else {
            $('#contact_us_image').next('.custom-file-label').html('Choose File');
        }
    }

    // function to set branding section value
    function setBrandingsValue(data) {
        if(data.id) {
            if(data.sub_domain) {
                $('#sub_domain').val(data.sub_domain).trigger('change');
            }
            if(data.onboarding_title) {
                $('#onboarding_title').val(data.onboarding_title);
            }
            if(data.onboarding_description) {
                $('#onboarding_description').val(data.onboarding_description);
            }
            if(data.portal_domain) {
                $('#portal_domain option:selected').removeAttr('selected');
                // alert(data.portal_domain);
                // $('#portal_domain').val(data.portal_domain);
                $("#portal_domain option[value='"+ data.portal_domain +"']").attr("selected", "selected");
                $('#portal_domain').select2().trigger('change');
            }
            if(data.portal_title) {
                $('#portal_title').val(data.portal_title);
            }
            if(data.portal_description) {
                $('#portal_description').val(data.portal_description);
            }
            if(data.portal_theme) {
                $('#portal_theme option:selected').removeAttr('selected');
                $("#portal_theme option[value='"+ data.portal_theme +"']").attr("selected", "selected");
                $('#portal_theme').select2().trigger('change');
            }
            if(data.exclude_gender_and_dob == 1){
                $('#exclude_gender_and_dob').attr('disabled', true).attr('checked', true);
            }else{
                $('#exclude_gender_and_dob').attr('disabled', false).attr('checked', false);
            }

            if(data.dt_title) {
                $('#dt_title').val(data.dt_title);
            }
            if(data.dt_description) {
                $('#dt_description').val(data.dt_description);
            }
        } else {
            $('#sub_domain, #onboarding_title, #onboarding_description, #portal_domain, #portal_title, #portal_description, #portal_theme').val('');
            $("#sub_domain").trigger('change');
            $('#exclude_gender_and_dob').attr('disabled', false).attr('checked', false);
        }

        if(data.branding_login_background) {
            $('#previewImg_login_screen_background').attr('src', data.branding_login_background.url);
            $('#login_screen_background').next('.custom-file-label').html(data.branding_login_background.name);
        } else {
            $('#previewImg_login_screen_background').attr('src', '');
            $('#login_screen_background').next('.custom-file-label').html('Choose File');
        }
        if(data.branding_logo) {
            $('#previewImg_login_screen_logo').attr('src', data.branding_logo.url);
            $('#login_screen_logo').next('.custom-file-label').html(data.branding_logo.name);
        } else {
            $('#previewImg_login_screen_logo').attr('src', '');
            $('#login_screen_logo').next('.custom-file-label').html('Choose File');
        }
        if(data.portal_logo_main) {
            $('#previewImg_portal_logo_main').attr('src', data.portal_logo_main.url);
            $('#portal_logo_main').next('.custom-file-label').html(data.portal_logo_main.name);
        } else {
            $('#previewImg_portal_logo_main').attr('src', '');
            $('#portal_logo_main').next('.custom-file-label').html('Choose File');
        }
        if(data.portal_logo_optional) {
            $('#previewImg_portal_logo_optional').attr('src', data.portal_logo_optional.url);
            $('#portal_logo_optional').next('.custom-file-label').html(data.portal_logo_optional.name);
        } else {
            $('#previewImg_portal_logo_optional').attr('src', '');
            $('#portal_logo_optional').next('.custom-file-label').html('Choose File');
        }
        if(data.portal_background_image) {
            $('#previewImg_portal_background_image').attr('src', data.portal_background_image.url);
            $('#portal_background_image').next('.custom-file-label').html(data.portal_background_image.name);
        } else {
            $('#previewImg_portal_background_image').attr('src', '');
            $('#portal_background_image').next('.custom-file-label').html('Choose File');
        }
        if(data.portal_favicon_icon) {
            $('#portal_favicon_icon').next('.custom-file-label').html(data.portal_favicon_icon.name);
        } else {
            $('#portal_favicon_icon').next('.custom-file-label').html('Choose File');
        }

        if(data.portal_homepage_logo_left) {
            $('#portal_homepage_logo_left').next('.custom-file-label').html(data.portal_homepage_logo_left.name);
        } else {
            $('#portal_homepage_logo_left').next('.custom-file-label').html('Choose File');
        }
        if(data.portal_homepage_logo_right) {
            $('#portal_homepage_logo_right').next('.custom-file-label').html(data.portal_homepage_logo_right.name);
        } else {
            $('#portal_homepage_logo_right').next('.custom-file-label').html('Choose File');
        }

        if(data.appointment_image) {
            $('#appointment_image').attr('disabled', false);
            $('#appointment_image').next('.custom-file-label').html(data.appointment_image.name).attr('disabled', false);;
        } else {
            $('#appointment_image').attr('disabled', false);
            $('#appointment_image').next('.custom-file-label').html("appointment-default.png");
        }

        if(data.appointment_title) {
            $('#appointment_title').attr('disabled', false).val(data.appointment_title);
        }
        if(data.appointment_description) {
            $('#appointment_description').attr('disabled', false).val(data.appointment_description);
        }
    }

    function getParentCoData() {
        var payload = {
            company: $('#parent_company').val(),
            is_reseller: $('input[name="is_reseller"]:checked').val(),
        };
        $.ajax({
            url: "{{ route('admin.companies.resellerDetails') }}",
            type: 'GET',
            dataType: 'json',
            data: payload
        })
        .done(function(data) {
            var roleOptionsHtml = "";
            if(data.roles) {
                if($('input[name="is_reseller"]:checked').val() == 'no') {
                    roleOptionsHtml += `<option disabled>Zevo Company Admin</option>`;
                }else{
                    roleOptionsHtml += `<option disabled>Super Admin</option>`;
                }
                $(data.roles).each(function(index, role) {
                    roleOptionsHtml += `<option value="${role.id}">${role.name}</option>`;
                });
            }
            $('#assigned_roles').html(roleOptionsHtml).val('').select2('destroy').select2();
            $("#assigned_roles option[value='9']").prop('disabled',true);
            $("#assigned_roles option[value='10']").prop('disabled',true);
            if(data.subscription.start_date && data.subscription.end_date) {
                $('#subscription_start_date').datepicker('setStartDate', new Date(data.subscription.start_date));
                $('#subscription_start_date').datepicker('setEndDate', new Date(data.subscription.end_date));
                $('#subscription_end_date').datepicker('setStartDate', new Date(data.subscription.start_date));
                $('#subscription_end_date').datepicker('setEndDate', new Date(data.subscription.end_date));
                $('#subscription_start_date').val(data.subscription.start_date);
                $('#subscription_end_date').val(data.subscription.end_date);
            } else {
                $('#subscription_start_date').datepicker('setStartDate', start);
                $('#subscription_start_date').datepicker('setEndDate', end);
                $('#subscription_end_date').datepicker('setStartDate', start);
                $('#subscription_end_date').datepicker('setEndDate', end);
                $('#subscription_start_date').val(start);
                $('#subscription_end_date').val(end);
            }

            $('#subscription_start_date').datepicker('setDate', data.subscription.start_date);
            $('#subscription_end_date').datepicker('setDate', data.subscription.end_date);
            $('#subscription_start_date').val(data.subscription.start_date);
            $('#subscription_end_date').val(data.subscription.end_date);
            $('#subscription_start_date-error, #subscription_end_date-error').hide();
            $('#subscription_start_date, #subscription_end_date').removeClass('is-valid is-invalid');

            $("#companyplan option[value='"+ data.subscription.company_plan +"']").attr("selected", "selected");
            $('#companyplan').select2('val', data.subscription.company_plan);
            $('#companyplan').select2().trigger('change');
            //$('#companyplanHidden').val(data.subscription.company_plan);
            $('#enable_survey').prop({'checked': data.subscription.enable_survey}).trigger('change');
            $('#manage_the_design_change').prop({'checked': data.branding.manage_the_design_change}).trigger('change');
            /*if($('#companyType').val() == 'reseller' && $('#isChild').val() == 1){
                $('#enable_survey').prop({'disabled': true}).trigger('change');
            }*/
            $('.select2').select2({allowClear: true,width: '100%'});
            setBrandingsValue(data.branding);
            setBrandingsContactDetailsValue(data.brandingContactDetails);

            var _parentCompanyValue = ($('#parent_company').val() || "zevo");
            if(_parentCompanyValue != 'zevo' && payload.is_reseller == 'no') {
                if(data.selectedContent.length > 0){
                    $.each(data.selectedContent, function(key, value) {
                        var _mainCategoryId = value.id;
                        $.each(value.subcategory, function(subKey, subValue) {
                            var _subCategoryId = subValue.id;
                            $.each(subValue['data'], function(dataKey, dataValue) {
                                var selectedValue = _mainCategoryId+'-'+_subCategoryId+'-'+dataValue;
                                $("#group_content option[value="+selectedValue+"]").attr('selected','selected');
                            });
                        });
                    });
                    $('.tree-multiselect').remove();
                    $("#group_content").treeMultiselect({
                        enableSelectAll: true,
                        searchable: true,
                        startCollapsed: true
                    });
                }
                $('#hidecontent').attr('disabled', true).attr('checked', data.hide_content);
            } else {
                $('.unselect-all').click();
            }
        })
        .fail(function(error) {
            toastr.error('Failed to load reseller company data.');
        }).always(function() {
            $('#reseller_loader, #parent_co_loader').hide();
        });
    }

    $(document).ready(function() {
        $(function() {
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
        });

       
            if($("#companyType").val() == 'reseller' && $('input[name="is_reseller"]:checked').val() == 'no'){
                $('#allow_app').parent('label').removeClass('prevent-events');
            }

            /*if($('#isChild').val() == 1){
                $('#enable_survey').prop({'disabled': true}).trigger('change');
            }*/
            let rowno = 1;
            $(document).on('click', '.add_moderator', function(e) {
                e.preventDefault();
                var newRow = `<tr class="moderators-wrap" data-id="${rowno}"><td class=""><span style="display:none" id="span_id_${rowno}"></span><input type="hidden" id="id_${rowno}" class="form-control" name="id[${rowno}]" value="id${rowno}"></td>
                    <td class="align-top"><span style="display:none" id="span_first_name_${rowno}"></span><input type="text" placeholder="First Name" maxlength="50" id="first_name_${rowno}" class="form-control" name="first_name[${rowno}]" value=""></td>
                    <td class="align-top"><span style="display:none" id="span_last_name_${rowno}"></span><input type="text" maxlength="50" placeholder="Last Name" id="last_name_${rowno}" class="form-control" name="last_name[${rowno}]" value=""></td>
                    <td class="align-top"><span style="display:none" id="span_email_${rowno}"></span><input type="text" class="form-control email" placeholder="Email" id="email_${rowno}" name="email[${rowno}]" maxLength="50" value=""></td>
                    <td class=" no-sort text-center">
                        <a class="action-icon edit_moderator" style="display:none;" id="edit_moderator_${rowno}" href="javascript:void(0);" title="Edit" data-id="${rowno}">
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

            $(document).on('click', '.edit_moderator', function(e) {
                e.preventDefault();
                var moderatorId = ($(this).data('id') || 0);
                $("#first_name_"+moderatorId).show();
                $("#span_first_name_"+moderatorId).hide();
                $("#last_name_"+moderatorId).show();
                $("#span_last_name_"+moderatorId).hide();
                $("#email_"+moderatorId).show();
                $("#span_email_"+moderatorId).hide();
                $("#save_moderator_"+moderatorId).show();
                $("#edit_moderator_"+moderatorId).hide();
            });

            $(document).on('click', '.save_moderator', function(e) {
                e.preventDefault();

                var moderatorId = ($(this).data('id') || 0);
                var first_name = $("#first_name_"+moderatorId).val().trim();
                var last_name = $("#last_name_"+moderatorId).val().trim();
                var email      = $("#email_"+moderatorId).val().trim();
                $('#first_name_'+moderatorId+'-error').remove();
                $('#last_name_'+moderatorId+'-error').remove();
                $('#email_'+moderatorId+'-error').remove();

                var getAllPreviousEmails =  $('.email').map(function() {
                    return this.value;
                }).get();
                
                getAllPreviousEmails.splice(moderatorId,1);
                //getAllPreviousEmails = getAllPreviousEmails.slice(0,-1);
                var arraycontainsturtles = (getAllPreviousEmails.indexOf(email));
                
                var res =  [];
                var emailexists = [];

                if(email!= ''){
                    $.ajax({
                        url: emailValidateURL,
                        method: 'post',
                        data: {
                            'email' : email
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
                            }else{
                                removeFrmArr(emailexists, 'email');
                            }
                        },
                        error: function(data){
                            //error
                        }
                    });
                }
                //regex:/(^([^0-9<>%$#@!*()_]*))+/
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
                }else if ( arraycontainsturtles != -1 && emailexists.length==0) {
                    $('#email_'+moderatorId).after('<div id="email_'+moderatorId+'-error" class="error error-feedback">'+message.email_exists+'</div>');
                    res.push("email");
                }else {
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

                if($("#first_name_0").val().length > 0 && $("#last_name_0").val().length > 0 && $("#email_0").val().length > 0 && $("#email_0-error").length == 0 && $("#first_name_0-error").length == 0 && $("#last_name_0-error").length == 0 && $('.error-feedback').length <= 0){
                    $('.actions ul li a[href="#next"]').removeClass("disabled");
                }else{
                    $('.actions ul li a[href="#next"]').addClass("disabled");
                }
            });


        $("#assigned_roles option[value='2']").prop('disabled',true);

        $("#subscription_start_date, #subscription_end_date").keypress(function(event) {
            event.preventDefault();
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

        $('#subscription_start_date').datepicker({
            startDate: start,
            endDate: end,
            autoclose: true,
            todayHighlight: true,
            format: 'yyyy-mm-dd',
        }).on('changeDate', function () {
            $('#subscription_end_date').datepicker('setStartDate', new Date($(this).val()));
            $('#subscription_start_date').valid();
        });

        $('#subscription_end_date').datepicker({
            startDate: start,
            endDate: end,
            autoclose: true,
            todayHighlight: true,
            format: 'yyyy-mm-dd',
        }).on('changeDate', function () {
            $('#subscription_start_date').datepicker('setEndDate', new Date($(this).val()));
            $('#subscription_end_date').valid();
        });

        $('#group_restriction').on('click', function(e){
            if(this.checked){
                $('#group_restriction_rule_block').show();
            } else {
                $('#group_restriction_rule_block').hide();
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
                } else if ($.inArray(id, ['login_screen_background', 'portal_background_image']) !== -1 && e.target.files[0].size > 5242880) {
                    toastr.error("{{trans('labels.common_title.image_size_5M_error')}}");
                    $(e.currentTarget).empty().val('');
                    $(this).parent('div').find('.custom-file-label').html('Choose File');
                    readURL(null, previewElement);
                } else {
                    readURL(e.target, previewElement);
                    $(this).parent('div').find('.custom-file-label').html(fileName);
                }
            } else {
                $(this).parent('div').find('.custom-file-label').html('Choose File');
                readURL(null, previewElement);
            }
        });

        // to keep county and timezone field enabled when editing locations.
        var country = $('#country').val();
        if (country != '' && country != undefined) {
            $('#country').trigger('change');
        }

        // to populate county and timezone field after redirecting with error.
        setTimeout(function(){
            var county = "{{ old('state') }}";
            if (county != '' && county != undefined) {
                $('#state').select2('val', county);
            }
            var timezone = "{{ old('timezone') }}";
            if (timezone != '' && timezone != undefined) {
                $('#timezone').select2('val', timezone);
            }
        }, 1000);

        $('#is_branding').on('change', function(e) {
            if($(this).is(":checked")) {
                $('#branding_wrapper').fadeIn('slow');
                // $('#portal_branding_wrapper').fadeIn('slow');
            } else {
                $('#branding_wrapper').hide();
                // $('#portal_branding_wrapper').hide();
            }
        });

        $('#enable_survey').on('change', function(e) {
            if($(this).is(":checked")) {
                $('#survey_wrapper').fadeIn('slow');
            } else {
                $('#survey_wrapper').hide();
            }
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

        $(document).on('change', 'input[name="is_reseller"]', function(e) {
            var checkedValue = $('input[name="is_reseller"]:checked').val();
            $('#disable_sso').prop({'disabled': false});
            resetJQuerySteps('#companyAddStep',0);
           
            if(checkedValue == "yes") {
               
                // parent company selection
                $('[data-parent-co-wrapper]').hide();

                // portal domain textbox
                $('[data-portal-domain-wrapper]').show();

                $(".portalBranding-content").removeClass('d-none');
                $(".enableSurvey-content").removeClass('d-none');

                // allow-app checkbox flag
                $('[data-allow-app-wrapper]').hide();
                $('#allow_app').prop({'checked': false});
                $('#allow_app').parent('label').removeClass('prevent-events');


                // branding and survey flag
                $('#is_branding').prop({'checked': true, 'disabled': false}).trigger('change');
                $('#enable_survey').prop({'checked': true, 'disabled': false}).trigger('change');
                $('#is_branding').parent('label').addClass('prevent-events');
                //$('#enable_survey').parent('label').addClass('prevent-events');
                $('#reseller_loader').show();

                // eanble branding fields
                $('.domainBranding-content :input').prop('disabled', false);
                $('#portal_branding_wrapper').fadeIn('slow');
                $('.portalBranding-content :input').prop('disabled', false);
                $('#portal_domain option:selected').removeAttr('selected');
                $('#portal_domain').select2().trigger('change');

                // Showing tooptip for subscription end date field for child companies
                $('#subscription_end_date_tooltip').addClass('d-none');
                
                $('#companyplan').prop('disabled', false);
                $('#companyplan option:selected').removeAttr('selected');
                $('#companyplan').select2().trigger('change');
                // EAP module enable who has mobile access
                // $('#eap_tab_counsellor').hide();
                $('#eap_tab').prop({'checked': false});

                // Company plan dropdown hide for reseller
                //$('#companyplandiv').hide();
                $('.companiesplan').show();

                // disable 'enable survey' fields
                $('#enable_event').prop({'checked': false, 'disabled': true});
                $('#enable_event_wrapper').hide();
                $("#isChild").val(0);
                //$('#companyplanHidden').prop('disabled', true);
                $('#manage_the_design_change').prop('disabled', false).prop({'checked': false}).trigger('change');
                getParentCoData();
            } else {
                // parent company selection
                $('[data-parent-co-wrapper]').show();
                $('#portal_branding_wrapper').hide();
                $('.portalBranding-content :input').prop('disabled', true);
                $('#parent_company').trigger('change');
                $('#allow_app').prop({'checked': false});
                $('#allow_app').parent('label').removeClass('prevent-events');
                //$('#enable_survey').parent('label').addClass('prevent-events');
                $('#is_branding').parent('label').addClass('prevent-events');
                $('#companyplan').prop('disabled', false);
                //$('#companyplanHidden').prop('disabled', false);
                $('#manage_the_design_change').prop('disabled', true).trigger('change');
                $("#isChild").val(1);
            }
        });

        $(document).on('change', '#parent_company', function(e) {
            var _value = ($(this).val() || "zevo");
            if(_value != "zevo") {
                // portal domain textbox
                $('[data-portal-domain-wrapper]').show();

                // allow-app checkbox flag
                $('[data-allow-app-wrapper]').show();
                $('#allow_app').prop({'checked': false, 'disabled': false});
                $('#allow_app').parent('label').removeClass('prevent-events');

                // branding and survey flag
                $('#is_branding').prop({'checked': true, 'disabled': true}).trigger('change');
                $('#enable_survey').prop({'checked': true}).trigger('change');

                // disable branding fields
                $('.domainBranding-content :input').prop('disabled', true);
                $('#portal_branding_wrapper').show();
                // $('#portal_branding_wrapper :input').prop('disabled', true);
                $(".portalBranding-content").removeClass('d-none');

                // Showing tooptip for subscription end date field for child companies
                $('#subscription_end_date_tooltip').removeClass('d-none');

                // EAP module enable who has mobile access
                // $('#eap_tab_counsellor').hide();
                $('#eap_tab').prop({'checked': false});

                // Company plan dropdown hide for reseller
                //$('#companyplandiv').hide();
                $('.companiesplan').show();

                // disable 'enable survey' fields
                $('#enable_event').prop({'checked': false, 'disabled': true});
                $('#enable_event_wrapper').hide();

                $('#manage_the_design_change').prop('disabled', true);
            } else {
                // allow-app checkbox flag
                $('[data-allow-app-wrapper]').show();
                $('#allow_app').prop({'checked': true});
                $('#allow_app').parent('label').addClass('prevent-events');

                // branding and survey flag
                $('#is_branding').prop({'disabled': false});
                $('#enable_survey').prop({'disabled': false});
                $('#is_branding').parent('label').removeClass('prevent-events');
                $('#enable_survey').parent('label').removeClass('prevent-events');

                // portal domain textbox
                $('[data-portal-domain-wrapper]').hide();

                // enable 'enable survey' fields
                $('#enable_event').prop({'checked': false, 'disabled': false});
                $('#enable_event_wrapper').show();

                // eanble branding fields
                $('.domainBranding-content :input').prop('disabled', false);
                $('#portal_branding_wrapper').hide();
                // $('#portal_branding_wrapper').fadeIn('slow');
                // $('#portal_branding_wrapper :input').prop('disabled', false);

                // Showing tooptip for subscription end date field for child companies
                $('#subscription_end_date_tooltip').addClass('d-none');

                // EAP module enable who has mobile access
                $('#eap_tab_counsellor').hide();

                // Company plan dropdown hide for reseller
                $('.companiesplan').hide();
                //$('#companyplandiv').show();
            }
            $('#parent_co_loader').show();
            getParentCoData();
        });

        if (roleGroup == 'reseller') {
            // Showing tooptip for subscription end date field for child companies
            $('#subscription_end_date_tooltip').removeClass('d-none');
        }
        $(document).on('change', '.portal_domain_list', function(e) {
            portalDomainFlag = true;
            var portalDomain = $("#portal_domain").val()
            var _token = $('input[name="_token"]').val();
            $("#portal_domain-error").remove();
            if (portalDomain != '' && $('input[name="is_reseller"]:checked').val() == 'yes') {
                $.ajax({
                    url: '{{ route('admin.ajax.portalDomainExists') }}',
                    method: 'post',
                    data: {
                        token: _token,
                        portalDomain: portalDomain
                    },
                    success: function(result) {
                        if(result == 1) {
                            portalDomainFlag = false;
                            $(".portal_domain").append("<div id='portal_domain-error' class='invalid-feedback' style='display:block'>The portal domain has already been taken.</div>");
                        } else {
                            portalDomainFlag = true;
                            $("#portal_domain-error").remove();
                        }
                    }
                });
            }
        });
        
        $companyAddForm.validate().settings.ignore = ":disabled,:hidden";
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $(function() {
        if($("#companyType").val() == 'reseller'){
            $('#is_branding').prop({'checked': true, 'disabled': true});
            $('#enable_survey').prop({'checked': true});
            $('#allow_app').prop({'checked': false, 'disabled': false});
        }
        $('.actions ul li a[href="#previous"]').addClass("disabled");
        
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

        $("#group_content").treeMultiselect({
            enableSelectAll: true,
            searchable: true,
            startCollapsed: true,
            onChange: function (allSelectedItems, addedItems, removedItems) {
                var selectedMembers = $('#group_content').val().length;
                if (selectedMembers == 0) {
                    $('#companyAdd').valid();
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
        $("#staffService").hide();

        $("#dt_wellbeing_sp_ids").on("select2:unselect", function (e) {
            var value=   e.params.data.id;
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
        });

        $(document).on('click', '#staffServiceManagment .fa-times', function(e) {
            var sid = ($(this).data('sid') || 0);
            var wsid = ($(this).data('wsid') || 0);
            $("#service_"+wsid+"_"+sid).remove();
            $('#wshours-'+wsid).remove();
            if($("td#staff-service-"+wsid).html().trim().length == 0){
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
        
         $(document).on('select2:selecting', '#dt_wellbeing_sp_ids', function(e) {
            var _value = e.params.args.data.id
            //var _value = $(this).val();
            var _token = $('input[name="_token"]').val();
            // if(_value.length <= 0) {
            //     $("#staffService").hide();
            // }
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
                    //if(result.staffServices.length > 0){
                        $("#staffService").show();
                        $.each(result.staffServices, function (key, value) 
                        {
                            html+= '<tr id='+key+'><td>' + value.staffName + '</td><td id="staff-service-'+key+'">';
                            wellbeinghoursHtml += '<tr id="wshours-'+key+'">';
                            wellbeinghoursHtml += '<td>' + value.staffName + '</td><td><a class="action-icon text-danger slot-specific" title="{{ trans('buttons.general.tooltip.no-avability-set') }}"><i class="far fa-exclamation-circle"></i></a><a class="action-icon bs-calendar-slidebar" href="javascript:;" id="'+key+'" data-toggle="canvas" data-target="#bs-canvas-right" aria-expanded="false" aria-controls="bs-canvas-right" title="{{ trans('buttons.general.tooltip.edit') }}"><i class="far fa-edit"></i></a><a class="action-icon" href="javascript:;" title="{{ trans('buttons.general.tooltip.set-calendar') }}"> - </a></td>';
                            $.each(value.services, function (k, v) 
                            { 
                                html+= '<span class="service-badge" id="service_'+key+'_'+k+'">'+v+'<i class="fal fa-times" data-sid="'+k+'"  data-wsid="'+key+'"></i><input type="hidden" name="service['+key+'][]" value="'+k+'"></span>';
                            });
                            html+= '</td></tr>';
                            wellbeinghoursHtml += '</td></tr>';
                            
                        });
                        $('#staffServiceManagment').append(html);
                        $('#dtwellbeinghours tbody').append(wellbeinghoursHtml);
                        $("#dt_wellbeing_sp_ids-error").hide();
                        var _hoursby = $('#set_hours_by').val();
                        var _availabilityby = $('#set_availability_by').val();
                        if (_hoursby == 1 && _availabilityby == 2) {
                            $('#set_wellbeing_hours').show();
                        }
                    //}
                    
                }
            });
        });

        $("#survey_roll_out_time").timepicker({
            'showDuration': true,
            'timeFormat': 'H:i',
            'step': 15,
            'useSelect': true,
        });

        $('#subscription_start_date').datepicker({
            startDate: start,
            endDate: end,
            autoclose: true,
            todayHighlight: true,
            format: 'yyyy-mm-dd',
        }).on('changeDate', function () {
            $('#subscription_end_date').val("");
            let subEndDate = $(this).val();
            subEndDate = moment(subEndDate, "YYYY-MM-DD");
            subEndDate.add(1, 'days');
            $('#subscription_end_date').datepicker('setStartDate', new Date(subEndDate));
            $('#subscription_start_date').valid();
        });

        $('#subscription_end_date').datepicker({
            startDate: start,
            endDate: end,
            autoclose: true,
            todayHighlight: true,
            format: 'yyyy-mm-dd',
        }).on('changeDate', function () {
            $('#subscription_start_date').datepicker('setEndDate', new Date($(this).val()));
            $('#subscription_end_date').valid();
        });

        //Add multiple moderators
        $('#addModerator').on('click', function() {
            // Get previous form value
            var currentFormId = $('#total-moderators').val();
            // Increase form value for next iteration.
            currentFormId++;
            // var previousFormId = currentFormId - 1;
           // Get last moderator html source
            var $lastItem = $('.zevo_form_submit .moderators-wrap').last();
            var previousFormId = $lastItem.attr('data-order');
            // Create new clone from lastItem
            var $newItem = $lastItem.clone(true);
            // Insert clone html after last moderator html
            $newItem.insertAfter($lastItem);
            // Leave id increment logic
            $newItem.find(':input').each(function() {
            var name = $(this).attr('name');
            if (name) {
                var name = $(this).attr('name').replace('[' + (previousFormId) + ']', '[' + currentFormId + ']');
                //var id = $(this).attr('id').replace('[' + (previousFormId) + ']', '[' + currentFormId + ']');
                var id = $(this).attr('id').replace(previousFormId, currentFormId);
                // Clean name and id attribute of previous element. DO NOT REMOVE BELLOW LINE, Otherwise it remain old data of previous input.
                $(this).attr({
                    'name': name,
                    'id': id,
                    'data-previewelement': currentFormId,
                    'aria-describedby': name+'-error'
                }).data('previewelement', currentFormId).val('').removeAttr('checked');
            }
            });
            
            // This is used for identify current raw of leave.
            $newItem.closest('.moderators-wrap').attr('data-order', currentFormId);
                $('#total-moderators').val(currentFormId);
        });

        $(document).on('click', '.delete_moderator', function(e) {
            var _id = ($(this).data('id') || 0);
            $('#remove-moderator-box').modal('show');
            $('#remove-moderator-box').attr('data-id',_id);
        
        });

        $(document).on('click', '#remove-moderator-confirm', function(e) {
            var id = $("#remove-moderator-box").attr("data-id");
            if (id) {
                $("tr").filter("[data-id='" + id + "']").remove();
                $('#remove-moderator-box').modal('hide');
                // $('.actions ul li a[href="#next"]').removeClass("disabled");
                if($('.error-feedback').length > 0){
                    $('.actions ul li a[href="#next"]').addClass("disabled");
                }
            }
        });
      });

    $(document).on('change', 'input[id="is_branding"]', function(e) {
        if($(this).is(":checked")) {
            $(".domainBranding-content").removeClass('d-none');
        } else {
            $(".domainBranding-content").addClass('d-none');
        }
    });

    if($("#enable_survey").is(":checked") == true){
        $(".enableSurvey-content").removeClass('d-none');
    }else{
        $(".enableSurvey-content").addClass('d-none');
    }

    $(document).on('change', 'input[id="enable_survey"]', function(e) {
        if($(this).is(":checked")) {
            $(".enableSurvey-content").removeClass('d-none');
        } else {
            $(".enableSurvey-content").addClass('d-none');
        }
    });

    var dtExists = false;
    if($("#companyType").val() != 'zevo'){
        $(document).on('change', '#companyplan', function(e) {
            checkDtExists();
        });
    }

    if($("#companyType").val() == 'normal'){
        checkDtExists();
    }

    if($("#companyType").val() == 'zevo'){
        $(document).on('change', '#companyplan', function(e) {
            var slug = $(this).find(':selected').text();
            if(slug == 'Digital Therapy' || slug == 'Digital Therapy with Challenge'){
                $(".enableDigitalTherapy-content").removeClass('d-none');
                $("#dtExistsHidden").val(1);
            }else{
                $(".enableDigitalTherapy-content").addClass('d-none');
                $("#dtExistsHidden").val(0);
            }
            $("#companyplanSlug").val(slug.split(' ').join('-').toLowerCase());
        });
    }
    

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
            var dtStaffValid    = true;

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
                ) {
                //Check data is exists for the all company and locations
                $("#slot-error").hide();
                $("#slot-wbs-error").hide();
                $("#slot-location-error").hide();
                $("#slot-wbs-error, #slot-location-error, #slot-error").html("");
                
                var wellbeingSp = $('#dt_wellbeing_sp_ids').val();
                if ($("#set_hours_by").val() == 1 && $("#set_availability_by").val() == 1 && $("#slots_exist").val().length == 0 && wellbeingSp.length > 0){
                    dtTabValid = false;
                    $("#slot-error").show();
                    $("#slot-error").html("Please set business hours for atleast one week day");
                } else if ($("#set_hours_by").val() == 1 && $("#set_availability_by").val() == 2 && $(".hiddenfields .general-slots").length == 0 && wellbeingSp.length > 0){
                    dtTabValid = false;
                    $("#slot-wbs-error").show();
                    $("#slot-wbs-error").html("Please set business hours for atleast one wellbeing specialist");
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
            return portalDomainFlag && $companyAddForm.valid();

        },
        onStepChanged: function(event, currentIndex, priorIndex) {
            $('.companySteps').removeClass('current').hide();
            if (currentIndex == 1 && priorIndex == 0) {
                $(".companyDetails-content").addClass("completed");
                $(".moderatorsDetails-content").addClass("active");
                $('#companyAddStep-p-1').show().addClass('current');
                $('.actions ul li a[href="#previous"]').removeClass("disabled");
                $('.select2').select2({allowClear: true,width: '100%'});
                if ($("#span_first_name_0").text().length > 0 && $("#span_last_name_0").text().length > 0 && $("#span_email_0").text().length > 0 && $("#first_name_0").val().length > 0 && $("#last_name_0").val().length > 0 && $("#email_0").val().length > 0 && $("#email_0-error").length == 0 && $("#first_name_0-error").length == 0 && $("#last_name_0-error").length == 0 && $('.error-feedback').length <= 0) {
                    $('.actions ul li a[href="#next"]').removeClass("disabled");
                } else {
                    $('.actions ul li a[href="#next"]').addClass("disabled");
                }
             }else if (currentIndex == 2 && priorIndex == 1 ) {
                if ($("#is_branding").is(":checked") == true) {
                    //domain branding 
                    $('#companyAddStep-p-2').show().addClass('current');
                    $(".domainBranding-content").addClass("active");
                } else if($("#enable_survey").is(":checked") == true) {
                    //survey branding
                    $('#companyAddStep-p-4').show().addClass('current');
                    $(".enableSurvey-content").addClass("active");
                } else {
                    $('#companyAddStep-p-5').show().addClass('current');
                    $(".enableLocation-content").addClass("active");
                }
                $(".moderatorsDetails-content").addClass("completed");
            } else if (currentIndex == 3 && priorIndex == 2 && $("#parent_company").val() != 'zevo'){
                if ($("#is_branding").is(":checked") == true ) {
                    //domain branding 
                    $('#companyAddStep-p-3').show().addClass('current');
                    $(".portalBranding-content").addClass("active");
                } else if($("#enable_survey").is(":checked") == true) {
                    //survey branding
                    $('#companyAddStep-p-4').show().addClass('current');
                    $(".enableSurvey-content").addClass("active");
                } else {
                    //location branding
                    $('#companyAddStep-p-5').show().addClass('current');
                    $(".enableLocation-content").addClass("active");
                }
                $('.select2').select2({allowClear: true,width: '100%'});
                $(".domainBranding-content").addClass("completed");
            } else if (currentIndex == 3 && priorIndex == 2 && $("#parent_company").val() == 'zevo'){
                if ($('.domainBranding-content').hasClass( "active" ) == true) { 
                    //domain branding
                    if ($("#enable_survey").is(":checked") == true) {
                        $('#companyAddStep-p-4').show().addClass('current');
                        $(".enableSurvey-content").addClass("active");
                        $(".domainBranding-content").addClass("completed");
                    } else {
                        $('#companyAddStep-p-5').show().addClass('current');
                        $(".enableLocation-content").addClass("active");
                        $(".domainBranding-content").addClass("completed");
                    }
                } else if ($('.enableSurvey-content').hasClass( "active" ) == true ) {
                    //domain branding 
                    $('#companyAddStep-p-5').show().addClass('current');
                    $(".enableLocation-content").addClass("active");
                    $(".enableSurvey-content").addClass("completed");
                } else if ($('#companyplan').find(':selected').text() == 'Digital Therapy' ||  $('#companyplan').find(':selected').text() == 'Digital Therapy with Challenge' ) {
                    //domain branding 
                    $('#companyAddStep-p-6').show().addClass('current');
                    $(".enableDigitalTherapy-content").addClass("active");
                    $(".enableLocation-content").addClass("completed");
                } else {
                    //location branding
                    $('#companyAddStep-p-7').show().addClass('current');
                    $(".enableManageContent-content").addClass("active");
                    $(".enableLocation-content").addClass("completed");
                    $('.actions ul li a[href="#next"]').parent().hide();
                    $('.actions ul li a[href="#finish"]').parent().show();
                }
            } else if (currentIndex == 4 && priorIndex == 3 && $("#parent_company").val() != 'zevo'){
                if ($("#enable_survey").is(":checked") == true) {
                    //domain branding 
                    $('#companyAddStep-p-4').show().addClass('current');
                    $(".enableSurvey-content").addClass("active");
                } else {
                    //location branding
                    $('#companyAddStep-p-5').show().addClass('current');
                    $(".enableLocation-content").addClass("active");

                }
                $(".portalBranding-content").addClass("completed");
            } else if (currentIndex == 4 && priorIndex == 3 && $("#parent_company").val() == 'zevo'){
                if ($('.domainBranding-content').hasClass( "active" ) == true && $("#enable_survey").is(":checked") == true) {
                    //domain branding 
                    /*$('#companyAddStep-p-4').show().addClass('current');
                    $(".enableSurvey-content").addClass("active");
                    $(".domainBranding-content").addClass("completed");*/
                    $('#companyAddStep-p-5').show().addClass('current');
                    $(".enableLocation-content").addClass("active");
                    $(".enableSurvey-content").addClass("completed");
                } else if ($('.enableLocation-content').hasClass( "active" ) == true && ($('.enableDigitalTherapy-content').hasClass( "active" ) == false && ($('#companyplan').find(':selected').text() == 'Digital Therapy' == 1 || $('#companyplan').find(':selected').text() == 'Digital Therapy with Challenge')) ) {
                        $('#companyAddStep-p-6').show().addClass('current');
                        $(".enableDigitalTherapy-content").addClass("active");
                        $(".enableLocation-content").addClass("completed");
                } else if ($('.enableDigitalTherapy-content').hasClass( "active" ) == true ) {
                        $('#companyAddStep-p-7').show().addClass('current');
                        $(".enableManageContent-content").addClass("active");
                        $('.actions ul li a[href="#next"]').parent().hide();
                        $('.actions ul li a[href="#finish"]').parent().show();
                        $(".enableDigitalTherapy-content").addClass("completed");
                } else {
                    $('#companyAddStep-p-7').show().addClass('current');
                    $(".enableManageContent-content").addClass("active");
                    $(".enableLocation-content").addClass("completed");
                    $('.actions ul li a[href="#next"]').parent().hide();
                    $('.actions ul li a[href="#finish"]').parent().show();
                }
            } else if(currentIndex == 5 && priorIndex == 4 && $("#parent_company").val() != 'zevo'){
                    //location branding
                    if ($('.enableDigitalTherapy-content').hasClass( "active" ) == true ) {
                        console.log("here1");
                        $('#companyAddStep-p-7').show().addClass('current');
                        $(".enableManageContent-content").addClass("active");
                        $('.actions ul li a[href="#next"]').parent().hide();
                        $('.actions ul li a[href="#finish"]').parent().show();
                        $(".enableDigitalTherapy-content").addClass("completed");
                    } else {
                        if($("#enable_survey").is(":checked") == true){
                            $('#companyAddStep-p-5').show().addClass('current');
                            $(".enableLocation-content").addClass("active");
                            $(".enableSurvey-content").addClass("completed");
                        } else if ($("#dtExistsHidden").val() == true &&  $('#companyType').val() == 'reseller') {
                            $('#companyAddStep-p-6').show().addClass('current');
                            $(".enableDigitalTherapy-content").addClass("active");
                            $(".enableLocation-content").addClass("completed");
                        } else {
                            $('#companyAddStep-p-7').show().addClass('current');
                            $(".enableManageContent-content").addClass("active");
                            $('.actions ul li a[href="#next"]').parent().hide();
                            $('.actions ul li a[href="#finish"]').parent().show();
                            $(".enableLocation-content").addClass("completed");
                        }
                    }
                    
            } else if(currentIndex == 5 && priorIndex == 4 && $("#parent_company").val() == 'zevo'){
                //location branding
                if ($('.enableLocation-content').hasClass( "active" ) == true && ($('.enableDigitalTherapy-content').hasClass( "active" ) == false && ($('#companyplan').find(':selected').text() == 'Digital Therapy' || $('#companyplan').find(':selected').text() == 'Digital Therapy with Challenge'))) {
                    $('#companyAddStep-p-6').show().addClass('current');
                    $(".enableDigitalTherapy-content").addClass("active");
                    $(".enableLocation-content").addClass("completed");
                } else if ($('.enableDigitalTherapy-content').hasClass( "active" ) == true) {
                    $('#companyAddStep-p-7').show().addClass('current');
                    $(".enableManageContent-content").addClass("active");
                    $('.actions ul li a[href="#next"]').parent().hide();
                    $('.actions ul li a[href="#finish"]').parent().show();
                    $(".enableDigitalTherapy-content").addClass("completed");
                } else {
                    $('#companyAddStep-p-7').show().addClass('current');
                    $(".enableManageContent-content").addClass("active");
                    $('.actions ul li a[href="#next"]').parent().hide();
                    $('.actions ul li a[href="#finish"]').parent().show();
                    $(".enableLocation-content").addClass("completed");
                }
                
        } else if (currentIndex == 6 && priorIndex == 5 && $("#parent_company").val() != 'zevo'){
                //location branding
                if ($('.enableDigitalTherapy-content').hasClass( "active" ) == false && $("#dtExistsHidden").val() == true) { // &&  $('#companyType').val() == 'reseller'
                    //domain branding 
                    $('#companyAddStep-p-6').show().addClass('current');
                    $(".enableDigitalTherapy-content").addClass("active");
                    $(".enableLocation-content").addClass("completed");
                } else {
                    //location branding
                    
                    $('#companyAddStep-p-7').show().addClass('current');
                    $(".enableManageContent-content").addClass("active");
                    $('.actions ul li a[href="#next"]').parent().hide();
                    $('.actions ul li a[href="#finish"]').parent().show();
                    $(".enableDigitalTherapy-content").addClass("completed");
                    if ($('.enableDigitalTherapy-content').hasClass( "active" ) == true && $("#dtExistsHidden").val() == true){
                        $(".enableDigitalTherapy-content").addClass("completed");    
                    } else {
                        $(".enableLocation-content").addClass("completed");
                    }
                }
                
            } else if (currentIndex == 6 && priorIndex == 5 && $("#parent_company").val() == 'zevo'){
                //location branding
                
                if ($('#companyplan').find(':selected').text() == 'Digital Therapy' ||  $('#companyplan').find(':selected').text() == 'Digital Therapy with Challenge') {
                    //domain branding 
                    $('#companyAddStep-p-7').show().addClass('current');
                    $(".enableManageContent-content").addClass("active");
                    $('.actions ul li a[href="#next"]').parent().hide();
                    $('.actions ul li a[href="#finish"]').parent().show();
                    $(".enableDigitalTherapy-content").addClass("completed");
                } 
                
            } else if (currentIndex == 7 && priorIndex == 6){
                //location branding
                $('#companyAddStep-p-7').show().addClass('current');
                $(".enableManageContent-content").addClass("active");
                $('.actions ul li a[href="#next"]').parent().hide();
                $('.actions ul li a[href="#finish"]').parent().show();

                if ($('#companyplan').find(':selected').text() == 'Digital Therapy' ||  $('#companyplan').find(':selected').text() == 'Digital Therapy with Challenge' || $("#dtExistsHidden").val() == true) {
                    $(".enableDigitalTherapy-content").addClass("completed");
                } else {
                    $(".enableLocation-content").addClass("completed");
                }
            } else {

            }

            //Previous fun
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
                if($("#first_name_0").val().length > 0 && $("#last_name_0").val().length > 0 && $("#email_0").val().length > 0 && $("#email_0-error").length == 0 && $("#first_name_0-error").length == 0 && $("#last_name_0-error").length == 0 && $('.error-feedback').length <= 0){
                    $('.actions ul li a[href="#next"]').removeClass("disabled");
                }else{
                    $('.actions ul li a[href="#next"]').addClass("disabled");
                }

                /*if(($('#moderatorsManagment').find('tr').length!='undefined' && $('#moderatorsManagment').find('tr').length > 1) && $('.error-feedback').length <= 0){
                    $('.actions ul li a[href="#next"]').removeClass("disabled");
                }else{
                    $('.actions ul li a[href="#next"]').addClass("disabled");
                }*/
                
            }else if(currentIndex == 2 && priorIndex == 3 && $("#parent_company").val() != 'zevo'){
                if($("#is_branding").is(":checked") == true ) { //&& $('input[name="is_reseller"]:checked').val() == 'yes'
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
                
            }else if(currentIndex == 2 && priorIndex == 3 && $("#parent_company").val() == 'zevo'){
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
            }else if(currentIndex == 3 && priorIndex == 4 && $("#parent_company").val() != 'zevo'){
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
            }else if(currentIndex == 3 && priorIndex == 4 && $("#parent_company").val() == 'zevo'){
                if($('.enableManageContent-content').hasClass( "active" ) == true) {
                    if($('#companyplan').find(':selected').text() == 'Digital Therapy'|| $('#companyplan').find(':selected').text() == 'Digital Therapy with Challenge'){
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
                }else if($('.enableLocation-content').hasClass( "active" ) == false && ($('#companyplan').find(':selected').text() == 'Digital Therapy with Challenge' || $('#companyplan').find(':selected').text() == 'Digital Therapy')){
                    $('#companyAddStep-p-6').hide().removeClass('current');
                    $(".enableDigitalTherapy-content").removeClass("active");
                    $(".enableLocation-content").removeClass("completed");
                    $(".enableLocation-content").addClass("active");
                    $("#companyAddStep-p-5").show().addClass('current');
                }else if($('.enableLocation-content').hasClass( "active" ) == true){
                    if($('.enableDigitalTherapy-content').hasClass( "active" ) == true &&  ($('#companyplan').find(':selected').text() == 'Digital Therapy with Challenge' || $('#companyplan').find(':selected').text() == 'Digital Therapy')){
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
                }else{
                    $('#companyAddStep-p-6').hide().removeClass('current');
                    $(".enableDigitalTherapy-content").removeClass("active");
                    $(".enableLocation-content").removeClass("completed");
                    $(".enableLocation-content").addClass("active");
                    $("#companyAddStep-p-5").show().addClass('current');
                }
            }else if(currentIndex == 4 && priorIndex == 5 && $("#parent_company").val() != 'zevo'){
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
                    
            }else if(currentIndex == 4 && priorIndex == 5 && $("#parent_company").val() == 'zevo'){
                //location branding
                if($('.enableManageContent-content').hasClass( "active" ) == true && ($('#companyplan').find(':selected').text() == 'Digital Therapy with Challenge'|| $('#companyplan').find(':selected').text() == 'Digital Therapy')) {
                    $('#companyAddStep-p-7').hide().removeClass('current');
                    $(".enableManageContent-content").removeClass("active");
                    $('.actions ul li a[href="#next"]').parent().show();
                    $('.actions ul li a[href="#finish"]').parent().hide();
                    $('#companyAddStep-p-6').show().addClass('current');
                    $(".enableDigitalTherapy-content").addClass("active");
                    $(".enableDigitalTherapy-content").removeClass("completed");
                }else if($('#companyplan').find(':selected').text() == 'Digital Therapy with Challenge'|| $('#companyplan').find(':selected').text() == 'Digital Therapy'){
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
            }else if(currentIndex == 5 && priorIndex == 6 && $("#parent_company").val() != 'zevo'){
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
                    console.log(222);
                    //location branding
                    $('#companyAddStep-p-7').hide().removeClass('current');
                    $(".enableManageContent-content").removeClass("active");
                    $('.actions ul li a[href="#next"]').parent().show();
                    $('.actions ul li a[href="#finish"]').parent().hide();
                    $(".enableLocation-content").addClass("active");
                    $(".enableLocation-content").removeClass("completed");
                    $('#companyAddStep-p-5').show().addClass('current');
                }
                
            }else if(currentIndex == 5 && priorIndex == 6 && $("#parent_company").val() == 'zevo'){
                //location branding
                $('#companyAddStep-p-7').hide().removeClass('current');
                $(".enableManageContent-content").removeClass("active");
                $('.actions ul li a[href="#next"]').parent().hide();
                $('.actions ul li a[href="#finish"]').parent().show();
                if($('#companyplan').find(':selected').text() == 'Digital Therapy with Challenge'  ||  $('#companyplan').find(':selected').text() == 'Digital Therapy' ) {
                    $(".enableDigitalTherapy-content").removeClass("completed");
                    $('#companyAddStep-p-6').show().addClass('current');
                    $(".enableDigitalTherapy-content").addClass("active");
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
        },
        onFinished: function(event, currentIndex) {
            var selectedMembers = $('#group_content').val().length;
            var _token = $('input[name="_token"]').val();
            if (selectedMembers != 0) {
                if($companyAddForm.valid() == true) {
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
                            $('#companyAdd').valid();
                            $('#group_content-min-error').hide();
                            $('#group_content-error').show();
                            $('.tree-multiselect').css('border-color', '#f44436');
                        } else {
                            if($companyAddForm.valid() == true) {
                                $companyAddForm.submit();
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
                    $companyAddForm.valid();
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


    });
</script>
@if($isShowContentType)
<script type="text/javascript">
function formSubmit() {
    var selectedMembers = $('#group_content').val().length;
    var _token = $('input[name="_token"]').val();
    if (selectedMembers != 0) {
        if($('#companyAdd').valid() == true) {
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
                    $('#companyAdd').valid();
                    $('#group_content-min-error').hide();
                    $('#group_content-error').show();
                    $('.tree-multiselect').css('border-color', '#f44436');
                } else {
                    if($('#companyAdd').valid() == true) {
                        $('#companyAdd').submit();
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
        $('#companyAdd').valid();
        $('#group_content-min-error').hide();
        $('#group_content-error').show();
        $('.tree-multiselect').css('border-color', '#f44436');
    }
}

function removeFrmArr(array, element) {
    return array.filter(e => e !== element);
}

function checkEmailExists(email){
    $.ajax({
        url: emailValidateURL,
        method: 'post',
        data: {
            'email' : email
        },
        //dataType: 'json',
        success: function(result) {
            if(result == 'exists'){
                $('#email_0').after('<div id="email_0-error" class="error error-feedback">Email already exists</div>');
                $("#save_moderator_0").hide();
            }else if(result == 'disposable'){
                $('#email_0').after('<div id="email_0-error" class="error error-feedback">Disposable email not allow</div>');
                $("#save_moderator_0").hide();
            } else {
                $('#email_0-error').remove();
                $("#save_moderator_0").show();
            }
        },
        error: function(data){
            //error
        }
    });
}

// to display survey rollout visiblity block.
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
                company: null,
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
           // toastr.error(error.responseJSON.data || message.something_went_wrong);
        });
    }
}

/*  function that will reset the wizard form */
function resetJQuerySteps(elementTarget, noOfSteps){
    var noOfSteps = noOfSteps - 1;

    var currentIndex = $(elementTarget).steps("getCurrentIndex");
        if(currentIndex >= 0){
            for(var x = 0; x < currentIndex;x++){
                $(elementTarget).steps("previous");
            }
        }
    $('.companySteps').removeClass('current').hide();    
    setTimeout(function resetHeaderCall(){ 
    var y, steps;
        for(y = 0, steps= 9; y < noOfSteps;y++){
            try{
                $(`${elementTarget} > .steps > ul > li:nth-child(${steps})`).removeClass("done");
                    $(`${elementTarget} > .steps > ul > li:nth-child(${steps})`).removeClass("current");
                    $(`${elementTarget} > .steps > ul > li:nth-child(${steps})`).addClass("disabled");

            }
            catch(err){}
        steps++;
        }
       
    }, 50);
    setTimeout(function (){
        $('.companySteps').removeClass('current').hide();   
        $(".comanytabs").removeClass('completed active');
        $(".companyDetails-content").addClass('active');
        $('#companyAddStep-p-0').show().addClass('current');
        $(".enableDigitalTherapy-content").addClass('d-none');
        $('.actions ul li a[href="#next"]').removeClass("disabled");
        $('.actions ul li a[href="#prev"]').addClass("disabled");
        $('#assigned_roles').select2().trigger('change');
    }, 500);
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
            console.log(result);
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
            console.log("===",dtExists);
        }
    });
}
</script>
{{-- <script src="{{ mix('js/company/create.js') }}">
</script> --}}
<script src="{{ mix('js/cronofy/slots.js') }}"></script>
<script src="{{ mix('js/cronofy/custom-specific-date.js') }}"></script>
@endif
@endsection
