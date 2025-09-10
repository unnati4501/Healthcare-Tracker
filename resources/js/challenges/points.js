$('#managePoints').submit(function(e) {
    var selectedMembers = $('#group_member').val().length;
    if (selectedMembers == 0) {
        event.preventDefault();
        $('#managePoints').valid();
        $('#group_member-error').show();
        $('.tree-multiselect-box').css('border-color', '#f44436');
    } else {
        $('#group_member-error').hide();
        $('.tree-multiselect-box').css('border-color', '#D8D8D8');
    }
});
$("#group_member").treeMultiselect({
    enableSelectAll: true,
    searchable: true,
    startCollapsed: true,
    onChange: function(allSelectedItems, addedItems, removedItems) {
        var selectedMembers = $('#group_member').val().length;
        if (selectedMembers == 0) {
            $('#managePoints').valid();
            $('#group_member-error').show();
            $('.tree-multiselect-box').css('border-color', '#f44436');
        } else {
            $('#group_member-error').hide();
            $('.tree-multiselect-box').css('border-color', '#D8D8D8');
        }
    }
});
$("#start_date").keypress(function(event) {
    event.preventDefault();
});
$('#start_date').datepicker({
    format: 'yyyy-mm-dd',
    startDate: startDate,
    endDate: currentDate,
    autoclose: true,
});