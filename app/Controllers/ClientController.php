<?php
namespace App\Controllers;

use App\Core\Controller;

/**
 * Contrôleur des Clients
 * Gestion des clients avec historique des factures
 */
class ClientController extends Controller
{
    private $clientModel;
    private $factureModel;

    public function __construct()
    {
        parent::__construct();
        $this->clientModel = $this->model('Client');
        $this->factureModel = $this->model('Facture');
    }

    public function index()
    {
        $this->requireAuth();
        
        $clients = $this->clientModel->getAll();
        
        foreach ($clients as &$client) {
            $stats = $this->factureModel->getStatsByClient($client['id']);
            $client['ca_total'] = $stats['total_facture'] ?? 0;
            $client['reste_a_payer'] = $stats['reste_a_payer'] ?? 0;
        }
        
        $data = [
            'title' => 'Gestion des Clients',
            'clients' => $clients,
            'user' => $_SESSION
        ];
        
        $this->view('clients/index', $data);
    }

    public function create()
    {
        $this->requireRole([ROLE_ADMIN, ROLE_COMMERCIAL]);
        
        $data = [
            'title' => 'Ajouter un Client',
            'csrf_token' => $this->generateCsrfToken(),
            'user' => $_SESSION
        ];
        
        $this->view('clients/create', $data);
    }

    public function store()
    {
        $this->requireRole([ROLE_ADMIN, ROLE_COMMERCIAL]);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/clients');
        }
        
        if (!$this->verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            $_SESSION['error'] = 'Token CSRF invalide';
            $this->redirect('/clients/create');
        }
        
        $errors = [];
        if (empty($_POST['nom'])) {
            $errors[] = 'Le nom est obligatoire';
        }
        
        if (!empty($errors)) {
            $_SESSION['error'] = implode('<br>', $errors);
            $_SESSION['old_input'] = $_POST;
            $this->redirect('/clients/create');
        }
        
        $data = [
            'nom' => $this->sanitize($_POST['nom']),
            'adresse' => $this->sanitize($_POST['adresse'] ?? ''),
            'telephone' => $this->sanitize($_POST['telephone'] ?? ''),
            'email' => $this->sanitize($_POST['email'] ?? ''),
            'nif' => $this->sanitize($_POST['nif'] ?? ''),
            'rc' => $this->sanitize($_POST['rc'] ?? ''),
            'type_client' => $this->sanitize($_POST['type_client'] ?? 'professionnel'),
            'conditions_paiement' => $this->sanitize($_POST['conditions_paiement'] ?? ''),
            'statut' => 'actif',
            'notes' => $this->sanitize($_POST['notes'] ?? '')
        ];
        
        try {
            $clientId = $this->clientModel->create($data);
            
            if ($clientId) {
                $_SESSION['success'] = 'Client créé avec succès';
                $this->redirect('/clients');
            } else {
                $_SESSION['error'] = 'Erreur lors de la création du client';
                $this->redirect('/clients/create');
            }
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erreur: ' . $e->getMessage();
            $this->redirect('/clients/create');
        }
    }

    public function edit($id)
    {
        $this->requireRole([ROLE_ADMIN, ROLE_COMMERCIAL]);
        
        $client = $this->clientModel->findById($id);
        
        if (!$client) {
            $_SESSION['error'] = 'Client introuvable';
            $this->redirect('/clients');
        }
        
        $data = [
            'title' => 'Modifier le Client',
            'client' => $client,
            'csrf_token' => $this->generateCsrfToken(),
            'user' => $_SESSION
        ];
        
        $this->view('clients/edit', $data);
    }

    public function update($id)
    {
        $this->requireRole([ROLE_ADMIN, ROLE_COMMERCIAL]);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/clients');
        }
        
        if (!$this->verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            $_SESSION['error'] = 'Token CSRF invalide';
            $this->redirect('/clients/edit/' . $id);
        }
        
        $data = [
            'nom' => $this->sanitize($_POST['nom']),
            'adresse' => $this->sanitize($_POST['adresse'] ?? ''),
            'telephone' => $this->sanitize($_POST['telephone'] ?? ''),
            'email' => $this->sanitize($_POST['email'] ?? ''),
            'nif' => $this->sanitize($_POST['nif'] ?? ''),
            'rc' => $this->sanitize($_POST['rc'] ?? ''),
            'type_client' => $this->sanitize($_POST['type_client']),
            'conditions_paiement' => $this->sanitize($_POST['conditions_paiement'] ?? ''),
            'statut' => $this->sanitize($_POST['statut']),
            'notes' => $this->sanitize($_POST['notes'] ?? '')
        ];
        
        try {
            $result = $this->clientModel->update($id, $data);
            
            if ($result) {
                $_SESSION['success'] = 'Client modifié avec succès';
                $this->redirect('/clients');
            } else {
                $_SESSION['error'] = 'Erreur lors de la modification';
                $this->redirect('/clients/edit/' . $id);
            }
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erreur: ' . $e->getMessage();
            $this->redirect('/clients/edit/' . $id);
        }
    }

    public function delete($id)
    {
        $this->requireRole([ROLE_ADMIN]);
        
        $sql = "SELECT COUNT(*) as count FROM factures WHERE client_id = :id";
        $result = $this->db->fetch($sql, [':id' => $id]);
        
        if ($result['count'] > 0) {
            $_SESSION['error'] = 'Impossible de supprimer ce client (historique de factures)';
            $this->redirect('/clients');
        }
        
        try {
            $this->clientModel->delete($id);
            $_SESSION['success'] = 'Client supprimé avec succès';
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erreur: ' . $e->getMessage();
        }
        
        $this->redirect('/clients');
    }
}
