<?php
require_once APP_PATH . '/Views/components/statut_badge.php';
ob_start();
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0"><i class="bi bi-file-earmark-text me-2"></i>Documents Engins - Matrice de Conformité</h1>
                    <p class="text-muted mb-0">Suivi des 5 documents obligatoires par engin</p>
                </div>
                <?php if (in_array($_SESSION['role_id'] ?? 0, [ROLE_ADMIN])): ?>
                <a href="<?= BASE_URL ?>/documentengin/upload" class="btn btn-primary">
                    <i class="bi bi-upload me-2"></i>Télécharger un Document
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Alertes Globales -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-success">
                <div class="card-body text-center">
                    <h2 class="text-success"><?= $stats['conformes'] ?? 0 ?></h2>
                    <small>Engins Conformes</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-danger">
                <div class="card-body text-center">
                    <h2 class="text-danger"><?= $stats['expires'] ?? 0 ?></h2>
                    <small>Documents Expirés</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-warning">
                <div class="card-body text-center">
                    <h2 class="text-warning"><?= $stats['expire_bientot'] ?? 0 ?></h2>
                    <small>Expire sous 15j</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-secondary">
                <div class="card-body text-center">
                    <h2><?= $stats['manquants'] ?? 0 ?></h2>
                    <small>Documents Manquants</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Matrice de Conformité -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-table me-2"></i>Matrice de Conformité</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-sm">
                    <thead class="table-dark">
                        <tr>
                            <th>Engin</th>
                            <th>Carte Grise</th>
                            <th>Assurance</th>
                            <th>Carte Transport</th>
                            <th>Carte Stationnement</th>
                            <th>Visite Technique</th>
                            <th>Conformité Globale</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($engins as $engin): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($engin['immatriculation']) ?></strong></td>
                                <?php 
                                $docs = $engin['documents'] ?? [];
                                $types = ['carte_grise', 'assurance', 'carte_transport', 'carte_stationnement', 'visite_technique'];
                                foreach ($types as $type): 
                                    $doc = $docs[$type] ?? null;
                                ?>
                                    <td class="text-center">
                                        <?php if ($doc): ?>
                                            <a href="<?= BASE_URL ?>/documentengin/show/<?= $doc['id'] ?>" 
                                               title="<?= htmlspecialchars($doc['numero_document'] ?? '') ?>">
                                                <?= badgeValiditeDocument($doc['date_expiration'] ?? '') ?>
                                            </a>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Manquant</span>
                                        <?php endif; ?>
                                    </td>
                                <?php endforeach; ?>
                                <td class="text-center">
                                    <?= badgeConformiteEngin(
                                        $engin['conforme'] ?? false,
                                        $engin['documents_manquants'] ?? 0,
                                        $engin['documents_expires'] ?? 0
                                    ) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Légende -->
    <div class="card mt-3">
        <div class="card-body">
            <h6>Légende:</h6>
            <div class="row">
                <div class="col-md-3">
                    <span class="badge bg-danger me-2">Expiré</span> Document expiré
                </div>
                <div class="col-md-3">
                    <span class="badge bg-warning text-dark me-2">X jours</span> Expire bientôt
                </div>
                <div class="col-md-3">
                    <span class="badge bg-success me-2">Valide</span> Document valide
                </div>
                <div class="col-md-3">
                    <span class="badge bg-secondary me-2">Manquant</span> Document non uploadé
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
$current_page = 'documents';
$breadcrumbs = [['label' => 'Documents Engins', 'url' => BASE_URL . '/documentengin']];
include APP_PATH . '/Views/layouts/main.php';
?>
