<?php
require_once APP_PATH . '/Views/components/statut_badge.php';
ob_start();
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h3 mb-0"><i class="bi bi-truck me-2"></i><?= htmlspecialchars($engin['immatriculation']) ?></h1>
                <div>
                    <a href="<?= BASE_URL ?>/engin" class="btn btn-secondary">
                        <i class="bi bi-arrow-left me-1"></i>Retour
                    </a>
                    <?php if (in_array($_SESSION['role_id'] ?? 0, [ROLE_ADMIN])): ?>
                    <a href="<?= BASE_URL ?>/engin/edit/<?= $engin['id'] ?>" class="btn btn-primary">
                        <i class="bi bi-pencil me-1"></i>Modifier
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Onglets -->
    <ul class="nav nav-tabs mb-3">
        <li class="nav-item">
            <a class="nav-link active" data-bs-toggle="tab" href="#infos"><i class="bi bi-info-circle me-1"></i>Informations</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="tab" href="#documents"><i class="bi bi-file-earmark me-1"></i>Documents</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="tab" href="#maintenances"><i class="bi bi-tools me-1"></i>Maintenances</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="tab" href="#livraisons"><i class="bi bi-box-seam me-1"></i>Livraisons</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="tab" href="#carburant"><i class="bi bi-fuel-pump me-1"></i>Consommation</a>
        </li>
    </ul>

    <div class="tab-content">
        <!-- Infos -->
        <div class="tab-pane fade show active" id="infos">
            <div class="row">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-body">
                            <table class="table">
                                <tr><th>Type:</th><td><?= htmlspecialchars($engin['type']) ?></td></tr>
                                <tr><th>Marque/Modèle:</th><td><?= htmlspecialchars($engin['marque']) ?> <?= htmlspecialchars($engin['modele']) ?></td></tr>
                                <tr><th>Année:</th><td><?= htmlspecialchars($engin['annee_fabrication'] ?? 'N/A') ?></td></tr>
                                <tr><th>Statut:</th><td><?= badgeStatutEngin($engin['statut']) ?></td></tr>
                                <tr><th>Conformité:</th><td><?= badgeConformiteEngin($engin['conforme'] ?? false) ?></td></tr>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body text-center">
                            <?php if (!empty($engin['photo_path'])): ?>
                                <img src="<?= BASE_URL ?>/uploads/photos/<?= htmlspecialchars($engin['photo_path']) ?>" class="img-fluid rounded">
                            <?php else: ?>
                                <i class="bi bi-truck text-muted" style="font-size: 5rem;"></i>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Documents -->
        <div class="tab-pane fade" id="documents">
            <div class="card">
                <div class="card-body">
                    <p>Documents de l'engin</p>
                </div>
            </div>
        </div>

        <!-- Maintenances -->
        <div class="tab-pane fade" id="maintenances">
            <div class="card">
                <div class="card-body">
                    <p>Historique des maintenances</p>
                </div>
            </div>
        </div>

        <!-- Livraisons -->
        <div class="tab-pane fade" id="livraisons">
            <div class="card">
                <div class="card-body">
                    <p>Historique des livraisons</p>
                </div>
            </div>
        </div>

        <!-- Carburant -->
        <div class="tab-pane fade" id="carburant">
            <div class="card">
                <div class="card-body">
                    <p>Consommation carburant</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
$current_page = 'engins';
$breadcrumbs = [
    ['label' => 'Engins', 'url' => BASE_URL . '/engin'],
    ['label' => $engin['immatriculation'], 'url' => '']
];
include APP_PATH . '/Views/layouts/main.php';
?>
