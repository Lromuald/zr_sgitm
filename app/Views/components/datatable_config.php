<?php
/**
 * Configuration DataTables Standard Français
 * Composant réutilisable pour toutes les tables de données
 */
?>
<script>
// Configuration DataTables standard en français
const dataTablesConfigFR = {
    language: {
        url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/fr-FR.json',
        decimal: ',',
        thousands: ' ',
        lengthMenu: 'Afficher _MENU_ éléments',
        search: 'Rechercher :',
        info: 'Affichage de _START_ à _END_ sur _TOTAL_ éléments',
        infoEmpty: 'Affichage de 0 à 0 sur 0 élément',
        infoFiltered: '(filtré de _MAX_ éléments au total)',
        infoPostFix: '',
        loadingRecords: 'Chargement...',
        zeroRecords: 'Aucun élément trouvé',
        emptyTable: 'Aucune donnée disponible',
        paginate: {
            first: 'Premier',
            previous: 'Précédent',
            next: 'Suivant',
            last: 'Dernier'
        },
        aria: {
            sortAscending: ': activer pour trier la colonne par ordre croissant',
            sortDescending: ': activer pour trier la colonne par ordre décroissant'
        }
    },
    pageLength: 25,
    lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'Tous']],
    responsive: true,
    dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
         '<"row"<"col-sm-12"tr>>' +
         '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
    autoWidth: false,
    processing: true,
    stateSave: false,
    order: [[0, 'asc']]
};

// Configuration DataTables avec export Excel/PDF
const dataTablesConfigFRWithExport = {
    ...dataTablesConfigFR,
    dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
         '<"row"<"col-sm-12 col-md-6"B>>' +
         '<"row"<"col-sm-12"tr>>' +
         '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
    buttons: [
        {
            extend: 'excel',
            text: '<i class="bi bi-file-earmark-excel me-1"></i> Excel',
            className: 'btn btn-success btn-sm',
            exportOptions: {
                columns: ':not(.no-export)'
            }
        },
        {
            extend: 'pdf',
            text: '<i class="bi bi-file-earmark-pdf me-1"></i> PDF',
            className: 'btn btn-danger btn-sm',
            exportOptions: {
                columns: ':not(.no-export)'
            }
        },
        {
            extend: 'print',
            text: '<i class="bi bi-printer me-1"></i> Imprimer',
            className: 'btn btn-info btn-sm',
            exportOptions: {
                columns: ':not(.no-export)'
            }
        }
    ]
};

/**
 * Initialise une DataTable avec la configuration française standard
 * @param {string} selector - Sélecteur CSS de la table
 * @param {object} customOptions - Options personnalisées supplémentaires
 * @returns {DataTable} Instance DataTable
 */
function initDataTableFR(selector, customOptions = {}) {
    const options = $.extend(true, {}, dataTablesConfigFR, customOptions);
    return $(selector).DataTable(options);
}

/**
 * Initialise une DataTable avec export
 * @param {string} selector - Sélecteur CSS de la table
 * @param {object} customOptions - Options personnalisées supplémentaires
 * @returns {DataTable} Instance DataTable
 */
function initDataTableFRWithExport(selector, customOptions = {}) {
    const options = $.extend(true, {}, dataTablesConfigFRWithExport, customOptions);
    return $(selector).DataTable(options);
}
</script>
