<?php
namespace App\Models;

use App\Core\Model;

/**
 * Modèle ConsommationCarburant
 * Gestion et analyse de la consommation de carburant
 */
class ConsommationCarburant extends Model
{
    protected $table = 'consommations_carburant';

    /**
     * Récupère toutes les consommations d'un engin
     * 
     * @param int $enginId
     * @return array
     */
    public function getByEngin($enginId)
    {
        $sql = "SELECT c.*, 
                e.immatriculation, e.type as engin_type,
                ch.nom as chauffeur_nom, ch.prenom as chauffeur_prenom,
                p.reference as carburant_reference, p.description as carburant_description
                FROM {$this->table} c
                LEFT JOIN engins e ON c.engin_id = e.id
                LEFT JOIN chauffeurs ch ON c.chauffeur_id = ch.id
                LEFT JOIN pieces p ON c.piece_carburant_id = p.id
                WHERE c.engin_id = :engin_id
                ORDER BY c.date_plein DESC";
        
        return $this->db->fetchAll($sql, [':engin_id' => $enginId]);
    }

    /**
     * Récupère les consommations sur une période
     * 
     * @param string $dateDebut Format YYYY-MM-DD
     * @param string $dateFin Format YYYY-MM-DD
     * @return array
     */
    public function getByPeriode($dateDebut, $dateFin)
    {
        $sql = "SELECT c.*, 
                e.immatriculation, e.type as engin_type,
                ch.nom as chauffeur_nom, ch.prenom as chauffeur_prenom
                FROM {$this->table} c
                LEFT JOIN engins e ON c.engin_id = e.id
                LEFT JOIN chauffeurs ch ON c.chauffeur_id = ch.id
                WHERE c.date_plein BETWEEN :date_debut AND :date_fin
                ORDER BY c.date_plein DESC";
        
        return $this->db->fetchAll($sql, [
            ':date_debut' => $dateDebut,
            ':date_fin' => $dateFin
        ]);
    }

    /**
     * Calcule la consommation pour un plein (litres/100km ou litres/heure)
     * 
     * @param int $id
     * @return bool
     */
    public function calculerConsommation($id)
    {
        $consommation = $this->findById($id);
        
        if (!$consommation) {
            return false;
        }
        
        // Récupérer le plein précédent pour calculer la consommation
        $sql = "SELECT kilometrage, heures_moteur, quantite_litres
                FROM {$this->table}
                WHERE engin_id = :engin_id
                AND date_plein < :date_plein
                ORDER BY date_plein DESC
                LIMIT 1";
        
        $pleinPrecedent = $this->db->fetch($sql, [
            ':engin_id' => $consommation['engin_id'],
            ':date_plein' => $consommation['date_plein']
        ]);
        
        $consommationCalculee = null;
        
        if ($pleinPrecedent) {
            // Calculer selon le type de mesure disponible
            if (!empty($consommation['kilometrage']) && !empty($pleinPrecedent['kilometrage'])) {
                // Consommation en L/100km
                $distanceParcourue = $consommation['kilometrage'] - $pleinPrecedent['kilometrage'];
                
                if ($distanceParcourue > 0) {
                    $consommationCalculee = ($pleinPrecedent['quantite_litres'] / $distanceParcourue) * 100;
                }
            } elseif (!empty($consommation['heures_moteur']) && !empty($pleinPrecedent['heures_moteur'])) {
                // Consommation en L/heure pour engins stationnaires
                $heuresFonctionnement = $consommation['heures_moteur'] - $pleinPrecedent['heures_moteur'];
                
                if ($heuresFonctionnement > 0) {
                    $consommationCalculee = $pleinPrecedent['quantite_litres'] / $heuresFonctionnement;
                }
            }
        }
        
        if ($consommationCalculee !== null) {
            // Mettre à jour la consommation calculée
            $sql = "UPDATE {$this->table} 
                    SET consommation_calculee = :consommation
                    WHERE id = :id";
            
            $this->db->execute($sql, [
                ':id' => $id,
                ':consommation' => round($consommationCalculee, 2)
            ]);
            
            // Vérifier la surconsommation
            $this->detecterSurconsommation($consommation['engin_id']);
            
            return true;
        }
        
        return false;
    }

    /**
     * Détecte si un engin a une surconsommation (> moyenne mobile + 20%)
     * 
     * @param int $enginId
     * @return bool
     */
    public function detecterSurconsommation($enginId)
    {
        $moyenneMobile = $this->getMoyenneMobile($enginId, 30);
        
        if ($moyenneMobile === null || $moyenneMobile == 0) {
            return false;
        }
        
        $seuilAlerte = $moyenneMobile * 1.20; // +20%
        
        // Récupérer les dernières consommations non encore analysées
        $sql = "SELECT id, consommation_calculee
                FROM {$this->table}
                WHERE engin_id = :engin_id
                AND consommation_calculee IS NOT NULL
                AND consommation_calculee > :seuil
                ORDER BY date_plein DESC
                LIMIT 10";
        
        $surconsommations = $this->db->fetchAll($sql, [
            ':engin_id' => $enginId,
            ':seuil' => $seuilAlerte
        ]);
        
        // Marquer les surconsommations
        foreach ($surconsommations as $conso) {
            $sql = "UPDATE {$this->table} 
                    SET alerte_surconsommation = TRUE,
                        moyenne_mobile_30j = :moyenne
                    WHERE id = :id";
            
            $this->db->execute($sql, [
                ':id' => $conso['id'],
                ':moyenne' => round($moyenneMobile, 2)
            ]);
        }
        
        return !empty($surconsommations);
    }

    /**
     * Calcule la moyenne mobile de consommation sur X jours
     * 
     * @param int $enginId
     * @param int $jours Nombre de jours (défaut: 30)
     * @return float|null
     */
    public function getMoyenneMobile($enginId, $jours = 30)
    {
        $sql = "SELECT AVG(consommation_calculee) as moyenne
                FROM {$this->table}
                WHERE engin_id = :engin_id
                AND consommation_calculee IS NOT NULL
                AND date_plein >= DATE_SUB(CURDATE(), INTERVAL :jours DAY)";
        
        $result = $this->db->fetch($sql, [
            ':engin_id' => $enginId,
            ':jours' => $jours
        ]);
        
        return $result['moyenne'] ?? null;
    }

    /**
     * Génère un rapport mensuel de consommation par engin
     * 
     * @param int $mois (1-12)
     * @param int $annee
     * @return array
     */
    public function getRapportMensuel($mois, $annee)
    {
        $sql = "SELECT 
                e.id as engin_id,
                e.immatriculation,
                e.type as engin_type,
                e.marque,
                e.modele,
                COUNT(c.id) as nombre_pleins,
                COALESCE(SUM(c.quantite_litres), 0) as litres_total,
                COALESCE(SUM(c.montant), 0) as montant_total,
                COALESCE(AVG(c.consommation_calculee), 0) as consommation_moyenne,
                MAX(c.kilometrage) - MIN(c.kilometrage) as distance_parcourue,
                MAX(c.heures_moteur) - MIN(c.heures_moteur) as heures_fonctionnement,
                SUM(CASE WHEN c.alerte_surconsommation = TRUE THEN 1 ELSE 0 END) as nb_alertes
                FROM engins e
                LEFT JOIN {$this->table} c ON e.id = c.engin_id
                    AND MONTH(c.date_plein) = :mois
                    AND YEAR(c.date_plein) = :annee
                GROUP BY e.id
                HAVING nombre_pleins > 0
                ORDER BY e.immatriculation";
        
        return $this->db->fetchAll($sql, [
            ':mois' => $mois,
            ':annee' => $annee
        ]);
    }

    /**
     * Récupère les pleins avec alerte de surconsommation
     * 
     * @return array
     */
    public function getSurconsommations()
    {
        $sql = "SELECT c.*, 
                e.immatriculation, e.type as engin_type,
                ch.nom as chauffeur_nom, ch.prenom as chauffeur_prenom,
                ((c.consommation_calculee - c.moyenne_mobile_30j) / c.moyenne_mobile_30j * 100) as pourcentage_surconso
                FROM {$this->table} c
                LEFT JOIN engins e ON c.engin_id = e.id
                LEFT JOIN chauffeurs ch ON c.chauffeur_id = ch.id
                WHERE c.alerte_surconsommation = TRUE
                ORDER BY c.date_plein DESC";
        
        return $this->db->fetchAll($sql);
    }

    /**
     * Récupère les statistiques de consommation par engin
     * 
     * @param int $enginId
     * @return array
     */
    public function getStatsByEngin($enginId)
    {
        $sql = "SELECT 
                COUNT(*) as nombre_pleins,
                COALESCE(SUM(quantite_litres), 0) as litres_total,
                COALESCE(AVG(quantite_litres), 0) as litres_moyen_par_plein,
                COALESCE(SUM(montant), 0) as cout_total,
                COALESCE(AVG(montant), 0) as cout_moyen_par_plein,
                COALESCE(AVG(consommation_calculee), 0) as consommation_moyenne,
                MIN(date_plein) as premier_plein,
                MAX(date_plein) as dernier_plein,
                SUM(CASE WHEN alerte_surconsommation = TRUE THEN 1 ELSE 0 END) as nb_alertes
                FROM {$this->table}
                WHERE engin_id = :engin_id";
        
        return $this->db->fetch($sql, [':engin_id' => $enginId]);
    }

    /**
     * Récupère les consommations par chauffeur
     * 
     * @param int $chauffeurId
     * @return array
     */
    public function getByChauffeur($chauffeurId)
    {
        $sql = "SELECT c.*, 
                e.immatriculation, e.type as engin_type
                FROM {$this->table} c
                LEFT JOIN engins e ON c.engin_id = e.id
                WHERE c.chauffeur_id = :chauffeur_id
                ORDER BY c.date_plein DESC";
        
        return $this->db->fetchAll($sql, [':chauffeur_id' => $chauffeurId]);
    }

    /**
     * Récupère le dernier plein d'un engin
     * 
     * @param int $enginId
     * @return array|false
     */
    public function getDernierPlein($enginId)
    {
        $sql = "SELECT c.*, 
                ch.nom as chauffeur_nom, ch.prenom as chauffeur_prenom
                FROM {$this->table} c
                LEFT JOIN chauffeurs ch ON c.chauffeur_id = ch.id
                WHERE c.engin_id = :engin_id
                ORDER BY c.date_plein DESC
                LIMIT 1";
        
        return $this->db->fetch($sql, [':engin_id' => $enginId]);
    }

    /**
     * Récupère le tableau de bord des consommations
     * 
     * @return array
     */
    public function getDashboard()
    {
        return [
            'surconsommations' => $this->getSurconsommations(),
            'consommation_mensuelle' => $this->getRapportMensuel(date('n'), date('Y')),
            'total_pleins_mois' => $this->getTotalPleinsMoisCourant(),
            'cout_total_mois' => $this->getCoutTotalMoisCourant()
        ];
    }

    /**
     * Récupère le nombre total de pleins du mois courant
     * 
     * @return int
     */
    private function getTotalPleinsMoisCourant()
    {
        $sql = "SELECT COUNT(*) as total
                FROM {$this->table}
                WHERE MONTH(date_plein) = MONTH(CURDATE())
                AND YEAR(date_plein) = YEAR(CURDATE())";
        
        $result = $this->db->fetch($sql);
        return $result['total'] ?? 0;
    }

    /**
     * Récupère le coût total de carburant du mois courant
     * 
     * @return float
     */
    private function getCoutTotalMoisCourant()
    {
        $sql = "SELECT COALESCE(SUM(montant), 0) as total
                FROM {$this->table}
                WHERE MONTH(date_plein) = MONTH(CURDATE())
                AND YEAR(date_plein) = YEAR(CURDATE())";
        
        $result = $this->db->fetch($sql);
        return $result['total'] ?? 0;
    }
}
