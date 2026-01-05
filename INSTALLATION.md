# ZRSGIMT - Guide d'Installation et de Déploiement

## 📋 Table des Matières
1. [Prérequis](#prérequis)
2. [Installation en Développement](#installation-en-développement)
3. [Configuration](#configuration)
4. [Déploiement en Production](#déploiement-en-production)
5. [Maintenance](#maintenance)
6. [Dépannage](#dépannage)

---

## 🔧 Prérequis

### Serveur
- **OS** : Linux (Ubuntu 20.04+, Debian 10+, CentOS 8+) ou Windows Server
- **Serveur Web** : Apache 2.4+ ou Nginx 1.18+
- **PHP** : 8.2 ou supérieur
- **Base de données** : MySQL 8.0+ ou MariaDB 10.5+
- **Espace disque** : Minimum 2 Go recommandé

### Extensions PHP Requises
```bash
php8.2-cli
php8.2-fpm
php8.2-mysql
php8.2-mbstring
php8.2-xml
php8.2-json
php8.2-curl
php8.2-gd
php8.2-zip
```

### Outils
- Git
- Composer (optionnel, pour futures dépendances)
- MySQL client

---

## 🚀 Installation en Développement

### Étape 1 : Installation des Dépendances

#### Sur Ubuntu/Debian
```bash
# Mettre à jour les paquets
sudo apt update && sudo apt upgrade -y

# Installer Apache
sudo apt install apache2 -y

# Installer PHP 8.2 et extensions
sudo apt install php8.2 php8.2-cli php8.2-fpm php8.2-mysql \
  php8.2-mbstring php8.2-xml php8.2-json php8.2-curl \
  php8.2-gd php8.2-zip -y

# Installer MySQL
sudo apt install mysql-server -y

# Activer les modules Apache nécessaires
sudo a2enmod rewrite
sudo a2enmod php8.2
sudo systemctl restart apache2

# Sécuriser MySQL
sudo mysql_secure_installation
```

#### Sur Windows
1. Télécharger et installer XAMPP (https://www.apachefriends.org)
2. Ou utiliser WampServer (https://www.wampserver.com)
3. S'assurer que PHP 8.2+ est inclus

### Étape 2 : Cloner le Projet

```bash
# Naviguer vers le répertoire web
cd /var/www/html  # Sur Linux
# ou
cd C:\xampp\htdocs  # Sur Windows avec XAMPP

# Cloner le repository
git clone https://github.com/Lromuald/zr_sgitm.git
cd zr_sgitm

# Définir les permissions (Linux uniquement)
sudo chown -R www-data:www-data .
sudo chmod -R 755 .
sudo chmod -R 775 uploads/
```

### Étape 3 : Configuration de la Base de Données

#### Créer la base de données

```bash
# Se connecter à MySQL
mysql -u root -p

# Exécuter dans MySQL
mysql> CREATE DATABASE zrsgimt_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
mysql> CREATE USER 'zrsgimt_user'@'localhost' IDENTIFIED BY 'VotreMotDePasse123!';
mysql> GRANT ALL PRIVILEGES ON zrsgimt_db.* TO 'zrsgimt_user'@'localhost';
mysql> FLUSH PRIVILEGES;
mysql> exit;

# Importer le schéma et les données
mysql -u zrsgimt_user -p zrsgimt_db < database/schema.sql
mysql -u zrsgimt_user -p zrsgimt_db < database/demo_data.sql
```

#### Vérifier l'importation

```bash
mysql -u zrsgimt_user -p zrsgimt_db -e "SHOW TABLES;"
```

Vous devriez voir 18 tables.

### Étape 4 : Configuration de l'Application

#### Copier et configurer database.php

```bash
# Copier le fichier exemple
cp config/database.example.php config/database.php

# Éditer le fichier
nano config/database.php  # ou vi, vim, etc.
```

Modifier avec vos paramètres :

```php
<?php
define('DB_HOST', 'localhost');
define('DB_NAME', 'zrsgimt_db');
define('DB_USER', 'zrsgimt_user');
define('DB_PASS', 'VotreMotDePasse123!');
define('DB_CHARSET', 'utf8mb4');

define('DB_OPTIONS', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
]);
```

#### Configurer l'URL de base

Éditer `config/constants.php` :

```php
// Changer cette ligne selon votre configuration
define('BASE_URL', 'http://localhost/zr_sgitm');
// Ou pour XAMPP sur Windows
define('BASE_URL', 'http://localhost/zr_sgitm');
// Ou pour un domaine personnalisé
define('BASE_URL', 'http://zrsgimt.local');
```

### Étape 5 : Configuration Apache

#### Option A : Sous-répertoire (Plus simple)

Le projet fonctionne déjà si placé dans `/var/www/html/zr_sgitm`.  
Accessible via : `http://localhost/zr_sgitm`

#### Option B : VirtualHost (Recommandé pour développement)

```bash
# Créer le fichier de configuration
sudo nano /etc/apache2/sites-available/zrsgimt.conf
```

Contenu du fichier :

```apache
<VirtualHost *:80>
    ServerName zrsgimt.local
    ServerAlias www.zrsgimt.local
    
    DocumentRoot /var/www/html/zr_sgitm/public
    
    <Directory /var/www/html/zr_sgitm/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    # Bloquer l'accès aux dossiers sensibles
    <Directory /var/www/html/zr_sgitm/config>
        Require all denied
    </Directory>
    
    <Directory /var/www/html/zr_sgitm/database>
        Require all denied
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/zrsgimt-error.log
    CustomLog ${APACHE_LOG_DIR}/zrsgimt-access.log combined
</VirtualHost>
```

Activer le site :

```bash
# Activer le site
sudo a2ensite zrsgimt.conf

# Recharger Apache
sudo systemctl reload apache2

# Ajouter l'entrée dans /etc/hosts
echo "127.0.0.1 zrsgimt.local" | sudo tee -a /etc/hosts
```

Accéder via : `http://zrsgimt.local`

### Étape 6 : Tester l'Installation

1. Ouvrir un navigateur
2. Accéder à l'URL configurée
3. Vous devriez voir la page de connexion

**Comptes de test :**
- Admin : `admin@zrsgimt.com` / `Admin@2025`
- Stock : `stock@zrsgimt.com` / `Admin@2025`
- Maintenance : `maintenance@zrsgimt.com` / `Admin@2025`
- Commercial : `commercial@zrsgimt.com` / `Admin@2025`

---

## ⚙️ Configuration

### Configuration PHP (php.ini)

Paramètres recommandés :

```ini
; Taille maximale des uploads
upload_max_filesize = 10M
post_max_size = 12M

; Mémoire
memory_limit = 256M

; Temps d'exécution
max_execution_time = 300

; Timezone
date.timezone = Africa/Kinshasa

; Désactiver l'affichage des erreurs en production
display_errors = Off
display_startup_errors = Off
log_errors = On
error_log = /var/log/php/error.log

; Sessions
session.cookie_httponly = 1
session.cookie_secure = 1  ; Activer en HTTPS
session.use_only_cookies = 1
session.gc_maxlifetime = 1800
```

Redémarrer Apache après modifications :
```bash
sudo systemctl restart apache2
```

### Configuration MySQL

Optimisations recommandées dans `/etc/mysql/my.cnf` :

```ini
[mysqld]
# Encodage
character-set-server = utf8mb4
collation-server = utf8mb4_unicode_ci

# Performance
innodb_buffer_pool_size = 256M
innodb_log_file_size = 64M
max_connections = 150

# Logs
slow_query_log = 1
slow_query_log_file = /var/log/mysql/slow-query.log
long_query_time = 2
```

Redémarrer MySQL :
```bash
sudo systemctl restart mysql
```

---

## 🌐 Déploiement en Production

### Pré-déploiement

#### 1. Sécurité

**Changer les mots de passe :**
```sql
-- Se connecter à MySQL
mysql -u root -p

USE zrsgimt_db;

-- Changer tous les mots de passe utilisateurs
UPDATE users SET password = '$2y$10$VotreNouveauHashIci' WHERE email = 'admin@zrsgimt.com';
-- Répéter pour tous les utilisateurs
```

Générer un hash bcrypt en PHP :
```php
<?php
echo password_hash('VotreNouveauMotDePasse', PASSWORD_DEFAULT);
?>
```

**Mettre à jour config/constants.php :**
```php
// Passer en mode production
define('APP_ENV', 'production');

// URL de production
define('BASE_URL', 'https://votre-domaine.com');
```

**Activer HTTPS obligatoire dans .htaccess :**
```apache
# Redirection HTTP vers HTTPS
RewriteEngine On
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

#### 2. Optimisations

**Désactiver les données de démo :**
```sql
-- Ne pas exécuter demo_data.sql en production
-- Ou supprimer les données de test
DELETE FROM livraisons WHERE id > 0;
DELETE FROM factures WHERE id > 0;
-- etc.
```

**Nettoyer les logs :**
```bash
# Créer un cron pour nettoyer les vieux logs
sudo crontab -e

# Ajouter
0 2 * * * find /var/log/apache2/ -name "*.log" -mtime +30 -delete
```

### Déploiement sur Serveur Linux

#### 1. Configuration Firewall

```bash
# UFW (Ubuntu)
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw allow 22/tcp
sudo ufw enable

# Ou FirewallD (CentOS)
sudo firewall-cmd --permanent --add-service=http
sudo firewall-cmd --permanent --add-service=https
sudo firewall-cmd --reload
```

#### 2. Installation SSL/TLS (Let's Encrypt)

```bash
# Installer Certbot
sudo apt install certbot python3-certbot-apache -y

# Obtenir un certificat
sudo certbot --apache -d votre-domaine.com -d www.votre-domaine.com

# Test renouvellement automatique
sudo certbot renew --dry-run
```

#### 3. Renforcement Apache

Éditer `/etc/apache2/conf-available/security.conf` :

```apache
# Cacher la version Apache
ServerTokens Prod
ServerSignature Off

# Headers de sécurité
Header always set X-Content-Type-Options "nosniff"
Header always set X-Frame-Options "SAMEORIGIN"
Header always set X-XSS-Protection "1; mode=block"
Header always set Referrer-Policy "strict-origin-when-cross-origin"
Header always set Content-Security-Policy "default-src 'self'; script-src 'self' 'unsafe-inline' cdn.jsdelivr.net code.jquery.com; style-src 'self' 'unsafe-inline' cdn.jsdelivr.net; img-src 'self' data:; font-src 'self' cdn.jsdelivr.net;"
```

Activer les headers :
```bash
sudo a2enmod headers
sudo systemctl restart apache2
```

#### 4. Backups Automatiques

Créer un script de backup `/usr/local/bin/backup-zrsgimt.sh` :

```bash
#!/bin/bash

# Configuration
BACKUP_DIR="/backups/zrsgimt"
DB_NAME="zrsgimt_db"
DB_USER="zrsgimt_user"
DB_PASS="VotreMotDePasse"
APP_DIR="/var/www/html/zr_sgitm"
DATE=$(date +%Y%m%d_%H%M%S)

# Créer le répertoire de backup
mkdir -p $BACKUP_DIR

# Backup base de données
mysqldump -u $DB_USER -p$DB_PASS $DB_NAME | gzip > $BACKUP_DIR/db_$DATE.sql.gz

# Backup fichiers uploads
tar -czf $BACKUP_DIR/uploads_$DATE.tar.gz $APP_DIR/uploads/

# Garder seulement les 30 derniers backups
find $BACKUP_DIR -name "db_*.sql.gz" -mtime +30 -delete
find $BACKUP_DIR -name "uploads_*.tar.gz" -mtime +30 -delete

echo "Backup completed: $DATE"
```

Rendre exécutable et ajouter au cron :
```bash
sudo chmod +x /usr/local/bin/backup-zrsgimt.sh

# Cron quotidien à 2h du matin
sudo crontab -e
0 2 * * * /usr/local/bin/backup-zrsgimt.sh >> /var/log/zrsgimt-backup.log 2>&1
```

#### 5. Monitoring

Installer et configurer un outil de monitoring (optionnel) :

```bash
# Nagios, Zabbix, ou simple script de vérification
# Exemple script simple
cat > /usr/local/bin/check-zrsgimt.sh << 'EOF'
#!/bin/bash
if ! curl -s -o /dev/null -w "%{http_code}" https://votre-domaine.com | grep -q "200\|302"; then
    echo "ZRSGIMT is DOWN!" | mail -s "ALERT: ZRSGIMT DOWN" admin@example.com
fi
EOF

sudo chmod +x /usr/local/bin/check-zrsgimt.sh

# Vérifier toutes les 5 minutes
sudo crontab -e
*/5 * * * * /usr/local/bin/check-zrsgimt.sh
```

---

## 🛠️ Maintenance

### Backups Manuels

#### Base de données
```bash
# Backup
mysqldump -u zrsgimt_user -p zrsgimt_db > backup_$(date +%Y%m%d).sql

# Restore
mysql -u zrsgimt_user -p zrsgimt_db < backup_20250105.sql
```

#### Fichiers
```bash
# Backup uploads
tar -czf uploads_backup_$(date +%Y%m%d).tar.gz uploads/

# Restore
tar -xzf uploads_backup_20250105.tar.gz
```

### Nettoyage

#### Nettoyer les vieux logs d'audit (>5 ans)
```sql
DELETE FROM logs_audit WHERE date_action < DATE_SUB(NOW(), INTERVAL 5 YEAR);
```

#### Nettoyer les notifications lues (>90 jours)
```sql
DELETE FROM notifications WHERE lu = 1 AND date_creation < DATE_SUB(NOW(), INTERVAL 90 DAY);
```

#### Nettoyer l'historique des connexions
```sql
-- Garder seulement les 50 dernières par utilisateur
-- Script à exécuter périodiquement
```

### Mise à Jour

```bash
# Sauvegarder avant mise à jour
mysqldump -u zrsgimt_user -p zrsgimt_db > backup_avant_maj.sql

# Récupérer les dernières modifications
git pull origin main

# Appliquer les migrations SQL si nécessaire
mysql -u zrsgimt_user -p zrsgimt_db < database/migrations/20250105_update.sql

# Vider le cache PHP si nécessaire
sudo systemctl restart php8.2-fpm
```

---

## 🔍 Dépannage

### Problème : Page blanche

**Causes possibles :**
1. Erreur PHP non affichée
2. Permissions fichiers incorrectes
3. Problème de base de données

**Solutions :**
```bash
# Activer l'affichage des erreurs temporairement
# Dans config/constants.php
define('APP_ENV', 'development');

# Vérifier les logs Apache
sudo tail -f /var/log/apache2/error.log

# Vérifier les permissions
sudo chown -R www-data:www-data /var/www/html/zr_sgitm
sudo chmod -R 755 /var/www/html/zr_sgitm
```

### Problème : Erreur de connexion à la base de données

**Vérifier :**
```bash
# MySQL est démarré
sudo systemctl status mysql

# Tester la connexion
mysql -u zrsgimt_user -p -h localhost zrsgimt_db

# Vérifier les paramètres dans config/database.php
```

### Problème : Erreur 404 sur toutes les pages

**Cause :** mod_rewrite non activé

**Solution :**
```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```

### Problème : Upload de fichiers échoue

**Vérifier :**
```bash
# Permissions du dossier uploads
sudo chmod -R 775 uploads/
sudo chown -R www-data:www-data uploads/

# Taille max dans php.ini
php -i | grep upload_max_filesize
php -i | grep post_max_size
```

### Problème : Session expire trop vite

**Modifier dans config/constants.php :**
```php
define('SESSION_LIFETIME', 3600); // 1 heure au lieu de 30 min
```

---

## 📞 Support

Pour toute question ou problème :
- **Email** : support@zrsgimt.com
- **Documentation** : https://github.com/Lromuald/zr_sgitm
- **Issues** : https://github.com/Lromuald/zr_sgitm/issues

---

**Document mis à jour le : 5 janvier 2025**  
**Version : 1.0**
