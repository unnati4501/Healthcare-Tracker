$(document).ready(function() {
    $("#locations").hide();
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        timeout: (60000 * 20)
    });
    var subCategories = null;
    // initialize bookings category carousel
    _wsCarousel = $('#ws-owl-carousel').owlCarousel({
        navText: ["<i class='far fa-long-arrow-left'></i>", "<i class='far fa-long-arrow-right'></i>"],
        loop: false,
        margin: 10,
        width: 100,
        nav: true,
        dots: false,
        pullDrag: false,
        mouseDrag: false,
        responsive: {
            0: {
                items: 2
            },
            500: {
                items: 3
            },
            1000: {
                items: 3
            }
        }
    });
    $("#add_users").treeMultiselect({
        enableSelectAll: true,
        searchable: true,
        startCollapsed: true,
        onChange: function(allSelectedItems, addedItems, removedItems) {
            var userValidate = $('#add_users').val().length;
            var service = $('#service').val();
            var sericeIsCounselling = $('#serviceIsCounselling').val();
            var serviceType = $('#sessionType').val();
            if (userValidate == 0) {
                $('#addgroupsession').valid();
                $('#add_users-error').show();
                $('.tree-multiselect').css('border-color', '#f44436');
            } else if (serviceType == 1 && userValidate > 1) {
                $('#addgroupsession').valid();
                $('#add_users-max-error').show();
                $('.tree-multiselect').css('border-color', '#f44436');
            } else {
                $('#add_users-error').hide();
                $('.tree-multiselect').css('border-color', '#D8D8D8');
            }
        }
    });
    $('#service').change(function() {
        $("#serviceIsCounselling").val("");
        var selectedId = $(this).val();
        var _token = $('input[name="_token"]').val();
        var url = ajaxUrl.getSubCategories.replace(':id', selectedId);
        $.ajax({
            url: url,
            method: 'get',
            data: {
                _token: _token
            },
            success: function(res) {
                $('#sub_category').empty().select2("val", "");
                $("#serviceIsCounselling").val(res.response.serviceIsCounsessling);
                var num = 0;
                if (res.response.result) {
                    $.each(res.response.subcategory, function(key, value) {
                        if (num <= 0) {
                            subCategories = key;
                            $('#sub_category').append('<option value="' + key + '" selected="selected">' + value + '</option>');
                        } else {
                            $('#sub_category').append('<option value="' + key + '">' + value + '</option>');
                        }
                        num++;
                    });
                    $('#sub_category').select2();
                }
            }
        });
    });
    $('#sub_category').change(function() {
        if (!data.is_ws) {
            $('[data-capacity-block]').data('totalusers', 0);
            $('#selectedslot-error, [data-slots-block], [data-hint-block], [data-no-slots-block], [data-register-all-user-error]').hide();
            $('[data-loader-block]').show();
            var selectedId = $(this).val();
            setTimeout(function() {
                var _token = $('input[name="_token"]').val();
                if (selectedId == undefined) {
                    selectedId = subCategories;
                }
                var url = ajaxUrl.getWSUser.replace(':id', selectedId);
                $.ajax({
                    url: url,
                    method: 'get',
                    data: {
                        _token: _token
                    },
                    success: function(res) {
                        $('[data-loader-block]').hide();
                        if (res.length > 0) {
                            $('[data-capacity-block]').data('totalusers', res.length);
                            // if slots are available then show accordingly
                            $('[data-hint-block], [data-no-slots-block]').hide();
                            $('[data-slots-block]').show();
                            _wsCarousel.trigger('replace.owl.carousel', res).trigger('refresh.owl.carousel');
                        } else {
                            $('[data-capacity-block]').data('totalusers', 0);
                            $('[data-slots-block], [data-hint-block]').hide();
                            $('[data-no-slots-block]').show();
                        }
                    }
                });
            }, 1000);
        }
    });

    $('#company').change(function() {
        $('.error_location_message').addClass('d-none');
        $('#zevo_submit_btn').attr('disabled', false);
        var selectedId = $(this).val();
        var _token = $('input[name="_token"]').val();
        var url = ajaxUrl.getUser.replace(':id', selectedId);
        // Get locations from company avability
        var companyLocation = ajaxUrl.getCompanyLocations.replace(':id', selectedId);
        var isAvabilitySetByLocation = false;
        $.ajax({
            url: companyLocation,
            method: 'get',
            data: {
                _token: _token
            },
            success: function(res) {
                $('#location').empty().select2("val", "");
                var num = 0;
                var locationSelected = null;
                if (res.response.result) {
                    isAvabilitySetByLocation = true;
                    $('#locations').show();
                    $.each(res.response.locations, function(key, value) {
                        if (num <= 0) {
                            locationSelected = key;
                            $('#location').append('<option value="' + key + '" selected="selected">' + value + '</option>');
                        } else {
                            $('#location').append('<option value="' + key + '">' + value + '</option>');
                        }
                        num++;
                    });
                   
                    $('#location').select2();
                    getUsersData(locationSelected);
                } else {
                    if (res.response.isLocation) {
                        $('#zevo_submit_btn').attr('disabled', true);
                        $('.error_location_message').removeClass('d-none');
                    }
                    $('#locations').hide();
                }

                if(isAvabilitySetByLocation == false){
                    getUsersData();
                }
            }
            
        });
    });

    $(document).on('change', '#location', function() {
        var selectedId = $(this).val();
        if(selectedId != null){
            getUsersData(selectedId);
        }
    });

    $(document).on('click', '#zevo_submit_btn', function() {

        var domEditableElement = document.querySelector( '.ck-editor__editable' );
            editorInstance = domEditableElement.ckeditorInstance;
            notes = editorInstance.getData();
            notes = $(notes).text().trim();

        var notesRetu = false;
        var userRetu = false
        var userValidate = $('#add_users').val().length;
        var service = $('#service').val();
        var sericeIsCounselling = $('#serviceIsCounselling').val();
        var serviceType = $('#sessionType').val();
        if (notes.length > 6000) {
            event.preventDefault();
            notesRetu = true;
            $('#addgroupsession').valid();
            $('#notes-error-cstm').html(message.note_length).addClass('is-invalid').show();
        } else {
            notesRetu = false;
            $('#notes-error-cstm').removeClass('is-invalid').hide();
        }
        if (userValidate == 0) {
            event.preventDefault();
            userRetu = true;
            $('#addgroupsession').valid();
            $('#add_users-error').show();
            $('.tree-multiselect').css('border-color', '#f44436');
        } else if (serviceType == 1 && userValidate > 1) {
            userRetu = true;
            event.preventDefault();
            $('#addgroupsession').valid();
            $('#add_users-max-error').show();
            $('.tree-multiselect').css('border-color', '#f44436');
        } else {
            userRetu = false;
            $('#add_users-error').hide();
            $('#add_users-max-error').hide();
            $('.tree-multiselect').css('border-color', '#D8D8D8');
        }
        if (userRetu == false && notesRetu == false) {
            $('#addgroupsession').submit();
        }
    });
});

function getUsersData(locationId = null){
    var url = ajaxUrl.getUser.replace(':id', $("#company").val());
    var _token = $('input[name="_token"]').val();
    $.ajax({
        url: url,
        method: 'get',
        data: {
            _token: _token,
            locationId : locationId
        },
        success: function(res) {
            $('.user_sections').hide();
            if (res.response.result) {
                $('.user_sections').show();
                $('#add_users').parent().find('.tree-multiselect').remove();
                $('#add_users option').remove();
                $.each(res.response.companies.location, function(key, value) {
                    let locationName = value.locationName;
                    let department = value.department;
                    $.each(department, function(dkey, dvalue) {
                        let departmentName = dvalue.departmentName;
                        let teams = dvalue.team;
                        $.each(teams, function(tkey, tvalue) {
                            let teamName = tvalue.name;
                            let users = tvalue.user;
                            $.each(users, function(ukey, uvalue) {
                                $('#add_users').append('<option value="' + uvalue.id + '" data-section="' + locationName + '/' + departmentName + '/' + teamName + '">' + uvalue.name + ' - ' + uvalue.email + '</option>');
                            });
                        });
                    });
                });
                $('.tree-multiselect').remove();
                $("#add_users").treeMultiselect({
                    enableSelectAll: true,
                    searchable: true,
                    startCollapsed: true,
                    onChange: function(allSelectedItems, addedItems, removedItems) {
                        var userValidate = $('#add_users').val().length;
                        var service = $('#service').val();
                        var serviceType = $('#sessionType').val();
                        var sericeIsCounselling = $('#serviceIsCounselling').val();
                        $('#add_users-error').hide();
                        $('#add_users-max-error').hide();
                        if (userValidate == 0) {
                            $('#addgroupsession').valid();
                            $('#add_users-error').show();
                            $('.tree-multiselect').css('border-color', '#f44436');
                        } else if (serviceType == 1 && userValidate > 1) {
                            $('#addgroupsession').valid();
                            $('#add_users-max-error').show();
                            $('.tree-multiselect').css('border-color', '#f44436');
                        } else {
                            $('#add_users-error').hide();
                            $('#add_users-max-error').hide();
                            $('.tree-multiselect').css('border-color', '#D8D8D8');
                        }
                    }
                });
            }
        }
    });
}