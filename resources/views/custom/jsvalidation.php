<script>
    $(document).ready(function() {
        $("<?=$validator['selector'];?>").each(function() {
            $(this).validate({
                errorElement: 'div',
                errorClass: 'invalid-feedback',
                errorPlacement: function(error, element) {
                    if ($(element).data('errplaceholder')) {
                        $(element.data('errplaceholder')).html(error);
                    } else if (element.parent('.input-group').length || element.prop('type') === 'checkbox' || element.prop('type') === 'radio') {
                        if(element.prop('type') === 'text') {
                            if(element.next().hasClass('input-group-append')) {
                                error.insertAfter(element.next())
                            } else {
                                error.insertAfter(element);
                            }
                        } else {
                            error.insertAfter(element.parent().parent());
                        }
                        // else just place the validation message immediately after the input
                    } else if (element.prop('type') === 'select-one' || element.prop('type') === 'select-multiple') {
                        // Patch :- 1 , For select2 error placement at bottom of the control
                        if (element.hasClass('double-error')) {
                            error.insertAfter(element);
                        } else {
                            element.closest('.form-control').parent().append(error);
                        }
                    } else if (element.hasClass('sideProfImage')) {
                        error.insertAfter(element.parent());
                    } else {
                        error.insertAfter(element);
                    }
                },
                highlight: function(element) {
                    $(element).closest('.form-control').removeClass('is-valid').addClass('is-invalid');
                    if($(element).hasClass('article-ckeditor')) {
                        $(element).next().removeClass('is-valid-cstm is-valid').addClass('is-invalid-cstm is-invalid');
                    }
                },
                <?php if (isset($validator['ignore']) && is_string($validator['ignore'])): ?>
                ignore: "<?=$validator['ignore'];?>",
                <?php endif;?>
                // Uncomment this to mark as validated non required fields
                unhighlight: function(element) {
                    $(element).closest('.form-control').removeClass('is-invalid').addClass('is-valid');
                    if($(element).hasClass('article-ckeditor')) {
                        $(element).next().removeClass('is-invalid-cstm is-invalid').addClass('is-valid-cstm is-valid');
                    }
                },
                success: function(element) {
                    $(element).closest('.form-control').removeClass('is-invalid').addClass('is-valid'); // remove the Boostrap error class from the control group
                },
                focusInvalid: false, // do not focus the last invalid input
                invalidHandler: function(form, validator) {
                    $('#zevo_submit_btn').removeAttr('disabled');
                    // if (!validator.numberOfInvalids()) return;
                    // $('html, body').animate({
                    //     scrollTop: $(validator.errorList[0].element).offset().top
                    // }, <?=Config::get('jsvalidation.duration_animate')?>);
                    // $(validator.errorList[0].element).focus();
                },
                <?php if (Config::get('jsvalidation.focus_on_error')): ?>
                <?php endif;?>
                rules: <?=json_encode($validator['rules']);?>
            });
        });
        $('.select2').change(function() {
            if (this.value != "" || this.value != undefined) {
                $(this).closest('.form-control').removeClass('is-invalid').addClass('is-valid');
            }
        });
    });
</script>