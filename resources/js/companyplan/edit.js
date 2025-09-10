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
    setPrivileges();
    $(document).on('click','#companyplanSubmit',function(event){
        $('#companyplanedit').valid();
        var setPrivileges = $('#set_privileges').val().length;
        if (setPrivileges == 0 || setPrivileges == '') {
            event.preventDefault();
            $('#companyplanedit').valid();
            $('#set_privileges_error').show();
            $('.tree-multiselect').css('border-color', '#f44436');
        } else {
            $('.tree-multiselect').css('border-color', '#D8D8D8');
        }
    });
});