<?php
ob_start();
?>

<div class="container-fluid">
    <h1 class="h3 mb-4"><i class="bi bi-plus-circle me-2"></i>Nouveau Chauffeur</h1>

    <form method="POST" action="<?= BASE_URL ?>/chauffeur/store" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
        
        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header"><h5 class="mb-0">Informations Personnelles</h5></div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nom <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="nom" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Prénom <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="prenom" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Téléphone</label>
                                <input type="tel" class="form-control" name="telephone">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" name="email">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Adresse</label>
                            <textarea class="form-control" name="adresse" rows="2"></textarea>
                        </div>
                    </div>
                </div>

                <div class="card mt-3">
                    <div class="card-header"><h5 class="mb-0">Permis de Conduire</h5></div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">N° Permis</label>
                                <input type="text" class="form-control" name="numero_permis">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Catégorie</label>
                                <select class="form-select" name="categorie_permis">
                                    <option value="">Sélectionner...</option>
                                    <option value="B">B - Véhicules légers</option>
                                    <option value="C">C - Poids lourds</option>
                                    <option value="CE">CE - Poids lourds + remorque</option>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Date Obtention</label>
                                <input type="date" class="form-control" name="date_obtention_permis">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Date Expiration</label>
                                <input type="date" class="form-control" name="date_expiration_permis">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header"><h5 class="mb-0">Photo</h5></div>
                    <div class="card-body text-center">
                        <div id="photoPreview" class="mb-3">
                            <i class="bi bi-person-circle text-muted" style="font-size: 8rem;"></i>
                        </div>
                        <input type="file" class="form-control" name="photo" accept="image/*">
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-3">
            <a href="<?= BASE_URL ?>/chauffeur" class="btn btn-secondary">Annuler</a>
            <button type="submit" class="btn btn-primary">Enregistrer</button>
        </div>
    </form>
</div>

<?php
$content = ob_get_clean();
$current_page = 'chauffeurs';
$breadcrumbs = [
    ['label' => 'Chauffeurs', 'url' => BASE_URL . '/chauffeur'],
    ['label' => 'Nouveau', 'url' => '']
];
include APP_PATH . '/Views/layouts/main.php';
?>
