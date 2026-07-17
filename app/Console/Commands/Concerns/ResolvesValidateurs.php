<?php

namespace App\Console\Commands\Concerns;

use App\Models\User;
use Illuminate\Support\Collection;

trait ResolvesValidateurs
{
    /**
     * Destinataires des relances de validation : utilisateurs ayant le rôle Spatie
     * admin / rh / directeur-general, UNION emails configurés dans config/cofima.php
     * (email_secretaire, email_dg, email_rh) — utile car le secrétariat n'a pas de
     * rôle Spatie dédié, et personne ne porte forcément le rôle "rh". Dédupliqué par email.
     */
    private function getValidateurs(): Collection
    {
        $destinataires = User::whereHas('roles', function ($q) {
            $q->whereIn('name', ['directeur-general', 'rh', 'admin']);
        })->get(['id', 'prenom', 'nom', 'email'])->keyBy(fn ($u) => strtolower($u->email));

        $groupes = [
            'Secrétariat'        => (array) config('cofima.email_secretaire'),
            'Direction Générale' => (array) config('cofima.email_dg'),
            'RH'                 => [config('cofima.email_rh')],
        ];

        foreach ($groupes as $label => $emails) {
            foreach ($emails as $email) {
                $email = trim((string) $email);
                $key   = strtolower($email);

                if ($email === '' || $destinataires->has($key)) {
                    continue;
                }

                $destinataires->put($key, User::where('email', $email)->first(['id', 'prenom', 'nom', 'email'])
                    ?? (object) ['prenom' => $label, 'nom' => '', 'email' => $email]);
            }
        }

        return $destinataires->values();
    }
}
