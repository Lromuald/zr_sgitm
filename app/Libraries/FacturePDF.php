<?php
namespace App\Libraries;

/**
 * Bibliothèque de génération de PDF pour les factures
 * Utilise TCPDF
 */
class FacturePDF extends \TCPDF
{
    private $factureData;
    
    /**
     * En-tête personnalisé
     */
    public function Header()
    {
        // Logo et informations entreprise
        $this->SetFont('helvetica', 'B', 20);
        $this->SetTextColor(36, 0, 70); // #240046
        $this->Cell(0, 15, 'ZRSGIMT', 0, false, 'L', 0, '', 0, false, 'M', 'M');
        
        $this->SetFont('helvetica', '', 9);
        $this->SetTextColor(100, 100, 100);
        $this->Ln(5);
        $this->Cell(0, 5, 'Système de Gestion Intégrée - Transport & Mining', 0, false, 'L', 0, '', 0, false, 'M', 'M');
        $this->Ln(4);
        $this->Cell(0, 5, 'Kinshasa, République Démocratique du Congo', 0, false, 'L', 0, '', 0, false, 'M', 'M');
        $this->Ln(4);
        $this->Cell(0, 5, 'Tél: +243 XX XXX XXXX | Email: contact@zrsgimt.com', 0, false, 'L', 0, '', 0, false, 'M', 'M');
        
        // Ligne de séparation
        $this->Ln(5);
        $this->SetLineStyle(array('width' => 0.5, 'color' => array(36, 0, 70)));
        $this->Line(15, $this->GetY(), 195, $this->GetY());
        $this->Ln(5);
    }
    
    /**
     * Pied de page personnalisé
     */
    public function Footer()
    {
        $this->SetY(-25);
        $this->SetLineStyle(array('width' => 0.5, 'color' => array(36, 0, 70)));
        $this->Line(15, $this->GetY(), 195, $this->GetY());
        
        $this->Ln(3);
        $this->SetFont('helvetica', 'I', 8);
        $this->SetTextColor(100, 100, 100);
        
        // Mentions légales
        $this->Cell(0, 5, 'Conditions de paiement: ' . ($this->factureData['conditions_paiement'] ?? 'Paiement à 30 jours'), 0, false, 'C', 0, '', 0, false, 'M', 'M');
        $this->Ln(4);
        $this->Cell(0, 5, 'Facture générée le ' . date('d/m/Y à H:i'), 0, false, 'C', 0, '', 0, false, 'M', 'M');
        $this->Ln(4);
        $this->Cell(0, 5, 'Page ' . $this->getAliasNumPage() . ' sur ' . $this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'M', 'M');
    }
    
    /**
     * Génère le PDF de la facture
     * 
     * @param array $facture Données complètes de la facture
     */
    public function generer($facture)
    {
        $this->factureData = $facture;
        
        // Configuration du document
        $this->SetCreator('ZRSGIMT');
        $this->SetAuthor('ZRSGIMT System');
        $this->SetTitle('Facture ' . $facture['numero_facture']);
        $this->SetSubject('Facture Client');
        $this->SetKeywords('Facture, ZRSGIMT, Transport');
        
        // Marges
        $this->SetMargins(15, 45, 15);
        $this->SetHeaderMargin(10);
        $this->SetFooterMargin(10);
        
        // Auto page breaks
        $this->SetAutoPageBreak(TRUE, 30);
        
        // Ajouter une page
        $this->AddPage();
        
        // Titre FACTURE
        $this->SetFont('helvetica', 'B', 24);
        $this->SetTextColor(36, 0, 70);
        $this->Cell(0, 15, 'FACTURE', 0, false, 'C', 0, '', 0, false, 'M', 'M');
        $this->Ln(10);
        
        // Numéro et date
        $this->SetFont('helvetica', '', 10);
        $this->SetTextColor(0, 0, 0);
        $this->Cell(90, 6, 'Numéro: ' . $facture['numero_facture'], 0, false, 'L', 0, '', 0, false, 'M', 'M');
        $this->Cell(90, 6, 'Date d\'émission: ' . date('d/m/Y', strtotime($facture['date_emission'])), 0, false, 'R', 0, '', 0, false, 'M', 'M');
        $this->Ln(5);
        $this->Cell(90, 6, '', 0, false, 'L', 0, '', 0, false, 'M', 'M');
        $this->Cell(90, 6, 'Date d\'échéance: ' . date('d/m/Y', strtotime($facture['date_echeance'])), 0, false, 'R', 0, '', 0, false, 'M', 'M');
        $this->Ln(10);
        
        // Informations client
        $this->SetFillColor(240, 240, 240);
        $this->SetFont('helvetica', 'B', 11);
        $this->Cell(0, 8, 'FACTURÉ À', 0, false, 'L', 1, '', 0, false, 'M', 'M');
        $this->Ln(2);
        
        $this->SetFont('helvetica', '', 10);
        $this->Cell(0, 6, $facture['client_nom'] ?? 'N/A', 0, false, 'L', 0, '', 0, false, 'M', 'M');
        $this->Ln(5);
        
        if (!empty($facture['client_adresse'])) {
            $this->Cell(0, 6, $facture['client_adresse'], 0, false, 'L', 0, '', 0, false, 'M', 'M');
            $this->Ln(5);
        }
        
        if (!empty($facture['client_telephone'])) {
            $this->Cell(0, 6, 'Tél: ' . $facture['client_telephone'], 0, false, 'L', 0, '', 0, false, 'M', 'M');
            $this->Ln(5);
        }
        
        if (!empty($facture['client_email'])) {
            $this->Cell(0, 6, 'Email: ' . $facture['client_email'], 0, false, 'L', 0, '', 0, false, 'M', 'M');
            $this->Ln(5);
        }
        
        if (!empty($facture['client_nif'])) {
            $this->Cell(0, 6, 'NIF: ' . $facture['client_nif'], 0, false, 'L', 0, '', 0, false, 'M', 'M');
            $this->Ln(5);
        }
        
        $this->Ln(10);
        
        // Tableau des lignes de facture
        $this->SetFont('helvetica', 'B', 10);
        $this->SetFillColor(36, 0, 70);
        $this->SetTextColor(255, 255, 255);
        
        // En-têtes du tableau
        $this->Cell(100, 8, 'DESCRIPTION', 1, false, 'L', 1, '', 0, false, 'M', 'M');
        $this->Cell(30, 8, 'QUANTITÉ', 1, false, 'C', 1, '', 0, false, 'M', 'M');
        $this->Cell(25, 8, 'P.U.', 1, false, 'R', 1, '', 0, false, 'M', 'M');
        $this->Cell(25, 8, 'TOTAL HT', 1, false, 'R', 1, '', 0, false, 'M', 'M');
        $this->Ln();
        
        // Lignes de facture
        $this->SetFont('helvetica', '', 9);
        $this->SetTextColor(0, 0, 0);
        $this->SetFillColor(250, 250, 250);
        
        // Description de la livraison
        $description = 'Livraison ' . ($facture['numero_livraison'] ?? 'N/A');
        if (!empty($facture['type_materiau'])) {
            $description .= "\nMatériau: " . $facture['type_materiau'];
        }
        if (!empty($facture['poids'])) {
            $description .= "\nPoids: " . $facture['poids'] . ' tonnes';
        }
        if (!empty($facture['lieu_depart']) && !empty($facture['lieu_destination'])) {
            $description .= "\nTrajet: " . $facture['lieu_depart'] . ' → ' . $facture['lieu_destination'];
        }
        if (!empty($facture['immatriculation'])) {
            $description .= "\nEngin: " . $facture['immatriculation'];
        }
        
        $startY = $this->GetY();
        $this->MultiCell(100, 6, $description, 1, 'L', 1, 0, '', '', true, 0, false, true, 0);
        $this->Cell(30, 6, '1', 1, false, 'C', 1, '', 0, false, 'M', 'M');
        $this->Cell(25, 6, number_format($facture['montant_ht'], 2, ',', ' ') . ' FC', 1, false, 'R', 1, '', 0, false, 'M', 'M');
        $this->Cell(25, 6, number_format($facture['montant_ht'], 2, ',', ' ') . ' FC', 1, false, 'R', 1, '', 0, false, 'M', 'M');
        
        // Ajuster la hauteur de la ligne si nécessaire
        $endY = $this->GetY();
        $cellHeight = $endY - $startY;
        if ($cellHeight < 6) {
            $cellHeight = 6;
        }
        
        $this->Ln($cellHeight);
        
        // Totaux
        $this->Ln(5);
        $this->SetFont('helvetica', '', 10);
        
        // Sous-total HT
        $this->Cell(155, 6, 'Sous-total HT:', 0, false, 'R', 0, '', 0, false, 'M', 'M');
        $this->SetFont('helvetica', 'B', 10);
        $this->Cell(25, 6, number_format($facture['montant_ht'], 2, ',', ' ') . ' FC', 0, false, 'R', 0, '', 0, false, 'M', 'M');
        $this->Ln(6);
        
        // TVA
        $this->SetFont('helvetica', '', 10);
        $this->Cell(155, 6, 'TVA (' . number_format($facture['taux_tva'], 0) . '%):', 0, false, 'R', 0, '', 0, false, 'M', 'M');
        $this->SetFont('helvetica', 'B', 10);
        $this->Cell(25, 6, number_format($facture['montant_tva'], 2, ',', ' ') . ' FC', 0, false, 'R', 0, '', 0, false, 'M', 'M');
        $this->Ln(8);
        
        // Total TTC
        $this->SetFillColor(36, 0, 70);
        $this->SetTextColor(255, 255, 255);
        $this->SetFont('helvetica', 'B', 12);
        $this->Cell(155, 10, 'TOTAL TTC:', 1, false, 'R', 1, '', 0, false, 'M', 'M');
        $this->Cell(25, 10, number_format($facture['montant_ttc'], 2, ',', ' ') . ' FC', 1, false, 'R', 1, '', 0, false, 'M', 'M');
        
        $this->Ln(15);
        
        // Informations de paiement
        $this->SetTextColor(0, 0, 0);
        $this->SetFont('helvetica', 'I', 9);
        $this->SetFillColor(255, 250, 240);
        $this->MultiCell(0, 8, 'Merci de votre confiance. Pour tout renseignement, veuillez nous contacter.', 1, 'C', 1, 0, '', '', true);
        
        // Nom du fichier
        $nomFichier = $facture['numero_facture'] . '_' . preg_replace('/[^a-zA-Z0-9]/', '_', $facture['client_nom']) . '.pdf';
        
        // Output
        $this->Output($nomFichier, 'D'); // 'D' pour download, 'I' pour inline
    }
}
