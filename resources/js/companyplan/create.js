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
    $(document).on('click','#companyPlanSubmit',function(event){
        $('#companyplanadd').valid();
        var setPrivileges = $('#set_privileges').val().length;
        if (setPrivileges == 0) {
            event.preventDefault();
            $('#companyplanadd').valid();
            $('#set_privileges_error').show();
            $('.tree-multiselect').css('border-color', '#f44436');
        } else {
            $('.tree-multiselect').css('border-color', '#D8D8D8');
        }
    });

    $(document).on('change','.roleGroup',function(){
        var group = this.value;
        $.ajax({
            url: cpPlanFeaturesUrl.replace(':group', group),
            method: 'GET',
            success: function(result) {
                $('#setPermissionList').empty();
                $('#setPermissionList').append(result.body);
                setPrivileges();
            }
        });
    });
});