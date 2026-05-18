<?php

return [
    'email_secretaire' => array_map('trim', explode(',', env('COFIMA_EMAIL_SECRETAIRE', 'rabiatouyantekoua@gmail.com,postsmartia@gmail.com'))),
    'email_dg'         => array_map('trim', explode(',', env('COFIMA_EMAIL_DG', 'biroko@cofima.cc'))),
    'email_rh'         => env('COFIMA_EMAIL_RH', 'ryantekoua@cofima.cc'),
];
