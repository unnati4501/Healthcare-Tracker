$(document).ready(function() {
    collapseCard();
    $(function() {
        $('#questionManagment').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: hsQuestionListUrl,
                data: {
                    question: $('#question').val(),
                    category: $('#category').val(),
                    sub_category: $('#sub_category').val(),
                    question_type: $('#question_type').val()
                },
            },
            columns: [{
                data: 'updated_at',
                name: 'updated_at',
                className: 'hidden'
            }, {
                data: 'category',
                name: 'category'
            }, {
                data: 'sub_category',
                name: 'sub_category'
            }, {
                data: 'question',
                name: 'question'
            }, {
                data: 'image',
                name: 'image'
            }, {
                data: 'question_type',
                name: 'question_type'
            }, {
                data: 'actions',
                name: 'actions',
                searchable: false,
                sortable: false
            }],
            paging: true,
            pageLength: pagination,
            lengthChange: false,
            searching: false,
            ordering: true,
            order: [
                [0, 'desc']
            ],
            dom: '<<"pagination-top"lB><t><"pagination-wrap"<"pagination-left"i>p>',
            info: true,
            autoWidth: false,
            columnDefs: [{
                targets: 'no-sort',
                orderable: false,
            }, {
                targets: [4,6],
                className: 'text-center',
            }],
            stateSave: false
        });
    });
});
$(document).on('click', '#getQuestions', function(e) {
    $('#questionModelData').empty();
    var questionId = $(this).attr("data-id");
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    $.ajax({
        type: 'GET',
        url: hsQuestionShowUrl + '/' + questionId,
        data: {
            modal: true
        },
        contentType: 'html',
        success: function(data) {
            $('#questionModelData').html(data);
            $('#questionShow').modal({
                show: true,
                keyboard: false,
                backdrop: 'static'
            });
        }
    });
    $('#questionManagment').DataTable().ajax.reload(null, false);
});
$('#category').change(function() {
    if ($('#category').val() != '' && $('#category').val() != null) {
        if ($(this).attr("id") == 'category' && $(this).attr('target-data') == 'sub_category') {
            var select = $(this).attr("id");
            var value = $(this).val();
            var subCategoryDependent = $(this).attr('target-data');
            var _token = $('input[name="_token"]').val();
            url = hsSubCategoryUrl.replace(':id', value);
            $.ajax({
                url: url,
                method: 'get',
                data: {
                    _token: _token
                },
                success: function(result) {
                    $('#' + subCategoryDependent).empty();
                    $('#' + subCategoryDependent).attr('disabled', false);
                    $('#' + subCategoryDependent).val('').trigger('change').append('<option value="">Select</option>');
                    $('#' + subCategoryDependent).removeClass('is-valid');
                    $.each(result.result, function(key, value) {
                        $('#' + subCategoryDependent).append('<option value="' + value.id + '">' + value.name + '</option>');
                    });
                    if (Object.keys(result.result).length == 1) {
                        $.each(result.result, function(key, value) {
                            $('#' + subCategoryDependent).select2('val', value.id);
                        });
                    }
                }
            })
        }
    }
});
/*
 * Js function used for search card collapse.
 */
function collapseCard() {
    var question = document.getElementById("question").value;
    var category = document.getElementById("category").value;
    var sub_category = document.getElementById("sub_category").value;
    var question_type = document.getElementById("question_type").value;
    if (question != '' || category != '' || sub_category != '' || question_type != '') {
        $('#collapseCard').removeClass('collapsed-card');
    } else {
        $('#collapseCard').addClass('collapsed-card');
    }
}