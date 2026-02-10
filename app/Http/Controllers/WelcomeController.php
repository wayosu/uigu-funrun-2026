<?php

namespace App\Http\Controllers;

use App\Actions\Registration\ValidateAvailableSlotsAction;
use App\Models\Event;
use Illuminate\View\View;

class WelcomeController extends Controller
{
    public function index(ValidateAvailableSlotsAction $validateSlots): View
    {
        $event = Event::where('is_active', true)->with('raceCategories')->first();

        $categoryStates = [];
        if ($event) {
            foreach ($event->raceCategories as $category) {
                // Default state
                $state = 'open';
                $label = 'Daftar Kategori Ini';
                $disabled = false;

                // Check dates
                if ($category->registration_open_at && now()->isBefore($category->registration_open_at)) {
                    $state = 'coming_soon';
                    $label = 'Dibuka '.$category->registration_open_at->locale('id')->isoFormat('D MMM HH:mm');
                    $disabled = true;
                } elseif ($category->registration_close_at && now()->isAfter($category->registration_close_at)) {
                    $state = 'closed';
                    $label = 'Pendaftaran Ditutup';
                    $disabled = true;
                }
                // Check slots (only if not already closed/coming soon)
                elseif ($validateSlots->getAvailableSlots($category) <= 0) {
                    $state = 'sold_out';
                    $label = 'Habis Terjual';
                    $disabled = true;
                }

                $categoryStates[$category->id] = [
                    'state' => $state,
                    'label' => $label,
                    'disabled' => $disabled,
                ];
            }
        }

        return view('welcome', compact('event', 'categoryStates'));
    }
}
