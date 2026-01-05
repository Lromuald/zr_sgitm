<?php
require_once APP_PATH . '/Views/components/statut_badge.php';
ob_start();
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h3 mb-0"><i class="bi bi-file-earmark me-2"></i>Document: <?= htmlspecialchars(ucfirst(str_replace('_', ' ', $document['type_document']))) ?></h1>
                <div>
                    <a href="<?= BASE_URL ?>/uploads/documents/<?= htmlspecialchars($document['chemin_fichier']) ?>" 
                       download class="btn btn-outline-primary">
                        <i class="bi bi-download me-1"></i>Télécharger
                    </a>
                    <a href="<?= BASE_URL ?>/documentengin" class="btn btn-secondary">
                        <i class="bi bi-arrow-left me-1"></i>Retour
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <!-- Visualiseur -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-eye me-2"></i>Aperçu du Document</h5>
                </div>
                <div class="card-body text-center">
                    <?php
                    $extension = strtolower(pathinfo($document['chemin_fichier'], PATHINFO_EXTENSION));
                    $filePath = BASE_URL . '/uploads/documents/' . htmlspecialchars($document['chemin_fichier']);
                    ?>
                    
                    <?php if ($extension === 'pdf'): ?>
                        <iframe src="<?= $filePath ?>" 
                                style="width: 100%; height: 600px; border: 1px solid #ddd;"></iframe>
                    <?php elseif (in_array($extension, ['jpg', 'jpeg', 'png', 'gif'])): ?>
                        <img src="<?= $filePath ?>" class="img-fluid" alt="Document">
                    <?php else: ?>
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>
                            Aperçu non disponible pour ce type de fichier. 
                            <a href="<?= $filePath ?>" download>Télécharger le document</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Informations -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>Informations</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tr>
                            <th>Engin:</th>
                            <td><?= htmlspecialchars($document['immatriculation']) ?></td>
                        </tr>
                        <tr>
                            <th>Type:</th>
                            <td><?= htmlspecialchars(ucfirst(str_replace('_', ' ', $document['type_document']))) ?></td>
                        </tr>
                        <tr>
                            <th>N° Document:</th>
                            <td><?= htmlspecialchars($document['numero_document'] ?? 'N/A') ?></td>
                        </tr>
                        <tr>
                            <th>Date Upload:</th>
                            <td><?= date('d/m/Y', strtotime($document['date_upload'])) ?></td>
                        </tr>
                        <tr>
                            <th>Date Expiration:</th>
                            <td>
                                <?php if (!empty($document['date_expiration'])): ?>
                                    <?= date('d/m/Y', strtotime($document['date_expiration'])) ?><br>
                                    <?= badgeValiditeDocument($document['date_expiration']) ?>
                                <?php else: ?>
                                    <span class="text-muted">N/A</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <th>Statut:</th>
                            <td><?= badgeValiditeDocument($document['date_expiration'] ?? '') ?></td>
                        </tr>
                    </table>

                    <?php if (!empty($document['notes'])): ?>
                    <hr>
                    <h6>Notes:</h6>
                    <p class="small"><?= nl2br(htmlspecialchars($document['notes'])) ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Actions -->
            <?php if (in_array($_SESSION['role_id'] ?? 0, [ROLE_ADMIN])): ?>
            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-gear me-2"></i>Actions</h5>
                </div>
                <div class="card-body">
                    <a href="<?= BASE_URL ?>/documentengin/edit/<?= $document['id'] ?>" class="btn btn-primary w-100 mb-2">
                        <i class="bi bi-pencil me-1"></i>Modifier Informations
                    </a>
                    <button type="button" class="btn btn-danger w-100" 
                            onclick="confirmDelete(<?= $document['id'] ?>)">
                        <i class="bi bi-trash me-1"></i>Supprimer
                    </button>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
$additional_js = file_get_contents(APP_PATH . '/Views/components/confirm_modal.php') . '
<script>
function confirmDelete(id) {
    showConfirmModal(
        "Confirmer la suppression",
        "Êtes-vous sûr de vouloir supprimer ce document ? Cette action est irréversible.",
        function() {
            window.location.href = "' . BASE_URL . '/documentengin/delete/" + id;
        }
    );
}
</script>';
$current_page = 'documents';
$breadcrumbs = [
    ['label' => 'Documents Engins', 'url' => BASE_URL . '/documentengin'],
    ['label' => 'Visualiser', 'url' => '']
];
include APP_PATH . '/Views/layouts/main.php';
?>
