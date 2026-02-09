<?php

namespace App\Exports;

use App\Models\JerseySize;
use App\Models\Participant;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ParticipantsExport implements FromQuery, WithHeadings, WithMapping, WithStyles, WithTitle
{
    protected ?Builder $query = null;

    public function __construct(?Builder $query = null)
    {
        $this->query = $query;
    }

    public function query()
    {
        $query = $this->query ?? Participant::query();

        return $query->with([
            'registration.raceCategory.event',
            'registration.payments',
            'checkins',
        ])->orderBy('created_at', 'desc');
    }

    public function headings(): array
    {
        return [
            'Registration Number',
            'Event Name',
            'Race Category',
            'Distance',
            'Registration Type',
            'BIB Number',
            'Is PIC',
            'Full Name',
            'Email Address',
            'Phone Number',
            'Gender',
            'Birth Date',
            'Age',
            'Identity Number',
            'Blood Type',
            'Jersey Size',
            'Emergency Contact',
            'Payment Status',
            'Total Amount',
            'Payment Expiry',
            'Check-in Status',
            'Check-in Time',
            'Registered At',
        ];
    }

    public function map($participant): array
    {
        $registration = $participant->registration;
        $raceCategory = $registration?->raceCategory;
        $event = $raceCategory?->event;

        $jerseySize = $participant->jersey_size
            ? (JerseySize::query()->where('code', $participant->jersey_size)->first()?->name ?? strtoupper($participant->jersey_size))
            : '-';

        $latestCheckin = $participant->checkins()->latest()->first();

        return [
            $registration?->registration_number ?? '-',
            $event?->name ?? '-',
            $raceCategory?->name ?? '-',
            $raceCategory?->distance ?? '-',
            $registration?->registration_type?->label() ?? '-',
            $participant->bib_number ?? '-',
            $participant->is_pic ? 'Yes' : 'No',
            $participant->name,
            $participant->email,
            $participant->phone,
            $participant->gender?->label() ?? '-',
            $participant->birth_date?->format('d/m/Y') ?? '-',
            $participant->birth_date ? $participant->getAge().' years' : '-',
            $participant->identity_number ?? '-',
            $participant->blood_type ?? '-',
            $jerseySize,
            $participant->emergency_contact ?? '-',
            $registration?->status?->label() ?? '-',
            $registration?->total_amount ? 'Rp '.number_format((float) $registration->total_amount, 0, ',', '.') : '-',
            $registration?->expired_at?->format('d/m/Y H:i') ?? '-',
            $participant->isCheckedIn() ? 'Checked In' : 'Not Checked In',
            $latestCheckin?->checked_in_at?->format('d/m/Y H:i:s') ?? '-',
            $participant->created_at?->format('d/m/Y H:i:s') ?? '-',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                    'size' => 12,
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '009AA6'],
                ],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                ],
            ],
        ];
    }

    public function title(): string
    {
        return 'Participants';
    }
}
