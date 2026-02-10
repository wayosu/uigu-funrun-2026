<?php

namespace App\Exports;

use App\Exports\Sheets\ParticipantsDataSheet;
use App\Exports\Sheets\SummarySheet;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithProperties;

class ParticipantsExport implements WithMultipleSheets, WithProperties
{
    protected ?Builder $query = null;

    protected string $exportMode = 'all'; // 'all', 'new_only', 'updated_since_last'

    public function __construct(?Builder $query = null, string $exportMode = 'all')
    {
        $this->query = $query;
        $this->exportMode = $exportMode;
    }

    public function sheets(): array
    {
        return [
            new SummarySheet($this->query, $this->exportMode),
            new ParticipantsDataSheet($this->query, $this->exportMode),
        ];
    }

    public function properties(): array
    {
        return [
            'creator' => 'UIGU Fun Run System',
            'lastModifiedBy' => 'Admin',
            'title' => 'Participants Export',
            'description' => 'Comprehensive participants data export with summary for event management',
            'subject' => 'Event Participants',
            'keywords' => 'participants,event,registration,export,summary,statistics',
            'category' => 'Event Management',
            'manager' => 'Event Admin',
            'company' => 'UIGU Fun Run',
        ];
    }
}
