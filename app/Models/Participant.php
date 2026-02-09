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
}
