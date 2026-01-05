<?php
namespace App\Controllers;

use App\Core\Controller;

/**
 * Contrôleur des Pièces
 * Gestion du catalogue des pièces et carburant
 */
class PieceController extends Controller
{
    private $pieceModel;
    private $fournisseurModel;

    public function __construct()
    {
        parent::__construct();
        $this->pieceModel = $this->model('Piece');
    }

    /**
     * Liste des pièces
     */
    public function index()
    {
        $this->requireAuth();
        
        $pieces = $this->pieceModel->getActivePieces();
        $criticalStock = $this->pieceModel->getCriticalStock();
        
        $data = [
            'title' => 'Gestion des Pièces',
            'pieces' => $pieces,
            'critical_stock' => $criticalStock,
            'user' => $_SESSION
        ];
        
        $this->view('pieces/index', $data);
    }

    /**
     * Formulaire de création
     */
    public function create()
    {
        $this->requireRole([ROLE_ADMIN, ROLE_GESTIONNAIRE_STOCK]);
        
        // Charger les fournisseurs pour le select
        $sql = "SELECT id, nom FROM fournisseurs WHERE statut = 'actif' ORDER BY nom";
        $fournisseurs = $this->db->fetchAll($sql);
        
        $data = [
            'title' => 'Ajouter une Pièce',
            'fournisseurs' => $fournisseurs,
            'csrf_token' => $this->generateCsrfToken(),
            'user' => $_SESSION
        ];
        
        $this->view('pieces/create', $data);
    }

    /**
     * Enregistrement d'une nouvelle pièce
     */
    public function store()
    {
        $this->requireRole([ROLE_ADMIN, ROLE_GESTIONNAIRE_STOCK]);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/pieces');
        }
        
        // Vérification CSRF
        if (!$this->verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            $_SESSION['error'] = 'Token CSRF invalide';
            $this->redirect('/pieces/create');
        }
        
        // Validation des données
        $errors = [];
        
        if (empty($_POST['reference'])) {
            $errors[] = 'La référence est obligatoire';
        }
        
        if (empty($_POST['description'])) {
            $errors[] = 'La description est obligatoire';
        }
        
        if (empty($_POST['type'])) {
            $errors[] = 'Le type est obligatoire';
        }
        
        if (!isset($_POST['seuil_alerte']) || $_POST['seuil_alerte'] < 0) {
            $errors[] = 'Le seuil d\'alerte doit être un nombre positif';
        }
        
        if (!empty($errors)) {
            $_SESSION['error'] = implode('<br>', $errors);
            $_SESSION['old_input'] = $_POST;
            $this->redirect('/pieces/create');
        }
        
        // Préparer les données
        $data = [
            'reference' => $this->sanitize($_POST['reference']),
            'description' => $this->sanitize($_POST['description']),
            'type' => $this->sanitize($_POST['type']),
            'seuil_alerte' => floatval($_POST['seuil_alerte']),
            'fournisseur_principal_id' => !empty($_POST['fournisseur_principal_id']) ? intval($_POST['fournisseur_principal_id']) : null,
            'unite_mesure' => $this->sanitize($_POST['unite_mesure'] ?? 'unite'),
            'statut' => 'actif',
            'stock_actuel' => 0,
            'prix_unitaire_cump' => 0
        ];
        
        // Gérer l'upload de la photo
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $uploadResult = $this->handlePhotoUpload($_FILES['photo']);
            if ($uploadResult['success']) {
                $data['photo_path'] = $uploadResult['path'];
            } else {
                $_SESSION['error'] = $uploadResult['error'];
                $_SESSION['old_input'] = $_POST;
                $this->redirect('/pieces/create');
            }
        }
        
        // Créer la pièce
        try {
            $pieceId = $this->pieceModel->create($data);
            
            if ($pieceId) {
                $_SESSION['success'] = 'Pièce créée avec succès';
                $this->redirect('/pieces');
            } else {
                $_SESSION['error'] = 'Erreur lors de la création de la pièce';
                $this->redirect('/pieces/create');
            }
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erreur: ' . $e->getMessage();
            $this->redirect('/pieces/create');
        }
    }

    /**
     * Formulaire de modification
     */
    public function edit($id)
    {
        $this->requireRole([ROLE_ADMIN, ROLE_GESTIONNAIRE_STOCK]);
        
        $piece = $this->pieceModel->getPieceWithFournisseur($id);
        
        if (!$piece) {
            $_SESSION['error'] = 'Pièce introuvable';
            $this->redirect('/pieces');
        }
        
        // Charger les fournisseurs
        $sql = "SELECT id, nom FROM fournisseurs WHERE statut = 'actif' ORDER BY nom";
        $fournisseurs = $this->db->fetchAll($sql);
        
        $data = [
            'title' => 'Modifier la Pièce',
            'piece' => $piece,
            'fournisseurs' => $fournisseurs,
            'csrf_token' => $this->generateCsrfToken(),
            'user' => $_SESSION
        ];
        
        $this->view('pieces/edit', $data);
    }

    /**
     * Mise à jour d'une pièce
     */
    public function update($id)
    {
        $this->requireRole([ROLE_ADMIN, ROLE_GESTIONNAIRE_STOCK]);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/pieces');
        }
        
        // Vérification CSRF
        if (!$this->verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            $_SESSION['error'] = 'Token CSRF invalide';
            $this->redirect('/pieces/edit/' . $id);
        }
        
        $piece = $this->pieceModel->findById($id);
        if (!$piece) {
            $_SESSION['error'] = 'Pièce introuvable';
            $this->redirect('/pieces');
        }
        
        // Validation
        $errors = [];
        
        if (empty($_POST['reference'])) {
            $errors[] = 'La référence est obligatoire';
        }
        
        if (empty($_POST['description'])) {
            $errors[] = 'La description est obligatoire';
        }
        
        if (!empty($errors)) {
            $_SESSION['error'] = implode('<br>', $errors);
            $this->redirect('/pieces/edit/' . $id);
        }
        
        // Préparer les données
        $data = [
            'reference' => $this->sanitize($_POST['reference']),
            'description' => $this->sanitize($_POST['description']),
            'type' => $this->sanitize($_POST['type']),
            'seuil_alerte' => floatval($_POST['seuil_alerte']),
            'fournisseur_principal_id' => !empty($_POST['fournisseur_principal_id']) ? intval($_POST['fournisseur_principal_id']) : null,
            'unite_mesure' => $this->sanitize($_POST['unite_mesure'] ?? 'unite'),
            'statut' => $this->sanitize($_POST['statut'] ?? 'actif')
        ];
        
        // Gérer l'upload de la photo
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $uploadResult = $this->handlePhotoUpload($_FILES['photo']);
            if ($uploadResult['success']) {
                $data['photo_path'] = $uploadResult['path'];
                // Supprimer l'ancienne photo si elle existe
                if (!empty($piece['photo_path']) && file_exists(UPLOAD_PATH . '/photos/' . $piece['photo_path'])) {
                    unlink(UPLOAD_PATH . '/photos/' . $piece['photo_path']);
                }
            }
        }
        
        // Mettre à jour
        try {
            $result = $this->pieceModel->update($id, $data);
            
            if ($result) {
                $_SESSION['success'] = 'Pièce modifiée avec succès';
                $this->redirect('/pieces');
            } else {
                $_SESSION['error'] = 'Erreur lors de la modification';
                $this->redirect('/pieces/edit/' . $id);
            }
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erreur: ' . $e->getMessage();
            $this->redirect('/pieces/edit/' . $id);
        }
    }

    /**
     * Suppression d'une pièce
     */
    public function delete($id)
    {
        $this->requireRole([ROLE_ADMIN]);
        
        $piece = $this->pieceModel->findById($id);
        
        if (!$piece) {
            $_SESSION['error'] = 'Pièce introuvable';
            $this->redirect('/pieces');
        }
        
        // Vérifier si la pièce a été utilisée dans des mouvements
        $sql = "SELECT COUNT(*) as count FROM mouvements_stock WHERE piece_id = :piece_id";
        $result = $this->db->fetch($sql, [':piece_id' => $id]);
        
        if ($result['count'] > 0) {
            // Ne pas supprimer, juste désactiver
            $this->pieceModel->update($id, ['statut' => 'inactif']);
            $_SESSION['success'] = 'Pièce désactivée (historique de mouvements existant)';
        } else {
            // Supprimer la photo si elle existe
            if (!empty($piece['photo_path']) && file_exists(UPLOAD_PATH . '/photos/' . $piece['photo_path'])) {
                unlink(UPLOAD_PATH . '/photos/' . $piece['photo_path']);
            }
            
            $this->pieceModel->delete($id);
            $_SESSION['success'] = 'Pièce supprimée avec succès';
        }
        
        $this->redirect('/pieces');
    }

    /**
     * Gère l'upload d'une photo de pièce
     */
    private function handlePhotoUpload($file)
    {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
        $maxSize = 5 * 1024 * 1024; // 5MB
        
        if (!in_array($file['type'], $allowedTypes)) {
            return ['success' => false, 'error' => 'Type de fichier non autorisé (JPEG, PNG uniquement)'];
        }
        
        if ($file['size'] > $maxSize) {
            return ['success' => false, 'error' => 'Fichier trop volumineux (max 5MB)'];
        }
        
        $uploadDir = UPLOAD_PATH . '/photos/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'piece_' . uniqid() . '.' . $extension;
        $destination = $uploadDir . $filename;
        
        if (move_uploaded_file($file['tmp_name'], $destination)) {
            return ['success' => true, 'path' => $filename];
        }
        
        return ['success' => false, 'error' => 'Erreur lors de l\'upload du fichier'];
    }
}
