<?php

namespace App\Exports\Sheets;

use App\Enums\Gender;
use App\Enums\PaymentStatus;
use App\Models\JerseySize;
use App\Models\Participant;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SummarySheet implements FromCollection, ShouldAutoSize, WithEvents, WithStyles, WithTitle
{
    protected ?Builder $query = null;

    protected string $exportMode = 'all';

    public function __construct(?Builder $query = null, string $exportMode = 'all')
    {
        $this->query = $query;
        $this->exportMode = $exportMode;
    }

    public function collection()
    {
        $query = $this->query ?? Participant::query();

        // Apply export mode filter
        if ($this->exportMode === 'new_only') {
            $query->notExported();
        } elseif ($this->exportMode === 'updated_since_last') {
            $query->newOrUpdatedSinceLastExport();
        }

        $participants = $query->with(['registration.raceCategory.event', 'registration'])->get();

        $data = collect();

        // Title
        $data->push(['PARTICIPANTS SUMMARY REPORT']);
        $data->push(['Generated: '.now()->format('d F Y, H:i:s')]);
        $data->push(['Export Mode: '.ucwords(str_replace('_', ' ', $this->exportMode))]);
        $data->push(['']); // Empty row

        // Export Statistics
        $data->push(['EXPORT STATISTICS']);
        $data->push(['Total in This Export', $participants->count()]);
        $data->push(['Previously Exported', $participants->where('export_count', '>', 0)->count()]);
        $data->push(['Never Exported Before', $participants->where('export_count', 0)->count()]);
        if ($this->exportMode === 'new_only') {
            $data->push(['Filter Applied', 'New participants only (never exported)']);
        } elseif ($this->exportMode === 'updated_since_last') {
            $data->push(['Filter Applied', 'New or updated since last export']);
        } else {
            $data->push(['Filter Applied', 'All participants']);
        }
        $data->push(['']); // Empty row

        // Overview Statistics
        $data->push(['OVERVIEW']);
        $data->push(['Total Participants', $participants->count()]);
        $data->push(['Total PICs', $participants->where('is_pic', true)->count()]);
        $data->push(['Total Checked In', $participants->filter(fn ($p) => $p->isCheckedIn())->count()]);
        $data->push(['Not Checked In', $participants->filter(fn ($p) => ! $p->isCheckedIn())->count()]);
        $data->push(['With BIB Number', $participants->whereNotNull('bib_number')->count()]);
        $data->push(['Without BIB Number', $participants->whereNull('bib_number')->count()]);
        $data->push(['']); // Empty row

        // Gender Breakdown
        $data->push(['GENDER BREAKDOWN']);
        foreach (Gender::cases() as $gender) {
            $count = $participants->where('gender', $gender)->count();
            $percentage = $participants->count() > 0 ? round(($count / $participants->count()) * 100, 2) : 0;
            $data->push([$gender->label(), $count, $percentage.'%']);
        }
        $data->push(['']); // Empty row

        // Payment Status Breakdown
        $data->push(['PAYMENT STATUS']);
        $registrations = $participants->pluck('registration')->unique('id');
        foreach (PaymentStatus::cases() as $status) {
            $count = $registrations->where('status', $status)->count();
            $totalAmount = $registrations->where('status', $status)->sum('total_amount');
            $data->push([$status->label(), $count, 'Rp '.number_format($totalAmount, 0, ',', '.')]);
        }
        $totalRevenue = $registrations->where('status', PaymentStatus::PaymentVerified)->sum('total_amount');
        $data->push(['Total Verified Revenue', '', 'Rp '.number_format($totalRevenue, 0, ',', '.')]);
        $data->push(['']); // Empty row

        // Race Category Breakdown
        $data->push(['RACE CATEGORY BREAKDOWN']);
        $data->push(['Category', 'Distance', 'Participants']);
        $categoryStats = $participants->groupBy(fn ($p) => $p->registration?->raceCategory?->id);
        foreach ($categoryStats as $categoryId => $categoryParticipants) {
            $category = $categoryParticipants->first()?->registration?->raceCategory;
            if ($category) {
                $data->push([
                    $category->name,
                    $category->distance ?? '-',
                    $categoryParticipants->count(),
                ]);
            }
        }
        $data->push(['']); // Empty row

        // Jersey Size Breakdown
        $data->push(['JERSEY SIZE BREAKDOWN']);
        $data->push(['Size', 'Count']);
        $jerseySizes = $participants->groupBy('jersey_size')->sortKeys();
        foreach ($jerseySizes as $size => $sizeParticipants) {
            $jerseySize = JerseySize::query()->where('code', $size)->first();
            $sizeName = $jerseySize ? $jerseySize->name : strtoupper($size);
            $data->push([$sizeName, $sizeParticipants->count()]);
        }
        $data->push(['']); // Empty row

        // Registration Type Breakdown
        $data->push(['REGISTRATION TYPE']);
        $registrationTypes = $registrations->groupBy('registration_type');
        foreach ($registrationTypes as $type => $typeRegistrations) {
            $data->push([
                $typeRegistrations->first()?->registration_type?->label() ?? '-',
                $typeRegistrations->count().' registrations',
                $typeRegistrations->sum('participants_count').' participants',
            ]);
        }
        $data->push(['']); // Empty row

        // Age Group Breakdown
        $data->push(['AGE GROUP BREAKDOWN']);
        $data->push(['Age Range', 'Count']);
        $ageGroups = [
            '< 18' => $participants->filter(fn ($p) => $p->birth_date && $p->getAge() < 18)->count(),
            '18-25' => $participants->filter(fn ($p) => $p->birth_date && $p->getAge() >= 18 && $p->getAge() <= 25)->count(),
            '26-35' => $participants->filter(fn ($p) => $p->birth_date && $p->getAge() >= 26 && $p->getAge() <= 35)->count(),
            '36-45' => $participants->filter(fn ($p) => $p->birth_date && $p->getAge() >= 36 && $p->getAge() <= 45)->count(),
            '46-55' => $participants->filter(fn ($p) => $p->birth_date && $p->getAge() >= 46 && $p->getAge() <= 55)->count(),
            '> 55' => $participants->filter(fn ($p) => $p->birth_date && $p->getAge() > 55)->count(),
        ];
        foreach ($ageGroups as $range => $count) {
            $data->push([$range, $count]);
        }

        return $data;
    }

    public function styles(Worksheet $sheet)
    {
        // Title styling
        $sheet->getStyle('A1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 16,
                'color' => ['rgb' => '009AA6'],
            ],
        ]);

        $sheet->getStyle('A2')->applyFromArray([
            'font' => [
                'italic' => true,
                'size' => 10,
                'color' => ['rgb' => '6B7280'],
            ],
        ]);

        // Section headers styling
        $sectionHeaders = [5, 13, 21, 30, 38, 46, 54];
        foreach ($sectionHeaders as $row) {
            $sheet->getStyle('A'.$row)->applyFromArray([
                'font' => [
                    'bold' => true,
                    'size' => 12,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '009AA6'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_LEFT,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ]);
        }

        return [];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $highestRow = $sheet->getHighestRow();

                // Apply borders to data sections
                for ($row = 6; $row <= $highestRow; $row++) {
                    $cellValue = $sheet->getCell('A'.$row)->getValue();
                    if (! empty($cellValue) && $cellValue !== '') {
                        $sheet->getStyle('A'.$row.':C'.$row)->applyFromArray([
                            'borders' => [
                                'outline' => [
                                    'borderStyle' => Border::BORDER_THIN,
                                    'color' => ['rgb' => 'E5E7EB'],
                                ],
                            ],
                        ]);
                    }
                }

                // Bold and highlight total rows
                $totalRows = [12, 28]; // Adjust based on data structure
                foreach ($totalRows as $row) {
                    if ($row <= $highestRow) {
                        $sheet->getStyle('A'.$row.':C'.$row)->applyFromArray([
                            'font' => [
                                'bold' => true,
                            ],
                            'fill' => [
                                'fillType' => Fill::FILL_SOLID,
                                'startColor' => ['rgb' => 'FEF3C7'],
                            ],
                        ]);
                    }
                }
            },
        ];
    }

    public function title(): string
    {
        return 'Summary';
    }
}
