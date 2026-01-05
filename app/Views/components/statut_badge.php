<?php
/**
 * Composant Générateur de Badges de Statut
 * Fonctions PHP pour générer des badges Bootstrap colorés selon le contexte
 */

/**
 * Badge pour le statut d'un engin
 * @param string $statut
 * @return string HTML du badge
 */
function badgeStatutEngin($statut) {
    $badges = [
        'disponible' => '<span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Disponible</span>',
        'en_mission' => '<span class="badge bg-primary"><i class="bi bi-truck me-1"></i>En Mission</span>',
        'en_maintenance' => '<span class="badge bg-warning text-dark"><i class="bi bi-tools me-1"></i>En Maintenance</span>',
        'hors_service' => '<span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i>Hors Service</span>'
    ];
    
    return $badges[$statut] ?? '<span class="badge bg-secondary">Inconnu</span>';
}

/**
 * Badge pour le statut d'une livraison
 * @param string $statut
 * @return string HTML du badge
 */
function badgeStatutLivraison($statut) {
    $badges = [
        'planifiee' => '<span class="badge bg-info"><i class="bi bi-calendar-event me-1"></i>Planifiée</span>',
        'en_cours' => '<span class="badge bg-warning text-dark"><i class="bi bi-hourglass-split me-1"></i>En Cours</span>',
        'livree' => '<span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Livrée</span>',
        'annulee' => '<span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i>Annulée</span>'
    ];
    
    return $badges[$statut] ?? '<span class="badge bg-secondary">Inconnu</span>';
}

/**
 * Badge pour le statut d'une maintenance
 * @param string $statut
 * @return string HTML du badge
 */
function badgeStatutMaintenance($statut) {
    $badges = [
        'planifiee' => '<span class="badge bg-info"><i class="bi bi-calendar-check me-1"></i>Planifiée</span>',
        'en_cours' => '<span class="badge bg-warning text-dark"><i class="bi bi-wrench me-1"></i>En Cours</span>',
        'terminee' => '<span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Terminée</span>',
        'annulee' => '<span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i>Annulée</span>'
    ];
    
    return $badges[$statut] ?? '<span class="badge bg-secondary">Inconnu</span>';
}

/**
 * Badge pour le type de maintenance
 * @param string $type
 * @return string HTML du badge
 */
function badgeTypeMaintenance($type) {
    $badges = [
        'preventive' => '<span class="badge bg-success"><i class="bi bi-shield-check me-1"></i>Préventive</span>',
        'corrective' => '<span class="badge bg-danger"><i class="bi bi-wrench me-1"></i>Corrective</span>'
    ];
    
    return $badges[$type] ?? '<span class="badge bg-secondary">Inconnu</span>';
}

/**
 * Badge pour le statut de paiement d'une facture
 * @param string $statut
 * @return string HTML du badge
 */
function badgeStatutPaiement($statut) {
    $badges = [
        'impayee' => '<span class="badge bg-danger"><i class="bi bi-exclamation-circle me-1"></i>Impayée</span>',
        'partiellement_payee' => '<span class="badge bg-warning text-dark"><i class="bi bi-hourglass-split me-1"></i>Partielle</span>',
        'payee' => '<span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Payée</span>'
    ];
    
    return $badges[$statut] ?? '<span class="badge bg-secondary">Inconnu</span>';
}

/**
 * Badge pour le niveau de stock
 * @param float $stock_actuel
 * @param float $seuil_alerte
 * @return string HTML du badge
 */
function badgeNiveauStock($stock_actuel, $seuil_alerte) {
    if ($stock_actuel <= 0) {
        return '<span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i>Rupture</span>';
    } elseif ($stock_actuel < $seuil_alerte) {
        return '<span class="badge bg-danger"><i class="bi bi-exclamation-triangle me-1"></i>Critique</span>';
    } elseif ($stock_actuel < $seuil_alerte * 1.5) {
        return '<span class="badge bg-warning text-dark"><i class="bi bi-exclamation-circle me-1"></i>Bas</span>';
    } else {
        return '<span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>OK</span>';
    }
}

/**
 * Badge pour la conformité d'un engin (documents)
 * @param bool $conforme
 * @param int $nb_documents_manquants
 * @param int $nb_documents_expires
 * @return string HTML du badge
 */
function badgeConformiteEngin($conforme, $nb_documents_manquants = 0, $nb_documents_expires = 0) {
    if ($conforme) {
        return '<span class="badge bg-success"><i class="bi bi-shield-check me-1"></i>Conforme</span>';
    }
    
    $messages = [];
    if ($nb_documents_manquants > 0) {
        $messages[] = "$nb_documents_manquants manquant" . ($nb_documents_manquants > 1 ? 's' : '');
    }
    if ($nb_documents_expires > 0) {
        $messages[] = "$nb_documents_expires expiré" . ($nb_documents_expires > 1 ? 's' : '');
    }
    
    $tooltip = implode(', ', $messages);
    
    return '<span class="badge bg-danger" title="' . htmlspecialchars($tooltip) . '" data-bs-toggle="tooltip"><i class="bi bi-exclamation-triangle me-1"></i>Non Conforme</span>';
}

/**
 * Badge pour la validité d'un document
 * @param string $date_expiration Format 'Y-m-d'
 * @return string HTML du badge
 */
function badgeValiditeDocument($date_expiration) {
    if (empty($date_expiration)) {
        return '<span class="badge bg-secondary">N/A</span>';
    }
    
    $today = new DateTime();
    $expiration = new DateTime($date_expiration);
    $diff = $today->diff($expiration);
    $jours = (int)$diff->format('%r%a'); // %r pour le signe, %a pour les jours
    
    if ($jours < 0) {
        return '<span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i>Expiré</span>';
    } elseif ($jours <= 3) {
        return '<span class="badge bg-danger"><i class="bi bi-exclamation-triangle me-1"></i>' . $jours . ' jour' . ($jours > 1 ? 's' : '') . '</span>';
    } elseif ($jours <= 15) {
        return '<span class="badge bg-warning text-dark"><i class="bi bi-exclamation-circle me-1"></i>' . $jours . ' jours</span>';
    } elseif ($jours <= 30) {
        return '<span class="badge bg-info"><i class="bi bi-info-circle me-1"></i>' . $jours . ' jours</span>';
    } else {
        return '<span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Valide</span>';
    }
}

/**
 * Badge pour la priorité
 * @param string $priorite 'basse', 'normale', 'haute', 'urgente'
 * @return string HTML du badge
 */
function badgePriorite($priorite) {
    $badges = [
        'basse' => '<span class="badge bg-info"><i class="bi bi-arrow-down me-1"></i>Basse</span>',
        'normale' => '<span class="badge bg-secondary"><i class="bi bi-dash me-1"></i>Normale</span>',
        'haute' => '<span class="badge bg-warning text-dark"><i class="bi bi-arrow-up me-1"></i>Haute</span>',
        'urgente' => '<span class="badge bg-danger"><i class="bi bi-exclamation-triangle me-1"></i>Urgente</span>'
    ];
    
    return $badges[$priorite] ?? '<span class="badge bg-secondary">Normale</span>';
}
