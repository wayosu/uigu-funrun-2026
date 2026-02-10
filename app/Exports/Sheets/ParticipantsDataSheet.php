<?php

namespace App\Exports\Sheets;

use App\Models\JerseySize;
use App\Models\Participant;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ParticipantsDataSheet implements FromQuery, ShouldAutoSize, WithColumnFormatting, WithEvents, WithHeadings, WithMapping, WithStyles, WithTitle
{
    protected ?Builder $query = null;

    protected string $exportMode = 'all';

    public function __construct(?Builder $query = null, string $exportMode = 'all')
    {
        $this->query = $query;
        $this->exportMode = $exportMode;
    }

    public function query()
    {
        $query = $this->query ?? Participant::query();

        // Apply export mode filter
        if ($this->exportMode === 'new_only') {
            $query->notExported();
        } elseif ($this->exportMode === 'updated_since_last') {
            $query->newOrUpdatedSinceLastExport();
        }

        return $query->with([
            'registration.raceCategory.event',
            'registration.payments',
            'checkins',
        ])->orderBy('created_at', 'desc');
    }

    public function headings(): array
    {
        return [
            // Registration Info
            'Registration Number',
            'Event Name',
            'Event Date',
            'Event Location',
            'Race Category',
            'Distance',
            'Registration Type',

            // Participant Info
            'BIB Number',
            'Is PIC',
            'Full Name',
            'BIB Name',
            'Email Address',
            'Phone Number',
            'Gender',
            'Birth Date',
            'Age',
            'Identity Number',
            'Jersey Size',

            // Emergency Contact
            'Emergency Contact Name',
            'Emergency Contact Phone',
            'Emergency Contact Relation',

            // Payment Info
            'Payment Status',
            'Total Amount (IDR)',
            'Payment Date',
            'Payment Method',
            'Payment Expiry',

            // Check-in Info
            'Check-in Status',
            'Check-in Time',

            // Timestamps
            'Registered At',
            'Last Updated',

            // Export Tracking
            'Last Exported At',
            'Export Count',
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

        // Get payment info
        $verifiedPayment = $registration?->payments()
            ->whereNotNull('verified_at')
            ->latest('verified_at')
            ->first();

        return [
            // Registration Info
            $registration?->registration_number ?? '-',
            $event?->name ?? '-',
            $event?->date?->format('d/m/Y') ?? '-',
            $event?->location ?? '-',
            $raceCategory?->name ?? '-',
            $raceCategory?->distance ?? '-',
            $registration?->registration_type?->label() ?? '-',

            // Participant Info
            $participant->bib_number ?? '-',
            $participant->is_pic ? 'Yes' : 'No',
            $participant->name,
            $participant->bib_name ?? '-',
            $participant->email ?? '-',
            $participant->phone,
            $participant->gender?->label() ?? '-',
            $participant->birth_date?->format('d/m/Y') ?? '-',
            $participant->birth_date ? $participant->getAge() : '-',
            $participant->identity_number ?? '-',
            $jerseySize,

            // Emergency Contact
            $participant->emergency_name ?? '-',
            $participant->emergency_phone ?? '-',
            $participant->emergency_relation ?? '-',

            // Payment Info
            $registration?->status?->label() ?? '-',
            $registration?->total_amount ?? 0,
            $verifiedPayment?->verified_at?->format('d/m/Y H:i') ?? '-',
            $verifiedPayment ? 'Manual Transfer' : '-',
            $registration?->expired_at?->format('d/m/Y H:i') ?? '-',

            // Check-in Info
            $participant->isCheckedIn() ? 'Checked In' : 'Not Checked In',
            $latestCheckin?->checked_in_at?->format('d/m/Y H:i:s') ?? '-',

            // Timestamps
            $participant->created_at?->format('d/m/Y H:i:s') ?? '-',
            $participant->updated_at?->format('d/m/Y H:i:s') ?? '-',

            // Export Tracking
            $participant->last_exported_at?->format('d/m/Y H:i:s') ?? 'Never',
            $participant->export_count ?? 0,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();

        // Header style
        $sheet->getStyle('A1:'.$highestColumn.'1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 11,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '009AA6'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ]);

        // Set row height for header
        $sheet->getRowDimension(1)->setRowHeight(30);

        // Data rows style with alternating colors
        for ($row = 2; $row <= $highestRow; $row++) {
            $fillColor = ($row % 2 == 0) ? 'F9FAFB' : 'FFFFFF';

            $sheet->getStyle('A'.$row.':'.$highestColumn.$row)->applyFromArray([
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => $fillColor],
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => 'E5E7EB'],
                    ],
                ],
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ]);
        }

        // Center align specific columns
        $centerColumns = ['H', 'I', 'N', 'O', 'P', 'AC', 'AD']; // BIB, Is PIC, Gender, Birth Date, Age, Check-in Status, Check-in Time
        foreach ($centerColumns as $col) {
            $sheet->getStyle($col.'2:'.$col.$highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

        // Right align amount column
        $sheet->getStyle('Z2:Z'.$highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        return [];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $highestRow = $sheet->getHighestRow();

                // Freeze first row
                $sheet->freezePane('A2');

                // Auto filter
                $sheet->setAutoFilter('A1:'.$sheet->getHighestColumn().'1');

                // Conditional formatting for payment status (column Y)
                $statusColumn = 'Y';
                for ($row = 2; $row <= $highestRow; $row++) {
                    $cellValue = $sheet->getCell($statusColumn.$row)->getValue();

                    if (str_contains($cellValue, 'Verified') || str_contains($cellValue, 'Paid')) {
                        $sheet->getStyle($statusColumn.$row)->applyFromArray([
                            'font' => ['color' => ['rgb' => '10B981'], 'bold' => true],
                        ]);
                    } elseif (str_contains($cellValue, 'Pending')) {
                        $sheet->getStyle($statusColumn.$row)->applyFromArray([
                            'font' => ['color' => ['rgb' => 'F59E0B'], 'bold' => true],
                        ]);
                    } elseif (str_contains($cellValue, 'Rejected') || str_contains($cellValue, 'Cancelled')) {
                        $sheet->getStyle($statusColumn.$row)->applyFromArray([
                            'font' => ['color' => ['rgb' => 'EF4444'], 'bold' => true],
                        ]);
                    }
                }

                // Conditional formatting for check-in status (column AC)
                $checkinColumn = 'AC';
                for ($row = 2; $row <= $highestRow; $row++) {
                    $cellValue = $sheet->getCell($checkinColumn.$row)->getValue();

                    if (str_contains($cellValue, 'Checked In')) {
                        $sheet->getStyle($checkinColumn.$row)->applyFromArray([
                            'font' => ['color' => ['rgb' => '10B981'], 'bold' => true],
                        ]);
                    } else {
                        $sheet->getStyle($checkinColumn.$row)->applyFromArray([
                            'font' => ['color' => ['rgb' => '6B7280']],
                        ]);
                    }
                }
            },
        ];
    }

    public function columnFormats(): array
    {
        return [
            'Z' => '#,##0', // Total Amount as number format
        ];
    }

    public function title(): string
    {
        return 'Participants Data';
    }
}
