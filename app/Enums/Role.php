<?php

namespace App\Enums;

enum Role: int
{
    CASE ADMIN = 1;
    CASE PRIVILEGED = 2;

    CASE RECEPT = 3;
    CASE WORKER = 4;

    public function label(): string
    {
        return match ($this) {
            self::ADMIN => 'Administrador',
            self::PRIVILEGED => 'Privilegiado',
            self::RECEPT => 'Recepción',
            self::WORKER => 'Trabajador',
        };
    }
}
