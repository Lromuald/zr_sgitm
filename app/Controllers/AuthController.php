<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\User;

/**
 * Contrôleur d'authentification
 * Gestion de la connexion, déconnexion et session
 */
class AuthController extends Controller
{
    private $userModel;

    public function __construct()
    {
        parent::__construct();
        $this->userModel = $this->model('User');
    }

    /**
     * Page de connexion
     */
    public function login()
    {
        // Si déjà connecté, rediriger vers le dashboard
        if ($this->isLoggedIn()) {
            $this->redirect('/dashboard');
        }

        // Traitement du formulaire de connexion
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->processLogin();
            return;
        }

        // Afficher le formulaire
        $data = [
            'csrf_token' => $this->generateCsrfToken(),
            'error' => $_GET['error'] ?? null,
            'message' => $_GET['message'] ?? null,
            'timeout' => $_GET['timeout'] ?? null
        ];

        $this->view('auth/login', $data);
    }

    /**
     * Traitement de la connexion
     */
    private function processLogin()
    {
        // Vérifier le token CSRF
        if (!isset($_POST['csrf_token']) || !$this->verifyCsrfToken($_POST['csrf_token'])) {
            $this->redirect('/auth/login?error=csrf');
            return;
        }

        // Récupérer et valider les données
        $email = $this->sanitize($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            $this->redirect('/auth/login?error=empty_fields');
            return;
        }

        // Vérifier que l'email est valide
        if (!$this->validateEmail($email)) {
            $this->redirect('/auth/login?error=invalid_email');
            return;
        }

        // Tenter l'authentification
        $user = $this->userModel->findByEmail($email);

        if (!$user) {
            $this->redirect('/auth/login?error=invalid_credentials');
            return;
        }

        // Vérifier si l'utilisateur est bloqué
        if ($this->userModel->isBlocked($user['id'])) {
            $this->redirect('/auth/login?error=blocked');
            return;
        }

        // Vérifier si l'utilisateur est actif
        if (!$user['actif']) {
            $this->redirect('/auth/login?error=inactive');
            return;
        }

        // Vérifier les tentatives de connexion
        if ($user['tentatives_connexion'] >= MAX_LOGIN_ATTEMPTS) {
            // Bloquer l'utilisateur
            $this->userModel->blockUser($user['id'], LOGIN_BLOCK_DURATION / 60);
            $this->redirect('/auth/login?error=too_many_attempts');
            return;
        }

        // Vérifier le mot de passe
        if (!password_verify($password, $user['password'])) {
            // Incrémenter les tentatives
            $this->userModel->incrementLoginAttempts($user['id']);
            
            // Enregistrer la tentative échouée
            $this->userModel->logConnection(
                $user['id'],
                $_SERVER['REMOTE_ADDR'],
                $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
                false
            );

            // Logger l'audit
            $this->logAudit($user['id'], 'LOGIN', null, null, 'Tentative de connexion échouée');

            $attemptsLeft = MAX_LOGIN_ATTEMPTS - ($user['tentatives_connexion'] + 1);
            $this->redirect('/auth/login?error=invalid_credentials&attempts_left=' . $attemptsLeft);
            return;
        }

        // Connexion réussie
        $this->successfulLogin($user);
    }

    /**
     * Gestion de la connexion réussie
     * 
     * @param array $user
     */
    private function successfulLogin($user)
    {
        // Réinitialiser les tentatives de connexion
        $this->userModel->resetLoginAttempts($user['id']);

        // Mettre à jour les informations de dernière connexion
        $this->userModel->updateLastLogin(
            $user['id'],
            $_SERVER['REMOTE_ADDR'],
            $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
        );

        // Enregistrer dans l'historique
        $this->userModel->logConnection(
            $user['id'],
            $_SERVER['REMOTE_ADDR'],
            $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
            true
        );

        // Logger l'audit
        $this->logAudit($user['id'], 'LOGIN', null, null, 'Connexion réussie');

        // Régénérer l'ID de session pour prévenir le session hijacking
        session_regenerate_id(true);

        // Créer la session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['nom'] = $user['nom'];
        $_SESSION['prenom'] = $user['prenom'];
        $_SESSION['role_id'] = $user['role_id'];
        $_SESSION['langue'] = $user['langue'];
        $_SESSION['photo'] = $user['photo'];
        $_SESSION['last_activity'] = time();

        // Récupérer les permissions du rôle
        $userWithRole = $this->userModel->getUserWithRole($user['id']);
        if ($userWithRole && $userWithRole['role_permissions']) {
            $_SESSION['permissions'] = json_decode($userWithRole['role_permissions'], true);
            $_SESSION['role_nom'] = $userWithRole['role_nom'];
        }

        // Rediriger vers la page demandée ou le dashboard
        $redirectTo = $_SESSION['redirect_after_login'] ?? '/dashboard';
        unset($_SESSION['redirect_after_login']);
        
        $this->redirect($redirectTo);
    }

    /**
     * Déconnexion
     */
    public function logout()
    {
        if ($this->isLoggedIn()) {
            // Logger l'audit
            $this->logAudit($_SESSION['user_id'], 'LOGOUT', null, null, 'Déconnexion');
        }

        // Détruire la session
        session_unset();
        session_destroy();

        // Rediriger vers la page de connexion
        $this->redirect('/auth/login?message=logged_out');
    }

    /**
     * Enregistre une action dans les logs d'audit
     * 
     * @param int $userId
     * @param string $action
     * @param string $tableName
     * @param int $recordId
     * @param string $description
     */
    private function logAudit($userId, $action, $tableName = null, $recordId = null, $description = null)
    {
        $sql = "INSERT INTO logs_audit 
                (user_id, action, table_name, record_id, description, ip_address, user_agent, date_action)
                VALUES (:user_id, :action, :table_name, :record_id, :description, :ip_address, :user_agent, NOW())";
        
        $this->db->query($sql, [
            ':user_id' => $userId,
            ':action' => $action,
            ':table_name' => $tableName,
            ':record_id' => $recordId,
            ':description' => $description,
            ':ip_address' => $_SERVER['REMOTE_ADDR'],
            ':user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
        ]);
    }
}
