<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Charge l'utilisateur 1 et envoie une notification de test via NotificationService
$user = App\Models\User::find(1);
if (! $user) {
    echo "User #1 not found\n";
    exit(1);
}

app(App\Services\NotificationService::class)->notifyUser($user, 'test_type', ['message' => 'Test via script']);

echo "Notification created\n";
