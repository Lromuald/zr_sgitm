<?php
namespace App\Models;

use App\Core\Model;

/**
 * Modèle Maintenance
 * Gestion des maintenances préventives et correctives
 */
class Maintenance extends Model
{
    protected $table = 'maintenances';

    /**
     * Récupère toutes les maintenances d'un engin
     * 
     * @param int $enginId
     * @return array
     */
    public function getByEngin($enginId)
    {
        $sql = "SELECT m.*, e.immatriculation, e.type as engin_type,
                u.nom as user_nom, u.prenom as user_prenom
                FROM {$this->table} m
                LEFT JOIN engins e ON m.engin_id = e.id
                LEFT JOIN users u ON m.user_id = u.id
                WHERE m.engin_id = :engin_id
                ORDER BY m.date_planifiee DESC";
        
        return $this->db->fetchAll($sql, [':engin_id' => $enginId]);
    }

    /**
     * Récupère les maintenances planifiées
     * 
     * @return array
     */
    public function getPlanifiees()
    {
        $sql = "SELECT m.*, e.immatriculation, e.type as engin_type
                FROM {$this->table} m
                LEFT JOIN engins e ON m.engin_id = e.id
                WHERE m.statut = 'planifiee'
                ORDER BY m.date_planifiee ASC";
        
        return $this->db->fetchAll($sql);
    }

    /**
     * Récupère les maintenances en cours
     * 
     * @return array
     */
    public function getEnCours()
    {
        $sql = "SELECT m.*, e.immatriculation, e.type as engin_type
                FROM {$this->table} m
                LEFT JOIN engins e ON m.engin_id = e.id
                WHERE m.statut = 'en_cours'
                ORDER BY m.date_debut_reelle DESC";
        
        return $this->db->fetchAll($sql);
    }

    /**
     * Calcule et met à jour le coût total d'une maintenance
     * 
     * @param int $id
     * @return bool
     */
    public function calculerCoutTotal($id)
    {
        // Le cout_total est calculé automatiquement par MySQL (GENERATED ALWAYS AS)
        // Cette méthode récupère simplement les valeurs actuelles
        $maintenance = $this->findById($id);
        
        if (!$maintenance) {
            return false;
        }
        
        return true;
    }

    /**
     * Récupère et décode les pièces utilisées
     * 
     * @param int $id
     * @return array|false
     */
    public function getPiecesUtilisees($id)
    {
        $maintenance = $this->findById($id);
        
        if (!$maintenance || empty($maintenance['pieces_utilisees'])) {
            return [];
        }
        
        $pieces = json_decode($maintenance['pieces_utilisees'], true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            return [];
        }
        
        // Enrichir avec les informations des pièces
        if (!empty($pieces)) {
            $pieceIds = array_column($pieces, 'piece_id');
            $placeholders = implode(',', array_fill(0, count($pieceIds), '?'));
            
            $sql = "SELECT id, reference, description, prix_unitaire_cump
                    FROM pieces
                    WHERE id IN ($placeholders)";
            
            $piecesInfo = $this->db->fetchAll($sql, $pieceIds);
            
            // Créer un tableau associatif pour un accès rapide
            $piecesMap = [];
            foreach ($piecesInfo as $piece) {
                $piecesMap[$piece['id']] = $piece;
            }
            
            // Enrichir les données
            foreach ($pieces as &$piece) {
                if (isset($piecesMap[$piece['piece_id']])) {
                    $piece['reference'] = $piecesMap[$piece['piece_id']]['reference'];
                    $piece['description'] = $piecesMap[$piece['piece_id']]['description'];
                    $piece['prix_unitaire'] = $piecesMap[$piece['piece_id']]['prix_unitaire_cump'];
                }
            }
        }
        
        return $pieces;
    }

    /**
     * Récupère une maintenance avec toutes ses données enrichies
     * 
     * @param int $id
     * @return array|false
     */
    public function getMaintenanceComplete($id)
    {
        $sql = "SELECT m.*, 
                e.immatriculation, e.type as engin_type, e.marque, e.modele,
                u.nom as user_nom, u.prenom as user_prenom
                FROM {$this->table} m
                LEFT JOIN engins e ON m.engin_id = e.id
                LEFT JOIN users u ON m.user_id = u.id
                WHERE m.id = :id";
        
        $maintenance = $this->db->fetch($sql, [':id' => $id]);
        
        if ($maintenance) {
            $maintenance['pieces_utilisees_detail'] = $this->getPiecesUtilisees($id);
        }
        
        return $maintenance;
    }

    /**
     * Récupère les maintenances par type
     * 
     * @param string $type (preventive|corrective)
     * @return array
     */
    public function getByType($type)
    {
        $sql = "SELECT m.*, e.immatriculation, e.type as engin_type
                FROM {$this->table} m
                LEFT JOIN engins e ON m.engin_id = e.id
                WHERE m.type = :type
                ORDER BY m.date_planifiee DESC";
        
        return $this->db->fetchAll($sql, [':type' => $type]);
    }

    /**
     * Récupère les maintenances à venir (prochains 7 jours)
     * 
     * @param int $jours Nombre de jours à regarder en avant
     * @return array
     */
    public function getAVenir($jours = 7)
    {
        $sql = "SELECT m.*, e.immatriculation, e.type as engin_type
                FROM {$this->table} m
                LEFT JOIN engins e ON m.engin_id = e.id
                WHERE m.statut = 'planifiee'
                AND m.date_planifiee BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL :jours DAY)
                ORDER BY m.date_planifiee ASC";
        
        return $this->db->fetchAll($sql, [':jours' => $jours]);
    }

    /**
     * Récupère l'historique des maintenances par période
     * 
     * @param string $dateDebut
     * @param string $dateFin
     * @return array
     */
    public function getByPeriode($dateDebut, $dateFin)
    {
        $sql = "SELECT m.*, e.immatriculation, e.type as engin_type
                FROM {$this->table} m
                LEFT JOIN engins e ON m.engin_id = e.id
                WHERE m.date_planifiee BETWEEN :date_debut AND :date_fin
                ORDER BY m.date_planifiee DESC";
        
        return $this->db->fetchAll($sql, [
            ':date_debut' => $dateDebut,
            ':date_fin' => $dateFin
        ]);
    }

    /**
     * Calcule les statistiques de maintenance par engin
     * 
     * @param int $enginId
     * @return array
     */
    public function getStatsByEngin($enginId)
    {
        $sql = "SELECT 
                COUNT(*) as total_maintenances,
                SUM(CASE WHEN type = 'preventive' THEN 1 ELSE 0 END) as nb_preventives,
                SUM(CASE WHEN type = 'corrective' THEN 1 ELSE 0 END) as nb_correctives,
                SUM(CASE WHEN statut = 'terminee' THEN 1 ELSE 0 END) as nb_terminees,
                COALESCE(SUM(CASE WHEN statut = 'terminee' THEN cout_total ELSE 0 END), 0) as cout_total,
                COALESCE(AVG(CASE WHEN statut = 'terminee' THEN cout_total ELSE NULL END), 0) as cout_moyen
                FROM {$this->table}
                WHERE engin_id = :engin_id";
        
        return $this->db->fetch($sql, [':engin_id' => $enginId]);
    }
}
