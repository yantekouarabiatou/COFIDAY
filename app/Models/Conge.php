<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Conge extends Model
{
    use HasFactory;

    protected $fillable = [
        'type_conge',
        'date_debut',
        'date_fin',
        'user_id',
        'leave_type_id',
        'days',
        'reason',
        'status',
    ];



    protected $casts = [
        'date_debut' => 'date',
        'date_fin' => 'date',
        'type_conge' => 'string',
    ];

    // Liste des valeurs possibles pour type_conge
    const TYPES = ['MALADIE', 'MATERNITE', 'REMUNERE', 'NON REMUNERE'];

    /**
     * Un congé appartient à un utilisateur
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function getNombreJoursAttribute()
    {
        if ($this->date_debut && $this->date_fin) {
            $start = Carbon::parse($this->date_debut);
            $end = Carbon::parse($this->date_fin);
            return $start->diffInDays($end) + 1;
        }
        return 0;
    }

    public function getDateDebutFormattedAttribute()
    {
        return Carbon::parse($this->date_debut)->format('d/m/Y');
    }

    public function getDateFinFormattedAttribute()
    {
        return Carbon::parse($this->date_fin)->format('d/m/Y');
    }

}
