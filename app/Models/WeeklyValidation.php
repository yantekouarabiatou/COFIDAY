<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class WeeklyValidation extends Model
{
    protected $fillable = [
        'user_id', 'validated_by', 'semaine', 'annee',
        'statut', 'motif_refus', 'validated_at',
    ];

    protected $casts = [
        'validated_at' => 'datetime',
    ];

    // ── Relations ────────────────────────────────────────────────
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function validator()
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    public function dailyEntries()
    {
        return DailyEntry::where('user_id', $this->user_id)
            ->where('semaine', $this->semaine)
            ->where('annee_semaine', $this->annee)
            ->get();
    }

    // ── Scopes ───────────────────────────────────────────────────
    public function scopeCurrentWeek($query)
    {
        return $query->where('semaine', now()->isoWeek())
                     ->where('annee', now()->isoWeekYear());
    }

    // ── Helpers ──────────────────────────────────────────────────
    public static function forWeek(int $userId, int $semaine, int $annee): ?self
    {
        return static::where('user_id', $userId)
            ->where('semaine', $semaine)
            ->where('annee', $annee)
            ->first();
    }

    public function getDateDebutAttribute(): Carbon
    {
        return Carbon::now()
            ->setISODate($this->annee, $this->semaine)
            ->startOfWeek();
    }

    public function getDateFinAttribute(): Carbon
    {
        return Carbon::now()
            ->setISODate($this->annee, $this->semaine)
            ->endOfWeek();
    }
}