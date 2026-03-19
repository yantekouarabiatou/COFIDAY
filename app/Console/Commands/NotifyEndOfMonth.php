<?php
namespace App\Console\Commands;

use App\Models\User;
use App\Notifications\FinDeMoisNotification;
use Carbon\Carbon;
use Illuminate\Console\Command;

class NotifyEndOfMonth extends Command
{
    protected $signature   = 'notify:fin-de-mois';
    protected $description = 'Envoie une notification de validation à la fin du mois';

    public function handle()
    {
        User::where('is_active', 1)->each(function ($user) {
            $user->notify(new FinDeMoisNotification(Carbon::now()->month));
        });

        $this->info('Notifications envoyées.');
    }
}
