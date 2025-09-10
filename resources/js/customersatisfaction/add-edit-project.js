$(document).ready(function() {
    var start = new Date(new Date().setDate(new Date().getDate() + 1));

    $('#start_date').datepicker({
        startDate: start,
        autoclose: true,
        todayHighlight: true,
        format: 'yyyy-mm-dd',
    }).on('changeDate', function () {
        var stdate = new Date();
        if($(this).val() != '')
            stdate = $(this).val();

        $('#end_date').datepicker('setStartDate', new Date(stdate));

        if(new Date($('#end_date').val()) < new Date($('#start_date').val()))
        {
            $('#end_date').val('');
            $('#end_date').datepicker('setDate', null);
        }
        $('#start_date').valid();

    });

    $('#end_date').datepicker({
        startDate: start,
        autoclose: true,
        todayHighlight: true,
        format: 'yyyy-mm-dd',
    }).on('changeDate', function () {
        $('#end_date').valid();
    });



});

$("#project_name").focusout(function () {
    $(this).val($.trim($(this).val()).replace(/^0+/, ''));
});