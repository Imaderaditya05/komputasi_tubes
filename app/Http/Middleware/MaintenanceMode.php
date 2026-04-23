<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

class MaintenanceMode
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! Schema::hasTable('settings')) {
            return $next($request);
        }

        $maintenance = (bool) Setting::getValue('maintenance_mode', false);
        if (! $maintenance) {
            return $next($request);
        }

        if ($request->routeIs('admin.*') || $request->routeIs('login.admin') || $request->routeIs('login.admin.submit')) {
            return $next($request);
        }

        if ($request->is('admin') || $request->is('admin/*')) {
            return $next($request);
        }

        if (Auth::check() && Auth::user()->role === 'admin') {
            return $next($request);
        }

        return response()
            ->view('surprisebite.maintenance', [], 503);
    }
}
