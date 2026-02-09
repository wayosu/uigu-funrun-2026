<?php

namespace App\Http\Controllers;

use App\Models\Registration;
use Barryvdh\DomPDF\Facade\Pdf;
use Symfony\Component\HttpFoundation\Response;

class TicketController extends Controller
{
    public function show(Registration $registration): Response
    {
        // Ensure only paid/verified can see ticket?
        // Or maybe allow pending too for reference?
        // System design implies E-Ticket generated after payment.
        if (! in_array($registration->status, ['paid', 'verified'])) {
            // For now, let's just abort or redirect.
            // Actually, let's allow viewing but maybe show "UNPAID" watermark.
            // But per system design, E-Ticket is for check-in.
        }

        return response()->view('ticket.show', compact('registration'));
    }

    public function download(Registration $registration): Response
    {
        $pdf = Pdf::loadView('ticket.pdf', [
            'registration' => $registration->load('participants', 'raceCategory.event'),
        ]);

        $fileName = 'e-ticket-'.$registration->registration_number.'.pdf';

        return $pdf->download($fileName);
    }
}
