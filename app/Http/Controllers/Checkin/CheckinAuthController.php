<?php

namespace App\Http\Controllers\Checkin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CheckinAuthController extends Controller
{
    public function login()
    {
        return view('checkin.login');
    }

    public function authenticate(Request $request)
    {
        $request->validate([
            'pin' => 'required|string',
        ]);

        $setting = \App\Models\CheckinSetting::first();

        if (! $setting || ! $setting->is_active) {
            return back()->withErrors(['pin' => 'Check-in system is currently disabled.']);
        }

        if ($request->pin === $setting->pin_code) {
            $request->session()->put('checkin_authorized', true);

            return redirect()->route('checkin.scan');
        }

        return back()->withErrors(['pin' => 'Invalid PIN code.']);
    }

    public function logout(Request $request)
    {
        $request->session()->forget('checkin_authorized');

        return redirect()->route('checkin.login');
    }
}
