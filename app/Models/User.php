<?php
namespace App\Models;

use App\Core\Model;

/**
 * Modèle User
 * Gestion des utilisateurs
 */
class User extends Model
{
    protected $table = 'users';

    /**
     * Trouve un utilisateur par email
     * 
     * @param string $email
     * @return array|false
     */
    public function findByEmail($email)
    {
        return $this->findOne(['email' => $email]);
    }

    /**
     * Vérifie les credentials de connexion
     * 
     * @param string $email
     * @param string $password
     * @return array|false
     */
    public function authenticate($email, $password)
    {
        $user = $this->findByEmail($email);
        
        if (!$user) {
            return false;
        }
        
        if (!password_verify($password, $user['password'])) {
            return false;
        }
        
        return $user;
    }

    /**
     * Vérifie si l'utilisateur est bloqué
     * 
     * @param int $userId
     * @return bool
     */
    public function isBlocked($userId)
    {
        $user = $this->findById($userId);
        
        if (!$user) {
            return true;
        }
        
        if (!$user['bloque_jusqua']) {
            return false;
        }
        
        return strtotime($user['bloque_jusqua']) > time();
    }

    /**
     * Incrémente les tentatives de connexion
     * 
     * @param int $userId
     */
    public function incrementLoginAttempts($userId)
    {
        $sql = "UPDATE {$this->table} 
                SET tentatives_connexion = tentatives_connexion + 1,
                    derniere_tentative_connexion = NOW()
                WHERE id = :id";
        
        $this->db->query($sql, [':id' => $userId]);
    }

    /**
     * Bloque l'utilisateur temporairement
     * 
     * @param int $userId
     * @param int $minutes
     */
    public function blockUser($userId, $minutes = 15)
    {
        $blockedUntil = date('Y-m-d H:i:s', time() + ($minutes * 60));
        
        $this->update($userId, [
            'bloque_jusqua' => $blockedUntil
        ]);
    }

    /**
     * Réinitialise les tentatives de connexion
     * 
     * @param int $userId
     */
    public function resetLoginAttempts($userId)
    {
        $this->update($userId, [
            'tentatives_connexion' => 0,
            'bloque_jusqua' => null
        ]);
    }

    /**
     * Met à jour les informations de dernière connexion
     * 
     * @param int $userId
     * @param string $ipAddress
     * @param string $userAgent
     */
    public function updateLastLogin($userId, $ipAddress, $userAgent)
    {
        $this->update($userId, [
            'date_derniere_connexion' => date('Y-m-d H:i:s'),
            'ip_derniere_connexion' => $ipAddress,
            'user_agent' => $userAgent
        ]);
    }

    /**
     * Récupère les informations complètes de l'utilisateur avec son rôle
     * 
     * @param int $userId
     * @return array|false
     */
    public function getUserWithRole($userId)
    {
        $sql = "SELECT u.*, r.nom as role_nom, r.permissions as role_permissions
                FROM {$this->table} u
                JOIN roles r ON u.role_id = r.id
                WHERE u.id = :id";
        
        return $this->db->fetch($sql, [':id' => $userId]);
    }

    /**
     * Enregistre une connexion dans l'historique
     * 
     * @param int $userId
     * @param string $ipAddress
     * @param string $userAgent
     * @param bool $success
     */
    public function logConnection($userId, $ipAddress, $userAgent, $success = true)
    {
        // Parser le user agent pour extraire le navigateur et l'OS
        $browser = $this->getBrowserFromUserAgent($userAgent);
        $os = $this->getOSFromUserAgent($userAgent);
        
        $sql = "INSERT INTO historique_connexions 
                (user_id, date_connexion, ip_address, user_agent, navigateur, systeme_exploitation, succes)
                VALUES (:user_id, NOW(), :ip_address, :user_agent, :navigateur, :os, :succes)";
        
        $this->db->query($sql, [
            ':user_id' => $userId,
            ':ip_address' => $ipAddress,
            ':user_agent' => $userAgent,
            ':navigateur' => $browser,
            ':os' => $os,
            ':succes' => $success ? 1 : 0
        ]);
    }

    /**
     * Récupère les dernières connexions d'un utilisateur
     * 
     * @param int $userId
     * @param int $limit
     * @return array
     */
    public function getRecentConnections($userId, $limit = 50)
    {
        $sql = "SELECT * FROM historique_connexions
                WHERE user_id = :user_id
                ORDER BY date_connexion DESC
                LIMIT :limit";
        
        return $this->db->fetchAll($sql, [
            ':user_id' => $userId,
            ':limit' => $limit
        ]);
    }

    /**
     * Extrait le nom du navigateur depuis le User Agent
     * 
     * @param string $userAgent
     * @return string
     */
    private function getBrowserFromUserAgent($userAgent)
    {
        if (preg_match('/MSIE/i', $userAgent) || preg_match('/Trident/i', $userAgent)) {
            return 'Internet Explorer';
        } elseif (preg_match('/Firefox/i', $userAgent)) {
            return 'Mozilla Firefox';
        } elseif (preg_match('/Chrome/i', $userAgent)) {
            return 'Google Chrome';
        } elseif (preg_match('/Safari/i', $userAgent)) {
            return 'Safari';
        } elseif (preg_match('/Opera|OPR/i', $userAgent)) {
            return 'Opera';
        } elseif (preg_match('/Edge/i', $userAgent)) {
            return 'Microsoft Edge';
        }
        
        return 'Inconnu';
    }

    /**
     * Extrait le système d'exploitation depuis le User Agent
     * 
     * @param string $userAgent
     * @return string
     */
    private function getOSFromUserAgent($userAgent)
    {
        if (preg_match('/windows|win32/i', $userAgent)) {
            return 'Windows';
        } elseif (preg_match('/macintosh|mac os x/i', $userAgent)) {
            return 'macOS';
        } elseif (preg_match('/linux/i', $userAgent)) {
            return 'Linux';
        } elseif (preg_match('/ubuntu/i', $userAgent)) {
            return 'Ubuntu';
        } elseif (preg_match('/android/i', $userAgent)) {
            return 'Android';
        } elseif (preg_match('/iphone|ipad|ipod/i', $userAgent)) {
            return 'iOS';
        }
        
        return 'Inconnu';
    }

    /**
     * Crée un nouveau hash de mot de passe
     * 
     * @param string $password
     * @return string
     */
    public function hashPassword($password)
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    /**
     * Récupère tous les utilisateurs actifs
     * 
     * @return array
     */
    public function getActiveUsers()
    {
        $sql = "SELECT u.*, r.nom as role_nom
                FROM {$this->table} u
                JOIN roles r ON u.role_id = r.id
                WHERE u.actif = 1
                ORDER BY u.nom, u.prenom";
        
        return $this->db->fetchAll($sql);
    }
}
