// to populate county and timezone field after redirecting with error.
setTimeout(function(){
    var country = data.oldCountry;
    if (country != '' && country != undefined) {
        $('#country_id').select2('val', country);
    }

    var timezone = data.oldTimezone;
    if (timezone != '' && timezone != undefined) {
        $('#timezone').select2('val', timezone);
    }
}, 250);

$(document).ready(function() {
    var country = $('#country_id').val();
    if (country != '' && country != undefined) {
        $('#country_id').trigger('change');
    }
    $('.select2').change(function() {
        stateUrl = stateUrl;
        tzUrl = tzUrl;
        if ($(this).val() != '' && $(this).val() != null) {
            if ($(this).attr("id") == 'country_id' && $(this).attr('data-dependent') == 'state_id') {
                var select = $(this).attr("id");
                var value = $(this).val();
                var dependent = $(this).attr('data-dependent');
                var _token = $('input[name="_token"]').val();

                url = stateUrl.replace(':id', value);

                $.ajax({
                    url: url,
                    method: 'get',
                    data: {
                        _token: _token
                    },
                    success: function(result) {
                        $('#' + dependent).empty();
                        $('#' + dependent).attr('disabled', false);
                        $('#' + dependent).val('').trigger('change').append('<option value="">'+data.placeholder.select+'</option>');
                        $('#' + dependent).removeClass('is-valid');
                        $.each(result.result, function(key, value) {
                            $('#' + dependent).append('<option value="' + value.id + '">' + value.name + '</option>');
                        });
                        if (Object.keys(result.result).length == 1) {
                            $.each(result.result, function(key, value) {
                                $('#' + dependent).select2('val', value.id);
                            });
                        }

                        var county = data.oldCountry;
                        if (county != '' && county != undefined) {
                            $('#state_id').select2('val', county);
                        }
                    }
                })
            }
        }

        if ($(this).val() != '' && $(this).val() != null) {
            if ($(this).attr("id") == 'country_id' && $(this).attr('target-data') == 'timezone') {
                var select = $(this).attr("id");
                var value = $(this).val();
                var tzDependent = $(this).attr('target-data');
                var _token = $('input[name="_token"]').val();

                url = tzUrl.replace(':id', value);

                $.ajax({
                    url: url,
                    method: 'get',
                    data: {
                        _token: _token
                    },
                    success: function(result) {
                        $('#' + tzDependent).empty();
                        $('#' + tzDependent).attr('disabled', false);
                        $('#' + tzDependent).val('').trigger('change').append('<option value="">'+data.placeholder.select+'</option>');
                        $('#' + tzDependent).removeClass('is-valid');
                        $.each(result.result, function(key, value) {
                            $('#' + tzDependent).append('<option value="' + value.id + '">' + value.name + '</option>');
                        });
                        if (Object.keys(result.result).length == 1) {
                            $.each(result.result, function(key, value) {
                                $('#' + tzDependent).select2('val', value.id);
                            });
                        }
                    }
                })
            }
        }
    });
});