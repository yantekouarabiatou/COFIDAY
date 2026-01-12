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
        'actif',
        'couleur',
    ];

    protected $casts = [
        'est_paye' => 'boolean',
        'actif' => 'boolean',
    ];

    public function demandes(): HasMany
    {
        return $this->hasMany(DemandeConge::class, 'type_conge_id');
    }
}
