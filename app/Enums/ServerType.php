<?php

namespace App\Enums;

enum ServerType: string
{
    case RDP = 'rdp';
    case SSH = 'ssh';
    case VNC = 'vnc';
    case Telnet = 'telnet';

    public function defaultPort(): int
    {
        return match ($this) {
            self::RDP => 3389,
            self::SSH => 22,
            self::VNC => 5900,
            self::Telnet => 23,
        };
    }
}
