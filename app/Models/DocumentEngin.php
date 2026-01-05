<?php
namespace App\Models;

use App\Core\Model;

/**
 * Modèle DocumentEngin
 * Gestion des documents administratifs des engins
 */
class DocumentEngin extends Model
{
    protected $table = 'documents_engins';

    /**
     * Récupère tous les documents d'un engin
     * 
     * @param int $enginId
     * @return array
     */
    public function getByEngin($enginId)
    {
        $sql = "SELECT d.*, u.nom as user_nom, u.prenom as user_prenom
                FROM {$this->table} d
                LEFT JOIN users u ON d.user_id = u.id
                WHERE d.engin_id = :engin_id
                ORDER BY d.type_document, d.date_expiration DESC";
        
        return $this->db->fetchAll($sql, [':engin_id' => $enginId]);
    }

    /**
     * Récupère les documents expirés
     * 
     * @return array
     */
    public function getExpires()
    {
        $sql = "SELECT d.*, e.immatriculation, e.type as engin_type
                FROM {$this->table} d
                LEFT JOIN engins e ON d.engin_id = e.id
                WHERE d.statut_validite = 'expire'
                ORDER BY d.date_expiration DESC";
        
        return $this->db->fetchAll($sql);
    }

    /**
     * Récupère les documents expirant dans X jours
     * 
     * @param int $jours Nombre de jours (30, 15, 3, etc.)
     * @return array
     */
    public function getExpirantSous($jours)
    {
        $sql = "SELECT d.*, e.immatriculation, e.type as engin_type,
                DATEDIFF(d.date_expiration, CURDATE()) as jours_restants
                FROM {$this->table} d
                LEFT JOIN engins e ON d.engin_id = e.id
                WHERE d.date_expiration BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL :jours DAY)
                AND d.statut_validite != 'expire'
                ORDER BY d.date_expiration ASC";
        
        return $this->db->fetchAll($sql, [':jours' => $jours]);
    }

    /**
     * Vérifie et met à jour la validité d'un document
     * 
     * @param int $id
     * @return bool
     */
    public function verifierValidite($id)
    {
        $document = $this->findById($id);
        
        if (!$document) {
            return false;
        }
        
        $dateExpiration = strtotime($document['date_expiration']);
        $aujourdhui = time();
        $joursRestants = floor(($dateExpiration - $aujourdhui) / 86400);
        
        $nouveauStatut = 'valide';
        $niveauAlerte = 'info';
        
        if ($joursRestants < 0) {
            $nouveauStatut = 'expire';
            $niveauAlerte = 'critique';
        } elseif ($joursRestants <= 3) {
            $nouveauStatut = 'a_renouveler';
            $niveauAlerte = 'urgent';
        } elseif ($joursRestants <= 15) {
            $nouveauStatut = 'a_renouveler';
            $niveauAlerte = 'attention';
        } elseif ($joursRestants <= 30) {
            $niveauAlerte = 'attention';
        }
        
        $sql = "UPDATE {$this->table} 
                SET statut_validite = :statut_validite,
                    alerte_niveau = :alerte_niveau
                WHERE id = :id";
        
        return $this->db->execute($sql, [
            ':id' => $id,
            ':statut_validite' => $nouveauStatut,
            ':alerte_niveau' => $niveauAlerte
        ]);
    }

    /**
     * Récupère uniquement les documents critiques
     * Types: carte_grise, assurance, carte_transport, visite_technique
     * 
     * @return array
     */
    public function getDocumentsCritiques()
    {
        $sql = "SELECT d.*, e.immatriculation, e.type as engin_type,
                DATEDIFF(d.date_expiration, CURDATE()) as jours_restants
                FROM {$this->table} d
                LEFT JOIN engins e ON d.engin_id = e.id
                WHERE d.est_critique = TRUE
                ORDER BY d.date_expiration ASC";
        
        return $this->db->fetchAll($sql);
    }

    /**
     * Vérifie si tous les documents critiques d'un engin sont valides
     * 
     * @param int $enginId
     * @return array ['conforme' => bool, 'documents_manquants' => array, 'documents_expires' => array]
     */
    public function verifierConformiteEngin($enginId)
    {
        // Types de documents critiques requis
        $typesCritiques = [
            'carte_grise',
            'assurance',
            'carte_transport',
            'visite_technique'
        ];
        
        // Récupérer tous les documents critiques de l'engin
        $sql = "SELECT type_document, statut_validite, date_expiration
                FROM {$this->table}
                WHERE engin_id = :engin_id
                AND est_critique = TRUE";
        
        $documents = $this->db->fetchAll($sql, [':engin_id' => $enginId]);
        
        // Vérifier les documents présents
        $documentsPresents = array_column($documents, 'type_document');
        $documentsManquants = array_diff($typesCritiques, $documentsPresents);
        
        // Vérifier les documents expirés
        $documentsExpires = [];
        foreach ($documents as $doc) {
            if ($doc['statut_validite'] === 'expire') {
                $documentsExpires[] = $doc['type_document'];
            }
        }
        
        $conforme = empty($documentsManquants) && empty($documentsExpires);
        
        return [
            'conforme' => $conforme,
            'documents_manquants' => array_values($documentsManquants),
            'documents_expires' => $documentsExpires
        ];
    }

    /**
     * Récupère un document par type pour un engin
     * 
     * @param int $enginId
     * @param string $typeDocument
     * @return array|false
     */
    public function getByEnginAndType($enginId, $typeDocument)
    {
        $sql = "SELECT d.*, u.nom as user_nom, u.prenom as user_prenom
                FROM {$this->table} d
                LEFT JOIN users u ON d.user_id = u.id
                WHERE d.engin_id = :engin_id
                AND d.type_document = :type_document
                ORDER BY d.date_upload DESC
                LIMIT 1";
        
        return $this->db->fetch($sql, [
            ':engin_id' => $enginId,
            ':type_document' => $typeDocument
        ]);
    }

    /**
     * Récupère les documents par niveau d'alerte
     * 
     * @param string $niveauAlerte (info|attention|urgent|critique)
     * @return array
     */
    public function getByNiveauAlerte($niveauAlerte)
    {
        $sql = "SELECT d.*, e.immatriculation, e.type as engin_type,
                DATEDIFF(d.date_expiration, CURDATE()) as jours_restants
                FROM {$this->table} d
                LEFT JOIN engins e ON d.engin_id = e.id
                WHERE d.alerte_niveau = :alerte_niveau
                ORDER BY d.date_expiration ASC";
        
        return $this->db->fetchAll($sql, [':alerte_niveau' => $niveauAlerte]);
    }

    /**
     * Met à jour en masse la validité de tous les documents
     * 
     * @return int Nombre de documents mis à jour
     */
    public function verifierTousLesDocuments()
    {
        $documents = $this->findAll();
        $count = 0;
        
        foreach ($documents as $document) {
            if ($this->verifierValidite($document['id'])) {
                $count++;
            }
        }
        
        return $count;
    }

    /**
     * Récupère les statistiques des documents par engin
     * 
     * @param int $enginId
     * @return array
     */
    public function getStatsByEngin($enginId)
    {
        $sql = "SELECT 
                COUNT(*) as total_documents,
                SUM(CASE WHEN est_critique = TRUE THEN 1 ELSE 0 END) as nb_critiques,
                SUM(CASE WHEN statut_validite = 'valide' THEN 1 ELSE 0 END) as nb_valides,
                SUM(CASE WHEN statut_validite = 'expire' THEN 1 ELSE 0 END) as nb_expires,
                SUM(CASE WHEN statut_validite = 'a_renouveler' THEN 1 ELSE 0 END) as nb_a_renouveler
                FROM {$this->table}
                WHERE engin_id = :engin_id";
        
        return $this->db->fetch($sql, [':engin_id' => $enginId]);
    }

    /**
     * Récupère le tableau de bord des documents
     * 
     * @return array
     */
    public function getDashboard()
    {
        return [
            'expires' => $this->getExpires(),
            'expirant_3j' => $this->getExpirantSous(3),
            'expirant_15j' => $this->getExpirantSous(15),
            'expirant_30j' => $this->getExpirantSous(30),
            'critiques_non_conformes' => $this->getDocumentsCritiquesNonConformes()
        ];
    }

    /**
     * Récupère les documents critiques non conformes (expirés ou manquants)
     * 
     * @return array
     */
    private function getDocumentsCritiquesNonConformes()
    {
        $sql = "SELECT d.*, e.immatriculation, e.type as engin_type
                FROM {$this->table} d
                LEFT JOIN engins e ON d.engin_id = e.id
                WHERE d.est_critique = TRUE
                AND d.statut_validite = 'expire'
                ORDER BY d.date_expiration ASC";
        
        return $this->db->fetchAll($sql);
    }
}
