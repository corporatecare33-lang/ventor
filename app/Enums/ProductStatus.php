<?php

namespace App\Enums;

use App\Enums\Concerns\EnumHelpers;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum ProductStatus: string implements HasColor, HasLabel
{
    use EnumHelpers;

    case Active = 'active';
    case Draft = 'draft';
    case OutOfStock = 'out_of_stock';

    public function getLabel(): string
    {
        return match ($this) {
            self::Active => 'Active',
            self::Draft => 'Draft',
            self::OutOfStock => 'Out of Stock',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Active => 'success',
            self::Draft => 'gray',
            self::OutOfStock => 'danger',
        };
    }
}
