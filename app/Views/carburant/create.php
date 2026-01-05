<?php
ob_start();
?>

<div class="container-fluid">
    <h1 class="h3 mb-4"><i class="bi bi-plus-circle me-2"></i>Nouveau Plein de Carburant</h1>

    <form method="POST" action="<?= BASE_URL ?>/carburant/store">
        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
        
        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Engin <span class="text-danger">*</span></label>
                                <select class="form-select" id="engin_id" name="engin_id" required>
                                    <option value="">Sélectionner...</option>
                                    <?php foreach ($engins as $engin): ?>
                                        <option value="<?= $engin['id'] ?>" data-dernier-km="<?= $engin['dernier_kilometrage'] ?? 0 ?>">
                                            <?= htmlspecialchars($engin['immatriculation']) ?> - <?= htmlspecialchars($engin['type']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Chauffeur</label>
                                <select class="form-select" name="chauffeur_id">
                                    <option value="">Sélectionner...</option>
                                    <?php foreach ($chauffeurs as $chauffeur): ?>
                                        <option value="<?= $chauffeur['id'] ?>"><?= htmlspecialchars($chauffeur['nom']) ?> <?= htmlspecialchars($chauffeur['prenom']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Date et Heure <span class="text-danger">*</span></label>
                                <input type="datetime-local" class="form-control" name="date_plein" value="<?= date('Y-m-d\TH:i') ?>" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Quantité (L) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="quantite" name="quantite_litres" step="0.01" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Prix Unitaire (FC/L) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="prix" name="prix_unitaire" step="0.01" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Kilométrage <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="kilometrage" name="kilometrage" required>
                                <small class="text-muted" id="dernier_km_text"></small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Station Service</label>
                                <input type="text" class="form-control" name="station_service">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Notes</label>
                            <textarea class="form-control" name="notes" rows="2"></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header bg-info text-white"><h5 class="mb-0">Aperçu Calcul</h5></div>
                    <div class="card-body">
                        <div id="preview">
                            <p class="text-muted">Remplissez les champs pour voir l'aperçu</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-3">
            <a href="<?= BASE_URL ?>/carburant" class="btn btn-secondary">Annuler</a>
            <button type="submit" class="btn btn-primary">Enregistrer</button>
        </div>
    </form>
</div>

<?php
$content = ob_get_clean();
$additional_js = '<script>
$(document).ready(function() {
    $("#engin_id").on("change", function() {
        var dernierKm = $(this).find(":selected").data("dernier-km");
        $("#dernier_km_text").text("Dernier kilométrage: " + dernierKm + " km");
        updatePreview();
    });
    
    function updatePreview() {
        var quantite = parseFloat($("#quantite").val()) || 0;
        var prix = parseFloat($("#prix").val()) || 0;
        var km = parseFloat($("#kilometrage").val()) || 0;
        var dernierKm = parseFloat($("#engin_id").find(":selected").data("dernier-km")) || 0;
        
        if (quantite > 0 && prix > 0 && km > dernierKm) {
            var cout = quantite * prix;
            var distance = km - dernierKm;
            var conso = distance > 0 ? (quantite / distance) * 100 : 0;
            
            var html = "<table class=\"table table-sm\">";
            html += "<tr><td>Coût total:</td><td class=\"text-end\"><strong>" + cout.toLocaleString("fr-FR") + " FC</strong></td></tr>";
            html += "<tr><td>Distance:</td><td class=\"text-end\">" + distance + " km</td></tr>";
            html += "<tr><td>Consommation:</td><td class=\"text-end\"><strong>" + conso.toFixed(2) + " L/100km</strong></td></tr>";
            html += "</table>";
            
            $("#preview").html(html);
        }
    }
    
    $("#quantite, #prix, #kilometrage").on("input", updatePreview);
});
</script>';
$current_page = 'carburant';
$breadcrumbs = [
    ['label' => 'Carburant', 'url' => BASE_URL . '/carburant'],
    ['label' => 'Nouveau Plein', 'url' => '']
];
include APP_PATH . '/Views/layouts/main.php';
?>
