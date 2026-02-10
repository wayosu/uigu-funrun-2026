<?php

namespace App\Models;

use App\Enums\Gender;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Participant extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'gender' => Gender::class,
        'birth_date' => 'date',
        'is_pic' => 'boolean',
        'last_exported_at' => 'datetime',
    ];

    // Relationships
    public function registration()
    {
        return $this->belongsTo(Registration::class);
    }

    public function checkins()
    {
        return $this->hasMany(Checkin::class);
    }

    public function lastExportedBy()
    {
        return $this->belongsTo(User::class, 'last_exported_by');
    }

    // Scopes
    public function scopePic(Builder $query): void
    {
        $query->where('is_pic', true);
    }

    public function scopeHasBibNumber(Builder $query): void
    {
        $query->whereNotNull('bib_number');
    }

    public function scopeWithoutBibNumber(Builder $query): void
    {
        $query->whereNull('bib_number');
    }

    public function scopeCheckedIn(Builder $query): void
    {
        $query->whereHas('checkins');
    }

    public function scopeNotCheckedIn(Builder $query): void
    {
        $query->whereDoesntHave('checkins');
    }

    public function scopeExported(Builder $query): void
    {
        $query->whereNotNull('last_exported_at');
    }

    public function scopeNotExported(Builder $query): void
    {
        $query->whereNull('last_exported_at');
    }

    public function scopeExportedAfter(Builder $query, $date): void
    {
        $query->where('last_exported_at', '>', $date);
    }

    public function scopeNewOrUpdatedSinceLastExport(Builder $query): void
    {
        $query->where(function ($q) {
            $q->whereNull('last_exported_at')
                ->orWhereColumn('updated_at', '>', 'last_exported_at');
        });
    }

    // Helper Methods
    public function isCheckedIn(): bool
    {
        return $this->checkins()->exists();
    }

    public function hasBibNumber(): bool
    {
        return ! is_null($this->bib_number);
    }

    public function getAge(): int
    {
        /** @var \Illuminate\Support\Carbon $dateOfBirth */
        $dateOfBirth = $this->birth_date;

        return $dateOfBirth->diffInYears(now());
    }

    public function getFullContactInfo(): string
    {
        return "{$this->name} | {$this->email} | {$this->phone}";
    }

    public function markAsExported(?int $userId = null): void
    {
        $this->update([
            'last_exported_at' => now(),
            'last_exported_by' => $userId ?? auth()->id(),
            'export_count' => $this->export_count + 1,
        ]);
    }

    public function hasBeenExported(): bool
    {
        return ! is_null($this->last_exported_at);
    }
}
