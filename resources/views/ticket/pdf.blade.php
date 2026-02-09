<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>E-Ticket - {{ $registration->registration_number }}</title>
    <style>
        :root {
            --fun-green: #00d285;
            --fun-teal: #009aa6;
            --fun-yellow: #f8c400;
            --fun-dark: #1a1a1a;
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            --gray-500: #6b7280;
            --gray-800: #1f2937;
            --text-white: #ffffff;
        }

        @page {
            margin: 0;
            padding: 0;
        }

        body {
            font-family: "DejaVu Sans", sans-serif;
            background-color: #f3f4f6;
            margin: 0;
            padding: 40px;
            color: #1f2937;
        }

        .ticket-container {
            max-width: 800px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            position: relative;
            border: 1px solid #e5e7eb;
        }

        /* Watermark */
        .watermark {
            position: absolute;
            bottom: -50px;
            right: -50px;
            width: 300px;
            height: 300px;
            opacity: 0.04;
            z-index: 0;
            color: #009aa6;
        }

        /* Header */
        .header {
            background-color: #009aa6; /* fun-teal */
            color: #ffffff;
            padding: 0;
            border-bottom: 5px solid #f8c400; /* fun-yellow */
            position: relative;
            overflow: hidden;
        }
        
        /* Simulating gradient via overlaid partial transparent blocks if needed, 
           but solid color is safer for PDF. 
           Let's just use the primary brand color. */

        .header-table {
            width: 100%;
            border-collapse: collapse;
        }

        .header-left {
            padding: 24px 32px;
            width: 60%;
            vertical-align: middle;
        }

        .header-right {
            padding: 24px 32px;
            width: 40%;
            text-align: right;
            vertical-align: middle;
            background-color: rgba(255, 255, 255, 0.1);
        }

        .logo-img {
            height: 48px;
            width: auto;
            background-color: white;
            padding: 4px;
            border-radius: 6px;
        }

        .event-title {
            font-size: 26px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin: 0;
            line-height: 1.2;
            color: white;
        }

        .event-subtitle {
            font-size: 13px;
            color: #fee60d; /* Light yellow text */
            font-weight: bold;
            margin-top: 4px;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .ticket-label {
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: rgba(255, 255, 255, 0.9);
            margin-bottom: 6px;
        }

        .category-badge {
            display: inline-block;
            background-color: #f8c400; /* fun-yellow */
            color: #1f2937;
            padding: 8px 16px;
            border-radius: 50px; /* Pillow shape */
            font-size: 16px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* Body Content */
        .body-content {
            padding: 32px;
            position: relative;
            z-index: 10;
        }

        .info-grid {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-bottom: 32px;
        }

        .info-left {
            width: 65%;
            vertical-align: top;
            padding-right: 24px;
        }

        .info-right {
            width: 35%;
            vertical-align: top;
        }

        .section-title {
            font-size: 14px;
            font-weight: bold;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 16px;
            border-left: 4px solid #00d285; /* fun-green */
            padding-left: 10px;
        }

        /* icons row */
        .icon-row {
            margin-bottom: 16px;
        }

        .icon-table {
            width: 100%;
            border-collapse: collapse;
        }

        .icon-cell {
            width: 24px;
            vertical-align: top;
            padding-top: 2px;
        }

        .text-cell {
            padding-left: 12px;
            vertical-align: top;
        }

        .icon-svg {
            width: 18px;
            height: 18px;
            color: #009aa6; /* fun-teal */
        }

        .info-text-label {
            font-size: 10px;
            color: #9ca3af;
            display: block;
            margin-bottom: 2px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .info-text-value {
            font-size: 14px;
            font-weight: bold;
            color: #111827;
        }

        /* QR Box */
        .qr-box {
            background-color: #f9fafb;
            border: 2px dashed #d1d5db;
            border-radius: 12px;
            padding: 24px 16px;
            text-align: center;
        }

        .reg-label {
            font-size: 9px;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 6px;
        }

        .reg-number {
            font-family: "Courier New", Courier, monospace;
            font-size: 22px;
            font-weight: 800;
            color: #009aa6;
            margin-bottom: 16px;
            display: block;
            letter-spacing: -0.5px;
        }

        .qr-img {
            display: block;
            margin: 0 auto;
            border: 4px solid white;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
        }

        /* Dashed Divider */
        .divider {
            border-top: 2px dashed #e5e7eb;
            margin: 10px 0 32px 0;
            position: relative;
        }

        .divider::before, .divider::after {
            content: "";
            position: absolute;
            background-color: #f3f4f6; /* matches body bg */
            width: 24px;
            height: 24px;
            border-radius: 50%;
            top: -13px;
        }

        .divider::before { left: -44px; }
        .divider::after { right: -44px; }

        /* Participants Table */
        .participants-table {
            width: 100%;
            border-collapse: collapse;
        }

        .participants-table th {
            text-align: left;
            font-size: 10px;
            text-transform: uppercase;
            color: #6b7280;
            padding: 10px 12px;
            border-bottom: 2px solid #e5e7eb;
            font-weight: bold;
            letter-spacing: 0.5px;
        }

        .participants-table td {
            padding: 14px 12px;
            border-bottom: 1px solid #f3f4f6;
            vertical-align: middle;
        }

        .participants-table tr:last-child td {
            border-bottom: none;
        }

        .avatar-circle {
            width: 24px;
            height: 24px;
            background-color: #00d285; /* fun-green */
            color: white;
            border-radius: 50%;
            text-align: center;
            font-size: 11px;
            font-weight: bold;
            display: inline-block;
            line-height: 24px; 
            margin-right: 8px;
        }

        .participant-name {
            font-size: 13px;
            font-weight: bold;
            color: #1f2937;
        }

        .participant-meta {
            font-size: 11px;
            color: #6b7280;
        }

        .bib-badge {
            background-color: #f3f4f6;
            border: 1px solid #e5e7eb;
            color: #1a1a1a;
            padding: 4px 8px;
            border-radius: 4px;
            font-family: "Courier New", monospace;
            font-weight: bold;
            font-size: 14px;
        }

        /* Footer */
        .footer {
            background-color: #f9fafb;
            padding: 20px 32px;
            text-align: center;
            border-top: 1px solid #e5e7eb;
        }

        .footer-text {
            font-size: 10px;
            color: #9ca3af;
            line-height: 1.5;
            margin: 0;
        }
    </style>
</head>
<body>
    <div class="ticket-container">
        <!-- Header -->
        <div class="header">
            <table class="header-table">
                <tr>
                    <td class="header-left">
                        @if(file_exists(public_path("assets/logo-event-1.png")))
                            <img src="{{ public_path("assets/logo-event-1.png") }}" class="logo-img" alt="Logo">
                            <div style="height: 12px;"></div>
                        @else
                            {{-- Text fallback if logo missing in PDF render --}}
                        @endif
                        <h1 class="event-title">{{ $registration->raceCategory->event->name ?? "UIGU FUN RUN" }}</h1>
                        <div class="event-subtitle">Official E-Ticket</div>
                    </td>
                    <td class="header-right">
                        <div class="ticket-label">Kategori</div>
                        <div class="category-badge">{{ $registration->raceCategory->name }}</div>
                    </td>
                </tr>
            </table>
        </div>

        <div class="body-content">
            <!-- Watermark SVG -->
            <div class="watermark">
                <svg viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 14h2v2h-2zm0-10h2v8h-2z"/>
                </svg>
            </div>

            <!-- Top Section -->
            <table class="info-grid">
                <tr>
                    <td class="info-left">
                        <div class="section-title">Detail Acara</div>

                        <div class="icon-row">
                            <table class="icon-table">
                                <tr>
                                    <td class="icon-cell">
                                        <!-- Calendar Icon -->
                                        <svg class="icon-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                                    </td>
                                    <td class="text-cell">
                                        <span class="info-text-label">TANGGAL</span>
                                        <span class="info-text-value">{{ $registration->raceCategory->event->date?->format("d F Y") ?? "To Be Announced" }}</span>
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <div class="icon-row">
                            <table class="icon-table">
                                <tr>
                                    <td class="icon-cell">
                                        <!-- Clock Icon -->
                                        <svg class="icon-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                                    </td>
                                    <td class="text-cell">
                                        <span class="info-text-label">WAKTU</span>
                                        <span class="info-text-value">{{ $registration->raceCategory->event->date?->format("H:i") ?? "TBA" }} WITA</span>
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <div class="icon-row">
                            <table class="icon-table">
                                <tr>
                                    <td class="icon-cell">
                                        <!-- Map Pin Icon -->
                                        <svg class="icon-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                                    </td>
                                    <td class="text-cell">
                                        <span class="info-text-label">LOKASI</span>
                                        <span class="info-text-value">{{ $registration->raceCategory->event->location ?? "Universitas Ichsan Gorontalo Utara" }}</span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </td>
                    <td class="info-right">
                        <div class="qr-box">
                            <div class="reg-label">ID REGISTRASI</div>
                            <div class="reg-number">{{ $registration->registration_number }}</div>
                            <div class="qr-img">
                                <img src="data:image/png;base64,{{ base64_encode(\SimpleSoftwareIO\QrCode\Facades\QrCode::format("png")->size(120)->generate($registration->registration_number)) }}" alt="QR Code" width="100">
                            </div>
                        </div>
                    </td>
                </tr>
            </table>

            <div class="divider"></div>

            <!-- Participants -->
            <div class="section-title">Peserta Lari</div>
            <table class="participants-table">
                <thead>
                    <tr>
                        <th width="40">#</th>
                        <th>Nama</th>
                        <th width="80" style="text-align: right;">Size</th>
                        <th width="120" style="text-align: right;">No. BIB</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($registration->participants as $index => $participant)
                        <tr>
                            <td>
                                <span class="avatar-circle">{{ $index + 1 }}</span>
                            </td>
                            <td>
                                <div class="participant-name">{{ $participant->name }}</div>
                            </td>
                            <td style="text-align: right;">
                                <span class="participant-meta" style="text-transform: uppercase">{{ $participant->jersey_size }}</span>
                            </td>
                            <td style="text-align: right;">
                                <span class="bib-badge">{{ $participant->bib_number ?? "TBA" }}</span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="footer">
            <p class="footer-text">
                Tunjukkan E-Ticket ini saat pengambilan Race Pack di meja registrasi ulang.<br> 
                &copy; {{ date('Y') }} Universitas Ichsan Gorontalo Utara. All rights reserved.
            </p>
        </div>
    </div>
</body>
</html>
