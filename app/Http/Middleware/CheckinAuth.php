<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckinAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->session()->has('checkin_authorized')) {
            return redirect()->route('checkin.login');
        }

        $setting = \App\Models\CheckinSetting::first();

        if (! $setting || ! $setting->is_active) {
            $request->session()->forget('checkin_authorized');

            return redirect()->route('checkin.login')
                ->withErrors(['system' => 'Check-in system has been disabled.']);
        }

        // Check check-in time window
        $now = now();
        if ($setting->checkin_start_time && $now->lt($setting->checkin_start_time)) {
            $request->session()->forget('checkin_authorized');

            return redirect()->route('checkin.login')
                ->withErrors(['time' => 'Check-in has not started yet. Start time: '.$setting->checkin_start_time->format('d M Y H:i')]);
        }

        if ($setting->checkin_end_time && $now->gt($setting->checkin_end_time)) {
            $request->session()->forget('checkin_authorized');

            return redirect()->route('checkin.login')
                ->withErrors(['time' => 'Check-in has ended. End time: '.$setting->checkin_end_time->format('d M Y H:i')]);
        }

        return $next($request);
    }
}
