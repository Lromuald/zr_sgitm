<?php
namespace App\Core;

/**
 * Autoloader personnalisé pour charger automatiquement les classes
 */
class Autoloader
{
    /**
     * Charge automatiquement les classes
     * 
     * @param string $class Nom complet de la classe avec namespace
     */
    public static function load($class)
    {
        // Conversion du namespace en chemin de fichier
        $class = str_replace('App\\', '', $class);
        $class = str_replace('\\', '/', $class);
        
        $file = APP_PATH . '/' . $class . '.php';
        
        if (file_exists($file)) {
            require_once $file;
        }
    }
}
