<?php

namespace App\Enums;

use App\Enums\Concerns\EnumHelpers;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum CouponType: string implements HasColor, HasLabel
{
    use EnumHelpers;

    case Fixed = 'fixed';
    case Percent = 'percent';

    public function getLabel(): string
    {
        return match ($this) {
            self::Fixed => 'Fixed Amount',
            self::Percent => 'Percentage',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Fixed => 'gray',
            self::Percent => 'gray',
        };
    }
}
