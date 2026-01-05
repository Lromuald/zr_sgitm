<?php
/**
 * Composant Toast Bootstrap
 * Affichage de messages de notification temporaires
 */
?>

<!-- Toast Container -->
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 9999;">
    <div id="liveToast" class="toast align-items-center border-0" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body"></div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
</div>

<script>
/**
 * Affiche un toast de notification
 * @param {string} message - Le message à afficher
 * @param {string} type - Type: 'success', 'error', 'warning', 'info'
 * @param {number} duration - Durée en millisecondes (défaut: 5000)
 */
function showToast(message, type = 'info', duration = 5000) {
    const toastEl = document.getElementById('liveToast');
    const toastBody = toastEl.querySelector('.toast-body');
    
    // Réinitialiser les classes
    toastEl.className = 'toast align-items-center border-0';
    
    // Définir la couleur selon le type
    let bgClass = 'bg-info';
    let icon = 'bi-info-circle';
    
    switch(type) {
        case 'success':
            bgClass = 'bg-success';
            icon = 'bi-check-circle';
            break;
        case 'error':
        case 'danger':
            bgClass = 'bg-danger';
            icon = 'bi-exclamation-circle';
            break;
        case 'warning':
            bgClass = 'bg-warning';
            icon = 'bi-exclamation-triangle';
            break;
        case 'info':
            bgClass = 'bg-info';
            icon = 'bi-info-circle';
            break;
    }
    
    toastEl.classList.add(bgClass, 'text-white');
    toastBody.innerHTML = `<i class="bi ${icon} me-2"></i>${message}`;
    
    // Initialiser et afficher le toast
    const toast = new bootstrap.Toast(toastEl, {
        autohide: true,
        delay: duration
    });
    
    toast.show();
}

/**
 * Affiche un toast de succès
 * @param {string} message
 */
function showSuccessToast(message) {
    showToast(message, 'success');
}

/**
 * Affiche un toast d'erreur
 * @param {string} message
 */
function showErrorToast(message) {
    showToast(message, 'error');
}

/**
 * Affiche un toast d'avertissement
 * @param {string} message
 */
function showWarningToast(message) {
    showToast(message, 'warning');
}

/**
 * Affiche un toast d'information
 * @param {string} message
 */
function showInfoToast(message) {
    showToast(message, 'info');
}

// Auto-conversion des messages flash en toasts au chargement de la page
$(document).ready(function() {
    // Convertir les alertes existantes en toasts
    $('.alert-success').each(function() {
        const message = $(this).text().trim();
        showSuccessToast(message);
        $(this).remove();
    });
    
    $('.alert-danger').each(function() {
        const message = $(this).text().trim();
        showErrorToast(message);
        $(this).remove();
    });
    
    $('.alert-warning').each(function() {
        const message = $(this).text().trim();
        showWarningToast(message);
        $(this).remove();
    });
    
    $('.alert-info').each(function() {
        const message = $(this).text().trim();
        showInfoToast(message);
        $(this).remove();
    });
});
</script>
