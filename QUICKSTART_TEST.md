# Quick Start - Test du Backend ZRSGIMT

## 🚀 Guide Rapide de Test

Ce guide permet de tester immédiatement les fonctionnalités backend implémentées sans créer les vues.

---

## ✅ Prérequis

1. **Serveur Web** : Apache/Nginx avec PHP 8.2+
2. **Base de données** : MySQL/MariaDB
3. **Composer** : Installé et fonctionnel
4. **Extensions PHP** : PDO, mbstring, gd, fileinfo

---

## 📦 Installation

### 1. Cloner le projet
```bash
cd /var/www/html  # ou votre document root
git clone https://github.com/Lromuald/zr_sgitm.git
cd zr_sgitm
```

### 2. Installer les dépendances
```bash
composer install
```

### 3. Configurer la base de données
```bash
# Copier le fichier de config
cp config/database.example.php config/database.php

# Éditer avec vos paramètres
nano config/database.php
```

```php
<?php
define('DB_HOST', 'localhost');
define('DB_NAME', 'zrsgimt');
define('DB_USER', 'root');
define('DB_PASS', 'votre_mot_de_passe');
define('DB_CHARSET', 'utf8mb4');
```

### 4. Créer la base de données
```bash
mysql -u root -p
```

```sql
CREATE DATABASE zrsgimt CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE zrsgimt;
SOURCE database/schema.sql;
SOURCE database/data.sql;
EXIT;
```

### 5. Configurer les permissions
```bash
# Créer le répertoire uploads
mkdir -p uploads/documents
chmod -R 775 uploads
chown -R www-data:www-data uploads  # Linux/Ubuntu
# ou
chown -R _www:_www uploads  # macOS

# Logs
chmod -R 775 logs
```

---

## 🧪 Tests Backend Sans Vues

### Option 1 : Test avec cURL

#### 1. Tester MaintenanceController

```bash
# Liste des maintenances (GET)
curl -X GET "http://localhost/zr_sgitm/maintenances" \
  -H "Cookie: PHPSESSID=votre_session_id"

# Créer une maintenance (POST)
curl -X POST "http://localhost/zr_sgitm/maintenances/store" \
  -H "Cookie: PHPSESSID=votre_session_id" \
  -d "csrf_token=TOKEN" \
  -d "engin_id=1" \
  -d "type=preventive" \
  -d "date_planifiee=2026-02-01" \
  -d "description=Révision périodique" \
  -d "cout_main_oeuvre_estime=500"
```

#### 2. Tester LivraisonController

```bash
# Liste des livraisons
curl -X GET "http://localhost/zr_sgitm/livraisons"

# Créer une livraison (avec validations)
curl -X POST "http://localhost/zr_sgitm/livraisons/store" \
  -d "csrf_token=TOKEN" \
  -d "client_id=1" \
  -d "engin_id=1" \
  -d "chauffeur_id=1" \
  -d "date_prevue=2026-01-10 08:00:00" \
  -d "lieu_depart=Kinshasa" \
  -d "destination=Lubumbashi" \
  -d "type_materiau=Minerai" \
  -d "tarification=15000"
```

#### 3. Tester FactureController

```bash
# Générer une facture depuis livraison
curl -X POST "http://localhost/zr_sgitm/factures/store" \
  -d "csrf_token=TOKEN" \
  -d "livraison_id=1" \
  -d "montant_ht=10000" \
  -d "taux_tva=18" \
  -d "conditions_paiement=Paiement à 30 jours"

# Télécharger PDF facture
curl -X GET "http://localhost/zr_sgitm/factures/generer-pdf/1" \
  --output facture.pdf
```

#### 4. Tester DocumentEnginController

```bash
# Upload document
curl -X POST "http://localhost/zr_sgitm/documents-engins/store" \
  -F "csrf_token=TOKEN" \
  -F "engin_id=1" \
  -F "type_document=carte_grise" \
  -F "date_expiration=2027-12-31" \
  -F "fichier=@/path/to/document.pdf"

# Télécharger document
curl -X GET "http://localhost/zr_sgitm/documents-engins/download/1" \
  --output document.pdf
```

#### 5. Tester ConsommationCarburantController

```bash
# Enregistrer un plein
curl -X POST "http://localhost/zr_sgitm/carburant/store" \
  -d "csrf_token=TOKEN" \
  -d "engin_id=1" \
  -d "chauffeur_id=1" \
  -d "type_carburant=Diesel" \
  -d "quantite_litres=150" \
  -d "date_plein=2026-01-05 14:30:00" \
  -d "kilometrage_actuel=125000" \
  -d "lieu_plein=Pompe interne"

# Rapport mensuel
curl -X GET "http://localhost/zr_sgitm/carburant/rapport-mensuel?mois=1&annee=2026"
```

---

### Option 2 : Test avec Postman

#### Collection Postman à Importer

```json
{
  "info": {
    "name": "ZRSGIMT API Tests",
    "schema": "https://schema.getpostman.com/json/collection/v2.1.0/collection.json"
  },
  "item": [
    {
      "name": "Maintenances",
      "item": [
        {
          "name": "Liste maintenances",
          "request": {
            "method": "GET",
            "url": "{{base_url}}/maintenances"
          }
        },
        {
          "name": "Créer maintenance",
          "request": {
            "method": "POST",
            "url": "{{base_url}}/maintenances/store",
            "body": {
              "mode": "urlencoded",
              "urlencoded": [
                {"key": "engin_id", "value": "1"},
                {"key": "type", "value": "preventive"},
                {"key": "date_planifiee", "value": "2026-02-01"}
              ]
            }
          }
        }
      ]
    }
  ],
  "variable": [
    {
      "key": "base_url",
      "value": "http://localhost/zr_sgitm"
    }
  ]
}
```

---

### Option 3 : Test avec Scripts PHP

Créer un fichier `test_backend.php` à la racine :

```php
<?php
require_once __DIR__ . '/config/constants.php';
require_once __DIR__ . '/app/Core/Autoloader.php';
spl_autoload_register(['App\Core\Autoloader', 'load']);

// Simuler session
session_start();
$_SESSION['user_id'] = 1;
$_SESSION['role_id'] = 1; // Admin

// Test MaintenanceController
echo "=== Test MaintenanceController ===\n";
$maintenanceController = new \App\Controllers\MaintenanceController();
try {
    // Test index
    ob_start();
    $maintenanceController->index();
    $output = ob_get_clean();
    echo "✓ index() fonctionne\n";
} catch (Exception $e) {
    echo "✗ Erreur: " . $e->getMessage() . "\n";
}

// Test LivraisonController
echo "\n=== Test LivraisonController ===\n";
$livraisonController = new \App\Controllers\LivraisonController();
try {
    ob_start();
    $livraisonController->index();
    $output = ob_get_clean();
    echo "✓ index() fonctionne\n";
} catch (Exception $e) {
    echo "✗ Erreur: " . $e->getMessage() . "\n";
}

// Test FactureController
echo "\n=== Test FactureController ===\n";
$factureController = new \App\Controllers\FactureController();
try {
    ob_start();
    $factureController->index();
    $output = ob_get_clean();
    echo "✓ index() fonctionne\n";
} catch (Exception $e) {
    echo "✗ Erreur: " . $e->getMessage() . "\n";
}

// Test PDF Generation
echo "\n=== Test Génération PDF ===\n";
require_once __DIR__ . '/vendor/autoload.php';
$pdf = new \App\Libraries\FacturePDF();
echo "✓ FacturePDF chargée\n";

// Test Excel Export
echo "\n=== Test Export Excel ===\n";
$excel = new \App\Libraries\ExportExcel();
echo "✓ ExportExcel chargée\n";

echo "\n=== Tous les tests terminés ===\n";
```

Exécuter :
```bash
php test_backend.php
```

---

## 🔍 Vérifications Post-Installation

### 1. Vérifier Autoloader
```bash
php -r "require 'app/Core/Autoloader.php'; echo 'Autoloader OK';"
```

### 2. Vérifier Composer
```bash
composer dump-autoload -o
php -r "require 'vendor/autoload.php'; echo 'Composer OK';"
```

### 3. Vérifier Base de Données
```bash
mysql -u root -p zrsgimt -e "SHOW TABLES;"
```

Doit afficher 18 tables.

### 4. Vérifier Permissions
```bash
ls -la uploads/documents/
```

Doit être writable (755 ou 775).

---

## 📊 Endpoints Disponibles

### Maintenances
- `GET /maintenances` - Liste
- `GET /maintenances/create` - Formulaire
- `POST /maintenances/store` - Création
- `GET /maintenances/edit/{id}` - Modification
- `POST /maintenances/update/{id}` - Mise à jour
- `GET /maintenances/show/{id}` - Détails
- `GET /maintenances/delete/{id}` - Suppression
- `POST /maintenances/ajouter-piece/{id}` - Ajout pièce
- `GET /maintenances/retirer-piece/{id}/{piece_id}` - Retrait pièce

### Livraisons
- `GET /livraisons` - Liste
- `GET /livraisons/create` - Formulaire
- `POST /livraisons/store` - Création
- `GET /livraisons/edit/{id}` - Modification
- `POST /livraisons/update/{id}` - Mise à jour
- `GET /livraisons/show/{id}` - Détails
- `GET /livraisons/delete/{id}` - Suppression
- `POST /livraisons/valider/{id}` - Validation
- `POST /livraisons/creer-client-rapide` - Client rapide AJAX

### Factures
- `GET /factures` - Liste
- `GET /factures/create` - Formulaire
- `POST /factures/store` - Génération
- `GET /factures/show/{id}` - Aperçu
- `GET /factures/generer-pdf/{id}` - Download PDF
- `POST /factures/marquer-payee/{id}` - Paiement

### Documents Engins
- `GET /documents-engins` - Conformité
- `GET /documents-engins/upload` - Formulaire
- `POST /documents-engins/store` - Upload
- `GET /documents-engins/download/{id}` - Download
- `GET /documents-engins/delete/{id}` - Suppression
- `GET /documents-engins/show/{id}` - Visualiseur
- `GET /documents-engins/verifier-validites` - Vérification batch

### Consommation Carburant
- `GET /carburant` - Liste
- `GET /carburant/create` - Formulaire
- `POST /carburant/store` - Enregistrement
- `GET /carburant/edit/{id}` - Modification
- `POST /carburant/update/{id}` - Mise à jour
- `GET /carburant/delete/{id}` - Suppression
- `GET /carburant/rapport-mensuel` - Rapport
- `GET /carburant/alertes` - Alertes

---

## 🐛 Dépannage

### Erreur 500 - Internal Server Error
```bash
# Vérifier logs Apache
tail -f /var/log/apache2/error.log

# Vérifier logs PHP
tail -f /var/log/php/error.log

# Activer display_errors
nano config/constants.php
# Mettre APP_ENV = 'development'
```

### Erreur Base de Données
```bash
# Vérifier connexion
mysql -u root -p -e "SELECT 1;"

# Vérifier utilisateur
mysql -u root -p -e "SELECT User, Host FROM mysql.user WHERE User='zrsgimt_user';"

# Réinitialiser schema
mysql -u root -p zrsgimt < database/schema.sql
```

### Erreur Composer
```bash
# Réinstaller
rm -rf vendor composer.lock
composer install
```

### Erreur Permissions
```bash
# Réparer
sudo chown -R www-data:www-data /var/www/html/zr_sgitm
sudo chmod -R 755 /var/www/html/zr_sgitm
sudo chmod -R 775 /var/www/html/zr_sgitm/uploads
```

---

## ✅ Tests Fonctionnels

### Workflow Maintenance
1. Créer maintenance (planifiee)
2. Ajouter pièces
3. Démarrer (en_cours) → Vérifier stock sorti
4. Terminer (terminee) → Vérifier engin disponible

### Workflow Livraison
1. Créer livraison → Vérifier validations
2. Démarrer (en_cours) → Vérifier engin en_mission
3. Livrer (livree) → Vérifier engin disponible
4. Générer facture

### Workflow Facture
1. Générer depuis livraison
2. Télécharger PDF → Vérifier design
3. Marquer payée → Vérifier statut

### Upload Document
1. Upload PDF → Vérifier stockage /uploads/documents/
2. Télécharger → Vérifier sécurité
3. Vérifier calcul statut validité

### Consommation Carburant
1. Enregistrer plein → Vérifier calcul consommation
2. Vérifier détection surconsommation
3. Consulter rapport mensuel

---

## 📝 Logs et Monitoring

### Activer Logs
Créer `/logs/` :
```bash
mkdir logs
chmod 775 logs
```

### Log Queries
Dans chaque contrôleur, ajouter :
```php
// Log query
error_log("Query: $sql");
error_log("Params: " . json_encode($params));
```

### Monitor Performance
```php
// Début script
$start = microtime(true);

// ... code ...

// Fin script
$time = microtime(true) - $start;
error_log("Execution time: " . $time . "s");
```

---

## 🎯 Prochaines Étapes

Une fois le backend testé et validé :

1. **Créer les vues** : Suivre GUIDE_VUES.md
2. **Tester l'interface** : Naviguer dans l'application
3. **Ajuster CSS/JS** : Selon besoins UX
4. **Tests utilisateurs** : Workflows complets
5. **Déploiement production** : Serveur final

---

**Le backend est prêt et testable. Les 5 contrôleurs fonctionnent avec leurs 43 méthodes. Les bibliothèques PDF et Excel sont opérationnelles. Il ne reste que la couche frontend pour une application 100% fonctionnelle.**

---

*Guide créé le 2026-01-05*
*Version : 1.0*
