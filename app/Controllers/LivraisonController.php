<?php
namespace App\Controllers;

use App\Core\Controller;

/**
 * Contrôleur des Livraisons
 * Gestion des livraisons avec validations critiques et workflow
 */
class LivraisonController extends Controller
{
    private $livraisonModel;
    private $clientModel;
    private $enginModel;
    private $chauffeurModel;
    private $documentEnginModel;

    public function __construct()
    {
        parent::__construct();
        $this->livraisonModel = $this->model('Livraison');
        $this->clientModel = $this->model('Client');
        $this->enginModel = $this->model('Engin');
        $this->chauffeurModel = $this->model('Chauffeur');
        $this->documentEnginModel = $this->model('DocumentEngin');
    }

    /**
     * Liste livraisons avec vue calendrier
     */
    public function index()
    {
        $this->requireAuth();
        
        // Récupérer les filtres
        $filters = [
            'statut' => $_GET['statut'] ?? null,
            'client_id' => $_GET['client_id'] ?? null,
            'engin_id' => $_GET['engin_id'] ?? null,
            'chauffeur_id' => $_GET['chauffeur_id'] ?? null,
            'date_debut' => $_GET['date_debut'] ?? null,
            'date_fin' => $_GET['date_fin'] ?? null
        ];
        
        // Récupérer les livraisons avec filtres
        $result = $this->livraisonModel->getLivraisons($filters);
        
        // Compteurs par statut
        $sql = "SELECT statut, COUNT(*) as count FROM livraisons GROUP BY statut";
        $compteurs = $this->db->fetchAll($sql);
        $compteursArray = [];
        foreach ($compteurs as $c) {
            $compteursArray[$c['statut']] = $c['count'];
        }
        
        // Listes pour filtres
        $clients = $this->clientModel->findAll(['actif' => 1]);
        $engins = $this->enginModel->findAll();
        $chauffeurs = $this->chauffeurModel->findAll();
        
        $data = [
            'title' => 'Gestion des Livraisons',
            'livraisons' => $result['data'],
            'compteurs' => $compteursArray,
            'clients' => $clients,
            'engins' => $engins,
            'chauffeurs' => $chauffeurs,
            'filters' => $filters,
            'user' => $_SESSION
        ];
        
        $this->view('livraisons/index', $data);
    }

    /**
     * Formulaire création livraison avec validations critiques
     */
    public function create()
    {
        $this->requireRole([ROLE_ADMIN, ROLE_COMMERCIAL]);
        
        // Récupérer SEULEMENT les engins disponibles ET conformes
        $sql = "SELECT e.* FROM engins e
                WHERE e.statut = 'disponible'
                AND NOT EXISTS (
                    SELECT 1 FROM documents_engins d
                    WHERE d.engin_id = e.id
                    AND d.est_critique = TRUE
                    AND d.statut_validite = 'expire'
                )
                ORDER BY e.immatriculation";
        $enginsDisponibles = $this->db->fetchAll($sql);
        
        // Récupérer SEULEMENT les chauffeurs avec permis valide
        $sql = "SELECT * FROM chauffeurs
                WHERE bloque = FALSE
                AND date_expiration_permis > CURDATE()
                ORDER BY nom, prenom";
        $chauffeursValides = $this->db->fetchAll($sql);
        
        // Récupérer les clients actifs
        $clients = $this->clientModel->findAll(['actif' => 1]);
        
        $data = [
            'title' => 'Nouvelle Livraison',
            'engins' => $enginsDisponibles,
            'chauffeurs' => $chauffeursValides,
            'clients' => $clients,
            'user' => $_SESSION,
            'csrf_token' => $this->generateCsrfToken()
        ];
        
        $this->view('livraisons/create', $data);
    }

    /**
     * Enregistrement livraison avec validations serveur CRITIQUES
     */
    public function store()
    {
        $this->requireRole([ROLE_ADMIN, ROLE_COMMERCIAL]);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/livraisons');
            return;
        }
        
        // Vérifier le token CSRF
        if (!$this->verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            $_SESSION['error'] = "Token de sécurité invalide";
            $this->redirect('/livraisons/create');
            return;
        }
        
        // Validation des champs requis
        $errors = [];
        $required = ['client_id', 'engin_id', 'chauffeur_id', 'date_prevue', 'lieu_depart', 'destination'];
        
        foreach ($required as $field) {
            if (empty($_POST[$field])) {
                $errors[] = "Le champ $field est requis";
            }
        }
        
        if (!empty($errors)) {
            $_SESSION['error'] = implode('<br>', $errors);
            $this->redirect('/livraisons/create');
            return;
        }
        
        $enginId = intval($_POST['engin_id']);
        $chauffeurId = intval($_POST['chauffeur_id']);
        $clientId = intval($_POST['client_id']);
        
        // VALIDATION CRITIQUE 1: Vérifier statut engin
        $engin = $this->enginModel->findById($enginId);
        if (!$engin || $engin['statut'] !== 'disponible') {
            $_SESSION['error'] = "L'engin sélectionné n'est pas disponible";
            $this->redirect('/livraisons/create');
            return;
        }
        
        // VALIDATION CRITIQUE 2: Vérifier conformité documentaire engin
        $conformite = $this->documentEnginModel->verifierConformiteEngin($enginId);
        if (!$conformite['conforme']) {
            $erreurs = [];
            if (!empty($conformite['documents_manquants'])) {
                $erreurs[] = "Documents manquants: " . implode(', ', $conformite['documents_manquants']);
            }
            if (!empty($conformite['documents_expires'])) {
                $erreurs[] = "Documents expirés: " . implode(', ', $conformite['documents_expires']);
            }
            $_SESSION['error'] = "Engin non conforme:<br>" . implode('<br>', $erreurs);
            $this->redirect('/livraisons/create');
            return;
        }
        
        // VALIDATION CRITIQUE 3: Vérifier permis chauffeur
        $chauffeur = $this->chauffeurModel->findById($chauffeurId);
        if (!$chauffeur) {
            $_SESSION['error'] = "Chauffeur introuvable";
            $this->redirect('/livraisons/create');
            return;
        }
        
        if ($chauffeur['bloque']) {
            $_SESSION['error'] = "Le chauffeur est bloqué";
            $this->redirect('/livraisons/create');
            return;
        }
        
        if (strtotime($chauffeur['date_expiration_permis']) < time()) {
            $_SESSION['error'] = "Le permis du chauffeur est expiré";
            $this->redirect('/livraisons/create');
            return;
        }
        
        // VALIDATION CRITIQUE 4: Vérifier affectation engin-chauffeur
        $sql = "SELECT * FROM affectations_engin_chauffeur
                WHERE engin_id = :engin_id
                AND chauffeur_id = :chauffeur_id
                AND actif = TRUE
                LIMIT 1";
        $affectation = $this->db->fetch($sql, [
            ':engin_id' => $enginId,
            ':chauffeur_id' => $chauffeurId
        ]);
        
        if (!$affectation) {
            $_SESSION['error'] = "Aucune affectation active entre ce chauffeur et cet engin";
            $this->redirect('/livraisons/create');
            return;
        }
        
        // VALIDATION CRITIQUE 5: Vérifier pas de conflit
        $datePrevue = $_POST['date_prevue'];
        $sql = "SELECT * FROM livraisons
                WHERE engin_id = :engin_id
                AND statut IN ('planifiee', 'en_cours')
                AND DATE(date_prevue) = DATE(:date_prevue)";
        $conflit = $this->db->fetch($sql, [
            ':engin_id' => $enginId,
            ':date_prevue' => $datePrevue
        ]);
        
        if ($conflit) {
            $_SESSION['error'] = "L'engin est déjà réservé pour une livraison à cette date";
            $this->redirect('/livraisons/create');
            return;
        }
        
        // Préparer les données
        $data = [
            'client_id' => $clientId,
            'engin_id' => $enginId,
            'chauffeur_id' => $chauffeurId,
            'date_prevue' => $datePrevue,
            'lieu_depart' => $this->sanitize($_POST['lieu_depart']),
            'destination' => $this->sanitize($_POST['destination']),
            'type_materiau' => $this->sanitize($_POST['type_materiau'] ?? ''),
            'poids_volume' => !empty($_POST['poids_volume']) ? floatval($_POST['poids_volume']) : null,
            'bon_commande_client' => $this->sanitize($_POST['bon_commande_client'] ?? ''),
            'tarification' => !empty($_POST['tarification']) ? floatval($_POST['tarification']) : 0,
            'notes' => $this->sanitize($_POST['notes'] ?? ''),
            'statut' => 'planifiee',
            'user_id' => $_SESSION['user_id']
        ];
        
        try {
            $id = $this->livraisonModel->insert($data);
            $_SESSION['success'] = "Livraison créée avec succès";
            $this->redirect('/livraisons/edit/' . $id);
        } catch (\Exception $e) {
            $_SESSION['error'] = "Erreur lors de la création : " . $e->getMessage();
            $this->redirect('/livraisons/create');
        }
    }

    /**
     * Modification livraison selon statut
     */
    public function edit($id)
    {
        $this->requireRole([ROLE_ADMIN, ROLE_COMMERCIAL]);
        
        $livraison = $this->livraisonModel->getWithDetails($id);
        
        if (!$livraison) {
            $_SESSION['error'] = "Livraison introuvable";
            $this->redirect('/livraisons');
            return;
        }
        
        // Récupérer listes pour formulaire
        $clients = $this->clientModel->findAll(['actif' => 1]);
        $engins = $this->enginModel->findAll();
        $chauffeurs = $this->chauffeurModel->findAll();
        
        // Vérifier si une facture existe
        $sql = "SELECT * FROM factures WHERE livraison_id = :livraison_id LIMIT 1";
        $facture = $this->db->fetch($sql, [':livraison_id' => $id]);
        
        $data = [
            'title' => 'Modifier Livraison #' . $id,
            'livraison' => $livraison,
            'clients' => $clients,
            'engins' => $engins,
            'chauffeurs' => $chauffeurs,
            'facture' => $facture,
            'user' => $_SESSION,
            'csrf_token' => $this->generateCsrfToken()
        ];
        
        $this->view('livraisons/edit', $data);
    }

    /**
     * Mise à jour avec gestion workflow
     */
    public function update($id)
    {
        $this->requireRole([ROLE_ADMIN, ROLE_COMMERCIAL]);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/livraisons');
            return;
        }
        
        // Vérifier le token CSRF
        if (!$this->verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            $_SESSION['error'] = "Token de sécurité invalide";
            $this->redirect('/livraisons/edit/' . $id);
            return;
        }
        
        $livraison = $this->livraisonModel->findById($id);
        
        if (!$livraison) {
            $_SESSION['error'] = "Livraison introuvable";
            $this->redirect('/livraisons');
            return;
        }
        
        $ancienStatut = $livraison['statut'];
        $nouveauStatut = $_POST['statut'] ?? $ancienStatut;
        
        $data = [];
        
        // Mise à jour selon statut actuel
        if ($ancienStatut === 'planifiee') {
            // Tous les champs modifiables
            if (isset($_POST['client_id'])) $data['client_id'] = intval($_POST['client_id']);
            if (isset($_POST['engin_id'])) $data['engin_id'] = intval($_POST['engin_id']);
            if (isset($_POST['chauffeur_id'])) $data['chauffeur_id'] = intval($_POST['chauffeur_id']);
            if (isset($_POST['date_prevue'])) $data['date_prevue'] = $_POST['date_prevue'];
            if (isset($_POST['lieu_depart'])) $data['lieu_depart'] = $this->sanitize($_POST['lieu_depart']);
            if (isset($_POST['destination'])) $data['destination'] = $this->sanitize($_POST['destination']);
            if (isset($_POST['type_materiau'])) $data['type_materiau'] = $this->sanitize($_POST['type_materiau']);
            if (isset($_POST['poids_volume'])) $data['poids_volume'] = floatval($_POST['poids_volume']);
            if (isset($_POST['tarification'])) $data['tarification'] = floatval($_POST['tarification']);
        }
        
        // Notes toujours modifiables
        if (isset($_POST['notes'])) {
            $data['notes'] = $this->sanitize($_POST['notes']);
        }
        
        try {
            $this->db->beginTransaction();
            
            // Gestion changements de statut
            if ($nouveauStatut !== $ancienStatut) {
                $data['statut'] = $nouveauStatut;
                
                // Passage à en_cours
                if ($nouveauStatut === 'en_cours' && $ancienStatut === 'planifiee') {
                    $data['heure_depart_reelle'] = date('Y-m-d H:i:s');
                    $this->enginModel->update($livraison['engin_id'], ['statut' => 'en_mission']);
                }
                
                // Passage à livree
                if ($nouveauStatut === 'livree' && $ancienStatut === 'en_cours') {
                    $data['heure_arrivee'] = date('Y-m-d H:i:s');
                    $this->enginModel->update($livraison['engin_id'], ['statut' => 'disponible']);
                }
                
                // Annulation
                if ($nouveauStatut === 'annulee') {
                    if ($ancienStatut === 'en_cours') {
                        $this->enginModel->update($livraison['engin_id'], ['statut' => 'disponible']);
                    }
                }
            }
            
            if (!empty($data)) {
                $this->livraisonModel->update($id, $data);
            }
            
            $this->db->commit();
            $_SESSION['success'] = "Livraison mise à jour avec succès";
            
        } catch (\Exception $e) {
            $this->db->rollback();
            $_SESSION['error'] = "Erreur lors de la mise à jour : " . $e->getMessage();
        }
        
        $this->redirect('/livraisons/edit/' . $id);
    }

    /**
     * Suppression (seulement si planifiee)
     */
    public function delete($id)
    {
        $this->requireRole([ROLE_ADMIN]);
        
        $livraison = $this->livraisonModel->findById($id);
        
        if (!$livraison) {
            $_SESSION['error'] = "Livraison introuvable";
            $this->redirect('/livraisons');
            return;
        }
        
        if ($livraison['statut'] !== 'planifiee') {
            $_SESSION['error'] = "Seules les livraisons planifiées peuvent être supprimées";
            $this->redirect('/livraisons');
            return;
        }
        
        try {
            $this->livraisonModel->delete($id);
            $_SESSION['success'] = "Livraison supprimée avec succès";
        } catch (\Exception $e) {
            $_SESSION['error'] = "Erreur lors de la suppression : " . $e->getMessage();
        }
        
        $this->redirect('/livraisons');
    }

    /**
     * Vue détaillée
     */
    public function show($id)
    {
        $this->requireAuth();
        
        $livraison = $this->livraisonModel->getWithDetails($id);
        
        if (!$livraison) {
            $_SESSION['error'] = "Livraison introuvable";
            $this->redirect('/livraisons');
            return;
        }
        
        // Récupérer la facture si elle existe
        $sql = "SELECT * FROM factures WHERE livraison_id = :livraison_id LIMIT 1";
        $facture = $this->db->fetch($sql, [':livraison_id' => $id]);
        
        // Calculer durée réelle si livrée
        $dureeReelle = null;
        if ($livraison['statut'] === 'livree' && $livraison['heure_depart_reelle'] && $livraison['heure_arrivee']) {
            $depart = strtotime($livraison['heure_depart_reelle']);
            $arrivee = strtotime($livraison['heure_arrivee']);
            $dureeMinutes = ($arrivee - $depart) / 60;
            $dureeReelle = floor($dureeMinutes / 60) . 'h ' . ($dureeMinutes % 60) . 'min';
        }
        
        $data = [
            'title' => 'Détail Livraison #' . $id,
            'livraison' => $livraison,
            'facture' => $facture,
            'duree_reelle' => $dureeReelle,
            'user' => $_SESSION
        ];
        
        $this->view('livraisons/show', $data);
    }

    /**
     * Validation par Commercial/Admin
     */
    public function valider($id)
    {
        $this->requireRole([ROLE_ADMIN, ROLE_COMMERCIAL]);
        
        try {
            $this->livraisonModel->validerLivraison($id, $_SESSION['user_id']);
            $_SESSION['success'] = "Livraison validée avec succès";
        } catch (\Exception $e) {
            $_SESSION['error'] = $e->getMessage();
        }
        
        $this->redirect('/livraisons/edit/' . $id);
    }

    /**
     * Création client rapide en AJAX
     */
    public function creerClientRapide()
    {
        $this->requireRole([ROLE_ADMIN, ROLE_COMMERCIAL]);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'error' => 'Méthode non autorisée'], 405);
            return;
        }
        
        // Validation minimale
        if (empty($_POST['nom_raison_sociale'])) {
            $this->json(['success' => false, 'error' => 'Le nom est requis'], 400);
            return;
        }
        
        $data = [
            'nom_raison_sociale' => $this->sanitize($_POST['nom_raison_sociale']),
            'telephone' => $this->sanitize($_POST['telephone'] ?? ''),
            'email' => $this->sanitize($_POST['email'] ?? ''),
            'actif' => 1,
            'type_client' => 'entreprise'
        ];
        
        // Validation email si fourni
        if (!empty($data['email']) && !$this->validateEmail($data['email'])) {
            $this->json(['success' => false, 'error' => 'Email invalide'], 400);
            return;
        }
        
        try {
            $id = $this->clientModel->insert($data);
            $client = $this->clientModel->findById($id);
            $this->json([
                'success' => true,
                'client' => $client
            ]);
        } catch (\Exception $e) {
            $this->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
}
