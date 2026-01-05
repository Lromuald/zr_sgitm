<?php
namespace App\Core;

use PDO;
use PDOException;

/**
 * Classe de gestion de la base de données
 * Singleton pattern pour une seule connexion
 */
class Database
{
    private static $instance = null;
    private $connection;

    /**
     * Constructeur privé pour empêcher l'instanciation directe
     */
    private function __construct()
    {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $this->connection = new PDO($dsn, DB_USER, DB_PASS, DB_OPTIONS);
        } catch (PDOException $e) {
            error_log("Erreur de connexion à la base de données: " . $e->getMessage());
            throw new \Exception("Impossible de se connecter à la base de données");
        }
    }

    /**
     * Empêcher le clonage de l'instance
     */
    private function __clone() {}

    /**
     * Empêcher la désérialisation
     */
    public function __wakeup()
    {
        throw new \Exception("Cannot unserialize singleton");
    }

    /**
     * Retourne l'instance unique de la base de données
     * 
     * @return Database
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Retourne la connexion PDO
     * 
     * @return PDO
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * Prépare et exécute une requête avec des paramètres
     * 
     * @param string $sql Requête SQL
     * @param array $params Paramètres de la requête
     * @return \PDOStatement
     */
    public function query($sql, $params = [])
    {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            error_log("Erreur de requête SQL: " . $e->getMessage());
            error_log("Requête: " . $sql);
            throw new \Exception("Erreur lors de l'exécution de la requête");
        }
    }

    /**
     * Récupère toutes les lignes d'une requête
     * 
     * @param string $sql Requête SQL
     * @param array $params Paramètres
     * @return array
     */
    public function fetchAll($sql, $params = [])
    {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll();
    }

    /**
     * Récupère une seule ligne
     * 
     * @param string $sql Requête SQL
     * @param array $params Paramètres
     * @return array|false
     */
    public function fetch($sql, $params = [])
    {
        $stmt = $this->query($sql, $params);
        return $stmt->fetch();
    }

    /**
     * Récupère une seule colonne
     * 
     * @param string $sql Requête SQL
     * @param array $params Paramètres
     * @return mixed
     */
    public function fetchColumn($sql, $params = [])
    {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchColumn();
    }

    /**
     * Retourne l'ID du dernier insert
     * 
     * @return string
     */
    public function lastInsertId()
    {
        return $this->connection->lastInsertId();
    }

    /**
     * Démarre une transaction
     */
    public function beginTransaction()
    {
        return $this->connection->beginTransaction();
    }

    /**
     * Valide une transaction
     */
    public function commit()
    {
        return $this->connection->commit();
    }

    /**
     * Annule une transaction
     */
    public function rollback()
    {
        return $this->connection->rollback();
    }
}
