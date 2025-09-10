$(document).ready(function() {
    $('#theme').change(function (e) {
        var fileName = e.target.files[0].name,
            allowedMimeTypes = ['application/json'];
        if (!allowedMimeTypes.includes(e.target.files[0].type)) {
            toastr.error(message.json_valid_error);
            $(e.currentTarget).empty().val('');
            $(this).parent('div').find('.custom-file-label').val('');
        } else if (e.target.files[0].size > 2097152) {
            toastr.error(message.json_size_2M_error);
            $(e.currentTarget).empty().val('');
            $(this).parent('div').find('.custom-file-label').val('');
        } else {
            $(this).parent('div').find('.custom-file-label').html(fileName);
        }
    });
});