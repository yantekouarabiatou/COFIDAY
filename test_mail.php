<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Mail;

try {
    Mail::raw('Test de mail COFIDAY - ' . now()->format('Y-m-d H:i:s'), function($message) {
        $message->to('adisiroko@gmail.com')->subject('Test COFIDAY');
    });
    echo 'Mail envoyé avec succès à ' . now()->format('Y-m-d H:i:s');
} catch (Exception $e) {
    echo 'Erreur: ' . $e->getMessage();
}

