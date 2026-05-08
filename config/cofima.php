<?php

return [
    'email_secretaire' => array_map('trim', explode(',', env('COFIMA_EMAIL_SECRETAIRE', 'agodjie83@gmail.com,isossa@cofima.cc'))),
    'email_dg'         => array_map('trim', explode(',', env('COFIMA_EMAIL_DG', 'rabiatouyantekoua@gmail.com,postsmartia@gmail.com'))),
    'email_rh'         => env('COFIMA_EMAIL_RH', 'cofima@cofimabenin.cc'),
];

