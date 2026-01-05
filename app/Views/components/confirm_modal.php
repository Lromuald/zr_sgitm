<?php
/**
 * Composant Modale de Confirmation
 * Modale réutilisable pour confirmer les suppressions et actions critiques
 */
?>

<!-- Confirmation Modal -->
<div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="confirmModalLabel">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <span id="confirmModalTitle">Confirmer l'action</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p id="confirmModalMessage" class="mb-0">
                    Êtes-vous sûr de vouloir effectuer cette action ? Cette action est irréversible.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-1"></i> Annuler
                </button>
                <button type="button" class="btn btn-danger" id="confirmModalBtn">
                    <i class="bi bi-check-circle me-1"></i> Confirmer
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let confirmModalCallback = null;
let confirmModalInstance = null;

/**
 * Affiche une modale de confirmation
 * @param {string} title - Titre de la modale
 * @param {string} message - Message à afficher
 * @param {function} callback - Fonction à appeler si confirmé
 * @param {string} btnText - Texte du bouton de confirmation (optionnel)
 * @param {string} btnClass - Classe CSS du bouton (optionnel, défaut: 'btn-danger')
 */
function showConfirmModal(title, message, callback, btnText = 'Confirmer', btnClass = 'btn-danger') {
    // Initialiser la modale si ce n'est pas déjà fait
    if (!confirmModalInstance) {
        confirmModalInstance = new bootstrap.Modal(document.getElementById('confirmModal'));
    }
    
    // Définir le contenu
    document.getElementById('confirmModalTitle').textContent = title;
    document.getElementById('confirmModalMessage').textContent = message;
    
    // Définir le bouton
    const confirmBtn = document.getElementById('confirmModalBtn');
    confirmBtn.textContent = btnText;
    confirmBtn.className = 'btn ' + btnClass;
    
    // Stocker le callback
    confirmModalCallback = callback;
    
    // Afficher la modale
    confirmModalInstance.show();
}

/**
 * Raccourci pour confirmer une suppression
 * @param {string} itemName - Nom de l'élément à supprimer
 * @param {function} callback - Fonction à appeler si confirmé
 */
function confirmDelete(itemName, callback) {
    showConfirmModal(
        'Confirmer la suppression',
        `Êtes-vous sûr de vouloir supprimer "${itemName}" ? Cette action est irréversible.`,
        callback,
        'Supprimer',
        'btn-danger'
    );
}

/**
 * Raccourci pour confirmer une action
 * @param {string} actionName - Nom de l'action
 * @param {string} message - Message de confirmation
 * @param {function} callback - Fonction à appeler si confirmé
 */
function confirmAction(actionName, message, callback) {
    showConfirmModal(
        `Confirmer ${actionName}`,
        message,
        callback,
        'Confirmer',
        'btn-primary'
    );
}

// Gérer le clic sur le bouton de confirmation
document.addEventListener('DOMContentLoaded', function() {
    const confirmBtn = document.getElementById('confirmModalBtn');
    
    if (confirmBtn) {
        confirmBtn.addEventListener('click', function() {
            if (typeof confirmModalCallback === 'function') {
                confirmModalCallback();
            }
            
            // Fermer la modale
            if (confirmModalInstance) {
                confirmModalInstance.hide();
            }
            
            // Réinitialiser le callback
            confirmModalCallback = null;
        });
    }
    
    // Réinitialiser le callback quand la modale est fermée
    const modalEl = document.getElementById('confirmModal');
    if (modalEl) {
        modalEl.addEventListener('hidden.bs.modal', function() {
            confirmModalCallback = null;
        });
    }
});
</script>
