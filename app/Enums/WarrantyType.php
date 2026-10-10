<?php

namespace App\Enums;

use App\Enums\Concerns\EnumHelpers;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum WarrantyType: string implements HasColor, HasLabel
{
    use EnumHelpers;

    case Registration = 'registration';
    case Claim = 'claim';

    public function getLabel(): string
    {
        return match ($this) {
            self::Registration => 'Registration',
            self::Claim => 'Claim',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Registration => 'info',
            self::Claim => 'warning',
        };
    }
}
