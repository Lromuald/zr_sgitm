<?php
namespace App\Core;

/**
 * Modèle de base
 * Tous les modèles héritent de cette classe
 */
class Model
{
    protected $db;
    protected $table;
    protected $primaryKey = 'id';

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Récupère tous les enregistrements
     * 
     * @param array $conditions Conditions WHERE
     * @param string $orderBy Ordre de tri
     * @param int $limit Limite
     * @param int $offset Offset
     * @return array
     */
    public function findAll($conditions = [], $orderBy = null, $limit = null, $offset = 0)
    {
        $sql = "SELECT * FROM {$this->table}";
        $params = [];
        
        if (!empty($conditions)) {
            $where = [];
            foreach ($conditions as $field => $value) {
                $where[] = "$field = :$field";
                $params[":$field"] = $value;
            }
            $sql .= " WHERE " . implode(' AND ', $where);
        }
        
        if ($orderBy) {
            $sql .= " ORDER BY $orderBy";
        }
        
        if ($limit) {
            $sql .= " LIMIT $limit";
            if ($offset) {
                $sql .= " OFFSET $offset";
            }
        }
        
        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Récupère un enregistrement par ID
     * 
     * @param int $id
     * @return array|false
     */
    public function findById($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = :id LIMIT 1";
        return $this->db->fetch($sql, [':id' => $id]);
    }

    /**
     * Récupère un enregistrement selon des conditions
     * 
     * @param array $conditions
     * @return array|false
     */
    public function findOne($conditions)
    {
        $sql = "SELECT * FROM {$this->table}";
        $params = [];
        
        if (!empty($conditions)) {
            $where = [];
            foreach ($conditions as $field => $value) {
                $where[] = "$field = :$field";
                $params[":$field"] = $value;
            }
            $sql .= " WHERE " . implode(' AND ', $where);
        }
        
        $sql .= " LIMIT 1";
        
        return $this->db->fetch($sql, $params);
    }

    /**
     * Compte les enregistrements
     * 
     * @param array $conditions
     * @return int
     */
    public function count($conditions = [])
    {
        $sql = "SELECT COUNT(*) FROM {$this->table}";
        $params = [];
        
        if (!empty($conditions)) {
            $where = [];
            foreach ($conditions as $field => $value) {
                $where[] = "$field = :$field";
                $params[":$field"] = $value;
            }
            $sql .= " WHERE " . implode(' AND ', $where);
        }
        
        return (int) $this->db->fetchColumn($sql, $params);
    }

    /**
     * Insère un nouvel enregistrement
     * 
     * @param array $data Données à insérer
     * @return int|false ID inséré ou false
     */
    public function insert($data)
    {
        $fields = array_keys($data);
        $placeholders = array_map(function($field) {
            return ":$field";
        }, $fields);
        
        $sql = "INSERT INTO {$this->table} (" . implode(', ', $fields) . ") 
                VALUES (" . implode(', ', $placeholders) . ")";
        
        $params = [];
        foreach ($data as $field => $value) {
            $params[":$field"] = $value;
        }
        
        $this->db->query($sql, $params);
        return $this->db->lastInsertId();
    }

    /**
     * Met à jour un enregistrement
     * 
     * @param int $id ID de l'enregistrement
     * @param array $data Données à mettre à jour
     * @return bool
     */
    public function update($id, $data)
    {
        $fields = [];
        $params = [':id' => $id];
        
        foreach ($data as $field => $value) {
            $fields[] = "$field = :$field";
            $params[":$field"] = $value;
        }
        
        $sql = "UPDATE {$this->table} SET " . implode(', ', $fields) . 
               " WHERE {$this->primaryKey} = :id";
        
        $this->db->query($sql, $params);
        return true;
    }

    /**
     * Supprime un enregistrement
     * 
     * @param int $id
     * @return bool
     */
    public function delete($id)
    {
        $sql = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = :id";
        $this->db->query($sql, [':id' => $id]);
        return true;
    }

    /**
     * Exécute une requête SQL personnalisée
     * 
     * @param string $sql
     * @param array $params
     * @return \PDOStatement
     */
    public function query($sql, $params = [])
    {
        return $this->db->query($sql, $params);
    }

    /**
     * Démarre une transaction
     */
    public function beginTransaction()
    {
        return $this->db->beginTransaction();
    }

    /**
     * Valide une transaction
     */
    public function commit()
    {
        return $this->db->commit();
    }

    /**
     * Annule une transaction
     */
    public function rollback()
    {
        return $this->db->rollback();
    }
}
