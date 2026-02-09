<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case QRIS = 'qris';
    case BankTransfer = 'bank_transfer';

    public function label(): string
    {
        return match ($this) {
            self::QRIS => 'QRIS',
            self::BankTransfer => 'Bank Transfer',
        };
    }

    public function indonesianLabel(): string
    {
        return match ($this) {
            self::QRIS => 'QRIS',
            self::BankTransfer => 'Transfer Bank',
        };
    }
}
