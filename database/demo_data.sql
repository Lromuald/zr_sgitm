-- ============================================================
-- Données de démonstration pour ZRSGIMT
-- À exécuter après schema.sql
-- ============================================================

USE zrsgimt_db;

-- ============================================================
-- FOURNISSEURS
-- ============================================================
INSERT INTO fournisseurs (nom, contact, email, telephone, adresse, conditions_paiement, categorie) VALUES
('TOTAL Lubumbashi', 'Martin Kasongo', 'contact@total-lshi.cd', '+243997001001', 'Avenue Likasi, Lubumbashi', '30 jours net', 'Carburant'),
('Pièces Auto Congo', 'Sophie Mbuyi', 'info@piecesauto.cd', '+243997002002', 'Boulevard Lumumba, Kinshasa', '60 jours fin de mois', 'Pièces détachées'),
('Michelin DRC', 'Jacques Mutombo', 'ventes@michelin-drc.com', '+243997003003', 'Zone industrielle, Lubumbashi', '45 jours', 'Pneumatiques'),
('Shell Lubrifiants', 'André Kalala', 'shell@lubumbashi.cd', '+243997004004', 'Avenue Kasenga, Lubumbashi', '30 jours', 'Huiles et lubrifiants'),
('Batterie Plus', 'Marie Kabongo', 'contact@batterieplus.cd', '+243997005005', 'Marché Central, Likasi', 'Comptant', 'Batteries');

-- ============================================================
-- PIÈCES ET CARBURANT
-- ============================================================
INSERT INTO pieces (reference, type, description, prix_unitaire_cump, stock_actuel, seuil_alerte, fournisseur_principal_id, statut) VALUES
-- Carburants
('DIESEL-001', 'carburant', 'Diesel B7 haute qualité', 1.85, 15000.00, 5000.00, 1, 'actif'),
('ESSENCE-001', 'carburant', 'Essence Sans Plomb 95', 2.15, 8000.00, 3000.00, 1, 'actif'),

-- Filtres
('FILTRE-AIR-001', 'filtre', 'Filtre à air universel camion', 45.50, 25.00, 10.00, 2, 'actif'),
('FILTRE-HUILE-001', 'filtre', 'Filtre à huile moteur diesel', 28.00, 40.00, 15.00, 2, 'actif'),
('FILTRE-CARB-001', 'filtre', 'Filtre à carburant diesel', 35.00, 30.00, 12.00, 2, 'actif'),
('FILTRE-AIR-002', 'filtre', 'Filtre à air Caterpillar', 85.00, 15.00, 5.00, 2, 'actif'),

-- Pneus
('PNEU-CAMION-001', 'pneu', 'Pneu 11R22.5 Michelin XZE2+', 385.00, 20.00, 8.00, 3, 'actif'),
('PNEU-CAMION-002', 'pneu', 'Pneu 295/80R22.5 Bridgestone', 350.00, 18.00, 8.00, 3, 'actif'),
('PNEU-ENGIN-001', 'pneu', 'Pneu excavateur 20.5R25', 1250.00, 8.00, 4.00, 3, 'actif'),
('PNEU-BULL-001', 'pneu', 'Pneu bulldozer 23.5R25', 1450.00, 6.00, 3.00, 3, 'actif'),

-- Huiles
('HUILE-MOTEUR-001', 'huile', 'Huile moteur 15W40 diesel (20L)', 125.00, 45.00, 20.00, 4, 'actif'),
('HUILE-MOTEUR-002', 'huile', 'Huile moteur 10W40 semi-synthèse (20L)', 145.00, 35.00, 15.00, 4, 'actif'),
('HUILE-HYDR-001', 'huile', 'Huile hydraulique ISO 68 (208L)', 450.00, 12.00, 5.00, 4, 'actif'),
('HUILE-TRANS-001', 'huile', 'Huile transmission SAE 80W90 (20L)', 135.00, 25.00, 10.00, 4, 'actif'),

-- Batteries
('BATT-CAMION-001', 'batterie', 'Batterie 12V 180Ah camion', 185.00, 12.00, 5.00, 5, 'actif'),
('BATT-CAMION-002', 'batterie', 'Batterie 12V 225Ah poids lourd', 245.00, 10.00, 4.00, 5, 'actif'),
('BATT-ENGIN-001', 'batterie', 'Batterie 24V 200Ah engin', 425.00, 6.00, 3.00, 5, 'actif'),

-- Autres pièces
('PLAQ-FREIN-001', 'autre', 'Plaquettes de frein avant camion', 95.00, 20.00, 8.00, 2, 'actif'),
('DISQ-FREIN-001', 'autre', 'Disque de frein avant (paire)', 175.00, 12.00, 6.00, 2, 'actif'),
('COURR-001', 'autre', 'Courroie alternateur universelle', 35.00, 25.00, 10.00, 2, 'actif'),
('BALAI-ESS-001', 'autre', 'Balais essuie-glace (paire)', 18.00, 30.00, 12.00, 2, 'actif');

-- ============================================================
-- ENGINS
-- ============================================================
INSERT INTO engins (type, marque, modele, immatriculation, num_serie, annee_fabrication, date_achat, montant_achat, 
                    kilometrage_actuel, heures_moteur, capacite_charge, consommation_theorique, statut) VALUES
-- Camions
('camion', 'Mercedes-Benz', 'Actros 2546', 'CD-1234-LU', 'MB-ACT-2546-2020-001', 2020, '2020-03-15', 95000.00, 
 125000.00, 8500.00, 25.00, 28.5, 'disponible'),
('camion', 'Volvo', 'FH16 750', 'CD-5678-LU', 'VLV-FH16-750-2019-002', 2019, '2019-11-20', 110000.00, 
 158000.00, 10200.00, 30.00, 32.0, 'disponible'),
('camion', 'Scania', 'R500', 'CD-9012-LU', 'SCN-R500-2021-003', 2021, '2021-06-10', 105000.00, 
 78000.00, 5200.00, 28.00, 30.0, 'disponible'),
('camion', 'MAN', 'TGX 26.440', 'CD-3456-LU', 'MAN-TGX-440-2018-004', 2018, '2018-09-05', 88000.00, 
 195000.00, 12800.00, 26.00, 29.5, 'disponible'),
('camion', 'Iveco', 'Stralis 460', 'CD-7890-LU', 'IVC-STR-460-2020-005', 2020, '2020-12-01', 92000.00, 
 110000.00, 7300.00, 24.00, 27.0, 'disponible'),
 
-- Excavateurs
('excavateur', 'Caterpillar', '336F', 'CD-EXC-001', 'CAT-336F-2019-006', 2019, '2019-04-20', 185000.00, 
 0.00, 6500.00, 0.00, 22.0, 'disponible'),
('excavateur', 'Komatsu', 'PC350', 'CD-EXC-002', 'KOM-PC350-2020-007', 2020, '2020-08-15', 175000.00, 
 0.00, 4800.00, 0.00, 20.5, 'disponible'),
('excavateur', 'Hitachi', 'ZX350', 'CD-EXC-003', 'HIT-ZX350-2018-008', 2018, '2018-11-10', 165000.00, 
 0.00, 8200.00, 0.00, 21.0, 'disponible'),

-- Bulldozers
('bulldozer', 'Caterpillar', 'D6T', 'CD-BUL-001', 'CAT-D6T-2020-009', 2020, '2020-05-10', 220000.00, 
 0.00, 3500.00, 0.00, 18.5, 'disponible'),
('bulldozer', 'Komatsu', 'D65PX', 'CD-BUL-002', 'KOM-D65-2019-010', 2019, '2019-07-22', 195000.00, 
 0.00, 5100.00, 0.00, 19.0, 'disponible'),

-- Chargeuses
('chargeuse', 'Caterpillar', '966H', 'CD-CHA-001', 'CAT-966H-2021-011', 2021, '2021-02-14', 165000.00, 
 0.00, 2200.00, 0.00, 16.5, 'disponible'),
('chargeuse', 'Volvo', 'L120H', 'CD-CHA-002', 'VLV-L120-2020-012', 2020, '2020-10-05', 155000.00, 
 0.00, 3800.00, 0.00, 17.0, 'disponible');

-- ============================================================
-- CHAUFFEURS
-- ============================================================
INSERT INTO chauffeurs (nom, prenom, telephone, email, categorie_permis, numero_permis, 
                        date_obtention_permis, date_expiration_permis, statut) VALUES
('Mwamba', 'Joseph', '+243998111111', 'j.mwamba@zrsgimt.cd', 'C+E', 'PERM-2018-001', '2018-03-10', '2025-03-10', 'actif'),
('Kabila', 'Patrick', '+243998222222', 'p.kabila@zrsgimt.cd', 'C+E', 'PERM-2017-002', '2017-05-15', '2025-05-15', 'actif'),
('Tshisekedi', 'Emmanuel', '+243998333333', 'e.tshisekedi@zrsgimt.cd', 'C+E', 'PERM-2019-003', '2019-07-20', '2026-07-20', 'actif'),
('Mulumba', 'Jean-Pierre', '+243998444444', 'jp.mulumba@zrsgimt.cd', 'C+E', 'PERM-2018-004', '2018-09-12', '2025-09-12', 'actif'),
('Kasongo', 'Daniel', '+243998555555', 'd.kasongo@zrsgimt.cd', 'C+E', 'PERM-2020-005', '2020-01-18', '2027-01-18', 'actif'),
('Ilunga', 'François', '+243998666666', 'f.ilunga@zrsgimt.cd', 'C+E', 'PERM-2019-006', '2019-11-25', '2026-11-25', 'actif'),
('Mutombo', 'André', '+243998777777', 'a.mutombo@zrsgimt.cd', 'C', 'PERM-2018-007', '2018-04-08', '2025-04-08', 'actif'),
('Kalala', 'Robert', '+243998888888', 'r.kalala@zrsgimt.cd', 'C+E', 'PERM-2021-008', '2021-02-14', '2028-02-14', 'actif'),
('Mukendi', 'Pierre', '+243998999999', 'p.mukendi@zrsgimt.cd', 'C+E', 'PERM-2017-009', '2017-06-30', '2024-06-30', 'actif'),
('Ngoy', 'Michel', '+243998101010', 'm.ngoy@zrsgimt.cd', 'C+E', 'PERM-2020-010', '2020-08-22', '2027-08-22', 'actif'),
('Kabamba', 'Serge', '+243998111010', 's.kabamba@zrsgimt.cd', 'C', 'PERM-2019-011', '2019-12-05', '2026-12-05', 'actif'),
('Tshombe', 'Albert', '+243998121212', 'a.tshombe@zrsgimt.cd', 'C+E', 'PERM-2018-012', '2018-10-17', '2025-10-17', 'actif'),
('Mbuyi', 'Claude', '+243998131313', 'c.mbuyi@zrsgimt.cd', 'C+E', 'PERM-2021-013', '2021-03-28', '2028-03-28', 'actif'),
('Luamba', 'Gérard', '+243998141414', 'g.luamba@zrsgimt.cd', 'C', 'PERM-2019-014', '2019-05-10', '2026-05-10', 'actif'),
('Mpiana', 'Héritier', '+243998151515', 'h.mpiana@zrsgimt.cd', 'C+E', 'PERM-2020-015', '2020-11-15', '2027-11-15', 'actif');

-- ============================================================
-- AFFECTATIONS ENGIN-CHAUFFEUR
-- ============================================================
-- Affecter plusieurs chauffeurs à chaque engin
INSERT INTO affectations_engin_chauffeur (engin_id, chauffeur_id, date_debut, actif) VALUES
-- Camions
(1, 1, '2024-01-01', TRUE),
(1, 2, '2024-01-01', TRUE),
(2, 3, '2024-01-01', TRUE),
(2, 4, '2024-01-01', TRUE),
(3, 5, '2024-01-01', TRUE),
(3, 6, '2024-01-01', TRUE),
(4, 7, '2024-01-01', TRUE),
(4, 8, '2024-01-01', TRUE),
(5, 9, '2024-01-01', TRUE),
(5, 10, '2024-01-01', TRUE),
-- Excavateurs
(6, 11, '2024-01-01', TRUE),
(7, 12, '2024-01-01', TRUE),
(8, 13, '2024-01-01', TRUE),
-- Bulldozers
(9, 14, '2024-01-01', TRUE),
(10, 15, '2024-01-01', TRUE),
-- Chargeuses
(11, 1, '2024-01-01', TRUE),
(12, 2, '2024-01-01', TRUE);

-- ============================================================
-- CLIENTS
-- ============================================================
INSERT INTO clients (nom_raison_sociale, contact, adresse, telephone, email, nif_rc, 
                     conditions_paiement, categorie, secteur_activite) VALUES
('Mines de Kamoto SARL', 'Jean Kaluba', 'Avenue Industrielle, Kolwezi', '+243997201001', 'contact@kamoto.cd', 
 'A1234567N', '60 jours fin de mois', 'professionnel', 'Mines'),
('Gécamines', 'Marie Kasongo', 'Boulevard du 30 Juin, Lubumbashi', '+243997202002', 'commercial@gecamines.cd', 
 'B2345678O', '90 jours', 'professionnel', 'Mines'),
('TP Congo Construction', 'Paul Mbuyi', 'Quartier Commercial, Likasi', '+243997203003', 'info@tpcongo.cd', 
 'C3456789P', '45 jours', 'professionnel', 'Construction'),
('SICOMINES', 'André Mutombo', 'Zone Industrielle, Kolwezi', '+243997204004', 'achats@sicomines.cd', 
 'D4567890Q', '60 jours', 'professionnel', 'Mines'),
('Entreprise Minière du Katanga', 'Sophie Ilunga', 'Avenue Lumumba, Lubumbashi', '+243997205005', 'emk@katanga.cd', 
 'E5678901R', '30 jours', 'professionnel', 'Mines'),
('Carrières du Kasaï', 'Robert Kabila', 'Route de Mbuji-Mayi, Mbuji-Mayi', '+243997206006', 'carrieres@kasai.cd', 
 'F6789012S', '30 jours', 'professionnel', 'Carrières'),
('BTP Lualaba', 'François Tshombe', 'Centre-ville, Kolwezi', '+243997207007', 'contact@btplualaba.cd', 
 'G7890123T', '45 jours', 'professionnel', 'BTP'),
('Société Générale de Transport', 'Claude Ngoy', 'Gare Centrale, Lubumbashi', '+243997208008', 'sgt@transport.cd', 
 'H8901234U', '30 jours', 'professionnel', 'Transport'),
('Mines Artisanales du Congo', 'Daniel Mukendi', 'Marché Central, Kolwezi', '+243997209009', 'mac@artisanal.cd', 
 'I9012345V', 'Comptant', 'professionnel', 'Mines'),
('Construction Moderne SPRL', 'Pierre Luamba', 'Boulevard Kasenga, Likasi', '+243997210010', 'moderne@construction.cd', 
 'J0123456W', '60 jours', 'professionnel', 'Construction');

-- ============================================================
-- MOUVEMENTS DE STOCK (Entrées initiales)
-- ============================================================
INSERT INTO mouvements_stock (piece_id, type, quantite, prix_unitaire, valeur_totale, motif, 
                               fournisseur_id, bon_commande, user_id, date_mouvement) VALUES
-- Carburant (les stocks sont déjà entrés via le trigger)
(1, 'entree', 15000.00, 1.85, 27750.00, 'achat', 1, 'BC-2024-001', 2, '2024-01-15 08:00:00'),
(2, 'entree', 8000.00, 2.15, 17200.00, 'achat', 1, 'BC-2024-002', 2, '2024-01-15 08:30:00');

-- Les autres pièces sont déjà entrées avec leurs quantités initiales

-- ============================================================
-- MAINTENANCES
-- ============================================================
INSERT INTO maintenances (engin_id, type, description, date_planifiee, statut, user_id) VALUES
(1, 'preventive', 'Révision 120 000 km - Vidange complète + filtres', '2025-02-15 08:00:00', 'planifiee', 3),
(2, 'preventive', 'Contrôle freinage et remplacement plaquettes', '2025-02-20 09:00:00', 'planifiee', 3),
(6, 'preventive', 'Entretien 6500h - Vidange hydraulique', '2025-02-10 07:00:00', 'planifiee', 3),
(3, 'corrective', 'Problème de direction assistée', '2025-01-25 10:00:00', 'planifiee', 3);

-- Maintenances terminées (historique)
INSERT INTO maintenances (engin_id, type, description, date_planifiee, date_debut_reelle, date_fin_reelle, 
                          statut, cout_pieces, cout_main_oeuvre, kilometrage_intervention, user_id, date_creation) VALUES
(1, 'preventive', 'Vidange moteur + filtres', '2024-11-10 08:00:00', '2024-11-10 08:15:00', '2024-11-10 11:30:00', 
 'terminee', 198.00, 75.00, 105000.00, 3, '2024-11-05 10:00:00'),
(4, 'corrective', 'Remplacement batterie défectueuse', '2024-12-05 09:00:00', '2024-12-05 09:10:00', '2024-12-05 10:45:00', 
 'terminee', 185.00, 50.00, 190000.00, 3, '2024-12-04 14:00:00');

-- ============================================================
-- LIVRAISONS
-- ============================================================
INSERT INTO livraisons (client_id, engin_id, chauffeur_id, date_prevue, lieu_depart, destination, 
                        type_materiau, poids_volume, bon_commande, tarification, statut, user_id) VALUES
-- Livraisons terminées (historique récent)
(1, 1, 1, '2025-01-02 06:00:00', 'Entrepôt Central Lubumbashi', 'Mine Kamoto - Kolwezi', 
 'Matériel minier', 24.50, 'BC-KMT-2025-001', 850.00, 'livree', 4),
(2, 2, 3, '2025-01-03 07:00:00', 'Entrepôt Central Lubumbashi', 'Site Gécamines - Likasi', 
 'Équipements lourds', 28.00, 'BC-GCM-2025-002', 920.00, 'livree', 4),
(3, 3, 5, '2025-01-04 05:30:00', 'Entrepôt Central Lubumbashi', 'Chantier TP Congo - Lubumbashi', 
 'Matériaux de construction', 25.00, 'BC-TPC-2025-003', 780.00, 'livree', 4),
(4, 4, 7, '2025-01-05 06:00:00', 'Dépôt Kolwezi', 'Mine SICOMINES - Kolwezi', 
 'Pièces détachées', 22.00, 'BC-SCM-2025-004', 650.00, 'livree', 4),

-- Livraisons du jour (à planifier)
(5, 1, 2, '2025-01-05 07:00:00', 'Entrepôt Central Lubumbashi', 'EMK - Lubumbashi', 
 'Consommables miniers', 23.00, 'BC-EMK-2025-005', 720.00, 'planifiee', 4),
(6, 5, 9, '2025-01-05 08:00:00', 'Entrepôt Central Lubumbashi', 'Carrières Kasaï - Route Mbuji-Mayi', 
 'Carburant et lubrifiants', 20.00, 'BC-CKS-2025-006', 880.00, 'planifiee', 4),
(7, 3, 6, '2025-01-05 09:00:00', 'Dépôt Likasi', 'Chantier BTP Lualaba - Kolwezi', 
 'Ciment et ferraille', 27.00, 'BC-BTP-2025-007', 950.00, 'planifiee', 4),

-- Livraisons à venir
(8, 2, 4, '2025-01-06 06:30:00', 'Entrepôt Central Lubumbashi', 'SGT - Gare Centrale', 
 'Pièces mécaniques', 18.00, 'BC-SGT-2025-008', 580.00, 'planifiee', 4),
(9, 4, 8, '2025-01-07 07:00:00', 'Dépôt Kolwezi', 'MAC - Kolwezi Centre', 
 'Outillage minier', 15.00, 'BC-MAC-2025-009', 520.00, 'planifiee', 4),
(10, 5, 10, '2025-01-08 05:30:00', 'Entrepôt Central Lubumbashi', 'Construction Moderne - Likasi', 
 'Matériaux divers', 26.00, 'BC-CMD-2025-010', 820.00, 'planifiee', 4);

-- Mettre à jour quelques livraisons comme en cours
UPDATE livraisons SET statut = 'en_cours', heure_depart_reelle = '2025-01-05 07:15:00' 
WHERE id IN (5, 6);

-- ============================================================
-- FACTURES (pour les livraisons terminées)
-- ============================================================
INSERT INTO factures (livraison_id, client_id, date_emission, montant_ht, taux_tva, 
                      date_echeance, statut_paiement, user_id) VALUES
(1, 1, '2025-01-02', 850.00, 16.00, '2025-03-02', 'impayee', 4),
(2, 2, '2025-01-03', 920.00, 16.00, '2025-04-03', 'impayee', 4),
(3, 3, '2025-01-04', 780.00, 16.00, '2025-02-18', 'partiellement_payee', 4),
(4, 4, '2025-01-05', 650.00, 16.00, '2025-03-05', 'impayee', 4);

-- Enregistrer un paiement partiel
UPDATE factures SET montant_paye = 400.00 WHERE id = 3;

-- ============================================================
-- CONSOMMATIONS CARBURANT (Historique)
-- ============================================================
INSERT INTO consommations_carburant (engin_id, chauffeur_id, piece_carburant_id, type_carburant, 
                                     quantite_litres, date_plein, kilometrage, lieu_plein, user_id) VALUES
-- Camion 1
(1, 1, 1, 'Diesel', 280.00, '2024-12-01 08:00:00', 120000.00, 'Station TOTAL Lubumbashi', 2),
(1, 2, 1, 'Diesel', 295.00, '2024-12-15 09:00:00', 121500.00, 'Station TOTAL Lubumbashi', 2),
(1, 1, 1, 'Diesel', 285.00, '2024-12-28 07:30:00', 123000.00, 'Station TOTAL Lubumbashi', 2),
(1, 2, 1, 'Diesel', 290.00, '2025-01-04 08:15:00', 124500.00, 'Station TOTAL Lubumbashi', 2),

-- Camion 2
(2, 3, 1, 'Diesel', 320.00, '2024-12-02 07:00:00', 155000.00, 'Station Shell Likasi', 2),
(2, 4, 1, 'Diesel', 315.00, '2024-12-16 08:30:00', 156800.00, 'Station Shell Likasi', 2),
(2, 3, 1, 'Diesel', 325.00, '2024-12-30 09:00:00', 158500.00, 'Station Shell Likasi', 2),

-- Excavateur 6
(6, 11, 1, 'Diesel', 180.00, '2024-12-05 06:00:00', 0.00, 'Dépôt sur site', 2),
(6, 11, 1, 'Diesel', 175.00, '2024-12-20 06:30:00', 0.00, 'Dépôt sur site', 2),
(6, 11, 1, 'Diesel', 185.00, '2025-01-03 07:00:00', 0.00, 'Dépôt sur site', 2);

-- ============================================================
-- NOTIFICATIONS (Exemples)
-- ============================================================
INSERT INTO notifications (user_id, type, titre, message, priorite, lu) VALUES
(1, 'document', 'Documents à renouveler', '3 documents arrivent à expiration dans les 30 prochains jours', 'attention', FALSE),
(2, 'stock', 'Stock bas', 'Le stock de Filtre à air universel est en dessous du seuil d\'alerte', 'attention', FALSE),
(3, 'maintenance', 'Maintenance planifiée', 'Une maintenance préventive est prévue pour le 15/02/2025', 'info', FALSE),
(4, 'livraison', 'Livraison en attente', '2 livraisons nécessitent une validation', 'attention', FALSE),
(1, 'system', 'Bienvenue', 'Bienvenue sur le système ZRSGIMT', 'info', TRUE);

-- ============================================================
-- LOGS AUDIT (Exemples)
-- ============================================================
INSERT INTO logs_audit (user_id, action, table_name, record_id, description, ip_address) VALUES
(1, 'LOGIN', NULL, NULL, 'Connexion réussie', '192.168.1.100'),
(2, 'CREATE', 'mouvements_stock', 1, 'Entrée de stock: Diesel', '192.168.1.101'),
(3, 'UPDATE', 'maintenances', 5, 'Maintenance terminée', '192.168.1.102'),
(4, 'CREATE', 'factures', 1, 'Création facture FACT-2025-0001', '192.168.1.103'),
(1, 'UPDATE', 'engins', 1, 'Modification statut engin', '192.168.1.100');

-- ============================================================
-- FIN DU SCRIPT DE DONNÉES DE DÉMONSTRATION
-- ============================================================
