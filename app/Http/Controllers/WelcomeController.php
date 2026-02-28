<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\View\View;

class WelcomeController extends Controller
{
    public function index(): View
    {
        $event = Event::where('is_active', true)->with('raceCategories')->first();

        $categoryStates = [];
        if ($event) {
            foreach ($event->raceCategories as $category) {
                // Default state
                $state = 'open';
                $label = 'Daftar Kategori Ini';
                $disabled = false;

                // Check dates only - no quota check as per client request
                if ($category->registration_open_at && now()->isBefore($category->registration_open_at)) {
                    $state = 'coming_soon';
                    $label = 'Dibuka '.$category->registration_open_at->locale('id')->isoFormat('D MMM HH:mm');
                    $disabled = true;
                } elseif ($category->registration_close_at && now()->isAfter($category->registration_close_at)) {
                    $state = 'closed';
                    $label = 'Pendaftaran Ditutup';
                    $disabled = true;
                }

                $categoryStates[$category->id] = [
                    'state' => $state,
                    'label' => $label,
                    'disabled' => $disabled,
                ];
            }

            // Sort categories so closed/disabled ones appear last
            $event->setRelation(
                'raceCategories',
                $event->raceCategories->sortBy(
                    fn ($category) => $categoryStates[$category->id]['disabled'] ? 1 : 0
                )->values()
            );
        }

        return view('welcome', compact('event', 'categoryStates'));
    }
}
