<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case PendingPayment = 'pending_payment';
    case PaymentUploaded = 'payment_uploaded';
    case PaymentVerified = 'payment_verified';
    case Expired = 'expired';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::PendingPayment => 'Pending Payment',
            self::PaymentUploaded => 'Payment Uploaded',
            self::PaymentVerified => 'Payment Verified',
            self::Expired => 'Expired',
            self::Cancelled => 'Cancelled',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PendingPayment => 'warning',
            self::PaymentUploaded => 'info',
            self::PaymentVerified => 'success',
            self::Expired => 'danger',
            self::Cancelled => 'danger',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::PendingPayment => 'heroicon-o-clock',
            self::PaymentUploaded => 'heroicon-o-arrow-up-tray',
            self::PaymentVerified => 'heroicon-o-check-circle',
            self::Expired => 'heroicon-o-x-circle',
            self::Cancelled => 'heroicon-o-no-symbol',
        };
    }

    public function isPaid(): bool
    {
        return $this === self::PaymentVerified;
    }

    public function canUploadPayment(): bool
    {
        return $this === self::PendingPayment;
    }

    public function canBeVerified(): bool
    {
        return $this === self::PaymentUploaded;
    }
}
