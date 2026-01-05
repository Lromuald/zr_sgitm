<?php
ob_start();
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0"><i class="bi bi-people me-2"></i>Gestion des Clients</h1>
                    <p class="text-muted mb-0">Liste complète avec chiffre d'affaires</p>
                </div>
                <?php if (in_array($_SESSION['role_id'] ?? 0, [ROLE_ADMIN, ROLE_COMMERCIAL])): ?>
                <a href="<?= BASE_URL ?>/client/create" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-2"></i>Nouveau Client
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
                    <h2><?= $stats['total_clients'] ?? 0 ?></h2>
                    <small>Total Clients</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-success">
                <div class="card-body text-center">
                    <h2 class="text-success"><?= $stats['clients_actifs'] ?? 0 ?></h2>
                    <small>Clients Actifs</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-primary">
                <div class="card-body text-center">
                    <h2><?= number_format($stats['ca_total'] ?? 0, 0, ',', ' ') ?> FC</h2>
                    <small>CA Total</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Tableau -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-list me-2"></i>Liste des Clients</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="clientsTable" class="table table-hover">
                    <thead>
                        <tr>
                            <th>Code Client</th>
                            <th>Nom/Raison Sociale</th>
                            <th>Contact</th>
                            <th>Téléphone</th>
                            <th>Email</th>
                            <th>Adresse</th>
                            <th>Livraisons</th>
                            <th>CA Total</th>
                            <th class="no-export">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($clients as $client): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($client['code_client']) ?></strong></td>
                                <td><?= htmlspecialchars($client['nom']) ?></td>
                                <td><?= htmlspecialchars($client['personne_contact'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($client['telephone'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($client['email'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars(substr($client['adresse'] ?? 'N/A', 0, 30)) ?></td>
                                <td class="text-center">
                                    <span class="badge bg-primary"><?= $client['nb_livraisons'] ?? 0 ?></span>
                                </td>
                                <td class="text-end">
                                    <strong><?= number_format($client['ca_total'] ?? 0, 0, ',', ' ') ?> FC</strong>
                                </td>
                                <td class="no-export">
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?= BASE_URL ?>/client/show/<?= $client['id'] ?>" 
                                           class="btn btn-outline-info" title="Détails">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <?php if (in_array($_SESSION['role_id'] ?? 0, [ROLE_ADMIN, ROLE_COMMERCIAL])): ?>
                                            <a href="<?= BASE_URL ?>/client/edit/<?= $client['id'] ?>" 
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
    initDataTableFR("#clientsTable", { order: [[1, "asc"]] });
});
</script>';
$current_page = 'clients';
$breadcrumbs = [['label' => 'Clients', 'url' => BASE_URL . '/client']];
include APP_PATH . '/Views/layouts/main.php';
?>
