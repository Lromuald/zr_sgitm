# ZRSGIMT - Système de Gestion Intégrée

Application web complète de gestion intégrée pour entreprises opérant dans les secteurs minier et de transport.

## 📋 Fonctionnalités

### Modules Principaux
- **Authentification & Utilisateurs** : Système sécurisé avec 5 rôles (Administrateur, Gestionnaire Stock, Maintenance, Commercial, Lecture seule)
- **Gestion des Engins** : Parc complet avec suivi des statuts, documents et maintenances
- **Gestion des Chauffeurs** : Affectations, permis, alertes d'expiration
- **Gestion des Stocks** : Méthode CUMP, mouvements, alertes automatiques
- **Livraisons** : Workflow complet avec validations et blocages automatiques
- **Maintenances** : Préventive et corrective avec gestion des pièces
- **Facturation** : Génération automatique, suivi des paiements
- **Documents Administratifs** : Alertes multi-niveaux (J-30/J-15/J-3), blocages automatiques
- **Consommation Carburant** : Suivi détaillé, détection de surconsommation

### Caractéristiques Techniques
- Architecture MVC personnalisée
- Sécurité renforcée (CSRF, XSS, SQL injection)
- Interface responsive (Bootstrap 5.3)
- Graphiques interactifs (Chart.js)
- Tableaux de données (DataTables)
- Système d'audit complet
- Notifications en temps réel

## 🚀 Installation

### Prérequis
- PHP 8.2 ou supérieur
- MySQL 8.0 ou supérieur
- Serveur web (Apache/Nginx)
- Extensions PHP : PDO, PDO_MySQL, mbstring, json

### Étapes d'installation

#### 1. Cloner le projet
```bash
git clone https://github.com/Lromuald/zr_sgitm.git
cd zr_sgitm
```

#### 2. Configuration de la base de données

**a. Créer la base de données**
```bash
# Se connecter à MySQL
mysql -u root -p

# Exécuter les scripts SQL
source database/schema.sql
source database/demo_data.sql
```

**b. Configurer la connexion**
```bash
# Copier le fichier de configuration
cp config/database.example.php config/database.php

# Éditer config/database.php avec vos paramètres
```

Modifier les paramètres dans `config/database.php` :
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'zrsgimt_db');
define('DB_USER', 'votre_utilisateur');
define('DB_PASS', 'votre_mot_de_passe');
```

#### 3. Configuration Apache

**a. Activer mod_rewrite**
```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```

**b. Configuration VirtualHost (optionnel)**
```apache
<VirtualHost *:80>
    ServerName zrsgimt.local
    DocumentRoot /chemin/vers/zr_sgitm/public
    
    <Directory /chemin/vers/zr_sgitm/public>
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/zrsgimt-error.log
    CustomLog ${APACHE_LOG_DIR}/zrsgimt-access.log combined
</VirtualHost>
```

#### 4. Permissions des fichiers
```bash
# Définir les permissions pour les dossiers uploads
chmod 755 uploads/documents
chmod 755 uploads/photos

# Si nécessaire, ajuster le propriétaire
chown -R www-data:www-data uploads/
```

#### 5. Configuration de l'URL de base

Modifier `config/constants.php` avec votre URL :
```php
define('BASE_URL', 'http://localhost/zr_sgitm'); // ou votre URL
```

#### 6. Accéder à l'application

Ouvrir votre navigateur et accéder à : `http://localhost/zr_sgitm` ou votre URL configurée

## 👤 Comptes de Démonstration

### Administrateur
- **Email** : admin@zrsgimt.com
- **Mot de passe** : Admin@2025
- **Permissions** : Accès complet

### Gestionnaire Stock
- **Email** : stock@zrsgimt.com
- **Mot de passe** : Admin@2025
- **Permissions** : Gestion stocks et fournisseurs

### Maintenance
- **Email** : maintenance@zrsgimt.com
- **Mot de passe** : Admin@2025
- **Permissions** : Gestion maintenances et documents

### Commercial
- **Email** : commercial@zrsgimt.com
- **Mot de passe** : Admin@2025
- **Permissions** : Gestion clients, livraisons, factures

### Lecture Seule
- **Email** : lecture@zrsgimt.com
- **Mot de passe** : Admin@2025
- **Permissions** : Consultation uniquement

## 🔧 Configuration Avancée

### Paramètres de sécurité
Modifier dans `config/constants.php` :
```php
define('SESSION_LIFETIME', 1800); // 30 minutes
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_BLOCK_DURATION', 900); // 15 minutes
```

### Alertes documents
```php
define('ALERT_LEVEL_INFO', 30);      // J-30
define('ALERT_LEVEL_ATTENTION', 15); // J-15
define('ALERT_LEVEL_URGENT', 3);     // J-3
```

### Uploads
```php
define('MAX_UPLOAD_SIZE', 10485760); // 10 Mo
```

## 📊 Structure de la Base de Données

La base de données comprend 18 tables principales :
- `users` : Utilisateurs et authentification
- `roles` : Rôles et permissions
- `engins` : Parc d'engins
- `chauffeurs` : Chauffeurs et permis
- `pieces` : Catalogue pièces et carburant
- `mouvements_stock` : Historique des mouvements
- `maintenances` : Maintenances préventives et correctives
- `clients` : Clients et contacts
- `livraisons` : Livraisons et missions
- `factures` : Facturation et paiements
- `documents_engins` : Documents administratifs
- `consommations_carburant` : Suivi carburant
- `notifications` : Système de notifications
- `logs_audit` : Traçabilité complète
- Et plus...

## 🔐 Sécurité

### Mesures implémentées
- ✅ Requêtes préparées PDO (protection SQL injection)
- ✅ Protection XSS (htmlspecialchars)
- ✅ Tokens CSRF pour tous les formulaires
- ✅ Protection brute force (5 tentatives, blocage 15 min)
- ✅ Sessions sécurisées (HttpOnly, Secure en production)
- ✅ Hachage des mots de passe (bcrypt)
- ✅ Validation des uploads (type MIME, taille, extensions)
- ✅ Stockage uploads hors dossier public
- ✅ Logs d'audit complets

### Recommandations de production
1. Activer HTTPS (SSL/TLS)
2. Configurer les cookies sécurisés
3. Activer les headers de sécurité
4. Désactiver l'affichage des erreurs
5. Configurer les sauvegardes automatiques
6. Mettre en place un firewall
7. Surveiller les logs d'audit

## 🛠️ Maintenance

### Sauvegardes
```bash
# Sauvegarder la base de données
mysqldump -u root -p zrsgimt_db > backup_$(date +%Y%m%d).sql

# Sauvegarder les uploads
tar -czf uploads_backup_$(date +%Y%m%d).tar.gz uploads/
```

### Nettoyage des notifications
Les notifications sont automatiquement archivées après 90 jours.

### Logs d'audit
Conservation minimale : 5 ans (configurable)

## 📱 Responsive Design

L'application est entièrement responsive et optimisée pour :
- 🖥️ Desktop (1920×1080)
- 📱 Tablette (1024×768)
- 📱 Mobile (375×667)

## 🎨 Palette de Couleurs

- **Violet principal** : #240046 (boutons primaires, en-têtes)
- **Violet secondaire** : #ff5400 (accents, actions)
- **Vert** : #28a745 (succès, disponibilité)
- **Orange** : #ffc107 (attention, en cours)
- **Rouge** : #dc3545 (erreurs, blocages)
- **Gris** : #6c757d (textes secondaires)

## 📚 Documentation

### Structure du code
```
/app
  /Controllers  - Contrôleurs MVC
  /Models       - Modèles de données
  /Views        - Vues et templates
  /Core         - Classes de base
/public         - Point d'entrée web
  /css          - Feuilles de style
  /js           - Scripts JavaScript
  /img          - Images publiques
/config         - Configuration
/database       - Scripts SQL
/uploads        - Fichiers uploadés (hors public)
```

### Ajouter un nouveau module

1. Créer le contrôleur dans `/app/Controllers/`
2. Créer le modèle dans `/app/Models/`
3. Créer les vues dans `/app/Views/`
4. Ajouter les routes dans le menu

## 🐛 Dépannage

### Erreur de connexion à la base de données
- Vérifier `config/database.php`
- Vérifier que MySQL est démarré
- Vérifier les permissions utilisateur

### Page blanche
- Vérifier les logs PHP
- Activer le mode développement dans `config/constants.php`
- Vérifier les permissions des fichiers

### Problème de routing
- Vérifier que mod_rewrite est activé
- Vérifier le fichier `.htaccess`
- Vérifier `BASE_URL` dans `config/constants.php`

## 🤝 Support

Pour toute question ou problème :
- Email : support@zrsgimt.com
- Documentation technique complète à venir

## 📄 Licence

Propriétaire - Tous droits réservés

## ✨ Crédits

Développé pour ZRSGIMT - Système de Gestion Intégrée  
Version 1.0.0  
© 2025 ZRSGIMT. Tous droits réservés. 
