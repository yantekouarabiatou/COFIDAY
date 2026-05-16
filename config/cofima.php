<?php

return [
    'email_secretaire' => array_map('trim', explode(',', env('COFIMA_EMAIL_SECRETAIRE', 'cofima@cofimabenin.com','cofima@cofima.cc'))),
    'email_dg'         => array_map('trim', explode(',', env('COFIMA_EMAIL_DG', 'jcavande@cofimabenin.com','jcavande@cofima.cc'))),
    'email_rh'         => env('COFIMA_EMAIL_RH', 'cofima@cofimabenin.com'),
];

