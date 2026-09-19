<?php

namespace App\Enums;

enum DeviceStatus: string
{
    case Available = 'available';
    case Assigned = 'assigned';
    case Maintenance = 'maintenance';
    case Retired = 'retired';

    public function label(): string
    {
        return match ($this) {
            self::Available => 'Available',
            self::Assigned => 'Assigned',
            self::Maintenance => 'Maintenance',
            self::Retired => 'Retired',
        };
    }
}
