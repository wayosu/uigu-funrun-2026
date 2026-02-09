<?php

namespace App\Http\Controllers\Checkin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CheckinController extends Controller
{
    public function index()
    {
        $setting = \App\Models\CheckinSetting::first();

        if (! $setting || ! $setting->is_active) {
            return redirect()->route('checkin.login')
                ->withErrors(['system' => 'Check-in system is currently disabled.']);
        }

        // Check check-in time window
        $now = now();
        if ($setting->checkin_start_time && $now->lt($setting->checkin_start_time)) {
            return redirect()->route('checkin.login')
                ->withErrors(['time' => 'Check-in has not started yet. Start time: '.$setting->checkin_start_time->format('d M Y H:i')]);
        }

        if ($setting->checkin_end_time && $now->gt($setting->checkin_end_time)) {
            return redirect()->route('checkin.login')
                ->withErrors(['time' => 'Check-in has ended. End time: '.$setting->checkin_end_time->format('d M Y H:i')]);
        }

        return view('checkin.scan', compact('setting'));
    }

    public function verify(Request $request)
    {
        $setting = \App\Models\CheckinSetting::first();

        if (! $setting || ! $setting->is_active) {
            return response()->json([
                'status' => 'error',
                'message' => 'Check-in system is currently disabled.',
            ], 403);
        }

        // Check check-in time window
        $now = now();
        if ($setting->checkin_start_time && $now->lt($setting->checkin_start_time)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Check-in has not started yet. Start time: '.$setting->checkin_start_time->format('d M Y H:i'),
            ], 403);
        }

        if ($setting->checkin_end_time && $now->gt($setting->checkin_end_time)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Check-in has ended. End time: '.$setting->checkin_end_time->format('d M Y H:i'),
            ], 403);
        }

        $request->validate([
            'qr_content' => 'required|string',
        ]);

        $registrationNumber = $request->qr_content;

        $registration = \App\Models\Registration::where('registration_number', $registrationNumber)
            ->with(['participants', 'raceCategory', 'payment'])
            ->first();

        if (! $registration) {
            return response()->json([
                'status' => 'error',
                'message' => 'Registration not found.',
            ], 404);
        }

        // Check Payment Status
        if (! in_array($registration->status, ['paid', 'verified'])) {
            return response()->json([
                'status' => 'error',
                'message' => 'Registration STATUS: '.strtoupper($registration->status).'. Payment verification required.',
            ], 400);
        }

        // Check if already checked in
        // A registration can have multiple participants.
        // But usually check-in is per participant or per registration (picking up all packs).
        // System design: "Scan QR E-Ticket -> Validasi peserta -> Tandai sudah ambil race pack".
        // E-Ticket is per registration. So we mark all participants as checked in? Or one by one?
        // Let's assume one scan = Check-in for ALL participants in that registration (Collective pickup).
        // OR return participant list and let volunteer tick them?
        // Simpler: Mark Registration as Checked-in.
        // But Checkin model links to Participant.

        // Let's Return Data to UI first, let volunteer confirm or Auto confirm?
        // "Scan QR -> Validasi -> Tandai".
        // Let's do: Scan -> Show Info -> Click "Check-in All" or "Check-in Selected".
        // API verify just returns info.
        // API checkin does the action.

        // Re-reading plan: "verify: Find reg, check status... If valid: Create Checkin record".
        // So scan = auto checkin? Risk of accidental scan.
        // Better: Verify returns info. Confirm endpoint performs checkin.
        // But Plan says: "If valid: Create Checkin record, return Success".
        // Let's stick to plan for simplicity but maybe adding a "dry_run" flag or similar?
        // Let's follow plan: Verify = Action.

        // Check if ANY participant already checked in?
        $checkedInCount = $registration->participants()->whereHas('checkins')->count();
        $totalParticipants = $registration->participants()->count();

        // Check duplicate scan setting
        if (! $setting->allow_duplicate_scan && $checkedInCount >= $totalParticipants) {
            return response()->json([
                'status' => 'error',
                'message' => 'All participants in this registration have already checked in.',
                'data' => $registration,
            ], 400);
        }

        // If photo verification is required, return data for frontend to capture photo
        if ($setting->require_photo_verification && ! $request->has('photo')) {
            return response()->json([
                'status' => 'pending_photo',
                'message' => 'Photo verification required. Please capture participant photo.',
                'data' => $registration,
                'participants' => $registration->participants,
                'require_photo' => true,
            ]);
        }

        // Perform Check-in
        \Illuminate\Support\Facades\DB::transaction(function () use ($registration, $request, $setting) {
            foreach ($registration->participants as $participant) {
                // Check if this specific participant checked in?
                if (! $participant->checkins()->exists() || $setting->allow_duplicate_scan) {
                    $checkinData = [
                        'registration_id' => $registration->id,
                        'checkin_at' => now(),
                        'checked_in_by' => auth()->id() ?? null,
                    ];

                    // Store photo if provided
                    if ($request->has('photo') && $setting->require_photo_verification) {
                        // Photo should be stored via file upload or base64
                        // For now, we'll store the path/reference if it exists
                        $checkinData['photo_path'] = $request->photo;
                    }

                    $participant->checkins()->create($checkinData);
                }
            }
        });

        $response = [
            'status' => 'success',
            'message' => 'Check-in Successful!',
            'data' => $registration,
            'participants' => $registration->participants,
        ];

        // Add print instruction if auto_print_bib is enabled
        if ($setting->auto_print_bib) {
            $response['auto_print'] = true;
            $response['print_data'] = [
                'registration_number' => $registration->registration_number,
                'participants' => $registration->participants->map(fn ($p) => [
                    'name' => $p->full_name,
                    'bib_number' => $p->bib_number,
                    'category' => $registration->raceCategory->name,
                ]),
            ];
        }

        return response()->json($response);
    }
}
