<?php

namespace App\Enums;

enum Type: int
{
    CASE INDUSTRY = 1;
    CASE SUCURSAL = 2;


    public function label(): string
    {
        return match ($this) {
            self::INDUSTRY => 'Area de producción',
            self::SUCURSAL => 'Sucursal',
        };
    }
}
