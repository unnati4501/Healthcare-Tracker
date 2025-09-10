@extends('layouts.app')

@section('after-styles')
@if($isShowContentType)
<link href="{{asset('assets/plugins/tree-multiselect/tree-multiselect.css?var='.rand())}}" rel="stylesheet"/>
@endif
<link href="{{asset('assets/plugins/datepicker/datepicker3.css?var='.rand())}}" rel="stylesheet"/>
<link href="{{asset('assets/plugins/timepicker/bootstrap-timepicker.min.css?var='.rand())}}" rel="stylesheet"/>
<style type="text/css">
    .prevent-events { pointer-events: none; }
</style>
@endsection

@section('content-header')
<!-- Content Header (Page header) -->
@include('admin.companies_old.breadcrumb', [
    'mainTitle'  => trans('company.title.add'),
    //'breadcrumb' => Breadcrumbs::render('companiesold.create'),
    'companyType'=> $companyType
])
<!-- /.content-header -->
@endsection

@section('content')
<section class="content">
    <div class="container-fluid">
        <div class="card form-card">
            {{ Form::open(['route' => ['admin.companiesold.store', $companyType], 'class' => 'form-horizontal zevo_form_submit', 'method' => 'post', 'role' => 'form', 'id' => 'companyAdd', 'files' => true]) }}
            {{Form::hidden('companyType', $companyType)}}
            <div class="card-body">
                @include('admin.companies_old.form', ['edit' => false])
            </div>
            <div class="card-footer">
                <div class="save-cancel-wrap">
                    <a class="btn btn-outline-primary" href="{{ route('admin.companiesold.index', $companyType) }}">
                        {{ trans('buttons.general.cancel') }}
                    </a>
                    @if($isShowContentType)
                    <button class="btn btn-primary" onclick="formSubmit();" type="submit">
                        {{ trans('buttons.general.save') }}
                    </button>
                    @else
                    <button class="btn btn-primary" type="submit">
                        {{ trans('buttons.general.save') }}
                    </button>
                    @endif
                </div>
            </div>
            {{ Form::close() }}
        </div>
    </div>
</section>
@endsection

@section('after-scripts')
{!! JsValidator::formRequest('App\Http\Requests\Admin\CreateCompanyRequest','#companyAdd') !!}
<script src="{{asset('assets/plugins/datepicker/bootstrap-datepicker.js?var='.rand())}}">
</script>
<script src="{{asset('assets/plugins/timepicker/bootstrap-timepicker.js?var='.rand())}}">
</script>
@if($isShowContentType)
<script src="{{ asset('assets/plugins/tree-multiselect/tree-multiselect.js?var='.rand()) }}">
</script>
@endif
<script type="text/javascript">
    var start = new Date(),
        end = new Date('{{ $maxEndDate }}'),
        stateUrl = '{{ route("admin.ajax.states", ":id") }}',
        tzUrl = '{{ route("admin.ajax.timezones", ":id") }}',
        contentValidateURL = '{{ route("admin.ajax.checkcompaniescontentvalidate") }}';
        roleGroup = '{{ $user_role->group }}';
    var message = {
        upload_image_dimension: '{{ trans('company.messages.upload_image_dimension') }}'
    };
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
                    if((this.width < imageWidth && this.height < imageHeight) || ratioGcd != aspectedRatio){
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
                // alert(data.portal_theme);
                // $('#portal_theme').val(data.portal_theme);
                $("#portal_theme option[value='"+ data.portal_theme +"']").attr("selected", "selected");
                $('#portal_theme').select2().trigger('change');
            }
        } else {
            $('#sub_domain, #onboarding_title, #onboarding_description, #portal_domain, #portal_title, #portal_description, #portal_theme').val('');
            $("#sub_domain").trigger('change');
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
    }

    function getParentCoData() {
        var payload = {
            company: $('#parent_company').val(),
            is_reseller: $('input[name="is_reseller"]:checked').val(),
        };
        $.ajax({
            url: "{{ route('admin.companiesold.resellerDetails') }}",
            type: 'GET',
            dataType: 'json',
            data: payload
        })
        .done(function(data) {
            var roleOptionsHtml = "";
            if(data.roles) {
                $(data.roles).each(function(index, role) {
                    roleOptionsHtml += `<option value="${role.id}">${role.name}</option>`;
                });
            }
            $('#assigned_roles').html(roleOptionsHtml).val('').select2('destroy').select2();

            if(data.subscription.start_date && data.subscription.end_date) {
                $('#subscription_start_date').datepicker('setStartDate', new Date(data.subscription.start_date));
                $('#subscription_start_date').datepicker('setEndDate', new Date(data.subscription.end_date));
                $('#subscription_end_date').datepicker('setStartDate', new Date(data.subscription.start_date));
                $('#subscription_end_date').datepicker('setEndDate', new Date(data.subscription.end_date));
            } else {
                $('#subscription_start_date').datepicker('setStartDate', start);
                $('#subscription_start_date').datepicker('setEndDate', end);
                $('#subscription_end_date').datepicker('setStartDate', start);
                $('#subscription_end_date').datepicker('setEndDate', end);
            }

            $('#subscription_start_date').datepicker('setDate', data.subscription.start_date);
            $('#subscription_end_date').datepicker('setDate', data.subscription.end_date);
            $('#subscription_start_date-error, #subscription_end_date-error').hide();
            $('#subscription_start_date, #subscription_end_date').removeClass('is-valid is-invalid');
            setBrandingsValue(data.branding);


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
        if($('#setPermissionList').length > 0) {
            $("#setPermissionList").mCustomScrollbar({
                axis: "y",
                scrollButtons: {
                    enable: true
                },
                theme: "inset-dark"
            });
        }

        $('#survey_roll_out_time').timepicker({
            showInputs: false,
            showMeridian: false,
            defaultTime: '00:00'
        });

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

        $(document).on('change', '#logo, #login_screen_logo, #login_screen_background, #email_header, #portal_logo_main, #portal_logo_optional, #portal_background_image', function (e) {
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
                } else if ($.inArray(id, ['logo', 'login_screen_logo', 'portal_logo_main', 'portal_logo_optional']) !== -1 && e.target.files[0].size > 2097152) {
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

        $("#sub_domain").on('keyup change', function (e) {
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

        $('.select2').change(function() {
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
                                //console.log(key, value);
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

        $('#is_premium').on('change', function(e) {
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
            if(checkedValue == "yes") {
                // parent company selection
                $('[data-parent-co-wrapper]').hide();

                // portal domain textbox
                $('[data-portal-domain-wrapper]').show();

                // allow-app checkbox flag
                $('[data-allow-app-wrapper]').hide();
                $('#allow_app').prop({'checked': false});
                $('#allow_app').parent('label').removeClass('prevent-events');


                // branding and survey flag
                $('#is_branding').prop({'checked': true, 'disabled': false}).trigger('change');
                $('#enable_survey').prop({'checked': true, 'disabled': false}).trigger('change');
                $('#is_branding').parent('label').addClass('prevent-events');
                $('#enable_survey').parent('label').addClass('prevent-events');
                $('#reseller_loader').show();

                // eanble branding fields
                $('#branding_wrapper :input').prop('disabled', false);
                $('#portal_branding_wrapper').fadeIn('slow');
                $('#portal_branding_wrapper :input').prop('disabled', false);
                $('#portal_domain option:selected').removeAttr('selected');
                $('#portal_domain').select2().trigger('change');

                // Showing tooptip for subscription end date field for child companies
                $('#subscription_end_date_tooltip').addClass('d-none');

                // EAP module enable who has mobile access
                // $('#eap_tab_counsellor').hide();
                $('#eap_tab').prop({'checked': false});

                // Company plan dropdown hide for reseller
                $('#companyplandiv').hide();
                $('.companiesplan').show();

                // disable 'enable survey' fields
                $('#enable_event').prop({'checked': false, 'disabled': true});
                $('#enable_event_wrapper').hide();

                getParentCoData();
            } else {
                // parent company selection
                $('[data-parent-co-wrapper]').show();
                $('#portal_branding_wrapper').hide();
                $('#portal_branding_wrapper :input').prop('disabled', true);                
                $('#parent_company').trigger('change');
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
                $('#enable_survey').prop({'checked': true, 'disabled': true}).trigger('change');

                // disable branding fields
                $('#branding_wrapper :input').prop('disabled', true);
                $('#portal_branding_wrapper').show();
                // $('#portal_branding_wrapper :input').prop('disabled', true);

                // Showing tooptip for subscription end date field for child companies
                $('#subscription_end_date_tooltip').removeClass('d-none');

                // EAP module enable who has mobile access
                // $('#eap_tab_counsellor').hide();
                $('#eap_tab').prop({'checked': false});

                // Company plan dropdown hide for reseller
                $('#companyplandiv').hide();
                $('.companiesplan').show();

                // disable 'enable survey' fields
                $('#enable_event').prop({'checked': false, 'disabled': true});
                $('#enable_event_wrapper').hide();
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
                $('#branding_wrapper :input').prop('disabled', false);
                $('#portal_branding_wrapper').hide();
                // $('#portal_branding_wrapper').fadeIn('slow');
                // $('#portal_branding_wrapper :input').prop('disabled', false);

                // Showing tooptip for subscription end date field for child companies
                $('#subscription_end_date_tooltip').addClass('d-none');

                // EAP module enable who has mobile access
                $('#eap_tab_counsellor').hide();

                // Company plan dropdown hide for reseller
                $('.companiesplan').hide();
                $('#companyplandiv').show();
            }
            $('#parent_co_loader').show();
            getParentCoData();
        });

        if(roleGroup == 'reseller'){
            // Showing tooptip for subscription end date field for child companies
            $('#subscription_end_date_tooltip').removeClass('d-none');
        }

        // $('#allow_app').change(function() {
        //     // EAP module enable who has mobile access
        //     if(this.checked) {
        //         // $('#eap_tab_counsellor').show();
        //     } else {
        //         // $('#eap_tab_counsellor').hide();
        //         $('#eap_tab').prop({'checked': false});
        //     }
        // });
    });
</script>
@if($isShowContentType)
<script type="text/javascript">
    $(document).ready(function(){
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
});
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
</script>
@endif
@endsection
