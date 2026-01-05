/**
 * Fichier JavaScript utilitaire pour l'application ZRSGIMT
 */

// Configuration DataTables française standard
const dataTablesConfigFR = {
    language: {
        url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/fr-FR.json'
    },
    pageLength: 25,
    responsive: true,
    order: [[0, 'desc']]
};

/**
 * Confirmation suppression avec modal
 * @param {string} url - URL de suppression
 * @param {string} nom - Nom de l'élément à supprimer
 */
function confirmerSuppression(url, nom) {
    if (confirm(`Êtes-vous sûr de vouloir supprimer "${nom}" ?\n\nCette action est irréversible.`)) {
        window.location.href = url;
    }
}

/**
 * Afficher un toast Bootstrap
 * @param {string} message - Message à afficher
 * @param {string} type - Type de toast (success, error, warning, info)
 */
function afficherToast(message, type = 'success') {
    const toastContainer = document.getElementById('toastContainer');
    
    if (!toastContainer) {
        // Créer le container s'il n'existe pas
        const container = document.createElement('div');
        container.id = 'toastContainer';
        container.className = 'toast-container position-fixed top-0 end-0 p-3';
        container.style.zIndex = '9999';
        document.body.appendChild(container);
    }
    
    const colors = {
        success: 'bg-success',
        error: 'bg-danger',
        warning: 'bg-warning',
        info: 'bg-info'
    };
    
    const icons = {
        success: 'bi-check-circle-fill',
        error: 'bi-x-circle-fill',
        warning: 'bi-exclamation-triangle-fill',
        info: 'bi-info-circle-fill'
    };
    
    const toastId = 'toast-' + Date.now();
    const bgColor = colors[type] || colors.success;
    const icon = icons[type] || icons.success;
    
    const toastHTML = `
        <div id="${toastId}" class="toast align-items-center text-white ${bgColor} border-0" role="alert">
            <div class="d-flex">
                <div class="toast-body">
                    <i class="bi ${icon} me-2"></i>
                    ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    `;
    
    document.getElementById('toastContainer').insertAdjacentHTML('beforeend', toastHTML);
    
    const toastElement = document.getElementById(toastId);
    const toast = new bootstrap.Toast(toastElement, { delay: 4000 });
    toast.show();
    
    // Supprimer le toast après fermeture
    toastElement.addEventListener('hidden.bs.toast', function() {
        toastElement.remove();
    });
}

/**
 * Calcul automatique montants facture (HT → TTC)
 */
function calculerMontantsTVA() {
    const montantHT = parseFloat(document.getElementById('montant_ht')?.value || 0);
    const tauxTVA = parseFloat(document.getElementById('taux_tva')?.value || 0);
    
    const montantTVA = (montantHT * tauxTVA / 100).toFixed(2);
    const montantTTC = (montantHT + parseFloat(montantTVA)).toFixed(2);
    
    if (document.getElementById('montant_tva')) {
        document.getElementById('montant_tva').value = montantTVA;
    }
    
    if (document.getElementById('montant_ttc')) {
        document.getElementById('montant_ttc').value = montantTTC;
    }
}

/**
 * Preview image upload
 * @param {HTMLInputElement} input - Input file element
 * @param {string} previewId - ID de l'élément img de preview
 */
function previewImage(input, previewId) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            const preview = document.getElementById(previewId);
            if (preview) {
                preview.src = e.target.result;
                preview.style.display = 'block';
            }
        };
        
        reader.readAsDataURL(input.files[0]);
    }
}

/**
 * Initialisation au chargement de la page
 */
document.addEventListener('DOMContentLoaded', function() {
    // Initialiser les tooltips Bootstrap
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Auto-calculer montants TVA si champs présents
    const montantHTInput = document.getElementById('montant_ht');
    const tauxTVAInput = document.getElementById('taux_tva');
    
    if (montantHTInput && tauxTVAInput) {
        montantHTInput.addEventListener('input', calculerMontantsTVA);
        tauxTVAInput.addEventListener('change', calculerMontantsTVA);
    }
    
    // Validation formulaires
    const forms = document.querySelectorAll('.needs-validation');
    Array.from(forms).forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
                afficherToast('Veuillez corriger les erreurs dans le formulaire', 'error');
            }
            form.classList.add('was-validated');
        }, false);
    });
});
