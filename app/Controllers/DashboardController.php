<?php
namespace App\Controllers;

use App\Core\Controller;

/**
 * Contrôleur Dashboard
 * Page d'accueil principale après connexion
 */
class DashboardController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Page d'accueil du dashboard
     */
    public function index()
    {
        $this->requireAuth();

        // Récupérer les statistiques pour le dashboard
        $stats = $this->getDashboardStats();

        // Récupérer les alertes
        $alerts = $this->getDashboardAlerts();

        // Récupérer les notifications non lues
        $notifications = $this->getUnreadNotifications();

        $data = [
            'title' => 'Tableau de bord',
            'stats' => $stats,
            'alerts' => $alerts,
            'notifications' => $notifications,
            'user' => [
                'nom' => $_SESSION['nom'],
                'prenom' => $_SESSION['prenom'],
                'email' => $_SESSION['email'],
                'role' => $_SESSION['role_nom'] ?? 'Utilisateur',
                'photo' => $_SESSION['photo'] ?? null
            ]
        ];

        $this->view('dashboard/index', $data);
    }

    /**
     * Récupère les statistiques pour le dashboard
     * 
     * @return array
     */
    private function getDashboardStats()
    {
        $stats = [];

        // Nombre total d'engins
        $sql = "SELECT COUNT(*) as total FROM engins";
        $result = $this->db->fetch($sql);
        $stats['total_engins'] = $result['total'] ?? 0;

        // Engins disponibles
        $sql = "SELECT COUNT(*) as total FROM engins WHERE statut = 'disponible'";
        $result = $this->db->fetch($sql);
        $stats['engins_disponibles'] = $result['total'] ?? 0;

        // Engins en maintenance
        $sql = "SELECT COUNT(*) as total FROM engins WHERE statut = 'en_maintenance'";
        $result = $this->db->fetch($sql);
        $stats['engins_maintenance'] = $result['total'] ?? 0;

        // Engins hors service
        $sql = "SELECT COUNT(*) as total FROM engins WHERE statut = 'hors_service'";
        $result = $this->db->fetch($sql);
        $stats['engins_hors_service'] = $result['total'] ?? 0;

        // Livraisons du jour
        $sql = "SELECT COUNT(*) as total FROM livraisons 
                WHERE DATE(date_prevue) = CURDATE() 
                AND statut IN ('planifiee', 'en_cours')";
        $result = $this->db->fetch($sql);
        $stats['livraisons_jour'] = $result['total'] ?? 0;

        // Stock critique
        $sql = "SELECT COUNT(*) as total FROM pieces 
                WHERE stock_actuel < seuil_alerte AND statut = 'actif'";
        $result = $this->db->fetch($sql);
        $stats['stock_critique'] = $result['total'] ?? 0;

        // Documents expirés
        $sql = "SELECT COUNT(*) as total FROM documents_engins 
                WHERE statut_validite = 'expire'";
        $result = $this->db->fetch($sql);
        $stats['documents_expires'] = $result['total'] ?? 0;

        // Maintenances à venir (7 prochains jours)
        $sql = "SELECT COUNT(*) as total FROM maintenances 
                WHERE statut = 'planifiee' 
                AND date_planifiee BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 7 DAY)";
        $result = $this->db->fetch($sql);
        $stats['maintenances_a_venir'] = $result['total'] ?? 0;

        // CA du mois (factures payées)
        $sql = "SELECT COALESCE(SUM(montant_ttc), 0) as ca_mois 
                FROM factures 
                WHERE YEAR(date_emission) = YEAR(CURDATE()) 
                AND MONTH(date_emission) = MONTH(CURDATE())";
        $result = $this->db->fetch($sql);
        $stats['ca_mois'] = $result['ca_mois'] ?? 0;

        return $stats;
    }

    /**
     * Récupère les alertes pour le dashboard
     * 
     * @return array
     */
    private function getDashboardAlerts()
    {
        $alerts = [];

        // Documents critiques expirés ou à renouveler
        $sql = "SELECT e.immatriculation, d.type_document, d.date_expiration,
                DATEDIFF(d.date_expiration, CURDATE()) as jours_restants
                FROM documents_engins d
                JOIN engins e ON d.engin_id = e.id
                WHERE d.est_critique = TRUE
                AND (d.statut_validite = 'expire' OR d.statut_validite = 'a_renouveler')
                ORDER BY d.date_expiration
                LIMIT 10";
        $alerts['documents'] = $this->db->fetchAll($sql);

        // Stock critique
        $sql = "SELECT reference, description, stock_actuel, seuil_alerte
                FROM pieces
                WHERE stock_actuel < seuil_alerte
                AND statut = 'actif'
                ORDER BY (stock_actuel - seuil_alerte)
                LIMIT 10";
        $alerts['stock'] = $this->db->fetchAll($sql);

        // Livraisons du jour
        $sql = "SELECT l.id, c.nom_raison_sociale as client, 
                e.immatriculation, l.destination, l.statut, l.date_prevue
                FROM livraisons l
                JOIN clients c ON l.client_id = c.id
                JOIN engins e ON l.engin_id = e.id
                WHERE DATE(l.date_prevue) = CURDATE()
                AND l.statut IN ('planifiee', 'en_cours')
                ORDER BY l.date_prevue";
        $alerts['livraisons'] = $this->db->fetchAll($sql);

        // Maintenances planifiées (7 prochains jours)
        $sql = "SELECT m.id, e.immatriculation, m.type, m.description, m.date_planifiee
                FROM maintenances m
                JOIN engins e ON m.engin_id = e.id
                WHERE m.statut = 'planifiee'
                AND m.date_planifiee BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 7 DAY)
                ORDER BY m.date_planifiee
                LIMIT 10";
        $alerts['maintenances'] = $this->db->fetchAll($sql);

        return $alerts;
    }

    /**
     * Récupère les notifications non lues
     * 
     * @return array
     */
    private function getUnreadNotifications()
    {
        $userId = $_SESSION['user_id'];

        $sql = "SELECT * FROM notifications
                WHERE (user_id = :user_id OR user_id IS NULL)
                AND lu = FALSE
                ORDER BY date_creation DESC
                LIMIT 10";

        return $this->db->fetchAll($sql, [':user_id' => $userId]);
    }

    /**
     * Page d'accès refusé
     */
    public function accessDenied()
    {
        $this->requireAuth();
        
        $data = [
            'title' => 'Accès refusé'
        ];

        $this->view('dashboard/access-denied', $data);
    }
}
