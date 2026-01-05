<?php
require_once APP_PATH . '/Views/components/statut_badge.php';
ob_start();
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h3 mb-0"><i class="bi bi-exclamation-triangle me-2"></i>Alertes Surconsommation</h1>
                <a href="<?= BASE_URL ?>/carburant" class="btn btn-secondary">
                    <i class="bi bi-arrow-left me-1"></i>Retour
                </a>
            </div>
        </div>
    </div>

    <!-- Graphique Comparatif -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-graph-up me-2"></i>Comparaison Consommation Réelle vs Théorique</h5>
        </div>
        <div class="card-body">
            <canvas id="comparaisonChart" height="80"></canvas>
        </div>
    </div>

    <!-- Liste Alertes -->
    <div class="card">
        <div class="card-header bg-warning">
            <h5 class="mb-0"><i class="bi bi-list me-2"></i>Engins en Surconsommation</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Engin</th>
                            <th>Conso Théorique</th>
                            <th>Conso Réelle (Moy 30j)</th>
                            <th>Écart</th>
                            <th>Dépassement</th>
                            <th>Dernière Mesure</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($alertes as $alerte): ?>
                            <tr class="table-warning">
                                <td><strong><?= htmlspecialchars($alerte['immatriculation']) ?></strong></td>
                                <td><?= number_format($alerte['consommation_theorique'], 2, ',', ' ') ?> L/100km</td>
                                <td><strong><?= number_format($alerte['consommation_reelle'], 2, ',', ' ') ?> L/100km</strong></td>
                                <td>
                                    <span class="badge bg-warning text-dark">
                                        +<?= number_format($alerte['ecart'], 2, ',', ' ') ?> L/100km
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-danger">
                                        +<?= number_format($alerte['pourcentage_depassement'], 1, ',', ' ') ?> %
                                    </span>
                                </td>
                                <td><?= date('d/m/Y', strtotime($alerte['derniere_mesure'])) ?></td>
                                <td>
                                    <a href="<?= BASE_URL ?>/engin/show/<?= $alerte['engin_id'] ?>#carburant" 
                                       class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye me-1"></i>Détails
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
$additional_js = file_get_contents(APP_PATH . '/Views/components/chart_config.php') . '
<script>
$(document).ready(function() {
    var data = ' . json_encode($alertes ?? []) . ';
    var labels = data.map(d => d.immatriculation);
    var theorique = data.map(d => d.consommation_theorique);
    var reelle = data.map(d => d.consommation_reelle);
    
    createBarChart("comparaisonChart", labels, [
        { label: "Consommation Théorique", data: theorique, backgroundColor: "#28a745" },
        { label: "Consommation Réelle", data: reelle, backgroundColor: "#ffc107" }
    ]);
});
</script>';
$current_page = 'carburant';
$breadcrumbs = [
    ['label' => 'Carburant', 'url' => BASE_URL . '/carburant'],
    ['label' => 'Alertes', 'url' => '']
];
include APP_PATH . '/Views/layouts/main.php';
?>
