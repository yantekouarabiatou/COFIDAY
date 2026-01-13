<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class TestMail extends Command
{
    protected $signature = 'mail:test';
    protected $description = 'Test email configuration';

    public function handle()
    {
        try {
            Mail::raw('Test email from COFTIME', function ($message) {
                $message->to('adisiroko@gmail.com')
                        ->subject('Test Email Configuration');
            });

            $this->info('Email sent successfully!');
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
        }
    }
}
