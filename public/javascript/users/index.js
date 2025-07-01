document.addEventListener('DOMContentLoaded', function () {
    window.tigress = window.tigress || {};

    window.tigress.loadTranslations(language.translations)
        .then(function () {

        let url = '/users/get/active';
        if (variables.show === 'archive') {
            url = '/users/get/inactive';
        }

        const tableUsers = $('#dataTableUsers').DataTable({
            processing: true,
            layout: {
                top: {
                    searchPanes: {
                        layout: 'columns-3',
                        viewTotal: false,
                        cascadePanes: true,
                        initCollapsed: true,
                        orderable: false,
                        columns: [6]
                    },
                },
            },
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
                    title: __('ID'),
                    data: "id",
                },
                {
                    title: __('Family name'),
                    data: "last_name",
                },
                {
                    title: __('First name'),
                    data: "first_name",
                },
                {
                    title: __('E-mail'),
                    data: "email",
                },
                {
                    title: __('Last login'),
                    data: "last_login",
                    className: "text-nowrap",
                },
                {
                    title: "recht",
                    data: "access_level",
                    visible: false,
                },
                {
                    title: __('Access level'),
                    data: "access_level_name",
                    render: function (data, type, row) {
                        return __(data);
                    }
                },
                {
                    title: __('Actions'),
                    data: null,
                    width: "1%",
                    class: "text-nowrap",
                    targets: -1,
                    render: function (nTd, sData, oData, iRow, iCol) {
                        let output = "<form action='#' method='post' enctype='multipart/form-data'>";
                        if (variables.show === 'archive') {
                            if (variables.delete) {
                                output += ` <button title="${__('Restore')}" type="button" class="btn btn-sm btn-success open-modal" data-bs-toggle="modal" data-bs-target="#confirm-undelete" data-id="${oData.id}"><i class="fa-solid fa-undo" aria-hidden="true"></i></button>`;
                            }
                        } else {
                            if (variables.write) {
                                output += ` <a data-bs-toggle="tooltip" title="${__('Edit')}" href="/users/edit/${oData.id}" class="btn btn-sm btn-success"><i class='fa fa-pencil' aria-hidden='true'></i></a>`;
                                output += ` <a data-bs-toggle="tooltip" title="${__('Edit rights')}" href="/users/rights/${oData.id}" class="btn btn-sm btn-warning"><i class='fa fa-gear' aria-hidden='true'></i></a>`;
                            }
                            if (variables.delete) {
                                output += ` <button title="${__('Archive')}" type="button" class="btn btn-sm btn-danger open-modal" data-bs-toggle="modal" data-bs-target="#confirm-delete" data-id="${oData.id}"><i class="fa fa-archive" aria-hidden="true"></i></button>`;
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
                    render: function (data, type) {
                        if (!data) {
                            return '';
                        }
                        if (data === '0000-00-00 00:00:00') {
                            return type === 'display' ? `<span class="text-muted">${__('No login')}</span>` : null;
                        }
                        return type === 'display'
                            ? moment(data, 'YYYY-MM-DD HH:mm:ss').format('DD-MM-YYYY, HH:mm')
                            : moment(data, 'YYYY-MM-DD HH:mm:ss').valueOf();
                    }
                }
            ],
            stateSave: true,
            order: [[1, "asc"]],
            language: tigress.languageOption,
        });

        // Tooltip initialiseren bij elke redraw
        tableUsers.on('draw', function () {
            initTooltips();
        });

        let modalDelete = document.getElementById('confirm-delete');
        if (modalDelete) {
            modalDelete.addEventListener('show.bs.modal', function (event) {
                let button = event.relatedTarget;
                let itemId = button.getAttribute('data-id');
                let input = modalDelete.querySelector('#DeleteUser');
                if (input) input.value = itemId;
            });
        }

        let modalRestore = document.getElementById('confirm-undelete');
        if (modalRestore) {
            modalRestore.addEventListener('show.bs.modal', function (event) {
                let button = event.relatedTarget;
                let itemId = button.getAttribute('data-id');
                let input = modalRestore.querySelector('#RestoreUser');
                if (input) input.value = itemId;
            });
        }
    })
});