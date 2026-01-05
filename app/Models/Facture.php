<?php
namespace App\Models;

use App\Core\Model;

/**
 * Modèle Facture
 * Gestion de la facturation et des paiements
 */
class Facture extends Model
{
    protected $table = 'factures';

    /**
     * Récupère toutes les factures d'un client
     * 
     * @param int $clientId
     * @return array
     */
    public function getByClient($clientId)
    {
        $sql = "SELECT f.*, c.nom as client_nom, l.numero_livraison
                FROM {$this->table} f
                LEFT JOIN clients c ON f.client_id = c.id
                LEFT JOIN livraisons l ON f.livraison_id = l.id
                WHERE f.client_id = :client_id
                ORDER BY f.date_emission DESC";
        
        return $this->db->fetchAll($sql, [':client_id' => $clientId]);
    }

    /**
     * Récupère les factures impayées
     * 
     * @return array
     */
    public function getImpayees()
    {
        $sql = "SELECT f.*, c.nom as client_nom, c.telephone as client_telephone
                FROM {$this->table} f
                LEFT JOIN clients c ON f.client_id = c.id
                WHERE f.statut_paiement = 'impayee'
                ORDER BY f.date_emission DESC";
        
        return $this->db->fetchAll($sql);
    }

    /**
     * Récupère les factures en retard (impayées avec échéance dépassée)
     * 
     * @return array
     */
    public function getEnRetard()
    {
        $sql = "SELECT f.*, c.nom as client_nom, c.telephone as client_telephone,
                DATEDIFF(CURDATE(), f.date_echeance) as jours_retard
                FROM {$this->table} f
                LEFT JOIN clients c ON f.client_id = c.id
                WHERE f.statut_paiement IN ('impayee', 'partiellement_payee')
                AND f.date_echeance < CURDATE()
                ORDER BY f.date_echeance ASC";
        
        return $this->db->fetchAll($sql);
    }

    /**
     * Génère un numéro de facture unique au format FACT-YYYY-NNNN
     * 
     * @return string
     */
    public function genererNumero()
    {
        $annee = date('Y');
        
        // Récupérer le dernier numéro de l'année
        $sql = "SELECT numero_facture 
                FROM {$this->table} 
                WHERE numero_facture LIKE :pattern
                ORDER BY numero_facture DESC 
                LIMIT 1";
        
        $result = $this->db->fetch($sql, [':pattern' => "FACT-$annee-%"]);
        
        if ($result) {
            // Extraire le numéro séquentiel
            $parts = explode('-', $result['numero_facture']);
            $sequence = intval($parts[2] ?? 0) + 1;
        } else {
            $sequence = 1;
        }
        
        return sprintf('FACT-%s-%04d', $annee, $sequence);
    }

    /**
     * Calcule les montants TTC à partir du montant HT
     * 
     * @param float $montantHt
     * @param float $tauxTva
     * @return array ['montant_tva' => float, 'montant_ttc' => float]
     */
    public function calculerMontants($montantHt, $tauxTva)
    {
        $montantTva = round($montantHt * $tauxTva / 100, 2);
        $montantTtc = round($montantHt + $montantTva, 2);
        
        return [
            'montant_tva' => $montantTva,
            'montant_ttc' => $montantTtc
        ];
    }

    /**
     * Marque une facture comme payée
     * 
     * @param int $id
     * @param string $datePaiement Format YYYY-MM-DD
     * @param string $modePaiement
     * @return bool
     */
    public function marquerPayee($id, $datePaiement, $modePaiement)
    {
        $sql = "UPDATE {$this->table} 
                SET statut_paiement = 'payee',
                    date_paiement = :date_paiement,
                    mode_paiement = :mode_paiement,
                    montant_paye = montant_ttc
                WHERE id = :id";
        
        return $this->db->execute($sql, [
            ':id' => $id,
            ':date_paiement' => $datePaiement,
            ':mode_paiement' => $modePaiement
        ]);
    }

    /**
     * Calcule le chiffre d'affaires sur une période
     * 
     * @param string $dateDebut Format YYYY-MM-DD
     * @param string $dateFin Format YYYY-MM-DD
     * @return array
     */
    public function getChiffreAffaires($dateDebut, $dateFin)
    {
        $sql = "SELECT 
                COUNT(*) as nombre_factures,
                COALESCE(SUM(montant_ht), 0) as total_ht,
                COALESCE(SUM(montant_tva), 0) as total_tva,
                COALESCE(SUM(montant_ttc), 0) as total_ttc,
                COALESCE(SUM(CASE WHEN statut_paiement = 'payee' THEN montant_ttc ELSE 0 END), 0) as total_encaisse,
                COALESCE(SUM(CASE WHEN statut_paiement IN ('impayee', 'partiellement_payee') THEN montant_ttc ELSE 0 END), 0) as total_impaye
                FROM {$this->table}
                WHERE date_emission BETWEEN :date_debut AND :date_fin";
        
        return $this->db->fetch($sql, [
            ':date_debut' => $dateDebut,
            ':date_fin' => $dateFin
        ]);
    }

    /**
     * Récupère une facture complète avec toutes ses données
     * 
     * @param int $id
     * @return array|false
     */
    public function getFactureComplete($id)
    {
        $sql = "SELECT f.*,
                c.nom as client_nom, c.adresse as client_adresse,
                c.telephone as client_telephone, c.email as client_email,
                c.nif as client_nif, c.rc as client_rc,
                l.numero_livraison, l.type_materiau, l.poids, l.volume,
                l.lieu_depart, l.lieu_destination,
                l.date_depart, l.date_livraison,
                e.immatriculation, e.type as engin_type,
                ch.nom as chauffeur_nom, ch.prenom as chauffeur_prenom,
                u.nom as user_nom, u.prenom as user_prenom
                FROM {$this->table} f
                LEFT JOIN clients c ON f.client_id = c.id
                LEFT JOIN livraisons l ON f.livraison_id = l.id
                LEFT JOIN engins e ON l.engin_id = e.id
                LEFT JOIN chauffeurs ch ON l.chauffeur_id = ch.id
                LEFT JOIN users u ON f.user_id = u.id
                WHERE f.id = :id";
        
        return $this->db->fetch($sql, [':id' => $id]);
    }

    /**
     * Récupère les statistiques de facturation par client
     * 
     * @param int $clientId
     * @return array
     */
    public function getStatsByClient($clientId)
    {
        $sql = "SELECT 
                COUNT(*) as nombre_factures,
                COALESCE(SUM(montant_ttc), 0) as total_facture,
                COALESCE(SUM(montant_paye), 0) as total_paye,
                COALESCE(SUM(montant_ttc - montant_paye), 0) as reste_a_payer,
                MIN(date_emission) as premiere_facture,
                MAX(date_emission) as derniere_facture
                FROM {$this->table}
                WHERE client_id = :client_id";
        
        return $this->db->fetch($sql, [':client_id' => $clientId]);
    }

    /**
     * Récupère les factures par statut de paiement
     * 
     * @param string $statutPaiement
     * @return array
     */
    public function getByStatutPaiement($statutPaiement)
    {
        $sql = "SELECT f.*, c.nom as client_nom
                FROM {$this->table} f
                LEFT JOIN clients c ON f.client_id = c.id
                WHERE f.statut_paiement = :statut_paiement
                ORDER BY f.date_emission DESC";
        
        return $this->db->fetchAll($sql, [':statut_paiement' => $statutPaiement]);
    }

    /**
     * Récupère les factures échéant dans les prochains jours
     * 
     * @param int $jours
     * @return array
     */
    public function getEcheantSous($jours = 7)
    {
        $sql = "SELECT f.*, c.nom as client_nom, c.telephone as client_telephone,
                DATEDIFF(f.date_echeance, CURDATE()) as jours_avant_echeance
                FROM {$this->table} f
                LEFT JOIN clients c ON f.client_id = c.id
                WHERE f.statut_paiement IN ('impayee', 'partiellement_payee')
                AND f.date_echeance BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL :jours DAY)
                ORDER BY f.date_echeance ASC";
        
        return $this->db->fetchAll($sql, [':jours' => $jours]);
    }

    /**
     * Récupère le chiffre d'affaires mensuel par année
     * 
     * @param int $annee
     * @return array
     */
    public function getCAMensuel($annee)
    {
        $sql = "SELECT 
                MONTH(date_emission) as mois,
                COUNT(*) as nombre_factures,
                COALESCE(SUM(montant_ttc), 0) as ca_ttc,
                COALESCE(SUM(CASE WHEN statut_paiement = 'payee' THEN montant_ttc ELSE 0 END), 0) as ca_encaisse
                FROM {$this->table}
                WHERE YEAR(date_emission) = :annee
                GROUP BY MONTH(date_emission)
                ORDER BY mois";
        
        return $this->db->fetchAll($sql, [':annee' => $annee]);
    }
}
