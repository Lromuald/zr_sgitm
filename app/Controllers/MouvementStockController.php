<?php
namespace App\Controllers;

use App\Core\Controller;

/**
 * Contrôleur des Mouvements de Stock
 * Gestion des entrées et sorties de stock (méthode CUMP)
 */
class MouvementStockController extends Controller
{
    private $mouvementModel;
    private $pieceModel;

    public function __construct()
    {
        parent::__construct();
        $this->mouvementModel = $this->model('MouvementStock');
        $this->pieceModel = $this->model('Piece');
    }

    /**
     * Liste des mouvements de stock
     */
    public function index()
    {
        $this->requireAuth();
        
        // Filtres
        $type = $_GET['type'] ?? '';
        $pieceId = $_GET['piece_id'] ?? '';
        $dateDebut = $_GET['date_debut'] ?? '';
        $dateFin = $_GET['date_fin'] ?? '';
        
        // Récupérer les mouvements
        if (!empty($dateDebut) && !empty($dateFin)) {
            $mouvements = $this->mouvementModel->getByPeriode($dateDebut, $dateFin);
        } else {
            $mouvements = $this->mouvementModel->getRecentMovements(100);
        }
        
        // Appliquer les filtres
        if ($type) {
            $mouvements = array_filter($mouvements, function($m) use ($type) {
                return $m['type'] === $type;
            });
        }
        
        if ($pieceId) {
            $mouvements = array_filter($mouvements, function($m) use ($pieceId) {
                return $m['piece_id'] == $pieceId;
            });
        }
        
        // Liste des pièces pour le filtre
        $pieces = $this->pieceModel->getActivePieces();
        
        $data = [
            'title' => 'Mouvements de Stock',
            'mouvements' => $mouvements,
            'pieces' => $pieces,
            'filters' => [
                'type' => $type,
                'piece_id' => $pieceId,
                'date_debut' => $dateDebut,
                'date_fin' => $dateFin
            ],
            'user' => $_SESSION
        ];
        
        $this->view('mouvements_stock/index', $data);
    }

    /**
     * Formulaire d'ajout de mouvement
     */
    public function create()
    {
        $this->requireRole([ROLE_ADMIN, ROLE_GESTIONNAIRE_STOCK]);
        
        $pieces = $this->pieceModel->getActivePieces();
        
        // Liste des engins pour les sorties
        $sql = "SELECT id, immatriculation, type FROM engins WHERE statut != 'hors_service' ORDER BY immatriculation";
        $engins = $this->db->fetchAll($sql);
        
        // Liste des maintenances en cours pour les sorties
        $sql = "SELECT m.id, m.description, e.immatriculation 
                FROM maintenances m
                LEFT JOIN engins e ON m.engin_id = e.id
                WHERE m.statut = 'en_cours'
                ORDER BY m.date_debut_reelle DESC";
        $maintenances = $this->db->fetchAll($sql);
        
        $data = [
            'title' => 'Nouveau Mouvement de Stock',
            'pieces' => $pieces,
            'engins' => $engins,
            'maintenances' => $maintenances,
            'csrf_token' => $this->generateCsrfToken(),
            'user' => $_SESSION
        ];
        
        $this->view('mouvements_stock/create', $data);
    }

    /**
     * Enregistrement d'un mouvement
     */
    public function store()
    {
        $this->requireRole([ROLE_ADMIN, ROLE_GESTIONNAIRE_STOCK]);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/mouvements-stock');
        }
        
        // Vérification CSRF
        if (!$this->verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            $_SESSION['error'] = 'Token CSRF invalide';
            $this->redirect('/mouvements-stock/create');
        }
        
        // Validation
        $errors = [];
        
        if (empty($_POST['piece_id'])) {
            $errors[] = 'La pièce est obligatoire';
        }
        
        if (empty($_POST['type'])) {
            $errors[] = 'Le type de mouvement est obligatoire';
        }
        
        if (empty($_POST['quantite']) || $_POST['quantite'] <= 0) {
            $errors[] = 'La quantité doit être supérieure à 0';
        }
        
        $type = $_POST['type'];
        
        // Validation spécifique pour les entrées
        if ($type === 'entree') {
            if (empty($_POST['prix_unitaire']) || $_POST['prix_unitaire'] <= 0) {
                $errors[] = 'Le prix unitaire est obligatoire pour une entrée';
            }
        }
        
        // Validation spécifique pour les sorties
        if ($type === 'sortie') {
            if (empty($_POST['motif'])) {
                $errors[] = 'Le motif est obligatoire pour une sortie';
            }
            
            // Vérifier le stock disponible
            $pieceId = intval($_POST['piece_id']);
            $quantite = floatval($_POST['quantite']);
            
            if (!$this->pieceModel->hasEnoughStock($pieceId, $quantite)) {
                $errors[] = 'Stock insuffisant pour cette pièce';
            }
        }
        
        if (!empty($errors)) {
            $_SESSION['error'] = implode('<br>', $errors);
            $_SESSION['old_input'] = $_POST;
            $this->redirect('/mouvements-stock/create');
        }
        
        // Préparer les données
        $data = [
            'piece_id' => intval($_POST['piece_id']),
            'type' => $this->sanitize($type),
            'quantite' => floatval($_POST['quantite']),
            'user_id' => $_SESSION['user_id'],
            'notes' => $this->sanitize($_POST['notes'] ?? '')
        ];
        
        if ($type === 'entree') {
            $data['prix_unitaire'] = floatval($_POST['prix_unitaire']);
            $data['valeur_totale'] = $data['quantite'] * $data['prix_unitaire'];
            $data['numero_bon_commande'] = $this->sanitize($_POST['numero_bon_commande'] ?? '');
            $data['fournisseur_id'] = !empty($_POST['fournisseur_id']) ? intval($_POST['fournisseur_id']) : null;
        } else {
            // Sortie
            $data['motif'] = $this->sanitize($_POST['motif']);
            $data['engin_id'] = !empty($_POST['engin_id']) ? intval($_POST['engin_id']) : null;
            $data['maintenance_id'] = !empty($_POST['maintenance_id']) ? intval($_POST['maintenance_id']) : null;
        }
        
        // Enregistrer le mouvement
        try {
            $mouvementId = $this->mouvementModel->create($data);
            
            if ($mouvementId) {
                $_SESSION['success'] = 'Mouvement enregistré avec succès';
                $this->redirect('/mouvements-stock');
            } else {
                $_SESSION['error'] = 'Erreur lors de l\'enregistrement du mouvement';
                $this->redirect('/mouvements-stock/create');
            }
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erreur: ' . $e->getMessage();
            $_SESSION['old_input'] = $_POST;
            $this->redirect('/mouvements-stock/create');
        }
    }

    /**
     * Suppression d'un mouvement (admin uniquement)
     */
    public function delete($id)
    {
        $this->requireRole([ROLE_ADMIN]);
        
        $mouvement = $this->mouvementModel->findById($id);
        
        if (!$mouvement) {
            $_SESSION['error'] = 'Mouvement introuvable';
            $this->redirect('/mouvements-stock');
        }
        
        // Vérifier si ce n'est pas le dernier mouvement de cette pièce
        // pour éviter de fausser le calcul du CUMP
        $sql = "SELECT MAX(id) as dernier_id FROM mouvements_stock WHERE piece_id = :piece_id";
        $result = $this->db->fetch($sql, [':piece_id' => $mouvement['piece_id']]);
        
        if ($result['dernier_id'] != $id) {
            $_SESSION['error'] = 'Impossible de supprimer ce mouvement car il y a des mouvements plus récents';
            $this->redirect('/mouvements-stock');
        }
        
        // Supprimer
        try {
            // Il faudrait recalculer le CUMP après suppression
            // Pour simplifier, on empêche la suppression si c'est une entrée
            if ($mouvement['type'] === 'entree') {
                $_SESSION['error'] = 'Impossible de supprimer une entrée (calcul CUMP)';
                $this->redirect('/mouvements-stock');
            }
            
            $this->mouvementModel->delete($id);
            $_SESSION['success'] = 'Mouvement supprimé avec succès';
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erreur: ' . $e->getMessage();
        }
        
        $this->redirect('/mouvements-stock');
    }

    /**
     * API: Récupère les informations d'une pièce
     */
    public function getPieceInfo($pieceId)
    {
        $this->requireAuth();
        
        $piece = $this->pieceModel->findById($pieceId);
        
        if (!$piece) {
            $this->json(['error' => 'Pièce introuvable'], 404);
        }
        
        $this->json([
            'id' => $piece['id'],
            'reference' => $piece['reference'],
            'description' => $piece['description'],
            'stock_actuel' => $piece['stock_actuel'],
            'prix_unitaire_cump' => $piece['prix_unitaire_cump'],
            'seuil_alerte' => $piece['seuil_alerte'],
            'unite_mesure' => $piece['unite_mesure']
        ]);
    }
}
