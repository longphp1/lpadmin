<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GenerateNextWebSiteEvent
{
    use Dispatchable, SerializesModels;

    public function __construct()
    {
    }
}
