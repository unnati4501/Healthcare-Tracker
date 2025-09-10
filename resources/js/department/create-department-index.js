$(document).ready(function () {
    $('.select2').select2({
        allowClear: true,
        width: '100%'
    });

    $.validator.addMethod("ecRequired", $.validator.methods.required, message.employeeCountError);
    $.validator.messages.min = function (param, input) { return message.employeeCountLength + `${param}.`; }
    $.validator.messages.max = function (param, input) { return message.employeeCountGreater + `${param}.`; }
    $.validator.addClassRules("ecClass", {ecRequired: true, number: true, min:1, max:10000});

    $(document).on('mouseenter', '.select2-container .select2-dropdown', function (e) {
        var selectId = $(this).find("ul").attr('id').replace("select2-", "").replace("-results", "");
        if (selectId == 'multilocation') {
            $(this).parent().addClass("select2-checkbox");
        }
    });

    var company = $('#company_id').val();
    if (company != '' && company != undefined) {
        $('#company_id').trigger('change');
    }

    setTimeout(function(){
        var comp = data.oldCompanyId;
        if (comp != '' && comp != undefined) {
            $('#company_id').select2('val', comp);
        }
    }, 1000);

    $('.select2').change(function() {
        companyLocationUrl = companyLocationUrl;

        if ($(this).val() != '' && $(this).val() != null) {
            if ($(this).attr("id") == 'company_id') {
                var select = $(this).attr("id");
                var value = $(this).val();
                var dependent = $(this).attr('data-dependent');
                var _token = $('input[name="_token"]').val();

                $('#' + dependent).attr('disabled', true);
                url = companyLocationUrl.replace(':id', value);

                $.ajax({
                    url: url,
                    method: 'get',
                    data: {
                        _token: _token
                    },
                    success: function(result) {
                        $('#' + dependent).empty();
                        $('#' + dependent).attr('disabled', false);
                        $('#' + dependent).removeClass('is-valid');
                        $.each(result.result, function(key, value) {
                            $('#' + dependent).append('<option value="' + value.id + '">' + value.name + '</option>');
                        });
                        if (Object.keys(result.result).length == 1) {
                            $.each(result.result, function(key, value) {
                                $('#' + dependent).select2('val', value.id);
                            });
                        }
                        $("#multilocation").select2("val", "");
                        $('#location_list').empty();
                        $('#team-block').hide();
                    }
                })
            }
        }
    });

    $('#departmentAdd').ajaxForm({
        beforeSend: function() {
            $('.toast').remove();
            var preventSubmit = true;
            if($('#location_list').children().length > 0) {
                var teamNames = [];
                $('.show-suggestions').each(function(index, teamName) {
                    var name = $(teamName).val().toLowerCase();
                    if(index > 0 && $.inArray(name, teamNames) >= 0) {
                        toastr.error(message.teamUnique);
                        preventSubmit = false;
                        return false;
                    }
                    teamNames.push(name);
                });
            }
            if(preventSubmit) {
                showPageLoaderWithMessage(validation.processing);
            }
            return preventSubmit;
        },
        success: function(data) {
            if(data.status && data.status == 1) {
                window.location.href = departmentUrl;
            } else {
                if(data.message && data.message != '') { toastr.error(message.somethingWrongTryAgain); }
            }
        },
        error: function(data) {
            toastr.error(data?.responseJSON?.message || message.somethingWrongTryAgain)
        },
        complete: function(xhr) {
            hidesPageLoader();
        }
    });
});