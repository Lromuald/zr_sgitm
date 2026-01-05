<?php
namespace App\Controllers;

use App\Core\Controller;

/**
 * Contrôleur des Maintenances
 * Gestion des maintenances préventives et correctives avec pièces
 */
class MaintenanceController extends Controller
{
    private $maintenanceModel;
    private $enginModel;
    private $pieceModel;
    private $mouvementStockModel;

    public function __construct()
    {
        parent::__construct();
        $this->maintenanceModel = $this->model('Maintenance');
        $this->enginModel = $this->model('Engin');
        $this->pieceModel = $this->model('Piece');
        $this->mouvementStockModel = $this->model('MouvementStock');
    }

    /**
     * Liste toutes les maintenances avec filtres
     */
    public function index()
    {
        $this->requireAuth();
        
        // Récupérer les filtres
        $filters = [
            'statut' => $_GET['statut'] ?? null,
            'type' => $_GET['type'] ?? null,
            'engin_id' => $_GET['engin_id'] ?? null,
            'date_debut' => $_GET['date_debut'] ?? null,
            'date_fin' => $_GET['date_fin'] ?? null
        ];
        
        // Construction de la requête avec filtres
        $where = [];
        $params = [];
        
        if ($filters['statut']) {
            $where[] = "m.statut = :statut";
            $params[':statut'] = $filters['statut'];
        }
        
        if ($filters['type']) {
            $where[] = "m.type = :type";
            $params[':type'] = $filters['type'];
        }
        
        if ($filters['engin_id']) {
            $where[] = "m.engin_id = :engin_id";
            $params[':engin_id'] = $filters['engin_id'];
        }
        
        if ($filters['date_debut']) {
            $where[] = "DATE(m.date_planifiee) >= :date_debut";
            $params[':date_debut'] = $filters['date_debut'];
        }
        
        if ($filters['date_fin']) {
            $where[] = "DATE(m.date_planifiee) <= :date_fin";
            $params[':date_fin'] = $filters['date_fin'];
        }
        
        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
        
        $sql = "SELECT m.*, e.immatriculation, e.type as engin_type, e.marque, e.modele
                FROM maintenances m
                LEFT JOIN engins e ON m.engin_id = e.id
                $whereClause
                ORDER BY m.date_planifiee DESC";
        
        $maintenances = $this->db->fetchAll($sql, $params);
        
        // Récupérer liste engins pour filtre
        $engins = $this->enginModel->findAll();
        
        $data = [
            'title' => 'Gestion des Maintenances',
            'maintenances' => $maintenances,
            'engins' => $engins,
            'filters' => $filters,
            'user' => $_SESSION
        ];
        
        $this->view('maintenances/index', $data);
    }

    /**
     * Formulaire création maintenance
     */
    public function create()
    {
        $this->requireRole([ROLE_ADMIN, ROLE_MAINTENANCE]);
        
        // Récupérer les engins
        $engins = $this->enginModel->findAll();
        
        $data = [
            'title' => 'Nouvelle Maintenance',
            'engins' => $engins,
            'user' => $_SESSION,
            'csrf_token' => $this->generateCsrfToken()
        ];
        
        $this->view('maintenances/create', $data);
    }

    /**
     * Enregistrement nouvelle maintenance
     */
    public function store()
    {
        $this->requireRole([ROLE_ADMIN, ROLE_MAINTENANCE]);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/maintenances');
            return;
        }
        
        // Vérifier le token CSRF
        if (!$this->verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            $_SESSION['error'] = "Token de sécurité invalide";
            $this->redirect('/maintenances/create');
            return;
        }
        
        // Validation
        $errors = [];
        
        if (empty($_POST['engin_id'])) {
            $errors[] = "L'engin est requis";
        }
        
        if (empty($_POST['type'])) {
            $errors[] = "Le type de maintenance est requis";
        } elseif (!in_array($_POST['type'], ['preventive', 'corrective'])) {
            $errors[] = "Type de maintenance invalide";
        }
        
        if (empty($_POST['date_planifiee'])) {
            $errors[] = "La date planifiée est requise";
        }
        
        if (!empty($errors)) {
            $_SESSION['error'] = implode('<br>', $errors);
            $this->redirect('/maintenances/create');
            return;
        }
        
        // Préparer les données
        $data = [
            'engin_id' => intval($_POST['engin_id']),
            'type' => $this->sanitize($_POST['type']),
            'date_planifiee' => $_POST['date_planifiee'],
            'description' => $this->sanitize($_POST['description'] ?? ''),
            'kilometrage_intervention' => !empty($_POST['kilometrage_intervention']) ? floatval($_POST['kilometrage_intervention']) : null,
            'cout_main_oeuvre_estime' => !empty($_POST['cout_main_oeuvre_estime']) ? floatval($_POST['cout_main_oeuvre_estime']) : 0,
            'statut' => 'planifiee',
            'pieces_utilisees' => '[]',
            'cout_pieces' => 0,
            'cout_main_oeuvre' => 0,
            'cout_total' => 0,
            'user_id' => $_SESSION['user_id']
        ];
        
        try {
            $id = $this->maintenanceModel->insert($data);
            $_SESSION['success'] = "Maintenance créée avec succès";
            $this->redirect('/maintenances/edit/' . $id);
        } catch (\Exception $e) {
            $_SESSION['error'] = "Erreur lors de la création : " . $e->getMessage();
            $this->redirect('/maintenances/create');
        }
    }

    /**
     * Formulaire modification + gestion pièces
     */
    public function edit($id)
    {
        $this->requireRole([ROLE_ADMIN, ROLE_MAINTENANCE]);
        
        $maintenance = $this->maintenanceModel->findById($id);
        
        if (!$maintenance) {
            $_SESSION['error'] = "Maintenance introuvable";
            $this->redirect('/maintenances');
            return;
        }
        
        // Récupérer les détails de l'engin
        $engin = $this->enginModel->findById($maintenance['engin_id']);
        
        // Récupérer toutes les pièces disponibles
        $pieces = $this->pieceModel->findAll();
        
        // Décoder les pièces utilisées
        $piecesUtilisees = json_decode($maintenance['pieces_utilisees'] ?? '[]', true);
        
        // Enrichir avec les infos des pièces
        foreach ($piecesUtilisees as &$piece) {
            $pieceInfo = $this->pieceModel->findById($piece['piece_id']);
            if ($pieceInfo) {
                $piece['reference'] = $pieceInfo['reference'];
                $piece['designation'] = $pieceInfo['designation'];
            }
        }
        
        $data = [
            'title' => 'Modifier Maintenance #' . $id,
            'maintenance' => $maintenance,
            'engin' => $engin,
            'pieces' => $pieces,
            'pieces_utilisees' => $piecesUtilisees,
            'user' => $_SESSION,
            'csrf_token' => $this->generateCsrfToken()
        ];
        
        $this->view('maintenances/edit', $data);
    }

    /**
     * Mise à jour maintenance
     */
    public function update($id)
    {
        $this->requireRole([ROLE_ADMIN, ROLE_MAINTENANCE]);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/maintenances');
            return;
        }
        
        // Vérifier le token CSRF
        if (!$this->verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            $_SESSION['error'] = "Token de sécurité invalide";
            $this->redirect('/maintenances/edit/' . $id);
            return;
        }
        
        $maintenance = $this->maintenanceModel->findById($id);
        
        if (!$maintenance) {
            $_SESSION['error'] = "Maintenance introuvable";
            $this->redirect('/maintenances');
            return;
        }
        
        $data = [];
        $ancienStatut = $maintenance['statut'];
        $nouveauStatut = $_POST['statut'] ?? $ancienStatut;
        
        // Mise à jour des champs standards
        if (isset($_POST['description'])) {
            $data['description'] = $this->sanitize($_POST['description']);
        }
        
        if (isset($_POST['cout_main_oeuvre'])) {
            $data['cout_main_oeuvre'] = floatval($_POST['cout_main_oeuvre']);
        }
        
        try {
            $this->db->beginTransaction();
            
            // Gestion des changements de statut
            if ($nouveauStatut !== $ancienStatut) {
                $data['statut'] = $nouveauStatut;
                
                // Passage à en_cours
                if ($nouveauStatut === 'en_cours' && $ancienStatut === 'planifiee') {
                    $data['date_debut_reelle'] = date('Y-m-d H:i:s');
                    
                    // Sortir les pièces du stock
                    $piecesUtilisees = json_decode($maintenance['pieces_utilisees'] ?? '[]', true);
                    foreach ($piecesUtilisees as $piece) {
                        $this->mouvementStockModel->insert([
                            'piece_id' => $piece['piece_id'],
                            'type_mouvement' => 'sortie',
                            'quantite' => $piece['quantite'],
                            'motif' => 'Maintenance engin #' . $maintenance['engin_id'],
                            'reference_id' => $id,
                            'reference_type' => 'maintenance',
                            'user_id' => $_SESSION['user_id']
                        ]);
                    }
                    
                    // Changer statut engin
                    $this->enginModel->update($maintenance['engin_id'], ['statut' => 'en_maintenance']);
                }
                
                // Passage à terminee
                if ($nouveauStatut === 'terminee' && $ancienStatut === 'en_cours') {
                    $data['date_fin_reelle'] = date('Y-m-d H:i:s');
                    
                    // Remettre engin disponible
                    $this->enginModel->update($maintenance['engin_id'], ['statut' => 'disponible']);
                }
            }
            
            // Recalculer le coût total si nécessaire
            if (isset($data['cout_main_oeuvre']) || !empty($_POST['recalculer'])) {
                $coutMainOeuvre = $data['cout_main_oeuvre'] ?? $maintenance['cout_main_oeuvre'];
                $coutPieces = $maintenance['cout_pieces'];
                $data['cout_total'] = $coutMainOeuvre + $coutPieces;
            }
            
            $this->maintenanceModel->update($id, $data);
            $this->db->commit();
            
            $_SESSION['success'] = "Maintenance mise à jour avec succès";
            $this->redirect('/maintenances/edit/' . $id);
            
        } catch (\Exception $e) {
            $this->db->rollback();
            $_SESSION['error'] = "Erreur lors de la mise à jour : " . $e->getMessage();
            $this->redirect('/maintenances/edit/' . $id);
        }
    }

    /**
     * Suppression (seulement si statut = planifiee ou annulee)
     */
    public function delete($id)
    {
        $this->requireRole([ROLE_ADMIN]);
        
        $maintenance = $this->maintenanceModel->findById($id);
        
        if (!$maintenance) {
            $_SESSION['error'] = "Maintenance introuvable";
            $this->redirect('/maintenances');
            return;
        }
        
        if (!in_array($maintenance['statut'], ['planifiee', 'annulee'])) {
            $_SESSION['error'] = "Seules les maintenances planifiées ou annulées peuvent être supprimées";
            $this->redirect('/maintenances');
            return;
        }
        
        try {
            $this->maintenanceModel->delete($id);
            $_SESSION['success'] = "Maintenance supprimée avec succès";
        } catch (\Exception $e) {
            $_SESSION['error'] = "Erreur lors de la suppression : " . $e->getMessage();
        }
        
        $this->redirect('/maintenances');
    }

    /**
     * Vue détaillée maintenance
     */
    public function show($id)
    {
        $this->requireAuth();
        
        $sql = "SELECT m.*, e.immatriculation, e.type as engin_type, e.marque, e.modele,
                u.nom as user_nom, u.prenom as user_prenom
                FROM maintenances m
                LEFT JOIN engins e ON m.engin_id = e.id
                LEFT JOIN users u ON m.user_id = u.id
                WHERE m.id = :id";
        
        $maintenance = $this->db->fetch($sql, [':id' => $id]);
        
        if (!$maintenance) {
            $_SESSION['error'] = "Maintenance introuvable";
            $this->redirect('/maintenances');
            return;
        }
        
        // Décoder et enrichir les pièces utilisées
        $piecesUtilisees = json_decode($maintenance['pieces_utilisees'] ?? '[]', true);
        
        foreach ($piecesUtilisees as &$piece) {
            $pieceInfo = $this->pieceModel->findById($piece['piece_id']);
            if ($pieceInfo) {
                $piece['reference'] = $pieceInfo['reference'];
                $piece['designation'] = $pieceInfo['designation'];
                $piece['montant'] = $piece['quantite'] * $piece['prix_unitaire_cump'];
            }
        }
        
        $data = [
            'title' => 'Détail Maintenance #' . $id,
            'maintenance' => $maintenance,
            'pieces_utilisees' => $piecesUtilisees,
            'user' => $_SESSION
        ];
        
        $this->view('maintenances/show', $data);
    }

    /**
     * Ajouter une pièce au JSON pieces_utilisees
     */
    public function ajouterPiece($maintenanceId)
    {
        $this->requireRole([ROLE_ADMIN, ROLE_MAINTENANCE]);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/maintenances/edit/' . $maintenanceId);
            return;
        }
        
        // Vérifier le token CSRF
        if (!$this->verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            $_SESSION['error'] = "Token de sécurité invalide";
            $this->redirect('/maintenances/edit/' . $maintenanceId);
            return;
        }
        
        $maintenance = $this->maintenanceModel->findById($maintenanceId);
        
        if (!$maintenance) {
            $_SESSION['error'] = "Maintenance introuvable";
            $this->redirect('/maintenances');
            return;
        }
        
        $pieceId = intval($_POST['piece_id']);
        $quantite = floatval($_POST['quantite']);
        
        if ($pieceId <= 0 || $quantite <= 0) {
            $_SESSION['error'] = "Données invalides";
            $this->redirect('/maintenances/edit/' . $maintenanceId);
            return;
        }
        
        // Récupérer le CUMP de la pièce
        $piece = $this->pieceModel->findById($pieceId);
        
        if (!$piece) {
            $_SESSION['error'] = "Pièce introuvable";
            $this->redirect('/maintenances/edit/' . $maintenanceId);
            return;
        }
        
        // Décoder les pièces actuelles
        $piecesUtilisees = json_decode($maintenance['pieces_utilisees'] ?? '[]', true);
        
        // Vérifier si la pièce existe déjà
        $pieceExiste = false;
        foreach ($piecesUtilisees as &$p) {
            if ($p['piece_id'] == $pieceId) {
                $p['quantite'] += $quantite;
                $pieceExiste = true;
                break;
            }
        }
        
        if (!$pieceExiste) {
            $piecesUtilisees[] = [
                'piece_id' => $pieceId,
                'quantite' => $quantite,
                'prix_unitaire_cump' => floatval($piece['cump'])
            ];
        }
        
        // Recalculer le coût des pièces
        $coutPieces = 0;
        foreach ($piecesUtilisees as $p) {
            $coutPieces += $p['quantite'] * $p['prix_unitaire_cump'];
        }
        
        $coutTotal = $coutPieces + floatval($maintenance['cout_main_oeuvre']);
        
        // Mettre à jour la maintenance
        try {
            $this->maintenanceModel->update($maintenanceId, [
                'pieces_utilisees' => json_encode($piecesUtilisees),
                'cout_pieces' => $coutPieces,
                'cout_total' => $coutTotal
            ]);
            
            $_SESSION['success'] = "Pièce ajoutée avec succès";
        } catch (\Exception $e) {
            $_SESSION['error'] = "Erreur lors de l'ajout : " . $e->getMessage();
        }
        
        $this->redirect('/maintenances/edit/' . $maintenanceId);
    }

    /**
     * Retirer une pièce du JSON
     */
    public function retirerPiece($maintenanceId, $pieceId)
    {
        $this->requireRole([ROLE_ADMIN, ROLE_MAINTENANCE]);
        
        $maintenance = $this->maintenanceModel->findById($maintenanceId);
        
        if (!$maintenance) {
            $_SESSION['error'] = "Maintenance introuvable";
            $this->redirect('/maintenances');
            return;
        }
        
        // Décoder les pièces actuelles
        $piecesUtilisees = json_decode($maintenance['pieces_utilisees'] ?? '[]', true);
        
        // Retirer la pièce
        $piecesUtilisees = array_filter($piecesUtilisees, function($p) use ($pieceId) {
            return $p['piece_id'] != $pieceId;
        });
        
        // Réindexer le tableau
        $piecesUtilisees = array_values($piecesUtilisees);
        
        // Recalculer le coût des pièces
        $coutPieces = 0;
        foreach ($piecesUtilisees as $p) {
            $coutPieces += $p['quantite'] * $p['prix_unitaire_cump'];
        }
        
        $coutTotal = $coutPieces + floatval($maintenance['cout_main_oeuvre']);
        
        // Mettre à jour la maintenance
        try {
            $this->maintenanceModel->update($maintenanceId, [
                'pieces_utilisees' => json_encode($piecesUtilisees),
                'cout_pieces' => $coutPieces,
                'cout_total' => $coutTotal
            ]);
            
            $_SESSION['success'] = "Pièce retirée avec succès";
        } catch (\Exception $e) {
            $_SESSION['error'] = "Erreur lors du retrait : " . $e->getMessage();
        }
        
        $this->redirect('/maintenances/edit/' . $maintenanceId);
    }
}
