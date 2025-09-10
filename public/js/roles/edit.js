function formSubmit() {
    var selectedMembers = $('#set_privileges').val().length;
    if (selectedMembers == 0) {
        event.preventDefault();
        $('#roleEdit').valid();
        $('#set_privileges_error').show();
        $('.tree-multiselect').css('border-color', '#f44436');
    } else {
        $('#set_privileges_error').hide();
        $('.tree-multiselect').css('border-color', '#D8D8D8');
    }
}
$(document).ready(function() {
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
    // Custom Scrolling
    if ($("#setPermissionList").length > 0) {
        $.mCustomScrollbar.defaults.scrollButtons.enable = true;
        $.mCustomScrollbar.defaults.axis = "yx";
        $("#setPermissionList").mCustomScrollbar({
            axis: "y",
            theme: "inset-dark"
        });
    }
});