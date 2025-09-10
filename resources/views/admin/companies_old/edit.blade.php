@extends('layouts.app')

@section('after-styles')
@if($isShowContentType)
<link href="{{asset('assets/plugins/tree-multiselect/tree-multiselect.css?var='.rand())}}" rel="stylesheet"/>
@endif
<link href="{{asset('assets/plugins/datepicker/datepicker3.css?var='.rand())}}" rel="stylesheet"/>
<link href="{{asset('assets/plugins/timepicker/bootstrap-timepicker.min.css?var='.rand())}}" rel="stylesheet"/>
<style type="text/css">
    #survey_roll_out_time.form-control[readonly] { background-color: transparent; }
</style>
@endsection

@section('content-header')
<!-- Content Header (Page header) -->
@include('admin.companies_old.breadcrumb', [
    'mainTitle'   => trans('company.title.edit'),
    //'breadcrumb'  => Breadcrumbs::render('companiesold.edit'),
    'companyType' => $companyType
])
<!-- /.content-header -->
@endsection

@section('content')
<section class="content">
    <div class="container-fluid">
        <div class="card form-card">
            {{ Form::open(['route' => ['admin.companiesold.update', [$companyType, $recordData->id]], 'class' => 'form-horizontal zevo_form_submit', 'method' => 'PATCH', 'role' => 'form', 'id' => 'companyEdit', 'files' => true]) }}
            {{ Form::hidden('companyType', $companyType)}}
            <div class="card-body">
                @include('admin.companies_old.form', ['edit' => true])
            </div>
            <div class="card-footer">
                <div class="save-cancel-wrap">
                    <a class="btn btn-outline-primary" href="{{ route('admin.companiesold.index',$companyType) }}">
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
            </div>
            {{ Form::close() }}
        </div>
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
@endsection

@section('after-scripts')
{!! JsValidator::formRequest('App\Http\Requests\Admin\EditCompanyRequest','#companyEdit') !!}
<script src="{{asset('assets/plugins/datepicker/bootstrap-datepicker.js?var='.rand())}}">
</script>
<script src="{{asset('assets/plugins/timepicker/bootstrap-timepicker.js?var='.rand())}}">
</script>
@if($isShowContentType)
<script src="{{ asset('assets/plugins/tree-multiselect/tree-multiselect.js?var='.rand()) }}">
</script>
@endif
<script type="text/javascript">
    var stateUrl = '{{ route("admin.ajax.states", ":id") }}',
        tzUrl = '{{ route("admin.ajax.timezones", ":id") }}',
        max_end_date = new Date('{{ $max_end_date }}'),
        start_start_date = new Date('{{ $start_start_date }}'),
        end_start_date = new Date('{{ $end_start_date }}'),
        company_roles = $('#assigned_roles').val(),
        users_attached_role = {!! $usersAttachedRole !!};
        roleGroup = '{{ $user_role->group }}';
        contentValidateURL = '{{ route("admin.ajax.checkcompaniescontentvalidate") }}';
    var message = {
        upload_image_dimension: '{{ trans('company.messages.upload_image_dimension') }}'
    };
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
                    if((this.width < imageWidth && this.height < imageHeight) || ratioGcd != aspectedRatio){
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
        $('#survey_roll_out_time').timepicker({
            showInputs: false,
            showMeridian: false,
            defaultTime: '00:00'
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
            $('#subscription_end_date').datepicker('setStartDate', new Date($(this).val()));
            $('#subscription_start_date, #subscription_end_date').valid();
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

        // $('#subscription_start_date').val('{{ old('subscription_start_date', $recordData->subscription_start_date->toDateString()) }}');
        // $('#subscription_end_date').val('{{ old('subscription_end_date', $recordData->subscription_end_date->toDateString()) }}');

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

        // var allowApp = $('#allow_app').prop("checked");
        // if (allowApp == false) {
        //     // $('#eap_tab_counsellor').hide();
        //     $('#eap_tab').prop({'checked': false});
        // }
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
</script>
@endif
@endsection
