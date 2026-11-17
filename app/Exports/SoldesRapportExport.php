<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

/**
 * Classeur Excel du rapport des soldes.
 * Chaque feuille est un SoldesRapportSheet (logo COFIMA, titre, tableau, total).
 */
class SoldesRapportExport implements WithMultipleSheets
{
    /** @param SoldesRapportSheet[] $feuilles */
    public function __construct(protected array $feuilles) {}

    public function sheets(): array
    {
        return $this->feuilles;
    }
}
