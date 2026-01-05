<?php
namespace App\Controllers;

use App\Core\Controller;

/**
 * Contrôleur des Consommations Carburant
 * Suivi carburant et alertes surconsommation
 */
class ConsommationCarburantController extends Controller
{
    private $consommationModel;
    private $enginModel;
    private $chauffeurModel;
    private $mouvementStockModel;

    public function __construct()
    {
        parent::__construct();
        $this->consommationModel = $this->model('ConsommationCarburant');
        $this->enginModel = $this->model('Engin');
        $this->chauffeurModel = $this->model('Chauffeur');
        $this->mouvementStockModel = $this->model('MouvementStock');
    }

    /**
     * Liste des pleins avec graphiques
     */
    public function index()
    {
        $this->requireAuth();
        
        // Récupérer les filtres
        $filters = [
            'engin_id' => $_GET['engin_id'] ?? null,
            'chauffeur_id' => $_GET['chauffeur_id'] ?? null,
            'alerte_surconsommation' => $_GET['alerte_surconsommation'] ?? null,
            'date_debut' => $_GET['date_debut'] ?? null,
            'date_fin' => $_GET['date_fin'] ?? null
        ];
        
        // Construction requête avec filtres
        $where = [];
        $params = [];
        
        if ($filters['engin_id']) {
            $where[] = "c.engin_id = :engin_id";
            $params[':engin_id'] = $filters['engin_id'];
        }
        
        if ($filters['chauffeur_id']) {
            $where[] = "c.chauffeur_id = :chauffeur_id";
            $params[':chauffeur_id'] = $filters['chauffeur_id'];
        }
        
        if ($filters['alerte_surconsommation'] !== null) {
            $where[] = "c.alerte_surconsommation = :alerte";
            $params[':alerte'] = $filters['alerte_surconsommation'] === '1' ? 1 : 0;
        }
        
        if ($filters['date_debut']) {
            $where[] = "DATE(c.date_plein) >= :date_debut";
            $params[':date_debut'] = $filters['date_debut'];
        }
        
        if ($filters['date_fin']) {
            $where[] = "DATE(c.date_plein) <= :date_fin";
            $params[':date_fin'] = $filters['date_fin'];
        }
        
        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
        
        $sql = "SELECT c.*, 
                e.immatriculation, e.type as engin_type,
                ch.nom as chauffeur_nom, ch.prenom as chauffeur_prenom
                FROM consommations_carburant c
                LEFT JOIN engins e ON c.engin_id = e.id
                LEFT JOIN chauffeurs ch ON c.chauffeur_id = ch.id
                $whereClause
                ORDER BY c.date_plein DESC
                LIMIT 100";
        
        $consommations = $this->db->fetchAll($sql, $params);
        
        // Données pour graphique (7 derniers jours)
        $sql = "SELECT DATE(date_plein) as date, 
                e.immatriculation,
                AVG(consommation_calculee) as consommation_moyenne
                FROM consommations_carburant c
                LEFT JOIN engins e ON c.engin_id = e.id
                WHERE date_plein >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                AND consommation_calculee IS NOT NULL
                GROUP BY DATE(date_plein), e.id
                ORDER BY date, e.immatriculation";
        $graphiqueData = $this->db->fetchAll($sql);
        
        // Listes pour filtres
        $engins = $this->enginModel->findAll();
        $chauffeurs = $this->chauffeurModel->findAll();
        
        $data = [
            'title' => 'Suivi Consommation Carburant',
            'consommations' => $consommations,
            'graphique_data' => json_encode($graphiqueData),
            'engins' => $engins,
            'chauffeurs' => $chauffeurs,
            'filters' => $filters,
            'user' => $_SESSION
        ];
        
        $this->view('consommations_carburant/index', $data);
    }

    /**
     * Formulaire enregistrement plein
     */
    public function create()
    {
        $this->requireRole([ROLE_ADMIN, ROLE_GESTIONNAIRE_STOCK]);
        
        // Récupérer engins
        $engins = $this->enginModel->findAll();
        
        // Récupérer chauffeurs
        $chauffeurs = $this->chauffeurModel->findAll();
        
        $typesCarburant = ['Diesel', 'Essence', 'Autre'];
        
        $data = [
            'title' => 'Enregistrer un Plein',
            'engins' => $engins,
            'chauffeurs' => $chauffeurs,
            'types_carburant' => $typesCarburant,
            'user' => $_SESSION,
            'csrf_token' => $this->generateCsrfToken()
        ];
        
        $this->view('consommations_carburant/create', $data);
    }

    /**
     * Enregistrement plein avec calcul consommation et détection surconsommation
     */
    public function store()
    {
        $this->requireRole([ROLE_ADMIN, ROLE_GESTIONNAIRE_STOCK]);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/carburant');
            return;
        }
        
        // Vérifier le token CSRF
        if (!$this->verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            $_SESSION['error'] = "Token de sécurité invalide";
            $this->redirect('/carburant/create');
            return;
        }
        
        // Validation
        $errors = [];
        
        if (empty($_POST['engin_id'])) {
            $errors[] = "L'engin est requis";
        }
        
        if (empty($_POST['type_carburant'])) {
            $errors[] = "Le type de carburant est requis";
        }
        
        if (empty($_POST['quantite_litres']) || floatval($_POST['quantite_litres']) <= 0) {
            $errors[] = "La quantité de carburant est requise et doit être positive";
        }
        
        if (empty($_POST['date_plein'])) {
            $errors[] = "La date du plein est requise";
        }
        
        if (!empty($errors)) {
            $_SESSION['error'] = implode('<br>', $errors);
            $this->redirect('/carburant/create');
            return;
        }
        
        $enginId = intval($_POST['engin_id']);
        $quantiteLitres = floatval($_POST['quantite_litres']);
        $kilometrageActuel = !empty($_POST['kilometrage_actuel']) ? floatval($_POST['kilometrage_actuel']) : null;
        $heuresMoteurActuel = !empty($_POST['heures_moteur_actuel']) ? floatval($_POST['heures_moteur_actuel']) : null;
        
        // Récupérer le dernier plein de cet engin
        $sql = "SELECT * FROM consommations_carburant 
                WHERE engin_id = :engin_id 
                ORDER BY date_plein DESC 
                LIMIT 1";
        $dernierPlein = $this->db->fetch($sql, [':engin_id' => $enginId]);
        
        // Calculer la consommation si possible
        $consommationCalculee = null;
        $alerteSurconsommation = false;
        
        if ($dernierPlein) {
            if ($kilometrageActuel && $dernierPlein['kilometrage_actuel']) {
                // Calcul L/100km
                $kmParcourus = $kilometrageActuel - floatval($dernierPlein['kilometrage_actuel']);
                
                if ($kmParcourus > 0) {
                    $consommationCalculee = ($quantiteLitres / $kmParcourus) * 100;
                }
            } elseif ($heuresMoteurActuel && $dernierPlein['heures_moteur_actuel']) {
                // Calcul L/h
                $heuresUtilisees = $heuresMoteurActuel - floatval($dernierPlein['heures_moteur_actuel']);
                
                if ($heuresUtilisees > 0) {
                    $consommationCalculee = $quantiteLitres / $heuresUtilisees;
                }
            }
        }
        
        // Préparer les données
        $data = [
            'engin_id' => $enginId,
            'chauffeur_id' => !empty($_POST['chauffeur_id']) ? intval($_POST['chauffeur_id']) : null,
            'type_carburant' => $this->sanitize($_POST['type_carburant']),
            'quantite_litres' => $quantiteLitres,
            'date_plein' => $_POST['date_plein'],
            'kilometrage_actuel' => $kilometrageActuel,
            'heures_moteur_actuel' => $heuresMoteurActuel,
            'lieu_plein' => $this->sanitize($_POST['lieu_plein'] ?? ''),
            'montant' => !empty($_POST['montant']) ? floatval($_POST['montant']) : null,
            'notes' => $this->sanitize($_POST['notes'] ?? ''),
            'consommation_calculee' => $consommationCalculee,
            'alerte_surconsommation' => false,
            'user_id' => $_SESSION['user_id']
        ];
        
        try {
            $this->db->beginTransaction();
            
            $id = $this->consommationModel->insert($data);
            
            // Détecter surconsommation
            if ($consommationCalculee !== null) {
                $surconsommation = $this->consommationModel->detecterSurconsommation($enginId);
                
                if ($surconsommation) {
                    $this->consommationModel->update($id, ['alerte_surconsommation' => true]);
                    
                    // Créer notification pour admin/maintenance
                    $sql = "INSERT INTO notifications (titre, message, type, user_id, reference_id, reference_type)
                            VALUES (:titre, :message, 'alerte', :user_id, :reference_id, 'consommation_carburant')";
                    $this->db->query($sql, [
                        ':titre' => 'Surconsommation détectée',
                        ':message' => "Surconsommation carburant détectée sur l'engin #$enginId",
                        ':user_id' => 1, // Admin
                        ':reference_id' => $id
                    ]);
                }
            }
            
            // Sortir carburant du stock
            // Trouver la pièce carburant correspondante
            $sqlPiece = "SELECT id FROM pieces 
                         WHERE type = 'carburant' 
                         AND LOWER(designation) LIKE :type_carburant 
                         LIMIT 1";
            $piece = $this->db->fetch($sqlPiece, [
                ':type_carburant' => '%' . strtolower($_POST['type_carburant']) . '%'
            ]);
            
            if ($piece) {
                $this->mouvementStockModel->insert([
                    'piece_id' => $piece['id'],
                    'type_mouvement' => 'sortie',
                    'quantite' => $quantiteLitres,
                    'motif' => 'Consommation carburant engin #' . $enginId,
                    'reference_id' => $id,
                    'reference_type' => 'consommation_carburant',
                    'user_id' => $_SESSION['user_id']
                ]);
            }
            
            // Mettre à jour le kilométrage/heures de l'engin
            $updateEngin = [];
            if ($kilometrageActuel !== null) {
                $updateEngin['kilometrage_actuel'] = $kilometrageActuel;
            }
            if ($heuresMoteurActuel !== null) {
                $updateEngin['heures_moteur'] = $heuresMoteurActuel;
            }
            
            if (!empty($updateEngin)) {
                $this->enginModel->update($enginId, $updateEngin);
            }
            
            $this->db->commit();
            
            $_SESSION['success'] = "Plein enregistré avec succès";
            $this->redirect('/carburant');
            
        } catch (\Exception $e) {
            $this->db->rollback();
            $_SESSION['error'] = "Erreur lors de l'enregistrement : " . $e->getMessage();
            $this->redirect('/carburant/create');
        }
    }

    /**
     * Modification
     */
    public function edit($id)
    {
        $this->requireRole([ROLE_ADMIN, ROLE_GESTIONNAIRE_STOCK]);
        
        $consommation = $this->consommationModel->findById($id);
        
        if (!$consommation) {
            $_SESSION['error'] = "Consommation introuvable";
            $this->redirect('/carburant');
            return;
        }
        
        $engins = $this->enginModel->findAll();
        $chauffeurs = $this->chauffeurModel->findAll();
        $typesCarburant = ['Diesel', 'Essence', 'Autre'];
        
        $data = [
            'title' => 'Modifier Plein #' . $id,
            'consommation' => $consommation,
            'engins' => $engins,
            'chauffeurs' => $chauffeurs,
            'types_carburant' => $typesCarburant,
            'user' => $_SESSION,
            'csrf_token' => $this->generateCsrfToken()
        ];
        
        $this->view('consommations_carburant/edit', $data);
    }

    /**
     * Mise à jour
     */
    public function update($id)
    {
        $this->requireRole([ROLE_ADMIN, ROLE_GESTIONNAIRE_STOCK]);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/carburant');
            return;
        }
        
        // Vérifier le token CSRF
        if (!$this->verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            $_SESSION['error'] = "Token de sécurité invalide";
            $this->redirect('/carburant/edit/' . $id);
            return;
        }
        
        $data = [
            'notes' => $this->sanitize($_POST['notes'] ?? ''),
            'montant' => !empty($_POST['montant']) ? floatval($_POST['montant']) : null
        ];
        
        try {
            $this->consommationModel->update($id, $data);
            $_SESSION['success'] = "Plein mis à jour avec succès";
        } catch (\Exception $e) {
            $_SESSION['error'] = "Erreur lors de la mise à jour : " . $e->getMessage();
        }
        
        $this->redirect('/carburant');
    }

    /**
     * Suppression
     */
    public function delete($id)
    {
        $this->requireRole([ROLE_ADMIN]);
        
        try {
            $this->consommationModel->delete($id);
            $_SESSION['success'] = "Plein supprimé avec succès";
        } catch (\Exception $e) {
            $_SESSION['error'] = "Erreur lors de la suppression : " . $e->getMessage();
        }
        
        $this->redirect('/carburant');
    }

    /**
     * Rapport mensuel détaillé
     */
    public function rapportMensuel()
    {
        $this->requireAuth();
        
        $mois = $_GET['mois'] ?? date('m');
        $annee = $_GET['annee'] ?? date('Y');
        
        // Statistiques par engin pour le mois
        $sql = "SELECT e.id, e.immatriculation, e.type,
                COUNT(c.id) as nb_pleins,
                SUM(c.quantite_litres) as total_litres,
                COALESCE(SUM(c.montant), 0) as total_cout,
                AVG(c.consommation_calculee) as consommation_moyenne
                FROM engins e
                LEFT JOIN consommations_carburant c ON e.id = c.engin_id
                    AND MONTH(c.date_plein) = :mois
                    AND YEAR(c.date_plein) = :annee
                GROUP BY e.id
                HAVING nb_pleins > 0
                ORDER BY total_litres DESC";
        
        $statistiquesEngins = $this->db->fetchAll($sql, [
            ':mois' => $mois,
            ':annee' => $annee
        ]);
        
        // Évolution quotidienne du mois
        $sql = "SELECT DATE(date_plein) as date,
                SUM(quantite_litres) as total_litres,
                SUM(montant) as total_cout
                FROM consommations_carburant
                WHERE MONTH(date_plein) = :mois
                AND YEAR(date_plein) = :annee
                GROUP BY DATE(date_plein)
                ORDER BY date";
        
        $evolutionQuotidienne = $this->db->fetchAll($sql, [
            ':mois' => $mois,
            ':annee' => $annee
        ]);
        
        // Pleins avec surconsommation du mois
        $sql = "SELECT c.*, e.immatriculation, e.type as engin_type
                FROM consommations_carburant c
                LEFT JOIN engins e ON c.engin_id = e.id
                WHERE c.alerte_surconsommation = TRUE
                AND MONTH(c.date_plein) = :mois
                AND YEAR(c.date_plein) = :annee
                ORDER BY c.date_plein DESC";
        
        $surconsommations = $this->db->fetchAll($sql, [
            ':mois' => $mois,
            ':annee' => $annee
        ]);
        
        $data = [
            'title' => 'Rapport Mensuel Carburant',
            'mois' => $mois,
            'annee' => $annee,
            'statistiques_engins' => $statistiquesEngins,
            'evolution_quotidienne' => json_encode($evolutionQuotidienne),
            'surconsommations' => $surconsommations,
            'user' => $_SESSION
        ];
        
        $this->view('consommations_carburant/rapport_mensuel', $data);
    }

    /**
     * Dashboard alertes surconsommation
     */
    public function alertes()
    {
        $this->requireRole([ROLE_ADMIN, ROLE_MAINTENANCE]);
        
        // Récupérer les pleins avec alerte non traités
        $sql = "SELECT c.*, e.immatriculation, e.type as engin_type,
                ch.nom as chauffeur_nom, ch.prenom as chauffeur_prenom
                FROM consommations_carburant c
                LEFT JOIN engins e ON c.engin_id = e.id
                LEFT JOIN chauffeurs ch ON c.chauffeur_id = ch.id
                WHERE c.alerte_surconsommation = TRUE
                AND (c.alerte_verifiee = FALSE OR c.alerte_verifiee IS NULL)
                ORDER BY c.date_plein DESC";
        
        $alertes = $this->db->fetchAll($sql);
        
        // Pour chaque alerte, calculer comparaison avec moyenne mobile 30j
        foreach ($alertes as &$alerte) {
            $sql = "SELECT AVG(consommation_calculee) as moyenne
                    FROM consommations_carburant
                    WHERE engin_id = :engin_id
                    AND date_plein BETWEEN DATE_SUB(:date_plein, INTERVAL 30 DAY) AND :date_plein
                    AND id != :id
                    AND consommation_calculee IS NOT NULL";
            
            $moyenneMobile = $this->db->fetch($sql, [
                ':engin_id' => $alerte['engin_id'],
                ':date_plein' => $alerte['date_plein'],
                ':id' => $alerte['id']
            ]);
            
            $alerte['moyenne_mobile_30j'] = $moyenneMobile['moyenne'] ?? null;
            
            if ($alerte['moyenne_mobile_30j']) {
                $alerte['ecart_pourcentage'] = (($alerte['consommation_calculee'] - $alerte['moyenne_mobile_30j']) / $alerte['moyenne_mobile_30j']) * 100;
            }
        }
        
        $data = [
            'title' => 'Alertes Surconsommation',
            'alertes' => $alertes,
            'user' => $_SESSION,
            'csrf_token' => $this->generateCsrfToken()
        ];
        
        $this->view('consommations_carburant/alertes', $data);
    }

    /**
     * Marquer alerte comme vérifiée
     */
    public function marquerVerifiee($id)
    {
        $this->requireRole([ROLE_ADMIN, ROLE_MAINTENANCE]);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $noteExplication = $this->sanitize($_POST['note_explication'] ?? '');
            
            $data = [
                'alerte_verifiee' => true,
                'notes' => $noteExplication
            ];
            
            try {
                $this->consommationModel->update($id, $data);
                $_SESSION['success'] = "Alerte marquée comme vérifiée";
            } catch (\Exception $e) {
                $_SESSION['error'] = "Erreur : " . $e->getMessage();
            }
        }
        
        $this->redirect('/carburant/alertes');
    }
}
