<?php
namespace App\Controllers;

use App\Core\Controller;

/**
 * Contrôleur des Factures
 * Génération et gestion des factures
 */
class FactureController extends Controller
{
    private $factureModel;
    private $livraisonModel;
    private $clientModel;

    public function __construct()
    {
        parent::__construct();
        $this->factureModel = $this->model('Facture');
        $this->livraisonModel = $this->model('Livraison');
        $this->clientModel = $this->model('Client');
    }

    /**
     * Liste factures
     */
    public function index()
    {
        $this->requireAuth();
        
        // Récupérer les filtres
        $filters = [
            'statut_paiement' => $_GET['statut_paiement'] ?? null,
            'client_id' => $_GET['client_id'] ?? null,
            'date_debut' => $_GET['date_debut'] ?? null,
            'date_fin' => $_GET['date_fin'] ?? null
        ];
        
        // Construction requête avec filtres
        $where = [];
        $params = [];
        
        if ($filters['statut_paiement']) {
            $where[] = "f.statut_paiement = :statut_paiement";
            $params[':statut_paiement'] = $filters['statut_paiement'];
        }
        
        if ($filters['client_id']) {
            $where[] = "f.client_id = :client_id";
            $params[':client_id'] = $filters['client_id'];
        }
        
        if ($filters['date_debut']) {
            $where[] = "DATE(f.date_emission) >= :date_debut";
            $params[':date_debut'] = $filters['date_debut'];
        }
        
        if ($filters['date_fin']) {
            $where[] = "DATE(f.date_emission) <= :date_fin";
            $params[':date_fin'] = $filters['date_fin'];
        }
        
        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
        
        $sql = "SELECT f.*, c.nom_raison_sociale as client,
                CASE 
                    WHEN f.statut_paiement IN ('impayee', 'partiellement_payee') 
                         AND f.date_echeance < CURDATE() THEN 1 
                    ELSE 0 
                END as en_retard,
                DATEDIFF(CURDATE(), f.date_echeance) as jours_retard
                FROM factures f
                LEFT JOIN clients c ON f.client_id = c.id
                $whereClause
                ORDER BY f.date_emission DESC";
        
        $factures = $this->db->fetchAll($sql, $params);
        
        // Calculer totaux par statut
        $sqlTotaux = "SELECT statut_paiement, 
                      SUM(montant_ttc) as total,
                      COUNT(*) as count
                      FROM factures
                      GROUP BY statut_paiement";
        $totaux = $this->db->fetchAll($sqlTotaux);
        $totauxArray = [];
        foreach ($totaux as $t) {
            $totauxArray[$t['statut_paiement']] = [
                'total' => $t['total'],
                'count' => $t['count']
            ];
        }
        
        // Récupérer clients pour filtre
        $clients = $this->clientModel->findAll(['actif' => 1]);
        
        $data = [
            'title' => 'Gestion des Factures',
            'factures' => $factures,
            'totaux' => $totauxArray,
            'clients' => $clients,
            'filters' => $filters,
            'user' => $_SESSION
        ];
        
        $this->view('factures/index', $data);
    }

    /**
     * Formulaire génération facture depuis livraison
     */
    public function create()
    {
        $this->requireRole([ROLE_ADMIN, ROLE_COMMERCIAL]);
        
        // Récupérer SEULEMENT les livraisons livrées SANS facture
        $sql = "SELECT l.*, c.nom_raison_sociale as client,
                e.immatriculation, e.type as engin_type
                FROM livraisons l
                LEFT JOIN clients c ON l.client_id = c.id
                LEFT JOIN engins e ON l.engin_id = e.id
                WHERE l.statut = 'livree'
                AND NOT EXISTS (
                    SELECT 1 FROM factures f WHERE f.livraison_id = l.id
                )
                ORDER BY l.heure_arrivee DESC";
        $livraisonsDisponibles = $this->db->fetchAll($sql);
        
        $data = [
            'title' => 'Générer une Facture',
            'livraisons' => $livraisonsDisponibles,
            'user' => $_SESSION,
            'csrf_token' => $this->generateCsrfToken()
        ];
        
        $this->view('factures/create', $data);
    }

    /**
     * Génération facture (IMMUABLE après création)
     */
    public function store()
    {
        $this->requireRole([ROLE_ADMIN, ROLE_COMMERCIAL]);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/factures');
            return;
        }
        
        // Vérifier le token CSRF
        if (!$this->verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            $_SESSION['error'] = "Token de sécurité invalide";
            $this->redirect('/factures/create');
            return;
        }
        
        // Validation
        $errors = [];
        
        if (empty($_POST['livraison_id'])) {
            $errors[] = "La livraison est requise";
        }
        
        if (empty($_POST['montant_ht'])) {
            $errors[] = "Le montant HT est requis";
        }
        
        if (!isset($_POST['taux_tva'])) {
            $errors[] = "Le taux de TVA est requis";
        }
        
        if (!empty($errors)) {
            $_SESSION['error'] = implode('<br>', $errors);
            $this->redirect('/factures/create');
            return;
        }
        
        $livraisonId = intval($_POST['livraison_id']);
        $livraison = $this->livraisonModel->getWithDetails($livraisonId);
        
        if (!$livraison) {
            $_SESSION['error'] = "Livraison introuvable";
            $this->redirect('/factures/create');
            return;
        }
        
        if ($livraison['statut'] !== 'livree') {
            $_SESSION['error'] = "Seules les livraisons terminées peuvent être facturées";
            $this->redirect('/factures/create');
            return;
        }
        
        // Vérifier qu'aucune facture n'existe déjà
        $sql = "SELECT * FROM factures WHERE livraison_id = :livraison_id";
        $factureExistante = $this->db->fetch($sql, [':livraison_id' => $livraisonId]);
        
        if ($factureExistante) {
            $_SESSION['error'] = "Une facture existe déjà pour cette livraison";
            $this->redirect('/factures/create');
            return;
        }
        
        // Calculs
        $montantHt = floatval($_POST['montant_ht']);
        $tauxTva = floatval($_POST['taux_tva']);
        $montants = $this->factureModel->calculerMontants($montantHt, $tauxTva);
        
        // Générer numéro facture unique
        $numeroFacture = $this->factureModel->genererNumero();
        
        // Calculer date échéance selon conditions paiement
        $conditionsPaiement = $this->sanitize($_POST['conditions_paiement'] ?? 'Paiement à 30 jours');
        $dateEcheance = date('Y-m-d', strtotime('+30 days'));
        
        // Parser les conditions si possible
        if (preg_match('/(\d+)\s*jours?/i', $conditionsPaiement, $matches)) {
            $jours = intval($matches[1]);
            $dateEcheance = date('Y-m-d', strtotime("+$jours days"));
        }
        
        // Préparer les données
        $data = [
            'numero_facture' => $numeroFacture,
            'client_id' => $livraison['client_id'],
            'livraison_id' => $livraisonId,
            'date_emission' => date('Y-m-d'),
            'date_echeance' => $dateEcheance,
            'montant_ht' => $montantHt,
            'taux_tva' => $tauxTva,
            'montant_tva' => $montants['montant_tva'],
            'montant_ttc' => $montants['montant_ttc'],
            'montant_paye' => 0,
            'statut_paiement' => 'impayee',
            'conditions_paiement' => $conditionsPaiement,
            'mode_calcul' => $this->sanitize($_POST['mode_calcul'] ?? 'manuel'),
            'user_id' => $_SESSION['user_id']
        ];
        
        try {
            $id = $this->factureModel->insert($data);
            $_SESSION['success'] = "Facture $numeroFacture générée avec succès";
            $this->redirect('/factures/show/' . $id);
        } catch (\Exception $e) {
            $_SESSION['error'] = "Erreur lors de la génération : " . $e->getMessage();
            $this->redirect('/factures/create');
        }
    }

    /**
     * Aperçu facture format imprimable
     */
    public function show($id)
    {
        $this->requireAuth();
        
        $facture = $this->factureModel->getFactureComplete($id);
        
        if (!$facture) {
            $_SESSION['error'] = "Facture introuvable";
            $this->redirect('/factures');
            return;
        }
        
        // Récupérer les paiements partiels si applicable
        $sql = "SELECT * FROM paiements_partiels 
                WHERE facture_id = :facture_id 
                ORDER BY date_paiement";
        $paiementsPartiels = $this->db->fetchAll($sql, [':facture_id' => $id]);
        
        $data = [
            'title' => 'Facture ' . $facture['numero_facture'],
            'facture' => $facture,
            'paiements_partiels' => $paiementsPartiels,
            'user' => $_SESSION,
            'print_mode' => isset($_GET['print']) && $_GET['print'] == '1'
        ];
        
        $this->view('factures/show', $data);
    }

    /**
     * Génération PDF avec TCPDF
     */
    public function genererPDF($id)
    {
        $this->requireAuth();
        
        $facture = $this->factureModel->getFactureComplete($id);
        
        if (!$facture) {
            $_SESSION['error'] = "Facture introuvable";
            $this->redirect('/factures');
            return;
        }
        
        try {
            // Charger la bibliothèque FacturePDF
            require_once APP_PATH . '/Libraries/FacturePDF.php';
            
            $pdf = new \App\Libraries\FacturePDF();
            $pdf->generer($facture);
            
        } catch (\Exception $e) {
            $_SESSION['error'] = "Erreur lors de la génération du PDF : " . $e->getMessage();
            $this->redirect('/factures/show/' . $id);
        }
    }

    /**
     * Marquer comme payée (formulaire paiement)
     */
    public function marquerPayee($id)
    {
        $this->requireRole([ROLE_ADMIN, ROLE_COMMERCIAL]);
        
        $facture = $this->factureModel->findById($id);
        
        if (!$facture) {
            $_SESSION['error'] = "Facture introuvable";
            $this->redirect('/factures');
            return;
        }
        
        if ($facture['statut_paiement'] === 'payee') {
            $_SESSION['info'] = "Cette facture est déjà marquée comme payée";
            $this->redirect('/factures/show/' . $id);
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Vérifier le token CSRF
            if (!$this->verifyCsrfToken($_POST['csrf_token'] ?? '')) {
                $_SESSION['error'] = "Token de sécurité invalide";
                $this->redirect('/factures/show/' . $id);
                return;
            }
            
            $datePaiement = $_POST['date_paiement'] ?? date('Y-m-d');
            $modePaiement = $this->sanitize($_POST['mode_paiement'] ?? 'Espèces');
            $montantPaye = floatval($_POST['montant_paye'] ?? $facture['montant_ttc']);
            
            $data = [
                'date_paiement' => $datePaiement,
                'mode_paiement' => $modePaiement,
                'montant_paye' => $montantPaye
            ];
            
            // Déterminer le statut selon le montant payé
            if ($montantPaye >= $facture['montant_ttc']) {
                $data['statut_paiement'] = 'payee';
                $data['montant_paye'] = $facture['montant_ttc'];
            } elseif ($montantPaye > 0) {
                $data['statut_paiement'] = 'partiellement_payee';
                
                // Enregistrer dans paiements_partiels
                $sqlInsert = "INSERT INTO paiements_partiels 
                             (facture_id, montant, date_paiement, mode_paiement, user_id)
                             VALUES (:facture_id, :montant, :date_paiement, :mode_paiement, :user_id)";
                $this->db->query($sqlInsert, [
                    ':facture_id' => $id,
                    ':montant' => $montantPaye,
                    ':date_paiement' => $datePaiement,
                    ':mode_paiement' => $modePaiement,
                    ':user_id' => $_SESSION['user_id']
                ]);
            }
            
            try {
                $this->factureModel->update($id, $data);
                $_SESSION['success'] = "Paiement enregistré avec succès";
            } catch (\Exception $e) {
                $_SESSION['error'] = "Erreur lors de l'enregistrement : " . $e->getMessage();
            }
            
            $this->redirect('/factures/show/' . $id);
        } else {
            // Afficher formulaire
            $data = [
                'title' => 'Enregistrer Paiement',
                'facture' => $facture,
                'user' => $_SESSION,
                'csrf_token' => $this->generateCsrfToken()
            ];
            
            $this->view('factures/marquer_payee', $data);
        }
    }

    /**
     * Suppression INTERDITE (intégrité comptable)
     */
    public function delete($id)
    {
        $_SESSION['error'] = "La suppression de factures est interdite pour des raisons d'intégrité comptable";
        $this->redirect('/factures');
    }
}
