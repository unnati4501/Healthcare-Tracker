let notesCkeditorAdd;

function loadCkeditor(){
    CKEDITOR.ClassicEditor.create(document.querySelector( '.notes-add-ckeditor' ), {
        toolbar: {
            items: [
                'undo', 'redo',
                'bold', 'italic', 'strikethrough', 'underline', '|',
                'heading', '|',
                '-',
                'fontColor', 'fontBackgroundColor',
                'alignment', '|',
                'link', 'codeBlock', '|',
                'sourceEditing'
            ],
            shouldNotGroupWhenFull: true
        },
        list: {
            properties: {
                styles: true,
                startIndex: true,
                reversed: true
            }
        },
        heading: {
            options: [
                { model: 'paragraph', title: 'Paragraph', class: 'ck-heading_paragraph' },
                { model: 'heading1', view: 'h1', title: 'Heading 1', class: 'ck-heading_heading1' },
                { model: 'heading2', view: 'h2', title: 'Heading 2', class: 'ck-heading_heading2' },
                { model: 'heading3', view: 'h3', title: 'Heading 3', class: 'ck-heading_heading3' },
                { model: 'heading4', view: 'h4', title: 'Heading 4', class: 'ck-heading_heading4' },
                { model: 'heading5', view: 'h5', title: 'Heading 5', class: 'ck-heading_heading5' },
                { model: 'heading6', view: 'h6', title: 'Heading 6', class: 'ck-heading_heading6' }
            ]
        },
        link: {
            decorators: {
                addTargetToExternalLinks: true,
                defaultProtocol: 'https://',
                toggleDownloadable: {
                    mode: 'manual',
                    label: 'Downloadable',
                    attributes: {
                        download: 'file'
                    }
                }
            }
        },
        mention: {
            feeds: [
                {
                    marker: '@',
                    feed: [
                        '@apple', '@bears', '@brownie', '@cake', '@cake', '@candy', '@canes', '@chocolate', '@cookie', '@cotton', '@cream',
                        '@cupcake', '@danish', '@donut', '@dragée', '@fruitcake', '@gingerbread', '@gummi', '@ice', '@jelly-o',
                        '@liquorice', '@macaroon', '@marzipan', '@oat', '@pie', '@plum', '@pudding', '@sesame', '@snaps', '@soufflé',
                        '@sugar', '@sweet', '@topping', '@wafer'
                    ],
                    minimumCharacters: 1
                }
            ]
        },
        updateSourceElementOnDestroy: true,
        removePlugins: [
            'CKBox',
            'RealTimeCollaborativeComments',
            'RealTimeCollaborativeTrackChanges',
            'RealTimeCollaborativeRevisionHistory',
            'PresenceList',
            'Comments',
            'TrackChanges',
            'TrackChangesData',
            'RevisionHistory',
            'Pagination',
            'WProofreader',
            'MathType'
        ]
    }).then( editor => {
        notesCkeditorAdd = editor;
    } )
    .catch( err => {
        console.error( err.stack );
    } );
}

$(document).ready(function() {
    loadCkeditor();
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        timeout: (60000 * 20)
    });
    var subCategories = null;
    $('#questionsTbl').DataTable({
        order: [],
        paging: false,
        pageLength: pagination.value,
        lengthChange: false,
        searching: false,
        ordering: true,
        info: false,
        autoWidth: false,
        columnDefs: [{
            targets: 'no-sort',
            orderable: false,
        }],
        dom: '<<"pagination-top"lB><t><"pagination-wrap"<"pagination-left"i>p>',
        stateSave: false
    });
    $(document).on('click', '#addQuestion', function() {
        var serviceConfirmModalBox = '#addQuestion-model-box';
        $('#question_title').val(' ');
        $('#editflag').val(' ');
        $('#question_description-error-cstm, #question_title-error-cstm').html('').hide();
        // CKEDITOR.instances['question_description'].setData('');
        $(serviceConfirmModalBox).modal('show');
        $.fn.modal.Constructor.prototype._enforceFocus = function() {
          modal_this = this
          $(document).on('focusin', function (e) {
            if (modal_this.$element[0] !== e.target && !modal_this.$element.has(e.target).length 
            && !$(e.target.parentNode).hasClass('cke_dialog_ui_input_select') 
            && !$(e.target.parentNode).hasClass('cke_dialog_ui_input_text')) {
              modal_this.$element.focus()
            }
          })
        };
    });
    $(document).on('click', '#editQuestion', function() {
        var _id = $(this).attr('orderId');
        $('#editflag').val(_id);
        $('#question_description-error-cstm, #question_title-error-cstm').html('').hide();
        var questionTitle = $('#question_name_'+_id).val();
        var questionDescription = $('#question_description_'+_id).val();
        $('#question_title').val(questionTitle);
        content = notesCkeditorAdd.getData();
        description = $(content).text().trim();
        notesCkeditorAdd.setData(questionDescription);
        notesCkeditorAdd.destroy();
    
        loadCkeditor();
        // CKEDITOR.instances['question_description'].setData(questionDescription);
        var serviceConfirmModalBox = '#addQuestion-model-box';
        $(serviceConfirmModalBox).modal('show');
        $.fn.modal.Constructor.prototype._enforceFocus = function() {
          modal_this = this
          $(document).on('focusin', function (e) {
            if (modal_this.$element[0] !== e.target && !modal_this.$element.has(e.target).length 
            && !$(e.target.parentNode).hasClass('cke_dialog_ui_input_select') 
            && !$(e.target.parentNode).hasClass('cke_dialog_ui_input_text')) {
              modal_this.$element.focus()
            }
          })
        };
    });
    $(document).on('click', '#questionSave', function() {
        var editFlag = $('#editflag').val();
        var isError = 0;
        var isErrorDes = 0;
        var questionTitle = $.trim($('#question_title').val());
        var regexValidation = /^[a-zA-Z]+(([',. -][a-zA-Z ])?[a-zA-Z]*)*$/;
        // var questionDescription = $('#question_description').val();
       
        const questionDescription = notesCkeditorAdd.getData();
        notesCkeditorAdd.setData(questionDescription);
        notesCkeditorAdd.destroy();
        loadCkeditor();
        $('#question_description-error-cstm, #question_title-error-cstm').html('').hide();
        if(!regexValidation.test(questionTitle)) {
            $('#question_title-error-cstm').html(message.title_required).addClass('is-invalid').show();
            isError = 0;
        } else {
            if(questionTitle.length > 100) {
                isError = 0;
                $('#question_title-error-cstm').html(message.title_length).addClass('is-invalid').show();
            } else {
                isError = 1;
                $('#question_title-error-cstm').removeClass('is-invalid').hide();
            }
        }
        if(questionDescription == ' ' || questionDescription.length <= 0) {
            $('#question_description-error-cstm').html(message.desc_required).addClass('is-invalid').show();
            isErrorDes = 0;
        } else {
            if(description.length > 7000) {
                isErrorDes = 0;
                $('#question_description-error-cstm').html(message.desc_length).addClass('is-invalid').show();
            } else {
                isErrorDes = 1;
                $('#question_description-error-cstm').removeClass('is-invalid').hide();
            }
        }
        if(isError == 1 && isErrorDes == 1) {
            if(editFlag > 0) {
                $('#title_'+editFlag).text(questionTitle);
                $('#question_name_'+editFlag).val(questionTitle);
                $('#question_description_'+editFlag).val(questionDescription);
            } else {
                var totalCount = $('.list_question_title').length + 1;
                var template = $('#consent_form_question_data_template').text().trim().replace(/:key/g, totalCount).replace(/:title/g, questionTitle).replace(":description", questionDescription).replace(/:id/g, totalCount).replace('question-remove hide', 'question-remove');
                $("#questionsTbl tbody").append(template);
            }
            var serviceConfirmModalBox = '#addQuestion-model-box';
            $(serviceConfirmModalBox).modal('hide');
        }
    });
    $(document).on('click', '.question-remove', function() {
        var _id = $(this).attr('orderId');
        var deleteConfirmationBox = '#delete-question-model-box';
        $('#delete-question-model-box-confirm').attr('orderId', _id);
        $(deleteConfirmationBox).modal('show');
        // $(this).parent().parent().remove();
    });

    $(document).on('click','#delete-question-model-box-confirm', function() {
        var _id = $(this).attr('orderId');
        $('#row_'+_id).remove();
        var deleteConfirmationBox = '#delete-question-model-box';
        $(deleteConfirmationBox).modal('hide');
        toastr.success(message.question_deleted);
    });
    $(document).on('submit', '#updateconsentform', function() {
        var isError = 1;
        $('#description-error-cstm').removeClass('is-invalid').hide().html('');
        if(description == ''){
            $('#zevo_submit_btn').removeAttr('disabled');
            event.preventDefault();
            if($('#updateconsentform').length > 0) {
                $('#updateconsentform').valid();
            } else {
                
                $('#updateconsentform').valid();
            }
            $('#description-error-cstm').html(message.desc_required).addClass('is-invalid').show();
            isError = 0;
        } else {
            if(description.length > 7000) {
                event.preventDefault();
                isError = 0;
                $('#description-error-cstm').html(message.desc_length).addClass('is-invalid').show();
            } else {
                isError = 1;
                $('#description-error-cstm').removeClass('is-invalid').hide();
            }
            $('#zevo_submit_btn').removeAttr('disabled');
        }
        return true;
    });
});