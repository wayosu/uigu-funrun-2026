<?php

namespace App\Enums;

enum RegistrationType: string
{
    case Individual = 'individual';
    case Collective5 = 'collective_5';
    case Collective10 = 'collective_10';

    public function label(): string
    {
        return match ($this) {
            self::Individual => 'Individual Registration',
            self::Collective5 => 'Collective Registration (5 People)',
            self::Collective10 => 'Collective Registration (10 People)',
        };
    }

    public function participantsCount(): int
    {
        return match ($this) {
            self::Individual => 1,
            self::Collective5 => 5,
            self::Collective10 => 10,
        };
    }

    public static function collectiveTypes(): array
    {
        return [
            self::Collective5,
            self::Collective10,
        ];
    }
}
