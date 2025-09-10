var bar = $('#mainProgrssbar'),
    percent = $('#mainProgrssbar .progpercent');
$(document).ready(function() {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    $('#CILManagment').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: url.datatable,
            data: {
                target_type: $('#target_type').val(),
                getQueryString: window.location.search
            },
        },
        columns: [{
            data: 'image',
            searchable: false,
            sortable: false,
        }, {
            data: 'target',
            name: 'target'
        }, {
            data: 'actions',
            name: 'actions',
            searchable: false,
            sortable: false,
            className: 'text-center'
        }],
        paging: true,
        pageLength: pagination.value,
        lengthChange: false,
        searching: false,
        ordering: true,
        order: [],
        info: true,
        autoWidth: false,
        columnDefs: [{
            targets: 'no-sort',
            orderable: false,
        }],
        language: {
            paginate: {
                previous: pagination.previous,
                next: pagination.next,
            }
        },
        dom: '<<"pagination-top"lB><t><"pagination-wrap"<"pagination-left"i>p>',
    });
    $(document).on('click', '.delete-image', function(t) {
        $('#delete-model-box').data("id", $(this).data('id'));
        $('#delete-model-box').modal('show');
    });
    $(document).on('click', '#delete-model-box-confirm', function(e) {
        var _this = $(this),
            objectId = $('#delete-model-box').data("id");
        _this.prop('disabled', true);
        $('.page-loader-wrapper').show();
        $.ajax({
            type: 'DELETE',
            url: url.delete + `/${objectId}`,
            crossDomain: true,
            cache: false,
            contentType: 'json'
        }).done(function(data) {
            $('#CILManagment').DataTable().ajax.reload(null, false);
            if (data.deleted == true) {
                toastr.success(message.deleted);
            } else {
                toastr.error(message.somethingWentWrong);
            }
        }).fail(function(data) {
            if (data == 'Forbidden') {
                toastr.error(message.somethingWentWrong);
            }
        }).always(function() {
            _this.prop('disabled', false);
            $('#delete-model-box').modal('hide');
            $('.page-loader-wrapper').hide();
        });
    });
    $(document).on('change', '#images', function(e) {
        var _this = e.currentTarget,
            _files = _this.files,
            _length = _files.length,
            _allowedMimeTypes = ['image/png', 'image/jpeg', 'image/jpg'],
            _canProceed = true;
        if (_files && _length > 0) {
            if (_length <= maxImagesLimit) {
                $.each(_files, function(key, file) {
                    if (!_allowedMimeTypes.includes(file.type)) {
                        toastr.error(message.image_valid_error);
                        _canProceed = false;
                        return false;
                    } else if (file.size > 2097152) {
                        toastr.error(message.image_size_2M_error);
                        _canProceed = false;
                        return false;
                    } else {
                        // Validation for image max height / width and Aspected Ratio
                        var reader = new FileReader();
                        reader.onload = function (e) {
                            var image = new Image();
                            image.src = e.target.result;
                            image.onload = function () {
                                var imageWidth = $(_this).data('width');
                                var imageHeight = $(_this).data('height');
                                var ratio = $(_this).data('ratio');
                                var aspectedRatio = ratio;
                                var ratioSplit = ratio.split(':');
                                var newWidth = ratioSplit[0];
                                var newHeight = ratioSplit[1];
                                var ratioGcd = gcd(this.width, this.height, newHeight, newWidth);
                                if((this.width < imageWidth && this.height < imageHeight) || ratioGcd != aspectedRatio){
                                    toastr.error(message.upload_image_dimension);
                                    $('#images').val('');
                                    $(_this).parent('div').find('.custom-file-label').html('Choose File');
                                    _canProceed = false;
                                    return false;
                                } else {
                                    if (_canProceed == false) {
                                        $('#images').val('');
                                        $(_this).parent('div').find('.custom-file-label').html('Choose File');
                                    } else {
                                        $(_this).parent('div').find('.custom-file-label').html(`${_length} image${((_length > 1) ? 's' : '')} selected`);
                                    }
                                }
                            }
                        }
                        reader.readAsDataURL(file);
                    }
                });
            } else {
                toastr.error(`Maximum ${maxImagesLimit} images can be uploaded at a time.`);
                $('#images').val('');
                $(_this).parent('div').find('.custom-file-label').html('Choose File');
            }
        }
    });
    $('#bulkUploadImagesFrm').ajaxForm({
        beforeSend: function() {
            $('.progress-loader-wrapper .status-text').html('Uploading images....');
            $('.progress-loader-wrapper').show();
            var percentVal = '0%';
            bar.width(percentVal)
            percent.html(percentVal);
        },
        uploadProgress: function(event, position, total, percentComplete) {
            var percentVal = percentComplete + '%';
            bar.width(percentVal)
            percent.html(percentVal);
            if (percentComplete == 100) {
                $('.progress-loader-wrapper .status-text').html('Processing on images...');
            }
        },
        success: function(data) {
            $('.progress-loader-wrapper').hide();
            var percentVal = '100%';
            bar.width(percentVal)
            percent.html(percentVal);
            if (data.status && data.status == 1) {
                window.location.reload();
            } else {
                if (data.message && data.message != '') {
                    toastr.error(data.message);
                }
            }
        },
        error: function(data) {
            $('.progress-loader-wrapper').hide();
            if (data.responseJSON && data.responseJSON.message && data.responseJSON.message != '') {
                toastr.error(data.responseJSON.message || message.somethingWentWrong);
            } else {
                toastr.error(message.somethingWentWrong);
            }
            var percentVal = '0%';
            bar.width(percentVal)
            percent.html(percentVal);
        },
        complete: function(xhr) {
            $('.progress-loader-wrapper').hide();
        }
    });
});