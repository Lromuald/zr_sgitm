<?php
namespace App\Models;

use App\Core\Model;

/**
 * Modèle Piece
 * Gestion du catalogue des pièces et carburant
 */
class Piece extends Model
{
    protected $table = 'pieces';

    /**
     * Récupère toutes les pièces actives
     * 
     * @return array
     */
    public function getActivePieces()
    {
        $sql = "SELECT p.*, f.nom as fournisseur_nom
                FROM {$this->table} p
                LEFT JOIN fournisseurs f ON p.fournisseur_principal_id = f.id
                WHERE p.statut = 'actif'
                ORDER BY p.reference";
        
        return $this->db->fetchAll($sql);
    }

    /**
     * Récupère les pièces en stock critique
     * 
     * @return array
     */
    public function getCriticalStock()
    {
        $sql = "SELECT p.*, f.nom as fournisseur_nom,
                (p.seuil_alerte - p.stock_actuel) as quantite_a_commander
                FROM {$this->table} p
                LEFT JOIN fournisseurs f ON p.fournisseur_principal_id = f.id
                WHERE p.stock_actuel < p.seuil_alerte
                AND p.statut = 'actif'
                ORDER BY (p.seuil_alerte - p.stock_actuel) DESC";
        
        return $this->db->fetchAll($sql);
    }

    /**
     * Récupère les pièces par type
     * 
     * @param string $type
     * @return array
     */
    public function getPiecesByType($type)
    {
        $sql = "SELECT * FROM {$this->table}
                WHERE type = :type AND statut = 'actif'
                ORDER BY reference";
        
        return $this->db->fetchAll($sql, [':type' => $type]);
    }

    /**
     * Récupère une pièce avec son fournisseur
     * 
     * @param int $id
     * @return array|false
     */
    public function getPieceWithFournisseur($id)
    {
        $sql = "SELECT p.*, f.nom as fournisseur_nom, f.telephone as fournisseur_telephone,
                f.email as fournisseur_email
                FROM {$this->table} p
                LEFT JOIN fournisseurs f ON p.fournisseur_principal_id = f.id
                WHERE p.id = :id";
        
        return $this->db->fetch($sql, [':id' => $id]);
    }

    /**
     * Recherche des pièces
     * 
     * @param string $query
     * @return array
     */
    public function searchPieces($query)
    {
        $sql = "SELECT p.*, f.nom as fournisseur_nom
                FROM {$this->table} p
                LEFT JOIN fournisseurs f ON p.fournisseur_principal_id = f.id
                WHERE (p.reference LIKE :query OR p.description LIKE :query)
                AND p.statut = 'actif'
                ORDER BY p.reference
                LIMIT 50";
        
        return $this->db->fetchAll($sql, [':query' => "%$query%"]);
    }

    /**
     * Vérifie si le stock est suffisant
     * 
     * @param int $pieceId
     * @param float $quantite
     * @return bool
     */
    public function hasEnoughStock($pieceId, $quantite)
    {
        $piece = $this->findById($pieceId);
        if (!$piece) {
            return false;
        }
        
        return $piece['stock_actuel'] >= $quantite;
    }

    /**
     * Récupère les statistiques d'une pièce
     * 
     * @param int $pieceId
     * @return array
     */
    public function getPieceStats($pieceId)
    {
        $stats = [];
        
        // Entrées totales
        $sql = "SELECT COALESCE(SUM(quantite), 0) as total_entrees,
                COALESCE(SUM(valeur_totale), 0) as valeur_entrees,
                COUNT(*) as nb_entrees
                FROM mouvements_stock
                WHERE piece_id = :piece_id AND type = 'entree'";
        $stats['entrees'] = $this->db->fetch($sql, [':piece_id' => $pieceId]);
        
        // Sorties totales
        $sql = "SELECT COALESCE(SUM(quantite), 0) as total_sorties,
                COUNT(*) as nb_sorties
                FROM mouvements_stock
                WHERE piece_id = :piece_id AND type = 'sortie'";
        $stats['sorties'] = $this->db->fetch($sql, [':piece_id' => $pieceId]);
        
        // Derniers mouvements
        $sql = "SELECT ms.*, u.nom as user_nom, u.prenom as user_prenom
                FROM mouvements_stock ms
                LEFT JOIN users u ON ms.user_id = u.id
                WHERE ms.piece_id = :piece_id
                ORDER BY ms.date_mouvement DESC
                LIMIT 10";
        $stats['derniers_mouvements'] = $this->db->fetchAll($sql, [':piece_id' => $pieceId]);
        
        return $stats;
    }
}
