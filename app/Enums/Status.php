<?php

namespace App\Enums;

enum Status: int
{
    CASE ACTIVE = 1;
    CASE INACTIVE = 2;
    CASE FINISH = 3;
    CASE DELIVERED = 4;
    CASE CANCELED = 5;

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => 'Activo',
            self::INACTIVE => 'Inactivo',
            self::FINISH => 'Finalizado',
            self::DELIVERED => 'Entregado',
            self::CANCELED => 'Cancelado',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::ACTIVE => 'badge-success',
            self::INACTIVE => 'badge-secondary',
            self::FINISH => 'badge-primary',
            self::DELIVERED => 'badge-info',
            self::CANCELED => 'badge-danger',
        };
    }
}
