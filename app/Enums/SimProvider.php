<?php

namespace App\Enums;

enum SimProvider: string
{
    case Ntc = 'ntc';
    case Ncell = 'ncell';
    case Jio = 'jio';
    case Airtel = 'airtel';
    case Vodafone = 'vodafone';
}
