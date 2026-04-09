<?php

return [
    // Email destinataires des notifications RH / DG / secrétariat.
    // Ces valeurs peuvent être personnalisées dans le fichier .env.
    'email_secretaire' => env('COFIMA_EMAIL_SECRETAIRE', 'meguagie@cofima.cc'),
    'email_dg'        => env('COFIMA_EMAIL_DG', 'jmavande@cofima.cc'),
    'email_rh'        => env('COFIMA_EMAIL_RH', null),
];
