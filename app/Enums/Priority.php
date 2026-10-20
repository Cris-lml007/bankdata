<?php

namespace App\Enums;

enum Priority: int
{
    CASE HIGH = 1;
    CASE URGENT = 2;
    CASE MEDIUM = 3;
    CASE LOW = 4;
    CASE NORMAL = 5;

    public function label(): string
    {
        return match ($this) {
            self::HIGH => 'Alto',
            self::URGENT => 'Urgente',
            self::MEDIUM => 'Media',
            self::LOW => 'Baja',
            self::NORMAL => 'Normal'
        };
    }

}
