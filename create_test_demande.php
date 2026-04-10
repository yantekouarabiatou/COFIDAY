<?php
require 'vendor/autoload.php';
\ = require 'bootstrap/app.php';
\ = \->make(Illuminate\Contracts\Console\Kernel::class);
\->bootstrap();

\ = App\Models\User::find(5);
\ = App\Models\DemandeDemission::create([
    'user_id' => \->id,
    'date_depart_souhaitee' => now()->addDays(30),
    'date_embauche' => now()->subYears(2),
    'lettre' => 'Test de démission pour débogage',
    'numero_reference' => App\Models\DemandeDemission::genererNumeroReference(),
    'statut' => 'en_attente'
]);
echo 'Demande créée avec ID: ' . \->id;

