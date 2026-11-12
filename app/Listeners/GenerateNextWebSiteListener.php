<?php

namespace App\Listeners;

use App\Events\GenerateNextWebSiteEvent;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class GenerateNextWebSiteListener
{
    public function handle(GenerateNextWebSiteEvent $event): void
    {
        Log::info('开始执行 generate:website_next');
        Artisan::call('generate:website_next');
        Log::info('generate:website_next 执行完成');
    }
}
