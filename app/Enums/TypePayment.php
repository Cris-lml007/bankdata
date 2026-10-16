<?php

namespace App\Enums;

enum TypePayment: int
{
    CASE QR = 1;
    CASE CASH = 2;
    CASE MIXED = 3;
    CASE NONE = 4;
    CASE TRANSFER = 5;

    public function label(): string
    {
        return match ($this) {
            self::QR => 'QR',
            self::CASH => 'EFECTIVO',
            self::MIXED => 'MIXTO',
            self::NONE => 'NINGUNO',
            self::TRANSFER => 'TRANSFERENCIA'
        };
    }

}
