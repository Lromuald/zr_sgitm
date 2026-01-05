<?php
/**
 * Point d'entrée principal de l'application
 * Système de Gestion Intégrée zrsgimt
 */

// Démarrage de la session sécurisée
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 0); // Mettre à 1 en production avec HTTPS
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_samesite', 'Strict');

session_start();

// Chargement des configurations
require_once __DIR__ . '/../config/constants.php';

// Charger la configuration database si elle existe
if (file_exists(__DIR__ . '/../config/database.php')) {
    require_once __DIR__ . '/../config/database.php';
} else {
    // Utiliser le fichier exemple si database.php n'existe pas
    require_once __DIR__ . '/../config/database.example.php';
}

// Autoloader pour les classes
require_once __DIR__ . '/../app/Core/Autoloader.php';
spl_autoload_register(['App\Core\Autoloader', 'load']);

// Gestion des erreurs en développement
if (APP_ENV === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Initialisation du router
$router = new \App\Core\Router();

// Lancement de l'application
try {
    $router->dispatch();
} catch (Exception $e) {
    // Log de l'erreur
    error_log($e->getMessage());
    
    if (APP_ENV === 'development') {
        echo '<h1>Erreur</h1>';
        echo '<p>' . htmlspecialchars($e->getMessage()) . '</p>';
        echo '<pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
    } else {
        // Rediriger vers une page d'erreur en production
        header('HTTP/1.1 500 Internal Server Error');
        include __DIR__ . '/errors/500.html';
    }
}
