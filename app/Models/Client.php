<?php
namespace App\Models;

use App\Core\Model;

/**
 * Modèle Client
 * Gestion des clients
 */
class Client extends Model
{
    protected $table = 'clients';

    /**
     * Récupère tous les clients actifs
     * 
     * @return array
     */
    public function getActifs()
    {
        return $this->findAll(['actif' => 1], 'nom_raison_sociale');
    }

    /**
     * Récupère un client avec ses statistiques
     * 
     * @param int $clientId
     * @return array
     */
    public function getWithStats($clientId)
    {
        $client = $this->findById($clientId);
        
        if (!$client) {
            return false;
        }

        // Nombre de livraisons
        $sql = "SELECT COUNT(*) as total FROM livraisons WHERE client_id = :client_id";
        $client['nb_livraisons'] = $this->db->fetchColumn($sql, [':client_id' => $clientId]);

        // Livraisons livrées
        $sql = "SELECT COUNT(*) as total FROM livraisons 
                WHERE client_id = :client_id AND statut = 'livree'";
        $client['nb_livraisons_livrees'] = $this->db->fetchColumn($sql, [':client_id' => $clientId]);

        // Total facturé
        $sql = "SELECT COALESCE(SUM(montant_ttc), 0) as total 
                FROM factures WHERE client_id = :client_id";
        $client['total_facture'] = $this->db->fetchColumn($sql, [':client_id' => $clientId]);

        // Total payé
        $sql = "SELECT COALESCE(SUM(montant_paye), 0) as total 
                FROM factures WHERE client_id = :client_id";
        $client['total_paye'] = $this->db->fetchColumn($sql, [':client_id' => $clientId]);

        // Créance
        $client['creance'] = $client['total_facture'] - $client['total_paye'];

        return $client;
    }

    /**
     * Recherche de clients
     * 
     * @param string $query
     * @return array
     */
    public function search($query)
    {
        $sql = "SELECT * FROM {$this->table}
                WHERE nom_raison_sociale LIKE :query
                OR contact LIKE :query
                OR email LIKE :query
                OR telephone LIKE :query
                OR nif_rc LIKE :query
                ORDER BY nom_raison_sociale
                LIMIT 50";
        
        return $this->db->fetchAll($sql, [':query' => "%$query%"]);
    }
}
