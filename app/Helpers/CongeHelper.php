<?php

namespace App\Helpers;

use Carbon\Carbon;
use App\Models\RegleConge;
use App\Services\JoursFeriesService;

class CongeHelper
{
    public static function estJourOuvrable($date)
    {
        // Week-ends + jours fériés (récurrents et propres à une année)
        return JoursFeriesService::estJourOuvrable($date);
    }

    public static function estPeriodeBloquee($date)
    {
        $regles = RegleConge::first();
        if (!$regles || !$regles->periodes_bloquees) {
            return false;
        }
        
        $periodesBloquees = json_decode($regles->periodes_bloquees, true);
        if (!is_array($periodesBloquees)) {
            return false;
        }
        
        $date = Carbon::parse($date);
        
        foreach ($periodesBloquees as $periode) {
            if (isset($periode['debut']) && isset($periode['fin'])) {
                try {
                    $debut = Carbon::parse($periode['debut']);
                    $fin = Carbon::parse($periode['fin']);
                    
                    if ($date->between($debut, $fin)) {
                        return true;
                    }
                } catch (\Exception $e) {
                    continue;
                }
            }
        }
        
        return false;
    }
    
    public static function calculerJoursOuvres($debut, $fin)
    {
        return JoursFeriesService::calculerJoursOuvres($debut, $fin);
    }

    public static function prochainJourOuvrable($date)
    {
        $date = Carbon::parse($date)->copy();
        
        while (!self::estJourOuvrable($date) || self::estPeriodeBloquee($date)) {
            $date->addDay();
        }
        
        return $date;
    }
}