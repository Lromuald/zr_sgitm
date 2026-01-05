-- ============================================================
-- Script de création de la base de données ZRSGIMT
-- Système de Gestion Intégrée - Mining & Transport
-- Version: 1.0.0
-- ============================================================

-- Créer la base de données
DROP DATABASE IF EXISTS zrsgimt_db;
CREATE DATABASE zrsgimt_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE zrsgimt_db;

-- ============================================================
-- TABLE: roles
-- Gestion des rôles utilisateurs
-- ============================================================
CREATE TABLE roles (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nom VARCHAR(100) NOT NULL UNIQUE,
    permissions JSON,
    description TEXT,
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: users
-- Gestion des utilisateurs
-- ============================================================
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role_id INT NOT NULL,
    telephone VARCHAR(20),
    photo VARCHAR(255),
    langue ENUM('fr', 'en') DEFAULT 'fr',
    actif BOOLEAN DEFAULT TRUE,
    tentatives_connexion INT DEFAULT 0,
    derniere_tentative_connexion DATETIME,
    bloque_jusqua DATETIME,
    preferences_notifications JSON,
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    date_derniere_connexion DATETIME,
    ip_derniere_connexion VARCHAR(45),
    user_agent TEXT,
    INDEX idx_email (email),
    INDEX idx_role (role_id),
    INDEX idx_actif (actif),
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: fournisseurs
-- Gestion des fournisseurs
-- ============================================================
CREATE TABLE fournisseurs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nom VARCHAR(200) NOT NULL,
    contact VARCHAR(100),
    email VARCHAR(150),
    telephone VARCHAR(20),
    adresse TEXT,
    conditions_paiement TEXT,
    categorie VARCHAR(50),
    actif BOOLEAN DEFAULT TRUE,
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_nom (nom),
    INDEX idx_actif (actif)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: pieces
-- Catalogue des pièces et carburant
-- ============================================================
CREATE TABLE pieces (
    id INT PRIMARY KEY AUTO_INCREMENT,
    reference VARCHAR(100) NOT NULL UNIQUE,
    type ENUM('carburant', 'filtre', 'pneu', 'huile', 'batterie', 'autre') NOT NULL,
    description TEXT,
    photo VARCHAR(255),
    prix_unitaire_cump DECIMAL(12, 2) DEFAULT 0.00,
    stock_actuel DECIMAL(12, 2) DEFAULT 0.00,
    seuil_alerte DECIMAL(12, 2) DEFAULT 0.00,
    fournisseur_principal_id INT,
    statut ENUM('actif', 'obsolete') DEFAULT 'actif',
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    date_modification TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_reference (reference),
    INDEX idx_type (type),
    INDEX idx_statut (statut),
    INDEX idx_stock (stock_actuel),
    FOREIGN KEY (fournisseur_principal_id) REFERENCES fournisseurs(id) ON DELETE SET NULL,
    CHECK (prix_unitaire_cump >= 0),
    CHECK (stock_actuel >= 0),
    CHECK (seuil_alerte >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: engins
-- Gestion du parc d'engins
-- ============================================================
CREATE TABLE engins (
    id INT PRIMARY KEY AUTO_INCREMENT,
    photo VARCHAR(255),
    type ENUM('camion', 'excavateur', 'bulldozer', 'chargeuse', 'autre') NOT NULL,
    marque VARCHAR(100),
    modele VARCHAR(100),
    immatriculation VARCHAR(50) NOT NULL UNIQUE,
    num_serie VARCHAR(100),
    annee_fabrication YEAR,
    date_achat DATE,
    montant_achat DECIMAL(12, 2),
    kilometrage_actuel DECIMAL(10, 2) DEFAULT 0.00,
    heures_moteur DECIMAL(10, 2) DEFAULT 0.00,
    capacite_charge DECIMAL(10, 2),
    consommation_theorique DECIMAL(8, 2),
    statut ENUM('disponible', 'en_mission', 'en_maintenance', 'hors_service') DEFAULT 'disponible',
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    date_modification TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_immatriculation (immatriculation),
    INDEX idx_statut (statut),
    INDEX idx_type (type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: chauffeurs
-- Gestion des chauffeurs
-- ============================================================
CREATE TABLE chauffeurs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    photo VARCHAR(255),
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    telephone VARCHAR(20),
    email VARCHAR(150),
    categorie_permis VARCHAR(50),
    numero_permis VARCHAR(50) UNIQUE,
    date_obtention_permis DATE,
    date_expiration_permis DATE,
    statut ENUM('actif', 'inactif', 'conge') DEFAULT 'actif',
    bloque BOOLEAN DEFAULT FALSE,
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_nom (nom, prenom),
    INDEX idx_statut (statut),
    INDEX idx_expiration_permis (date_expiration_permis),
    INDEX idx_bloque (bloque)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: affectations_engin_chauffeur
-- Liaison engins-chauffeurs autorisés
-- ============================================================
CREATE TABLE affectations_engin_chauffeur (
    id INT PRIMARY KEY AUTO_INCREMENT,
    engin_id INT NOT NULL,
    chauffeur_id INT NOT NULL,
    date_debut DATE NOT NULL,
    date_fin DATE,
    actif BOOLEAN DEFAULT TRUE,
    notes TEXT,
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_engin (engin_id),
    INDEX idx_chauffeur (chauffeur_id),
    INDEX idx_actif (actif),
    FOREIGN KEY (engin_id) REFERENCES engins(id) ON DELETE CASCADE,
    FOREIGN KEY (chauffeur_id) REFERENCES chauffeurs(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: mouvements_stock
-- Historique des mouvements de stock
-- ============================================================
CREATE TABLE mouvements_stock (
    id INT PRIMARY KEY AUTO_INCREMENT,
    piece_id INT NOT NULL,
    type ENUM('entree', 'sortie') NOT NULL,
    quantite DECIMAL(12, 2) NOT NULL,
    prix_unitaire DECIMAL(12, 2),
    valeur_totale DECIMAL(12, 2),
    motif ENUM('achat', 'maintenance', 'carburant', 'perte_casse', 'inventaire', 'autre') NOT NULL,
    engin_id INT,
    maintenance_id INT,
    consommation_id INT,
    fournisseur_id INT,
    bon_commande VARCHAR(100),
    notes TEXT,
    user_id INT NOT NULL,
    date_mouvement DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_piece (piece_id),
    INDEX idx_type (type),
    INDEX idx_date (date_mouvement),
    INDEX idx_engin (engin_id),
    FOREIGN KEY (piece_id) REFERENCES pieces(id) ON DELETE RESTRICT,
    FOREIGN KEY (engin_id) REFERENCES engins(id) ON DELETE SET NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE RESTRICT,
    FOREIGN KEY (fournisseur_id) REFERENCES fournisseurs(id) ON DELETE SET NULL,
    CHECK (quantite > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: maintenances
-- Gestion des maintenances
-- ============================================================
CREATE TABLE maintenances (
    id INT PRIMARY KEY AUTO_INCREMENT,
    engin_id INT NOT NULL,
    type ENUM('preventive', 'corrective') NOT NULL,
    description TEXT NOT NULL,
    date_planifiee DATETIME NOT NULL,
    date_debut_reelle DATETIME,
    date_fin_reelle DATETIME,
    statut ENUM('planifiee', 'en_cours', 'terminee', 'annulee') DEFAULT 'planifiee',
    cout_pieces DECIMAL(12, 2) DEFAULT 0.00,
    cout_main_oeuvre DECIMAL(12, 2) DEFAULT 0.00,
    cout_total DECIMAL(12, 2) GENERATED ALWAYS AS (cout_pieces + cout_main_oeuvre) STORED,
    kilometrage_intervention DECIMAL(10, 2),
    heures_moteur_intervention DECIMAL(10, 2),
    pieces_utilisees JSON,
    technicien VARCHAR(100),
    notes TEXT,
    user_id INT NOT NULL,
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_engin (engin_id),
    INDEX idx_statut (statut),
    INDEX idx_type (type),
    INDEX idx_date_planifiee (date_planifiee),
    FOREIGN KEY (engin_id) REFERENCES engins(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: clients
-- Gestion des clients
-- ============================================================
CREATE TABLE clients (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nom_raison_sociale VARCHAR(200) NOT NULL,
    contact VARCHAR(100),
    adresse TEXT,
    telephone VARCHAR(20),
    email VARCHAR(150),
    nif_rc VARCHAR(100),
    conditions_paiement TEXT,
    categorie ENUM('professionnel', 'particulier') DEFAULT 'professionnel',
    secteur_activite VARCHAR(100),
    actif BOOLEAN DEFAULT TRUE,
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_nom (nom_raison_sociale),
    INDEX idx_categorie (categorie),
    INDEX idx_actif (actif)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: livraisons
-- Gestion des livraisons
-- ============================================================
CREATE TABLE livraisons (
    id INT PRIMARY KEY AUTO_INCREMENT,
    client_id INT NOT NULL,
    engin_id INT NOT NULL,
    chauffeur_id INT NOT NULL,
    date_prevue DATETIME NOT NULL,
    heure_depart_reelle DATETIME,
    heure_arrivee DATETIME,
    lieu_depart VARCHAR(255) NOT NULL,
    destination VARCHAR(255) NOT NULL,
    type_materiau VARCHAR(100),
    poids_volume DECIMAL(12, 2),
    bon_commande VARCHAR(100),
    tarification DECIMAL(12, 2),
    statut ENUM('planifiee', 'en_cours', 'livree', 'annulee') DEFAULT 'planifiee',
    notes TEXT,
    signature_client TEXT,
    user_id INT NOT NULL,
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    date_modification TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_client (client_id),
    INDEX idx_engin (engin_id),
    INDEX idx_chauffeur (chauffeur_id),
    INDEX idx_statut (statut),
    INDEX idx_date_prevue (date_prevue),
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE RESTRICT,
    FOREIGN KEY (engin_id) REFERENCES engins(id) ON DELETE RESTRICT,
    FOREIGN KEY (chauffeur_id) REFERENCES chauffeurs(id) ON DELETE RESTRICT,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: factures
-- Gestion de la facturation
-- ============================================================
CREATE TABLE factures (
    id INT PRIMARY KEY AUTO_INCREMENT,
    numero_facture VARCHAR(50) NOT NULL UNIQUE,
    livraison_id INT NOT NULL,
    client_id INT NOT NULL,
    date_emission DATE NOT NULL,
    montant_ht DECIMAL(12, 2) NOT NULL,
    taux_tva DECIMAL(5, 2) DEFAULT 16.00,
    montant_tva DECIMAL(12, 2) GENERATED ALWAYS AS (montant_ht * taux_tva / 100) STORED,
    montant_ttc DECIMAL(12, 2) GENERATED ALWAYS AS (montant_ht + (montant_ht * taux_tva / 100)) STORED,
    statut_paiement ENUM('impayee', 'partiellement_payee', 'payee') DEFAULT 'impayee',
    date_echeance DATE,
    date_paiement DATE,
    mode_paiement VARCHAR(50),
    montant_paye DECIMAL(12, 2) DEFAULT 0.00,
    notes TEXT,
    user_id INT NOT NULL,
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_numero (numero_facture),
    INDEX idx_client (client_id),
    INDEX idx_statut (statut_paiement),
    INDEX idx_date_emission (date_emission),
    INDEX idx_date_echeance (date_echeance),
    FOREIGN KEY (livraison_id) REFERENCES livraisons(id) ON DELETE RESTRICT,
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE RESTRICT,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE RESTRICT,
    UNIQUE KEY unique_livraison (livraison_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: documents_engins
-- Gestion des documents administratifs des engins
-- ============================================================
CREATE TABLE documents_engins (
    id INT PRIMARY KEY AUTO_INCREMENT,
    engin_id INT NOT NULL,
    type_document ENUM('carte_grise', 'assurance', 'carte_transport', 'carte_stationnement', 'visite_technique') NOT NULL,
    fichier_path VARCHAR(255) NOT NULL,
    fichier_nom_original VARCHAR(255),
    date_emission DATE,
    date_expiration DATE NOT NULL,
    numero_document VARCHAR(100),
    statut_validite ENUM('valide', 'expire', 'a_renouveler') DEFAULT 'valide',
    alerte_niveau ENUM('info', 'attention', 'urgent', 'critique') DEFAULT 'info',
    est_critique BOOLEAN DEFAULT FALSE,
    user_id INT NOT NULL,
    date_upload TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_engin (engin_id),
    INDEX idx_type (type_document),
    INDEX idx_expiration (date_expiration),
    INDEX idx_statut (statut_validite),
    INDEX idx_alerte (alerte_niveau),
    FOREIGN KEY (engin_id) REFERENCES engins(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: consommations_carburant
-- Suivi de la consommation de carburant
-- ============================================================
CREATE TABLE consommations_carburant (
    id INT PRIMARY KEY AUTO_INCREMENT,
    engin_id INT NOT NULL,
    chauffeur_id INT NOT NULL,
    piece_carburant_id INT NOT NULL,
    type_carburant VARCHAR(50),
    quantite_litres DECIMAL(10, 2) NOT NULL,
    date_plein DATETIME NOT NULL,
    kilometrage DECIMAL(10, 2),
    heures_moteur DECIMAL(10, 2),
    lieu_plein VARCHAR(255),
    montant DECIMAL(12, 2),
    bon_carburant VARCHAR(100),
    consommation_calculee DECIMAL(8, 2),
    moyenne_mobile_30j DECIMAL(8, 2),
    alerte_surconsommation BOOLEAN DEFAULT FALSE,
    notes TEXT,
    user_id INT NOT NULL,
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_engin (engin_id),
    INDEX idx_chauffeur (chauffeur_id),
    INDEX idx_date (date_plein),
    INDEX idx_alerte (alerte_surconsommation),
    FOREIGN KEY (engin_id) REFERENCES engins(id) ON DELETE CASCADE,
    FOREIGN KEY (chauffeur_id) REFERENCES chauffeurs(id) ON DELETE RESTRICT,
    FOREIGN KEY (piece_carburant_id) REFERENCES pieces(id) ON DELETE RESTRICT,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE RESTRICT,
    CHECK (quantite_litres > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: notifications
-- Système de notifications
-- ============================================================
CREATE TABLE notifications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    type ENUM('document', 'stock', 'carburant', 'permis', 'livraison', 'paiement', 'maintenance', 'system') NOT NULL,
    titre VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    lien VARCHAR(255),
    priorite ENUM('info', 'attention', 'urgent', 'critique') DEFAULT 'info',
    lu BOOLEAN DEFAULT FALSE,
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user (user_id),
    INDEX idx_type (type),
    INDEX idx_lu (lu),
    INDEX idx_date (date_creation),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: logs_audit
-- Audit trail de toutes les opérations
-- ============================================================
CREATE TABLE logs_audit (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    action ENUM('CREATE', 'UPDATE', 'DELETE', 'LOGIN', 'LOGOUT', 'ACCESS_DENIED') NOT NULL,
    table_name VARCHAR(100),
    record_id INT,
    anciennes_valeurs JSON,
    nouvelles_valeurs JSON,
    description TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    date_action TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user (user_id),
    INDEX idx_action (action),
    INDEX idx_table (table_name),
    INDEX idx_date (date_action),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: historique_connexions
-- Historique des connexions utilisateurs
-- ============================================================
CREATE TABLE historique_connexions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    date_connexion DATETIME NOT NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    navigateur VARCHAR(100),
    systeme_exploitation VARCHAR(100),
    succes BOOLEAN DEFAULT TRUE,
    INDEX idx_user (user_id),
    INDEX idx_date (date_connexion),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TRIGGERS
-- ============================================================

-- Trigger: Calcul automatique du CUMP lors d'une entrée de stock
DELIMITER //
CREATE TRIGGER trg_calculate_cump_after_insert
AFTER INSERT ON mouvements_stock
FOR EACH ROW
BEGIN
    IF NEW.type = 'entree' THEN
        DECLARE ancien_stock DECIMAL(12, 2);
        DECLARE ancien_cump DECIMAL(12, 2);
        DECLARE nouvelle_valeur DECIMAL(12, 2);
        DECLARE nouveau_stock DECIMAL(12, 2);
        DECLARE nouveau_cump DECIMAL(12, 2);
        
        -- Récupérer les valeurs actuelles
        SELECT stock_actuel, prix_unitaire_cump 
        INTO ancien_stock, ancien_cump
        FROM pieces WHERE id = NEW.piece_id;
        
        -- Calculer le nouveau CUMP
        SET nouvelle_valeur = (ancien_stock * ancien_cump) + (NEW.quantite * NEW.prix_unitaire);
        SET nouveau_stock = ancien_stock + NEW.quantite;
        
        IF nouveau_stock > 0 THEN
            SET nouveau_cump = nouvelle_valeur / nouveau_stock;
        ELSE
            SET nouveau_cump = ancien_cump;
        END IF;
        
        -- Mettre à jour la pièce
        UPDATE pieces 
        SET prix_unitaire_cump = nouveau_cump,
            stock_actuel = nouveau_stock
        WHERE id = NEW.piece_id;
    ELSEIF NEW.type = 'sortie' THEN
        -- Mise à jour du stock pour les sorties
        UPDATE pieces 
        SET stock_actuel = stock_actuel - NEW.quantite
        WHERE id = NEW.piece_id;
    END IF;
END//

-- Trigger: Bloquer le chauffeur si permis expiré
CREATE TRIGGER trg_check_permis_expiration
BEFORE UPDATE ON chauffeurs
FOR EACH ROW
BEGIN
    IF NEW.date_expiration_permis < CURDATE() THEN
        SET NEW.bloque = TRUE;
    ELSE
        SET NEW.bloque = FALSE;
    END IF;
END//

-- Trigger: Vérifier l'état des documents et mettre à jour le statut de l'engin
CREATE TRIGGER trg_check_documents_after_update
AFTER UPDATE ON documents_engins
FOR EACH ROW
BEGIN
    DECLARE nb_docs_expires INT;
    
    -- Compter les documents critiques expirés pour cet engin
    SELECT COUNT(*) INTO nb_docs_expires
    FROM documents_engins
    WHERE engin_id = NEW.engin_id
    AND est_critique = TRUE
    AND statut_validite = 'expire';
    
    -- Si des documents critiques sont expirés, mettre l'engin hors service
    IF nb_docs_expires > 0 THEN
        UPDATE engins 
        SET statut = 'hors_service'
        WHERE id = NEW.engin_id
        AND statut != 'hors_service';
    END IF;
END//

-- Trigger: Changer le statut de l'engin lors du changement de statut de maintenance
CREATE TRIGGER trg_maintenance_status_change
AFTER UPDATE ON maintenances
FOR EACH ROW
BEGIN
    IF NEW.statut = 'en_cours' AND OLD.statut != 'en_cours' THEN
        -- Passer l'engin en maintenance
        UPDATE engins SET statut = 'en_maintenance' WHERE id = NEW.engin_id;
    ELSEIF NEW.statut = 'terminee' AND OLD.statut = 'en_cours' THEN
        -- Remettre l'engin disponible si aucun document n'est expiré
        UPDATE engins 
        SET statut = 'disponible' 
        WHERE id = NEW.engin_id
        AND id NOT IN (
            SELECT engin_id FROM documents_engins 
            WHERE est_critique = TRUE AND statut_validite = 'expire'
        );
    END IF;
END//

-- Trigger: Générer automatiquement le numéro de facture
CREATE TRIGGER trg_generate_facture_numero
BEFORE INSERT ON factures
FOR EACH ROW
BEGIN
    DECLARE next_num INT;
    DECLARE annee_courante YEAR;
    
    SET annee_courante = YEAR(NEW.date_emission);
    
    -- Récupérer le dernier numéro de l'année
    SELECT COALESCE(MAX(CAST(SUBSTRING(numero_facture, 11) AS UNSIGNED)), 0) + 1
    INTO next_num
    FROM factures
    WHERE YEAR(date_emission) = annee_courante;
    
    -- Générer le numéro
    SET NEW.numero_facture = CONCAT('FACT-', annee_courante, '-', LPAD(next_num, 4, '0'));
END//

DELIMITER ;

-- ============================================================
-- VUES UTILES
-- ============================================================

-- Vue: Engins avec leurs documents expirés
CREATE VIEW v_engins_documents_expires AS
SELECT 
    e.id AS engin_id,
    e.immatriculation,
    e.type,
    e.marque,
    e.modele,
    e.statut,
    d.type_document,
    d.date_expiration,
    d.statut_validite,
    d.alerte_niveau,
    DATEDIFF(d.date_expiration, CURDATE()) AS jours_restants
FROM engins e
LEFT JOIN documents_engins d ON e.id = d.engin_id
WHERE d.statut_validite IN ('expire', 'a_renouveler');

-- Vue: Stock critique (en dessous du seuil d'alerte)
CREATE VIEW v_stock_critique AS
SELECT 
    p.id,
    p.reference,
    p.type,
    p.description,
    p.stock_actuel,
    p.seuil_alerte,
    p.prix_unitaire_cump,
    f.nom AS fournisseur,
    (p.seuil_alerte - p.stock_actuel) AS quantite_a_commander
FROM pieces p
LEFT JOIN fournisseurs f ON p.fournisseur_principal_id = f.id
WHERE p.stock_actuel < p.seuil_alerte
AND p.statut = 'actif';

-- Vue: Dashboard livraisons du jour
CREATE VIEW v_livraisons_jour AS
SELECT 
    l.id,
    l.statut,
    c.nom_raison_sociale AS client,
    e.immatriculation AS engin,
    e.type AS type_engin,
    ch.nom AS chauffeur_nom,
    ch.prenom AS chauffeur_prenom,
    l.lieu_depart,
    l.destination,
    l.date_prevue,
    l.type_materiau,
    l.poids_volume
FROM livraisons l
JOIN clients c ON l.client_id = c.id
JOIN engins e ON l.engin_id = e.id
JOIN chauffeurs ch ON l.chauffeur_id = ch.id
WHERE DATE(l.date_prevue) = CURDATE()
AND l.statut IN ('planifiee', 'en_cours');

-- Vue: Maintenances à venir (7 prochains jours)
CREATE VIEW v_maintenances_a_venir AS
SELECT 
    m.id,
    m.type,
    m.description,
    m.date_planifiee,
    m.statut,
    e.immatriculation,
    e.type AS type_engin,
    e.marque,
    e.modele,
    DATEDIFF(m.date_planifiee, CURDATE()) AS jours_avant
FROM maintenances m
JOIN engins e ON m.engin_id = e.id
WHERE m.statut = 'planifiee'
AND m.date_planifiee BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
ORDER BY m.date_planifiee;

-- Vue: Statistiques par engin
CREATE VIEW v_stats_engins AS
SELECT 
    e.id,
    e.immatriculation,
    e.type,
    e.marque,
    e.modele,
    e.statut,
    COUNT(DISTINCT l.id) AS nb_livraisons,
    COUNT(DISTINCT m.id) AS nb_maintenances,
    COALESCE(SUM(c.quantite_litres), 0) AS total_carburant_litres,
    COALESCE(AVG(c.consommation_calculee), 0) AS consommation_moyenne
FROM engins e
LEFT JOIN livraisons l ON e.id = l.engin_id AND l.statut = 'livree'
LEFT JOIN maintenances m ON e.id = m.engin_id AND m.statut = 'terminee'
LEFT JOIN consommations_carburant c ON e.id = c.engin_id
GROUP BY e.id;

-- ============================================================
-- DONNÉES INITIALES: RÔLES
-- ============================================================
INSERT INTO roles (nom, permissions, description) VALUES
('Administrateur', 
 '{"all": true, "users": ["create", "read", "update", "delete"], "stock": ["create", "read", "update", "delete"], "engins": ["create", "read", "update", "delete"], "livraisons": ["create", "read", "update", "delete", "validate"], "factures": ["create", "read", "update", "delete"], "reports": ["read", "export"]}',
 'Accès complet à toutes les fonctionnalités du système'),
 
('Gestionnaire Stock', 
 '{"stock": ["create", "read", "update", "delete"], "fournisseurs": ["create", "read", "update"], "pieces": ["create", "read", "update"], "mouvements": ["create", "read"], "reports": ["read", "export"]}',
 'Gestion complète des stocks, pièces et fournisseurs'),
 
('Maintenance', 
 '{"engins": ["read", "update"], "maintenances": ["create", "read", "update"], "stock": ["read"], "documents": ["create", "read", "update"], "reports": ["read"]}',
 'Gestion des maintenances et documents techniques'),
 
('Commercial', 
 '{"clients": ["create", "read", "update"], "livraisons": ["create", "read", "update", "validate"], "factures": ["create", "read"], "engins": ["read"], "chauffeurs": ["read"], "reports": ["read", "export"]}',
 'Gestion des clients, livraisons et facturation'),
 
('Lecture seule', 
 '{"all": ["read"], "reports": ["read"]}',
 'Consultation uniquement, aucune modification');

-- ============================================================
-- DONNÉES INITIALES: UTILISATEURS
-- ============================================================
-- Mot de passe: Admin@2025 (hash bcrypt)
INSERT INTO users (nom, prenom, email, password, role_id, telephone, langue, actif) VALUES
('Administrateur', 'Système', 'admin@zrsgimt.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, '+243999999999', 'fr', TRUE),
('Mukendi', 'Jean', 'stock@zrsgimt.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 2, '+243888888888', 'fr', TRUE),
('Kabamba', 'Paul', 'maintenance@zrsgimt.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 3, '+243777777777', 'fr', TRUE),
('Ilunga', 'Marie', 'commercial@zrsgimt.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 4, '+243666666666', 'fr', TRUE),
('Tshombe', 'Pierre', 'lecture@zrsgimt.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 5, '+243555555555', 'fr', TRUE);

-- (Script continue avec les données de démonstration dans la partie 2...)
