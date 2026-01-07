<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class SoldesCongesSeeder extends Seeder
{
    public function run(): void
    {
        $annee = now()->year;

        User::all()->each(function ($user) use ($annee) {
            DB::table('soldes_conges')->insert([
                'user_id' => $user->id,
                'annee' => $annee,
                'jours_acquis' => 0,
                'jours_pris' => 0,
                'jours_restants' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });
    }
}
