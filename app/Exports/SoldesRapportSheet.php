<?php

namespace App\Exports;

use App\Services\RapportSoldeGraphics;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

/**
 * Une feuille du rapport des soldes :
 *   lignes 1-3 : logo COFIMA | ligne 4 : titre | ligne 5 : date | ligne 7 : en-têtes | ligne 8+ : données | TOTAL
 */
class SoldesRapportSheet implements FromArray, WithEvents, WithTitle, WithDrawings, ShouldAutoSize
{
    private const PREMIERE_LIGNE_DONNEES = 8;

    /**
     * @param string     $nomFeuille     nom de l'onglet
     * @param string     $titre          titre affiché en haut
     * @param string[]   $colonnesLibelle en-têtes des colonnes descriptives (ex. ['Collaborateur'] ou ['Collaborateur', 'Année'])
     * @param Collection $lignes         éléments ['libelles' => [...], 'acquis', 'reportes', 'pris', 'restants']
     */
    public function __construct(
        protected string $nomFeuille,
        protected string $titre,
        protected array $colonnesLibelle,
        protected Collection $lignes
    ) {}

    public function title(): string
    {
        return mb_substr($this->nomFeuille, 0, 31);
    }

    public function drawings()
    {
        $path = RapportSoldeGraphics::logoPath();
        if (!$path) {
            return [];
        }

        $logo = new Drawing();
        $logo->setName('COFIMA');
        $logo->setDescription('Logo COFIMA');
        $logo->setPath($path);
        $logo->setHeight(48);
        $logo->setCoordinates('A1');
        $logo->setOffsetX(4);
        $logo->setOffsetY(4);

        return [$logo];
    }

    private function derniereColonne(): string
    {
        return Coordinate::stringFromColumnIndex(count($this->colonnesLibelle) + 6);
    }

    public function array(): array
    {
        $taux = fn ($pris, $dispo) => $dispo > 0 ? round($pris / $dispo * 100, 1) . ' %' : '-';

        $rows = [
            [], [], [],                                          // zone du logo
            [$this->titre],
            ['Exporté le ' . now()->format('d/m/Y à H:i')],
            [],
            array_merge($this->colonnesLibelle, [
                'Jours acquis', 'Jours reportés', 'Total disponible', 'Jours consommés', 'Jours restants', 'Taux de consommation',
            ]),
        ];

        foreach ($this->lignes as $l) {
            $dispo  = $l['acquis'] + $l['reportes'];
            $rows[] = array_merge($l['libelles'], [
                $l['acquis'], $l['reportes'], $dispo, $l['pris'], $l['restants'], $taux($l['pris'], $dispo),
            ]);
        }

        $t = [
            'acquis'   => $this->lignes->sum('acquis'),
            'reportes' => $this->lignes->sum('reportes'),
            'pris'     => $this->lignes->sum('pris'),
            'restants' => $this->lignes->sum('restants'),
        ];
        $dispo  = $t['acquis'] + $t['reportes'];
        $rows[] = array_merge(
            array_pad(['TOTAL'], count($this->colonnesLibelle), ''),
            [$t['acquis'], $t['reportes'], $dispo, $t['pris'], $t['restants'], $taux($t['pris'], $dispo)]
        );

        return $rows;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet   = $event->sheet->getDelegate();
                $last    = $this->derniereColonne();
                $first   = self::PREMIERE_LIGNE_DONNEES;
                $header  = $first - 1;
                $lastRow = $first + $this->lignes->count();     // ligne TOTAL
                $nbLib   = count($this->colonnesLibelle);
                $firstNum = Coordinate::stringFromColumnIndex($nbLib + 1);

                foreach ([1, 2, 3] as $r) {
                    $sheet->getRowDimension($r)->setRowHeight(18);
                }

                $sheet->mergeCells("A4:{$last}4");
                $sheet->mergeCells("A5:{$last}5");
                $sheet->getStyle('A4')->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 14, 'color' => ['argb' => 'FF26467F']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);
                $sheet->getRowDimension(4)->setRowHeight(24);
                $sheet->getStyle('A5')->applyFromArray([
                    'font'      => ['italic' => true, 'size' => 9, 'color' => ['argb' => 'FF666666']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                $sheet->getStyle("A{$header}:{$last}{$header}")->applyFromArray([
                    'font'      => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF26467F']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);
                $sheet->getRowDimension($header)->setRowHeight(22);

                $sheet->getStyle("A{$header}:{$last}{$lastRow}")->applyFromArray([
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFD9D9D9']]],
                ]);
                $sheet->getStyle("{$firstNum}{$first}:{$last}{$lastRow}")
                    ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Jours consommés en rouge
                $colPris = Coordinate::stringFromColumnIndex($nbLib + 4);
                $sheet->getStyle("{$colPris}{$first}:{$colPris}{$lastRow}")
                    ->getFont()->getColor()->setARGB('FFDC3545');

                $sheet->getStyle("A{$lastRow}:{$last}{$lastRow}")->applyFromArray([
                    'font' => ['bold' => true],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFC6E0B4']],
                ]);

                $sheet->freezePane("A{$first}");
                $sheet->setAutoFilter("A{$header}:{$last}" . ($lastRow - 1));
            },
        ];
    }
}
