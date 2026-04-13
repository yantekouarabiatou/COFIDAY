<?php

use Illuminate\Notifications\Notification;

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = App\Models\User::find(1);
if (! $user) {
    echo "User #1 not found\n";
    exit(1);
}

$notifications = Notification::where('notifiable_type', get_class($user))
    ->where('notifiable_id', $user->id)
    ->orderBy('created_at', 'desc')
    ->take(5)
    ->get();

foreach ($notifications as $n) {
    echo "ID: {$n->id} | type: {$n->type} | read_at: {$n->read_at} | message: " . (\json_encode($n->data) ?: '-') . "\n";
}

if ($notifications->isEmpty()) echo "No notifications found\n";
