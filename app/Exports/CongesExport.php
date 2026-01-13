<?php

namespace App\Exports;

use App\Models\DemandeConge;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Carbon\Carbon;

class CongesExport implements
    FromCollection,
    WithHeadings,
    WithMapping,
    WithStyles,
    WithEvents,
    WithColumnFormatting,
    WithCustomStartCell
{
    protected $conges;
    protected $totalJours = 0;
    protected $totalApprouves = 0;
    protected $totalEnAttente = 0;
    protected $totalRefuses = 0;
    protected $annee;
    protected $startDate;
    protected $endDate;
    protected $exportType; // 'all', 'user', 'period'

    public function __construct($conges, $annee = null, $exportType = 'all')
    {
        $this->conges = $conges;
        $this->annee = $annee ?? now()->year;
        $this->exportType = $exportType;
        $this->calculerTotaux();
        $this->extractDates();
    }

    private function calculerTotaux()
    {
        $this->totalJours = $this->conges->sum('nombre_jours');

        $this->totalApprouves = $this->conges->where('statut', 'approuve')->count();
        $this->totalEnAttente = $this->conges->where('statut', 'en_attente')->count();
        $this->totalRefuses = $this->conges->where('statut', 'refuse')->count();
    }

    private function extractDates()
    {
        if ($this->conges->isNotEmpty()) {
            $dates = $this->conges->pluck('date_debut')->merge($this->conges->pluck('date_fin'));
            $this->startDate = $dates->min();
            $this->endDate = $dates->max();
        }
    }

    public function collection()
    {
        return $this->conges;
    }

    public function headings(): array
    {
        // En-têtes écrits manuellement dans AfterSheet
        return [];
    }

    public function map($conge): array
    {
        $statutColor = $this->getStatutColor($conge->statut);
        $statutLabel = $this->getStatutLabel($conge->statut);

        // Historique des actions
        $historique = $conge->historiques->map(function ($hist) {
            $date = $hist->date_action->format('d/m/Y H:i');
            $action = $this->getActionLabel($hist->action);
            $par = $hist->effectuePar ? $hist->effectuePar->name : 'Système';
            $comment = $hist->commentaire ? ": {$hist->commentaire}" : '';

            return "- {$date} : {$action} par {$par}{$comment}";
        })->implode("\n");

        // Validation info
        $validationInfo = '';
        if ($conge->valide_par && $conge->date_validation) {
            $dateValidation = Carbon::parse($conge->date_validation)->format('d/m/Y H:i');
            $validationInfo = "Validé par: {$conge->validePar->name}\nLe: {$dateValidation}";
        }

        // Calcul du nombre de jours ouvrés vs calendaires
        $joursCalendaires = Carbon::parse($conge->date_debut)->diffInDays(Carbon::parse($conge->date_fin)) + 1;
        $difference = $joursCalendaires - $conge->nombre_jours;
        $joursInfo = "{$conge->nombre_jours} jours ouvrés\n({$joursCalendaires} jours calendaires)";

        return [
            $conge->id,
            Carbon::parse($conge->date_debut)->format('d/m/Y'),
            Carbon::parse($conge->date_fin)->format('d/m/Y'),
            $joursInfo,
            $conge->user->prenom . ' ' . $conge->user->nom,
            $conge->user->email,
            $conge->user->poste->intitule ?? '-',
            $conge->typeConge->libelle,
            $conge->typeConge->est_paye ? 'Oui' : 'Non',
            $statutLabel,
            $validationInfo,
            $conge->motif ?: '-',
            $historique,
            $conge->created_at->format('d/m/Y H:i'),
            Carbon::parse($conge->date_debut)->diffForHumans(),
        ];
    }

    private function getStatutColor($statut)
    {
        return match($statut) {
            'approuve' => '28a745',
            'en_attente' => 'ffc107',
            'refuse' => 'dc3545',
            'annule' => '6c757d',
            default => '6c757d'
        };
    }

    private function getStatutLabel($statut)
    {
        return match($statut) {
            'approuve' => 'Approuvé',
            'en_attente' => 'En attente',
            'refuse' => 'Refusé',
            'annule' => 'Annulé',
            default => $statut
        };
    }

    private function getActionLabel($action)
    {
        $labels = [
            'demande_soumise' => 'Demande soumise',
            'demande_modifiee' => 'Demande modifiée',
            'demande_approuvee' => 'Demande approuvée',
            'demande_refusee' => 'Demande refusée',
            'demande_annulee' => 'Demande annulée',
            'solde_ajuste' => 'Solde ajusté',
        ];

        return $labels[$action] ?? $action;
    }

    public function startCell(): string
    {
        return 'A6'; // Les données commencent à la ligne 6
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getDefaultRowDimension()->setRowHeight(22);
        $sheet->getStyle('A:O')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

        // Largeurs des colonnes
        $sheet->getColumnDimension('A')->setWidth(8);  // ID
        $sheet->getColumnDimension('B')->setWidth(12); // Date début
        $sheet->getColumnDimension('C')->setWidth(12); // Date fin
        $sheet->getColumnDimension('D')->setWidth(18); // Jours info
        $sheet->getColumnDimension('E')->setWidth(25); // Employé
        $sheet->getColumnDimension('F')->setWidth(25); // Email
        $sheet->getColumnDimension('G')->setWidth(20); // Poste
        $sheet->getColumnDimension('H')->setWidth(18); // Type
        $sheet->getColumnDimension('I')->setWidth(10); // Payé
        $sheet->getColumnDimension('J')->setWidth(12); // Statut
        $sheet->getColumnDimension('K')->setWidth(25); // Validation info
        $sheet->getColumnDimension('L')->setWidth(30); // Motif
        $sheet->getColumnDimension('M')->setWidth(40); // Historique
        $sheet->getColumnDimension('N')->setWidth(16); // Créé le
        $sheet->getColumnDimension('O')->setWidth(15); // Dans

        // Wrap text pour les colonnes avec du texte long
        $sheet->getStyle('L:L')->getAlignment()->setWrapText(true);
        $sheet->getStyle('M:M')->getAlignment()->setWrapText(true);
        $sheet->getStyle('K:K')->getAlignment()->setWrapText(true);
        $sheet->getStyle('D:D')->getAlignment()->setWrapText(true);

        return [];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Dernière ligne de données
                $lastDataRow = $this->conges->count() + 5;

                // ===== TITRE PRINCIPAL =====
                $title = "RAPPORT DES CONGÉS";
                if ($this->exportType === 'user' && $this->conges->isNotEmpty()) {
                    $user = $this->conges->first()->user;
                    $title .= " - " . strtoupper($user->prenom . ' ' . $user->nom);
                }

                $sheet->setCellValue('A1', $title);
                $sheet->mergeCells('A1:O1');
                $sheet->getRowDimension(1)->setRowHeight(35);
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 18, 'color' => ['argb' => 'FF2E75B6']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFE2EFDA']],
                ]);

                // ===== SOUS-TITRE =====
                $subTitle = "Année : {$this->annee}";
                if ($this->startDate && $this->endDate) {
                    $start = Carbon::parse($this->startDate)->format('d/m/Y');
                    $end = Carbon::parse($this->endDate)->format('d/m/Y');
                    if ($start !== $end) {
                        $subTitle .= " | Période : {$start} au {$end}";
                    } else {
                        $subTitle .= " | Date : {$start}";
                    }
                }

                $sheet->setCellValue('A2', $subTitle);
                $sheet->mergeCells('A2:O2');
                $sheet->getRowDimension(2)->setRowHeight(28);
                $sheet->getStyle('A2')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 13, 'color' => ['argb' => 'FF404040']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFF2F2F2']],
                ]);

                // ===== STATISTIQUES RAPIDES =====
                $sheet->setCellValue('A3', "Statistiques :");
                $sheet->mergeCells('A3:E3');
                $sheet->getStyle('A3')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 11],
                ]);

                $statsText = "Total : {$this->conges->count()} congés | ";
                $statsText .= "Jours : {$this->totalJours} | ";
                $statsText .= "Approuvés : {$this->totalApprouves} | ";
                $statsText .= "En attente : {$this->totalEnAttente} | ";
                $statsText .= "Refusés : {$this->totalRefuses}";

                $sheet->setCellValue('F3', $statsText);
                $sheet->mergeCells('F3:O3');
                $sheet->getStyle('F3')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 10, 'color' => ['argb' => 'FF666666']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                // Ligne vide
                $sheet->getRowDimension(4)->setRowHeight(10);
                $sheet->getRowDimension(5)->setRowHeight(30);

                // ===== EN-TÊTES DES COLONNES =====
                $headers = [
                    'A5' => 'ID', 'B5' => 'Date début', 'C5' => 'Date fin',
                    'D5' => 'Durée', 'E5' => 'Employé', 'F5' => 'Email',
                    'G5' => 'Poste', 'H5' => 'Type de congé', 'I5' => 'Payé',
                    'J5' => 'Statut', 'K5' => 'Validation', 'L5' => 'Motif',
                    'M5' => 'Historique', 'N5' => 'Soumis le', 'O5' => 'Dans'
                ];

                foreach ($headers as $cell => $value) {
                    $sheet->setCellValue($cell, $value);
                }

                $sheet->getStyle('A5:O5')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 11, 'color' => ['argb' => 'FFFFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF2E75B6']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['argb' => 'FF2E75B6']]],
                ]);

                // ===== STYLE DES DONNÉES =====
                $dataRange = 'A6:O' . $lastDataRow;
                $sheet->getStyle($dataRange)->applyFromArray([
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFD9D9D9']]],
                ]);

                // Alignement spécifique par colonne
                $sheet->getStyle('A6:A' . $lastDataRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('B6:C' . $lastDataRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('D6:D' . $lastDataRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('I6:I' . $lastDataRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('J6:J' . $lastDataRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('N6:N' . $lastDataRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('O6:O' . $lastDataRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Coloration des statuts
                for ($row = 6; $row <= $lastDataRow; $row++) {
                    $statutCell = $sheet->getCell("J{$row}")->getValue();
                    $color = $this->getStatutColorForExcel($statutCell);

                    $sheet->getStyle("J{$row}")->applyFromArray([
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $color]],
                        'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                    ]);
                }

                // ===== RÉCAPITULATIF PAR TYPE =====
                $recapStartRow = $lastDataRow + 3;

                // Titre récapitulatif
                $sheet->setCellValue("A{$recapStartRow}", "RÉCAPITULATIF PAR TYPE DE CONGÉ");
                $sheet->mergeCells("A{$recapStartRow}:D{$recapStartRow}");
                $sheet->getStyle("A{$recapStartRow}")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 12],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                // Calcul des statistiques par type
                $statsByType = [];
                foreach ($this->conges as $conge) {
                    $type = $conge->typeConge->libelle;
                    if (!isset($statsByType[$type])) {
                        $statsByType[$type] = [
                            'count' => 0,
                            'total_jours' => 0,
                            'approuves' => 0,
                            'en_attente' => 0,
                            'refuses' => 0
                        ];
                    }

                    $statsByType[$type]['count']++;
                    $statsByType[$type]['total_jours'] += $conge->nombre_jours;

                    switch ($conge->statut) {
                        case 'approuve': $statsByType[$type]['approuves']++; break;
                        case 'en_attente': $statsByType[$type]['en_attente']++; break;
                        case 'refuse': $statsByType[$type]['refuses']++; break;
                    }
                }

                // En-têtes du récapitulatif
                $recapHeadersRow = $recapStartRow + 1;
                $recapHeaders = ['Type', 'Nombre', 'Jours totaux', 'Approuvés', 'En attente', 'Refusés'];
                $col = 0;
                foreach ($recapHeaders as $header) {
                    $cell = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col + 1) . $recapHeadersRow;
                    $sheet->setCellValue($cell, $header);
                    $col++;
                }

                $sheet->getStyle("A{$recapHeadersRow}:F{$recapHeadersRow}")->applyFromArray([
                    'font' => ['bold' => true],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFD9E1F2']],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                // Données du récapitulatif
                $dataRow = $recapHeadersRow + 1;
                foreach ($statsByType as $type => $data) {
                    $sheet->setCellValue("A{$dataRow}", $type);
                    $sheet->setCellValue("B{$dataRow}", $data['count']);
                    $sheet->setCellValue("C{$dataRow}", $data['total_jours']);
                    $sheet->setCellValue("D{$dataRow}", $data['approuves']);
                    $sheet->setCellValue("E{$dataRow}", $data['en_attente']);
                    $sheet->setCellValue("F{$dataRow}", $data['refuses']);

                    $dataRow++;
                }

                // Style des données du récapitulatif
                $lastRecapRow = $dataRow - 1;
                $sheet->getStyle("A{$recapHeadersRow}:F{$lastRecapRow}")->applyFromArray([
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                ]);

                // Totaux du récapitulatif
                $totalRow = $lastRecapRow + 1;
                $sheet->setCellValue("A{$totalRow}", "TOTAL");
                $sheet->setCellValue("B{$totalRow}", $this->conges->count());
                $sheet->setCellValue("C{$totalRow}", $this->totalJours);
                $sheet->setCellValue("D{$totalRow}", $this->totalApprouves);
                $sheet->setCellValue("E{$totalRow}", $this->totalEnAttente);
                $sheet->setCellValue("F{$totalRow}", $this->totalRefuses);

                $sheet->getStyle("A{$totalRow}:F{$totalRow}")->applyFromArray([
                    'font' => ['bold' => true],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFC6E0B4']],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_MEDIUM]],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                // ===== DATE D'EXPORT =====
                $exportRow = $totalRow + 2;
                $sheet->setCellValue("O{$exportRow}", "Exporté le " . now()->format('d/m/Y à H:i'));
                $sheet->getStyle("O{$exportRow}")->applyFromArray([
                    'font' => ['italic' => true, 'size' => 9, 'color' => ['argb' => 'FF666666']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT],
                ]);

                // Ajustement de la hauteur des lignes pour les colonnes avec texte long
                for ($row = 6; $row <= $lastDataRow; $row++) {
                    $historique = $sheet->getCell("M{$row}")->getValue();
                    $motif = $sheet->getCell("L{$row}")->getValue();

                    $lineCount = 1;
                    if ($historique && strpos($historique, "\n") !== false) {
                        $lineCount = max($lineCount, substr_count($historique, "\n") + 1);
                    }
                    if ($motif && strpos($motif, "\n") !== false) {
                        $lineCount = max($lineCount, substr_count($motif, "\n") + 1);
                    }

                    if ($lineCount > 1) {
                        $sheet->getRowDimension($row)->setRowHeight(22 * $lineCount);
                    }
                }

                // ===== FIGER L'EN-TÊTE =====
                $sheet->freezePane('A6');

                // ===== PROTECTION DES CELLULES =====
                // $sheet->getProtection()->setSheet(true);
                // $sheet->getStyle('A6:O' . $lastDataRow)->getProtection()->setLocked(\PhpOffice\PhpSpreadsheet\Style\Protection::PROTECTION_UNPROTECTED);
            },
        ];
    }

    private function getStatutColorForExcel($statut)
    {
        return match($statut) {
            'Approuvé' => 'FF28a745',
            'En attente' => 'FFffc107',
            'Refusé' => 'FFdc3545',
            'Annulé' => 'FF6c757d',
            default => 'FF6c757d'
        };
    }

    public function columnFormats(): array
    {
        return [
            'B' => NumberFormat::FORMAT_DATE_DDMMYYYY,
            'C' => NumberFormat::FORMAT_DATE_DDMMYYYY,
            'D' => NumberFormat::FORMAT_NUMBER_00,
            'N' => NumberFormat::FORMAT_DATE_DATETIME,
        ];
    }
}
