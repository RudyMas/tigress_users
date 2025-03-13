$(function () {
    let url = '/users/get/active';
    if (variables.toon === 'archief') {
        url = '/users/get/inactive';
    }

    let table = $('#dataTableGebruikers').DataTable({
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
                visible: false,
            },
            {
                title: "Naam",
                data: "last_name",
            },
            {
                title: "Voornaam",
                data: "first_name",
            },
            {
                title: "Login",
                data: "modified",
                className: "text-nowrap",
            },
            {
                title: "E-mail",
                data: "email",
            },
            {
                title: "School/Team",
                data: null,
                render: function (data, type, row) {
                    if (row.school !== '') {
                        return row.school;
                    } else if (row.team !== '') {
                        return row.team;
                    } else {
                        return "";
                    }
                }
            },
            {
                title: "recht",
                data: "access_level",
                visible: false,
            },
            {
                title: "Recht",
                data: "access_level_name",
            },
            {
                title: "Fun & Eva",
                data: null,
                className: "text-nowrap",
                render: function (data, type, row) {
                    if (row.functie !== null) {
                        switch (row.functie) {
                            case 0:
                                row.functie = "";
                                break;
                            case 1:
                                row.functie = "Coach";
                                break;
                            case 2:
                                row.functie = "Evaluator";
                                break;
                            case 3:
                                row.functie = "Coach/Evaluator";
                                break;
                            case 4:
                                row.functie = "Directie";
                                break;
                            case 5:
                                row.functie = "Personeelsdienst";
                                break;
                        }
                        return row.functie;
                    } else {
                        return "";
                    }
                }
            },
            {
                data: null,
                width: "1%",
                class: "text-nowrap",
                targets: -1,
                title: "Acties",
                render: function (nTd, sData, oData, iRow, iCol) {
                    let uitvoer = "<form action='#' method='post' enctype='multipart/form-data'>";
                    if (variables.toon === 'archief') {
                        if (variables.verwijderToegang) {
                            uitvoer += ' <a data-toggle="tooltip" title="Terugplaatsen"><button type="button" class="btn btn-sm btn-success open-modal" data-bs-toggle="modal" data-bs-target="#confirm-undelete" data-id="' + oData.id + '"><i class="fa-solid fa-recycle" aria-hidden="true"></i></button></a>';
                        }
                    } else {
                        if (variables.schrijfToegang) {
                            uitvoer += " <a href='/users/edit/" + (oData.id) + "'><button type='button' class='btn btn-sm btn-warning' data-toggle='tooltip' title='Bewerk'><i class='fa fa-pencil' aria-hidden='true'></i></button></a>";
                            uitvoer += " <a href='/users/rechten/" + (oData.id) + "'><button type='button' class='btn btn-sm btn-secondary' data-toggle='tooltip' title='Rechten'><i class='fa fa-gear' aria-hidden='true'></i></button></a>";
                        }
                        if (variables.verwijderToegang) {
                            uitvoer += ' <button type="button" class="btn btn-sm btn-danger open-modal" data-toggle="tooltip" title="Verwijder" data-bs-toggle="modal" data-bs-target="#confirm-delete" data-id="' + oData.id + '"><i class="fa fa-trash" aria-hidden="true"></i></button>';
                        }
                    }
                    uitvoer += "</form>";
                    return uitvoer;
                },
            },
        ],
        columnDefs: [
            {
                targets: 3,
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
            url: '/node_modules/datatables.net-plugins/i18n/nl-NL.json',
        },
        initComplete: function (settings, json) {
            setInterval(function () {
                table.ajax.reload();
            }, 180000);

            table.column(7).data().unique().sort().each(function (d, j) {
                $('#rechtFilter').append('<option value="' + d + '">' + d + '</option>');
            });

            $('#rechtFilter').on('change', function () {
                table.column(7).search(this.value).draw();
            });
        },
    });

    let schoolTeamFilter = $('#schoolTeamFilter');
    schoolTeamFilter.on('change', function () {
        table.draw(); // Redraw the table to filter
    });
    $.fn.dataTable.ext.search.push(
        function (settings, data, dataIndex) {
            let selectedSchoolTeam = schoolTeamFilter.val();
            let schoolTeam = data[5];
            return !!(selectedSchoolTeam === "" || schoolTeam.includes(selectedSchoolTeam));
        }
    );

    let funEvaFilter = $('#funEvaFilter');
    funEvaFilter.on('change', function () {
        table.draw(); // Redraw the table to filter
    });
    $.fn.dataTable.ext.search.push(
        function (settings, data, dataIndex) {
            let selectedFunEva = funEvaFilter.val();
            let funEva = data[8]; // Adjust the index 8 if necessary
            return (selectedFunEva === "" || funEva.includes(selectedFunEva));
        }
    );

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

    let modalVerwijder = $('#confirm-delete');
    modalVerwijder.on('show.bs.modal', function (event) {
        let button = $(event.relatedTarget);
        let itemId = button.data('id');

        modalVerwijder.find('#VerwijderUser').val(itemId);
    });

    let modalTerugzetten = $('#confirm-undelete');
    modalTerugzetten.on('show.bs.modal', function (event) {
        let button = $(event.relatedTarget);
        let itemId = button.data('id');

        modalTerugzetten.find('#TerugzettenUser').val(itemId);
    });
});