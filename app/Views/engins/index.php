<?php
// Charger les composants
require_once APP_PATH . '/Views/components/statut_badge.php';

// Préparer le contenu
ob_start();
?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0"><i class="bi bi-truck me-2"></i>Gestion du Parc d'Engins</h1>
                    <p class="text-muted mb-0">Liste complète avec conformité documentaire</p>
                </div>
                <?php if (in_array($_SESSION['role_id'] ?? 0, [ROLE_ADMIN])): ?>
                <a href="<?= BASE_URL ?>/engin/create" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-2"></i>Nouvel Engin
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Statistiques -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-left-primary">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-primary text-white rounded-circle p-3" style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center;">
                                <i class="bi bi-truck" style="font-size: 1.5rem;"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h3 class="mb-0"><?= $stats['total'] ?? 0 ?></h3>
                            <small class="text-muted">Total Engins</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-left-success">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-success text-white rounded-circle p-3" style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center;">
                                <i class="bi bi-check-circle" style="font-size: 1.5rem;"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h3 class="mb-0"><?= $stats['disponibles'] ?? 0 ?></h3>
                            <small class="text-muted">Disponibles</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-left-warning">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-warning text-dark rounded-circle p-3" style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center;">
                                <i class="bi bi-tools" style="font-size: 1.5rem;"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h3 class="mb-0"><?= $stats['en_maintenance'] ?? 0 ?></h3>
                            <small class="text-muted">En Maintenance</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-left-danger">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-danger text-white rounded-circle p-3" style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center;">
                                <i class="bi bi-exclamation-triangle" style="font-size: 1.5rem;"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h3 class="mb-0"><?= $stats['non_conformes'] ?? 0 ?></h3>
                            <small class="text-muted">Non Conformes</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tableau des engins -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-list me-2"></i>Liste des Engins</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="enginsTable" class="table table-hover">
                    <thead>
                        <tr>
                            <th>Photo</th>
                            <th>Immatriculation</th>
                            <th>Type</th>
                            <th>Marque/Modèle</th>
                            <th>Année</th>
                            <th>Statut</th>
                            <th>Conformité</th>
                            <th>Livraisons</th>
                            <th>Maintenances</th>
                            <th>Conso Moy.</th>
                            <th class="no-export">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($engins as $engin): ?>
                            <tr>
                                <td>
                                    <?php if (!empty($engin['photo_path'])): ?>
                                        <img src="<?= BASE_URL ?>/uploads/photos/<?= htmlspecialchars($engin['photo_path']) ?>" 
                                             alt="Photo" class="img-thumbnail" style="width: 60px; height: 60px; object-fit: cover;">
                                    <?php else: ?>
                                        <div class="bg-secondary text-white d-flex align-items-center justify-content-center" 
                                             style="width: 60px; height: 60px; border-radius: 4px;">
                                            <i class="bi bi-truck" style="font-size: 1.5rem;"></i>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td><strong><?= htmlspecialchars($engin['immatriculation']) ?></strong></td>
                                <td><span class="badge bg-info"><?= htmlspecialchars($engin['type']) ?></span></td>
                                <td><?= htmlspecialchars($engin['marque']) ?> <?= htmlspecialchars($engin['modele']) ?></td>
                                <td><?= htmlspecialchars($engin['annee_fabrication'] ?? 'N/A') ?></td>
                                <td><?= badgeStatutEngin($engin['statut']) ?></td>
                                <td>
                                    <?= badgeConformiteEngin(
                                        $engin['conforme'], 
                                        $engin['documents_manquants'] ?? 0, 
                                        $engin['documents_expires'] ?? 0
                                    ) ?>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-primary"><?= $engin['nb_livraisons'] ?? 0 ?></span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-warning text-dark"><?= $engin['nb_maintenances'] ?? 0 ?></span>
                                </td>
                                <td class="text-end">
                                    <?php if (isset($engin['consommation_moyenne']) && $engin['consommation_moyenne'] > 0): ?>
                                        <?= number_format($engin['consommation_moyenne'], 2, ',', ' ') ?> L/100km
                                    <?php else: ?>
                                        <span class="text-muted">N/A</span>
                                    <?php endif; ?>
                                </td>
                                <td class="no-export">
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?= BASE_URL ?>/engin/show/<?= $engin['id'] ?>" 
                                           class="btn btn-outline-info" title="Détails">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <?php if (in_array($_SESSION['role_id'] ?? 0, [ROLE_ADMIN])): ?>
                                            <a href="<?= BASE_URL ?>/engin/edit/<?= $engin['id'] ?>" 
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

<?php
$content = ob_get_clean();

// JavaScript spécifique
$additional_js = '
    <!-- DataTables Config -->
    ' . file_get_contents(APP_PATH . '/Views/components/datatable_config.php') . '
    <script>
        $(document).ready(function() {
            // Initialiser tooltips Bootstrap
            var tooltipTriggerList = [].slice.call(document.querySelectorAll(\'[data-bs-toggle="tooltip"]\'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });

            // Initialiser DataTable
            initDataTableFR("#enginsTable", {
                order: [[1, "asc"]],
                pageLength: 25
            });
        });
    </script>
';

// Inclure le layout
$current_page = 'engins';
$breadcrumbs = [
    ['label' => 'Engins', 'url' => BASE_URL . '/engin']
];
include APP_PATH . '/Views/layouts/main.php';
?>
