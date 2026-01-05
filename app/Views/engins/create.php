<?php
// Préparer le contenu
ob_start();
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 mb-0"><i class="bi bi-plus-circle me-2"></i>Nouvel Engin</h1>
            <p class="text-muted mb-0">Ajout d'un nouveau véhicule au parc</p>
        </div>
    </div>

    <form method="POST" action="<?= BASE_URL ?>/engin/store" enctype="multipart/form-data" id="enginForm">
        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
        
        <div class="row">
            <div class="col-lg-8">
                <!-- Informations principales -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>Informations Principales</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="immatriculation" class="form-label">Immatriculation <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="immatriculation" name="immatriculation" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="type" class="form-label">Type d'Engin <span class="text-danger">*</span></label>
                                <select class="form-select" id="type" name="type" required>
                                    <option value="">Sélectionner...</option>
                                    <option value="Camion">Camion</option>
                                    <option value="Camionnette">Camionnette</option>
                                    <option value="Véhicule léger">Véhicule léger</option>
                                    <option value="Remorque">Remorque</option>
                                    <option value="Engin spécial">Engin spécial</option>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="marque" class="form-label">Marque <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="marque" name="marque" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="modele" class="form-label">Modèle <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="modele" name="modele" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="annee_fabrication" class="form-label">Année</label>
                                <input type="number" class="form-control" id="annee_fabrication" name="annee_fabrication" 
                                       min="1980" max="<?= date('Y') + 1 ?>">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="numero_serie" class="form-label">Numéro de Série (VIN)</label>
                                <input type="text" class="form-control" id="numero_serie" name="numero_serie">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="couleur" class="form-label">Couleur</label>
                                <input type="text" class="form-control" id="couleur" name="couleur">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="capacite_charge" class="form-label">Capacité de Charge (kg)</label>
                                <input type="number" class="form-control" id="capacite_charge" name="capacite_charge" step="0.01">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="consommation_theorique" class="form-label">Consommation Théorique (L/100km)</label>
                                <input type="number" class="form-control" id="consommation_theorique" name="consommation_theorique" step="0.01">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="statut" class="form-label">Statut <span class="text-danger">*</span></label>
                            <select class="form-select" id="statut" name="statut" required>
                                <option value="disponible">Disponible</option>
                                <option value="en_mission">En Mission</option>
                                <option value="en_maintenance">En Maintenance</option>
                                <option value="hors_service">Hors Service</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Informations d'achat -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-cash me-2"></i>Informations d'Achat</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="date_acquisition" class="form-label">Date d'Acquisition</label>
                                <input type="date" class="form-control" id="date_acquisition" name="date_acquisition">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="prix_achat" class="form-label">Prix d'Achat (FC)</label>
                                <input type="number" class="form-control" id="prix_achat" name="prix_achat" step="0.01">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="notes" class="form-label">Notes / Observations</label>
                            <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <!-- Photo -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-image me-2"></i>Photo de l'Engin</h5>
                    </div>
                    <div class="card-body text-center">
                        <div id="photoPreview" class="mb-3">
                            <div class="bg-light border rounded d-flex align-items-center justify-content-center" 
                                 style="width: 100%; height: 250px;">
                                <i class="bi bi-truck text-muted" style="font-size: 4rem;"></i>
                            </div>
                        </div>
                        <input type="file" class="form-control" id="photo" name="photo" accept="image/*">
                        <small class="text-muted">JPG, PNG, max 5 Mo</small>
                    </div>
                </div>

                <!-- Aide -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-question-circle me-2"></i>Aide</h5>
                    </div>
                    <div class="card-body">
                        <p class="small mb-2"><strong>Champs obligatoires :</strong></p>
                        <ul class="small">
                            <li>Immatriculation unique</li>
                            <li>Type, marque et modèle</li>
                            <li>Statut initial</li>
                        </ul>
                        <p class="small mb-0"><strong>Après création :</strong></p>
                        <p class="small">Vous pourrez ajouter les documents obligatoires (carte grise, assurance, etc.)</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Boutons -->
        <div class="row mt-3">
            <div class="col-12">
                <div class="d-flex justify-content-between">
                    <a href="<?= BASE_URL ?>/engin" class="btn btn-secondary">
                        <i class="bi bi-x-circle me-1"></i>Annuler
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle me-1"></i>Enregistrer
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<?php
$content = ob_get_clean();

$additional_js = '
<script>
$(document).ready(function() {
    // Prévisualisation de la photo
    $("#photo").on("change", function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                $("#photoPreview").html(
                    \'<img src="\' + e.target.result + \'" class="img-fluid rounded" style="max-height: 250px;">\' 
                );
            };
            reader.readAsDataURL(file);
        }
    });
});
</script>
';

$current_page = 'engins';
$breadcrumbs = [
    ['label' => 'Engins', 'url' => BASE_URL . '/engin'],
    ['label' => 'Nouveau', 'url' => '']
];
include APP_PATH . '/Views/layouts/main.php';
?>
