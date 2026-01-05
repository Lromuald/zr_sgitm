<?php
namespace App\Models;

use App\Core\Model;

/**
 * Modèle Livraison
 * Gestion des livraisons avec workflow et blocages automatiques
 */
class Livraison extends Model
{
    protected $table = 'livraisons';

    /**
     * Récupère toutes les livraisons avec détails
     * 
     * @param array $filters
     * @param int $page
     * @param int $perPage
     * @return array
     */
    public function getLivraisons($filters = [], $page = 1, $perPage = 25)
    {
        $offset = ($page - 1) * $perPage;
        $where = [];
        $params = [];

        // Filtres
        if (!empty($filters['statut'])) {
            $where[] = "l.statut = :statut";
            $params[':statut'] = $filters['statut'];
        }

        if (!empty($filters['client_id'])) {
            $where[] = "l.client_id = :client_id";
            $params[':client_id'] = $filters['client_id'];
        }

        if (!empty($filters['engin_id'])) {
            $where[] = "l.engin_id = :engin_id";
            $params[':engin_id'] = $filters['engin_id'];
        }

        if (!empty($filters['chauffeur_id'])) {
            $where[] = "l.chauffeur_id = :chauffeur_id";
            $params[':chauffeur_id'] = $filters['chauffeur_id'];
        }

        if (!empty($filters['date_debut'])) {
            $where[] = "DATE(l.date_prevue) >= :date_debut";
            $params[':date_debut'] = $filters['date_debut'];
        }

        if (!empty($filters['date_fin'])) {
            $where[] = "DATE(l.date_prevue) <= :date_fin";
            $params[':date_fin'] = $filters['date_fin'];
        }

        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        // Compter le total
        $sql = "SELECT COUNT(*) as total FROM {$this->table} l $whereClause";
        $total = $this->db->fetchColumn($sql, $params);

        // Récupérer les données
        $sql = "SELECT l.*, 
                c.nom_raison_sociale as client,
                e.immatriculation as engin,
                e.type as engin_type,
                ch.nom as chauffeur_nom,
                ch.prenom as chauffeur_prenom,
                u.nom as user_nom,
                u.prenom as user_prenom
                FROM {$this->table} l
                JOIN clients c ON l.client_id = c.id
                JOIN engins e ON l.engin_id = e.id
                JOIN chauffeurs ch ON l.chauffeur_id = ch.id
                LEFT JOIN users u ON l.user_id = u.id
                $whereClause
                ORDER BY l.date_prevue DESC
                LIMIT :limit OFFSET :offset";

        $params[':limit'] = $perPage;
        $params[':offset'] = $offset;

        $livraisons = $this->db->fetchAll($sql, $params);

        return [
            'data' => $livraisons,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => ceil($total / $perPage)
        ];
    }

    /**
     * Récupère une livraison avec tous ses détails
     * 
     * @param int $id
     * @return array|false
     */
    public function getWithDetails($id)
    {
        $sql = "SELECT l.*, 
                c.nom_raison_sociale as client_nom,
                c.telephone as client_telephone,
                c.email as client_email,
                c.adresse as client_adresse,
                e.immatriculation as engin_immatriculation,
                e.type as engin_type,
                e.marque as engin_marque,
                e.modele as engin_modele,
                e.statut as engin_statut,
                ch.nom as chauffeur_nom,
                ch.prenom as chauffeur_prenom,
                ch.telephone as chauffeur_telephone,
                ch.bloque as chauffeur_bloque,
                u.nom as user_nom,
                u.prenom as user_prenom
                FROM {$this->table} l
                JOIN clients c ON l.client_id = c.id
                JOIN engins e ON l.engin_id = e.id
                JOIN chauffeurs ch ON l.chauffeur_id = ch.id
                LEFT JOIN users u ON l.user_id = u.id
                WHERE l.id = :id";
        
        return $this->db->fetch($sql, [':id' => $id]);
    }

    /**
     * Vérifie si une livraison peut être créée (toutes les validations)
     * 
     * @param array $data
     * @return array ['allowed' => bool, 'errors' => array]
     */
    public function validateLivraison($data)
    {
        $errors = [];

        // Vérifier que tous les champs obligatoires sont présents
        $requiredFields = ['client_id', 'engin_id', 'chauffeur_id', 'date_prevue', 'lieu_depart', 'destination'];
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                $errors[] = "Le champ $field est obligatoire";
            }
        }

        if (!empty($errors)) {
            return ['allowed' => false, 'errors' => $errors];
        }

        // Vérifier l'engin
        $enginModel = new Engin();
        $enginCheck = $enginModel->canBeUsedForLivraison($data['engin_id']);
        if (!$enginCheck['allowed']) {
            $errors[] = "Engin: " . $enginCheck['reason'];
        }

        // Vérifier le chauffeur
        $chauffeurModel = new Chauffeur();
        $chauffeurCheck = $chauffeurModel->canDriveEngin($data['chauffeur_id'], $data['engin_id']);
        if (!$chauffeurCheck['allowed']) {
            $errors[] = "Chauffeur: " . $chauffeurCheck['reason'];
        }

        // Vérifier le client
        $clientModel = new Client();
        $client = $clientModel->findById($data['client_id']);
        if (!$client || !$client['actif']) {
            $errors[] = "Client invalide ou inactif";
        }

        return [
            'allowed' => empty($errors),
            'errors' => $errors
        ];
    }

    /**
     * Créer une nouvelle livraison avec validations
     * 
     * @param array $data
     * @return int ID de la livraison
     */
    public function creerLivraison($data)
    {
        // Valider
        $validation = $this->validateLivraison($data);
        if (!$validation['allowed']) {
            throw new \Exception(implode(', ', $validation['errors']));
        }

        // Définir le statut initial
        $data['statut'] = 'planifiee';
        $data['date_creation'] = date('Y-m-d H:i:s');

        return $this->insert($data);
    }

    /**
     * Valider une livraison planifiée (passer de planifiee à validée)
     * 
     * @param int $livraisonId
     * @param int $userId
     * @return bool
     */
    public function validerLivraison($livraisonId, $userId)
    {
        $livraison = $this->findById($livraisonId);
        
        if (!$livraison) {
            throw new \Exception("Livraison introuvable");
        }

        if ($livraison['statut'] !== 'planifiee') {
            throw new \Exception("Seules les livraisons planifiées peuvent être validées");
        }

        // Re-vérifier les conditions avant validation
        $enginModel = new Engin();
        $enginCheck = $enginModel->canBeUsedForLivraison($livraison['engin_id']);
        if (!$enginCheck['allowed']) {
            throw new \Exception("Validation impossible: " . $enginCheck['reason']);
        }

        // La livraison reste en statut 'planifiee' après validation commerciale
        // Elle passera à 'en_cours' au départ effectif
        
        return true;
    }

    /**
     * Démarrer une livraison (passer de planifiee à en_cours)
     * 
     * @param int $livraisonId
     * @return bool
     */
    public function demarrerLivraison($livraisonId)
    {
        $livraison = $this->findById($livraisonId);
        
        if (!$livraison) {
            throw new \Exception("Livraison introuvable");
        }

        if ($livraison['statut'] !== 'planifiee') {
            throw new \Exception("Seules les livraisons planifiées peuvent être démarrées");
        }

        // Vérifier à nouveau les conditions
        $validation = $this->validateLivraison($livraison);
        if (!$validation['allowed']) {
            throw new \Exception(implode(', ', $validation['errors']));
        }

        // Mettre à jour la livraison
        $this->beginTransaction();
        
        try {
            $this->update($livraisonId, [
                'statut' => 'en_cours',
                'heure_depart_reelle' => date('Y-m-d H:i:s')
            ]);

            // Changer le statut de l'engin
            $enginModel = new Engin();
            $enginModel->changeStatut($livraison['engin_id'], 'en_mission');

            $this->commit();
            return true;
        } catch (\Exception $e) {
            $this->rollback();
            throw $e;
        }
    }

    /**
     * Terminer une livraison (passer de en_cours à livree)
     * 
     * @param int $livraisonId
     * @return bool
     */
    public function terminerLivraison($livraisonId)
    {
        $livraison = $this->findById($livraisonId);
        
        if (!$livraison) {
            throw new \Exception("Livraison introuvable");
        }

        if ($livraison['statut'] !== 'en_cours') {
            throw new \Exception("Seules les livraisons en cours peuvent être terminées");
        }

        $this->beginTransaction();
        
        try {
            // Mettre à jour la livraison
            $this->update($livraisonId, [
                'statut' => 'livree',
                'heure_arrivee' => date('Y-m-d H:i:s')
            ]);

            // Libérer l'engin (le remettre disponible si pas de problème)
            $enginModel = new Engin();
            
            // Vérifier si l'engin a des documents expirés
            $sql = "SELECT COUNT(*) as nb FROM documents_engins 
                    WHERE engin_id = :engin_id AND est_critique = TRUE AND statut_validite = 'expire'";
            $result = $this->db->fetch($sql, [':engin_id' => $livraison['engin_id']]);
            
            if ($result['nb'] > 0) {
                $enginModel->changeStatut($livraison['engin_id'], 'hors_service');
            } else {
                $enginModel->changeStatut($livraison['engin_id'], 'disponible');
            }

            $this->commit();
            return true;
        } catch (\Exception $e) {
            $this->rollback();
            throw $e;
        }
    }

    /**
     * Annuler une livraison
     * 
     * @param int $livraisonId
     * @param string $motif
     * @return bool
     */
    public function annulerLivraison($livraisonId, $motif = '')
    {
        $livraison = $this->findById($livraisonId);
        
        if (!$livraison) {
            throw new \Exception("Livraison introuvable");
        }

        if ($livraison['statut'] === 'livree') {
            throw new \Exception("Impossible d'annuler une livraison terminée");
        }

        if ($livraison['statut'] === 'annulee') {
            throw new \Exception("Cette livraison est déjà annulée");
        }

        $this->beginTransaction();
        
        try {
            // Mettre à jour la livraison
            $this->update($livraisonId, [
                'statut' => 'annulee',
                'notes' => ($livraison['notes'] ?? '') . "\n[ANNULATION] " . $motif
            ]);

            // Si la livraison était en cours, libérer l'engin
            if ($livraison['statut'] === 'en_cours') {
                $enginModel = new Engin();
                $enginModel->changeStatut($livraison['engin_id'], 'disponible');
            }

            $this->commit();
            return true;
        } catch (\Exception $e) {
            $this->rollback();
            throw $e;
        }
    }

    /**
     * Récupère les livraisons du jour
     * 
     * @return array
     */
    public function getLivraisonsJour()
    {
        $sql = "SELECT l.*, 
                c.nom_raison_sociale as client,
                e.immatriculation as engin,
                ch.nom as chauffeur_nom,
                ch.prenom as chauffeur_prenom
                FROM {$this->table} l
                JOIN clients c ON l.client_id = c.id
                JOIN engins e ON l.engin_id = e.id
                JOIN chauffeurs ch ON l.chauffeur_id = ch.id
                WHERE DATE(l.date_prevue) = CURDATE()
                AND l.statut IN ('planifiee', 'en_cours')
                ORDER BY l.date_prevue";
        
        return $this->db->fetchAll($sql);
    }

    /**
     * Récupère les statistiques des livraisons sur une période
     * 
     * @param string $dateDebut
     * @param string $dateFin
     * @return array
     */
    public function getStatsPeriode($dateDebut, $dateFin)
    {
        $stats = [];

        // Total par statut
        $sql = "SELECT statut, COUNT(*) as nb,
                COALESCE(SUM(poids_volume), 0) as total_poids,
                COALESCE(SUM(tarification), 0) as total_tarif
                FROM {$this->table}
                WHERE DATE(date_prevue) BETWEEN :date_debut AND :date_fin
                GROUP BY statut";
        $stats['par_statut'] = $this->db->fetchAll($sql, [
            ':date_debut' => $dateDebut,
            ':date_fin' => $dateFin
        ]);

        // Top clients
        $sql = "SELECT c.nom_raison_sociale as client, COUNT(*) as nb_livraisons,
                COALESCE(SUM(l.tarification), 0) as ca_total
                FROM {$this->table} l
                JOIN clients c ON l.client_id = c.id
                WHERE DATE(l.date_prevue) BETWEEN :date_debut AND :date_fin
                AND l.statut = 'livree'
                GROUP BY l.client_id
                ORDER BY nb_livraisons DESC
                LIMIT 10";
        $stats['top_clients'] = $this->db->fetchAll($sql, [
            ':date_debut' => $dateDebut,
            ':date_fin' => $dateFin
        ]);

        // Top engins
        $sql = "SELECT e.immatriculation as engin, COUNT(*) as nb_livraisons
                FROM {$this->table} l
                JOIN engins e ON l.engin_id = e.id
                WHERE DATE(l.date_prevue) BETWEEN :date_debut AND :date_fin
                AND l.statut = 'livree'
                GROUP BY l.engin_id
                ORDER BY nb_livraisons DESC
                LIMIT 10";
        $stats['top_engins'] = $this->db->fetchAll($sql, [
            ':date_debut' => $dateDebut,
            ':date_fin' => $dateFin
        ]);

        return $stats;
    }
}
