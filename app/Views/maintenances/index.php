<?php
require_once APP_PATH . '/Views/components/statut_badge.php';
ob_start();
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0"><i class="bi bi-tools me-2"></i>Gestion des Maintenances</h1>
                    <p class="text-muted mb-0">Planning et historique des interventions</p>
                </div>
                <?php if (in_array($_SESSION['role_id'] ?? 0, [ROLE_ADMIN, ROLE_MAINTENANCE])): ?>
                <a href="<?= BASE_URL ?>/maintenance/create" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-2"></i>Nouvelle Maintenance
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
                    <h2 class="text-info"><?= $stats['planifiees'] ?? 0 ?></h2>
                    <small>Planifiées</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-warning">
                <div class="card-body text-center">
                    <h2 class="text-warning"><?= $stats['en_cours'] ?? 0 ?></h2>
                    <small>En Cours</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-success">
                <div class="card-body text-center">
                    <h2 class="text-success"><?= $stats['terminees'] ?? 0 ?></h2>
                    <small>Terminées</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <h2><?= number_format($stats['cout_total'] ?? 0, 0, ',', ' ') ?> FC</h2>
                    <small>Coût Total (Mois)</small>
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
                <i class="bi bi-calendar me-1"></i>Calendrier
            </a>
        </li>
    </ul>

    <div class="tab-content">
        <!-- Vue Liste -->
        <div class="tab-pane fade show active" id="liste">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="maintenancesTable" class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Date Planifiée</th>
                                    <th>Engin</th>
                                    <th>Type</th>
                                    <th>Description</th>
                                    <th>Statut</th>
                                    <th>Coût Total</th>
                                    <th>Durée</th>
                                    <th class="no-export">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($maintenances as $mnt): ?>
                                    <tr>
                                        <td><?= date('d/m/Y H:i', strtotime($mnt['date_planifiee'])) ?></td>
                                        <td><strong><?= htmlspecialchars($mnt['immatriculation']) ?></strong></td>
                                        <td><?= badgeTypeMaintenance($mnt['type']) ?></td>
                                        <td><?= htmlspecialchars(substr($mnt['description'], 0, 50)) ?></td>
                                        <td><?= badgeStatutMaintenance($mnt['statut']) ?></td>
                                        <td class="text-end"><?= number_format($mnt['cout_total'] ?? 0, 0, ',', ' ') ?> FC</td>
                                        <td>
                                            <?php if ($mnt['statut'] === 'terminee' && $mnt['date_debut'] && $mnt['date_fin']): ?>
                                                <?php
                                                $debut = new DateTime($mnt['date_debut']);
                                                $fin = new DateTime($mnt['date_fin']);
                                                $duree = $debut->diff($fin);
                                                echo $duree->days . 'j ' . $duree->h . 'h';
                                                ?>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="no-export">
                                            <div class="btn-group btn-group-sm">
                                                <a href="<?= BASE_URL ?>/maintenance/show/<?= $mnt['id'] ?>" 
                                                   class="btn btn-outline-info" title="Détails">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <?php if (in_array($_SESSION['role_id'] ?? 0, [ROLE_ADMIN, ROLE_MAINTENANCE])): ?>
                                                    <a href="<?= BASE_URL ?>/maintenance/edit/<?= $mnt['id'] ?>" 
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

        <!-- Vue Calendrier -->
        <div class="tab-pane fade" id="calendrier">
            <div class="card">
                <div class="card-body">
                    <div id="calendar" style="max-width: 1000px; margin: 0 auto;"></div>
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
    initDataTableFR("#maintenancesTable", { order: [[0, "desc"]] });
    
    // Calendrier simple avec les maintenances
    var events = ' . json_encode(array_map(function($m) {
        return [
            'title' => $m['immatriculation'] . ' - ' . $m['type'],
            'start' => $m['date_planifiee'],
            'backgroundColor' => $m['type'] === 'preventive' ? '#28a745' : '#dc3545'
        ];
    }, $maintenances ?? [])) . ';
    
    // Affichage basique des événements
    var calendarHtml = "<div class=\"alert alert-info\">Calendrier: " + events.length + " maintenance(s) planifiée(s)</div>";
    $("#calendar").html(calendarHtml);
});
</script>';
$current_page = 'maintenances';
$breadcrumbs = [['label' => 'Maintenances', 'url' => BASE_URL . '/maintenance']];
include APP_PATH . '/Views/layouts/main.php';
?>
