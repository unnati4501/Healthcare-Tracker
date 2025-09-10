$(document).ready(function() {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        timeout: (60000 * 20)
    });
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
        freeze:true,
        onChange: function(allSelectedItems, addedItems, removedItems) {
            var userValidate = $('#add_users').val().length;
            var service = $('#service').val();
            if (userValidate == 0) {
                $('#updategroupsession').valid();
                $('#add_users-error').show();
                $('.tree-multiselect').css('border-color', '#f44436');
            } else if (service == 1 && userValidate > 1) {
                $('#updategroupsession').valid();
                $('#add_users-max-error').show();
                $('.tree-multiselect').css('border-color', '#f44436');
            } else {
                $('#add_users-error').hide();
                $('.tree-multiselect').css('border-color', '#D8D8D8');
            }
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
        if (notes.length > 6000) {
            event.preventDefault();
            notesRetu = true;
            $('#updategroupsession').valid();
            $('#notes-error-cstm').html(message.note_length).addClass('is-invalid').show();
        } else {
            notesRetu = false;
            $('#notes-error-cstm').removeClass('is-invalid').hide();
        }
        if (userValidate == 0) {
            event.preventDefault();
            userRetu = true;
            $('#updategroupsession').valid();
            $('#add_users-error').show();
            $('.tree-multiselect').css('border-color', '#f44436');
        } else if (service == 1 && userValidate > 1) {
            userRetu = true;
            event.preventDefault();
            $('#updategroupsession').valid();
            $('#add_users-max-error').show();
            $('.tree-multiselect').css('border-color', '#f44436');
        } else {
            userRetu = false;
            $('#add_users-error').hide();
            $('#add_users-max-error').hide();
            $('.tree-multiselect').css('border-color', '#D8D8D8');
        }
        if(userRetu == false && notesRetu == false) {
            $('#updategroupsession').submit();
        }
    });
    // show session cancel popup on cancel button click
    $(document).on('click', '#cancelSessionModel', function(e) {
        var bid = $(this).data('id');
        $('#cancelSessionForm').attr('action', url.cancelSession.replace(':bid', bid));
        $('#cancel-session-model-box').data("bid", bid).modal('show');
    });
    // reset cancel session reason from once popup is closed
    $(document).on('hidden.bs.modal', '#cancel-session-model-box', function(e) {
        $('#cancelled_reason').val('').removeClass('is-invalid is-valid');
        $('#cancelled_reason-error').remove();
        $('#cancelSessionForm').attr('action', '');
    });
    // cancel session on yes button of cancel modal
    $(document).on('click', '#session-cancel-model-box-confirm', function(e) {
        $('#cancelled_reason-error').remove();
        var res = [];
        var cancelledReason = $('#cancelled_reason').val();
        if(cancelledReason.length > 1000) {
            $('textarea#cancelled_reason').after('<div id="cancelled_reason-error" class="error text-danger">'+message.cancel_reason_required+'</div>');
            res.push("name");
        }else{
            removeFrmArr(res, 'name');
        }
        if(res.length <= 0){
            toastr.clear();
            var bid = $('#cancel-session-model-box').data("bid");
            $('.page-loader-wrapper').show();
            $.ajax({
                type: 'POST',
                url: url.cancelSession.replace(":bid", bid),
                data: {
                    cancelled_reason: $('#cancelled_reason').val()
                },
                success: function (data) {
                    var sessionCancelModalBox = '#cancel-session-model-box';
                    $(sessionCancelModalBox).modal('hide');
                    $('.page-loader-wrapper').hide();

                    if(data.status == 1) {
                        toastr.success(message.cancelled_success);
                    } else {
                        toastr.error(message.cancelled_error);
                    }

                    setTimeout(function () {
                        window.location.reload()
                    }, 1000);
                },
            })
        }
    });
    $(document).on('click', '.reschedule-button', function() {
        $('.page-loader-wrapper').show();
        let url = $(this).attr('url');
        window.location.href = url;
    });
});
function removeFrmArr(array, element) {
    return array.filter(e => e !== element);
}