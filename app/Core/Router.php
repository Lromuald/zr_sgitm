<?php
namespace App\Core;

/**
 * Router de l'application
 * Gère le routing des URLs vers les contrôleurs
 */
class Router
{
    private $controller = 'Dashboard';
    private $method = 'index';
    private $params = [];

    public function __construct()
    {
        $url = $this->parseUrl();
        
        // Si pas d'URL, rediriger vers la page par défaut
        if (empty($url)) {
            // Vérifier si l'utilisateur est connecté
            if (isset($_SESSION['user_id'])) {
                $this->controller = 'Dashboard';
            } else {
                $this->controller = 'Auth';
                $this->method = 'login';
            }
        } else {
            // Vérifier si le contrôleur existe
            $controllerClass = 'App\\Controllers\\' . ucfirst($url[0]) . 'Controller';
            
            if (class_exists($controllerClass)) {
                $this->controller = ucfirst($url[0]);
                unset($url[0]);
            }
        }
        
        // Instancier le contrôleur
        $controllerClass = 'App\\Controllers\\' . $this->controller . 'Controller';
        
        if (!class_exists($controllerClass)) {
            $this->show404();
            return;
        }
        
        $controllerInstance = new $controllerClass();
        
        // Vérifier la méthode
        if (isset($url[1])) {
            $method = $url[1];
            if (method_exists($controllerInstance, $method)) {
                $this->method = $method;
                unset($url[1]);
            }
        }
        
        // Paramètres
        $this->params = $url ? array_values($url) : [];
    }

    /**
     * Parse l'URL
     * 
     * @return array
     */
    private function parseUrl()
    {
        if (isset($_GET['url'])) {
            $url = rtrim($_GET['url'], '/');
            $url = filter_var($url, FILTER_SANITIZE_URL);
            $url = explode('/', $url);
            return $url;
        }
        return [];
    }

    /**
     * Dispatche la requête vers le contrôleur approprié
     */
    public function dispatch()
    {
        $controllerClass = 'App\\Controllers\\' . $this->controller . 'Controller';
        $controller = new $controllerClass();
        
        if (!method_exists($controller, $this->method)) {
            $this->show404();
            return;
        }
        
        call_user_func_array([$controller, $this->method], $this->params);
    }

    /**
     * Affiche une page 404
     */
    private function show404()
    {
        http_response_code(404);
        require_once APP_PATH . '/Views/errors/404.php';
        exit;
    }
}
