<?php

namespace App\Enums;

enum Role: int
{
    CASE ADMIN = 1;
    CASE PRIVILEGED = 2;

    CASE SUPERVISOR = 3;
    CASE WORKER = 4;

    CASE RECEPT = 5;

    public function label(): string
    {
        return match ($this) {
            self::ADMIN => 'Administrador',
            self::PRIVILEGED => 'Privilegiado',
            self::SUPERVISOR => 'Supervisor',
            self::WORKER => 'Trabajador',
            self::RECEPT => 'Recepción'
        };
    }
}
