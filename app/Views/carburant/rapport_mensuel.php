<?php
require_once APP_PATH . '/Views/components/statut_badge.php';
ob_start();
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h3 mb-0"><i class="bi bi-file-earmark-bar-graph me-2"></i>Rapport Mensuel Carburant</h1>
                <div>
                    <button onclick="window.print()" class="btn btn-outline-secondary">
                        <i class="bi bi-printer me-1"></i>Imprimer
                    </button>
                    <a href="<?= BASE_URL ?>/carburant" class="btn btn-secondary">
                        <i class="bi bi-arrow-left me-1"></i>Retour
                    </a>
                </div>
            </div>
            <p class="text-muted"><?= date('F Y', strtotime($mois ?? 'now')) ?></p>
        </div>
    </div>

    <!-- Statistiques Globales -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <h3><?= number_format($stats['total_litres'] ?? 0, 2, ',', ' ') ?> L</h3>
                    <small>Total Carburant</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <h3><?= number_format($stats['cout_total'] ?? 0, 0, ',', ' ') ?> FC</h3>
                    <small>Coût Total</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <h3><?= number_format($stats['distance_totale'] ?? 0, 0, ',', ' ') ?> km</h3>
                    <small>Distance Totale</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <h3><?= number_format($stats['conso_moyenne'] ?? 0, 2, ',', ' ') ?> L/100km</h3>
                    <small>Consommation Moyenne</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Graphique 1: Évolution Journalière -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-graph-up me-2"></i>Évolution Journalière de la Consommation</h5>
        </div>
        <div class="card-body">
            <canvas id="evolutionChart" height="80"></canvas>
        </div>
    </div>

    <!-- Graphique 2: Répartition par Engin -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-pie-chart me-2"></i>Répartition par Engin</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <canvas id="repartitionChart"></canvas>
                </div>
                <div class="col-md-6">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Engin</th>
                                <th>Litres</th>
                                <th>Coût</th>
                                <th>%</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $total = array_sum(array_column($repartition ?? [], 'litres'));
                            foreach ($repartition ?? [] as $rep): 
                            ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($rep['immatriculation']) ?></strong></td>
                                    <td><?= number_format($rep['litres'], 2, ',', ' ') ?> L</td>
                                    <td><?= number_format($rep['cout'], 0, ',', ' ') ?> FC</td>
                                    <td><?= number_format(($rep['litres'] / $total) * 100, 1, ',', ' ') ?> %</td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Détails par Engin -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-list me-2"></i>Détails par Engin</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Engin</th>
                            <th>Nb Pleins</th>
                            <th>Total Litres</th>
                            <th>Coût Total</th>
                            <th>Distance</th>
                            <th>Consommation Moy.</th>
                            <th>Coût/km</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($details_engins ?? [] as $detail): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($detail['immatriculation']) ?></strong></td>
                                <td><?= $detail['nb_pleins'] ?></td>
                                <td><?= number_format($detail['total_litres'], 2, ',', ' ') ?> L</td>
                                <td><?= number_format($detail['cout_total'], 0, ',', ' ') ?> FC</td>
                                <td><?= number_format($detail['distance'], 0, ',', ' ') ?> km</td>
                                <td>
                                    <?php if ($detail['consommation_moyenne'] > 0): ?>
                                        <?= number_format($detail['consommation_moyenne'], 2, ',', ' ') ?> L/100km
                                    <?php else: ?>
                                        <span class="text-muted">N/A</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($detail['distance'] > 0): ?>
                                        <?= number_format($detail['cout_total'] / $detail['distance'], 2, ',', ' ') ?> FC/km
                                    <?php else: ?>
                                        <span class="text-muted">N/A</span>
                                    <?php endif; ?>
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
    // Graphique évolution
    var evolutionData = ' . json_encode($evolution_journaliere ?? []) . ';
    var labels = evolutionData.map(d => d.date);
    var data = evolutionData.map(d => d.litres);
    
    createLineChart("evolutionChart", labels, [{
        label: "Litres consommés",
        data: data,
        borderColor: "#ff5400",
        backgroundColor: "#ff540020"
    }]);
    
    // Graphique répartition
    var repartitionData = ' . json_encode($repartition ?? []) . ';
    var labelsRep = repartitionData.map(d => d.immatriculation);
    var dataRep = repartitionData.map(d => d.litres);
    
    createPieChart("repartitionChart", labelsRep, dataRep, "doughnut");
});
</script>';
$current_page = 'carburant';
$breadcrumbs = [
    ['label' => 'Carburant', 'url' => BASE_URL . '/carburant'],
    ['label' => 'Rapport Mensuel', 'url' => '']
];
include APP_PATH . '/Views/layouts/main.php';
?>
