<?php
require 'vendor/autoload.php';
\ = require 'bootstrap/app.php';
\ = \->make(Illuminate\Contracts\Console\Kernel::class);
\->bootstrap();

use Illuminate\Support\Facades\Mail;

try {
    Mail::raw('Test de mail COFTIME - ' . now()->format('Y-m-d H:i:s'), function(\) {
        \->to('adisiroko@gmail.com')->subject('Test COFTIME');
    });
    echo 'Mail envoyé avec succès à ' . now()->format('Y-m-d H:i:s');
} catch (Exception \) {
    echo 'Erreur: ' . \->getMessage();
}

