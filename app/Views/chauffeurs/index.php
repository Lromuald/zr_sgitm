<?php
require_once APP_PATH . '/Views/components/statut_badge.php';
ob_start();
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0"><i class="bi bi-person-badge me-2"></i>Gestion des Chauffeurs</h1>
                    <p class="text-muted mb-0">Liste complète avec validité des permis</p>
                </div>
                <?php if (in_array($_SESSION['role_id'] ?? 0, [ROLE_ADMIN])): ?>
                <a href="<?= BASE_URL ?>/chauffeur/create" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-2"></i>Nouveau Chauffeur
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Statistiques -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    <h2><?= $stats['total'] ?? 0 ?></h2>
                    <small class="text-muted">Total Chauffeurs</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-success">
                <div class="card-body text-center">
                    <h2 class="text-success"><?= $stats['permis_valides'] ?? 0 ?></h2>
                    <small class="text-muted">Permis Valides</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-danger">
                <div class="card-body text-center">
                    <h2 class="text-danger"><?= $stats['permis_expires'] ?? 0 ?></h2>
                    <small class="text-muted">Permis Expirés/Expire Bientôt</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Tableau -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-list me-2"></i>Liste des Chauffeurs</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="chauffeursTable" class="table table-hover">
                    <thead>
                        <tr>
                            <th>Photo</th>
                            <th>Nom Complet</th>
                            <th>Téléphone</th>
                            <th>N° Permis</th>
                            <th>Catégorie Permis</th>
                            <th>Validité Permis</th>
                            <th>Engin Affecté</th>
                            <th>Livraisons</th>
                            <th class="no-export">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($chauffeurs as $chauffeur): ?>
                            <tr>
                                <td>
                                    <?php if (!empty($chauffeur['photo_path'])): ?>
                                        <img src="<?= BASE_URL ?>/uploads/photos/<?= htmlspecialchars($chauffeur['photo_path']) ?>" 
                                             alt="Photo" class="rounded-circle" style="width: 50px; height: 50px; object-fit: cover;">
                                    <?php else: ?>
                                        <div class="bg-secondary text-white rounded-circle d-flex align-items-center justify-content-center" 
                                             style="width: 50px; height: 50px;">
                                            <i class="bi bi-person"></i>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td><strong><?= htmlspecialchars($chauffeur['nom']) ?> <?= htmlspecialchars($chauffeur['prenom']) ?></strong></td>
                                <td><?= htmlspecialchars($chauffeur['telephone'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($chauffeur['numero_permis'] ?? 'N/A') ?></td>
                                <td><span class="badge bg-info"><?= htmlspecialchars($chauffeur['categorie_permis'] ?? 'N/A') ?></span></td>
                                <td><?= badgeValiditeDocument($chauffeur['date_expiration_permis'] ?? '') ?></td>
                                <td>
                                    <?php if (!empty($chauffeur['engin_immatriculation'])): ?>
                                        <span class="badge bg-primary"><?= htmlspecialchars($chauffeur['engin_immatriculation']) ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">Aucun</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-success"><?= $chauffeur['nb_livraisons'] ?? 0 ?></span>
                                </td>
                                <td class="no-export">
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?= BASE_URL ?>/chauffeur/show/<?= $chauffeur['id'] ?>" 
                                           class="btn btn-outline-info" title="Détails">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <?php if (in_array($_SESSION['role_id'] ?? 0, [ROLE_ADMIN])): ?>
                                            <a href="<?= BASE_URL ?>/chauffeur/edit/<?= $chauffeur['id'] ?>" 
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
$additional_js = file_get_contents(APP_PATH . '/Views/components/datatable_config.php') . '
<script>
$(document).ready(function() {
    initDataTableFR("#chauffeursTable", { order: [[1, "asc"]] });
});
</script>';
$current_page = 'chauffeurs';
$breadcrumbs = [['label' => 'Chauffeurs', 'url' => BASE_URL . '/chauffeur']];
include APP_PATH . '/Views/layouts/main.php';
?>
