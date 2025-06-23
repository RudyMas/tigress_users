document.addEventListener('DOMContentLoaded', function () {
    window.tigress = window.tigress || {};

    const allTranslations = {
        nl: {
            id: 'Id',
            access_level: 'Toegangsniveau',
            actions: 'Acties',
            archive: 'Archiveren',
            edit: 'Bewerk',
            edit_rights: 'Bewerk Rechten',
            email: 'Email',
            family_name: 'Familienaam',
            first_name: 'Voornaam',
            last_login: 'Laatste Aanmelding',
            no_login: 'Geen aanmelding',
            restore: 'Herstellen',
        },
        fr: {
            id: 'Id',
            access_level: 'Niveau d\'accès',
            actions: 'Actions',
            archive: 'Archiver',
            edit: 'Éditer',
            edit_rights: 'Modifier les droits',
            email: 'E-mail',
            family_name: 'Nom de famille',
            first_name: 'Prénom',
            last_login: 'Dernière connexion',
            no_login: 'Aucune connexion',
            restore: 'Restaurer',
        },
        de: {
            id: 'Id',
            access_level: 'Zugriffslevel',
            actions: 'Aktionen',
            archive: 'Archivieren',
            edit: 'Bearbeiten',
            edit_rights: 'Rechte bearbeiten',
            email: 'E-Mail',
            family_name: 'Familienname',
            first_name: 'Vorname',
            last_login: 'Letzte Anmeldung',
            no_login: 'Keine Anmeldung',
            restore: 'Wiederherstellen',
        },
        es: {
            id: 'Id',
            access_level: 'Nivel de acceso',
            actions: 'Acciones',
            archive: 'Archivar',
            edit: 'Editar',
            edit_rights: 'Editar derechos',
            email: 'Correo electrónico',
            family_name: 'Apellido',
            first_name: 'Nombre',
            last_login: 'Último inicio de sesión',
            no_login: 'Sin inicio de sesión',
            restore: 'Restaurar',
        },
        it: {
            id: 'Id',
            access_level: 'Livello di accesso',
            actions: 'Azioni',
            archive: 'Archivia',
            edit: 'Modifica',
            edit_rights: 'Modifica diritti',
            email: 'E-mail',
            family_name: 'Cognome',
            first_name: 'Nome',
            last_login: 'Ultimo accesso',
            no_login: 'Nessun accesso',
            restore: 'Ripristina',
        },
        en: {
            id: 'Id',
            access_level: 'Access Level',
            actions: 'Actions',
            archive: 'Archive',
            edit: 'Edit',
            edit_rights: 'Edit Rights',
            email: 'E-mail',
            family_name: 'Family Name',
            first_name: 'First Name',
            last_login: 'Last Login',
            no_login: 'No login',
            restore: 'Restore',
        }
    }

    const translations = allTranslations[tigress.shortLang] || allTranslations['en'];

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
                title: translations.id,
                data: "id",
            },
            {
                title: translations.family_name,
                data: "last_name",
            },
            {
                title: translations.first_name,
                data: "first_name",
            },
            {
                title: translations.email,
                data: "email",
            },
            {
                title: translations.last_login,
                data: "last_login",
                className: "text-nowrap",
            },
            {
                title: "recht",
                data: "access_level",
                visible: false,
            },
            {
                title: translations.access_level,
                data: "access_level_name",
            },
            {
                title: translations.actions,
                data: null,
                width: "1%",
                class: "text-nowrap",
                targets: -1,
                render: function (nTd, sData, oData, iRow, iCol) {
                    let output = "<form action='#' method='post' enctype='multipart/form-data'>";
                    if (variables.show === 'archive') {
                        if (variables.delete) {
                            output += ` <button title="${translations.restore}" type="button" class="btn btn-sm btn-success open-modal" data-bs-toggle="modal" data-bs-target="#confirm-undelete" data-id="${oData.id}"><i class="fa-solid fa-undo" aria-hidden="true"></i></button>`;
                        }
                    } else {
                        if (variables.write) {
                            output += ` <a data-bs-toggle="tooltip" title="${translations.edit}" href="/users/edit/${oData.id}" class="btn btn-sm btn-success"><i class='fa fa-pencil' aria-hidden='true'></i></a>`;
                            output += ` <a data-bs-toggle="tooltip" title="${translations.edit_rights}" href="/users/rights/${oData.id}" class="btn btn-sm btn-warning"><i class='fa fa-gear' aria-hidden='true'></i></a>`;
                        }
                        if (variables.delete) {
                            output += ` <button title="${translations.archive}" type="button" class="btn btn-sm btn-danger open-modal" data-bs-toggle="modal" data-bs-target="#confirm-delete" data-id="${oData.id}"><i class="fa fa-archive" aria-hidden="true"></i></button>`;
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
                        return type === 'display' ? `<span class="text-muted">${translations.no_login}</span>` : null;
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
});