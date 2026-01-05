<?php
ob_start();
?>

<div class="container-fluid">
    <h1 class="h3 mb-4"><i class="bi bi-plus-circle me-2"></i>Nouveau Client</h1>

    <form method="POST" action="<?= BASE_URL ?>/client/store">
        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
        
        <div class="card">
            <div class="card-header"><h5 class="mb-0">Informations Client</h5></div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Nom / Raison Sociale <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="nom" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Code Client</label>
                        <input type="text" class="form-control" name="code_client" placeholder="Généré automatiquement si vide">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Personne de Contact</label>
                        <input type="text" class="form-control" name="personne_contact">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Téléphone</label>
                        <input type="tel" class="form-control" name="telephone">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" name="email">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Numéro Contribuable</label>
                        <input type="text" class="form-control" name="numero_contribuable">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Adresse</label>
                    <textarea class="form-control" name="adresse" rows="2"></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Notes</label>
                    <textarea class="form-control" name="notes" rows="3"></textarea>
                </div>
            </div>
        </div>

        <div class="mt-3">
            <a href="<?= BASE_URL ?>/client" class="btn btn-secondary">Annuler</a>
            <button type="submit" class="btn btn-primary">Enregistrer</button>
        </div>
    </form>
</div>

<?php
$content = ob_get_clean();
$current_page = 'clients';
$breadcrumbs = [
    ['label' => 'Clients', 'url' => BASE_URL . '/client'],
    ['label' => 'Nouveau', 'url' => '']
];
include APP_PATH . '/Views/layouts/main.php';
?>
