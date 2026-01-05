<?php
ob_start();
?>

<div class="container-fluid">
    <h1 class="h3 mb-4"><i class="bi bi-plus-circle me-2"></i>Nouvelle Maintenance</h1>

    <form method="POST" action="<?= BASE_URL ?>/maintenance/store">
        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
        
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Engin <span class="text-danger">*</span></label>
                        <select class="form-select" name="engin_id" required>
                            <option value="">Sélectionner...</option>
                            <?php foreach ($engins as $engin): ?>
                                <option value="<?= $engin['id'] ?>"><?= htmlspecialchars($engin['immatriculation']) ?> - <?= htmlspecialchars($engin['marque']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Type <span class="text-danger">*</span></label>
                        <select class="form-select" name="type" required>
                            <option value="preventive">Préventive</option>
                            <option value="corrective">Corrective</option>
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Date Planifiée <span class="text-danger">*</span></label>
                        <input type="datetime-local" class="form-control" name="date_planifiee" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Kilométrage Intervention</label>
                        <input type="number" class="form-control" name="kilometrage_intervention">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Description <span class="text-danger">*</span></label>
                    <textarea class="form-control" name="description" rows="4" required></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Coût Main d'Oeuvre Estimé (FC)</label>
                    <input type="number" class="form-control" name="cout_main_oeuvre_estime" step="0.01">
                </div>
            </div>
        </div>

        <div class="mt-3">
            <a href="<?= BASE_URL ?>/maintenance" class="btn btn-secondary">Annuler</a>
            <button type="submit" class="btn btn-primary">Planifier</button>
        </div>
    </form>
</div>

<?php
$content = ob_get_clean();
$current_page = 'maintenances';
$breadcrumbs = [
    ['label' => 'Maintenances', 'url' => BASE_URL . '/maintenance'],
    ['label' => 'Nouvelle', 'url' => '']
];
include APP_PATH . '/Views/layouts/main.php';
?>
