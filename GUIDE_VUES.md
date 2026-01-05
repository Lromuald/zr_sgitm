# Guide de Développement Restant - Vues Frontend

## 📌 État Actuel
✅ **Backend complet (100%)** : Tous les contrôleurs, modèles, bibliothèques créés
⏳ **Frontend (20%)** : Structure créée, CSS/JS prêts, vues à créer

## 🎯 Objectif
Créer les 29 vues PHP manquantes pour atteindre 100% de complétion

---

## 📚 Structure des Vues à Créer

### Template de Base (À réutiliser partout)

Chaque vue doit inclure :

```php
<?php require_once APP_PATH . '/Views/layouts/main.php'; ?>

<!-- Affichage messages flash session -->
<?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <?= htmlspecialchars($_SESSION['success']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['success']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <?= $_SESSION['error'] ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<div class="container-fluid mt-4">
    <div class="card">
        <div class="card-header">
            <h4 class="mb-0">
                <i class="bi bi-icon-name"></i> Titre Page
            </h4>
        </div>
        <div class="card-body">
            <!-- Contenu -->
        </div>
    </div>
</div>

<!-- Scripts JS spécifiques -->
<script>
// Initialisation DataTables
$(document).ready(function() {
    $('#myTable').DataTable(dataTablesConfigFR);
});
</script>
```

---

## 🗂️ Vues par Module

### 1. Maintenances (4 vues)

#### **index.php** - Liste maintenances
```php
// Variables disponibles : $maintenances, $engins, $filters
// Afficher tableau avec colonnes :
// - Engin (immatriculation + type)
// - Type (badge preventive/corrective)
// - Date planifiée
// - Statut (badge avec couleur)
// - Coût total
// - Actions (Voir/Modifier/Supprimer)
```

#### **create.php** - Formulaire création
```php
// Variables disponibles : $engins, $csrf_token
// Champs formulaire :
// - Dropdown engins (required)
// - Radio type (preventive/corrective)
// - Date planifiée (date input, required)
// - Description (textarea)
// - Kilométrage intervention (number, optional)
// - Coût main d'oeuvre estimé (number)
// - Hidden: csrf_token
// Action : POST vers /maintenances/store
```

#### **edit.php** - Modification + gestion pièces
```php
// Variables : $maintenance, $engin, $pieces, $pieces_utilisees, $csrf_token
// Section 1 : Infos maintenance (form)
// Section 2 : Liste pièces utilisées (table)
//   - Colonnes : Référence, Désignation, Quantité, Prix unitaire, Total
//   - Bouton supprimer chaque ligne
// Section 3 : Ajouter pièce (form inline)
//   - Dropdown pièces, Input quantité, Bouton ajouter
// Section 4 : Changement statut
//   - Si planifiee : Bouton "Démarrer"
//   - Si en_cours : Bouton "Terminer"
//   - Toujours : Bouton "Annuler"
// Section 5 : Coûts
//   - Affichage coût pièces (readonly, calculé)
//   - Input coût main d'oeuvre
//   - Affichage coût total (readonly, calculé)
```

#### **show.php** - Vue détaillée
```php
// Variables : $maintenance, $pieces_utilisees
// Design rapport professionnel :
// - En-tête : Type, Engin, Dates
// - Section pièces : Tableau avec totaux
// - Section coûts : Détail HT + Total
// - Timeline changements statut (si historique disponible)
// - Bouton "Télécharger PDF" (optionnel)
```

---

### 2. Livraisons (4 vues)

#### **index.php** - Liste livraisons
```php
// Variables : $livraisons, $compteurs, $clients, $engins, $chauffeurs, $filters
// Badges compteurs par statut en haut
// Filtres : Statut, Client, Engin, Chauffeur, Période
// Tableau colonnes :
// - Client
// - Engin (immatriculation)
// - Chauffeur (nom complet)
// - Date prévue
// - Destination
// - Statut (badge coloré)
// - Actions
```

#### **create.php** - Formulaire création
```php
// Variables : $engins, $chauffeurs, $clients, $csrf_token
// IMPORTANT : Afficher alertes si listes vides
// - Si pas d'engins disponibles : Alert warning
// - Si pas de chauffeurs valides : Alert warning
// Champs :
// - Dropdown client (required) + Bouton "Nouveau client rapide"
// - Dropdown engin (required, seulement disponibles)
// - Dropdown chauffeur (required, seulement permis valides)
// - DateTime date prévue
// - Text lieu départ, destination
// - Text type matériau
// - Number poids/volume
// - Text BC client
// - Number tarification prévisionnelle
// - Textarea notes
```

#### **edit.php** - Modification selon statut
```php
// Variables : $livraison, $clients, $engins, $chauffeurs, $facture, $csrf_token
// Afficher workflow visuel (étapes avec couleurs)
// Form avec champs selon statut :
// - Si planifiee : tous champs modifiables + Bouton "Démarrer"
// - Si en_cours : seulement notes + Bouton "Marquer livrée"
// - Si livree : readonly + lien facture si existe + Bouton "Générer facture"
// - Si annulee : readonly
```

#### **show.php** - Vue détaillée
```php
// Variables : $livraison, $facture, $duree_reelle
// Design fiche complète :
// - Bloc client (nom, contact)
// - Bloc engin (immatriculation, type)
// - Bloc chauffeur (nom, téléphone)
// - Bloc trajet (départ → destination)
// - Bloc timing (dates prévue/réelle, durée)
// - Bloc matériau (type, poids)
// - Timeline statuts avec dates/heures
// - Lien facture si générée
```

---

### 3. Factures (3 vues)

#### **index.php** - Liste factures
```php
// Variables : $factures, $totaux, $clients, $filters
// Badges totaux par statut en haut (avec montants)
// Filtres : Statut paiement, Client, Période
// Tableau :
// - Numéro facture
// - Client
// - Date émission
// - Date échéance
// - Montant TTC
// - Statut paiement (badge)
// - Badge rouge si en retard (class facture-retard)
// - Actions (Voir/Télécharger PDF/Marquer payée)
```

#### **create.php** - Génération facture
```php
// Variables : $livraisons, $csrf_token
// Info : Une facture par livraison
// Form :
// - Dropdown livraison (seulement livrées sans facture)
//   → onChange : charger infos livraison en AJAX (optionnel)
// - Radio mode calcul (tonnage/forfait/distance)
// - Input montant HT (required)
// - Dropdown taux TVA (0%, 18%, autre)
// - Input montant TVA (readonly, auto-calculé)
// - Input montant TTC (readonly, auto-calculé)
// - Text conditions paiement (pré-rempli)
// - Date échéance (auto-calculée selon conditions)
// Script JS : calculerMontantsTVA() sur change HT ou TVA
```

#### **show.php** - Aperçu imprimable
```php
// Variables : $facture, $paiements_partiels, $print_mode
// Si $print_mode : masquer boutons (class no-print)
// Design professionnel facture :
// - En-tête entreprise (logo, coordonnées)
// - Numéro + dates (émission, échéance)
// - Bloc client (adresse complète, NIF)
// - Tableau lignes (Description livraison détaillée)
// - Totaux HT/TVA/TTC (styling)
// - Conditions paiement
// - Si paiements partiels : section historique
// Boutons (no-print) :
// - Imprimer (window.print())
// - Télécharger PDF
// - Marquer comme payée (si impayee)
// - Envoyer par email (optionnel)
```

---

### 4. Documents Engins (3 vues)

#### **index.php** - Tableau conformité
```php
// Variables : $engins, $types_documents, $compteurs
// Badges compteurs globaux en haut
// Bouton "Vérifier toutes les validités"
// Tableau matriciel :
// - Ligne par engin (immatriculation, type, statut)
// - 5 colonnes documents (carte grise, assurance, transport, stationnement, visite)
// - Chaque cellule : 
//   → Si existe : date expiration + badge couleur (valide/j30/j15/j3/expiré)
//   → Si manquant : Badge gris "MANQUANT"
// - Colonne Actions : Bouton "Upload document"
// Légende couleurs en bas
```

#### **upload.php** - Formulaire upload
```php
// Variables : $engins, $types_documents, $csrf_token
// Form avec validation HTML5 :
// - Dropdown engin (required)
// - Dropdown type document (required)
// - File input (required, accept=".pdf,.jpg,.jpeg,.png")
//   → Afficher contraintes : max 10 Mo, extensions autorisées
// - Date émission (date, optional)
// - Date expiration (date, required)
// - Text numéro document (optional)
// Script JS : validerFichier() avant submit
// Preview image si sélection (previewImage())
```

#### **show.php** - Visualiseur document
```php
// Variables : $document, $engin, $extension
// Header : Infos document (type, dates, statut badge)
// Visualisation selon extension :
// - Si PDF : utiliser PDF.js ou <embed>
// - Si image : <img> responsive
// Boutons :
// - Télécharger
// - Supprimer (confirmation)
// - Retour liste
```

---

### 5. Consommation Carburant (4 vues)

#### **index.php** - Liste pleins + graphique
```php
// Variables : $consommations, $graphique_data, $engins, $chauffeurs, $filters
// Section graphique Chart.js (évolution 7 derniers jours)
// Filtres : Engin, Chauffeur, Alerte surconsommation, Période
// Tableau :
// - Date plein
// - Engin
// - Chauffeur
// - Type carburant
// - Quantité (litres)
// - Consommation calculée (L/100km ou L/h)
// - Montant
// - Badge alerte surconsommation (si true)
// - Actions
// Script Chart.js pour graphique ligne
```

#### **create.php** - Formulaire plein
```php
// Variables : $engins, $chauffeurs, $types_carburant, $csrf_token
// Form :
// - Dropdown engin (required)
// - Dropdown chauffeur (optional)
// - Dropdown type carburant (required)
// - Number quantité litres (required, step=0.1)
// - DateTime date plein (required)
// - Number kilométrage actuel OU heures moteur (selon type engin)
// - Text lieu plein
// - Number montant (optional)
// - Textarea notes
// Preview calcul consommation si km/heures saisis
```

#### **rapport_mensuel.php** - Rapport détaillé
```php
// Variables : $mois, $annee, $statistiques_engins, $evolution_quotidienne, $surconsommations
// Sélection mois/année (form GET)
// Section 1 : Stats par engin (table)
//   - Colonnes : Engin, Nb pleins, Total litres, Coût, Consommation moy
//   - Ligne totaux
// Section 2 : Graphique barres (Chart.js) - Comparaison engins
// Section 3 : Graphique ligne (Chart.js) - Évolution quotidienne
// Section 4 : Liste surconsommations du mois
// Bouton export Excel
```

#### **alertes.php** - Dashboard alertes
```php
// Variables : $alertes, $csrf_token
// Liste alertes non vérifiées
// Pour chaque alerte :
// - Card avec infos plein
// - Afficher consommation réelle vs moyenne mobile 30j
// - Afficher écart en %
// - Graphique gauge comparatif (Chart.js, optionnel)
// - Form inline : textarea note + bouton "Marquer vérifié"
```

---

### 6. Autres Modules (Plus simples)

#### **mouvements_stock/index.php**
```php
// Historique tous mouvements stock
// Filtres : Type (entrée/sortie), Pièce, Engin, Période
// Tableau : Date, Type, Pièce, Quantité, Motif, Stock après
```

#### **mouvements_stock/create.php**
```php
// Form entrée/sortie stock
// Si entrée : calcul CUMP preview
// Si sortie : vérifier stock disponible
```

#### **engins/index.php**
```php
// Liste engins avec badges statut
// Colonne conformité (icône check/warning selon docs)
// Filtres : Type, Statut, Conformité
```

#### **engins/create.php, edit.php, show.php**
```php
// CRUD standard engin
// show.php inclut : onglets (Infos, Documents, Maintenances, Livraisons, Consommations)
```

#### **chauffeurs/index.php, create.php, edit.php, show.php**
```php
// CRUD standard chauffeur
// Badge permis (vert si valide, rouge si expiré)
// show.php inclut : affectations engins, livraisons effectuées
```

#### **clients/index.php, create.php, edit.php, show.php**
```php
// CRUD standard client
// index.php : colonne CA total
// show.php : historique factures + stats (CA total, nb factures, impayés)
```

---

## 🎨 Éléments de Design à Respecter

### Badges Statuts
```php
// Engins
<span class="badge badge-disponible">Disponible</span>
<span class="badge badge-en-mission">En Mission</span>
<span class="badge badge-en-maintenance">En Maintenance</span>
<span class="badge badge-hors-service">Hors Service</span>

// Livraisons
<span class="badge badge-planifiee">Planifiée</span>
<span class="badge badge-en-cours">En Cours</span>
<span class="badge badge-livree">Livrée</span>
<span class="badge badge-annulee">Annulée</span>

// Paiements
<span class="badge badge-payee">Payée</span>
<span class="badge badge-impayee">Impayée</span>
<span class="badge badge-partiellement-payee">Partiellement Payée</span>
```

### Icons Bootstrap Icons
```php
<i class="bi bi-truck"></i> <!-- Livraisons -->
<i class="bi bi-tools"></i> <!-- Maintenances -->
<i class="bi bi-receipt"></i> <!-- Factures -->
<i class="bi bi-file-earmark-text"></i> <!-- Documents -->
<i class="bi bi-fuel-pump"></i> <!-- Carburant -->
<i class="bi bi-box-seam"></i> <!-- Stock -->
<i class="bi bi-person"></i> <!-- Chauffeurs -->
<i class="bi bi-people"></i> <!-- Clients -->
```

### DataTables Initialisation
```javascript
$(document).ready(function() {
    $('#myTable').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/fr-FR.json'
        },
        pageLength: 25,
        responsive: true,
        order: [[0, 'desc']]
    });
});
```

---

## ✅ Checklist Avant Finalisation

Pour chaque vue créée, vérifier :
- [ ] Messages flash session affichés
- [ ] CSRF token inclus dans forms POST
- [ ] Tous outputs échappés avec htmlspecialchars()
- [ ] DataTables initialisé avec config FR
- [ ] Badges avec bonnes classes CSS
- [ ] Boutons avec bonnes classes Bootstrap
- [ ] Formulaires avec validation HTML5
- [ ] Confirmations suppression avec confirmerSuppression()
- [ ] Responsive mobile (Bootstrap grid)
- [ ] Icons cohérentes (Bootstrap Icons)

---

## 🚀 Ordre de Création Recommandé

1. **Maintenances** (workflow critique)
2. **Livraisons** (workflow critique)
3. **Factures** (workflow critique)
4. **Documents engins** (conformité sécurité)
5. **Consommation carburant** (alertes importantes)
6. **Engins** (module central)
7. **Chauffeurs** (module central)
8. **Clients** (module standard)
9. **Mouvements stock** (historique)

---

## 📦 Ressources Disponibles

- **Modèle référence** : `/app/Views/pieces/index.php`
- **CSS custom** : `/public/css/custom.css`
- **JS utilities** : `/public/js/app.js`
- **Controllers** : Tous prêts dans `/app/Controllers/`
- **Modèles** : Tous prêts dans `/app/Models/`

---

**Avec ce guide, toutes les vues peuvent être créées en suivant les mêmes patterns et structures, assurant cohérence et maintenabilité.**
