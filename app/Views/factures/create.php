<?php
ob_start();
?>

<div class="container-fluid">
    <h1 class="h3 mb-4"><i class="bi bi-plus-circle me-2"></i>Nouvelle Facture</h1>

    <form method="POST" action="<?= BASE_URL ?>/facture/store">
        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
        
        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Client <span class="text-danger">*</span></label>
                                <select class="form-select" name="client_id" required>
                                    <option value="">Sélectionner...</option>
                                    <?php foreach ($clients as $client): ?>
                                        <option value="<?= $client['id'] ?>"><?= htmlspecialchars($client['nom']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Date Facture <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" name="date_facture" value="<?= date('Y-m-d') ?>" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Livraison (optionnel)</label>
                                <select class="form-select" name="livraison_id">
                                    <option value="">Nouvelle facture indépendante</option>
                                    <?php foreach ($livraisons_non_facturees as $liv): ?>
                                        <option value="<?= $liv['id'] ?>"><?= htmlspecialchars($liv['numero_livraison']) ?> - <?= number_format($liv['montant'], 0, ',', ' ') ?> FC</option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Date Échéance <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" name="date_echeance" value="<?= date('Y-m-d', strtotime('+30 days')) ?>" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Montant HT (FC) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="montant_ht" name="montant_ht" step="0.01" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Taux TVA (%)</label>
                                <input type="number" class="form-control" id="taux_tva" name="taux_tva" value="16" step="0.01">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Montant TTC (FC)</label>
                                <input type="number" class="form-control" id="montant_ttc" name="montant_ttc" step="0.01" readonly>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Notes</label>
                            <textarea class="form-control" name="notes" rows="3"></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header"><h5 class="mb-0">Calcul Automatique</h5></div>
                    <div class="card-body">
                        <table class="table table-sm">
                            <tr><td>Montant HT:</td><td class="text-end" id="preview_ht">0 FC</td></tr>
                            <tr><td>TVA (16%):</td><td class="text-end" id="preview_tva">0 FC</td></tr>
                            <tr class="fw-bold"><td>Total TTC:</td><td class="text-end" id="preview_ttc">0 FC</td></tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-3">
            <a href="<?= BASE_URL ?>/facture" class="btn btn-secondary">Annuler</a>
            <button type="submit" class="btn btn-primary">Créer Facture</button>
        </div>
    </form>
</div>

<?php
$content = ob_get_clean();
$additional_js = '<script>
$(document).ready(function() {
    function calculateTTC() {
        var ht = parseFloat($("#montant_ht").val()) || 0;
        var taux = parseFloat($("#taux_tva").val()) || 0;
        var tva = ht * (taux / 100);
        var ttc = ht + tva;
        
        $("#montant_ttc").val(ttc.toFixed(2));
        $("#preview_ht").text(ht.toLocaleString("fr-FR") + " FC");
        $("#preview_tva").text(tva.toLocaleString("fr-FR") + " FC");
        $("#preview_ttc").text(ttc.toLocaleString("fr-FR") + " FC");
    }
    
    $("#montant_ht, #taux_tva").on("input", calculateTTC);
});
</script>';
$current_page = 'factures';
$breadcrumbs = [
    ['label' => 'Factures', 'url' => BASE_URL . '/facture'],
    ['label' => 'Nouvelle', 'url' => '']
];
include APP_PATH . '/Views/layouts/main.php';
?>
