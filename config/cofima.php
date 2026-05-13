<?php

return [
    'email_secretaire' => array_map('trim', explode(',', env('COFIMA_EMAIL_SECRETAIRE', 'rabiatouyantekoua@gmail.com,isossa@cofima.cc,isossa@cofima.cc'))),
    'email_dg'         => array_map('trim', explode(',', env('COFIMA_EMAIL_DG', 'biroko@cofima.cc'))),
    'email_rh'         => env('COFIMA_EMAIL_RH', 'ryantekoua@cofima.cc'),
];

