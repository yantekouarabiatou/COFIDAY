<?php

use App\Services\JoursFeriesService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * 1. Congés spéciaux « Mariage » : le justificatif n'est plus obligatoire.
 * 2. Jours fériés : les fériés à date variable (Vodun days, Pentecôte, Tabaski,
 *    Maouloud…) saisis en 2026 étaient répétés chaque année à tort. Ils sont
 *    rattachés à 2026, et les Vodun days 2027 (jeudi 7 et vendredi 8 janvier)
 *    sont ajoutés. Le 1er janvier et le 25 décembre restent récurrents.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('types_conges')
            ->where('libelle', 'like', '%ariage%')
            ->update(['justificatif_requis' => false, 'updated_at' => now()]);

        $regle = DB::table('regles_conges')->where('id', 1)->first() ?? DB::table('regles_conges')->first();
        if (!$regle) {
            return;
        }

        $liste = JoursFeriesService::decoder($regle->jours_feries);

        foreach ($liste as &$f) {
            if ($f['annee'] !== null) {
                continue;
            }
            $nom = mb_strtolower($f['nom']);
            foreach (JoursFeriesService::MOTS_CLES_FERIES_MOBILES as $mc) {
                if (str_contains($nom, $mc)) {
                    $f['annee'] = 2026; // saisi pour 2026, ne vaut pas pour les années suivantes
                    break;
                }
            }
        }
        unset($f);

        $liste[] = ['nom' => 'Nouvel An', 'date' => '01-01', 'annee' => null];
        $liste[] = ['nom' => 'Noël',      'date' => '12-25', 'annee' => null];
        $liste[] = ['nom' => 'Vodun days', 'date' => '01-07', 'annee' => 2027];
        $liste[] = ['nom' => 'Vodun days', 'date' => '01-08', 'annee' => 2027];

        // Doublons (même date / même année) éliminés par normaliser() : la dernière occurrence
        // l'emporte, d'où l'inversion pour conserver le nom déjà saisi.
        $normalisee = JoursFeriesService::normaliser(array_reverse($liste));

        DB::table('regles_conges')->where('id', $regle->id)->update([
            // même format de stockage que l'écran d'administration (JSON encodé deux fois via le cast array)
            'jours_feries' => json_encode(json_encode($normalisee, JSON_UNESCAPED_UNICODE)),
            'updated_at'   => now(),
        ]);

        JoursFeriesService::viderCache();
    }

    public function down(): void
    {
        DB::table('types_conges')
            ->where('libelle', 'like', '%ariage%')
            ->update(['justificatif_requis' => true]);
    }
};
