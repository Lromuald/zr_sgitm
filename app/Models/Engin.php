<?php
namespace App\Models;

use App\Core\Model;

/**
 * Modèle Engin
 * Gestion du parc d'engins
 */
class Engin extends Model
{
    protected $table = 'engins';

    /**
     * Récupère tous les engins avec leurs statistiques
     * 
     * @return array
     */
    public function getAllWithStats()
    {
        $sql = "SELECT e.*,
                COUNT(DISTINCT l.id) as nb_livraisons,
                COUNT(DISTINCT m.id) as nb_maintenances
                FROM {$this->table} e
                LEFT JOIN livraisons l ON e.id = l.engin_id AND l.statut = 'livree'
                LEFT JOIN maintenances m ON e.id = m.engin_id AND m.statut = 'terminee'
                GROUP BY e.id
                ORDER BY e.immatriculation";
        
        return $this->db->fetchAll($sql);
    }

    /**
     * Récupère les engins par statut
     * 
     * @param string $statut
     * @return array
     */
    public function getByStatut($statut)
    {
        return $this->findAll(['statut' => $statut], 'immatriculation');
    }

    /**
     * Récupère les engins disponibles
     * 
     * @return array
     */
    public function getDisponibles()
    {
        return $this->getByStatut('disponible');
    }

    /**
     * Vérifie si un engin peut être utilisé pour une livraison
     * 
     * @param int $enginId
     * @return array ['allowed' => bool, 'reason' => string]
     */
    public function canBeUsedForLivraison($enginId)
    {
        $engin = $this->findById($enginId);
        
        if (!$engin) {
            return ['allowed' => false, 'reason' => 'Engin introuvable'];
        }

        // Vérifier le statut
        if ($engin['statut'] === 'hors_service') {
            return ['allowed' => false, 'reason' => 'Engin hors service'];
        }

        if ($engin['statut'] === 'en_maintenance') {
            return ['allowed' => false, 'reason' => 'Engin en maintenance'];
        }

        if ($engin['statut'] === 'en_mission') {
            return ['allowed' => false, 'reason' => 'Engin déjà en mission'];
        }

        // Vérifier les documents critiques
        $sql = "SELECT COUNT(*) as nb_expires
                FROM documents_engins
                WHERE engin_id = :engin_id
                AND est_critique = TRUE
                AND statut_validite = 'expire'";
        
        $result = $this->db->fetch($sql, [':engin_id' => $enginId]);
        
        if ($result['nb_expires'] > 0) {
            return ['allowed' => false, 'reason' => 'Documents critiques expirés'];
        }

        return ['allowed' => true, 'reason' => ''];
    }

    /**
     * Change le statut d'un engin
     * 
     * @param int $enginId
     * @param string $nouveauStatut
     * @return bool
     */
    public function changeStatut($enginId, $nouveauStatut)
    {
        $statutsValides = ['disponible', 'en_mission', 'en_maintenance', 'hors_service'];
        
        if (!in_array($nouveauStatut, $statutsValides)) {
            throw new \Exception("Statut invalide");
        }

        return $this->update($enginId, ['statut' => $nouveauStatut]);
    }

    /**
     * Récupère les engins avec documents expirés
     * 
     * @return array
     */
    public function getWithDocumentsExpires()
    {
        $sql = "SELECT DISTINCT e.*, 
                COUNT(d.id) as nb_docs_expires
                FROM {$this->table} e
                JOIN documents_engins d ON e.id = d.engin_id
                WHERE d.statut_validite = 'expire'
                GROUP BY e.id
                ORDER BY nb_docs_expires DESC, e.immatriculation";
        
        return $this->db->fetchAll($sql);
    }

    /**
     * Récupère l'historique des maintenances d'un engin
     * 
     * @param int $enginId
     * @param int $limit
     * @return array
     */
    public function getMaintenanceHistory($enginId, $limit = 20)
    {
        $sql = "SELECT m.*, u.nom as user_nom, u.prenom as user_prenom
                FROM maintenances m
                LEFT JOIN users u ON m.user_id = u.id
                WHERE m.engin_id = :engin_id
                ORDER BY m.date_planifiee DESC
                LIMIT :limit";
        
        return $this->db->fetchAll($sql, [
            ':engin_id' => $enginId,
            ':limit' => $limit
        ]);
    }

    /**
     * Récupère l'historique des livraisons d'un engin
     * 
     * @param int $enginId
     * @param int $limit
     * @return array
     */
    public function getLivraisonHistory($enginId, $limit = 20)
    {
        $sql = "SELECT l.*, c.nom_raison_sociale as client,
                ch.nom as chauffeur_nom, ch.prenom as chauffeur_prenom
                FROM livraisons l
                JOIN clients c ON l.client_id = c.id
                JOIN chauffeurs ch ON l.chauffeur_id = ch.id
                WHERE l.engin_id = :engin_id
                ORDER BY l.date_prevue DESC
                LIMIT :limit";
        
        return $this->db->fetchAll($sql, [
            ':engin_id' => $enginId,
            ':limit' => $limit
        ]);
    }

    /**
     * Calcule le taux de disponibilité d'un engin sur une période
     * 
     * @param int $enginId
     * @param int $jours Nombre de jours
     * @return float Pourcentage
     */
    public function calculateDisponibilite($enginId, $jours = 30)
    {
        $sql = "SELECT 
                COUNT(DISTINCT DATE(date_planifiee)) as jours_maintenance
                FROM maintenances
                WHERE engin_id = :engin_id
                AND statut IN ('en_cours', 'terminee')
                AND date_planifiee >= DATE_SUB(NOW(), INTERVAL :jours DAY)";
        
        $result = $this->db->fetch($sql, [
            ':engin_id' => $enginId,
            ':jours' => $jours
        ]);
        
        $joursIndisponibles = $result['jours_maintenance'] ?? 0;
        $tauxDispo = (($jours - $joursIndisponibles) / $jours) * 100;
        
        return round($tauxDispo, 2);
    }

    /**
     * Récupère les statistiques détaillées d'un engin
     * 
     * @param int $enginId
     * @return array
     */
    public function getDetailedStats($enginId)
    {
        $stats = [];
        
        // Stats de base
        $engin = $this->findById($enginId);
        $stats['engin'] = $engin;
        
        // Taux de disponibilité
        $stats['disponibilite_30j'] = $this->calculateDisponibilite($enginId, 30);
        
        // Nombre de livraisons
        $sql = "SELECT COUNT(*) as total FROM livraisons WHERE engin_id = :engin_id AND statut = 'livree'";
        $stats['nb_livraisons'] = $this->db->fetchColumn($sql, [':engin_id' => $enginId]);
        
        // Nombre de maintenances
        $sql = "SELECT COUNT(*) as total FROM maintenances WHERE engin_id = :engin_id AND statut = 'terminee'";
        $stats['nb_maintenances'] = $this->db->fetchColumn($sql, [':engin_id' => $enginId]);
        
        // Coût total des maintenances
        $sql = "SELECT COALESCE(SUM(cout_total), 0) as total 
                FROM maintenances WHERE engin_id = :engin_id AND statut = 'terminee'";
        $stats['cout_total_maintenances'] = $this->db->fetchColumn($sql, [':engin_id' => $enginId]);
        
        // Consommation carburant totale
        $sql = "SELECT COALESCE(SUM(quantite_litres), 0) as total,
                COALESCE(AVG(consommation_calculee), 0) as moyenne
                FROM consommations_carburant WHERE engin_id = :engin_id";
        $carburant = $this->db->fetch($sql, [':engin_id' => $enginId]);
        $stats['total_carburant_litres'] = $carburant['total'];
        $stats['consommation_moyenne'] = $carburant['moyenne'];
        
        // Documents
        $sql = "SELECT * FROM documents_engins WHERE engin_id = :engin_id ORDER BY date_expiration";
        $stats['documents'] = $this->db->fetchAll($sql, [':engin_id' => $enginId]);
        
        return $stats;
    }

    /**
     * Recherche d'engins
     * 
     * @param string $query
     * @return array
     */
    public function search($query)
    {
        $sql = "SELECT * FROM {$this->table}
                WHERE immatriculation LIKE :query
                OR marque LIKE :query
                OR modele LIKE :query
                OR num_serie LIKE :query
                ORDER BY immatriculation
                LIMIT 50";
        
        return $this->db->fetchAll($sql, [':query' => "%$query%"]);
    }
}
