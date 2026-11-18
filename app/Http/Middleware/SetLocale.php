<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class SetLocale
{
    public function handle(Request $request, Closure $next)
    {
        $available = array_keys(config('lpadmin.languages', []));
        $locale = $request->session()->get('locale', $request->cookie('admin_locale', 'zh_CN'));
        if (!in_array($locale, $available, true)) {
            $locale = 'zh_CN';
        }
        App::setLocale($locale);

        return $next($request);
    }
}
