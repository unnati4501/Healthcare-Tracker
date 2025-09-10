$(document).ready(function() {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        timeout: (60000 * 20)
    });
    $("select#map_companies").treeMultiselect({
        enableSelectAll: true,
        searchParams: ['section', 'text'],
        searchable: true,
        startCollapsed: true,
        onChange: function (allSelectedItems, addedItems, removedItems) {
            var webinarCompany = $('#map_companies').val().length;
            if (webinarCompany == 0) {
                $('#AddChallengeMap').valid();
                $('#map_companies').addClass('is-invalid');
                $('#map_companies-error').show();
                $('.tree-multiselect').css('border-color', '#f44436');
            } else {
                $('#map_companies').removeClass('is-invalid');
                $('#map_companies-error').hide();
                $('.tree-multiselect').css('border-color', '#D8D8D8');
            }
        }
    });
    $(document).on('change', '#image', function (e) {
        var previewElement = $(this).data('previewelement');
        if(e.target.files.length > 0) {
            var fileName = e.target.files[0].name,
                allowedMimeTypes = ['image/png', 'image/jpeg', 'image/jpg'];

            if (!allowedMimeTypes.includes(e.target.files[0].type)) {
                toastr.error(message.image_valid_error);
                $(e.currentTarget).empty().val('');
                $(this).parent('div').find('.custom-file-label').html('Choose File');
                readURL(null, previewElement);
            } else if (e.target.files[0].size > 2097152) {
                toastr.error(message.image_size_2M_error);
                $(e.currentTarget).empty().val('');
                $(this).parent('div').find('.custom-file-label').html('Choose File');
                readURL(null, previewElement);
            } else {
                readURL(e.target, previewElement);
                $(this).parent('div').find('.custom-file-label').html(fileName);
            }
        } else {
            $(this).parent('div').find('.custom-file-label').html('Choose File');
            readURL(null, previewElement);
        }
    });
    $(document).on('click','#zevo_submit_btn',function(){
        var mapCompany = $('#map_companies').val().length;
        if (mapCompany == 0) {
            event.preventDefault();
            $('#AddChallengeMap').valid();
            $('#map_companies').addClass('is-invalid');
            $('#map_companies-error').show();
            $('.tree-multiselect').css('border-color', '#f44436');
        } else {
            $('#map_companies').removeClass('is-invalid');
            $('#map_companies-error').hide();
            $('.tree-multiselect').css('border-color', '#D8D8D8');
        }
    });
    $(document).on('click', '.map-remove', function() {
        var id = $(this).attr('id').split('_');
        $('#delete-location-model-box').attr("data-id", id[1]);
        $('#delete-location-model-box').modal('show');
    });
    $(document).on('click', '#delete-location-model-box-confirm', function(e) {
        var _this = $(this),
            objectId = $('#delete-location-model-box').attr("data-id");
        $('.page-loader-wrapper').show();
        var latlong = $('#location_'+objectId).val();
        var index = objectId - 1;
        locationArray.splice(index, 1);
        $('#tr_'+objectId).remove();
        if(locationArray.length <= 0) {
            $('#totalLocations').val('');
            $('#mapDiv').attr('class', 'col-lg-12');
        }
        $('#totalLocations').val(locationArray.length);
        initMap();
        toastr.success(message.deleted);
        $('.page-loader-wrapper').hide();
        $('#delete-location-model-box').modal('hide');
    });
});

function readURL(input, selector) {
    if (input != null && input.files.length > 0) {
        var reader = new FileReader();
        reader.onload = function (e) {
            // Validation for image max height / width and Aspected Ratio
            var image = new Image();
            image.src = e.target.result;
            image.onload = function () {
                var imageWidth = $(input).data('width');
                var imageHeight = $(input).data('height');
                var ratio = $(input).data('ratio');
                var aspectedRatio = ratio;
                var ratioSplit = ratio.split(':');
                var newWidth = ratioSplit[0];
                var newHeight = ratioSplit[1];
                var ratioGcd = gcd(this.width, this.height, newHeight, newWidth);
                if((this.width < imageWidth && this.height < imageHeight) || ratioGcd != aspectedRatio){
                    $(input).empty().val('');
                    $(input).parent('div').find('.custom-file-label').html('Choose File');
                    $(input).parent('div').find('.invalid-feedback').remove();
                    $(selector).removeAttr('src');
                    toastr.error(message.upload_image_dimension);
                    readURL(null, selector);
                }
            }
            $(selector).attr('src', e.target.result);
        }
        reader.readAsDataURL(input.files[0]);
    } else {
        $(selector).removeAttr('src');
    }
}