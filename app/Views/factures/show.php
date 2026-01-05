<?php
require_once APP_PATH . '/Views/components/statut_badge.php';
ob_start();
?>

<style>
@media print {
    .no-print { display: none !important; }
    .print-only { display: block !important; }
}
.print-only { display: none; }
</style>

<div class="container-fluid">
    <!-- En-tête non imprimable -->
    <div class="row mb-4 no-print">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h3 mb-0"><i class="bi bi-receipt me-2"></i>Facture <?= htmlspecialchars($facture['numero_facture']) ?></h1>
                <div>
                    <button onclick="window.print()" class="btn btn-outline-secondary">
                        <i class="bi bi-printer me-1"></i>Imprimer
                    </button>
                    <a href="<?= BASE_URL ?>/facture/pdf/<?= $facture['id'] ?>" class="btn btn-danger">
                        <i class="bi bi-file-pdf me-1"></i>PDF
                    </a>
                    <a href="<?= BASE_URL ?>/facture" class="btn btn-secondary">
                        <i class="bi bi-arrow-left me-1"></i>Retour
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Aperçu Facture (Format A4) -->
    <div class="card" style="max-width: 210mm; margin: 0 auto;">
        <div class="card-body p-5">
            <!-- En-tête -->
            <div class="row mb-4">
                <div class="col-6">
                    <h2 class="mb-3">ZR_SGITM</h2>
                    <p class="mb-0"><strong>Système de Gestion Intégrée</strong></p>
                    <p class="small">Kinshasa, RD Congo</p>
                </div>
                <div class="col-6 text-end">
                    <h3 class="mb-3">FACTURE</h3>
                    <p class="mb-0"><strong><?= htmlspecialchars($facture['numero_facture']) ?></strong></p>
                    <p class="small">Date: <?= date('d/m/Y', strtotime($facture['date_facture'])) ?></p>
                    <p class="small">Échéance: <?= date('d/m/Y', strtotime($facture['date_echeance'])) ?></p>
                </div>
            </div>

            <hr>

            <!-- Informations Client -->
            <div class="row mb-4">
                <div class="col-6">
                    <h6>Facturé à:</h6>
                    <p class="mb-0"><strong><?= htmlspecialchars($facture['client_nom']) ?></strong></p>
                    <?php if (!empty($facture['client_adresse'])): ?>
                        <p class="small mb-0"><?= htmlspecialchars($facture['client_adresse']) ?></p>
                    <?php endif; ?>
                    <?php if (!empty($facture['client_telephone'])): ?>
                        <p class="small mb-0">Tél: <?= htmlspecialchars($facture['client_telephone']) ?></p>
                    <?php endif; ?>
                </div>
                <div class="col-6 text-end">
                    <?= badgeStatutPaiement($facture['statut_paiement']) ?>
                </div>
            </div>

            <!-- Détails -->
            <table class="table">
                <thead class="table-light">
                    <tr>
                        <th>Description</th>
                        <th class="text-end">Montant</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($facture['livraison_numero'])): ?>
                        <tr>
                            <td>Livraison <?= htmlspecialchars($facture['livraison_numero']) ?></td>
                            <td class="text-end"><?= number_format($facture['montant_ht'], 0, ',', ' ') ?> FC</td>
                        </tr>
                    <?php else: ?>
                        <tr>
                            <td>Prestation de service</td>
                            <td class="text-end"><?= number_format($facture['montant_ht'], 0, ',', ' ') ?> FC</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <!-- Totaux -->
            <div class="row">
                <div class="col-6"></div>
                <div class="col-6">
                    <table class="table">
                        <tr>
                            <td>Montant HT:</td>
                            <td class="text-end"><?= number_format($facture['montant_ht'], 0, ',', ' ') ?> FC</td>
                        </tr>
                        <tr>
                            <td>TVA (16%):</td>
                            <td class="text-end"><?= number_format($facture['montant_tva'], 0, ',', ' ') ?> FC</td>
                        </tr>
                        <tr class="fw-bold">
                            <td>Total TTC:</td>
                            <td class="text-end"><?= number_format($facture['montant_ttc'], 0, ',', ' ') ?> FC</td>
                        </tr>
                    </table>
                </div>
            </div>

            <?php if (!empty($facture['notes'])): ?>
            <div class="mt-4">
                <h6>Notes:</h6>
                <p class="small"><?= nl2br(htmlspecialchars($facture['notes'])) ?></p>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Gestion Paiements (Non imprimable) -->
    <div class="card mt-4 no-print" style="max-width: 210mm; margin: 20px auto 0;">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-cash me-2"></i>Gestion des Paiements</h5>
        </div>
        <div class="card-body">
            <?php if ($facture['statut_paiement'] !== 'payee'): ?>
            <form method="POST" action="<?= BASE_URL ?>/facture/ajouterPaiement/<?= $facture['id'] ?>" class="row g-3 mb-3">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                <div class="col-md-4">
                    <input type="number" class="form-control" name="montant" placeholder="Montant" step="0.01" required>
                </div>
                <div class="col-md-4">
                    <input type="date" class="form-control" name="date_paiement" value="<?= date('Y-m-d') ?>" required>
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-success w-100">
                        <i class="bi bi-plus-circle me-1"></i>Ajouter Paiement
                    </button>
                </div>
            </form>
            <?php endif; ?>

            <h6>Historique des Paiements</h6>
            <?php if (!empty($paiements)): ?>
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Montant</th>
                        <th>Mode</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($paiements as $pmt): ?>
                        <tr>
                            <td><?= date('d/m/Y', strtotime($pmt['date_paiement'])) ?></td>
                            <td><?= number_format($pmt['montant'], 0, ',', ' ') ?> FC</td>
                            <td><?= htmlspecialchars($pmt['mode_paiement'] ?? 'N/A') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <p class="text-muted">Aucun paiement enregistré</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
$current_page = 'factures';
$breadcrumbs = [
    ['label' => 'Factures', 'url' => BASE_URL . '/facture'],
    ['label' => $facture['numero_facture'], 'url' => '']
];
include APP_PATH . '/Views/layouts/main.php';
?>
