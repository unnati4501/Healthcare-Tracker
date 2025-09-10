$(document).ready(function() {
    $('#multilocation').select2({
        placeholder: "Select Locations",
        multiple: true,
        closeOnSelect: true,
    });

    $.validator.addMethod("ecRequired", $.validator.methods.required, message.employeeCountError);
    $.validator.addMethod("ncRequired", $.validator.methods.required, message.namingConvention);
    $.validator.messages.maxlength = function (param, input) {
        return message.namingConventionParam.replace(':param', param);
    }
    $.validator.messages.min = function (param, input) { return message.employeeCountLength + `${param}.`; }
    $.validator.messages.max = function (param, input) { return message.employeeCountGreater + `${param}.`; }
    $.validator.addClassRules("ecClass", {ecRequired: true, number: true, min:1, max:10000});
    $.validator.addClassRules("ncClass", {ncRequired: true, minlength:1, maxlength:30});

    $(document).on('mouseenter', '.select2-container .select2-dropdown', function (e) {
        var selectId = $(this).find("ul").attr('id').replace("select2-", "").replace("-results", "");
        if (selectId == 'multilocation') {
            $(this).parent().addClass("select2-checkbox");
        }
    });

    if(!askForAutoTeamCreation){
        $('#multilocation').on('select2:unselecting', function(e) {
            $('.toast').remove();
            var item = e.params.args.data;
            if ($.inArray(parseInt(item.id), locationsBeingUsed) >= 0) {
                toastr.error(error.deptRemove);
                e.preventDefault();
            }
        });
    }

    $('#departmentEdit').ajaxForm({
        beforeSend: function() {
            var preventSubmit = true;
            if($('#location_list').children().length > 0) {
                var teamNames = [];
                $('.show-suggestions').each(function(index, teamName) {
                    var name = $(teamName).val().toLowerCase();
                    if(index > 0 && $.inArray(name, teamNames) >= 0) {
                        $('.toast').remove();
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