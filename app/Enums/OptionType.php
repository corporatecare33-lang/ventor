<?php

namespace App\Enums;

use App\Enums\Concerns\EnumHelpers;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum OptionType: string implements HasColor, HasLabel
{
    use EnumHelpers;

    case Color = 'color';
    case Material = 'material';
    case Size = 'size';

    public function getLabel(): string
    {
        return match ($this) {
            self::Color => 'Color',
            self::Material => 'Material',
            self::Size => 'Size',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Color => 'gray',
            self::Material => 'gray',
            self::Size => 'gray',
        };
    }
}
