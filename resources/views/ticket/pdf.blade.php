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
            font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
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
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            position: relative;
            border: 1px solid #e5e7eb;
        }

        /* Header */
        .header {
            background-color: #009aa6;
            background-image: linear-gradient(135deg, #00d285 0%, #009aa6 100%);
            color: #ffffff;
            padding: 0;
            border-bottom: 6px solid #f8c400;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
        }

        .header-left {
            padding: 30px 40px;
            width: 65%;
            vertical-align: middle;
        }

        .header-right {
            padding: 30px 40px;
            width: 35%;
            text-align: right;
            vertical-align: middle;
        }

        .logo-img {
            height: 50px;
            width: auto;
            background-color: white;
            padding: 6px;
            border-radius: 8px;
            margin-bottom: 12px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .event-title {
            font-size: 28px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin: 0;
            line-height: 1.1;
            color: white;
            text-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .event-subtitle {
            font-size: 13px;
            color: #fff9e6;
            font-weight: 600;
            margin-top: 6px;
            letter-spacing: 2px;
            text-transform: uppercase;
            opacity: 0.9;
        }

        .ticket-label {
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: rgba(255, 255, 255, 0.9);
            margin-bottom: 8px;
            font-weight: 600;
        }

        .category-badge {
            display: inline-block;
            background-color: #f8c400;
            color: #1a1a1a;
            padding: 10px 20px;
            border-radius: 12px;
            font-size: 18px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        /* Body Content */
        .body-content {
            padding: 40px;
            position: relative;
            background: #fff;
            z-index: 10;
        }

        /* Watermark */
        .watermark {
            position: absolute;
            bottom: -80px;
            right: -80px;
            width: 400px;
            height: 400px;
            opacity: 0.03;
            z-index: 0;
            color: #00d285;
            pointer-events: none;
        }

        .info-grid {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-bottom: 40px;
        }

        .info-left {
            width: 60%;
            vertical-align: top;
            padding-right: 40px;
        }

        .info-right {
            width: 40%;
            vertical-align: top;
        }

        .section-title {
            font-size: 12px;
            font-weight: 800;
            color: #9ca3af;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 20px;
            border-bottom: 2px solid #f3f4f6;
            padding-bottom: 8px;
            display: block;
        }

        /* Info Items */
        .info-item {
            margin-bottom: 24px;
        }

        .info-label {
            font-size: 11px;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 4px;
            font-weight: 600;
            display: block;
        }

        .info-value {
            font-size: 18px;
            font-weight: 700;
            color: #111827;
            line-height: 1.3;
            display: block;
        }

        .info-icon {
            display: inline-block;
            width: 20px;
            height: 20px;
            margin-right: 8px;
            vertical-align: bottom;
            color: #009aa6;
        }

        /* QR Box */
        .qr-box {
            background-color: #f0fdf9; /* very light mint */
            border: 2px solid #00d285;
            border-radius: 16px;
            padding: 24px;
            text-align: center;
        }

        .reg-label {
            font-size: 10px;
            color: #009aa6;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .reg-number {
            font-family: "Courier New", Courier, monospace;
            font-size: 24px;
            font-weight: 900;
            color: #111827;
            margin-bottom: 20px;
            display: block;
            letter-spacing: -1px;
            padding-bottom: 16px;
            border-bottom: 1px dashed #00d285;
        }

        .qr-img {
            display: block;
            margin: 0 auto;
            border-radius: 8px;
            padding: 8px;
            background: white;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }

        /* Dashed Divider */
        .divider {
            border-top: 2px dashed #e5e7eb;
            margin: 30px 0;
            position: relative;
        }

        .divider::before, .divider::after {
            content: "";
            position: absolute;
            background-color: #f3f4f6; /* matches body bg */
            width: 30px;
            height: 30px;
            border-radius: 50%;
            top: -16px;
        }

        .divider::before { left: -55px; }
        .divider::after { right: -55px; }

        /* Participants Table */
        .participants-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }

        .participants-table th {
            text-align: left;
            font-size: 11px;
            text-transform: uppercase;
            color: #6b7280;
            padding: 12px 16px;
            background-color: #f9fafb;
            font-weight: 700;
            letter-spacing: 1px;
            border-bottom: 1px solid #e5e7eb;
        }

        .participants-table td {
            padding: 16px;
            border-bottom: 1px solid #f3f4f6;
            vertical-align: middle;
            color: #374151;
        }

        .participants-table tr:last-child td {
            border-bottom: none;
        }

        .participants-table tr:nth-child(even) td {
            background-color: #f9fafb;
        }

        .number-badge {
            background-color: #009aa6;
            color: white;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            line-height: 24px;
            text-align: center;
            display: inline-block;
            font-size: 11px;
            font-weight: bold;
        }

        .participant-name {
            font-weight: 700;
            color: #111827;
            font-size: 14px;
        }

        .bib-container {
            text-align: right;
        }

        .bib-badge {
            background-color: #111827;
            color: #f8c400; /* fun-yellow */
            padding: 6px 12px;
            border-radius: 6px;
            font-family: "Courier New", monospace;
            font-weight: 900;
            font-size: 16px;
            letter-spacing: 1px;
            display: inline-block;
        }

        .no-bib {
            color: #9ca3af;
            font-style: italic;
            font-size: 12px;
        }

        /* Footer */
        .footer {
            background-color: #f9fafb;
            padding: 24px 40px;
            text-align: center;
            border-top: 1px solid #e5e7eb;
        }

        .footer p {
            font-size: 11px;
            color: #6b7280;
            line-height: 1.6;
            margin: 0;
            max-width: 80%;
            margin: 0 auto;
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
                            <div style="height: 15px;"></div>
                        @endif
                        <h1 class="event-title">{{ $registration->raceCategory->event->name ?? "UIGU FUN RUN" }}</h1>
                        <div class="event-subtitle">Official E-Ticket</div>
                    </td>
                    <td class="header-right">
                        <div class="ticket-label">Tiket Kategori</div>
                        <div class="category-badge">{{ $registration->raceCategory->name }}</div>
                    </td>
                </tr>
            </table>
        </div>

        <div class="body-content">
            <!-- Watermark -->
            <div class="watermark">
                <svg viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 14h2v2h-2zm0-10h2v8h-2z"/>
                </svg>
            </div>

            <!-- Top Section -->
            <table class="info-grid">
                <tr>
                    <td class="info-left">
                        <span class="section-title">Detail Acara</span>

                        <div class="info-item">
                            <span class="info-label">Tanggal Pelaksanaan</span>
                            <span class="info-value" style="color: #00d285;">
                                {{ $registration->raceCategory->event->date?->format("l, d F Y") ?? "To Be Announced" }}
                            </span>
                        </div>

                        <div class="info-item">
                            <span class="info-label">Waktu Start</span>
                            <span class="info-value">
                                {{ $registration->raceCategory->event->date?->format("H:i") ?? "TBA" }} WITA
                            </span>
                        </div>

                        <div class="info-item" style="margin-bottom: 0;">
                            <span class="info-label">Lokasi</span>
                            <span class="info-value" style="font-size: 16px;">
                                {{ $registration->raceCategory->event->location ?? "Universitas Ichsan Gorontalo Utara" }}
                            </span>
                        </div>
                    </td>
                    <td class="info-right">
                        <div class="qr-box">
                            <div class="reg-label">ID Registrasi</div>
                            <div class="reg-number">{{ $registration->registration_number }}</div>
                            <div class="qr-img">
                                <img src="data:image/png;base64,{{ base64_encode(\SimpleSoftwareIO\QrCode\Facades\QrCode::format("png")->size(150)->color(0, 50, 60)->generate($registration->registration_number)) }}" alt="QR Code" width="120">
                            </div>
                            <div style="font-size: 9px; color: #009aa6; margin-top: 12px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px;">Scan saat Registrasi Ulang</div>
                        </div>
                    </td>
                </tr>
            </table>

            <div class="divider"></div>

            <!-- Participants -->
            <span class="section-title">Peserta Terdaftar</span>
            <table class="participants-table">
                <thead>
                    <tr>
                        <th width="50" style="text-align: center">No</th>
                        <th>Nama Peserta</th>
                        <th width="100" style="text-align: center">Ukuran</th>
                        <th width="140" style="text-align: right">Nomor BIB</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($registration->participants as $index => $participant)
                        <tr>
                            <td style="text-align: center">
                                <span class="number-badge">{{ $index + 1 }}</span>
                            </td>
                            <td>
                                <div class="participant-name">{{ $participant->name }}</div>
                            </td>
                            <td style="text-align: center">
                                <span style="font-weight: bold; color: #6b7280;">{{ $participant->jersey_size }}</span>
                            </td>
                            <td class="bib-container">
                                @if($participant->bib_number)
                                    <span class="bib-badge">{{ $participant->bib_number }}</span>
                                @else
                                    <span class="no-bib">Belum Rilis</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="footer">
            <p>
                Harap membawa E-Ticket ini (cetak atau digital) dan kartu identitas asli saat pengambilan Race Pack.<br>
                Registrasi ulang tidak dapat diwakilkan tanpa surat kuasa bermaterai.
            </p>
        </div>
    </div>
</body>
</html>
