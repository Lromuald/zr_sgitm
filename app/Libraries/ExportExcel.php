<?php
namespace App\Libraries;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

/**
 * Bibliothèque d'export Excel
 * Utilise PhpSpreadsheet
 */
class ExportExcel
{
    private $spreadsheet;
    private $sheet;
    
    public function __construct()
    {
        $this->spreadsheet = new Spreadsheet();
        $this->sheet = $this->spreadsheet->getActiveSheet();
    }
    
    /**
     * Export générique d'une liste avec en-têtes
     * 
     * @param array $data Données à exporter
     * @param array $headers En-têtes des colonnes
     * @param string $filename Nom du fichier
     */
    public function exporterListe($data, $headers, $filename = 'export.xlsx')
    {
        // Définir les en-têtes
        $col = 'A';
        foreach ($headers as $header) {
            $this->sheet->setCellValue($col . '1', $header);
            $col++;
        }
        
        // Style des en-têtes
        $lastCol = chr(ord('A') + count($headers) - 1);
        $headerRange = 'A1:' . $lastCol . '1';
        
        $this->sheet->getStyle($headerRange)->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 12
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '240046']
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ]
        ]);
        
        // Remplir les données
        $row = 2;
        foreach ($data as $item) {
            $col = 'A';
            foreach (array_keys($headers) as $key) {
                $value = $item[$key] ?? '';
                $this->sheet->setCellValue($col . $row, $value);
                $col++;
            }
            $row++;
        }
        
        // Auto-dimensionner les colonnes
        foreach (range('A', $lastCol) as $col) {
            $this->sheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        // Bordures sur toutes les cellules
        if ($row > 2) {
            $dataRange = 'A1:' . $lastCol . ($row - 1);
            $this->sheet->getStyle($dataRange)->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000']
                    ]
                ]
            ]);
        }
        
        // Télécharger
        $this->download($filename);
    }
    
    /**
     * Export rapport mensuel consommation carburant
     * 
     * @param int $mois
     * @param int $annee
     * @param array $statistiquesEngins
     * @param array $pleins
     */
    public function exporterRapportMensuel($mois, $annee, $statistiquesEngins, $pleins = [])
    {
        $nomsMois = [
            1 => 'Janvier', 2 => 'Février', 3 => 'Mars', 4 => 'Avril',
            5 => 'Mai', 6 => 'Juin', 7 => 'Juillet', 8 => 'Août',
            9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre'
        ];
        
        // Titre du rapport
        $this->sheet->setCellValue('A1', 'RAPPORT CONSOMMATION CARBURANT');
        $this->sheet->setCellValue('A2', $nomsMois[$mois] . ' ' . $annee);
        $this->sheet->mergeCells('A1:G1');
        $this->sheet->mergeCells('A2:G2');
        
        $this->sheet->getStyle('A1:A2')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 14
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER
            ]
        ]);
        
        // En-têtes statistiques par engin
        $row = 4;
        $headers = ['Immatriculation', 'Type', 'Nb Pleins', 'Total Litres', 'Coût Total', 'Consommation Moyenne'];
        $col = 'A';
        foreach ($headers as $header) {
            $this->sheet->setCellValue($col . $row, $header);
            $col++;
        }
        
        $this->sheet->getStyle('A' . $row . ':F' . $row)->applyFromArray([
            'font' => ['bold' => true],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'FF5400']
            ],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
        ]);
        
        // Données statistiques
        $row++;
        $totalLitres = 0;
        $totalCout = 0;
        
        foreach ($statistiquesEngins as $stat) {
            $this->sheet->setCellValue('A' . $row, $stat['immatriculation']);
            $this->sheet->setCellValue('B' . $row, $stat['type']);
            $this->sheet->setCellValue('C' . $row, $stat['nb_pleins']);
            $this->sheet->setCellValue('D' . $row, number_format($stat['total_litres'], 2));
            $this->sheet->setCellValue('E' . $row, number_format($stat['total_cout'], 2));
            $this->sheet->setCellValue('F' . $row, number_format($stat['consommation_moyenne'], 2));
            
            $totalLitres += $stat['total_litres'];
            $totalCout += $stat['total_cout'];
            
            $row++;
        }
        
        // Totaux
        $this->sheet->setCellValue('A' . $row, 'TOTAL');
        $this->sheet->setCellValue('D' . $row, number_format($totalLitres, 2));
        $this->sheet->setCellValue('E' . $row, number_format($totalCout, 2));
        
        $this->sheet->getStyle('A' . $row . ':F' . $row)->applyFromArray([
            'font' => ['bold' => true],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'E0E0E0']
            ]
        ]);
        
        // Si détail des pleins fourni
        if (!empty($pleins)) {
            $row += 3;
            $this->sheet->setCellValue('A' . $row, 'DÉTAIL DES PLEINS');
            $this->sheet->mergeCells('A' . $row . ':G' . $row);
            $this->sheet->getStyle('A' . $row)->applyFromArray([
                'font' => ['bold' => true, 'size' => 12]
            ]);
            
            $row += 2;
            $headers = ['Date', 'Engin', 'Chauffeur', 'Type Carburant', 'Quantité (L)', 'Consommation', 'Montant'];
            $col = 'A';
            foreach ($headers as $header) {
                $this->sheet->setCellValue($col . $row, $header);
                $col++;
            }
            
            $this->sheet->getStyle('A' . $row . ':G' . $row)->applyFromArray([
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'FF5400']
                ]
            ]);
            
            $row++;
            foreach ($pleins as $plein) {
                $this->sheet->setCellValue('A' . $row, date('d/m/Y', strtotime($plein['date_plein'])));
                $this->sheet->setCellValue('B' . $row, $plein['immatriculation'] ?? '');
                $this->sheet->setCellValue('C' . $row, ($plein['chauffeur_nom'] ?? '') . ' ' . ($plein['chauffeur_prenom'] ?? ''));
                $this->sheet->setCellValue('D' . $row, $plein['type_carburant']);
                $this->sheet->setCellValue('E' . $row, number_format($plein['quantite_litres'], 2));
                $this->sheet->setCellValue('F' . $row, $plein['consommation_calculee'] ? number_format($plein['consommation_calculee'], 2) : 'N/A');
                $this->sheet->setCellValue('G' . $row, number_format($plein['montant'] ?? 0, 2));
                $row++;
            }
        }
        
        // Auto-dimensionner
        foreach (range('A', 'G') as $col) {
            $this->sheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        $filename = 'rapport_carburant_' . $nomsMois[$mois] . '_' . $annee . '.xlsx';
        $this->download($filename);
    }
    
    /**
     * Export rapport conformité documentaire
     * 
     * @param array $engins Engins avec leurs documents
     */
    public function exporterRapportConformite($engins)
    {
        // Titre
        $this->sheet->setCellValue('A1', 'RAPPORT DE CONFORMITÉ DOCUMENTAIRE');
        $this->sheet->setCellValue('A2', 'Généré le ' . date('d/m/Y à H:i'));
        $this->sheet->mergeCells('A1:I1');
        $this->sheet->mergeCells('A2:I2');
        
        $this->sheet->getStyle('A1:A2')->applyFromArray([
            'font' => ['bold' => true, 'size' => 14],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
        ]);
        
        // En-têtes
        $row = 4;
        $headers = [
            'Immatriculation', 'Type', 'Statut Engin', 
            'Carte Grise', 'Assurance', 'Carte Transport', 
            'Carte Stationnement', 'Visite Technique', 'Conformité'
        ];
        
        $col = 'A';
        foreach ($headers as $header) {
            $this->sheet->setCellValue($col . $row, $header);
            $col++;
        }
        
        $this->sheet->getStyle('A' . $row . ':I' . $row)->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '240046']
            ],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
        ]);
        
        // Données
        $row++;
        foreach ($engins as $engin) {
            $this->sheet->setCellValue('A' . $row, $engin['immatriculation']);
            $this->sheet->setCellValue('B' . $row, $engin['type']);
            $this->sheet->setCellValue('C' . $row, ucfirst($engin['statut']));
            
            // Documents
            $typesDocs = ['carte_grise', 'assurance', 'carte_transport', 'carte_stationnement', 'visite_technique'];
            $col = 'D';
            $tousValides = true;
            
            foreach ($typesDocs as $type) {
                if (isset($engin['documents'][$type]) && $engin['documents'][$type]['existe']) {
                    $doc = $engin['documents'][$type];
                    $dateExp = date('d/m/Y', strtotime($doc['date_expiration']));
                    $this->sheet->setCellValue($col . $row, $dateExp);
                    
                    // Colorier selon statut
                    if ($doc['badge'] === 'expire') {
                        $color = 'FF0000'; // Rouge
                        $tousValides = false;
                    } elseif ($doc['badge'] === 'urgent') {
                        $color = 'FF6600'; // Orange foncé
                        $tousValides = false;
                    } elseif ($doc['badge'] === 'attention') {
                        $color = 'FFA500'; // Orange
                    } else {
                        $color = '28A745'; // Vert
                    }
                    
                    $this->sheet->getStyle($col . $row)->applyFromArray([
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => $color]
                        ]
                    ]);
                } else {
                    $this->sheet->setCellValue($col . $row, 'MANQUANT');
                    $this->sheet->getStyle($col . $row)->applyFromArray([
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => '808080']
                        ]
                    ]);
                    $tousValides = false;
                }
                $col++;
            }
            
            // Conformité globale
            $this->sheet->setCellValue('I' . $row, $tousValides ? 'CONFORME' : 'NON CONFORME');
            $this->sheet->getStyle('I' . $row)->applyFromArray([
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => $tousValides ? '28A745' : 'DC3545']
                ]
            ]);
            
            $row++;
        }
        
        // Bordures
        $lastRow = $row - 1;
        $this->sheet->getStyle('A4:I' . $lastRow)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN
                ]
            ]
        ]);
        
        // Auto-dimensionner
        foreach (range('A', 'I') as $col) {
            $this->sheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        $filename = 'conformite_documentaire_' . date('Y-m-d') . '.xlsx';
        $this->download($filename);
    }
    
    /**
     * Télécharge le fichier Excel
     * 
     * @param string $filename
     */
    private function download($filename)
    {
        // Headers pour téléchargement
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        
        $writer = new Xlsx($this->spreadsheet);
        $writer->save('php://output');
        exit;
    }
}
