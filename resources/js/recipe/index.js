$(document).ready(function() {
    $('#visible_to_company_tbl').DataTable({
        lengthChange: false,
        pageLength: 10,
        autoWidth: false,
        columns: [{
            data: 'id',
            name: 'id'
        }, {
            data: 'group_type',
            name: 'group_type'
        }, {
            data: 'company',
            name: 'company'
        }],
        order: [
            [0, 'asc']
        ],
        dom: '<<"pagination-top"lB><t><"pagination-wrap"<"pagination-left"i>p>',
        language: {
            paginate: {
                previous: pagination.previous,
                next: pagination.next,
            },
            sInfo: "Entries _START_ to _END_",
            infoFiltered: ""
        },
    });
    $(document).on('click', '.companyDelete', function(t) {
        $('#delete-model-box').data("id", $(this).data('id')).modal('show');
    });
    $(document).on('click', '#delete-model-box-confirm', function(e) {
        $('.page-loader-wrapper').show();
        var objectId = $('#delete-model-box').data("id");
        $.ajax({
            type: 'DELETE',
            url: url.delete.replace(':id', objectId),
            contentType: 'json'
        }).done(function(data) {
            $('#receipeManagment').DataTable().ajax.reload(null, false);
            if (data.deleted == true) {
                toastr.success(data.message);
            } else {
                toastr.error(data.message);
            }
        }).fail(function(data) {
            toastr.error(messages.delete_fail);
        }).always(function() {
            $('#delete-model-box').modal('hide');
            $('.page-loader-wrapper').hide();
        });
    });
    $(document).on('click', '.recipeApprove', function(e) {
        $('#approve-recipe-model-box').data("id", $(this).data('id')).modal('show');
    });
    $(document).on('click', '#approve-recipe-model-box-confirm', function(e) {
        var _this = $(this);
        _this.prop('disabled', 'disabled');
        var objectId = $('#approve-recipe-model-box').data("id");
        $.ajax({
            type: 'POST',
            url: url.approve.replace(':id', objectId),
            contentType: 'json'
        }).done(function(data) {
            $('#receipeManagment').DataTable().ajax.reload(null, false);
            if (data.approved == true) {
                $('#count_unapproved').html(data.count_unapproved);
                $('#count_approved').html(data.count_approved);
                toastr.success(data.message);
            } else {
                toastr.error(data.message);
            }
        }).fail(function(data) {
            toastr.error(messages.approve_fail);
        }).always(function() {
            _this.removeAttr('disabled');
            $('#approve-recipe-model-box').modal('hide');
        });
    });
    $(document).on('click', '.preview_companies', function(e) {
        var _data = ($(this).data('rowdata') || ''),
            dataJson = [];
        if (_data != "") {
            _data = $.parseJSON(atob($(this).data('rowdata')));
            $(_data).each(function(index, el) {
                dataJson.push({
                    id: (index + 1),
                    group_type: el.group_type,
                    company: el.name
                });
            });
            $('#visible_to_company_tbl').DataTable().clear().rows.add(dataJson).order([0, 'asc']).search('').draw();
            $('#company_visibility_preview').modal('show')
        }
    });
});