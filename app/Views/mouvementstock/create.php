<?php
// Préparer le contenu
ob_start();
?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 mb-0"><i class="bi bi-plus-circle me-2"></i>Nouveau Mouvement de Stock</h1>
            <p class="text-muted mb-0">Entrée ou sortie de pièces</p>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <!-- Formulaire -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-pencil me-2"></i>Détails du Mouvement</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="<?= BASE_URL ?>/mouvementstock/store" id="mouvementForm">
                        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                        
                        <!-- Type de mouvement -->
                        <div class="mb-4">
                            <label class="form-label">Type de Mouvement <span class="text-danger">*</span></label>
                            <div class="btn-group w-100" role="group">
                                <input type="radio" class="btn-check" name="type_mouvement" id="type_entree" value="entree" required checked>
                                <label class="btn btn-outline-success" for="type_entree">
                                    <i class="bi bi-arrow-down-circle me-2"></i>Entrée
                                </label>
                                
                                <input type="radio" class="btn-check" name="type_mouvement" id="type_sortie" value="sortie" required>
                                <label class="btn btn-outline-danger" for="type_sortie">
                                    <i class="bi bi-arrow-up-circle me-2"></i>Sortie
                                </label>
                            </div>
                        </div>

                        <!-- Sélection pièce -->
                        <div class="mb-3">
                            <label for="piece_id" class="form-label">Pièce <span class="text-danger">*</span></label>
                            <select class="form-select" id="piece_id" name="piece_id" required>
                                <option value="">Sélectionnez une pièce...</option>
                                <?php foreach ($pieces as $piece): ?>
                                    <option value="<?= $piece['id'] ?>" 
                                            data-stock="<?= $piece['stock_actuel'] ?>"
                                            data-cump="<?= $piece['prix_unitaire_cump'] ?>"
                                            data-unite="<?= htmlspecialchars($piece['unite_mesure'] ?? '') ?>">
                                        <?= htmlspecialchars($piece['reference']) ?> - <?= htmlspecialchars($piece['description']) ?>
                                        (Stock: <?= number_format($piece['stock_actuel'], 2) ?> <?= htmlspecialchars($piece['unite_mesure'] ?? '') ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="row">
                            <!-- Quantité -->
                            <div class="col-md-6 mb-3">
                                <label for="quantite" class="form-label">Quantité <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="quantite" name="quantite" 
                                       step="0.01" min="0.01" required>
                                <div class="form-text" id="stock_actuel_text"></div>
                            </div>

                            <!-- Prix unitaire -->
                            <div class="col-md-6 mb-3">
                                <label for="prix_unitaire" class="form-label">Prix Unitaire (FC) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="prix_unitaire" name="prix_unitaire" 
                                       step="0.01" min="0" required>
                                <div class="form-text" id="cump_actuel_text"></div>
                            </div>
                        </div>

                        <!-- Date mouvement -->
                        <div class="mb-3">
                            <label for="date_mouvement" class="form-label">Date du Mouvement <span class="text-danger">*</span></label>
                            <input type="datetime-local" class="form-control" id="date_mouvement" name="date_mouvement" 
                                   value="<?= date('Y-m-d\TH:i') ?>" required>
                        </div>

                        <!-- Motif -->
                        <div class="mb-3">
                            <label for="motif" class="form-label">Motif</label>
                            <textarea class="form-control" id="motif" name="motif" rows="3" 
                                      placeholder="Raison du mouvement (optionnel)"></textarea>
                        </div>

                        <!-- Référence document (pour entrées) -->
                        <div class="mb-3" id="reference_document_group">
                            <label for="reference_document" class="form-label">Référence Document</label>
                            <input type="text" class="form-control" id="reference_document" name="reference_document" 
                                   placeholder="N° bon de commande, facture, etc.">
                        </div>

                        <!-- Boutons -->
                        <div class="d-flex justify-content-between">
                            <a href="<?= BASE_URL ?>/mouvementstock" class="btn btn-secondary">
                                <i class="bi bi-x-circle me-1"></i>Annuler
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle me-1"></i>Enregistrer
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Preview CUMP -->
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="bi bi-calculator me-2"></i>Aperçu Calcul CUMP</h5>
                </div>
                <div class="card-body">
                    <div id="cump_preview">
                        <div class="text-center text-muted py-4">
                            <i class="bi bi-info-circle" style="font-size: 3rem;"></i>
                            <p class="mt-2">Sélectionnez une pièce et entrez les valeurs pour voir l'aperçu</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Aide -->
            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-question-circle me-2"></i>Aide</h5>
                </div>
                <div class="card-body">
                    <h6>Entrée:</h6>
                    <p class="small">Ajoute des pièces au stock. Le CUMP est recalculé automatiquement selon la formule :</p>
                    <div class="alert alert-info small mb-3">
                        CUMP = (Valeur Stock + Valeur Entrée) / (Stock + Quantité Entrée)
                    </div>
                    
                    <h6>Sortie:</h6>
                    <p class="small">Retire des pièces du stock. Le prix est valorisé au CUMP actuel. Le CUMP ne change pas.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();

// JavaScript spécifique
$additional_js = '
<script>
$(document).ready(function() {
    const pieceSelect = $("#piece_id");
    const quantiteInput = $("#quantite");
    const prixInput = $("#prix_unitaire");
    const typeEntree = $("#type_entree");
    const typeSortie = $("#type_sortie");

    // Mise à jour des infos pièce
    pieceSelect.on("change", function() {
        const option = $(this).find(":selected");
        const stock = parseFloat(option.data("stock")) || 0;
        const cump = parseFloat(option.data("cump")) || 0;
        const unite = option.data("unite") || "";

        $("#stock_actuel_text").html(`Stock actuel: <strong>${stock.toFixed(2)} ${unite}</strong>`);
        $("#cump_actuel_text").html(`CUMP actuel: <strong>${cump.toFixed(2)} FC</strong>`);
        
        // Pour les sorties, utiliser le CUMP comme prix par défaut
        if (typeSortie.is(":checked")) {
            prixInput.val(cump.toFixed(2));
        }
        
        updatePreview();
    });

    // Mise à jour du preview
    function updatePreview() {
        const option = pieceSelect.find(":selected");
        const stock = parseFloat(option.data("stock")) || 0;
        const cump = parseFloat(option.data("cump")) || 0;
        const unite = option.data("unite") || "";
        const quantite = parseFloat(quantiteInput.val()) || 0;
        const prix = parseFloat(prixInput.val()) || 0;
        const isEntree = typeEntree.is(":checked");

        if (pieceSelect.val() && quantite > 0 && prix > 0) {
            let html = "";
            
            if (isEntree) {
                const valeurStock = stock * cump;
                const valeurEntree = quantite * prix;
                const nouveauStock = stock + quantite;
                const nouveauCUMP = nouveauStock > 0 ? (valeurStock + valeurEntree) / nouveauStock : 0;
                const valeurNouveauStock = nouveauStock * nouveauCUMP;

                html = `
                    <table class="table table-sm">
                        <tr>
                            <td>Stock avant:</td>
                            <td class="text-end"><strong>${stock.toFixed(2)} ${unite}</strong></td>
                        </tr>
                        <tr>
                            <td>CUMP avant:</td>
                            <td class="text-end">${cump.toFixed(2)} FC</td>
                        </tr>
                        <tr>
                            <td>Valeur stock avant:</td>
                            <td class="text-end">${valeurStock.toFixed(2)} FC</td>
                        </tr>
                        <tr class="table-info">
                            <td><strong>Quantité entrée:</strong></td>
                            <td class="text-end"><strong>${quantite.toFixed(2)} ${unite}</strong></td>
                        </tr>
                        <tr class="table-info">
                            <td><strong>Prix unitaire:</strong></td>
                            <td class="text-end"><strong>${prix.toFixed(2)} FC</strong></td>
                        </tr>
                        <tr class="table-info">
                            <td><strong>Valeur entrée:</strong></td>
                            <td class="text-end"><strong>${valeurEntree.toFixed(2)} FC</strong></td>
                        </tr>
                        <tr class="table-success">
                            <td><strong>Nouveau stock:</strong></td>
                            <td class="text-end"><strong>${nouveauStock.toFixed(2)} ${unite}</strong></td>
                        </tr>
                        <tr class="table-success">
                            <td><strong>Nouveau CUMP:</strong></td>
                            <td class="text-end"><strong>${nouveauCUMP.toFixed(2)} FC</strong></td>
                        </tr>
                        <tr class="table-success">
                            <td><strong>Valeur nouveau stock:</strong></td>
                            <td class="text-end"><strong>${valeurNouveauStock.toFixed(2)} FC</strong></td>
                        </tr>
                    </table>
                `;
            } else {
                // Sortie
                if (quantite > stock) {
                    html = `
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            <strong>Stock insuffisant!</strong><br>
                            Stock disponible: ${stock.toFixed(2)} ${unite}
                        </div>
                    `;
                } else {
                    const valeurSortie = quantite * cump;
                    const nouveauStock = stock - quantite;
                    const valeurNouveauStock = nouveauStock * cump;

                    html = `
                        <table class="table table-sm">
                            <tr>
                                <td>Stock avant:</td>
                                <td class="text-end"><strong>${stock.toFixed(2)} ${unite}</strong></td>
                            </tr>
                            <tr>
                                <td>CUMP:</td>
                                <td class="text-end">${cump.toFixed(2)} FC</td>
                            </tr>
                            <tr class="table-danger">
                                <td><strong>Quantité sortie:</strong></td>
                                <td class="text-end"><strong>${quantite.toFixed(2)} ${unite}</strong></td>
                            </tr>
                            <tr class="table-danger">
                                <td><strong>Valeur sortie:</strong></td>
                                <td class="text-end"><strong>${valeurSortie.toFixed(2)} FC</strong></td>
                            </tr>
                            <tr class="table-warning">
                                <td><strong>Nouveau stock:</strong></td>
                                <td class="text-end"><strong>${nouveauStock.toFixed(2)} ${unite}</strong></td>
                            </tr>
                            <tr class="table-warning">
                                <td><strong>CUMP inchangé:</strong></td>
                                <td class="text-end"><strong>${cump.toFixed(2)} FC</strong></td>
                            </tr>
                            <tr class="table-warning">
                                <td><strong>Valeur nouveau stock:</strong></td>
                                <td class="text-end"><strong>${valeurNouveauStock.toFixed(2)} FC</strong></td>
                            </tr>
                        </table>
                    `;
                }
            }
            
            $("#cump_preview").html(html);
        }
    }

    // Événements
    quantiteInput.on("input", updatePreview);
    prixInput.on("input", updatePreview);
    typeEntree.on("change", function() {
        updatePreview();
    });
    typeSortie.on("change", function() {
        // Mettre le CUMP par défaut pour les sorties
        const option = pieceSelect.find(":selected");
        const cump = parseFloat(option.data("cump")) || 0;
        prixInput.val(cump.toFixed(2));
        updatePreview();
    });

    // Validation avant soumission
    $("#mouvementForm").on("submit", function(e) {
        const option = pieceSelect.find(":selected");
        const stock = parseFloat(option.data("stock")) || 0;
        const quantite = parseFloat(quantiteInput.val()) || 0;

        if (typeSortie.is(":checked") && quantite > stock) {
            e.preventDefault();
            alert("Stock insuffisant! Stock disponible: " + stock.toFixed(2));
            return false;
        }
    });
});
</script>
';

// Inclure le layout
$current_page = 'mouvements';
$breadcrumbs = [
    ['label' => 'Mouvements Stock', 'url' => BASE_URL . '/mouvementstock'],
    ['label' => 'Nouveau', 'url' => '']
];
include APP_PATH . '/Views/layouts/main.php';
?>
