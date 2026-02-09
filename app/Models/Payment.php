<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Payment extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'amount' => 'decimal:2',
        'verified_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::deleting(function (Payment $payment): void {
            if ($payment->proof_path && Storage::disk('local')->exists($payment->proof_path)) {
                Storage::disk('local')->delete($payment->proof_path);
            }
        });
    }

    // Relationships
    public function registration()
    {
        return $this->belongsTo(Registration::class);
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    // Scopes
    public function scopeVerified(Builder $query): void
    {
        $query->whereNotNull('verified_at');
    }

    public function scopePending(Builder $query): void
    {
        $query->whereNull('verified_at')
            ->whereNull('rejection_reason');
    }

    public function scopeRejected(Builder $query): void
    {
        $query->whereNotNull('rejection_reason');
    }

    // Helper Methods
    public function isVerified(): bool
    {
        return ! is_null($this->verified_at);
    }

    public function isRejected(): bool
    {
        return ! is_null($this->rejection_reason);
    }

    public function isPending(): bool
    {
        return is_null($this->verified_at) && is_null($this->rejection_reason);
    }
}
