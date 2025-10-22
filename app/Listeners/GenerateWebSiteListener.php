<?php

namespace App\Listeners;

use App\Events\GenerateWebSiteEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Artisan;

class GenerateWebSiteListener
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(GenerateWebSiteEvent $event): void
    {
        Artisan::call('generate:website_new');
    }
}
