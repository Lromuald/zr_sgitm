<?php
// Préparer le contenu
ob_start();
?>

<div class="container-fluid">
    <!-- Page Title -->
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 mb-0">Tableau de bord</h1>
            <p class="text-muted">Vue d'ensemble de votre système de gestion</p>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row g-3 mb-4">
        <!-- Total Engins -->
        <div class="col-xl-3 col-md-6">
            <div class="stat-card">
                <div class="stat-card-icon bg-primary-gradient text-white">
                    <i class="bi bi-truck"></i>
                </div>
                <div class="stat-card-value"><?php echo $stats['total_engins'] ?? 0; ?></div>
                <div class="stat-card-label">Total Engins</div>
            </div>
        </div>

        <!-- Engins Disponibles -->
        <div class="col-xl-3 col-md-6">
            <div class="stat-card">
                <div class="stat-card-icon bg-success-gradient text-white">
                    <i class="bi bi-check-circle"></i>
                </div>
                <div class="stat-card-value"><?php echo $stats['engins_disponibles'] ?? 0; ?></div>
                <div class="stat-card-label">Disponibles</div>
            </div>
        </div>

        <!-- En Maintenance -->
        <div class="col-xl-3 col-md-6">
            <div class="stat-card">
                <div class="stat-card-icon bg-warning-gradient text-white">
                    <i class="bi bi-tools"></i>
                </div>
                <div class="stat-card-value"><?php echo $stats['engins_maintenance'] ?? 0; ?></div>
                <div class="stat-card-label">En Maintenance</div>
            </div>
        </div>

        <!-- Hors Service -->
        <div class="col-xl-3 col-md-6">
            <div class="stat-card">
                <div class="stat-card-icon bg-danger-gradient text-white">
                    <i class="bi bi-x-circle"></i>
                </div>
                <div class="stat-card-value"><?php echo $stats['engins_hors_service'] ?? 0; ?></div>
                <div class="stat-card-label">Hors Service</div>
            </div>
        </div>
    </div>

    <!-- Secondary Statistics -->
    <div class="row g-3 mb-4">
        <!-- Livraisons du jour -->
        <div class="col-xl-3 col-md-6">
            <div class="stat-card">
                <div class="d-flex align-items-center">
                    <div class="stat-card-icon bg-primary-gradient text-white me-3" style="width: 50px; height: 50px; font-size: 24px;">
                        <i class="bi bi-box-seam"></i>
                    </div>
                    <div>
                        <div class="stat-card-value" style="font-size: 28px;"><?php echo $stats['livraisons_jour'] ?? 0; ?></div>
                        <div class="stat-card-label">Livraisons du jour</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stock Critique -->
        <div class="col-xl-3 col-md-6">
            <div class="stat-card">
                <div class="d-flex align-items-center">
                    <div class="stat-card-icon bg-warning-gradient text-white me-3" style="width: 50px; height: 50px; font-size: 24px;">
                        <i class="bi bi-exclamation-triangle"></i>
                    </div>
                    <div>
                        <div class="stat-card-value" style="font-size: 28px;"><?php echo $stats['stock_critique'] ?? 0; ?></div>
                        <div class="stat-card-label">Stock Critique</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Documents Expirés -->
        <div class="col-xl-3 col-md-6">
            <div class="stat-card">
                <div class="d-flex align-items-center">
                    <div class="stat-card-icon bg-danger-gradient text-white me-3" style="width: 50px; height: 50px; font-size: 24px;">
                        <i class="bi bi-file-earmark-x"></i>
                    </div>
                    <div>
                        <div class="stat-card-value" style="font-size: 28px;"><?php echo $stats['documents_expires'] ?? 0; ?></div>
                        <div class="stat-card-label">Documents Expirés</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- CA du Mois -->
        <div class="col-xl-3 col-md-6">
            <div class="stat-card">
                <div class="d-flex align-items-center">
                    <div class="stat-card-icon bg-success-gradient text-white me-3" style="width: 50px; height: 50px; font-size: 24px;">
                        <i class="bi bi-currency-dollar"></i>
                    </div>
                    <div>
                        <div class="stat-card-value" style="font-size: 24px;"><?php echo number_format($stats['ca_mois'] ?? 0, 0, ',', ' '); ?>$</div>
                        <div class="stat-card-label">CA du Mois</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Alerts Section -->
    <div class="row g-3">
        <!-- Documents à Renouveler -->
        <?php if (!empty($alerts['documents'])): ?>
        <div class="col-xl-6 col-lg-12">
            <div class="card">
                <div class="card-header bg-danger text-white">
                    <h5 class="card-title mb-0"><i class="bi bi-file-earmark-x me-2"></i>Documents Critiques</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr>
                                    <th>Engin</th>
                                    <th>Document</th>
                                    <th>Expiration</th>
                                    <th>Jours</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_slice($alerts['documents'], 0, 5) as $doc): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($doc['immatriculation']); ?></strong></td>
                                    <td><?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $doc['type_document']))); ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($doc['date_expiration'])); ?></td>
                                    <td>
                                        <?php if ($doc['jours_restants'] < 0): ?>
                                            <span class="badge bg-danger">Expiré</span>
                                        <?php elseif ($doc['jours_restants'] <= 3): ?>
                                            <span class="badge bg-danger"><?php echo $doc['jours_restants']; ?>j</span>
                                        <?php elseif ($doc['jours_restants'] <= 15): ?>
                                            <span class="badge bg-warning"><?php echo $doc['jours_restants']; ?>j</span>
                                        <?php else: ?>
                                            <span class="badge bg-info"><?php echo $doc['jours_restants']; ?>j</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php if (count($alerts['documents']) > 5): ?>
                    <div class="text-center mt-2">
                        <a href="<?php echo BASE_URL; ?>/documents" class="btn btn-sm btn-outline-danger">Voir tout (<?php echo count($alerts['documents']); ?>)</a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Stock Critique -->
        <?php if (!empty($alerts['stock'])): ?>
        <div class="col-xl-6 col-lg-12">
            <div class="card">
                <div class="card-header bg-warning text-dark">
                    <h5 class="card-title mb-0"><i class="bi bi-boxes me-2"></i>Stock Bas</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr>
                                    <th>Référence</th>
                                    <th>Description</th>
                                    <th>Stock</th>
                                    <th>Seuil</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_slice($alerts['stock'], 0, 5) as $piece): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($piece['reference']); ?></strong></td>
                                    <td><?php echo htmlspecialchars(substr($piece['description'], 0, 30)); ?></td>
                                    <td><span class="badge bg-danger"><?php echo number_format($piece['stock_actuel'], 2); ?></span></td>
                                    <td><?php echo number_format($piece['seuil_alerte'], 2); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php if (count($alerts['stock']) > 5): ?>
                    <div class="text-center mt-2">
                        <a href="<?php echo BASE_URL; ?>/stocks" class="btn btn-sm btn-outline-warning">Voir tout (<?php echo count($alerts['stock']); ?>)</a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Livraisons du Jour -->
        <?php if (!empty($alerts['livraisons'])): ?>
        <div class="col-xl-6 col-lg-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0"><i class="bi bi-box-seam me-2"></i>Livraisons du Jour</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr>
                                    <th>Client</th>
                                    <th>Engin</th>
                                    <th>Destination</th>
                                    <th>Statut</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_slice($alerts['livraisons'], 0, 5) as $livraison): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($livraison['client']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($livraison['immatriculation']); ?></td>
                                    <td><?php echo htmlspecialchars(substr($livraison['destination'], 0, 30)); ?></td>
                                    <td>
                                        <?php if ($livraison['statut'] === 'en_cours'): ?>
                                            <span class="badge bg-warning">En cours</span>
                                        <?php else: ?>
                                            <span class="badge bg-info">Planifiée</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php if (count($alerts['livraisons']) > 5): ?>
                    <div class="text-center mt-2">
                        <a href="<?php echo BASE_URL; ?>/livraisons" class="btn btn-sm btn-outline-primary">Voir tout (<?php echo count($alerts['livraisons']); ?>)</a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Maintenances À Venir -->
        <?php if (!empty($alerts['maintenances'])): ?>
        <div class="col-xl-6 col-lg-12">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="card-title mb-0"><i class="bi bi-tools me-2"></i>Maintenances Planifiées (7j)</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr>
                                    <th>Engin</th>
                                    <th>Type</th>
                                    <th>Description</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_slice($alerts['maintenances'], 0, 5) as $maintenance): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($maintenance['immatriculation']); ?></strong></td>
                                    <td>
                                        <?php if ($maintenance['type'] === 'preventive'): ?>
                                            <span class="badge bg-success">Préventive</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Corrective</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars(substr($maintenance['description'], 0, 30)); ?></td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($maintenance['date_planifiee'])); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php if (count($alerts['maintenances']) > 5): ?>
                    <div class="text-center mt-2">
                        <a href="<?php echo BASE_URL; ?>/maintenances" class="btn btn-sm btn-outline-info">Voir tout (<?php echo count($alerts['maintenances']); ?>)</a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Empty State if No Alerts -->
    <?php if (empty($alerts['documents']) && empty($alerts['stock']) && empty($alerts['livraisons']) && empty($alerts['maintenances'])): ?>
    <div class="row mt-4">
        <div class="col-12">
            <div class="card text-center py-5">
                <div class="card-body">
                    <i class="bi bi-check-circle-fill text-success" style="font-size: 64px;"></i>
                    <h4 class="mt-3">Tout est en ordre !</h4>
                    <p class="text-muted">Aucune alerte nécessitant votre attention.</p>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();

// Inclure le layout
$current_page = 'dashboard';
include APP_PATH . '/Views/layouts/main.php';
?>
