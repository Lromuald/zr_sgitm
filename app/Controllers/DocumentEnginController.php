<?php
namespace App\Controllers;

use App\Core\Controller;

/**
 * Contrôleur des Documents Engins
 * Upload et gestion de la conformité documentaire
 */
class DocumentEnginController extends Controller
{
    private $documentEnginModel;
    private $enginModel;

    public function __construct()
    {
        parent::__construct();
        $this->documentEnginModel = $this->model('DocumentEngin');
        $this->enginModel = $this->model('Engin');
    }

    /**
     * Vue globale conformité documentaire
     */
    public function index()
    {
        $this->requireAuth();
        
        // Récupérer tous les engins avec leurs documents
        $sql = "SELECT e.id, e.immatriculation, e.type, e.marque, e.modele, e.statut,
                COUNT(DISTINCT d.id) as nb_documents,
                SUM(CASE WHEN d.est_critique = TRUE AND d.statut_validite = 'expire' THEN 1 ELSE 0 END) as nb_critiques_expires
                FROM engins e
                LEFT JOIN documents_engins d ON e.id = d.engin_id
                GROUP BY e.id
                ORDER BY nb_critiques_expires DESC, e.immatriculation";
        $engins = $this->db->fetchAll($sql);
        
        // Pour chaque engin, récupérer les 5 documents critiques
        $typesDocuments = ['carte_grise', 'assurance', 'carte_transport', 'carte_stationnement', 'visite_technique'];
        
        foreach ($engins as &$engin) {
            $engin['documents'] = [];
            
            foreach ($typesDocuments as $type) {
                $doc = $this->documentEnginModel->getByEnginAndType($engin['id'], $type);
                
                if ($doc) {
                    // Calculer le statut badge
                    $joursRestants = floor((strtotime($doc['date_expiration']) - time()) / 86400);
                    
                    if ($joursRestants < 0) {
                        $badge = 'expire';
                        $color = 'danger';
                    } elseif ($joursRestants <= 3) {
                        $badge = 'urgent';
                        $color = 'danger';
                    } elseif ($joursRestants <= 15) {
                        $badge = 'attention';
                        $color = 'warning';
                    } elseif ($joursRestants <= 30) {
                        $badge = 'info';
                        $color = 'info';
                    } else {
                        $badge = 'valide';
                        $color = 'success';
                    }
                    
                    $engin['documents'][$type] = [
                        'existe' => true,
                        'date_expiration' => $doc['date_expiration'],
                        'jours_restants' => $joursRestants,
                        'badge' => $badge,
                        'color' => $color,
                        'id' => $doc['id']
                    ];
                } else {
                    $engin['documents'][$type] = [
                        'existe' => false,
                        'badge' => 'manquant',
                        'color' => 'secondary'
                    ];
                }
            }
        }
        
        // Compteurs globaux
        $sql = "SELECT 
                SUM(CASE WHEN statut_validite = 'valide' THEN 1 ELSE 0 END) as nb_valides,
                SUM(CASE WHEN alerte_niveau = 'attention' THEN 1 ELSE 0 END) as nb_expire_bientot,
                SUM(CASE WHEN statut_validite = 'expire' THEN 1 ELSE 0 END) as nb_expires
                FROM documents_engins";
        $compteurs = $this->db->fetch($sql);
        
        $data = [
            'title' => 'Conformité Documentaire',
            'engins' => $engins,
            'types_documents' => $typesDocuments,
            'compteurs' => $compteurs,
            'user' => $_SESSION
        ];
        
        $this->view('documents_engins/index', $data);
    }

    /**
     * Formulaire upload document
     */
    public function upload()
    {
        $this->requireRole([ROLE_ADMIN, ROLE_GESTIONNAIRE_STOCK]);
        
        // Récupérer engins
        $engins = $this->enginModel->findAll();
        
        $typesDocuments = [
            'carte_grise' => 'Carte Grise',
            'assurance' => 'Assurance',
            'carte_transport' => 'Carte de Transport',
            'carte_stationnement' => 'Carte de Stationnement',
            'visite_technique' => 'Visite Technique'
        ];
        
        $data = [
            'title' => 'Upload Document',
            'engins' => $engins,
            'types_documents' => $typesDocuments,
            'user' => $_SESSION,
            'csrf_token' => $this->generateCsrfToken()
        ];
        
        $this->view('documents_engins/upload', $data);
    }

    /**
     * Traitement upload avec validations sécurité CRITIQUES
     */
    public function store()
    {
        $this->requireRole([ROLE_ADMIN, ROLE_GESTIONNAIRE_STOCK]);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/documents-engins');
            return;
        }
        
        // Vérifier le token CSRF
        if (!$this->verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            $_SESSION['error'] = "Token de sécurité invalide";
            $this->redirect('/documents-engins/upload');
            return;
        }
        
        // Validation
        $errors = [];
        
        if (empty($_POST['engin_id'])) {
            $errors[] = "L'engin est requis";
        }
        
        if (empty($_POST['type_document'])) {
            $errors[] = "Le type de document est requis";
        }
        
        if (empty($_POST['date_expiration'])) {
            $errors[] = "La date d'expiration est requise";
        }
        
        if (!isset($_FILES['fichier']) || $_FILES['fichier']['error'] !== UPLOAD_ERR_OK) {
            $errors[] = "Le fichier est requis";
        }
        
        if (!empty($errors)) {
            $_SESSION['error'] = implode('<br>', $errors);
            $this->redirect('/documents-engins/upload');
            return;
        }
        
        $file = $_FILES['fichier'];
        
        // VALIDATION SÉCURITÉ 1: Vérifier taille (max 10 Mo)
        if ($file['size'] > MAX_UPLOAD_SIZE) {
            $_SESSION['error'] = "Le fichier est trop volumineux (max 10 Mo)";
            $this->redirect('/documents-engins/upload');
            return;
        }
        
        // VALIDATION SÉCURITÉ 2: Vérifier extension (whitelist)
        $pathInfo = pathinfo($file['name']);
        $extension = strtolower($pathInfo['extension'] ?? '');
        
        if (!in_array($extension, ALLOWED_DOCUMENT_EXTENSIONS)) {
            $_SESSION['error'] = "Extension de fichier non autorisée. Extensions autorisées: " . implode(', ', ALLOWED_DOCUMENT_EXTENSIONS);
            $this->redirect('/documents-engins/upload');
            return;
        }
        
        // VALIDATION SÉCURITÉ 3: Vérifier MIME type réel avec finfo_file()
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        $mimeTypesAutorise = [
            'application/pdf',
            'image/jpeg',
            'image/jpg',
            'image/png'
        ];
        
        if (!in_array($mimeType, $mimeTypesAutorise)) {
            $_SESSION['error'] = "Type de fichier non autorisé (MIME type: $mimeType)";
            $this->redirect('/documents-engins/upload');
            return;
        }
        
        // Récupérer l'engin pour le nom du fichier
        $engin = $this->enginModel->findById($_POST['engin_id']);
        
        if (!$engin) {
            $_SESSION['error'] = "Engin introuvable";
            $this->redirect('/documents-engins/upload');
            return;
        }
        
        // VALIDATION SÉCURITÉ 4: Renommer fichier {type_doc}_{immatriculation}_{timestamp}.{ext}
        $immatriculation = preg_replace('/[^a-zA-Z0-9]/', '_', $engin['immatriculation']);
        $typeDocument = $_POST['type_document'];
        $timestamp = time();
        $nomFichier = "{$typeDocument}_{$immatriculation}_{$timestamp}.{$extension}";
        
        // VALIDATION SÉCURITÉ 5: Déplacer vers /uploads/documents/ (HORS /public/)
        $cheminDestination = UPLOAD_PATH . '/documents/' . $nomFichier;
        
        // Créer le répertoire s'il n'existe pas
        if (!is_dir(UPLOAD_PATH . '/documents')) {
            mkdir(UPLOAD_PATH . '/documents', 0755, true);
        }
        
        if (!move_uploaded_file($file['tmp_name'], $cheminDestination)) {
            $_SESSION['error'] = "Erreur lors de l'upload du fichier";
            $this->redirect('/documents-engins/upload');
            return;
        }
        
        // Calculer statut_validite selon date_expiration
        $dateExpiration = strtotime($_POST['date_expiration']);
        $aujourdhui = time();
        $joursRestants = floor(($dateExpiration - $aujourdhui) / 86400);
        
        $statutValidite = 'valide';
        $niveauAlerte = 'info';
        
        if ($joursRestants < 0) {
            $statutValidite = 'expire';
            $niveauAlerte = 'critique';
        } elseif ($joursRestants <= 3) {
            $statutValidite = 'a_renouveler';
            $niveauAlerte = 'urgent';
        } elseif ($joursRestants <= 15) {
            $statutValidite = 'a_renouveler';
            $niveauAlerte = 'attention';
        } elseif ($joursRestants <= 30) {
            $niveauAlerte = 'attention';
        }
        
        // Déterminer si critique
        $typesCritiques = ['carte_grise', 'assurance', 'carte_transport', 'visite_technique'];
        $estCritique = in_array($typeDocument, $typesCritiques);
        
        // Préparer les données
        $data = [
            'engin_id' => intval($_POST['engin_id']),
            'type_document' => $typeDocument,
            'numero_document' => $this->sanitize($_POST['numero_document'] ?? ''),
            'date_emission' => $_POST['date_emission'] ?? null,
            'date_expiration' => $_POST['date_expiration'],
            'chemin_fichier' => 'documents/' . $nomFichier,
            'statut_validite' => $statutValidite,
            'alerte_niveau' => $niveauAlerte,
            'est_critique' => $estCritique,
            'user_id' => $_SESSION['user_id']
        ];
        
        try {
            $this->db->beginTransaction();
            
            $id = $this->documentEnginModel->insert($data);
            
            // Si document critique et valide, vérifier si engin peut sortir de 'hors_service'
            if ($estCritique && $statutValidite === 'valide') {
                $conformite = $this->documentEnginModel->verifierConformiteEngin($_POST['engin_id']);
                
                if ($conformite['conforme'] && $engin['statut'] === 'hors_service') {
                    $this->enginModel->update($_POST['engin_id'], ['statut' => 'disponible']);
                }
            }
            
            $this->db->commit();
            
            $_SESSION['success'] = "Document uploadé avec succès";
            $this->redirect('/documents-engins');
            
        } catch (\Exception $e) {
            $this->db->rollback();
            
            // Supprimer le fichier uploadé en cas d'erreur
            if (file_exists($cheminDestination)) {
                unlink($cheminDestination);
            }
            
            $_SESSION['error'] = "Erreur lors de l'enregistrement : " . $e->getMessage();
            $this->redirect('/documents-engins/upload');
        }
    }

    /**
     * Téléchargement sécurisé
     */
    public function download($id)
    {
        $this->requireAuth();
        
        $document = $this->documentEnginModel->findById($id);
        
        if (!$document) {
            $_SESSION['error'] = "Document introuvable";
            $this->redirect('/documents-engins');
            return;
        }
        
        $cheminFichier = UPLOAD_PATH . '/' . $document['chemin_fichier'];
        
        if (!file_exists($cheminFichier)) {
            $_SESSION['error'] = "Fichier introuvable sur le serveur";
            $this->redirect('/documents-engins');
            return;
        }
        
        // Déterminer le type MIME
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $cheminFichier);
        finfo_close($finfo);
        
        // Nom du fichier pour le téléchargement
        $nomFichier = basename($document['chemin_fichier']);
        
        // Headers pour téléchargement
        header('Content-Type: ' . $mimeType);
        header('Content-Disposition: attachment; filename="' . $nomFichier . '"');
        header('Content-Length: ' . filesize($cheminFichier));
        header('Cache-Control: no-cache, must-revalidate');
        header('Pragma: no-cache');
        
        // Lire et envoyer le fichier
        readfile($cheminFichier);
        exit;
    }

    /**
     * Suppression document
     */
    public function delete($id)
    {
        $this->requireRole([ROLE_ADMIN]);
        
        $document = $this->documentEnginModel->findById($id);
        
        if (!$document) {
            $_SESSION['error'] = "Document introuvable";
            $this->redirect('/documents-engins');
            return;
        }
        
        try {
            $this->db->beginTransaction();
            
            // ATTENTION: Si document critique, mettre engin en hors_service
            if ($document['est_critique']) {
                $this->enginModel->update($document['engin_id'], ['statut' => 'hors_service']);
            }
            
            // Supprimer le fichier physique
            $cheminFichier = UPLOAD_PATH . '/' . $document['chemin_fichier'];
            if (file_exists($cheminFichier)) {
                unlink($cheminFichier);
            }
            
            // Supprimer l'enregistrement
            $this->documentEnginModel->delete($id);
            
            $this->db->commit();
            
            $_SESSION['success'] = "Document supprimé avec succès";
            
        } catch (\Exception $e) {
            $this->db->rollback();
            $_SESSION['error'] = "Erreur lors de la suppression : " . $e->getMessage();
        }
        
        $this->redirect('/documents-engins');
    }

    /**
     * Vérification validités (tâche cron ou bouton admin)
     */
    public function verifierValidites()
    {
        $this->requireRole([ROLE_ADMIN]);
        
        try {
            $count = $this->documentEnginModel->verifierTousLesDocuments();
            $_SESSION['success'] = "$count documents vérifiés et mis à jour";
        } catch (\Exception $e) {
            $_SESSION['error'] = "Erreur lors de la vérification : " . $e->getMessage();
        }
        
        $this->redirect('/documents-engins');
    }

    /**
     * Vue document (visualiseur)
     */
    public function show($id)
    {
        $this->requireAuth();
        
        $document = $this->documentEnginModel->findById($id);
        
        if (!$document) {
            $_SESSION['error'] = "Document introuvable";
            $this->redirect('/documents-engins');
            return;
        }
        
        $cheminFichier = UPLOAD_PATH . '/' . $document['chemin_fichier'];
        
        if (!file_exists($cheminFichier)) {
            $_SESSION['error'] = "Fichier introuvable sur le serveur";
            $this->redirect('/documents-engins');
            return;
        }
        
        // Récupérer l'engin
        $engin = $this->enginModel->findById($document['engin_id']);
        
        // Déterminer le type de fichier
        $extension = pathinfo($document['chemin_fichier'], PATHINFO_EXTENSION);
        
        $data = [
            'title' => 'Document ' . $document['type_document'],
            'document' => $document,
            'engin' => $engin,
            'extension' => $extension,
            'user' => $_SESSION
        ];
        
        $this->view('documents_engins/show', $data);
    }
}
