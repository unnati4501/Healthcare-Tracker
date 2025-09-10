$(document).ready(function() {
    $(document).on('change', '#auto_team_creation', function(e) {
        var isChecked = $(this).is(":checked");
        ((isChecked == true) ?  $('#team_limit_block').removeClass('d-none') : $('#team_limit_block').addClass('d-none'));
    })
});