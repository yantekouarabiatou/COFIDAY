<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TypeConge extends Model
{
    protected $table = 'types_conges';

    protected $fillable = [
        'libelle',
        'nombre_jours_max',
        'est_paye',
        'est_annuel',
        'est_horaire',
        'defalque_du_solde',
        'duree_legale_jours',
        'report_possible',
        'justificatif_requis',
        'actif',
        'couleur',
    ];

    protected $casts = [
        'est_paye'            => 'boolean',
        'est_annuel'          => 'boolean',
        'est_horaire'         => 'boolean',
        'defalque_du_solde'   => 'boolean',
        'report_possible'     => 'boolean',
        'justificatif_requis' => 'boolean',
        'actif'               => 'boolean',
    ];

    /**
     * Nombre de jours maximum BLOQUANT (congés à durée fixée), ou null si le
     * maximum n'est qu'indicatif.
     *  - durée légale renseignée (paternité, mariage, décès, déménagement…) ;
     *  - maternité / paternité : plafond nombre_jours_max.
     */
    public function plafondStrict(): ?int
    {
        if (!empty($this->duree_legale_jours)) {
            return (int) $this->duree_legale_jours;
        }

        $libelle = mb_strtolower((string) $this->libelle);
        if ((str_contains($libelle, 'maternit') || str_contains($libelle, 'paternit')) && $this->nombre_jours_max) {
            return (int) $this->nombre_jours_max;
        }

        return null;
    }

    public function demandes(): HasMany
    {
        return $this->hasMany(DemandeConge::class, 'type_conge_id');
    }
}
