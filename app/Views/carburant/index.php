<?php
ob_start();
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0"><i class="bi bi-fuel-pump me-2"></i>Gestion du Carburant</h1>
                    <p class="text-muted mb-0">Suivi des consommations et pleins</p>
                </div>
                <div>
                    <a href="<?= BASE_URL ?>/carburant/alertes" class="btn btn-warning me-2">
                        <i class="bi bi-exclamation-triangle me-1"></i>Alertes
                    </a>
                    <a href="<?= BASE_URL ?>/carburant/rapport_mensuel" class="btn btn-info me-2">
                        <i class="bi bi-file-earmark-bar-graph me-1"></i>Rapport
                    </a>
                    <?php if (in_array($_SESSION['role_id'] ?? 0, [ROLE_ADMIN, ROLE_GESTIONNAIRE_STOCK])): ?>
                    <a href="<?= BASE_URL ?>/carburant/create" class="btn btn-primary">
                        <i class="bi bi-plus-circle me-1"></i>Nouveau Plein
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistiques -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <h2><?= number_format($stats['total_litres'] ?? 0, 2, ',', ' ') ?> L</h2>
                    <small>Total Mois</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <h2><?= number_format($stats['cout_total'] ?? 0, 0, ',', ' ') ?> FC</h2>
                    <small>Coût Total</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <h2><?= number_format($stats['conso_moyenne'] ?? 0, 2, ',', ' ') ?> L/100km</h2>
                    <small>Consommation Moyenne</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-danger">
                <div class="card-body text-center">
                    <h2 class="text-danger"><?= $stats['alertes_surconso'] ?? 0 ?></h2>
                    <small>Alertes Surconso</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Graphique Évolution 7 jours -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-graph-up me-2"></i>Évolution Consommation (7 derniers jours)</h5>
        </div>
        <div class="card-body">
            <canvas id="consoChart" height="80"></canvas>
        </div>
    </div>

    <!-- Tableau -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-list me-2"></i>Historique des Pleins</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="carburantTable" class="table table-hover">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Engin</th>
                            <th>Chauffeur</th>
                            <th>Quantité (L)</th>
                            <th>Prix/L</th>
                            <th>Coût Total</th>
                            <th>Kilométrage</th>
                            <th>Distance</th>
                            <th>Consommation</th>
                            <th>Station</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pleins as $plein): ?>
                            <tr class="<?= ($plein['alerte_surconsommation'] ?? false) ? 'table-warning' : '' ?>">
                                <td><?= date('d/m/Y H:i', strtotime($plein['date_plein'])) ?></td>
                                <td><strong><?= htmlspecialchars($plein['immatriculation']) ?></strong></td>
                                <td><?= htmlspecialchars($plein['chauffeur_nom'] ?? 'N/A') ?></td>
                                <td class="text-end"><strong><?= number_format($plein['quantite_litres'], 2, ',', ' ') ?> L</strong></td>
                                <td class="text-end"><?= number_format($plein['prix_unitaire'], 0, ',', ' ') ?> FC</td>
                                <td class="text-end"><?= number_format($plein['cout_total'], 0, ',', ' ') ?> FC</td>
                                <td class="text-end"><?= number_format($plein['kilometrage'], 0, ',', ' ') ?> km</td>
                                <td class="text-end">
                                    <?php if (isset($plein['distance_parcourue']) && $plein['distance_parcourue'] > 0): ?>
                                        <?= number_format($plein['distance_parcourue'], 0, ',', ' ') ?> km
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <?php if (isset($plein['consommation_calculee']) && $plein['consommation_calculee'] > 0): ?>
                                        <strong><?= number_format($plein['consommation_calculee'], 2, ',', ' ') ?> L/100km</strong>
                                        <?php if ($plein['alerte_surconsommation'] ?? false): ?>
                                            <i class="bi bi-exclamation-triangle text-warning" title="Surconsommation détectée"></i>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($plein['station_service'] ?? 'N/A') ?></td>
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
$additional_js = file_get_contents(APP_PATH . '/Views/components/datatable_config.php') . 
                 file_get_contents(APP_PATH . '/Views/components/chart_config.php') . '
<script>
$(document).ready(function() {
    initDataTableFR("#carburantTable", { order: [[0, "desc"]] });
    
    // Graphique évolution 7 jours
    var evolutionData = ' . json_encode($evolution_7j ?? []) . ';
    var labels = evolutionData.map(d => d.date);
    var data = evolutionData.map(d => d.total_litres);
    
    createLineChart("consoChart", labels, [{
        label: "Litres consommés",
        data: data
    }]);
});
</script>';
$current_page = 'carburant';
$breadcrumbs = [['label' => 'Carburant', 'url' => BASE_URL . '/carburant']];
include APP_PATH . '/Views/layouts/main.php';
?>
