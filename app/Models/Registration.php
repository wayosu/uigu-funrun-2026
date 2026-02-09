<?php

namespace App\Models;

use App\Enums\PaymentStatus;
use App\Enums\RegistrationType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Registration extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'registration_type' => RegistrationType::class,
        'status' => PaymentStatus::class,
        'total_amount' => 'decimal:2',
        'expired_at' => 'datetime',
        'payment_verified_at' => 'datetime',
    ];

    // Relationships
    public function event()
    {
        return $this->hasOneThrough(
            Event::class,
            RaceCategory::class,
            'id', // Foreign key on race_categories table
            'id', // Foreign key on events table
            'race_category_id', // Local key on registrations table
            'event_id' // Local key on race_categories table
        );
    }

    public function raceCategory()
    {
        return $this->belongsTo(RaceCategory::class);
    }

    public function participants()
    {
        return $this->hasMany(Participant::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function notificationLogs()
    {
        return $this->hasMany(NotificationLog::class);
    }

    public function verifiedBy()
    {
        return $this->belongsTo(User::class, 'payment_verified_by');
    }

    // Scopes
    public function scopePendingPayment(Builder $query): void
    {
        $query->where('status', PaymentStatus::PendingPayment);
    }

    public function scopePaymentUploaded(Builder $query): void
    {
        $query->where('status', PaymentStatus::PaymentUploaded);
    }

    public function scopePaymentVerified(Builder $query): void
    {
        $query->where('status', PaymentStatus::PaymentVerified);
    }

    public function scopeExpired(Builder $query): void
    {
        $query->where('status', PaymentStatus::Expired)
            ->orWhere(function ($q) {
                $q->where('expired_at', '<', now())
                    ->where('status', PaymentStatus::PendingPayment);
            });
    }

    public function scopeActive(Builder $query): void
    {
        $query->whereNotIn('status', [PaymentStatus::Expired, PaymentStatus::Cancelled]);
    }

    // Helper Methods
    public function isExpired(): bool
    {
        return $this->status === PaymentStatus::Expired
            || ($this->status === PaymentStatus::PendingPayment && $this->expired_at->isPast());
    }

    public function isPaid(): bool
    {
        return $this->status === PaymentStatus::PaymentVerified;
    }

    public function canUploadPayment(): bool
    {
        return $this->status === PaymentStatus::PendingPayment && ! $this->isExpired();
    }

    public function getPicParticipant(): ?Participant
    {
        return $this->participants()->where('is_pic', true)->first();
    }
}
