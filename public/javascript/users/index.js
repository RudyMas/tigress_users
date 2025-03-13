$(function () {
    let url = '/users/get/active';
    if (variables.show === 'archive') {
        url = '/users/get/inactive';
    }

    let table = $('#dataTableUsers').DataTable({
        processing: true,
        ajax: {
            url: url,
            dataType: 'json',
        },
        lengthMenu: [
            [10, 25, 50, -1],
            [10, 25, 50, "Alle"]
        ],
        responsive: true,
        columns: [
            {
                data: "id",
                visible: true,
            },
            {
                title: "Family Name",
                data: "last_name",
            },
            {
                title: "Firstname",
                data: "first_name",
            },
            {
                title: "E-mail",
                data: "email",
            },
            {
                title: "Last Login",
                data: "modified",
                className: "text-nowrap",
            },
            {
                title: "recht",
                data: "access_level",
                visible: false,
            },
            {
                title: "Access Level",
                data: "access_level_name",
            },
            {
                data: null,
                width: "1%",
                class: "text-nowrap",
                targets: -1,
                title: "Actions",
                render: function (nTd, sData, oData, iRow, iCol) {
                    let output = "<form action='#' method='post' enctype='multipart/form-data'>";
                    if (variables.show === 'archive') {
                        if (variables.deleteAccess) {
                            output += ' <a data-toggle="tooltip" title="Restore user"><button type="button" class="btn btn-sm btn-success open-modal" data-bs-toggle="modal" data-bs-target="#confirm-undelete" data-id="' + oData.id + '"><i class="fa-solid fa-recycle" aria-hidden="true"></i></button></a>';
                        }
                    } else {
                        if (variables.writeAccess) {
                            output += " <a href='/users/edit/" + (oData.id) + "'><button type='button' class='btn btn-sm btn-warning' data-toggle='tooltip' title='Edit'><i class='fa fa-pencil' aria-hidden='true'></i></button></a>";
                            output += " <a href='/users/rights/" + (oData.id) + "'><button type='button' class='btn btn-sm btn-secondary' data-toggle='tooltip' title='Edit Rights'><i class='fa fa-gear' aria-hidden='true'></i></button></a>";
                        }
                        if (variables.deleteAccess) {
                            output += ' <button type="button" class="btn btn-sm btn-danger open-modal" data-toggle="tooltip" title="Archive" data-bs-toggle="modal" data-bs-target="#confirm-delete" data-id="' + oData.id + '"><i class="fa fa-trash" aria-hidden="true"></i></button>';
                        }
                    }
                    output += "</form>";
                    return output;
                },
            },
        ],
        columnDefs: [
            {
                targets: 4,
                type: "datetime-moment",
                render: function (data, type, row) {
                    if (!data) return "";

                    if (type === "display") {
                        return moment(data, "YYYY-MM-DD HH:mm:ss").format("DD/MM/YYYY HH:mm:ss");
                    }
                    return moment(data, "YYYY-MM-DD HH:mm:ss").valueOf();
                }
            }
        ],
        order: [
            [1, "asc"]
        ],
        language: {
            // url: '/node_modules/datatables.net-plugins/i18n/nl-NL.json',
        },
        initComplete: function (settings, json) {
            setInterval(function () {
                table.ajax.reload();
            }, 180000);

            table.column(6).data().unique().sort().each(function (d, j) {
                $('#rightsFilter').append('<option value="' + d + '">' + d + '</option>');
            });

            $('#rightsFilter').on('change', function () {
                table.column(6).search(this.value).draw();
            });
        },
    });

    table.on('draw', function () {
        $('[data-toggle="tooltip"]').tooltip({
            boundary: 'window',
            trigger: 'hover'
        });
    });

    function clearSearch() {
        document.getElementById('searchBalk').value = "";
        let table = dataTable.DataTable();
        table.search('').draw();
    }

    let modalDelete = $('#confirm-delete');
    modalDelete.on('show.bs.modal', function (event) {
        let button = $(event.relatedTarget);
        let itemId = button.data('id');

        modalDelete.find('#DeleteUser').val(itemId);
    });

    let modalRestore = $('#confirm-undelete');
    modalRestore.on('show.bs.modal', function (event) {
        let button = $(event.relatedTarget);
        let itemId = button.data('id');

        modalRestore.find('#RestoreUser').val(itemId);
    });
});