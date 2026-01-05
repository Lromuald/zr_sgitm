<?php
require_once APP_PATH . '/Views/components/statut_badge.php';
ob_start();
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0"><i class="bi bi-box-seam me-2"></i>Gestion des Livraisons</h1>
                    <p class="text-muted mb-0">Suivi complet des livraisons</p>
                </div>
                <?php if (in_array($_SESSION['role_id'] ?? 0, [ROLE_ADMIN, ROLE_COMMERCIAL])): ?>
                <a href="<?= BASE_URL ?>/livraison/create" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-2"></i>Nouvelle Livraison
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Statistiques -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-info">
                <div class="card-body text-center">
                    <h2><?= $stats['planifiees'] ?? 0 ?></h2>
                    <small>Planifiées</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-warning">
                <div class="card-body text-center">
                    <h2><?= $stats['en_cours'] ?? 0 ?></h2>
                    <small>En Cours</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-success">
                <div class="card-body text-center">
                    <h2><?= $stats['livrees'] ?? 0 ?></h2>
                    <small>Livrées</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <h2><?= number_format($stats['montant_total'] ?? 0, 0, ',', ' ') ?> FC</h2>
                    <small>Chiffre d'Affaires</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Vues: Liste et Calendrier -->
    <ul class="nav nav-tabs mb-3">
        <li class="nav-item">
            <a class="nav-link active" data-bs-toggle="tab" href="#liste">
                <i class="bi bi-list me-1"></i>Liste
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="tab" href="#calendrier">
                <i class="bi bi-calendar me-1"></i>Calendrier Mensuel
            </a>
        </li>
    </ul>

    <div class="tab-content">
        <!-- Vue Liste -->
        <div class="tab-pane fade show active" id="liste">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="livraisonsTable" class="table table-hover">
                            <thead>
                                <tr>
                                    <th>N° Livraison</th>
                                    <th>Date</th>
                                    <th>Client</th>
                                    <th>Engin</th>
                                    <th>Chauffeur</th>
                                    <th>Destination</th>
                                    <th>Statut</th>
                                    <th>Montant</th>
                                    <th>Facture</th>
                                    <th class="no-export">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($livraisons as $liv): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($liv['numero_livraison']) ?></strong></td>
                                        <td><?= date('d/m/Y', strtotime($liv['date_livraison'])) ?></td>
                                        <td><?= htmlspecialchars($liv['client_nom']) ?></td>
                                        <td><?= htmlspecialchars($liv['immatriculation']) ?></td>
                                        <td><?= htmlspecialchars($liv['chauffeur_nom'] ?? 'N/A') ?></td>
                                        <td><?= htmlspecialchars(substr($liv['destination'], 0, 30)) ?></td>
                                        <td><?= badgeStatutLivraison($liv['statut']) ?></td>
                                        <td class="text-end"><?= number_format($liv['montant'] ?? 0, 0, ',', ' ') ?> FC</td>
                                        <td>
                                            <?php if (!empty($liv['facture_numero'])): ?>
                                                <a href="<?= BASE_URL ?>/facture/show/<?= $liv['facture_id'] ?>" class="badge bg-success">
                                                    <?= htmlspecialchars($liv['facture_numero']) ?>
                                                </a>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="no-export">
                                            <div class="btn-group btn-group-sm">
                                                <a href="<?= BASE_URL ?>/livraison/show/<?= $liv['id'] ?>" 
                                                   class="btn btn-outline-info" title="Détails">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <?php if (in_array($_SESSION['role_id'] ?? 0, [ROLE_ADMIN, ROLE_COMMERCIAL])): ?>
                                                    <a href="<?= BASE_URL ?>/livraison/edit/<?= $liv['id'] ?>" 
                                                       class="btn btn-outline-primary" title="Modifier">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                <?php endif; ?>
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

        <!-- Vue Calendrier Mensuel -->
        <div class="tab-pane fade" id="calendrier">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Calendrier des Livraisons - <?= date('F Y') ?></h5>
                        <div>
                            <button class="btn btn-sm btn-outline-secondary" onclick="changeMonth(-1)">
                                <i class="bi bi-chevron-left"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-secondary" onclick="changeMonth(1)">
                                <i class="bi bi-chevron-right"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div id="calendar-view" class="text-center">
                        <p class="text-muted">Calendrier des livraisons du mois</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
$additional_js = file_get_contents(APP_PATH . '/Views/components/datatable_config.php') . '
<script>
$(document).ready(function() {
    initDataTableFR("#livraisonsTable", { order: [[1, "desc"]] });
});

function changeMonth(delta) {
    alert("Changement de mois: " + delta);
}
</script>';
$current_page = 'livraisons';
$breadcrumbs = [['label' => 'Livraisons', 'url' => BASE_URL . '/livraison']];
include APP_PATH . '/Views/layouts/main.php';
?>
