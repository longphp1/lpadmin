<?php

namespace App\Http\Controllers\LPadmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LocaleController extends Controller
{
    public function switch(Request $request, string $locale): RedirectResponse
    {
        $available = array_keys(config('lpadmin.languages', []));
        abort_unless(in_array($locale, $available, true), 404);

        $request->session()->put('locale', $locale);
        cookie()->queue(cookie('admin_locale', $locale, 60 * 24 * 365));

        return redirect()->back(fallback: route('lpadmin.login'));
    }
}
