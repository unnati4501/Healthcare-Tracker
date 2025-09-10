$(document).ready(function() {
    $("#selectetSurveyUsers").mCustomScrollbar({
        axis: "y",
        scrollButtons: {
            enable: true
        },
        theme: "inset-dark"
    });
    $("#survey-users").treeMultiselect({
        enableSelectAll: true,
        searchable: true,
        startCollapsed: true,
        onChange: function(allSelectedItems, addedItems, removedItems) {
            var selectedMembers = $('#survey-users').val().length;
            if (selectedMembers == 0) {
                $('#surveyConfigForm').valid();
                $('#survey-users-error').show();
                $('.tree-multiselect').css('border-color', '#f44436');
            } else {
                $('#survey-users-error').hide();
                $('.tree-multiselect').css('border-color', '#D8D8D8');
            }
        }
    });

    $(document).on('change', "input[name='survey_for_all']", function(e) {
        if ($(this).val() == 'on') {
            $('#surveyUsersWrapper').addClass('d-none');
        } else {
            $('#surveyUsersWrapper').removeClass('d-none');
        }
       
    });

    $('#surveyConfigForm').ajaxForm({
        type: 'POST',
        dataType: 'json',
        beforeSend: function() {
            // check survey for all checkbox
            var survey_for_all = $('#survey_for_all').is(':checked');
            // hide error
            $('#survey-users-error').hide();
            $('.tree-multiselect').css('border-color', '#D8D8D8');
            // validation for users if survey_for_all is set to false
            if (survey_for_all == false) {
                var selectedMembers = $('#survey-users').val().length;
                if (selectedMembers == 0) {
                    $('#survey-users-error').show();
                    $('.tree-multiselect').css('border-color', '#f44436');
                    return false;
                }
            }
            $('.page-loader-wrapper').show();
        },
        success: function(data) {
            if (data.status && data.status == 1) {
                window.location.replace(url.success);
            }
        },
        error: function(data) {
            toastr.error((data?.responseJSON?.message || messages.error));
        },
        complete: function(xhr) {
            $('.page-loader-wrapper').hide();
        }
    });
});