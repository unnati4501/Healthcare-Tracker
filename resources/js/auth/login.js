$(document).ready(function(){
    $('.verify-btn').attr("disabled",true);
    zE("webWidget", "hide")
    zE('webWidget', 'setLocale', 'en');
    window.zESettings = {
        webWidget: {
            color: { theme: '#424e89' },
        }
    };
    window.zE('webWidget:on', 'close', function () {
        window.zE('webWidget', 'hide');
        $('.openwidget').show();
    });
    window.zE('webWidget:on', 'open', function () {
        window.zE('webWidget', 'show');
        setTimeout(function() {
            $('.openwidget').hide();
        }, 500);
    });
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        timeout: 60000
    });
    var timezone = Intl.DateTimeFormat().resolvedOptions().timeZone;
    $("#timezone").val(timezone);
    $("#email").focusout(function () {
        $(this).val($.trim($(this).val()));
    });
    $('.2fa-button').click(function(){
        var id = $(this).attr('id');
        if(id == '2fa') {
            $('.password-field, .remember-field').addClass('d-none');
            // $('.login-btn-popup').removeClass('d-none');
            $(this).attr('id', 'password').text('Login with password');
            $('#type').val('2fa');
        } else {
            $('.password-field, .remember-field').removeClass('d-none');
            // $('.login-btn-popup').addClass('d-none');
            $(this).attr('id', '2fa').text('Login with 2FA');
            $('#type').val('password');
        }
    });
    $('.login-btn').click(function() {
        var type = $('#type').val();
        if(type == '2fa') {
            event.preventDefault();
            if($('#loginForm').valid()) {
                $(".page-loader-wrapper").fadeIn();
                $.ajax({
                    url: url.send_email,
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        email: $('#email').val()
                    }
                }).done(function(data) {
                    $(".page-loader-wrapper").fadeOut();
                    if(data.status) {
                        // toastr.success(data.message);
                        $('#email_text').html($('#email').val());
                        $('#twoFactorModal').modal('show');
                    } else {
                        toastr.error(data.message);
                    }
                });
            }
        }
    });
    $('#resend-otp').click(function() {
        var type = $('#type').val();
        if(type == '2fa') {
            $('#resent-msg').addClass('d-none');
            $('#resent-again').removeClass('d-none');
            var timer2 = "1:00";
            var interval = setInterval(function() {
              var timer = timer2.split(':');
              var minutes = parseInt(timer[0], 10);
              var seconds = parseInt(timer[1], 10);
              --seconds;
              minutes = (seconds < 0) ? --minutes : minutes;
              if (minutes < 0) clearInterval(interval);
              seconds = (seconds < 0) ? 59 : seconds;
              seconds = (seconds < 10) ? '0' + seconds : seconds;
              $('.countdown').html(minutes + ':' + seconds);
              timer2 = minutes + ':' + seconds;
              if(timer2 == "0:00"){
                $('#resent-msg').removeClass('d-none');
                $('#resent-again').addClass('d-none');
              }
            }, 500);
            counter = 0;
            event.preventDefault();
            if($('#loginForm').valid()) {
                $(".page-loader-wrapper").fadeIn();
                $.ajax({
                    url: url.send_email,
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        email: $('#email').val()
                    }
                }).done(function(data) {
                    $(".page-loader-wrapper").fadeOut();
                    if(data.status) {
                        // toastr.success(data.message);
                        $('#email_text').html($('#email').val());
                        $('#twoFactorModal').modal('show');
                    } else {
                        toastr.error(data.message);
                    }
                });
            }
        }
    })
    var counter = 0;
    $('.verify-btn').click(function() {
        counter = counter + 1;
        if (counter > 5) {
            counter = 0;
            $(".page-loader-wrapper").fadeOut();
            $('#twoFactorModal').modal('hide');
            $("input[name^='digit']").val("");
            $('.verify-btn').attr("disabled",true);
            toastr.error("Please request a new code");
          } else {
            var values = $("input[name='digit[]']").map(function(){
                return $(this).val();
            }).get();
            if (values.length == 6){
                $('.verify-btn').attr("disabled",false);
            } 
            var type = $('#type').val();
            if(type == '2fa') {
                $(".page-loader-wrapper").fadeIn();
                event.preventDefault();
                $.ajax({
                    url: url.verify_otp,
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        timezone: $('#timezone').val(),
                        type: type,
                        email: $('#email').val(),
                        digit: values
                    }
                }).done(function(data) {
                    if(data.status) {
                        // toastr.success(data.message);
                        window.location.href = url.dashboad;
                        // $(".page-loader-wrapper").fadeOut();
                    } else {
                        $(".page-loader-wrapper").fadeOut();
                        toastr.error(data.message);
                        $("input[name^='digit']").val("");
                        $('.verify-btn').attr("disabled",true);
                    }
                });
            }
        }
    })
    $('.digit-group').find('input').each(function() {
        $(this).attr('maxlength', 1);
        $(this).on('keyup', function(e) {
            var parent = $($(this).parent());
            if (e.keyCode === 8 || e.keyCode === 37) {
                var prev = parent.find('input#' + $(this).data('previous'));

                if(prev.length) {
                    $(prev).select();
                }
            } else if((e.keyCode >= 48 && e.keyCode <= 57) || (e.keyCode >= 65 && e.keyCode <= 90) || (e.keyCode >= 96 && e.keyCode <= 105) || e.keyCode === 39) {
                var next = parent.find('input#' + $(this).data('next'));

                if (next.length) {
                    $(next).select();
                } 
                // else {
                //     $('.verify-btn').attr("disabled",false);
                //     if(parent.data('autosubmit')) {
                //         parent.submit();
                //     }
                // }
            }

            validateCount = 0;
            $('.digit-group').find('input').each(function() {
                if ($(this).val() == '') {
                    validateCount++;
                } else {

                }
            });

            if(validateCount > 0) {
                $('.verify-btn').attr("disabled",true);
            } else {
                $('.verify-btn').attr("disabled",false);
            }
        });
    });

    $(document).on('hidden.bs.modal', '#twoFactorModal', function(e) {
        $("input[name^='digit']").val("");
        $('.verify-btn').attr("disabled",true);
        counter = 0;
    });
    
    $(document).on('click', '.openwidget', function() {
        $('.verify-btn').attr("disabled",true);
        window.zE('webWidget', 'show');
        window.zE('webWidget', 'open');
    });
});
