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
        'jours_restants'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
