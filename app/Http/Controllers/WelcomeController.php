<?php

namespace App\Http\Controllers;

class WelcomeController extends Controller
{
    public function index()
    {
        $event = \App\Models\Event::where('is_active', true)->with('raceCategories')->first();

        return view('welcome', compact('event'));
    }
}
