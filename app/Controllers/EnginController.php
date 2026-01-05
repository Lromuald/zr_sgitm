<?php
namespace App\Controllers;

use App\Core\Controller;

/**
 * Contrôleur des Engins
 * Gestion du parc d'engins et vérification de conformité
 */
class EnginController extends Controller
{
    private $enginModel;
    private $documentModel;

    public function __construct()
    {
        parent::__construct();
        $this->enginModel = $this->model('Engin');
        $this->documentModel = $this->model('DocumentEngin');
    }

    /**
     * Liste des engins
     */
    public function index()
    {
        $this->requireAuth();
        
        $engins = $this->enginModel->getAllWithStats();
        
        // Enrichir avec les infos de conformité des documents
        foreach ($engins as &$engin) {
            $conformite = $this->documentModel->verifierConformiteEngin($engin['id']);
            $engin['conforme'] = $conformite['conforme'];
            $engin['documents_manquants'] = $conformite['documents_manquants'];
            $engin['documents_expires'] = $conformite['documents_expires'];
        }
        
        $data = [
            'title' => 'Gestion des Engins',
            'engins' => $engins,
            'user' => $_SESSION
        ];
        
        $this->view('engins/index', $data);
    }

    /**
     * Formulaire de création
     */
    public function create()
    {
        $this->requireRole([ROLE_ADMIN, ROLE_MAINTENANCE]);
        
        $data = [
            'title' => 'Ajouter un Engin',
            'csrf_token' => $this->generateCsrfToken(),
            'user' => $_SESSION
        ];
        
        $this->view('engins/create', $data);
    }

    /**
     * Enregistrement d'un engin
     */
    public function store()
    {
        $this->requireRole([ROLE_ADMIN, ROLE_MAINTENANCE]);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/engins');
        }
        
        // Vérification CSRF
        if (!$this->verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            $_SESSION['error'] = 'Token CSRF invalide';
            $this->redirect('/engins/create');
        }
        
        // Validation
        $errors = [];
        
        if (empty($_POST['immatriculation'])) {
            $errors[] = 'L\'immatriculation est obligatoire';
        }
        
        if (empty($_POST['type'])) {
            $errors[] = 'Le type d\'engin est obligatoire';
        }
        
        if (empty($_POST['marque'])) {
            $errors[] = 'La marque est obligatoire';
        }
        
        if (empty($_POST['modele'])) {
            $errors[] = 'Le modèle est obligatoire';
        }
        
        // Vérifier l'unicité de l'immatriculation
        if (!empty($_POST['immatriculation'])) {
            $existing = $this->enginModel->findOne(['immatriculation' => $_POST['immatriculation']]);
            if ($existing) {
                $errors[] = 'Cette immatriculation existe déjà';
            }
        }
        
        if (!empty($errors)) {
            $_SESSION['error'] = implode('<br>', $errors);
            $_SESSION['old_input'] = $_POST;
            $this->redirect('/engins/create');
        }
        
        // Préparer les données
        $data = [
            'immatriculation' => strtoupper($this->sanitize($_POST['immatriculation'])),
            'type' => $this->sanitize($_POST['type']),
            'marque' => $this->sanitize($_POST['marque']),
            'modele' => $this->sanitize($_POST['modele']),
            'numero_serie' => $this->sanitize($_POST['numero_serie'] ?? ''),
            'annee_fabrication' => !empty($_POST['annee_fabrication']) ? intval($_POST['annee_fabrication']) : null,
            'date_achat' => !empty($_POST['date_achat']) ? $_POST['date_achat'] : null,
            'montant_achat' => !empty($_POST['montant_achat']) ? floatval($_POST['montant_achat']) : null,
            'kilometrage' => !empty($_POST['kilometrage']) ? floatval($_POST['kilometrage']) : 0,
            'heures_moteur' => !empty($_POST['heures_moteur']) ? floatval($_POST['heures_moteur']) : 0,
            'capacite_charge' => !empty($_POST['capacite_charge']) ? floatval($_POST['capacite_charge']) : null,
            'consommation_moyenne_theorique' => !empty($_POST['consommation_moyenne_theorique']) ? floatval($_POST['consommation_moyenne_theorique']) : null,
            'statut' => 'disponible',
            'notes' => $this->sanitize($_POST['notes'] ?? '')
        ];
        
        // Gérer l'upload de la photo
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $uploadResult = $this->handlePhotoUpload($_FILES['photo']);
            if ($uploadResult['success']) {
                $data['photo_path'] = $uploadResult['path'];
            }
        }
        
        // Créer l'engin
        try {
            $enginId = $this->enginModel->create($data);
            
            if ($enginId) {
                $_SESSION['success'] = 'Engin créé avec succès';
                $this->redirect('/engins');
            } else {
                $_SESSION['error'] = 'Erreur lors de la création de l\'engin';
                $this->redirect('/engins/create');
            }
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erreur: ' . $e->getMessage();
            $this->redirect('/engins/create');
        }
    }

    /**
     * Formulaire de modification
     */
    public function edit($id)
    {
        $this->requireRole([ROLE_ADMIN, ROLE_MAINTENANCE]);
        
        $engin = $this->enginModel->findById($id);
        
        if (!$engin) {
            $_SESSION['error'] = 'Engin introuvable';
            $this->redirect('/engins');
        }
        
        // Récupérer la conformité des documents
        $conformite = $this->documentModel->verifierConformiteEngin($id);
        $engin['conforme'] = $conformite['conforme'];
        
        $data = [
            'title' => 'Modifier l\'Engin',
            'engin' => $engin,
            'csrf_token' => $this->generateCsrfToken(),
            'user' => $_SESSION
        ];
        
        $this->view('engins/edit', $data);
    }

    /**
     * Mise à jour d'un engin
     */
    public function update($id)
    {
        $this->requireRole([ROLE_ADMIN, ROLE_MAINTENANCE]);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/engins');
        }
        
        // Vérification CSRF
        if (!$this->verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            $_SESSION['error'] = 'Token CSRF invalide';
            $this->redirect('/engins/edit/' . $id);
        }
        
        $engin = $this->enginModel->findById($id);
        if (!$engin) {
            $_SESSION['error'] = 'Engin introuvable';
            $this->redirect('/engins');
        }
        
        // Validation
        $errors = [];
        
        if (empty($_POST['immatriculation'])) {
            $errors[] = 'L\'immatriculation est obligatoire';
        }
        
        // Vérifier l'unicité de l'immatriculation (sauf pour cet engin)
        if (!empty($_POST['immatriculation']) && $_POST['immatriculation'] !== $engin['immatriculation']) {
            $existing = $this->enginModel->findOne(['immatriculation' => $_POST['immatriculation']]);
            if ($existing) {
                $errors[] = 'Cette immatriculation existe déjà';
            }
        }
        
        if (!empty($errors)) {
            $_SESSION['error'] = implode('<br>', $errors);
            $this->redirect('/engins/edit/' . $id);
        }
        
        // Préparer les données
        $data = [
            'immatriculation' => strtoupper($this->sanitize($_POST['immatriculation'])),
            'type' => $this->sanitize($_POST['type']),
            'marque' => $this->sanitize($_POST['marque']),
            'modele' => $this->sanitize($_POST['modele']),
            'numero_serie' => $this->sanitize($_POST['numero_serie'] ?? ''),
            'annee_fabrication' => !empty($_POST['annee_fabrication']) ? intval($_POST['annee_fabrication']) : null,
            'date_achat' => !empty($_POST['date_achat']) ? $_POST['date_achat'] : null,
            'montant_achat' => !empty($_POST['montant_achat']) ? floatval($_POST['montant_achat']) : null,
            'kilometrage' => !empty($_POST['kilometrage']) ? floatval($_POST['kilometrage']) : 0,
            'heures_moteur' => !empty($_POST['heures_moteur']) ? floatval($_POST['heures_moteur']) : 0,
            'capacite_charge' => !empty($_POST['capacite_charge']) ? floatval($_POST['capacite_charge']) : null,
            'consommation_moyenne_theorique' => !empty($_POST['consommation_moyenne_theorique']) ? floatval($_POST['consommation_moyenne_theorique']) : null,
            'statut' => $this->sanitize($_POST['statut']),
            'notes' => $this->sanitize($_POST['notes'] ?? '')
        ];
        
        // Gérer l'upload de la photo
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $uploadResult = $this->handlePhotoUpload($_FILES['photo']);
            if ($uploadResult['success']) {
                $data['photo_path'] = $uploadResult['path'];
                // Supprimer l'ancienne photo
                if (!empty($engin['photo_path']) && file_exists(UPLOAD_PATH . '/photos/' . $engin['photo_path'])) {
                    unlink(UPLOAD_PATH . '/photos/' . $engin['photo_path']);
                }
            }
        }
        
        // Mettre à jour
        try {
            $result = $this->enginModel->update($id, $data);
            
            if ($result) {
                $_SESSION['success'] = 'Engin modifié avec succès';
                $this->redirect('/engins');
            } else {
                $_SESSION['error'] = 'Erreur lors de la modification';
                $this->redirect('/engins/edit/' . $id);
            }
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erreur: ' . $e->getMessage();
            $this->redirect('/engins/edit/' . $id);
        }
    }

    /**
     * Suppression d'un engin
     */
    public function delete($id)
    {
        $this->requireRole([ROLE_ADMIN]);
        
        $engin = $this->enginModel->findById($id);
        
        if (!$engin) {
            $_SESSION['error'] = 'Engin introuvable';
            $this->redirect('/engins');
        }
        
        // Vérifier s'il y a des dépendances
        $sql = "SELECT 
                (SELECT COUNT(*) FROM livraisons WHERE engin_id = :id) as nb_livraisons,
                (SELECT COUNT(*) FROM maintenances WHERE engin_id = :id) as nb_maintenances";
        $result = $this->db->fetch($sql, [':id' => $id]);
        
        if ($result['nb_livraisons'] > 0 || $result['nb_maintenances'] > 0) {
            $_SESSION['error'] = 'Impossible de supprimer cet engin (historique de livraisons/maintenances)';
            $this->redirect('/engins');
        }
        
        // Supprimer
        try {
            // Supprimer la photo
            if (!empty($engin['photo_path']) && file_exists(UPLOAD_PATH . '/photos/' . $engin['photo_path'])) {
                unlink(UPLOAD_PATH . '/photos/' . $engin['photo_path']);
            }
            
            $this->enginModel->delete($id);
            $_SESSION['success'] = 'Engin supprimé avec succès';
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erreur: ' . $e->getMessage();
        }
        
        $this->redirect('/engins');
    }

    /**
     * Gère l'upload d'une photo
     */
    private function handlePhotoUpload($file)
    {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
        $maxSize = 5 * 1024 * 1024; // 5MB
        
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
        $filename = 'engin_' . uniqid() . '.' . $extension;
        $destination = $uploadDir . $filename;
        
        if (move_uploaded_file($file['tmp_name'], $destination)) {
            return ['success' => true, 'path' => $filename];
        }
        
        return ['success' => false, 'error' => 'Erreur lors de l\'upload'];
    }
}
