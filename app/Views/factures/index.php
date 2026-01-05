<?php
require_once APP_PATH . '/Views/components/statut_badge.php';
ob_start();
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0"><i class="bi bi-receipt me-2"></i>Gestion des Factures</h1>
                    <p class="text-muted mb-0">Suivi des facturations et paiements</p>
                </div>
                <?php if (in_array($_SESSION['role_id'] ?? 0, [ROLE_ADMIN, ROLE_COMMERCIAL])): ?>
                <a href="<?= BASE_URL ?>/facture/create" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-2"></i>Nouvelle Facture
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Statistiques -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <h2><?= $stats['total_factures'] ?? 0 ?></h2>
                    <small>Total Factures</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-danger">
                <div class="card-body text-center">
                    <h2 class="text-danger"><?= $stats['impayees'] ?? 0 ?></h2>
                    <small>Impayées</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-warning">
                <div class="card-body text-center">
                    <h2 class="text-warning"><?= $stats['retard'] ?? 0 ?></h2>
                    <small>En Retard</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-success">
                <div class="card-body text-center">
                    <h2><?= number_format($stats['ca_mensuel'] ?? 0, 0, ',', ' ') ?> FC</h2>
                    <small>CA Mensuel</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Graphique CA mensuel -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-graph-up me-2"></i>Évolution du Chiffre d'Affaires</h5>
        </div>
        <div class="card-body">
            <canvas id="caChart" height="80"></canvas>
        </div>
    </div>

    <!-- Alertes Retards -->
    <?php if (!empty($factures_retard)): ?>
    <div class="alert alert-warning">
        <h6><i class="bi bi-exclamation-triangle me-2"></i>Factures en Retard (<?= count($factures_retard) ?>)</h6>
        <ul class="mb-0">
            <?php foreach (array_slice($factures_retard, 0, 5) as $fac): ?>
                <li><?= htmlspecialchars($fac['numero_facture']) ?> - <?= htmlspecialchars($fac['client_nom']) ?> - 
                    Retard: <?= $fac['jours_retard'] ?> jours</li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>

    <!-- Tableau -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-list me-2"></i>Liste des Factures</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="facturesTable" class="table table-hover">
                    <thead>
                        <tr>
                            <th>N° Facture</th>
                            <th>Date</th>
                            <th>Client</th>
                            <th>Montant HT</th>
                            <th>TVA</th>
                            <th>Montant TTC</th>
                            <th>Statut Paiement</th>
                            <th>Date Échéance</th>
                            <th class="no-export">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($factures as $fac): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($fac['numero_facture']) ?></strong></td>
                                <td><?= date('d/m/Y', strtotime($fac['date_facture'])) ?></td>
                                <td><?= htmlspecialchars($fac['client_nom']) ?></td>
                                <td class="text-end"><?= number_format($fac['montant_ht'], 0, ',', ' ') ?> FC</td>
                                <td class="text-end"><?= number_format($fac['montant_tva'], 0, ',', ' ') ?> FC</td>
                                <td class="text-end"><strong><?= number_format($fac['montant_ttc'], 0, ',', ' ') ?> FC</strong></td>
                                <td><?= badgeStatutPaiement($fac['statut_paiement']) ?></td>
                                <td><?= date('d/m/Y', strtotime($fac['date_echeance'])) ?></td>
                                <td class="no-export">
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?= BASE_URL ?>/facture/show/<?= $fac['id'] ?>" 
                                           class="btn btn-outline-info" title="Voir">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="<?= BASE_URL ?>/facture/pdf/<?= $fac['id'] ?>" 
                                           class="btn btn-outline-danger" title="PDF">
                                            <i class="bi bi-file-pdf"></i>
                                        </a>
                                    </div>
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
$additional_js = file_get_contents(APP_PATH . '/Views/components/datatable_config.php') . 
                 file_get_contents(APP_PATH . '/Views/components/chart_config.php') . '
<script>
$(document).ready(function() {
    initDataTableFR("#facturesTable", { order: [[1, "desc"]] });
    
    // Graphique CA mensuel
    var caData = ' . json_encode($ca_mensuel ?? []) . ';
    var labels = caData.map(d => d.mois);
    var data = caData.map(d => d.montant);
    
    createBarChart("caChart", labels, [{
        label: "CA Mensuel (FC)",
        data: data
    }], { valueFormat: "currency" });
});
</script>';
$current_page = 'factures';
$breadcrumbs = [['label' => 'Factures', 'url' => BASE_URL . '/facture']];
include APP_PATH . '/Views/layouts/main.php';
?>
