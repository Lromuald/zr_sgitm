<!DOCTYPE html>
<html lang="<?php echo $_SESSION['langue'] ?? 'fr'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title><?php echo $title ?? 'ZRSGIMT'; ?> | Système de Gestion Intégrée</title>
    
    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/style.css">
    
    <style>
        :root {
            --primary-color: #240046;
            --secondary-color: #ff5400;
            --success-color: #28a745;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
            --neutral-color: #6c757d;
            --sidebar-width: 260px;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
        }

        /* Sidebar */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: var(--sidebar-width);
            background: linear-gradient(180deg, var(--primary-color) 0%, #3c096c 100%);
            color: white;
            overflow-y: auto;
            transition: all 0.3s ease;
            z-index: 1000;
        }

        .sidebar-header {
            padding: 25px 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .sidebar-logo {
            font-size: 24px;
            font-weight: bold;
            color: white;
            text-decoration: none;
            display: block;
        }

        .sidebar-subtitle {
            font-size: 12px;
            opacity: 0.8;
            margin-top: 5px;
        }

        .sidebar-menu {
            padding: 20px 0;
        }

        .menu-item {
            padding: 12px 20px;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            display: flex;
            align-items: center;
            transition: all 0.3s ease;
        }

        .menu-item:hover {
            background: rgba(255, 255, 255, 0.1);
            color: white;
        }

        .menu-item.active {
            background: var(--secondary-color);
            color: white;
        }

        .menu-item i {
            width: 30px;
            font-size: 18px;
        }

        /* Main Content */
        .main-content {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
        }

        /* Top Navigation */
        .top-nav {
            background: white;
            padding: 15px 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .top-nav-left {
            display: flex;
            align-items: center;
        }

        .breadcrumb {
            background: none;
            margin: 0;
            padding: 0;
        }

        .top-nav-right {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .notification-bell {
            position: relative;
            cursor: pointer;
            font-size: 20px;
            color: var(--neutral-color);
        }

        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: var(--danger-color);
            color: white;
            font-size: 10px;
            padding: 2px 6px;
            border-radius: 10px;
        }

        .user-menu {
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--primary-color);
            color: white;
            display: flex;
            justify-content: center;
            align-items: center;
            font-weight: bold;
        }

        /* Content Area */
        .content-area {
            padding: 30px;
        }

        /* Cards */
        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            transform: translateY(-2px);
        }

        .stat-card-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 28px;
            margin-bottom: 15px;
        }

        .stat-card-value {
            font-size: 32px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .stat-card-label {
            color: var(--neutral-color);
            font-size: 14px;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                margin-left: calc(-1 * var(--sidebar-width));
            }

            .main-content {
                margin-left: 0;
            }

            .sidebar.show {
                margin-left: 0;
            }
        }

        /* Utility Classes */
        .bg-primary-gradient {
            background: linear-gradient(135deg, var(--primary-color) 0%, #5a189a 100%);
        }

        .bg-secondary-gradient {
            background: linear-gradient(135deg, var(--secondary-color) 0%, #ff6d00 100%);
        }

        .bg-success-gradient {
            background: linear-gradient(135deg, var(--success-color) 0%, #34ce57 100%);
        }

        .bg-warning-gradient {
            background: linear-gradient(135deg, var(--warning-color) 0%, #ffdd00 100%);
        }

        .bg-danger-gradient {
            background: linear-gradient(135deg, var(--danger-color) 0%, #ff5252 100%);
        }
    </style>

    <?php if (isset($additional_css)): ?>
        <?php echo $additional_css; ?>
    <?php endif; ?>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <a href="<?php echo BASE_URL; ?>/dashboard" class="sidebar-logo">ZRSGIMT</a>
            <div class="sidebar-subtitle">Gestion Intégrée</div>
        </div>

        <div class="sidebar-menu">
            <a href="<?php echo BASE_URL; ?>/dashboard" class="menu-item <?php echo ($current_page ?? '') == 'dashboard' ? 'active' : ''; ?>">
                <i class="bi bi-speedometer2"></i>
                <span>Tableau de bord</span>
            </a>

            <a href="<?php echo BASE_URL; ?>/engins" class="menu-item <?php echo ($current_page ?? '') == 'engins' ? 'active' : ''; ?>">
                <i class="bi bi-truck"></i>
                <span>Engins</span>
            </a>

            <a href="<?php echo BASE_URL; ?>/chauffeurs" class="menu-item <?php echo ($current_page ?? '') == 'chauffeurs' ? 'active' : ''; ?>">
                <i class="bi bi-person-badge"></i>
                <span>Chauffeurs</span>
            </a>

            <a href="<?php echo BASE_URL; ?>/livraisons" class="menu-item <?php echo ($current_page ?? '') == 'livraisons' ? 'active' : ''; ?>">
                <i class="bi bi-box-seam"></i>
                <span>Livraisons</span>
            </a>

            <a href="<?php echo BASE_URL; ?>/stocks" class="menu-item <?php echo ($current_page ?? '') == 'stocks' ? 'active' : ''; ?>">
                <i class="bi bi-boxes"></i>
                <span>Stocks & Pièces</span>
            </a>

            <a href="<?php echo BASE_URL; ?>/maintenances" class="menu-item <?php echo ($current_page ?? '') == 'maintenances' ? 'active' : ''; ?>">
                <i class="bi bi-tools"></i>
                <span>Maintenances</span>
            </a>

            <a href="<?php echo BASE_URL; ?>/clients" class="menu-item <?php echo ($current_page ?? '') == 'clients' ? 'active' : ''; ?>">
                <i class="bi bi-people"></i>
                <span>Clients</span>
            </a>

            <a href="<?php echo BASE_URL; ?>/factures" class="menu-item <?php echo ($current_page ?? '') == 'factures' ? 'active' : ''; ?>">
                <i class="bi bi-receipt"></i>
                <span>Factures</span>
            </a>

            <a href="<?php echo BASE_URL; ?>/documents" class="menu-item <?php echo ($current_page ?? '') == 'documents' ? 'active' : ''; ?>">
                <i class="bi bi-file-earmark-text"></i>
                <span>Documents</span>
            </a>

            <a href="<?php echo BASE_URL; ?>/carburant" class="menu-item <?php echo ($current_page ?? '') == 'carburant' ? 'active' : ''; ?>">
                <i class="bi bi-fuel-pump"></i>
                <span>Carburant</span>
            </a>

            <?php if (isset($_SESSION['role_id']) && $_SESSION['role_id'] == ROLE_ADMIN): ?>
            <a href="<?php echo BASE_URL; ?>/users" class="menu-item <?php echo ($current_page ?? '') == 'users' ? 'active' : ''; ?>">
                <i class="bi bi-person-gear"></i>
                <span>Utilisateurs</span>
            </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Navigation -->
        <div class="top-nav">
            <div class="top-nav-left">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>/dashboard">Accueil</a></li>
                        <?php if (isset($breadcrumbs)): ?>
                            <?php foreach ($breadcrumbs as $key => $crumb): ?>
                                <?php if ($key < count($breadcrumbs) - 1): ?>
                                    <li class="breadcrumb-item"><a href="<?php echo $crumb['url']; ?>"><?php echo $crumb['label']; ?></a></li>
                                <?php else: ?>
                                    <li class="breadcrumb-item active" aria-current="page"><?php echo $crumb['label']; ?></li>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ol>
                </nav>
            </div>

            <div class="top-nav-right">
                <!-- Notifications -->
                <div class="notification-bell" id="notificationBell">
                    <i class="bi bi-bell"></i>
                    <?php if (isset($notifications) && count($notifications) > 0): ?>
                        <span class="notification-badge"><?php echo count($notifications); ?></span>
                    <?php endif; ?>
                </div>

                <!-- User Menu -->
                <div class="user-menu dropdown">
                    <div class="d-flex align-items-center" data-bs-toggle="dropdown">
                        <div class="user-avatar">
                            <?php if (isset($user['photo']) && $user['photo']): ?>
                                <img src="<?php echo BASE_URL; ?>/uploads/photos/<?php echo $user['photo']; ?>" alt="Photo" style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover;">
                            <?php else: ?>
                                <?php echo strtoupper(substr($user['prenom'] ?? 'U', 0, 1) . substr($user['nom'] ?? '', 0, 1)); ?>
                            <?php endif; ?>
                        </div>
                        <div class="ms-2">
                            <div style="font-weight: 600; font-size: 14px;"><?php echo htmlspecialchars($user['prenom'] ?? ''); ?> <?php echo htmlspecialchars($user['nom'] ?? ''); ?></div>
                            <div style="font-size: 12px; color: var(--neutral-color);"><?php echo htmlspecialchars($user['role'] ?? ''); ?></div>
                        </div>
                        <i class="bi bi-chevron-down ms-2"></i>
                    </div>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/profile"><i class="bi bi-person me-2"></i>Mon profil</a></li>
                        <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/settings"><i class="bi bi-gear me-2"></i>Paramètres</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="<?php echo BASE_URL; ?>/auth/logout"><i class="bi bi-box-arrow-right me-2"></i>Déconnexion</a></li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Content Area -->
        <div class="content-area">
            <?php echo $content ?? ''; ?>
        </div>
    </div>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    
    <!-- Bootstrap 5.3 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

    <?php if (isset($additional_js)): ?>
        <?php echo $additional_js; ?>
    <?php endif; ?>
</body>
</html>
