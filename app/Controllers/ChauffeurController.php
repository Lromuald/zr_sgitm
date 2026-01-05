<?php
namespace App\Controllers;

use App\Core\Controller;

/**
 * Contrôleur des Chauffeurs
 * Gestion des chauffeurs avec vérification de validité des permis
 */
class ChauffeurController extends Controller
{
    private $chauffeurModel;

    public function __construct()
    {
        parent::__construct();
        $this->chauffeurModel = $this->model('Chauffeur');
    }

    /**
     * Liste des chauffeurs
     */
    public function index()
    {
        $this->requireAuth();
        
        $chauffeurs = $this->chauffeurModel->getAll();
        
        $data = [
            'title' => 'Gestion des Chauffeurs',
            'chauffeurs' => $chauffeurs,
            'user' => $_SESSION
        ];
        
        $this->view('chauffeurs/index', $data);
    }

    /**
     * Formulaire de création
     */
    public function create()
    {
        $this->requireRole([ROLE_ADMIN, ROLE_COMMERCIAL]);
        
        $data = [
            'title' => 'Ajouter un Chauffeur',
            'csrf_token' => $this->generateCsrfToken(),
            'user' => $_SESSION
        ];
        
        $this->view('chauffeurs/create', $data);
    }

    /**
     * Enregistrement d'un chauffeur
     */
    public function store()
    {
        $this->requireRole([ROLE_ADMIN, ROLE_COMMERCIAL]);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/chauffeurs');
        }
        
        if (!$this->verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            $_SESSION['error'] = 'Token CSRF invalide';
            $this->redirect('/chauffeurs/create');
        }
        
        // Validation
        $errors = [];
        
        if (empty($_POST['nom'])) {
            $errors[] = 'Le nom est obligatoire';
        }
        
        if (empty($_POST['prenom'])) {
            $errors[] = 'Le prénom est obligatoire';
        }
        
        if (empty($_POST['numero_permis'])) {
            $errors[] = 'Le numéro de permis est obligatoire';
        }
        
        if (empty($_POST['categorie_permis'])) {
            $errors[] = 'La catégorie de permis est obligatoire';
        }
        
        if (empty($_POST['date_expiration_permis'])) {
            $errors[] = 'La date d\'expiration du permis est obligatoire';
        } elseif (strtotime($_POST['date_expiration_permis']) < time()) {
            $errors[] = 'Le permis est déjà expiré';
        }
        
        if (!empty($errors)) {
            $_SESSION['error'] = implode('<br>', $errors);
            $_SESSION['old_input'] = $_POST;
            $this->redirect('/chauffeurs/create');
        }
        
        $data = [
            'nom' => strtoupper($this->sanitize($_POST['nom'])),
            'prenom' => $this->sanitize($_POST['prenom']),
            'telephone' => $this->sanitize($_POST['telephone'] ?? ''),
            'email' => $this->sanitize($_POST['email'] ?? ''),
            'adresse' => $this->sanitize($_POST['adresse'] ?? ''),
            'numero_permis' => $this->sanitize($_POST['numero_permis']),
            'categorie_permis' => $this->sanitize($_POST['categorie_permis']),
            'date_obtention_permis' => !empty($_POST['date_obtention_permis']) ? $_POST['date_obtention_permis'] : null,
            'date_expiration_permis' => $_POST['date_expiration_permis'],
            'statut' => 'actif',
            'notes' => $this->sanitize($_POST['notes'] ?? '')
        ];
        
        // Gérer l'upload de la photo
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $uploadResult = $this->handlePhotoUpload($_FILES['photo']);
            if ($uploadResult['success']) {
                $data['photo_path'] = $uploadResult['path'];
            }
        }
        
        try {
            $chauffeurId = $this->chauffeurModel->insert($data);
            
            if ($chauffeurId) {
                $_SESSION['success'] = 'Chauffeur créé avec succès';
                $this->redirect('/chauffeurs');
            } else {
                $_SESSION['error'] = 'Erreur lors de la création du chauffeur';
                $this->redirect('/chauffeurs/create');
            }
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erreur: ' . $e->getMessage();
            $this->redirect('/chauffeurs/create');
        }
    }

    /**
     * Formulaire de modification
     */
    public function edit($id)
    {
        $this->requireRole([ROLE_ADMIN, ROLE_COMMERCIAL]);
        
        $chauffeur = $this->chauffeurModel->findById($id);
        
        if (!$chauffeur) {
            $_SESSION['error'] = 'Chauffeur introuvable';
            $this->redirect('/chauffeurs');
        }
        
        $data = [
            'title' => 'Modifier le Chauffeur',
            'chauffeur' => $chauffeur,
            'csrf_token' => $this->generateCsrfToken(),
            'user' => $_SESSION
        ];
        
        $this->view('chauffeurs/edit', $data);
    }

    /**
     * Mise à jour d'un chauffeur
     */
    public function update($id)
    {
        $this->requireRole([ROLE_ADMIN, ROLE_COMMERCIAL]);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/chauffeurs');
        }
        
        if (!$this->verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            $_SESSION['error'] = 'Token CSRF invalide';
            $this->redirect('/chauffeurs/edit/' . $id);
        }
        
        $chauffeur = $this->chauffeurModel->findById($id);
        if (!$chauffeur) {
            $_SESSION['error'] = 'Chauffeur introuvable';
            $this->redirect('/chauffeurs');
        }
        
        $data = [
            'nom' => strtoupper($this->sanitize($_POST['nom'])),
            'prenom' => $this->sanitize($_POST['prenom']),
            'telephone' => $this->sanitize($_POST['telephone'] ?? ''),
            'email' => $this->sanitize($_POST['email'] ?? ''),
            'adresse' => $this->sanitize($_POST['adresse'] ?? ''),
            'numero_permis' => $this->sanitize($_POST['numero_permis']),
            'categorie_permis' => $this->sanitize($_POST['categorie_permis']),
            'date_obtention_permis' => !empty($_POST['date_obtention_permis']) ? $_POST['date_obtention_permis'] : null,
            'date_expiration_permis' => $_POST['date_expiration_permis'],
            'statut' => $this->sanitize($_POST['statut']),
            'notes' => $this->sanitize($_POST['notes'] ?? '')
        ];
        
        // Gérer l'upload de la photo
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $uploadResult = $this->handlePhotoUpload($_FILES['photo']);
            if ($uploadResult['success']) {
                $data['photo_path'] = $uploadResult['path'];
                if (!empty($chauffeur['photo_path']) && file_exists(UPLOAD_PATH . '/photos/' . $chauffeur['photo_path'])) {
                    unlink(UPLOAD_PATH . '/photos/' . $chauffeur['photo_path']);
                }
            }
        }
        
        try {
            $result = $this->chauffeurModel->update($id, $data);
            
            if ($result) {
                $_SESSION['success'] = 'Chauffeur modifié avec succès';
                $this->redirect('/chauffeurs');
            } else {
                $_SESSION['error'] = 'Erreur lors de la modification';
                $this->redirect('/chauffeurs/edit/' . $id);
            }
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erreur: ' . $e->getMessage();
            $this->redirect('/chauffeurs/edit/' . $id);
        }
    }

    /**
     * Suppression d'un chauffeur
     */
    public function delete($id)
    {
        $this->requireRole([ROLE_ADMIN]);
        
        $chauffeur = $this->chauffeurModel->findById($id);
        
        if (!$chauffeur) {
            $_SESSION['error'] = 'Chauffeur introuvable';
            $this->redirect('/chauffeurs');
        }
        
        // Vérifier les dépendances
        $sql = "SELECT COUNT(*) as count FROM livraisons WHERE chauffeur_id = :id";
        $result = $this->db->fetch($sql, [':id' => $id]);
        
        if ($result['count'] > 0) {
            $_SESSION['error'] = 'Impossible de supprimer ce chauffeur (historique de livraisons)';
            $this->redirect('/chauffeurs');
        }
        
        try {
            if (!empty($chauffeur['photo_path']) && file_exists(UPLOAD_PATH . '/photos/' . $chauffeur['photo_path'])) {
                unlink(UPLOAD_PATH . '/photos/' . $chauffeur['photo_path']);
            }
            
            $this->chauffeurModel->delete($id);
            $_SESSION['success'] = 'Chauffeur supprimé avec succès';
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erreur: ' . $e->getMessage();
        }
        
        $this->redirect('/chauffeurs');
    }

    private function handlePhotoUpload($file)
    {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
        $maxSize = 5 * 1024 * 1024;
        
        if (!in_array($file['type'], $allowedTypes)) {
            return ['success' => false, 'error' => 'Type de fichier non autorisé'];
        }
        
        if ($file['size'] > $maxSize) {
            return ['success' => false, 'error' => 'Fichier trop volumineux'];
        }
        
        $uploadDir = UPLOAD_PATH . '/photos/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'chauffeur_' . uniqid() . '.' . $extension;
        $destination = $uploadDir . $filename;
        
        if (move_uploaded_file($file['tmp_name'], $destination)) {
            return ['success' => true, 'path' => $filename];
        }
        
        return ['success' => false, 'error' => 'Erreur lors de l\'upload'];
    }
}
