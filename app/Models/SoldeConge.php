<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SoldeConge extends Model
{
    protected $table = 'soldes_conges';

    protected $fillable = [
        'user_id',
        'annee',
        'jours_acquis',
        'jours_pris',
        'jours_restants',
        'jours_reportes',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected static function booted()
    {
        static::saving(function ($solde) {
            $solde->jours_restants = ($solde->jours_acquis + ($solde->jours_reportes ?? 0)) - $solde->jours_pris;
        });
    }
}
