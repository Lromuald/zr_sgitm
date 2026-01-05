<?php
/**
 * Fichier de configuration des constantes globales
 * Système de Gestion Intégrée zrsgimt
 */

// Configuration de base
define('APP_NAME', 'ZRSGIMT');
define('APP_VERSION', '1.0.0');
define('APP_ENV', 'development'); // production, development

// Chemins
define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');
define('PUBLIC_PATH', BASE_PATH . '/public');
define('UPLOAD_PATH', BASE_PATH . '/uploads');
define('CONFIG_PATH', BASE_PATH . '/config');

// URL de base (à ajuster selon l'environnement)
define('BASE_URL', 'http://localhost/zr_sgitm');

// Sécurité
define('SESSION_LIFETIME', 1800); // 30 minutes en secondes
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_BLOCK_DURATION', 900); // 15 minutes en secondes
define('CSRF_TOKEN_LIFETIME', 3600); // 1 heure

// Uploads
define('MAX_UPLOAD_SIZE', 10485760); // 10 Mo en octets
define('ALLOWED_DOCUMENT_EXTENSIONS', ['pdf', 'jpg', 'jpeg', 'png']);
define('ALLOWED_IMAGE_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif']);

// Pagination
define('DEFAULT_PER_PAGE', 25);
define('PER_PAGE_OPTIONS', [10, 25, 50, 100]);

// Alertes documents (jours avant expiration)
define('ALERT_LEVEL_INFO', 30);
define('ALERT_LEVEL_ATTENTION', 15);
define('ALERT_LEVEL_URGENT', 3);
define('ALERT_LEVEL_EXPIRED', 0);

// Surconsommation carburant
define('SURCONSOMMATION_THRESHOLD', 20); // Pourcentage au-dessus de la moyenne
define('MOYENNE_MOBILE_JOURS', 30); // Période pour calcul moyenne mobile

// Statuts engins
define('ENGIN_STATUS_DISPONIBLE', 'disponible');
define('ENGIN_STATUS_EN_MISSION', 'en_mission');
define('ENGIN_STATUS_EN_MAINTENANCE', 'en_maintenance');
define('ENGIN_STATUS_HORS_SERVICE', 'hors_service');

// Statuts livraisons
define('LIVRAISON_STATUS_PLANIFIEE', 'planifiee');
define('LIVRAISON_STATUS_EN_COURS', 'en_cours');
define('LIVRAISON_STATUS_LIVREE', 'livree');
define('LIVRAISON_STATUS_ANNULEE', 'annulee');

// Statuts maintenances
define('MAINTENANCE_STATUS_PLANIFIEE', 'planifiee');
define('MAINTENANCE_STATUS_EN_COURS', 'en_cours');
define('MAINTENANCE_STATUS_TERMINEE', 'terminee');
define('MAINTENANCE_STATUS_ANNULEE', 'annulee');

// Types de maintenances
define('MAINTENANCE_TYPE_PREVENTIVE', 'preventive');
define('MAINTENANCE_TYPE_CORRECTIVE', 'corrective');

// Statuts paiements
define('PAIEMENT_STATUS_IMPAYEE', 'impayee');
define('PAIEMENT_STATUS_PARTIELLE', 'partiellement_payee');
define('PAIEMENT_STATUS_PAYEE', 'payee');

// Types de documents critiques
define('DOC_TYPE_CARTE_GRISE', 'carte_grise');
define('DOC_TYPE_ASSURANCE', 'assurance');
define('DOC_TYPE_CARTE_TRANSPORT', 'carte_transport');
define('DOC_TYPE_CARTE_STATIONNEMENT', 'carte_stationnement');
define('DOC_TYPE_VISITE_TECHNIQUE', 'visite_technique');

// Documents critiques (qui bloquent l'engin)
define('DOCUMENTS_CRITIQUES', [
    DOC_TYPE_CARTE_GRISE,
    DOC_TYPE_ASSURANCE,
    DOC_TYPE_CARTE_TRANSPORT,
    DOC_TYPE_VISITE_TECHNIQUE
]);

// Rôles utilisateurs
define('ROLE_ADMIN', 1);
define('ROLE_GESTIONNAIRE_STOCK', 2);
define('ROLE_MAINTENANCE', 3);
define('ROLE_COMMERCIAL', 4);
define('ROLE_LECTURE_SEULE', 5);

// Couleurs de l'application
define('COLOR_PRIMARY', '#240046');
define('COLOR_SECONDARY', '#ff5400');
define('COLOR_SUCCESS', '#28a745');
define('COLOR_WARNING', '#ffc107');
define('COLOR_DANGER', '#dc3545');
define('COLOR_NEUTRAL', '#6c757d');

// Configuration email (optionnel)
define('SMTP_HOST', 'smtp.example.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'noreply@zrsgimt.com');
define('SMTP_PASSWORD', '');
define('SMTP_FROM', 'noreply@zrsgimt.com');
define('SMTP_FROM_NAME', 'ZRSGIMT System');

// Timezone
date_default_timezone_set('Africa/Kinshasa');

// Langue par défaut
define('DEFAULT_LANGUAGE', 'fr');

// Archivage notifications
define('NOTIFICATIONS_ARCHIVE_DAYS', 90);

// Conservation logs audit
define('AUDIT_RETENTION_YEARS', 5);
