<?php
namespace App\Core;

/**
 * Contrôleur de base
 * Toutes les contrôleurs héritent de cette classe
 */
class Controller
{
    protected $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Charge un modèle
     * 
     * @param string $model Nom du modèle
     * @return object Instance du modèle
     */
    protected function model($model)
    {
        $modelClass = "App\\Models\\" . $model;
        if (class_exists($modelClass)) {
            return new $modelClass();
        }
        throw new \Exception("Modèle $model introuvable");
    }

    /**
     * Charge une vue
     * 
     * @param string $view Chemin de la vue
     * @param array $data Données à passer à la vue
     */
    protected function view($view, $data = [])
    {
        // Extraire les données pour les rendre accessibles dans la vue
        extract($data);
        
        $viewFile = APP_PATH . '/Views/' . $view . '.php';
        
        if (file_exists($viewFile)) {
            require_once $viewFile;
        } else {
            throw new \Exception("Vue $view introuvable");
        }
    }

    /**
     * Redirige vers une URL
     * 
     * @param string $path Chemin de redirection
     */
    protected function redirect($path)
    {
        header('Location: ' . BASE_URL . '/' . ltrim($path, '/'));
        exit;
    }

    /**
     * Retourne une réponse JSON
     * 
     * @param mixed $data Données à retourner
     * @param int $statusCode Code HTTP
     */
    protected function json($data, $statusCode = 200)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Vérifie si l'utilisateur est connecté
     * 
     * @return bool
     */
    protected function isLoggedIn()
    {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }

    /**
     * Vérifie si l'utilisateur a un rôle spécifique
     * 
     * @param int|array $roles ID(s) du/des rôle(s)
     * @return bool
     */
    protected function hasRole($roles)
    {
        if (!$this->isLoggedIn()) {
            return false;
        }
        
        if (!is_array($roles)) {
            $roles = [$roles];
        }
        
        return in_array($_SESSION['role_id'], $roles);
    }

    /**
     * Nécessite une authentification
     * Redirige vers login si non connecté
     */
    protected function requireAuth()
    {
        if (!$this->isLoggedIn()) {
            $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
            $this->redirect('/auth/login');
        }
        
        // Vérifier le timeout de session
        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > SESSION_LIFETIME)) {
            session_destroy();
            $this->redirect('/auth/login?timeout=1');
        }
        
        $_SESSION['last_activity'] = time();
    }

    /**
     * Nécessite un rôle spécifique
     * 
     * @param int|array $roles ID(s) du/des rôle(s)
     */
    protected function requireRole($roles)
    {
        $this->requireAuth();
        
        if (!$this->hasRole($roles)) {
            $this->redirect('/dashboard/access-denied');
        }
    }

    /**
     * Génère un token CSRF
     * 
     * @return string
     */
    protected function generateCsrfToken()
    {
        if (!isset($_SESSION['csrf_token']) || !isset($_SESSION['csrf_token_time']) 
            || (time() - $_SESSION['csrf_token_time']) > CSRF_TOKEN_LIFETIME) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            $_SESSION['csrf_token_time'] = time();
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * Vérifie un token CSRF
     * 
     * @param string $token Token à vérifier
     * @return bool
     */
    protected function verifyCsrfToken($token)
    {
        if (!isset($_SESSION['csrf_token']) || !isset($_SESSION['csrf_token_time'])) {
            return false;
        }
        
        if ((time() - $_SESSION['csrf_token_time']) > CSRF_TOKEN_LIFETIME) {
            return false;
        }
        
        return hash_equals($_SESSION['csrf_token'], $token);
    }

    /**
     * Nettoie les données d'entrée
     * 
     * @param mixed $data Données à nettoyer
     * @return mixed
     */
    protected function sanitize($data)
    {
        if (is_array($data)) {
            return array_map([$this, 'sanitize'], $data);
        }
        return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Valide un email
     * 
     * @param string $email
     * @return bool
     */
    protected function validateEmail($email)
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
}
