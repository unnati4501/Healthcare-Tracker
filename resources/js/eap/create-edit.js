function readURL(input, selector) {
    if (input && input.files && input.files[0]) {
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

$(document).ready(function() {

    $(document).on('click', '#zevo_submit_btn', function(){
        if(data.isSA) {
            var isError = 0;

            var domEditableElement = document.querySelector( '.ck-editor__editable' );
                editorInstance = domEditableElement.ckeditorInstance;
                description = editorInstance.getData();
                description = $(description).text().trim();

            var eapCompany = $('#eap_company').val().length;
            if (eapCompany == 0) {
                event.preventDefault();
                if($('#EAPEdit').length > 0) {
                    $('#EAPEdit').valid();
                } else {
                    $('#EAPAdd').valid();
                }
                $('#eap_company-error').addClass('is-invalid').show();
                $('.tree-multiselect').css('border-color', '#f44436');
                isError = 0;
            } else {
                isError = 1;
            }

            if(description == ''){
                event.preventDefault();
                if($('#EAPEdit').length > 0) {
                    $('#EAPEdit').valid();
                } else {
                    $('#EAPAdd').valid();
                }
                $('#description-error').html(message.desc_required).addClass('is-invalid').show();
                isError = 0;
            } else {
                if(description.length > 750) {
                    event.preventDefault();
                    isError = 0;
                    $('#description-error').html(message.desc_length).addClass('is-invalid').show();
                } else {
                    isError = 1;
                    $('#description-error').removeClass('is-invalid').hide();
                }
            }

            if(isError == 0) {
                $('#eap_company').removeClass('is-invalid');
                $('.tree-multiselect').css('border-color', '#D8D8D8');
            }
        }
    });

    if ($("#setPermissionList").length > 0) {
        $("#setPermissionList").mCustomScrollbar({
            axis: "y",
            theme: "inset-dark",
            scrollButtons: {
                enable: true,
            }
        });
    }

    $('.numeric').numeric({ decimal: false, negative: false });

    $('#logo').change(function (e) {
        var fileName = e.target.files[0].name,
            allowedMimeTypes = ['image/png', 'image/jpeg', 'image/jpeg'];

        if (fileName.length > 40) { fileName = fileName.substr(0, 40); }

        if (!allowedMimeTypes.includes(e.target.files[0].type)) {
            toastr.error(message.image_valid_error);
            $(this).parent('div').find('img').attr('src',data.assets_img);
            $(e.currentTarget).empty().val('');
            $(this).parent('div').find('.custom-file-label').html('Choose File');
        } else if (e.target.files[0].size > 2097152) {
            toastr.error(message.image_size_2M_error);
            $(this).parent('div').find('img').attr('src',data.assets_img);
            $(e.currentTarget).empty().val('');
            $(this).parent('div').find('.custom-file-label').html('Choose File');
        } else {
            readURL(this, '#previewImg');
            $(this).parent('div').find('.custom-file-label').html(fileName);
        }
    });

    if(data.isSA) {
        $("#eap_company").treeMultiselect({
            enableSelectAll: true,
            searchable: true,
            startCollapsed: true,
            onChange: function (allSelectedItems, addedItems, removedItems) {
                var eapCompany = $('#eap_company').val().length;
                if (eapCompany == 0) {
                     if($('#EAPEdit').length > 0) {
                        $('#EAPEdit').valid();
                    } else {
                        $('#EAPAdd').valid();
                    }
                    $('#eap_company-error').show();
                    $('#eap_company').addClass('is-invalid');
                    $('.tree-multiselect').css('border-color', '#f44436');
                } else {
                    $('#eap_company-error').hide();
                    $('#eap_company').removeClass('is-invalid');
                    $('.tree-multiselect').css('border-color', '#D8D8D8');
                }
            }
        });
    }
    $('.select2').select2({
        placeholder: "Select",
        allowClear: true,
        width: '100%'
    });

    $('select.select2').select2();

    $(document).on('change', '#locations', function() {
        var _value = $(this).val();
        var _token = $('input[name="_token"]').val();
        if(_value.length <= 0) {
            $('#department').val('');
            $('#department').trigger('change');
            $('#department').empty().attr('disabled', true);
            return true;
        }
        $.ajax({
            url: getDepartment,
            method: 'post',
            data: {
                _token: _token,
                value: _value
            },
            success: function(result) {
                $('#department').empty().attr('disabled', false);
                $.each(result, function(key, value) {
                    $('#department').append('<option value="' + key + '" selected>' + value + '</option>');
                });
                $('#department').select2();
            }
        });
    });

});