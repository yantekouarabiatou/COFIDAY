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

    public function demandes(): HasMany
    {
        return $this->hasMany(DemandeConge::class, 'type_conge_id');
    }
}
