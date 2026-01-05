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
                    <h1 class="h3 mb-0"><i class="bi bi-arrow-left-right me-2"></i>Historique des Mouvements de Stock</h1>
                    <p class="text-muted mb-0">Suivi complet des entrées et sorties</p>
                </div>
                <?php if (in_array($_SESSION['role_id'] ?? 0, [ROLE_ADMIN, ROLE_GESTIONNAIRE_STOCK])): ?>
                <a href="<?= BASE_URL ?>/mouvementstock/create" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-2"></i>Nouveau Mouvement
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Filtres -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-funnel me-2"></i>Filtres</h5>
        </div>
        <div class="card-body">
            <form method="GET" action="<?= BASE_URL ?>/mouvementstock" id="filterForm">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Type de Mouvement</label>
                        <select name="type" class="form-select" onchange="document.getElementById('filterForm').submit()">
                            <option value="">Tous</option>
                            <option value="entree" <?= ($filters['type'] ?? '') === 'entree' ? 'selected' : '' ?>>Entrées</option>
                            <option value="sortie" <?= ($filters['type'] ?? '') === 'sortie' ? 'selected' : '' ?>>Sorties</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Pièce</label>
                        <select name="piece_id" class="form-select" onchange="document.getElementById('filterForm').submit()">
                            <option value="">Toutes</option>
                            <?php foreach ($pieces as $piece): ?>
                                <option value="<?= $piece['id'] ?>" <?= ($filters['piece_id'] ?? '') == $piece['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($piece['reference']) ?> - <?= htmlspecialchars(substr($piece['description'], 0, 30)) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Date Début</label>
                        <input type="date" name="date_debut" class="form-control" 
                               value="<?= htmlspecialchars($filters['date_debut'] ?? '') ?>"
                               onchange="document.getElementById('filterForm').submit()">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Date Fin</label>
                        <input type="date" name="date_fin" class="form-control" 
                               value="<?= htmlspecialchars($filters['date_fin'] ?? '') ?>"
                               onchange="document.getElementById('filterForm').submit()">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">&nbsp;</label>
                        <a href="<?= BASE_URL ?>/mouvementstock" class="btn btn-secondary w-100">
                            <i class="bi bi-arrow-clockwise me-1"></i>Réinitialiser
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Statistiques -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-success">
                <div class="card-body text-center">
                    <div class="text-success mb-2" style="font-size: 2rem;"><i class="bi bi-arrow-down-circle"></i></div>
                    <h3 class="mb-0"><?= number_format($stats['total_entrees'] ?? 0, 0, ',', ' ') ?></h3>
                    <small class="text-muted">Entrées</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-danger">
                <div class="card-body text-center">
                    <div class="text-danger mb-2" style="font-size: 2rem;"><i class="bi bi-arrow-up-circle"></i></div>
                    <h3 class="mb-0"><?= number_format($stats['total_sorties'] ?? 0, 0, ',', ' ') ?></h3>
                    <small class="text-muted">Sorties</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-primary">
                <div class="card-body text-center">
                    <div class="text-primary mb-2" style="font-size: 2rem;"><i class="bi bi-cash-stack"></i></div>
                    <h3 class="mb-0"><?= number_format($stats['valeur_totale_entrees'] ?? 0, 0, ',', ' ') ?> FC</h3>
                    <small class="text-muted">Valeur Entrées</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-warning">
                <div class="card-body text-center">
                    <div class="text-warning mb-2" style="font-size: 2rem;"><i class="bi bi-cash"></i></div>
                    <h3 class="mb-0"><?= number_format($stats['valeur_totale_sorties'] ?? 0, 0, ',', ' ') ?> FC</h3>
                    <small class="text-muted">Valeur Sorties</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Tableau des mouvements -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-list me-2"></i>Liste des Mouvements</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="mouvementsTable" class="table table-hover">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Référence Pièce</th>
                            <th>Description</th>
                            <th>Quantité</th>
                            <th>Prix Unitaire</th>
                            <th>Valeur Totale</th>
                            <th>CUMP Après</th>
                            <th>Stock Après</th>
                            <th>Motif</th>
                            <th>Utilisateur</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($mouvements as $mvt): ?>
                            <tr>
                                <td><?= date('d/m/Y H:i', strtotime($mvt['date_mouvement'])) ?></td>
                                <td>
                                    <?php if ($mvt['type_mouvement'] === 'entree'): ?>
                                        <span class="badge bg-success"><i class="bi bi-arrow-down-circle me-1"></i>Entrée</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger"><i class="bi bi-arrow-up-circle me-1"></i>Sortie</span>
                                    <?php endif; ?>
                                </td>
                                <td><strong><?= htmlspecialchars($mvt['piece_reference']) ?></strong></td>
                                <td><?= htmlspecialchars(substr($mvt['piece_description'], 0, 40)) ?></td>
                                <td class="text-end">
                                    <strong><?= number_format($mvt['quantite'], 2, ',', ' ') ?></strong>
                                    <?= htmlspecialchars($mvt['unite_mesure'] ?? '') ?>
                                </td>
                                <td class="text-end"><?= number_format($mvt['prix_unitaire'], 2, ',', ' ') ?> FC</td>
                                <td class="text-end">
                                    <strong><?= number_format($mvt['quantite'] * $mvt['prix_unitaire'], 2, ',', ' ') ?> FC</strong>
                                </td>
                                <td class="text-end"><?= number_format($mvt['cump_apres'], 2, ',', ' ') ?> FC</td>
                                <td class="text-end">
                                    <span class="badge bg-info"><?= number_format($mvt['stock_apres'], 2, ',', ' ') ?></span>
                                </td>
                                <td>
                                    <small class="text-muted"><?= htmlspecialchars($mvt['motif'] ?? 'N/A') ?></small>
                                </td>
                                <td>
                                    <small><?= htmlspecialchars($mvt['utilisateur_nom'] ?? 'N/A') ?></small>
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

// Charger les composants JavaScript
$additional_js = '
    <!-- DataTables Config -->
    ' . file_get_contents(APP_PATH . '/Views/components/datatable_config.php') . '
    <script>
        $(document).ready(function() {
            initDataTableFR("#mouvementsTable", {
                order: [[0, "desc"]],
                pageLength: 50
            });
        });
    </script>
';

// Inclure le layout
$current_page = 'mouvements';
$breadcrumbs = [
    ['label' => 'Mouvements Stock', 'url' => BASE_URL . '/mouvementstock']
];
include APP_PATH . '/Views/layouts/main.php';
?>
