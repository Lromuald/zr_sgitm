<?php
namespace App\Models;

use App\Core\Model;

/**
 * Modèle MouvementStock
 * Gestion des mouvements de stock avec calcul CUMP automatique
 */
class MouvementStock extends Model
{
    protected $table = 'mouvements_stock';

    /**
     * Enregistre une entrée de stock (avec calcul CUMP automatique via trigger)
     * 
     * @param array $data
     * @return int ID du mouvement
     */
    public function enregistrerEntree($data)
    {
        // Valider les données
        if (!isset($data['piece_id']) || !isset($data['quantite']) || !isset($data['prix_unitaire'])) {
            throw new \Exception("Données incomplètes pour l'entrée de stock");
        }

        // Calculer la valeur totale
        $data['valeur_totale'] = $data['quantite'] * $data['prix_unitaire'];
        $data['type'] = 'entree';
        $data['date_mouvement'] = date('Y-m-d H:i:s');

        // Le trigger calculera automatiquement le nouveau CUMP
        return $this->insert($data);
    }

    /**
     * Enregistre une sortie de stock
     * 
     * @param array $data
     * @return int|false ID du mouvement ou false
     */
    public function enregistrerSortie($data)
    {
        // Valider les données
        if (!isset($data['piece_id']) || !isset($data['quantite'])) {
            throw new \Exception("Données incomplètes pour la sortie de stock");
        }

        // Vérifier le stock disponible
        $pieceModel = new Piece();
        if (!$pieceModel->hasEnoughStock($data['piece_id'], $data['quantite'])) {
            throw new \Exception("Stock insuffisant");
        }

        // Récupérer le CUMP actuel pour valoriser la sortie
        $piece = $pieceModel->findById($data['piece_id']);
        $data['prix_unitaire'] = $piece['prix_unitaire_cump'];
        $data['valeur_totale'] = $data['quantite'] * $data['prix_unitaire'];
        $data['type'] = 'sortie';
        $data['date_mouvement'] = date('Y-m-d H:i:s');

        // Le trigger mettra à jour le stock automatiquement
        return $this->insert($data);
    }

    /**
     * Récupère l'historique des mouvements avec pagination
     * 
     * @param int $page
     * @param int $perPage
     * @param array $filters
     * @return array
     */
    public function getMouvements($page = 1, $perPage = 25, $filters = [])
    {
        $offset = ($page - 1) * $perPage;
        $where = [];
        $params = [];

        // Filtres
        if (!empty($filters['piece_id'])) {
            $where[] = "ms.piece_id = :piece_id";
            $params[':piece_id'] = $filters['piece_id'];
        }

        if (!empty($filters['type'])) {
            $where[] = "ms.type = :type";
            $params[':type'] = $filters['type'];
        }

        if (!empty($filters['motif'])) {
            $where[] = "ms.motif = :motif";
            $params[':motif'] = $filters['motif'];
        }

        if (!empty($filters['date_debut'])) {
            $where[] = "DATE(ms.date_mouvement) >= :date_debut";
            $params[':date_debut'] = $filters['date_debut'];
        }

        if (!empty($filters['date_fin'])) {
            $where[] = "DATE(ms.date_mouvement) <= :date_fin";
            $params[':date_fin'] = $filters['date_fin'];
        }

        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        // Compter le total
        $sql = "SELECT COUNT(*) as total FROM {$this->table} ms $whereClause";
        $total = $this->db->fetchColumn($sql, $params);

        // Récupérer les données
        $sql = "SELECT ms.*, 
                p.reference as piece_reference, p.description as piece_description,
                u.nom as user_nom, u.prenom as user_prenom,
                e.immatriculation as engin_immatriculation,
                f.nom as fournisseur_nom
                FROM {$this->table} ms
                LEFT JOIN pieces p ON ms.piece_id = p.id
                LEFT JOIN users u ON ms.user_id = u.id
                LEFT JOIN engins e ON ms.engin_id = e.id
                LEFT JOIN fournisseurs f ON ms.fournisseur_id = f.id
                $whereClause
                ORDER BY ms.date_mouvement DESC
                LIMIT :limit OFFSET :offset";

        $params[':limit'] = $perPage;
        $params[':offset'] = $offset;

        $mouvements = $this->db->fetchAll($sql, $params);

        return [
            'data' => $mouvements,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => ceil($total / $perPage)
        ];
    }

    /**
     * Récupère les mouvements d'une pièce spécifique
     * 
     * @param int $pieceId
     * @param int $limit
     * @return array
     */
    public function getMouvementsByPiece($pieceId, $limit = 50)
    {
        $sql = "SELECT ms.*, 
                u.nom as user_nom, u.prenom as user_prenom,
                e.immatriculation as engin_immatriculation
                FROM {$this->table} ms
                LEFT JOIN users u ON ms.user_id = u.id
                LEFT JOIN engins e ON ms.engin_id = e.id
                WHERE ms.piece_id = :piece_id
                ORDER BY ms.date_mouvement DESC
                LIMIT :limit";

        return $this->db->fetchAll($sql, [
            ':piece_id' => $pieceId,
            ':limit' => $limit
        ]);
    }

    /**
     * Récupère les statistiques des mouvements sur une période
     * 
     * @param string $dateDebut
     * @param string $dateFin
     * @return array
     */
    public function getStatsPeriode($dateDebut, $dateFin)
    {
        $stats = [];

        // Total entrées
        $sql = "SELECT 
                COUNT(*) as nb_entrees,
                COALESCE(SUM(quantite), 0) as quantite_entrees,
                COALESCE(SUM(valeur_totale), 0) as valeur_entrees
                FROM {$this->table}
                WHERE type = 'entree'
                AND DATE(date_mouvement) BETWEEN :date_debut AND :date_fin";
        $stats['entrees'] = $this->db->fetch($sql, [
            ':date_debut' => $dateDebut,
            ':date_fin' => $dateFin
        ]);

        // Total sorties
        $sql = "SELECT 
                COUNT(*) as nb_sorties,
                COALESCE(SUM(quantite), 0) as quantite_sorties,
                COALESCE(SUM(valeur_totale), 0) as valeur_sorties
                FROM {$this->table}
                WHERE type = 'sortie'
                AND DATE(date_mouvement) BETWEEN :date_debut AND :date_fin";
        $stats['sorties'] = $this->db->fetch($sql, [
            ':date_debut' => $dateDebut,
            ':date_fin' => $dateFin
        ]);

        // Sorties par motif
        $sql = "SELECT motif, COUNT(*) as nb, COALESCE(SUM(quantite), 0) as quantite
                FROM {$this->table}
                WHERE type = 'sortie'
                AND DATE(date_mouvement) BETWEEN :date_debut AND :date_fin
                GROUP BY motif";
        $stats['sorties_par_motif'] = $this->db->fetchAll($sql, [
            ':date_debut' => $dateDebut,
            ':date_fin' => $dateFin
        ]);

        return $stats;
    }

    /**
     * Effectue un inventaire (ajustement de stock)
     * 
     * @param int $pieceId
     * @param float $stockReel
     * @param int $userId
     * @param string $notes
     * @return int ID du mouvement
     */
    public function inventaire($pieceId, $stockReel, $userId, $notes = '')
    {
        $pieceModel = new Piece();
        $piece = $pieceModel->findById($pieceId);

        if (!$piece) {
            throw new \Exception("Pièce introuvable");
        }

        $stockTheorique = $piece['stock_actuel'];
        $ecart = $stockReel - $stockTheorique;

        if ($ecart == 0) {
            // Pas d'écart, pas de mouvement
            return 0;
        }

        // Créer un mouvement d'ajustement
        $data = [
            'piece_id' => $pieceId,
            'type' => $ecart > 0 ? 'entree' : 'sortie',
            'quantite' => abs($ecart),
            'prix_unitaire' => $piece['prix_unitaire_cump'],
            'valeur_totale' => abs($ecart) * $piece['prix_unitaire_cump'],
            'motif' => 'inventaire',
            'notes' => $notes . " | Écart inventaire: " . $ecart,
            'user_id' => $userId,
            'date_mouvement' => date('Y-m-d H:i:s')
        ];

        return $this->insert($data);
    }
}
