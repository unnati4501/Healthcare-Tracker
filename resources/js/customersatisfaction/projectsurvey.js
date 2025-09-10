$(document).ready(function(){
    $(document).on('click', '#customerFeedBack',function(){
        customerFeedBack();
    });
    $(document).on('click','#resetcustomerFeedBack',function(){
        window.location.reload();
    });
    $('.select2').select2();
    customerFeedBack();
});
function copySurveyLink(link) {
    var x = document.createElement("INPUT");
    $("body").append(x);
    x.setAttribute("type", "text");
    x.setAttribute("value",link);
    x.select();
    document.execCommand("copy");
    toastr.success(message.survey_link_copied);
}
function customerFeedBack() {
    var fileName = "report";
    $('#challengeUserActivity').DataTable({
        destroy: true,
        processing: true,
        serverSide: true,
        ajax: {
            url: url.datatable,
            data: {
                status: 1,
                feedBackType: $('#feedBackType').val(),
                getQueryString: window.location.search
            },
        },
        columns: [
            {data: 'survey_received_on', name: 'survey_received_on' , visible: false},
            {data: 'logo', name: 'logo'},
            {data: 'feedback_emoji', name: 'feedback_emoji'},
            {data: 'feedback', name: 'feedback'},
            {
              data: 'survey_received_on',
              name: 'survey_received_on',
              render: function (data, type, row) {
                return moment.utc(row.survey_received_on).format(date_format);
              }
            }
        ],
        paging: true,
        pageLength: pagination.value,
        lengthChange: true,
        lengthMenu: [[25, 50, 100, -1], [25, 50, 100, 'All']],
        searching: false,
        ordering: true,
        order: [[4, 'desc']],
        info: true,
        autoWidth: false,
        columnDefs: [{
            targets: 'no-sort',
            orderable: false,
        }],
        stateSave: false,
        "language": {
            "paginate": {
                "previous": pagination.previous,
                "next": pagination.next
            },
            "lengthMenu": "Entries per page _MENU_",
        },
        dom: '<<"pagination-top"lB><t><"pagination-wrap"<"pagination-left"i>p>',
        buttons: [{
            extend: 'excel',
            text: button.export,
            className: 'btn btn-primary',
            title: `${fileName}_${Date.now()}`,
            download: 'open',
            orientation:'landscape',
            exportOptions: {
                columns: [2,3,4],
                order : 'current'
            }
        }]

    });
}