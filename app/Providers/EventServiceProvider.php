<?php

namespace App\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use App\Events\ModelActivityEvent;
use App\Listeners\SendActivityNotification;
use Illuminate\Support\Facades\Log;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        ModelActivityEvent::class => [
            SendActivityNotification::class,
        ],
    ];

    public function boot()
    {
        parent::boot();

        // Log de débogage
        Log::info('EventServiceProvider chargé', [
            'events' => array_keys($this->listen),
            'time' => now()
        ]);

        // Vérifier que l'événement est bien enregistré
        Event::listen('*', function ($eventName, $payload) {
            // Log pour déboguer tous les événements
            if (str_contains($eventName, 'ModelActivity')) {
                Log::debug('Événement ModelActivity détecté', [
                    'event' => $eventName,
                    'payload_count' => count($payload)
                ]);
            }
        });
    }
}
