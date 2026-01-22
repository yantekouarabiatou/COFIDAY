<?php

namespace App\Helpers;

use Carbon\Carbon;
use App\Models\RegleConge;

class CongeHelper
{
    public static function estJourOuvrable($date)
    {
        $date = Carbon::parse($date);
        
        // Vérifier si c'est un week-end
        if ($date->isWeekend()) {
            return false;
        }
        
        // Vérifier si c'est un jour férié
        $regles = RegleConge::first();
        if ($regles && $regles->jours_feries) {
            $joursFeries = json_decode($regles->jours_feries, true);
            if (is_array($joursFeries)) {
                $dateStr = $date->format('m-d');
                foreach ($joursFeries as $jour) {
                    if (isset($jour['date']) && $jour['date'] === $dateStr) {
                        return false;
                    }
                }
            }
        }
        
        return true;
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
        $jours = 0;
        $current = Carbon::parse($debut)->copy();
        $fin = Carbon::parse($fin);
        
        while ($current->lte($fin)) {
            if (self::estJourOuvrable($current)) {
                $jours++;
            }
            $current->addDay();
        }
        
        return $jours;
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