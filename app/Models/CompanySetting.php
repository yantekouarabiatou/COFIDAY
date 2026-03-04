<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanySetting extends Model
{
    protected $fillable = [
        'company_name',
        'slogan',
        'email',
        'telephone',
        'adresse',
        'logo',
        'site_web',
        'ville',
        'pays',
        'guide',
    ];

    // Accessor URL du logo
    public function getLogoUrlAttribute()
    {
        return $this->logo
            ? asset('storage/' . $this->logo)
            : asset('assets/img/logo_cofima_bon.jpg');
    }

    // Accessor URL du guide
    public function getGuideUrlAttribute()
    {
        return $this->guide
            ? asset('storage/' . $this->guide)
            : null;
    }
}
