function formSubmit() {
    var selectedMembers = $('#set_privileges').val().length;
    if (selectedMembers == 0) {
        event.preventDefault();
        $('#roleAdd').valid();
        $('#set_privileges_error').show();
        $('.tree-multiselect').css('border-color', '#f44436');
    } else {
        $('#set_privileges_error').hide();
        $('.tree-multiselect').css('border-color', '#D8D8D8');
    }
}

function setPrivileges() {
    $("#set_privileges").treeMultiselect({
        enableSelectAll: true,
        searchable: true,
        startCollapsed: true,
        onChange: function(allSelectedItems, addedItems, removedItems) {
            var selectedMembers = $('#set_privileges').val().length;
            if (selectedMembers == 0) {
                $('#set_privileges_error').show();
                $('.tree-multiselect').css('border-color', '#f44436');
            } else {
                $('#set_privileges_error').hide();
                $('.tree-multiselect').css('border-color', '#D8D8D8');
            }
        }
    });
}
$(document).ready(function() {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    setPrivileges();
    // Custom Scrolling
    if ($("#setPermissionList").length > 0) {
        $.mCustomScrollbar.defaults.scrollButtons.enable = true;
        $.mCustomScrollbar.defaults.axis = "yx";
        $("#setPermissionList").mCustomScrollbar({
            axis: "y",
            theme: "inset-dark"
        });
    }
    $('.roleGroup').change(function() {
        var group = this.value;
        $.ajax({
            url: permissionsUrl.replace(':group', group),
            method: 'GET',
            success: function(result) {
                $('#setPermissionList').empty();
                $('#setPermissionList').append(result.body);
                setPrivileges();
            }
        });
    });
});