<?php
ob_start();
?>

<div class="container-fluid">
    <h1 class="h3 mb-4"><i class="bi bi-upload me-2"></i>Télécharger un Document Engin</h1>

    <form method="POST" action="<?= BASE_URL ?>/documentengin/store" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
        
        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Engin <span class="text-danger">*</span></label>
                                <select class="form-select" name="engin_id" required>
                                    <option value="">Sélectionner...</option>
                                    <?php foreach ($engins as $engin): ?>
                                        <option value="<?= $engin['id'] ?>"><?= htmlspecialchars($engin['immatriculation']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Type de Document <span class="text-danger">*</span></label>
                                <select class="form-select" name="type_document" required>
                                    <option value="">Sélectionner...</option>
                                    <option value="carte_grise">Carte Grise</option>
                                    <option value="assurance">Assurance</option>
                                    <option value="carte_transport">Carte de Transport</option>
                                    <option value="carte_stationnement">Carte de Stationnement</option>
                                    <option value="visite_technique">Visite Technique</option>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Numéro Document</label>
                                <input type="text" class="form-control" name="numero_document">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Date Expiration</label>
                                <input type="date" class="form-control" name="date_expiration">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Fichier Document <span class="text-danger">*</span></label>
                            <div id="dropZone" class="border border-2 border-dashed rounded p-5 text-center" style="cursor: pointer;">
                                <i class="bi bi-cloud-upload" style="font-size: 3rem;"></i>
                                <p class="mt-2">Glissez-déposez un fichier ici ou cliquez pour sélectionner</p>
                                <p class="small text-muted">PDF, JPG, PNG - Max 10 Mo</p>
                            </div>
                            <input type="file" class="d-none" id="fileInput" name="fichier" accept=".pdf,.jpg,.jpeg,.png" required>
                            <div id="filePreview" class="mt-2"></div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Notes</label>
                            <textarea class="form-control" name="notes" rows="3"></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header"><h5 class="mb-0">Informations</h5></div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <h6>Documents Critiques</h6>
                            <p class="small mb-0">Les documents Carte Grise, Assurance, Carte Transport et Visite Technique sont obligatoires pour qu'un engin soit considéré conforme.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-3">
            <a href="<?= BASE_URL ?>/documentengin" class="btn btn-secondary">Annuler</a>
            <button type="submit" class="btn btn-primary">Télécharger</button>
        </div>
    </form>
</div>

<?php
$content = ob_get_clean();
$additional_js = '<script>
$(document).ready(function() {
    const dropZone = $("#dropZone");
    const fileInput = $("#fileInput");
    const filePreview = $("#filePreview");
    
    dropZone.on("click", function() {
        fileInput.click();
    });
    
    dropZone.on("dragover", function(e) {
        e.preventDefault();
        $(this).addClass("border-primary");
    });
    
    dropZone.on("dragleave", function() {
        $(this).removeClass("border-primary");
    });
    
    dropZone.on("drop", function(e) {
        e.preventDefault();
        $(this).removeClass("border-primary");
        const files = e.originalEvent.dataTransfer.files;
        if (files.length > 0) {
            fileInput[0].files = files;
            showFilePreview(files[0]);
        }
    });
    
    fileInput.on("change", function() {
        if (this.files.length > 0) {
            showFilePreview(this.files[0]);
        }
    });
    
    function showFilePreview(file) {
        const name = file.name;
        const size = (file.size / 1024 / 1024).toFixed(2) + " Mo";
        filePreview.html(`<div class="alert alert-success">
            <i class="bi bi-file-earmark-check me-2"></i><strong>${name}</strong> (${size})
        </div>`);
    }
});
</script>';
$current_page = 'documents';
$breadcrumbs = [
    ['label' => 'Documents Engins', 'url' => BASE_URL . '/documentengin'],
    ['label' => 'Télécharger', 'url' => '']
];
include APP_PATH . '/Views/layouts/main.php';
?>
