<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'ZR_SGITM') ?></title>
    
    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/style.css">
    
    <style>
        :root {
            --primary-color: #240046;
            --secondary-color: #ff5400;
            --success-color: #28a745;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
        }
        
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(180deg, var(--primary-color) 0%, #3a015c 100%);
            padding: 20px 0;
        }
        
        .sidebar .nav-link {
            color: #fff;
            padding: 12px 20px;
            margin: 5px 15px;
            border-radius: 8px;
            transition: all 0.3s;
        }
        
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background-color: var(--secondary-color);
            transform: translateX(5px);
        }
        
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            margin-bottom: 20px;
        }
        
        .card-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, #3a015c 100%);
            color: white;
            border-radius: 12px 12px 0 0 !important;
            padding: 15px 20px;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-primary:hover {
            background-color: #3a015c;
            border-color: #3a015c;
        }
        
        .btn-secondary {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
        }
        
        .badge-stock-ok {
            background-color: var(--success-color);
        }
        
        .badge-stock-low {
            background-color: var(--warning-color);
        }
        
        .badge-stock-critical {
            background-color: var(--danger-color);
        }
        
        .table-hover tbody tr:hover {
            background-color: #f1f3f5;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 d-md-block sidebar">
                <div class="text-center mb-4">
                    <h4 class="text-white">ZR_SGITM</h4>
                    <small class="text-white-50">Système de Gestion Intégrée</small>
                </div>
                
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>/dashboard">
                            <i class="fas fa-tachometer-alt me-2"></i> Tableau de bord
                        </a>
                    </li>
                    
                    <li class="nav-item mt-3">
                        <small class="text-white-50 ms-3">GESTION STOCK</small>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="<?= BASE_URL ?>/piece">
                            <i class="fas fa-boxes me-2"></i> Pièces & Carburant
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>/mouvementstock">
                            <i class="fas fa-exchange-alt me-2"></i> Mouvements Stock
                        </a>
                    </li>
                    
                    <li class="nav-item mt-3">
                        <small class="text-white-50 ms-3">PARC & PERSONNEL</small>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>/engin">
                            <i class="fas fa-truck me-2"></i> Engins
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>/chauffeur">
                            <i class="fas fa-users me-2"></i> Chauffeurs
                        </a>
                    </li>
                    
                    <li class="nav-item mt-3">
                        <small class="text-white-50 ms-3">COMMERCIAL</small>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>/client">
                            <i class="fas fa-user-tie me-2"></i> Clients
                        </a>
                    </li>
                    
                    <li class="nav-item mt-4">
                        <a class="nav-link text-danger" href="<?= BASE_URL ?>/auth/logout">
                            <i class="fas fa-sign-out-alt me-2"></i> Déconnexion
                        </a>
                    </li>
                </ul>
            </nav>
            
            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <!-- Breadcrumb -->
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/dashboard">Accueil</a></li>
                        <li class="breadcrumb-item active">Pièces & Carburant</li>
                    </ol>
                </nav>
                
                <!-- Flash Messages -->
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i>
                        <?= htmlspecialchars($_SESSION['success']) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php unset($_SESSION['success']); ?>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <?= $_SESSION['error'] ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php unset($_SESSION['error']); ?>
                <?php endif; ?>
                
                <!-- Page Header -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="fas fa-boxes me-2"></i> Gestion des Pièces & Carburant</h2>
                    <?php if (in_array($_SESSION['role_id'] ?? 0, [ROLE_ADMIN, ROLE_GESTIONNAIRE_STOCK])): ?>
                        <a href="<?= BASE_URL ?>/piece/create" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i> Ajouter une Pièce
                        </a>
                    <?php endif; ?>
                </div>
                
                <!-- Critical Stock Alert -->
                <?php if (!empty($critical_stock)): ?>
                    <div class="card border-danger mb-4">
                        <div class="card-header bg-danger text-white">
                            <i class="fas fa-exclamation-triangle me-2"></i> Stock Critique - <?= count($critical_stock) ?> pièce(s)
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-sm table-hover">
                                    <thead>
                                        <tr>
                                            <th>Référence</th>
                                            <th>Description</th>
                                            <th>Stock Actuel</th>
                                            <th>Seuil</th>
                                            <th>À Commander</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($critical_stock as $piece): ?>
                                            <tr>
                                                <td><strong><?= htmlspecialchars($piece['reference']) ?></strong></td>
                                                <td><?= htmlspecialchars($piece['description']) ?></td>
                                                <td><span class="badge bg-danger"><?= $piece['stock_actuel'] ?></span></td>
                                                <td><?= $piece['seuil_alerte'] ?></td>
                                                <td><strong><?= $piece['quantite_a_commander'] ?></strong></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                
                <!-- Pieces Table -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-list me-2"></i> Liste des Pièces</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="piecesTable" class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Photo</th>
                                        <th>Référence</th>
                                        <th>Description</th>
                                        <th>Type</th>
                                        <th>Stock</th>
                                        <th>CUMP</th>
                                        <th>Valeur Stock</th>
                                        <th>Fournisseur</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($pieces as $piece): ?>
                                        <?php
                                        $stockClass = 'badge-stock-ok';
                                        if ($piece['stock_actuel'] < $piece['seuil_alerte']) {
                                            $stockClass = 'badge-stock-critical';
                                        } elseif ($piece['stock_actuel'] < $piece['seuil_alerte'] * 1.5) {
                                            $stockClass = 'badge-stock-low';
                                        }
                                        $valeurStock = $piece['stock_actuel'] * $piece['prix_unitaire_cump'];
                                        ?>
                                        <tr>
                                            <td>
                                                <?php if (!empty($piece['photo_path'])): ?>
                                                    <img src="<?= BASE_URL ?>/uploads/photos/<?= htmlspecialchars($piece['photo_path']) ?>" 
                                                         alt="Photo" class="img-thumbnail" style="width: 50px; height: 50px; object-fit: cover;">
                                                <?php else: ?>
                                                    <div class="bg-secondary text-white d-flex align-items-center justify-content-center" 
                                                         style="width: 50px; height: 50px; border-radius: 4px;">
                                                        <i class="fas fa-box"></i>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td><strong><?= htmlspecialchars($piece['reference']) ?></strong></td>
                                            <td><?= htmlspecialchars($piece['description']) ?></td>
                                            <td><span class="badge bg-info"><?= htmlspecialchars($piece['type']) ?></span></td>
                                            <td>
                                                <span class="badge <?= $stockClass ?>">
                                                    <?= $piece['stock_actuel'] ?> <?= htmlspecialchars($piece['unite_mesure'] ?? '') ?>
                                                </span>
                                            </td>
                                            <td><?= number_format($piece['prix_unitaire_cump'], 2, ',', ' ') ?> FC</td>
                                            <td><strong><?= number_format($valeurStock, 2, ',', ' ') ?> FC</strong></td>
                                            <td><?= htmlspecialchars($piece['fournisseur_nom'] ?? 'N/A') ?></td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <?php if (in_array($_SESSION['role_id'] ?? 0, [ROLE_ADMIN, ROLE_GESTIONNAIRE_STOCK])): ?>
                                                        <a href="<?= BASE_URL ?>/piece/edit/<?= $piece['id'] ?>" 
                                                           class="btn btn-outline-primary" title="Modifier">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                    <?php if (($_SESSION['role_id'] ?? 0) === ROLE_ADMIN): ?>
                                                        <button type="button" class="btn btn-outline-danger" 
                                                                onclick="confirmDelete(<?= $piece['id'] ?>)" title="Supprimer">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
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
            </main>
        </div>
    </div>
    
    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">Confirmer la suppression</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    Êtes-vous sûr de vouloir supprimer cette pièce ? Cette action est irréversible.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <form id="deleteForm" method="POST" style="display: inline;">
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash me-2"></i> Supprimer
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    
    <script>
        $(document).ready(function() {
            // Initialize DataTable
            $('#piecesTable').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/fr-FR.json'
                },
                order: [[1, 'asc']],
                pageLength: 25,
                responsive: true
            });
            
            // Auto-hide alerts after 5 seconds
            setTimeout(function() {
                $('.alert').fadeOut('slow');
            }, 5000);
        });
        
        function confirmDelete(id) {
            const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
            document.getElementById('deleteForm').action = '<?= BASE_URL ?>/piece/delete/' + id;
            modal.show();
        }
    </script>
</body>
</html>
