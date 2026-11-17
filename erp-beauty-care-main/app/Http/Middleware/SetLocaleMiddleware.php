<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocaleMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->session()->get('locale');
        if (!in_array($locale, ['id', 'en'], true)) {
            $cookieLocale = $request->cookie('erp_locale');
            $locale = in_array($cookieLocale, ['id', 'en'], true) ? $cookieLocale : 'id';
        }

        App::setLocale($locale);

        return $next($request);
    }
}

