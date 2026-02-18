<?php

namespace App\Enums;

enum UnitType: string
{
    case CFT = 'CFT';
    case SFT = 'SFT';
    case RFT = 'RFT';
    case CUM = 'CUM';
    case SQM = 'SQM';
    case RM = 'RM';
    case KG = 'KG';
    case JOB = 'JOB';
    case NOS = 'NOS';
    case BAG = 'BAG';

    /**
     * Get all unit type values as an array (for dropdowns, validation, etc.).
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
