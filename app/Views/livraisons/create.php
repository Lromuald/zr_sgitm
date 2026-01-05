<?php
ob_start();
?>

<div class="container-fluid">
    <h1 class="h3 mb-4"><i class="bi bi-plus-circle me-2"></i>Nouvelle Livraison</h1>

    <form method="POST" action="<?= BASE_URL ?>/livraison/store" id="livraisonForm">
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
                                <small class="text-muted">
                                    <a href="#" id="quickClient">+ Créer un client rapidement</a>
                                </small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Date Livraison <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" name="date_livraison" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Engin <span class="text-danger">*</span></label>
                                <select class="form-select" name="engin_id" required>
                                    <option value="">Sélectionner...</option>
                                    <?php foreach ($engins_disponibles as $engin): ?>
                                        <option value="<?= $engin['id'] ?>"><?= htmlspecialchars($engin['immatriculation']) ?> - <?= htmlspecialchars($engin['type']) ?></option>
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
                        <div class="mb-3">
                            <label class="form-label">Destination <span class="text-danger">*</span></label>
                            <textarea class="form-control" name="destination" rows="2" required></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Montant (FC) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" name="montant" step="0.01" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Statut</label>
                                <select class="form-select" name="statut">
                                    <option value="planifiee">Planifiée</option>
                                    <option value="en_cours">En Cours</option>
                                    <option value="livree">Livrée</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header"><h5 class="mb-0">Informations</h5></div>
                    <div class="card-body">
                        <div class="alert alert-info" id="validationStatus">
                            Remplissez le formulaire pour validation
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-3">
            <a href="<?= BASE_URL ?>/livraison" class="btn btn-secondary">Annuler</a>
            <button type="submit" class="btn btn-primary">Créer Livraison</button>
        </div>
    </form>
</div>

<?php
$content = ob_get_clean();
$additional_js = '<script>
$(document).ready(function() {
    $("#quickClient").on("click", function(e) {
        e.preventDefault();
        alert("Fonction création rapide client à implémenter");
    });
});
</script>';
$current_page = 'livraisons';
$breadcrumbs = [
    ['label' => 'Livraisons', 'url' => BASE_URL . '/livraison'],
    ['label' => 'Nouvelle', 'url' => '']
];
include APP_PATH . '/Views/layouts/main.php';
?>
