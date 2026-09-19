<?php

namespace App\Enums;

enum SimStatus: string
{
    case Assigned = 'assigned';
    case ReturnedToIt = 'returned_to_it';
    case Lost = 'lost';

    public function label(): string
    {
        return match ($this) {
            self::Assigned => 'Assigned',
            self::ReturnedToIt => 'Returned to IT',
            self::Lost => 'Lost',
        };
    }
}
