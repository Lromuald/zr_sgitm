<?php
namespace App\Models;

use App\Core\Model;

/**
 * Modèle Chauffeur
 * Gestion des chauffeurs et permis
 */
class Chauffeur extends Model
{
    protected $table = 'chauffeurs';

    /**
     * Récupère tous les chauffeurs actifs
     * 
     * @return array
     */
    public function getActifs()
    {
        return $this->findAll(['statut' => 'actif'], 'nom, prenom');
    }

    /**
     * Vérifie si un chauffeur peut conduire un engin
     * 
     * @param int $chauffeurId
     * @param int $enginId
     * @return array ['allowed' => bool, 'reason' => string]
     */
    public function canDriveEngin($chauffeurId, $enginId)
    {
        $chauffeur = $this->findById($chauffeurId);
        
        if (!$chauffeur) {
            return ['allowed' => false, 'reason' => 'Chauffeur introuvable'];
        }

        // Vérifier le statut
        if ($chauffeur['statut'] !== 'actif') {
            return ['allowed' => false, 'reason' => 'Chauffeur inactif'];
        }

        // Vérifier si le chauffeur est bloqué (permis expiré)
        if ($chauffeur['bloque']) {
            return ['allowed' => false, 'reason' => 'Permis expiré'];
        }

        // Vérifier l'expiration du permis
        if ($chauffeur['date_expiration_permis'] && 
            strtotime($chauffeur['date_expiration_permis']) < time()) {
            return ['allowed' => false, 'reason' => 'Permis expiré'];
        }

        // Vérifier si le chauffeur est autorisé sur cet engin
        $sql = "SELECT COUNT(*) as nb
                FROM affectations_engin_chauffeur
                WHERE chauffeur_id = :chauffeur_id
                AND engin_id = :engin_id
                AND actif = TRUE
                AND (date_fin IS NULL OR date_fin >= CURDATE())";
        
        $result = $this->db->fetch($sql, [
            ':chauffeur_id' => $chauffeurId,
            ':engin_id' => $enginId
        ]);
        
        if ($result['nb'] == 0) {
            return ['allowed' => false, 'reason' => 'Chauffeur non autorisé sur cet engin'];
        }

        return ['allowed' => true, 'reason' => ''];
    }

    /**
     * Récupère les engins autorisés pour un chauffeur
     * 
     * @param int $chauffeurId
     * @return array
     */
    public function getEnginsAutorises($chauffeurId)
    {
        $sql = "SELECT e.*, aec.date_debut, aec.date_fin
                FROM engins e
                JOIN affectations_engin_chauffeur aec ON e.id = aec.engin_id
                WHERE aec.chauffeur_id = :chauffeur_id
                AND aec.actif = TRUE
                AND (aec.date_fin IS NULL OR aec.date_fin >= CURDATE())
                ORDER BY e.immatriculation";
        
        return $this->db->fetchAll($sql, [':chauffeur_id' => $chauffeurId]);
    }

    /**
     * Récupère les chauffeurs avec permis expiré ou proche de l'expiration
     * 
     * @param int $joursAvant Nombre de jours avant expiration
     * @return array
     */
    public function getPermisExpirantSous($joursAvant = 30)
    {
        $sql = "SELECT *, 
                DATEDIFF(date_expiration_permis, CURDATE()) as jours_restants
                FROM {$this->table}
                WHERE date_expiration_permis IS NOT NULL
                AND date_expiration_permis <= DATE_ADD(CURDATE(), INTERVAL :jours DAY)
                AND statut = 'actif'
                ORDER BY date_expiration_permis";
        
        return $this->db->fetchAll($sql, [':jours' => $joursAvant]);
    }

    /**
     * Récupère l'historique des livraisons d'un chauffeur
     * 
     * @param int $chauffeurId
     * @param int $limit
     * @return array
     */
    public function getLivraisonHistory($chauffeurId, $limit = 20)
    {
        $sql = "SELECT l.*, c.nom_raison_sociale as client,
                e.immatriculation as engin
                FROM livraisons l
                JOIN clients c ON l.client_id = c.id
                JOIN engins e ON l.engin_id = e.id
                WHERE l.chauffeur_id = :chauffeur_id
                ORDER BY l.date_prevue DESC
                LIMIT :limit";
        
        return $this->db->fetchAll($sql, [
            ':chauffeur_id' => $chauffeurId,
            ':limit' => $limit
        ]);
    }

    /**
     * Récupère les statistiques d'un chauffeur
     * 
     * @param int $chauffeurId
     * @return array
     */
    public function getStats($chauffeurId)
    {
        $stats = [];
        
        // Info de base
        $stats['chauffeur'] = $this->findById($chauffeurId);
        
        // Nombre de livraisons effectuées
        $sql = "SELECT COUNT(*) as total FROM livraisons 
                WHERE chauffeur_id = :chauffeur_id AND statut = 'livree'";
        $stats['nb_livraisons'] = $this->db->fetchColumn($sql, [':chauffeur_id' => $chauffeurId]);
        
        // Livraisons ce mois
        $sql = "SELECT COUNT(*) as total FROM livraisons 
                WHERE chauffeur_id = :chauffeur_id 
                AND statut = 'livree'
                AND YEAR(date_prevue) = YEAR(CURDATE())
                AND MONTH(date_prevue) = MONTH(CURDATE())";
        $stats['nb_livraisons_mois'] = $this->db->fetchColumn($sql, [':chauffeur_id' => $chauffeurId]);
        
        // Consommation carburant
        $sql = "SELECT COALESCE(SUM(quantite_litres), 0) as total,
                COALESCE(AVG(consommation_calculee), 0) as moyenne,
                COUNT(*) as nb_pleins
                FROM consommations_carburant
                WHERE chauffeur_id = :chauffeur_id";
        $carburant = $this->db->fetch($sql, [':chauffeur_id' => $chauffeurId]);
        $stats['total_carburant'] = $carburant['total'];
        $stats['consommation_moyenne'] = $carburant['moyenne'];
        $stats['nb_pleins'] = $carburant['nb_pleins'];
        
        // Engins autorisés
        $stats['engins_autorises'] = $this->getEnginsAutorises($chauffeurId);
        
        return $stats;
    }

    /**
     * Affecter un chauffeur à un engin
     * 
     * @param int $chauffeurId
     * @param int $enginId
     * @param string $dateDebut
     * @param string $dateFin
     * @return int ID de l'affectation
     */
    public function affecterEngin($chauffeurId, $enginId, $dateDebut, $dateFin = null)
    {
        // Vérifier que le chauffeur existe et est actif
        $chauffeur = $this->findById($chauffeurId);
        if (!$chauffeur || $chauffeur['statut'] !== 'actif') {
            throw new \Exception("Chauffeur invalide ou inactif");
        }

        // Vérifier que l'engin existe
        $enginModel = new Engin();
        $engin = $enginModel->findById($enginId);
        if (!$engin) {
            throw new \Exception("Engin introuvable");
        }

        $sql = "INSERT INTO affectations_engin_chauffeur 
                (engin_id, chauffeur_id, date_debut, date_fin, actif)
                VALUES (:engin_id, :chauffeur_id, :date_debut, :date_fin, TRUE)";
        
        $this->db->query($sql, [
            ':engin_id' => $enginId,
            ':chauffeur_id' => $chauffeurId,
            ':date_debut' => $dateDebut,
            ':date_fin' => $dateFin
        ]);

        return $this->db->lastInsertId();
    }

    /**
     * Désaffecter un chauffeur d'un engin
     * 
     * @param int $affectationId
     * @return bool
     */
    public function desaffecterEngin($affectationId)
    {
        $sql = "UPDATE affectations_engin_chauffeur 
                SET actif = FALSE, date_fin = CURDATE()
                WHERE id = :id";
        
        $this->db->query($sql, [':id' => $affectationId]);
        return true;
    }

    /**
     * Recherche de chauffeurs
     * 
     * @param string $query
     * @return array
     */
    public function search($query)
    {
        $sql = "SELECT * FROM {$this->table}
                WHERE nom LIKE :query
                OR prenom LIKE :query
                OR numero_permis LIKE :query
                OR telephone LIKE :query
                ORDER BY nom, prenom
                LIMIT 50";
        
        return $this->db->fetchAll($sql, [':query' => "%$query%"]);
    }
}
