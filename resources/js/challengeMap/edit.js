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
        onChange: function(allSelectedItems, addedItems, removedItems) {
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
    $(document).on('change', '#image', function(e) {
        var previewElement = $(this).data('previewelement');
        if (e.target.files.length > 0) {
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
    $(document).on('change', '#propertyimage', function(e) {
        var previewElement = $(this).data('previewelement');
        if (e.target.files.length > 0) {
            var reader = new FileReader();
            reader.onloadend = function() {
                $("#base64Img").val(reader.result);
            }
            reader.readAsDataURL(e.target.files[0]);
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
    $(document).on('click', '#zevo_update_btn', function() {
        var mapCompany = $('#map_companies').val().length;
        if (mapCompany == 0) {
            event.preventDefault();
            $('#EditChallengeMap').valid();
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
        var id = $(this).attr('id');
        var recordsId = $(this).attr('recordsId');
        $('#delete-location-model-box').attr('data-id', id);
        $('#delete-location-model-box').attr('data-recordsid', recordsId);
        $('#delete-location-model-box').modal('show');
    });
    $(document).on('click', '#delete-location-model-box-confirm', function(e) {
        var _this = $(this),
            objectId = $('#delete-location-model-box').attr('data-id')
        recordsId = $('#delete-location-model-box').attr('data-recordsid');
        var objectId = objectId.split('_')[1];
        $('.page-loader-wrapper').show();
        if (recordsId <= 0) {
            var latlong = $('#location_' + objectId).val();
            var index = objectId - 1;
            locationArray.splice(index, 1);
            $('#tr_' + objectId).remove();
            if (locationArray.length <= 0) {
                $('#totalLocations').val('');
                $('#mapDiv').attr('class', 'col-lg-12');
            }
            $('#totalLocations').val(locationArray.length);
            initMap();
            toastr.success(message.deleted);
            $('#delete-location-model-box').modal('hide');
            $('.page-loader-wrapper').hide();
        } else {
            $.ajax({
                type: 'DELETE',
                url: url.deleteLocation + `/${recordsId}`,
                crossDomain: true,
                cache: false,
                contentType: 'json'
            }).done(function(data) {
                if (data.status.deleted == true) {
                    toastr.success(message.deleted);
                    var latlong = $('#location_' + objectId).val();
                    var index = objectId - 1;
                    locationArray.splice(index, 1);
                    $('#tr_' + objectId).remove();
                    if (locationArray.length <= 0) {
                        $('#totalLocations').val('');
                        $('#mapDiv').attr('class', 'col-lg-12');
                    }
                    $('#total_distance').val(data.totalDistance);
                    $('#total_steps').val(data.totalSteps);
                    $('#totalLocations').val(data.totalLocation);
                    initMap();
                } else {
                    toastr.error(message.somethingWentWrong);
                }
                $('.page-loader-wrapper').hide();
                $('#delete-location-model-box').modal('hide');
            }).fail(function(data) {
                if (data == 'Forbidden') {
                    toastr.error(message.somethingWentWrong);
                }
                $('.page-loader-wrapper').hide();
                $('#delete-location-model-box').modal('hide');
            }).always(function() {
                $('#delete-location-model-box').modal('hide');
                $('.page-loader-wrapper').hide();
            });
        }
    });
    $(document).on('change', '#location_type', function() {
        var value = $(this).val();
        if (value == 2) {
            $('.location_name_div,.location_upload_files_div').hide();
        } else {
            $('.propertyimage-label').html('Choose File');
            $('#propertyimage_preview').attr('src', url.commonImage);
            $('.location_name_div,.location_upload_files_div').show();
        }
    });
    $(document).on('click', '.map-edit', function() {
        var id = $(this).attr('recordsid');
        var num = $(this).attr('id').split('_')[1];
        $('.page-loader-wrapper').show();
        var previousId = null;
        $('#location_type-error').remove();
        $('#location_name-error').remove();
        $('#distance-error').remove();
        $('#steps-error').remove();
        $('#propertyimage-error').remove();
        $('.propertyimage-label').html('Choose File');
        $('#propertyimage_preview').attr('src', url.commonImage);
        if (num != 1) {
            var previous_num = num - 1;
            previousId = $('#mapedit_' + previous_num).attr('recordsid');
        }
        $.ajax({
            type: 'GET',
            url: url.getLocation + `/${id}` + '?previousId=' + previousId,
            crossDomain: true,
            cache: false,
            contentType: 'json'
        }).done(function(data) {
            $('.page-loader-wrapper').hide();
            let latLong = "";
            let locationType = "";
            let locationName = "";
            let distance = "";
            let steps = "";
            $('#property-label-id').html(num);
            if (num == 1) {
                $('#distanceDiv,#stepsDiv').hide();
                $('#distance').val('');
            } else {
                $('#distanceDiv,#stepsDiv').show();
            }
            $('#num').val(num);
            if (Object.keys(data).length > 0) {
                if (data.property.lat.length > 0 && data.property.lng.length > 0) {
                    latLong = data.property.lat + ',' + data.property.lng;
                }
                if (data.property.hasOwnProperty('locationType')) {
                    locationType = data.property.locationType;
                }
                if (data.property.hasOwnProperty('locationName')) {
                    locationName = data.property.locationName;
                }
                if (data.property.hasOwnProperty('distance')) {
                    distance = data.property.distance;
                } else {
                    distance = data.distance;
                }
                if (data.property.hasOwnProperty('steps')) {
                    steps = data.property.steps;
                } else {
                    steps = data.steps;
                }
                $('#property_id').val(data.id);
                $('#lat_long').val(latLong);
                if(locationType != '') {
                    $('#location_type').val(locationType).select2();
                    if(num == 1) {
                        $('#location_type').attr('disabled', true);
                    } else {
                        $('#location_type').attr('disabled', false);
                    }
                } else if(num == 1) {
                    $('#location_type').val(1).select2().attr('disabled', true);
                } else {
                    $('#location_type').val('').select2('').attr('disabled', false);
                }
                $('#location_name').val(locationName);
                if (num > 1) {
                    $('#distance').val(distance);
                    $('#steps').val(steps);
                }
                if (data.imageName.length > 0) {
                    $('.propertyimage-label').html(data.imageName);
                    $('#propertyimage_preview').attr('src', data.imageUrl);
                } else {
                    $('.propertyimage-label').html('Choose File');
                    $('#propertyimage_preview').attr('src', url.commonImage);
                }
                if (locationType == 2) {
                    $('.location_name_div,.location_upload_files_div').hide();
                } else {
                    $('.location_name_div,.location_upload_files_div').show();
                }
            } else {
                $('#property_id').val("");
                $('#lat_long').val($('#location_' + num).val());
                $('#location_type').val(locationType).select2();
                $('#location_name').val(locationName);
                $('#distance').val(distance);
                $('#steps').val(steps);
                $('.propertyimage-label').html('Choose File');
                $('#propertyimage_preview').attr('src', url.commonImage);
                if (locationType == 2) {
                    $('.location_name_div,.location_upload_files_div').hide();
                } else {
                    $('.location_name_div,.location_upload_files_div').show();
                }
            }
        }).fail(function(data) {
            if (data == 'Forbidden') {
                toastr.error(message.somethingWentWrong);
            }
            $('.page-loader-wrapper').hide();
        }).always(function() {
            $('.page-loader-wrapper').hide();
        });
    });
    $(document).on('click', '#save-property', function() {
        var property_id = $('#property_id').val();
        var lat_long = $('#lat_long').val();
        var location_type = $('#location_type').val();
        var location_name = $('#location_name').val();
        var distance = $('#distance').val();
        var steps = $('#steps').val();
        var num = $('#num').val();
        var fileInput = document.getElementById('propertyimage');
        var file = fileInput.files[0];
        var regex = /^[0-9-+()]*$/;
        let ret = false;
        let validationResponse = [];
        var propertyimage_preview = $('.propertyimage-label').html();
        $('#location_type-error').remove();
        $('#location_name-error').remove();
        $('#distance-error').remove();
        $('#steps-error').remove();
        $('#propertyimage-error').remove();
        let locationType = (location_type.length > 0) ? location_type : 1;
        if (locationType == 1) {
            if (file == undefined && propertyimage_preview == 'Choose File') {
                ret = false;
                validationResponse.push('image');
                $('#propertyimage').addClass('is-invalid').attr('aria-describedby', 'propertyimage-error').next().after('<div id="propertyimage-error" class="invalid-feedback custom-validate">' + validation.property_upload_required + '</div>')
            } else {
                validationResponse = $.grep(validationResponse, function(n) {
                    return n != 'image';
                });
                ret = true;
                $('#propertyimage-error').remove();
                $('#propertyimage').removeClass('is-invalid').attr('aria-describedby', '');
            }
            if (location_name.length <= 0) {
                ret = false;
                validationResponse.push('name');
                $('#location_name').addClass('is-invalid').attr('aria-describedby', 'location_name-error').after('<div id="location_name-error" class="invalid-feedback custom-validate">' + validation.location_required + '</div>')
            } else if (location_name.length > 50) {
                ret = false;
                validationResponse.push('name');
                $('#location_name').addClass('is-invalid').attr('aria-describedby', 'location_name-error').after('<div id="location_name-error" class="invalid-feedback custom-validate">' + validation.location_greater_char + '</div>')
            } else {
                validationResponse = $.grep(validationResponse, function(n) {
                    return n != 'name';
                });
                ret = true;
                $('#location_name-error').remove();
                $('#location_name').removeClass('is-invalid').attr('aria-describedby', '');
            }
        }
        if (location_type.length <= 0) {
            ret = false;
            validationResponse.push('type');
            $('#location_type').addClass('is-invalid').attr('aria-describedby', 'location_type-error').next().after('<div id="location_type-error" class="invalid-feedback custom-validate">' + validation.location_type_required + '</div>')
        } else {
            ret = true;
            validationResponse = $.grep(validationResponse, function(n) {
                return n != 'type';
            });
            $('#location_type-error').remove();
            $('#location_type').removeClass('is-invalid').attr('aria-describedby', '');
        }
        if (num > 1) {
            if (distance.length <= 0) {
                ret = false;
                validationResponse.push('distance');
                $('#distance').addClass('is-invalid').attr('aria-describedby', 'distance-error').next().after('<div id="distance-error" class="invalid-feedback custom-validate">' + validation.distance_required + '</div>')
            } else if (regex.test(distance) == false) {
                ret = false;
                validationResponse.push('distance');
                $('#distance').addClass('is-invalid').attr('aria-describedby', 'distance-error').next().after('<div id="distance-error" class="invalid-feedback custom-validate">' + validation.distance_number + '</div>')
            } else if (distance <= 0) {
                ret = false;
                validationResponse.push('distance');
                $('#distance').addClass('is-invalid').attr('aria-describedby', 'distance-error').next().after('<div id="distance-error" class="invalid-feedback custom-validate">' + validation.distance_valid_number + '</div>')
            } else {
                ret = true;
                validationResponse = $.grep(validationResponse, function(n) {
                    return n != 'distance';
                });
                $('#distance-error').remove();
                $('#distance').removeClass('is-invalid').attr('aria-describedby', '');
            }
            if (steps.length <= 0) {
                ret = false;
                validationResponse.push('steps');
                $('#steps').addClass('is-invalid').attr('aria-describedby', 'steps-error').after('<div id="steps-error" class="invalid-feedback custom-validate">' + validation.steps_required + '</div>')
            } else if (regex.test(steps) == false) {
                ret = false;
                validationResponse.push('steps');
                $('#steps').addClass('is-invalid').attr('aria-describedby', 'steps-error').after('<div id="steps-error" class="invalid-feedback custom-validate">' + validation.steps_number + '</div>')
            } else if (steps <= 0) {
                ret = false;
                validationResponse.push('steps');
                $('#steps').addClass('is-invalid').attr('aria-describedby', 'steps-error').after('<div id="steps-error" class="invalid-feedback custom-validate">' + validation.steps_valid_number + '</div>')
            } else {
                ret = true;
                validationResponse = $.grep(validationResponse, function(n) {
                    return n != 'steps';
                });
                $('#steps-error').remove();
                $('#steps').removeClass('is-invalid').attr('aria-describedby', '');
            }
        }
        if (validationResponse.length <= 0) {
            $('.page-loader-wrapper').show();
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                type: 'PATCH',
                url: url.storeProperty,
                crossDomain: true,
                cache: false,
                data: {
                    'lat_long': lat_long,
                    'location_type': location_type,
                    'location_name': location_name,
                    'distance_location': distance,
                    'steps_location': steps,
                    'num': num,
                    'map_id': $('#map_id').val(),
                    'property_id': property_id,
                    'propertyimage': $('#base64Img').val(),
                },
            }).done(function(data) {
                if (data.status == 1) {
                    $('#total_distance').val(data.totalDistance);
                    $('#total_steps').val(data.totalSteps);
                    $('#totalLocations').val(data.totalLocation);
                    $('#mapedit_' + num).attr('recordsid', data.propertiesId);
                    $('#mapdelete_' + num).attr('recordsid', data.propertiesId);
                    toastr.success(data.data);
                } else {
                    toastr.error(data.data);
                }
                $('.page-loader-wrapper').hide();
            });
        }
    });
    $(document).on('keyup', '#distance', function() {
        var distance = $(this).val();
        var steps = data.steps * distance;
        $('#steps').val(steps);
    });
});

function readURL(input, selector) {
    if (input != null && input.files.length > 0) {
        var reader = new FileReader();
        reader.onload = function(e) {
            // Validation for image max height / width and Aspected Ratio
            var image = new Image();
            image.src = e.target.result;
            image.onload = function() {
                var imageWidth = $(input).data('width');
                var imageHeight = $(input).data('height');
                var ratio = $(input).data('ratio');
                var aspectedRatio = ratio;
                var ratioSplit = ratio.split(':');
                var newWidth = ratioSplit[0];
                var newHeight = ratioSplit[1];
                var ratioGcd = gcd(this.width, this.height, newHeight, newWidth);
                if ((this.width < imageWidth && this.height < imageHeight) || ratioGcd != aspectedRatio) {
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