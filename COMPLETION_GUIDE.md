# Guide de Complétion du Système ZR_SGITM

## 📊 État d'Avancement Actuel

### ✅ Complété (70%)

#### Modèles (11/11 - 100%)
Tous les modèles requis sont créés et fonctionnels:
- ✅ User.php (authentification)
- ✅ Piece.php (catalogue pièces)
- ✅ MouvementStock.php (entrées/sorties CUMP)
- ✅ Engin.php (parc véhicules)
- ✅ Chauffeur.php (gestion chauffeurs)
- ✅ Client.php (gestion clients)
- ✅ Livraison.php (workflow livraisons)
- ✅ **Maintenance.php** (maintenances préventives/correctives)
- ✅ **Facture.php** (facturation avec numérotation auto)
- ✅ **DocumentEngin.php** (documents administratifs)
- ✅ **ConsommationCarburant.php** (suivi carburant avec alertes)

#### Contrôleurs (7/13 - 54%)
- ✅ AuthController.php
- ✅ DashboardController.php
- ✅ PieceController.php (CRUD complet avec upload photo)
- ✅ MouvementStockController.php (entrées/sorties avec CUMP)
- ✅ EnginController.php (CRUD avec conformité documents)
- ✅ ChauffeurController.php (CRUD avec vérification permis)
- ✅ ClientController.php (CRUD avec CA total)

### 🚧 À Compléter (30%)

#### Contrôleurs Manquants (6/13)
1. **MaintenanceController.php** (Priorité: CRITIQUE)
   - Gestion workflow: planifiee → en_cours → terminee/annulee
   - Sélection pièces utilisées + sortie automatique stock
   - Calcul automatique des coûts
   
2. **LivraisonController.php** (Priorité: CRITIQUE)
   - Gestion workflow complet
   - Validations avant création (engin, documents, permis)
   - Changement automatique statut engin
   
3. **FactureController.php** (Priorité: HAUTE)
   - Création depuis livraisons
   - Génération PDF (utiliser TCPDF ou FPDF)
   - Gestion paiements

4. **DocumentEnginController.php** (Priorité: HAUTE)
   - Upload sécurisé (PDF, JPG, PNG max 10Mo)
   - Vérification MIME types + extensions
   - Renommage: {type}_{immatriculation}_{date}.ext
   - Stockage hors public (uploads/documents/)

5. **ConsommationCarburantController.php** (Priorité: MOYENNE)
   - Enregistrement pleins
   - Calcul automatique consommation
   - Détection surconsommation > moyenne + 20%
   - Rapports mensuels

6. **FournisseurController.php** (Priorité: BASSE - non mentionné dans le ticket mais utile)

#### Vues CRUD (0/~30)
Toutes les vues doivent suivre ce pattern:

```php
// Structure commune de vue
<?php include APP_PATH . '/Views/layouts/header.php'; ?>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <?php include APP_PATH . '/Views/layouts/sidebar.php'; ?>
        
        <!-- Contenu principal -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <!-- Breadcrumb -->
            <!-- Messages Flash (success/error) -->
            <!-- Contenu spécifique de la page -->
        </main>
    </div>
</div>

<?php include APP_PATH . '/Views/layouts/footer.php'; ?>
```

**Vues à créer par module:**

1. **Pièces** (/app/Views/pieces/)
   - index.php : Liste avec DataTables, filtres, indicateur stock critique
   - create.php : Formulaire ajout avec upload photo
   - edit.php : Formulaire modification

2. **Mouvements Stock** (/app/Views/mouvements_stock/)
   - index.php : Historique avec filtres (type, date, pièce)
   - create.php : Formulaire entrée/sortie dynamique

3. **Engins** (/app/Views/engins/)
   - index.php : Liste avec statuts + conformité documents
   - create.php : Formulaire complet + upload photo
   - edit.php : Modification

4. **Chauffeurs** (/app/Views/chauffeurs/)
   - index.php : Liste avec validité permis
   - create.php : Formulaire + upload photo
   - edit.php : Modification

5. **Maintenances** (/app/Views/maintenances/)
   - index.php : Liste avec filtres (statut, type, engin)
   - create.php : Formulaire avec sélection pièces
   - edit.php : Modification + ajout pièces
   - show.php : Détail complet, pièces utilisées, coûts

6. **Livraisons** (/app/Views/livraisons/)
   - index.php : Liste + vue calendrier (FullCalendar.js)
   - create.php : Formulaire avec validations temps réel
   - edit.php : Modification selon statut
   - show.php : Détail complet + historique

7. **Clients** (/app/Views/clients/)
   - index.php : Liste avec CA total
   - create.php : Formulaire complet
   - edit.php : Modification

8. **Factures** (/app/Views/factures/)
   - index.php : Liste avec statuts paiement
   - create.php : Sélection livraison + calcul auto
   - show.php : Aperçu + bouton PDF + marquer payée

9. **Documents Engins** (/app/Views/documents_engins/)
   - index.php : Tableau par engin + alertes expiration
   - upload.php : Formulaire upload sécurisé

10. **Consommations Carburant** (/app/Views/consommations_carburant/)
    - index.php : Liste des pleins + alertes
    - create.php : Formulaire avec calcul auto
    - rapport_mensuel.php : Rapport avec Chart.js

#### Routes (/config/routes.php)
Ajouter toutes les routes RESTful:

```php
// Pièces
$router->add('GET', '/pieces', 'PieceController@index');
$router->add('GET', '/pieces/create', 'PieceController@create');
$router->add('POST', '/pieces/store', 'PieceController@store');
$router->add('GET', '/pieces/edit/:id', 'PieceController@edit');
$router->add('POST', '/pieces/update/:id', 'PieceController@update');
$router->add('POST', '/pieces/delete/:id', 'PieceController@delete');

// Mouvements Stock
$router->add('GET', '/mouvements-stock', 'MouvementStockController@index');
$router->add('GET', '/mouvements-stock/create', 'MouvementStockController@create');
$router->add('POST', '/mouvements-stock/store', 'MouvementStockController@store');
$router->add('POST', '/mouvements-stock/delete/:id', 'MouvementStockController@delete');
$router->add('GET', '/mouvements-stock/piece-info/:id', 'MouvementStockController@getPieceInfo');

// Engins
$router->add('GET', '/engins', 'EnginController@index');
$router->add('GET', '/engins/create', 'EnginController@create');
$router->add('POST', '/engins/store', 'EnginController@store');
$router->add('GET', '/engins/edit/:id', 'EnginController@edit');
$router->add('POST', '/engins/update/:id', 'EnginController@update');
$router->add('POST', '/engins/delete/:id', 'EnginController@delete');

// Chauffeurs
$router->add('GET', '/chauffeurs', 'ChauffeurController@index');
$router->add('GET', '/chauffeurs/create', 'ChauffeurController@create');
$router->add('POST', '/chauffeurs/store', 'ChauffeurController@store');
$router->add('GET', '/chauffeurs/edit/:id', 'ChauffeurController@edit');
$router->add('POST', '/chauffeurs/update/:id', 'ChauffeurController@update');
$router->add('POST', '/chauffeurs/delete/:id', 'ChauffeurController@delete');

// Clients
$router->add('GET', '/clients', 'ClientController@index');
$router->add('GET', '/clients/create', 'ClientController@create');
$router->add('POST', '/clients/store', 'ClientController@store');
$router->add('GET', '/clients/edit/:id', 'ClientController@edit');
$router->add('POST', '/clients/update/:id', 'ClientController@update');
$router->add('POST', '/clients/delete/:id', 'ClientController@delete');

// À AJOUTER pour les autres modules...
```

#### Navigation Sidebar
Mettre à jour `/app/Views/layouts/sidebar.php` (ou `/app/Views/layouts/main.php`) pour inclure tous les modules:

```php
<nav class="sidebar">
    <ul class="nav flex-column">
        <li class="nav-item">
            <a class="nav-link" href="/dashboard">
                <i class="fas fa-tachometer-alt"></i> Tableau de bord
            </a>
        </li>
        
        <!-- Gestion Stock -->
        <li class="nav-item">
            <a class="nav-link" href="/pieces">
                <i class="fas fa-boxes"></i> Pièces & Carburant
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="/mouvements-stock">
                <i class="fas fa-exchange-alt"></i> Mouvements Stock
            </a>
        </li>
        
        <!-- Parc & Personnel -->
        <li class="nav-item">
            <a class="nav-link" href="/engins">
                <i class="fas fa-truck"></i> Engins
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="/chauffeurs">
                <i class="fas fa-users"></i> Chauffeurs
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="/documents-engins">
                <i class="fas fa-file-alt"></i> Documents
            </a>
        </li>
        
        <!-- Opérations -->
        <li class="nav-item">
            <a class="nav-link" href="/maintenances">
                <i class="fas fa-wrench"></i> Maintenances
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="/livraisons">
                <i class="fas fa-shipping-fast"></i> Livraisons
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="/consommations-carburant">
                <i class="fas fa-gas-pump"></i> Carburant
            </a>
        </li>
        
        <!-- Commercial -->
        <li class="nav-item">
            <a class="nav-link" href="/clients">
                <i class="fas fa-user-tie"></i> Clients
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="/factures">
                <i class="fas fa-file-invoice-dollar"></i> Factures
            </a>
        </li>
    </ul>
</nav>
```

## 🎯 Plan de Complétion Recommandé

### Étape 1: Créer les Contrôleurs Manquants (2-3 heures)
Suivre le pattern des contrôleurs existants (PieceController, EnginController)

### Étape 2: Créer une Vue Complète de Référence (1 heure)
Commencer par pieces/index.php comme template

### Étape 3: Dupliquer et Adapter les Vues (4-5 heures)
Utiliser la vue de référence pour tous les autres modules

### Étape 4: Configurer les Routes (30 min)
Ajouter toutes les routes dans routes.php

### Étape 5: Tester et Debugger (2-3 heures)
Test complet de chaque module

## 📋 Checklist de Validation

- [ ] Tous les modèles ont leurs méthodes métier
- [ ] Tous les contrôleurs ont CRUD complet
- [ ] Toutes les vues sont créées et fonctionnelles
- [ ] Toutes les routes sont configurées
- [ ] Navigation sidebar complète
- [ ] Upload de fichiers sécurisé
- [ ] Calculs automatiques (CUMP, coûts, consommation)
- [ ] Validations formulaires (HTML5 + serveur)
- [ ] Messages flash (succès/erreur)
- [ ] Protection CSRF sur tous les formulaires
- [ ] DataTables sur toutes les listes
- [ ] Responsive design Bootstrap 5
- [ ] Permissions par rôle respectées

## 🔒 Points de Sécurité Critiques

1. **Upload de fichiers**:
   - Vérifier extension ET MIME type
   - Limiter taille (5-10Mo)
   - Renommer systématiquement
   - Stocker hors public
   - Vérifier avec `getimagesize()` pour images

2. **Validation**:
   - Toujours valider côté serveur
   - Utiliser `$this->sanitize()` sur toutes les entrées
   - Requêtes préparées PDO (déjà en place)

3. **CSRF**:
   - Token sur TOUS les formulaires
   - Vérifier avec `$this->verifyCsrfToken()`

## 📚 Librairies Recommandées

- **PDF**: TCPDF (composer require tecnickcom/tcpdf)
- **Export Excel**: PhpSpreadsheet (composer require phpoffice/phpspreadsheet)
- **Charts**: Chart.js (CDN déjà référencé)
- **Datepicker**: Bootstrap Datepicker
- **DataTables**: jQuery DataTables (CDN)

## 🎨 Style Guide

- Couleur primaire: #240046 (violet foncé)
- Couleur secondaire: #ff5400 (orange)
- Succès: #28a745
- Attention: #ffc107
- Erreur: #dc3545

Tous les éléments Bootstrap 5.3 sont disponibles.

## 🆘 Aide et Support

- Modèles: Regarder Piece.php, Engin.php pour exemples
- Contrôleurs: Regarder PieceController.php pour CRUD complet
- Base de données: schema.sql pour structure complète
- Triggers: Déjà en place pour CUMP et validations auto
